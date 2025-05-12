<?php
if (!defined('ABSPATH')) exit;

// Téléverse une image depuis une URL si elle n'existe pas déjà
function nei_import_featured_image($image_url, $post_id) {
    // Check if the image already exists as a post thumbnail
    if (has_post_thumbnail($post_id)) {
        return get_post_thumbnail_id($post_id);
    }

    // Check if the image already exists in the media library
    $existing_media = attachment_url_to_postid($image_url);
    if ($existing_media) {
        set_post_thumbnail($post_id, $existing_media);
        return $existing_media;
    }

    // Upload the image if it doesn't exist
    $media = media_sideload_image($image_url, $post_id, null, 'id');

    if (!is_wp_error($media)) {
        set_post_thumbnail($post_id, $media);
    }

    return $media;
}
