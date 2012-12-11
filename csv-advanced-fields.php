<?php

/*
  Plugin Name: CSV Advanced Fields Import
  Plugin URI: http://github.com/thechurch/wp-csv-advanced-fields
  Description: Import data from a CSV file with advanced fields.
  Version: 0.1
  Author: +THECHURCH+
  Author URI: http://thechurch.co.nz/
  License: MIT
*/

// The MIT License
// 
// Copyright (c) 2012 +THECHURCH+
// 
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files
// (the "Software"), to deal in the Software without restriction,
// including without limitation the rights to use, copy, modify, merge,
// publish, distribute, sublicense, and/or sell copies of the Software, and
// to permit persons to whom the Software is furnished to do so, subject to
// the following conditions:
// 
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
// IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
// CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
// TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
// SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

if (!defined('CSVAFPLUGINPATH')) {
  define('CSVAFPLUGINPATH', plugin_dir_path(__FILE__));
}

require_once CSVAFPLUGINPATH . '/inc/controller.class.php';

/**
 * Add csvaf options to the admin menu.
 * 
 * @access public
 * @return void
 */
function csvaf_addadminmenu () {
  CsvafController::Adminmenu();
}

/**
 * Add the upload form etc.
 * 
 * @access public
 * @return void
 */
function csvaf_menupage () {
  if (!current_user_can('manage_options')) {
    return wp_die(__('You do not have sufficient permissions to acces this page.'));
  }

  $out = CsvafView::Uploadform();

  echo '<div class="wrap"><p>' . $out . '</p></div>';
}

add_action('admin_menu', 'csvaf_addadminmenu');