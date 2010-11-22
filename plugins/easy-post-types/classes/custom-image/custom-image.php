<?php
/*
Plugin Name: Easy Post Types - Image Field
Plugin URI:
Description: Custom Type ImageField
Author: New Signature
Version: 1.0.0
Author URI: http://newsignature.com/
*/

include dirname(__FILE__)."/javascript.php";

define ( 'CUSTOM_IMAGEFIELD_TEMPLATEPATH', dirname(__FILE__));
define ( 'IMAGE_FIELD_NAME', '_imageField');

define ('IMAGE_FIELD_TITLE', '_title');
define ('IMAGE_FIELD_ALTERNATE', '_alternate');

define ('IMAGE_FIELD_THUMBNAIL_WIDTH', 80);
define ('IMAGE_FIELD_THUMBNAIL_HEIGHT', 80);

define ('IMAGE_FIELD_MEDIUM_WIDTH', 320);
define ('IMAGE_FIELD_MEDIUM_HEIGHT', 240);

class CustomFields_ImageField {

    public $mainContentType;
    public $root;
    public $httpRoot;

    public function getId() {
        return "imagefield";
    }

    public function __toString()
    {
        return "Easy Post Types : Image Field";
    }

    public function  __construct() {
        $this->root=dirname(__FILE__).'/';
        $this->httpRoot = plugins_url( '', __FILE__).'/';

        load_plugin_textdomain('cct', false, dirname( plugin_basename( __FILE__ ) )  );
        add_action( 'widgets_init', array($this, 'init' ));
        add_action('ct_load_types', array($this, 'load_type'));
        $cssfile = CUSTOM_IMAGEFIELD_TEMPLATEPATH. '/'.$this->getId().'-style.css';
        if (file_exists($cssfile))
            wp_enqueue_style($this->getId().'-custom-style', $cssfile);
        else
            wp_enqueue_style($this->getId().'-style', $this->root. 'style.css');

        add_action('wp_ajax_imgfield_remove_image', array($this,'ajax_removeImage'));
        add_action('wp_ajax_imgfield_add_image', array($this,'ajax_addImage'));
        add_action('admin_menu', array($this, 'addJs'));
        add_image_size('ept_thumbnail', IMAGE_FIELD_THUMBNAIL_WIDTH, IMAGE_FIELD_THUMBNAIL_HEIGHT, true);
        add_image_size('ept_medium', IMAGE_FIELD_MEDIUM_WIDTH, IMAGE_FIELD_MEDIUM_HEIGHT, true);
        add_image_size('ept_original', 0, 0, true);
    }

    public function addJs() {
        $js = new ImageField_Javascript();
        $js->create($this);
   }
   
    public function extra($post_values) {
        return array(
            'show_label'    => $post_values['show_label'],
            'icon_size'     => $post_values['icon_size'],
            'medium_size'   => $post_values['medium_size'],
            'crop'          => $post_values['crop'],
            'kwidth'        => $post_values['kwidth'],
            'kheight'       => $post_values['kheight'],
            );
    }

