<?php 
class EventsController extends Controller{

	public $primaryKey = 'id';

	public function __construct($req=null){

		parent::__construct($req);

		$this->cacheWeather = new Cache(Conf::getCachePath().'/events_weather_forecast',2); //3 hours
	}

	public function admin_index(){

		$this->loadModel('Events');

		$events = $this->Events->findEvents(array(
			'order'=>'E.date_depot DESC',
			'limit'=>1000,
			'online'=>'both',
			'tempo'=>'futur',

			));

		$events = $this->Events->joinSports($events,$this->getLang());

		$array = array();
		foreach ($events as $key => $event) {
			if($event->getSerieId()==0)
			{
				$array[] = $event;
				continue;
			}


			if($key>=1 && $event->getSerieId()!=$events[$key-1]->getSerieId())
			{
				$array[] = $event;
				continue;	
			} 
		}

		$this->set(array(
			'events'=>$array
			));
	}

	public function admin_desactivate($id){

		$this->loadModel('Events');

		$event = $this->Events->findEventByID($id);

		$events = $this->Events->findSerieByEvent($event);

		foreach ($events as $i => $e) {
			
			$this->Events->desactivateEvent($e);			
		}

		$this->session->setFlash('Evénement '.$event->getTitle().' désactivé');

		$this->redirect('admin/events/index');
	}

	public function admin_activate($id){

		$this->loadModel('Events');

		$event = $this->Events->findEventByID($id);

		$events = $this->Events->findSerieByEvent($event);

		foreach ($events as $i => $e) {
			
			$this->Events->activateEvent($e);			
		}

		$this->session->setFlash('Evénement '.$event->getTitle().' activé');

		$this->redirect('admin/events/index');
	}

	public function admin_delete($id){

		$this->loadModel('Events');

		$event = $this->Events->findEventByID($id);

		$events = $this->Events->findSerieByEvent($event);

		foreach ($events as $i => $e) {
			
			$this->Events->deleteEvent($e);			
		}

		$this->session->setFlash('Evénement '.$event->getTitle().' supprimé');

		$this->redirect('admin/events/index');
	}

	public function calendar($period='week',$action = 'now',$date = null){

		$this->view = 'events/index';
		$this->layout = 'none';
		$this->loadModel('Events');
		$this->loadModel('Worlds');
		
		//Parameter of the sql query
		$query = array();

		//GET Parameter
		$params =(array) $this->request->get();

		//COOKIE parameter
		$cookie = $this->cookieEventSearch->arr();

		//check validity of the date
		if(isset($date))
			if(!Date::is_valid_date($date,'yyyy-mm-dd')) exit('date is not valid');
		
		//Number of day of the week
		$numDaysPerWeek = 7; //default	
		if(isset($params['nbdays']) && is_numeric($params['nbdays'])){
			$numDaysPerWeek = $params['nbdays'];		
			$cookie['nbdays'] = $params['nbdays'];
		}
		
		if(isset($params['maxdays']) && is_numeric($params['maxdays']) && 0 != $params['maxdays'] && $params['maxdays'] < 7)
			$numDaysPerWeek = $params['maxdays'];

		
		if(!empty($action)){

			if(is_string($action)) {

				//day param			
				if($action=='now') $day = date('Y-m-d');
				elseif($action=='bottom') $day = $this->cookieEventSearch->read('date');
				elseif($action=='date') $day = $date;
				elseif($action=='prev'){
					if(isset($date)) $day = date('Y-m-d',strtotime($date.' - '.$numDaysPerWeek.' days'));
					elseif($this->cookieEventSearch->read('date')) $day = date('Y-m-d',strtotime($this->cookieEventSearch->read('date').' - '.$numDaysPerWeek.' days'));
					else $day = date('Y-m-d',strtotime(date('Y-m-d').' - '.$numDaysPerWeek.' days'));					
				}
				elseif($action=='next'){
					if(isset($date)) $day = date('Y-m-d',strtotime($date.' + '.$numDaysPerWeek.' days'));
					elseif($this->cookieEventSearch->read('date')) $day = date('Y-m-d',strtotime($this->cookieEventSearch->read('date').' + '.$numDaysPerWeek.' days'));
					else $day = date('Y-m-d',strtotime(date('Y-m-d').' + '.$numDaysPerWeek.' days'));	
				}				
				else $day = date('Y-m-d');

				if(!isset($day)) exit('no good url');

				$query['date'] = $day;

				//set cookie date			
				$cookie['date'] = $day;	
						
			}
		}	


		//if city is entered
		if(!empty($params['cityID']) && is_numeric($params['cityID'])){
			//set location for the model
			$query['location'] = array('cityID'=>$params['cityID']);
		}

		//if extend to city arroud
		if(!empty($params['extend']) && $params['extend'] != ' ' && is_numeric($params['cityID'])){			
			$extend = $params['extend'];
			if(!empty($params['cityID'])){

				//find latitude longitude of the city
				$city = $this->Worlds->findFirst(array('table'=>'world_cities','fields'=>array('LATITUDE','LONGITUDE'),'conditions'=>array('UNI'=>$params['cityID'])));
				if(!empty($city)){
					//and set params for the model
					$query['cityLat'] = $city->LATITUDE;
					$query['cityLon'] = $city->LONGITUDE;
					$query['extend'] = $extend;
					$query['extend_metric'] = 'km';
					//set extend cookie
					$cookie['extend'] = $extend;					
				}
			}			
		}
		else{
			$cookie['extend'] = '';
		}

		//Fields
		$query['fields'] = 'E.id, E.user_id, E.cityID, E.cityName, E.sport, E.date, E.time, E.title, E.slug, E.confirmed, E.description';


		//Limit
		$query['limit'] = 12;
		if(!empty($params['page']) && is_numeric($params['page'])){
			$query['page'] = $params['page'];
		}
		

		//if some sports are selected
		if(!empty($params['sports'])){

			$query['sports'] = $params['sports'];
			$cookie['sports'] = $params['sports'];
		}
		else {
			$cookie['sports'] = '';
		}


		//if one sport is selected
		if(!empty($params['sport']) && is_string($params['sport']) && trim($params['sport'])!=''){			
			if(!empty($query['sports']) && is_array($query['sports'])) $query['sports'][] = $params['sport'];
			else $query['sports'] = array($params['sport']);
			if(!empty($cookie['sport']) && is_array($cookie['sport'])) $cookie['sport'][] = $params['sport'];
			else $cookie['sport'] = array($params['sport']);
		}
		else
			$cookie['sport'] = '';

		
		//Rewrite cookie to remember the search				
		$this->cookieEventSearch->write($cookie);

		//initialize variable for days loop
		//first day of the week
		$firstday = $day;
		$weekday = $day;		
		$events = array();
		$dayevents = array();

		//for each days , get the events
		for($i=1; $i<= $numDaysPerWeek; $i++){
 		
			//set date param
			$query['date'] = array('day'=> $weekday) ;
			//find events in db
			$dayevents = $this->Events->findEvents($query);			
			$dayevents = $this->Events->joinSports($dayevents,$this->getLang());	
			$dayevents = $this->Worlds->JOIN_GEO($dayevents);
			$dayevents = $this->Events->joinEventsParticipants($dayevents);
			$dayevents = $this->Events->joinUserParticipation($dayevents,$this->session->user()->getID());

			//test purpose
			//$dayevents = array_merge($dayevents,$dayevents,$dayevents,$dayevents);

			$events[$weekday] = $dayevents;
			//set next day			
			$weekday = date("Y-m-d", strtotime($weekday. " +1 day"));

		}

		if($numDaysPerWeek<=14){
			$numWeeks = 1;
			$weeks = array($events);
		}
		else {
			$weeks = array_chunk($events, 7,true);	
			$numWeeks = count($weeks);		
		}


		$d['weeks'] = $weeks;
		$d['firstday'] = $firstday;
		$d['numWeeks'] = $numWeeks;
		$d['numDays'] = $numDaysPerWeek;
		
		$this->set($d);

		if($action=='bottom') $this->view = 'events/bottom';

		$this->render();
	}





