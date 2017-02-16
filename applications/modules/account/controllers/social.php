<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ob_start();

class Social extends MX_Controller {
	
	private $pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
	private $replacement = "[email]";


	function __construct(){
	    parent::__construct();
	    $this->load->model('account_model','account',TRUE);
	    $this->load->library('auth/auth_lib');
		
		$this->_check_login();
		$this->_load_language('account');
	}

	function _check_login(){
	    $this->load->library('auth/auth_lib');
	    $check = $this->auth_lib->check_authorize();
	      if(!$check['status']){
	        redirect('auth/access?callback='.urlencode($this->auth_lib->cur_page_url()));
	    }
	}

	function _load_language($module){
	    $expand = array('en'=>'english','id'=>'indonesia');
	    if($lang = $this->session->userdata('lang')){
	      $this->lang->load($module,$expand[$lang]);
	    }
	    else $this->lang->load($module,$expand['id']);
	}

	function user_id(){
		return $this->session->userdata('user_id');
		//return 1;
	}

	public function index(){
		$data = array(
			'twitter' => null,
			'instagram' => $this->account->get_instagram_account($this->user_id()),
			'blog' => $this->account->get_blog_account($this->user_id())
		);


		$template = modules::load('templates/define');
		$template->account($data,'account','index');
	}


	function kota($id){
		$this->db->like('nama',$id);
		$kota = $this->db->get('tbl_city',1)->row();
		return (@$kota->city_id)?$kota->city_id:"''";
	}
	function propinsi($id){
		$this->db->like('nama',$id);
		$propinsi = $this->db->get('tbl_province',1)->row();
		return (@$propinsi)?$propinsi->province_id:"''";
	}
	function negara($id){
		$this->db->like('nama',$id);
		$negara = $this->db->get('tbl_country',1)->row();
		return (@$negara)?$negara->country_id:"''";
	}


	function instagram($social_id = false){
		$this->load->library('global/global_lib');
		$this->config->load('api_token');

		$token=""; 
		require_once(APPPATH.'libraries/auth/oauth/http.php');
		require_once(APPPATH.'libraries/auth/oauth/oauth_client.php');

		$client = new oauth_client_class;
		$client->debug = false;
		$client->debug_http = true;
		$client->server = 'Instagram';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
			dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/instagram';

		$client->client_id = $this->config->item('instagram_client_id'); $application_line = __LINE__;
		$client->client_secret = $this->config->item('instagram_client_secret');

		if(strlen($client->client_id) == 0
		|| strlen($client->client_secret) == 0)
			die('Please go to Instagram Apps page http://instagram.com/developer/register/ , '.
				'create an application, and in the line '.$application_line.
				' set the client_id to client id key and client_secret with client secret');

		/* API permissions
		 */
		$client->scope = 'basic';
		if($success = $client->Initialize()){

			if($success = $client->Process())
			{
				if(strlen($client->access_token))
				{
					$token=$client->access_token;
					$success = $client->CallAPI(
						'https://api.instagram.com/v1/users/self/', 
						'GET', array(), array('FailOnAccessError'=>true), $user);
				}
			}
			$success = $client->Finalize($success);
		}

		if($client->exit) exit;

		if($success){
			/* CHECK IF INSTAGRAM ACCOUNT ALREADY EXIST */
			if($detail = $this->account->get_instagram_by_username($user->data->username)){

				if($detail->inf_id == $this->user_id()){ //User already has this instagram account 
					
					$data = array(
						'inf_ig_id_ig' 				=> $user->data->id,
						'inf_ig_username' 			=> $user->data->username,
						'inf_ig_display_name' 		=> $user->data->full_name,
						'inf_ig_description'		=> preg_replace($this->pattern, $this->replacement, $user->data->bio),
						'inf_ig_followers' 			=> $user->data->counts->followed_by,
						'inf_ig_photo_profile' 		=> $user->data->profile_picture,
						'inf_ig_access_token' 		=> $token,
						'inf_ig_status' 			=> true,
						'inf_ig_is_active_account'  => true,
					);

					if($detail->inf_ig_is_active_account == 2 || $detail->inf_ig_is_active_account == 1){ //only active and deleted status can be proceed
						$social_id = $this->session->userdata('instagram_id');
						if($social_id){ //reconnect account
							if($social_id == $detail->inf_ig_id){  //make sure they re-connect to the right account
								//update new data account
								$this->account->update_instagram_account($data,$detail->inf_ig_id,true); //true means will upadate clean table
								
								$this->session->set_flashdata('message',sprintf(lang('success_reconnect'),'Instagram',$user->data->username));

								$this->session->unset_userdata('instagram_id');
							}
							else{
								$this->session->set_flashdata('error',sprintf(lang('error_reconnect'),$user->data->username,'Instagram'));
							}
						}
						else{ //re-activate deleted account

							//update new data account
							$this->account->update_instagram_account($data,$detail->inf_ig_id,true); //true means will upadate clean table
							
							$this->session->set_flashdata('message',sprintf(lang('success_reconnect_deleted'),'Instagram',$user->data->username));
						}
					}
					else{
						$this->session->set_flashdata('error',sprintf(lang('suspended'),'Instagram','support@sociabuzz.com'));
					}	


				}
				else{
					$this->session->set_flashdata('error',sprintf(lang('registered_by_other'),'Instagram'));
				}

				redirect('account/instagram');
			}
			else{ // yups register this account instead
				if($user->data->id != ''){
					$person = $this->account->get_inf_by_id($this->user_id());

					$data = array(
						'inf_id' 					=> $this->user_id(),
						'inf_ig_id_ig' 				=> $user->data->id,
						'inf_ig_username' 			=> $user->data->username,
						'inf_ig_display_name' 		=> $user->data->full_name,
						'inf_ig_description'		=> preg_replace($this->pattern, $this->replacement, $user->data->bio),
						'inf_ig_followers' 			=> $user->data->counts->followed_by,
						'inf_ig_photo_profile' 		=> $user->data->profile_picture,
						'inf_ig_access_token' 		=> $token,
						'inf_ig_person_fullname' 	=> $person->inf_fullname,
						'inf_ig_person_gender'		=> $person->inf_gender,
						'inf_ig_person_birthday'	=> $person->inf_birthdate,
						'inf_ig_created_date_sb' 	=> $this->global_lib->datetime(),
						'inf_ig_status' 			=> true,
						'inf_ig_is_active_account' 	=> true,
					);

					if($this->account->create_new_inf_instagram($data)){
						//send email to admin
						$emailer = modules::load('notifications/emailer');
						$emailer->send_admin($data,'Pendaftaran akun instagram baru',false,'relation@sociabuzz.com');

						

						redirect('account/instagram');
					}
					else{
						$this->session->set_flashdata('error',sprintf(lang('social_reg_failed'),'Instagram'));
						redirect('account/instagram');
					}
				}
				else die('Error caught when trying to connect instagram.com!');
			}
			
		}
		else
		{
			$this->session->set_flashdata('error','Can not catch session right now.');
			redirect('account/instagram');
		}
	}

