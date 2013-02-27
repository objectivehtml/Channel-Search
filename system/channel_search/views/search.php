<!--<h2><?php echo $search_id?></h2>-->

<script type="text/javascript">
	
	$(document).ready(function() {
		var url = '<?php echo str_replace('&amp;', '&', $order_url)?>';
		
		// Return a helper with preserved width of cells
		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};
		
		$(".sortable tbody").sortable({
			helper: fixHelper,
			update: function(e, ui) {
			
				var order = [];
				
				$('.sortable tbody tr').each(function() {
					var $t  = $(this);
					var css = $t.index() % 2 == 0 ? 'odd' : 'even';
					
					$t.removeClass('odd').removeClass('even').addClass(css);
					order.push($(this).data('id'));
				});
				
				$.post(url, {rule_id: <?php echo $rule_id?>, order: order}, function(data) {
					console.log(data);
				});
			}
		}).disableSelection();
	
	});
	
</script>

<h3>Channels</h3>

<p><?php echo $channel_names ?></p>

<?php if($modifiers->num_rows() == 0): ?>
	<p class="alert-box warning">There are no modifiers attached to this search.</p>
<?php else: ?>
	<table class="sortable mainTable padTable" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th></th>
				<th>Rule Name</th>
				<th>Type</th>
				<th>Clause</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($modifiers->result() as $row): ?>				
			<tr data-id="<?php echo $row->id?>">
				<td width="10"><?php echo $row->id?></td>
				<td><?php echo $row->name?></td>
				<td><?php echo $row->modifier?></td>
				<td><?php echo $row->search_clause?></td>
				<td width="20"><a href="<?php echo $edit_url . '&id='.$row->id?>">Edit</a></td>
				<td width="20"><a href="<?php echo $delete_url . '&id='.$row->id?>">Delete</a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<h3>Add Search Rule</h3>

<form action="<?php echo $action?>" method="post">
	<?php echo $dropdown ?>
	<input type="hidden" name="rule_id" value="<?php echo $rule_id?>" />
	<button type="submit" class="submit">&plus; Add</button>
</form>