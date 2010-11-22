<?php
/*
Plugin Name: Activity Log
Plugin URI: n/a
Description: Provides a template tag for adding to a log
Version: 1
Author: Jonathan Lister
Author URI: http://jaybyjayfresh.com
*/
global $activity_log_db_version;
$activity_log_db_version = "1.0";

function activity_log_install() {
	global $wpdb;
	global $activity_log_db_version;
	$table_name = $wpdb->prefix . "activity_log";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time bigint(11) DEFAULT '0' NOT NULL,
			type tinytext NOT NULL,
			entry text NOT NULL,
			UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql); 
		add_option("activity_log_db_version", $activity_log_db_version);
	}
	$rows_affected = activity_log(array(
		'type'=>'admin',
		'entry'=>'Activity Log plugin activated'
	));
}

function activity_log($opts=array()) {
	global $wpdb;
	$table_name = $wpdb->prefix . "activity_log";
	$type = $opts['type'];
	$entry = $opts['entry'];
	if(!$type) {
		$type = "misc";
	}
	if(!$entry) { // JRL: maybe we could store all the request headers if no body provided
		$entry = "no info provided";
	}
	$rows_affected = $wpdb->insert( $table_name, array(
		'time' => time(),
		'type' => $type,
		'entry' => $entry
	) );
	return $rows_affected;
}

function activity_log_menu() {
	add_menu_page('Activity Log', 'Activity Log', 'manage_options', 'activity-log-admin', 'activity_log_menu_handler');
}

function activity_log_menu_handler() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	global $wpdb;
	$table_name = $wpdb->prefix . "activity_log";
	$log_entries = $wpdb->get_results( "SELECT time, type, entry FROM ".$table_name." ORDER BY time DESC");
?>
	<div class="wrap">
		<h1>Activity Log</h1>
		<table>
			<thead>
				<tr>
		  		<th>Time</th>
		  		<th>Type</th>
		  		<th>Entry</th>
				</tr>	  		
			</thead>
			<tbody>
			<?php foreach ($log_entries as $entry) {
				$time = $entry->time;
				$timeLabel = date('Y-m-d H:i:s', $time);
			?>
				<tr>
					<td><?php echo $timeLabel; ?></td>
					<td><?php echo $entry->type; ?></td>
					<td><?php echo $entry->entry; ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
<?php
}

register_activation_hook(__FILE__,'activity_log_install');
add_action('admin_menu', 'activity_log_menu');

?>