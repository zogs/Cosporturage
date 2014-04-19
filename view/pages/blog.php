<div class="blog">

	<?php echo $this->session->flash(); ?>
	
	<div class="container page-container">	

		<div class="fresque"></div>	

		<div class="blog-banner">
			<img src="<?php echo Router::url('img/blog_banner.jpg');?>" alt="">
		</div>
		
		<div class="white-sheet">		
		
			<div class="col_large">					
				<div class="blog-content">
					<?php 

						$allowComment = false;

						if(!isset($post_id)){

							$this->request('comments','show',array(
																	array(
																		'context'=>'blog',
																		'context_id'=>1,
																		'allowTitle'=>true,
																		'allowComment'=>$allowComment,
																		'allowReply'=>true,
																		'displayRenderButtons'=>false,
																		'enablePreview'=>true,
																		'enableInfiniteScrolling'=>true,
																		'placeholderCommentForm'=>'Rédige ton post ici',
																		'placeholderTitleForm' => 'Donne un titre à ton post',
																		'placeholderReplyForm' => 'Laisser un ptit mot...'
																		),
																	)
											);
						}
						elseif(is_numeric($post_id)){


							$this->request('comments','show',array(
																	array(
																		'context'=>'comment',
																		'context_id'=>$post_id,
																		'allowTitle'=>true,
																		'allowComment'=>$allowComment,
																		'allowReply'=>true,
																		'displayRenderButtons'=>false,
																		'enablePreview'=>true,
																		'enableInfiniteScrolling'=>false,
																		'placeholderCommentForm'=>'Rédige ton post ici',
																		'placeholderTitleForm' => 'Donne un titre à ton post',
																		'placeholderReplyForm' => 'Laisser un ptit mot...'
																		),
																	)
											);
						}

			 	?>

				</div>
			</div>

			<div class="col_small">

					
				

				<div class="block block-orange events-to-come events-list">
					<h3>10 sports à venir</h3>
					<div class="block-content">
						<ul>
							<?php foreach ($eventsToCome as $e):?>							
								<li>
									
									<span class="ws-icon ws-icon-small ws-icon ws-icon-<?php echo $e->sport->slug;?>"></span>
									<a href="<?php echo $e->getUrl();?>" title="<?php echo $e->getTitle();?>">
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
						</ul></div>
				</div>

				<div class="block block-green event-map">
					<h3>Carte des événements</h3>
					<div class="block-content">
						<?php echo $gmap->getGoogleMap();?>
					</div>
				</div>
				
				<div class="block block-red">
					<h3>Publicité</h3>
					<div class="block-content">
						<script type="text/javascript"><!--
							google_ad_client = "ca-pub-5083969946680628";
							/* WeSport big rectangle */
							google_ad_slot = "1932113308";
							google_ad_width = 336;
							google_ad_height = 280;
							//-->
							</script>
							<script type="text/javascript"
							src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
						</script>
					</div>
				</div>		
			</div>

			<div class="clearfix"></div>
		</div>
	</div>
</div>