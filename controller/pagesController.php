<?php

class PagesController extends Controller {

		public function __construct($request = null){

			parent::__construct($request);

			if(isset($request)) $this->pagename = $request->action;
		}

		public function handleSubdomain($sub){

			//si le cookie CITY exist déjà stop
			if($this->cookieEventSearch->read('cityID')!=''){
				return;
			}
			//if the subdomain is a predefined city
			if(array_key_exists($sub,Conf::$villes)){
				$cookie = $this->cookieEventSearch->arr();
				$cookie['cityName'] = Conf::$villes[$sub]['name'];
				$cookie['cityID'] = Conf::$villes[$sub]['id'];

				$this->cookieEventSearch->write($cookie);
			}
			else{
				
				$this->redirect('http://we-sport.fr');
			}
		}

		public function home( $day = null ){					

			$this->view = 'pages/home';
			$this->loadModel('Events');
			$this->loadModel('Worlds');
			$this->loadJS = array(			
				'js/jquery/tourbus/jquery.tourbus.min.js',
				'js/jquery/jquery.scrollTo-min.js',	
				'js/jquery/jquery.easing.1.3.js',
				'js/jquery/jquery.requestAnimationFrame.js',			
				);
			$this->loadCSS = array(
				'js/jquery/tourbus/tourbus.css'
				);

			//Set cookie for demo tour
			$d['display_demo'] = 1;
			if(!isset($_COOKIE['nb_visits'])){
				setcookie('nb_visits',0,time()+604800);	// a one week cookie						
			}
			else {
				$nb = $_COOKIE['nb_visits'];
				$nb++;
				setcookie('nb_visits',$nb,time()+3024000); // extends for a month
				//At the 5th visit, the demo tour is not displayed
				if($nb > 3)
					$d['display_demo'] = 0;
			}
		
			//Valeur par default de la recherche
			$params['sports'] = '';
			$params['cityID'] = '';
			$params['cityName'] = '';
			$params['extend'] = '';
			$params['location'] = '';
			$params['nbdays'] = 7;
			$params = array_merge($params,$this->cookieEventSearch->arr());			

			//Date de début à afficher
			if($day === null ){
					$params['date'] = date('Y-m-d');				
			}
			else {
				$params['date'] = $day;										
			}

			//On utilise par default les données du cookie
			$params['sports'] = $this->cookieEventSearch->read('sports');
			$params['cityID'] = $this->cookieEventSearch->read('cityID');
			$params['cityName'] = $this->cookieEventSearch->read('cityName');
			$params['extend'] = $this->cookieEventSearch->read('extend');
			$params['location'] = $this->cookieEventSearch->read('location');
			$params['nbdays'] = $this->cookieEventSearch->read('nbdays');
			
			
			//Ou on utilise les données de la requete
			if($this->request->get()){
				
				//on recupere les parametres
				foreach ($this->request->get() as $key => $value) {					
					$params[$key] = $value;
				}	

				//If sport not defined set sport to zero
				if(!$this->request->get('sports') && empty($params['sports'])) $params['sports'] = '';
				
				if($this->request->get('sport')) $params['sports'] = array($this->request->get('sport'));

				//si l'ID de la ville n'est pas fourni, on cherche une ville par son nom
				if(empty($params['cityID'])) {					
					if(!empty($params['cityName'])){
						$cities = $this->Worlds->suggestCities(array('CC1'=>'FR','prefix'=>$params['cityName'])); //on recupere les villes qui correspondent						
						if(!empty($cities)){
							$params['cityID'] = $cities[0]->city_id; //et on choisi la premiere ville
							$params['cityName'] = $cities[0]->name;
						}
					}
				}

				//if extend is not defined set it to zero
				if(!$this->request->get('extend')) $params['extend'] = '';

				if($this->request->get('nbdays') && is_numeric($this->request->get('nbdays'))){
					$params['nbdays'] = $this->request->get('nbdays');
				}
			}

			//On recupere le nom des regions
			$params['location'] = $this->Worlds->findCityById($params['cityID'],'CC1,ADM1,ADM2,ADM3,ADM4');
			$params['location'] = $this->Worlds->findStatesNames($params['location']);
			$params['location'] = (array) $params['location'];
			
			//on réécrit le cookie avec les nouveaux parametres
			$this->cookieEventSearch->write($params);					
			$d['params'] = $params;			
			$d['sports_available'] = $this->Events->findSports($this->getLang());			
			$d['sports_available_txt'] = '';
			foreach ($d['sports_available'] as $s) {
							$d['sports_available_txt'] .= $s->name.', ';
			}			
			
			$d['title_for_layout'] = "We-Sport - Agenda et rencontres sportives - pour faire du sport dans sa ville -";
			$d['description_for_layout'] = "We-Sport est un agenda des activités sportives dans ta ville. Venez découvrir les associations, les amateurs, et les professionels du sport autour de chez vous ! Découvrez de nouvelles activités, pratiquez librement, et faites de nouvelles rencontres autour du sport !";
			$d['keywords_for_layout'] = "We-sport, Wesport, Sport, Ville, Activités sportives, Agenda, Calendrier, ".$d['sports_available_txt'];
			
			$this->set($d);

		}

