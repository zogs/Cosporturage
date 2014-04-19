<div class="contact">
	
	<?php echo $this->session->flash() ;?>			
	
	<div class="container page-container">
		<div class="white-sheet">
			<div class="fresque fresque-mini"></div>
			<div class="head-sheet"><h1 class="title-sheet"><?php echo $page->title;?></h1></div>

			<form class="form form-contact" action="<?php echo Router::url('pages/contact/#');?>" method="POST">
			<p class="intro"><?php echo $page->content;?></p>

				<?php echo $this->Form->input('user_id','hidden',array('value'=>$user->getID())); ?>
				<?php echo $this->Form->input("name","Votre nom",array("type"=>"text","value"=>$user->getLogin())) ;?>
				<?php echo $this->Form->input("email","Votre mail",array("type"=>"text","value"=>$user->getEmail())) ;?>
				<?php echo $this->Form->input("token","hidden",array("value"=>$this->session->token())) ;?>
				<?php echo $this->Form->input("time","hidden",array("value"=>time())) ;?>
				<?php echo $this->Form->input("login","hidden",array("value"=>"")) ;?>
				<?php echo $this->Form->input("title","Titre du message",array("placeholder"=>"Titre du message")) ;?>
				<?php echo $this->Form->input("message","Votre message",array("type"=>"textarea","placeholder"=>"Dites nous ce que vous pensez, si ça marche ou pas, et nous vous répondrons le plus vite possible","style"=>"height:100px")) ;?>
				<?php echo $this->Form->input('lang','hidden',array('value'=>$this->getLang())); ?>
				
				<?php echo $this->Form->input('submit','',array('type'=>'submit', 'class'=>'btn-ws','value'=>'Envoyer')); ?>
				
				
			</form>
			<div class="clearfix"></div>
			<div class="fresque"></div>			
		</div>
	</div>
</div>