	function twitter($social_id = false){
		//if(!$this->input->get('denied')){
			$this->config->load('api_token');
			$this->load->library('global/global_lib');

		    require_once(APPPATH.'libraries/auth/oauth/http.php');
			require_once(APPPATH.'libraries/auth/oauth/oauth_client.php');

		    $client = new oauth_client_class;
		    $client->debug = false;
		    $client->debug_http = true;
		    $client->server = 'Twitter';
		    $client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
	        dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/twitter';

		    /*
		     * Uncomment the next line if you want to use
		     * the pin based authorization flow
		     */
		    // $client->redirect_uri = 'oob';

		    /*
		     * Was this script included defining the pin the
		     * user entered to authorize the API access?
		     */
		    if(defined('OAUTH_PIN'))
		        $client->pin = OAUTH_PIN;
		    //'F1V4WMqMYvlqse0SGtQDO1wdE'
		    //3WUcMplBbK4QwJPu7zglDtGKANjsg34Fn8EhdWRykrRPpiEGD3
		    $client->client_id = $this->config->item('twitter_consumer_token'); $application_line = __LINE__;
		    $client->client_secret = $this->config->item('twitter_consumer_secret');

		    if(strlen($client->client_id) == 0
		    || strlen($client->client_secret) == 0)
		        die('Please go to Twitter Apps page https://dev.twitter.com/apps/new , '.
		            'create an application, and in the line '.$application_line.
		            ' set the client_id to Consumer key and client_secret with Consumer secret. '.
		            'The Callback URL must be '.$client->redirect_uri.' If you want to post to '.
		            'the user timeline, make sure the application you create has write permissions');

		    if(($success = $client->Initialize()))
		    {
		        if(($success = $client->Process()))
		        {
		            if(strlen($client->access_token))
		            {
		                $success = $client->CallAPI(
		                    'https://api.twitter.com/1.1/account/verify_credentials.json', 
		                    'GET', array(), array('FailOnAccessError'=>true), $user);

		/*
						//make testing tweet for the first time they sign up

		                $values = array(
		                    'status'=>'Baru saja bergabung di sociabuzz.com :) (@sociabuzz.com) pic.twitter.com/GIyytfevwj'
		                );
		                $success = $client->CallAPI(
		                    'https://api.twitter.com/1.1/statuses/update.json', 
		                    'POST', $values, array('FailOnAccessError'=>true), $update);
		                if(!$success)
		                    error_log(print_r($update->errors[0]->code, 1));
		*/

		/* Tweet with an attached image
		                $success = $client->CallAPI(
		                    "https://api.twitter.com/1.1/statuses/update_with_media.json",
		                    'POST', array(
		                        'status'=>'This is a test tweet to evaluate the PHP OAuth API support to upload image files sent at '.strftime("%Y-%m-%d %H:%M:%S"),
		                        'media[]'=>'php-oauth.png'
		                    ),array(
		                        'FailOnAccessError'=>true,
		                        'Files'=>array(
		                            'media[]'=>array(
		                            )
		                        )
		                    ), $upload);
		*/
		            }
		        }
		        $success = $client->Finalize($success);
		    }

		    if($client->exit)
		        exit;
		    if($success){
		    	
		    	/* CHECK IF TWITTER ACCOUNT ALREADY EXIST */
				if($detail = $this->account->get_twitter_by_twitter_id($user->id)){
					if($detail->inf_id == $this->user_id()){ //User already has this twitter account 
						
						//data initialize
						$data = array(
							'inf_tw_username' 			=> $user->screen_name,
							'inf_tw_display_name' 		=> $user->name,
							'inf_tw_mention'			=> '@'.$user->screen_name,
							'inf_tw_followers' 			=> $user->followers_count,
							'inf_tw_total_posts' 		=> $user->statuses_count,
							'inf_tw_photo_profile' 		=> $user->profile_image_url,
							'inf_tw_description' 		=> preg_replace($this->pattern, $this->replacement, $user->description),
							'inf_tw_verified_account' 	=> ($user->verified)?true:false,
							'inf_tw_status' 			=> true,
							'inf_tw_is_active_account' 	=> true,
						);

						if($this->input->get('oauth_token')){
							$token = array(
								'inf_tw_access_token' 		=> $client->access_token,
								'inf_tw_access_token_post' 	=> $client->access_token_secret
							);

							$data = array_merge($data,$token);
						}

						if($detail->inf_tw_is_active_account == 2 || $detail->inf_tw_is_active_account == 1){ //only active and deleted status can be proceed
							$social_id = $this->session->userdata('twitter_id');
							if($social_id){ //reconnect account
								if($social_id == $detail->inf_tw_id){  //make sure they re-connect to the right account
									//update new data account
									$this->account->update_twitter_account($data,$detail->inf_tw_id,true);
									
									$this->session->set_flashdata('message',sprintf(lang('success_reconnect'),'Twitter',$user->screen_name));

									$this->session->unset_userdata('twitter_id');
								}
								else{
									$this->session->set_flashdata('error',sprintf(lang('error_reconnect'),$user->screen_name,'Twitter'));
								}
							}
							else{ //re-activate deleted account

								//update new data account
								$this->account->update_twitter_account($data,$detail->inf_tw_id,true);
								
								$this->session->set_flashdata('message',sprintf(lang('success_reconnect_deleted'),'Twitter',$user->screen_name));
							}
						}
						else{
							$this->session->set_flashdata('error',sprintf(lang('suspended'),'Twitter','support@sociabuzz.com'));
						}	

					}
					else{
						$this->session->set_flashdata('error',sprintf(lang('registered_by_other'),'Twitter'));
					}

					redirect('account/twitter');
				}
				else{ // yups register this account instead

					$person = $this->account->get_inf_by_id($this->user_id());
					
					$data = array(
						'inf_id' 					=> $this->user_id(),
						'inf_tw_id_tw' 				=> $user->id,
						'inf_tw_username' 			=> $user->screen_name,
						'inf_tw_mention'			=> '@'.$user->screen_name,
						'inf_tw_display_name' 		=> $user->name,
						'inf_tw_followers' 			=> $user->followers_count,
						'inf_tw_total_posts' 		=> $user->statuses_count,
						'inf_tw_photo_profile' 		=> $user->profile_image_url,
						'inf_tw_description' 		=> preg_replace($this->pattern, $this->replacement, $user->description),
						'inf_tw_access_token' 		=> $client->access_token,
						'inf_tw_access_token_post' 	=> $client->access_token_secret,
						'inf_tw_verified_account' 	=> ($user->verified)?true:false,
						'inf_tw_created_date_tw' 	=> $this->global_lib->datetime_dmyhia($user->created_at),
						'inf_tw_created_date_sb' 	=> $this->global_lib->datetime(),
						'inf_tw_person_fullname' 	=> $person->inf_fullname,
						'inf_tw_person_gender'		=> $person->inf_gender,
						'inf_tw_person_birthday'	=> $person->inf_birthdate,
						'inf_tw_status' 			=> true,
						'inf_tw_is_active_account' 	=> true,
					);

					if($this->account->create_new_inf_twitter($data)){
						//live tweet after ther registered
						$values = array( 'status'=>'Just joined SociaBuzz.com Influencer Community (@sociabuzz) pic.twitter.com/USKK0KiDtC' );
		                $live_tweet = $client->CallAPI(
		                    'https://api.twitter.com/1.1/statuses/update.json', 
		                    'POST', $values, array('FailOnAccessError'=>true), $update);
		                if(!$live_tweet){
		                    //error_log(print_r($update->errors[0]->code, 1));

		                	$ip = $user->screen_name.' failed to live tweet. Return Value : '.var_dump($update);
								@file_put_contents(APPPATH."logs/log_live_tweet_after_register.txt", $ip, FILE_APPEND);
		                }

		                $emailer = modules::load('notifications/emailer');
					    $emailer->send_admin($data,'Pendaftaran akun twitter baru',false,'relation@sociabuzz.com');

					    
						redirect('account/twitter');
					}
					else{
						$this->session->set_flashdata('error',sprintf(lang('social_reg_failed'),'Twitter'));
						redirect('account/twitter');
					}
				}	
		    }
		    else{
		    	$this->session->set_flashdata('error',HtmlSpecialChars($client->error));
		    	redirect('account/twitter');
		    }
		// }
		/*else{
			$this->session->set_flashdata('error','API access denied.');
			redirect('account/twitter');
		}*/
		
	}

