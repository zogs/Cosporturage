<div class="forum-board">

	<?php echo $this->session->flash();?>


	<div class="container page-container">
		<div class="fresque"></div>
		<div class="white-sheet">
			<section>
				<div class="content-header">
					<div class="title-header">
						<h1>FORUM</h1>
					</div>
				</div>
			</section>

			<section>
				<div id="forum-content">
					<?php echo $this->request('forum','index',array($type,$slug));?>					
				</div>
			</section>
		</div>
	</div>

</div>

<script type="text/javascript">
	$(document).ready(function(){

		$('.link-forum').click(function(e){
			
			e.preventDefault();

			var url = $(this).attr('href');
			var container = $("#forum-content");

			$.ajax({
				url:url,
				type:'GET',
				success: function(html){

					container.empty().html(html);
				}
			})
		});

	});
</script>