<?php
/*
Plugin Name: Easy Post Types
Plugin URI:
Description: Handle multiple post types and related custom fields and templates
Author: New Signature
Version: 1.0.1
Author URI: http://newsignature.com/
Plugin URI: http://www.wpeasyposttypes.com
*/

include "classes/db.php";
include "classes/javascript.php";

include "classes/custom-textfield/custom-textfield.php";
include "classes/custom-select/custom-select.php";
include "classes/custom-checkbox/custom-checkbox.php";
include "classes/custom-datefield/custom-datefield.php";
// include "classes/custom-image/custom-image.php";

define( 'BOXES_TEMPLATES_DIR', dirname(__FILE__).'/boxes/' );
define( 'CONTEXFLOW_TEMPLATES_DIR', dirname(__FILE__).'/templates/' );
define( 'TEMPLATES_DIR', CONTEXFLOW_TEMPLATES_DIR );
define( 'PLUGIN_POST_KEY', md5('cct') );
define( 'PLUGIN_DIR', dirname(__FILE__).'/' );
define( 'CONTEXFLOW_PLUGIN_URL', plugins_url( '', __FILE__).'/');
define( 'CONTEXFLOW_ICONS_URL', CONTEXFLOW_PLUGIN_URL.'icons/');


class CustomFields {
    public  $ajaxUrl;
    public  $types = array();
    public  $registeredPostTypes= array();
    public  $fields_info = array();
    public  $properties;
    public  $dbclass;
    public  $jsclass;
    public  $labels;
    public  $permissions;
    public  $httpRoot;
    public  $supports = array(
        'title','editor','author',
        'thumbnail','excerpt','trackbacks',
        'custom_fields','comments','revisions',
        'parent_child_relationships', 'page_attributes');

    public function __toString()
    {
        return "Easy Post Types";
    }

    public function  __construct() {
        global $wpdb;
        
        $this->ajaxUrl = admin_url('admin-ajax.php');
        $this->httpRoot = CONTEXFLOW_PLUGIN_URL;
        load_plugin_textdomain('cct', false, dirname( plugin_basename( __FILE__ ) )  );

        $this->permissions = array(
            'permission_edit_object',
            'permission_edit_type',
            'permission_edit_others_objects',
            'permission_publish_objects',
            'permission_read_object',
            'permission_read_private_object',
            'permission_delete_object'
        );

        $this->properties = array(
            'fieldset1' => array('fieldset','group-1', 'open'),
            'label' => array('textfield',__('Label','cct')),
            'singular_label' => array('textfield',__('Singular Label','cct')),
            'public' => array('checkbox',__('Public','cct')),
            'fieldset2' => array('fieldset', 'group-1', 'close'),
            'show_ui' => array('checkbox',__('Show UI','cct')),
            'rewrite' => array('checkbox',__('Rewrite','cct')),
            'query_var' => array('checkbox',__('Query Var','cct')),
            'hierarchical' => array('checkbox',__('Hierarchical','cct')),
            'capability_type' => array('select', __('Capability Type','cct'), array('post','page')),
            'supports' => array('multiselect', __('Supports','cct'), 0)
        );

        $this->labels = array(
            'name' => array( _x('Posts', 'post type general name'), _x('Pages', 'post type general name'), __('Name','cct')),
            'singular_name' => array( _x('Post', 'post type singular name'), _x('Page', 'post type singular name'),__('Singular Name','cct') ),
            'add_new' => array( _x('Add New', 'post'), _x('Add New', 'page'),__('Add New','cct') ),
            'add_new_item' => array( __('Add New Post','cct'), __('Add New Page','cct'),__('Add new Item','cct') ),
            'edit_item' => array( __('Edit Post','cct'), __('Edit Page','cct'), __('Edit Item','cct') ),
            'edit' => array( _x('Edit', 'post'), _x('Edit', 'page'), __('Edit','cct') ),
            'new_item' => array( __('New Post','cct'), __('New Page','cct'), __('New Item','cct') ),
            'view_item' => array( __('View Post','cct'), __('View Page','cct'), __('View Item','cct') ),
            'search_items' => array( __('Search Posts','cct'), __('Search Pages','cct'),__('Search Items','cct') ),
            'not_found' => array( __('No posts found','cct'), __('No pages found','cct'), __('Not Found','cct') ),
            'not_found_in_trash' => array( __('No posts found in Trash','cct'), __('No pages found in Trash','cct'), __("Not Found in Trash",'cct') ),
            'view' => array( __('View Post','cct'), __('View Page','cct'), __('View','cct') ),
            'parent_item_colon' => array( null, __('Parent Page:','cct'), __('Parent Item','cct') )
        );
        
        
        add_filter('the_posts', array($this, 'change_post'), 1);

        add_action('init', array($this, 'init' ));
        add_action('save_post', array($this, 'save_postdata'));
        add_action('admin_menu', array($this, 'main_menu'));
        add_action('admin_init', array($this, 'post_handler'));
        add_action('admin_init', array($this, 'add_meta_boxes'));

        add_action('wp_ajax_update_labels', array($this,'ajax_update_labels'));
        add_action('wp_ajax_update_permissions', array($this,'ajax_update_permissions'));
        add_action('wp_ajax_update_admin', array($this,'ajax_update_admin'));
        add_action('wp_ajax_update_visibility', array($this,'ajax_update_visibility'));
        add_action('wp_ajax_load_quick_edit', array($this,'ajax_load_quick_edit'));
        add_action('wp_ajax_export_content', array($this,'ajax_export_content'));
        add_action('wp_ajax_import_content', array($this,'ajax_import_content'));
        add_action('wp_ajax_delete_field', array($this,'ajax_delete_field'));
        add_action('wp_ajax_edit_field', array($this,'ajax_edit_field'));
        add_action('wp_ajax_update_field', array($this,'ajax_update_field'));
        add_action('wp_ajax_edit_category', array($this,'ajax_edit_category'));
        add_action('wp_ajax_update_category', array($this,'ajax_update_category'));
        add_action('wp_ajax_save_categories_used_by_content_type', array($this,'ajax_save_categories_used_by_content_type'));
        add_action('wp_ajax_add_category', array($this,'ajax_add_category'));
        add_action('wp_ajax_reload_content', array($this,'ajax_reload_content'));
        add_action('wp_ajax_reload_page_content', array($this,'ajax_reload_page_content'));
        add_action('wp_ajax_reload_fieldtype', array($this,'ajax_reload_fieldtype'));
        add_action('wp_ajax_save_field', array($this,'ajax_save_field'));
        add_action('wp_ajax_delete_content', array($this,'ajax_delete_content'));
        add_action('wp_ajax_move_content', array($this,'ajax_move_content'));
        add_action('wp_ajax_update_field_type_settings', array($this, 'ajax_update_field_type_settings'));
        add_action('wp_ajax_add_field', array($this, 'ajax_add_field'));

        add_action('contextual_help_list', array($this, 'admin_help'), 10, 2);
        add_filter('get_user_option_closedpostboxes_content-type', array($this, 'default_closed_boxes_contenttype'), 10, 3 ); // default closed meta boxes

        $this->dbclass = new CustomFields_db();
   }

