<?php
/*
Plugin Name: Nova Export/Import
Description: Exportateur de CPT avec champs ACF sélectionnables
Version: 0.7.2
Author: Asterisk
GitHub Plugin URI: https://github.com/fredasterisk/nova-export-import
GitHub Branch: main
*/
if (!defined('ABSPATH')) exit;

// Hook vers admin-post.php pour l'export
add_action( 'admin_post_nei_export',      'nei_cpt_exporter_handle_export' );
add_action( 'admin_post_nopriv_nei_export','nei_cpt_exporter_handle_export' );
// Hook vers admin-post.php pour l'import
add_action( 'admin_post_nei_import',       'nei_handle_import_upload' );
add_action( 'admin_post_nopriv_nei_import','nei_handle_import_upload' );

// Inclure les fonctions
require_once plugin_dir_path(__FILE__) . 'includes/exporter.php';
require_once plugin_dir_path(__FILE__) . 'includes/importer.php';

require_once plugin_dir_path(__FILE__) . 'includes/taxonomies.php';
require_once plugin_dir_path(__FILE__) . 'includes/media.php';
require_once plugin_dir_path(__FILE__) . 'admin/export-page.php';
require_once plugin_dir_path(__FILE__) . 'admin/import-page.php';
