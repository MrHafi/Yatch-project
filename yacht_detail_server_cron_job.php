<?php


set_time_limit(0); // prevents timeout during large API sync

// load WordPress core
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; // loads WP environment
require_once ABSPATH . 'wp-includes/pluggable.php';

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

/*  first batch of a new sync cycle */
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

// Load WordPress media tools once
require_once ABSPATH.'wp-admin/includes/file.php';
require_once ABSPATH.'wp-admin/includes/media.php';
require_once ABSPATH.'wp-admin/includes/image.php';

    /* loop through each yacht code */
    foreach ($codes as $code) {

        // skip empty yacht codes
        if (!$code) continue; // prevents invalid API calls

        /* build API request URL */
        $url = 'https://www.centralyachtagent.com/snapins/json-ebrochure.php?user=1073&apicode=1073YF4$sdRr91%X&idin='.$code;

        usleep(200000); // small delay to prevent API rate limits
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



/////------------------------------------------------------------------------------------------------------------------------------------

        /* store yacht data */
        $y = $data['yacht'];

        /* =============================ALL FIELDS FROM APUI=============== */
        $name = sanitize_text_field($y['yachtName'] ?? ''); 
       $type = sanitize_text_field($y['yachtType'] ?? '');
        $length_feet = floatval(str_replace(' Ft','',$y['sizeFeet'])); // remove "Ft" text
        $beam = floatval($y['yachtBeam'] ?? 0);
        $draft = floatval($y['yachtDraft'] ?? 0);      $pax = intval($y['yachtPax'] ?? 0);
       $cabins = intval($y['yachtCabins'] ?? 0);
        $year_built = intval($y['yachtYearBuilt'] ?? 0);
        $builder = sanitize_text_field($y['yachtBuilder'] ?? '');
        $low_price = floatval($y['yachtLowNumericPrice']);
        $high_price = floatval($y['yachtHighNumericPrice']);
        $currency = sanitize_text_field($y['yachtCurrency']) ?? '';
        $summer_area = sanitize_text_field($y['yachtSummerArea']) ?? '';
        $winter_area = sanitize_text_field($y['yachtWinterArea']) ?? '';
        $home_port = sanitize_text_field($y['yachtHomePort']) ?? '';
        $cruise_speed = sanitize_text_field($y['yachtCruiseSpeed']);
        $ac = sanitize_text_field($y['yachtAc']) ?? '';
        $accommodations = $y['yachtAccommodations'];
        $description = $y['yachtDesc1'];
        $captain_name = sanitize_text_field($y['yachtCaptainName']);
        $crew_name = sanitize_text_field($y['yachtCrewName']);
        $crew_profile = $y['yachtCrewProfile'];
        $main_image = esc_url_raw($y['yachtPic1']); //API IMAGE 
        $layout_image_api = esc_url_raw($y['yachtLayout']); //MAIN IMAGE TO DOWNLAD



          // Fetch existing yacht record from database
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT data_hash, layout_image, layout_image_api 
                FROM $details_table 
                WHERE yacht_code=%s",
                $code
            )
        );
        // Previous values from DB
        $existing_hash          = $row->data_hash ?? null;
        $new_wp_image           = $row->layout_image ?? ''; // // final WordPress URL of downloaded image
        $old_layout_image_api   = $row->layout_image_api ?? ''; //// Previously stored API layout image (used to detect image change)



        //---------------- HASHING THE FILEDS, to check if anything change before updating  -------------
        $data_hash = md5(json_encode([
        $name,
        $type,
        $length_feet,
        $beam,
        $draft,
        $pax,
        $cabins,
        $year_built,
        $builder,
        $low_price,
        $high_price,
        $currency,
        $summer_area,
        $winter_area,
        $home_port,
        $cruise_speed,
        $ac,
        $accommodations,
        $description,
        $captain_name,
        $crew_name,
        $crew_profile
        ]));

// Detect data change
$data_changed = ($existing_hash !== $data_hash);

// Detect image change
$image_changed = ($old_layout_image_api !== $layout_image_api);


//-0------------------- Skip update if nothing changed-----------------
if (!$data_changed && !$image_changed) {

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE $details_table 
             SET last_seen=%d 
             WHERE yacht_code=%s",
            $sync_time,
            $code
        )
    );

    continue;
}


// Download new image only when layout image changed
if ($image_changed && !empty($layout_image_api)) {

    $attachment_id = media_sideload_image($layout_image_api, 0, null, 'id');

    if (!is_wp_error($attachment_id)) {
        $new_wp_image = wp_get_attachment_url($attachment_id);
    }
}



        /* INSERT NEW YACHT OR UPDATE EXISTOMG ONE */
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $details_table
                (yacht_code,name,type,length_feet,beam,draft,pax,cabins,year_built,builder,low_price,high_price,currency,summer_area,winter_area,home_port,cruise_speed,ac,accommodations,description,captain_name,crew_name,crew_profile,main_image,layout_image,layout_image_api,last_seen,status,data_hash)
                VALUES (%s,%s,%s,%f,%f,%f,%d,%d,%d,%s,%f,%f,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,'active', %s)
              
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
                layout_image_api=VALUES(layout_image_api),
                last_seen=%d, 
                status='active',
                data_hash=VALUES(data_hash)", 
                
                $code,$name,$type,$length_feet,$beam,$draft,$pax,$cabins,$year_built,$builder,$low_price,$high_price,$currency,$summer_area,$winter_area,$home_port,$cruise_speed,$ac,$accommodations,$description,$captain_name,$crew_name,$crew_profile,$main_image,$new_wp_image,$layout_image_api,$sync_time,$sync_time, $data_hash
            )
        );
    }

    /* move offset forward for next cron run */
    update_option('yacht_details_sync_offset', $offset + $limit); // next batch start
}

/* run the sync function */
yacht_details_sync_batch(); // execute batch sync