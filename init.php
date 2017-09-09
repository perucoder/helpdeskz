<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */
define('BASEPATH', __DIR__.DIRECTORY_SEPARATOR);


/*
 * Environment and error reporting
 */
define('ENVIRONMENT','development');
switch (ENVIRONMENT)
{
    case 'production':
        error_reporting(0);
        break;
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;
    case 'testing':
        error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        break;
}


/*
 *
 * -------------------------------------
 * Logs
 * -------------------------------------
 * 0 = Disable logging
 * 1 = Error Messages
 * 2 = Debug Messages
 * 3 = Error and Debug Messages
 * 4 = Error, Debug and Notice Messages
 */
define('LOGS', 4);


include __DIR__.DIRECTORY_SEPARATOR.'vendor/autoload.php';
$logger = new \Monolog\Logger('app_log');
$stream = new \Monolog\Handler\StreamHandler(__DIR__.DIRECTORY_SEPARATOR.'logs/app.log');
$logger->pushHandler($stream, \Monolog\Logger::WARNING);



function errorHandler($error_level, $error_message, $error_file, $error_line){
    global $logger;
    $context = ['File' => $error_file, 'Line' => $error_line];
    switch ($error_level)
    {
        case 8:
        case 1024:
            if(LOGS == 4){
                $logger->addNotice($error_message, $context);
            }
            if(ENVIRONMENT != 'production'){
                echo '<strong>Notice:</strong> '.$error_message.' in <strong>'.$error_file.'</strong> on line <strong>'.$error_line.'</strong>';
            }
            break;
        case 2:
        case 512:
            if(LOGS == 1 || LOGS == 3 || LOGS == 4){
                $logger->addWarning($error_message, $context);
            }
            if(ENVIRONMENT != 'production'){
                echo '<strong>Warning:</strong> '.$error_message.' in <strong>'.$error_file.'</strong> on line <strong>'.$error_line.'</strong>';
            }
            break;
        default:
            if(LOGS == 1 || LOGS == 3 || LOGS == 4){
                $logger->addError($error_message, $context);
            }
            if(ENVIRONMENT != 'production'){
                echo '<strong>Error:</strong> '.$error_message.' in <strong>'.$error_file.'</strong> on line <strong>'.$error_line.'</strong>';
            }
            break;
    }
}

function shutdownHandler()
{
    global $logger;
    if(LOGS == 1 || LOGS == 3 || LOGS == 4){
        $error = error_get_last();
        if(isset($error['message'])){
            $error_message = $error['message'];
            $context = ['File' => $error['file'], 'Line' => $error['line']];
            $logger->addCritical($error_message, $context);
            exit();
        }
    }
}
set_error_handler('errorHandler');
register_shutdown_function('shutdownHandler');

function log_message($type, $message){
    global $logger;
    switch ($type){
        case 'error':
            $logger->addError($message);
            break;
        case 'info':
            $logger->addInfo($message);
            break;
        case 'warning':
            $logger->addWarning($message);
            break;
        case 'debug':
            $logger->addDebug($message);
            if(ENVIRONMENT != 'production'){
                echo $message;
            }
            break;
    }
}

if ( ! function_exists('html_escape'))
{
    /**
     * Returns HTML escaped variable.
     *
     * @param	mixed	$var		The input string or array of strings to be escaped.
     * @param	bool	$double_encode	$double_encode set to FALSE prevents escaping twice.
     * @return	mixed			The escaped string or array of strings as a result.
     */
    function html_escape($var, $double_encode = TRUE)
    {
        if (empty($var))
        {
            return $var;
        }

        if (is_array($var))
        {
            foreach (array_keys($var) as $key)
            {
                $var[$key] = html_escape($var[$key], $double_encode);
            }

            return $var;
        }

        return htmlspecialchars($var, ENT_QUOTES, 'UTF-8', $double_encode);
    }
}