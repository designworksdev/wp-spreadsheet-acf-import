=== Spreadsheet + ACF Import ===
Contributors: timsmart
Plugin URI: https://github.com/thechurch/wp-spreadsheet-acf-import
Author URI: http://thechurch.co.nz/
Tags: import, spreadsheet, acf, csv
Requires at least: 3.0.0
Tested up to: 3.8.1
Stable tag: 0.1.7
License: MIT

Import data from spreadsheets into posts with Advanced Custom Fields.

== Description ==

To make contributions visit: https://github.com/thechurch/wp-spreadsheet-acf-import

To use, activate the plugin and click the "Import Spreadsheet" option in the
Wordpress admin toolbar / sidebar.

Spreadsheets should have the top row reserved for header names, with everything
after being the actual data.

Select the file you want to upload, the post type to create, then click Upload.
The next step you choose which columns to map to the fields. Depending on the
field type different options will appear. Dates will have a format in and format
out text entry which accepts PHP date() format strings. The standard format out
for ACF date pickers is automatically inserted for you. Post objects and
relationships will do a reverse lookup in the post titles. This may have more
flexibility in the future.

Click Upload and the posts will be created. A log is then generated if errors /
warnings are produced.

== Changelog ==

= 0.1.7 =
* Bump version for deployment

= 0.1.6 =
* Trigger acf_save_post on insert

= 0.1.5 =
* Ensure plugin works without valid acf version

= 0.1.3 =
* Update readme.txt

= 0.1.2 =
* Add usage text
* Move to tools menu

= 0.1.1 =
* Readme update

= 0.1.0 =
* Initial version
