<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Channel_search {
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('channel_search_lib');
	}
	
	public function results()
	{
		$this->EE->load->helper('url');
		
		$id       = $this->param('id', FALSE, TRUE);
		$order_by = $this->param('order_by', 'entry_id');
		$sort     = $this->param('sort', 'desc');
		$limit    = $this->param('limit', 20);
		$page     = (float) $this->param('page');
		$offset   = $this->param('offset', 0);
		
		if($page)
		{
			$offset = $limit * $page - $limit;
		}
		
		$results = $this->EE->channel_search_lib->search($id, $order_by, $sort, $limit, $offset);
		
		if($results->response->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();	
		}
		
		$return = array();
		$tagdata = NULL;
		
		foreach($results->response->result() as $index => $row)
		{		
			$row->index         = $index;
			$row->count         = $index + 1;
			$row->limit         = $limit;
			$row->offset        = $offset;
			$row->sort          = $sort;
			$row->order_by      = $order_by;
			$row->total_results = $results->response->num_rows();
			$row->grand_total   = $results->grand_total;
			$row->current_page	= $page;
			$row->first_page	= 1;
			$row->last_page		= ceil($results->grand_total / $limit);
			$row->is_first_page = $page == 1 ? TRUE : FALSE;
			$row->is_last_page  = $page == $row->last_page ? TRUE : FALSE;
			$row->total_pages	= $row->last_page;
			$row->next_page		= $row->total_pages > ($page+1) ? $page+1 : $page;
			$row->prev_page		= ($page - 1) > 0 ? $page-1 : 1;			
			$row->next_page_url = $this->EE->channel_search_lib->get_url($page+1);						
			$row->prev_page_url = $this->EE->channel_search_lib->get_url($page-1);
						
			$tagdata .= $this->EE->channel_search_lib->parse($row);			
		}
		
		return $tagdata;
	}
		
	public function get()
	{
		$default = $this->param('default', NULL);
		
		$return = $this->EE->input->get($this->EE->TMPL->tagparts[2]);
		
		return $return !== FALSE ? $return : $default; 
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
		$name 	= $param;
		$param 	= $this->EE->TMPL->fetch_param($param);
		
		if($required && !$param) show_error('You must define a "'.$name.'" parameter in the '.__CLASS__.' tag.');
			
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
}