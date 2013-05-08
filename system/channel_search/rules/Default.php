<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Default_channel_search_rule extends Base_rule {
	
	protected $title = 'Default';
	
	protected $label = 'This is a test label';
	
	protected $description = 'This is a test description';
	
	protected $name = 'default';
	
	protected $reserved_fields = array('title', 'status', 'entry_date', 'expiration_date');
	
	protected $date_fields = array('entry_date', 'expiration_date');
	
	protected $fields = array(						
		'rules' => array(
			'label' => 'Search Fields',
			'description' => 'Use the table below to create various search rules. These rules will be appended in the order in which they are created.',
			'id'    => 'search_fields',
			'type'	=> 'matrix',
			'settings' => array(
				'columns' => array(
					0 => array(
						'name'  => 'channel_field_name',
						'title' => 'Channel Field Name'
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
					$field_names = $this->trim_array(explode(',', $rule->channel_field_name));
					
					if(count($field_names) == 1 && isset($this->fields[$rule->channel_field_name]) || in_array($field_names[0], $this->reserved_fields))
					{
						if(in_array($field_names[0], $this->reserved_fields))
						{
							if(in_array($field_names[0], $this->date_fields))
							{
								$value = strtotime($value);
							}
							
							$where[] = $rule->clause.' '.$field_names[0].' '.$rule->operator.' '.$EE->db->escape($value);
						}
						else
						{	
							$where[] = $rule->clause.' field_id_'.$this->fields[$rule->channel_field_name]->field_id.' '.$rule->operator.' '.$EE->db->escape($value);
						}
					}
					else
					{	
						$concat = array('\' \'');
						
						foreach($field_names as $field_name)
						{
							if(isset($this->fields[$field_name]))
							{
								$concat[] = 'field_id_'.$this->fields[$field_name]->field_id;	
							}
						}
						
						if(count($concat) > 1)
						{
							$where[] = $rule->clause.' concat_ws('.implode($concat, ',').') '.$rule->operator.' '.$EE->db->escape($value);
						}
					}
				}
			}
			
			$this->where = $this->clean_sql(implode(' ', $where));
		}
		
		return $this->where;
	}
}