		//===================
		// Permet de rentre une page
		// $param $id id du post dans la bdd
		public function view($slug){
		
			//Si le slug correspond a une page particuliere
			if(method_exists($this, $slug)){
				$method = $slug;
				$this->$method(); //lance la methode particuliere du controller
				return;
			}

			//On charge le model
			$this->loadModel('Pages');			
				
			//On cherche la page		
			if(!$this->request->get('lang'))
				$page = $this->Pages->findPageBySlug($slug);
			else {
				debug($this->request->get('lang'));
				$page = $this->Pages->findPageBySlugAndLang($slug,$this->request->get('lang'));
			}

			//Si la page n'existe pas on redirige sur 404
			if(!$page->exist()){
				$this->e404('Page introuvable');
			}
			
			//On cherche le contenu
			$page = $this->Pages->JOIN_i18n($page,$page->lang);

			//Si la page a une methode particuliere
			if(method_exists($this, $page->slug)){
				$method = $page->slug;
				$this->$method();
				return;
			}

			//Si la traduction demandé n'existe pas on cherche la langue par default , si n'existe pas redirege 404
			if(!$page->isTraductionExist() || !$page->isTraductionValid()){
				$this->session->setFlash("La traduction demandé n'est pas disponible... <a href=".Router::url('pages/view/'.$page->slug.'/?lang='.$page->langDefault).">Cliquez ici</a> pour voir la page dans sa langue d origine ","warning");
				$this->e404('Page introuvable');
			}


			//Atttribution de l'objet $page a une variable page
			$this->set('page',$page);				
		}

		//Permet de recuperer les pages pour le menu
		public function getMenu($menu){

			$this->loadModel('Pages');

			//get requested lang
			$lang = $this->getLang();
			//search all pages to appears in menu
			$pages = $this->Pages->findMenu($menu);
			//debug($pages);			
			//find all traduction for requested language
			$pages = $this->Pages->JOINS_i18n($pages, $lang);
			
			//Unset page that have no traduction for requested lang
			foreach ($pages as $k => $page) {				
				if(!$page->isTraductionExist() || !$page->isTraductionValid() )  unset($pages[$k]);
			}

			//return pages if exist
			if(!empty($pages))
				return  $pages;	
			else 
				return array();				
		}


