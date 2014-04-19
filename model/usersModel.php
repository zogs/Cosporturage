<?php 
class UsersModel extends Model{

	public $primaryKey = 'user_id';
	public $table = 'users';

	public $validates = array(
		'register' => array(
			'login' => array(
				'rules'=>array(
						array(
							'rule'    => 'notEmpty',
							'message' => 'Vous devez choisir un pseudo'		
						),
						array(
							'rule'=>'regex',
							'regex'=>'^[a-zA-Z0-9 \.\-_]+$',
							'message'=>"Pas d'accents ou de caractères interdits..."
						)
					)
				),				
			'email' => array(
				'rules'=>array(
							array(
								'rule' => '[_a-zA-Z0-9-+]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,4})',
								'message' => "L'adresse email n'est pas valide"),
							array(
								'rule' => 'notEmpty',
								'message' => 'Vous devez remplir une adresse email')
							)
				),
			'password' => array(
				'rule' => '.{5,20}',
				'message' => "Votre mot de passe doit etre entre 5 et 20 caracteres"
				),
			'confirm' => array(
				'rule' => 'confirmPassword',
				'message' => "Vos mots de passe ne sont pas identiques"
				),
			'prenom' => array(
				'optional'=> 'optional',
				),
			'nom' => array(
				'optional'=> 'optional',
				),
			'day' => array(
				'rules'=> array(
							array(
								'rule'=> '[0-9]{1,2}',
								'message'=> "Votre jour de naissance n'est pas correct"
							),							
						)
				),
			'month' => array(
				'rules'=> array(
							array(
								'rule'=> '[0-9]{1,2}',
								'message'=> "Votre mois de naissance n'est pas correct"
							),							
						)
				),
			'year' => array(
				'rules'=> array(
							array(
								'rule'=> '[0-9]{4}',
								'message'=> "Votre année de naissance n'est pas correct"
							),							
						)
				)
		),
		'account_info' => array(
			'login' => array(
				'rules'=> array(
							array(
								'rule' => 'notEmpty',
								'message' => 'Your login is empty'
									),
							array('rule' => '.{5,20}',
								'message' => 'Login between 5 and 20 caracters'
								)
							)
				),
			'email' => array(
				'rule' => '[_a-zA-Z0-9-+]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,4})',
				'message' => "L'adresse email n'est pas valide"
				)
		),
		'account_profil' => array(
			
		),
		'recovery_mdp' => array(
			'password' => array(
				'rule' => '.{5,20}',
				'message' => "Votre mot de passe doit etre entre 5 et 20 caracteres"
				),
			'confirm' => array(
				'rule' => 'confirmPassword',
				'message' => "Vos mots de passe ne sont pas identiques"
				)
		),
		'account_password' => array(
			'oldpassword' => array(
				'rule' => 'notEmpty',
				'message' => "Votre mot de passe doit contenir entre 5 et 20 caracteres"
				),
			'password' => array(
				'rule' => '.{5,20}',
				'message' => "Votre mot de passe doit etre entre 5 et 20 caracteres"
				),
			'confirm' => array(
				'rule' => 'confirmPassword',
				'message' => "Vos mots de passe ne sont pas identiques"
				)
		),
		'account_mailing'=>array(

		),
		'account_delete' => array(
			'password' => array(
				'rule' => 'notEmpty',
				'message' => "Enter your password"
				)
		),
		'account_avatar'=>array(
			'avatar'=>array(
				'rule'=> 'file',
				'params'=>array(
					'destination' => 'media/users/avatar',
					'extentions'=>array('png','gif','jpg','jpeg','JPG','bmp'),
					'extentions_error'=>'Your avatar is not an image file',
					'max_size'=>10000000, // 10 Mo
					'max_size_error'=>'Your image is too big',
					'ban_php_code'=>true
					)
			)
		),
	);

	public function __construct(){

		parent::__construct();
		
		//cache for cluster of wesporter location
		$this->cacheData = new Cache(Conf::getCachePath().'/statistics',60); //one day

	}
	public function saveUser($user,$user_id = null){

		//unset action value
		if(isset($user->action)) unset($user->action);
		//set user_id for updating
		if(isset($user_id)) $user->user_id = $user_id;

		//birthday
		if(!isset($user->birthdate)&&isset($user->day)&&isset($user->month)&&isset($user->year)){
			$user->birthdate = $user->year.'-'.$user->month.'-'.$user->day;
			unset($user->day);
			unset($user->month);
			unset($user->year);
		}

		//default avatar
		if(!isset($user->avatar)) $user->avatar = rand(1,10);

		//maybye there is nothing to save
		$tab = (array) $user;
		if( (count($tab)==0 ) || (count($tab)==1 && isset($tab['user_id'])))
			return true;	

		if($user_id = $this->save($user)){

			//create mailing 
			$this->saveUserSettingsMailing($user_id);

			//create statistics
			$this->saveUserStatistics($user_id);


			return $user_id;
		}
		else
			return false;

	}

