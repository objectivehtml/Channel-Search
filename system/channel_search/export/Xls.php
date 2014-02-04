<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Xls_channel_search_export extends Base_export {
	
	protected $name = 'xls';
	
	protected $title = '.xls';
	
	protected $trigger = 'xls';
	
	public function export($sql, $rules = array())
	{
		$this->output_xls($sql);
	}
}