<?php

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

/**
 * Database model for the CSV Advanced Fields plugin.
 * 
 * @package Csvaf
 * @version 0.1
 */
class CsvafModel {
  /**
   * The default wordpress fields.
   *
   * @todo    Translatable / use __() somehow.
   * @static
   * @access  public
   * @var     array
   */
  public static $WPFIELDS = array(
    'post_content'   => 'Content'
  , 'post_excerpt'   => 'Excerpt'
  , 'post_name'      => 'Post Name'
  , 'post_password'  => 'Password'
  , 'post_title'     => 'Title'
  , 'post_status'    => 'Post Status'
  );

  /**
   * @static
   * @access  public
   * @var     array
   */
  public static $ACFFIELDS = array(
    'text',   'textarea', 'editor'
  , 'select', 'post_object'
  , 'date_picker', 'relationship'
  , 'time_picker', 'true_false'
  );


  /**
   * Variable cache
   *
   * @static
   * @access  public
   * @var     array
   */
  public static $CACHE = array();

  /**
   * Get a cached value
   *
   * @static
   * @access  public
   * @param   string  $key
   * @return  mixed
   */
  public static function Getcached ($key) {
    if (isset(self::$CACHE[$key])) return self::$CACHE[$key];
    return null;
  }

  /**
   * Set a cached value
   *
   * @static
   * @access  public
   * @param   string  $key
   * @param   mixed   $data
   * @return  mixed
   */
  public static function Setcached ($key, $data) {
    self::$CACHE[$key] = $data;
    return $data;
  }

  /**
   * Get the wordpress timezone
   *
   * @static
   * @access  public
   * @return  DateTimeZone
   */
  public static function Gettimezone () {
    $timezone = self::Getcached('timezone');
    if ($timezone) return $timezone;

    $offset   = get_option('gmt_offset');
    $timezone = timezone_name_from_abbr(null, $offset * 3600, true);

    if ($timezone === false) {
      $timezone = timezone_name_from_abbr(null, $offset * 3600, false);
    }

    $timezone = new DateTimeZone($timezone);
    return self::Setcached('timezone', $timezone);
  }

  /**
   * Get the available post types.
   * 
   * @static
   * @access  public
   * @return  array  The post types
   */
  public static function Getposttypes () {
    $ret = self::Getcached('posttypes');
    if ($ret) return $ret;

    return self::Setcached('posttypes', get_post_types());
  }

  /**
   * Get fields for post type.
   *
   * If the advanced custom field plugin is installed then also include these
   * fields.
   * 
   * @static
   * @access  public
   * @param   string  $posttype 
   * @return  array   The fields.
   */
  public static function Getfieldsfortype ($posttype) {
    global $acf;
    $fields   = array();

    // Normal fields
    foreach (self::$WPFIELDS as $key => $name) {
      $field = array(
        'advanced'  => false
      , 'id'        => $key
      , 'name'      => $name
      , 'key'       => $key
      , 'type'      => null
      , 'formatin'  => null
      , 'formatout' => null
      , 'default'   => null
      );

      $fields[] = $field;
    }

    // Advanced custom fields
    if ($acf && method_exists($acf, 'get_field_groups')) {
      $fieldgroups  = $acf->get_field_groups();

      foreach ($fieldgroups as $fieldgroup) {
        $rules  = $fieldgroup['location']['rules'];
        $passes = false;

        foreach ($rules as $rule) {
          if (
             'post_type' == $rule['param']
          && '==' == $rule['operator']
          && $posttype == $rule['value']
          ) {
            $passes = true;
            break;
          }
        }

        if ($passes) {
          foreach ($fieldgroup['fields'] as $field) {
            if (!in_array($field['type'], self::$ACFFIELDS)) continue;

            $type      = null;
            $formatin  = null;
            $formatout = null;

            switch ($field['type']) {
              case 'post_object':
              case 'relationship':
                $type = 'lookup';
                break;

              case 'date_picker':
                $type      = 'format';
                $formatin  = 'm/d/y';
                $formatout = 'Ymd';
                break;

              case 'time_picker':
                $type   = 'format';
                $format = '';
                if ($field['timepicker_show_date_format']) {
                  $format .= $field['timepicker_date_format'] . ' ';
                }
                $format .= $field['timepicker_time_format'];
                break;

              case 'true_false':
                $type   = 'boolean';
                break;
            }

            $fields[] = array(
              'advanced'  => true
            , 'id'        => $field['key']
            , 'name'      => $field['label']
            , 'key'       => $field['name']
            , 'type'      => $type
            , 'formatin'  => $formatin
            , 'formatout' => $formatout
            , 'default'   => isset($field['default_value'])
                ? $field['default_value']
                : null
            );
          }
        }
      }
    }

    return $fields;
  }

