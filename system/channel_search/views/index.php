<table class="mainTable padTable" cellpadding="0" cellspacing="0">

<thead>
	<tr>
		<th>Search ID</th>
		<th>Channels</th>
		<th>Manage Rules</th>
		<th>Edit</th>
		<th>Delete</th>
	</tr>
</thead>

<tbody>
<?php if(count($settings) == 0): ?>
	<tr>
		<td colspan="5">You have not created any search rules yet. <a href="<?php echo $new_url?>">Create a New Search</a></td>
	</tr>
<?php endif;?>
<?php foreach($settings as $setting): ?>
	<tr>
		<td><?php echo $setting->search_id?></td>
		<td><?php echo $setting->channel_names?></td>
		<td align="center" width="80"><a href="<?php echo $manage_url?>&id=<?php echo $setting->id?>">Manage Rules</a></td>
		<td align="center" width="20"><a href="<?php echo $edit_url?>&id=<?php echo $setting->id?>">Edit</a></td>
		<td align="center" width="20"><a href="<?php echo $delete_url?>&id=<?php echo $setting->id?>">Delete</a></td>
	</tr>
<?php endforeach; ?>
</tbody>

</table>
