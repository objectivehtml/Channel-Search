<form action="<?php echo $action?>" method="post">
	
	<?php echo $settings?>
		
	<?php if(isset($return)): ?>
		<input type="hidden" name="return" value="<?php echo $return?>" />
	<?php endif; ?>
	
	<?php if(isset($id)): ?>
		<input type="hidden" name="id" value="<?php echo $id?>" />
	<?php endif; ?>
	
	<input type="hidden" name="XID" value="<?php echo $xid?>" />
	
	<button class="submit"><?php echo $button_text ?></button>	
</form>