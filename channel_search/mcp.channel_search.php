<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Search
 * 
 * @package		Channel Search
 * @author		Justin Kimbrell
 * @copyright	Copyright (c) 2012, Objective HTML
 * @link 		http://www.objectivehtml.com/google-maps
 * @version		0.0.1
 * @build		20120530
 */

require 'config/channel_search_config.php';

class Channel_search_mcp {
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->model('channel_search_model');
		$this->EE->load->driver('Interface_builder');
	}
	
	public function index()
	{
		$this->EE->cp->set_right_nav(array('New Rule' => $this->cp_url('settings')));
		
		$vars = array(
			'edit_url'   => $this->cp_url('settings'),
			'delete_url' => $this->cp_url('delete'),
			'settings'   => $this->EE->channel_search_model->get_settings()->result()
		);
		
		return $this->EE->load->view('index', $vars, TRUE);
	}
	
	public function settings()
	{
		$this->EE->cp->set_right_nav(array('&larr; Back to Home' => $this->cp_url('index')));
		
		if($this->EE->input->get('id'))
		{
			$settings = $this->EE->channel_search_model->get_setting($this->EE->input->get('id'))->row_array();
			$settings['rules'] = json_decode($settings['rules']);
		}
		else
		{
			$settings = array();	
		}
		
		$fields = array(						
			'rules' => array(
				'label' => 'Search Fields',
				'description' => '',
				'id'    => 'matrix_fields',
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
						),
						4 => array(
							'name'  => 'prefix',
							'title' => 'Prefix' 
						),
						5 => array(
							'name'  => 'suffix',
							'title' => 'Suffix' 
						),
						6 => array(
							'name'  => 'driver',
							'title' => 'Driver'
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
		
		
		$this->EE->interface_builder->data = $settings;
		$this->EE->interface_builder->add_fields('settings', $fields);
		
		$search_fields = $this->EE->interface_builder->build();
				
		$this->EE->interface_builder->clear();
		$this->EE->interface_builder->data = $settings;
		
		$fields = array(
			'settings' => array(
				'title'       => 'Search Settings',
				'attributes'  => array(
					'class' => 'mainTable padTable',
					'border' => 0,
					'cellpadding' => 0,
					'cellspacing' => 0
				),
				'wrapper' => 'div',
				'fields'  => array(					
					'search_id' => array(
						'label' => 'Search ID',
						'description' => 'To access these rules on the front-end, you must assign a unqiue ID. This field allows you to define as many different search patterns as you desire.',
						'type'	=> 'input'
					),
					'channel_names' => array(
						'label' => 'Channel Names(s)',
						'description' => 'Enter the channel name(s) you wish to search. If you are searching multiple channels, delimit the channel name with a comma.',
						'type'	=> 'input'
					)	
				)		
			)
		);
		
		$this->EE->interface_builder->add_fieldsets($fields);
		
		$vars        = array(
			'return'        => $this->current_url().$this->cp_url(),
			'settings'      => $this->EE->interface_builder->fieldsets(),
			'search_fields' => $search_fields['rules']->field,
			'id'			=> $this->EE->input->get('id'),
			'action'        => $this->current_url('ACT', $this->EE->channel_data->get_action_id('Channel_search_mcp', 'save_settings_action'))
		);

		return $this->EE->load->view('search', $vars, TRUE);
	}
	
	public function delete()
	{
		$id = $this->EE->input->get('id');
		
		$this->EE->channel_search_model->delete_setting($id);
		
		$this->EE->functions->redirect($this->cp_url('index'));
	}
	
	public function save_settings_action()
	{
		$post = array(
			'id'            => $this->EE->input->post('id') ? $this->EE->input->post('id') : 0,
			'search_id'     => $this->EE->input->post('search_id'),
			'channel_names' => $this->EE->input->post('channel_names'),
			'rules'         => json_encode($this->EE->input->post('rules'))
		);
		
		$this->EE->channel_search_model->save_settings($post);
		
		$this->EE->functions->redirect($this->EE->input->post('return'));
	}

	private function cp_url($method = 'index', $useAmp = FALSE)
	{
		$amp  = !$useAmp ? AMP : '&';

		$file = substr(BASE, 0, strpos(BASE, '?'));
		$file = str_replace($file, '', $_SERVER['PHP_SELF']) . BASE;

		$url  = $file .$amp. '&C=addons_modules' .$amp . 'M=show_module_cp' . $amp . 'module=channel_search' . $amp . 'method=' . $method;

		return str_replace(AMP, $amp, $url);
	}
	
	private function current_url($append = '', $value = '')
	{
		$http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
		
		$port = $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? NULL : ':' . $_SERVER['SERVER_PORT'];
		
		if(!isset($_SERVER['SCRIPT_URI']))
		{				
			 $_SERVER['SCRIPT_URI'] = $http . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
		}
		
		$base_url = $http . $_SERVER['HTTP_HOST'];
		
		if(!empty($append))
		{
			$base_url .= '?'.$append.'='.$value;
		}
		
		return $base_url;
	}
}
// END CLASS

/* End of file ext.gmap.php */
/* Location: ./system/expressionengine/third_party/modules/gmap/ext.gmap.php */