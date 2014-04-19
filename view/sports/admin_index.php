<div class="page-header">
	<h1><?php echo count($sports);?> Sports</h1>	
	<a href="<?php echo Router::url('admin/sports/view');?>">Ajouter un sport</a>
</div>

<table class="table table-stripped">
	<thead>
		<th>Id</th>
		<th>Sport</th>
		<th>Icon</th>
		<th>Slug</th>
		<th>Action</th>
		<th>Lang</th>
	</thead>
	<tbody>
		<?php foreach($sports as $k=>$s):?>
			<tr>
				<td><?php echo $s->sport_id;?></td>
				<td><a href="<?php echo Router::url('admin/sports/view/'.$s->sport_id);?>"><?php echo $s->name;?></a></td>
				<td><span class="ws-icon-<?php echo $s->slug;?>"></td>
				<td><?php echo $s->slug; ?></td>
				<td><?php echo $s->action; ?></td>
				<td><?php echo $s->lang;?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>