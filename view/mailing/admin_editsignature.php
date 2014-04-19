<div class="container">
	<?php echo $this->session->flash(); ?>

	<div class="clearfix">
		<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
		<h3>Edition des signatures</h3>		

		<form class='form w80' action="<?php echo Router::url('admin/mailing/editsignature');?>" method="POST" enctype="multipart/form-data">
			

			<?php echo $this->Form->input('name','Nom de la signature',array('type'=>'text')); ?>
						
			<div class="control-group">
				<label for="" class="control-label">Contenu</label>
				<div class="controls">
					<textarea id="content" name="content"></textarea>					
				</div>
			</div>

			<?php echo $this->Form->input('id','hidden',array('value'=>0));?>
			<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())); ?>
			<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-large btn-primary','value'=>'Enregistrer une nouvelle signature')); ?>


		</form>
	</div>
	<script type="text/javascript">
        CKEDITOR.replace( 'content', { filebrowserBrowseUrl: '/js/ckeditor_filemanager/index.html'});
        CKEDITOR.add;
	</script>


	<?php foreach ($signatures as $s):?>
		
		<div class="clearfix">
		<form class='form w80' action="<?php echo Router::url('admin/mailing/editsignature');?>" method="POST" enctype="multipart/form-data">
			
			<?php echo $this->Form->input('name','Nom de la signature',array('type'=>'text','value'=>$s->name)); ?>
			
			<div class="control-group">
				<label for="" class="control-label">Contenu</label>
				<div class="controls">					
					<textarea id="content<?php echo $s->id;?>" name="content<?php echo $s->id;?>"><?php echo $s->content;?></textarea>					
				</div>
			</div>

			<?php echo $this->Form->input('id','hidden',array('value'=>$s->id));?>
			<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())); ?>
			<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-large btn-info','value'=>'Sauvegarder la signature')); ?>
	
			<script type="text/javascript">
	        CKEDITOR.replace( '<?php echo "content".$s->id;?>', { filebrowserBrowseUrl: '/js/ckeditor_filemanager/index.html'});
	        CKEDITOR.add;
			</script>

		</form>
	</div>	
	<?php endforeach;?>	

</div>