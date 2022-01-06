<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { die(); };
		 global $wpdb;
		 $wp_track_table = $wpdb->prefix . 'todo_list_items';
		 $wpdb->query( "DROP TABLE IF EXISTS {$wp_track_table}" );
		 $wp_track_table = $wpdb->prefix . 'todo_list_items_status';
		 $wpdb->query( "DROP TABLE IF EXISTS {$wp_track_table}" );
?>