    public function createImageSize($name, $file, $ext, $uploadDir, $width, $height, $size, $extra) {
        global $_wp_additional_image_sizes;
        switch (strtolower($ext)) {
            case 'jpg' :
            case 'jpeg':
                $src_img=@imagecreatefromjpeg($name);
                break;
            case 'png' :
                $src_img=@imagecreatefrompng($name);
                break;
            case 'gif' :
                $src_img=@imagecreatefromgif($name);
                break;
        }

        if ($src_img===false) return false;
        $old_x=imageSX($src_img);
        $old_y=imageSY($src_img);

        if (isset($_wp_additional_image_sizes[$size])) {
            $width=$_wp_additional_image_sizes[$size]['width'];
            $height=$_wp_additional_image_sizes[$size]['height'];
            if (empty($width) ||$width==0 || empty($height) || $height==0) {
                $width=IMAGE_FIELD_THUMBNAIL_WIDTH;
                $height=IMAGE_FIELD_THUMBNAIL_HEIGHT;
                $size='ept_thumbnail';
            }
        }
        else {
            $width=IMAGE_FIELD_THUMBNAIL_WIDTH;
            $height=IMAGE_FIELD_THUMBNAIL_HEIGHT;
        }

        if ($size=='ept_original')
            $filename = $uploadDir['path'].'/'.$file.'.'.$ext;
        else
            $filename = $uploadDir['path'].'/'.$file.'_'.$size.'.'.$ext;

        if ($extra['crop']=='yes') {
            if ($extra['kwidth']=='yes') {
                $hw_ratio = $old_x/$old_y;
                $ratio = $width/$height;

                if ($hw_ratio>$ratio) {
                    $cropx = ($old_x - ($old_y *$ratio))/2;
                    $cropy=0;
                }elseif ($hw_ratio <$ratio) {
                    $cropx=0;
                    $cropy=($old_y -($old_x/$ratio))/2;
                }else {
                    $cropx=0;
                    $cropy=0;
                }
            }
            if ($extra['kheight']=='yes') {
                $hw_ratio = $old_y/$old_x;
                $ratio = $height/$width;

                if ($hw_ratio>$ratio) {
                    $cropx = ($old_x - ($old_y *$ratio))/2;
                    $cropy=0;
                }elseif ($hw_ratio <$ratio) {
                    $cropx=0;
                    $cropy=($old_y -($old_x/$ratio))/2;
                }else {
                    $cropx=0;
                    $cropy=0;
                }
            }
        } else {

            if (($extra['kwidth']=='yes' && $extra['kheight']=='yes') ||
                ($extra['kwidth']!='yes' && $extra['kheight']!='yes')) {
                $cropx=0;
                $cropy=0;
            }elseif ($extra['kwidth']=='yes') {
                $ratio=$old_x/$width;
                $height=$old_y/$ratio;
                $cropx=0;
                $cropy=0;
            }elseif ($extra['kheight']=='yes') {
                $ratio=$old_y/$height;
                $width=$old_x/$ratio;
                $cropx=0;
                $cropy=0;
            }

        }

        $dst_img=ImageCreateTrueColor($width,$height);
	imagecopyresampled($dst_img,$src_img,0,0,$cropx,$cropy,$width,$height,$old_x-2*$cropx,$old_y-2*$cropy);
        switch (strtolower($ext)) {
            case 'jpg' :
            case 'jpeg':
                imagejpeg($dst_img, $filename);
                break;
            case 'png' :
                imagepng($dst_img, $filename);
                break;
            case 'gif' :
                imagegif($dst_img, $filename);
                break;
        }
    }

    public function getImage($name, $field_name, $post_type, $size=null){
        global $_wp_additional_image_sizes;

        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post_type, $field_name);
        $uploadDir = wp_upload_dir();
        $parts=explode("/",$name);
        $file=$parts[sizeof($parts)-1];
        $fparts = split("\.", $file);
        $file=$fparts[0];
        $ext=$fparts[1];

        if ($size==null) {
            $size='ept_thumbnail';
        }

        if (isset($_wp_additional_image_sizes[$size])) {
            $width=$_wp_additional_image_sizes[$size]['width'];
            $height=$_wp_additional_image_sizes[$size]['height'];
            if (empty($width) || empty($height)) {
                $width=IMAGE_FIELD_THUMBNAIL_WIDTH;
                $height=IMAGE_FIELD_THUMBNAIL_HEIGHT;
                $size='ept_thumbnail';
            }
        }
        else {
            $width=IMAGE_FIELD_THUMBNAIL_WIDTH;
            $height=IMAGE_FIELD_THUMBNAIL_HEIGHT;
        }

        if ($size=='ept_original') {
            $filename = $uploadDir['url'].'/'.$file.'.'.$ext;
        }
        else {
            $filename = $uploadDir['url'].'/'.$file.'_'.$size.'.'.$ext;
        }

