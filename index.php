<?php
/**
 * Created by PhpStorm.
 * User: andres
 * Date: 6/09/17
 * Time: 21:55
 */


/*
 *
 * -------------------------------------
 * Environment and error reporting
 * -------------------------------------
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
$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__.DIRECTORY_SEPARATOR.'logs/app.log'), \Monolog\Logger::WARNING);



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