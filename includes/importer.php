<?php
if (!defined('ABSPATH')) exit;
function nei_handle_import_upload() {
    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>Erreur lors de l\'upload du fichier.</p></div>';
        return;
    }

    $cpt = sanitize_text_field($_POST['target_cpt']);
    $file = $_FILES['import_file']['tmp_name'];
    $content = file_get_contents($file);
    $data = json_decode($content, true);

    if (!is_array($data)) {
        echo '<div class="notice notice-error"><p>Fichier JSON invalide.</p></div>';
        return;
    }

    foreach ($data as $item) {
        $post_id = wp_insert_post([
            'post_title'   => $item['title'],
            'post_content' => $item['content'],
            'post_name'    => $item['slug'],
            'post_status'  => 'publish',
            'post_type'    => $cpt,
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            // Import ACF
            foreach ($item['meta'] as $key => $value) {
                update_field($key, $value, $post_id);
            }
        
            // Import des taxonomies
            if (!empty($item['taxonomies'])) {
                foreach ($item['taxonomies'] as $taxonomy => $terms) {
                    wp_set_post_terms($post_id, $terms, $taxonomy);
                }
            }
        
            // Import image à la une
            if (!empty($item['featured_image'])) {
                nei_import_featured_image($item['featured_image'], $post_id);
            }
        }        

        if ($post_id && !is_wp_error($post_id)) {
            foreach ($item['meta'] as $key => $value) {
                update_field($key, $value, $post_id);
            }
        }

        // Import de l'image à la une (AJOUT)
        if (!empty($item['featured_image'])) {
            nei_import_featured_image($item['featured_image'], $post_id);
        }
    }

    echo '<div class="notice notice-success"><p>Importation terminée avec succès.</p></div>';
}
