<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store_channel_search_rule extends Base_rule {
	
	protected $title = 'Expresso Store Search';
	
	protected $description = 'Expresso Store Search allows you to search data stored in the exp_store_products table alongside channel entries.';

	protected $name = 'store';
	
	protected $fields = array(	
		'field_name' => array(
			'label' => 'Channel Field Name',
			'description' => 'The name of the store field you are searching.',
			'type'	=> 'input'
		)		
	);

	public function get_select()
	{
		return array('exp_store_products.*');
	}
	
	public function get_join()
	{
		return 'LEFT JOIN exp_store_products ON exp_channel_data.entry_id = exp_store_products.entry_id';
	}
}