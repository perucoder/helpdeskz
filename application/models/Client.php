<?php
/**
 * @package DeskIgniter
 * @author: PeruCoder Dev Team
 * @Copyright (c) 2017, PeruCoder Dev Team - All rights reserved
 * @link http://deskigniter.com
 */

class Client extends CI_Model
{
    protected $is_online;
    protected $info;
    public function isOnline()
    {
        if($this->is_online){
            return $this->is_online;
        }
        $this->is_online = false;
        if($this->session->has_userdata('client_id') && $this->session->has_userdata('client_password'))
        {
            if(!$this->info = $this->findOne([
                'id' => $this->session->userdata('client_id'),
                'password' => $this->session->userdata('client_password')
            ])){
                return $this->logout();
            }
            //Set user timezone
            $this->settings->setTimeZone($this->info->timezone);
            $this->is_online = true;
        }
        return $this->is_online;
    }

    public function logout()
    {
        $this->session->unset_userdata($this->session->all_userdata());
        return $this->is_online = false;
    }

    public function findOne($where=array(), $return=true, $select='*')
    {
        $this->db->where($where);
        if($return === true){
            $q = $this->db->select($select)
                ->get('users');
            if($q->num_rows() == 0){
                return null;
            }
            return $q->row();
        }
        $r = $this->db->count_all_results('users');
        if($r == 0){
            return false;
        }
        return true;
    }
}