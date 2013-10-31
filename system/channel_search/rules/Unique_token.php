<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unique_token_channel_search_rule extends Base_rule {
	
	protected $title = 'Unique Token Search';
	
	protected $description = 'A Unique Token Search allows you to pass an arbitrary unique, and Channel Search will search the channel entries for that unique string. You can then traslate that unique token to a string that is saved in the database. For example, if you are search products have a dropdown field that represents the "brand". This dropdown field is populated by entries in the Brands channels by using the "brand_name" field. A Unique Token search will allow you to pass a URL title of that brand ans search the corresponding "Brand Name".';

	protected $name = 'unique_token';
	
	protected $fields = array(						
		'channel_name' => array(
			'label' => 'Channel Name',
			'description' => 'The name of the related channel.',
			'type'	=> 'input'
		),
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
		'rel_field_name' => array(
			'label' => 'Related Channel Field Name',
			'description' => 'The name of the channel field storing the unique token in the related channel.',
			'type'	=> 'input'
		),
		'uri_field' => array(
			'label' => 'URI Field Name',
			'description' => 'The name of the field used to pass the data through the URI. (Most likely this is the url_title).',
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

		$channel = ee()->channel_data->get_channel_by_name($rules->channel_name);
		$entry   = ee()->channel_data->get_channel_entries($channel->row('channel_id'), array(
			'where' => array(
				$rules->uri_field => $form_data
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