   public function prepare_labels() {
       $output = "";
       foreach($this->labels as $key=>$label) {
           $output .= "data['post_label_".$key."']=jQuery(\"#post_label_".$key.'").val();';
           $output .= "data['page_label_".$key."']=jQuery(\"#page_label_".$key.'").val();';
       }
       
       return $output;
   }

   public function prepare_permissions() {
       $output = "";
       foreach($this->permissions as $key=>$label) {
           $output .= "data['".$label."']=jQuery('#".$label."').val();\n";
       }

       return $output;
   }

   public function change_post($var) {
       foreach($var as $key=>$post) {
        $var[$key]->ct_theme=$this;
       }
       return $var;
   }
   
   
   
   /**
    * Create the admin pages
    *
    * This register an admin menu and sub admin pages. It attaches a callback for each page.
    * It also setups the scripts and styles to be included on the admin pages it registers.
    *
    */
   public function main_menu() {
       $page1=add_menu_page('Page title', 'Easy Post Types', 'administrator', 'ct_general', array($this, 'options'), CONTEXFLOW_PLUGIN_URL.'images/easy-post-type-small.png');
       add_submenu_page( 'ct_general', 'Easy Post Types General Settings', __('General Settings','cct'), 'administrator', 'ct_general', array($this, 'noop'));
       $page2=add_submenu_page( 'ct_general', 'Easy Post Types Edit Fields Settings', __('Edit Fields','cct'), 'administrator', 'ct_editcontent', array($this, 'edit_content'));
       $page3=add_submenu_page( 'ct_general', 'Easy Post Types Utilities', __('Utilities','cct'), 'administrator', 'ct_utilities', array($this, 'utilities'));

       add_action('admin_print_scripts-'.$page1, array($this, 'options_scripts' ));
       add_action('admin_print_scripts-'.$page2, array($this, 'edit_scripts' ));
       add_action('admin_print_scripts-'.$page3, array($this, 'options_scripts' ));
       add_action('admin_print_styles-'.$page1, array($this, 'print_admin_styles' ));
       add_action('admin_print_styles-'.$page2, array($this, 'print_admin_styles' ));
       add_action('admin_print_styles-'.$page3, array($this, 'print_admin_styles' ));
   }
   
   public function noop(){}
   
   public function admin_help($contextual_help, $screen) {
      
      if($screen->id == 'toplevel_page_ct_general') {
        $contextual_help[$screen->id] = __('Help for the custom post types settings page','cct');
      } else {
        $contextual_help[$screen->id] = '';
      }
      
      if($screen->parent_base == 'ct_general'){
        $contextual_help[$screen->id] .= '<h5>' . __('General Help','cct') . '</h5>';
        $contextual_help[$screen->id] .= __('General Help','cct');
      }
      
      return $contextual_help;
   }

   public function selectContentType() {
      if (count($this->registeredPostTypes)>0) {
          $choices='<option selected value="">'.__('Please Select','cct').'</option>';
          foreach($this->registeredPostTypes as $key=>$obj){
            $choices.='<option value="'.$obj.'">'.$obj.'</option>';
          }

          $changeAction="javascript:window.location='".get_bloginfo('wpurl')."/wp-admin/admin.php?page=ct_editcontent&type='+jQuery('#ct_name').val()";
          echo '<select onChange="'.$changeAction.'" id="ct_name" name="ct_name">'.$choices.'</select>';
      }
      else {
        _e('Please add a post type first...','cct');
      }
   }
    
    
    /**
     * Render the edit field form
     * 
     * @param $values
     */
    public function edit_field_form($values=array(), $errors=array(), $is_new=true) {
      include CONTEXFLOW_TEMPLATES_DIR.'field-form.php';
    }
    
    
    /**
     * Render the settings for a form type in the edit field
     *
     * @param $field_type - the key for the type of field to render
     * @param $content_type - the post type
     */
    public function edit_field_form_type_fields($field_type, $content_type='', $values=array()){
      $fieldType=$this->getFieldType($field_type);
      include CONTEXFLOW_TEMPLATES_DIR.'field-form-field-settings.php';
    }
    
    
    /**
     * Render the category form
     *
     * @param $category
     * @param $errors
     */
    public function edit_category_form($category, $errors=array()) {
      include CONTEXFLOW_TEMPLATES_DIR.'add-category.php';
    }

    public function getFieldInfo($id, $type, $name) {
        return array('extra' => $this->fields_info['fields'][$type][$name]['extra'],
                     'info' => $this->fields_info['fields'][$type][$name]);
    }

    public function saveField() {
        if (empty($_POST['content_type']) ||
            empty($_POST['ct_name'])      ||
            empty($_POST['field_name'])   ||
            empty($_POST['name'])) return false;
        $type=$_POST['ct_name'];
        $fieldType=$this->getFieldType($type);
        $this->fields_info['fields'][$_POST['content_type']][$_POST['field_name']]=array(
            'field_name' => $_POST['field_name'],
            'name' => $_POST['name'],
            'type' => $type,
            'show_list' => $_POST['show_list'],
            'extra' => $fieldType->extra($_POST));
        $this->save_content_type();
        return true;
    }

