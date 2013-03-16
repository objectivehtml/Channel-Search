<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search {
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->lang->loadfile('channel_search');
		$this->EE->load->library('channel_search_lib');
		$this->EE->load->library('search_rules');
		$this->EE->load->helper('addon');
	}
	
	public function vars($return_vars = FALSE)
	{
		$vars = array();
		
		foreach(array_merge($_GET, $_POST) as $index => $value)
		{
			$vars[$this->param('prefix', 'form').':'.$index] = $this->EE->input->get_post($index, TRUE);
		}
		
		if(!$return_vars)
		{
			return $this->parse(array($vars));
		}
		
		return $vars;
	}
	
	public function url()
	{
		$this->_cache_post();
		
		$params = array();
		
		foreach(array_merge($_GET, $_POST) as $index => $value)
		{
			if(is_array($value))
			{
				$value = implode(',', $value);
			}
			
			$params[$index] = $value;
		}
		
		return page_url(TRUE, FALSE) . '?' . http_build_query($params);
	}
	
	public function form()
	{
		$this->_cache_post();
		
		$vars   = array();
		$where  = array();		
		
		$id    = $this->param('id', FALSE, FALSE, TRUE);
		$rules = $this->EE->channel_search_model->get_rule_by_id($id);
		
		if($rules->num_rows() == 0)
		{
			$this->EE->output->show_user_error('error', lang('channel_search_invalid_search_id'));	
		}
		
		$rules = $rules->row();
		
		$attributes = array_merge(array(
			'method' => 'get',
			'action' => page_url(TRUE, TRUE, FALSE)
		), $this->EE->TMPL->tagparams);
		
		$vars[0] = $this->vars(TRUE);
		
		$channels = $this->EE->channel_data->get_channels(array(
			'where' => array(
				'site_id' => config_item('site_id')
			)
		));
		
		$channels = $this->EE->channel_data->utility->reindex('channel_name', $channels->result());
		
		$category_groups = array();
		
		foreach(explode(',', $rules->channel_names) as $name)
		{
			$category_groups = array_merge($category_groups, explode('|', $channels[$name]->cat_group));
		}
		
		$categories = $this->EE->channel_data->get_categories(array(
			'where' => array(
				'group_id' => $category_groups,
				'site_id'  => config_item('site_id')
			),
			'order_by' => 'parent_id, cat_name',
			'sort' => 'asc'
		));
		
		$vars[0]['categories'] = array();
		
		foreach($categories->result() as $cat_index => $category)
		{
			$selected = '';
			$checked  = '';
			
			$form_categories = $this->EE->input->get_post($this->param('category_name', 'category'));
			
			if(is_string($form_categories))
			{
				$form_categories = explode(',', $form_categories);
			}
			
			if(!is_array($form_categories))
			{
				$form_categories = array();
			}
			
			$form_categories = $this->EE->channel_search_lib->trim_array($form_categories);
			
			if(in_array($category->{$this->param('category_index', 'cat_url_title')}, $form_categories))
			{
				$selected = 'selected="selected';
				$checked  = 'checked="checked"';
			}
			
			$vars[0]['categories'][] = array(
				'category_id'   		  => $category->cat_id,
				'category_parent_id'	  => $category->parent_id,
				'category_group_id'		  => $category->group_id,
				'category_name' 		  => $category->cat_name,
				'category_url_title'      => $category->cat_url_title,
				'category_description'    => $category->cat_description,
				'category_image'		  => $category->cat_image,
				'selected'				  => $selected,
				'checked'				  => $checked
			);	
		}
		
		$attribute_string = NULL;
		
		foreach($attributes as $index => $value)
		{
			$attribute_string .= $index.'="'.$value.'" ';	
		}
		
		$form = '<form '.trim($attribute_string).'>'.$this->parse($vars).'</form>';
		$form = preg_replace('/{form:.+}/', '', $form);
		
		return $form;
	}
	
	public function results()
	{
		$this->_cache_post();
		
		$id       = $this->param('id', FALSE);		
		$order_by = $this->param('order_by', $this->param('orderby', 'entry_id'));
		$sort     = $this->param('sort', 'desc');
		$limit    = (int) $this->param('limit', 20);
		$page     = (int) $this->param('page', 1);
		$offset   = 0;
		
		if($page > 1)
		{
			$offset = ($page - 1) * $limit;
		}	
		
		$results = $this->EE->channel_search_lib->search($id, $order_by, $sort, $limit, $offset, $this->param('export', FALSE, TRUE));
		
		if(!$limit)
		{
			$limit = $results->grand_total;
		}
		
		if($results === FALSE || ($results->has_searched && ($results->response === FALSE || $results->response->num_rows() == 0)))
		{
			return $this->EE->TMPL->no_results();
		}
		
		$vars = array(
			//'total_results'  => $results->response->num_rows(),
			'grand_total'      => $results->grand_total,
			'sort'             => $sort,
			'order_by'         => $order_by,
			'limit'            => $limit,
			'offset'           => $offset,
			'page'             => $page,
			'has_searched'     => $results->has_searched,
			'has_not_searched' => $results->has_not_searched
		);
		
		$vars['total_pages']   = ceil($results->grand_total / $limit);
		
		$_GET['page'] = $page - 1;
		
		$vars['prev_page']     = ($page - 1 > 0 ? $page - 1 : 1);
		$vars['prev_page_url'] = $page - 1 > 0  ? page_url(TRUE, TRUE, FALSE) : FALSE;
		
		$_GET['page'] = $page + 1;
		
		$vars['next_page']     = $page + 1 < $vars['total_pages'] ? $page + 1 : $page;
	
		$vars['next_page_url'] = $vars['next_page'] < $vars['total_pages'] ? page_url(TRUE, TRUE, FALSE) : FALSE; 
		
		$_GET['page'] = $page;
		
		$vars['current_page']  = $page;
		$vars['is_first_page'] = $page == 1 ? TRUE : FALSE;
		$vars['is_last_page']  = $page == $vars['total_pages'] ? TRUE : FALSE;
		
		$this->EE->load->library('entries_lib');
		
		$entry_ids = array();
		
		if($results->response)
		{
			foreach($results->response->result() as $entry)
			{
				$entry_ids[] = $entry->entry_id;	
			}
		}
		
		if($results->has_searched)
		{
			$this->EE->session->set_cache('channel_search', 'search_results', $results);
			
			$this->EE->TMPL->tagdata = $this->EE->entries_lib->entries(array(
				'channel'     => $results->channels,
				'entry_id'    => implode($entry_ids, '|'),
				'fixed_order' => implode($entry_ids, '|'),
				'limit'       => $limit,
			));
		}
		
		$this->EE->TMPL->tagdata = $this->parse(array($vars));
		
		return $this->EE->TMPL->tagdata;
	}
	
	private function _cache_post()
	{
		if($this->param('cache_post'))
		{
			if(count($_POST) > 0)
			{						
				if($this->param('reset_get'))
				{
					$this->EE->channel_search_lib->reset_get();
				}
				
				$this->EE->channel_search_lib->set_cache();
			}
			else
			{		
				$this->EE->channel_search_lib->get_cache();
			}
		}
	}
	
	/*
	public function results()
	{
		$this->EE->load->helper('url');
		
		$id       = $this->param('id', FALSE, TRUE);
		$order_by = $this->param('order_by', 'entry_id');
		$sort     = $this->param('sort', 'desc');
		$limit    = $this->param('limit', 20);
		$page     = (float) $this->param('page');
		$offset   = $this->param('offset', 0);
		
		if($page)
		{
			$offset = $limit * $page - $limit;
		}
		
		$results = $this->EE->channel_search_lib->search($id, $order_by, $sort, $limit, $offset);
		
		if($results->response->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();	
		}
		
		$return = array();
		$tagdata = NULL;
		
		foreach($results->response->result() as $index => $row)
		{		
			$row->index         = $index;
			$row->count         = $index + 1;
			$row->limit         = $limit;
			$row->offset        = $offset;
			$row->sort          = $sort;
			$row->order_by      = $order_by;
			$row->total_results = $results->response->num_rows();
			$row->grand_total   = $results->grand_total;
			$row->current_page	= $page;
			$row->first_page	= 1;
			$row->last_page		= ceil($results->grand_total / $limit);
			$row->is_first_page = $page == 1 ? TRUE : FALSE;
			$row->is_last_page  = $page == $row->last_page ? TRUE : FALSE;
			$row->total_pages	= $row->last_page;
			$row->next_page		= $row->total_pages > ($page+1) ? $page+1 : $page;
			$row->prev_page		= ($page - 1) > 0 ? $page-1 : 1;			
			$row->next_page_url = $this->EE->channel_search_lib->get_url($page+1);						
			$row->prev_page_url = $this->EE->channel_search_lib->get_url($page-1);
						
			$tagdata .= $this->EE->channel_search_lib->parse($row);			
		}
		
		return $tagdata;
	}
	*/
	
	public function get()
	{
		$default = $this->param('default', NULL);
		
		$return = $this->EE->input->get_post($this->EE->TMPL->tagparts[2]);
		
		return $return !== FALSE ? $return : $default; 
	}
	
	private function parse($vars, $tagdata = FALSE)
	{
		if($tagdata === FALSE)
		{
			$tagdata = $this->EE->TMPL->tagdata;
		}
			
		return $this->EE->TMPL->parse_variables($tagdata, $vars);
	}
	
	private function param($param, $default = FALSE, $boolean = FALSE, $required = FALSE)
	{
		$name 	= $param;
		$param 	= $this->EE->input->get_post($param) ? $this->EE->input->get_post($param) : $this->EE->TMPL->fetch_param($param);
		
		if($required && !$param) show_error('You must define a "'.$name.'" parameter in the '.__CLASS__.' tag.');
			
		if($param === FALSE && $default !== FALSE)
		{
			$param = $default;
		}
		else
		{				
			if($boolean)
			{
				$param = strtolower($param);
				$param = ($param == 'true' || $param == 'yes') ? TRUE : FALSE;
			}			
		}
		
		return $param;			
	}
}