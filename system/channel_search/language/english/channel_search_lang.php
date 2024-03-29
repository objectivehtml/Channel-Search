<?php

$lang = array(

'channel_search_module_name'        => 'Channel Search',
'channel_search_module_description' => '',

'channel_search_search_id'          => 'Search ID',
'channel_search_search_id_desc'     => 'To access these rules on the front-end, you must assign a unqiue ID. This field allows you to define as many different search patterns as you desire.',

'channel_search_channel_names'      => 'Channel Names(s)',
'channel_search_channel_names_desc' => 'Enter the channel name(s) you wish to search. If you are searching multiple channels, delimit the channel name with a comma.',

'channel_search_get_trigger'        => 'Variable Trigger(s)',
'channel_search_get_trigger_desc'   => 'This is the name of the GET/POST variable that should trigger the results tag to perform the search.',

'channel_search_get_trigger_operator' => 'Variable Trigger Logical Operator',
'channel_search_get_trigger_operator_desc' => 'This operator is used to determine if multiple variable triggers are valid. By default, the search will only be triggered if all variables are set. If you use the "Or" operator, the search will trigger if only one of the variables are set.',

'channel_search_empty_trigger' 		=> 'Trigger if empty?',
'channel_search_empty_trigger_desc' => 'If this is set to True, the search will be performed if the Variable Triggers are set but have empty values. By default, the Variable Triggers must be set and contain some value to trigger the search.',

'channel_search_prevent_search_trigger' => 'Prevent Search Trigger',
'channel_search_prevent_search_trigger_desc' => 'This is the name of the GET/POST variable that will prevent the search from being searched if it is set.',

'channel_search_manage_rules'       => 'Manage Rules',
'channel_search_new_rule'           => 'New Rule',
'channel_search_edit_rule'          => 'Edit Rule',
'channel_search_edit_search'        => 'Edit Search',
'channel_search_new_search'         => 'New Search',
'channel_search_back_to_rules'      => 'Back to Rules',
'channel_search_back_to_home'       => 'Back to Home',

'channel_search_invalid_search_id'  => 'The search ID you entered is not valid',

/*
'channel_search_channel_field_name' => 'Channel Field Name',
'channel_search_form_field_name' => 'Form Field Name',

'channel_search_operator' => 'Operator (>, >=, <, <=, =, !=, LIKE)',
'channel_search_clause' => 'Clause (AND, OR)',
*/

//
''=>''
);