    public function saveCategory() {
        if (empty($_POST['content_type']) ||
            empty($_POST['internal_name'])||
            empty($_POST['name'])){
            return false;
        }

        $type=$_POST['content_type'];
        $this->fields_info['categories'][$_POST['internal_name']]=array (
            'internal_name' => $_POST['internal_name'],
            'object_type'   => array($type),
            'filters'       => array(
                'hierarchical'  => $_POST['hierarchical']=='on',
                'query_var'     => $_POST['query_var']=='on',
                'rewrite'       => $_POST['rewrite']=='on',
                'public'        => $_POST['public']=='on',
                'label'         => $_POST['name']
            )
        );
        register_taxonomy($_POST['internal_name'], array($type), $this->fields_info['categories'][$_POST['internal_name']]['filters']);
        $this->save_content_type();
        return true;
    }
    
    
    /**
     * Add/Update a field settings
     * 
     * @param $content_type string - the key for the post type to add a field to
     * @param $field_key string - the key for the field
     * @param $field_type string - the name of the type of field
     * @param $field_name string - the display name of the field
     * @param $add_as_column boolean - flag to display the field as a column in the table of posts
     * @param $extra array - settings for the field type
     */
    public function update_field($content_type, $field_key, $field_type, $field_name=null, $add_as_column=null, $extra=null){
      // $field_key, $content_type and $field_type are required to be non-empty
      if(empty($field_key) || empty($content_type) || empty($field_type)){
        return false;
      }
      
      // Get the definition for the field type
      $field_type_def =$this->getFieldType($field_type);
      if($field_type_def==null){
        // return if not set
        return false;
      }
      
      // Check if we are updating or adding
      if(isset($this->fields_info['fields'][$content_type][$field_key])){
        // update the field
        $update = array();
        if(!empty($field_name)){
          $update['name'] = $field_name;
        }
        
        if(!empty($add_as_column)){
          $update['show_list'] = $add_as_column;
        }
        
        if($extra!=null){
          $update['extra'] = $field_type_def->extra($extra);
        }
        
        $this->fields_info['fields'][$content_type][$field_key] = array_merge( $this->fields_info['fields'][$content_type][$field_key], $update );
      } else {
        // new field
        $this->fields_info['fields'][$content_type][$field_key] = array(
          'name' => $field_name,
          'field_name' => $field_key,
          'type' => $field_type,
          'show_list' => $add_as_column,
          'extra' =>  $field_type_def->extra($extra),
        );
      }
      
      $this->save_content_type();
      
      return $this->fields_info['fields'][$content_type][$field_key];
    }
    
    
    /**
     *  Register a custom post type form handler
     */
    public function register_post_type_form_handler() {
        global $user_login;
        
        // get the really basic information first
        $post_type_key = $_POST['content_type'];
        $name_plural = $_POST['label'];
        $name_singular = $_POST['singular_label'];
        
        // Will not save without a name for the post type
        if(empty($name_plural)){
          return;
        }
        
        
        // is this a new post type?
        $is_new = empty($post_type_key) || !isset($this->fields_info['types'][$post_type_key]);
        
        // Validate/create key
        if(!$is_new && empty($post_type_key)){
          return; // it must have a key for an existing post type
          
        } else if(empty($post_type_key)){
          // create a unique key for a new post type
          $post_type_key = $this->sanitize_key($name_plural);
          if(isset($this->fields_info[$post_type_key])){
            $i = 1;
            while(isset($this->fields_info[$post_type_key.'-'.$i])){
              ++$i;
            }
            $post_type_key = $post_type_key.'-'.$i;
          }
        }
        
        // redundant check to stop if key is missing
        if(empty($post_type_key)){
          return;
        }
        
        
        // Convert a value of 'on' to true, a new content type to true, otherwise, false
        $pretty_urls = (!empty($_POST['pretty_urls']) && $_POST['pretty_urls']=='on') || $is_new;
        
        
        // 'label' = $name_plural
        // 'content_type' = $post_type_key
        // 'singular_label' = $name_singular
        // 'pretty_urls' = $pretty_urls
        
        
        // Save the names
        $this->fields_info['types'][$post_type_key]['label']= $name_plural;
        $this->fields_info['types'][$post_type_key]['singular_label']=(empty($name_singular) ? $name_plural : $name_singular);
        
        
        // Save all the supported features
        foreach($this->supports as $key) {
            $this->fields_info['types'][$post_type_key][$key]= (!empty($_POST[$key]) && $_POST[$key]=='on');
        }
        
        
        // Save creation time and user
        if (!isset($this->fields_info['types'][$post_type_key]['created'])) {
          $this->fields_info['types'][$post_type_key]['created']=time();
          $this->fields_info['types'][$post_type_key]['createdby']=$user_login;
        }
        
        // Save the pretty URLs setting
        $this->fields_info['types'][$post_type_key]['rewrite']= $pretty_urls;
        
        // Default settings for a new post type
        // show_ui
        if (!isset($this->fields_info['types'][$post_type_key]['show_ui'])) {
            $this->fields_info['types'][$post_type_key]['show_ui']=true;
        }
        // public
        if (!isset($this->fields_info['types'][$post_type_key]['public'])) {
            $this->fields_info['types'][$post_type_key]['public']=true;
        }
        
        // Add the special columns for the admin screen with all the objects of the post type
        $this->fields_info['fields'][$post_type_key]['admin_columns']=array('cb','title','author');
        
        $this->save_content_type();

        if ($is_new) flush_rewrite_rules(); // Make sure permalinks knows about our new post type.
    }
    
    

    public function utilities() {
      include BOXES_TEMPLATES_DIR.'utility.php';
    }

    public function unset_categories($type) {
        if (is_array($this->fields_info['categories'])) {
            foreach($this->fields_info['categories'] as $key=>$category) {
                if (in_array($type, $category['object_type'])) {
                    $this->fields_info['categories'][$key]['object_type']=array_diff($this->fields_info['categories'][$key]['object_type'], array($type));
                }
            }
        }
    }

    public function post_handler() {
        if (empty($_POST['plugin_post_key']) || $_POST['plugin_post_key']!=PLUGIN_POST_KEY) return;
        if (!empty($_POST['update_content']) || !empty($_POST['save']) || !empty($_POST['add_content'])) {
            $this->register_post_type_form_handler();
            $this->registerContentTypes();

            header("location:".admin_url('admin.php').'?'.$_SERVER['QUERY_STRING']);
            return;
        }
        if ($_POST['save_field']) {
            if (empty($_POST['content_type'])) return;
            $this->saveField();
            header("location:".admin_url('admin.php').'?'.$_SERVER['QUERY_STRING']);
        }
        if ($_POST['save_category']) {
            if (empty($_POST['content_type'])) return;
            //$this->saveCategory();
            header("location:".admin_url('admin.php').'?'.$_SERVER['QUERY_STRING']);
        }
        if ($_POST['doaction']) {
            $action=$_POST['action'];
            if (isset($_POST['delete_content_type'])) {
                $cts=$_POST['delete_content_type'];
                foreach($cts as $key => $value) {
                    switch($action) {
                        case 'delete':
                            $this->dbclass->deleteContentType($value);
                            $this->unset_categories($value);
                            unset($this->fields_info['types'][$value]);
                            unset($this->fields_info['fields'][$value]);
                            $this->save_content_type();
                            break;
                    }
                }
            }
        }

    }

    public function options() {
        $contentTypes = $this->fields_info['types'];
        $contentFields = $this->fields_info;
        $this->jsclass = new CustomType_Javascript();
        $this->jsclass->create($this);
        include PLUGIN_DIR . "main.php";
    }
    
    /**
     * Delete a field from a post type
     *
     * @param $content_type - the key for the post type to delete the field from.
     * @param $field_name - the name of the field to delete
     */
    public function remove_field_from_content_type($content_type, $field_name){
      unset($this->fields_info['fields'][$content_type][$field_name]);
      $this->save_content_type();
    }

