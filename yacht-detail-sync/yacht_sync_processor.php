<?php

if (!defined('ABSPATH')) {
    exit;
}


// ---------------- Extract gallery images ----------------
function yacht_get_gallery_images($yacht) {

    $gallery_images = [];

    // Start from Pic2 so main image is not duplicated in gallery.
    for ($i = 2; $i <= 19; $i++) {
        $image_key = 'yachtPic' . $i;

        $image_url = esc_url_raw($yacht[$image_key] ?? '');

        if (!empty($image_url)) {
            $gallery_images[] = $image_url;
        }
    }

    return $gallery_images;
}


// ---------------- Build clean yacht data array ----------------
function yacht_get_clean_yacht_data($yacht) {

    $main_image_api   = esc_url_raw($yacht['yachtPic1'] ?? '');
    $layout_image_api = esc_url_raw($yacht['yachtLayout'] ?? '');

    $gallery_images = yacht_get_gallery_images($yacht);

    return [
        'name'            => sanitize_text_field($yacht['yachtName'] ?? ''),
        'previous_name'   => sanitize_text_field($yacht['yachtPreviousName'] ?? ''),
        'type'            => sanitize_text_field($yacht['yachtType'] ?? ''),

        'length_feet'     => (float) str_replace(' Ft', '', $yacht['sizeFeet'] ?? ''),
        'length_meter'    => (float) str_replace([' m', 'm'], '', $yacht['sizeMeter'] ?? ''),
        'beam'            => (float) ($yacht['yachtBeam'] ?? 0),
        'draft'           => (float) ($yacht['yachtDraft'] ?? 0),

        'pax'             => (int) ($yacht['yachtPax'] ?? 0),
        'cabins'          => (int) ($yacht['yachtCabins'] ?? 0),

        'year_built'      => (int) ($yacht['yachtYearBuilt'] ?? 0),
        'refit_year'      => (int) ($yacht['yachtRefit'] ?? 0),
        'builder'         => sanitize_text_field($yacht['yachtBuilder'] ?? ''),

        'low_price'       => (float) ($yacht['yachtLowNumericPrice'] ?? 0),
        'high_price'      => (float) ($yacht['yachtHighNumericPrice'] ?? 0),
        'currency'        => sanitize_text_field($yacht['yachtCurrency'] ?? ''),
        'terms'           => sanitize_text_field($yacht['yachtTerms'] ?? ''),

        'summer_area'     => sanitize_text_field($yacht['yachtSummerArea'] ?? ''),
        'winter_area'     => sanitize_text_field($yacht['yachtWinterArea'] ?? ''),
        'home_port'       => sanitize_text_field($yacht['yachtHomePort'] ?? ''),

        'cruise_speed'    => sanitize_text_field($yacht['yachtCruiseSpeed'] ?? ''),
        'max_speed'       => sanitize_text_field($yacht['yachtMaxSpeed'] ?? ''),

        'description'     => wp_kses_post($yacht['yachtDesc1'] ?? ''),
        'accommodations'  => wp_kses_post($yacht['yachtAccommodations'] ?? ''),

        'captain_name'    => sanitize_text_field($yacht['yachtCaptainName'] ?? ''),
        'crew_profile'    => wp_kses_post($yacht['yachtCrewProfile'] ?? ''),

        'main_image_api'   => $main_image_api,
        'layout_image_api' => $layout_image_api,
        'gallery_images'   => wp_json_encode($gallery_images),
    ];
}


// ---------------- Create hash for change detection ----------------
function yacht_get_data_hash($data) {

    return md5(wp_json_encode([
        $data['name'],
        $data['previous_name'],
        $data['type'],

        $data['length_feet'],
        $data['length_meter'],
        $data['beam'],
        $data['draft'],

        $data['pax'],
        $data['cabins'],

        $data['year_built'],
        $data['refit_year'],
        $data['builder'],

        $data['low_price'],
        $data['high_price'],
        $data['currency'],
        $data['terms'],

        $data['summer_area'],
        $data['winter_area'],
        $data['home_port'],

        $data['cruise_speed'],
        $data['max_speed'],

        $data['description'],
        $data['accommodations'],

        $data['captain_name'],
        $data['crew_profile'],

        $data['main_image_api'],
        $data['layout_image_api'],
        $data['gallery_images'],
    ]));
}


// ---------------- Get existing yacht row ----------------
function yacht_get_existing_yacht_row($code, $details_table) {

    global $wpdb;

    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, data_hash, main_image, main_image_api, layout_image, layout_image_api
             FROM $details_table
             WHERE yacht_code = %s
             LIMIT 1",
            $code
        )
    );
}


// ---------------- Check whether image is used by another yacht ----------------
function yacht_image_is_used_elsewhere($details_table, $image_url, $current_id) {

    global $wpdb;

    if (empty($image_url)) {
        return false;
    }

    $count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*)
             FROM $details_table
             WHERE id != %d
             AND (main_image = %s OR layout_image = %s)",
            (int) $current_id,
            $image_url,
            $image_url
        )
    );

    return ((int) $count > 0);
}


// ---------------- Delete old image only when safe ----------------
function yacht_delete_old_image_if_needed($details_table, $old_image_url, $current_id) {

    if (empty($old_image_url)) {
        return;
    }

    // Do not delete if another yacht is still using the same image.
    if (yacht_image_is_used_elsewhere($details_table, $old_image_url, $current_id)) {
        return;
    }

    yacht_delete_old_image($old_image_url);
}


