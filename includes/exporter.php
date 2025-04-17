<?php
if (!defined('ABSPATH')) exit;
function nei_cpt_exporter_handle_export() {
    $cpt = sanitize_text_field($_POST['cpt']);
    $fields = $_POST['acf_fields'] ?? [];

    $posts = get_posts(['post_type' => $cpt, 'numberposts' => -1]);
    $output = [];

    foreach ($posts as $post) {
        $entry = [
            'title' => $post->post_title,
            'content' => $post->post_content,
            'slug' => $post->post_name,
            'meta' => [],
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'), // ← AJOUT ICI
        ];

        foreach ($fields as $field_key) {
            $entry['meta'][$field_key] = get_field($field_key, $post->ID);
        }

        $output[] = $entry;
    }

    $filename = 'nova_export_' . $cpt . '_' . date('Ymd_His') . '.json';
    header('Content-disposition: attachment; filename=' . $filename);
    header('Content-type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}
