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

require_once 'config/channel_search_config.php';
require_once PATH_THIRD . 'channel_search/libraries/InterfaceBuilder/InterfaceBuilder.php';

class Channel_search_mcp {
	
	public $search_feilds = array();

	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->lang->loadfile('channel_search');
		$this->EE->load->helper('channel_search');
		$this->EE->load->helper('addon');
		$this->EE->load->model('channel_search_model');
		$this->EE->load->library('channel_search_lib');
		
		if(!isset($this->EE->theme_loader))
		{
			$this->EE->load->library('theme_loader');			
		}

		$this->search_fields = array(					
			'search_id' => array(
				'label' => lang('channel_search_search_id'),
				'description' => lang('channel_search_search_id_desc'),
				'type'	=> 'input'
			),
			'channel_names' => array(
				'label' => lang('channel_search_channel_names'),
				'description' => lang('channel_search_channel_names_desc'),
				'type'	=> 'input'
			),
			'get_trigger' => array(
				'label' => lang('channel_search_get_trigger'),
				'description' => lang('channel_search_get_trigger_desc'),
				'type'	=> 'input'
			),
			'empty_trigger' => array(
				'label' => lang('channel_search_empty_trigger'),
				'description' => lang('channel_search_empty_trigger_desc'),
				'type'	=> 'radio',
				'settings' => array(
					'options' => array(
						'false' => 'No',
						'true'  => 'Yes'
					)
				)
			),
			'prevent_search_trigger' => array(
				'label' => lang('channel_search_prevent_search_trigger'),
				'description' => lang('channel_search_prevent_search_trigger_desc'),
				'type'	=> 'input'
			)	
		);
		
		$this->EE->theme_loader->module_name = 'channel_search';
		$this->EE->theme_loader->javascript('InterfaceBuilder');
		
		$this->EE->theme_loader->css('channel-search');
		
		$this->EE->theme_loader->output('
			var IB = new InterfaceBuilder();
			
			/*
			$(document).ready(function() {
				$(".rule").change(function() {
					var $t   = $(this);
					var val  = $t.val()
					var rule = "#default-rule-"+val;
					
					$(".default-rule").hide();
					
					if(val) {
						console.log(rule);
						var html = $(rule).html();
					}
				});
			});
			*/
		');


		$this->EE->load->library('channel_search_rules');
	}
	
	public function index()
	{
		$this->EE->cp->set_variable('cp_page_title', lang('channel_search_module_name'));
		$this->EE->cp->set_right_nav(array(lang('channel_search_new_search') => $this->cp_url('new_search')));
		
		$vars = array(
			'manage_url' => $this->cp_url('manage_rules'),
			'new_url'    => $this->cp_url('new_search'),
			'edit_url'   => $this->cp_url('edit_search'),
			'delete_url' => $this->cp_url('delete'),
			'settings'   => $this->EE->channel_search_model->get_rules()->result()
		);
		
		return $this->EE->load->view('index', $vars, TRUE);
	}
	
	public function order_rule()
	{
		foreach($_POST['order'] as $index => $id)
		{
			$this->EE->channel_search_model->update_modifier($id, array(
				'order' => $index
			));
		}
		
		exit();
	}
	
	public function manage_rules()
	{
		$id   = $this->EE->input->get_post('id');
		$rule = $this->EE->channel_search_model->get_rule($id);
		
		$this->EE->cp->set_variable('cp_page_title', lang('channel_search_manage_rules') . ' > ' . $rule->row('search_id'));
		$this->EE->cp->set_right_nav(array(lang('channel_search_edit_search') => $this->cp_url('edit_search') . '&id='.$id, lang('channel_search_back_to_home') => $this->cp_url('index'),));
		
		
		if($rule->num_rows() == 0)
		{
			return;	
		}
		
		$vars = array(
			'rule_id'       => $id,
			'search_id'     => $rule->row('search_id'),
			'channel_names' => $rule->row('channel_names'),
			'order_url'     => $this->cp_url('order_rule'),
			'edit_url'      => $this->cp_url('edit_rule'),
			'delete_url'    => $this->cp_url('delete_rule_action'),
			'xid'           => $this->EE->channel_search_lib->generate_xid(),
			'action'        => $this->cp_url('new_rule'),
			'dropdown'      => $this->EE->channel_search_rules->dropdown(),
			'modifiers'     => $this->EE->channel_search_model->get_rule_modifiers($id, array(
				'order_by'  => 'order',
				'sort'      => 'asc'
			))
		);

		return $this->EE->load->view('search', $vars, TRUE);
	}
		