	public function saveUserSettingsMailing($user_id,$settings = array()){

		$settings_exist = $this->findFirst(array('table'=>'users_settings_mailing','fields'=>'id','conditions'=>array('user_id'=>$user_id)));

		$settings = (object) $settings;
		$settings->table = 'users_settings_mailing';
		$settings->key = 'id';
		$settings->user_id = $user_id;
		if(!empty($settings_exist)){
			$settings->id = $settings_exist->id;
		}


		if($this->save($settings))
			return true;
		else
			return false;
	}

	public function saveUserStatistics($user_id,$array = array()){

		$stat_exist = $this->findFirst(array('table'=>'users_stat','fields'=>'id,account_updated','conditions'=>array('user_id'=>$user_id)));		

		$stat = new stdClass();		
		$stat->table = 'users_stat';
		$stat->key = 'id';
		$stat->user_id = $user_id;
		if(!empty($stat_exist)) {
			$stat->id = $stat_exist->id;
			$stat->account_updated = ($stat_exist->account_updated+1);
		}

		foreach ($array as $key=>$val) {
			$stat->$key = $val;
		}

		if($this->save($stat))
			return true;
		else
			return false;
	}

	public function saveUserAvatar($user_id){

 		//Les vérifications sont faites dans model/validates

 		$tmp = $_FILES['avatar'];
 		$ext = $extention = substr(strrchr($tmp['name'], '.'),1);
 
 		$newname = 'u'.$user_id.'.'.$ext;
 		$directory = 'media/user/avatar';
 		$destination = $directory.'/'.$newname;

 		
 		if(move_uploaded_file($tmp['tmp_name'], $destination)){
		
 			$user = new StdClass();
 			$user->user_id = $user_id;
 			$user->avatar = $destination;

 			$this->table = 'users';
 			if($this->save($user)){
 				return true;
 			}
 		}
 		else return false;

	}

	public function findUsers($req){

		$sql = 'SELECT ';
 		
 		if(isset($req['fields'])){
			
			$sql .= $this->sqlFields($req['fields']);
 		}
 		else $sql .= ' * ';


 		$sql .= " FROM ";

 		if(isset($req['table'])) $sql .= $req['table'];
 		else $sql .= $this->table;

 		$sql .= " WHERE ";


 		 if(!empty($req['conditions'])){ 			
 			
 			$sql .= $this->sqlConditions($req['conditions']);
 			
 		}

 		if(!empty($req['order'])){
 			if($req['order'] == 'random') $sql .= ' ORDER BY rand()';
 			else $sql .= ' ORDER BY '.$req['order'];
 		}

 		if(!empty($req['limit'])){
			$sql .= ' LIMIT '.$req['limit'];
 		}

 		  // debug($sql);
 		$results = $this->query($sql);
 		

 		if(empty($results)) return array(new User());

 		$users = array();
 		foreach ($results as $user) {
 					
 			$users[] = new User($user); 
 		}
 		return $users;
	}

	public function findFirstUser($req){

		return current($this->findUsers($req));
	}

	
	public function countTotalUsers(){

		$cacheName = 'totalUsers';
		if($cache = $this->cacheData->read($cacheName)) return $cache;
		$users = $this->findFirst(array('fields'=>"COUNT($this->primaryKey) as total"));
		$total = $users->total;
		$this->cacheData->write($cacheName,$total);

		return $total;
	}

	public function countTotalUsersByCity($city_id){

		$cachename = 'nbUserByCIty/'.$city_id;
		if($cache = $this->cacheData->read($cachename)) return $cache;
		$users = $this->findFirst(array('fields'=>"COUNT($this->primaryKey) as total",'conditions'=>array('city'=>$city_id)));
		$total = $users->total;
		$this->cacheData->write($cachename,$total);
		return $total;
	}

	public function findRegisteringFromDays($days = 0){

		$sql = "SELECT * FROM $this->table WHERE DATE(date_signin) >= DATE( DATE_SUB(NOW(),INTERVAL $days DAY))";
		$res = $this->query($sql);
		return $res;
	}

	public function countRegisteringFromDays($days = 0){

		$cacheName = 'countUsersFrom'.$days.'days';
		if($cache = $this->cacheData->read($cacheName)) return $cache;
		$users = $this->findRegisteringFromDays($days);
		$count = count($users);
		$this->cacheData->write($cacheName,$count);	
		return $count;
	}

	public function countMonthRegisteringForYear($year){

		$cacheName = 'countUsersPerMonthOf'.$year;
		if($cache = $this->cacheData->read($cacheName)) return unserialize($cache);
		$sql = "select month(date_signin) as month, count($this->primaryKey) as count
				from $this->table
				where year(date_signin) = $year
				group by month(date_signin)
				order by month(date_signin)";

		$res = $this->query($sql);
		$return = array();
		foreach ($res as $m) {
			$monthName = date("F", mktime(0, 0, 0, $m->month, 10));
			$return[$monthName] = $m->count;
		}
		$this->cacheData->write($cacheName,serialize($return));
		return $return;
	}
	// public function findUserThread($user_id){


