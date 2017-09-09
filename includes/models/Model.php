<?php
/**
 * @package HelpDeskZ
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link http://helpdeskz.com
 */

namespace HelpDeskZ\Models;


use HelpDeskZ\Components\Database;

class Model
{
    protected $db;
    public function __construct()
    {
        $this->db = Database::connect();
    }
}