  /**
   * Create a query from POST values
   *
   * @static
   * @access  public
   * @param   string  $posttype   The type of post to create
   * @param   array   $fields     The post type fields
   * @param   array   $docarray   Data source
   * @return  array   The query
   */
  public static function Createinsertquery ($posttype, $fields, $docarray) {
    $headers       = array_shift($docarray);
    $columns       = array_keys($headers);
    $fieldmap      = array();
    $needunique    = false;

    $out           = array();
    $badout        = array();

    $columnparsers = array();

    foreach ($fields as $field) {
      $fieldmap[$field['key']] = $field;
    }

    foreach ($columns as $column) {
      $fieldkey  = "csvaf_column_{$column}_field";
      if (!is_string($_POST[$fieldkey]) || !$fieldmap[$_POST[$fieldkey]]) continue;
      $fieldkey  = $_POST[$fieldkey];

      $field     = $fieldmap[$fieldkey];
      $type      = $field['type'];

      $default   = $_POST["csvaf_column_{$column}_default"];
      $default   = $default ? $default : null;
      $unique    = 'on' === $_POST["csvaf_column_{$column}_unique"];
      $lookup    = null;
      $formatin  = null;
      $formatout = null;

      switch ($type) {
        case 'lookup':
          $lookup = "csvaf_column_{$column}_type";
          if (!is_string($_POST[$lookup])) continue;
          $lookup = $_POST[$lookup];
          break;

        case 'format':
          $formatin = "csvaf_column_{$column}_formatin";
          if (!is_string($_POST[$formatin])) continue;
          $formatin = $_POST[$formatin];

          $formatout = "csvaf_column_{$column}_formatout";
          if (!is_string($_POST[$formatout])) continue;
          $formatout = $_POST[$formatout];
          break;
      }

      if ($unique) $needunique = true;

      $columnparsers[$column] = array(
        'field'     => $field
      , 'lookup'    => $lookup
      , 'formatin'  => $formatin
      , 'formatout' => $formatout
      , 'unique'    => $unique
      , 'default'   => $default
      , 'type'      => $type
      );
    }

    foreach ($docarray as $rownumber => $row) {
      $realrow = $rownumber + 2;

      $toinsert = array(
        'wp'      => array()
      , 'acf'     => array()
      , 'row'     => $rownumber
      , 'realrow' => $realrow
      );

      $badfields = array();

      foreach ($row as $column => $value) {
        if (!isset($columnparsers[$column])) continue;
        $info  = $columnparsers[$column];

        $toset = null;

        switch ($info['type']) {
          case 'lookup':
            $value = trim($value);
            $toset = get_page_by_title($value, null, $info['lookup']);
            $toset = $toset ? $toset->ID : null;
            break;

          case 'format':
            $toset = DateTime::createFromFormat(
              $info['formatin']
            , $value, self::Gettimezone()
            );
            $toset = $toset ? $toset->format($info['formatout']) : null;
            break;

          case 'boolean':
            $value = strtolower($value);

            switch ($value) {
            case 'yes':
            case 'y':
            case '1':
            case 'true':
              $toset = '1';
              break;

            default:
              $toset = '0';
              break;
            }
            break;

          default:
            $toset = $value;
            break;
        }

        if (!$toset && $info['default']) {
          $toset = $info['default'];
        }
        if (($info['type'] && !$toset) || ($info['unique'] && !$toset)) {
          $badfields[] = $info['field'];
          continue;
        }
        if (!$toset) $toset = '';

        if ($info['field']['advanced']) {
          $toinsert['acf'][$info['field']['id']] = $toset;
        } else {
          $toinsert['wp'][$info['field']['key']] = $toset;
        }

        if ($info['unique']) {
          $toinsert['unique'][] = $info['field'];
        }
      }

      if (count($badfields)) {
        $badout[$realrow] = $badfields;
      } else {
        $out[] = $toinsert;
      }
    }

    return array($out, $badout, $needunique);
  }