// ---------------- Save yacht row ----------------
function yacht_save_yacht_row($code, $data, $details_table, $sync_time, $existing_row) {

    global $wpdb;

    $db_data = [
        'yacht_code'       => $code,

        'name'             => $data['name'],
        'previous_name'    => $data['previous_name'],
        'type'             => $data['type'],

        'length_feet'      => $data['length_feet'],
        'length_meter'     => $data['length_meter'],
        'beam'             => $data['beam'],
        'draft'            => $data['draft'],

        'pax'              => $data['pax'],
        'cabins'           => $data['cabins'],

        'year_built'       => !empty($data['year_built']) ? $data['year_built'] : null,
        'refit_year'       => !empty($data['refit_year']) ? $data['refit_year'] : null,
        'builder'          => $data['builder'],

        'low_price'        => $data['low_price'],
        'high_price'       => $data['high_price'],
        'currency'         => $data['currency'],
        'terms'            => $data['terms'],

        'summer_area'      => $data['summer_area'],
        'winter_area'      => $data['winter_area'],
        'home_port'        => $data['home_port'],

        'cruise_speed'     => $data['cruise_speed'],
        'max_speed'        => $data['max_speed'],

        'description'      => $data['description'],
        'accommodations'   => $data['accommodations'],

        'captain_name'     => $data['captain_name'],
        'crew_profile'     => $data['crew_profile'],

        'main_image'       => $data['main_image'],
        'main_image_api'   => $data['main_image_api'],
        'layout_image'     => $data['layout_image'],
        'layout_image_api' => $data['layout_image_api'],
        'gallery_images'   => $data['gallery_images'],

        'last_seen'        => $sync_time,
        'status'           => 'active',
        'data_hash'        => $data['data_hash'],
    ];

    if ($existing_row) {
        $wpdb->update(
            $details_table,
            $db_data,
            ['id' => $existing_row->id]
        );
    } else {
        $wpdb->insert($details_table, $db_data);
    }
}


// ---------------- Process a single yacht ----------------
function yacht_process_single_yacht($code, $details_table, $sync_time) {

    // Get yacht data from API.
    $yacht = yacht_get_api_yacht_data($code);

    if (!$yacht) {
        return;
    }

    // Build clean data array.
    $data = yacht_get_clean_yacht_data($yacht);

    // Get existing row if yacht already exists.
    $existing_row = yacht_get_existing_yacht_row($code, $details_table);

    $existing_hash            = $existing_row ? $existing_row->data_hash : '';
    $existing_main_image      = $existing_row ? $existing_row->main_image : '';
    $existing_main_image_api  = $existing_row ? $existing_row->main_image_api : '';
    $existing_layout_image    = $existing_row ? $existing_row->layout_image : '';
    $existing_layout_image_api= $existing_row ? $existing_row->layout_image_api : '';
    $existing_id              = $existing_row ? (int) $existing_row->id : 0;

    // Create hash after all clean values are prepared.
    $data['data_hash'] = yacht_get_data_hash($data);

    $data_changed        = ($existing_hash !== $data['data_hash']);
    $main_image_changed  = ($existing_main_image_api !== $data['main_image_api']);
    $layout_image_changed= ($existing_layout_image_api !== $data['layout_image_api']);

   // Skip full update when nothing changed.
    if ($existing_row && !$data_changed && !$main_image_changed && !$layout_image_changed) {

        global $wpdb;

        $wpdb->update(
            $details_table,
            [
                'last_seen' => $sync_time,
                'status'    => 'active',
            ],
            ['id' => $existing_id]
        );

        // Return 'skipped' because data hasn't changed, no full update needed
        return 'skipped';
    }

    // Keep old image values by default.
    $data['main_image']   = $existing_main_image;
    $data['layout_image'] = $existing_layout_image;

    // Download main image only when source changed.
    if (!empty($data['main_image_api']) && $main_image_changed) {

        $new_main_image = yacht_get_processed_image_url($data['main_image_api'], $code);

        if (!empty($new_main_image)) {
            $data['main_image'] = $new_main_image;

            // Delete old image only after new one is ready.
            yacht_delete_old_image_if_needed($details_table, $existing_main_image, $existing_id);
        } else {
            yacht_sync_log('Main image update failed for yacht ' . $code);
        }
    }

    // Download layout image only when source changed.
    if (!empty($data['layout_image_api']) && $layout_image_changed) {

        $new_layout_image = yacht_get_processed_image_url($data['layout_image_api'], $code);

        if (!empty($new_layout_image)) {
            $data['layout_image'] = $new_layout_image;

            // Delete old image only after new one is ready.
            yacht_delete_old_image_if_needed($details_table, $existing_layout_image, $existing_id);
        } else {
            yacht_sync_log('Layout image update failed for yacht ' . $code);
        }
    }

    // Save new or updated yacht row.
    yacht_save_yacht_row($code, $data, $details_table, $sync_time, $existing_row);

    if (!$existing_row) {
        yacht_sync_log('Yacht ' . $code . ' added.');
        return 'added';
    }

    yacht_sync_log('Yacht ' . $code . ' updated.');
    return 'updated';
}