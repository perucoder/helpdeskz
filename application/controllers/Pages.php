<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_Client();
    }

    public function home()
	{
        $this->load->model('content');
        return $this->load->view('client/pages', ['content' => $this->content->load('home')]);
        if($this->settings->get('homepage') == 'knowledgebase' && $this->settings->get('knowledgebase') == 'yes'){
            return $this->kb();
        }elseif($this->settings->get('homepage') == 'news' && $this->settings->get('news') == 'yes'){
            return $this->news();
        }else{
            $this->load->model('content');
            $this->load->view('client/pages', ['content' => $this->content->load('home')]);
        }
		$this->load->view('welcome_message');
	}

	public function kb(){

    }

    public function news(){
        $q = $db->query("SELECT * FROM ".TABLE_PREFIX."news WHERE public=1 ORDER BY date DESC LIMIT 5");
        while($r = $db->fetch_array($q)){
            $r['url'] = getUrl('news',$r['id'],array(strtourl($r['title'])));
            $news[] = $r;
        }
        $template_vars['news'] = $news;
        $template_name = 'home_news.html';
    }
}
