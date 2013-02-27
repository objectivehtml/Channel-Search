<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search_rules {
			
	/**
	 * Base Path
	 * 
	 * @var string
	 */
	 
	protected $base_path;
	
			
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
		$this->base_path = PATH_THIRD . 'channel_search/';
		
		$this->EE =& get_instance();
	}
	
	
	public function dropdown($name = 'rule', $selected = FALSE, $attributes = array())
	{
		$this->EE->load->helper('form');
		
		$rules = $this->get_rules();
		
		$options = array('' => '-Select a Rule-');
		
		foreach($rules as $rule)
		{
			$options[$rule->get_name()] = $rule->get_title();
		}
		
		return form_dropdown($name, $options, $selected, InterfaceBuilder::attr($attributes));
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
		$this->rules = $this->get_rules();
		
		if($index && is_int($index))
		{
			if(!isset($this->rules[$index]))
			{
				return $index;
			}
			
			return $this->rules[$index];
		}
		else
		{
			foreach($this->rules as $x => $obj)
			{
				$rule = rtrim(get_class($obj), $this->class_suffix);
								
				if($index == $obj->get_name() || $index == $obj->get_title())
				{
					return $this->rules[$x];
				}
			}
		}
				
		return $this->get_rule(ucfirst(rtrim($this->default_rule, '.php')));
	}
	
	
	/**
	 * Get the available rules from the directory
	 *
	 * @access	public
	 * @return	array
	 */
	
	public function get_rules()
	{
		$this->EE->load->helper('directory');
		
		$rules = array();
		
		$orig_base_path = $this->base_path;
		$base_directory = rtrim($this->base_directory, '/');
		
		foreach(directory_map(PATH_THIRD) as $dir_name => $dir)
		{
			$this->base_path = PATH_THIRD . $dir_name . '/';
			
			if(isset($dir[$base_directory]) && is_array($dir[$base_directory]))
			{
				$dir = $dir[$base_directory];
				
				foreach($dir as $file)
				{		
					if($rule = $this->load($file))
					{	
						$rules[] = $rule;
					}
				}
			}
		}
		
		$this->base_path = $orig_base_path;
		
		return $rules;
	}	
	
	
	/**
	 * Total Rules
	 *
	 * @access	public
	 * @return	int
	 */
	
	public function total_rules()
	{
		$this->rules = $this->get_rules();
		
		return count($this->rules);
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
		if(!class_exists('Base_rule'))
		{
			require_once PATH_THIRD . 'channel_search/libraries/Base_rule.php';	
		}
		
		if(!empty($file))
		{	
			$file = preg_replace('/.php$/', '', $file);
			
			require_once $this->base_path . $this->base_directory . ucfirst($file) . '.php';
			
			$class = $file . $this->class_suffix;
						
			if(class_exists($class))
			{
				$return = new $class($params);
				
				return $return;
			}
		}
		
		return FALSE;
	}
	
}