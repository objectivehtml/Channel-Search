<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'channel_search/libraries/Channel_search_base_lib.php';

class Channel_search_export extends Channel_search_base_lib {
			
	/**
	 * Base Path
	 * 
	 * @var string
	 */
	 
	protected $base_path;
	
		
	/**
	 * Base Class Name
	 * 
	 * @var string
	 */
	 
	protected $base_class = 'Base_export';
	
	
	/**
	 * Base Class Directory Path
	 * 
	 * @var string
	 */
	 
	protected $base_class_dir;
		
	/**
	 * Base Directory name
	 * 
	 * @var string
	 */
	 
	protected $base_directory = 'export/';
	
	
	/**
	 * Class Suffix
	 * 
	 * @var string
	 */
	 
	protected $class_suffix = '_channel_search_export';
	
		
	/**
	 * Default Rule
	 * 
	 * @var string
	 */
	 
	protected $default_object = 'csv';
		
		
	/**
	 * Default Rule Dropdown OPtion
	 * 
	 * @var string
	 */
	 
	
	protected $default_dropdown_option = '-Select a Driver-';
			
	/**
	 * Rules
	 * 
	 * @var string
	 */
	 
	protected $rules = array();
	
				
	/**
	 * Reserved files not to be included
	 * 
	 * @var string
	 */
	 
	protected $reserved_files = array('Default');
		
	
	/**
	 * The id of the current search
	 * 
	 * @var string
	 */

	public $id = null;

	
	/**
	 * Construct
	 *
	 * @access	public
	 * @param	array 	Dynamically set properties
	 * @return	void
	 */
	
	public function __construct($data = array(), $debug = FALSE)
	{
		$this->base_class_dir = PATH_THIRD . 'channel_search/libraries/Base_export.php';
		
		parent::__construct($data, $debug);
	}
	
		
	/**
	 * Build a dropdown with all the available drivers
	 *
	 * @access	public
	 * @param	mixed    The field name
	 * @param	mixed    The selected value
	 * @param	array    An associative array of HTML attributes
	 * @return	mixed
	 */
	
	public function dropdown($name = 'rule', $selected = FALSE, $attributes = array())
	{
		return parent::dropdown($name, $selected, $attributes);
	}
	
	
	/**
	 * Get a single driver from the directory
	 *
	 * @access	public
	 * @param	mixed    A valid index or rule name
	 * @return	mixed
	 */
	
	public function get_driver($index = FALSE)
	{		
		return parent::get_object($index);
	}
	
	
	/**
	 * Get the available drivers from the directory
	 *
	 * @access	public
	 * @return	array
	 */
	
	public function get_drivers()
	{
		return parent::get_objects();
	}	
	
	
	/**
	 * Total Rules
	 *
	 * @access	public
	 * @return	int
	 */
	
	public function total_drivers()
	{
		return parent::total_objects();
	}
	
			
	/**
	 * Trigger the export method
	 *
	 * @access	public
	 * @param	string  A name of the export driver to trigger
	 * @param 	object  A database object of the query
	 * @param   array   An array of rule objects
	 * @param   string  The search's user created id
	 * @return	int
	 */
	
	public function trigger($driver, $data, $rules)
	{	
		$trigger_obj = FALSE;
		
		foreach($this->get_drivers() as $driver_obj)
		{
			if($driver_obj->get_trigger() == $driver)
			{
				$trigger_obj = $driver_obj;
			}
		}
		
		if(!$trigger_obj)
		{
			$trigger_obj = $this->get_driver($this->default_object);	
		}

		$trigger_obj->set_id($this->id);
		
		return $trigger_obj->export($data, $rules);		
	}
	
	
	/**
	 * Load
	 *
	 * @access	public
	 * @param	string  A valid file name
	 * @return	mixed
	 */
	
	public function load($file, $params = array())
	{
		return parent::load($file, $params);
	}
	
}