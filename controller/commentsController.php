<?php 
/**
 * 

	Controller des commentaires

 */
 class CommentsController extends Controller
 {
 	//Require parameters
 	public $layout = 'none';
 	public $table = 'comments';
 	public $context = 'none';
 	public $context_id = 0;
 	
 	//Options parameters
 	public $commentsPerPage = 6;
 	public $repliesDisplayPerComment = 3;
 	public $allowComment = true;
 	public $allowReply = true;
 	public $allowVoting = false;
 	public $allowTitle = false; 
 	public $displayReply = true;
 	public $enablePreview = true;
 	public $enableInfiniteScrolling = false;
 	public $levelFormReplyToDisplay = 1;
 	public $displayRenderButtons = false;

 	//Default text value
 	public $placeholderTitleForm = "Ecrire un titre";
 	public $placeholderCommentForm = "Laisser un commentaire";
 	public $placeholderNeedLog = "Veuillez vous connecter pour commenter...";
 	public $placeholderReplyForm = "Ecrire un commentaire...";
 	public $linkReply = "Répondre";


 	/*=======================================
 	Show the whole comment system
 	========================================*/
 	public function show( $params = array()){

 		$this->loadModel('Comments');
 		
 		//Override property config
 		$this->overrideConfig($params);

 		if(!isset($this->context)) throw new Exception("Context is require", 1);
 		if(!isset($this->context_id)) throw new Exception("Context_id is require", 1);
 		
 		$d['context'] = $this->context;
 		$d['context_id'] = $this->context_id;
 		$d['totalComments'] = $this->Comments->totalComments($this->context,$this->context_id);

 		$this->set($d);
 		$this->view = 'comments/show';
 		$this->render();

 	}

 	private function overrideConfig( $config ){ 		

 		if(empty($config)) return;

 		foreach ($config as $key => $value) {			
 			if(isset($this->$key)) {
 				$this->$key = $value;
 				$this->config[$key] = $value;
 			}

 		}

 	}

 	/*========================================
 	List all the comment depending of the context
 	@param $context (manif, group user...)
 	@param $context_id int
 	=========================================*/
 	public function index($context, $context_id, $comment_id = null) {

		$context         = (strlen($context)<=10)? $context : exit('wrong url context parameter');
		$context_id      = (is_numeric($context_id))? $context_id : exit('wrong url id parameter');
		$comment_id      = (isset($comment_id) && is_numeric($comment_id))? $comment_id : null;
		
		$d['context']    = $context;
		$d['context_id'] = $context_id;


 		$this->loadModel('Comments');
 		$this->view = 'comments/index';
				
 		$config = $this->request->get('config');
 		$this->overrideConfig(json_decode($config));

		$params = array(	
									
			"context"    =>$context,
			"context_id" =>$context_id,
			"comment_id" =>$comment_id,
			'limit'      =>$this->commentsPerPage,
			"lang"       =>$this->request->get('lang')
			);
		
		if(isset($this->request->get)){

			$params = array_merge( get_object_vars($this->request->get) ,$params);					
		}
		
		
		if($context=='events' ||$context=='group'){

			//$d['coms']     = $this->Comments->findComments($params);
			$d['coms']  = $this->Comments->findCommentsWithoutJOIN($params);		
				
		}
		elseif($context=='blog'){

			$d['coms'] = $this->Comments->findCommentsWithoutJOIN( $params );

		}
		elseif($context=='comment'){


			$com_id          = $context_id;
			$com             = $this->Comments->getComments($com_id);			
			$d['context']    = $com[0]->context;
			$d['context_id'] = $com[0]->context_id;
			$d['coms']       = $com;


		}
		elseif($context=='user'){

			//$d['coms'] = $this->Comments->threadUser($params);
						
			$d['coms']       = $this->Comments->findCommentsWithoutJOIN($params);	

		}

		$d['total'] = $this->Comments->totalComments($context,$context_id);

		$this->set($d);

		$this->render();

 	}


 	/*====================================
	Show a unique comment
	@param $comment_id int
 	=====================================*/
 	public function view($comment_id ){

		$this->layout = 'default';
		$this->loadModel('Comments');
		$this->view = 'comments/view';
		
		$com = $this->Comments->getComments($comment_id);
		$com = $com[0];

		if(empty($com)) $this->e404('Cette page n\'existe pas');

		if(isset($com->context) && $com->context != ''){

			$context = $this->findFirst(array('table'=>$com->context,'',array('id'=>$com->context_id)));
			$context_name = $context->title;
			$context_link = Router::url('events/view/'.$context->id.'/'.$context->slug);
		}
			
		$d['context_name'] = $context_name;	
		$d['context_link'] = $context_link;
		$d['comment_id'] = $comment_id;

 		$this->set($d);

		
 	}


 	/*========================================
 	Check for new comments
 	@param $context_id
 	$param $context
 	@param $min_id ID to check uppon
 	========================================*/
 	public function tcheck($context,$context_id,$min_id){

 		$this->loadModel('Comments');
 		$this->layout = 'none';
 		$this->view = 'json';

 		if(!is_numeric($context_id) || !is_numeric($min_id)) throw new Exeption("tcheck() attribute are not numeric");

 		$conditions = array('context'=>$context,'context_id'=>$context_id,'reply_to'=>0);

 		$d['count'] = $this->Comments->countNewEntryById($conditions,$min_id);

 		$this->set($d);

 	}



 
 	/*================================
 	Add a new comment
 	================================*/
 	public function add(){

		$this->loadModel('Comments');
 		$this->layout = 'none';

 		$d = array();
 		
 		if($com = $this->request->post()){

	 		//if there is a user logged in
	 		if($this->session->user()->isLog()){

	 			$com->user_id = $this->session->user()->getID();
	 			$com->lang = $this->getLang();

	 			//encode to prevent XSS for user != admin
	 			if(!$this->session->user()->isAdmin())
	 				$com->content = String::htmlEncode($com->content);


	 			if($id = $this->Comments->saveComment($com)){


	 				if($com->context=='events'){

	 					$event = new EventsController();
	 					$event->sendMailNewComment($com->context_id,$id);
	 				}
	 				


	 				$com = $this->Comments->getComment($id);
	 				if($com->isEmpty()) $d['fail'] = 'The comment is empty';
	 				$d['com'] = $com;
	 			}
	 			else {
	 				$d['fail'] = 'Error while saving comment ( add:commentsModel)'; 				
	 			}			
	 		}
	 		else {
	 			$d['fail'] = "You should log in first...";
	 		} 		 		
 		}




 		$this->set($d);
 	}


 	public function reply(){

 		$this->loadModel('Comments');
 		$this->layout = 'none';

 		if($this->request->post('content')!=''){

 			$com = $this->request->post();

	 		if(empty($com->reply_to) || !is_numeric($com->reply_to)) throw new zException("Reply_to is not defined", 1);
	 		

 			if($this->session->user()->isLog()){

	 			//On rajoute les params nécessaires
	 			$com->user_id = $this->session->user()->getID();
	 			$com->lang = $this->getLang();	 			

	 			if($id = $this->Comments->saveComment($com)){

	 				if($com->context=='events'){
	 					$event = new EventsController();
	 					$event->sendMailNewReply($com->reply_to,$id);
 					}

	 				$com = $this->Comments->getComment($com->reply_to);
	 				if($com->isEmpty()) $d['fail'] = 'the comment is empty';
	 				$d['com']  = $com;
	 			
	 			} else 
	 				$d['fail'] = 'Error while saving comment (reply:commentsModel)';	 			
	 						
	 		} else 
	 			$d['fail'] = 'You should log in first...';	 		

	 	} else 
	 		$d['fail'] = 'Comment is empty...';	 		
	 	
 	
	 	$this->set($d);
 	}

 	public function vote($id){

 		$this->loadModel('Comments');
 		$this->layout = 'none'; 
 		$this->view ='json';		

 		if(is_numeric($id)){

	 		if($this->session->user()->isLog()){

	 			$user_id = $this->session->user()->getID();

	 			if(!$this->Comments->alreadyVoted($id,$user_id)){

		 			$note = $this->Comments->increment(array(
							"table" =>"manif_comment",
							"field" =>"note",
							"id"    =>$id
		 				));
		 			$this->Comments->haveVoted(array(
							"user_id"    =>$user_id,
							"comment_id" =>$id
		 				));

		 			if(is_numeric($note->note)) $d['note'] = $note->note;
		 			else $d['erreur'] = 'Note not exist for this comment';

		 			
	 			}
	 			else $d['erreur'] = "You have already voted for this comment";
	 		}

 		}
 		else $d['erreur'] = 'Error id is not numeric';


 		$this->set($d);
 	}


 	public function preview(){

 		$this->layout = 'none';
 		$vars = array();


		if($this->request->get() && $this->enablePreview){

			if($this->request->get('url')) {

				//get url
				$url = trim($this->request->get('url'));				
				$url = urldecode($url);								
								
				//Same regex as in the javascript
				$pattern = "/\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:(?:[^\s()<>.]+[.]?)+|\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\))+(?:\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";				//$pattern = '/^http\:\/\/[a-zA-Z0-9\-\.\_]+\.[a-zA-Z]{2,4}(\/\S*)?$/'; //old pattern
				//if dont match regex OR have no '.' OR start with '.'
				if(!preg_match($pattern,$url) || strpos($url,'.')===0 || strpos($url,'.')=='') exit('not url');

				//parse url and get domain & extension
				$purl = String::parse_url($url);
				$domain = $purl['domain'];
				$extension = $purl['extension'];

				//default params
				$image_extension = array('jpg','jpeg','gif','png');
				$default_thumbnail = 'http://localhost/ypp/img/sign.png';

				//If the url ends with an image extension
				if(!empty($purl['path']) && in_array(substr($purl['path'],strripos($purl['path'],'.')+1),$image_extension)){

					$type             = 'img';
					$title            = $url;
					$description      = 'Image from '.$domain;
					$thumbnails       = array();
					$thumbnails[]     = $url;
					$thumbnails_first = $url;
					$media = $url;

				
				}
				elseif($domain =='youtube') {

							$video_id                      = getYTid($url);
							$type                          = 'video';


							$request_Youtube_API = 'http://gdata.youtube.com/feeds/api/videos/'.$video_id.'?v=2&alt=json';

							if($json = file_get_contents_curl($request_Youtube_API)){

								$json = json_decode($json,TRUE);
								
								$video        = $json['entry'];
								$title        = $video['title']['$t'];								
								$description  = $video['media$group']['media$description']['$t'];								
								$player_url   = $video['media$group']['media$player']['url'];
								$ytthumbnails = $video['media$group']['media$thumbnail'];
								$thumbnails   = array();
								foreach ($ytthumbnails as $thumbnail) {
									$thumbnails[] = $thumbnail['url'];
								}
								$thumbnails_first = $thumbnails[0];

								$media = '<iframe id="ytplayer" type="text/html" width="380" height="285"
										  src="http://www.youtube.com/embed/'.$video_id.'"
										  frameborder="0"/>';
								$media = urlencode($media);
								
							}
							else
								$type = '404';
							

							// require_once 'Zend/Loader.php';
							
							// Zend_Loader::loadClass('Zend_Gdata_YouTube');
							
							// $yt                            = new Zend_Gdata_YouTube();
							// $yt->setMajorProtocolVersion(2);
							// $videoEntry                    = $yt->getVideoEntry($video_id);
							// $title                         = $videoEntry->getVideoTitle();
							// $description                   = $videoEntry->getVideoDescription();					
							// $ytthumbnails                  = $videoEntry->getVideoThumbnails();
							// $player_url                    = $videoEntry->getFlashPlayerUrl();
							// $media = '<object width="400" height="225">
							// 				  <param name="movie" value="'.$player_url.'&autoplay=1"></param>
							// 				  <param name="allowFullScreen" value="true"></param>
							// 				  <param name="allowScriptAccess" value="always"></param>
							// 				  <embed src="'.$player_url.'&autoplay=1" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="400" height="225"></embed>
							// 				</object>';
							// $media = urlencode($media);

							// $thumbnails = array();
							// foreach ($ytthumbnails as $key => $value) {														
							// 	$thumbnails[]                  = $value['url'];
							// }			
							// $thumbnails_first              = $thumbnails[0];							
							
				}
				elseif( $domain == 'dailymotion') {

							$request_Dailymotion_API = "http://www.dailymotion.com/services/oembed?format=json&url=".$url;

							if($json = file_get_contents_curl($request_Dailymotion_API)){
					
								$json = json_decode($json);

								$type             = 'video';
								$title            = $json->title;
								$description      = $json->title.' - <a href="'.$json->author_url.'">'.$json->author_name.'</a>';
								$thumbnails       = array();
								$thumbnails[]     = $json->thumbnail_url;
								$thumbnails_first = $thumbnails[0];
								$media     = urlencode($json->html);
								
							}
							else $type = '404';										

				}
				elseif( $domain == 'vimeo') {
							
							$video_id         = getVIMEOid($url);
							$request_VIMEO_API = 'http://vimeo.com/api/v2/video/'.$video_id.'.json';

							if($json = file_get_contents_curl($request_VIMEO_API)){
							
								$json = json_decode($json);
								$json = $json[0];

								$type             = 'video';
								$title            = $json->title;
								$description      = $json->description;
								$thumbnails       = array();
								$thumbnails[]     = $json->thumbnail_small;
								$thumbnails[]     = $json->thumbnail_medium;
								$thumbnails[]     = $json->thumbnail_large;
								$thumbnails_first = $thumbnails[0];
								$media = urlencode("<iframe src='http://player.vimeo.com/video/".$video_id."?title=0&byline=0&autoplay=1' width='400' height='270' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>");
								//$contentURL       = "http://vimeo.com/moogaloop.swf?clip_id=".$video_id."&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=ffffff&amp;fullscreen=1&amp;autoplay=0&amp;loop=0";
							}
							else $type = '404';

				}
				else {

							if($html = file_get_contents_curl($url)){

							
								if($html){

									$type = 'link';
									$media = $url;
									libxml_use_internal_errors(true); //useful to silent html error
									$doc = new DOMDocument();
									@$doc->loadHTML($html);
									$nodes      = $doc->getElementsByTagName('title');
									$title      = $nodes->item(0)->nodeValue;
									$metas      = $doc->getElementsByTagName('meta');
	

									for ($i = 0; $i < $metas->length; $i++)
									{
									    $meta = $metas->item($i);			    
									    if($meta->getAttribute('name') == 'description')
									        $description = $meta->getAttribute('content');
									}

									$image_regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
									preg_match_all($image_regex, $html, $images, PREG_PATTERN_ORDER);
									$images = $images[1];
									if(!empty($images)){

										foreach ($images as $key => $value) {
											
											//we get only images with absolute url
											if(strpos($value,'http://')===0 || strpos($value,'https://')===0 || strpos($value,'www')===0 ){
												$thumbnails[] = $value;											
											}
											
											else {	
												

												$thumbnails[] = $purl['all'].$value;											
											}
											
										}
										
										if(!empty($thumbnails)){
											$thumbnails_first = $thumbnails[0];
										}
									}
									else {

										$thumbnails = array($default_thumbnail);
										$thumbnails_first = $default_thumbnail;
									}
									
								}
								else $type = '404';	
							}
							else $type = '404';						
					}

			}
			

			$vars['type']             = (isset($type))? $type : '404';
			$vars['url']              = (isset($url))? $url : '';
			$vars['title']            = (isset($title))? $title : '';
			$vars['description']      = (isset($description))? $description : '';
			$vars['thumbnails']       = (isset($thumbnails))? $thumbnails : '';
			$vars['thumbnails_first'] = (isset($thumbnails_first))? $thumbnails_first : '';			
			$vars['media']       = (isset($media))? $media : '';

			//debug($vars);

			$this->set($vars);
		}

		$this->set(array('type'=>'404'));

 	}






 } ?>