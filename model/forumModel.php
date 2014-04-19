<?php
class ForumModel extends Model{

	public $tableCategories = 'forum_categories';
	public $tableTopics = 'forum_topics';
	public $tableReplies = 'forum_replies';

	public $validates = array(
		'editReply' => array(
			'user_id' => array(
				'rule'    => 'isNumeric',
				'message' => 'user id is not numeric'		
				),
			'topic_id' => array(
				'rule' => 'isNumeric',
				'message' => "topic id must be numeric"
				),
			'cat_id' => array(
				'rule' => 'isNumeric',
				'message' => "category id must be numeric"
				),
			'content' => array(
				'rule' => 'notEmpty',
				'message' => "Votre message n'est pas rempli"
				)
			)
		);
		


	public function findCategories($params = array()){

		return $this->find(array('table'=>$this->tableCategories,'conditions'=>$params));
	}

	public function findTopics($params = array()){

		return $this->find(array('table'=>$this->tableTopics,'conditions'=>$params));
	}

	public function findReplies($params = array()){

		return $this->find(array('table'=>$this->tableReplies,'conditions'=>$params));
	}

	public function getCategoryBySlug($slug){

		return $this->findFirst(array('table'=>$this->tableCategories,'conditions'=>array('slug'=>$slug)));
	}

	public function getCategoryById($id){

		return $this->findFirst(array('table'=>$this->tableCategories,'conditions'=>array('cat_id'=>$id)));
	}

	public function getTopicBySlug($slug){

		return $this->findFirst(array('table'=>$this->tableTopics,'conditions'=>array('slug'=>$slug)));
	}

	public function getTopicById($id){

		return $this->findFirst(array('table'=>$this->tableTopics,'conditions'=>array('topic_id'=>$id)));
	}

	public function incrementTopicCountReplies($tid){

		$data = new stdClass();
		$data->table = $this->tableTopics;
		$data->field = 'nbreplies';
		$data->key = 'topic_id';
		$data->id = $tid;

		if($this->increment($data)) return true;
		return false;
	}

	public function incrementCategoryCountReplies($cid){

		$data = new stdClass();
		$data->table = $this->tableCategories;
		$data->field = 'nbreplies';
		$sata->key = 'cat_id';
		$data->id = $cid;

		if($this->increment($data)) return true;
		return false;
	}

	public function incrementCategoryCountTopics($cid){

		$data = new stdClass();
		$data->table = $this->tableCategories;
		$data->field = 'nbTopics';
		$data->key = 'topic_id';
		$data->id = $cid;

		if($this->increment($data)) return true;
		return false;
	}

	public function saveReply($data, $id = null){

		$data->table = $this->tableReplies;
		$data->date = Date::MysqlNow();


		if(isset($id) && is_numeric($id)){
			$data->key='reply_id';
			$data->id=$id;
		}

		if($this->save($data)){

			return true;
		}
		return false;
	}

}
?>