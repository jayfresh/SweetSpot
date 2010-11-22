<?php
/*
Plugin Name: Easy Post Types - TextField
Plugin URI:
Description: Custom Type Textfield
Author: New Signature
Version: 1.0.0
Author URI: http://newsignature.com/
*/

define( 'CUSTOM_TEXTFIELD_TEMPLATEPATH', dirname(__FILE__));

class CustomFields_TextField {

    public $mainContentType;
    public $root;
    public $httpRoot;

    public function getId() {
        return "textfield";
    }

    public function __toString()
    {
        return "Easy Post Types : Text Field";
    }

    public function  __construct() {
        $this->root=dirname(__FILE__).'/';
        $this->httpRoot = plugins_url( '', __FILE__).'/';
        
        load_plugin_textdomain('cct', false, dirname( plugin_basename( __FILE__ ) )  );
        add_action( 'init', array($this, 'init' ));
        add_action('ct_load_types', array($this, 'load_type'));
        $cssfile = CUSTOM_TEXTFIELD_TEMPLATEPATH. '/'.$this->getId().'-style.css';
        if (file_exists($cssfile))
            wp_enqueue_style($this->getId().'-custom-style', $cssfile);
        else
            wp_enqueue_style($this->getId().'-style', $this->root. 'style.css');
    }

    public function extra($post_values) {
        return array(
            'show_label'=>$post_values['show_label'],
            'multiline'=>$post_values['multiline'],);
    }

    public function includeTemplate($name, $values=array()) {
        $path=CUSTOM_TEXTFIELD_TEMPLATEPATH.'/'.$name;
        if (file_exists($path))
            include($path);
        else
            include($this->root . $name);
    }


    public function load_type($cf) {
        $cf->registerType($this);
        $this->mainContentType=$cf;
    }
    public function getRoot() {
        return $this->root;
    }

    public function getName() {
        return 'Text Field';
    }

    public function load_fields($fields) {
        return get_post_meta($fields['postid'], $fields['fields']['field_name'],true);
    }

    public function save_fields($fields) {
        update_post_meta($fields['postid'], $fields['fields']['field_name'], $fields['value']);
    }

    public function theme($post, $name, $options=null)  {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        if ($options['raw']==true) return $value[$name][0];
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);
        
        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value[$name][0], 'extra' => $fieldInfo['extra']);

        $template=$options['template'];

        $this->mainContentType->include_template($this, $name, $template, $values);
    }

    public function themeList($post, $name) {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);

        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value[$name][0], 'extra' => $fieldInfo['extra']);

        $this->includeTemplate('theme-textfield-list.php', $values);
    }

    public function theme_admin($values) {
        $this->includeTemplate('theme-textfield-admin.php', $values);
    }

    public function theme_input($values) {
        $this->includeTemplate('theme-textfield-input.php', $values);
    }

    public function init() {
         
    }
}
$cf_textfield= new CustomFields_TextField();