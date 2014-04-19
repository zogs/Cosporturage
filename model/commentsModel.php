<?php /**
* 
*/
class CommentsModel extends Model
{
	public $table = 'comments';	
	public $table_vote = 'comments_voted';
	
	public function findCommentsWithoutJOIN($req){

		//$timestart=microtime(true);

		foreach ($req as $k => $v) {
			
			$$k = $v;
		}

		$val = array();

		if(isset($order)){

        	if ($order == "datedesc" || $order == '' || $order == '0')
            	$order="date DESC";
	        elseif ($order == "dateasc")
	            $order="date ASC";
	        elseif ($order == "noteasc")
	            $order="note ASC";
	        elseif ($order == "notedesc")
            	$order="note DESC";
        }
        else {
        	$order = "date DESC";
        }

		if(!empty($limit) && is_numeric($limit) && !empty($page) && is_numeric($page)){

			if(!isset($page) || empty($page)) $page = 1;
				$limit = (($page-1)*$limit).','.$limit;
		}
		else $limit = 165131654;
       


        if (isset($rch) && $rch != "0") {
        	if( trim( $rch != "" ) ) {        		
        		$rch =" ( U.login LIKE '%:rch%' OR X.content LIKE '%:rch%' )";
        	}           
        }

        $user_id = $this->session->user()->getID();

		$sql = " SELECT C.*
				FROM $this->table as C
				
				WHERE ";
				if(isset($comment_id) && is_numeric($comment_id)) {
							$sql .= " C.id=$comment_id ";	
							
				} 
				elseif(isset($reply_to) && is_numeric($reply_to)){
							$sql .= " C.reply_to=$reply_to ";							
				}
				else {
							$sql .= " C.context=:context AND C.context_id=:context_id AND C.reply_to=0 ";
							$val['context'] = $context;
							$val['context_id'] = $context_id;
				}			
				if (isset($type) && $type != "all" && $type != "0" ){

							$sql .=' AND C.type=:type ';
							$val['type'] = $type;

				}
				if (!empty($start) && is_numeric($start)){

							$sql .=' AND C.id <= '.$start.' ';			
				}
				if( !empty($newest) && is_numeric($newest)){

							$sql .=' AND C.id > '.$newest.' ';
					
				}
				if(isset($lang) && !empty($lang)!=''){

							$sql .= ' AND lang=:lang ';
							$val['lang'] = $lang;
					
				}
				$sql.=" ORDER BY ".$order." LIMIT ".$limit." ";

			
		$res = $this->query($sql,$val);		
			
		//foreach comment make an object	
		$comments = array();
		foreach ($res as $com) {
			
			$comments[] = new Comment($com);

		}
		//foreach comment join replies if exist
		$comments = $this->joinReplies($comments);
		$comments = $this->joinUserData($comments);
 		//$timeend=microtime(true);
		//$time=$timeend-$timestart;
		//debug('temps d\'execution sans les JOIN:'.$time);

		return $comments;
	}



	/*
	Associe les réponses aux commentaires
	@param array/objet of comments
	@return array of comments
	**/
	public function joinReplies($comments){

		//put in a array if its not an array
		if(is_object($comments)) $comments = array($comments);
		
		//loop the array of comments
		foreach ($comments as $comment) {
			
			if($comment->haveReplies()){

				//this will find all replies in Comment object
				$replies = $this->findCommentsWithoutJOIN(array('reply_to'=>$comment->id,'order'=>'dateasc'));								
				$comment->replies = $replies;
							
			}			
		}

		return $comments;
	}


	public function getComments($comments_id){
	
		$array = array();
		if(is_array($comments_id)){

			foreach($comments_id as $comment_id){

				$res = $this->findCommentsWithoutJOIN(array('comment_id'=>$comment_id));
				$array[] = $res[0];
			}					
		}
		elseif(is_numeric($comments_id)){

			$res = $this->findCommentsWithoutJOIN(array('comment_id'=>$comments_id));
			$array[] = $res[0];					
		}

		return $array;

	}

