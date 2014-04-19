<div class="container">
	<?php echo $this->session->flash(); ?>

	<script type="text/javascript">
	setTimeout(refreshSending, 60000); 

	function refreshSending(){
		window.location.reload(1);
	}
	</script>
</div>