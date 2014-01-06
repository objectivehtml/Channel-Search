<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Json_channel_search_export extends Base_export {
	
	protected $name = 'json';
	
	protected $title = '.json';
	
	protected $trigger = 'json';
	
	public function export($sql, $rules = array())
	{
		$this->output_json($sql);
	}
}