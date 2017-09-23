<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */
define('DESKIGNITER_VERSION', '1.0');
class MY_Controller extends CI_Controller
{
    protected function _Installer()
    {
        $this->config->set_item('base_url',"http://".$this->input->server('HTTP_HOST'));
        $this->load->library(['session','form_validation']);
        $this->load->helper(['url','template']);
    }

    protected function _Client()
    {
        $this->load->library(['session','form_validation']);
        $this->load->helper(['url','template','language']);
        $this->load->model(['settings','client']);
        if(!$config = $this->_appConfig()){
            $this->config->set_item('base_url',"http://".$this->input->server('HTTP_HOST'));
            redirect('install');
        }
        $db = array(
            'dsn'	=> '',
            'hostname' => $config['db_host'],
            'username' => $config['db_username'],
            'password' => $config['db_password'],
            'database' => $config['db_name'],
            'dbdriver' => 'mysqli',
            'dbprefix' => $config['db_prefix'],
            'pconnect' => FALSE,
            'db_debug' => (ENVIRONMENT !== 'production'),
            'cache_on' => FALSE,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            'encrypt' => FALSE,
            'compress' => FALSE,
            'stricton' => FALSE,
            'failover' => array(),
            'save_queries' => TRUE
        );
        $this->load->database($db);

        //Set default url
        $this->config->set_item('base_url', $this->settings->get('site_url'));

        //Set timezone
        $this->settings->setTimeZone();

        //Load language
        $this->settings->clientLanguage();

        $this->lang->load('global', $this->settings->client_idiom);

        //Initialize client and check if user is or is not online
        $this->client->isOnline();
    }

    protected function _appConfig()
    {
        if(file_exists(APPPATH.'config/app_config.php'))
        {
            ob_start();
            include APPPATH.'config/app_config.php';
            ob_end_clean();
            if(isset($config) && is_array($config)){
                return $config;
            }
        }
        return null;
    }

    protected function _maintenance()
    {
        $this->lang->load('maintenance', $this->settings->client_idiom);
        $this->load->view('client/maintenance');
    }

    /*
    public function _remap($method, $params = array()){
        $maintenance = false;
        if($this->uri->segment(1) != 'staff')
        {
            if($this->settings->get('maintenance') == 1){
                $this->load->model('staff');
                if(!$this->staff->isOnline()){
                    $maintenance = true;
                }
            }
        }

        if($maintenance === true){
            return $this->_maintenance();
        }else{
            if (method_exists($this, $method))
            {
                return call_user_func_array(array($this, $method), $params);
            }
            show_404();
        }
    }
    */
}