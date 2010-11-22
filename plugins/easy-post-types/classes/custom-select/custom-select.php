<?php
/*
Plugin Name: Easy Post Types - SelectField
Plugin URI:
Description: Custom Type SelectField
Author: New Signature
Version: 1.0.0
Author URI: http://newsignature.com/
*/

define( 'CUSTOM_SELECTFIELD_TEMPLATEPATH', dirname(__FILE__));

class CustomFields_SelectField {

    public $mainContentType;
    public $root;
    public $httpRoot;

    public function getId() {
        return "select";
    }

    public function __toString()
    {
        return "Easy Post Types : Select Field";
    }

    public function  __construct() {
        $this->root=dirname(__FILE__).'/';
        $this->httpRoot = plugins_url( '', __FILE__).'/';

        load_plugin_textdomain('cct', false, dirname( plugin_basename( __FILE__ ) )  );
        add_action( 'widgets_init', array($this, 'init' ));
        add_action('ct_load_types', array($this, 'load_type'));
        $cssfile = CUSTOM_SELECTFIELD_TEMPLATEPATH. '/'.$this->getId().'-style.css';
        if (file_exists($cssfile))
            wp_enqueue_style($this->getId().'-custom-style', $cssfile);
        else
            wp_enqueue_style($this->getId().'-style', $this->root. 'style.css');
    }

    public function extra($post_values) {
        return array(
            'show_label'    =>$post_values['show_label'],
            'select_values' =>$post_values['select_values'],
            'php_code'      =>$post_values['php_code']
            );
    }

    public function load_type($cf) {
        $cf->registerType($this);
        $this->mainContentType=$cf;
    }
    public function getRoot() {
        return $this->root;
    }

    public function getName() {
        return 'Select Field';
    }

    public function load_fields($fields) {
        return get_post_meta($fields['postid'], $fields['fields']['field_name'],true);
    }

    public function save_fields($fields) {
        update_post_meta($fields['postid'], $fields['fields']['field_name'], $fields['value']);
    }

    public function includeTemplate($name, $values=array()) {
        $path=CUSTOM_SELECTFIELD_TEMPLATEPATH.'/'.$name;
        if (file_exists($path))
            include($path);
        else
            include($this->root . $name);
    }


    public function theme($post, $name, $options=null) { 
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        if ($options['raw']==true) return $value[$name][0];
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);
        
        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value[$name][0], 'extra' => $fieldInfo['extra']);

        if (empty($fieldInfo['extra']['php_code'])) {
            $list = $this->prepareArray($fieldInfo['extra']['select_values'], $value[$name][0]);
        }
        else {
            $list = $this->prepareArrayCode($fieldInfo['extra']['php_code'], $value[$name][0]);
        }
        $values['value']=$list[$value[$name][0]]['value'];

        $template=$options['template'];
        $this->mainContentType->include_template($this, $name, $template, $values);
    }

    public function themeList($post, $name) {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);

        if (empty($fieldInfo['extra']['php_code'])) {
            $list = $this->prepareArray($fieldInfo['extra']['select_values'], $value[$name][0]);
        } else  {
            $list = $this->prepareArrayCode($fieldInfo['extra']['php_code'], $value[$name][0]);
        }
        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$list[$value[$name][0]], 'extra' => $fieldInfo['extra']);

        $this->includeTemplate('theme-select-list.php', $values);
    }

    public function theme_admin($values=array()) {
        $this->includeTemplate('theme-select-admin.php', $values);
    }

    public function prepareArray($input, $value) {
        $options=array();
        $items=split("\n", $input);
        foreach($items as $item) {
            $pieces=split("\|", $item);
            $options[$pieces[0]]=array('key'=>$pieces[0], 'value'=>$pieces[1], 'selected' => $value==$pieces[0]?"selected":"");
        }
        return $options;
    }

    public function prepareArrayCode($input, $value) {
        $options=array();
        $items=eval($input);
        foreach($items as $key => $item) {
            $options[$key]=array('key'=>$key, 'value'=>$item, 'selected' => $value==$key?"selected":"");
        }
        return $options;
    }

    public function theme_input($values) {
        global $post;
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $values['field_name']);
        
        if (empty($fieldInfo['extra']['php_code'])) {
            $options = $this->prepareArray($fieldInfo['extra']['select_values'], $values['value']);
        } else {
            $options = $this->prepareArrayCode($fieldInfo['extra']['php_code'], $values['value']);
        }
        $values['options']=$options;

        $this->includeTemplate('theme-select-input.php', $values);
    }

    public function init() {
         
    }
}
$cf_selectfield= new CustomFields_SelectField();