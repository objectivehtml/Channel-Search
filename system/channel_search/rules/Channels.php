<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channels_channel_search_rule extends Base_rule {
	
	protected $title = 'Channels';
	
	protected $description = 'This search rule allows you to search filter out which channel you are searching. So if your rule is setup to search multiple channels, but you within that search you want to give the ability to search only a few of the channels available, use this rule.';

	protected $name = 'channels';
	
	protected $fields = array(
		'form_field_name' => array(
			'label' => 'Form Field Name',
			'description' => 'The name of the form field that will be searching the Tagger field(s).',
			'type'	=> 'input'
		),
		'search_clause' => array(
			'label'       => 'Search Clause',
			'description' => 'The search clause can use used to match categories with an AND or an OR clause.',
			'id'          => 'search_clause',
			'type'		  => 'select',
			'settings'	  => array(
				'options' => array(
					'OR'  => 'OR',
					'AND' => 'AND',
				) 
			)
		)	
	);
	
	public function get_where()
	{
		$EE =& get_instance();

		$where = array();
		$value = $EE->input->get_post($this->settings->rules->form_field_name);

		if($value && !empty($value))
		{
			if(!is_array($value))
			{
				$value = array($value);
			}

			foreach($value as $tag)
			{
				$where[] = 'exp_channel_titles.channel_id = "'.$tag.'"';
			}
		}

		return $this->where = implode(' '.$this->settings->rules->search_clause.' ', $where);
	}
}