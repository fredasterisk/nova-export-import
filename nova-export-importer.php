<?php
/*
Plugin Name: Nova Export/Import
Description: Exportateur de CPT avec champs ACF sélectionnables
Version: 0.1
Author: Asterisk
*/
if (!defined('ABSPATH')) exit;
// Inclure les fonctions
require_once plugin_dir_path(__FILE__) . 'includes/exporter.php';
require_once plugin_dir_path(__FILE__) . 'admin/export-page.php';

require_once plugin_dir_path(__FILE__) . 'admin/import-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/importer.php';