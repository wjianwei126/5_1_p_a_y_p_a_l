<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webbase extends CI_Controller {
  public $expirettl=array('5m'=>300,'15m'=>900,'30m'=>1800,'1h'=>3600,'3h'=>10800,'6h'=>21600,'9h'=>32400,'12h'=>43200,'1d'=>86400,'3d'=>253200,'5d'=>432000,'7d'=>604800);
  public $showimgapi = 'http://img.hacktea8.com/showpic.php?key=';
  protected $mem = '';
  protected $redis = '';
  public $viewData = array();
  protected $userInfo = array('uid'=>0,'uname'=>'','isvip'=>0,'isadmin'=>0);
  public $adminList = array(3);
  protected $_c = 'index'; 
  protected $_a = 'index'; 
  
  public function __construct(){
    parent::__construct();
    $this->load->library('memcache');
    $this->mem = &$this->memcache;
    $this->load->library('rediscache');
    $this->redis = &$this->rediscache;
    //解析UID
    $uid = getSynuserUid();
    if($uid){
      $uinfo = getSynuserInfo($uid);
      $this->userInfo['isadmin'] = $this->checkIsadmin();
    }
    //var_dump($uinfo);exit;
    //$this->userInfo = $this->usermodel->getUserInfo($uinfo);
    $this->_c = $this->uri->segment(1,'index');
    $this->_a = $this->uri->segment(2,'index');
    $c = isset($_GET['c'])?$_GET['c']:'';
    if($c){
       $this->_a = 'list' == $c ? 'lists' : 'topic';
    }
    $this->assign(array('domain'=>$this->config->item('domain'),
                'base_url'=>$this->config->item('base_url'),'css_url'=>$this->config->item('css_url'),
                'admin_email'=>$this->config->item('admin_email'),'errorimg'=>'/public/images/show404.jpg',
                'img_url'=>$this->config->item('img_url'),'js_url'=>$this->config->item('js_url'),
                'toptips'=>$this->config->item('toptips'),'web_title'=>$this->config->item('web_title')
                ,'version'=>20140109,'login_url'=>$this->config->item('login_url'),'uinfo'=>$this->userInfo
                ,'_c'=>$this->_c,'_a'=>$this->_a
    ));
  }
  
  protected function checkLogin(){
    if(isset($this->userInfo['uid']) &&$this->userInfo['uid']>0){
      return true;
    }else{
      return false;
    }
  }
  protected function checkIsadmin(){
    if(!$this->checkLogin()){
      redirect($this->config->item('login_url').$this->config->item('base_url'));
    }
    if(in_array($this->userInfo['groupid'],$this->adminList)){
      return true;
    }
    foreach($this->userInfo['groups'] as $gid){
      if(in_array($gid,$this->adminList)){
        return true;
      }
    }
      return false;
  }
  protected function assign($data){
    foreach($data as $key => $val){
      $this->viewData[$key] = $val;
    }
  }
  protected function view($view_file){
    $this->load->view('header', $this->viewData);
    $this->load->view($view_file);
    $this->load->view('footer');
  }
  public function _rewrite_list_url(&$list){
    foreach($list as &$v){
      $v['url'] = list_url($v['id'],0,1);
      if(isset($v['pid'])){
        $v['purl'] = list_url($v['pid'],0,1);
      }
    }
   
  }
  public function _rewrite_article_url(&$list){
    $list = isset($list[0]['id']) ? $list : array($list);
    foreach($list as &$v){
      $v['curl'] = list_url($v['cid'],0,1);
      $v['url'] = article_url($v['id']);
    }
  }
}