	public function view($id = null,$slug = null,$sport = null){
	
		$this->view = 'events/view';

		$this->loadModel('Events');
		$this->loadModel('Worlds');	
		$this->loadModel('Users');	

		//check if id is numeruc
		if(empty($id) || !is_numeric($id)) $this->e404("Cet événement n'existe pas");

		//get event
		$event = $this->Events->findEventById($id);	
		
		//check if event exist
		if(!$event->exist()) $this->e404("Cet événement n'existe pas");

		//check if slug is correct, if not redirect to correct url
		if($event->slug != $slug) $this->redirect($event->getUrl());

		//events
		$event = $this->Worlds->JOIN_GEO($event);
		$event->sport = $this->Events->findSport(array('slug'=>$event->sport,$this->getLang()));
		$event->sport_en = $this->Events->findSport(array('slug'=>$event->sport->slug,'lang'=>'en'));	
		$event = $this->Events->joinUserParticipation($event,$this->session->user()->getID());		
		
		//Participants
		$event->participants = $this->Events->eventsParticipants($event->id,1);
		$event->uncertains = $this->Events->eventsParticipants($event->id,0);

		//review
		$event->reviews = $this->Events->findReviewByOrga($event->user_id);
		$event->reviews = $this->Users->joinUser($event->reviews,'login,user_id,avatar,birthdate');
	
		//google map API
		require(LIB.DS.'GoogleMap'.DS.'GoogleMapAPIv3.class.php');
		$gmap = new GoogleMapAPI();
		$gmap->setDivId('eventmap');
		$gmap->setSize('100%','250px');
		$gmap->setLang('fr');
		$gmap->setEnableWindowZoom(true);
		$fullAddress = $event->address.' , '.$event->getCityName().', '.$event->firstRegion().', '.$event->getCountry();	
		$gmap->addMarkerByAddress( $fullAddress, $event->title, "<img src='".$event->getSportLogo()."' width='40px' height='40px'/><strong>".$event->title."</strong> <p>sport : <em>".$event->getSportName()."<em><br />Adresse: <em>".addslashes($event->address)."<br />Ville : <em>".$event->getCityName()."</em></p>",$event->getSportName());
		$gmap->setCenter($fullAddress);
		$gmap->setZoom(12);
		$gmap->generate();		
		$d['gmap'] = $gmap;
		
		//setet exact Lat & Lon from googlemap
		$event->addressCoord = array('lat'=>$gmap->centerLat,'lng'=>$gmap->centerLng);

		//set OpenGraph Object
		$this->OpenGraphObject = $this->request('events','fb_og_metaMarkup',array($event));		

		//debug($event);

		//titre de la page
		$d['title_for_layout'] = 'weSport - '.$event->getSportName().' à '.$event->getCityName().' le '.$event->getDate($this->getLang());
		$d['description_for_layout'] = $event->author->getLogin().' organise un match de '.$event->getSportName().' près de la ville de '.$event->getCityName().' le '.$event->getDate($this->getLang()).' - via Wesport - '.$event->getTitle();
		$d['keywords_for_layout'] = 'Sport : '.$event->getSportName();
		$d['event'] = $event;
		$this->set($d);

	}