	public function getComment($comment_id){

		$res = $this->findCommentsWithoutJOIN(array('comment_id'=>$comment_id));
		return $res[0];
	}

	public function saveComment($com){

		$c = new stdClass();
		$c = $com;
			
		$c->content = nl2br($c->content);

		if(!empty($c->media) && !empty($c->media_url)){
			$c->content = str_replace($c->media_url,'',$c->content);
			$c->media = $c->media;
			$c->media = html_entity_decode($c->media,ENT_NOQUOTES|'ENT_XHTML', 'UTF-8' );
		}

		if(!empty($title)){
			$c->title = $c->title;
			$c->type = 'news';
		}

		if(!empty($c->reply_to) && is_numeric($c->reply_to)){

			$this->increment(array('field'=>'replies','id'=>$c->reply_to));
		}

		if($id = $this->save($c)){

			return $id;
		}
		else
			return false;


	}

	public function joinUserData($data){
		
		$data = $this->joinUser($data);
		$data = $this->JOIN($this->table_vote,'id as voted',array('comment_id'=>':id','user_id'=>$this->session->user()->getID()),$data);		

		return $data;
	}

	public function totalComments($context,$context_id){

		$sql = "SELECT COUNT(id) as count FROM $this->table WHERE context='$context' AND context_id=$context_id AND reply_to=0";
		$pre = $this->db->prepare($sql);
		$pre->execute();
		return $pre->fetchColumn();
	}

	public function userTotalComments($user_id){

		$sql = 'SELECT COUNT(id) as count FROM $this->table WHERE user_id='.$user_id;
		$res = $this->db->prepare($sql);
		$res->execute();
		return $res->fetchColumn();
	}

	public function userTotalEventsComments($user_id,$event_id){

		$sql = 'SELECT COUNT(id) as count FROM $this->table WHERE user_id='.$user_id.' AND context="event" AND context_id='.$event_id;
		$res = $this->db->prepare($sql);
		$res->execute();
		return $res->fetchColumn();
	}

	public function alreadyVoted($id,$user_id){

		$sql = "SELECT COUNT(id) FROM $this->table_vote WHERE comment_id=$id AND user_id=$user_id";
		$pre = $this->db->prepare($sql);
		$pre->execute();
		return (bool)$pre->fetchColumn();
	}

	public function haveVoted($data){

 		foreach ($data as $k => $v) {
	 			$tab[":$k"] = $v; //tableau des valeurs pour la fonction execute de PDO	 		
 		}
		$sql = "INSERT INTO $this->table_vote SET user_id = :user_id , comment_id = :comment_id";
		$pre = $this->db->prepare($sql);
		$pre->execute($tab);			
		return true;
	}

	public function findUserComments($req){

		$sql = 'SELECT ';
 		if(isset($req['fields']))
			if(is_array($req['fields']))
 				$sql .= implode(', ',$req['fields']); 			
 			else
 				$sql .= $req['fields']; 			 		
 		else
 			$sql .= 'C.*, U.*'; 		

		$sql .= " FROM $this->table as C
					JOIN users as U ON U.user_id=C.user_id
				  	WHERE ";

		if(isset($req['conditions'])){ 			
 			if(!is_array($req['conditions']))
 				$sql .= $req['conditions']; 				
 			else {
 				$cond = array();
	 			foreach ($req['conditions'] as $k => $v) {
	 				if(!is_numeric($v)){ 
	 					$v = '"'.mysql_escape_string($v).'"';	 					
	 				}
	 				$cond[] = "$k=$v";	 			
	 			}
	 			$sql .= implode(' AND ',$cond);
 			} 			
 		}

		if(isset($req['order'])){
 			if($req['order'] == 'random') $sql .= ' ORDER BY rand()';
 			else $sql .= ' ORDER BY '.$req['order'];
 		}

 		if(isset($req['limit'])){
			$sql .= ' LIMIT '.$req['limit'];
 		}

 		// debug($sql);
		$res = $this->db->prepare($sql);
		$res->execute();
		return $res->fetchAll(PDO::FETCH_OBJ);
	}



