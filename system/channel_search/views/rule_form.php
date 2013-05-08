<form action="<?php echo $action?>" method="post">
			
	<?php echo $description?>
		
	<p>
		<label for="name">Rule Name</label>
		<input type="text" name="name" value="<?php echo $rule_name?>" id="name" />
	</p>
	
	<p>
		<select name="search_clause" id="search_clause">
			<option value="AND" <?php echo $search_clause == 'AND' ? 'selected="selected"' : ''?> id="search_clause">AND</option>
			<option value="OR" <?php echo $search_clause == 'OR' ? 'selected="selected"' : ''?> id="search_clause">OR</option>
		</select>
	</p>
	
	<hr>
	
	<?php echo $display_rule?>
	
	<hr>
		
	<?php if(isset($return)): ?>
		<input type="hidden" name="return" value="<?php echo $return?>" />
	<?php endif; ?>
	
	<?php if(isset($rule_id)): ?>
		<input type="hidden" name="rule_id" value="<?php echo $rule_id?>" />
	<?php endif; ?>
	
	<?php if(isset($id)): ?>
		<input type="hidden" name="id" value="<?php echo $id?>" />
	<?php endif; ?>
	
	<input type="hidden" name="modifier" value="<?php echo $name?>" />
		
	<input type="hidden" name="XID" value="<?php echo $xid?>" />
	
	<button class="submit"><?php echo $button_text ?></button>
		
</form>
