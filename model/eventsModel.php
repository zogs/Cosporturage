<?php 
class EventsModel extends Model{

	public $validates = array(
		'title' => array(
			'rule'    => 'notEmpty',
			'message' => 'Vous devez préciser un titre'		
		),
		'sport' => array(
			"rules"=>array(
				array(
					'rule'=>'notEmpty',
					'message'=>"Un sport doit être renseigné"
				),
				array(
					'rule'=>'notNull',
					'message'=>"Un sport doit être renseigné"
				)
			)
		),
		'address' => array(
			"rules"=>array(
				array(
					'rule'=>'notEmpty',
					'message'=>"Une adresse doit être renseigné"
				)
			)
		),
		'cityName' => array(
			"rules"=>array(
				array(
					'rule'=>'notEmpty',
					'message'=>"Une ville doit être renseigné"
				),
				array('rule'=>'notNull','message'=>"Une ville doit être renseigné1")
			)
		),
		'date' => array(			
			"rules"=>array(	
				'optional'=> 'optional',			
				array(
					'rule'=>'datefutur',
					'message'=>'La date doit être dans le futur')
			)
		),
		'startdate' => array(			
			"rules"=>array(	
				'optional'=> 'optional',			
				array(
					'rule'=>'datefutur',
					'message'=>'La date de début doit être dans le futur')
			)
		),
		'enddate' => array(			
			"rules"=>array(
				'optional'=> 'optional',				
				array(
					'rule'=>'datefutur',
					'message'=>'La date de fin doit être dans le futur')
			)
		),
		'hours' => array(
			"rules"=>array(
				array(
					'rule'=>'notEmpty',
					'message'=>"Un horaire doit être renseigné"
				),
				array(
					'rule'=>'regex',
					'regex'=>'[0-9]{2}',
					'message'=>"L'heure n'est pas dans le bon format !"
				)
			)
		),
		'minutes' => array(
			"rules"=>array(
				array(
					'rule'=>'notEmpty',
					'message'=>"Un horaire doit être renseigné"
				),
				array(
					'rule'=>'regex',
					'regex'=>'[0-9]{2}',
					'message'=>"L'heure n'est pas dans le bon format !"
				)
			)
		),
		'nbmin' => array(
			"rules"=>array(
				array(
					'rule'=>'([0-9]+)',
					'message'=>"Nbmin doit être un nombre"
				)
			)
		),
		'user_id' => array(
			"rules"=>array(
				array(
					'rule'=>'([0-9]+)',
					'message'=>"User_id doit être un nombre"
				)
			)
		)

	);

