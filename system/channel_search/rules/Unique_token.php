<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unique_token_channel_search_rule extends Base_rule {
	
	protected $title = 'Unique Token Search';
	
	protected $description = 'This rule is pretty unique and specialized and is based around searching unique tokens in another channel, returning a piece of the related data to use for searching. Say you are searching for "Products" and want to search those products by "Brand". You could configure this rule to search products with a brand url_title, while the actual brand name is stored in the product itself. Since the url_title is a unique token in the "Brands" channel, you could return the "Brand Name" to search in the "Products" channel. You can return any field from the related channel and use it for searching within the parent channel.';

	protected $name = 'unique_token';
	
	protected $fields = array(	
		'form_field' => array(
			'label' => 'Form Field',
			'description' => 'The name of the form field passing the unique token.',
			'type'	=> 'input'
		),			
		'field_name' => array(
			'label' => 'Channel Field Name',
			'description' => 'The name of the field you are searching.',
			'type'	=> 'input'
		),
		'rel_channel_name' => array(
			'label' => 'Related Channel Name',
			'description' => 'The name of the related channel.',
			'type'	=> 'input'
		),	
		'rel_search_field_name' => array(
			'label' => 'Related Channel Field to Search',
			'description' => 'The name of the related field you are searching',
			'type'	=> 'input'
		),	
		'rel_field_name' => array(
			'label' => 'Related Channel Field to Return',
			'description' => 'The name of the related channel field with the value you want to return that will be used to search the parent channel.',
			'type'	=> 'input'
		),
		'operator' => array(
			'label' => 'Search Operator',
			'description' => 'This is the operator that will be used to perform the search',
			'type' => 'select',
			'settings' => array(
				'options' => array(
					'=' => '=',
					'>' => '>',
					'>=' => '>=',
					'<=' => '<=',
					'<' => '<',
					'!=' => '!=',
				)
			)
		)			
	);
	
	public function get_where()
	{
		$rules = $this->settings->rules;
	
		$form_data = ee()->input->get_post($rules->form_field);

		if(!$form_data)
		{
			return array();
		}

		$channel = ee()->channel_data->get_channel_by_name($rules->rel_channel_name);
		$entry   = ee()->channel_data->get_channel_entries($channel->row('channel_id'), array(
			'where' => array(
				$rules->rel_search_field_name => $form_data
			)
		));

		$value = $entry->row($rules->rel_field_name);

		$field = $rules->field_name;

		if(isset($this->fields[$rules->field_name]))
		{
			$field = 'field_id_'.$this->fields[$rules->field_name]->field_id;
		}

		return $field . $rules->operator . ' \'' . $value . '\'';
	}
}