		public function admin_home(){

			$this->loadModel('Pages');
			$this->loadModel('Events');
			$this->loadModel('Users');

			$d['totalUsers'] = $this->Users->countTotalUsers();
			$d['monthRegistering'][2013] = $this->Users->countMonthRegisteringForYear(2013);
			$d['todayRegistering'] = $this->Users->countRegisteringFromDays(0);

			$d['totalEvents'] = $this->Events->countTotalEvents();
			$d['nbEventsPerMonth'][2013] = $this->Events->countMonthEventsForYear(2013);
			$d['nbEventsToday'] = $this->Events->countEventsForNextDays(0);
			
			$this->set($d);
		}

		public function admin_request(){

			$this->loadModel('Pages');

			$d['table'] = '';
			$d['field'] = '';	
			$d['primaryKey'] = '';		
			$d['all_tables'] = $this->Pages->findAllTables();

			if($data = $this->request->post()){
						
				if(!empty($data->table)){

					$d['all_fields'] = $this->Pages->findFieldsForTable($data->table);
					$d['primaryKey'] = $this->Pages->findPrimaryKeyForTable($data->table);
					$d['table'] = $data->table;
				}				

				if(isset($data->query)){

					$sql = "SELECT * FROM $data->table WHERE $data->field = :$data->field";
					$d['results'] = $this->Pages->query($sql,array($data->field=>$data->value));
					$d['all_fields'] = $this->Pages->findFieldsForTable($data->table);
					$d['primaryKey'] = $data->primaryKey;
					$d['table'] = $data->table;
					$d['field'] = $data->field;
				}

				if(isset($data->update)){
					
					$nb = $data->nbresults;
					$table = $data->table;
					$primaryKey = $data->primaryKey;
					unset($data->nbresults);
					unset($data->table);
					unset($data->primaryKey);
					unset($data->update);


					for($i=0; $i<$nb; $i++) {
					
						$obj = new stdClass();
						$obj->table = $table;
						$obj->key = $primaryKey;
						foreach ($data as $key => $value) {
							$obj->$key = $value[$i];
						}

						$err = 0;
						if($this->Pages->save($obj)){

						}
						else{
							$err++;
						}
					}

					if($err==0)
						$this->session->setFlash('Items saved');
					else
						$this->session->setFlash($err." errors occured when saving the items","warning");
					
				}
			}

			$this->set($d);
		}

		public function sendMailDailyStatToAdmins(){			

			//security
			//exit if is not a cron request BUT continue if user is an admin
			if(get_class($this->request)!='Cron') {
				if($this->session->user() && !$this->session->user()->isAdmin())
					exit('only cron job can access this method {pages:sendMailDailyStatToAdmins}');
			}

			$this->view = 'none';
    			$this->layout = 'none';
			$this->loadModel('Events');
			$this->loadModel('Users');

			$events_planned = $this->Events->findEventsForNextDays(0);
			$events_confirmed = 0;
			foreach ($events_planned as $e) {
				if($e->confirmed==1) $events_confirmed++;
			}
			$events_planned = count($events_planned);
			$events_deposed = $this->Events->countEventsDeposedLastDays(0);
			$registration = $this->Users->countRegisteringFromDays(0);

			//admin mails
			$emails = Conf::$emailsAdmins;			
			
			//Création d'une instance de swift mailer
			 $mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

			//Récupère le template et remplace les variables
			$body = file_get_contents(ROOT.'/view/email/admin/daily.html');
			$body = preg_replace("~{events_planned}~i", $events_planned, $body);
			$body = preg_replace("~{events_deposed}~i", $events_deposed, $body);
			$body = preg_replace("~{events_confirmed}~i", $events_confirmed, $body);
			$body = preg_replace("~{registration}~i", $registration, $body);

			//Création du mail
			$message = Swift_Message::newInstance()
			  ->setSubject("Daily stat")
			  ->setFrom('noreply@'.Conf::$websiteDOT, Conf::$website)
			  ->setTo($emails)
			  ->setBody($body, 'text/html', 'utf-8');          
			       
			 //Envoi du message et affichage des erreurs éventuelles
			 if (!$mailer->send($message, $failures))
			 {
			     $log = 'Fail: Mail daily stat.';
			 }
			 else $log = 'Daily stat. sended: registration'.$registration.', events planned:'.$events_planned.' , confirmed:'.$events_confirmed.'  events deposed:'.$events_deposed;

			       
		   	$this->Events->saveLog('admin mail','pages/admin_dailyMail',$log);
		   	exit($log);

		}