	public function __construct(){

		parent::__construct();
		
		//cache for cluster of wesporter location
		$this->cacheData = new Cache(Conf::getCachePath().'/statistics',1440); //one day

	}
	public function findEvents($params){		

		extract($params);

		//If extend arround a city
		// set params to modified the query
		if(!empty($extend)){

			if(!empty($params['cityLat']) && !empty($params['cityLon']) && !empty($params['extend']) 
				&& is_numeric($params['cityLon']) && is_numeric($params['cityLat']) && is_numeric($params['extend'])){

				$cityLat = $params['cityLat'];
				$cityLon = $params['cityLon'];
				$distance= $params['extend'];
				//default in km
				$onedegree = 111.045;
				$earthradius = 6366.565;
				if(!empty($params['extend_metric']) && $params['extend_metric'] == 'km'){ // in km
					$onedegree = 111.045;
					$earthradius = 6366.565;
				}
				if(!empty($params['extend_metric']) && $params['extend_metric'] == 'miles'){ // in km
					$onedegree = 69;
					$earthradius = 3956;
				}			;				

				//calcul of the box
				$lon1 = $cityLon-$distance/abs(cos(deg2rad($cityLat))*$onedegree);
				$lon2 = $cityLon+$distance/abs(cos(deg2rad($cityLat))*$onedegree);
				$lat1 = $cityLat-($distance/$onedegree);
				$lat2 = $cityLat+($distance/$onedegree);

				$extend_zone = ", $earthradius * 2 * ASIN(SQRT( POWER(SIN(($cityLat - C.LATITUDE) *  pi()/180 / 2), 2) +COS($cityLat * pi()/180) * COS(C.LATITUDE * pi()/180) * POWER(SIN(($cityLon - C.LONGITUDE) * pi()/180 / 2), 2) )) as distance";
				
			}
			else {

			}
		}


		//values send to pdo prepare()
		$values = array();

		//Beginning og the query
		$sql = 'SELECT ';

		if(!empty($fields))
			$sql .= $this->sqlfields($fields);
		else
			$sql .= $this->sqlfields('E.*');

		//add distance field
		if(!empty($extend_zone))
			$sql .= $extend_zone;

		//FROM SQL TABLE
		$sql .= ' FROM events as E					

				';

		//add CITIES TABLE
		if(!empty($extend_zone))
			$sql .= " JOIN world_cities as C ON C.UNI = E.cityID ";


		$sql .= ' WHERE 1=1 ';


		//generic conditions
		if(isset($conditions))
			$sql .= ' AND '.$this->sqlConditions($conditions);

		if(isset($online))
		{
			if($online===0) $sql .= ' AND E.online=0 ';
			if($online===1) $sql .= ' AND E.online=1 ';
			
		}


		if(!empty($date)){
			if(is_string($date)){


			}
		}


		//date
		if(!empty($tempo)){

				if($tempo=='past')
					$sql .= ' AND E.date < CURDATE()';
				if($tempo=='futur')
					$sql .= ' AND E.date >= CURDATE()';
				
		}	
		elseif(!empty($date)){

			if(is_string($date)) $day = $date;
			if(is_array($date)) $day = $date['day'];

				$sql .= ' AND E.date = :date ';
				$values[':date'] = $day;

		}			
			
		$sql .=' ';
		
		

		//location
		if(!empty($location)){

			$arr = array('cityID','CC1','ADM1','ADM2','ADM3','ADM4');
			$ADM = array();
			foreach ($arr as $key) {

				if(!empty($location[$key]) && trim($location[$key])!=''){

					if(!empty($extend) && $key=='cityID') continue; // useful for extend cities to a radius
					
					$ADM[] = $key.'=:'.$key;
					$values[':'.$key] = $location[$key];
					//$ADM[] = $key.'="'.$location[$key].'" ';
				}								
			}
			if(count($ADM)>0)
				$sql .= ' AND '.implode(' AND ',$ADM).' ';
		}

		//extend beetween the box
		if(!empty($extend_zone)){

			$sql .= 'AND C.LONGITUDE BETWEEN :lon1 AND :lon2 AND C.LATITUDE BETWEEN :lat1 AND :lat2 ';

			$values[':lon1'] = $lon1;
			$values[':lon2'] = $lon2;
			$values[':lat1'] = $lat1;
			$values[':lat2'] = $lat2;

		}

		//get only sports of
		if(!empty($sports)){			

			if(is_array($sports)){

				$sql .= 'AND ';
				if(count($sports)>1){

					$i=1;
					
					$arr = array();
					foreach ($sports as $sport) {
					
						$arr[] = ' E.sport=:sport'.$i;
						$values['sport'.$i] = $sport;
						$i++;						
					}
					$sql .= '( '.implode(' OR ',$arr).' )';
				}
				else {

					$sql .= ' sport=:sport';
					$values[':sport'] = $sports[0];
					
				}
			}
			else{
				if(is_numeric($sports)){
					$sql .= 'AND ';
					if($sports!=0){
						$sql .= 'E.sport='.$sport;		
					}
					else {
						$sql .= ' E.sport!=0 ';
					}
				}
				
			}
					
		}		

		//get only cities where distance to chosen city < $distance
		if(!empty($distance) && is_numeric($distance)){
			$sql .= " having distance < ".$distance;
		}

		//Order by default or perso
		if(!empty($order)){

			$sql .= ' ORDER BY '.$order;

		}
		else {
			$sql .= ' ORDER BY E.date ASC, E.time ASC';
		}

		//set limit
		if(!empty($limit) && is_numeric($limit)){

			if(!isset($page) || empty($page) ||  !is_numeric($page)) $page = 1;			
			
			$sql .= ' LIMIT '.(($page-1)*$limit).','.$limit;			
			
		}

		//add string to the end of the query
		if(!empty($end)){

			$sql .= ' '.$end;
		}


		//debug($sql);
		//debug($values);
		//exit();
		$results = $this->query($sql,$values);

		$events = array();
		foreach ($results as $k => $event) {
			
			//join the author of the event
			$event->author = $this->eventAuthor($event->user_id);
			
			//jump oup if the author doesn't exist anymore
			if(!$event->author->exist()) continue;

			//if recurrent event, join days of recurrence
			if(isset($event->recur) && $event->recur==1)
				$event->recur_day = $this->eventRecurrence($event->id);
			
			//instanciate new Event
			$events[] = new Event($event);
		}

		//debug($events);
		return $events;
	}