	function youtube($social_id = false){

		if(!$this->input->get('error')){
			$this->config->load('api_token');
			$this->load->library('global/global_lib');

			$token=""; 
			require_once(APPPATH.'libraries/auth/oauth/http.php');
			require_once(APPPATH.'libraries/auth/oauth/oauth_client.php');

			$client = new oauth_client_class;
			$client->debug = false;
			$client->debug_http = true;
			$client->server = 'Google';
			$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
				dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/youtube';
			//1016967261635-amlb1cplkukq0tbofe06uvvvvap7honu.apps.googleusercontent.com
			//bgjeWqAoDMDCqwkQRoK9t62g
			$client->client_id = $this->config->item('google_client_id'); $application_line = __LINE__;
			$client->client_secret = $this->config->item('google_client_secret');

			if(strlen($client->client_id) == 0
			|| strlen($client->client_secret) == 0)
				die('Please go to Google Developers page https://console.developers.google.com/apis/credentials/key/ , '.
					'create an application, and in the line '.$application_line.
					' set the client_id to client id key and client_secret with client secret');

			/* API permissions
				Manage your YouTube account. 
				This scope is functionally identical to the youtube.force-ssl scope listed above 
				because the YouTube API server is only available via an HTTPS endpoint. 
				As a result, even though this scope does not require an SSL connection, 
				there is actually no other way to make an API request.
			*/

			$client->scope = 'https://www.googleapis.com/auth/youtube https://www.googleapis.com/auth/youtubepartner https://www.googleapis.com/auth/youtube.force-ssl https://www.googleapis.com/auth/youtubepartner-channel-audit';
			
			if($success = $client->Initialize()){
			
				if($success = $client->Process())
				{	

					if(strlen($client->access_token))
					{
						
						$token=$client->access_token;
						$this->session->set_userdata(array('access_token'=>$client->access_token,'refresh_token'=>$client->refresh_token));
						$success = $client->CallAPI(
							'https://www.googleapis.com/youtube/v3/channels', 
							'GET', array('part'=>'snippet,statistics,contentDetails,brandingSettings','mine'=>'true'), array('FailOnAccessError'=>true), $profile);
					}
				}
				
				$success = $client->Finalize($success);

			}

			if($client->exit) exit;

			if($success){
				/* CHECK IF YOUTUBE ACCOUNT ALREADY EXIST */
				
				foreach ($profile->items as $key => $value) {
					if($detail = $this->account->get_youtube_by_username($value->id)){

						if($detail->inf_id == $this->user_id()){ //User already has this instagram account 
							
							//initialize data
							$data = array(
								'inf_yt_id_yt'		 			=> $value->id,
								'inf_yt_username'	 			=> @$value->snippet->customUrl,
								'inf_yt_access_token' 			=> $this->session->userdata('access_token'),
								'inf_yt_refresh_token' 			=> $this->session->userdata('refresh_token'),
								'inf_yt_description' 			=> preg_replace($this->pattern, $this->replacement, $value->snippet->description),
								'inf_yt_display_name' 			=> $value->snippet->title,
								'inf_yt_total_posts' 			=> $value->statistics->videoCount,
								'inf_yt_subscriber' 			=> $value->statistics->subscriberCount,
								'inf_yt_photo_profile' 			=> $value->snippet->thumbnails->medium->url,
								'inf_yt_total_posts_comments' 	=> $value->statistics->commentCount,
								'inf_yt_total_posts_views' 		=> $value->statistics->viewCount,
								'inf_yt_status' 				=> true,
								'inf_yt_is_active_account' 		=> true,
							);

							if($detail->inf_yt_is_active_account == 2 || $detail->inf_yt_is_active_account == 1){ //only active and deleted status can be proceed
								$social_id = $this->session->userdata('youtube_id');
								if($social_id){ //reconnect account
									if($social_id == $detail->inf_yt_id){ //make sure they re-connect to the right account
										//update new data account
										$this->account->update_youtube_account($data,$detail->inf_yt_id,true); //true means 
										
										$this->session->set_flashdata('message',sprintf(lang('success_reconnect'),'YouTube',$value->snippet->title));

										$this->session->unset_userdata('youtube_id');
										//unset cookie that created from oauth library to enable connect multiple social
										setcookie ("PHPSESSID", "", time() - 3600);
									}
									else{
										$this->session->set_flashdata('error',sprintf(lang('error_reconnect'),$value->snippet->title,'YouTube'));
									}
								}
								else{ //re-activate deleted account

									//update new data account
									$this->account->update_youtube_account($data,$detail->inf_yt_id,true); //true means 
									
									$this->session->set_flashdata('message',sprintf(lang('success_reconnect_deleted'),'YouTube',$value->snippet->title));
								}
							}
							else{
								$this->session->set_flashdata('error',sprintf(lang('suspended'),'YouTube','support@sociabuzz.com'));
							}	

						}
						else{
							$this->session->set_flashdata('error',sprintf(lang('registered_by_other'),'YouTube'));
						}
						redirect('account/youtube');
					}
					else{ // yups register this account instead

						$person = $this->account->get_inf_by_id($this->user_id());

						$data = array(
							'inf_id' 						=> $this->user_id(),
							'inf_yt_id_yt'		 			=> $value->id,
							'inf_yt_username'	 			=> @$value->snippet->customUrl,
							'inf_yt_access_token' 			=> $this->session->userdata('access_token'),
							'inf_yt_refresh_token' 			=> $this->session->userdata('refresh_token'),
							'inf_yt_description' 			=> preg_replace($this->pattern, $this->replacement, $value->snippet->description),
							'inf_yt_display_name' 			=> $value->snippet->title,
							'inf_yt_total_posts' 			=> $value->statistics->videoCount,
							'inf_yt_subscriber' 			=> $value->statistics->subscriberCount,
							'inf_yt_photo_profile' 			=> $value->snippet->thumbnails->medium->url,
							'inf_yt_total_posts_comments' 	=> $value->statistics->commentCount,
							'inf_yt_total_posts_views' 		=> $value->statistics->viewCount,
							'inf_yt_created_date_sb' 		=> $this->global_lib->datetime(),
							'inf_yt_person_fullname' 		=> $person->inf_fullname,
							'inf_yt_person_gender'			=> $person->inf_gender,
							'inf_yt_person_birthday'		=> $person->inf_birthdate,
							'inf_yt_status' 				=> true,
							'inf_yt_is_active_account' 		=> true,
						);

						if($this->account->create_new_inf_youtube($data)){
							//send email to admin
							$emailer = modules::load('notifications/emailer');
							$emailer->send_admin($data,'Pendaftaran akun youtube baru',false,'relation@sociabuzz.com');
							
							//unset cookie that created from oauth library to enable connect multiple social
							setcookie ("PHPSESSID", "", time() - 3600);

							$this->session->unset_userdata('member_id');

							redirect('account/youtube');
						}
						else{
							$this->session->set_flashdata('error',sprintf(lang('social_reg_failed'),'YouTube'));
							redirect('account/youtube');
						}
					}
				
				}
				
			}
			else
			{
				$this->session->set_flashdata('error','Can not catch session right now.');
				redirect('account/youtube');
			}
		}
		else{
			$this->session->set_flashdata('error','API access denied.');
			redirect('account/youtube');
		}
			
	}
	