    public function edit_content() {
        $content="";
        if (isset($_REQUEST['type']))
            $type=$_REQUEST['type'];
        else
            $type="";

        if (isset($this->fields_info['types'][$type])) {
            $content=$this->fields_info['types'][$type];
            $content['systemkey']=$type;
            $content['fields'] = $this->fields_info['fields'][$type];
            $content['categories'] = empty($this->fields_info['categories']) ? null : $this->fields_info['categories'];
        }

        $this->jsclass = new CustomType_Javascript();
        $this->jsclass->create($this);

        include PLUGIN_DIR . "editcontent.php";
    }

    public function include_template($plugin, $name, $template, $values) {
        if (!empty($template)) {
            $path=TEMPLATEPATH.'/'.$template;
            if (file_exists($path)) {
                include($path);
                return;
            }
        }

        $path=TEMPLATEPATH.'/theme-'.$plugin->getId().'-'.$name.'.php';
        if (file_exists($path))
            include($path);
        else {
            $path=TEMPLATEPATH.'/theme-'.$plugin->getId().'.php';
            if (file_exists($path))
                include($path);
            else
                include($plugin->getRoot(). 'theme-'.$plugin->getId().'.php');
        }
    }

    public function theme($post, $name, $template=null, $raw=false) {
        $field=$this->getFieldType($this->fields_info['fields'][$post->post_type][$name]['type']);
        if ($field)
            return $field->theme($post, $name, $template, $raw);
        else {
            echo "<div class=\"error\">";
            _e("Template missing for field : ",'cct');
            echo $post->post_type.' => '.$name;
            echo "</div>";
        }
    }

    public function themeList($post, $name) {
        $field=$this->getFieldType($this->fields_info['fields'][$post->post_type][$name]['type']);
        if ($field)
            return $field->themeList($post, $name);
        else {
            echo "<div class=\"error\">";
            _e("Template missing for field: ",'cct');
            echo $post->post_type.' => '.$name;
            echo "</div>";
        }
    }

    public function display_fields($type) {

        if ($this->fields_info['fields'][$type]) {
            echo '<div id="field-list-id" class="field-list">';
            echo '<ul class="description">';
            foreach($this->fields_info['fields'][$type] as $key =>$value) {
                echo "<li>";
                echo '<ul class="field_list">';
                echo '<li><span class="label">'.__('Type:','cct').'</span><span class="value">'.$value['type'].'</span></li>';
                echo '<li><span class="label">'.__('Label:','cct').'</span><span class="value">'.$value['name'].'</span></li>';
                echo '<li><span class="label">'.__('Field Name:','cct').'</span><span class="value">'.$value['field_name'].'</span></li>';
                echo '</ul>';
                echo "</li>";
            }
            echo '</ul>';
            echo '</div>';
          }
    }
  
  
  
    /**
     * Delete a field from a content-type via AJAX
     *
     * 
     */
    public function ajax_delete_field() {
      
      $retval = array( 'status' => 'success' );
      
      // Check that the content_type is given
      if(empty($_POST['content_type'])){
        $retval['status'] = 'error';
        $retval['message'] = __('No post type was given.', 'cct');
        
      } else {
        // Get values from the POST
        $content_type = $_POST['content_type'];
        $field_name = $_POST['field_name'];
        
        // Delete the field
        $this->remove_field_from_content_type($content_type, $field_name);
        
        // Rerender the list of fields
        $content=$this->fields_info['types'][$content_type];
        $content['systemkey'] = $content_type;
        $content['fields'] = $this->fields_info['fields'][$content_type];
        
        ob_start();
        $this->list_fields($content);
        $fields = ob_get_contents();
        ob_end_clean(); 
        $retval['contents'] = $fields;
      }
      
      echo $this->json_encode($retval);
      exit();
    }

    // Ajax Call to update labels.
    public function ajax_update_labels() {
        $type=$_POST['content_type'];
        
        $this->fields_info['types'][$type]['labels']=array();
        foreach($this->labels as $key=>$label) {
           $this->fields_info['types'][$type]['labels'][$key]=array();
           if ($this->fields_info['types'][$type]['parent_child_relationships']==0)
            $this->fields_info['types'][$type]['labels'][$key]=$_POST['post_label_'.$key];
           else
            $this->fields_info['types'][$type]['labels'][$key]=$_POST['page_label_'.$key];
        }

        $this->save_content_type();

        exit();
    }

    public function ajax_update_permissions() {
        $type=$_POST['content_type'];

        $this->fields_info['types'][$type]['capabilities']=array();
        foreach($this->permissions as $key=>$label) {
           $this->fields_info['types'][$type]['capabilities'][]=$_POST[$label];
        }

        $this->save_content_type();

        exit();
    }

    public function ajax_update_admin() {
        $type=$_POST['content_type'];

        $fields=split(',',$_POST['fields_to_show_in_table']);
        $this->fields_info['types'][$type]['public']=$_POST['public']=='true'?true:false;
        $this->fields_info['types'][$type]['show_ui']=$_POST['show_ui']=='true'?true:false;

        unset($this->fields_info['fields'][$type]['admin_columns']);

        foreach ($this->fields_info['fields'][$type] as $key=>$value) {
            $this->fields_info['fields'][$type][$key]['show_list']='off';
        }
        
        if (is_array($fields)) {
            foreach($fields as $field) {
                if (isset($this->fields_info['fields'][$type][$field])) {
                    $this->fields_info['fields'][$type][$field]['show_list']='on';
                }
                if (strpos($field,'%<')!==false) {
                    $this->fields_info['fields'][$type]['admin_columns'][]=strtolower(substr($field,2,strlen($field)-4));
                }
            }
        }
        
        echo $_POST['admin_menu_icon'];
        
        $this->fields_info['types'][$type]['menu_position']=$_POST['admin_menu_position'];
        $this->fields_info['types'][$type]['menu_icon']=empty($_POST['admin_menu_icon'])? null: $_POST['admin_menu_icon'];
        
        $this->save_content_type();

        exit();
    }

    public function ajax_update_visibility() {
        $type=$_POST['content_type'];

        $this->fields_info['types'][$type]['query_var']=$_POST['query_var']=='true'?true:false;
        $this->fields_info['types'][$type]['exclude_from_search']=$_POST['exclude_from_search']=='true'?false:true;

        $this->save_content_type();

        exit();
    }

    public function list_fields($content) {
      include BOXES_TEMPLATES_DIR.'field-rows.php';
    }

    public function ajax_edit_field() {
        $name=empty($_POST['field_name']) ? '' : $_POST['field_name'] ;
        $type=empty($_POST['content_type']) ? '' : $_POST['content_type'];
        $is_new=!empty($_POST['new_field']) && $_POST['new_field']=='true';
        $values= empty($this->fields_info['fields'][$type][$name]) ? array() : $this->fields_info['fields'][$type][$name];
        $this->edit_field_form($values, array(), $is_new);
        exit();
    }