	private function eventAuthor($uid){

		$sql = 'SELECT user_id,avatar,login,email,birthdate,account FROM users WHERE user_id=:user_id';
		$author = $this->query($sql,array('user_id'=>$uid));
		if(!empty($author[0])) return new User($author[0]);
		else return new User();
	}

	private function eventRecurrence($eid){

		$sql = 'SELECT Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday FROM events_recur WHERE event_id=:event_id ';
		$days = $this->query($sql,array('event_id'=>$eid),'fetchAll','array');		
		return $days[0];
	
	}


	public function findEventByID($event_id, $fields = '*'){

		$res = $this->findEvents(array('conditions'=>array('id'=>$event_id)));

		if(!empty($res))
			return $res[0];
		else
			return new Event();
		
	}

	public function findEventsBySerie($sid){

		return $this->findEvents(array('conditions'=>array('serie_id'=>$sid)));
	}

	public function findSerieByEvent($event){

		if($event->getSerieId()==0) return array($event);

		return $this->findEvents(array('conditions'=>array('serie_id'=>$event->getSerieId())));
	}


	/**
	 * return events in the futur
	 *
	 * @param string $CC1 country code
	 * @param int $number number of events to return
	 * @param int $sport type of sport to return
	 */
	public function getEventsToCome($CC1,$count,$sport=''){

		$sql = 'SELECT * ';
		$sql .= 'FROM events WHERE CC1=:CC1 ';
		$val = array();
		$val['CC1'] = $CC1;

		if(!empty($sport)) {
			$sql .= ' AND sport=:sport';
			$val['sport'] = $sport;
		}

		//day is superior or equal as current day and time is superior as current time
		$sql.= ' AND ( ( date = CURDATE() AND time > CAST("'.date("H:i:s").'" AS time) )';
		//OR following days
		$sql.= ' OR date > CURDATE() )';
		//number of event to return
		$sql.= ' LIMIT '.$count;
		
		$res = $this->query($sql,$val);

		$events = array();
		foreach ($res as $event) {
			
			$events[] = new Event($event);
		}

		return $events;
	}


