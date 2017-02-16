<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account_model extends CI_Model {


	protected $user 		= 'tbl_influencer';
	protected $country 		= 'tbl_country';
	protected $province 	= 'tbl_province';
	protected $city 		= 'tbl_city';
	protected $language 	= 'tbl_languages';
	protected $religion 	= 'tbl_religion';
	protected $ethnic 		= 'tbl_ethnic';

	
	protected $categories 	= 'tbl_categories';
	
	protected $topics 		= 'tbl_content_and_topic';
	protected $tw_topics 	= 'tbl_influencer_twitter_topics';
	protected $ig_topics 	= 'tbl_influencer_instagram_topics';
	protected $yt_topics 	= 'tbl_influencer_youtube_topics';
	protected $bl_topics 	= 'tbl_influencer_blog_topics';

	protected $brands 		= 'tbl_brands';
	protected $tw_brands 	= 'tbl_influencer_twitter_brand';
	protected $ig_brands 	= 'tbl_influencer_instagram_brand';
	protected $yt_brands 	= 'tbl_influencer_youtube_brand';
	protected $bl_brands 	= 'tbl_influencer_blog_brand';	

	protected $avoids 		= 'tbl_avoid_ads';
	protected $tw_avoids 	= 'tbl_influencer_twitter_avoid';
	protected $ig_avoids 	= 'tbl_influencer_instagram_avoid';
	protected $yt_avoids 	= 'tbl_influencer_youtube_avoid';
	protected $bl_avoids 	= 'tbl_influencer_blog_avoid';	

	protected $occupation 		= 'tbl_master_occupation';
	protected $tw_occupation 	= 'tbl_influencer_twitter_avoid';
	protected $ig_occupation 	= 'tbl_influencer_instagram_avoid';
	protected $yt_occupation 	= 'tbl_influencer_youtube_avoid';
	protected $bl_occupation 	= 'tbl_influencer_blog_avoid';	
	 
	protected $tw_portfolio = 'tbl_influencer_twitter_portfolio';
	protected $ig_portfolio = 'tbl_influencer_instagram_portfolio';
	protected $bl_portfolio = 'tbl_influencer_blog_portfolio';
	protected $yt_portfolio = 'tbl_influencer_youtube_portfolio';

	protected $advertiser 	= 'tbl_influencer';

	protected $instagram 	= 'tbl_influencer_instagram_0';
	protected $youtube 		= 'tbl_influencer_youtube_0';
	protected $blog 		= 'tbl_influencer_blog_0';
	protected $twitter 		= 'tbl_influencer_twitter_0';

	protected $instagram_1 	= 'tbl_influencer_instagram';
	protected $youtube_1 		= 'tbl_influencer_youtube';
	protected $blog_1 		= 'tbl_influencer_blog';
	protected $twitter_1 		= 'tbl_influencer_twitter';


	function get_social_account_secure($soc, $id, $username, $user_id){
		if($soc == 'instagram') return $this->get_instagram_by_username_secure($user_id,$username);
		if($soc == 'twitter') return $this->get_twitter_by_username_secure($user_id,$username);
		if($soc == 'blog') return $this->get_blog_by_username_secure($user_id,$username);
		if($soc == 'youtube') return $this->get_youtube_by_username_secure($user_id,$username);
	}


	function create_portfolio($data,$soc){
		if($soc == 'instagram') return $this->create_portfolio_instagram($data);
		if($soc == 'twitter') return $this->create_portfolio_twitter($data);
		if($soc == 'blog') return $this->create_portfolio_blog($data);
		if($soc == 'youtube') return $this->create_portfolio_youtube($data);
	}



	//INSTAGRAM 
	function get_instagram_active_account($id){
		$this->db->where('inf_id',$id);
		$this->db->where('inf_ig_status',true);
		$this->db->where('inf_ig_is_active_account',true);
		$query = $this->db->get($this->instagram);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_instagram_account($id){
		$this->db->where('inf_id',$id);
		/*$this->db->order_by('inf_ig_id','desc');*/
		$this->db->where('inf_ig_is_active_account !=',2);
		$query = $this->db->get($this->instagram);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_instagram_by_username($username){
		$this->db->where('inf_ig_username',$username);
		$query = $this->db->get($this->instagram);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_instagram_by_username_secure($user_id,$username){
		$this->db->where('inf_id',$user_id);
		$this->db->where('inf_ig_username',$username);
		$this->db->or_where('inf_ig_id',$username);
		$query = $this->db->get($this->instagram);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function create_new_inf_instagram($data){
		$query = $this->db->insert($this->instagram,$data);
		$query = $this->db->insert($this->instagram_1,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}

	function update_instagram_account($data,$id,$clean = false){
		$this->db->where('inf_ig_id',$id);
		$query = $this->db->update($this->instagram,$data);
		if($this->db->affected_rows() > 0){
			if($clean) {
				$this->db->where('inf_ig_id',$id);
				$query = $this->db->update($this->instagram_1,$data);
			}
          return true;
        }else{
          return false;
        }	
	}

	function get_instagram_portfolio($inf_id){
		$this->db->where('inf_ig_id',$inf_id);
		$query = $this->db->get($this->ig_portfolio);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function create_portfolio_instagram($data){
		$query = $this->db->insert($this->ig_portfolio,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}



	//TWITTER 

	function get_twitter_active_account($id){
		$this->db->where('inf_id',$id);
		$this->db->where('inf_tw_status',true);
		$this->db->where('inf_tw_is_active_account',true);
		$query = $this->db->get($this->twitter);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_twitter_account($id){
		$this->db->where('inf_id',$id);
		/*$this->db->order_by('inf_ig_id','desc');*/
		$this->db->where('inf_tw_is_active_account !=',2);
		$query = $this->db->get($this->twitter);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_twitter_by_username($username){
		$this->db->where('inf_tw_username',$username);
		$query = $this->db->get($this->twitter);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_twitter_by_twitter_id($id){
		$this->db->where('inf_tw_id_tw',"$id");
		$query = $this->db->get($this->twitter);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_twitter_by_username_secure($user_id,$username){
		$this->db->where('inf_id',$user_id);
		$this->db->where('inf_tw_username',$username);
		$this->db->or_where('inf_tw_id',$username);
		$query = $this->db->get($this->twitter);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function create_new_inf_twitter($data){
		$query = $this->db->insert($this->twitter,$data);
		$query = $this->db->insert($this->twitter_1,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}

	function update_twitter_account($data,$id,$clean = false){
		$this->db->where('inf_tw_id',$id);
		$query = $this->db->update($this->twitter,$data);
		if($this->db->affected_rows() > 0){
			if($clean){
				$this->db->where('inf_tw_id',$id);
				$query = $this->db->update($this->twitter_1,$data);
			}
          	return true;
        }else{
          return false;
        }	
	}

	function get_twitter_portfolio($inf_id){
		$this->db->where('inf_tw_id',$inf_id);
		$query = $this->db->get($this->tw_portfolio);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function create_portfolio_twitter($data){
		$query = $this->db->insert($this->tw_portfolio,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}




	//YOUTUBE 
	function get_youtube_active_account($id){
		$this->db->where('inf_id',$id);
		$this->db->where('inf_yt_status',true);
		$this->db->where('inf_yt_is_active_account',true);
		$query = $this->db->get($this->youtube);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_youtube_account($id){
		$this->db->where('inf_id',$id);
		/*$this->db->order_by('inf_ty_id','desc');*/
		$this->db->where('inf_yt_is_active_account !=',2);
		$query = $this->db->get($this->youtube);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_youtube_by_username($username){
		$this->db->where('inf_yt_id_yt',$username);
		$query = $this->db->get($this->youtube);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_youtube_by_username_secure($user_id,$username){
		$this->db->where('inf_id',$user_id);
		$this->db->where('inf_yt_id_yt',$username);
		$this->db->or_where('inf_yt_id',$username);
		$query = $this->db->get($this->youtube);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function create_new_inf_youtube($data){
		$query = $this->db->insert($this->youtube,$data);
		$query = $this->db->insert($this->youtube_1,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}

	function update_youtube_account($data,$id,$clean = false){
		$this->db->where('inf_yt_id',$id);
		$query = $this->db->update($this->youtube,$data);
		if($this->db->affected_rows() > 0){
			if($clean) {
				$this->db->where('inf_yt_id',$id);
				$query = $this->db->update($this->youtube_1,$data);
			}
          return true;
        }else{
          return false;
        }	
	}

	function get_youtube_portfolio($inf_id){
		$this->db->where('inf_yt_id',$inf_id);
		$query = $this->db->get($this->yt_portfolio);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function create_portfolio_youtube($data){
		$query = $this->db->insert($this->yt_portfolio,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}



	//blog 
	function get_blog_active_account($id){
		$this->db->where('inf_id',$id);
		$this->db->where('inf_bl_status',true);
		$this->db->where('inf_bl_is_active_account',true);
		$query = $this->db->get($this->blog);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_blog_account($id){
		$this->db->where('inf_id',$id);
		/*$this->db->order_by('inf_ig_id','desc');*/
		$this->db->where('inf_bl_is_active_account !=',2);
		$query = $this->db->get($this->blog);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_blog_by_username($username){
		$this->db->where('inf_bl_url',$username);
		$query = $this->db->get($this->blog);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function get_blog_by_username_secure($user_id,$username){
		$this->db->where('inf_id',$user_id);
		$this->db->like('inf_bl_url',$username);
		$this->db->or_like('inf_bl_id',$username);
		$query = $this->db->get($this->blog);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function create_new_inf_blog($data){
		$query = $this->db->insert($this->blog,$data);
		$query = $this->db->insert($this->blog_1,$data); //insert into valid table
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}

	function update_blog_account($data,$id,$clean = false){
		$this->db->where('inf_bl_id',$id);
		$query = $this->db->update($this->blog,$data);
		if($this->db->affected_rows() > 0){
			if($clean){
				$this->db->where('inf_bl_id',$id);
				$query = $this->db->update($this->blog_1,$data);
			}
          return true;
        }else{
          return false;
        }	
	}

	function get_blog_portfolio($inf_id){
		$this->db->where('inf_bl_id',$inf_id);
		$query = $this->db->get($this->bl_portfolio);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function create_portfolio_blog($data){
		$query = $this->db->insert($this->bl_portfolio,$data);
		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }
	}





	function get_account_type($cat = false){
		$this->db->where('categories_parent_id',$cat);
		$query = $this->db->get($this->categories);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_categories($cat = false){
		if($cat) $this->db->where('categories_parent_id',$cat);
		$query = $this->db->get($this->categories);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	
	function get_avoid_ads_in($id){
		$this->db->where('avoid_status',true);
		$this->db->where_in('avoid_id',$id);
		$query = $this->db->get($this->avoids);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_avoid_ads_by_id($id,$type,$avoid = false){
		if($avoid) $this->db->where($this->avoids.'.avoid_id',$avoid);
		
		switch ($type) {
			case 'tw':
				
				$this->db->where('inf_tw_id',$id);
				$this->db->join($this->avoids,$this->avoids.'.avoid_id = '.$this->tw_avoids.'.avoid_id');
				$query = $this->db->get($this->tw_avoids);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'ig':
				
				$this->db->where('inf_ig_id',$id);
				$this->db->join($this->avoids,$this->avoids.'.avoid_id = '.$this->ig_avoids.'.avoid_id');
				$query = $this->db->get($this->ig_avoids);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'yt':
				
				$this->db->where('inf_yt_id',$id);
				$this->db->join($this->avoids,$this->avoids.'.avoid_id = '.$this->yt_avoids.'.avoid_id');
				$query = $this->db->get($this->yt_avoids);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'bl':
				
				$this->db->where('inf_bl_id',$id);
				$this->db->join($this->avoids,$this->avoids.'.avoid_id = '.$this->bl_avoids.'.avoid_id');
				$query = $this->db->get($this->bl_avoids);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			default:
				# code...
				break;
		}
	}

	function get_avoid_ads_content($key,$limit){
		$this->db->where('avoid_status',true);
		$this->db->like('avoid_description',$key);
		$query = $this->db->get($this->avoids,$limit);
		return ($query->num_rows() > 0)? $query->result_array() : null;	
	}

	function insert_avoid($data){
		switch ($type) {
			case 'tw':
				$query = $this->db->insert($this->tw_avoid,$data);
				break;
			case 'ig':
				$query = $this->db->insert($this->ig_avoid,$data);
				break;
			case 'yt':
				$query = $this->db->insert($this->yt_avoid,$data);
				break;
			case 'bl':
				$query = $this->db->insert($this->bl_avoid,$data);
				break;
			default:
				# code...
				break;
		}

		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }	
	}

	function insert_new_avoid($data){
		$query = $this->db->insert($this->avoids,$data);
		if($this->db->affected_rows() > 0){
          return $this->db->insert_id();
        }else{
          return false;
        }	
	}


	function get_topic_content($key,$limit){
		$this->db->like('content_and_topic',$key);
		$query = $this->db->get($this->topics,$limit);
		return ($query->num_rows() > 0)? $query->result_array() : null;	
	}

	function get_topics_in($id){
		$this->db->where_in('content_and_topic_id',$id);
		$query = $this->db->get($this->topics);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_topic_by_id($id,$type,$topic_id = false){
		if($topic_id) $this->db->where($this->topics.'.content_and_topic_id',$topic_id);
		
		switch ($type) {
			case 'tw':
				/*$this->db->where_in('content_and_topic_id',$id);
				$query = $this->db->get($this->topics);
				return ($query->num_rows() > 0)? $query->result() : null;*/

				$this->db->where('inf_tw_id',$id);
				$this->db->join($this->topics,$this->topics.'.content_and_topic_id = '.$this->tw_topics.'.content_and_topic_id');
				$query = $this->db->get($this->tw_topics);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'ig':
				
				$this->db->where('inf_ig_id',$id);
				$this->db->join($this->topics,$this->topics.'.content_and_topic_id = '.$this->ig_topics.'.content_and_topic_id');
				$query = $this->db->get($this->ig_topics);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'yt':
				
				$this->db->where('inf_yt_id',$id);
				$this->db->join($this->topics,$this->topics.'.content_and_topic_id = '.$this->yt_topics.'.content_and_topic_id');
				$query = $this->db->get($this->yt_topics);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'bl':
				
				$this->db->where('inf_bl_id',$id);
				$this->db->join($this->topics,$this->topics.'.content_and_topic_id = '.$this->bl_topics.'.content_and_topic_id');
				$query = $this->db->get($this->bl_topics);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			default:
				# code...
				break;
		}

	}

	function insert_topic($data,$type){
		switch ($type) {
			case 'tw':
				$query = $this->db->insert($this->tw_topics,$data);
				break;
			case 'ig':
				$query = $this->db->insert($this->ig_topics,$data);
				break;
			case 'yt':
				$query = $this->db->insert($this->yt_topics,$data);
				break;
			case 'bl':
				$query = $this->db->insert($this->bl_topics,$data);
				break;
			default:
				# code...
				break;
		}

		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }	
	}

	function insert_new_topic($data){
		$query = $this->db->insert($this->topics,$data);
		if($this->db->affected_rows() > 0){
          return $this->db->insert_id();
        }else{
          return false;
        }	
	}




	function get_brand_content($key,$limit){
		$this->db->like('brand',$key);
		$query = $this->db->get($this->brands,$limit);
		return ($query->num_rows() > 0)? $query->result_array() : null;	
	}

	function get_brands_in($id){
		$this->db->where_in('brand_id',$id);
		$query = $this->db->get($this->brands);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_brand_by_id($id,$type,$brand_id = false){
		if($brand_id) $this->db->where($this->brands.'.brand_id',$brand_id);
		
		switch ($type) {
			case 'tw':
				
				$this->db->where('inf_tw_id',$id);
				$this->db->join($this->brands,$this->brands.'.brand_id = '.$this->tw_brands.'.brand_id');
				$query = $this->db->get($this->tw_brands);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'ig':
				
				$this->db->where('inf_ig_id',$id);
				$this->db->join($this->brands,$this->brands.'.brand_id = '.$this->ig_brands.'.brand_id');
				$query = $this->db->get($this->ig_brands);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'yt':
				
				$this->db->where('inf_yt_id',$id);
				$this->db->join($this->brands,$this->brands.'.brand_id = '.$this->yt_brands.'.brand_id');
				$query = $this->db->get($this->yt_brands);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'bl':
				
				$this->db->where('inf_bl_id',$id);
				$this->db->join($this->brands,$this->brands.'.brand_id = '.$this->bl_brands.'.brand_id');
				$query = $this->db->get($this->bl_brands);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			default:
				# code...
				break;
		}
	}

	function insert_brand($data,$type){
		switch ($type) {
			case 'tw':
				$query = $this->db->insert($this->tw_brands,$data);
				break;
			case 'ig':
				$query = $this->db->insert($this->ig_brands,$data);
				break;
			case 'yt':
				$query = $this->db->insert($this->yt_brands,$data);
				break;
			case 'bl':
				$query = $this->db->insert($this->bl_brands,$data);
				break;
			default:
				# code...
				break;
		}

		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }	
	}

	function insert_new_brand($data){
		$query = $this->db->insert($this->brands,$data);
		if($this->db->affected_rows() > 0){
          return $this->db->insert_id();
        }else{
          return false;
        }	
	}


	//occupatin
	function get_occupation_content($key,$limit){
		$this->db->like('occupation',$key);
		$query = $this->db->get($this->occupation,$limit);
		return ($query->num_rows() > 0)? $query->result_array() : null;	
	}

	function get_occupation_in($id){
		$this->db->where_in('occupation_id',$id);
		$query = $this->db->get($this->occupation);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_occupation_by_id($id,$type,$occupation_id = false){
		if($brand_id) $this->db->where($this->occupation.'.occupation_id',$occupation_id);
		
		switch ($type) {
			case 'tw':
				
				$this->db->where('inf_tw_id',$id);
				$this->db->join($this->occupation,$this->occupation.'.occupation_id = '.$this->tw_occupation.'.occupation_id');
				$query = $this->db->get($this->tw_occupation);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'ig':
				
				$this->db->where('inf_ig_id',$id);
				$this->db->join($this->occupation,$this->occupation.'.occupation_id = '.$this->ig_brands.'.occupation_id');
				$query = $this->db->get($this->ig_brands);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'yt':
				
				$this->db->where('inf_yt_id',$id);
				$this->db->join($this->occupation,$this->occupation.'.occupation_id = '.$this->yt_occupation.'.occupation_id');
				$query = $this->db->get($this->yt_occupation);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			case 'bl':
				
				$this->db->where('inf_bl_id',$id);
				$this->db->join($this->occupation,$this->occupation.'.occupation_id = '.$this->bl_occupation.'.brand_id');
				$query = $this->db->get($this->bl_occupation);
				return ($query->num_rows() > 0)? $query->result() : null;

				break;
			default:
				# code...
				break;
		}
	}

	function insert_occupation($data,$type){
		switch ($type) {
			case 'tw':
				$query = $this->db->insert($this->tw_occupation,$data);
				break;
			case 'ig':
				$query = $this->db->insert($this->ig_occupation,$data);
				break;
			case 'yt':
				$query = $this->db->insert($this->yt_occupation,$data);
				break;
			case 'bl':
				$query = $this->db->insert($this->bl_occupation,$data);
				break;
			default:
				# code...
				break;
		}

		if($this->db->affected_rows() > 0){
          return true;
        }else{
          return false;
        }	
	}

	function insert_new_occupation($data){
		$query = $this->db->insert($this->occupation,$data);
		if($this->db->affected_rows() > 0){
          return $this->db->insert_id();
        }else{
          return false;
        }	
	}


	function disconnect_social_media($id,$type,$inf_id){
		switch ($type) {
			case 'twitter':
				$this->db->where('inf_tw_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->twitter,array('inf_tw_is_active_account'=>2));
				if($this->db->affected_rows() > 0){
		          	$this->db->where('inf_tw_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->twitter_1,array('inf_tw_is_active_account'=>2));
					if($this->db->affected_rows() > 0){
						return true;
					}else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			case 'instagram':
				$this->db->where('inf_ig_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->instagram,array('inf_ig_is_active_account'=>2));
				if($this->db->affected_rows() > 0){
		          	$this->db->where('inf_ig_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->instagram_1,array('inf_ig_is_active_account'=>2));
					if($this->db->affected_rows() > 0){
						return true;
					}
					else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			case 'blog':
				$this->db->where('inf_bl_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->blog,array('inf_bl_is_active_account'=>2));
				if($this->db->affected_rows() > 0){
		          $this->db->where('inf_bl_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->blog_1,array('inf_bl_is_active_account'=>2));
					if($this->db->affected_rows() > 0){
						return true;
					}
					else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			case 'youtube':
				$this->db->where('inf_yt_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->youtube,array('inf_yt_is_active_account'=>2));
				if($this->db->affected_rows() > 0){
		          $this->db->where('inf_yt_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->youtube_1,array('inf_yt_is_active_account'=>2));
					if($this->db->affected_rows() > 0){
						return true;
					}
					else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			default:
				return false;
				break;
		}
	}

	function reconnect_social_media($id,$type,$inf_id){
		switch ($type) {
			case 'twitter':
				$this->db->where('inf_tw_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->twitter,array('inf_tw_status'=>true));
				if($this->db->affected_rows() > 0){
		          	$this->db->where('inf_tw_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->twitter_1,array('inf_tw_status'=>true));
					if($this->db->affected_rows() > 0){
						return true;
					}else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			case 'instagram':
				$this->db->where('inf_ig_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->instagram,array('inf_ig_status'=>true));
				if($this->db->affected_rows() > 0){
		          	$this->db->where('inf_ig_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->instagram_1,array('inf_ig_status'=>true));
					if($this->db->affected_rows() > 0){
						return true;
					}
					else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			case 'blog':
				$this->db->where('inf_bl_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->blog,array('inf_bl_status'=>true));
				if($this->db->affected_rows() > 0){
		          $this->db->where('inf_bl_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->blog_1,array('inf_bl_status'=>true));
					if($this->db->affected_rows() > 0){
						return true;
					}
					else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			case 'youtube':
				$this->db->where('inf_yt_id',$id);
				$this->db->where('inf_id',$inf_id);
				$query = $this->db->update($this->youtube,array('inf_yt_status'=>true));
				if($this->db->affected_rows() > 0){
		          $this->db->where('inf_yt_id',$id);
					$this->db->where('inf_id',$inf_id);
					$query = $this->db->update($this->youtube_1,array('inf_yt_status'=>true));
					if($this->db->affected_rows() > 0){
						return true;
					}
					else{
						return false;
					}
		        }else{
		          return false;
		        }
				break;
			default:
				return false;
				break;
		}
	}

	//delete portfolio
	function get_media_portfolio($social_id,$id,$soc){
		if($soc == 'tw') $table = $this->tw_portfolio;
		elseif($soc == 'ig') $table = $this->ig_portfolio;
		elseif($soc == 'bl') $table = $this->bl_portfolio;
		elseif($soc == 'yt') $table = $this->yt_portfolio;

		$this->db->where('assets_id',$id);
		$this->db->where('inf_'.$soc.'_id',$social_id);
		$query = $this->db->get($table);
		return ($query->num_rows() > 0)? $query->row() : null;
	}

	function delete_media_portfolio($id,$soc){
		if($soc == 'tw') $table = $this->tw_portfolio;
		elseif($soc == 'ig') $table = $this->ig_portfolio;
		elseif($soc == 'bl') $table = $this->bl_portfolio;
		elseif($soc == 'yt') $table = $this->yt_portfolio;
		
		$this->db->where('assets_id',$id);
		$query = $this->db->delete($table);
		return ($this->db->affected_rows() > 0)? true : false;
	}

	

	







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

	function get_language_list(){
		$query = $this->db->get($this->language);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_country_list(){
		$query = $this->db->get($this->country);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_religion_list(){
		$query = $this->db->get($this->religion);
		return ($query->num_rows() > 0)? $query->result() : null;
	}

	function get_ethnic_list(){
		$query = $this->db->get($this->ethnic);
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




}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */