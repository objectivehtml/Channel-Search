<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search_model extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->driver('channel_data');
	}
	
	public function create_rule($rule)
	{
		$this->db->insert('channel_search_rules', $rule);
		
		return $this->db->insert_id();
	}
	
	public function update_rule($id, $rule)
	{
		$this->db->where('id', $id);
		$this->db->update('channel_search_rules', $rule);
	}
	
	public function delete_rule($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('channel_search_rules');
		
		$this->db->where('rule_id', $id);
		$this->db->delete('channel_search_modifiers');
	}
	
	public function create_modifier($modifier)
	{
		$modifiers = $this->get_modifiers(array(
			'where' => array(
				'rule_id' => $modifier['rule_id']
			)
		));
		
		$modifier['order'] = $modifiers->num_rows();
		
		$this->db->insert('channel_search_modifiers', $modifier);
		
		return $this->db->insert_id();
	}
	
	public function update_modifier($id, $modifier)
	{
		$this->db->where('id', $id);
		$this->db->update('channel_search_modifiers', $modifier);
	}
	
	public function delete_modifier($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('channel_search_modifiers');
	}
	
	public function get_rule_modifiers($rule_id, $params = array())
	{
		return $this->get_modifiers(array_merge($params, array(
			'where' => array(
				'rule_id' => $rule_id
			)
		)));
	}
	
	public function get_modifiers($param = array())
	{
		return $this->channel_data->get('channel_search_modifiers', $param);
	}
	
	public function get_modifier($id, $param = array())
	{
		return $this->get_modifiers(array_merge($param, array(
			'where' => array(
				'id' => $id
			)
		)));
	}
	
	public function get_rules($param = array())
	{
		return $this->channel_data->get('channel_search_rules', $param);
	}
		
	public function get_rule($id)
	{
		return $this->get_rules(array(
			'where' => array(
				'id' => $id
			)
		));
	}	
	
	public function get_rule_by_id($id)
	{
		return $this->get_rules(array(
			'where' => array(
				'search_id' => $id
			)
		));
	}	
}