	public function joinEventsParticipants($events, $proba = 1){

		if(is_object($events)) {
			$events = array($events);
			$is_object = true;
		}

		//Pour chaque evenement on cherche les participants
		foreach ($events as $event) {
					
			if(is_array($event)) $event = (object) $event;

			$participants = $this->findParticipants($event->id, $proba);

			$users = array();
			foreach ($participants as $participant) {
				
				$user = $this->findFirst(array('table'=>'users','conditions'=>array('user_id'=>$participant->user_id)));
				if(!empty($user)) $users[] = new User( $user );
				
			}

			$event->participants = $users;

		}

		if(isset($is_object))
			return $events[0];
		else
			return $events;

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

	public function eventsParticipants($event_id,$proba){

		$participants = $this->findParticipants($event_id, $proba);

		$users = array();
		foreach ($participants as $participant) {
			$user = $this->findFirst(array('table'=>'users','conditions'=>array('user_id'=>$participant->user_id)));
			if(!empty($user))
				$users[] = new User( $user );
		}

		return $users;
	}

	public function saveOcurrence($e){

		//timestamp of the event date
		$e->timestamp = strtotime($e->date.' '.$e->time);

		//unset recurence
		unset($e->recur_day);
		unset($e->startdate);
		unset($e->enddate);

		$this->loadModel('Users');		


		if($id = $this->save($e)) {

			//if new event, increment event created  statistics			
			if(isset($e->id) && $e->id!=$id) $this->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$e->user_id,'field'=>'events_created'));

			
			//save organizator participation
			$u = $this->Users->findFirstUser(array('fields'=>'user_id','conditions'=>array('user_id'=>$this->session->user()->getID())));
			$e->id = $id;
			$this->saveParticipants($u,$e);	


			return $id;
		}
		return false;
	}

	public function saveSerie($e){

			$start = new DateTime($e->startdate);
			$end = new DateTime($e->enddate);
			$end->modify('+1 day');

			$interval = DateInterval::createFromDateString('1 day');
			$period = new DatePeriod($start,$interval,$end);

			$occurences = array();
			$recur_day = $e->recur_day;

			foreach ($period as $dt) {

				$day = $dt->format('l');

				if(in_array($day, $recur_day)){
					
					$n = clone $e;
					$n->date = $dt->format('Y-m-d');
					
					$occurences[] = $n;				
				}
				
			}

			//save serie
			$serie = new stdClass();
			$serie->table= 'events_serie';
			$serie->startdate = $e->startdate;
			$serie->enddate = $e->enddate;
			$serie->count = count($occurences);
			foreach ($recur_day as $day) {			
				if(!empty($day)) $serie->$day = 1;
			}		
			if(!$serie_id = $this->save($serie)) return false;

			
			//save occurence
			foreach ($occurences as $k => $o) {
				
				$o->serie_id = $serie_id;
				$id = $this->saveOcurrence($o);

				$occurences[$k] = $id;
			}			

			//return first event_id
			if(isset($occurences[0]))
				return $occurences[0];				
			//return false if there is no occurence in the serie
			return false;


	}

	public function saveEvent($event){

		//time of the event
		$event->time = $event->hours.':'.$event->minutes;
		unset($event->hours);
		unset($event->minutes);

		//Country of the city
		if(empty($event->CC1)){
			$this->loadModel('Worlds');
			$city = $this->Worlds->findCityById($event->cityID,'CC1');
			$event->CC1 = $city->CC1;
		}

		//description
		if(!empty($event->description)){
			$event->description = nl2br($event->description);
		}

		//nombre minimum
		if(isset($event->nbmin)){			
			if($event->nbmin<=1) {
				$event->nbmin = 1;
				$event->confirmed = 1;
			}
		} 

		//recureence
		if($event->enddate!='0000-00-00' && $event->startdate!='0000-00-00' && !empty($event->recur_day)){
			
			return $this->saveSerie($event);
		}		
		
		return $this->saveOcurrence($event);

		return false;
	}


	public function saveParticipants($users,$event,$proba = 1){
		
		if(is_object($users)){
			$users = array($users);
			$is_unique = true;
		}

		foreach ($users as $user) {
			
			//debug($event);
			$check = $this->find(array('table'=>'sporters','conditions'=>array('event_id'=>$event->id,'user_id'=>$user->user_id)));

			if(empty($check)){

				$s = new stdClass();
				$s->event_id = $event->id;
				$s->user_id = $user->user_id;
				$s->date = date('Y-m-d');
				$s->date_event = $event->timestamp;
				$s->table = 'sporters';	
				$s->proba = $proba;			
				$this->save($s);	


				//Increment user participation
				$this->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$user->user_id,'field'=>'user_participation'));
		
			}	
				
		}

		return true;
	}

	public function cancelParticipation($user_id){

		$p = new StdClass();
		$p->table = "sporters";
		$p->key = 'id';
		$p->id = $user_id;

		if($this->delete($p)) {
			
			//decrement user participation
			$this->decrement(array('table'=>'users_stat','key'=>'user_id','id'=>$user_id,'field'=>'user_participation'));

			return true;
		}

		return false;
	}

	public function findParticipants($event_id, $proba = 1){

		if(!is_numeric($event_id)) return false;

		$participants = $this->find(array('table'=>'sporters',
			'fields'=>'user_id',
			'conditions'=>array('event_id'=>$event_id,'proba'=>$proba)));

		return $participants;
	}


	public function findWeekParticipation($user_id,$nbweek = 1){

		$sql = "SELECT * FROM sporters WHERE user_id = $user_id AND mail=1 AND FROM_UNIXTIME(date_event) > TIMESTAMPADD(WEEK,-$nbweek,NOW())";
		return $this->query($sql);

	}

	public function findMonthParticipation($user_id,$nbmonth = 1){

		$sql = "SELECT * FROM sporters WHERE user_id = $user_id AND mail=1 AND FROM_UNIXTIME(date_event) > TIMESTAMPADD(MONTH,-$nbmonth,NOW())";
		return $this->query($sql);

	}

	public function findSportersNotYetMailed(){
		
		$sql = "SELECT S.* FROM sporters AS S 
				JOIN events AS E ON E.id = S.event_id 
				WHERE E.confirmed=1 AND S.date_event < UNIX_TIMESTAMP() AND S.mail=0";

		return $this->query($sql);
	}

	public function mailReminderSended($sporter_id){

		$sql = "UPDATE sporters SET mail=1 WHERE id=$sporter_id";
		return $this->query($sql);
	}

	public function countTotalEvents(){

		$cachename = 'totalEvents';
		if($cache = $this->cacheData->read($cachename)) return $cache;
		$events = $this->findFirst(array('fields'=>"COUNT($this->primaryKey) as total"));
		$return = $events->total;
		$this->cacheData->write($cachename,$return);
		return $return;
	}

	public function findEventsDeposedLastDays($days = 0){

		$sql = "SELECT * FROM $this->table WHERE DATE(date_depot) >= DATE( DATE_SUB(NOW(),INTERVAL $days DAY))";
		$res = $this->query($sql);
		return $res;
	}

	public function countEventsDeposedLastDays($days = 0){		
		$cachename = 'countEventsDeposedFrom'.$days.'days';
		if($cache = $this->cacheData->read($cachename)) return $cache;
		$events = $this->findEventsDeposedLastDays($days);	
		$count = count($events);
		$this->cacheData->write($cachename,$count);
		return $count;
	}

	public function findEventsForNextDays($days = 0){

		$sql = "SELECT * FROM $this->table WHERE date >= CURDATE() AND date <=DATE_ADD(CURDATE(),INTERVAL $days DAY)";
		$res = $this->query($sql);
		return $res;
	}

	public function countEventsForNextDays($days = 0){		
		$cachename = 'countEventsFrom'.$days.'days';
		if($cache = $this->cacheData->read($cachename)) return $cache;
		$events = $this->findEventsForNextDays($days);	
		$count = count($events);
		$this->cacheData->write($cachename,$count);
		return $count;
	}

	public function countMonthEventsForYear($year){

		$cachename = 'countEventsPerMonthFor'.$year;
		if($cache = $this->cacheData->read($cachename)) return unserialize($cache);
		$sql = "select month(date) as month, count($this->primaryKey) as count
				from $this->table
				where year(date) = $year
				group by month(date)
				order by month(date)";

		$res = $this->query($sql);
		$a = array();
		foreach ($res as $m) {
			$monthName = date("F", mktime(0, 0, 0, $m->month, 10));
			$a[$monthName] = $m->count;
		}
		$this->cacheData->write($cachename,serialize($a));
		return $a;
	}

	public function countParticipants($event_id){

		$p = $this->findParticipants($event_id);

		return count($p);
	}

	public function confirmEvent($event_id){

		$s = new stdClass();
		$s->table = 'events';
		$s->id = $event_id;
		$s->confirmed = 1;

		if($this->save($s)) {

			//increment event confirmed
			$event = $this->findEventById($event_id);		
			$this->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$event->user_id,'field'=>'events_confirmed'));

			return true;
		}

		return false;
	}

	public function cancelEvent($event_id){

		$s = new stdClass();
		$s->table = 'events';
		$s->id = $event_id;
		$s->confirmed = 0;

		if($this->save($s)) return true;

		return false;
	}

	public function joinUserParticipation($events,$user_id){

		if($user_id==0) return $events;

		if(is_object($events)) {
			$events = array($events);
			$is_unique = true;
		}

		foreach ($events as $event) {

			$check = $this->findFirst(array('table'=>'sporters',"fields"=>"*",'conditions'=>array('event_id'=>$event->id,'user_id'=>$user_id)));
			if(!empty($check)){
				$event->UserParticipation = $check;
			}
		}
		
		if(isset($is_unique))
			return $events[0];
		else
			return $events;
	}


	public function findUserFuturParticipations($uid){
		
		$sql = "SELECT * FROM sporters as S
				JOIN events as E ON E.id = S.event_id 
				WHERE S.user_id=$uid AND S.date_event > UNIX_TIMESTAMP() ORDER BY S.date_event ASC";
		$res = $this->query($sql);

		$events = array();
		foreach ($res as $event) {
			$events[] = new Event($event);
		}
		return $events;
	}

	public function findUserPastParticipations($uid){
		
		$sql = "SELECT * FROM sporters as S
				JOIN events as E ON E.id = S.event_id 
				WHERE S.user_id=$uid AND S.date_event < UNIX_TIMESTAMP() ORDER BY S.date_event ASC";
		$res = $this->query($sql);

		$events = array();		
		foreach ($res as $event) {
			$events[] = new Event($event);
		}
		return $events;
	}

	public function findEventsUserOrganize($uid){

		$sql = "SELECT * FROM events WHERE user_id=$uid AND timestamp >= UNIX_TIMESTAMP()";
		$res = $this->query($sql);

		$events = array();
		foreach ($res as $event) {
			
			$event->reviews = $this->findReviewByEventId($event->id);

			$events[] = new Event($event);
		}		
		return $events;
	}

	public function findEventsUserHasOrganized($uid){

		$sql = "SELECT * FROM events WHERE user_id=$uid AND timestamp < UNIX_TIMESTAMP()";
		$res = $this->query($sql);
		
		$events = array();
		foreach ($res as $event) {	

			$events[] = new Event($event);
		}		
		return $events;
	}
	
	public function findReviewByEvents($events){

		
		foreach ($events as $k=>$event) {
			
			if($review = $this->findReviewByEventId($event->id)) $events[$k] = $review;

		}
		return $events;
	}

	public function findReviewByEventId($event_id){
		
		$res = $this->findFirst(array('table'=>'events_review','conditions'=>array('event_id'=>$event_id)));
		return $res;
	}	

	public function deleteEvent( $event ){
			
			if(!isset($event->id)) throw new zException("id must be defined to $event object", 1);

			$event->table ="events";

			if($this->delete($event))
				return true;
			else
				return false;		

	}

	public function desactivateEvent($event){

		$update = new stdClass();
		$update->table = 'events';
		$update->key = 'id';
		$update->id = $event->getID();
		$update->online = 0;

		if($this->save($update)){
			return true;
		}
		return false;
	}

	public function activateEvent($event){

		$update = new stdClass();
		$update->table = 'events';
		$update->key = 'id';
		$update->id = $event->getID();
		$update->online = 1;

		if($this->save($update)){
			return true;
		}
		return false;
	}

	public function findReviewByEvent($event_id){

	}

	public function findReviewByUser($user_id){

		$reviews = $this->find(array('table'=>'events_review','conditions'=>array('user_id'=>$user_id)));

		foreach ($reviews as $key => $review) {
				$event = $this->findEventByID($review->event_id);
				if($event->exist()) $review->event = $event;
				else unset($reviews[$key]);				
		}		
		return $reviews;
	}

	public function findReviewByOrga($orga_id){

		$reviews = $this->find(array('table'=>'events_review','conditions'=>array('orga_id'=>$orga_id)));

		foreach ($reviews as $key => $review) {
				$event = $this->findEventByID($review->event_id);
				if($event->exist()) $review->event = $event;
				else unset($reviews[$key]);				
		}		
		return $reviews;
	}	

	public function saveReview($data){

		//if exist
		$exist = $this->findFirst(array('table'=>'events_review','conditions'=>array('event_id'=>$data->event_id,'user_id'=>$data->user_id)));
		if(!empty($exist)) return 'already';
		
		$review = new stdClass();
		$review->table = 'events_review';
		$review->event_id = $data->event_id;
		$review->user_id = $data->user_id;
		$review->orga_id = $data->orga_id;
		$review->review = $data->review;
		$review->lang = $data->lang;

		if($this->save($review)){

			//increment user review
			$this->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$review->user_id,'field'=>'events_reviewed'));

			return true;
		}
		else
			return false;

	}

	public function saveFBGotoSportID($user_id,$event_id,$fb_id){

		$id = $this->getFBGotoSportID($user_id,$event_id);
		if(empty($id)) return;

		$s = new stdClass();
		$s->table = 'sporters';
		$s->key = 'id';
		$s->id = $id;
		$s->fb_id = $fb_id;

		if($this->save($s)) return true;
		else return false;
	}

	public function getFBGotoSportID($user_id,$event_id){

		$e = $this->findFirst(array('table'=>'sporters','fields'=>array('id'),'conditions'=>array('event_id'=>$event_id,'user_id'=>$user_id)));
		return $e->id;
	}


	public function setSportPracticed($user_id,$current_sport){

		$check = $this->findFirst(array('table'=>'users_sports_practiced','conditions'=>array('user_id'=>$user_id,'sport_slug'=>$current_sport)));

		if(empty($check)){

			$save = new stdClass();
			$save->table = 'users_sports_practiced';
			$save->user_id = $user_id;
			$save->sport_slug = $current_sport;
			if($this->save($save)) return true;
		}
		
	}

	public function getSportsPracticed($user_id){

		return $this->find(array('table'=>'users_sports_practiced','conditions'=>array('user_id'=>$user_id)));
	}

	

} 

