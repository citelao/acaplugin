<?php
/*
 Plugin Name: ACAC Featureset
 Plugin URI: TODO (github)
 Description: This internal plugin adds all the content features necessary to run the ACAC site.
 Version: 0.0
 Author: Ben Stolovitz
 Author URI: http://ben.stolovitz.com
 License: Proprietary
 */
namespace Acaplugin;
// require_once('src/types.php');
require __DIR__ . '/vendor/autoload.php';

/**
 * Register types
 */
new Types\Auditionees();
new Types\Groups();
new Types\Songs();

// add_action('cmb2_admin_init', 'Acaplugin\cmb2_admin_init');
// function cmb2_admin_init() {
// 	Types::metaboxes();
// }