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
	
	public function char_count()
	{
		return strlen(trim($this->param('string', $this->EE->TMPL->tagdata)));
	}

	public function random_hash()
	{
		return md5(time());
	}
	
	public function date()
	{
		$date = $this->param('string', $this->param('time', $this->EE->TMPL->tagdata ? trim($this->EE->TMPL->tagdata) : FALSE));

		if(!$date)
		{
			$date = $this->EE->localize->now;
		}

		if(!preg_match('/^\d*$/', $date))
		{
			$date = strtotime($date);
		}

		if($format = $this->param('format', $this->param('date_format')))
		{
			return date(str_replace('%', '', $format), $date);
		}

		return $date;
	}
	
	public function entry_has_category()
	{
		$entry_id = $this->param('entry_id', FALSE, FALSE);

		if(!$entry_id)
		{
			$entry = $this->EE->channel_data->get_entries(array(
				'where' => array(
					'url_title' => $this->param('url_title', FALSE, FALSE, TRUE)
				)
			));

			$entry_id = $entry->row('entry_id');

			if(!$entry_id)
			{
				return FALSE;
			}
		}

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
		$vars = $this->param('vars', $this->param('params', TRUE, TRUE), TRUE);

		return page_url(TRUE, $vars, FALSE);
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
				
					if($this->param('type', 'get') == 'get')
					{
						$_GET[$name]  = $value;
					}
					else
					{
						$_POST[$name] = $value;
					}
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
		
		if(is_array($this->EE->TMPL->tagparams))
		{
			foreach($this->EE->TMPL->tagparams as $param => $value)
			{
				$pattern = '/^default:/';

				if(preg_match($pattern, $param))
				{
					$param = preg_replace($pattern, '', $param);

					if(!$this->EE->input->get_post($param))
					{
						if($this->param('type', 'get') == 'get')
						{
							$_GET[$param] = $value;
						}
						else
						{
							$_POST[$param] = $value;
						}
					}
				}
			}
		}

		foreach(array_merge($_GET, $_POST) as $index => $value)
		{
			$vars[$index] = $this->EE->input->get_post($index, TRUE);

			if($format = str_replace('%', '', $this->param('format:'.$index)))
			{
				$vars[$index] = date($format, strtotime($vars[$index]));
			}

			$delimeter = $this->param('delimeter:', $index);

			if(!$delimeter)
			{
				$delimeter = '|';
			}

			if(is_array($vars[$index]))
			{
				$vars[$index] = implode($delimeter, $vars[$index]);
			}
		}
		
		if($prefix = $this->param('prefix', 'form'))
		{
			$vars = $this->EE->channel_search_lib->add_prefix($prefix, $vars);
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

	public function segment()
	{
		$name     = $this->param('name');
		$segment  = $this->param('segment');
		$segments = $this->EE->uri->segment_array();
		
		if(preg_match('/^\d*$/', $segment))
		{
			if(isset($segments[$segment]))
			{
				return TRUE;
			}
		}

		foreach($segments as $index => $segment)
		{
			if($segment == $name)
			{
				return isset($segments[$index + 1]) ? $segments[$index + 1] : FALSE;
			}
		}

		return FALSE;
	}

	public function segment_exists()
	{
		$segment  = $this->param('name', $this->param('segment'));
		$segments = $this->EE->uri->segment_array();

		if(preg_match('/^\d*$/', $segment))
		{
			if(isset($segments[$segment]))
			{
				return TRUE;
			}
		}

		if(in_array(strtolower($segment), $segments))
		{
			return TRUE;
		}

		return FALSE;
	}
	
	public function categories()
	{
		$group_id 	   = $this->param('group_id');
		$parent_id 	   = $this->param('parent_id');

		$url_title 	   = $this->param('url_title');
		$entry_id 	   = $this->param('entry_id');
		$cat_url_title = $this->param('cat_url_title');
		$cat_id 	   = $this->param('cat_id');
		$cat_name 	   = $this->param('cat_name');

		$where = array();

		if($url_title || $entry_id)
		{
			$entry_where = array();

			if($url_title)
			{
				$entry = $this->EE->channel_data->get_entries(array(
					'where' => array(
						'url_title' => $url_title
					),
					'limit' => 1
				));

				if($entry->num_rows() == 1)
				{
					$entry_id = $entry->row('entry_id');
				}
			}

			if($entry_id)
			{
				$cat    = $this->_get_last_category_from_entry($entry_id);
			
				if($cat)
				{
					$cat_id = $cat['cat_id'];
				}
			}
		}

		if($cat_id !== FALSE && !empty($cat_id))
		{
			$where['cat_id'] = $cat_id;
		}

		if($group_id !== FALSE && !empty($group_id))
		{
			$where['group_id'] = $group_id;
		}

		if($parent_id === '0' || ($parent_id !== FALSE && !empty($parent_id)))
		{
			$where['parent_id'] = $parent_id;
		}

		if($cat_url_title !== FALSE && !empty($cat_url_title))
		{
			$where['cat_url_title'] = $cat_url_title;
		}

		if($cat_name !== FALSE && !empty($cat_name))
		{
			$where['cat_name'] = $cat_name;
		}

		if(!count($where))
		{
			return '';
		}

		$categories = $this->_get_categories($where);

		$categories = $this->_show_trail($categories);
		$categories = $this->_show_param($categories, 'show_parent', 'cat_id', 'parent_id');
		$categories = $this->_show_param($categories, 'show_children', 'parent_id', 'cat_id');
		$categories = $this->_show_parents($categories);
		$categories = $this->_show_siblings($categories);

		$count = 0;
		$total = count($categories);

		foreach($categories as $index => $category)
		{
			$categories[$index]['parent_category'] = array($this->_get_parent_category($category['cat_id']));
			$categories[$index]['is_last_category'] = FALSE;
			$categories[$index]['is_not_last_category'] = TRUE;
			$categories[$index]['is_not_first_category'] = TRUE;
			$categories[$index]['is_first_category'] = FALSE;
			$categories[$index]['total_categories'] = $total;

			if($count == 0)
			{
				$categories[$index]['is_first_category'] = TRUE;
				$categories[$index]['is_not_first_category'] = FALSE;
			}

			if($count + 1 == $total)
			{
				$categories[$index]['is_last_category'] = TRUE;
				$categories[$index]['is_not_last_category'] = FALSE;
			}

			$count++;
		}

		if(!count($categories))
		{
			return $this->EE->TMPL->no_results();
		}

		if($prefix = $this->param('prefix'))
		{
			$categories = $this->EE->channel_search_lib->add_prefix($prefix, $categories);
		}

		return $this->parse($categories);
	}

	public function segments()
	{
		$start = $this->param('start', FALSE);
		$stop  = $this->param('stop', FALSE);
		$limit = $this->param('limit', FALSE);

		$segments = $this->EE->uri->segment_array();

		$return = array();

		$valid = FALSE;
		$count = 0;

		if(!$start)
		{
			$valid = TRUE;
		}

		foreach($segments as $index => $segment)
		{
			if($stop && $stop == $segment)
			{
				$valid = FALSE;
			}

			if($limit && $count >= $limit)
			{
				$valid = FALSE;
			}

			if($valid)
			{
				$return[] = $segment;
				
				$count++;
			}

			if($start && $start == $segment)
			{
				$valid = TRUE;
			}
		}

		$leading_slash = FALSE;

		if(!empty($return) && $this->param('leading_slash', FALSE, TRUE))
		{
			$leading_slash = TRUE;
		}

		$prepend = $this->param('prepend', '');
		$prepend = !empty($prepend) && !empty($return) ? $prepend : '';

		$append = $this->param('append', '');
		$append = !empty($append) && !empty($return) ? $append : '';

		return strtolower($prepend.($leading_slash ? '/' : '').implode('/', $return).$append);
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
			foreach(explode('|', $channels[$name]->cat_group) as $group)
			{
				$category_groups[] = 'OR '.$group;
			}
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
			
			$form_categories = $this->EE->input->get_post($this->param('category_field', 'category'));
			
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
			$return = array(
				array(
					'total_results' => $results->grand_total,
					'data' 			=> $results->result_array()
				)
			);

			if($prefix = $this->param('prefix', ''))
			{
				$return = $this->EE->channel_search_lib->add_prefix($prefix, $return);
			}

			$return = $this->parse($return);
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
		
		if(!$results)
		{
			return;
		}

		if(!$limit)
		{
			$limit = $results->grand_total;
		}
		
		$this->EE->TMPL->tagparams['channel_search_result_tag'] = TRUE;
		
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
			
			$_GET  = $get;
			$_POST = $post;
			
			return $this->parse(array($vars), $this->EE->TMPL->no_results());
		}
		
		$vars = array(
			//'total_results'  => $results->response->num_rows(),
			'grand_total'      => $results->grand_total,
			'sort'             => $sort,
			'order_by'         => $order_by,
			'orderby'          => $order_by,
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
		
		if($format = str_replace('%', '', $this->param('format')))
		{
			$return = date($format, preg_match('/^\d*$/', $return) ? $return : ($strtotime ? strtotime($strtotime, strtotime($return)) : strtotime($return)));	
		}
		
		if(is_array($return))
		{
			$return = implode($this->param('delimeter', '|'), $return);
		}

		return $return; 
	}
	
	private function _get_root_category($cat_id, $categories)
	{
		$return = FALSE;

		foreach($categories as $cat_index => $category)
		{
			if($cat_id == $category['cat_id'])
			{
				if($category['parent_id'] == 0)
				{
					$return = $category;
				}
				else
				{
					$return = $this->_get_root_category($category['parent_id'], $categories);
				}
			}
		}

		return $return;
	}

	private function _get_last_child_category($cat_id, $categories, $return = FALSE, $debug = FALSE)
	{
		foreach($categories as $cat_index => $category)
		{
			if($cat_id == $category['parent_id'])
			{
				$next_node = $this->_get_last_child_category($category['cat_id'], $categories, $category, TRUE);
				
				if($next_node)
				{
					$return = $next_node;
				}
			}
		}

		return $return;
	}

	private function _exists_in_tree($cat_id, $tree)
	{
		$return = FALSE;

		foreach($tree as $node_id => $node)
		{
			if($cat_id == $node_id)
			{
				$return = TRUE;
			}

			else if(isset($node['children']) && count($node['children']) > 0)
			{
				if(!$return)
				{
					$return = $this->_exists_in_tree($cat_id, $node['children']);
				}
			}
		}

		return $return;
	}

	private function _append_to_tree($append_id, $append, $tree, $stop = FALSE)//, $parent, $children)
	{
		foreach($tree as $node_id => $node)
		{
			if($append_id == 0)
			{
				$tree[$append['cat_id']] = array(
					'parent' => $append,
					'children' => array()
				);
			}

			else if($append_id == $node_id)
			{
				$tree[$node_id]['children'][$append['cat_id']] = array(
					'parent' => $append,
					'children' => array()
				);
			}
			
			else if(isset($node['children']) && count($node['children']) > 0)
			{
				$tree[$node_id]['children'] = $this->_append_to_tree($append_id, $append, $node['children']);
			}
		}

		return $tree;
	}
	
	private function _get_category_from_tree($tree, $category)
	{
		$return = FALSE;

		foreach($tree as $parent_id => $branch)
		{
			if($category->cat_id == $parent_id)
			{
				exit('match');
			}
			else
			{
				var_dump($category->cat_id, $parent_id, $branch);exit();
			}
		}

		return $return;
	}

	private function _build_trail($category, $categories = array(), $return = array())
	{
		array_unshift($return, $category);

		if($category['parent_id'] != "0")
		{
			$parent = $this->_get_categories(array(
				'cat_id' => $category['parent_id']
			));

			$return = $this->_build_trail($parent[0], $categories, $return);
		}


		return $return;
	}

	private function _show_siblings($categories)
	{
		if($this->param('show_siblings', FALSE, TRUE))
		{
			$siblings = array();

			foreach($categories as $category)
			{
				$siblings = array_merge($siblings, $this->_get_categories(array(
					'parent_id' => $category['parent_id']
				)));
			}

			return $siblings;
		}

		return $categories;
	}

	private function _show_parents($categories)
	{
		if($this->param('show_parents', FALSE, TRUE))
		{
			$parents = array();

			foreach($categories as $category)
			{
				$parent = $this->_get_categories(array(
					'cat_id' => $category['parent_id']
				));

				$parents = array_merge($parents, $this->_get_categories(array(
					'parent_id' => isset($parent[0]['parent_id']) ? $parent[0]['parent_id'] : 0
				)));
			}

			return $parents;
		}

		return $categories;
	}

	private function _show_param($categories, $param, $where_key, $var_key)
	{
		if($this->param($param, FALSE, TRUE))
		{
			$return = array();

			foreach($categories as $category)
			{
				$show_cats = $this->_get_categories(array(
					'group_id' => $category['group_id'],
					$where_key => $category[$var_key]
				), TRUE);

				if($param == 'show_children' && !count($show_cats))
				{
					$show_cats = $this->_get_categories(array(
						'group_id' => $category['group_id'],
						$where_key => $category[$where_key]
					));

					$return = $show_cats;
				}
				else
				{
					$return = array_merge($show_cats, $return);
				}
			}

			return $return;
		}

		return $categories;
	}

	private function _get_categories($where = array(), $debug = FALSE)
	{	
		$where = array_merge(array(
			'site_id' => $this->param('site_id', config_item('site_id'))
		), $where);

		return $this->EE->channel_data->get_categories(array(
			'where' 	=> $where,
			'limit' 	=> $this->param('limit'),
			'offset' 	=> $this->param('offset'),
			'order_by'  => $this->param('order_by', 'parent_id'),
			'sort' 		=> $this->param('sort', 'desc')
		))->result_array();
	}

	private function _get_last_category_from_entry($entry_id)
	{
		$entry_posts = $this->EE->channel_data->get('category_posts', array(
			'where' => array(
				'entry_id' => $entry_id
			)
		));

		$cat_where = array();

		foreach($entry_posts->result() as $row)
		{
			$cat_where[] = 'or '.$row->cat_id;
		}

		$categories = $this->EE->channel_data->get_categories(array(
			'where' => array(
				'cat_id'    => $cat_where,
				'site_id'   => config_item('site_id')
			),
			'order_by' => 'parent_id asc, cat_order asc',
			'sort'     => ''
		));

		return $this->_get_last_child_category($categories->row('cat_id'), $categories->result_array());
	}

	private function _show_trail($categories)
	{
		if($this->param('show_trail', FALSE, TRUE))
		{
			if(isset($categories[0]))
			{
				return $this->_build_trail($categories[0], $this->_get_categories());
			}
		}

		return $categories;
	}

	private function _get_parent_category($cat_id, $categories = FALSE)
	{
		if(!$categories)
		{
			$categories = $this->_get_categories();
		}

		foreach($categories as $parent_id => $category)
		{
			if($category['cat_id'] == $cat_id)
			{
				return $this->EE->channel_search_lib->add_prefix('parent', $category);
			}
		}

		return FALSE;	
	}

	private function _get_child_categories($cat_id, $categories)
	{
		$return = array();

		foreach($categories as $parent_id => $category)
		{
			if($cat_id == $parent_id)
			{
				$return[] = $category;
			}
		}

		return $return;
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


	private function _setter()
	{
		if($this->EE->TMPL->tagparams)
		{
			foreach(array('default', 'set', 'unset') as $type)
			{
				foreach($this->EE->TMPL->tagparams as $param => $value)
				{
					$pattern = '/^'.$type.':/';
					
					if(preg_match($pattern, $param))
					{
						$param = preg_replace($pattern, '', $param);
						
						if($type == 'default')
						{
							if(!$this->EE->input->get_post($param))
							{
								$_GET[$param]  = $value;
								$_POST[$param] = $value;
							}
						}
						else if($type == 'set')
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

	/*
	private function _build_tree($categories, $tree = array())
	{
		$tree = array();

		$fake_tree = array(
			 1 => array(
				'parent' => array(
					'cat_id' => 1
				),
				'children' => array(
					25 => array(
						'parent' => array(
							'cat_id' => 25
						),
						'children' => array(
							33 => array(
								'parent' => array(
									'cat_id' => 33
								),
								'children' => array(
									34 => array(
										'parent' => array(
											'cat_id' => 34
										),
										'children' => array()
									)
								)
							)	
						)
					)
				)
			)
		);

		$fake_tree = $this->_append_to_tree(0, array('cat_id' => 123), $fake_tree);

		var_dump();
		exit();

		foreach($categories as $cat_index => $category)
		{
			var_dump($category);exit();

			if($category['parent_id'] != '0')
			{
				$this->_append_to_tree($category, $tree);
			}
			else
			{
				exit('stop on parent');
			}
		}

		exit('stop');
	}
	*/

}