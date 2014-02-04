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

	public function get_data($sql)
	{	
		$EE =& get_instance();
		
		$EE->config->load('channel_search_config');
		
		$exclude = config_item('channel_search_export_exclude_fields');

		$data = array();

		$response = $this->query($sql)->result_array();

		if(is_array($exclude) && count($exclude))
		{
			foreach($response as $index => $row)
			{	
				foreach($row as $field => $value)
				{
					if(!in_array($field, $exclude))
					{
						$data[$index][$field] = $value;
					}
				}
			}
		}
		
		return $data;
	}

	public function output_xls($sql)
	{		
		$EE =& get_instance();	
		$EE->load->library('excel_xml');
		
		$filename = "search-results-".date('Y-m-d-H-i', time());
		$fields = array();
		$data = $this->get_data($sql);

		foreach($data[0] as $field => $value)
		{
			$fields[] = $field;
		}

		$xls = new Excel_XML;
		$xls->addArray(array(1 => $fields));
		$xls->addArray($data);
		$xls->generateXML($filename);

		exit();
	}

	public function output_csv($sql)
	{		
		$EE =& get_instance();		
		$EE->load->dbutil();

		$filename = "search-results-".date('Y-m-d-H-i', time()).".csv";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$filename."\";" );
		header("Content-Transfer-Encoding: binary"); 

		echo $EE->dbutil->csv_from_result($this->query($sql));
		exit();
	}
	
	public function output_json($sql)
	{
		header("Content-Type: application/json");		
		echo(json_encode($this->get_data($sql)));
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