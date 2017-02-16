<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends MX_Controller {

	public $data;
	
	function __construct()
	{
		parent::__construct();
		
		// Define
		@define('is_ajax', $this->input->is_ajax_request());

		
	}

	function user_id(){
	    return $this->session->userdata('user_id');
	    //return 1;
	}

	function _check_login(){
	    $this->load->library('auth/auth_lib');
	    $check = $this->auth_lib->check_authorize();
	      if(!$check['status']){
	        redirect('auth/access?callback='.urlencode($this->auth_lib->cur_page_url()));
	    }
	}

}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */