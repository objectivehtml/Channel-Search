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
	public $version = CHANNEL_SEARCH_VERSION;
	
	private $tables = array(
		'channel_search_terms' => array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'site_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'search_id' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'history_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'ip_address' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'member_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'term' => array(
				'type'			=> 'longtext',
			),
			'total_chars' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'total_words' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'date' => array(
				'type'			=> 'timestamp',
			),
		),
		'channel_search_history' => array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'site_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'search_id' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'ip_address' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'member_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'terms' => array(
				'type'			=> 'longtext',
			),
			'params' => array(
				'type'			=> 'longtext',
			),
			'query' => array(
				'type'			=> 'longtext',
			),
			'sql' => array(
				'type'			=> 'longtext',
			),
			'date' => array(
				'type'			=> 'timestamp',
			),
		),		
		'channel_search_rules' => array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 100,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'search_id' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'channel_names' => array(
				'type'			=> 'longtext',
			),
			'get_trigger' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'get_trigger_operator' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'empty_trigger' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'prevent_search_trigger' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			)
		),
		'channel_search_modifiers' => array(
			'id'	=> array(
				'type'				=> 'int',
				'constraint'		=> 11,
				'primary_key'		=> TRUE,
				'auto_increment'	=> TRUE
			),
			'rule_id' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			),
			'name' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'search_clause' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'modifier' => array(
				'type'			=> 'varchar',
				'constraint' 	=> 200
			),
			'rules' => array(
				'type'			=> 'longtext',
			),
			'order' => array(
				'type'			=> 'int',
				'constraint' 	=> 11
			)
		)
	);
	
	private $actions = array(
		array(
			'class' 	=> 'Gmap_mcp',
			'method'	=> 'cron_import_action'
		)
	);
	
	private $hooks = array(
		array('channel_entries_query_result', 'channel_entries_query_result'),
		array('channel_entries_tagdata_end', 'channel_entries_tagdata_end', '', 100)
	);
	
    public function __construct()
    {
        // Make a local reference to the ExpressionEngine super object
        $this->EE =& get_instance();
        
        $this->mod_name 	= str_replace('_upd', '', __CLASS__);
        $this->ext_name		= $this->mod_name . '_ext';
        $this->mcp_name		= $this->mod_name . '_mcp';
    }
	
	public function install()
	{	
		$this->EE->load->library('data_forge');
		
		$this->EE->data_forge->update_tables($this->tables);
				
		$data = array(
	        'module_name' 		 => $this->mod_name,
	        'module_version' 	 => $this->version,
	        'has_cp_backend' 	 => 'y',
	        'has_publish_fields' => 'n'
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
		require_once 'libraries/Data_forge.php';
	
		$this->EE->data_forge = new Data_forge();
		$this->EE->data_forge->update_tables($this->tables);

		foreach($this->actions as $action)
		{
			$this->EE->db->where(array(
				'class'  => $action['class'],
				'method' => $action['method']
			));
			
			$existing = $this->EE->db->get('actions');

			if($existing->num_rows() == 0)
			{
				$this->EE->db->insert('actions', $action);
			}
		}
		
		foreach($this->hooks as $row)
		{
			$this->EE->db->where(array(
				'class'  => $this->ext_name,
				'method'  => $row[0],
				'hook' => $row[1]
			));
			
			$existing = $this->EE->db->get('extensions');

			if($existing->num_rows() == 0)
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
		}
		
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
		
		foreach(array_keys($this->tables) as $table)
		{
			$this->EE->dbforge->drop_table($table);
		}
			
		return TRUE;
	}
	
	private function _set_defaults()
	{ 

	}
}
// END CLASS

/* End of file upd.gmap.php */
/* Location: ./system/expressionengine/third_party/modules/gmap/upd.gmap.php */
