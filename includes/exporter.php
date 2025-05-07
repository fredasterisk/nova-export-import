<?php

if (!defined('ABSPATH')) exit;

function nei_cpt_exporter_handle_export() {
    $cpt = sanitize_text_field($_POST['cpt']);
    $fields = $_POST['acf_fields'] ?? [];
    $taxonomy_data = nei_export_taxonomies_and_terms($cpt);
    $query = new WP_Query([
        'post_type'           => $cpt,
        'posts_per_page'      => -1,
        'post_status'         => 'any',     // or 'publish' if you only want published
        'ignore_sticky_posts' => true,
    ]);
    $posts = $query->have_posts() ? $query->posts : [];
    $output = [
        'posts' => [],
        'defined_taxonomies' => $taxonomy_data,
    ];

    foreach ($posts as $post) {
        $entry = [
            'title' => $post->post_title,
            'content' => $post->post_content,
            'slug' => $post->post_name,
            'post_date' => $post->post_date,
            'post_date_gmt' => $post->post_date_gmt,
            'post_modified' => $post->post_modified,
            'post_modified_gmt' => $post->post_modified_gmt,
            'taxonomies' => [],
            'meta' => [],
            'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
        ];

        foreach ($fields as $field_key) {
            $entry['meta'][$field_key] = get_field($field_key, $post->ID);
        }

        $taxonomies = get_object_taxonomies($post->post_type);

        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'names']);
            if (!is_wp_error($terms)) {
                $entry['taxonomies'][$taxonomy] = $terms;
            }
        }


        $output['posts'][] = $entry;
    }
    wp_reset_postdata();
    // Clean output buffer if anything was sent before
    if (ob_get_length()) {
        ob_clean();
    }
    $filename = 'nova_export_' . $cpt . '_' . date('Ymd_His') . '.json';
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Type: application/json');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    echo json_encode($output, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}