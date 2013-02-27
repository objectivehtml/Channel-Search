<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textarea_IBField extends InterfaceBuilderField {

	public function displayField($data = FALSE)
	{
		if($data)
		{
			$this->data = $data;	
		}
		
		return '<textarea name="'.$this->name.'" id="'.$this->id.'">'.$this->data.'</textarea>';
	}

}