<div class="recovery">

	<?php echo $this->session->flash() ;?>			

	<div class="container page_container">
		<div class="white-sheet">
			<div class="fresque"></div>
			
		<?php if($action=='' || $action=='show_form_email'): ?>
		<div class="head-sheet"><h1 class="title-sheet">Mot de passe perdu </h1></div>	
		<form class="form form-ws label-hidden w60 center" action="<?php echo Router::url('users/recovery'); ?>" method="POST">
			<?php echo $this->Form->input('email','Entrer votre adresse email',array('required'=>'required','icon'=>'icon-envelope','placeholder'=>'Entre ton email')) ;?>
			<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ;?>
			<div class="control-group control-submit">
						<label for="" class="control-label"></label>
						<input type="submit" class="btn-ws" value="Envoyer le mail"/>						
			</div>
		</form>	

		<?php endif;?>

		<?php if($action=='show_form_password') : ?>
		<div class="head-sheet"><h1 class="title-sheet">Réinitialisation</h1></div>	
		<form class="form form-ws label-hidden w60 center" action="<?php echo Router::url('users/recovery'); ?>" method="POST">
				
			<?php echo $this->Form->input('code','hidden',array('value'=>$code)) ;?>
			<?php echo $this->Form->input('user','hidden',array('value'=>$user_id)) ;?>	
			<?php echo $this->Form->input('password','Nouveau mot de passe',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Nouveau mot de passe')) ;?>
			<?php echo $this->Form->input('confirm','Confirmer mot de passe',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Confirmer mot de passe')) ;?>
			<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ;?>
			<div class="control-group control-submit">
						<label for="" class="control-label"></label>
						<input type="submit" class="btn-ws" value="Enregistrer"/>						
			</div>	

		</form>

		<?php endif ;?>
		<div class="fresque"></div>
		</div>
		
	</div>
</div>