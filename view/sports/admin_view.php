<div class="page-header">
	<a href="<?php echo Router::url('admin/sports/index');?>">Retour aux sports</a>
	<h1>Editer un sport</h1>
</div>

<form action="<?php echo Router::url('admin/sports/edit');?>" class="form-ws form-vertical" method="POST">


	<h3><?php echo isset($sport->name)? "<span class='ws-icon-".$sport->slug."'></span> ".$sport->name : 'Nouveau';?></h3>
	<?php echo $this->Form->input("sport_id","hidden",array(""=>"")) ;?>
	<?php echo $this->Form->input("name","Nom",array(""=>"")) ;?>
	<?php echo $this->Form->input("slug","Slug",array(""=>"")); ?>
	<?php echo $this->Form->input("action","Action",array(""=>"")) ;?>
	<?php echo $this->Form->input("lang","Lang",array(""=>"")) ;?>
	<?php echo $this->Form->input("token","hidden",array("value"=>$this->session->token())) ;?>
	<?php echo $this->Form->input("","",array("type"=>"submit","value"=>"Sauvergarder","class"=>"btn btn-large btn-primary")) ;?>
	

</form>

<?php if(isset($sport->name)): ?>
<form action="<?php echo Router::url('admin/sports/delete');?>" method="POST">

	<?php echo $this->Form->input("sport_id","hidden",array(""=>"")) ;?>
	<?php echo $this->Form->input("token","hidden",array("value"=>$this->session->token())) ;?>
	<?php echo $this->Form->input("","Supprimer ce sport ?",array("type"=>"submit","value"=>"Supprimer","class"=>"btn btn-small")) ;?>
</form>
<?php endif;?>