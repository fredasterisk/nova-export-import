<?php
if (!defined('ABSPATH')) exit;

/**
 * Export all taxonomies and their terms linked to a CPT
 */
function nei_export_taxonomies_and_terms($cpt) {
    $taxonomies_data = [];
    $taxonomies = get_object_taxonomies($cpt, 'objects');

    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
        ]);

        $taxonomies_data[] = [
            'taxonomy' => $taxonomy->name,
            'label' => $taxonomy->label,
            'hierarchical' => $taxonomy->hierarchical,
            'public' => $taxonomy->public,
            'show_ui' => $taxonomy->show_ui,
            'terms' => array_map(function ($term) {
                return [
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description,
                    'parent' => $term->parent,
                ];
            }, $terms)
        ];
    }

    return $taxonomies_data;
}

/**
 * Import taxonomy definitions and terms
 */
function nei_import_taxonomies_and_terms($data) {
    foreach ($data as $taxonomy) {
        // Register taxonomy if it doesn't exist yet
        if (!taxonomy_exists($taxonomy['taxonomy'])) {
            register_taxonomy($taxonomy['taxonomy'], [], [
                'label' => $taxonomy['label'],
                'hierarchical' => $taxonomy['hierarchical'],
                'public' => $taxonomy['public'],
                'show_ui' => $taxonomy['show_ui'],
            ]);
        }

        // Create terms
        foreach ($taxonomy['terms'] as $term) {
            if (!term_exists($term['slug'], $taxonomy['taxonomy'])) {
                wp_insert_term($term['name'], $taxonomy['taxonomy'], [
                    'slug' => $term['slug'],
                    'description' => $term['description'],
                    'parent' => $term['parent'] // assumes same ID or will fallback to 0
                ]);
            }
        }
    }
}
