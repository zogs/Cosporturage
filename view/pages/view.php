<?php $title_for_layout = $page->title;?>
<div class="page <?php echo $page->slug;?>">

	<?php echo $this->session->flash() ;?>			
	
	<div class="container page-container">
		<div class="white-sheet">
			<div class="fresque fresque-mini"></div>
			<div class="head-sheet"><h1 class="title-sheet"><?php echo $page->title;?></h1></div>

			
			<div class="page-content <?php echo 'page-'.$page->slug;?>">
				<?php echo $page->content; ?>
			</div>				

			<div class="clearfix"></div>
			<div class="fresque"></div>			
		</div>
	</div>
</div>