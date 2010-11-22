<?php
/*
Plugin Name: Easy Post Types - Check Box
Plugin URI:
Description: Custom Type Check Box
Author: New Signature
Version: 1.0.0
Author URI: http://newsignature.com/
*/

class CustomFields_CheckBox {

    public $mainContentType;
    public $root;
    public $httpRoot;

    public function getId() {
        return "checkbox";
    }

    public function __toString()
    {
        return "Easy Post Types : Checkbox Field";
    }

    public function  __construct() {
        $this->root=dirname(__FILE__).'/';
        $this->httpRoot = plugins_url( '', __FILE__).'/';

        load_plugin_textdomain('cct', false, dirname( plugin_basename( __FILE__ ) )  );
        add_action( 'widgets_init', array($this, 'init' ));
        add_action('ct_load_types', array($this, 'load_type'));
        $cssfile = TEMPLATEPATH. '/'.$this->getId().'-style.css';
        if (file_exists($cssfile))
            wp_enqueue_style($this->getId().'-custom-style', $cssfile);
        else
            wp_enqueue_style($this->getId().'-style', $this->root. 'style.css');
    }

    public function load_type($cf) {
        $cf->registerType($this);
        $this->mainContentType=$cf;
    }
    public function extra($post_values) {
        return array();
    }
    public function getRoot() {
        return $this->root;
    }
    public function getName() {
        return 'Check Box';
    }

    public function load_fields($fields) {
        return get_post_meta($fields['postid'], $fields['fields']['field_name'],true);
    }

    public function save_fields($fields) {
        update_post_meta($fields['postid'], $fields['fields']['field_name'], $fields['value']);
    }

    public function theme($post, $name, $options=null) {
        $value=$this->load_fields(array('postid'=>$postid, 'field_name'=>$name));
        if ($options['raw']==true) return $value[$name][0];

        $values=array('name'=>$name, 'value'=>$value[$name][0]);

        $template=$options['template'];
        $this->mainContentType->include_template($this, $name, $template, $values);
    }

    public function includeTemplate($name, $values=array()) {
        $path=TEMPLATEPATH.'/'.$name;
        if (file_exists($path))
            include($path);
        else
            include($this->root . $name);

    }

    public function themeList($post, $name) {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);

        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value[$name][0], 'extra' => $fieldInfo['extra']);

        $this->includeTemplate('theme-checkbox-list.php', $values);
    }

    public function theme_admin($values=array()) {
        $this->includeTemplate('theme-checkbox-admin.php', $values);
    }

    public function theme_input($values) {
        $this->includeTemplate('theme-checkbox-input.php', $values);
    }

    public function init() {
    }
}
$cf_checkbox= new CustomFields_CheckBox();