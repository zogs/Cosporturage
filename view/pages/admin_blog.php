<div class="blog">
	<div class="top-banner">
		<div class="void"></div>
		<div class="fresque"></div>
		<?php echo $this->session->flash(); ?>
	</div>

	<div class="container">		
		<div class="blog-banner">
			<img src="<?php echo Router::url('img/blog_banner.jpg');?>" alt="">
		</div>
		
		<div class="white-sheet">		
		
			<div class="col_large">					
				<div class="blog-content">
					<?php 

						if($this->session->user()->getRole()=='admin') $allowComment = true;
						else $allowComment = false;

						if(!isset($post_id)){

							$this->request('comments','show',array(
																	array(
																		'context'=>'blog',
																		'context_id'=>1,
																		'allowTitle'=>true,
																		'allowComment'=>$allowComment,
																		'allowReply'=>true,
																		'displayRenderButtons'=>false,
																		'enablePreview'=>true,
																		'enableInfiniteScrolling'=>true,
																		'placeholderCommentForm'=>'Rédige ton post ici',
																		'placeholderTitleForm' => 'Donne un titre à ton post',
																		'placeholderReplyForm' => 'Laisser un ptit mot...'
																		),
																	)
											);
						}
						elseif(is_numeric($post_id)){


							$this->request('comments','show',array(
																	array(
																		'context'=>'comment',
																		'context_id'=>$post_id,
																		'allowTitle'=>true,
																		'allowComment'=>$allowComment,
																		'allowReply'=>true,
																		'displayRenderButtons'=>false,
																		'enablePreview'=>true,
																		'enableInfiniteScrolling'=>false,
																		'placeholderCommentForm'=>'Rédige ton post ici',
																		'placeholderTitleForm' => 'Donne un titre à ton post',
																		'placeholderReplyForm' => 'Laisser un ptit mot...'
																		),
																	)
											);
						}

			 	?>

				</div>
			</div>

			<div class="col_small">

			
					
			</div>

			<div class="clearfix"></div>
		</div>
	</div>
</div>

<script type="text/javascript">


        CKEDITOR.replace( 'content', { filebrowserBrowseUrl: '/js/ckeditor_filemanager/index.html'});
        

</script>