    public function ajax_update_field() {
        $sorted=array();
        $order=$_POST['order'];
        $type=$_POST['content_type'];
        $_REQUEST['type']=$_POST['content_type'];
        $keys=split(',',$order);
        $index=0;
        foreach($keys as $key) {
          if ($key=='%fieldset%') {
            $sorted['fieldset'.$index++]=array('type'=>'_fieldset');
          }
          elseif(array_key_exists($key, $this->fields_info['fields'][$type])) {
            $sorted[$key]=$this->fields_info['fields'][$type][$key];
          }
        }
        
        $this->fields_info['fields'][$type]=$sorted;

        $this->save_content_type();
        if (isset($this->fields_info['types'][$type])) {
          $content=$this->fields_info['types'][$type];
          $content['systemkey']=$type;
          $content['fields'] = $this->fields_info['fields'][$type];
          $content['categories'] = $this->fields_info['categories'];
        }

        $this->box_fields($content);

        exit();
    }
    
    
    /**
     * Update/Add a category
     *
     * @param $key - the string that is a key for the category
     * @param $options - an array of options
     *           $options.object_type
     *           $options.hierarchical
     *           $options.query_var
     *           $options.rewrite
     *           $options.public
     *           $options.label
     *
     * @return boolean for status, true if successful, otherwise, false
     */
    public function update_category($key, $options=array()) {
      if(empty($key)){
        return false;
      }
      
      if(isset($this->fields_info['categories'][$key])){
        $options = array_merge($this->fields_info['categories'][$key]['filters'], array( 'object_type' => $this->fields_info['categories'][$key]['object_type']), $options);
      } else {
        $options = array_merge(array(
            'object_type' => array(),
            'hierarchical'=> true,
            'query_var'   => true,
            'rewrite'     => true,
            'public'      => true,
            'label'       => $key,
          ), $options);
      }
      
      $this->fields_info['categories'][$key]=array (
        'internal_name' => $key,
        'object_type'   => $options['object_type'],
        'filters'       => array(
          'hierarchical'  => $options['hierarchical'],
          'query_var'     => $options['query_var'],
          'rewrite'       => $options['rewrite'],
          'public'        => $options['public'],
          'label'         => $options['label']
        )
      );
      
      register_taxonomy($key, $types, $this->fields_info['categories'][$key]['filters']);
      
      $this->save_content_type();
      
      return true;
    }
    
    
    /**
     * Returns the form for a category
     */
    public function ajax_edit_category() {
        if (isset($_POST['category'])) {
            $category=$this->fields_info['categories'][$_POST['category']];
        }
        else
            $category=array();
        $this->edit_category_form($category);
        exit();
    }
    
    
    /**
     * Add a category via AJAX
     *
     * This is the callback for an AJAX response to add a new category.
     * It creates a new category and attaches it to the post type that created it.
     */
    public function ajax_add_category() {
      return $this->_ajax_save_category(true);
    }
    
    
    /**
     * Renders the category list for a post type
     *
     */
    public function _rerender_categories_via_ajax($content_type) {
      $content=$this->fields_info['types'][$content_type];
      $content['systemkey']=$content_type;
      $content['fields'] = $this->fields_info['fields'][$content_type];
      $content['categories'] = $this->fields_info['categories'];
      
      ob_start();
      $this->list_categories($content);
      $cats = ob_get_contents();
      ob_end_clean();  
      
      return $cats;
    }
    
    
    public function _ajax_save_category($is_new){
      $retval = array( 
          'status' => 'success',
        );
        
        // Abort if content_type is not defined for a new category
        if ($is_new && empty($_POST['content_type'])){
            $retval = array( 
              'status' => 'error',
              'message' => __('Missing post type to associate the category with.', 'cct')
            );
        // Return the form back with error since the Name is missing
        } elseif (empty($_POST['name'])) {
          ob_start();
          $this->edit_category_form($_POST, array('name'));
          $form = ob_get_contents();
          ob_end_clean();
          $retval = array( 
              'status' => 'error',
              'message' => __('You need to give a name for the category.', 'cct'),
              'form' => $form,
            );
        // Abort if updating a category and the internal_name is missing
        } elseif(!$is_new && empty($_POST['internal_name'])){
          $retval = array( 
              'status' => 'error',
              'message' => __('The system name is missing.', 'cct'),
            );
        // Save the category
        } else { 
          // The options for the category
          $cat_options = array(
             'hierarchical' => $_POST['hierarchical']=='on', 
             'query_var' => $_POST['query_var']=='on',
             'rewrite' => $_POST['rewrite']=='on',
             'public' => $_POST['public']=='on',
             'label' => $_POST['name']
          );
          
          // If new, then create an internal_name if not provided and set the object_type to the current post type.
          if($is_new){
            // Create an internal name is not provided, and sanitize the internal name
            $_POST['internal_name'] = empty($_POST['internal_name']) ? $this->sanitize_key($_POST['name']) : $this->sanitize_key($_POST['internal_name']);
            $cat_options['object_type'] = array($_POST['content_type']);
          }
          
          $this->update_category($_POST['internal_name'], $cat_options);
          
          // Rerender the list of categories and return it to update the list
          $retval['categories'] = $this->_rerender_categories_via_ajax($_POST['content_type']);
        }
        
        
        echo $this->json_encode($retval);
        exit();
    }
    
    
    /**
     * Update the settings for an existing category via AJAX
     */
    public function ajax_update_category() {
        return $this->_ajax_save_category(false);
    }
    
    
    /**
     * Saves the categories that are used by a post type via AJAX
     *
     */
    public function ajax_save_categories_used_by_content_type() {
      
      $retval = array(
        'status' => 'success'
      );
      
      if(empty($_POST['content_type'])){
        $retval['status'] = 'error';
        $retval['message'] = __('Missing post type to associate the categories with.', 'cct');
      } else {
        $i = 0;
        foreach($_POST['categories_to_update'] as $key => $state){
          if($state == 'true'){
            // Add category to post type
            $this->fields_info['categories'][$key]['object_type']=array_merge($this->fields_info['categories'][$key]['object_type'], array($_POST['content_type']));
          } else {
            // Remove category from post type
            $this->fields_info['categories'][$key]['object_type']=array_diff($this->fields_info['categories'][$key]['object_type'], array($_POST['content_type']));
          }
          ++$i;
        }
        
        $this->save_content_type();
        $retval['number_changed'] = $i;
        $retval['contents'] = $this->_rerender_categories_via_ajax($_POST['content_type']);
      }
      
      echo $this->json_encode($retval);
      exit();
    }
    
    
    /**
     * The AJAX callback for adding fields
     * 
     * 
     */
    public function ajax_add_field() {
      $retval = array(
        'status' => 'success'
      );
      
      // ensure if content_type is set
      if(empty($_POST['content_type'])){
        $retval['status'] = 'error';
        $retval['message'] = __('Missing post type to associate the field with.', 'cct');
        
      // ensure that name, ct_name, field_name is set
      } elseif(empty($_POST['name']) || empty($_POST['ct_name'])) {
        $retval['status'] = 'error';
        $retval['errors'] = array();
        $errors = array();
        
        $es = array( 'name', 'ct_name' );
        foreach( $es as $e ){
          if(empty($_POST[$e])){
            $errors[] = $e;
          }
        }
        $retval['errors'] = $errors;
        
        // Recreate the form with errors!!!!      
        ob_start();
        $this->edit_field_form($_POST, $errors);
        $retval['contents'] = ob_get_contents();
        ob_end_clean();
      
      // save it
      } else {        
        $content_type = $_POST['content_type'];
        $field_key = empty($_POST['field_name']) ? $this->sanitize_key($_POST['name']) : $this->sanitize_key($_POST['field_name']);
        $field_type = $_POST['ct_name'];
        $field_name = $_POST['name'];
        $add_as_column = !!$_POST['show_list'];
        $extra = $_POST;
        
        $field = $this->update_field($content_type, $field_key, $field_type, $field_name, $add_as_column, $extra);
        if($field==false){
          $retval['status'] = 'error';
          $retval['message'] = __('The field was unable to be added.');
        } else {
          // Now the list of fields can be updated
          $content['fields'] = array();
          $content['fields'][$field_key] = $this->fields_info['fields'][$content_type][$field_key];
          $content['systemkey'] = $content_type;
          // Render just the field row, not all the rows
          ob_start();
          include BOXES_TEMPLATES_DIR.'field-rows.php';
          $retval['contents'] = ob_get_contents();
          ob_end_clean();
        }
      }
      
      
      echo $this->json_encode($retval);
      exit();
    }
    
    
    
    
    /**
     * The AJAX callback for saving a field
     * 
     * 
     */
    public function ajax_save_field() {
      $retval = array(
        'status' => 'success'
      );
      
      // ensure if content_type is set
      if(empty($_POST['content_type'])){
        $retval['status'] = 'error';
        $retval['message'] = __('Missing post type to associate the field with.', 'cct');
        
      // ensure that name, ct_name, field_name is set
      } elseif(empty($_POST['name']) || empty($_POST['ct_name'])) {
        $retval['status'] = 'error';
        $retval['errors'] = array();
        $errors = array();
        
        $es = array( 'name', 'ct_name' );
        foreach( $es as $e ){
          if(empty($_POST[$e])){
            $errors[] = $e;
          }
        }
        $retval['errors'] = $errors;
        
        // Recreate the form with errors!!!!      
        ob_start();
        $this->edit_field_form($_POST, $errors);
        $retval['contents'] = ob_get_contents();
        ob_end_clean();
      
      // save it
      } else {        
        $content_type = $_POST['content_type'];
        $field_key = $_POST['field_name'];
        $field_type = $_POST['ct_name'];
        $field_name = $_POST['name'];
        $add_as_column = !!$_POST['show_list'];
        $extra = $_POST;
        
        $field = $this->update_field($content_type, $field_key, $field_type, $field_name, $add_as_column, $extra);
        if($field==false){
          $retval['status'] = 'error';
          $retval['message'] = __('The field was unable to be added.');
        } else {
          // Now the list of fields can be updated
          $content['fields'] = array();
          $content['fields'] = $this->fields_info['fields'][$content_type];
          $content['systemkey'] = $content_type;
          // Render all the fields
          ob_start();
          include BOXES_TEMPLATES_DIR.'field-rows.php';
          $retval['contents'] = ob_get_contents();
          ob_end_clean();
        }
      }
      
      
      echo $this->json_encode($retval);
      exit();
    }
    
    
    
