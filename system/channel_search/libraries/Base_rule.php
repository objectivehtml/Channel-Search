<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(PATH_THIRD . 'channel_search/libraries/InterfaceBuilder/InterfaceBuilder.php');
		
abstract class Base_rule {
	
	protected $title;
	
	protected $name;
	
	protected $header;
		
	protected $fields   = array();
	
	protected $from     = array();
	
	protected $group_by = array();
	
	protected $having   = array();
	
	protected $join     = array();
			
	protected $select   = array();
	
	protected $where    = array();
	
	protected $settings = array();
	
	
	public function __construct()
	{
		
	}
	
	/**
	 * Display the label
	 *
	 * @access	public
	 * @return	string
	 */
	
	public function display_header()
	{
		return '<h4>'.$this->get_header().'</h4>';
	}
	
	
	/**
	 * Display the description
	 *
	 * @access	public
	 * @return	string
	 */
	
	public function display_description()
	{
		return '<p>'.$this->description.'</p>';
	}
	
	
	/**
	 * Display the rules with Interface Builder
	 *
	 * @access	public
	 * @param	array 	The rule data to populate the settings
	 * @param	array 	The an array of properties for to pass to the IB field
	 * @return	string
	 */
	
	public function display_rule($data = array(), $properties = array())
	{
		$default_properties = array(
			'dataArray' => TRUE,
			'varName'   => 'rules'
		);
		
		$properties = array_merge($default_properties, $properties);
		
		return InterfaceBuilder::table($this->fields, $data, $properties, channel_search_attr());
	}
	
	
	/**
	* Get the fields array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_fields()
	{
		return $this->fields;
	}	
	
	
	/**
	* Get the from array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_from()
	{
		return $this->from;
	}	
	
	
	/**
	* Get the group_by array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_group_by()
	{
		return $this->group_by;
	}		
	
	
	/**
	* Get the having array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_having()
	{
		return $this->having;
	}		
	
	
	/**
	* Get the having array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_join()
	{
		return $this->join;
	}
	
	
	/**
	* Get the name of the rule
	*
	* @access	public
	* @return	array
	*/
	
	public function get_name()
	{
		return $this->name;
	}
	
	
	/**
	* Get the channels
	*
	* @access	public
	* @return	array
	*/
	
	public function get_channels()
	{
		return $this->channels;
	}
	
	
	/**
	* Get the title of the rule
	*
	* @access	public
	* @return	array
	*/
	
	public function get_title()
	{
		return $this->title ? $this->title : $this->name;
	}
		
	
	/**
	* Get the description of the rule
	*
	* @access	public
	* @return	array
	*/
	
	public function get_description()
	{
		return $this->description;
	}
	
	
	/**
	* Get the header of the rule
	*
	* @access	public
	* @return	array
	*/
	
	public function get_header()
	{
		return $this->header ? $this->header : $this->get_title();
	}
	
	
	/**
	* Get the select array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_select()
	{
		return $this->select;
	}
	
	
	/**
	* Get the select settings
	*
	* @access	public
	* @return	array
	*/
	
	public function get_settings()
	{
		return $this->settings;
	}
	
	
	/**
	* Get the where array
	*
	* @access	public
	* @return	array
	*/
	
	public function get_where()
	{
		return $this->where;
	}
	
	
	/**
	* Sets the fields array
	*
	* @access	public
	* @param	array 	An array storing the fields
	* @return	null
	*/
	
	public function set_fields($fields)
	{
		$this->fields = $fields;
	}
		
	
	/**
	* Sets the channels array
	*
	* @access	public
	* @param	array 	An array storing the fields
	* @return	null
	*/
	
	public function set_channels($channels)
	{
		$this->channels = $channels;
	}
	
	
	/**
	* Sets the FROM array by merging with the existing array
	*
	* @access	public
	* @param	array 	An array storing the from statements
	* @param	bool 	If TRUE, the from array will be replace with the set array
	* @return	null
	*/
	
	public function set_from($from = array(), $override = FALSE)
	{
		$this->set('from', $from, $override);
	}
	
	
	/**
	* Sets the GROUP BY array by merging with the existing array
	*
	* @access	public
	* @param	array 	An array storing the select statements
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set_group_by($group_by = array(), $override = FALSE)
	{
		$this->set('group_by', $group_by, $override);
	}
	
	
	/**
	* Sets the HAVING array by merging with the existing array
	*
	* @access	public
	* @param	array 	An array storing the select statements
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set_having($having = array(), $override = FALSE)
	{
		$this->set('having', $having, $override);
	}
	
	
	/**
	* Sets the JOIN array by merging with the existing array
	*
	* @access	public
	* @param	array 	An array storing the select statements
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set_join($join = array(), $override = FALSE)
	{
		$this->set('join', $join, $override);
	}
	
	
	/**
	* Sets the settings array 
	*
	* @access	public
	* @param	array 	An array storing the select statements
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set_settings($settings = array())
	{
		$this->settings = $settings;
	}
	
	
	/**
	* Sets the SELECT array by merging with the existing array
	*
	* @access	public
	* @param	array 	An array storing the select statements
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set_select($select = array(), $override = FALSE)
	{
		$this->set('select', $select, $override);
	}
	
		
	/**
	* Sets the WHERE array by merging with the existing array
	*
	* @access	public
	* @param	array 	An array storing the select statements
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set_where($where = array(), $override = FALSE)
	{
		$this->set('where', $select, $override);
	}
			
	
	/**
	* Set a property
	*
	* @access	public
	* @param	string 	The name of the property you want to set
	* @param	array 	The array you want to set
	* @param	bool 	If TRUE, the select array will be replace with the set array
	* @return	null
	*/
	
	public function set($prop, $value = array(), $override = FALSE)
	{
		if(!$override)
		{
			$this->$prop = array_merge($this->$prop, $value);
		}
		else
		{
			$this->$prop = $value;
		}
	}
	
	public function clean_sql($sql = '')
	{
		if(is_array($sql))
		{
			$sql = implode(' ', $sql);
		}
		
		foreach(array('AND', 'OR') as $value)
		{
			$sql = ltrim($sql, $value);
		}
		
		return trim($sql);
	}
	
	public function trim_array($array)
	{
		foreach($array as $index => $value)
		{
			if(is_array($value))
			{
				$array[$index] = $this->trim_array($value);
			}
			else if(is_string($value))
			{
				$array[$index] = trim($value);
			}
		}
		
		return $array;
	}
}