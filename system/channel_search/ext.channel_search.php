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
	
	
	public function channel_entries_query_result($obj, $result)
	{
		$obj =& $obj;
		
		if(isset($this->EE->session->cache['channel_search']['search_results']))
		{
			$cache = $this->EE->session->cache['channel_search']['search_results'];
			$response = $cache->response->result();
			
			$result[0]['is_first_row'] = TRUE;
			$result[count($result) - 1]['is_last_row'] = TRUE;
			
			foreach($result as $index => $row)
			{
				if($index > 0)
				{
					$result[$index]['is_first_row'] = FALSE;
				}
				
				if($index < count($result) - 1)
				{
					$result[$index]['is_last_row'] = FALSE;
				}
				
				$result[$index]['distance'] = isset($response[$index]->distance) ? $response[$index]->distance : 'N/A';
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