	function blog(){
		$this->load->library('global/global_lib');

		if($url = $this->input->post('url')){
			if($detail = $this->account->get_blog_by_username(trim($url))){
				if($detail->inf_id == $this->user_id()){ //User already has this instagram account 
					$this->session->set_flashdata('error',lang('blog_already_registered'));
				}
				else{
					$this->session->set_flashdata('error',lang('blog_already_registered_by_other'));
				}
				redirect('account/blog');
			}
			else{
				
				$token = sha1(time().$this->user_id());

				$person = $this->account->get_inf_by_id($this->user_id());

				$data = array(
					'inf_id' 					=> $this->user_id(),
					'inf_bl_url' 				=> trim($url),
					'inf_bl_title' 				=> str_replace('/','',preg_replace('#^https?://#', '', trim($url))),
					'inf_bl_rss_feed'			=> trim($this->input->post('rss')),
					'inf_bl_created_date_sb' 	=> $this->global_lib->datetime(),
					'inf_bl_verified_token' 	=> $token,
					'inf_bl_person_fullname' 	=> $person->inf_fullname,
					'inf_bl_person_gender'		=> $person->inf_gender,
					'inf_bl_person_birthday'	=> $person->inf_birthdate,
					'inf_bl_is_active_account' 	=> true,
				);
				
				if($this->account->create_new_inf_blog($data)){
					//send email to admin
					$emailer = modules::load('notifications/emailer');
					$emailer->send_admin($data,'Pendaftaran akun blog baru',false,'relation@sociabuzz.com');

					$this->session->set_flashdata('message',lang('blog_success_register'));
					redirect('account/blog');
				}
				else{
					$this->session->set_flashdata('error',lang('blog_fail_register'));
					redirect('account/blog');
				}
			}
		}
	}

	function refresh($id = false){
		if($id){
			if($blog = $this->account->get_blog_by_username_secure($this->user_id(),$id)){
				if($blog->inf_bl_status == true && $blog->inf_bl_verified_token == ''){
					$this->session->set_flashdata('message',sprintf(lang('blog_active_verify'),$blog->inf_bl_url));
				}
				else{
					$this->session->set_flashdata('error',sprintf(lang('blog_fail_verify'),$blog->inf_bl_url));
				}
			}
			else{
				$this->session->set_flashdata('error',lang('blog_not_found'));
			}
			redirect('account/blog');
		}
	}

	function copy(){
		$to 	= $this->input->get('copyto');
		$from 	= $this->input->get('copyfrom');

		//format twitter-110020-sahal_fahi
		$id_to 		= explode('-', $to);
		$id_from 	= explode('-', $from);


		$check_to = $this->account->get_social_account_secure($id_to[0],$id_to[1],$id_to[2],$this->user_id());
		$check_from = $this->account->get_social_account_secure($id_from[0],$id_from[1],$id_from[2],$this->user_id());

		if($check_from && $check_to){
			if($this->_field_format($id_to[0],$id_from[0],$check_from,$id_to[1])){
				$this->session->set_flashdata('message',lang('copy_profile_success'));
			}
			else{
				$this->session->set_flashdata('error',lang('copy_profile_fail'));
			}
		}
		else{
			$this->session->set_flashdata('error',lang('copy_profile_not_matched'));
		}
		redirect('account');
	}

	/*
	* $to 		= string
	* $from 	= string
	* $data 	= object array
	* $id 		= int
	* return 	= boolean
	*/

