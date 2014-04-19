<?php

	echo $this->session->flash();

?>


<table class="table table-striped">
	<thead>
		<th>Date</th>
		<th>Titre</th>
		<th>Sport</th>
		<th>Ville</th>
		<th>Description</th>
		<th>Auteur</th>
		<th>Action</th>
	</thead>
	<tbody>

		 <?php foreach ($events as $k => $event): ?>
		 	<tr class="
					<?php if(!$event->isOnline()){ echo 'warning'; }?>

				">

		 		<td>	
		 			Publié 
		 			<abbr class="timeago" title="<?php echo $event->date_depot;?>"><?php echo $event->date_depot;?></abbr>
		 		</td>

		 		<td>		 			
		 			<?php if(!$event->isOnline()):?>
						<span class="label">Desactive</span>
		 			<?php endif;?>

		 			<a href="<?php echo Router::url($event->getUrl());?>"><strong><?php echo $event->getTitle();?></strong></a>

		 			<?php if($event->isASerie()):?>
		 				<span class="label label-info">Serie</span>
		 			<?php endif;?>

		 		</td>

		 		<td>		 		
		 			<?php echo $event->getSportName();?>
		 			<br/>
		 			<span class="ws-icon-<?php echo $event->getSportSlug();?>" style="font-size:30px"></span>
		 			
		 		</td>

		 		<td>
		 			<?php echo $event->getCityName();?>
		 		</td>

		 		<td>
		 			<small>
		 				<?php echo $event->getDescription();?>
		 			</small>
		 		</td>

		 		<td>
		 			
		 				<?php echo $event->author->login;?>
		 				<br>
		 				<?php if($event->author->isAsso() || $event->author->isPro()): ?>
		 				<span class="label"><?php echo $event->author->account;?></span>
		 				<br>
		 				<?php endif; ?>
		 				<small><?php echo $event->author->email;?></small>
		 			
		 		</td>

		 		<td>
		 			<?php if($event->isOnline()):?>
		 			<a class="btn btn-mini btn-link" href="<?php echo Router::url('admin/events/desactivate/'.$event->getID());?>" >Suspendre</a>		 			
		 			<?php else:?>
					<a class="btn btn-mini btn-link" href="<?php echo Router::url('admin/events/activate/'.$event->getID());?>" >Activer</a>		 	
		 			<?php endif;?>
		 			<a class="btn btn-mini btn-link" href="<?php echo Router::url('admin/events/delete/'.$event->getID());?>" onclick="return confirm('Etes vous sûr de spprimer cette activité ?')">Supprimer</a>
		 		</td>
		 	</tr>
		 	
		 <?php endforeach ?>
	</tbody>
</table>