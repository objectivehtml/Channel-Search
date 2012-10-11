<table class="mainTable padTable" cellpadding="0" cellspacing="0">

<thead>
	<tr>
		<th>Search ID</th>
		<th>Channels</th>
		<th>Rules</th>
		<th></th>
		<th></th>
	</tr>
</thead>

<tbody>
<?php foreach($settings as $setting): ?>
	<tr>
		<td><?php echo $setting->search_id?></td>
		<td><?php echo $setting->channel_names?></td>
		<td>
			<table class="mainTable padTable" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th>Channel Field Name</th>
						<th>Form Field Name</th>
						<th>Operator</th>
						<th>Prefix</th>
						<th>Suffix</th>
						<th>Driver</th>
					</tr>
				</thead>
				<tbody>
				<?php if(is_array(json_decode($setting->rules))): ?>
					<?php foreach(json_decode($setting->rules) as $rule): ?>
						<tr>
							<td><?php echo $rule->channel_field_name?></td>
							<td><?php echo $rule->form_field_name?></td>
							<td><?php echo $rule->operator?></td>
							<td><?php echo $rule->prefix?></td>
							<td><?php echo $rule->suffix?></td>
							<td><?php echo !empty($rule->driver) ? $rule->driver : 'default'?></td>
						</tr>				
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</td>
		<td align="center"><a href="<?php echo $edit_url?>&id=<?php echo $setting->id?>">Edit</a></td>
		<td align="center"><a href="<?php echo $delete_url?>&id=<?php echo $setting->id?>">Delete</a></td>
	</tr>
<?php endforeach; ?>
</tbody>

</table>