<?php
/*
Plugin Name: Easy Post Types - DateField
Plugin URI:
Description: Custom Type DateField
Author: New Signature
Version: 1.0.0
Author URI: http://newsignature.com/
*/
define( 'CT_SHORT_FORMAT', __('m/d/Y','cct'));
define( 'CUSTOM_DATEFIELD_TEMPLATEPATH', dirname(__FILE__));

class CustomFields_DateField {
    
    
    public $mainContentType;
    public $root;
    public $httpRoot;

    public $formats;

    public function getId() {
        return "datefield";
    }

    public function getRoot() {
        return $this->root;
    }

    public function __toString()
    {
        return "Easy Post Types : Date Field";
    }


    public function  __construct() {
        $this->root=dirname(__FILE__).'/';
        $this->httpRoot = plugins_url( '', __FILE__).'/';
        $this->formats = array (
            'Long'      => __('Y, F d, M','cct'),
            'Medium'    => __('D, m d Y', 'cct'),
            'Short'     => CT_SHORT_FORMAT
            );

        load_plugin_textdomain('cct', false, dirname( plugin_basename( __FILE__ ) )  );
        add_action( 'widgets_init', array($this, 'init' ));
        add_action('ct_load_types', array($this, 'load_type'));
        $cssfile = CUSTOM_DATEFIELD_TEMPLATEPATH. '/'.$this->getId().'-style.css';
        if (file_exists($cssfile))
            wp_enqueue_style($this->getId().'-custom-style', $cssfile);
        else
            wp_enqueue_style($this->getId().'-style', $this->root. 'style.css');
        $this->init();
    }

    public function extra($post_values) {
        return array(
            'show_label'            =>$post_values['show_label'],
            'date_format'           =>$post_values['date_format'],
            'custom_date_format'    =>$post_values['custom_date_format'],
            );
    }

    public function load_type($cf) {
        $cf->registerType($this);
        $this->mainContentType=$cf;
    }

    public function getName() {
        return 'Date Field';
    }

    public function load_fields($fields) {
        return get_post_meta($fields['postid'], $fields['fields']['field_name'],true);
    }

    public function save_fields($fields) {
        global $post;

        $dateInput = $fields['value'];

        /* get the info from the main content plugin */
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $fields['field_name']);
        if (empty($fields['fields']['extra']['custom_date_format'])) {
            $format=$this->formats[$fields['fields']['extra']['date_format']];
        } else {
            $format=$fields['fields']['extra']['custom_date_format'];
        }
        /* get the info from the field to parse the date */
        $timestamp = strtotime($dateInput);
        
        update_post_meta($fields['postid'], $fields['fields']['field_name'], $timestamp);
    }

    public function theme($post, $name, $options=null) {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        if ($options['raw']==true) return $value[$name][0];
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);
        
        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value[$name][0], 'extra' => $fieldInfo['extra']);

        if (empty($values['extra']['custom_date_format']))
            $format=$this->formats[$values['extra']['date_format']];
        else
            $format=$values['extra']['custom_date_format'];

        if (empty($values['value'])) {
            $values['value']=date($format);
        }else {
            $values['value']=date($format, $values['value']);
        }

        $template=$options['template'];
        $this->mainContentType->include_template($this, $name, $template, $values);
    }

    public function includeTemplate($name, $values) {
        $path=CUSTOM_DATEFIELD_TEMPLATEPATH.'/'.$name;
        if (file_exists($path))
            include($path);
        else
            include($this->root . $name);
    }

    public function themeList($post, $name) {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);

        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value[$name][0], 'extra' => $fieldInfo['extra']);

        if (empty($values['extra']['custom_date_format']))
            $format=$this->formats[$values['extra']['date_format']];
        else
            $format=$values['extra']['custom_date_format'];

        if (empty($values['value'])) {
            $values['value']=date($format);
        }else {
            $values['value']=date($format, $values['value']);
        }
        $this->includeTemplate('theme-datefield-list.php', $values);
    }

    public function theme_admin($values=array()) {
        $this->includeTemplate('theme-datefield-admin.php', $values);
    }

    public function theme_input($values) {
        $format=CT_SHORT_FORMAT;
        if (empty($values['value'])) {
            $values['value']=date($format);
        }else {
            $values['value']=date($format, $values['value']);
        }

        $this->includeTemplate('theme-datefield-input.php', $values);
    }

    public function init() {
        wp_enqueue_script('custom-datefield-ui', $this->httpRoot . 'ui.datepicker.js', array('jquery'));
        wp_enqueue_script('custom-datefield', $this->httpRoot . 'custom-datefield.js');
        wp_enqueue_style('custom-datefield-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');

    }
}
$cf_datefield= new CustomFields_DateField();