    /**
     * Outputs the table rows of categories for a post type.
     *
     */
    public function list_categories($content) {
      include BOXES_TEMPLATES_DIR.'category-sets-rows.php';
    }

    public function ajax_reload_fieldtype() {
        $this->edit_field_form();
        exit();
    }
    
    
    /**
     * Outputs the settings for a field type.
     *
     */
    public function ajax_update_field_type_settings() {
      $retval = array(
        'status' => 'success',
      );
      
      ob_start();
      $this->edit_field_form_type_fields($_POST['field_type'], $_POST['content_type']);
      $retval['contents'] = ob_get_contents();
      ob_end_clean();
      
      echo $this->json_encode($retval);
      exit();
    }


    public function json_delete_content($key) {
        $json = "{'url':'$this->ajaxUrl','name':'$key','passthru':'0'}";
        echo $json;
    }

    public function json_add_field($key) {
        $json = "{'url':'$this->ajaxUrl','name':'$key'}";
        echo $json;
    }

    public function json_edit_field($key, $name) {
        $json = "{'url':'$this->ajaxUrl','name':'$key', 'field_name':'$name'}";
        echo $json;
    }

    public function json_update_labels($key) {
        //$json = "{'url':'$this->ajaxUrl','name':'$key'}";
        //echo $json;
        echo $this->ajaxUrl;
    }
    public function json_update_category($key, $category) {
        $json = "{'url':'$this->ajaxUrl','name':'$key','category':'$category'}";
        echo $json;
    }
    public function json_update_visibility($key) {
        $json = "{'url':'$this->ajaxUrl','name':'$key'}";
        echo $json;
    }
    public function json_update_permissions($key) {
        $json = "{'url':'$this->ajaxUrl','name':'$key'}";
        echo $json;
    }


    public function ajax_delete_content() {
        $name=$_POST['content_type'];
        if ($_POST['passthru']==1) {
            $this->dbclass->deleteContentType($name);
            unset($this->fields_info['types'][$name]);
            unset($this->fields_info['fields'][$name]);
            $this->unset_categories($name);
            $this->save_content_type();
            $this->Options();
        }
        else {
            $n=$this->dbclass->getPostsPerContent($name);
            echo "<p>".__('Do you want to delete the ','cct'). $name .__(' content? It has ','cct') . $n . __(' entries.','cct') .'</p>';
        }
        exit();
    }

    public function ajax_move_content() {
        $name=$_POST['content_type'];
        $this->dbclass->moveContentType($name);
        unset($this->fields_info['types'][$name]);
        unset($this->fields_info['fields'][$name]);
        $this->unset_categories($name);
        $this->save_content_type();
        exit();
    }

    public function ajax_export_content() {
        $type=$_POST['content_type'];
        if ($type=='all')
            echo serialize(array('FULL' => $this->fields_info));
        else {
            echo serialize(array(
                'types'     => array($type => $this->fields_info['types'][$type]),
                'fields'    => array($type => $this->fields_info['fields'][$type]),
                'categories'=> $this->fields_info['categories']
                ));
        }
        exit();
    }

