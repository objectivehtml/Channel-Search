<form action="<?php echo $action?>" method="post">
	
	<?php echo $settings?>
	
	<h3>Rules</h3>
	
	<?php echo $search_fields?>
	
	<input type="hidden" name="return" value="<?php echo $return?>" />
	<input type="hidden" name="id" value="<?php echo $id?>" />
	
	<button class="submit">Save Settings</button>	
</form>