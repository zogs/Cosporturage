<div id="account">

	<?php echo $this->session->flash() ;?>			

	<div class="container page-container white-sheet">
		<div class="fresque"></div>
		<div class="col_small">
		
			<div class="module module-rounded profil">		
				<a class="" href="<?php echo Router::url('users/view/'.$user->getID());?>">
				<div class="fleft useravatar"><img src="<?php echo $this->session->user()->getAvatar();?>" class="avatar size32" alt="<?php echo $user->login;?>"></div>
				<div class="flef username"><b><?php echo $user->login;?></b></div>
				</a>
				<div class="clearfix"></div>
			</div>
			<div class="module module-rounded menu">
				<ul class="dashboard-links">
					<?php
						$links = array(										
										'account'=>'Compte',
										'profil'=>'Profil',
										'avatar'=>'Avatar',									
										'password'=>'Mot de passe',
										'mailing'=>'Mailing',
										'delete'=>'Suppression'
									);
						$icons = array(
							'account'=>'icon-user',
							'profil'=>'icon-star',
							'avatar'=>'icon-picture',
							'password'=>'icon-lock',
							'mailing'=>'icon-envelope',
							'delete'=>'icon-fire');

						foreach ($links as $key => $value) {
							
							if($key==$action)
								echo '<li class="active">';
							else
								echo '<li class="">';
							echo '<i class="icon '.$icons[$key].'"></i>';
							echo '<a class="" href="'.Router::url('users/account/'.$key).'">'.$value.'</a>';
							echo '</li>';
						}
					?>
				</ul>
			</div>
			
		</div>

		
		<div class="col_large">
			<div class="module module-rounded account-form">
				<form class="form form-ws <?php echo 'form-'.$action;?>" id="account-form" autocomplete="off" action="<?php echo Router::url('users/account/'.$action); ?>" method="post" enctype="multipart/form-data">
					<?php echo $this->Form->_input('action','hidden',array('value'=>$action)); ?>
					<?php echo $this->Form->_input('token','hidden',array('value'=>$this->session->token())) ;?>
					<?php echo $this->Form->_input('user_id','hidden',array('value'=>$this->session->user()->getID())) ;?>

					<?php //=========PROFIL================ ?>
					<?php if($action=='profil'): ?>

						<div class="module-header">
							<h2>Mon Profil</h2>
						</div>
					
						<?php if($user->isPerso()):?>
						<?php echo $this->Form->input('prenom','Prenom',array('icon'=>'icon-user','placeholder'=>'Prenom')); ?>
						<?php echo $this->Form->input('nom','Nom',array('icon'=>'icon-user','placeholder'=>'Nom')); ?>	
						<?php echo $this->Form->input('descr','Description',array('type'=>'textarea','rows'=>3,'placeholder'=>'Description','helper'=>'Décrivez vous en qq mots (130 caractères)')); ?>
						<?php echo $this->Form->select('sexe','Sexe',array('h'=>'Homme','f'=>'Femme'),array('placeholder'=>'Sexe','icon'=>'icon-star-empty','default'=>$user->getSexe())); ?>				
						<div class="control-group" id="control-birthday">
							<label for="birthday" class="control-label">Anniversaire</label>
							<div class="controls">
								<?php echo $this->Form->_select('day',Form::MonthDays(),array('default'=>$user->getBirthDay(),'style'=>'width:26%;','class'=>'inline','icon'=>'icon-gift')); ?>
								<?php echo $this->Form->_select('month',Form::Months(),array('default'=>$user->getBirthMonth(),'style'=>'width:30%;','class'=>'inline')); ?>
								<?php echo $this->Form->_selectNumber('year',2003,1940,array('default'=>$user->getBirthYear(),'style'=>'width:30%;','class'=>'inline')) ?>
							</div>
						</div>						
						<?php endif; ?>
						
						<?php if($user->isAsso()):?>
							<?php echo $this->Form->input('descr',"Description de l'association",array('type'=>'textarea','rows'=>3,'placeholder'=>"Description de l'association",'helper'=>'Décrivez votre association en qq mots (130 caractères)')); ?>
						<?php endif;?>

						<?php if($user->isPro()):?>
							<?php echo $this->Form->input('descr',"Description de l'entreprise",array('type'=>'textarea','rows'=>3,'placeholder'=>"Description de l'entreprise",'helper'=>'Décrivez votre entreprise en qq mots (130 caractères)')); ?>
						<?php endif;?>

						<div class="control-group">	
							<label for="CC1" class="control-label">Localisation</label>			
							<div class="controls">
								<i class='icon-form icon-home'></i>
								<?php $options = $this->request('world','locate',array(array('obj'=>$user,'style'=>'width:100%'))); ?>					
							</div>
						</div>								

						<input type="submit" class="btn btn-large btn-info" value="Sauvergarder" />			
					<?php endif ;?>




					<?php //=========ACCOUNT================= ?>
					<?php if($action=='account'||$action==''): ?>

						<div class="module-header">
							<h2>Mon Compte</h2>
							<p class="subheader"></p>
						</div>

						<?php echo $this->Form->input('login','Login',array('icon'=>'icon-user','required'=>'required','data-url'=>Router::url('users/check'))); ?>
						<?php echo $this->Form->input('email','Email',array('icon'=>'icon-envelope','required'=>'required','type'=>'email','data-url'=>Router::url('users/check'))); ?>
						<?php echo $this->Form->Select('lang','Langue',Conf::$languageAvailable,array('default'=>$user->lang,'placeholder'=>'( votre langue )','icon'=>'icon-book')) ;?>				
						<?php echo $this->Form->input('Sauvegarder','submit',array('class'=>'btn btn-large btn-info')) ;?>


					<?php endif ;?>



					
					<?php //=========AVATAR=================== ?>
					<?php if($action=='avatar'): ?>

						<div class="module-header">
							<h2>Mon avatar</h2>

						</div>

						<div class='avatar'>
							<img src="<?php echo Router::webroot($user->avatar); ?>" />
						</div>
						
							
						<?php echo $this->Form->input('avatar','Avatar',array('icon'=>'icon-bullhorn','type'=>'file')); ?>
							
						<input type="submit" class="btn btn-large btn-info" value="Sauvegarder" />		
				
					<?php endif ;?>


					<?php //=========PASSWORD============== ?>			
					<?php if($action=='password'):?>

						<div class="module-header">
							<h2>Mon mot de passe</h2>
					
						</div>


						<?php echo $this->Form->input('oldpassword','Ancien mot de passe',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Ancien mot de passe')); ?>
						<?php echo $this->Form->input('password','Nouveau mot de passe',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Nouveau mot de passe')); ?>
						<?php echo $this->Form->input('confirm','Confirmer mot de passe',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Confirmer mot de passe')); ?>
						<input class="btn btn-warning" type="submit" value="Sauvegarder le changement" />	
						
					<?php endif ;?>

					<?php //=========MAILING============== ?>			
					<?php if($action=='mailing'):?>

						<div class="module-header">
							<h2>Mailing</h2>
					
						</div>

						<p><strong>Les sports où je participe</strong></p>
						<?php echo $this->Form->checkbox('eventConfirmed','NULL',array(1=>"Recevoir un mail quand l'activité est confirmée"),array(''=>1,'switch'=>1)) ;?>
						<?php echo $this->Form->checkbox('eventCanceled','NULL',array(1=>"Recevoir un mail quand l'activité est annulée"),array(''=>1,'switch'=>1)) ;?>
						<?php echo $this->Form->checkbox('eventChanged','NULL',array(1=>"Recevoir un mail quand l'organisateur change l'événement"),array(''=>1,'switch'=>1)) ;?>
						<?php echo $this->Form->checkbox('eventOpinion','NULL',array(1=>"Recevoir un mail après l'événement pour donner votre avis"),array(''=>1,'switch'=>1)) ;?>
						<?php echo $this->Form->checkbox('eventOrgaReply','NULL',array(1=>"Recevoir un mail quand l'organisateur répond à votre question"),array(''=>1,'switch'=>1)) ;?>
						
						<p><strong>Les sports que j'organise</strong></p>
						<?php echo $this->Form->checkbox('eventUserQuestion','NULL',array(1=>"Recevoir un mail quand quelqu'un pose une question"),array(''=>1,'switch'=>1)) ;?>
						<?php echo $this->Form->checkbox('eventNewParticipant','NULL',array(1=>"Recevoir un mail quand il y a un nouvel inscript à l'activité"),array(''=>1,'switch'=>1)) ;?>
						<input class="btn btn-large btn-info" type="submit" value="Sauvegarder" />	
						
					<?php endif ;?>



					<?php //=======DELETE================ ?>
					<?php if($action=='delete'): ?>

						<div class="module-header">
							<h2>Supprimer mon compte</h2>
							
						</div>

				
						<?php echo $this->Form->input('password','Je veux supprimer', array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Entrer votre mot de passe pour supprimer')); ?>
						<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ;?>
						<input class="btn btn-small btn-danger" type="submit" value="Supprimer le compte" />

					<?php endif ;?>



				</form>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="fresque"></div>
	</div>
	
</div>