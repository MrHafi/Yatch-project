<?php

/* allow long execution */
set_time_limit(0);

/* load WordPress */
require_once ABSPATH . 'wp-load.php';

/* stop if WP not loaded */
if (!defined('ABSPATH')) {
    exit;
}

/* sync yacht details in batches */
function yacht_details_sync_batch() {

    global $wpdb;

    /* tables */
    $yachts_table  = $wpdb->prefix . 'temp_yachts';
    $details_table = $wpdb->prefix . 'temp_yacht_details';

    $limit = 50; 

    /* Reads the last saved offset  */
    $offset = get_option('yacht_details_sync_offset', 0); //Offset = where to start reading rows

    /* get next batch */
    $codes = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT code FROM $yachts_table LIMIT %d OFFSET %d",
            $limit,
            $offset
        )
    );

    /* if finished reset offset */
    if (empty($codes)) {
        update_option('yacht_details_sync_offset', 0);
        return;
    }

    foreach ($codes as $code) {

      // AVODING EMPTY REQ
        if (!$code) continue;

        /* details API */
        $url = 'https://www.centralyachtagent.com/snapins/json-ebrochure.php?user=1073&apicode=1073YF4$sdRr91%X&idin='.$code;

        $response = wp_remote_get($url, ['timeout' => 60]);

        if (is_wp_error($response)) {
            continue;
        }

      

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['yacht'])) {
            continue;
        }

        sleep(1); // SMALL DELAY 

        $y = $data['yacht'];

        /* field mapping */
        $name = sanitize_text_field($y['yachtName']);
        $type = sanitize_text_field($y['yachtType']);
        $length_feet = floatval(str_replace(' Ft','',$y['sizeFeet'])); //removing ft if in api
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

        /* insert or update */
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $details_table
                (yacht_code,name,type,length_feet,beam,draft,pax,cabins,year_built,builder,low_price,high_price,currency,summer_area,winter_area,home_port,cruise_speed,ac,accommodations,description,captain_name,crew_name,crew_profile,main_image,layout_image)
                VALUES (%s,%s,%s,%f,%f,%f,%d,%d,%d,%s,%f,%f,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
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
                layout_image=VALUES(layout_image)",
                $code,$name,$type,$length_feet,$beam,$draft,$pax,$cabins,$year_built,$builder,$low_price,$high_price,$currency,$summer_area,$winter_area,$home_port,$cruise_speed,$ac,$accommodations,$description,$captain_name,$crew_name,$crew_profile,$main_image,$layout_image
            )
        );
    }

    /* update offset for next run */
    update_option('yacht_details_sync_offset', $offset + $limit);
}

/* run batch sync */
yacht_details_sync_batch();