    public function ajax_import_content() {
        $text=$_POST['text'];
        $text=str_replace("\\\"","\"",$text);
        $obj=unserialize($text);

        if (isset($obj['FULL'])) {
            $this->fields_info=$obj['FULL'];
        }
        else {
            if (isset($obj['types']) && isset($obj['fields']) && isset($obj['categories'])) {
                if (is_array($this->fields_info['types']))
                    $this->fields_info['types']=array_merge($this->fields_info['types'], $obj['types']);
                else
                    $this->fields_info['types']=$obj['types'];

                if (is_array($this->fields_info['fields']))
                    $this->fields_info['fields']=array_merge($this->fields_info['fields'], $obj['fields']);
                else
                    $this->fields_info['fields']=$obj['fields'];

                if (is_array($this->fields_info['categories']))
                    $this->fields_info['categories']=array_merge($this->fields_info['categories'], $obj['categories']);
                else
                    $this->fields_info['categories']=$obj['categories'];
            }
        }
        $this->save_content_type();
        _e('Import successful','cct');
        exit();
    }

    // Ajax Call to reload content.
    public function ajax_reload_page_content() {
        $this->Options();
        exit();
    }
    public function ajax_load_quick_edit() {
        $key = $_POST['content_type'];
        $values = $this->fields_info['types'][$key];

        include PLUGIN_DIR . "quickedit-form.php";
        exit();
    }


    public function save_content_type() {
        update_option('ct_content_types', $this->fields_info['types']);
        update_option('ct_fields_types', $this->fields_info['fields']);
        update_option('ct_categories_types', $this->fields_info['categories']);
    }

    public function load_content_type() {
        $ct = get_option('ct_fields_types');
        if ($ct) {
            $this->fields_info['fields']=$ct;
        }
        
        
        
        $ct = get_option('ct_categories_types');
        if ($ct) {
            $this->fields_info['categories']=$ct;
        }
        

        $ct = get_option('ct_content_types');
        if ($ct) {
            $this->fields_info['types']=$ct;
        }
        
        
    }

    public function registerType($type) {
        $this->types[]=$type;
    }

    public function registerContentTypes() {
        
        $this->registeredPostTypes=array();
        if (isset($this->fields_info['types'])) {
            foreach ($this->fields_info['types'] as $key => $type) {
                // DON'T USE $type
                
                // register_meta_box_cb is an option when creating a custom post type
                // register_meta_box_cb - Provide a callback function that will be called when setting up the meta boxes for the edit form. Do remove_meta_box() and add_meta_box() calls in the callback
                $this->fields_info['types'][$key]['register_meta_box_cb'] = array($this, 'init_custom_meta_boxes_for_post_edit');
                $sup=array();
                foreach($this->supports as $supkey) {
                    if ($this->fields_info['types'][$key][$supkey]==true)
                      $sup[]=$supkey;
                }
                $this->fields_info['types'][$key]['supports']=$sup;
                
                register_post_type($key, $this->fields_info['types'][$key]);
                $this->register_columns($key, $this->fields_info['types'][$key]);
                $this->registeredPostTypes[]=$key;
            }
        }
        if (isset($this->fields_info['categories'])) {
            foreach($this->fields_info['categories'] as $key=>$category) {
                register_taxonomy($category['internal_name'], $category['object_type'], $category['filters']);
            }
        }
    }

    public function register_columns($key, $type) {
        add_action("manage_posts_custom_column", array($this, "customColumnsValue"));
        add_filter("manage_edit-".$key."_columns", array($this, "customColumns"));
    }

    public function customColumns($key) {
        global $post;
        if (in_array($post->post_type, $this->registeredPostTypes)) {
            if (is_array($this->fields_info['fields'][$post->post_type]['admin_columns'])) {
                foreach($key as $k => $v) {
                    if (!in_array($k, $this->fields_info['fields'][$post->post_type]['admin_columns']))
                        unset($key[$k]);
                    }
            }

            if (isset($this->fields_info['fields'][$post->post_type])) {
                foreach($this->fields_info['fields'][$post->post_type] as $k => $value) {
                    if ($value['show_list']=='on') {
                        $key[$value['name']]=$value['name'];
                    }
                }
            }
        }
        return $key;
    }

    public function customColumnsValue($key) {
        global $post;
        if (in_array($post->post_type, $this->registeredPostTypes)) {
            foreach($this->fields_info['fields'][$post->post_type] as $k => $value) {
                if ($value['show_list']=='on') {
                    if ($key==$value['name']) {
                        $this->themeList($post,$value['field_name']);
                    }
                }
            }
        }
        return $key;
    }
   
    public function options_scripts() {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');
        wp_enqueue_script('custom-type1', $this->httpRoot . 'translations.js', array('postbox'));
        wp_enqueue_script('custom-type', $this->httpRoot . 'custom-types.js', array('postbox'));
        wp_enqueue_script('custom-type', $this->httpRoot . 'edit.js', array('postbox'));
    }
    
    public function edit_scripts() {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');
        wp_enqueue_script('custom-type', $this->httpRoot . 'custom-types.js', array('postbox'));
        wp_enqueue_script('custom-content-type-edit', $this->httpRoot . 'edit.js', array('postbox'));
        wp_enqueue_script('custom-content-type-trans', $this->httpRoot . 'translations.js', array('postbox'));
    }
    
    public function print_admin_styles() {
        wp_enqueue_style('jquery-ui', $this->httpRoot .'/lib/jquery-ui-theme/theme.css');
        
    }

    public function init() {     
        wp_enqueue_style('jquery-ui');
        wp_enqueue_style('custom-type-style', $this->httpRoot . 'style.css');
        do_action('ct_load_types',$this);
        $this->load_content_type();
        $this->registerContentTypes();
        
    }