class Event{

	public $id      = 0;
	public $sport   = 0;
	public $cityID  = '';
	public $ADM1    = '';
	public $ADM2    = '';
	public $ADM3    = '';
	public $ADM4    = '';
	public $CC1     = '';
	public $hours   = '';
	public $minutes = '';
	public $seconds = '';
	public $time    = '12:00:00'; //default time
	public $timing  = '';
	public $confirmed = 0;
	public $recur_day = array();
	public $online  = 0;

	public function __construct( $fields = array() ){
		
		if(empty($fields)) return;

		foreach ($fields as $field => $value) {
			
			$this->$field = $value;
		}

		//set timing once for all (perf)
		$this->timing = $this->timingSituation();
	}

	public function exist(){

		if($this->id==0) return false;

		return true;
	}

	public function getUrl(){

		//return Router::url('events/view/'.$this->getID().'/'.$this->getSlug());
		return Router::url($this->getSportSlug().'/'.$this->getID().'/'.$this->getSlug());
	}

	public function getUrlCreate(){

		return Router::url('events/create/'.$this->getID());
	}

	public function getAuthor(){

		if(isset($this->author->login)) return $this->author->login;
		return '';
	}	

	public function getLinkAuthor(){
		return Router::url('users/view/'.$this->user_id.'/'.$this->getAuthor());
	}

