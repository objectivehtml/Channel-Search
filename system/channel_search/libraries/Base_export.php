<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Base_export {
	
	protected $title;
	
	protected $name;
	
	protected $trigger;
	
	protected $id;
	
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
		
		$columns = config_item('channel_search_export_columns');

		$exclude = config_item('channel_search_export_exclude_fields');

		$data = array();

		$response = $this->query($sql)->result_array();

		if(isset($columns[$this->id]))
		{
			$new_response = array();

			foreach($response as $row_index =>  $row)
			{
				foreach($columns[$this->id] as $column)
				{
					if(isset($row[$column]))
					{
						$new_response[$row_index][$column] = $row[$column];
					}
				}
			}

			$response = $new_response;
		}

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

	/**
	 * Generate CSV from a query result object
	 *
	 * @access	public
	 * @param	object	The query result object
	 * @param	string	The delimiter - comma by default
	 * @param	string	The newline character - \n by default
	 * @param	string	The enclosure - double quote by default
	 * @return	string
	 */
	function csv_from_sql($sql, $delim = ",", $newline = "\n", $enclosure = '"')
	{
		$data = $this->get_data($sql);

		$out = '';

		// First generate the headings from the table column names
		foreach ($data[0] as $name => $value)
		{
			$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $name).$enclosure.$delim;
		}

		$out = rtrim($out);
		$out .= $newline;

		// Next blast through the result array and build out the rows
		foreach ($data as $row)
		{
			foreach ($row as $item)
			{
				$out .= $enclosure.str_replace($enclosure, $enclosure.$enclosure, $item).$enclosure.$delim;
			}
			$out = rtrim($out);
			$out .= $newline;
		}

		return $out;
	}

	public function output_csv($sql)
	{		
		$filename = "search-results-".date('Y-m-d-H-i', time()).".csv";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$filename."\";" );
		header("Content-Transfer-Encoding: binary"); 

		echo $this->csv_from_sql($sql);
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
	
	public function set_id($id)
	{
		$this->id = $id;	
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
	
	public function get_id()
	{
		return $this->id;
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