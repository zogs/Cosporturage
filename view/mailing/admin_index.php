
<div class="container">
	
	<h2>Liste des mailings 
		<a class="btn btn-mini btn-info" href="<?php echo Router::url('admin/mailing/editmailing');?>">Créer un nouveau mailing</a>
		<a class="btn btn-mini btn-info" href="<?php echo Router::url('admin/mailing/listmailing');?>">Gestion des listes d'adresses</a>
		<a class="btn btn-mini btn-info" href="<?php echo Router::url('admin/mailing/editsignature');?>">Gestion des signatures</a>
	</h2>
	<table class="table table-striped table-hover">
		<thead>
			<th>Status</th>
			<th>Methode</th>
			<th>Nom du mailing</th>
			<th>Date</th>
			<th>Emails</th>
			<th>Durée</th>
			<th>Action</th>

		</thead>
		<tbody>
			 <?php foreach ($mailings as $m): ?>	
			 	<tr class="<?php
			 			if($m->status=='pending') echo 'info';
			 			if($m->status=='current') echo 'warning';
			 			if($m->status=='finished') echo 'success';
			 		?>">
		 			<td>			 			
		 				<?php if($m->status=='pending'): ?>
		 					<a href="<?php echo Router::url('admin/mailing/launchmailing/'.$m->id);?>"><span class="badge badge-info"><i class="icon icon-play icon-white"></i> Commencer</span> </a>
		 				<?php elseif($m->status=='finished'):?>
		 					<span class="label label-success"><i class="icon icon-ok-circle icon-white"></i> Finished</span>
		 				<?php elseif($m->status=='current'): ?>
							<img src="<?php echo Router::webroot('img/ajax-loader.gif');?>"><span class="label"> En cours</span>
		 				<?php endif;?>
		 			</td>
		 			<td>
		 				<?php echo $m->getMethod(); ?>
		 			</td>	 			
		 			<td><a href="<?php echo Router::url('admin/mailing/editmailing/'.$m->id);?>"><?php echo $m->title; ?></a></td>
		 			<td><?php echo date('d F Y',strtotime($m->date_finished));?></td>
		 			<td><?php echo $m->total_count;?></td>
		 			<td><?php echo $m->duration;?>s</td>
			 		<td>
			 			<a href="<?php echo Router::url('admin/mailing/editmailing/'.$m->id);?>">Editer</a>			 			
			 			<a href="<?php echo Router::url('admin/mailing/deletemailing/'.$m->id);?>">Supprimer</a>
			 		</td>				
						
			 	</tr>
				 </form>
			 <?php endforeach ?>
		</tbody>
	</table>
	
</div>