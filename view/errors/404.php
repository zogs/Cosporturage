<?php 

	$giphy = new Giphy();
	$gifurl = $giphy->getRandomGifUrlByKeywords('sport fail');


?>

<div class="ws-error-page">

	<?php echo $this->session->flash();?>			

	<div class="container page-container">
		<div class="white-sheet">
			<div class="fresque"></div>
			<div class="head-sheet"></div>
			<div class="col_large">	
				<div class="title">
					<h1><?php echo $title; ?></h1>
				</div>
				<div class="message">
					<?php echo $message; ?>		
				</div>
				<div class="forgive">
					Pour nous faire pardonner, voila une petite image aléatoire :)
				</div>
				<div class="action">					
					<ul>
						<li><strong>Sinon tu peux essayer :</strong></li>
						<li><a href="<?php echo Router::url('/');?>">d'allez au calendrier,</a></li>
						<li><a href="<?php echo Router::url('blog');?>">d'allez trainer sur le blog,</a></li>
						<li><a href="<?php echo Router::url('users/register');?>">de t'inscrire ( ah oui tiens bonne idée !)</a></li>
						<li><strong>ou encore de choisir un sport ci-desous...</strong></li>
					</ul>
				</div>
			</div>	
			<div class="col_small">
				<div class="image">
					<img src="<?php echo $gifurl;?>" alt="">
				</div>
			</div>		

			<div class="bottom">
				<?php 
				$sports_available = $this->requestModel('events','findSports',array($this->getLang()));					
				?>
				<div class="sportCheckboxs" id="sportCheckboxs">
					<?php 																	
						foreach ($sports_available as $sport):?>						
							<a class="sportChoice" href="<?php echo Router::url('?sports%5B%5D='.$sport->slug);?>">																
								<label for="label-<?php echo $sport->slug;?>" class="tooltiptop" data-toggle="tooltip" title="<?php echo ucfirst($sport->name);?>">
									<span class="ws-icon ws-icon-<?php echo $sport->slug;?>" style="opacity:1"><strong><?php echo $sport->name;?></strong></span>
								</label>								
							</a>						
					 	<?php endforeach; ?>					
					<?php ?>				
				</div>					
			</div>
			<div class="clearfix"></div>
			<div class="fresque"></div>		
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){

		$('#sportCheckboxs').FlowSlider();
		$('#sportCheckboxs').css('overflow','visible');

	});

</script>