<?php
/**
 * Created by PhpStorm.
 * User: Andres
 * Date: 01/03/2017
 * Time: 04:57 PM
 */

namespace HelpDeskZ\Components;



use HelpDeskZ\Components\DBLibrary\Driver\Driver;

class Database
{
    static $instance;


    public function testConnection($data)
    {
        try{
            $c = new Driver($data);
            return true;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function connect($data=null)
    {
        if(!self::$instance)
        {
            if(!is_array($data)){
                $config = array();
                include BASEPATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.php';
                if(!isset($config['Database']))
                {
                    return null;
                }

                $data['hostname'] = $config['Database']['servername'];
                $data['username'] = $config['Database']['username'];
                $data['password'] = $config['Database']['password'];
                $data['database'] = $config['Database']['dbname'];
                $data['dbprefix'] = $config['Database']['tableprefix'];
            }
            self::$instance = new Driver($data);
        }
        return self::$instance;
    }


}