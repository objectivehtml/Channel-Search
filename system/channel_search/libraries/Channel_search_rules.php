<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'channel_search/libraries/Channel_search_base_lib.php';

class Channel_search_rules extends Channel_search_base_lib {
			
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
	 
	protected $base_class = 'Base_rule';
	
	
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
	 
	protected $base_directory = 'rules/';
	
	
	/**
	 * Class Suffix
	 * 
	 * @var string
	 */
	 
	protected $class_suffix = '_channel_search_rule';
	
		
	/**
	 * Default Rule
	 * 
	 * @var string
	 */
	 
	protected $default_rule = 'Default';
		
		
	/**
	 * Default Rule Dropdown OPtion
	 * 
	 * @var string
	 */
	 
	
	protected $default_dropdown_option = '-Select a Rule-';
			
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
	 * Construct
	 *
	 * @access	public
	 * @param	array 	Dynamically set properties
	 * @return	void
	 */
	
	public function __construct($data = array(), $debug = FALSE)
	{
		$this->base_class_dir = PATH_THIRD . 'channel_search/libraries/Base_rule.php';
		
		parent::__construct($data, $debug);
	}
	
		
	/**
	 * Build a dropdown with all the available rules
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
	 * Get a single rule from the directory
	 *
	 * @access	public
	 * @param	mixed    A valid index or rule name
	 * @return	mixed
	 */
	
	public function get_rule($index = FALSE)
	{		
		return parent::get_object($index);
	}
	
	
	/**
	 * Get the available rules from the directory
	 *
	 * @access	public
	 * @return	array
	 */
	
	public function get_rules()
	{
		return parent::get_objects();
	}	
	
	
	/**
	 * Total Rules
	 *
	 * @access	public
	 * @return	int
	 */
	
	public function total_rules()
	{
		return parent::total_objects();
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