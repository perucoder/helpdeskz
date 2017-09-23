<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->language('staff_login');
    }

    public function index()
    {
        $this->load->view('staff/login');
    }
}