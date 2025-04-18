<?php
/*
Plugin Name: Nova Export/Import
Description: Exportateur de CPT avec champs ACF sélectionnables
Version: 0.5.4
Author: Asterisk
GitHub Plugin URI: https://github.com/fredasterisk/nova-export-import
GitHub Branch: main
*/
if (!defined('ABSPATH')) exit;
// Inclure les fonctions
require_once plugin_dir_path(__FILE__) . 'includes/exporter.php';
require_once plugin_dir_path(__FILE__) . 'includes/importer.php';

require_once plugin_dir_path(__FILE__) . 'includes/taxonomies.php';
require_once plugin_dir_path(__FILE__) . 'includes/media.php';
require_once plugin_dir_path(__FILE__) . 'admin/export-page.php';
require_once plugin_dir_path(__FILE__) . 'admin/import-page.php';
