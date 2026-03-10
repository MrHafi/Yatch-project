<?php 
/* 
find existing attachment by source URL
download image if needed
convert to webp
delete old image when changed
*/


if (!defined('ABSPATH')) {
    exit;
}


// ---------------- Get attachment ID by stored source URL ----------------
function yacht_get_attachment_id_by_source_url($image_url) {

    global $wpdb;

    if (empty($image_url)) {
        return 0;
    }

    $attachment_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_yacht_source_url'
             AND meta_value = %s
             LIMIT 1",
            $image_url
        )
    );

    return (int) $attachment_id;
}


// ---------------- Get source URL from existing WP image ----------------
function yacht_get_saved_image_source_url($wp_image_url) {

    if (empty($wp_image_url)) {
        return '';
    }

    $attachment_id = attachment_url_to_postid($wp_image_url);

    if (!$attachment_id) {
        return '';
    }

    return (string) get_post_meta($attachment_id, '_yacht_source_url', true);
}


// ---------------- Save source URL on attachment ----------------
function yacht_save_attachment_source_url($attachment_id, $image_url) {

    if (!$attachment_id || empty($image_url)) {
        return;
    }

    update_post_meta($attachment_id, '_yacht_source_url', $image_url);
}


// ---------------- Convert image to webp when possible ----------------
function yacht_convert_image_to_webp($attachment_id) {

    $file_path = get_attached_file($attachment_id);

    if (empty($file_path) || !file_exists($file_path)) {
        return;
    }

    $image = wp_get_image_editor($file_path);

    if (is_wp_error($image)) {
        return;
    }

    $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file_path);

    // Stop if file extension is not supported for webp conversion.
    if ($webp_path === $file_path) {
        return;
    }

    $image->set_quality(75); // Use balanced compression quality.
    $saved = $image->save($webp_path, 'image/webp');

    if (is_wp_error($saved) || empty($saved['path'])) {
        return;
    }

       update_attached_file($attachment_id, $saved['path']);

    $attachment_data = wp_generate_attachment_metadata($attachment_id, $saved['path']);
    wp_update_attachment_metadata($attachment_id, $attachment_data);

    // Update mime type after converting to webp.
    wp_update_post([
        'ID' => $attachment_id,
        'post_mime_type' => 'image/webp',
    ]);

    // Delete old original file after successful webp conversion.
    if ($saved['path'] !== $file_path && file_exists($file_path)) {
        unlink($file_path);
    }
}


// ---------------- Download or reuse image ----------------
function yacht_get_processed_image_url($image_url, $code) {

    if (empty($image_url)) {
        return '';
    }

    // Reuse old attachment if the same source URL was already downloaded before.
    $attachment_id = yacht_get_attachment_id_by_source_url($image_url);

    if (!$attachment_id) {
        $attachment_id = media_sideload_image($image_url, 0, null, 'id');
    }

    if (is_wp_error($attachment_id)) {
        yacht_sync_log('Image download failed for yacht ' . $code . ' - ' . $attachment_id->get_error_message());
        return '';
    }

    yacht_save_attachment_source_url($attachment_id, $image_url);
    yacht_convert_image_to_webp($attachment_id);

    return (string) wp_get_attachment_url($attachment_id);
}


// ---------------- Delete old WP image attachment ----------------
function yacht_delete_old_image($wp_image_url) {

    if (empty($wp_image_url)) {
        return;
    }

    $attachment_id = attachment_url_to_postid($wp_image_url);

    if ($attachment_id) {
        wp_delete_attachment($attachment_id, true);
    }
}