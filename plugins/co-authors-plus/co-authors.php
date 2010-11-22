<?php
/*
Plugin Name: Co-Authors Plus
Plugin URI: http://wordpress.org/extend/plugins/co-authors-plus/
Description: Allows multiple authors to be assigned to a post. Co-authored posts appear on a co-author's posts page and feed. New template tags allow listing of co-authors. Editors may assign co-authors to a post via the 'Post Author' box. <em>This plugin is an extended version of the Co-Authors plugin originally developed at [Shepherd Interactive](http://www.shepherd-interactive.com/ "Shepherd Interactive specializes in web design and development in Portland, Oregon") (2007). Their plugin was inspired by 'Multiple Authors' plugin by Mark Jaquith (2005).</em>
Version: 2.1.1
Author: Mohammad Jangda
Author URI: http://digitalize.ca
Copyright: Some parts (C) 2009, Mohammad Jangda; Other parts (C) 2008, Weston Ruter, Shepherd Interactive

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'co-authors-plus','wp-content/plugins/'.$plugin_dir, $plugin_dir);


define('COAUTHORS_FILE_PATH', '');
define('COAUTHORS_DEFAULT_BEFORE', '');
define('COAUTHORS_DEFAULT_BETWEEN', ', ');
define('COAUTHORS_DEFAULT_BETWEEN_LAST', __(' and ', 'co-authors-plus'));
define('COAUTHORS_DEFAULT_AFTER', '');
define('COAUTHORS_PLUS_VERSION', '2.1');

require_once('template-tags.php');

class coauthors_plus {
	
	// Name for the taxonomy we're using to store coauthors
	var $coauthor_taxonomy = 'author';
	// Unique identified added as a prefix to all options
	var $options_group = 'coauthors_plus_';
	// Initially stores default option values, but when load_options is run, it is populated with the options stored in the WP db
	var $options = array(
					'allow_subscribers_as_authors' => 0,
				);
	
	function __construct() {
		global $pagenow;

		// Load plugin options
		$this->load_options();

		// Register new taxonomy so that we can store all our authors
		if(!is_taxonomy($this->coauthor_taxonomy)) register_taxonomy( $this->coauthor_taxonomy, 'post', array('hierarchical' => false, 'update_count_callback' => '_update_post_term_count', 'label' => false, 'query_var' => false, 'rewrite' => false, 'sort' => true ) );

		// Modify SQL queries to include coauthors
		add_filter('posts_where', array(&$this, 'posts_where_filter'));
		add_filter('posts_join', array(&$this, 'posts_join_filter'));
		add_filter('posts_groupby', array(&$this, 'posts_groupby_filter'));
		
		// Hooks to add additional coauthors to author column to Edit Posts page
		if($pagenow == 'edit.php') {
			add_filter('manage_posts_columns', array(&$this, '_filter_manage_posts_columns'));
			add_action('manage_posts_custom_column', array(&$this, '_filter_manage_posts_custom_column'));
		}
		
		// Action to set users when a post is saved
		//add_action('edit_post', array(&$this, 'coauthors_update_post'));
		add_action('save_post', array(&$this, 'coauthors_update_post'));
		
		// Action to reassign posts when a user is deleted
		add_action('delete_user',  array(&$this, 'delete_user_action'));

		// Action to set up author auto-suggest
		add_action('wp_ajax_coauthors_ajax_suggest', array(&$this, 'ajax_suggest') );
	
		// Filter to allow coauthors to edit posts
		add_filter('user_has_cap', array(&$this, 'add_coauthor_cap'), 10, 3 );
	
		add_filter('comment_notification_headers', array(&$this, 'notify_coauthors'), 10, 3);
	
		// Add the main JS script and CSS file
		if(get_bloginfo('version') >= 2.8) {
			// Using WordPress 2.8, are we?
			add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		} else {
			// Pfft, you're old school
			add_action('admin_print_scripts', array(&$this, 'enqueue_scripts_legacy'));			
		}
		// Add necessary JS variables
		add_action('admin_print_scripts', array(&$this, 'js_vars') );
		
	}
	
	function init() {
		//Add the necessary pages for the plugin 
		add_action('admin_menu', array(&$this, 'add_menu_items'));
	}
	
	/**
	 * Initialize the plugin for the admin 
	 */
	function admin_init() {
		// Register all plugin settings so that we can change them and such
		foreach($this->options as $option => $value) {
	    	register_setting($this->options_group, $this->get_plugin_option_fullname($option));
	    }
	}
	/**
	 * Function to trigger actions when plugin is activated
	 */
	function activate_plugin() {}
	
	/**
	 * Adds menu items for the plugin
	 */
	function add_menu_items ( ) {
		// Add sub-menu page for Custom statuses		
		add_options_page(__('Co-Authors Plus', 'co-authors-plus'), __('Co-Authors Plus', 'co-authors-plus'), 8, __FILE__, array(&$this, 'settings_page'));
	}
	
	/**
	 * Add coauthors to author column on edit pages 
	 * 
	 * @param array $post_columns
	 **/
	function _filter_manage_posts_columns($posts_columns) {
		$new_columns = array();
		foreach ($posts_columns as $key => $value) {
			$new_columns[$key] = $value;
			if ($key == 'author') {
				unset($new_columns[$key]);
				$new_columns['coauthors'] = __('Authors', 'co-authors-plus');
			}
		}
		return $new_columns;
	} // END: _filter_manage_posts_columns
	
	/**
	 * Insert coauthors into post rows on Edit Page
	 * 
	 * @param string $column_name
	 **/
	function _filter_manage_posts_custom_column($column_name) {
		if ($column_name == 'coauthors') {
			global $post;
			$authors = get_coauthors($post->ID);
			
			$count = 1;
			foreach($authors as $author) :
				?>
				<a href="edit.php?author=<?php echo $author->ID; ?>"><?php echo $author->display_name ?></a><?php echo ($count < count($authors)) ? ',' : ''; ?>
				<?php
				$count++;
			endforeach;
		}
	}
	
	/* Modify the author query posts SQL to include posts co-authored
	 * 
	 */
	function posts_join_filter($join){
		global $wpdb,$wp_query;
				
		if(is_author()){
			$join .= " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";		
		}
		return $join;
	}
	/* Modify 
	 *
	 */
	function posts_where_filter($where){
		global $wpdb, $wp_query;
		
		if(is_author()) {
			$author = get_userdata($wp_query->query_vars['author']);//get_profile( 'user_login', $wp_query->query_vars['author']);
			$term = get_term_by('name', $author->user_login, $this->coauthor_taxonomy);
				
			if($author) {
				$where = preg_replace('/(\b(?:' . $wpdb->posts . '\.)?post_author\s*=\s*(\d+))/', '($1 OR (' . $wpdb->term_taxonomy . '.taxonomy = \''. $this->coauthor_taxonomy.'\' AND '. $wpdb->term_taxonomy .'.term_id = \''. $term->term_id .'\'))', $where, 1); #' . $wpdb->postmeta . '.meta_id IS NOT NULL AND 

			}
		}
		return $where;
	}
	/*
	 *
	 */
	function posts_groupby_filter($groupby){
		global $wpdb;
		
		if(is_author()) {
			$groupby = $wpdb->posts .'.ID';
		}
		return $groupby;
	}
	
	
	/* Update a post's co-authors
	 * @param $postID
	 * @return 
	 */
	function coauthors_update_post($post_ID){
		global $current_user;
		
		get_currentuserinfo();
		
		if($current_user->has_cap('edit_others_posts')){
			$coauthors = $_POST['coauthors'];
			return $this->add_coauthors($post_ID, $coauthors);
		}
	}
	
	/* Action taken when user is deleted.
	 * - User term is removed from all associated posts
	 * - Option to specify alternate user in place for each post
	 * @param delete_id
	 */
	function delete_user_action($delete_id){
		global $wpdb;
		
		$reassign_id = absint($_POST['reassign_user']);
		
		// If reassign posts, do that -- use coauthors_update_post
		if($reassign_id) {
			// Get posts belonging to deleted author
			$reassign_user = get_profile_by_id('user_login', $reassign_id);
			// Set to new author
			if($reassign_user) {
				$post_ids = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_author = %d", $delete_id) );

				if ($post_ids) {
					foreach ($post_ids as $post_id) {
						$this->add_coauthors($post_id, array($reassign_user), true);
					}
				}
			}
		}
		
		$delete_user = get_profile_by_id('user_login', $delete_id);
		if($delete_user) {
			// Delete term
			wp_delete_term($delete_user, $this->coauthor_taxonomy);
		}
	}
	
	/* Add a user as coauthor for a post
	 *
	 */
	function add_coauthors( $post_ID, $coauthors, $append = false ) {
		global $current_user;
		
		$post_ID = (int) $post_ID;
		
		// if an array isn't returned, create one and populate with default author
		if (!is_array($coauthors) || 0 == count($coauthors) || empty($coauthors)) {
			// @TOOD: create option for default author
			$coauthors = array(get_option('default_author'));
		}
		
		// Add each co-author to the post meta
		foreach(array_unique($coauthors) as $author){
			
			// Name and slug of term are the username; 
			$name = $author;
			
			// Add user as a term if they don't exist
			if(!is_term($name, $this->coauthor_taxonomy) ) {
				$args = array('slug' => sanitize_title($name) );		
				$insert = wp_insert_term( $name, $this->coauthor_taxonomy, $args );
			}
		}
		
		// Add authors as post terms
		if(!is_wp_error($insert)) {
			$set = wp_set_post_terms( $post_ID, $coauthors, $this->coauthor_taxonomy, $append );
		} else {
			// @TODO: error
		}

	}
	
	/* Main function that handles search-as-you-type
	 *
	 */
	function ajax_suggest() {
		global $wpdb, $current_user;
		
		// Make sure that user is logged in; we don't want to enable direct access
		get_currentuserinfo();
		global $user_level;
		
		if($current_user->has_cap('edit_others_posts')) {
			
			// Set the minimum level of users to return
			if(!$this->get_plugin_option('allow_subscribers_as_authors')) {
				$user_level_where = "WHERE meta_key = '".$wpdb->prefix."user_level' AND meta_value >= 1";
			}
			
			// @TODO validate return
			$q = '%'.strtolower($_REQUEST["q"]).'%';
			if (!$q) return;
	
			$authors_query = $wpdb->prepare("SELECT DISTINCT u.ID, u.user_login, u.display_name, u.user_email FROM $wpdb->users AS u"
											." INNER JOIN $wpdb->usermeta AS um ON u.ID = um.user_id"
											." WHERE ID = ANY (SELECT user_id FROM $wpdb->usermeta $user_level_where)"
											." AND (um.meta_key = 'first_name' OR um.meta_key = 'last_name' OR um.meta_key = 'nickname')"
											." AND (u.user_login LIKE %s"
												." OR u.user_nicename LIKE %s"
												." OR u.display_name LIKE %s"
												." OR u.user_email LIKE %s"
												." OR um.meta_value LIKE %s)",$q,$q,$q,$q,$q);
												
			//echo $authors_query;
			$authors = $wpdb->get_results($authors_query, ARRAY_A);
		
			if(is_array($authors)) {
				foreach ($authors as $author) {
					echo $author['ID'] ." | ". $author['user_login']." | ". $author['display_name'] ." | ".$author['user_email'] ."\n";
				}
			}
		}
		die();
			
	}
	
	/* Functions to add scripts and css
	 * enqueue_scripts is for 2.8+; enqueue_scripts_legacy for > 2.8
	 */
	function enqueue_scripts($hook_suffix) {
		global $pagenow;
		
		if($this->is_valid_page()) {
			wp_enqueue_style('co-authors-plus', plugins_url('co-authors-plus/admin.css'), false, '', 'all');
			wp_enqueue_script('co-authors-plus', plugins_url('co-authors-plus/admin.js'), array('jquery', 'suggest', 'sack'), '', true);
		}
		
		
		//wp_dropdown_users(array('name' => 'ef_author', 'include' => $current_user->ID ));
		
	}
	function enqueue_scripts_legacy($hook_suffix) {
		global $pagenow;
		
		if($this->is_valid_page()) {
			//wp_enqueue_style('co-authors-plus', plugins_url('co-authors-plus/admin.css'), false, '', 'all');
			wp_enqueue_script('co-authors-plus', plugins_url('co-authors-plus/admin.js'), array('jquery', 'suggest', 'sack'), '');
			?>
			<link type="text/css" rel="stylesheet" href="<?php echo plugins_url('co-authors-plus/admin.css') ?>" media="all" />
			<?php
		}
	}
	
	/* Adds necessary javascript variables to admin pages 
	 *
	 */
	function js_vars() {
		global $current_user, $post_ID;
		
		get_currentuserinfo();
		
		if($this->is_valid_page()) {
			//wp_print_scripts( array( 'sack' ));
			$coauthors = get_coauthors( $post_ID );
			?>
			<script type="text/javascript">
			
				// AJAX link used for the autosuggest
				var coauthor_ajax_suggest_link = "<?php echo 'admin-ajax.php?action=coauthors_ajax_suggest' ?>";
			
				if(!i18n || i18n == 'undefined') var i18n = {};
				i18n.coauthors = {};
				
				var coauthors_can_edit_others_posts = "<?php echo ($current_user->has_cap('edit_others_posts') ? 'true' : 'false')?>";
				
				i18n.coauthors.post_metabox_title = "<?php _e('Post Author(s)', 'co-authors-plus')?>";
				i18n.coauthors.page_metabox_title = "<?php _e('Page Author(s)', 'co-authors-plus')?>";
				i18n.coauthors.edit_label = "<?php _e('Edit', 'co-authors-plus')?>";
				i18n.coauthors.delete_label = "<?php _e('Delete', 'co-authors-plus')?>";
				i18n.coauthors.confirm_delete = "<?php _e('Are you sure you want to delete this author?', 'co-authors-plus')?>";
				i18n.coauthors.input_box_title = "<?php _e('Click to change this author', 'co-authors-plus')?>";
				i18n.coauthors.search_box_text = "<?php _e('Search for an author', 'co-authors-plus')?>";				
				i18n.coauthors.help_text = "<?php _e('Click on an author to change them. Click on <strong>Delete</strong> to remove them.', 'co-authors-plus')?>";
				
				<?php if(is_array($coauthors) && !(empty($coauthors))) : ?>
					var post_coauthors = [ 
					<?php 
						foreach($coauthors as $author) {
							echo "{";
							echo "'login': escape('". $author->user_login ."'),";
							echo "'name': escape('". $author->display_name ."'),";
							echo "'id': '". $author->ID  ."'";
							echo "},";
						}
					?> 
					];
				<?php else : ?>
					var post_coauthors = [
					<?php
						echo "{";
						echo "'login': '". $current_user->user_login ."',";
						echo "'name': '". $current_user->display_name ."',";						
						echo "'id': '". $current_user->ID  ."'";
						echo "},";
					?>
					];
				<?php endif; ?>
			</script>
			<?php
		}
	} // END: js_vars()
	
	/* Helper to only add javascript to necessary pages
	 * Avoid bloat on admin
	 */
	private function is_valid_page() {
		global $pagenow;
		
		$pages = array('edit.php', 'post.php', 'post-new.php', 'page.php', 'page-new.php');
		
		if(in_array($pagenow, $pages)) return true;
		
		return false;
	} 
	
	/* Allows coauthors to edit the post they're coauthors of
	 *
	 */
	function add_coauthor_cap( $allcaps, $caps, $args ) {
		
		if(in_array('edit_post', $args) || in_array('edit_others_posts', $args)) {
			// @TODO: Fix this disgusting hardcodedness. Ew.
			$user_id = $args[1];
			$post_id = $args[2];
			if(is_coauthor_for_post($user_id, $post_id)) {
				// @TODO check to see if can edit publish posts if post is published
				// @TODO check to see if can edit posts at all
				foreach($caps as $cap) {
					$allcaps[$cap] = 1;
				}
			}
		}
		return $allcaps;
	}
	
	/* Emails all coauthors when comment added instead of the main author
	 * 
	 */
	function notify_coauthors( $message_headers, $comment_id ) {
		//echo '<p>Pre:';
		//print_r($message_headers);
		$comment = get_comment($comment_id);
		$post = get_post($comment->comment_post_ID);
		$coauthors = get_coauthors($comment->comment_post_ID);
	
		$message_headers .= 'cc: ';
		$count = 0;
		foreach($coauthors as $author) {
			$count++;
			if($author->ID != $post->post_author){
				$message_headers .= $author->user_email;
				if($count < count($coauthors)) $message_headers .= ',';
			}
		}
		$message_headers .= "\n";
		return $message_headers;
		//echo '<p>Post:';
		//print_r($message_headers);
		
	}
	
	/**
	 * Loads options for the plugin.
	 * If option doesn't exist in database, it is added
	 *
	 * Note: default values are stored in the $this->options array
	 * Note: a prefix unique to the plugin is appended to all options. Prefix is stored in $this->options_group 
	 */
	protected function load_options ( ) {

		$new_options = array();
		
		foreach($this->options as $option => $value) {
			$name = $this->get_plugin_option_fullname($option);
			$return = get_option($name);
			if($return === false) {
				add_option($name, $value);
				$new_array[$option] = $value;
			} else {
				$new_array[$option] = $return;
			}
		}
		$this->options = $new_array;
		
	} // END: load_options

	
	/**
	 * Returns option for the plugin specified by $name, e.g. custom_stati_enabled
	 *
	 * Note: The plugin option prefix does not need to be included in $name 
	 * 
	 * @param string name of the option
	 * @return option|null if not found
	 *
	 */
	function get_plugin_option ( $name ) {
		if(is_array($this->options) && $option = $this->options[$name])
			return $option;
		else 
			return null;
	} // END: get_option
	
	// Utility function: appends the option prefix and returns the full name of the option as it is stored in the wp_options db
	protected function get_plugin_option_fullname ( $name ) {
		return $this->options_group . $name;
	}
	
	/* Adds Settings page for Edit Flow
	 *
	 */
	function settings_page( ) {
		global $wp_roles;
		
		?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br/></div>
				<h2><?php _e('Co-Authors Plus', 'co-authors-plus') ?></h2>
				
				<form method="post" action="options.php">
					<?php settings_fields($this->options_group); ?>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><strong><?php _e('Roles', 'co-authors-plus') ?></strong></th>
							<td>
								<p>
									<label for="allow_subscribers_as_authors">
										<input type="checkbox" name="<?php echo $this->get_plugin_option_fullname('allow_subscribers_as_authors') ?>" value="1" <?php echo ($this->get_plugin_option('allow_subscribers_as_authors')) ? 'checked="checked"' : ''; ?> id="allow_subscribers_as_authors" /> <?php _e('Allow subscribers as authors', 'co-authors-plus') ?>
									</label> <br />
									<span class="description"><?php _e('Enabling this option will allow you to add users with the subscriber role as authors for posts.', 'co-authors-plus') ?></span>
								</p>
							</td>
						</tr>
						
					</table>
									
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'co-authors-plus') ?>" />
					</p>
				</form>
			</div>
		<?php 
	}

	
	/* Function updates coauthors from old meta-based storage to taxonomy-based
	 *
	 */
	function update() { 
		// Get all posts with meta_key _coauthor
		$all_posts = get_posts(array('numberposts' => '-1'));
		
		//echo '<p>returned posts ('.count($all_posts).'):</p>';
		//print_r($posts);
		//echo '<hr />';
		
		if(is_array($all_posts)) {
			foreach($all_posts as $single_post) {
				
				// reset execution time limit
				set_time_limit( 60 );
				
				// create new array
				$coauthors = array();
				// get author id -- try to use get_profile
				$coauthors[] = get_profile_by_id('user_login', (int)$single_post->post_author);
				// get coauthors id
				$legacy_coauthors = get_post_meta($single_post->ID, '_coauthor'); 
				//print_r($legacy_coauthors);
				//echo '<hr />';
				if(is_array($legacy_coauthors)) {
					//echo '<p>Has Legacy coauthors';
					foreach($legacy_coauthors as $legacy_coauthor) {
						$legacy_coauthor_login = get_profile_by_id('user_login', (int)$legacy_coauthor);
						if($legacy_coauthor_login) $coauthors[] = $legacy_coauthor_login;
					}
				} else {
					//echo '<p>No Legacy coauthors';
				}
				//echo '<p>Post '.$single_post->ID; 
				//print_r($coauthors);
				//echo '<hr />';
				$this->add_coauthors($single_post->ID, $coauthors);
				
			}
		}
	}
	
	/*
	 * @TODO
	 * - Add new author
	 * - Add search-as-you-type to QuikcEdit
	 * - get_coauthor_meta function
	 */
	
}

/** Helper Functions **/

/* Replacement for the default WordPress get_profile function, since that doesn't allow for search by user_id
 * Returns a the specified column value for the specified user
 */

if(!function_exists('get_profile_by_id')) {
	function get_profile_by_id($field, $user_id) {
		global $wpdb;
		if($field && $user_id) return $wpdb->get_var( $wpdb->prepare("SELECT $field FROM $wpdb->users WHERE ID = %d", $user_id) );
		return false;
	}
}

/** Let's get the plugin rolling **/

// Create new instance of the edit_flow object
global $coauthors_plus;
$coauthors_plus = new coauthors_plus();
//$coauthors_plus->update();

// Core hooks to initialize the plugin
add_action('init', array(&$coauthors_plus,'init'));
add_action('admin_init', array(&$coauthors_plus,'admin_init'));

// Hook to perform action when plugin activated
register_activation_hook( __FILE__, array(&$edit_flow, 'activate_plugin'));

// Upgrade to new taxonomy system
if(floatval(get_option('coauthors_plus_version')) < 2.0) $coauthors_plus->update();

update_option('coauthors_plus_version', COAUTHORS_PLUS_VERSION);

?>