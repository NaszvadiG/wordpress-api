<?php
ob_start();

//define value to password hash config
define('PHPASS_HASH_STRENGTH', 8);
define('PHPASS_HASH_PORTABLE', false);

require_once('influencer/libraries/phpass-0.1/PasswordHash.php');


class auth_lib {

	protected $_ci;

	function __construct(){
		$this->_ci =&get_instance();
		$this->_ci->load->model('auth/auth_model','acc',TRUE);
	}

	public function check_authorize(){

        $now = time(); // checking the time now when home page starts
        //if($this->_ci->session->userdata('expire_session')){
        if($now > $this->_ci->session->userdata('expire_session')){
            $this->_ci->session->sess_destroy();
            //Waktu login anda sudah habis, Silahkan login kembali.
            return array('status'=> FALSE ,'message'=>'Your session time has been expired, please re-login.');
        }
        else{
            if(!$this->_ci->session->userdata('keep')){
                if(!$this->_ci->session->userdata('is_login')){
                    $this->_ci->session->sess_destroy();
                    //Terjadi kesalahan mengambil data session, mohon login kembali
                    return array('status'=> FALSE ,'message'=>'Error has been identified while attempt session cookies, please re-login.');
                }
                else{
                    //Kasih satu jam waktu session klo di lupa utak atik
                    $this->_ci->session->set_userdata(array('expire_session' => time()+(60*60)));
                    return array('status' => TRUE);
                }
            }else{
                if(!$this->_ci->session->userdata('is_login')){
                    $this->_ci->session->sess_destroy();
                    return array('status'=> FALSE ,'message'=>'Error has been identified while attempt session cookies, please re-login.');
                }
                else{
                    return array('status' => TRUE);
                } 
            }
        }
        //}
        //else{
        //     return array('status'=> FALSE ,'message'=>'');
       // }

    }

    public function login($username = '',$password = '', $keep = '', $redirect = '')
    {
        
        
        
        ($redirect) ? $callback = '?callback='.$redirect : $callback = '';
        if($username == '' || $password == ''){
            //Username dan Password Tidak Boleh Kosong
            $this->_ci->session->set_flashdata('error','Email dan Password tidak boleh kosong.');
            return array('status' => FALSE, 'redirect' => $callback);
        }else{
            
            if($user = $this->_ci->acc->show_user($username)) {   // login ok  
                if($user->inf_is_active_account){           
                    if($user->inf_is_verified_account){
                        // cek password hash
                        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH,PHPASS_HASH_PORTABLE);
                        if ($hasher->CheckPassword($password, $user->inf_password)) {       // password ok
                            //atur masa berlaku sesi dalam server 
                            if($keep){
                                $this->_ci->session->set_userdata(array(
                                    'expire_session' => time()+(30*(24*(60*60))),
                                    'keep' => TRUE 
                                    //beda milisecond gpp lah,, keep login tapi tetep aja kok bakal abis dalam satu bulan
                                ));
                            }
                            else{
                                $this->_ci->session->set_userdata(array(
                                    'expire_session' => time()+(60*60),
                                    'keep' => FALSE
                                    //Kasih satu jam waktu session klo di lupa utak atik
                                ));
                            }

                            $this->_ci->session->set_userdata(array(
                                    'is_login'      => TRUE,
                                    'user_id'       => $user->inf_id,
                                    'user'          => array('firstname'=>$user->inf_firstname,'picture'=>$user->inf_profile_picture),
                                    'currency'      => $user->inf_currency_type
                            )); 
                            //$this->acc->update_session($$session_id) //ditambahkan untuk validasi user
                            $this->_ci->load->library('global/global_lib');
                            $this->_ci->acc->update_user(array('inf_last_login' => $this->_ci->global_lib->datetime(),'inf_login_counter'=>$user->inf_login_counter+1),$user->inf_id);

                            if($callback) return array('status' => TRUE, 'redirect' => $redirect, 'user'=> $user); 
                            else{
                                return array('status' => TRUE, 'redirect' => null, 'user'=> $user);
                            } 
                            
                        } else {// fail - wrong password
                            $this->_ci->session->set_flashdata('error', 'Kata sandi yang dimasukkan salah. Coba kata sandi lain'); //Password yang dimasukkan salah, Masukkan password yang lain.
                            return array('status' => FALSE, 'redirect' => $callback);
                        }
                    }
                    else{
                        $this->_ci->session->set_flashdata('error', 'Akun anda belum terverifikasi. Harap verifikasi email terlebih dahulu!'); //User telah di suspend oleh admin / akun tidak aktif
                        return array('status' => FALSE, 'redirect' => $callback);    
                    }
                }
                else{
                    $this->_ci->session->set_flashdata('error', 'Akun anda sudah tidak aktif. Hubungi admin untuk mendapatkan akses masuk.'); //User telah di suspend oleh admin / akun tidak aktif
                    return array('status' => FALSE, 'redirect' => $callback);
                }
            } else {   
                                                               // fail - wrong login
                $this->_ci->session->set_flashdata('error','Email kamu tidak terdaftar. Silahkan buat akun sekarang.'); //Username yang dimasukkan tidak terdaftar, hubungi administrator untuk mendapatkan hak akses.
                return array('status' => FALSE, 'redirect' => $callback);
            }
            
        }
    }

    function check_matches_password($password,$hash_password){

        
        
        // cek password hash
        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH,PHPASS_HASH_PORTABLE);
        if ($hasher->CheckPassword($password, $hash_password)) {   // password ok
            return true;
        }
        else{
            return false;
        }
    }

    function hash_password($password){
        
        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH,PHPASS_HASH_PORTABLE);
        return $hasher->HashPassword($password);
    }

    public function a_logout(){
        //$this->_ci->acc->update_user(array('session_hash' => ''),$this->_ci->session->userdata('username'));
        $this->_ci->session->unset_userdata(array(
                            'user_id',
                            'is_login',
                            'expire_session',
                            'keep',
                            'lang'
                    ));
        $this->_ci->session->sess_destroy();
        return TRUE;
    }

    function cur_page_url() {
        $pageURL = 'http';
        if (@$_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
          $pageURL .= "://";
        if (@$_SERVER["SERVER_PORT"] != "80") {
          $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
          $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

	
}