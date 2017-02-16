<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Email
| -------------------------------------------------------------------------
| This file lets you define parameters for sending emails.
| Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/libraries/email.html
|
*/

//MAILGUN SMTP (SANDBOX)
$config['protocol']='smtp';
$config['smtp_host']='smtp.mail.org'; //(SMTP server)
$config['smtp_port']='587'; //(SMTP port)
$config['smtp_timeout']='30';
$config['smtp_user']='';
$config['smtp_pass']='';
$config['charset']='utf-8';
$config['newline']="\r\n"; 
$config['mailtype'] = 'html';



/* End of file email.php */
/* Location: ./application/config/email.php */
