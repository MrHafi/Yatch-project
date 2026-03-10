<?php

if (!defined('ABSPATH')) {
    exit;
}


// ---------------- Get single yacht data from API ----------------
function yacht_get_api_yacht_data($code) {

    // Build API URL for the current yacht code.
    $url = 'https://www.centralyachtagent.com/snapins/json-ebrochure.php?user=1073&apicode=1073YF4$sdRr91%X&idin=' . rawurlencode($code);

    usleep(200000); // Small delay to reduce API pressure.

    $response = wp_remote_get($url, [
        'timeout' => 60,
    ]);

    // Stop if request failed completely.
    if (is_wp_error($response)) {
        yacht_sync_log('API request failed for yacht ' . $code . ' - ' . $response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    // Stop if API did not return success.
    if ($status_code !== 200) {
        yacht_sync_log('API returned status ' . $status_code . ' for yacht ' . $code);
        return false;
    }

    $body = wp_remote_retrieve_body($response);

    // Stop if API returned empty body.
    if (empty($body)) {
        yacht_sync_log('Empty API response for yacht ' . $code);
        return false;
    }

    $data = json_decode($body, true);

    // Stop if JSON is invalid.
    if (json_last_error() !== JSON_ERROR_NONE) {
        yacht_sync_log('Invalid JSON for yacht ' . $code . ' - ' . json_last_error_msg());
        return false;
    }

    // Stop if yacht key is missing.
    if (empty($data['yacht']) || !is_array($data['yacht'])) {
        yacht_sync_log('Missing yacht data for yacht ' . $code);
        return false;
    }

    return $data['yacht'];
}