	public function isAdmin($user_id){

		if($this->user_id===$user_id) return true;
		return false;
	}

	public function isConfirm(){
		if($this->confirmed==1) return true;
		return false;
	}

	public function isASerie(){
		if($this->serie_id!=0) return true;
		return false;
	}

	public function getTitle(){

		return ucfirst($this->title);
	}
	public function getDescription(){

		return $this->description;
	}

	public function getTime(){

		return $this->time;
	}

	public function getHours(){
		if(!empty($this->hours)) return $this->hours;	
		$d = explode(':',$this->time);
		return $d[0];
	}

	public function getMinutes(){
		if(!empty($this->minutes)) return $this->minutes;
		$d = explode(':',$this->time);
		return $d[1];
	}

	public function getSecondes(){
		if(!empty($this->seconds)) return $this->seconds;
		$d = explode(':',$this->time);
		return $d[2];
	}

	public function getRowDate(){
		return $this->date;
	}
	public function getDate($lang=''){
		if(empty($lang)) $lang = Conf::$languageDefault;
		if($lang=='fr') return Date::datefr($this->date);
		return $this->date;
	}
	
	public function getDatetime(){
		return $this->getRowDate().' '.$this->getTime();
	}
	public function getTimestamp(){
		return $this->timestamp;
	}