	// 	$sql=" SELECT P.id,
	//                  'PROTEST' AS TYPE,
	//                  P.date,	                 
	//                  D.name as relatedName,
	//                  D.manif_id as relatedID,
	//                  D.slug     as relatedSlug,
	//                  I.logo     as relatedLogo

 //        		FROM manif_participation as P
 //        		LEFT JOIN manif_descr as D ON D.manif_id=P.manif_id AND D.lang='".$this->session->user()->getLang()."'	
 //        		LEFT JOIN manif_info as I ON I.manif_id=P.manif_id
 //           		WHERE P.user_id = $user_id
 //      		UNION
 //        --   	SELECT `id`,
	//        --           'COMMENT' AS TYPE,
	//        --           `date`
 //        --     	FROM `manif_comment`
	//        --     	WHERE user_id = $user_id AND reply_to=0
 //      		-- UNION
 //       		SELECT C.id,
 //                	'NEWS' AS TYPE,
 //                  	C.date,
 //                  	D.name as relatedName,
 //                  	D.manif_id as relatedID,
 //                  	D.slug     as relatedSlug,
 //                  	I.logo     as relatedLogo
 //              	FROM manif_comment AS C
 //              	LEFT JOIN manif_participation AS P ON P.user_id = $user_id
 //              	LEFT JOIN manif_descr as D ON D.manif_id=P.manif_id AND D.lang='".$this->session->user()->getLang()."'
 //              	LEFT JOIN manif_info as I ON I.manif_id=P.manif_id	
 //             	WHERE C.context_id = P.manif_id AND C.context='manif' AND C.type='news'
	// 		ORDER BY date DESC
	// 		";


	// 	$pre = $this->db->prepare($sql);
 // 		$pre->execute();
 // 		return $pre->fetchAll(PDO::FETCH_OBJ);

	// }

	
}


class User {


	public $user_id = 0;
	public $login = '';
	public $email = '';
	public $avatar  = 'img/LOGO.gif';
	public $account = 'visitor';
	public $role = 'visitor';
	public $city = 0;

	public function __construct( $fields = array() ){

		if(empty($fields)) return;
		
		foreach ($fields as $field => $value) {
			
			$this->$field = $value;
		}
	}

	public function getID(){

		return $this->user_id;
	}

	public function exist(){

		if($this->user_id==0) return false;
		return true;
	}

	public function getLogin(){

		if($this->account=='anonym') return 'anonym_'.$this->user_id;
		else return $this->login;
	}

	public function getEmail(){
		return $this->email;
	}

	public function getAvatar(){

		if(isset($this->avatar)&&!empty($this->avatar)&&file_exists(WEBROOT.DS.$this->avatar)) return Router::webroot($this->avatar);
		elseif(strpos($this->avatar,'http')===0) return $this->avatar;
		elseif(is_numeric($this->avatar)) return Router::webroot('img/avatars/default'.$this->avatar.'.gif');
		else return Router::webroot('img/avatars/default1.gif');
	}

	public function getLink(){

		return Router::url('users/view/'.$this->getID().'/'.$this->getLogin());
	}

	public function getBonhom(){

		return $this->bonhom;
	}

	public function getRole(){
		return $this->role;
	}

	public function getAccount(){
		if(!empty($this->account)) return $this->account;
		return 'public';
	}
	public function isPerso(){
		if(isset($this->account) && $this->account=='public') return true;
		return false;
	}
	public function isAsso(){
		if(isset($this->account) && $this->account=='asso') return true;
		return false;
	}
	public function isPro(){
		if(isset($this->account) && ($this->account=='pro' || $this->account=='bizness')) return true;
		return false;
	}

	public function isLog(){
		if($this->user_id!==0) return true;
		return false;
	}
	public function online(){
		if($this->user_id!==0) return true;
		return false;
	}


	public function setLang($lang){
		$this->lang = $lang;
	}

	public function getLang(){
		if(!empty($thus->lang)) return $lang;
		else return Conf::$languageDefault;
	}

	public function isAdmin(){

		if($this->role=='admin') return true;
		else return false;
	}

	public function getAge(){

		if(!empty($this->account)){
			if($this->account=='asso') return 'Association';
			if($this->account=='bizness') return 'Professionnel';
		}
		if(!empty($this->birthdate))
			return date('Y-m-d') - date($this->birthdate). ' ans';		
	}
	public function getBirthyear(){
		$d = explode('-',$this->birthdate);
		return $d[0];
	}
	public function getBirthMonth(){
		$d = explode('-',$this->birthdate);
		return $d[1];
	}
	public function getBirthDay(){
		$d = explode('-',$this->birthdate);
		return $d[2];
	}
	public function getBirthDate(){
		if(isset($this->birthdate)) return $this->birthdate;
	}
	public function getSexe(){		
		if(!empty($this->sexe)) return $this->sexe;
	}

	public function isFacebookUser(){
		if(!empty($this->facebook_id)) return true;
		return false;
	}
	public function getFacebookId(){
		if(!empty($this->facebook_id)) return $this->facebook_id;
		return false;
	}
	public function getFacebookToken(){
		if(!empty($this->facebook_token)) return $this->facebook_token;
		return false;
	}

}
 ?>