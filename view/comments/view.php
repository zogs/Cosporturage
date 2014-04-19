
Original context : <a href="<?php echo $context_link;?>"><?php echo $context_name; ?></a>


<?php 
    
          $this->request('comments','show',array(array('context'=>'comment','context_id'=>$comment_id)));

?>


