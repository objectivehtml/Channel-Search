<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search_history extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->driver('channel_data');
	}
	
	public function insert_history($search_id, $form_data = array(), $sql = NULL)
	{
		$terms = $this->_term_string($form_data);
		
		$data = array(
			'site_id'    => config_item('site_id'),
			'search_id'  => $search_id,
			'ip_address' => $this->input->ip_address(),
			'member_id'  => $this->session->userdata('member_id'),
			'terms'      => $terms,
			'params'     => json_encode($form_data),
			'query'      => $this->_build_query($form_data),
			'sql'        => $sql,
			'date'		 => date('Y-m-d H:i:s', time())
		);
		
		$this->db->insert('channel_search_history', $data);
		
		if(empty($terms))
		{
			return;	
		}
		
		$this->insert_terms($this->_build_terms($form_data), $search_id, $this->db->insert_id());		
	}
	
	public function insert_terms($terms, $search_id, $history_id = 0)
	{
		$data = array();
		
		foreach($terms as $index => $term)
		{
			preg_match_all('/(\S*)/u', $term, $matches);
			
			$total_words = 0;
			
			foreach($matches[0] as $match)
			{
				if(!empty($match))
				{
					$total_words++;
				}
			}
			
			$data[$index]['term']        = $term;
			$data[$index]['site_id']     = config_item('site_id');
			$data[$index]['search_id']   = $search_id;
			$data[$index]['history_id']  = $history_id;
			$data[$index]['ip_address']  = $this->session->userdata('ip_address');
			$data[$index]['member_id']   = $this->session->userdata('member_id');
			$data[$index]['total_chars'] = strlen($term);
			$data[$index]['total_words'] = $total_words;
			$data[$index]['date'] 		 = date('Y-m-d H:i:s', time());
		}
		
		$this->db->insert_batch('channel_search_terms', $data);
	}
	
	public function get($params = array(), $default_params = array(), $all_sites = FALSE)
	{		
		$default = array_merge(array(
			'order_by' => 'date',
			'sort' => 'desc'
		), $default_params);
		
		$params = array_merge($default_params, $params);
		
		if(!$all_sites)
		{
			$params['where']['site_id'] = config_item('site_id');
		}
		
		if(isset($params['where']['search']) && !$params['where']['search_id'])
		{
			unset($params['where']['search_id']);
		}
		
		return $this->channel_data->get('channel_search_history', $params);
	}
	
	public function get_history($id)
	{
		return $this->get(array(
			'where' => array(
				'id' => $id
			)
		));
	}
	
	public function get_member_history($member_id, $search_id = FALSE, $params = array())
	{
		return $this->get(array(
			'where' => array(
				'member_id' => $member_id,
				'search_id' => $search_id
			)
		), $params);		
	}
	
	public function get_ip_history($ip_address, $search_id = FALSE, $params = array())
	{
		return $this->get(array(
			'where' => array(
				'ip_address' => $ip_address,
				'search_id'  => $search_id
			)
		), $params);
	}
	
	public function get_search_history($search_id = FALSE, $params = array())
	{	
		return $this->get(array(
			'where' => array(
				'search_id'  => $search_id
			)
		), $params);
	}
	
	public function get_terms($params = array(), $default_params = array())
	{
		$default = array_merge(array(
			'order_by' => 'date',
			'sort' => 'desc'
		), $default_params);
		
		
		foreach($default as $index => $value)
		{
			if(!isset($params[$index]))
			{
				$params[$index] = $value;
			}
		}
		
		if(!isset($params['where']['site_id']))
		{
			$params['where']['site_id'] = config_item('site_id');
		}
		
		return $this->channel_data->get('channel_search_terms', $params);
	}
	
	public function get_terms_by_popularity($search_id = FALSE, $params = array())
	{
		$default_params = array(
			'order_by' => 'total desc, date desc',
			'sort'     => '',
			'select'   => 'term, count(term) as \'total\', date as \'last_used\', total_chars, total_words',
			'group_by' => 'term'
		);
		
		if($search_id)
		{
			$params['where']['search_id'] = $search_id;
		}
		
		return $this->get_terms($params, $default_params);
	}
	
	public function last_query($search_id = FALSE)
	{
		$return = $this->most_recent_search($search_id);
		
		return isset($return->query) ? $return->query : FALSE;
	}
	
	public function last_sql($search_id = FALSE)
	{
		$return = $this->most_recent_search($search_id);
		
		return isset($return->sql) ? $return->sql : FALSE;
	}
	
	public function last_terms($search_id = FALSE)
	{
		$return = $this->most_recent_search($search_id);
		
		return isset($return->terms) ? $return->terms : FALSE;
	}
	
	public function last_params($search_id = FALSE)
	{
		$return = $this->most_recent_search($search_id);
		
		return isset($return->params) ? $return->params : FALSE;
	}
		
	public function total_searches($search_id = FALSE)
	{
		return $this->get_search_history($search_id)->num_rows();
	}
	
	public function total_members_searches($member_id, $search_id = FALSE)
	{
		return $this->get_search_history($member_id, $search_id)->num_rows();
	}
	
	public function total_ip_searches($ip_address, $search_id = FALSE)
	{
		return $this->get_search_history($ip_address, $search_id)->num_rows();
	}
	
	public function total_terms($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id)->num_rows();
	}
	
	public function most_recent_search($search_id = FALSE)
	{
		return $this->get_search_history($search_id, array('order_by' => 'date', 'sort' => 'desc', 'limit' => 1))->row();
	}
	
	public function least_recent_search($search_id = FALSE)
	{
		return $this->get_search_history($search_id, array('order_by' => 'date', 'sort' => 'asc', 'limit' => 1))->row();
	}
	
	public function term_most_characters($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'total_chars desc, date desc'))->row();
	}
	
	public function term_most_words($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'total_words desc, date desc'))->row();
	}
	
	public function term_least_characters($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'total_chars asc, date desc'))->row();
	}
	
	public function term_least_words($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'total_words asc, date desc'))->row();
	}
	
	public function most_recent_term($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id)->row();
	}
	
	public function most_recent_terms($search_id = FALSE, $limit = 3)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'date', 'sort' => 'desc', 'limit' => $limit))->result();
	}
	
	public function least_recent_term($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'date', 'sort' => 'asc'))->row();
	}
	
	public function least_recent_terms($search_id = FALSE, $limit = 3)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'date', 'desc', 'limit' => $limit))->result();
	}
	
	public function most_popular_term($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id)->row('term');	
	}
	
	public function most_popular_terms($search_id = FALSE, $limit = 3)
	{
		return $this->get_terms_by_popularity($search_id, array('limit' => $limit))->result();
	}
		
	public function least_popular_term($search_id = FALSE)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'total asc, date asc', 'limit' => 1))->row('term');
	}
	
	public function least_popular_terms($search_id = FALSE, $limit = 3)
	{
		return $this->get_terms_by_popularity($search_id, array('order_by' => 'total asc, date asc', 'limit' => $limit))->result();	
	}
	
	private function _build_query($form_data)
	{
		foreach($form_data as $index => $value)
		{
			if(empty($value))
			{
				unset($form_data[$index]);
			}
		}
		
		return http_build_query($form_data);
	}
	
	private function _build_terms($form_data = array())
	{
		$terms = array();
		
		foreach($form_data as $data)
		{
			if(!empty($data))
			{
				if(!is_array($data) && !is_object($data))
				{
					$terms[] = trim($data);
				}
			}
		}
		
		return $terms;
	}
	
	private function _term_string($form_data = array())
	{
		$form = array();
		
		foreach($form_data as $index => $data)
		{
			if(!is_array($data) && !is_object($data))
			{
				$form[$index] = $data;
			}
		}
		
		return trim(str_replace("\n\n", "\n", implode("\n", $form)));
	}
	
}