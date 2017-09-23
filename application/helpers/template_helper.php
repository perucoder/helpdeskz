<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */

function html_css($data){
    if(is_array($data)){
        $code = '';
        foreach ($data as $d){
            $code .= html_css($d);
        }
        return $code;
    }

    $url = $data;
    if(!filter_var($url, FILTER_VALIDATE_URL)){
        $url = base_url($url);
    }
    return '<link href="'.$url.'" type="text/css" rel="stylesheet" />';
}

function html_script($data){
    if(is_array($data)){
        $code = '';
        foreach ($data as $d){
            $code .= html_script($d);
        }
        return $code;
    }
    $url = $data;
    if(!filter_var($url, FILTER_VALIDATE_URL)){
        $url = base_url($url);
    }
    return '<script src="'.$url.'"></script>';
}

function html_input($vars=array())
{
    //Label
    $label = false;
    if(isset($vars['label'])){
        $html_label = '<label>'.$vars['label'].'</label>';
        unset($vars['label']);
        $label = true;
    }

    //Help
    $help = false;
    if(isset($vars['help'])){
        $help = true;
        $html_help = '<small class="form-text text-muted">'.$vars['help'].'</small>';
        unset($vars['help']);
    }



    //If type is not set
    if(!isset($vars['type'])){
        $vars['type'] = 'text';
    }

    //Class
    if(!isset($vars['class'])){
        $vars['class'] = 'form-control';
    }

    //Add another class
    if(isset($vars['add_class'])){
        $vars['class'] = $vars['class'] . ' '.$vars['add_class'];
        unset($vars['add_class']);
    }


    $html = '<input ';
    foreach ($vars as $k => $v){
        $html .= $k.'="'.$v.'" ';
    }
    $html .= '>';
    if($help){
        $html .= $html_help;
    }

    if($label){
        $html = '<div class="form-group">'.$html_label.$html.'</div>';
    }

    return $html;
}