		public function admin_index($menu=null){

			$this->loadModel('Pages');

			if($this->request->post()){

				if($this->Pages->savePage($this->request->post())){

					$this->session->setFlash("Page sauvegardé !","success");
				}
				else
					$this->session->setFlash("message","type");
			}

			$lang = $this->getLang();			

			if(!isset($menu))
				$pages = $this->Pages->findPages();
			else
				$pages = $this->Pages->findMenu($menu);

			$traductions = $this->Pages->countPagesTraduction($pages);
			$pages = $this->Pages->JOINS_i18n($pages,$lang);	
			$menus = $this->Pages->findDistinctMenu();		

			if(empty($pages)) $pages = array();

			$d['traductions'] = $traductions;
			$d['menus'] = $menus;
			$d['pages'] = $pages;
			$d['lang'] = $lang;


			$this->set($d);			
		}

		public function admin_delete($id){

			$this->loadModel('Pages');

			if($this->Pages->deleteContent($id)){

				$this->session->setFlash("Page supprimé","success");

				if($this->Pages->deletei18nContents($id)){
					$this->session->setFlash("Traductions supprimés","success");
				}
				else {
					$this->session->setFlash("Error lors de la suppression des traductions","error");
				}				
			}
			else {
				$this->session->setFlash("Error lors de la suppression","error");
			}
			$this->redirect('admin/pages/index');
		}

		public function admin_edit($id = null){

			$this->loadModel('Pages');
			$d['id'] = $id;

			$lang = $this->getLang();		

			if($this->request->data){

				$new = $this->request->data;

				if($this->Pages->validates($new)){
									
					if($page_id = $this->Pages->savePage($new)){

						if($this->Pages->saveTraduction($new,$page_id)){

							$this->session->setFlash('Contenu modifié');
							//$this->redirect('admin/pages/edit/'.$page_id.'?lang='.$lang);
						}
					}
				}

				//on recupere la langue des données envoyés
				$lang = $new->lang;
			}
							
			if($id){
				$c = $this->Pages->getContent($id);
				$c = $this->Pages->JOIN_i18n($c,$lang);

				$trad = $this->Pages->findTraduction($id);

				$d['id'] = $id;
				$d['trad'] = $trad;
				$d['content'] = $c;
				$this->request->data = $c;
				
			}

			

			$this->set($d);
		}

		public function admin_blog($post_id=null, $slug=null){

			
		}

		public function blog($post_id=null, $slug=null){

			$this->loadModel('Events');
			$this->loadModel('Worlds');

			//If a post is specified
			if(isset($post_id)){
				$this->loadModel('Comments');
				$post = $this->Comments->getComments($post_id);
				if($post[0]->context!='blog') exit();
				$d['post_id'] = $post_id;
			}

			//EVENTS TO COME
			$eventsToCome = $this->Events->getEventsToCome($this->getCountryCode(),10);
			$eventsToCome = EventsController::arrangeEventsBySerie($eventsToCome);


			//CREATE GOOGLE MAP
			//include google map API class
			require(LIB.DS.'GoogleMap'.DS.'GoogleMapAPIv3.class.php');

			$gmap = new GoogleMapAPI();
			$gmap->setDivId('eventsToCome');
			$gmap->setSize('100%','250px');
			$gmap->setLang('FR');
			$gmap->setZoom(5);
			$gmap->setCenter('FR');
			$gmap->setEnableWindowZoom(true);
			$gmap->setDisplayDirectionFields(true);
			$gmap->setDefaultHideMarker(false);			

			//CREATE MARKERS
			foreach ($eventsToCome as $event) {
				
				$event = $this->Events->joinSport($event,$this->getLang());
				$event = $this->Worlds->JOIN_GEO($event);				
				$full_address = $event->address.', '.$event->getCityName().', '.$event->firstRegion().', '.$event->getCountry();

				$gmap->addMarkerByAddress($event->address.' , '.$event->getCityName(), $event->title, "<img src='".$event->getSportLogo()."' width='40px' height='40px'/><strong>".$event->title."</strong> <p>sport : <em>".$event->getSportName()."<em><br />Adresse: <em>".addslashes($event->address)."<br />Ville : <em>".$event->getCityName()."</em></p><p><small>".$event->description."</small></p>",$event->getSportSlug(),$event->getSportLogo());
			}			
			$gmap->generate();			
			$d['gmap'] = $gmap;
			
			reset($eventsToCome);
			$d['eventsToCome'] = $eventsToCome;

			$this->set($d);
		}


