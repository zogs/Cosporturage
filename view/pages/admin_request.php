<?php echo $this->session->flash(); ?>

<div class="page-header">
	<h1>Request</h1>		
</div>


<form action="#" class="form" method='POST' name='table_choice'>
	<?php foreach ($all_tables as $key => $value) {
		$all_tables[$value] = $value;
		unset($all_tables[$key]);
	} ?>
	<?php echo $this->Form->select('table','Choisir une table',$all_tables,array('default'=>$table)); ?>
	<?php echo $this->Form->input('table_choice','',array('type'=>'submit','class'=>'btn btn-info','value'=>'OK')); ?>
	<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ?>
</form>


<?php if(isset($all_fields)): ?>

	<form action="#" class="form" method="POST">
		<?php foreach ($all_fields as $key => $value) {
		$all_fields[$value] = $value;
		unset($all_fields[$key]);
		} ?>
		<?php echo $this->Form->select('field','Selectionner le champ',$all_fields,array('default'=>$field)); ?>
		<?php echo $this->Form->input('value','Entrer sa valeur',array('')); ?>
		<?php echo $this->Form->input('query','',array('type'=>'submit','class'=>'btn btn-info','value'=>'Chercher !')); ?>
		<?php echo $this->Form->input('table','hidden',array('value'=>$table)); ?>
		<?php echo $this->Form->input('primaryKey','hidden',array('value'=>$primaryKey)); ?>
		<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ?>

	</form>
<?php endif; ?>

<?php if(isset($results)): ?>
	<?php if(!empty($results)): ?>

		<h3><?php echo count($results);?> results :</h3>

		<form action="#" class="form-ws" method="POST">
		<?php foreach ($results as $result): ?>
			
			<div style="overflow:hidden;margin:60px 25px;padding:20px">
			<?php foreach ($result as $key => $val): ?>
				
				<?php $type = (strlen($val)>10)? 'textarea' : null; ?>
				<?php echo $this->Form->input($key.'[]',$key,array('type'=>$type,'value'=>$val)); ?>
				
			<?php endforeach; ?>

			<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ?>
			<?php echo $this->Form->input('table','hidden',array('value'=>$table)) ?>
			<?php echo $this->Form->input('primaryKey','hidden',array('value'=>$primaryKey)); ?>
			<?php echo $this->Form->input('nbresults','hidden',array('value'=>count($results))); ?>
			<?php echo $this->Form->input('update','',array('type'=>'submit','class'=>'btn btn-large btn-primary','value'=>'Sauvegarder')) ?>

			</div>			

		<?php endforeach; ?>
		</form>
	<?php else: ?>
		No results...
	<?php endif; ?>
<?php endif; ?>