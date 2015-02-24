<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Date_time_channel_search_rule extends Base_rule {
	
	protected $title = 'Date & Time';
	
	protected $label = 'This is a test label';
	
	protected $description = 'The Date & Time search modifier allows you to perform date range searches on any field and output the values in the defined format. If no format is defined, the strtotime() function will run and return a UNIX timestamp.';
	
	protected $name = 'date_time';
	
	protected $fields = array(						
		'rules' => array(
			'label' => 'Search Fields',
			'description' => 'Use the table below to create various search rules. These rules will be appended in the order in which they are created.',
			'id'    => 'search_fields',
			'type'	=> 'matrix',
			'settings' => array(
				'columns' => array(
					0 => array(
						'name'  => 'field_name',
						'title' => 'Channel or Static Field Name'
					),
					1 => array(
						'name'  => 'form_field_name',
						'title' => 'Form Field Name'
					),
					2 => array(
						'name'  => 'operator',
						'title' => 'Operator (>, >=, <, <=, =, !=, LIKE)'
					),
					3 => array(
						'name'  => 'clause',
						'title' => 'Clause (AND, OR)' 
					),
					3 => array(
						'name'  => 'format',
						'title' => 'PHP Date Format' 
					)
				),
				'attributes' => array(
					'class'       => 'mainTable padTable',
					'border'      => 0,
					'cellpadding' => 0,
					'cellspacing' => 0
				)
			)
		)			
	);

	protected $reserved_fields = array(
		'entry_date'
	);
	
	public function __construct($properties = array())
	{
		parent::__construct($properties);
	}
	
	public function display_rule($data = array())
	{
		return InterfaceBuilder::field('rules', $this->fields['rules'])->display_field($data);
	}
	
	public function get_where()
	{
		$EE =& get_instance();
		
		if(is_array($this->settings->rules))
		{
			$where = array();
			
			foreach($this->settings->rules as $rule)
			{
				$value = $EE->input->get_post($rule->form_field_name);
				
				if(empty($rule->clause))
				{
					$rule->clause = 'AND';
				}
				
				if(empty($rule->operator))
				{
					$rule->operator = '=';
				}
				
				if($rule->operator == 'LIKE' && $value && !empty($value))
				{
					$value = '%'.$value.'%';	
				}

				if($value && !empty($value))
				{
					$value = strtotime($value);

					if(!empty($rule->format))
					{
						$value = date($rule->format, $value);
					}

					if(isset($this->fields[$rule->field_name]))
					{
						$where[] = $rule->clause.' '.'field_id_'.$this->fields[$rule->field_name]->field_id.' '.$rule->operator.' '.$EE->db->escape($value);
					}
					else
					{
						$where[] = $rule->clause.' '.$rule->field_name.' '.$rule->operator.' '.$EE->db->escape($value);
					}
				}
			}
			
			$this->where = $this->clean_sql(implode(' ', $where));
		}

		return $this->where;
	}
}