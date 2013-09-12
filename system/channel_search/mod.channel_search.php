<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search {
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->lang->loadfile('channel_search');
		$this->EE->load->library('channel_search_lib');
		$this->EE->load->library('channel_search_rules');
		$this->EE->load->helper('addon');
	}
	

	public function _setter()
	{
		if($this->EE->TMPL->tagparams)
		{
			foreach(array('set', 'unset') as $type)
			{
				foreach($this->EE->TMPL->tagparams as $param => $value)
				{
					$pattern = '/^'.$type.':/';
					
					if(preg_match($pattern, $param))
					{
						$param = preg_replace($pattern, '', $param);
						
						if($type == 'set')
						{
							$current = $this->EE->input->get_post($param);
							$current_time = strtotime($value, strtotime($current ? $current : $value));
							$format  = $this->param('format:'.$param);
							$value   = $format ? date($format,  (preg_match('/^\d*$/', $current) ? $current : $current_time)) : $value;

							$_GET[$param]  = $value;
							$_POST[$param] = $value;
						}
						else
						{
							unset($this->EE->TMPL->tagparams[$param]);
							
							unset($_GET[$param]);
							unset($_POST[$param]);	
						}
					}
				}
			}
		}
	}

	public function char_count()
	{
		return strlen($this->param('string', $this->EE->TMPL->tagdata));
	}

	public function random_hash()
	{
		return md5(time());
	}
	
	public function date()
	{
		$return = strtotime($this->param('string', $this->param('time', $this->EE->localize->now)));
		
		if($format = $this->param('format', $this->param('date_format')))
		{
			return date(str_replace('%', '', $format), $return);
		}

		return $return;
	}
	
	public function entry_has_category()
	{
		$entry_id = $this->param('entry_id', FALSE, FALSE, TRUE);
		$posts    = $this->EE->channel_data->get_category_post($entry_id);
		$value    = 0;
		
		$where = array();
		
		foreach(array('cat_id', 'cat_url_title', 'cat_name') as $var)
		{
			if($value = $this->param($var))
			{
				$where[$var] = $value;
			}
		}
		
		$category = $this->EE->channel_data->get_categories(array(
			'where' => $where
		));
		
		if($category->num_rows() > 0)
		{
			$value = $category->row('cat_id');
		}
		
		foreach($posts->result() as $post)
		{
			if($post->cat_id == $value)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	public function current_url()
	{
		return page_url(TRUE, $this->param('params', TRUE, TRUE), FALSE);
	}
	
	public function set_default()
	{	
		$name    = $this->param('name', $this->param('var'));
		$not_set = explode('|', $this->param('not_set', ''));
		
		if($name && !$this->EE->input->get_post($name))
		{
			$is_set = FALSE;
			
			if(is_array($not_set))
			{
				foreach($not_set as $var)
				{
					if($this->EE->input->get_post($var))
					{
						$is_set = TRUE;
					}		
				}
				
				if(!$is_set)
				{
					$value = trim($this->param('value', $this->EE->TMPL->tagdata));
				
					$_GET[$name]  = $value;
					$_POST[$name] = $value;
				}
			}
		}
	}
	
	public function is_set()
	{		
		$name = $this->param('name', $this->param('var'));
		
		if($name && $this->EE->input->get_post($name))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function is_not_set()
	{		
		return $this->is_set() ? FALSE : TRUE;
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
	
	public function export_url()
	{
		return $this->url(array(
			'export' => $this->param('type', 'true')
		));
	}
	
	public function url($params = array())
	{
		$get  = $_GET;
		$post = $_POST;
		
		$this->_setter();
		$this->_cache_post();
	
		foreach(array_merge($_GET, $_POST) as $index => $value)
		{
			if(is_array($value))
			{
				$value = implode(',', $value);
			}
			
			$params[$index] = $value;
		}
		
		$return = $this->param('page_url', page_url(TRUE, FALSE)) . '?' . http_build_query($params);
		
		$_GET  = $get;
		$_POST = $post;
		
		return $return;
	}
	
	public function form()
	{
		$get  = $_GET;
		$post = $_POST;
		
		$this->_setter();
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
		
		foreach($this->EE->channel_search_lib->trim_array(explode(',', $rules->channel_names)) as $name)
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

		$has_searched = $this->EE->channel_search_lib->has_searched($rules);

		$vars[0]['has_searched'] 	  = $has_searched;
		$vars[0]['has_not_searched'] = $has_searched ? false : true;

		$validation = array(
			'success' 		 => TRUE,
			'errors'         => array(),
			'validation_ran' => FALSE
		);

		if($has_searched)
		{
			$validation = $this->EE->channel_search_lib->validate();

			$validation['validation_ran'] = TRUE;
		}

		$vars[0] = array_merge($vars[0], $validation);

		$form = '<form '.trim($attribute_string).'>'.$this->parse($vars).'</form>';
		$form = preg_replace('/{form:.+}/', '', $form);
				
		$_GET  = $get;
		$_POST = $post;
		
		return $form;
	}
	
	public function total_results()
	{
		$get  = $_GET;
		$post = $_POST;
		
		$this->_setter();
		
		$id      = $this->param('id', FALSE, FALSE, TRUE);	
		$results = $this->EE->channel_search_lib->search($id, 'entry_id', 'asc', 'all', 0, $this->param('export', FALSE));
		
		if(!$this->EE->TMPL->tagdata)
		{
			$return = $results->grand_total;
		}
		else
		{
			$prefix = $this->param('prefix', '');
			
			$return = $this->parse(array(
				array(
					$prefix.'total_results' => $results->grand_total,
					$prefix.'data' 			=> $results->result_array()
				)
			));
		}		
		
		$_GET  = $get;
		$_POST = $post;
		
		return $return;
	}
	
	public function results()
	{
		$get  = $_GET;
		$post = $_POST;
		
		$this->_setter();
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
		
		$results = $this->EE->channel_search_lib->search($id, $order_by, $sort, $limit, $offset, $this->param('export', FALSE));
		
		if(!$limit)
		{
			$limit = $results->grand_total;
		}
		
		if (preg_match('/'.LD.'if '.$this->param('prefix', '').'no_results'.RD.'(.*?)'.LD.'\/if'.RD.'/s', $this->EE->TMPL->tagdata, $match))
		{
			$this->EE->TMPL->tagdata = str_replace($match[0], '', $this->EE->TMPL->tagdata);
			
			$this->EE->TMPL->no_results = $match[1];
		}
		
		if($results === FALSE || ($results->has_searched && ($results->response === FALSE || $results->response->num_rows() == 0)))
		{
			$vars = array();
			
			foreach($results->rules as $rule)
			{
				$vars = array_merge($vars, $rule->get_vars());
			}
			
			return $this->parse(array($vars), $this->EE->TMPL->no_results());
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
				'status' 	  => implode('|', $results->statuses)
			));
		}
		
		$this->EE->TMPL->tagdata = $this->parse(array($vars));
		
		$_GET  = $get;
		$_POST = $post;
		
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
	
	public function set()
	{
		if($value = $this->param('value', $this->EE->TMPL->tagdata))
		{
			$name = $this->param('name', (isset($this->EE->TMPL->tagparts[2]) ? $this->EE->TMPL->tagparts[2] : FALSE));
			
			if($name)
			{
				if($type = $this->param('type', 'get') == 'get')
				{
					$_GET[$name] = $value;
				}
				else
				{
					$_POST[$name] = $value;
				}
			}
		}
		
	}
	
	public function reset()
	{
		$name = $this->param('name', isset($this->EE->TMPL->tagparts[2]) ? $this->EE->TMPL->tagparts[2] : false);
		
		unset($_GET[$name]);
		unset($_POST[$name]);
	}
	
	public function get()
	{
		$strtotime = $this->param('strtotime');
		$default   = $this->param('default', NULL);
		$name      = $this->param('name', isset($this->EE->TMPL->tagparts[2]) ? $this->EE->TMPL->tagparts[2] : false);
		$return    = $this->EE->input->get_post($name);
		$return    = $return !== FALSE ? $return : $default;
		
		if($format = $this->param('format'))
		{
			$return = date($format, preg_match('/^\d*$/', $return) ? $return : ($strtotime ? strtotime($strtotime, strtotime($return)) : strtotime($return)));	
		}
		
		return $return; 
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
		return $this->EE->channel_search_lib->param($param, $default, $boolean, $required);		
	}
}