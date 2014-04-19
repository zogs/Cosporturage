<?php
class ForumController extends Controller{


	public function board($type = null, $slug = null){

		$d['type'] = $type;
		$d['slug'] = $slug;
		$this->set($d);


	}


	public function index($type = null, $slug = null){

		$this->loadModel('Forum');
		$this->view = 'forum/index';
		$this->layout = 'none';

		if($type=="category" && !empty($slug)) $this->indexTopics($slug);
		elseif($type=="category" && !isset($slug)) $this->indexCategories();
		elseif($type=="topic" && !empty($slug)) $this->indexReplies($slug);
		elseif($type=="topic" && !isset($slug)) $this->indexTopics();
		else $this->indexCategories();

		$this->render();
	}

	public function indexCategories(){

		$catz = $this->Forum->findCategories();
		
		$this->set('categories',$catz);
		$this->set('display','category');

	}

	public function indexTopics( $cslug = null ){

		$params = array();
		if(!empty($cslug)){

			$cat = $this->Forum->getCategoryBySlug($cslug);
			$params['cat_id'] = $cat->cat_id;	
		} 

		$topics = $this->Forum->findTopics($params);
		$topics = $this->Forum->joinUser($topics);

		$this->set('topics',$topics);
		$this->set('display','topic');

	}

	public function indexReplies( $tslug = null ){

		$params = array();
		if(!empty($tslug)){

			$topic = $this->Forum->getTopicBySlug($tslug);			
			$params['topic_id'] = $topic->topic_id;
			$cat = $this->Forum->getCategoryById($topic->cat_id);
		} 

		$replies = $this->Forum->findReplies($params);
		$replies = $this->Forum->joinUser($replies);

		$d['replies'] = $replies;
		$d['topic'] = $topic;
		$d['display'] = 'reply';	
		$d['cat'] = $cat;
		$this->set($d);	
		
	}

	public function edit( $type = null, $id = null){

		if($type=='category') $this->editCategory($id);
		elseif($type=='topic') $this->editTopic($id);
		elseif($type=='reply') $this->editReply($id);
		else $this->e404();
	}

	public function editCategory(){

	}

	public function editTopic(){

	}

	public function editReply($id= null){

		$this->loadModel('Forum');

		if($data = $this->request->post()){

			if($data = $this->Forum->validates($data,'editReply')){

				if($id = $this->Forum->saveReply($data)){

					$this->Forum->incrementTopicCountReplies($data->topic_id);
					$this->Forum->incrementCategoryCountReplies($data->cat_id);

					$this->session->setFlash('Message sauvegardé');
				}
				else{
					$this->session->setFlash('Error savinf msg','error');
				}

			}

			$topic = $this->Forum->getTopicById($data->topic_id);

			$this->redirect('forum/board/topic/'.$topic->slug);
		}

		$this->e404('Pas de donnée à sauvegarder','Oups');

	}

	
}
?>