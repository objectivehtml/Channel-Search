<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search_model extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->driver('channel_data');
	}
	
	public function get_rules($id)
	{
		$rules = $this->get_settings(array(
			'where' => array(
				'search_id' => $id
			)
		));
		
		if($rules->num_rows() == 0)
		{
			$this->output->show_user_error('general', array(
				'\''.$id.'\' is not a valid id.'
			));
		}
		
		$rules = $rules->row();
		
		$rules->channel_names = explode('|', $rules->channel_names);
		$rules->rules         = json_decode($rules->rules);
		
		return $rules;
	}
	
	public function get_settings($param = array())
	{
		return $this->channel_data->get('channel_search_rules', $param);
	}
		
	public function get_setting($id)
	{
		return $this->get_settings(array(
			'where' => array(
				'id' => $id
			)
		));
	}
		
	public function save_settings($data)
	{
		$existing_records = $this->db->get_where('channel_search_rules', array(
			'id' => isset($data['id']) ? $data['id'] : 0
		));
		
		if($existing_records->num_rows() == 0)
		{
			$this->db->insert('channel_search_rules', $data);
		}
		else
		{
			$this->db->where('id', $data['id']);		
			$this->db->update('channel_search_rules', $data);
		}
	}
	
	public function delete_setting($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('channel_search_rules');
	}
	
}