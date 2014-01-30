<?php

/*
Plugin Name: Spreadsheet + ACF Import
Plugin URI: https://github.com/thechurch/wp-spreadsheet-acf-import
Description: Import data from a spreadsheet file with advanced custom fields.
Version: 0.1.7
Author: The Church
Author URI: http://thechurch.co.nz/
Plugin URI: https://github.com/thechurch/wp-spreadsheet-acf-import
License: MIT
*/

// The MIT License
// 
// Copyright (c) 2013 +THECHURCH+
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

// noncekey
if (!defined('CSVAFNONCEKEY')) {
  define('CSVAFNONCEKEY', '_csvafnonce');
}

// Allowed file extensions.
$CSVAFALLOWEDEXT = array(
  'xlsx',  'xlsm',  'xls'
, 'ods',   'slk',   'csv'
);

define('CSVAFPLUGINPATH', plugin_dir_path(__FILE__));
define('CSVAFURL', plugin_dir_url(__FILE__));

require_once CSVAFPLUGINPATH . '/inc/controller.class.php';

CsvafController::$ALLOWEDEXT = $CSVAFALLOWEDEXT;

// Add the admin menu hook
add_action('admin_menu', array('CsvafController', 'Adminmenu'));
