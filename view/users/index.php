<div class="indexuser">

			<?php echo $this->session->flash() ;?>			

	<div class="container page-container">
		<div class="fresque"></div>		
		<div class="white-sheet">
			<div class="head-sheet">
				<h1 class="title-sheet">
					Les sportifs de 
					<?php

						foreach ($location_names as $key => $value) {
							$txt = $value;
						}
						echo $txt;
					?>
				</h1>
			</div>

			<section>
				<form class="form-locate" action="<?php echo Router::url('users/index/');?>" method="GET">
					<?php				
						echo $this->request('world','locate',array(array("obj"=>$location_codes)));
						
					?>	
					<input type="submit" class="btn-ws btn-ws-small" value="Recherche">				
				</form>
			</section>		

			<section>
				<div class="col_large">	

					<div class="block">
						<div class="userlist">
							<?php foreach ($users as $user):?>
								
								<a class="user" href="<?php echo $user->getLink();?>">
									<img src="<?php echo $user->getAvatar();?>" alt="">
									<span>
										<strong><?php echo $user->getLogin();?></strong>
										<small><?php echo $user->getAge();?> ans</small>
									</span>
								</a>
							<?php endforeach; ?>
						</div>

						<div class="pagination">
							<ul>
								<?php for($i=1; $i <= $nbpage; $i++):?>
										<li <?php if($i==$this->request->get('page')) echo 'class="active"';?>><a href="<?php echo Router::url('users/index/?page='.$i);?>"><?php echo $i;?></a></li>
								<?php endfor; ?>	
							</ul>
						</div>
					</div>				
				</div>

				<div class="col_small">					
				</div>

				<div id="map" style="width:100%; height:250px">
					<?php echo $gmap->getGoogleMap(); ?>

				</div>
			</section>
		</div>
		<div class="fresque"></div>
	</div>
</div>