	// public function threadUser($params){

	// 	$thread = $this->getThreadUser($params); //get the order list
	// 	$thread = $this->fillThreadUser($thread);	//fill the list

	// 	return $thread;
	// }

	// public function getThreadUser($params){

	// 	//limit
	// 	if(isset( $params['limit']) && !empty($params['limit'])){
	// 		if(!isset($params['page'])) $params['page'] = 1;
	// 		$limit = (($params['page']-1)*$params['limit']).','.$params['limit'];
	// 	}
	// 	else $limit = 144;

	// 	//request
	// 	$sql = "SELECT 
	// 				'joinProtest' as thread,
	// 				id as id,
	// 				date as date
	// 			FROM 
	// 				manif_participation
	// 			WHERE 
	// 				user_id = ".$params['context_id']."
	// 			UNION
	// 			SELECT 
	// 				'manifNews' as thread,
	// 				C.id as id,
	// 				C.date as date
	// 			FROM
	// 				comments as C
	// 				LEFT JOIN manif_participation AS P ON P.user_id = ".$params['context_id']."
	// 			WHERE
	// 				C.context = 'manif' AND C.type='news' AND C.context_id = P.manif_id 
	// 			ORDER BY date DESC
	// 			LIMIT ".$limit." 

	// 			";

	// 	$pre = $this->db->prepare($sql);
	// 	$pre->execute();
	// 	$res = $pre->fetchAll(PDO::FETCH_OBJ);

	// 	return $res;

	// }

	// public function fillThreadUser($list){

	// 	$array = array();

	// 	foreach ($list as $thread) {
			
	// 		if($thread->thread == 'joinProtest'){
				
	// 			$protester = $this->session->controller->Manifs->findProtesters(array(
	// 																				'fields'=>array('U.user_id','U.login','P.manif_id','P.date','P.id'),
	// 																				'conditions'=>array('P.id'=>$thread->id)
	// 																			));

	// 			$sql = "SELECT manif_id, nommanif, slug, logo FROM manif_descr WHERE manif_id=".$protester->manif_id;
	// 			$pre = $this->db->prepare($sql);
	// 			$pre->execute();
	// 			$manif = $pre->fetch(PDO::FETCH_OBJ);
				
	// 			$protester = (object) array_merge((array) $protester, (array) $manif);
	// 			$protester->thread = $thread->thread;
	// 			$array[] = $protester;

	// 		}
	// 		elseif($thread->thread == 'manifNews'){

	// 			$com = $this->getComments($thread->id);
	// 			$com = $this->joinUserData($com);
	// 			$com = $com[0];
	// 			$com->thread = $thread->thread;
	// 			$array[] = $com;

	// 			if($com->replies > 0){

	// 				$replies = $this->findReplies($com);
	// 				$replies = $this->joinUserData($replies);
	// 				$array[] = $replies;

	// 			}
	// 		}
	// 	}


	// 	return $array;
	// }





} 


class Comment {

	public function __construct($params){

		foreach ($params as $key => $param) {
			
			$this->$key = $param;
		}
	}

	public function exist(){

		if(isset($this->id)) return true;
		return false;
	}

	public function haveReplies(){

		return (!empty($this->replies))? true : false;
	}

	public function userHaveVoted(){

		return (isset($this->voted)&&is_numeric($this->voted))? true : false;
	}

	public function isEmpty(){
		if(empty($this->content)) return true;
		return false;
	}

	public function isModerate( $msg = false){

		$bool = false;
		if($this->note<-10) {
			$bool = true;
			$msg = 'Ce commentaire a reçu trop de vote négatif...';
		}
		if($this->valid==0) {
			$bool = true;			
			$msg = 'Ce commentaire a été modéré.';
		}
		if($this->online==0) {
			$bool = true;
			$msg = 'Ce commentaire a été modéré et va être supprimé.';
		}

		if($msg == false)
			return $bool;
		else 
			return $msg;
	}



}?>