	function _field_format($to = false,$from = false, $data = null,$id = false){
		$this->load->library('global/global_lib');

		switch ($to) {
			case 'twitter':
				$new_value = null;
				switch ($from) {
					case 'twitter':
						$new_value = array(
							'inf_tw_description' 				=> $data->inf_tw_description,
							'inf_tw_language_usage' 			=> $data->inf_tw_language_usage,
							'inf_tw_audience_country' 			=> $data->inf_tw_audience_country,
							'inf_tw_audience_gender' 			=> $data->inf_tw_audience_gender,
							
							'inf_tw_audience_age_range' 		=> $data->inf_tw_audience_age_range,
							
							/*'inf_tw_cost_post' 					=> $data->inf_tw_cost_post,*/
							'inf_tw_type' 						=> $data->inf_tw_type,
							'inf_tw_category' 					=> $data->inf_tw_category,
							'inf_tw_usage_rights' 				=> $data->inf_tw_usage_rights,
							
							'inf_tw_topic_content'				=> $data->inf_tw_topic_content,
							'inf_tw_promoted_brands'			=> $data->inf_tw_promoted_brands,
							'inf_tw_avoided_ads_type'			=> $data->inf_tw_avoided_ads_type,

							'inf_tw_person_birthday'			=> $data->inf_tw_person_birthday,
							'inf_tw_person_fullname' 			=> $data->inf_tw_person_fullname,
							'inf_tw_person_gender' 				=> $data->inf_tw_person_gender,
							'inf_tw_person_relationship_status' => $data->inf_tw_person_relationship_status,
							'inf_tw_person_children' 			=> $data->inf_tw_person_children,
							'inf_tw_person_family_life_cycle' 	=> $data->inf_tw_person_family_life_cycle,
							'inf_tw_person_profession' 			=> $data->inf_tw_person_profession,
							'inf_tw_person_religion' 			=> $data->inf_tw_person_religion,
							'inf_tw_person_ethnic' 				=> $data->inf_tw_person_ethnic,

							/*'inf_tw_related_blog' 				=> $data->inf_tw_related_blog,
							'inf_tw_related_instagram' 			=> $data->inf_tw_related_instagram,*/

							'inf_tw_last_modified' 				=> $this->global_lib->datetime()
						);							

						break;
					case 'instagram':
						$new_value = array(
							'inf_tw_description' 				=> $data->inf_ig_description,
							'inf_tw_language_usage' 			=> $data->inf_ig_language_usage,
							'inf_tw_audience_country' 			=> $data->inf_ig_audience_country,
							'inf_tw_audience_gender' 			=> $data->inf_ig_audience_gender,
							
							'inf_tw_audience_age_range' 		=> $data->inf_ig_audience_age_range,
							
							/*'inf_tw_cost_post' 					=> $data->inf_ig_cost_post_photo,*/
							'inf_tw_type' 						=> $data->inf_ig_type,
							'inf_tw_category' 					=> $data->inf_ig_category,
							'inf_tw_usage_rights' 				=> $data->inf_ig_usage_rights,
							
							'inf_tw_topic_content'				=> $data->inf_ig_topic_content,
							'inf_tw_promoted_brands'			=> $data->inf_ig_promoted_brands,
							'inf_tw_avoided_ads_type'			=> $data->inf_ig_avoided_ads_type,

							'inf_tw_person_birthday'			=> $data->inf_ig_person_birthday,
							'inf_tw_person_fullname' 			=> $data->inf_ig_person_fullname,
							'inf_tw_person_gender' 				=> $data->inf_ig_person_gender,
							'inf_tw_person_relationship_status' => $data->inf_ig_person_marital_status,
							'inf_tw_person_children' 			=> $data->inf_ig_person_children,
							'inf_tw_person_family_life_cycle' 	=> $data->inf_ig_person_family_lifecycle,
							'inf_tw_person_profession' 			=> $data->inf_ig_person_profession,
							'inf_tw_person_religion' 			=> $data->inf_ig_person_religion,
							'inf_tw_person_ethnic' 				=> $data->inf_ig_person_ethnic,

							/*'inf_tw_related_blog' 				=> $data->inf_ig_related_blog,
							'inf_tw_related_instagram' 			=> $data->inf_ig_related_instagram,*/

							'inf_tw_last_modified' 				=> $this->global_lib->datetime()
						);							

						break;
					case 'blog':
						$new_value = array(
							'inf_tw_description' 				=> $data->inf_bl_description,
							'inf_tw_language_usage' 			=> $data->inf_bl_language_usage,
							'inf_tw_audience_country' 			=> $data->inf_bl_audience_country,
							'inf_tw_audience_gender' 			=> $data->inf_bl_audience_gender,
							
							'inf_tw_audience_age_range' 		=> $data->inf_bl_audience_age_range,
							
							/*'inf_tw_cost_post' 					=> $data->inf_bl_cost_post,*/
							'inf_tw_type' 						=> $data->inf_bl_type,
							'inf_tw_category' 					=> $data->inf_bl_category,
							'inf_tw_usage_rights' 				=> $data->inf_bl_usage_rights,
							
							'inf_tw_topic_content'				=> $data->inf_bl_topic_content,
							'inf_tw_promoted_brands'			=> $data->inf_bl_promoted_brands,
							'inf_tw_avoided_ads_type'			=> $data->inf_bl_avoided_ads_type,

							'inf_tw_person_birthday'			=> $data->inf_bl_person_birthday,
							'inf_tw_person_fullname' 			=> $data->inf_bl_person_fullname,
							'inf_tw_person_gender' 				=> $data->inf_bl_person_gender,
							'inf_tw_person_relationship_status' => $data->inf_bl_person_relationship_status,
							'inf_tw_person_children' 			=> $data->inf_bl_person_children,
							'inf_tw_person_family_life_cycle' 	=> $data->inf_bl_person_family_life_cycle,
							'inf_tw_person_profession' 			=> $data->inf_bl_person_profession,
							'inf_tw_person_religion' 			=> $data->inf_bl_person_religion,
							'inf_tw_person_ethnic' 				=> $data->inf_bl_person_ethnic,

							/*'inf_tw_related_blog' 				=> $data->inf_bl_related_blog,
							'inf_tw_related_instagram' 			=> $data->inf_bl_related_instagram,*/

							'inf_tw_last_modified' 				=> $this->global_lib->datetime()
							
						);

						break;
					default:
						# code...
						break;
				}
				
				if($this->account->update_twitter_account($new_value,$id)) return true;
				else return false;

				break;
			case 'instagram':
				switch ($from) {
					case 'twitter':
						$new_value = array(
							'inf_ig_description' 				=> $data->inf_tw_description,
							'inf_ig_language_usage' 			=> $data->inf_tw_language_usage,
							'inf_ig_audience_country' 			=> $data->inf_tw_audience_country,
							'inf_ig_audience_gender' 			=> $data->inf_tw_audience_gender,
							
							'inf_ig_audience_age_range' 		=> $data->inf_tw_audience_age_range,
							
							/*'inf_ig_cost_post_photo' 			=> $data->inf_tw_cost_post,
							'inf_ig_cost_post_video' 			=> $data->inf_tw_cost_post,*/
							'inf_ig_type' 						=> $data->inf_tw_type,
							'inf_ig_category' 					=> $data->inf_tw_category,
							'inf_ig_usage_rights' 				=> $data->inf_tw_usage_rights,
							
							'inf_ig_topic_content'				=> $data->inf_tw_topic_content,
							'inf_ig_promoted_brands'			=> $data->inf_tw_promoted_brands,
							'inf_ig_avoided_ads_type'			=> $data->inf_tw_avoided_ads_type,

							'inf_ig_person_birthday'			=> $data->inf_tw_person_birthday,
							'inf_ig_person_fullname' 			=> $data->inf_tw_person_fullname,
							'inf_ig_person_gender' 				=> $data->inf_tw_person_gender,
							'inf_ig_person_marital_status' 		=> $data->inf_tw_person_relationship_status,
							'inf_ig_person_children' 			=> $data->inf_tw_person_children,
							'inf_ig_person_family_lifecycle' 	=> $data->inf_tw_person_family_life_cycle,
							'inf_ig_person_profession' 			=> $data->inf_tw_person_profession,
							'inf_ig_person_religion' 			=> $data->inf_tw_person_religion,
							'inf_ig_person_ethnic' 				=> $data->inf_tw_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_tw_related_blog,
							'inf_ig_related_instagram' 			=> $data->inf_tw_related_instagram,*/

							'inf_ig_last_modified' 				=> $this->global_lib->datetime()
						);				

						break;
					case 'instagram':
						$new_value = array(
							'inf_ig_description' 				=> $data->inf_ig_description,
							'inf_ig_language_usage' 			=> $data->inf_ig_language_usage,
							'inf_ig_audience_country' 			=> $data->inf_ig_audience_country,
							'inf_ig_audience_gender' 			=> $data->inf_ig_audience_gender,
							
							'inf_ig_audience_age_range' 		=> $data->inf_ig_audience_age_range,
							
							/*'inf_ig_cost_post_photo' 			=> $data->inf_ig_cost_post_photo,
							'inf_ig_cost_post_video' 			=> $data->inf_ig_cost_post_video,*/
							'inf_ig_type' 						=> $data->inf_ig_type,
							'inf_ig_category' 					=> $data->inf_ig_category,
							'inf_ig_usage_rights' 				=> $data->inf_ig_usage_rights,
							
							'inf_ig_topic_content'				=> $data->inf_ig_topic_content,
							'inf_ig_promoted_brands'			=> $data->inf_ig_promoted_brands,
							'inf_ig_avoided_ads_type'			=> $data->inf_ig_avoided_ads_type,

							'inf_ig_person_birthday'			=> $data->inf_ig_person_birthday,
							'inf_ig_person_fullname' 			=> $data->inf_ig_person_fullname,
							'inf_ig_person_gender' 				=> $data->inf_ig_person_gender,
							'inf_ig_person_marital_status' 		=> $data->inf_ig_person_marital_status,
							'inf_ig_person_children' 			=> $data->inf_ig_person_children,
							'inf_ig_person_family_lifecycle' 	=> $data->inf_ig_person_family_lifecycle,
							'inf_ig_person_profession' 			=> $data->inf_ig_person_profession,
							'inf_ig_person_religion' 			=> $data->inf_ig_person_religion,
							'inf_ig_person_ethnic' 				=> $data->inf_ig_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_ig_related_blog,
							'inf_ig_related_instagram' 			=> $data->inf_ig_related_instagram,*/

							'inf_ig_last_modified' 				=> $this->global_lib->datetime()
						);							
						
						return $new_value;

						break;
					case 'blog':
						$new_value = array(
							'inf_ig_description' 				=> $data->inf_bl_description,
							'inf_ig_language_usage' 			=> $data->inf_bl_language_usage,
							'inf_ig_audience_country' 			=> $data->inf_bl_audience_country,
							'inf_ig_audience_gender' 			=> $data->inf_bl_audience_gender,
							
							'inf_ig_audience_age_range' 		=> $data->inf_bl_audience_age_range,
							
							/*'inf_ig_cost_post_photo' 			=> $data->inf_bl_cost_post,
							'inf_ig_cost_post_video' 			=> $data->inf_bl_cost_post,*/
							'inf_ig_type' 						=> $data->inf_bl_type,
							'inf_ig_category' 					=> $data->inf_bl_category,
							'inf_ig_usage_rights' 				=> $data->inf_bl_usage_rights,
							
							'inf_ig_topic_content'				=> $data->inf_bl_topic_content,
							'inf_ig_promoted_brands'			=> $data->inf_bl_promoted_brands,
							'inf_ig_avoided_ads_type'			=> $data->inf_bl_avoided_ads_type,

							'inf_ig_person_birthday'			=> $data->inf_bl_person_birthday,
							'inf_ig_person_fullname' 			=> $data->inf_bl_person_fullname,
							'inf_ig_person_gender' 				=> $data->inf_bl_person_gender,
							'inf_ig_person_relationship_status' => $data->inf_bl_person_relationship_status,
							'inf_ig_person_children' 			=> $data->inf_bl_person_children,
							'inf_ig_person_family_life_cycle' 	=> $data->inf_bl_person_family_life_cycle,
							'inf_ig_person_profession' 			=> $data->inf_bl_person_profession,
							'inf_ig_person_religion' 			=> $data->inf_bl_person_religion,
							'inf_ig_person_ethnic' 				=> $data->inf_bl_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_bl_related_blog,
							'inf_ig_related_blog' 				=> $data->inf_bl_related_instagram,*/

							'inf_ig_last_modified' 				=> $this->global_lib->datetime()
							
						);					

						break;
					default:
						# code...
						break;
				}

				if($this->account->update_instagram_account($new_value,$id)) return true;
				else return false;

				break;
			case 'blog':
				switch ($from) {
					case 'twitter':
						$new_value = array(
							'inf_bl_description' 				=> $data->inf_tw_description,
							'inf_bl_language_usage' 			=> $data->inf_tw_language_usage,
							'inf_bl_audience_country' 			=> $data->inf_tw_audience_country,
							'inf_bl_audience_gender' 			=> $data->inf_tw_audience_gender,
							
							'inf_bl_audience_age_range' 		=> $data->inf_tw_audience_age_range,
							
							'inf_bl_cost_post'		 			=> $data->inf_tw_cost_post,
							'inf_bl_type' 						=> $data->inf_tw_type,
							'inf_bl_category' 					=> $data->inf_tw_category,
							/*'inf_bl_usage_rights' 				=> $data->inf_tw_usage_rights,*/
							
							'inf_bl_topic_content'				=> $data->inf_tw_topic_content,
							'inf_bl_promoted_brands'			=> $data->inf_tw_promoted_brands,
							'inf_bl_avoided_ads_type'			=> $data->inf_tw_avoided_ads_type,

							'inf_bl_person_birthday'			=> $data->inf_tw_person_birthday,
							'inf_bl_person_fullname' 			=> $data->inf_tw_person_fullname,
							'inf_bl_person_gender' 				=> $data->inf_tw_person_gender,
							'inf_bl_person_marital_status' 		=> $data->inf_tw_person_relationship_status,
							'inf_bl_person_children' 			=> $data->inf_tw_person_children,
							'inf_bl_person_family_lifecycle' 	=> $data->inf_tw_person_family_life_cycle,
							'inf_bl_person_profession' 			=> $data->inf_tw_person_profession,
							'inf_bl_person_religion' 			=> $data->inf_tw_person_religion,
							'inf_bl_person_ethnic' 				=> $data->inf_tw_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_tw_related_blog,
							'inf_ig_related_instagram' 			=> $data->inf_tw_related_instagram,*/

							'inf_bl_last_modified' 				=> $this->global_lib->datetime()
						);

						break;
					case 'instagram':
						$new_value = array(
							'inf_bl_description' 				=> $data->inf_ig_description,
							'inf_bl_language_usage' 			=> $data->inf_ig_language_usage,
							'inf_bl_audience_country' 			=> $data->inf_ig_audience_country,
							'inf_bl_audience_gender' 			=> $data->inf_ig_audience_gender,
							
							'inf_bl_audience_age_range' 		=> $data->inf_ig_audience_age_range,
							
							/*'inf_bl_cost_post'		 			=> $data->inf_ig_cost_post_photo,*/
							'inf_bl_type' 						=> $data->inf_ig_type,
							'inf_bl_category' 					=> $data->inf_ig_category,
							/*'inf_bl_usage_rights' 				=> $data->inf_ig_usage_rights,*/
							
							'inf_bl_topic_content'				=> $data->inf_ig_topic_content,
							'inf_bl_promoted_brands'			=> $data->inf_ig_promoted_brands,
							'inf_bl_avoided_ads_type'			=> $data->inf_ig_avoided_ads_type,

							'inf_bl_person_birthday'			=> $data->inf_ig_person_birthday,
							'inf_bl_person_fullname' 			=> $data->inf_ig_person_fullname,
							'inf_bl_person_gender' 				=> $data->inf_ig_person_gender,
							'inf_bl_person_marital_status' 		=> $data->inf_ig_person_relationship_status,
							'inf_bl_person_children' 			=> $data->inf_ig_person_children,
							'inf_bl_person_family_lifecycle' 	=> $data->inf_ig_person_family_life_cycle,
							'inf_bl_person_profession' 			=> $data->inf_ig_person_profession,
							'inf_bl_person_religion' 			=> $data->inf_ig_person_religion,
							'inf_bl_person_ethnic' 				=> $data->inf_ig_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_ig_related_blog,
							'inf_ig_related_instagram' 			=> $data->inf_ig_related_instagram,*/

							'inf_bl_last_modified' 				=> $this->global_lib->datetime()
						);					

						break;
					case 'blog':
						$new_value = array(
							'inf_bl_description' 				=> $data->inf_bl_description,
							'inf_bl_language_usage' 			=> $data->inf_bl_language_usage,
							'inf_bl_audience_country' 			=> $data->inf_bl_audience_country,
							'inf_bl_audience_gender' 			=> $data->inf_bl_audience_gender,
							
							'inf_bl_audience_age_range' 		=> $data->inf_bl_audience_age_range,
							
							/*'inf_bl_cost_post'		 			=> $data->inf_bl_cost_post,*/
							'inf_bl_type' 						=> $data->inf_bl_type,
							'inf_bl_category' 					=> $data->inf_bl_category,
							/*'inf_bl_usage_rights' 				=> $data->inf_bl_usage_rights,*/
							
							'inf_bl_topic_content'				=> $data->inf_bl_topic_content,
							'inf_bl_promoted_brands'			=> $data->inf_bl_promoted_brands,
							'inf_bl_avoided_ads_type'			=> $data->inf_bl_avoided_ads_type,

							'inf_bl_person_birthday'			=> $data->inf_bl_person_birthday,
							'inf_bl_person_fullname' 			=> $data->inf_bl_person_fullname,
							'inf_bl_person_gender' 				=> $data->inf_bl_person_gender,
							'inf_bl_person_marital_status' => $data->inf_bl_person_relationship_status,
							'inf_bl_person_children' 			=> $data->inf_bl_person_children,
							'inf_bl_person_family_lifecycle' 	=> $data->inf_bl_person_family_life_cycle,
							'inf_bl_person_profession' 			=> $data->inf_bl_person_profession,
							'inf_bl_person_religion' 			=> $data->inf_bl_person_religion,
							'inf_bl_person_ethnic' 				=> $data->inf_bl_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_bl_related_blog,
							'inf_ig_related_blog' 				=> $data->inf_bl_related_instagram,*/

							'inf_bl_last_modified' 				=> $this->global_lib->datetime()
							
						);					

						break;
					default:
						# code...
						break;
				}

				if($this->account->update_blog_account($new_value,$id)) return true;
				else return false;

				break;
			case 'youtube':
				switch ($from) {
					case 'twitter':
						$new_value = array(
							'inf_yt_description' 				=> $data->inf_tw_description,
							'inf_yt_language_usage' 			=> $data->inf_tw_language_usage,
							'inf_yt_audience_country' 			=> $data->inf_tw_audience_country,
							'inf_yt_audience_gender' 			=> $data->inf_tw_audience_gender,
							
							'inf_yt_audience_age_range' 		=> $data->inf_tw_audience_age_range,
							
							/*'inf_ig_cost_post_photo' 			=> $data->inf_tw_cost_post,
							'inf_ig_cost_post_video' 			=> $data->inf_tw_cost_post,*/
							'inf_yt_type' 						=> $data->inf_tw_type,
							'inf_yt_category' 					=> $data->inf_tw_category,
							'inf_yt_usage_rights' 				=> $data->inf_tw_usage_rights,
							
							'inf_yt_topic_content'				=> $data->inf_tw_topic_content,
							'inf_yt_promoted_brands'			=> $data->inf_tw_promoted_brands,
							'inf_yt_avoided_ads_type'			=> $data->inf_tw_avoided_ads_type,

							'inf_yt_person_birthday'			=> $data->inf_tw_person_birthday,
							'inf_yt_person_fullname' 			=> $data->inf_tw_person_fullname,
							'inf_yt_person_gender' 				=> $data->inf_tw_person_gender,
							'inf_yt_person_marital_status' 		=> $data->inf_tw_person_relationship_status,
							'inf_yt_person_children' 			=> $data->inf_tw_person_children,
							'inf_yt_person_family_lifecycle' 	=> $data->inf_tw_person_family_life_cycle,
							'inf_yt_person_profession' 			=> $data->inf_tw_person_profession,
							'inf_yt_person_religion' 			=> $data->inf_tw_person_religion,
							'inf_yt_person_ethnic' 				=> $data->inf_tw_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_tw_related_blog,
							'inf_ig_related_instagram' 			=> $data->inf_tw_related_instagram,*/

							'inf_yt_last_modified' 				=> $this->global_lib->datetime()
						);				

						break;
					case 'instagram':
						$new_value = array(
							'inf_yt_description' 				=> $data->inf_ig_description,
							'inf_yt_language_usage' 			=> $data->inf_ig_language_usage,
							'inf_yt_audience_country' 			=> $data->inf_ig_audience_country,
							'inf_yt_audience_gender' 			=> $data->inf_ig_audience_gender,
							
							'inf_yt_audience_age_range' 		=> $data->inf_ig_audience_age_range,
							
							/*'inf_yt_cost_post_photo' 			=> $data->inf_ig_cost_post_photo,
							'inf_yt_cost_post_video' 			=> $data->inf_ig_cost_post_video,*/
							'inf_yt_type' 						=> $data->inf_ig_type,
							'inf_yt_category' 					=> $data->inf_ig_category,
							'inf_yt_usage_rights' 				=> $data->inf_ig_usage_rights,
							
							'inf_yt_topic_content'				=> $data->inf_ig_topic_content,
							'inf_yt_promoted_brands'			=> $data->inf_ig_promoted_brands,
							'inf_yt_avoided_ads_type'			=> $data->inf_ig_avoided_ads_type,

							'inf_yt_person_birthday'			=> $data->inf_ig_person_birthday,
							'inf_yt_person_fullname' 			=> $data->inf_ig_person_fullname,
							'inf_yt_person_gender' 				=> $data->inf_ig_person_gender,
							'inf_yt_person_marital_status' 		=> $data->inf_ig_person_marital_status,
							'inf_yt_person_children' 			=> $data->inf_ig_person_children,
							'inf_yt_person_family_lifecycle' 	=> $data->inf_ig_person_family_lifecycle,
							'inf_yt_person_profession' 			=> $data->inf_ig_person_profession,
							'inf_yt_person_religion' 			=> $data->inf_ig_person_religion,
							'inf_yt_person_ethnic' 				=> $data->inf_ig_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_ig_related_blog,
							'inf_ig_related_instagram' 			=> $data->inf_ig_related_instagram,*/

							'inf_yt_last_modified' 				=> $this->global_lib->datetime()
						);							
						
						return $new_value;

						break;
					case 'blog':
						$new_value = array(
							'inf_yt_description' 				=> $data->inf_bl_description,
							'inf_yt_language_usage' 			=> $data->inf_bl_language_usage,
							'inf_yt_audience_country' 			=> $data->inf_bl_audience_country,
							'inf_yt_audience_gender' 			=> $data->inf_bl_audience_gender,
							
							'inf_yt_audience_age_range' 		=> $data->inf_bl_audience_age_range,
							
							/*'inf_ig_cost_post_photo' 			=> $data->inf_bl_cost_post,
							'inf_ig_cost_post_video' 			=> $data->inf_bl_cost_post,*/
							'inf_yt_type' 						=> $data->inf_bl_type,
							'inf_yt_category' 					=> $data->inf_bl_category,
							'inf_yt_usage_rights' 				=> $data->inf_bl_usage_rights,
							
							'inf_yt_topic_content'				=> $data->inf_bl_topic_content,
							'inf_yt_promoted_brands'			=> $data->inf_bl_promoted_brands,
							'inf_yt_avoided_ads_type'			=> $data->inf_bl_avoided_ads_type,

							'inf_yt_person_birthday'			=> $data->inf_bl_person_birthday,
							'inf_yt_person_fullname' 			=> $data->inf_bl_person_fullname,
							'inf_yt_person_gender' 				=> $data->inf_bl_person_gender,
							'inf_yt_person_relationship_status' => $data->inf_bl_person_relationship_status,
							'inf_yt_person_children' 			=> $data->inf_bl_person_children,
							'inf_yt_person_family_life_cycle' 	=> $data->inf_bl_person_family_life_cycle,
							'inf_yt_person_profession' 			=> $data->inf_bl_person_profession,
							'inf_yt_person_religion' 			=> $data->inf_bl_person_religion,
							'inf_yt_person_ethnic' 				=> $data->inf_bl_person_ethnic,

							/*'inf_ig_related_blog' 				=> $data->inf_bl_related_blog,
							'inf_ig_related_blog' 				=> $data->inf_bl_related_instagram,*/

							'inf_yt_last_modified' 				=> $this->global_lib->datetime()
							
						);					

						break;
					default:
						# code...
						break;
				}

				if($this->account->update_youtube_account($new_value,$id)) return true;
				else return false;

				break;
			default:
				# code...
				break;
		}
	}