		public function contact(){

			$this->view = 'pages/contact';
			$this->loadModel('Pages');
			$page = $this->Pages->findPageBySlugAndLang('contact',$this->getLang());
			$page = $this->Pages->JOIN_i18n($page,$this->getLang());
			$d['page'] = $page;


			if($this->request->post()){

				$data = $this->request->post();
				
				//Security against robot
				//Login filed must be empty
				if(!empty($data->login)) throw new zException("Robot used contact form - Login field must be empty", 1);
				if(empty($data->time)) throw new zException("Robot used contact form - Login time must not be empty", 1);
				if(abs(time()-$data->time) < 3) throw new zException("Robot used contact form - form filled too fast for human being", 1);				
				
				

				if($this->sendMailContact($data->name,$data->email,$data->title,$data->message) && $this->Pages->saveContactMessage($data))
					$this->session->setFlash("Merci de votre message !","success");				
				else
					$this->session->setFlash("Erreur lors de la sauvegarde du message",'error');
				
			}

			$this->loadModel('Users');
			$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$this->session->user()->getID())));
			if(empty($user)) $user = new User();
			$d['user'] = $user;
			
			$this->set($d);
		}

		public function sendMailContact($sender_name,$sender_mail,$title,$message){

				//Création d'une instance de swift mailer
			        $mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

			        //Récupère le template et remplace les variables
			        $body = file_get_contents('../view/email/contact.html');
			        $body = preg_replace("~{site}~i", Conf::$website, $body);
			        $body = preg_replace("~{title}~i", $title, $body);
			        $body = preg_replace("~{user}~i", $sender_name, $body);
			        $body = preg_replace("~{user_mail}~i", $sender_mail, $body);
			        $body = preg_replace("~{date}~i", Date::datefr(date('Y-m-d')), $body);
			        $body = preg_replace("~{message}~i", $message, $body);

			        //Création du mail
			        $message = Swift_Message::newInstance()
			          ->setSubject($title." - ".$sender_name)
			          ->setFrom($sender_mail, $sender_name)
			          ->setTo(Conf::$contactEmail)
			          ->setBody($body, 'text/html', 'utf-8');          
			       
			        //Envoi du message et affichage des erreurs éventuelles
			        if (!$mailer->send($message, $failures))
			        {
			            echo "Erreur lors de l'envoi du email à :";
			            print_r($failures);
			            return false;
			        }
			        else return true;
		}

		public function testEmail($m = null){

			if(!$this->session->user()->isAdmin()) $this->e404('This method is reserved for admin user','No');

			if(!isset($m)) $m = Conf::$debugErrorEmails;

			if($this->sendEmails($m,'Test d\'envoi d\'email','Cet email est un test de la configuration d\'envoi de mail par le server.')){
				$this->session->setFlash('Email envoyé avec succès');
				$this->redirect('/');
			}
			else {
				exit();
			}
		}

}

?>