	public function addParticipant(){

		$this->loadModel('Events');
		$this->loadModel('Users');
		$this->view = 'none';
		$this->layout = 'none';

		if($this->request->get()){

			$data = $this->request->get();


			//Si un utilisateur est loggé
			if(!$this->session->user()->isLog()) throw new zException('User must log in before trying to participate',1);

			//Si les donnees sont bien numerique
			if(!is_numeric($data->user_id) || !is_numeric($data->event_id)) throw new zException('user_id or event_id is not numeric',1);

			//Si l'user correspond bien à la session
			if($data->user_id!=$this->session->user()->getID()) throw new zException("user is different from session's user", 1);
				
			//On vérifie si l'événement existe bien
			$event = $this->Events->findEventByID($data->event_id);
			if(!$event->exist()) throw new zException("L'évenement n'existe pas",1);

			//On vérifie si l'user existe bien
			$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$data->user_id)));
			if(!$user->exist()) throw new zException("L'utilisateur n'existe pas",1);

			//On vérifie qu'il ne participe pas déja
			$check = $this->Users->find(array('table'=>'sporters','fields'=>'id','conditions'=>array('user_id'=>$data->user_id,'event_id'=>$data->event_id)));
			if(!empty($check)) {
				$this->session->setFlash("Tu participe déjà !","info");				
			}

			//Probabilité de participation ( default=1)
			if(isset($data->proba)) $proba = $data->proba;
			else $proba = 1;

			//Sauver la participations
			if($this->Events->saveParticipants($user, $event, $proba)){
				
				//Set flash				
				$this->session->setFlash("C'est cool on va bien s'éclater :) ","success",5);
				//If facebook user post to OG:
				if($user->isFacebookUser()) $this->fb_og_GoToSport($event,$user);

				//On préviens l'organisateur
				if($this->sendNewParticipant($event,$user)){

					//Vérifier si le nombre est atteint
					//nombre actuel de participants
					$nbparticip = $this->Events->countParticipants($event->id);
					//Si ne nombre est atteint
					if($event->nbmin == $nbparticip ) {
						// on confirme l'evenement
						$this->Events->confirmEvent($event->id);
						//Envoi un mailing  aux participants
						$this->sendEventConfirmed($event);
					}
				}

			}
			else
				throw new zException('Unknown error while saving user participation',1);
				
		
			$this->redirect($event->getUrl());		
		}
	}



	public function removeParticipant(){

		$this->loadModel('Events');
		$this->loadModel('Users');
		$this->view = 'none';
		$this->layout = 'none';

		if($this->request->get()){

			$data = $this->request->get();

			if(!$this->session->user()->isLog()) throw new zException('User must log in before trying to cancel participation',1);

			//Si les donnees sont bien numerique
			if(!is_numeric($data->user_id) || !is_numeric($data->event_id)) throw new zException('user_id or event_id is not numeric',1);

			//Si l'user correspond bien à la session
			if($data->user_id!=$this->session->user()->getID()) throw new zException("user is different from session's user", 1);
				
			//On vérifie si l'événement existe bien
			$event = $this->Events->findEventById($data->event_id);
			if(!$event->exist()) throw new zException("L'évenement n'existe pas",1);

			//On vérifie si l'user existe bien
			$user = $this->Users->findFirstUser(array('fields'=>'user_id,facebook_id,facebook_token','conditions'=>array('user_id'=>$data->user_id)));
			if(!$user->exist()) throw new zException("L'utilisateur n'existe pas",1);

			//On vérifie qu'il participe
			$check = $this->Users->findFirst(array('table'=>'sporters','fields'=>'id','conditions'=>array('user_id'=>$data->user_id,'event_id'=>$data->event_id)));
			//Si il participe
			if(!empty($check)) {

				//Si c'est un utilisateur facebook on supprime la story
				if($user->isFacebookUser()) $this->fb_og_RemoveGoToSport($user,$event);
	
				//On annule sa participation
				if($this->Events->cancelParticipation($check->id)){
					//On previens
					$this->session->setFlash("Pas grave, à la prochaine !","success",3);					
					//On vérifie si le nombre min nest pas atteint
					//nombre actuel de participants
					$nbparticip = $this->Events->countParticipants($event->id);
					//Si ne nombre est atteint, on annule l'evenement
					if( $nbparticip == $event->nbmin-1 ) {
						//on annule l'événement
						if($this->Events->cancelEvent($event->id)){
							//on previens
							$this->session->setFlash("L'événement est suspendu...","warning",5);
							//on envoi un mailing  aux participants
							if($this->sendEventCanceled($event)) {

								$this->session->setFlash("Les participants ont été prévenues","info",7);	
							}

						} 
	
					}

				}
				else throw new zException("error cancel participation", 1);
						
			}			
						
			$this->redirect($event->getUrl());		
		}
	}


	public function review($eid){

		$this->loadModel('Events');

		$event = $this->Events->findEventById($eid);

		if($this->request->post()){

			$data = $this->request->post();
				
			if($res = $this->Events->saveReview($data)){
				
				if($res==='already') {
					$this->session->setFlash("Désolé mais vous avez déjà donné votre avis","warning");
				}
				else {

					$this->session->setFlash("Merci d'avoir donné votre avis !","success");	
				}
				
			}
		}
		$this->redirect($event->getUrl());
	}




	public function confirm($eid){

		if(!isset($eid) ||!is_numeric($eid)) exit();

		$this->loadModel('Events');

		$e = $this->Events->findEventById($eid);

		if($e->isAdmin($this->session->user()->getID())){
			//Confirm event
			$this->Events->confirmEvent($eid);			
			//set Flash
			$this->session->setFlash("L'activité est confirmée ! Amusez-vous bien !");
		}

		$this->redirect($e->getUrl());

	}



	public static function arrangeEventsBySerie($events){

		$arr = array();
		$serie = array();
		foreach ($events as $e) {
			
			if(!empty($e->serie_id)){
			
				if(!in_array($e->serie_id,$serie)){
					$serie[] = $e->serie_id;
					$e->serie = array();
					$arr[$e->serie_id] = $e;						
				}
				else{
					$arr[$e->serie_id]->serie[] = $e;
				}
			} 
			else $arr[] = $e;
		}

		return $arr;

	}
	public function create($event_id = 0){

		$this->loadModel('Events');
		$this->loadModel('Users');
		$this->loadModel('Worlds');

		// if user is logged
		if(!$this->session->user()->isLog()) {

			$this->session->setFlash("Vous devez vous connecter pour proposer un sport !","info");
			$this->redirect('users/login');
			exit();
		}

		
		//if an event is specifyed
		if($event_id!=0){


			//L'événement ne pas nouveau
			$is_new = false;
			//find event
			$evt = $this->Events->findEventById($event_id);
			//exit if event not exist
			if(!$evt->exist()) $this->redirect('events/create');
			
			//redistect if user not exist
			if(!$evt->isAdmin($this->session->user()->getID())){
				$this->session->setFlash("Vous n'êtes pas le créateur de cette annonce","error");				
				$this->redirect('users/login');			
			}						
			
		}
		else{
			//L'événement est nouveau
			$is_new = true;
			//créer un evenement vide pour l'affichage par default
			$evt = new Event();

		}

		//if new data are sended
		if($this->request->post()){				

			//data to save		
			$new = $this->request->post();
			
			//find a city if cityID is not defined			
			 if(empty($new->cityID)){
				//find cityID with cityName
				if(!empty($new->cityName)){
					$c = $this->Worlds->suggestCities(array('CC1'=>'FR','prefix'=>$new->cityName));
					
					if(!empty($c)){
						$new->cityID = $c[0]->city_id;
						$new->cityName = $c[0]->name;
					}
					else {
						$new->cityName = '';
					}
				}
			}

			//find states of the city, and latitude longitude
			if(!empty($new->cityID)){
				$states = $this->Worlds->findCityById($new->cityID,'ADM1,ADM2,ADM3,ADM4,CC1,LATITUDE as LAT,LONGITUDE as LON');
				foreach ($states as $key => $value) {
					if(!empty($value)) $new->$key = $value;
				}				
			}

			//init var
			$new->slug = slugify($new->title);


				if($this->Events->validates($new)){
					
					//Si l'evt existe déjà
					if($evt->exist()){

						//On regarde quel sont les changements , dans le but d'avertir les participants
						$changes = array();
						$silent_changes = array('slug','nbmin','cityID','ADM1','ADM2','ADM3','ADM4','CC1','LAT','LON','startdate','enddate');
						
						$evt = $this->Events->joinEventsParticipants($evt);
						if($evt->getNbParticipants()>1){

							foreach ($new as $key => $value) {

								if( isset($evt->$key) && $new->$key!=$evt->$key && !in_array($key,$silent_changes)) $changes[$key] = $new->$key;
							}
						}


						//On regarde si le nombre minimum est réduit, pour le confirmer si jamais
						if( $new->nbmin < $evt->nbmin ) {
							//On recupere le nombre de participants
							$nbparticipants = $this->Events->countParticipants($evt->id);
							//Si il y a plus de participants que nombre_min, on confirme l'événement
							if($nbparticipants >= $new->nbmin){

								$new->confirmed = 1;
								$this->session->setFlash("Le nombre de participants est atteint ! L'activité est confirmée !");
							}
						}

					}
										

					//save event
					if($event_id = $this->Events->saveEvent($new)){

						$this->session->setFlash("L'annonce a bien été enregistrée, elle est visible dès maintenant");
						
						//get event
						$evt = $this->Events->findEventById($event_id);
					
											
						//if the event is new
						if($is_new==true){
													
							//if its a facebook user, publish via the facebook OpenGraph
							if($this->session->user()->isFacebookUser()) $this->fb_og_WantSport($evt,$this->session->user());
						}

						//if the event already exist
						if($is_new==false){

							//if there are changes and event is not finished
							//email the changes 
							if(!empty($changes)&&$evt->timingSituation()!='past'){
								
								if($this->sendEventChanges($evt,$changes)){

									$this->session->setFlash('Les modifications ont été envoyées aux participants','warning');
								}
							}
							
						}

					}
					else{
						$this->session->setFlash("Il ya une erreur lors de la sauvegarde. Essaye encore","error");
					}

				}
				else{
					//if not validate , return a incomplete event fill with the data
					$evt = new Event($new);					
					$this->session->setFlash("Veuillez revoir votre formulaire",'error');
					$this->errors = $this->Events->errors;
				}		
		
		}
		
		$sports = $this->Events->findSportsList($this->getLang());		
		$d['sports_available'] = $sports;

		$eventfutur = $this->Events->findEvents(array('tempo'=>'futur','conditions'=>array('user_id'=>$this->session->user()->getID())));
		$eventfutur = $this->Events->joinSports($eventfutur,$this->getLang());
		$eventfutur = EventsController::arrangeEventsBySerie($eventfutur);
		$d['eventfutur'] = $eventfutur;


		$eventpast = $this->Events->findEvents(array('tempo'=>'past','order'=>'E.date DESC','conditions'=>array('user_id'=>$this->session->user()->getID())));
		$eventpast = $this->Events->joinSports($eventpast,$this->getLang());
		$eventpast = EventsController::arrangeEventsBySerie($eventpast);
		$d['eventpast'] = $eventpast;


		if($evt->exist()) {
			//On joint les sports dans la langue de l'utilisation
			$evt = $this->Events->joinSport($evt,$this->getLang());

			//On replace les <br/> par \n pour le textarea
			$evt->description = String::br2nl($evt->description);
		}

		$this->request->data = $evt;//send data to form class

		$d['event'] = $evt;


		$this->set($d);
	}


	public function delete($eid,$token){

		//tcheck token
		if($token!=$this->session->token()) $this->e404('Vous devez vous connecter avant de faire cette opération','Error');

		//find Event
		$this->loadModel('Events');
		$this->view = 'none';
		$evt = $this->Events->findEventById($eid);		

		//check if event exit
		if(!$evt->exist()) $this->e404('Cette activité n\'existe pas');

		//check if user is admin
		if(!$evt->isAdmin($this->session->user()->getID())) $this->e404('Vous ne pouvez pas supprimé cette activité','Error');

		//delete the event
		if($this->Events->deleteEvent($evt)){
			$this->session->setFlash("Activité supprimée !","success");

			//send Mailing to sporters				
			if($this->sendEventDeleting($evt)){
				$this->session->setFlash("Les participants ont été informés de l'annulation !","info");
			}
			
			$this->redirect('events/create');
		} else {
			$this->session->setFlash("Erreur... l'activité n'a pu être supprimée","danger");
			$this->redirect('events/create/'.$eid);
		}

		
	}

	public function serieDelete($eid,$token){

		//tcheck token
		if($token!=$this->session->token()) $this->e404('Vous devez vous connecter avant de faire cette opération','Error');

		//find Event
		$this->loadModel('Events');
		$this->view = 'none';
		$evt = $this->Events->findEventById($eid);	

		//check if event exit
		if(!$evt->exist()) $this->e404('Cette activité n\'existe pas');

		//check if user is admin
		if(!$evt->isAdmin($this->session->user()->getID())) $this->e404('Vous ne pouvez pas supprimé cette activité','Error');

		//find all event of the serie
		$evts = $this->Events->findEventsBySerie($evt->serie_id);

		foreach ($evts as $i => $evt) {
			
			$this->Events->deleteEvent($evt);
			$this->sendEventDeleting($evt);
				
		}

		$this->session->setFlash($i." événements correctement supprimés !");

		$this->redirect('events/create');
		
	}


	public function fb_og_WantSport($event,$user){


		if(!$user->isFacebookUser()) return false;

		//find english ACTION SPORT
		$this->loadModel('Events');
		$sport = $this->Events->findSport(array('slug'=>$event->sport,'lang'=>'en'));
		$sport_action = $sport->action;		

		$url = '/me/we-sport-:want_to?';
		$params = array(
			'sport'=>Conf::getSiteUrl().$event->getUrl(),
			'sport_action'=>$sport_action,
			'end_time'=>$event->getDate('en').' '.$event->getTime()
			);

		$res = $this->facebook->api($url,'POST',$params);

		if(!empty($res) && is_numeric($res['id'])){
			$this->session->setFlash('Story published on facebook');
			return true;
		}
		else {
			debug($res);
			$this->session->setFlash('Erreur OpenGraph','error');			
		}

	}

	public function fb_og_GoToSport($event,$user){

		if(!$user->isFacebookUser()) return false;

		//find english ACTION SPORT
		$this->loadModel('Events');
		$sport = $this->Events->findSport(array('slug'=>$event->sport,'lang'=>'en'));
		$sport_action = $sport->action;
		//if($sport_action=='go') $sport_action = ''; // "Go" is the default verb , no need to pass it in the og API call

		//url & params to POST to facebook open graph
		$url = '/me/we-sport-:go_to?';
		$params = array(
			'sport'=>Conf::getSiteUrl().$event->getUrl(),
			'sport_action'=>$sport_action,
			'end_time'=>$event->getDate('en').' '.$event->getTime()
			);		

		//facebook SDK		
		$res = $this->facebook->api($url,'POST',$params);

		//return 
		if(!empty($res) && is_numeric($res['id'])) {
			//save the id of the facebook story
			if($this->Events->saveFBGoToSportID($user->getID(),$event->id,$res['id'])){}
			//flash		
			$this->session->setFlash('Story published on facebook');
			return true;
		}
		else {
			debug($res);
			$this->session->setFlash('Erreur OpenGraph','error');			
		}
	}

	public function fb_og_RemoveGoToSport($user,$event){

		if(!$user->isFacebookUser()) exit('is not facebook user');

		$post_id = $this->Events->getFBGotoSportID($user->getID(),$event->id);
		if($post_id==0) exit('post_id=0');

		try{
			$res = $this->facebook->api('/'.$post_id,'DELETE',array('access_token'=>$user->getFacebookToken(),'method'=>'delete'));
		} catch (FacebookApiException $e){
			
			return false;
		}

		$this->session->setFlash('Deleting OpenGraph action !','success');
		return true;
	}

	public function fb_og_metaMarkup($event){
		//debug($event);
		$head = "prefix='og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# event: http://ogp.me/ns/event#'";
		$metas = '<meta property="fb:app_id"                content="'.Conf::$facebook['appId'].'" /> 
				  	<meta property="og:url"                   content="'.Conf::getSiteUrl().$event->getUrl().'" /> 
				  	<meta property="og:type"                  content="we-sport-:sport" /> 
				  	<meta property="og:title"                 content="'.$event->title.' - '.$event->getSportName().'" /> 
				  	<meta property="og:image"                 content="'.Conf::getSiteUrl().$event->getSportLogo('big').'" /> 
				  	<meta property="og:description"			content="'.substr($event->getDescription(),0,100).'" />
				  	<meta property="og:street-address" content="'.$event->address.'" />
					<meta property="og:locality" content="'.$event->cityName.'" />
					<meta property="og:country-name" content="'.$event->CC1.'" />
					<meta property="we-sport-:name"      content="'.$event->sport_en->name.'" />
					<meta property="we-sport-:action"    content="'.$event->sport_en->action.'" />
					<meta property="we-sport-:title"     content="'.$event->getTitle().'" />
					<meta property="we-sport-:description" content="'.substr($event->getDescription(),0,100).'" /> 					
					<meta property="we-sport-:datetime"            content="'.$event->getTimestamp().'" /> 
					<meta property="we-sport-:participants" content="'.count($event->participants).'" /> 
					<meta property="we-sport-:confirmed"     content="'.(($event->isConfirm())? 'true' : 'false').'" />
				  	<meta property="we-sport-:latitude"  content="'.$event->addressCoord['lat'].'" /> 
				  	<meta property="we-sport-:longitude" content="'.$event->addressCoord['lng'].'" />
				  	<meta property="we-sport-:adresse" content="'.$event->address.'" />
					<meta property="we-sport-:ville" content="'.$event->cityName.'" />
					<meta property="we-sport-:pays" content="'.$event->CC1.'" />
				';

		return array('head'=>$head,'metas'=>$metas);
	}

	protected function displayEventWeather($e){

		$diff = Date::dateDiff(Date('Y-m-d'),$e->date);
		if($diff>5)
			return false;
		return true;
	}

	private function deleteWeatherCache($eid){

		$cachename = 'event'.$eid;
		$this->cacheWeather->delete($cachename);
	}

	private function clearWeatherCache(){

		$this->cacheWeather->clear();
	}

	public function getEventWeather($eid,$token){

		//security
		if(!is_numeric($eid)) exit();
		if($token!=$this->session->token()) exit();

		$this->layout = 'none';
		$this->view = 'events/weather';

		//cache system
		//if the weather forecast have been put in cache , return the cached version
		$cachename = 'event'.$eid;
		if($cache = $this->cacheWeather->read($cachename)){		
			$this->set('weather',unserialize($cache));
			return;
		}
		//else
		//load models
		$this->loadModel('Events');
		$this->loadModel('Worlds');
		//find event and city
		$event = $this->Events->findEventByID($eid,'cityID,date');
		$city = $this->Worlds->findCityById($event->cityID,'FULLNAMEND as name,LATITUDE as lat,LONGITUDE as lon');

		//if no city return error
		if(empty($city)){
			$this->set('error','no city');
			return;
		}

		//check if the vent is in the 5 following days
		$diff = Date::dateDiff(Date('Y-m-d'),$event->date);
		if($diff>5) {
			//set error
			$this->set('error','forecast_limit_5days');
			return;
		}
		//set api key and api url
		//api http://developer.worldweatheronline.com/
		$api_key = 'a25upsyrnfwkrdce8nysm9ux';
		$url = "http://api.worldweatheronline.com/free/v1/weather.ashx?key=$api_key&q=$city->lat,$city->lon&date=$event->date&cc=no&format=json";
		
		//get content
		$json = curl_get_file_contents($url);
		$json = json_decode($json);

		//if weather data is not retrieve, that mean taht the request limit have been reached		
		if(!isset($json->data->weather)){
			$this->set('error','max_limit_request');
			return;
		}

		//set weather
		$weather = $json->data->weather[0];
		$this->cacheWeather->write($cachename,serialize($weather));
		$this->set('weather',$weather);

	}


	private function findEmailsParticipants($event,$mailingName=false,$withAuthor=false){

		$emails = array();

		$this->loadModel('Events');
		$this->loadModel('Users');

		//get emails of participants  	
		$sporters = $this->Events->findParticipants($event->id);	
		
		//pour chaque participants on cherche son email dans la bdd
		foreach ($sporters as $sporter) {

			$user = $this->Users->findFirstUser(array('fields'=>'user_id,email','conditions'=>array('user_id'=>$sporter->user_id)));			
			
			//si l'utilisateur nexiste pas on saute
			if(empty($user) || !$user->exist()) continue;

			if($withAuthor===false && $user->user_id==$event->user_id) continue; //on saute l'organisateur de l'événement 
			
			if($mailingName){
				//on recupere les preferences mailing de l'utilisateur
				$setting = $this->Users->findFirst(array('table'=>'users_settings_mailing','fields'=>$mailingName,'conditions'=>array('user_id'=>$sporter->user_id)));				
				//si l'utilisateur prefere ne pas recevoir ce mail on saute
				if(!empty($setting) && $setting->$mailingName == 0) continue;
			}

			
			//on push l'email dans un tableau
			$emails[] = $user->email;
			 
				
		}
		//retourne le tableau
		return $emails;
	}
	

	public function sendEventDeleting($event = null)
    {

    	$subject = "L'activité à laquelle vous participez a été supprimée - ".Conf::$website;

    	//get emails participants
    	$emails = $this->findEmailsParticipants($event,'eventCanceled');        	        

        //Récupère le template
        $body = file_get_contents('../view/email/eventDeleted.html');

        //Init varaible
        $lien = Conf::getSiteUrl()."/events/create";

        // remplace variables dans la template
        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{date}~i", Date::datefr($event->date), $body);
        $body = preg_replace("~{lien}~i", $lien, $body);
        $body = preg_replace("~{time}~i", $event->time, $body);
        $body = preg_replace("~{ville}~i", $event->cityName, $body);

        if($this->sendEmails($emails,$subject,$body)) return true;
        else return false;
    }

	private function sendEventChanges($event,$changes)
    {
    	//Sujet du mail
    	$subject = "L'activité à laquelle vous participez a été modifiée - ".Conf::$website;    

    	//get emails participatns
    	$emails = $this->findEmailsParticipants($event,'eventChanged');

        //Récupère le template 
        $body = file_get_contents('../view/email/eventChanges.html');

        //if time has been changed       
        if(isset($changes['hours']) || isset($changes['minutes'])) {
        	$changes['time'] = $event->time;
        	if(isset($changes['hours'])) unset($changes['hours']);
        	if(isset($changes['minutes'])) unset($changes['minutes']);
        }

        //Traduction
        $trad = array('title'=>'Titre','sport'=>'Sport','cityName'=>'Ville','address'=>'Adresse','date'=>'Date','time'=>'Heure','description'=>'Descriptif','phone'=>'Téléphone','recur_day'=>'Jour de la semaine :');

        //init variable
        $content = "";
        foreach ($changes as $key => $value) {
        	$content .= $trad[$key]." : <strong>".$value."</strong><br />";
        }        		
        $lien = Conf::getSiteUrl()."/".$event->getUrl();

        //remplace les variables dans la template
        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{date}~i", Date::datefr($event->date), $body);
        $body = preg_replace("~{lien}~i", $lien, $body);
        $body = preg_replace("~{content}~i", $content, $body);


        if($this->sendEmails($emails,$subject,$body)) return true;
        else return false;
    }

    public function sendMailNewComment($event_id,$comment_id){    	

    	if(!is_numeric($event_id)) throw new zException("Error Processing Request", 1);
    	if(!is_numeric($comment_id)) throw new zException("Error Processing Request", 1);
    	

    	$this->loadModel('Events');
    	$this->loadModel('Comments');
    	$this->view = 'none';
    	$this->layout = 'none';

    	$event = $this->Events->findEventById($event_id);
    	$email = $event->author->email;
    	
    	//user mailing setting
    	$setting = $this->Events->findFirst(array('table'=>'users_settings_mailing','fields'=>'eventUserQuestion','conditions'=>array('user_id'=>$event->author->user_id)));
    	if(!empty($setting) && $setting->eventUserQuestion==0) return false;
    
    	$com = $this->Comments->getComment($comment_id);
    	$content = $com->content;

    	if($event->author->user_id==$com->user_id) return false;

    	$user = $com->user;
    	$subject = $user->login.' vous a posé une question !';

    	$body = file_get_contents('../view/email/eventNewComment.html');

    	$lien = Conf::getSiteUrl()."/".$event->getUrl();

        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{user}~i", $user->login, $body);
        $body = preg_replace("~{comment}~i", $content, $body);
        $body = preg_replace("~{subject}~i", $subject, $body);
        $body = preg_replace("~{lien}~i", $lien, $body);

        if($this->sendEmails($email,$subject,$body)) return true;
        else return false;

    }

    public function sendMailNewReply($comment_id,$reply_id){


    	if(!is_numeric($reply_id)) throw new zException("Error Processing Request", 1);
    	if(!is_numeric($comment_id)) throw new zException("Error Processing Request", 1);
    	

    	$this->loadModel('Events');
    	$this->loadModel('Comments');
    	$this->view = 'none';
    	$this->layout = 'none';

    	$comment = $this->Comments->getComment($comment_id);
    	if(!$comment->exist()) exit('comment not exist');

    	$reply = $this->Comments->getComment($reply_id);
    	if(!$reply->exist()) exit('reply not exist');

    	$event = $this->Events->findEventById($comment->context_id);
    	if(!$event->exist()) exit('event not exist');

    	$email = $comment->user->email; 

    	//user mailing setting
    	$setting = $this->Events->findFirst(array('table'=>'users_settings_mailing','fields'=>'eventOrgaReply','conditions'=>array('user_id'=>$comment->user->user_id)));
    	if(!empty($setting) && $setting->eventOrgaReply==0) return false;   	

    	$content = $reply->content;

    	if($event->author->user_id==$comment->user_id) exit('user reply to himself');
    	

    	$subject = $reply->user->login.' vous a répondu !';

    	$body = file_get_contents('../view/email/eventNewReply.html');

    	$lien = Conf::getSiteUrl()."/".$event->getUrl();

        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{user}~i", $reply->user->login, $body);
        $body = preg_replace("~{comment}~i", $content, $body);
        $body = preg_replace("~{subject}~i", $subject, $body);
        $body = preg_replace("~{lien}~i", $lien, $body);

        if($this->sendEmails($email,$subject,$body)) return true;
        else return false;

    }

    private function sendEventConfirmed($event){

    	$subject = "L'activité ".$event->title." est confirmée !";

    	$emails = $this->findEmailsParticipants($event,'eventConfirmed',true);

    	$body = file_get_contents('../view/email/eventConfirmation.html');

    	$lien = Conf::getSiteUrl()."/".$event->getUrl();

        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{date}~i", Date::datefr($event->date), $body);
        $body = preg_replace("~{time}~i", $event->time, $body);
        $body = preg_replace("~{ville}~i", $event->cityName, $body);
        $body = preg_replace("~{lien}~i", $lien, $body);

        if($this->sendEmails($emails,$subject,$body)) return true;
        else return false;

    }


    private function sendEventCanceled($event){

    	$subject = "Un sportif s'est désisté, l'activité est suspendue...";

    	$emails = $this->findEmailsParticipants($event,'eventCanceled',true);

    	$body = file_get_contents('../view/email/eventAnnulation.html');

    	$lien = Conf::getSiteUrl()."/".$event->getUrl();

        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{date}~i", Date::datefr($event->date), $body);
        $body = preg_replace("~{time}~i", $event->time, $body);
        $body = preg_replace("~{ville}~i", $event->cityName, $body);
        $body = preg_replace("~{lien}~i", $lien, $body);
        

        if($this->sendEmails($emails,$subject,$body)) return true;
        else return false;

    }

    private function sendNewParticipant($event,$user){

    	$subject = $user->login." participe à votre activité !";

    	$this->loadModel('Users');
    	$author = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$event->user_id)));
    	$email = $author->email;

    	//user mailing setting
    	$setting = $this->Events->findFirst(array('table'=>'users_settings_mailing','fields'=>'eventNewParticipant','conditions'=>array('user_id'=>$author->user_id)));
    	if(!empty($setting) && $setting->eventNewParticipant==0) return false;   

    	$body = file_get_contents('../view/email/eventNewParticipant.html');

    	$eventLink = Conf::getSiteUrl()."/".$event->getUrl();
    	$userLink = Conf::getSiteUrl()."/users/view/".$user->getID().'/'.$user->getLogin();

        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{title}~i", $event->title, $body);
        $body = preg_replace("~{author}~i", $author->getLogin(), $body);
        $body = preg_replace("~{sporter}~i", $user->getLogin(), $body);
        $body = preg_replace("~{eventlink}~i", $eventLink, $body);
        $body = preg_replace("~{sporterlink}~i", $userLink, $body);

        if($this->sendEmails($email,$subject,$body)) return true;
        else return false;
    }

    public function sendMailUserEventOpinion(){

    	if(get_class($this->request)!='Cron') exit();

    	$debut = microtime(true);

    	$this->view = 'none';
    	$this->layout = 'none';
    	$this->loadModel('Events');
    	$this->loadModel('Users');

    	$sporters = $this->Events->findSportersNotYetMailed();

    	$nb_sporters = 0;
    	$nb_mail_sended = 0;
    	$nb_mail_silent = 0;
    	$nb_mail_error = 0;
    	$mail_content = file_get_contents(ROOT.'/view/email/eventPastEventReminder.html');


    	foreach ($sporters as $key => $sporter) {
    			
    		//find user
    		$sporter->user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$sporter->user_id)));    		
    		//if user dont exist jump out
    		if(empty($sporter->user) || !$sporter->user->exist()) continue;

    		//find event
    		$sporter->event = $this->Events->findEventById($sporter->event_id);
    		$sporter->event->numParticipants = $this->Events->countParticipants($sporter->event->id);

    		//if event dont exist jump out
    		if(!$sporter->event->exist()) continue;
    			
    		//jump out if the user dont want the mail
    		$setting = $this->Users->findFirst(array('table'=>'users_settings_mailing','fields'=>'eventOpinion','conditions'=>array('user_id'=>$sporter->user->getID())));
    		if(!empty($setting) && $setting->eventOpinion==0){
    			$nb_mail_silent++;
    			$this->Events->mailReminderSended($sporter->id); //set the mailing to done
    			continue;
    		}

    		//jump out if the user is the organisator
    		if($sporter->user_id==$sporter->event->user_id){    			
    			$this->Events->mailReminderSended($sporter->id); //set the mailing to done
    			continue;
    		}

    		$nb_sporters++;


    		//emailing
	    	$subject = 'Alors c\'était comment ?!';
	    	$eventLink = Conf::getSiteUrl()."/".$sporter->event->getUrl();
	    	$userLink = Conf::getSiteUrl()."/users/view/".$sporter->user->getID().'/'.$sporter->user->getLogin();
	    	$body = $mail_content;
	    	$body = preg_replace("~{site}~i", Conf::$website, $body);
	        $body = preg_replace("~{title}~i", $sporter->event->title, $body);
	        $body = preg_replace("~{subject}~i", $subject, $body);
	        $body = preg_replace("~{eventlink}~i", $eventLink, $body);

	        
	        if($this->sendEmails($sporter->user->email,$subject,$body)){

	        	$this->Events->mailReminderSended($sporter->id);
	        	$nb_mail_sended++;

	        	//increment events particpants						
				$this->Users->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$sporter->event->user_id,'field'=>'events_participants','number'=>$sporter->event->numParticipants));
				//increment sporters encourter
				$this->Users->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$sporter->user_id,'field'=>'sporters_encounted','number'=>$sporter->event->numParticipants));
				//Set sport practiced for stat
				$this->Events->setSportPracticed($sporter->user_id,$sporter->event->getSportSlug());
				//delete weather cache
				$this->deleteWeatherCache($sporter->event->id);

	        }
	        else $nb_mail_error++;
    	}
    	
    	$timer = round(microtime(true) - $debut,5).'s';
    	$log = 'Mail sended:'.$nb_mail_sended.', error:'.$nb_mail_error.' , silent:'.$nb_mail_silent.'  total:'.$nb_sporters.'  '.$timer;
    	$this->Events->saveLog('cron mail','events/sendMailUserEventOpinion',$log);
    	exit($log);

    }



} ?>