	function disconnect(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('social_id','ID Social Media','required');
		$this->form_validation->set_rules('social_type','Tipe Social Media','required');
		if($this->form_validation->run() == false){
			return false;
		}
		else{
			$social_id = $this->form_validation->set_value('social_id');
			$social_type = $this->form_validation->set_value('social_type');

			if($this->account->disconnect_social_media($social_id,$social_type,$this->user_id())){
				
				//send email to admin
				//$emailer = modules::load('notifications/emailer');
				//$emailer->send_admin('','Pemutusan koneksi akun instagram');

				//success
				$this->session->set_flashdata('message',lang('account_deleted'));
			}
			else{
				//failed
				$this->session->set_flashdata('error',lang('account_deleted_failed'));
			}	
			redirect('account');
		}
	}

	function reconnect(){
		$social_id = $this->input->get('id');
		$social_type = $this->input->get('type');

		if($social_id != '' && $social_type != ''){
			switch ($social_type) {
				case 'twitter':
					$this->session->set_userdata('twitter_id',$social_id);
					$this->twitter($social_id);
					break;
				case 'instagram':
					$this->session->set_userdata('instagram_id',$social_id);
					$this->instagram($social_id);
					break;
				case 'youtube':
					$this->session->set_userdata('youtube_id',$social_id);
					$this->youtube($social_id);
					break;
				case 'blog':
					if($detail = $this->account->get_blog_by_username_secure($this->user_id(),$social_id)){
						if($detail->inf_bl_verified_token == ''){
							if($this->account->reconnect_social_media($social_id,$social_type,$this->user_id())){
								$this->session->set_flashdata('message',lang('blog_reconnect'));
							}
							else{
								$this->session->set_flashdata('error',lang('failed_reconnect'));
							}
						}
						else{
							$this->session->set_flashdata('error',lang('blog_unverify'));
						}
					}

					redirect('account/blog/'.$social_id.'/basic_info');
					break;
				default:
					$this->session->set_flashdata('error',lang('alert')['unidentify_account']);
					redirect('account');
					break;
			}
		}
		else{
			$this->session->set_flashdata('error',lang('alert')['unidentify_account']);
			redirect('account');
		}

	}

