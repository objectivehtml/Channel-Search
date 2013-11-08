<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Base_export {
	
	protected $title;
	
	protected $name;
	
	protected $trigger;
	
	abstract public function export($data, $rules = array());
	
	public function query($sql)
	{
		$EE =& get_instance();
		
		return $EE->db->query($sql);
	}
	
	public function output_csv($sql)
	{		
		$EE =& get_instance();		
		$EE->load->dbutil();
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"search-results-".date('Y-m-d-H-i', time()).".csv\";" );
		header("Content-Transfer-Encoding: binary"); 
		 
		echo $EE->dbutil->csv_from_result($this->query($sql));
		exit();
	}
	
	public function output_json($sql)
	{
		header("Content-Type: application/json");		
		echo(json_encode($this->query($sql)->result()));
		exit();		
	}
	
	public function set_name($name)
	{
		$this->name = $name;	
	}
	
	public function set_title($title)
	{
		$this->title = $title;	
	}
	
	public function set_trigger($trigger)
	{
		$this->trigger = $trigger;	
	}
	
	public function get_name()
	{
		return $this->name;
	}
	
	public function get_title()
	{
		return $this->title;
	}
	
	public function get_trigger()
	{
		return $this->trigger;
	}
}