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

class Channel_search_ext {
	
	public $version;

    public $name       		= 'Channel Search';
    public $description    	= '';
    public $settings_exist 	= 'n';
  	public $docs_url       	= 'http://www.objectivehtml.com/';
	public $settings 		= array();
	public $required_by 	= array('module');
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
        $this->version	= config_item('channel_search_version');
	}
	
	public function channel_entries_tagdata_end($tagdata, $row, $obj)
	{		
		// has this hook already been called?
		if ($this->EE->extensions->last_call)
		{
			$tagdata = $this->EE->extensions->last_call;
		}

		if($this->EE->TMPL->fetch_param('channel_search_result_tag') != 'yes')
		{
			return $tagdata;
		}

		if(isset($this->EE->session->cache['channel_search']['search_results']))
		{
			$cache = $this->EE->session->cache['channel_search']['search_results'];
			
			$vars = array();

			if(isset($cache->rules))
			{
				foreach($cache->rules as $rule)
				{
					$vars = array_merge($vars, $rule->get_vars_row($row));
				}
				
				$tagdata = $this->EE->TMPL->parse_variables_row($tagdata, $vars);	
			}		
		}
		
		$this->EE->session->set_cache('channel_search', 'search_results', array());
			
		return $tagdata;
	}
	
	public function channel_entries_query_result($obj, $result)
	{
		$obj =& $obj;
		
		// -------------------------------------------
		//  Get the latest version of $query_result
		// -------------------------------------------

		if ($obj->EE->extensions->last_call !== FALSE)
		{
			$result = $obj->EE->extensions->last_call;
		}

		if($this->EE->TMPL->fetch_param('channel_search_result_tag') != 'yes')
		{
			return $result;
		}

		if(isset($this->EE->session->cache['channel_search']['search_results']))
		{
			$cache 	  = $this->EE->session->cache['channel_search']['search_results'];
			$response = $cache->response->result();
			
			$result[0]['is_first_row'] = TRUE;
			$result[count($result) - 1]['is_last_row'] = TRUE;

			if(isset($obj->pagination))
			{
				$obj->pagination->paginate = true;
				$obj->pagination->per_page = $cache->limit;
				$obj->pagination->offset = $cache->offset;
			}
			
			foreach($result as $index => $row)
			{
				$result[$index]['index'] = $index;
				$result[$index]['is_not_first_row'] = FALSE;
				$result[$index]['is_not_last_row']  = TRUE;

				if($index > 0)
				{
					$result[$index]['is_first_row'] = FALSE;
					$result[$index]['is_not_first_row'] = TRUE;
				}
				
				if($index < count($result) - 1)
				{
					$result[$index]['is_last_row'] = FALSE;
					$result[$index]['is_not_last_row'] = TRUE;
				}
				
				foreach($cache->rules as $rule)
				{
					if(!isset($response[$index]))
					{
						//$response[$index] = array();
					}

					$result[$index] = array_merge($result[$index], $rule->get_vars_row(array_merge((array) $response[$index], $row)));
				}
			}
				
			$vars = array();
				
			foreach($cache->rules as $rule)
			{
				$vars = array_merge($vars, $rule->get_vars());
			}

			$obj->EE->TMPL->tagdata = $obj->EE->TMPL->parse_variables_row($obj->EE->TMPL->tagdata, (array) $vars);
		}

		return $result;
	}	 
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @return void
	 */
	function activate_extension()
	{	    
	    return TRUE;
	}
	
	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return  mixed   void on update / false if none
	 */
	function update_extension($current = '')
	{
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	
	    if ($current < '1.0')
	    {
	        // Update to version 1.0
	    }
	
	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->update('extensions', array('version' => $this->version));
	}
	
	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->delete('extensions');
	}
	
}
// END CLASS

/* End of file ext.gmap.php */
/* Location: ./system/expressionengine/third_party/modules/gmap/ext.gmap.php */
