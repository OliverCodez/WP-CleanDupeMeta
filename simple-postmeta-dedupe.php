<?php
/**
 * Simple PostMeta deDupe
 *
 * @package     Simple PostMeta deDupe
 * @author      Oliver Westbrook
 * @copyright   2021 Oliver Westbrook
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: Simple PostMeta deDupe
 * Plugin URI:  https://github.com/joliverwestbrook
 * Description: Simple WP database deduper for postmeta
 * Author:      Oliver Westbrook
 * Author URI:  https://github.com/joliverwestbrook
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wp-postmeta-dedupe
 * Domain Path: /lang
 * Version:     0.1.0-beta
 */
$spmdd_settings = array(
  'posttypes' => array( // Change these to the post-type(s) you want to include in the dedupe procedure
    'my_custom_posttype',
    'posts',
  ),
  'status' => 'publish', // What post status to process - complete list of default statuses avail: https://wordpress.org/support/article/post-status/
  'meta_key_list' => array( // Change these values to the names of the postmeta items (their meta_key value) you are looking to dedupe
    'key_name',
    'another_keyName',
    '_some_key',
  ),
);
if ( is_admin() && isset( $_GET['cleanDB'] ) ) {
  foreach ( $spmdd_settings['posttypes'] as $post_type ) {
    $_allposts = get_posts( 'numberposts=-1&post_type=' . $post_type . '&post_status=' . $spmdd_settings['status'] );
    foreach( $_allposts as $_postinfo ) {
      $_postID = $_postinfo->ID;
      foreach ( $spmdd_settings['meta_key_list'] as $val_meta ) {
		  global $wpdb;
		  $meta = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '$val_meta' AND post_id = '$_postID'", ARRAY_A );
          $meta_id = $meta[0]['meta_id'];
		  $_dupes = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '$val_meta' AND post_id = '$_postID' AND meta_id <> '$meta_id'", ARRAY_A );
		  foreach ( $_dupes as $_dupe ) {
			  $dupe_id = $_dupe['meta_id'];
			  delete_metadata_by_mid( 'post', $dupe_id );
		  }
      }
    }
  }
}
