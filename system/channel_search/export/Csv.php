<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Csv_channel_search_export extends Base_export {
	
	protected $name = 'csv';
	
	protected $title = '.csv';
	
	protected $trigger = 'csv';
	
	public function export($sql, $rules = array())
	{
		$this->output_csv($sql);
	}
}