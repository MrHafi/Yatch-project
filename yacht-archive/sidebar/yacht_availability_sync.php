<?php
// RUN THIS FILE VIA SERVER CRON JOB 
if (php_sapi_name() !== 'cli') {
    exit('Direct access not allowed.');
}

set_time_limit(0); //no time limit for this complete this easily

// Load WordPress core.
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once ABSPATH . 'wp-includes/pluggable.php';


// ---------------- Write sync log ----------------//
function yacht_availability_log( $message ) {
    $log_file = plugin_dir_path( __FILE__ ) . 'yacht_availability_log.txt';
    $time     = current_time( 'mysql' );
    file_put_contents( $log_file, $time . ' - ' . $message . PHP_EOL, FILE_APPEND );
}

// ---------------- Fetch & sync availability ----------------
function yacht_availability_sync() {

    global $wpdb;
    $table        = $wpdb->prefix . 'yacht_availability';
    $yachts_table = $wpdb->prefix . 'temp_yachts';

    // get all yacht codes from DB
    $codes = $wpdb->get_col( "SELECT code FROM $yachts_table" );

    if ( empty( $codes ) ) {
        yacht_availability_log( 'No yacht codes found.' );
        return;
    }

    foreach ( $codes as $code ) {

        usleep( 200000 ); // 0.2s delay between each API call

        $api_url  = 'https://www.centralyachtagent.com/snapins/json-calendar.php?idin=' . rawurlencode( $code ) . '&user=1073&apicode&act=1073YF4$sdRr91';
        $response = wp_remote_get( $api_url, [ 'timeout' => 15, 'sslverify' => true ] ); //give up after 15s

        if ( is_wp_error( $response ) ) {
            yacht_availability_log( 'API error for ' . $code . ': ' . $response->get_error_message() );
            continue; // skip this yacht, move to next
        }

        if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
            yacht_availability_log( 'Bad response for yacht: ' . $code );
            continue;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( json_last_error() !== JSON_ERROR_NONE || empty( $data['calendar'] ) ) {
            yacht_availability_log( 'Empty calendar for yacht: ' . $code );
            continue;
        }

        $wpdb->query( 'START TRANSACTION' );

        $wpdb->delete( $table, [ 'yacht_code' => $code ], [ '%s' ] ); // delete old data for this codeOK, so.

        foreach ( $data['calendar'] as $entry ) {

            $yacht_code = sanitize_text_field( $entry['yachtBookId']       ?? '' );
            $start      = sanitize_text_field( $entry['yachtStartDateNum'] ?? '' );
            $end        = sanitize_text_field( $entry['yachtEndDateNum']   ?? '' );
            $book_type  = absint( $entry['yachtBookType'] ?? 0 );

            // Bad data? Skip it. Don't save garbage.
            if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) ) continue;
            if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end   ) ) continue;
            if ( ! in_array( $book_type, [ 1, 2, 3 ], true )      ) continue;
            if ( empty( $yacht_code )                              ) continue;

            $wpdb->insert( $table, [
                'yacht_code' => $yacht_code,
                'start_date' => $start,
                'end_date'   => $end,
                'book_type'  => $book_type,
                'synced_at'  => current_time( 'mysql' ),
            ], [ '%s', '%s', '%s', '%d', '%s' ] );
        }

        $wpdb->query( 'COMMIT' );
        yacht_availability_log( 'Synced availability for yacht: ' . $code );
    }

    yacht_availability_log( 'Full availability sync completed.' );
}

// ---------------- Run ----------------
yacht_availability_sync();