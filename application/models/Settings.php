<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */

class Settings extends CI_Model
{
    protected $vars;
    public $client_idiom;
    public function get($var)
    {
        if(!$this->vars){
            $q = $this->db->get('settings');
            foreach ($q->result() as $r){
                $this->vars[$r->field] = $r->value;
            }
        }

        return isset($this->vars[$var]) ? $this->vars[$var] : null;
    }

    public function setTimeZone($timezone=null){
        if(is_null($timezone)){
            $timezone = $this->get('timezone');
        }
        if(in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL)))
        {
            date_default_timezone_set($timezone);
        }
    }

    public function clientLanguage()
    {
        $this->client_idiom = 'english';
        if($this->get('client_language') != $this->client_idiom){
            if(is_dir(APPPATH.'language/'.$this->get('client_language')))
            {
                $this->client_idiom =  $this->get('client_language');
            }
        }
    }
}