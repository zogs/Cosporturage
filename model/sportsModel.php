<?php 
class SportsModel extends Model{

	public $validates = array(
		'edit' => array(
			'name' => array(
				'rule'    => 'notEmpty',
				'message' => 'Vous devez préciser un nom'
				),
			'slug' => array(
				'rule'=>'notEmpty',
				'message' => 'Vous devez préciser un slug',
				),
			'lang'=> array(
				'rule'=>'notEmpty',
				'message'=> 'Vous devze préciser une langue'
				),
			),
		'delete'=> array(
			'sport_id'=>array(
				'rule'=>'isNumeric',
				'message'=>'Sport_id doit etre un entier',
				)
			),	
	);

	public function saveSport($data){

		$s = new stdClass();
		$s->slug = $data->slug;
		$s->table = 'sports';
		if(!empty($data->sport_id)){
			$s->sport_id = $data->sport_id;
			$s->key = 'sport_id';
		}
		$sportId = $this->save($s);

		$check = $this->findFirst(array('table'=>'sports_i18n','conditions'=>array('lang'=>$data->lang,'sport_id'=>$sportId)));

		$s = new stdClass();
		$s->table = 'sports_i18n';
		$s->lang = $data->lang;
		$s->sport_id = $sportId;
		$s->slug = $data->slug;
		$s->name = $data->name;
		$s->action = $data->action;
		if(!empty($check)){
			$s->id = $check->id;
			$s->key = 'id';
		}

		$this->save($s);

		return $sportId;
	}

	public function deleteSport($data){

		$sql = 'DELETE FROM sports WHERE sport_id=:id';
		$val = array(':id'=>$data->sport_id);

		$this->query($sql,$val);

		$sql = 'DELETE FROM sports_i18n WHERE sport_id=:id';
		$val = array(':id'=>$data->sport_id);

		$this->query($sql,$val);

		return true;

	}

	public function joinSports($events,$lang){

		foreach ($events as $event) {
			
			$event = $this->joinSport($event,$lang);					
		}
		return $events;
	}

	public function joinSport($event,$lang){

		if(empty($event->sport)) return $event->sport='';

		$tab = array();
		$sql = "SELECT S.sport_id, S.slug, I.name, I.action, I.object , I.lang
				FROM sports as S
				LEFT JOIN sports_i18n as I ON I.sport_id=S.sport_id
				WHERE S.slug=:slug AND I.lang=:lang";

		$tab[':slug'] = $event->sport;
		$tab[':lang'] = $lang;
		
		$res = $this->query($sql,$tab);

		if(empty($res)){
			$tab[':lang'] = Conf::$languageDefault;
			$res = $this->query($sql,$tab);
		}		
	
		if(isset($res[0])){
			$event->sport = $res[0];
			return $event;			
		}

		return false;
		

	}

	public function findSport($params = array() ){
		
		return $this->findFirst(array('table'=>'sports_i18n','conditions'=>$params));
	}

	public function findSportById($id){

		return $this->findSport(array('sport_id'=>$id));
	}

	public function findSports($lang){
		$sql = "SELECT S.sport_id, S.slug, I.name, I.action, I.object, I.lang
				FROM sports as S
				LEFT JOIN sports_i18n as I on I.sport_id=S.sport_id
				WHERE I.lang=:lang
				ORDER BY I.name";
		$tab = array(':lang'=>$lang);
		$res = $this->query($sql,$tab);
		if(empty($res)){
			$tab[':lang'] = Conf::$languageDefault;
			$res = $this->query($sql,$tab);
		}
		
		return $res;
	}

	public function findSportsList($lang){

		$a = array();
		$sports = $this->findSports($lang);
		foreach ($sports as $sport) {
			$a[$sport->slug] = $sport->name;
		}
		return $a;
	}

	

} 
?>