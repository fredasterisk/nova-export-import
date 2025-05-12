<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function nei_handle_import_upload() {
    if ( empty( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
        echo '<div class="notice notice-error"><p>Erreur lors de l\'upload du fichier.</p></div>';
        return;
    }

    // Récupère le CPT cible
    $cpt   = sanitize_text_field( $_POST['target_cpt'] );
    $file  = $_FILES['import_file']['tmp_name'];
    $json  = file_get_contents( $file );
    $data  = json_decode( $json, true );

    if ( ! $data || ! is_array( $data ) ) {
        echo '<div class="notice notice-error"><p>Fichier JSON invalide ou vide.</p></div>';
        return;
    }

    // Si le JSON contient des définitions de taxonomies, on les importe d'abord
    if ( isset( $data['defined_taxonomies'] ) && is_array( $data['defined_taxonomies'] ) ) {
        nei_import_taxonomies_and_terms( $data['defined_taxonomies'] );
    }

    // Choix de l'ensemble d'éléments à importer
    if ( isset( $data['posts'] ) && is_array( $data['posts'] ) ) {
        $items = $data['posts'];
    } else {
        // JSON plat
        $items = $data;
    }

    foreach ( $items as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }
    
        $slug   = sanitize_title( $item['slug'] ?? '' );
        $title  = $item['title'] ?? '';
        $content= $item['content'] ?? '';
        $modified_date = $item['post_modified'] ?? null;
        $modified_date_gmt = $item['post_modified_gmt'] ?? null;
        $post_date = $item['post_date'] ?? null;
        $post_date_gmt = $item['post_date_gmt'] ?? null;
        $upload_media = true;
    
        // 1) On cherche un post existant au slug donné
        $existing = get_page_by_path( $slug, OBJECT, $cpt );
    
        if ( $existing ) {
            // 2a) Si trouvé → on met à jour
            $post_id = wp_update_post([
                'ID'           => $existing->ID,
                'post_title'   => $title,
                'post_content' => $content,
                'post_name'    => $slug,
                'post_modified' => $modified_date,
                'post_modified_gmt' => $modified_date_gmt,
                'post_date'    => $post_date,
                'post_date_gmt' => $post_date_gmt,
            ], true );
            $upload_media = false;
        } else {
            // 2b) Sinon → on insère
            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $content,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => $cpt,
                'post_modified' => $modified_date,
                'post_modified_gmt' => $modified_date_gmt,
                'post_date'    => $post_date,
                'post_date_gmt' => $post_date_gmt,
            ], true );
        }
    
        // Si wp_error ou pas d'ID, skip
        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }
    
        // 3) On met à jour les ACF / meta
        if ( ! empty( $item['meta'] ) && is_array( $item['meta'] ) ) {
            foreach ( $item['meta'] as $key => $value ) {
                update_field( $key, $value, $post_id );
            }
        }
    
        // 4) Gérer les taxonomies et leurs termes
        if ( ! empty( $item['taxonomies'] ) && is_array( $item['taxonomies'] ) ) {
            foreach ( $item['taxonomies'] as $taxonomy => $terms ) {
                if ( ! taxonomy_exists( $taxonomy ) ) {
                    continue;
                }

                $term_ids = [];
                foreach ( $terms as $term_name ) {
                    // 1) Vérifier si le terme existe
                    $term = term_exists( $term_name, $taxonomy );
                    if ( $term === 0 || $term === null ) {
                        // 2) Créer le terme s’il n’existe pas
                        $new = wp_insert_term( $term_name, $taxonomy );
                        if ( ! is_wp_error( $new ) && isset( $new['term_id'] ) ) {
                            $term_ids[] = (int) $new['term_id'];
                        }
                    } else {
                        // exists: term_exists peut retourner un array ou un ID
                        $term_ids[] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
                    }
                }

                // 3) Assigner tous les term_ids au post (remplace ceux existants)
                if ( ! empty( $term_ids ) ) {
                    wp_set_post_terms( $post_id, $term_ids, $taxonomy, false );
                }
            }
        }
    
        // 5) Image à la une
        if($upload_media) {
            if ( ! empty( $item['featured_image'] ) && filter_var( $item['featured_image'], FILTER_VALIDATE_URL ) ) {
                nei_import_featured_image( $item['featured_image'], $post_id );
            }
        }

    }

    echo '<div class="notice notice-success"><p>Importation terminée avec succès.</p></div>';
        // Facultatif : redirection vers la page d’import après chargement
        wp_safe_redirect( admin_url('admin.php?page=nei-importer') );
        exit;
}
