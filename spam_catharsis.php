<?php
/*
Plugin Name: Spam Catharsis
Plugin URI: http://thecodecave.com/plugins/spam-catharsis/
Description: Delete comments marked as spam after 15 days. This plugin is meant as a supplement to tools like Intense Debate and Spam Destroyer that mark items as spam but don't clear your spam box later.
Version: 1.0.1
Author: Brian Layman
Author URI: http://eHermitsInc.com
License: GPLv2
Requires: 2.3

Copyright 2014  Brian Layman  (email : plugins@thecodecave.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


function reg_spam_catharsis() {
	if ( !wp_next_scheduled( 'spam_catharsis' ) )
		wp_schedule_event( time(), 'daily', 'spam_catharsis');
}

function apply_catharsis() {
	// This routine is pretty much lifted straight from Akismet. No need to reinvent the wheel.
	global $wpdb;
	$now_gmt = current_time( 'mysql', 1 );
	$comment_ids = $wpdb->get_col( "SELECT comment_id FROM $wpdb->comments WHERE DATE_SUB('$now_gmt', INTERVAL 15 DAY) > comment_date_gmt AND comment_approved = 'spam'" );
	if ( empty( $comment_ids ) )
		return;
	$comma_comment_ids = implode( ', ', array_map('intval', $comment_ids) );

	do_action( 'delete_comment', $comment_ids );
	$wpdb->query( "DELETE FROM $wpdb->comments WHERE comment_id IN ( $comma_comment_ids )" );
	$wpdb->query( "DELETE FROM $wpdb->commentmeta WHERE comment_id IN ( $comma_comment_ids )" );
	clean_comment_cache( $comment_ids );
}

register_activation_hook( __FILE__, 'reg_spam_catharsis' );

add_action( 'spam_catharsis', 'apply_catharsis' );