    public function save_postdata( $post_id ) {
      // verify this came from the our screen and with proper authorization,
      // because save_post can be triggered at other times
      $nonce=$_POST['post_type'].'_nonce';
      if ( !wp_verify_nonce( $_POST[$nonce], plugin_basename(__FILE__) )) {
        return $post_id;
      }

      // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
      // to do anything
      if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;


      // Check permissions
      if ( 'page' == $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id ) )
          return $post_id;
      } else {
        if ( !current_user_can( 'edit_post', $post_id ) )
          return $post_id;
      }

      // OK, we're authenticated: we need to find and save the data

      if ($this->fields_info['fields'][$_POST['post_type']]) {
          foreach($this->fields_info['fields'][$_POST['post_type']] as $field) {
              $fieldType=$this->getFieldType($field['type']);
              if ($fieldType!=null)
                  $fieldType->save_fields(array('value'=>$_POST[$field['field_name']], 'fields' => $field, 'postid' =>$post_id, 'original'=>$_POST));
          }
      }
    }

    private function getFieldType($type) {
        foreach($this->types as $types) {
            if ($types->getId()==$type)
                return $types;
        }
        return null;
    }
    
    
    /**
     * This is called to setup the custom meta boxes for a custom post type edit page
     *
     * This is used to add the custom fields to the post edit page.
     * The edit page is found out [WP SITE URL]/wp-admin/post-new.php?post_type=[POST TYPE KEY]
     *
     * This is passed as a callback for when the post type is created.
     */
    public function init_custom_meta_boxes_for_post_edit($args) {
      $post_type_key = $args->post_type;
      $name = $this->fields_info['types'][$post_type_key]['singular_label'];
      
      $this->load_content_type();
      // check if the post type exists or has fields
      if ($this->fields_info['fields'][$post_type_key]) {
        add_meta_box('setcustommeta', $name.__(' fields','cct'), array($this, 'custom_fields_meta_box_for_post_edit'), $post_type_key, 'normal', 'core');
      }
    }
    
    
    
    
    public function custom_fields_meta_box_for_post_edit($post) {
        do_action('ct_load_types',$this);
        
        $rows = array();
        $nonce=$post->post_type.'_nonce';

        echo '<input type="hidden" name="'.$nonce.'" id="id_'.$nonce.'" value="' .
        wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
         
        $index=0;
        foreach($this->fields_info['fields'][$post->post_type] as $field) {
          
          if ($field['type']=='_fieldset') {
              // CODE FOR FIELDSET GROUP
              if ($index>0)
                $rows[$field['type'].$index++]['content']='</fieldset><fieldset class="'.$post->post_type.' group-'.$index.'">';
              else
                $rows[$field['type'].$index++]['content']='<fieldset class="'.$post->post_type.' group-'.$index.'">';
          }
          $fieldType=$this->getFieldType($field['type']);
          if ($fieldType!=null) {
            $value=$fieldType->load_fields(array('fields' => $field, 'postid' =>$post->ID));
            $field['value']=$value;
            ob_start();
            $fieldType->theme_input($field);
            $rows[$field['field_name']]['content'] = ob_get_clean();
            $rows[$field['field_name']]['type'] = $field['type'];
            $rows[$field['field_name']]['row'] = $field;
          }
        }
        if ($index>0) {
            $rows['_fieldset'.$index++]['content']='</fieldset>';
        }
        $path=TEMPLATEPATH.'/cct-theme-admin-'.$post->post_type.'.php';
        if (file_exists($path))
            include($path);
        else
            include(PLUGIN_DIR . '/cct-theme-admin.php');
      }
      
    
    
    /**
     * Add post type edit page meta boxes
     *
     * This add the various meta boxes to the post type edit page.
     */
    public function add_meta_boxes()
    {
        // Primary options
        add_meta_box('content-type-fields', __('Fields','cct'), array($this, 'box_fields'), 'content-type', 'normal', 'high' );
        add_meta_box('content-type-labels', __('Labels','cct'), array($this, 'box_labels'), 'content-type', 'normal', 'high' );
        add_meta_box('content-type-category-sets', __('Category Sets','cct'), array($this, 'box_category_sets'), 'content-type', 'normal', 'high' );
        
        // Advance options
        add_meta_box('content-type-admin-interface', __('Admin interface','cct'), array($this, 'box_admin_interface'), 'content-type', 'advance' );
        add_meta_box('content-type-public-visibility', __('Public visibility','cct'), array($this, 'box_public_visibility'), 'content-type', 'advance' );
        add_meta_box('content-type-permissions', __('Permissions','cct'), array($this, 'box_permissions'), 'content-type', 'advance' );
        
        // Right sidebar
        add_meta_box('content-type-save', __('Save','cct'), array($this, 'box_save'), 'content-type', 'side', 'high' );
        add_meta_box('content-type-statistics', __('Statistics','cct'), array($this, 'box_statistics'), 'content-type', 'side' );
        add_meta_box('content-type-utilities', __('Utilities','cct'), array($this, 'box_utilities'), 'content-type', 'side' );
    }
    
    //
    // Callbacks for to create the meta boxes
    //
    public function box_category_sets($content) {
      include BOXES_TEMPLATES_DIR.'category-sets.php';
    }
    
    public function box_admin_interface($content) {
      include BOXES_TEMPLATES_DIR.'admin-interface.php';
    }
    
    public function box_public_visibility($content) {
      include BOXES_TEMPLATES_DIR.'public-visibility.php';
    }
    
    public function box_permissions($content) {
      include BOXES_TEMPLATES_DIR.'permissions.php';
    }
    
    public function box_save($content) {
      include BOXES_TEMPLATES_DIR.'save.php';
    }
    
    public function box_statistics($content) {
      include BOXES_TEMPLATES_DIR.'statistics.php';
    }
    
    public function box_utilities($content) {
      include BOXES_TEMPLATES_DIR.'utilities.php';
    }
    
    public function box_fields($content) {
      include BOXES_TEMPLATES_DIR.'fields.php';
    }

    public function box_labels($content) {
      include BOXES_TEMPLATES_DIR.'labels.php';
    }
    
    
    /**
     * The default settings for which edit meta boxes are hidden
     *
     */
    public function default_closed_boxes_contenttype($result, $option, $user)
    {
      if(empty($result)) {
        $result = array( 'content-type-permissions', 'content-type-public-visibility', 'content-type-admin-interface' );
      }
      return $result;
    }
     
    
    
    /**
     * Encode JSON objects
     *
     * A wrapper for JSON encode methods. Pass in the PHP array and get a string
     * in return that is formatted as JSON.
     *
     * @param $obj - the array to be encoded
     *
     * @return string that is formatted as JSON
     */
    public function json_encode($obj) {
      // Try to use native PHP 5.2+ json_encode
      // Switch back to JSON library included with Tiny MCE
      if(function_exists('json_encode')){
        return json_encode($obj);
      } else {
        require_once(ABSPATH."/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
        $json_obj = new Moxiecode_JSON();
        $json = $json_obj->encode($obj);
        return $json;
      }
    }
    
    
    /**
     *  Sanitize systems keys
     *
     * It will convert any string into a key. A key is a only composed of lowercase letters,
     * digits and underscores.
     *
     * @param $key
     * 
     * @return string that can be used as a key
     */
    public function sanitize_key($key){
      $key = strtolower($key);
      $key = preg_replace('/&.+?;/', '', $key); // Kill entities
      $key = preg_replace('/[^a-z0-9_]+/', '_', $key);
      return $key;
    }
  }

$ept_cf = new CustomFields();

function the_ept_field($name, $options=null) {
    global $post;
    if (!isset($post->ct_theme)) return;
    echo $post->ct_theme->theme($post, $name, $options);
}

function get_ept_field($name, $options=null) {
    global $post;
    if (!isset($post->ct_theme)) return;
    if (isset($options['raw']) && $options['raw']==true )
        return $post->ct_theme->theme($post, $name, $options);
    ob_start();
    $post->ct_theme->theme($post, $name, $options);
    return ob_get_clean();
}
?>
