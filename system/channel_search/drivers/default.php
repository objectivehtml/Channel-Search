<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'Base_Search_Driver.php';

class Default_search_driver extends Base_Search_Driver {
	
	public function build_rule($rule, $data = array())
	{
		$reserved_fields = array(
			'title',
			'entry_date',
			'expiration_date',
			'author_id',
			'entry_id',
			'status',
			'channel_id'
		);
		
		if(empty($rule->clause))
		{
			$rule->clause = 'AND';
		}
		
		if(empty($rule->operator))
		{
			$rule->operator = '=';
		}
		
		if(in_array($rule->channel_field_name, $reserved_fields))
		{
			$field = $rule->channel_field_name;	
		}
		else
		{			
			$field = $this->channel_data->get_field_by_name($rule->channel_field_name);
			
			if($field->num_rows() == 0)
			{
				$this->EE->output->show_user_error('genera', array(
					'\''.$rule->channel_field_name.'\' is not a valid field name.'
				));
			}
		}
		
		return $rule->clause.' '.$field.' '.$rule->operator.' \''.$rule->prefix.$data[$rule->form_field_name].$rule->suffix.'\'';
	}		
}