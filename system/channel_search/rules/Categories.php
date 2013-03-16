<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories_channel_search_rule extends Base_rule {
	
	protected $title = 'Category Search';
	
	protected $description = 'Category Search allows you to search categories in various ways.';
	
	protected $name = 'categories';
	
	private $cat_where = array();
	
	protected $fields = array(
		'form_field' => array(
			'label'       => 'Form Field',
			'description' => 'Enter the name of the form field that stores the category values',
			'id'          => 'latitude_field',
		),
		'search_type' => array(
			'label'       => 'Search Type',
			'description' => 'Select the type of value you want to search',
			'id'          => 'search_type',
			'type'		  => 'select',
			'settings'	  => array(
				'options' => array(
					'cat_id'        => 'Category ID',
					'cat_name'      => 'Category Name',
					'cat_url_title' => 'Category URL Title'
				) 
			)
		),
		'operator' => array(
			'label'       => 'Category Operator',
			'description' => 'The category operator is used to match different variations of categories. By manipulation the operator, you can determine how explicit your categories need to return in the results.',
			'id'          => 'cat_operator',
			'type'		  => 'select',
			'settings'	  => array(
				'options' => array(
					'='    => '=',
					'!='   => '!=',
					'>'    => '>',
					'>='   => '>=',
					'<'    => '<',
					'<='   => '<=',
					'LIKE' => 'LIKE'
				) 
			)
		),
		'count_operator' => array(
			'label'       => 'Category Count Operator',
			'description' => 'The category count operator is used to results with various category counts. The default selection will match exactly the number that is passed to the search.',
			'id'          => 'cat_operator',
			'type'		  => 'select',
			'settings'	  => array(
				'options' => array(
					'>='   => '>=',
					'='    => '=',
					'!='   => '!=',
					'>'    => '>',
					'<'    => '<',
					'<='   => '<='
				) 
			)
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
	
	public function __construct($properties = array())
	{
		parent::__construct($properties);
	}
	
	public function get_from()
	{
		$EE =& get_instance();
		
		$rules    = $this->settings->rules;
		$field    = $rules->form_field;
		$type     = $rules->search_type;
		$operator = $rules->operator;
		$count_op = $rules->count_operator;
		$clause   = $rules->search_clause;
		
		$category_data = $EE->channel_data->get_categories(array(
			'where' => array(
				'site_id' => config_item('site_id')
			)
		));
		
		$categories = array();
		$cat_having = array();
		
		if($category_data->num_rows() > 0)
		{
			$categories = $EE->channel_data->utility->reindex($type, $category_data->result());
		}
		
		$new_array = array();
		
		foreach($categories as $cat_index => $category)
		{
			$new_array[strtolower($cat_index)]->cat_name = $category->cat_name;
		}
		
		$categories = $new_array;
		
		$value = $EE->input->get_post($field);
		$cat_count = 0;
		
		if($value)
		{	
			if(is_string($value))
			{
				$value = explode(',', $value);	
			}
			
			foreach($value as $cat_value)
			{
				$cat_value = trim($cat_value);
				
				if(isset($categories[strtolower($cat_value)]))
				{
					$cat_count++;
					
					$cat_having[] = $categories[strtolower($cat_value)]->$type;
				}
			}
		}
		
					
		$cat_where = array();
		
		foreach($cat_having as $field)
		{
			if($operator == 'LIKE')
			{
				$field = '%'.$field.'%';	
			}
			
			$cat_where[] = $type.' '.$operator.' '.$EE->db->escape($field);
		}
		
		$this->cat_where = $cat_where;
		
		return array(
			'(SELECT distinct entry_id, COUNT(cat_id) AS cat_count, cat_id, cat_id as \'category_id\', GROUP_CONCAT(cat_id SEPARATOR \'|\') as \'cat_ids\', GROUP_CONCAT(cat_id SEPARATOR \'|\') as \'category_ids\', exp_categories.cat_name, exp_categories.cat_name as \'category_name\', exp_categories.cat_url_title, exp_categories.cat_url_title as \'category_url_title\', exp_categories.parent_id as \'cat_parent_id\', exp_categories.parent_id as \'category_parent_id\', exp_categories.site_id as \'cat_site_id\', exp_categories.site_id as \'category_site_id\', exp_categories.group_id as \'cat_group_id\', exp_categories.group_id as \'category_group_id\', exp_categories.cat_description as \'cat_description\', exp_categories.cat_description as \'category_description\', exp_categories.cat_image as \'cat_image\', exp_categories.cat_image as \'category_image\', GROUP_CONCAT(exp_categories.cat_name  SEPARATOR \'|\') as \'cat_names\', GROUP_CONCAT(exp_categories.cat_name  SEPARATOR \'|\') as \'category_names\',  GROUP_CONCAT(exp_categories.cat_url_title  SEPARATOR \'|\') as \'cat_url_titles\', GROUP_CONCAT(exp_categories.cat_url_title  SEPARATOR \'|\') as \'category_url_titles\', GROUP_CONCAT(exp_categories.parent_id  SEPARATOR \'|\') as \'cat_parent_ids\', GROUP_CONCAT(exp_categories.parent_id  SEPARATOR \'|\') as \'category_parent_ids\', GROUP_CONCAT(exp_categories.cat_description  SEPARATOR \'|\') as \'cat_descriptions\', GROUP_CONCAT(exp_categories.cat_description  SEPARATOR \'|\') as \'category_descriptions\',  GROUP_CONCAT(exp_categories.group_id SEPARATOR \'|\') as \'cat_group_ids\', GROUP_CONCAT(exp_categories.group_id SEPARATOR \'|\') as \'category_group_ids\', GROUP_CONCAT(exp_categories.site_id  SEPARATOR \'|\') as \'cat_site_ids\', GROUP_CONCAT(exp_categories.site_id  SEPARATOR \'|\') as \'category_site_ids\', GROUP_CONCAT(exp_categories.cat_image  SEPARATOR \'|\') as \'cat_images\',  GROUP_CONCAT(exp_categories.cat_image  SEPARATOR \'|\') as \'category_images\'
			    FROM exp_channel_titles
			    LEFT JOIN exp_category_posts USING (entry_id) 
			    LEFT JOIN exp_categories USING (cat_id)
			    '.(count($cat_where) > 0 ? 'WHERE ('. implode(' '.$clause.' ', $cat_where) .')' : NULL).'
			    GROUP BY entry_id
			    '.(count($cat_having) > 0 ? 'HAVING cat_count '.$count_op.' '.count($cat_having) : NULL).'
			) cc
			INNER JOIN
		    	exp_channel_data
		  	USING (entry_id)'
		);
	}
	
	public function get_select()
	{
		return array(
			'cc.*'
		);
	}
	
	public function get_where()
	{
		if(count($this->cat_where) > 0)
		{
			return array('cat_count >= '.count($this->cat_where));
		}
	}
}