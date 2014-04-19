<div class="register">

	<?php echo $this->session->flash() ;?>			
	
	<div class="container page-container">
		<div class="white-sheet">
			<div class="fresque"></div>
			<div class="head-sheet"><h1 class="title-sheet">INSCRIPTION</h1></div>

			<form class="form form-ws label-hidden w50 form-register fleft" id="form_register" autocomplete="on" action="<?php echo Router::url('users/register'); ?>" method="post" <?php echo (isset($Success))? 'class="hide"':''; ?>>
				<p class="intro">En 2min via le formulaire suivant:</p>
				<?php echo $this->Form->radio('account','Profil',array('public'=>"Particulier",'asso'=>"Association",'pro'=>"Professionel"),array('default'=>'public','class'=>'account_type','openwrap'=>"<span class='control-radio'>",'closewrap'=>"</span>"));?>

				<?php echo $this->Form->input('login',"Nom d'utilisateur",array('icon'=>'icon-user','required'=>'required','placeholder'=>"Nom d'utilisateur","group-class"=>"user_field","class"=>"inputLogin",'data-url'=>Router::url('users/check'))) ?>
				<?php echo $this->Form->input('login',"Nom de l'association",array('icon'=>'icon-user','required'=>'required',"disabled"=>"disabled",'placeholder'=>"Nom de l'association","group-class"=>"asso_field hide","class"=>"inputLogin",'data-url'=>Router::url('users/check'))) ?>
				<?php echo $this->Form->input('login',"Nom de l'entreprise",array('icon'=>'icon-user','required'=>'required',"disabled"=>"disabled",'placeholder'=>"Nom de l'entreprise","group-class"=>"pro_field hide","class"=>"inputLogin",'data-url'=>Router::url('users/check'))) ?>

				<?php echo $this->Form->input('email',"Email de contact",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"Email de contact",'data-url'=>Router::url('users/check'))) ?>
				<?php echo $this->Form->input('password','Mot de passe',array('type'=>"password",'icon'=>'icon-lock','required'=>'required','placeholder'=>'Mot de passe')) ?>
				<?php echo $this->Form->input('confirm','Confirmer', array('type'=>'password','icon'=>'icon-lock','required'=>'required','placeholder'=>'Confirmer le mot de passe')) ?>		
				<?php echo $this->Form->select('sexe','Sexe',array('h'=>'Homme','f'=>'Femme'),array('placeholder'=>'Sexe','icon'=>'icon-star-empty',"group-class"=>"user_field",)); ?>
				<div class="control-group user_field" id="control-birthday">
					<label for="birthday" class="control-label">Date de naissance</label>
					<div class="controls">
						<?php echo $this->Form->_select('day',Form::MonthDays(),array('default'=>'1','style'=>'width:24%;float:left;margin-right:4%','icon'=>'icon-gift')); ?>
						<?php echo $this->Form->_select('month',Form::Months(),array('default'=>'01','style'=>'width:29%;float:left;margin-right:4%')); ?>
						<?php echo $this->Form->_selectNumber('year',2003,1940,array('default'=>1980,'style'=>'width:29%;float:left')) ?>
					</div>
				</div>
				<div class="control-group">
					<label for="pays" class="control-label"></label>
					<div class="controls">
					
							<?php 
							
							$this->request('world','locate',array(array('style'=>'width:100%')));


							?>	

					</div>
				</div>	
				<div class="actions">
					<?php echo $this->Form->input('token','hidden',array('value'=>$this->session->token())) ;?>			
					<div class="control-group control-submit">
						<label for="" class="control-label"></label>
						<input type="submit" class="btn-ws" value="C'est parti mon kiki !"/>	
						<p class="help help-center">
							En validant l'inscription j'accepte les <a href="<?php echo Router::url('conditions-d-utilisation');?>">conditions d'utilisations</a>	    			
						</p>

					</div>					
				</div>

			</form>


		

			<form class="form-wp form-facebook">
				<p class="intro">Ou en 1 clic seulement avec Facebook!</p>
				<p class="btn-facebook">
					<a rel="nofollow" href="<?php echo $this->request('users','link_register_with_facebook');?>" target="_self"><img src="<?php echo Router::webroot('img/register_with_facebook.png');?>"/></a>									
				</p>
				<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FWesport-Community%2F642996032385245&amp;width=292&amp;height=500&amp;colorscheme=light&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false&amp;appId=153720748148187" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:258px;" allowTransparency="true"></iframe>
			</form>
			
			<div class="clearfix"></div>
			<div class="fresque"></div>
		</div>
	</div>
</div>	

<script type="text/javascript">
	
$(document).ready(function(){

	$(".account_type").change(function(){

		if($(this).attr('id')=='public'){
			$(".user_field").show().find('input,select').prop('disabled',false);
			$(".asso_field,.pro_field").hide().find('input,select').attr('disabled',true);
		}
		if($(this).attr('id')=='asso'){
			$(".asso_field").show().find('input,select').prop('disabled',false);
			$(".user_field,.pro_field").hide().find('input,select').attr('disabled',true);
		}
		if($(this).attr('id')=='pro'){
			$(".pro_field").show().find('input,select').prop('disabled',false);
			$(".user_field,.asso_field").hide().find('input,select').attr('disabled',true);
		}
	});

});
	
</script>