	public function getSportLogo($size='small'){
		if(!$this->exist()) return '';
		
		if($size=='small') $size=30;
		elseif($size=='big') $size=60;
		else debug('Size of getSportLogo() is not an appropriate value');
		
		if(file_exists(WEBROOT.'/img/sport_icons/'.$size.'gif/'.$this->sport->slug.'.gif')) return Router::webroot('img/sport_icons/'.$size.'gif/'.$this->sport->slug.'.gif');
		return Router::webroot('img/sport_icons/'.$size.'gif/relaxation.gif');
	}

	public function getSportName(){
		if(!$this->exist()) return '';
		return $this->sport->name;
	}

	public function getSportSlug(){
		if(!$this->exist()) return '';
		if(is_string($this->sport)) return $this->sport;
		if(is_object($this->sport) && is_string($this->sport->slug)) return $this->sport->slug;
		
	}
	public function getSportAction(){
		if(!$this->exist()) return '';
		return $this->sport->action.' '.$this->sport->object;
	}
	public function getAvatar(){

		return $this->avatar;
	}

	public function getID(){

		return $this->id;
	}

	public function getSerieId(){
		return $this->serie_id;
	}

	public function getSlug(){

		return $this->slug;
	}

	public function getAge(){

		return date('Y')-$this->age;
	}

