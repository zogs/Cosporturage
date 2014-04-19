<div class="viewuser">

	<?php echo $this->session->flash() ;?>			

	<div class="container page-container">
		<div class="fresque"></div>
		<div class="calendar-return"><a class="tooltiptop" data-toggle="tooltip" title="Retour au calendrier" rel="nofollow" href="<?php echo Router::url('calendar/date/'.$this->cookieEventSearch->read('date'));?>"> </a></div>	
		<div class="white-sheet">
			<section>
				<div class="content-header">
					<div class="header-left">
						<div class="user-login">
							<img src="<?php echo $user->getAvatar();?>" alt="">
							<h1>
								<?php echo $user->getLogin();?>
							</h1>
								<?php if($user->getAccount()=='asso'): ?><span class="label ws-label ws-label-grey tooltiptop" data-toggle="tooltip" title="Association"><i>A</i></span><?php endif;?>
								<?php if($user->getAccount()=='bizness'): ?><span class="label ws-label ws-label-grey tooltiptop" data-toogle="tooltip" title="Professionnel"><i>P</i></span><?php endif;?>							

							<span class="user-descr">
								<?php if(!empty($user->descr)): ?>
									<?php echo $user->descr;?>
								<?php else: ?>
									<?php if($user->getID()==$this->session->user()->getID()):?>
										<a href="<?php echo Router::url('users/account/profil');?>">- ajouter votre description ici -</a>
									<?php else:?>
									- pas encore de description -
									<?php endif;?>
								<?php endif;?>
							</span>
						</div>						
					</div>
					<div class="header-right user-info">

							<span><strong><?php echo $user->getAge();?> , inscrit depuis <?php echo date('d/m/Y',strtotime($user->date_signin));?></strong></span>
							<span><?php echo count($pastParticipation);?> participation | <?php echo count($hasOrganized);?> organisation</span>
							<span><i><?php 
							$loc = (array) $user->location;
							$loc = array_reverse($loc);
							foreach ($loc as $state) {
								echo $state.', ';
							}							
							?>
							</i></span>
					</div>
				</div>
				<div class="event-action-bar <?php if(count($monthParticipation)==0) echo 'event-pending'; else echo 'event-confirmed';?>">
					<div class="count_activity">
						<span class="label <?php if(count($monthParticipation)!=0) echo 'label-success';?>"><?php echo count($monthParticipation);?></span> sports ce mois-ci						
					</div>
					<div class="which_activity">
						<?php if(!empty($sportsPracticed)): ?>
							Sports pratiqués : <?php foreach ($sportsPracticed as $sport):?><span class="ws-icon ws-icon-small ws-icon-<?php echo $sport->sport_slug;?>"></span><?php endforeach;?>
						<?php endif;?>
					</div>
				</div>
			</section>

			<section>
				<div class="col_large">

					<div class="block block-yellow event-reviews">
						<h3>L'Avis des autres</h3>
						<div class="block-content">
							<ul>
								<?php if(!empty($eventsReviewed)): ?>
									<?php foreach ($eventsReviewed as $key => $review): ?>
									<?php if(!empty($review->event)):?>
									<li>
											<a href="<?php echo Router::url('users/view/'.$review->user->getID().'/'.$review->user->getLogin());?>" rel="me nofollow">
												<img class="event-avatar tooltiptop" src="<?php echo $review->user->getAvatar();?>" alt="" data-toggle="tooltip" data-original-title="<?php echo $review->user->getLogin();?> (<?php echo $review->user->getAge();?>)">
											</a>
											<span><?php echo $review->review; ?></span>				
											<small><a href="<?php echo Router::url($review->event->getUrl());?>"><?php echo $review->event->getTitle(); ?></a></small>						
									</li>
									<?php endif; ?>
									<?php endforeach; ?>
								<?php else: ?>
									<li><small class="noevents">Personne n'a encore laisser d'avis</small></li>
								<?php endif;?>
							</ul>
						</div>
					</div>

					<div class="event-discussion">		
						<h3>Discussion</h3>
						<div class="event-comments">
							<?php 

							//Call to comment system
							$this->request('comments','show',array(
																		array('context'=>'user',
																				'context_id'=>$user->getID(),
																				'displayRenderButtons'=>false,
																				'enableInfiniteScrolling'=>false,
																				'levelFormReplyToDisplay'=>0,
																				'enablePreview'=>true,
																				'enableInfiniteScrolling'=>true,
																			)
																	)
											);

							?>
						</div>
					</div>
				</div>

				<div class="col_small">

					<div class="block block-orange events-list">
						<h3>Organise prochainement</h3>
						<div class="block-content">
							<ul>
							<?php if(!empty($organiseEvents)): ?>
							<?php foreach ($organiseEvents as $e): ?>
								<li>

									<span class="ws-icon ws-icon-small ws-icon-<?php echo $e->sport->slug;?>"></span>
									<a href="<?php echo $e->getUrl();?>"><strong><?php echo $e->getTitle();?></strong></a>								
									<small><?php echo $e->getDate();?></small>

									<?php if(!empty($e->serie)): ?><a class="showListSerie linkclose" href="#"><?php echo count($e->serie);?> autres dates</a>	<?php endif; ?>
									<?php if(!empty($e->serie)):?>
										<ul class="listserie">
											<?php foreach ($e->serie as $ev):?>
											<li><strong><a href="<?php echo $ev->getUrl();?>"><?php echo $ev->getDate();?></a></strong></li>
											<?php endforeach;?>
										</ul>
								<?php endif; ?>	


								</li>
							<?php endforeach; ?>

							<?php else: ?>
								<li>
									<small class="noevents">Pas d'activité de prévu...</small>
								</li>
							<?php endif; ?>
							</ul>							
						</div>
					</div>

					<div class="block block-green events-list">
						<h3>Participe prochainement</h3>
						<div class="block-content">
							<ul>
								<?php if(!empty($futurParticipation)): ?>
								<?php foreach ($futurParticipation as $e): ?>
								<li>

									<span class="ws-icon ws-icon-small ws-icon-<?php echo $e->sport->slug;?>"></span>
									<a href="<?php echo $e->getUrl();?>"><strong><?php echo $e->getTitle();?></strong></a>
									<small><?php echo $e->getDate();?></small>

									<?php if(!empty($e->serie)): ?><a class="showListSerie linkclose" href="#"><?php echo count($e->serie);?> autres dates</a><?php endif; ?>
									<?php if(!empty($e->serie)):?>
									<ul class="listserie">
										<?php foreach ($e->serie as $ev):?>
										<li><strong><a href="<?php echo $ev->getUrl();?>"><?php echo $ev->getDate();?></a></strong></li>
										<?php endforeach;?>
									</ul>
								<?php endif; ?>	


								</li>
								<?php endforeach; ?>


								<?php else:?>
								<li>
									<small class="noevents">Pas d'activité de prévu...</small>
								</li>
								<?php endif;?>
							</ul>							
						</div>
					</div>
					
				</div>
			</section>
		</div>
		<div class="fresque"></div>
	</div>
</div>
