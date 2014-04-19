<?php 

//debug($this->vars);
	
?>
<?php 

/*
	Display categories
*/

if($display=='category'):?>
<table class="table table-row table-stripped">
<?php foreach ($categories as $key => $cat):?>
	<tr>
		<td><a class='lllink-forum' href="<?php echo Router::url('forum/board/category/'.$cat->slug);?>"><?php echo $cat->name;?></a></td>
	</tr>
<?php endforeach;?>
</table>
<?php endif;?>




<?php 

/* 
	Display Topics
*/

if($display=='topic'):?>
<table class="table table-row table-stripped">
	<tr>
		<td>
			<small><a href="<?php echo Router::url('forum/board/category');?>">Retour aux sports</a></small>
		</td>
	</tr>
<?php foreach ($topics as $key => $topic):?>
	<tr>
		<td><a href="<?php echo Router::url('forum/board/topic/'.$topic->slug);?>"><?php echo $topic->subject;?></a></td>
		<td><?php echo $topic->user_id;?></td>
		<td><?php echo $topic->date;?></td>
		<td><?php echo $topic->nbreplies;?></td>
	</tr>
<?php endforeach;?>
</table>
<?php endif;?>





<?php
/* 
	Display Replies
*/

if($display=='reply'):?>
<table class="table table-row table-stripped">
	<tr>
	<td><small><a href="<?php echo Router::url('forum/board/category/'.$cat->slug);?>">Retour aux sujets <?php echo $cat->name;?></a></small></td>
	<td><strong><?php echo $topic->subject;?></strong></td>
	</tr>
<?php foreach ($replies as $key => $reply):?>
	<tr>
		<td>
			<a href="<?php echo $reply->user->getLink();?>"><?php echo $reply->user->login;?></a>
			<br>
			<small>Date: <?php echo $reply->date;?></small>
			<br>
			<small>Stauts: offline</small>
			<br>
			<small>Post: 23</small>
			<br>
			<?php if(is_numeric($reply->user->getAge())):?><small>Age: <?php echo $reply->user->getAge();?></small><?php endif;?>
		</td>
		<td>
			<?php echo $reply->content;?>
		</td>
	</tr>
<?php endforeach;?>
	<tr>
		<td>
			<form action="<?php echo Router::url('forum/edit/reply/');?>" method="POST">
				<?php echo $this->Form->input("user_id","hidden",array("value"=>$this->session->user()->getID())) ;?>
				<?php echo $this->Form->input("token","hidden",array("value"=>$this->session->token())) ;?>
				<?php echo $this->Form->input("topic_id","hidden",array("value"=>$topic->topic_id)) ;?>
				<?php echo $this->Form->input("cat_id","hidden",array("value"=>$cat->cat_id)) ;?>
				<?php echo $this->Form->input("content","Votre message",array("type"=>"textarea")) ;?>
				<input class="btn-ws btn-ws-small" type="submit" value="Envoyer">

			</form>
		</td>
	</tr>
</table>
<?php endif;?>