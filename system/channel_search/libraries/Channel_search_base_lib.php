<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'channel_search/libraries/Channel_search_base_lib.php';

if(!class_exists('Channel_search_base_lib'))
{
	abstract class Channel_search_base_lib {
				
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
		 
		protected $base_class;
		
			
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
		 
		protected $base_directory;
		
		
		/**
		 * Class Suffix
		 * 
		 * @var string
		 */
		 
		protected $class_suffix;
		
			
		/**
		 * Default Object
		 * 
		 * @var string
		 */
		 
		protected $default_object = 'Default';
		
		
		/**
		 * Default Dropdown Option
		 * 
		 * @var string
		 */
		 
		protected $defaut_dropdown_option;
		
					
		/**
		 * Objects
		 * 
		 * @var string
		 */
		 
		protected $objects = array();
		
					
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
		
		
		/**
		 * Build a dropdown with all the available objects
		 *
		 * @access	public
		 * @param	mixed    The field name
		 * @param	mixed    The selected value
		 * @param	array    An associative array of HTML attributes
		 * @return	mixed
		 */
		
		public function dropdown($name, $selected = FALSE, $attributes = array())
		{
			$this->EE->load->helper('form');
			
			$objects = $this->get_objects();
			
			$options = array('' => $this->default_dropdown_option);
			
			foreach($objects as $obj)
			{
				$options[$obj->get_name()] = $obj->get_title();
			}
			
			return form_dropdown($name, $options, $selected, InterfaceBuilder::attr($attributes));
		}
		
		
		/**
		 * Get a single object from the directory
		 *
		 * @access	public
		 * @param	mixed    A valid index or object name
		 * @return	mixed
		 */
		
		public function get_object($index = FALSE)
		{	
			$this->objects = $this->get_objects();
			
			if($index && is_int($index))
			{
				if(!isset($this->objects[$index]))
				{
					return $index;
				}
				
				return $this->objects[$index];
			}
			else
			{
				foreach($this->objects as $x => $obj)
				{
					$object = rtrim(get_class($obj), $this->class_suffix);
				
					if($index == $obj->get_name() || $index == $obj->get_title())
					{
						return $this->objects[$x];
					}
				}
			}
							
			return $this->get_object(ucfirst(rtrim($this->default_object, '.php')));
		}
		
		
		/**
		 * Get the available objects from the directory
		 *
		 * @access	public
		 * @return	array
		 */
		
		public function get_objects()
		{
			$this->EE->load->helper('directory');
			
			$objects = array();
			
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
						if($object = $this->load($file))
						{	
							$objects[] = $object;
						}
					}
				}
			}
			
			$this->base_path = $orig_base_path;
			
			return $objects;
		}	
		
		
		/**
		 * Total Objects
		 *
		 * @access	public
		 * @return	int
		 */
		
		public function total_objects()
		{
			$this->objects = $this->get_objects();
			
			return count($this->objects);
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
			if(!class_exists($this->base_class))
			{
				require_once $this->base_class_dir;	
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
}
