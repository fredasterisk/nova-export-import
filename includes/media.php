<?php
if (!defined('ABSPATH')) exit;

// Téléverse une image depuis une URL si elle n'existe pas déjà
function nei_import_featured_image($image_url, $post_id) {
    $media = media_sideload_image($image_url, $post_id, null, 'id');

    if (!is_wp_error($media)) {
        set_post_thumbnail($post_id, $media);
    }

    return $media;
}