  /**
   * Check if posts are unique
   *
   * @static
   * @access  public
   * @param   string    $posttype   The post type to check for
   * @param   array     $inserts    The data to check
   * @return  array
   */
  public static function Checkuniques ($posttype, $inserts) {
    $notunique = array();
    $out       = array();

    foreach ($inserts as $insert) {
      $isunique = self::Checkunique($posttype, $insert);

      if (!$isunique) $notunique[] = $insert;
      else            $out[]       = $insert;
    }

    return array($out, $notunique);
  }

  /**
   * Check if post is unique
   *
   * @static
   * @access  public
   * @param   string    $posttype   The post type to check for
   * @param   array     $insert     The data to check
   * @return  boolean
   */
  public static function Checkunique ($posttype, $insert) {
    global $wpdb;

    $query = "SELECT
  `posts$posttype`.`ID`
FROM $wpdb->posts AS `posts{$posttype}`";

    $innerjoins = array();
    $wheres     = array("`posts$posttype`.`post_type` = '$posttype'");

    foreach ($insert['unique'] as $field) {
      if (!$field['advanced']) {
        $wheres[] = $wpdb->prepare(
          "`posts$posttype`.`{$field['key']}` = %s"
        , $insert['wp'][$field['key']]
        );
        continue;
      }

      switch ($field['type']) {
      case 'lookup':
        $valuesub = '%d';
        break;
      default:
        $valuesub = '%s';
        break;
      }

      $columnname = "field{$field['key']}";

      $innerjoins[] = $wpdb->prepare(
        "INNER JOIN $wpdb->postmeta AS `$columnname`
ON ( `posts$posttype`.`ID` = `$columnname`.`post_id`
 AND `$columnname`.`meta_key` = '{$field['key']}'
 AND `$columnname`.`meta_value` = $valuesub
   )"
        , $insert['acf'][$field['id']]
      );
    }

    $innerjoins = "\n" . implode("\n", $innerjoins);
    $wheres     = implode("\nAND", $wheres);
    $query      = $query . $innerjoins . "\nWHERE\n" . $wheres . "\nLIMIT 0, 1";

    $results = $wpdb->query($query);

    if ($results) return false;
    return true;
  }

  /**
   * Insert some posts into the database (with acf's)
   *
   * @static
   * @access  public
   * @param   string    $posttype   The post type to check for
   * @param   array     $toinsert   The data to check
   * @return  void
   */
  public static function Insertposts ($posttype, $toinsert) {
    foreach ($toinsert as $post) {
      self::Insertpost($posttype, $post);
    }
  }

  /**
   * Insert a post into the database (with acf's)
   *
   * @static
   * @access  public
   * @param   string    $posttype   The post type to check for
   * @param   array     $toinsert   The data to check
   * @return  void
   */
  public static function Insertpost ($posttype, $toinsert) {
    $thepost              = $toinsert['wp'];
    $thepost['post_type'] = $posttype;

    if (!isset($thepost['post_title'])) {
      $thepost['post_title'] = '';
    }
    if (!isset($thepost['post_content'])) {
      $thepost['post_content'] = '';
    }
    if (!isset($thepost['post_status'])) {
      $thepost['post_status'] = 'publish';
    }

    $postid = wp_insert_post($thepost);

    if (!$postid) return;

    foreach ($toinsert['acf'] as $key => $value) {
      update_field($key, $value, $postid);
      // update_post_meta($postid, $key, $value);
    }

    do_action('acf_save_post', $postid);
  }
}