	public function new_rule()
	{
		$rule_id = $this->EE->input->get_post('rule_id');
		
		$rule 	 = $this->EE->input->get_post('rule');
		$rule 	 = $this->EE->channel_search_rules->get_rule($rule);
		
		$this->EE->cp->set_variable('cp_page_title', lang('channel_search_new_rule') . ' > ' . $rule->get_title());
		
		$this->EE->cp->set_right_nav(array(
			lang('channel_search_back_to_rules') => $this->cp_url('manage_rules') . '&id='.$rule_id,
			lang('channel_search_back_to_home') => $this->cp_url('index')
		));
		
		$vars = array(
			'type'          => 'New',
			'button_text'   => 'Add Rule',
			'rule_id'       => $rule_id,
			'rule_name'     => '',
			'search_clause' => '',
			'name'          => $rule->get_name(),
			'title'         => $rule->get_title(),
			'header'        => $rule->display_header(),
			'description'   => $rule->display_description(),
			'display_rule'  => $rule->display_rule(),
			'xid'           => $this->EE->channel_search_lib->generate_xid(),
			'action'        => cp_url('Channel_search', 'new_rule_action')
		);
		
		return $this->EE->load->view('rule_form', $vars, TRUE);
	}
	
	public function edit_rule()
	{
		$id = $this->EE->input->get('id');
		
		$modifier = $this->EE->channel_search_model->get_modifier($id);		
		
		$data = json_decode($modifier->row('rules'));
		
		$rule = $this->EE->channel_search_rules->get_rule($modifier->row('modifier'));
		
		$this->EE->cp->set_variable('cp_page_title', lang('channel_search_edit_rule') . ' > ' . $rule->get_title());
		
		$this->EE->cp->set_right_nav(array(
			lang('channel_search_back_to_rules') => $this->cp_url('manage_rules') . '&id='.$id,
			lang('channel_search_back_to_home') => $this->cp_url('index')
		));

		$vars = array(
			'type'         => 'Edit',
			'button_text'  => 'Save Changes',
			'id'     	   => $id,
			'rule_name'    => $modifier->row('name'),
			'search_clause'    => $modifier->row('search_clause'),
			'name'         => $rule->get_name(),
			'title'        => $rule->get_title(),
			'header'       => $rule->display_header(),
			'description'  => $rule->display_description(),
			'display_rule' => $rule->display_rule($data),
			'xid'          => $this->EE->channel_search_lib->generate_xid(),
			'action'       => cp_url('Channel_search', 'edit_rule_action')
		);
		
		return $this->EE->load->view('rule_form', $vars, TRUE);
	}
	
	public function new_search_action()
	{
		$rule = array(
			'search_id'     => $this->EE->input->post('search_id', TRUE),
			'channel_names' => $this->EE->input->post('channel_names', TRUE),
			'get_trigger'   => $this->EE->input->post('get_trigger', TRUE),
			'empty_trigger' => $this->EE->input->post('empty_trigger', TRUE),
			'prevent_search_trigger' => $this->EE->input->post('prevent_search_trigger', TRUE) 
		);
		
		$rule_id = $this->EE->channel_search_model->create_rule($rule);
		
		$this->EE->functions->redirect(cp_url('Channel_search', 'manage_rules').'&id='.$rule_id);
	}	
	
