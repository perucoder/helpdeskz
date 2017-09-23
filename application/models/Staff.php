<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */

class Staff extends CI_Model
{
    private $is_online;
    public function isOnline()
    {
        return false;
    }
}