	function following(){
	
		$this->config->load('api_token');
		$this->load->library('global/global_lib');

	    require_once(APPPATH.'libraries/auth/oauth/http.php');
		require_once(APPPATH.'libraries/auth/oauth/oauth_client.php');

	    $client = new oauth_client_class;
	    $client->debug = false;
	    $client->debug_http = true;
	    $client->server = 'Twitter';
	    $client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
        dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/following';
        $client->request_token_url = 'https://api.twitter.com/oauth/request_token';
	    $client->dialog_url = 'https://api.twitter.com/oauth/authenticate';
	    $client->access_token_url = 'https://api.twitter.com/oauth/access_token';
	    $client->oauth_version = '1.0a';

	    
	    if(defined('OAUTH_PIN'))
	        $client->pin = OAUTH_PIN;
	    
	    $client->client_id = $this->config->item('twitter_consumer_token'); $application_line = __LINE__;
	    $client->client_secret = $this->config->item('twitter_consumer_secret');

	    /*$client->access_token = $value->inf_tw_access_token;
		$client->access_token_secret = $value->inf_tw_access_token_post;*/

		$this->db->where('inf_tw_is_active_account',true);
		$this->db->limit(200,1);
		$list = $this->db->get('tbl_influencer_twitter')->result();

		foreach ($list as $key => $value) {
			$client->access_token = '440594483-iIDX85ULfnmoOUXn9lLuxJAPJg2zOeUM19udSYDS';
			$client->access_token_secret = '9BGLV9e0oyffFft2KzlbcW6BYEOoZHetvUzR5xYgVooeE';

		   	$success = $client->CallAPI(
	            'https://api.twitter.com/1.1/friendships/create.json', 
	            'POST', array('user_id'=>156700510,'follow'=>true), array('FailOnAccessError'=>true), $user);

			/*print_r($user);
		   	die();	
			*/

		   	if(@$user->id){
		   		$ip = "\n ".$value->inf_tw_username."; Success; ".date('d-m-Y H:i:s',time()).";";
				file_put_contents(APPPATH."logs/156700510.txt", $ip, FILE_APPEND);
		   	}
		   	else{
		   		$ip = "\n ".$value->inf_tw_username."; Failed; ".date('d-m-Y H:i:s',time()).";";
				file_put_contents(APPPATH."logs/156700510.txt", $ip, FILE_APPEND);
		   	}

		   	sleep(3); //sleep for 3 seconds then continue!

		}
		
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
			/* CHECK IF INSTAGRAM ACCOUNT ALREADY EXIST */
			print_r($client);

			echo "<hr>";

			print_r($user);
			
		}
		
	}


}

/* End of file users.php */
/* Location: ./application/modules/users/controllers/users.php */