<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Static_conditionals_channel_search_rule extends Base_rule {
	
	protected $title = 'Static Conditionals';
	
	protected $label = 'This is a test label';
	
	protected $description = 'Static Conditionals allow you to append static conditionals to the SQL. You can hardcode statuses and any other field you may need.';
	
	protected $name = 'static_conditionals';
	
	protected $fields = array(						
		'rules' => array(
			'label' => 'Search Fields',
			'description' => 'This is a test description',
			'id'    => 'search_fields',
			'type'	=> 'matrix',
			'settings' => array(
				'columns' => array(
					0 => array(
						'name'  => 'field_name',
						'title' => 'Channel Field, Status, or SELECT variable'
					),
					1 => array(
						'name'  => 'value',
						'title' => 'Static Value'
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
	
	public function get_where()
	{
		$EE =& get_instance();
		
		if(is_array($this->settings->rules))
		{
			$where = array();
			
			foreach($this->settings->rules as $rule)
			{
				$value = $EE->db->escape($rule->value);
				
				if(!empty($value))
				{
					$where[] = $rule->clause.' '.$rule->field_name.' '.(!empty($rule->operator) ? $rule->operator : '=').' '.$value;
				}
			}
			
			$this->where = $this->clean_sql(implode(' ', $where));
		}
		
		return $this->where;
	}
	
	public function display_rule($data = array())
	{
		return InterfaceBuilder::field('rules', $this->fields['rules'])->display_field($data);
	}
}