<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */

class Content extends CI_Model
{
    public function load($page_id)
    {
        $q = $this->db->where('id', $page_id)
            ->get('pages');
        if($q->num_rows() == 0){
            return null;
        }else{
            return $q->row();
        }
    }
}