<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ob_start();

class Social extends MX_Controller {
	
	private $pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
	private $replacement = "[email]";


	function __construct(){
	    parent::__construct();
	    $this->load->model('account_model','account',TRUE);
	    
	}

	function user_id(){
		return $this->session->userdata('user_id');
		//return 1;
	}

	

	function wordpress($social_id = false){
		$this->load->library('global/global_lib');
		$this->config->load('api_token');

		$token=""; 
		require_once(APPPATH.'libraries/auth/oauth/http.php');
		require_once(APPPATH.'libraries/auth/oauth/oauth_client.php');

		$client = new oauth_client_class;
		$client->debug = false;
		$client->debug_http = true;
		$client->server = 'Wordpress';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
			dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/wordpress';

		$client->client_id = $this->config->item('wordpress_client_id'); $application_line = __LINE__;
		$client->client_secret = $this->config->item('wordpress_client_secret');

		if(strlen($client->client_id) == 0
		|| strlen($client->client_secret) == 0)
			die('Please go to Wordpress Apps page http://developer.wordpress.com , '.
				'create an application, and in the line '.$application_line.
				' set the client_id to client id key and client_secret with client secret');

		/* API permissions
		 */
		$client->scope = 'global';
		if($success = $client->Initialize()){

			if($success = $client->Process())
			{
				if(strlen($client->access_token))
				{
					$token=$client->access_token;
					$success = $client->CallAPI(
						'https://public-api.wordpress.com/rest/v1/me/', 
						'GET', array(), array('FailOnAccessError'=>true), $user);
				}
			}
			$success = $client->Finalize($success);
		}

		if($client->exit) exit;

		if($success){
			
			print_r($client);

			echo "<hr>";

			print_r($user);
			
		}
		
	}


}

/* End of file users.php */
/* Location: ./application/modules/users/controllers/users.php */