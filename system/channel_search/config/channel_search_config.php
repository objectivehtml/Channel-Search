<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('ee'))
{
    function ee()
    {
        static $EE;
        if ( ! $EE) $EE = get_instance();
        return $EE;
    }
}

$config = array(

	/* --------------------------------------------------------
	 *  Version
	 * -------------------------------------------------------*/
	
	'channel_search_version' => '0.9.5',


	/* --------------------------------------------------------
	 *  Save Searches (TRUE|FALSE)
	 *
	 *  If this setting is set to TRUE, all your searches will
	 *  be saved in the database. Note, this can make your
	 *  database get large on active production sites so set to
	 *  FALSE if this is a concern.
	 * -------------------------------------------------------*/

	'channel_search_save_searches' => TRUE,

	/* --------------------------------------------------------
	 *  Default Export Driver
	 *
	 *  The default driver used for exporting. The following
	 *  formats are valid.
	 *
	 *  csv|json|xls
	 * -------------------------------------------------------*/

	'channel_search_export_driver' => 'csv',

	/* --------------------------------------------------------
	 *  .CSV Export Settings
	 *
	 *  If you need to configure your .CSV exports to output
	 *  differently, update these settings.
	 * -------------------------------------------------------*/

	'channel_search_export_delimeter' => ',',

	'channel_search_export_new_line' => '\n',

	'channel_search_export_enclosure' => '"',
	
	/* --------------------------------------------------------
	 *  Exclude Fields in .CSV
	 *
	 *  If you want to exlude certain fields from the .CSV
	 *  export, you may enter than here.
	 *  
	 *  Sample Format:
	 *  
	 *  array(
	 *		'your_field_name_1',
	 *		'your_field_name_2'
	 *  )
	 * -------------------------------------------------------*/

	'channel_search_export_exclude_fields' => array(
		'site_id',
		'pentry_id',
		'forum_topic_id',
		'ip_address',
		'versioning_enabled',
		'view_count_one',
		'view_count_two',
		'view_count_three',
		'view_count_four',
		'allow_comments',
		'sticky',
		'dst_enabled',
		'year',
		'month',
		'day',
		'expiration_date',
		'comment_expiration_date',
		'edit_date',
		'recent_comment_date',
		'comment_total'
	),

);