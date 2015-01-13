<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tagger_channel_search_rule extends Base_rule {
	
	protected $title = 'Tagger';
	
	protected $description = 'Tagger allows you to search your entries by tags.';

	protected $name = 'tagger';
	
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
				$where[] = 'exp_tagger.tag_name = "'.$tag.'"';
			}
		}

		return $this->where = implode(' '.$this->settings->rules->search_clause.' ', $where);
	}

	public function get_group_by()
	{
		return 'exp_channel_titles.entry_id';
	}

	public function get_join()
	{
		return array(
			'LEFT JOIN exp_tagger_links ON exp_channel_titles.entry_id = exp_tagger_links.entry_id',
			'LEFT JOIN exp_tagger ON exp_tagger_links.tag_id = exp_tagger.tag_id'
		);
	}
}