	public function edit_search_action()
	{
		$id   = $this->EE->input->get_post('id');	
		
		$rule = array(
			'search_id'     => $this->EE->input->post('search_id', TRUE),
			'channel_names' => $this->EE->input->post('channel_names', TRUE),
			'get_trigger'   => $this->EE->input->post('get_trigger', TRUE),
			'empty_trigger' => $this->EE->input->post('empty_trigger', TRUE),
			'prevent_search_trigger' => $this->EE->input->post('prevent_search_trigger', TRUE)  
		);
		
		$this->EE->channel_search_model->update_rule($id, $rule);
		
		$this->EE->functions->redirect(cp_url('Channel_search', 'index'));
	}
	
	public function new_rule_action()
	{
		$rule_id = $this->EE->input->post('rule_id');
		$rules   = $this->EE->input->post('rules');
		
		$this->EE->channel_search_model->create_modifier(array(
			'rule_id'       => $rule_id,
			'name'          => $this->EE->input->post('name'),
			'search_clause' => $this->EE->input->post('search_clause'),
			'modifier'      => $this->EE->input->post('modifier'),
			'rules'         => is_string($rules) ? $rules : json_encode($rules)
		));
		
		$this->EE->functions->redirect(cp_url('Channel_search', 'manage_rules').'&id='.$rule_id);
	}
	
	public function edit_rule_action()
	{
		$id    = $this->EE->input->post('id');
		$name  = $this->EE->input->post('name');
		$rules = $this->EE->input->post('rules');
		
		$this->EE->channel_search_model->update_modifier($id, array(
			'name'          => $name,
			'search_clause' => $this->EE->input->post('search_clause'),
			'rules'         => is_string($rules) ? $rules : json_encode($rules)
		));
		
		$modifier = $this->EE->channel_search_model->get_modifier($id);
		
		$this->EE->functions->redirect(cp_url('Channel_search', 'manage_rules').'&id='.$modifier->row('rule_id'));
	}
	
	public function new_search()
	{	
		$this->EE->cp->set_right_nav(array(lang('channel_search_back_to_home') => $this->cp_url('index')));
		$this->EE->cp->set_variable('cp_page_title', lang('channel_search_new_search'));

		$vars = array(
			'type'          => 'New',
			'button_text'   => 'Create Rule',
			'xid'           => $this->EE->channel_search_lib->generate_xid(),
			'settings'      => InterfaceBuilder::table($this->search_fields, array(), array(), channel_search_attr()),
			'action'        => cp_url('Channel_search', 'new_search_action')
		);

		return $this->EE->load->view('search_form', $vars, TRUE);
	}
	
	public function edit_search()
	{		
		$id     = $this->EE->input->get_post('id');	

		$this->EE->cp->set_right_nav(array(lang('channel_search_back_to_rules') => $this->cp_url('manage_rules') . '&id='.$id, lang('channel_search_back_to_home') => $this->cp_url('index'),));
		$this->EE->cp->set_variable('cp_page_title', lang('channel_search_edit_search'));

		$search = $this->EE->channel_search_model->get_rule($id)->row_array();
				
		$vars = array(
			'type'          => 'Edit',
			'id'			=> $id,
			'xid'           => $this->EE->channel_search_lib->generate_xid(),
			'button_text'   => 'Save Changes',
			'settings'      => InterfaceBuilder::table($this->search_fields, $search, array(), channel_search_attr()),
			'action'        => cp_url('Channel_search', 'edit_search_action')
		);

		return $this->EE->load->view('search_form', $vars, TRUE);
	}
	
	public function delete()
	{
		$id = $this->EE->input->get('id');
		
		$this->EE->channel_search_model->delete_rule($id);
		
		$this->EE->functions->redirect($this->cp_url('index'));
	}
	
	public function delete_rule_action()
	{
		$id = $this->EE->input->get('id');
		
		$modifier = $this->EE->channel_search_model->get_modifier($id);
		
		$this->EE->channel_search_model->delete_modifier($id);
		
		$this->EE->functions->redirect(cp_url('Channel_search', 'manage_rules').'&id='.$modifier->row('rule_id'));
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