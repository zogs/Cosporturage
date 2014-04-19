<div class="createEvent">

	<?php echo $this->session->flash(); ?>			

	<div class="container page-container">
		<div class="calendar-return"><a class="tooltiptop" data-toggle="tooltip" title="Retour au calendrier" rel="nofollow" href="<?php echo Router::url('calendar/date/'.$this->cookieEventSearch->read('date'));?>"></a></div>	
		<div class="fresque"></div>
		<div class="white-sheet">			
			<div class="head-sheet">			
				<?php if($event->exist()): ?>
				

				<div class="title-event">	
					<a href="<?php echo $event->getUrl();?>">				
					<span class="ws-icon ws-icon-large ws-icon-halo ws-icon-<?php echo $event->sport->slug;?>"></span>
					<h1><?php echo $event->getTitle();?></h1>
					</a>
					<small><?php echo $event->getCityName();?> - <?php echo $event->getDate();?> - <a href="<?php echo $event->getUrl();?>">Voir l'annonce</a></small>					
				</div>
				<?php else: ?>
				<h1 class="title-sheet">
					Proposer une activité !
				</h1>
				<?php endif; ?>
			</div>

			<div class="col_large">				
				<form class="form form-ws form-create" action="<?php echo Router::url('events/create/'.$event->id);?>" method="POST">
			
					<?php echo $this->Form->input("id","hidden",array("value"=>$event->id)) ;?>
					<?php echo $this->Form->input('user_id','hidden',array('value'=>$this->session->user()->getID())) ;?>
					<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ;?>
					<?php echo $this->Form->select('sport','Sport',$sports_available,array('default'=>$event->getSportSlug(),'placeholder'=>"Choisir sa catégorie",'style'=>'width:100%;')); ?>
					<?php echo $this->Form->input('title',"Titre",array('placeholder'=>"Petit match entre amis, ...")) ;?>
					<?php //$this->request('world','formLocate',array('city','Location',$event,array('helper'=>"Choississez jusqu'a votre ville"))); ?>
					<?php echo $this->Form->input("cityID","hidden",array("value"=>$event->getCityID())) ;?>
					<?php echo $this->Form->input("cityName","Ville",array("type"=>"text",'placeholder'=>'Ville',"required"=>"required","style"=>"width:100%;","data-autocomplete-url"=>Router::url('world/suggestCity'))) ;?>
					<?php echo $this->Form->input('address','Adresse',array('placeholder'=>'Salle Michel Bon, 36 rue Henri Dunant, ...')) ;?>
					<div class="control-group" id="control-date">
						<label for="" class="control-label">Date</label>
						<div class="controls">
							<div id="control-ocur">
								<?php echo $this->Form->_input('date','Date',array("class"=>'datepicker','placeholder'=>'ex : 2013/02/26 ','value'=>(($this->request->get('date'))? $this->request->get('date'):''))) ;?>								
							</div>
							<div id="control-recur" style="<?php if($event->isRecurrent()) echo 'display:none';?>">
							<?php echo $this->Form->_input('startdate','',array('class'=>'datepicker','placeholder'=>'Date de début','style'=>'float:left;width:46%;margin-right:6%')); ?>
							<?php echo $this->Form->_input('enddate','',array('class'=>'datepicker','placeholder'=>'Date de fin','style'=>'flaot:left;width:46%')); ?>
							<?php echo $this->Form->_checkbox('recur_day[]','',Form::WeekDays(),array('openwrap'=>'<div class="checkbox_recur_day">','closewrap'=>'</div>'));?>
							</div>
							
							<?php if(isset($this->errors['date']) ||isset($this->errors['startdate']) || isset($this->errors['enddate'])): ?>
								<p class="help-inline help-error">
									<?php if(isset($this->errors['date'])) echo $this->errors['date'].'<br/>'; ?>
									<?php if(isset($this->errors['startdate'])) echo $this->errors['startdate'].'<br/>'; ?>
									<?php if(isset($this->errors['enddate'])) echo $this->errors['enddate'].'<br/>'; ?>
								</p>
							<?php endif; ?>

							<p class="help-inline">
								<?php if(!$event->isRecurrent()):?>
								<a href="#" id="open-control-recur">Date régulière ?</a>
							<?php else:?>
								<small>Cet événement fait partie d'une série</small>
							<?php endif; ?>	
							</p>
								
						</div>
					</div>
	
					<div class="control-group " id="control-time">
						<label class="control-label">Heure</label>
						<div class="controls">
							<?php echo $this->Form->_select('hours',Form::Hours(),array('default'=>$event->getHours(),'style'=>'width:30%','class'=>'inline')); ?>
							<?php echo $this->Form->_select('minutes',Form::Minutes(),array('default'=>$event->getMinutes(),'style'=>'width:30%','class'=>'inline')); ?>
						</div>
					</div>	

					<?php 
					if($this->session->user()->isAsso() || $this->session->user()->isPro()){
						echo $this->Form->input('nbmin','Nombre minimum',array("type"=>"number",'placeholder'=>"Minimum 2 (vous inclus)","value"=>1,"group-class"=>"hide")) ;
					}
					else{
						echo $this->Form->input('nbmin','Nombre minimum',array("type"=>"number",'placeholder'=>"Minimum 2 (vous inclus)","value"=>(isset($event->nbmin)? $event->nbmin : ''))) ;	
					}
					?>					
					
					<?php echo $this->Form->input('description','Descriptif',array('type'=>'textarea','rows'=>'5','placeholder'=>"Préciser niveau de jeu, matériel à amener, le coût (si location de la salle ou autre), préciser la durée, le fonctionnement de l'activité, mixité ou non et si les «pompom girls» ou «pompom boys» sont accepté(e)s !!! Il en faut pour toutes et tous haha!")) ;?>
					<?php echo $this->Form->input("phone","Téléphone",array("type"=>"tel","placeholder"=>"optionnel")) ;?>

				
			</div>



			<div class="col_small">

				<div class="block form-submit">
					<?php if($event->exist()): ?>
						<?php echo $this->Form->input("Mettre à jour l'annonce",'submit',array('class'=>'btn-ws')) ;?>					
						<ul>
							<?php if(!$event->isConfirm()): ?>
								<li><a href="<?php echo Router::url('events/confirm/'.$event->getID().'/'.$this->session->token());?>" class="btn btn-link" onclick="return confirm('Confirmer l\'activité même si le nombre de participants n\'est pas atteint ?')">Confirmer l'activité</a></li>
							<?php endif; ?>

							<li><a href="<?php echo Router::url('events/delete/'.$event->getID().'/'.$this->session->token());?>" class="btn btn-link" onclick="return confirm('Êtes-vous sûr ?')">Supprimer l'activité </a></li>
							
							<?php if($event->isASerie()): ?>
								<li><a href="<?php echo Router::url('events/serieDelete/'.$event->getID().'/'.$this->session->token());?>" class="btn btn-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes les dates ?')">Supprimer toutes les dates</a></li>
							<?php endif;?>

							

							<li><a href="<?php echo Router::url('events/create');?>" class="btn btn-link">Proposer une nouvelle activité</a></li>
						</ul>
					<?php else: ?>
						<?php echo $this->Form->input("Soumettre l'annonce",'submit',array('class'=>'btn-ws')) ;?>
					<?php endif; ?>				

					</form>					
				</div>					

						
				<?php if(!$event->exist()): ?>
				<div class="block block-yellow ">
					<h3>Comment ça marche?</h3>
					<div class="block-content">
						<ul class="event-create-howto">
							<li>
								Vous pouvez créer une activité à une <strong>date précise</strong> ou à une <strong>date régulière</strong>.<small> (ex: tous les lundis et mercredi du mois de Juin)</small>
							</li>
							<li>
								L'activité est <strong>confirmé</strong> quand le nombre minimum attendu <strong>est atteint</strong>. <small>Les participants recoivent un email confirmant que l'activité a bien lieu.</small>
							</li>
							<li>
								L'adresse et le téléphone ne sont <strong>visible que par</strong> les membres We-Sport inscrits.
							</li>
							<li>
								Tous les champs sont <strong>obligatoires</strong>, sauf le téléphone.
							</li>
							<li>
								L'annonce est <strong>modifiable</strong> jusqu'à ce que l'activité soit <strong>confirmé</strong>. <small>Les changements sont envoyés par email aux participants déjà inscrits.</small>
							</li>
						</ul>
					</div>
				</div>
				<?php endif;?>


				<?php if(!empty($eventfutur)):?>
				<div class="block block-green event-to-come events-list">
					<h3>Mes activités à venir</h3>					
					<div class="block-content">
						<ul>

							<?php foreach ($eventfutur as $e):?>							
								<li>									
									<span class="ws-icon ws-icon-small ws-icon ws-icon-<?php echo $e->sport->slug;?>"></span>
									<a href="<?php echo $e->getUrlCreate();?>">
										<strong><?php echo $e->getTitle();?></strong>
									</a>
									<small><?php echo $e->getDate();?></small>
									<?php if(!empty($e->serie)): ?>
										<a class="showListSerie linkclose" href="#"><?php echo count($e->serie);?> autres dates</a>	
									<?php endif; ?>
										<?php if(!empty($e->serie)):?>
										<ul class="listserie">
											<?php foreach ($e->serie as $ev):?>
											<li>														
												<strong><a href="<?php echo $ev->getUrlCreate();?>"><?php echo $ev->getDate();?></a></strong>
											</li>
											<?php endforeach;?>
										</ul>
									<?php endif; ?>																		
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				<?php endif; ?>		

				<?php if(!empty($eventpast)): ?>
				<div class="block block-orange event-finished events-list">
					<h3>Activités terminés</h3>
					<div class="block-content">
						<ul>
						<?php foreach ($eventpast as $e): ?>					
							<li>								
								<span class="ws-icon ws-icon-small ws-icon-<?php echo $e->sport->slug;?>"></span>
								<a href="<?php echo $e->getUrlCreate();?>">
									<strong><?php echo $e->getTitle();?></strong>
								</a>
								<small><?php echo $e->getDate();?></small>
								<?php if(!empty($e->serie)): ?>
										<a class="showListSerie linkclose" href="#"><?php echo count($e->serie);?> autres dates</a>	
									<?php endif; ?>
									<?php if(!empty($e->serie)):?>
									<ul class="listserie">
										<?php foreach ($e->serie as $ev):?>
										<li>														
											<strong><a href="<?php echo $ev->getUrlCreate();?>"><?php echo $ev->getDate();?></a></strong>
										</li>
										<?php endforeach;?>
									</ul>
								<?php endif; ?>	
							</li>
						<?php endforeach;?>
						</ul>
						<a href="<?php echo Router::url('events/create');?>" class="btn btn-link">Proposer une nouvelle activité</a>	
					</div>
				</div>
				<?php endif; ?>		
				
			</div>
		<div class="clearfix"></div>
		<div class="fresque"></div>
		</div>	
	</div>
	
</div>
<script type="text/javascript">
	 $(document).ready(function(){
		
		$( ".datepicker" ).datepicker({
			format : 'yyyy/mm/dd',
			autoclose : true,
			todayHightlight : true,
			language : 'fr'
		});


		$("#open-control-recur").click(function(){
			$("#control-recur").toggle(0);
			$("#control-ocur").toggle(0);
		});
		

	});
</script>