#!/usr/bin/php -q
<?php
define('UPLOAD_DIR','uploads/');
define('MINUTES_CHECK',5);

include __DIR__.DIRECTORY_SEPARATOR.'init.php';
if ( ! function_exists('imap_open'))
{
    log_message('error', 'Imap function is not active in your server.');
    exit;
}

// A log file used to store start of IMAP fetching
$job_file = BASEPATH . 'cache/__imap-' . sha1(__FILE__) . '.txt';

// If the job file already exists, wait for the previous job to complete unless expired
if ( file_exists($job_file ) )
{
    // Get time when the active IMAP fetching started
    $last = intval( file_get_contents($job_file) );

    // Give a running process at least X minutes to finish
    if ( $last + MINUTES_CHECK * 60 > time() )
    {
        log_message('debug','Another IMAP fetching task is still in progress.');
        exit;
    }else{
        // Start the process (force)
        file_put_contents($job_file, time() );
    }
}
else
{
    // No job in progress, log when this one started
    file_put_contents($job_file, time() );
}

try{
    $mail = new \Zend\Mail\Storage\Imap([
        'host' => 'imap.gmail.com',
        'user' => 'username',
        'password' => 'password',
        'ssl' => 'SSL'
    ]);
}catch (Exception $exception){
    log_message('error', $exception);
    exit;
}


if($mail->countMessages(\Zend\Mail\Storage::FLAG_UNSEEN) == 0){
    log_message('debug','There is not new emails');
    exit;
}

foreach ($mail as $messageNum => $message)
{
    if($message->hasFlag(\Zend\Mail\Storage::FLAG_SEEN))
    {
        continue;
    }

    /*
     * Check if message is a fail notice
     */
    if (
        preg_match('/DELIVERY FAILURE/i', $message->subject) ||
        preg_match('/Undelivered Mail Returned to Sender/i', $message->subject) ||
        preg_match('/Delivery Status Notification \(Failure\)/i', $message->subject) ||
        preg_match('/Returned mail\: see transcript for details/i', $message->subject)
    )
    {
        exit;
    }

    $part = $message;
    $attachments = null;
    if($message->isMultipart())
    {
        $part = $message->getPart(1);
        for ($i=2;$i<=$message->countParts();$i++){
            $cpart = $message->getPart($i);
            $attachment['filename'] = $cpart->getHeaderField('Content-Type', 'name');
            $attachment['filetype'] = $cpart->getHeaderField('Content-Type');
            if($cpart->getHeaderField('Content-Transfer-Encoding') == 'base64'){
                $attachment['file'] = base64_decode($cpart->getContent());
            }else{
                $attachment['file'] = $cpart->getContent();
            }
            $attachments[] = $attachment;
        }
    }
    if (strtok($part->contentType, ';') == 'text/plain') {
        $content = $part->getContent();
    }else{
        $content = \Html2Text\Html2Text::convert($part->getContent());
    }

    $from = $message->from;
    $start = strpos($from,'<');
    $email = substr($from, $start, -1);
    $sender_email = trim(str_replace('<','',$email));
    $sender_name = trim(str_replace('<'.$sender_email.'>', '', $from));

    $parser = new \HelpDeskZ\Models\EmailParser();
    $parser->parse($sender_name, $sender_email, $message->subject, $content, $attachments);

    $mail->removeMessage($messageNum);

}
$mail->close();
unlink($job_file);