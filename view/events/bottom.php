<?php

	$nbevents=0;
	$events = array();

	foreach ($weeks as $week):

		$noCol = 0;
		foreach ($week as $date => $evts):

			$noCol++;
			//$datediff = Date::dateDiff(date('Y-m-d'),$date)%7+1; //compute the number of day between this date and today (modulo 7)
					
			foreach ($evts as $event):	

			ob_start();			
			?>				
			<div class="events <?php if($event->getUserParticipation()) echo 'events-userin' ?> <?php if($event->isConfirm()) echo 'events-confirmed';?> hidden" id="event<?php echo $event->getID();?>">				
				<a class="events-link" 
					href="<?php echo $event->getUrl();?>"	
					draggable="false"
					ondragstart="return false;"				
					data-content="Ville : <strong><?php echo $event->getCityName();?></strong><br />													
									Organisateur : <strong><?php echo $event->getAuthor();?></strong> (<?php echo $event->author->getAge();?>)<br />
									Participants : <strong><?php echo $event->getNbParticipants();?></strong><br />
									<i><?php echo substr(str_replace('"',' ',$event->getDescription()),0,100);?></i>
								"
					<?php if(!$event->confirmed):?>title="<span style='color:grey'>En attente de participants</span>"<?php endif;?>
				>
	
					<div class="events-content">							
					
						<div class="events-logo">
							<span class="ws-sport-icon"><span class="ws-icon-<?php echo $event->sport->slug;?> tooltipbottom" data-toggle="tooltip" title="<?php echo $event->sport->name;?>"></span></span>													
						</div>
						<div class="events-descr">
							<span class="events-title <?php echo (strlen($event->title)>60)? 'title-small' : '';?>"><?php echo ucfirst($event->title); ?></span>																																				
						</div>
						<div class="events-meta">
							<div class="events-time"><?php echo str_replace(':','h',substr($event->getTime(),0,5)); ?></div>
							<div class="events-label">
								<?php if($event->confirmed==1):?>
									<span class="ws-icon ws-icon-checkmark-circle ws-icon-confirm tooltipbottom" data-toggle="tooltip" title="L'activité est confirmé!"></span>										
								<?php else:?>
									<span class="ws-icon ws-icon-checkmark-circle tooltipbottom" data-toggle="tooltip" title="L'activité n'est pas encore confirmé."></span>
								<?php endif;?>
								<?php if(isset($event->UserParticipation)): ?><span class="label ws-label ws-label-important ws-label-participe tooltipbottom" data-toggle="tooltip" title="Je participe"><i class="icon icon-white icon-thumbs-up"></i></span><?php endif; ?>
								<?php if($event->author->isAsso()): ?><span class="label ws-label ws-label-grey tooltipbottom" data-toggle="tooltip" title="Association"><i>A</i></span><?php endif;?>
								<?php if($event->author->isPro()): ?><span class="label ws-label ws-label-grey tooltipbottom" data-toogle="tooltip" title="Bizness"><i>P</i></span><?php endif;?>																							
							</div>
							<div class="events-city"><?php echo $event->getCityName();?></div>
						</div>

					
					</div>
				</a>
				<div class="clearfix"></div>
				<div class="loader"></div>
			</div>

			<?php	


				$nbevents++;

				$html = ob_get_clean(); //get the buffer 

				if(!isset($events[$event->date])) $events[$event->date] = array();

				$events[$event->date][] = $html;


			endforeach;

			
		$cdate = $date;
		endforeach;
		
	endforeach;

	if(!empty($nbevents))
		echo json_encode(array('results'=>$events),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT);
	else
		echo json_encode(array('results'=>'empty'));

		?>
