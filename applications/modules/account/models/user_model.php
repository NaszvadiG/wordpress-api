<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {


	protected $user 			= 'tbl_influencer';
	protected $country 			= 'tbl_country';
	protected $province 		= 'tbl_province';
	protected $city 			= 'tbl_city';

	protected $advertiser 		= 'tbl_influencer';

	protected $referral_adv 	= 'tbl_referral_advertiser';
	protected $referral_inf 	= 'tbl_referral_influencer';

	protected $balance_adv 		= 'tbl_advertiser_balance';


	function create_new_inf($data){
		$query = $this->db->insert($this->user,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}

	function update_inf_account($data,$id){
		$this->db->where('inf_id',$id);
		$query = $this->db->update($this->user,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }	
	}

	function get_inf_by_email($email){
		$this->db->where('inf_email',$email);
		$this->db->order_by('inf_id','desc');
		$query = $this->db->get($this->user);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_inf_by_id($id){
		$this->db->where('inf_id',$id);
		$query = $this->db->get($this->user);
		return ($query->num_rows() > 0)? $query->row() : null;
	}


	function get_adv_by_id($id){
		$this->db->where('adv_id',$id);
		$query = $this->db->get($this->advertiser);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_country_list(){
		$query = $this->db->get($this->country);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function country_by_id($id){
		$this->db->where('country_id',$id);
		$query = $this->db->get($this->country);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_province_list($id){
		$this->db->where('country_id',$id);
		$query = $this->db->get($this->province);
		return ($query->num_rows() > 0)? $query->result() : null;	
	}

	function province_by_id($id){
		$this->db->where('province_id',$id);
		$query = $this->db->get($this->province);
		return ($query->num_rows() > 0)? $query->row() : null;	
	}

	function get_city_list($id){
		$this->db->where('province_id',$id);
		$query = $this->db->get($this->city);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function city_by_id($id){
		$this->db->where('city_id',$id);
		$query = $this->db->get($this->city);
		return ($query->num_rows() > 0)? $query->row() : null;	
	}




	function check_referral_code_adv($code){
		$this->db->where('adv_referral_code',$code);
		$query = $this->db->get($this->user);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function check_referral_code_inf($code){
		$this->db->where('inf_referral_code',$code);
		$query = $this->db->get($this->influencer);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	
	function get_referral_adv_detail($referral_id){
		$this->db->where('referral_adv_id',$referral_id);
		$query = $this->db->get($this->referral_adv);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function check_is_referral_adv_exist($user_id,$referral_id,$tbl){
		$this->db->where('referral_adv_redeem_id',$user_id);
		$this->db->where('referral_source_id',$referral_id);
		$this->db->where('referral_source_type',$tbl);
		$this->db->order_by('referral_adv_id','desc');

		$query = $this->db->get($this->referral_adv);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function create_redeem_used($data){
		$query = $this->db->insert($this->referral_adv,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}

	function last_balance_current($user_id,$tbl){
		$this->db->where('adv_id',$user_id);
		$this->db->order_by('adv_balance_id','desc');

		if($tbl == 'adv') $query = $this->db->get($this->balance_adv);
		if($tbl == 'inf') $query = $this->db->get($this->balance_inf);
		
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function create_balance_history($data,$tbl){
		if($tbl == 'adv'){	
			$query = $this->db->insert($this->balance_adv,$data);
			if($this->db->affected_rows() > 0){
		      return true;
		    }else{
		      return false;
		    }
		}
		if($tbl == 'inf'){
			$query = $this->db->insert($this->balance_inf,$data);
			if($this->db->affected_rows() > 0){
		      return true;
		    }else{
		      return false;
		    }
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */