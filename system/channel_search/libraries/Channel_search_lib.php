<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search_lib {
	
	public $ambiguous_columns = array(
		'entry_id',
		'title',
		'url_title',
		'entry_date',
		'expiration_date',
		'status'
	);
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->driver('channel_data');
		$this->EE->load->model('channel_search_model');
	}
	
	public function reset_get()
	{
		$_GET = array();
	}
	
	public function set_cache()
	{		
		$this->EE->functions->set_cookie('channel_search_last_post', json_encode($_POST), strtotime('+1 year'));
	}
	
	public function get_cache()
	{
		if($this->EE->input->cookie('channel_search_last_post'))
		{
			$_POST = (array) json_decode($this->EE->input->cookie('channel_search_last_post'));
		}
	}
	
	public function trim_array($array)
	{
		foreach($array as $index => $value)
		{
			if(is_array($value))
			{
				$array[$index] = $this->trim_array($value);
			}
			else if(is_string($value))
			{
				$array[$index] = trim($value);
			}
		}
		
		return $array;
	}

	public function has_searched($id)
	{
		$search = $id;

		if(!is_object($id))
		{
			$search = $this->EE->channel_search_model->get_rule_by_id($id);

			if($search->num_rows() == 0)
			{
				return FALSE;
			}
			
			$search = $search->row();
		}	

		$has_searched = FALSE;

		if(!empty($search->get_trigger))
		{
			$has_searched = TRUE;

			foreach($this->trim_array(explode(',', $search->get_trigger)) as $trigger)
			{
				$trigger = trim($trigger);
				$value   = $this->EE->input->get_post($trigger);
				
				if($search->empty_trigger != 'true')
				{
					if(!$value || empty($value))
					{
						$has_searched = FALSE;
					}
				}
				else
				{
					if($value === FALSE)
					{
						$has_searched = FALSE;
					}
				}
			}
		}
		else
		{
			$has_searched = TRUE;
		}

		return $has_searched;
	}

	public function validate()
	{
		$merge = array_merge($_POST, $_GET);
		$post  = $_POST;
		$_POST = $merge;

		$return = array(
			'success' 		=> TRUE,
			'field_errors'  => array(),
			'total_errors'  => 0
		);

		$this->EE->load->helper(array('form'));
		$this->EE->load->library('form_validation');

		$config = array();

		foreach($this->EE->TMPL->tagparams as $param => $value)
		{
			if(preg_match('/^rules:/', $param))
			{
				$name  = preg_replace('/^rules:/', '', $param);
				$label = $this->param('label:'.$name, $this->param('labels:'.$name, $name));

				$config[] = array(
					'field'  => $name,
					'label' => $label,
					'rules' => $value
				);
			}
		}

		$this->EE->form_validation->set_rules($config);
		$this->EE->form_validation->set_error_delimiters('', '');

		if(!$this->EE->form_validation->run())
		{
			$errors = array();

			foreach($config as $item)
			{
				$error = form_error($item['field']);;
				$return['error:'.$item['field']] = $error;
				$errors[] = array(
					'field_name'  => $item['field'],
					'field_label' => $item['label'],
					'error' => $error
				);
			}

			$return['total_errors'] = count($errors);
			$return['field_errors'] = $errors;
			$return['success'] 		= $return['total_errors'] ? FALSE : TRUE;
		}

		$_POST = $post;

		return $return;
	}
	
	public function search($id, $order_by = 'exp_channel_titles.entry_id', $sort = 'DESC', $limit = 20, $offset = 0, $export = FALSE)
	{	
		$orig_get  = $_GET;		
		$orig_post = $_POST;
		
		$orig_vars = array_merge($orig_get, $orig_post);
				
		$prevent_search = FALSE;
		
		$search = $this->EE->channel_search_model->get_rule_by_id($id);
		
		if($search->num_rows() == 0)
		{
			return FALSE;
		}
		
		$search    = $search->row();
		$rule_data = $this->EE->channel_search_model->get_rule_modifiers($search->id);
		
		if($rule_data->num_rows() == 0)
		{
			return FALSE;
		}
		
		if(!empty($search->prevent_search_trigger))
		{
			$prevent_triggers = explode(',', $search->prevent_search_trigger);
			
			foreach($prevent_triggers as $trigger)
			{
				if(isset($orig_vars[trim($trigger)]))
				{
					$prevent_search = TRUE;
					
					$_GET  = array();
					$_POST = array();
				}
			}
		}
		
		$from     = array();
		$group_by = array();
		$having   = array();
		$join     = array('INNER JOIN `exp_channel_titles` ON `exp_channel_titles`.`entry_id` = `exp_channel_data`.`entry_id`');
		$select   = array(
			'SQL_CALC_FOUND_ROWS `exp_channel_titles`.*',
			'exp_channel_titles.entry_id',
			'exp_channel_titles.site_id',
			'exp_channel_titles.title',
			'exp_channel_titles.url_title',
			'exp_channel_titles.entry_date',
			'exp_channel_titles.expiration_date',
			'exp_channel_titles.author_id',
			'exp_channel_titles.status',
		);
		
		$where = array();
		$required_where = array('exp_channel_titles.site_id = '.config_item('site_id'));
	
		$channel_names = explode(',', $search->channel_names);
		$channel_where = array();
		
		$field_array = array();
		
		$channels = array();
		$statuses = array();
		
		foreach($channel_names as $channel_name)
		{
			$channel_name = trim($channel_name);
			$channel      = $this->EE->channel_data->get_channel_by_name($channel_name);
			
			if($channel->num_rows() > 0)
			{
				$channel 		 = $channel->row();
				$channels[]      = $channel;
				$channel_where[] = 'exp_channel_titles.channel_id = '.$channel->channel_id;
				
				$status = $this->EE->channel_data->get_statuses(array(
					'where' => array(
						'group_id' => $channel->status_group
					)
				));
				
				foreach($status->result() as $status)
				{
					$statuses[] = $status->status;	
				}
				
				$fields  = $this->EE->channel_data->get_fields(array(
					'where' => array(
						'group_id' => $channel->field_group
					)
				));
								
				foreach($fields->result() as $field)
				{
					$field_array[] = $field;
					$select[] 	   = 'field_id_'.$field->field_id.' as \''.$field->field_name.'\'';
				}				
			}
		}
		
		$channels    = $this->EE->channel_data->utility->reindex('channel_name', $channels);		
		$field_array = $this->EE->channel_data->utility->reindex('field_name', $field_array);
		
		$required_where[] = implode(' OR ', $channel_where);
		
		$rules = array();
		
		foreach($rule_data->result() as $row)
		{
			$row->rules = json_decode($row->rules);
			
			$rule = $this->EE->channel_search_rules->get_rule($row->modifier);
			
			$rule->set_settings($row);
			$rule->set_fields($field_array);
			$rule->set_channels($channels);
			
			$rule_from   = $rule->get_from();
			$rule_having = $rule->get_having();
			$rule_select = $rule->get_select();
			$rule_where  = $rule->get_where();
			$rule_join   = $rule->get_join();
			$rule_group  = $rule->get_group_by();
			
			if($rule_from && !empty($rule_from))
			{
				$from[]  = $rule_from;
			}
			
			if(!empty($rule_having))
			{
				$having[]  = array(
					'clause' => $row->search_clause,
					'rule'   => $rule_having
				);
			}	
			
			if(!empty($rule_join))
			{
				$join[]  = $rule_join;
			}
			
			if(!empty($rule_select))
			{
				$select[]  = $rule_select;
			}
			
			if(!empty($rule_group))
			{
				$group_by[]  = ($rule_group);
			}
			
			if(!empty($rule_where))
			{
				$where[]  = array(
					'clause' => $row->search_clause,
					'rule'   => $rule_where
				);
			}
			
			$rules[$rule->get_export_trigger()] = $rule;		
		}
		
		if(count($join) > 0)
		{
			$join_sql = ' '.implode(' ', $join);
		}
		
		$having_sql = array();
		$where_sql = array();
		
		foreach(array('having', 'where') as $var)
		{
			foreach($$var as $value)
			{
				if(is_array($value))
				{
					if(is_array($value['rule']))
					{
						${$var.'_sql'}[] = $value['clause'].' ('.implode(' AND ', $value['rule']).')';
					}
					else
					{
						${$var.'_sql'}[] = $value['clause'].' ('.$value['rule'].')';
					}
				}
				else
				{
					${$var.'_sql'}[] = 'AND ('.$value.')';
				}
			}
		}
		
		$from_sql = array();
		$group_by_sql = array();
		$select_sql = array();
		
		foreach(array('from', 'group_by', 'select') as $var)
		{
			foreach($$var as $field)
			{
				if(is_string($field))
				{
					${$var.'_sql'}[] = $field;
				}
				else if(is_array($field))
				{
					${$var.'_sql'}[] = implode(', ', $field);
				}
			}
		}
		
		$from = count($from_sql) > 0 ? implode(' ', $from_sql) : '`exp_channel_data`';
		
		$where_sql = $this->clean_sql(implode(' ', $where_sql));
		
		if(in_array($order_by, $this->ambiguous_columns))
		{
			$order_by = 'exp_channel_titles.'.$order_by;
		}
		
		$sql = '
			SELECT 
				'.implode(', ', $select_sql).'
			FROM
				'.$from.'
			'.(isset($join_sql) ? $join_sql : NULL).'
			WHERE
				('.$this->clean_sql(implode(' AND ', $required_where)).')
				'.(!empty($where_sql) ? 'AND ('.$where_sql.')' : NULL).'
			'.(count($group_by_sql) > 0 ? 'GROUP BY '.implode(', ', $group_by_sql) : NULL).'
			'.(count($having_sql) > 0 ? 'HAVING '.$this->clean_sql(implode(' ', $having_sql)) : NULL).'
			ORDER BY '.$order_by.' '.$sort;
		
		if($export)
		{
			$this->EE->load->library('channel_search_export');
			$this->EE->load->model('channel_search_history');
			
			$this->EE->channel_search_history->insert_history($id, array_merge($_GET, $_POST), trim($sql));
			$this->EE->channel_search_export->trigger($export, $sql, $rules);	
		}
		
		$sql .= ($limit > 0 ? ' LIMIT '.$offset.','.$limit : NULL);
		
		$has_searched = $this->has_searched($search);
		
		$validation = array(
			'success' 		 => TRUE,
			'errors'         => array(),
			'validation_ran' => FALSE
		);

		if($has_searched)
		{
			$validation = $this->validate();

			$validation['validation_ran'] = TRUE;
		}

		if(!$validation['success'])
		{
			$has_searched 	  = FALSE;
			$has_not_searched = TRUE;
		}

		if($has_searched)
		{
			$this->EE->load->model('channel_search_history');
			
			$this->EE->channel_search_history->insert_history($id, array_merge($_GET, $_POST), trim($sql));
		}
		
		$response = (object) array_merge(array(
			'response' => $has_searched ? $this->EE->db->query($sql) : FALSE,
			'fields'   => $fields,
			'statuses' => $statuses,
			'channels' => implode('|', $channel_names),
			'has_searched'     => $has_searched ? TRUE : FALSE,
			'has_not_searched' => $has_searched ? FALSE : TRUE,
			'rules'	   => $rules,
			'id'       => $id,
			'order_by' => $order_by,
			'sort'     => $sort,
			'limit'    => $limit,
			'offset'   => $offset,
			'grand_total' => $has_searched ? $this->EE->db->query('SELECT FOUND_ROWS() as \'total\'')->row('total') : 0,
		), $validation);
		
		if($prevent_search)
		{
			$_GET  = $orig_get;
			$_POST = $orig_post;
		}
		
		return $response;
	}
	
	/*
	public function search($id, $order_by = 'entry_id', $sort = 'DESC', $limit = 20, $offset = 0)
	{
		$reserved_fields = array('entry_id', 'site_id', 'channel_id');
		
		$settings = $this->EE->channel_search_model->get_rules($id);
		$select   = array();
		$where    = array();
		
		if(in_array($order_by, $reserved_fields))
		{
			$order_by = 'exp_channel_data.' . $order_by;
		}
		
		foreach($settings->channel_names as $channel_name)
		{
			$channel = $this->EE->channel_data->get_channel_by_name($channel_name);
			$fields  = $this->EE->channel_data->get_channel_fields($channel->row('channel_id'));
			
			foreach($fields->result() as $field)
			{
				$select[] = '`field_id_'.$field->field_id.'` as \''.$field->field_name.'\'';
			}
			
			if($channel->num_rows() == 0)
			{
				if($this->EE->session->userdata['group_id'] == 1)
				{
					$this->EE->output->show_user_error('general', array(
						'\''.$channel_name.'\' is not a valid channel name.'
					));
				}
			}
			else
			{
				$where[] = '`exp_channel_titles`.`channel_id` = '.$channel->row('channel_id').' AND ('.$this->build_rules($settings->rules).')';		
			}	
		}
		
		$base_sql =  '		
		SELECT 
			 SQL_CALC_FOUND_ROWS `exp_channel_titles`.*, '.implode(', ', $select).'
		FROM
			`exp_channel_data`
		INNER JOIN `exp_channel_titles` USING (entry_id)
		'.(!empty($where) ? ' WHERE ' . ltrim(trim(implode(' OR ', $where)), 'OR') : NULL);
		
		$sql = $base_sql . '
			ORDER BY '.$order_by.' '.strtoupper($sort).'	
			LIMIT '.$offset.', '.$limit.'
		';
		
		$response = (object) array(
			'response' => $this->EE->db->query($sql),
			'id'       => $id,
			'order_by' => $order_by,
			'sort'     => $sort,
			'limit'    => $limit,
			'offset'   => $offset,
			'grand_total' => $this->EE->db->query('SELECT FOUND_ROWS() as \'total\'')->row('total'),
		);
		
		return $response;
	}*/
	
	public function parse($entry_data)
	{
		if(!isset($this->EE->TMPL))
		{
			require_once APPPATH.'/libraries/Template.php';
		}
		
		$this->EE->load->library('typography');
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$fields = $this->EE->api_channel_fields->fetch_custom_channel_fields();

		$parse_object = (object) array();

		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$channel_fields = $this->EE->channel_data->get_fields()->result();

		foreach($channel_fields as $index => $field)
		{
			$channel_fields[$field->field_name] = $field;
			unset($channel_fields[$index]);
		}
		
		$TMPL = $this->EE->TMPL;
		
		$this->EE->TMPL = new EE_Template();
		$this->EE->TMPL->template = $TMPL->tagdata;					
		$this->EE->TMPL->template = $this->EE->TMPL->parse_globals($this->EE->TMPL->template);
			
		$vars = $this->EE->functions->assign_variables($this->EE->TMPL->template);

		foreach($vars['var_single'] as $single_var)
		{
			$params = $this->EE->functions->assign_parameters($single_var);

			$single_var_array = explode(' ', $single_var);
			
			$field_name = str_replace('', '', $single_var_array[0]);
		
			$entry = FALSE;

			if(isset($channel_fields[$field_name]))
			{
				$field_type = $channel_fields[$field_name]->field_type;
				$field_id   = $channel_fields[$field_name]->field_id;
				$data       = $entry_data->$field_name;
				
				if($this->EE->api_channel_fields->setup_handler($field_id))
				{
					$this->EE->db->select('*');
					$this->EE->db->join('channel_titles', 'channel_titles.entry_id = channel_data.entry_id');
					$this->EE->db->join('channels', 'channel_data.channel_id = channels.channel_id');
					$row = $this->EE->db->get_where('channel_data', array('channel_data.entry_id' => $entry_data->entry_id))->row_array();
					$this->EE->api_channel_fields->apply('_init', array(array('row' => $row)));

					// Preprocess
					$data = $this->EE->api_channel_fields->apply('pre_process', array($row['field_id_'.$field_id]));

					$entry = $this->EE->api_channel_fields->apply('replace_tag', array($data, $params, FALSE));
					
					$this->EE->TMPL->template = $this->EE->TMPL->swap_var_single($single_var, $entry, $this->EE->TMPL->template );
				}
			}
		}

		$pair_vars = array();

		foreach($vars['var_pair'] as $pair_var => $params)
		{
			$pair_var_array = explode(' ', $pair_var);
			
			$field_name = str_replace('', '', $pair_var_array[0]);
			$offset = 0;

			while (($end = strpos($this->EE->TMPL->template, LD.'/'.$field_name.RD, $offset)) !== FALSE)
			{
				if (preg_match("/".LD."{$field_name}(.*?)".RD."(.*?)".LD.'\/'.$field_name.RD."/s", $this->EE->TMPL->template, $matches, 0, $offset))
				{
					$chunk  = $matches[0];
					$params = $matches[1];
					$inner  = $matches[2];

					// We might've sandwiched a single tag - no good, check again (:sigh:)
					if ((strpos($chunk, LD.$field_name, 1) !== FALSE) && preg_match_all("/".LD."{$field_name}(.*?)".RD."/s", $chunk, $match))
					{
						// Let's start at the end
						$idx = count($match[0]) - 1;
						$tag = $match[0][$idx];
						
						// Reassign the parameter
						$params = $match[1][$idx];

						// Cut the chunk at the last opening tag (PHP5 could do this with strrpos :-( )
						while (strpos($chunk, $tag, 1) !== FALSE)
						{
							$chunk = substr($chunk, 1);
							$chunk = strstr($chunk, LD.$field_name);
							$inner = substr($chunk, strlen($tag), -strlen(LD.'/'.$field_name.RD));
						}
					}
					
					$pair_vars[$field_name] = array($inner, $this->EE->functions->assign_parameters($params), $chunk);
				}
				
				$offset = $end + 1;
			}

			foreach($pair_vars as $field_name => $pair_var)
			{																
				if(isset($channel_fields[$field_name]))
				{
					$field_type = $channel_fields[$field_name]->field_type;
					$field_id   = $channel_fields[$field_name]->field_id;

					$data       = $entry_data->$field_name;

					if($this->EE->api_channel_fields->setup_handler($field_id))
					{
						$entry = $this->EE->api_channel_fields->apply('replace_tag', array($data, $pair_var[1], $pair_var[0]));

						$this->EE->TMPL->template = str_replace($pair_var[2], $entry, $this->EE->TMPL->template);
					}
				}
			}

			$entry = FALSE;
		}
		
		$this->EE->TMPL->template = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->template, (array) $entry_data);
		$this->EE->TMPL->parse($this->EE->TMPL->template);
		
		$return = $this->EE->TMPL->template;
					
		$this->EE->TMPL = $TMPL;
	
		return $return;
	}
	
	public function param($param, $default = FALSE, $boolean = FALSE, $required = FALSE)
	{
		$name 	= $param;
		$param 	= isset($this->EE->TMPL) && $this->EE->TMPL->fetch_param($param) ? $this->EE->TMPL->fetch_param($param) : $this->EE->input->get_post($param);
		
		if($required && !$param) show_error('You must define a "'.$name.'" parameter.');
			
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
	
	public function build_rules($rules)
	{
		$return = array();
		$data   = array();
		
		foreach(array_merge($_GET, $_POST) as $index => $value)
		{
			$data[$index] = $this->EE->input->get_post($index);
		}
		
		foreach($rules as $rule)
		{
			$return[] = $this->build_rule($rule, $data);
		}
		
		return $this->clean_sql($return);
	}
	
	public function build_rule($rule, $data = array())
	{
		$return = array();
		
		$driver = $this->load($rule->driver);
		
		return $driver->build_rule($rule, $data);
	}
	
	public function load($driver)
	{
		if(empty($driver))
		{
			$driver = 'default';	
		}
		
		require_once PATH_THIRD.'/channel_search/drivers/'.$driver.'.php';
		
		$class = $driver.'_search_driver';
		
		return new $class();
	}
	
	public function clean_sql($sql = '')
	{
		if(is_array($sql))
		{
			$sql = implode(' ', $sql);
		}
		
		foreach(array('AND', 'OR') as $value)
		{
			$sql = ltrim($sql, $value);
		}
		
		return trim($sql);
	}
	
	public function next_url($page)
	{
		 return $this->get_url($page+1);
	}
	
	public function prev_url($page)
	{
		 return $this->get_url($page-1);
	}
	
	public function get_url($page)
	{
		$url = NULL;
		 
		 foreach($_GET as $index => $value)
		 {
		 	if($index != 'page')
		 	{
				$url .= '&'.$index.'='.$value;
			}
		 }
		 
		$url .= '&page='.$page;
				
		 return $this->EE->channel_search_lib->current_url().'?'.ltrim($url, '&');
	}
	
	public function current_url($append = '', $value = '')
	{
		$http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
		
		$port = $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? NULL : ':' . $_SERVER['SERVER_PORT'];
		
		if(!isset($_SERVER['SCRIPT_URI']))
		{				
			 $_SERVER['SCRIPT_URI'] = $http . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
		}
		
		$base_url = $http . $_SERVER['HTTP_HOST'];
		
		foreach($this->EE->uri->segment_array() as $segment)
		{
			$base_url .= '/'.$segment;
		}
				
		if(!empty($append))
		{
			$base_url .= '?'.$append.'='.$value;
		}
		
		return $base_url;
	}
	
	/**
	 * Generate Security Hash
	 *
	 * @return String XID generated
	 */
	public function generate_xid($count = 1, $array = FALSE)
	{
		if(!method_exists($this->EE->security, 'generate_xid'))
		{
			$hashes = array();
			$inserts = array();
	
			for ($i = 0; $i < $count; $i++)
			{
				$hash = $this->EE->functions->random('encrypt');
				$inserts[] = array(
					'hash' 		   => $hash,
					'ip_address'   => $this->EE->input->ip_address(),
					'date' 		   => $this->EE->localize->now
				);	
				
				$hashes[] = $hash;	
			}
			
			$this->EE->db->insert_batch('security_hashes', $inserts);
	
			return (count($hashes) > 1 OR $array) ? $hashes : $hashes[0];
		}
		
		return $this->EE->security->generate_xid();
	}
		
}