	public function getCityName(){

		return $this->cityName;
	}

	public function getCityID(){

		return $this->cityID;
	}

	public function getOnline(){
		return $this->online;
	}

	public function isOnline(){
		if(isset($this->online) && $this->online==1) return true;
		return false;
	}

	public function getRegions($ADM = null){

		if(!isset($ADM)) return $this->ADM4.' '.$this->ADM3.' '.$this->ADM2.' '.$this->ADM1;
		elseif(isset($this->$ADM)) return $this->$ADM;
		else return 'NA';
	}

	public function lastRegion(){
		if(!empty($this->ADM4)) return $this->ADM4;
		if(!empty($this->ADM3)) return $this->ADM3;
		if(!empty($this->ADM2)) return $this->ADM2;
		if(!empty($this->ADM1)) return $this->ADM1;
	}

	public function firstRegion(){

		return $this->ADM1;
	}

	public function getCountry(){

		return $this->CC1;
	}

	public function getNbParticipants(){
		
		return count($this->participants);
	}

	public function getUserParticipation(){

		if(!empty($this->UserParticipation))
			return true;
		return false;		
	}

	public function timingSituation(){
	
		if($this->date < date('Y-m-d') ) return 'past';
		if($this->date > date('Y-m-d') ) return 'tocome';
		if($this->date == date('Y-m-d') && $this->time >= date('H:i:s')) return 'tocome';
		if($this->date == date('Y-m-d') && $this->time < date('H:i:s')) return 'past';
		if($this->date == date('Y-m-d') && $this->time == date('H:i:s')) return 'current';
	}

	public function isUserParticipate($user_id){

		if($user_id===0) return false;

		foreach ($this->participants as $participant) {
			
			if($participant->getID() === $user_id) return true;
		}

		return false;
	}

	public function authorReviewed(){

		if(!empty($this->reviews)) return true;
		return false;
	}

	public function authorReviews(){

		return $this->reviews;
	}

	public function isRecurrent(){

		if(!empty($this->serie_id)) return true;
		return false;
	}

}


?>