<?php


set_time_limit(0); // prevents timeout during large API sync

// load WordPress core
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; // loads WP environment

// stop execution if WordPress failed to load
if (!defined('ABSPATH')) {
    exit;
}


function yacht_details_sync_batch() {

    global $wpdb; 

    /* define database tables */
    $yachts_table  = $wpdb->prefix . 'temp_yachts'; 
    $details_table = $wpdb->prefix . 'temp_yacht_details'; 

    $limit = 50; // batch size


   /* get sync timestamp for the entire sync cycle */
$sync_time = get_option('yacht_sync_time'); // read previously saved sync time (same for all batches)

/* if this is the first batch of a new sync cycle */
if (!$sync_time) {
    $sync_time = current_time('timestamp'); // generate one timestamp for the whole sync
    update_option('yacht_sync_time', $sync_time); // save it so next batches use same value
}


    /* get last processed offset from WP options */
    $offset = get_option('yacht_details_sync_offset', 0); // starting point for this/current batch

    /* fetch next batch of yacht codes */
    $codes = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT code FROM $yachts_table ORDER BY code LIMIT %d OFFSET %d", // paginated fetch
            $limit,
            $offset
        )
    );

    /* if no codes found → sync finished */
    if (empty($codes)) {

    /* mark yachts not seen in this sync as removed */
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE $details_table SET status='removed' WHERE last_seen < %d",
            $sync_time
        )
    );

    update_option('yacht_details_sync_offset', 0); // restart next cycle
    delete_option('yacht_sync_time'); // reset sync cycle

    return;
}

    /* loop through each yacht code */
    foreach ($codes as $code) {

        // skip empty yacht codes
        if (!$code) continue; // prevents invalid API calls

        /* build API request URL */
        $url = 'https://www.centralyachtagent.com/snapins/json-ebrochure.php?user=1073&apicode=1073YF4$sdRr91%X&idin='.$code;

        /* request yacht details from API */
        $response = wp_remote_get($url, ['timeout' => 60]); // remote request with timeout


        /* skip if API request failed */
if (is_wp_error($response)) {
continue; // request failed
}

$status = wp_remote_retrieve_response_code($response); // get HTTP status

if ($status !== 200) {
continue; // skip if API not OK
}

        /* get API response body */
        $body = wp_remote_retrieve_body($response); // extract raw JSON

            /* convert JSON to PHP array */
            $data = json_decode($body, true); // decode API response

            if (!$data || json_last_error() !== JSON_ERROR_NONE) { // check if JSON invalid
                continue; // skip this yacht
            }

        /* skip if yacht data missing */
        if (empty($data['yacht'])) {
            continue; // invalid or removed yacht
        }

        sleep(200000); // small delay to prevent API rate limits

        /* store yacht data */
        $y = $data['yacht']; // shorthand variable

        /* sanitize and map fields from API */
        $name = sanitize_text_field($y['yachtName'] ?? ''); 
        $type = sanitize_text_field($y['yachtType']);
        $length_feet = floatval(str_replace(' Ft','',$y['sizeFeet'])); // remove "Ft" text
        $beam = floatval($y['yachtBeam']);
        $draft = floatval($y['yachtDraft']);
        $pax = intval($y['yachtPax']);
        $cabins = intval($y['yachtCabins']);
        $year_built = intval($y['yachtYearBuilt']);
        $builder = sanitize_text_field($y['yachtBuilder']);
        $low_price = floatval($y['yachtLowNumericPrice']);
        $high_price = floatval($y['yachtHighNumericPrice']);
        $currency = sanitize_text_field($y['yachtCurrency']);
        $summer_area = sanitize_text_field($y['yachtSummerArea']);
        $winter_area = sanitize_text_field($y['yachtWinterArea']);
        $home_port = sanitize_text_field($y['yachtHomePort']);
        $cruise_speed = sanitize_text_field($y['yachtCruiseSpeed']);
        $ac = sanitize_text_field($y['yachtAc']);
        $accommodations = $y['yachtAccommodations'];
        $description = $y['yachtDesc1'];
        $captain_name = sanitize_text_field($y['yachtCaptainName']);
        $crew_name = sanitize_text_field($y['yachtCrewName']);
        $crew_profile = $y['yachtCrewProfile'];
        $main_image = esc_url_raw($y['yachtPic1']);
        $layout_image = esc_url_raw($y['yachtLayout']);

        /* insert new yacht or update existing */
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $details_table
                (yacht_code,name,type,length_feet,beam,draft,pax,cabins,year_built,builder,low_price,high_price,currency,summer_area,winter_area,home_port,cruise_speed,ac,accommodations,description,captain_name,crew_name,crew_profile,main_image,layout_image,last_seen,status)
                VALUES (%s,%s,%s,%f,%f,%f,%d,%d,%d,%s,%f,%f,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,'active')
                ON DUPLICATE KEY UPDATE
                name=VALUES(name),
                type=VALUES(type),
                length_feet=VALUES(length_feet),
                beam=VALUES(beam),
                draft=VALUES(draft),
                pax=VALUES(pax),
                cabins=VALUES(cabins),
                year_built=VALUES(year_built),
                builder=VALUES(builder),
                low_price=VALUES(low_price),
                high_price=VALUES(high_price),
                currency=VALUES(currency),
                summer_area=VALUES(summer_area),
                winter_area=VALUES(winter_area),
                home_port=VALUES(home_port),
                cruise_speed=VALUES(cruise_speed),
                ac=VALUES(ac),
                accommodations=VALUES(accommodations),
                description=VALUES(description),
                captain_name=VALUES(captain_name),
                crew_name=VALUES(crew_name),
                crew_profile=VALUES(crew_profile),
                main_image=VALUES(main_image),
                layout_image=VALUES(layout_image),
                last_seen=%d, 
                status='active'", 
                $code,$name,$type,$length_feet,$beam,$draft,$pax,$cabins,$year_built,$builder,$low_price,$high_price,$currency,$summer_area,$winter_area,$home_port,$cruise_speed,$ac,$accommodations,$description,$captain_name,$crew_name,$crew_profile,$main_image,$layout_image,$sync_time,$sync_time
            )
        );
    }

    /* move offset forward for next cron run */
    update_option('yacht_details_sync_offset', $offset + $limit); // next batch start
}

/* run the sync function */
yacht_details_sync_batch(); // execute batch sync