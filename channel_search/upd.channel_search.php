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

include 'config/channel_search_config.php';
include 'libraries/Data_forge.php';

if(!defined('CHANNEL_SEARCH_VERSION'))
{	
	define('CHANNEL_SEARCH_VERSION', $config['channel_search_version']);
}

class Channel_search_upd {

	public $mod_name;
	public $ext_name;
	public $mcp_name;
	public $version;
	
	private $tables = array(
	);
	
	private $actions = array(
	);
	
	private $hooks = array(
	);
	
    public function __construct()
    {
        // Make a local reference to the ExpressionEngine super object
        $this->EE =& get_instance();
        
        $this->version	    = CHANNEL_SEARCH_VERSION;
        
        $this->mod_name 	= str_replace('_upd', '', __CLASS__);
        $this->ext_name		= $this->mod_name . '_ext';
        $this->mcp_name		= $this->mod_name . '_mcp';
    }
	
	public function install()
	{	
        $this->EE->load->config('channel_search_config');
        
        $this->version = CHANNEL_SEARCH_VERSION;
        
		$this->EE->load->library('data_forge');
		
		$this->EE->data_forge->update_tables($this->tables);
		
		$data = array(
	        'module_name' 			=> $this->mod_name,
	        'module_version' 		=> $this->version,
	        'has_cp_backend' 		=> 'y',
	        'has_publish_fields' 	=> 'n'
	    );
	    	
	    $this->EE->db->insert('modules', $data);
	    	    	    
		foreach ($this->hooks as $row)
		{
			$this->EE->db->insert(
				'extensions',
				array(
					'class' 	=> $this->ext_name,
					'method' 	=> $row[0],
					'hook' 		=> ( ! isset($row[1])) ? $row[0] : $row[1],
					'settings' 	=> ( ! isset($row[2])) ? '' : $row[2],
					'priority' 	=> ( ! isset($row[3])) ? 10 : $row[3],
					'version' 	=> $this->version,
					'enabled' 	=> 'y',
				)
			);
		}
		
		foreach($this->actions as $action)
			$this->EE->db->insert('actions', $action);
		
		$this->_set_defaults();
				
		return TRUE;
	}
	
	public function update($current = '')
	{	
		$this->EE->data_forge = new Data_forge();		
		$this->EE->data_forge->update_tables($this->tables);
		
		/* Update the version numbers */
		$this->EE->db->where('name', strtolower($this->mod_name));
		$this->EE->db->update('fieldtypes', array(
			'version' => $this->version
		));
				
		$this->EE->db->where('module_name', strtolower($this->mod_name));
		$this->EE->db->update('modules', array(
			'module_version' => $this->version
		));
		
		/* Do other updates */
		
	    return TRUE;
	}
	
	public function uninstall()
	{
		$this->EE->load->dbforge();
		
		$this->EE->db->delete('modules', array('module_name' => $this->mod_name));
		$this->EE->db->delete('extensions', array('class' => $this->ext_name));		
		$this->EE->db->delete('actions', array('class' => $this->mod_name));
		
		$this->EE->db->delete('actions', array('class' => $this->mod_name));
		$this->EE->db->delete('actions', array('class' => $this->mcp_name));
					
		return TRUE;
	}
	
	private function _set_defaults()
	{ 
		
	}
}
// END CLASS

/* End of file upd.gmap.php */
/* Location: ./system/expressionengine/third_party/modules/gmap/upd.gmap.php */