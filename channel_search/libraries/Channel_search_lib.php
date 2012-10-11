<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search_lib {
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->driver('channel_data');
		$this->EE->load->model('channel_search_model');
	}
	
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
	}
	
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
	
	private function param($param, $default = FALSE, $boolean = FALSE, $required = FALSE)
	{
		$name 	= $param;
		$param 	= $this->EE->input->get_post($param);
		
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
		
		return $this->clean_string(implode(' ', $return));
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
	
	public function clean_string($haystack = '')
	{
		foreach(array('AND', 'OR') as $needle)
		{
			$haystack = ltrim($haystack, $needle);
		}
		
		return trim($haystack);
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
	
}