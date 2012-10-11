<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Base_Search_Driver {
	
	protected $channel_data;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->channel_data = $this->EE->channel_data;
		$this->lib          = $this->EE->channel_search_lib;
		$this->model        = $this->EE->channel_search_model;
	}
	
	abstract function build_rule($rule, $data = array());	
}
