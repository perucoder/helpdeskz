<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */

namespace HelpDeskZ\Models;


use HelpDeskZ\Components\Mailer;

class EmailParser extends Model
{
    public function parse($senderName, $senderEmail, $subject='', $message, $attachments=null, $department_id=null)
    {
        if($subject == ''){
            return false;
        }

        if(preg_match ("/\#[[a-zA-Z0-9_]+\-[a-zA-Z0-9_]+\-[a-zA-Z0-9_]+\]/",$subject,$regs)) {
            $code=trim(preg_replace("/\[/", "", $regs[0]));
            $code=trim(preg_replace("/\]/", "", $code));
            $code=str_replace("#","",$code);
            $this->replyTicket($code, $senderEmail, $message, $attachments);
        }else{
            //New Ticket
            $department = null;
            if(is_numeric($department_id)){
                $q = $this->db->select('id, name')
                    ->where('id', $department_id)
                    ->get('departments');
                if($q->num_rows() > 0){
                    $department = $q->row();
                }
            }

            if(!$department){
                $department = $this->db->select('id, name')
                    ->where('autoassign',1)
                    ->limit(1)
                    ->get('departments')
                    ->row();
            }
            $this->newTicket($senderName, $senderEmail, $subject, $message, $attachments, $department);
        }
    }

    public function replyTicket($ticketCode, $email, $message, $attachments=null){
        $q = $this->db->select('t.id, t.status, t.fullname, t.code, t.subject, d.name as department, p.name as priority')
            ->where('t.email', $email)
            ->where('t.code', $ticketCode)
            ->from('tickets as t')
            ->join('departments as d','d.id=t.department_id')
            ->join('priority as p', 'p.id=t.priority_id')
            ->get();
        if($q->num_rows() == 0){
            return false;
        }
        $ticket = $q->row();
        $data = array(
            'ticket_id' => $ticket->id,
            'date' => time(),
            'message' => html_escape($message),
            'ip' => $email,
        );
        $this->db->insert("tickets_messages", $data);
        $message_id = $this->db->insert_id();
        if($ticket->status == 'Closed' || $ticket->status == 'Answered'){
            $status_name = 'Awaiting Reply';
            $this->db->set('status','Awaiting Reply');
        }else{
            $status_name = $ticket->status;
        }
        $this->db->set('last_update',time())
            ->set('replies','replies+1', false)
            ->set('last_replier', $ticket->fullname)
            ->where('id', $ticket->id)
            ->update('tickets');

        $this->saveAttachments($attachments, $ticket->id, $message_id);
        /* Mailer */
        $fullname = $ticket->fullname;
        $data_mail = array(
            'id' => 'autoresponse',
            'to' => $fullname,
            'to_mail' => $email,
            'vars' => array('%client_name%' => $ticket->fullname,
                '%client_email%' => $email,
                '%ticket_id%' => $ticket->code,
                '%ticket_subject%' => $ticket->subject,
                '%ticket_department%' => $ticket->department,
                '%ticket_status%' => $status_name,
                '%ticket_priority%' => $ticket->priority,
            ),
        );
        $mailer = new Mailer($data_mail);
    }


    public function newTicket($senderName, $senderEmail, $subject, $message, $attachments=null, $department){
        $user = $this->getUserInfo(html_escape($senderName), $senderEmail);
        $ticket_code = substr(strtoupper(sha1(time().$senderEmail)), 0, 11);
        $ticket_code = substr_replace($ticket_code, '-',3,0);
        $ticket_code = substr_replace($ticket_code, '-',7,0);
        $previewCode = substr((md5(time().$user->fullname)),2,12);

        $data = array(
            'code' => $ticket_code,
            'department_id' => $department->id,
            'priority_id' => 1,
            'user_id' => $user->id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'subject' => html_escape($subject),
            'date' => time(),
            'last_update' => time(),
            'previewcode' => $previewCode,
            'last_replier' => $user->fullname,
        );
        $this->db->insert('tickets', $data);
        $ticketID = $this->db->insert_id();
        $data = array(
            'ticket_id' => $ticketID,
            'date' => time(),
            'message' => html_escape($message),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'email' => $user->email,
        );
        $this->db->insert('tickets_messages', $data);
        $message_id = $this->db->insert_id();
        $this->saveAttachments($attachments, $ticketID, $message_id);
        /* Mailer */
        $data_mail = array(
            'id' => 'new_ticket',
            'to' => $user->fullname,
            'to_mail' => $user->email,
            'vars' => array('%client_name%' => $user->fullname,
                '%client_email%' => $user->email,
                '%ticket_id%' => $ticket_code,
                '%ticket_subject%' => $subject,
                '%ticket_department%' => $department->name,
                '%ticket_status%' => 'Open',
                '%ticket_priority%' => 'Low',
            ),
        );
        $mailer = new Mailer($data_mail);
    }

    public function getUserInfo($name, $email)
    {
        $q = $this->db->select('id, email, fullname')
            ->where('email', $email)
            ->get('users');
        if($q->num_rows() == 0){
            //New Users
            $user = array(
                'email' => $email,
                'fullname' => $name
            );
            $user_id = $this->registerUser($user);
            $user['id'] = $user_id;
            $user = (object) $user;
        }else{
            //User Registered
            $user = $q->row();
        }
        return $user;
    }

    public function registerUser($data){
        $password = substr((md5(time().$data['fullname'])),5,7);
        $data['password'] = sha1($password);
        $this->db->insert("users", $data);
        $user_id = $this->db->insert_id();

        /* Mailer */
        $data_mail = array(
            'id' => 'new_user',
            'to' => $data['fullname'],
            'to_mail' => $data['email'],
            'vars' => array('%client_name%' => $data['fullname'], '%client_email%' => $data['email'], '%client_password%' => $password),
        );
        $mailer = new Mailer($data_mail);
        return $user_id;
    }


    public function saveAttachments($attachments, $ticketID, $messageID)
    {
        if(is_array($attachments)){
            foreach($attachments as $attachment) {
                $filepath = UPLOAD_DIR.$attachment['filename'];
                // write the file to the directory you want to save it in
                @file_put_contents($filepath, $attachment['file']);
                $filesize = @filesize($filepath);
                if($filesize){
                    if($this->verifyAttachment(['name' => $attachment['filename'], 'size' => $filesize])){
                        $ext = pathinfo($attachment['filename'], PATHINFO_EXTENSION);
                        $filename_encoded = md5($attachment['filename'].time()).".".$ext;
                        $data = array(
                            'name' => $attachment['filename'],
                            'enc' => $filename_encoded,
                            'filesize' => $filesize,
                            'ticket_id' => $ticketID,
                            'msg_id' => $messageID,
                            'filetype' => $attachment['filetype']);
                        $this->db->insert("attachments", $data);
                        rename($filepath, UPLOAD_DIR.'tickets/'.$filename_encoded);
                    }else{
                        unlink($filepath);
                    }
                }
            }
        }
    }

    private function verifyAttachment($filename){
        $namepart = explode('.', $filename['name']);
        $totalparts = count($namepart)-1;
        $file_extension = $namepart[$totalparts];
        if(!ctype_alnum($file_extension)){
            return false;
        }
        $q = $this->db->select('size')
            ->where('type', $file_extension)
            ->get('file_types');
        if($q->num_rows() == 0){
            return false;
        }
        $filetype = $q->row();
        if($filename['size'] > $filetype->size && $filetype->size > 0){
            return false;
        }
        return true;
    }
}