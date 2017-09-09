<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */

namespace HelpDeskZ\Components;


class Mailer
{
    private $mail;
    private $mail_subject;
    private $mail_content;

    public function __construct($data_mail){
        global $db, $settings;
        $db = Database::connect();
        $settings = array();
        $q = $db->get('settings');
        foreach ($q->result() as $r){
            $settings[$r->field] = $r->value;
        }
        $this->data = $data_mail;
        $this->smtp_hostname = $settings['smtp_hostname'];
        $this->smtp_port = $settings['smtp_port'];
        $this->smtp_ssl = $settings['smtp_ssl'];
        $this->smtp_username = $settings['smtp_username'];
        $this->smtp_password = $settings['smtp_password'];
        $this->maildata = $db->fetchRow("SELECT subject, message FROM emails WHERE id='{$this->data['id']}'");
        $this->company_name = $settings['site_name'];
        $this->helpdesk_url = $settings['site_url'];
        $this->setVars();
        $this->mail = new \PHPMailer();
        if($settings['smtp'] == 'yes'){
            $this->mail->IsSMTP();
            $this->mail->SMTPAuth		= true;
            $this->mail->SMTPSecure		= $this->smtp_ssl;
            $this->mail->Host 			= $this->smtp_hostname;
            $this->mail->Port			= $this->smtp_port;
            $this->mail->Username		= $this->smtp_username;
            $this->mail->Password		= $this->smtp_password;
        }
        $this->mail->SetFrom($settings['email_ticket'], $this->company_name);
        $this->mail->AddReplyTo($settings['email_ticket'], $this->company_name);
        $this->mail->AddAddress($this->data['to_mail'], isset($this->data['from']) ? $this->data['from'] : $this->data['to_mail']);
        $this->mail->Subject = $this->mail_subject;
        $this->mail->ContentType = 'text/plain';
        $this->mail->IsHTML(false);
        $this->mail->Body = $this->mail_content;
        $this->mail->CharSet = 'UTF-8';
        if(isset($this->data['attachement']) && $this->data['attachement'] == 1){
            foreach($this->data['attachement_files'] as $v){
                $this->mail->addAttachment(UPLOAD_DIR.$this->data['attachement_type'].'/'.$v['enc'], $v['name']);
            }
        }
        if(!$this->mail->Send()) {
            $data = array('error' => 'Error sending email: '.$this->mail->ErrorInfo);
            $db->insert("error_log", $data);
        }
    }
    function setVars(){
        $vars = array_merge($this->data['vars'], array('%company_name%' => $this->company_name, '%helpdesk_url%' => $this->helpdesk_url));
        $this->mail_subject = str_replace(array_keys($vars), array_values($vars), $this->maildata['subject']);
        $this->mail_content = str_replace(array_keys($vars), array_values($vars), $this->maildata['message']);
    }
}