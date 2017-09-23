<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */

class Install extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_Installer();
    }

    public function index()
    {
        $this->load->view('installer/welcome');
    }

    public function install()
    {
        if($config = $this->_appConfig()){
            return $this->_InstallerFinal();
        }

        if($this->input->method() == 'post'){
            return $this->_installerStep1();
        }

        $this->load->view('installer/installerAgreement');
    }

    private function _installerStep1()
    {
        $data = array(
            'php' => array(
                'text' => 'Requires PHP 5.6+',
                'check' => 1
            ),
            'mysqli' => array(
                'text' => 'MySQLi extension',
                'check' => 1
            ),
            'writable1' => array(
                'text' => 'application/config/app_config.php is writable',
                'check' => 1
            ),
            'writable2' => array(
                'text' => 'application/upload is writable',
                'check' => 1
            )
        );
        $requirements = true;
        //Check PHP
        if ( function_exists('version_compare') && version_compare(PHP_VERSION,'5.6','<') ){
            $data['php']['check'] = 0;
            $requirements = false;
        }

        //Check MySQLi
        if ( ! function_exists('mysqli_connect') ){
            $data['mysqli']['check'] = 0;
            $requirements = false;
        }

        //Check writable
        if(!is_really_writable(APPPATH.'config/app_config.php')){
            $data['writable1']['check'] = 0;
            $requirements = false;
        }
        if(!is_really_writable(APPPATH.'upload')){
            $data['writable2']['check'] = 0;
            $requirements = false;
        }

        if($this->input->post('agreement') == 1){
            if($requirements !== true){
                $error_msg = 'Server does not meet script requirements';
            }else{
                $path_1 = 'upload/tickets';
                $path_2 = 'upload/articles';
                if(!is_dir(APPPATH.$path_1)){
                    mkdir(APPPATH.$path_1);
                }else{
                    if(!is_really_writable(APPPATH.$path_1)){
                        $error_msg = 'application/'.$path_1.' is not writable';
                    }
                }
                if(!is_dir(APPPATH.$path_2)){
                    mkdir(APPPATH.$path_2);
                }else{
                    if(!is_really_writable(APPPATH.$path_2)){
                        $error_msg = 'application/'.$path_2.' is not writable';
                    }
                }
                if(!isset($error_msg)){
                    return $this->_installerStep2();
                }

            }
        }
        $this->load->view('installer/installerRequirements', [
            'check' => $data,
            'requirements' => $requirements,
            'error_msg' => isset($error_msg) ? $error_msg : null,
        ]);
    }

    private function _installerStep2()
    {
        if($this->input->post('admin') == 1){
            $this->form_validation->set_rules('name','Full Name', 'required');
            $this->form_validation->set_rules('email','Email Address','required|valid_email');
            $this->form_validation->set_rules('username','Username','required|alpha_dash');
            $this->form_validation->set_rules('password','Password','required|min_length[5]');
            $this->form_validation->set_rules('site_name','Help Desk Name','required');
            $this->form_validation->set_rules('site_url','Help Desk URL','required|valid_url');
            if($this->form_validation->run() == FALSE){
                $error_msg = validation_errors();
            }else{
                return $this->_installerStep3();
            }
        }
        $this->load->view('installer/installerAdmin',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
        ]);
    }

    private function _installerStep3()
    {
        if($this->input->post('database') == 1){
            $this->form_validation->set_rules('db_host','Database Host', 'required');
            $this->form_validation->set_rules('db_name','Database Name', 'required');
            $this->form_validation->set_rules('db_user','Database Username', 'required');
            $this->form_validation->set_rules('db_prefix','Table Prefix', 'alpha_dash');
            if($this->form_validation->run() == FALSE){
                $error_msg = validation_errors();
            }else{
                $params = array(
                    'hostname' => $this->input->post('db_host'),
                    'username' => $this->input->post('db_user'),
                    'password' => $this->input->post('db_password'),
                    'database' => $this->input->post('db_name'),
                    'dbdriver' => 'mysqli',
                    'dbprefix' => $this->input->post('db_prefix'),
                    'pconnect' => FALSE,
                    'db_debug' => FALSE,
                    'cache_on' => FALSE,
                    'char_set' => 'utf8',
                    'dbcollat' => 'utf8_general_ci',
                    'encrypt' => FALSE,
                    'compress' => FALSE,
                    'stricton' => FALSE,
                    'failover' => array(),
                    'save_queries' => TRUE
                );
                @$this->load->database($params);
                $error = $this->db->error();
                if($error['code'] != 0){
                    $error_msg = '<p class="font-weight-bold">Database Error</p>'.$error['message'];
                }else{


                    $this->load->library('encryption');
                    $key = $this->encryption->create_key(16);
                    $config = [
                        'db_host' => $this->input->post('db_host'),
                        'db_username' => $this->input->post('db_user'),
                        'db_password' => $this->input->post('db_password'),
                        'db_name' => $this->input->post('db_name'),
                        'db_prefix' => $this->input->post('db_prefix'),
                        'key' => base64_encode($key),
                    ];
                    $data = "<?php\n";
                    foreach ($config as $k => $v){
                        $data .= "\$config['$k'] = '$v';\n";
                    }
                    $this->load->helper('file');
                    write_file(APPPATH.'config/app_config.php', $data);

                    $sql = $this->_installDB();
                    $sql = str_replace('`di_', '`'.$this->input->post('db_prefix'), $sql);
                    $sql = explode("\n", $sql);
                    $tempLine = '';


                    $header = $this->load->view('installer/header', array(), true);
                    echo $header;
                    echo '<h4 class="mb-3">Installing</h4><p>';
                    ob_end_flush();
                    ob_start();
                    foreach ($sql as $line)
                    {
                        if (substr($line, 0, 2) == '--' || $line == ''){
                            continue;
                        }

                        $tempLine .= $line;

                        if (substr(trim($line), -1, 1) == ';')
                        {
                            $this->db->query($tempLine);
                            $tempLine = '';
                        }
                        echo ' . ';
                        ob_flush();
                        flush();
                    }
                    $this->db->set('value', html_escape($this->input->post('site_name')))
                        ->where('field', 'site_name')
                        ->or_where('field', 'windows_title')
                        ->update('settings');

                    $this->db->set('value', html_escape($this->input->post('site_url')))
                        ->where('field','site_url')
                        ->update('settings');

                    $this->db->set('value', DESKIGNITER_VERSION)
                        ->where('field','script_version')
                        ->update('settings');

                    $this->db->set('username', html_escape($this->input->post('username')))
                        ->set('password', sha1($this->input->post('password')))
                        ->set('fullname', html_escape($this->input->post('name')))
                        ->set('email', html_escape($this->input->post('email')))
                        ->where('id', 1)
                        ->update('staff');
                    echo '</p>';
                    echo 'Done!';
                    ob_flush();
                    flush();
                    $this->session->set_flashdata('installer',1);
                    sleep(1);
                    echo '<script>location.href = "'.site_url('install/install').'";</script>';
                    exit;
                }
            }
        }
        $this->load->view('installer/installerDatabase',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
        ]);
    }

    public function _InstallerFinal()
    {
        $this->load->view('installer/installerCompleted');
    }


    private function _installDB()
    {
        return <<<SQL
CREATE TABLE `di_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `content` mediumtext,
  `category` int(11) DEFAULT '0',
  `author` varchar(250) NOT NULL,
  `date` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `public` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_attachments` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `enc` varchar(200) NOT NULL,
  `filetype` varchar(200) DEFAULT NULL,
  `article_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `msg_id` int(11) NOT NULL DEFAULT '0',
  `filesize` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_canned_response` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` mediumtext,
  `position` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_custom_fields` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(250) NOT NULL,
  `value` mediumtext,
  `required` int(1) NOT NULL DEFAULT '0',
  `display` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_departments` (
  `id` int(11) NOT NULL,
  `dep_order` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `type` int(2) NOT NULL DEFAULT '0',
  `autoassign` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_emails` (
  `id` varchar(255) NOT NULL,
  `orderlist` smallint(2) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_file_types` (
  `id` int(11) NOT NULL,
  `type` varchar(10) DEFAULT NULL,
  `size` varchar(100) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_knowledgebase_category` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `position` int(11) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `public` int(2) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_login_attempt` (
  `ip` varchar(200) NOT NULL,
  `attempts` int(2) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_login_log` (
  `id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(100) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `agent` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` mediumtext,
  `author` varchar(250) NOT NULL,
  `date` int(11) NOT NULL,
  `public` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_pages` (
  `id` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_priority` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(10) NOT NULL DEFAULT '#000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_settings` (
  `field` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_staff` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `login` int(11) NOT NULL DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `department` mediumtext,
  `timezone` varchar(255) DEFAULT NULL,
  `signature` longtext,
  `newticket_notification` smallint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(200) DEFAULT NULL,
  `admin` int(1) NOT NULL DEFAULT '0',
  `status` enum('Enable','Disable') NOT NULL DEFAULT 'Enable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_tickets` (
  `id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `department_id` int(11) NOT NULL DEFAULT '0',
  `priority_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `api_fields` mediumtext,
  `date` int(11) NOT NULL DEFAULT '0',
  `last_update` int(11) NOT NULL DEFAULT '0',
  `status` smallint(2) NOT NULL DEFAULT '1',
  `previewcode` varchar(12) DEFAULT NULL,
  `replies` int(11) NOT NULL DEFAULT '0',
  `last_replier` varchar(255) DEFAULT NULL,
  `custom_vars` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_tickets_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  `customer` int(2) NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `message` mediumtext,
  `ip` varchar(255) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `di_users` (
  `id` int(11) NOT NULL,
  `salutation` int(1) NOT NULL DEFAULT '0',
  `fullname` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` varchar(150) NOT NULL,
  `timezone` varchar(200) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `di_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`);

ALTER TABLE `di_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `msg_id` (`msg_id`);

ALTER TABLE `di_canned_response`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_custom_fields`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_departments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_emails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_file_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_knowledgebase_category`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_login_attempt`
  ADD UNIQUE KEY `ip` (`ip`);

ALTER TABLE `di_login_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`date`),
  ADD KEY `staff_id` (`staff_id`);

ALTER TABLE `di_news`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_pages`
  ADD UNIQUE KEY `home` (`id`);

ALTER TABLE `di_priority`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_settings`
  ADD UNIQUE KEY `field` (`field`);

ALTER TABLE `di_staff`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `di_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`);

ALTER TABLE `di_tickets_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

ALTER TABLE `di_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);


ALTER TABLE `di_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_canned_response`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_custom_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_file_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_knowledgebase_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_login_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_priority`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_tickets_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `di_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `di_departments` (`id`, `dep_order`, `name`, `type`, `autoassign`) VALUES(1, 1, 'General', 0, 1);

INSERT INTO `di_emails` (`id`, `orderlist`, `name`, `subject`, `message`) VALUES
('staff_reply', 5, 'Staff Reply', '[#%ticket_id%] %ticket_subject%', '%message%\n\n\nTicket Details\n---------------\n\nTicket ID: %ticket_id%\nDepartment: %ticket_department%\nStatus: %ticket_status%\nPriority: %ticket_priority%\n\n\nHelpdesk: %helpdesk_url%'),
('autoresponse', 4, 'New Message Autoresponse', '[#%ticket_id%] %ticket_subject%', 'Dear %client_name%,\n\nYour reply to support request #%ticket_id% has been noted.\n\n\nTicket Details\n---------------\n\nTicket ID: %ticket_id%\nDepartment: %ticket_department%\nStatus: %ticket_status%\nPriority: %ticket_priority%\n\n\nHelpdesk: %helpdesk_url%'),
('new_ticket', 3, 'New ticket creation', '[#%ticket_id%] %ticket_subject%', 'Dear %client_name%,\n\nThank you for contacting us. This is an automated response confirming the receipt of your ticket. One of our agents will get back to you as soon as possible. For your records, the details of the ticket are listed below. When replying, please make sure that the ticket ID is kept in the subject line to ensure that your replies are tracked appropriately.\n\n		Ticket ID: %ticket_id%\n		Subject: %ticket_subject%\n		Department: %ticket_department%\n		Status: %ticket_status%\n                Priority: %ticket_priority%\n\n\nYou can check the status of or reply to this ticket online at: %helpdesk_url%\n\nRegards,\n%company_name%'),
('new_user', 1, 'Welcome email registration', 'Welcome to %company_name% helpdesk', 'This email is confirmation that you are now registered at our helpdesk.\n\nRegistered email: %client_email%\nPassword: %client_password%\n\nYou can visit the helpdesk to browse articles and contact us at any time: %helpdesk_url%\n\nThank you for registering!\n\n%company_name%\nHelpdesk: %helpdesk_url%'),
('lost_password', 2, 'Lost password confirmation', 'Lost password request for %company_name% helpdesk', 'We have received a request to reset your account password for the %company_name% helpdesk (%helpdesk_url%).\n\nYour new passsword is: %client_password%\n\nThank you,\n\n\n%company_name%\nHelpdesk: %helpdesk_url%'),
('staff_ticketnotification', 6, 'New ticket notification to staff', 'New ticket notification', 'Dear %staff_name%,\r\n\r\nA new ticket has been created in department assigned for you, please login to staff panel to answer it.\r\n\r\n\r\nTicket Details\r\n---------------\r\n\r\nTicket ID: %ticket_id%\r\nDepartment: %ticket_department%\r\nStatus: %ticket_status%\r\nPriority: %ticket_priority%\r\n\r\n\r\nHelpdesk: %helpdesk_url%');

INSERT INTO `di_file_types` (`id`, `type`, `size`) VALUES
(1, 'gif', '0'),
(2, 'png', '0'),
(3, 'jpeg', '0'),
(4, 'jpg', '0'),
(5, 'ico', '0'),
(6, 'doc', '0'),
(7, 'docx', '0'),
(8, 'xls', '0'),
(9, 'xlsx', '0'),
(10, 'ppt', '0'),
(11, 'pptx', '0'),
(12, 'txt', '0'),
(13, 'htm', '0'),
(14, 'html', '0'),
(15, 'php', '0'),
(16, 'zip', '0'),
(17, 'rar', '0'),
(18, 'pdf', '0');

INSERT INTO `di_pages` (`id`, `title`, `content`) VALUES
('home', 'Welcome to the support & center', '<div class=\"introductory_display_texts\">\r\n<table style=\"height: 38px;\" width=\"100%\" cellspacing=\"4\">\r\n<tbody>\r\n<tr>\r\n<td style=\"vertical-align: top;\">\r\n<p><strong>New to HelpDeskZ?</strong></p>\r\n<ul>\r\n<li>If you are a customer, then you can login to our support center using the same login details that you use in your client panel.</li>\r\n<li>If you are <strong>not</strong> a customer, then you can submit a ticket, after this process you will receive a password to login to our support center.</li>\r\n</ul>\r\n</td>\r\n<td style=\"width: 50%; vertical-align: top;\">\r\n<p><strong>Do you need help?</strong></p>\r\n<ul>\r\n<li>Visit our knowledgebase at <a title=\"knowledgebase\" href=\"knowledgebase\">yoursite.com/knowledgebase</a></li>\r\n<li>Submit a&nbsp;<a href=\"submit_ticket\">support ticket</a> in English or Spanish.</li>\r\n</ul>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>');

INSERT INTO `di_priority` (`id`, `name`, `color`) VALUES
(1, 'Low', '#8A8A8A'),
(2, 'Medium', '#000000'),
(3, 'High', '#F07D18'),
(4, 'Urgent', '#E826C6'),
(5, 'Emergency', '#E06161'),
(6, 'Critical', '#FF0000');

INSERT INTO `di_settings` (`field`, `value`) VALUES
('use_captcha', '1'),
('email_ticket', 'support@mysite.com'),
('site_name', 'HELPDESK_SITE_NAME'),
('site_url', 'HELPDESK_SITE_URL'),
('windows_title', 'HELPDESK_SITE_NAME'),
('script_version', 'HELPDESK_VERSION'),
('show_tickets', 'DESC'),
('ticket_reopen', '0'),
('tickets_page', '20'),
('timezone', 'America/Lima'),
('ticket_attachment', '1'),
('permalink', '0'),
('loginshare', '0'),
('loginshare_url', 'http://yoursite.com/loginshare/'),
('date_format', 'd F Y h:i a'),
('page_size', '25'),
('login_attempt', '3'),
('login_attempt_minutes', '5'),
('overdue_time', '72'),
('knowledgebase_columns', '2'),
('knowledgebase_articlesundercat', '2'),
('knowledgebase_articlemaxchar', '200'),
('knowledgebase_mostpopular', 'yes'),
('knowledgebase_mostpopulartotal', '4'),
('knowledgebase_newest', 'yes'),
('knowledgebase_newesttotal', '4'),
('knowledgebase', 'yes'),
('news', 'yes'),
('news_page', '4'),
('homepage', 'knowledgebase'),
('email_piping', 'yes'),
('smtp', 'no'),
('smtp_hostname', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_ssl', 'tls'),
('smtp_username', 'mail@gmail.com'),
('smtp_password', 'password'),
('tickets_replies', '10'),
('closeticket_time', '72'),
('client_language', 'english'),
('staff_language', 'english'),
('client_multilanguage', '0'),
('maintenance', '0'),
('facebookoauth', '0'),
('facebookappid', NULL),
('facebookappsecret', NULL),
('googleoauth', '0'),
('googleclientid', NULL),
('googleclientsecret', NULL),
('socialbuttonnews', '0'),
('socialbuttonkb', '0');

INSERT INTO `di_staff` (`id`, `username`, `password`, `fullname`, `email`, `login`, `last_login`, `department`, `timezone`, `signature`, `avatar`, `admin`, `status`) VALUES
(1, 'ADMIN_USERNAME', 'ADMIN_PASSWORD', 'ADMIN_NAME', 'ADMIN_EMAIL', 0, 0, 'a:1:{i:0;s:1:\"1\";}', '', 'Best regards,\r\nAdministrator', NULL, 1, 'Enable');
SQL;

    }
    public function upgrade()
    {
        if(!$this->load->is_loaded('database')){
            return $this->load->view('installer/error',['error_msg' => '<p>You can not upgrade the script because you did not install it before.</p><a class="btn btn-danger text-white" onclick="history.back();">Return</a> ']);
        }
    }
}