        if (file_exists($filename)) {
            return array('url' => $filename, 'html' => '<img src="'.$filename.'" />');
        }

        $res=$this->createImageSize($name, $file, $ext, $uploadDir, $width, $height, $size, $fieldInfo['extra']);
        if ($res===false)
            return array('url' => 'error_image', 'html' => __('Image Error', 'cct'));
        return array('url' => $filename, 'html' => '<img src="'.$filename.'" />');
    }

    public function ajax_removeImage() {
        global $post;
        $res = get_post_meta($_POST['postid'], IMAGE_FIELD_NAME, true);

        if (is_array($res))
            array_splice($res, $_POST['index'], 1);
        else
            $res=array();
        update_post_meta($_POST['postid'], IMAGE_FIELD_NAME, $res);
        $values['value']=$res;
        $values['postid']=$_POST['postid'];
        include "theme-image-listing.php";

        exit();
    }

    public function ajax_addImage() {
        global $post;
        $r=$this->load_fields($_POST);
        $result=array_merge($r, array (array(
                'value' => $_POST['image'],
                IMAGE_FIELD_TITLE => $_POST['title'],
                IMAGE_FIELD_ALTERNATE => $_POST['alt']
                )) );
        update_post_meta($_POST['postid'], IMAGE_FIELD_NAME, $result);
        $values['value']=$result;
        $values['posttype']=$_POST['posttype'];
        $values['postid']=$_POST['postid'];
        $values['extra']=$_POST['extra'];
        $values['field_name']=$_POST['field_name'];
        include "theme-image-listing.php";

        exit();
    }


    public function load_type($cf) {
        $cf->registerType($this);
        $this->mainContentType=$cf;
    }
    public function getRoot() {
        return $this->root;
    }

    public function getName() {
        return 'Image Field';
    }

    public function load_fields($fields) {
        $res = get_post_meta($fields['postid'], IMAGE_FIELD_NAME, true);
        if (!is_array($res))
            $res=array();
        return $res;
    }

    public function save_fields($fields) {
    }

    public function includeTemplate($name, $values=array()) {
        $path=CUSTOM_IMAGEFIELD_TEMPLATEPATH.'/'.$name;
        if (file_exists($path))
            include($path);
        else
            include($this->root . $name);
    }


    private function getImages($value, $name, $post_type) {
        $images = array();
        $imgs=get_intermediate_image_sizes();
        foreach($imgs as $key => $img) {
            $images[$img] = $this->getImage($value, $name, $key, $post_type);
        }
        return $images;
    }

    public function theme($post, $name, $options=null) { 
        global $post;
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);
        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>$value, 'extra' => $fieldInfo['extra']);
        $values['existing']=$this->load_fields(array('postid'=>$post->ID));
        $values['options']=$options;
        foreach($values['existing'] as $key=>$image) {
            $values['images'][]=array('value'=>$image, 'size'=> $this->getImages($image['value'], $name, $post->post_type));
        }

        if ($options['raw']===true) {
            return $values;
        }

        $template=$options['template'];
        $this->mainContentType->include_template($this, $name, $template, $values);
    }

    public function themeList($post, $name) {
        $value=$this->load_fields(array('postid'=>$post->ID, 'field_name'=>$name));
        $fieldInfo=$this->mainContentType->getFieldInfo($this->getId(), $post->post_type, $name);

        $values=array('label' => $fieldInfo['info']['name'], 'name'=>$name, 'value'=>count($value), 'extra' => $fieldInfo['extra']);
        $this->includeTemplate('theme-image-list.php', $values);
    }

    public function theme_admin($values=array()) {
        $this->includeTemplate('theme-image-admin.php', $values);
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
        $values['postid']=$post->ID;
        $values['existing']=$this->load_fields(array('postid'=>$post->ID));
        $this->includeTemplate('theme-image-input.php', $values);
    }

    public function init() {
         wp_enqueue_script('custom-imagefield', $this->httpRoot . 'custom-imagefield.js');
    }
}
$cf_imagefield= new CustomFields_ImageField();