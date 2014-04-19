<?php 
class UsersController extends Controller{


	public $primaryKey = 'user_id'; //Nom de la clef primaire de la table
	public $table = 'users';
	public $table_recovery = 'users_mail_recovery';



	public function login(){

		$this->layout = 'default';
		$this->loadModel('Users');

		if($this->request->data){		
			
			$data = $this->request->data;
			$login = $data->login;

			if(strpos($login,'@'))
				$field = 'email';
			else
				$field = 'login';
			
			$user = $this->Users->findFirstUser(array(
				'fields'=> 'user_id,login,avatar,hash,salt,role,CC1,lang,account,birthdate,valid',
				'conditions' => array($field=>$login))
			);

			if($user->exist()){

				if($user->valid==1){

					if($user->hash == md5($user->salt.$data->password)){										

						//if the remember checbox is checked
						if(isset($data->remember)){
							//set secret key cookie
							$key = sha1($user->login.$user->hash.$user->salt.$_SERVER['REMOTE_ADDR']);
							setcookie('auto_connect',$user->user_id.'----'.$key, time() + 3600 * 24 * 7, '/', Conf::getSiteUrl(), false, true);

						}

						//increment user connexion
						$this->Users->increment(array('table'=>'users_stat','key'=>'user_id','id'=>$user->getID(),'field'=>'user_connexion'));

						//unset useless info
						unset( $user->hash);
						unset( $user->salt);
						unset($_SESSION['user']);
						unset($_SESSION['token']);

						//write session
						$this->user = $user;
						$this->session->write('user', $user);
						$this->session->setToken();				
						$this->session->setFlash('Vous êtes maintenant connecté','success',2);

						//redirection
						// redirect to the previous location if the user use the login page
						if(!empty($data->previous_url)){	
							//if the previous page is a wesport page						
							if(strpos($data->previous_url,Conf::getSiteUrl())===0) {
								//if it is not a /users/ page 				
								if(strpos($data->previous_url,'/users/')==0){
									//redirect to previous url
									header('Location: '.$data->previous_url);
									exit();																
								}
							}
						}						

						$this->redirect('/');
									
					
					}
					else {
						$this->session->setFlash('Mauvais mot de passe','error');
					}	
				}
				else{

					//security code
					$code = String::random(10);
					$_SESSION['secu_request_activation_mail'] = $code;
					$this->session->setFlash("Vous n'avez pas encore activé votre compte ! Veuillez cliquer sur le lien d'activation reçu dans votre boite mail. <a href='".Router::url('users/requestActivationMail?u='.$user->getID().'&c='.$code)."'>Ou demander un nouveau mail d'activation en cliquant ici</a>",'error');
				}					
			}
			else {
				$this->session->setFlash("Le ".$field." <strong>".$data->login."</strong> n'existe pas ...",'error');
			}
			$data->password = "";				

		}

	}

	/**
	* logout
	*/
	public function logout(){
		
		unset($_SESSION['user']);
		unset($_SESSION['token']);
		setcookie('auto_connect','', time() - 3600, '/', Conf::getSiteUrl(), false, true);

		$this->session->setFlash('Vous êtes maintenant déconnecté','info',2);	
		$this->reload();


	}		


	/*/**
	 * 
	 */
	public function index(){

		$this->loadModel('Worlds');
		$this->loadModel('Users');

		//default values
		$perPage = 3;	
		$codes = array('CC1'=>$this->getCountryCode());

		//if GET request (except page)
		if($this->request->get() && !$this->request->get('page')){
			$dpt = array('CC1','ADM1','ADM2','ADM3','ADM5','city');
			$codes = new stdClass();
			foreach ($this->request->get() as $key => $value) {
				if(in_array($key,$dpt) && trim($value)!='') $codes->$key = $value;
			}
			
			$this->session->write('userSearch',(array) $codes);		
		}
		elseif(is_array($this->session->read('userSearch'))){

			$codes = $this->session->read('userSearch');			
		}
		elseif($city_id = $this->cookieEventSearch->read('cityID')){
			
			$codes = $this->Worlds->findCityById($city_id,'CC1,ADM1,ADM2,ADM3,ADM4,UNI as city');							
		}

		//query params
		$params = array();		
		$params['conditions'] = (array) $codes;
		$params['fields'] = 'user_id,login,avatar,role,prenom,nom,birthdate,sexe,facebook_id,date_signin,city';
		$params['limit'] = (($this->request->page-1)*$perPage).','.$perPage;
		//users by page
		$users = $this->Users->findUsers($params);
		//all users !
		$params['limit'] = '';
		$all_users = $this->Users->findUsers($params);
		
		//total
		unset($params['limit']);
		$params['fields'] = 'count(user_id) as count';
		$d['total'] = $this->Users->findFirst($params);		
		$d['total'] = $d['total']->count; 
		$d['nbpage'] = ceil($d['total']/$perPage);

		//location
		$d['location_codes'] = $codes;
		$d['location_names'] = $this->Worlds->findStatesNames($codes);

		//maps					
		require(LIB.DS.'GoogleMap'.DS.'GoogleMapAPIv3.class.php');
		$gmap = new GoogleMapAPI();
		$gmap->setDivId('map');
		$gmap->setCenter(implode(' ',(array)$d['location_names']));
		$gmap->setDisplayDirectionFields(true);
		$gmap->setClusterer(true);
		$gmap->setSize('100%','100%');
		$gmap->setZoom(6);
		//$gmap->addKML('../googlemap/kml/Locator3RF.kml','radars_fixes','../googlemap/Locator3RF.png');
		if(!empty($users)){
			$path_to_kml = $this->Worlds->clusterOfWesportersCities('Wesporters Cities',$codes,$all_users);
			$gmap->addKML($path_to_kml,'Sportifs','../webroot/img/LOGO.gif');
		}
		$gmap->generate();
		 $d['gmap'] = $gmap;

		//users
		$d['users'] = $users;

		$this->set($d);

	}

	/**
	* auto_connect
	* search for cookie, auto connect and extend cookie if exist, delete cookie if not
	* @param session $session
	*/
	static function auto_connect($session){
		
		//if user not connected and cookie auto connect	exist
		if(isset($_COOKIE['auto_connect']) && !$session->user()->exist()){
			
			$auth = $_COOKIE['auto_connect'];
			$auth = explode('----',$auth);

			//find user in db
			$db = new UsersModel();
			$user = $db->findFirstUser(array('conditions'=>array('user_id'=>$auth[0])));

			//if user not exist delete cookie
			if(!$user->exist()) {				
				setcookie('auto_connect','', time() - 3600, '/', Conf::getSiteUrl(), false, true);
				return false;
			}

			//compute the key
			$key = sha1($user->login.$user->hash.$user->salt.$_SERVER['REMOTE_ADDR']);

			//if cookie match the key
			if($key == $auth[1]){
				//write user session
				$session->write('user',$user);
				$session->setToken();
				setcookie('auto_connect',$user->user_id.'----'.$key, time() + 3600 * 24 * 7, '/', Conf::getSiteUrl(), false , true);
			}
			//if not delete cookie
			else {					
				setcookie('auto_connect','',time() - 3600, '/', Conf::getSiteUrl(), false, true);
			}
		}
	}

	public function facebook_connect(){

		$this->view = "users/login";
		$this->loadModel('Users');

		$access_token = '';
		$app_id     = Conf::$facebook['appId'];
		$app_secret = Conf::$facebook['secret'];
		//redirect uri
		$redirect_url     = urlencode(Conf::getSiteUrl().'/?fb_connect=1');
		// /!\ if called directly from appcenter, change the redirect_uri  /!\ to be compliant with facebook logic that requires same redirect_uri from each call
		if(isset($_GET['source']) && $_GET['source']=='appcenter') 
			$redirect_url =urlencode(Conf::getSiteUrl().'/?fb_source=appcenter&fb_appcenter=1');



		//debug error si l'API ne revoit pas de code de connexion 
		if(empty($_REQUEST['code'])){
			$this->session->setFlash("Erreur avec facebook","error");
			$this->session->setFlash('[DEBUG] QUERY STRING was : '.$_SERVER['QUERY_STRING'],'warning');
			$this->e404('Facebook ne nous a pas communiquer les informations nécessaires. N\'oubliez pas de bien accepter l\'application ! ');
		}

		//else the code is set
		$code = $_REQUEST['code'];


		//the user have accepted the application
		//we can get a valid access_token. 		  	
		$token_url="https://graph.facebook.com/oauth/access_token?client_id="
		  . $app_id . "&redirect_uri=" .$redirect_url
		  . "&client_secret=" . $app_secret 
		  . "&code=" . $code . "&display=popup";
		$response = file_get_contents($token_url);
		$params = null;
		parse_str($response, $params);
		if(!isset($params['access_token'])) $this->e404("Il semble que la connexion facebook n'a pas pu être récupérée... Veuillez réessayer.","Oups");
		$access_token = $params['access_token'];
		    	 

		  //Confirm the token by querying the Graph
		  //Return the facebook user object
		  $graph_url = "https://graph.facebook.com/me?"
		    . "access_token=" . $access_token;
		  $response = curl_get_file_contents($graph_url);
		  $decoded_response = json_decode($response);
		    
		  //if the API return an error
		  if (isset($decoded_response->error)) {
		  	//check to seee if its an aAuth error
		  	if ($decoded_response->error->type== "OAuthException") {
		  		//if its an OAuth error, then try to get an new access token by reload this method
		  		$dialog_url = "https://www.facebook.com/dialog/oauth?"
		  				."redirect_uri=".$redirect_url
		  				."&client_id=".$app_id;
		  		$this->redirect($dialog_url);
		  	}
		  	else {
		  		throw new zException("Facebook connect : other error append", 1);		  		
		  	}

		  }
		  

		  //if no errors, user is in the decoded_response		 
		  $fbuser = $decoded_response;	  			  	
		  
			  //(fix) set a name for the og: Test User
			  if(empty($fbuser->username) && !empty($fbuser->name)) $fbuser->username = $fbuser->name;

			  //(debug) throw error if fuser id/email are missing
			  if(empty($fbuser->id) || empty($fbuser->email)) throw new zException("Missing id/email for facebook user {".serialize($fbuser)."}", 1);
			  
		  

		  //On vérifie si il existe dans la base
		  $user = $this->Users->findFirstUser(array('conditions'=>array('facebook_id'=>$fbuser->id)));
		
		  //Si il n'existe pas on l'enregistre dans la base
		  if(empty($user) || !$user->exist()){

		  	//on vérifie que ce login n'existe pas déjà
			$check = $this->Users->findFirst(array("table"=>"users",'fields'=>'user_id','conditions'=>array('login'=>$fbuser->username)));							
			if(!empty($check)) {
				$this->session->setFlash("Ce nom d'utilisateur est déjà pris !","error");
				$this->e404('Inscription avec facebook impossible, ce nom est déjà pris...');
			}				
			//on vérifie que ce mail n'exsite pas déjà
			$check = $this->Users->findFirst(array("table"=>"users",'fields'=>'user_id','conditions'=>array('email'=>$fbuser->email)));
			if(!empty($check)) {
				$this->session->setFlash("Cet email est déjà utilisé","error");
				$this->e404('Inscription aveec facebook impossible, cet email est déjà pris !');
			}

		  	$user = new stdClass();
		  	$user->login = $fbuser->username;
		  	$user->prenom = $fbuser->first_name;
		  	$user->nom = $fbuser->last_name;
		  	$user->email = $fbuser->email;
		  	$user->avatar = 'https://graph.facebook.com/'.$fbuser->id.'/picture';
		  	$user->hash = md5(String::random(10));
		  	$user->salt = String::random(10);
		  	$birthday          = explode('/',$fbuser->birthday);
			$user->birthdate   = $birthday[2].'-'.$birthday[0].'-'.$birthday[1];
			$user->sexe = ($fbuser->gender=='male')? 'h' : 'f';
			$user->valid = 1;
			$user->date_signin = $user->date_lastlogin = Date::MysqlNow();
			$fblocale = explode('_',$fbuser->locale);
			$user->lang = $fblocale[0];
			$user->CC1 = $fblocale[1];
			$user->facebook_id = $fbuser->id;
			$user->facebook_token = $access_token;

			if($user_id = $this->Users->saveUser($user)){
					$user->user_id = $user_id;
					$user = new User($user);
			}
			else {
				throw new zException("Error Saving Facebook User", 1);
				
			}
		  }

		//set cookie for autoconnexion			
		$key = sha1($user->login.$user->hash.$user->salt.$_SERVER['REMOTE_ADDR']);//set secret key cookie
		setcookie('auto_connect',$user->user_id.'----'.$key, time() + 3600 * 24 * 7, '/', Conf::getSiteUrl(), false, true);

		//unset session data
		unset( $user->hash);
		unset( $user->salt);
		unset($_SESSION['user']);
		unset($_SESSION['token']);

		//write session	data		
		$this->session->write('user', $user);
		$this->session->setToken();				
		$this->session->setFlash('Vous êtes maintenant connecté grace à facebook !','success');

		$this->redirect('/');

	}

	public function link_register_with_facebook(){

		$user = $this->facebook->getUser();

		//unique id that facebook send back to protect csrf attacks
		//$state = String::random(20);
		//$_SESSION['fbstate'] = $state;

		$loginUrl = $this->facebook->getLoginUrl(array(
			'redirect_uri'=>Conf::getSiteUrl().'/?fb_connect=1',
			'scope'=>'email,user_birthday,publish_actions',
			//'state'=>$state,
			));

		return $loginUrl;
		
	}


	/**
	* register
	* @param $data
	*/
	public function register( $data = null){

		$this->loadModel('Users');				

		$d = array();		
		
		if(isset($data) && is_object($data))
			$data = $data;		
		elseif($this->request->data)
			$data = $this->request->data;					

		if(empty($data)) return;
				
		//validates
		if($user = $this->Users->validates($data,'register')){

			//check if accept TOS
			if(isset($user->accept)&&$user->accept!=1){
				$this->session->setFlash("Veuillez accepter les conditions d'utilisations","error");
				$this->set(array('data'=>$user));
				return;		
			}

			//User
			$user->salt = String::random(10);
			$user->hash = md5($user->salt.$data->password);
			$user->codeactiv = String::random(25);					
			$user->lang = $this->session->user()->getLang();
			$user->date_signin = $user->date_lastlogin = Date::MysqlNow();
			unset($user->accept);						
			unset($user->password);

			

			//check if login exist
			$check = $this->Users->findFirst(array("table"=>"users",'fields'=>'user_id','conditions'=>array('login'=>$user->login)));							
			if(!empty($check)) {
				$this->session->setFlash("Ce nom d'utilisateur est déjà pris","error");
				$this->set(array('data'=>$user));
				return;
			}
			

			//check if email exist
			$check = $this->Users->findFirst(array("table"=>"users",'fields'=>'user_id','conditions'=>array('email'=>$user->email)));
			if(!empty($check)) {
				$this->session->setFlash("Cet email ".$user->email." est déjà utilisé","error");
				$this->set(array('data'=>$user));
				return;

			}

			//Save
			if($user_id = $this->Users->saveUser($user)) {

					if(isset($user->status) && $user->status!='group')
						$this->session->setFlash("<strong>Welcome</strong>","success");

					if($this->sendValidateMail(array('dest'=>$user->email,'user'=>$user->login,'codeactiv' =>$user->codeactiv,'user_id'=>$user_id)))
					{						
						$this->session->setFlash("Un email <strong>a été envoyé</strong> dans votre boite email. <strong>Veuillez cliquer sur le lien</strong> pour activer votre compte.", "success");
						$this->session->setFlash("Il est possible que cet email soit placé parmis les <strong>indésirables ou spam</strong> , pensez à vérifier !", "info");
					}
					else {
						$this->session->setFlash("Il y a eu une erreur lors de l'envoi de l'email de validation", "error");
					}

					$this->redirect('users/login');
			}
			else {
				
				throw new zException("Error can't save user", 1);					
			}					
			
			
		}
		else {				
			$this->session->setFlash("Veuillez vérifier vos informations",'error');
			$this->set(array('data'=>$data));

			return;
		}																		

	}



	/*===========================================================	        
	Validate
	Validate the email of the user	
	============================================================*/
	public function validate(){

		$this->loadModel('Users');

		if($this->request->get('c') && $this->request->get('u') ) {

			$get       = $this->request->get;
			$user_id   = urldecode($get->u);			
			$code_url = urldecode($get->c);
			
			$user = $this->Users->findFirstUser(array(				
				'conditions'=>array('user_id'=>$user_id)
				));


			if($user->exist()){

				if($user->codeactiv == $code_url) {
					$data =  new stdClass();
					$data->user_id = $user_id;
					$data->valid = 1;
					$this->Users->save($data);

					$this->session->setFlash('<strong>Bravo</strong> '.$user->login.' ! Tu as validé ton inscription','success');
					$this->session->setFlash('Tu peux te <strong>connecter</strong> dès maintenant!','info');
									

				}
				else {
					$this->session->setFlash("Une erreur inconnue est intervenue pendant l'activation",'error');
				}
			}
			else {	
						
				$this->session->setFlash("Le lien d'activation est incorrect... Veuillez réessayer.",'error');
				$this->session->setFlash("Vous pouvez demander un nouveau lien d'activation en essayant de vous connecter, et en cliquant sur <i>Demander un nouveau mail d'activation</i>",'info');
			}
		}
		else{
			$this->session->setFlash("Le lien d'activation est incorrect... Veuillez réessayer.",'error');
			$this->session->setFlash("Vous pouvez demander un nouveau lien d'activation en essayant de vous connecter, et en cliquant sur <i>Demander un nouveau mail d'activation</i>",'info');
		}


		$this->redirect('users/login');

	}


    public function account($action = null){    	

    	$this->loadModel('Users');
    	//$this->layout = 'none';

    	/*======================
			If user is logged
		========================*/
    	if($this->session->user()->getID())
    	{

    		
	    	$user_id = $this->session->user()->getID();
	    	
	    	/*======================
				If POST DATA are sended
			========================*/
	    	if($this->request->data) {							    		
	    		
	    		$data = $this->request->data;

	    		/*====================
	    			MODIFY ACCOUNT
	    		====================*/
	    		if($this->request->post('action')=='account' || $this->request->post('action')==''){
	    			
	    			if($this->Users->validates($data,'account_info')){

						$user = $this->Users->findFirstUser(array('fields'=>'login, email','conditions'=>array('user_id'=>$this->request->post('user_id'))));
							

						if($user->login!=$data->login){
							$check = $this->Users->findFirstUser(array('fields'=>'login','conditions'=>array('login'=>$data->login)));
							if($check->exist()) {
								unset($data->login);
								$this->session->setFlash('Ce pseudo est déjà utilisé');
							}
						}

						if($user->email!=$data->email){
							$check = $this->Users->findFirstUser(array('fields'=>'email','conditions'=>array('email'=>$data->email)));
							if($check->exist()){
								unset($data->email);
								$this->session->setFlash('Cet email est déjà utilisé dans notre système.');
							}
						}
					
    					if($this->Users->saveUser($data,$user_id)){

							$this->session->setFlash("Votre compte a été changé !","success");

							//update session login									
							$user = $this->session->user();
	    					if(isset($data->login)) $user->login = $data->login;
	    					if(isset($data->lang)) $user->lang = $data->lang;			    					
	    					$this->session->write('user', $user);
	    					
						}
						else{
							$this->session->setFlash("Error while saving your data, please retry","error");
						}
							
	    			}
	    			else {
	    				$this->session->setFlash("Please review the form","error");
	    			}
	    		}	    		


	    		/*====================
					MODIFY INFO
	    		=====================*/
	    		if($this->request->post('action') == 'profil'){

	    			if($this->Users->validates($data,'account_profil')){



	    				if($this->Users->saveUser($data,$user_id)){

	    					$this->session->setFlash('Votre profil a été changé !','success');
	    				}
	    				else {
	    					$this->session->setFlash("Sorry but something goes wrong please retry",'error');
	    				}
			    		
		    		}
		    		else 
		    			$this->session->setFlash('Veuillez revoir vos données','error');
		    	
	    		}


	    		/*===================
	    		 *   MODIFY AVATAR
	    		===================== */
	    		if($this->request->post('action') == 'avatar'){

	    			if($this->Users->validates($data,'account_avatar')){

	    				if($destination = $this->Users->saveFile('avatar','u'.$data->user_id)){

	    					$this->session->setFlash('Votre avatar a été changé ! ', 'success');

	    					$u = new stdClass();
	    					$u->user_id = $data->user_id;
	    					$u->avatar = $destination;
	    					$u->table = 'users';
				 			$this->Users->save($u);
				 				
				 			$u = $this->session->user();
				 			$u->avatar = $destination;
				 			$this->session->write('user', $u);
				 			$this->reload();
	    				}
	    				else{
	    					$this->session->setFlash('Une erreur a eu lieu lors de la sauvegarde. Merci de réessayer plus tard.','error');
	    					$this->reload();
	    				}
	    			}	    			
	    			else
	    				$this->session->setFlash('Veuillez revoir votre fichier','error');
	    		}
	    		/*====================
					MODIFY PASSWORD
	    		=====================*/
	    		if($this->request->post('action') == 'password')
	    		{
	    			
	    			if($data = $this->Users->validates($data,'account_password')){

		    				$db = $this->Users->findFirstUser(array(
		    					'fields' => 'user_id,salt,hash',
		    					'conditions'=> array('user_id'=>$user_id)
		    					));

		    				if($db->hash == md5($db->salt.$this->request->post('oldpassword'))){

		    					$newpw = new stdClass();
		    					$newpw->hash = md5($db->salt.$this->request->post('password'));
		    					$newpw->user_id = $user_id;
		    					
		    					if($this->Users->save($newpw))
		    						$this->session->setFlash('Votre mot de passe a été changé !');
		    					else
		    						$this->session->setFlash('Error while saving your password','error');

		    				}
		    				else $this->session->setFlash('Votre ancien mot de passe n\'est pas correct','error');
		    		}
		    		else 
		    			$this->session->setFlash('Veuillez revoir vos données','error');
	    		}

	    		/*====================
					MODIFY MAILING SETTINGS
	    		=====================*/
	    		if($this->request->post('action') == 'mailing')
	    		{
	    			
	    			if($data = $this->Users->validates($data,'account_mailing')){

	    				$settings_available = array('eventConfirmed',
	    									'eventCanceled',
	    									'eventOpinion',
	    									'eventChanged',
	    									'eventUserQuestion',
	    									'eventOrgaReply',
	    									'eventNewParticipant');

	    				$settings = array();
	    				foreach ($data as $key => $value) {
	    						
	    						if(in_array($key, $settings_available))
	    							$settings[$key] = $value;
	    				}

						if($this->Users->saveUserSettingsMailing($data->user_id,$settings)){
							$this->session->setFlash('Les paramêtres ont été sauvegardés');
						}
		    		}
		    		else 
		    			$this->session->setFlash('Veuillez revoir vos données','error');
	    		}

	    		/*====================
					MODIFY DELETE
	    		=====================*/
	    		if($this->request->post('action') == 'delete'){

	    			if($this->Users->validates($data,'account_delete')){

	    				$db = $this->Users->findFirstUser(array(
	    					'fields'=>'hash,salt',
	    					'conditions'=>array('user_id'=>$user_id)
	    					));
	    				
	    				if($db->hash == md5($db->salt.$this->request->post('password'))){

	    					
	    					$this->Users->delete($user_id);
	    					unset($_SESSION['user']);
	    					$user_id = 0;
	    					$this->session->setFlash('Votre compte a été supprimé... A bientôt ;)');

	    				}
	    				else
	    					$this->session->setFlash('Votre mot de passe n\'est pas correct','error');

	    			}
	    			else
	    				$this->session->setFlash('Veuillez revoir votre mot de passe','error');

	    		}	    			    			  
		    	
		    }

		    //get account info
	    	$user = $this->Users->findFirstUser(array(
					'conditions' => array('user_id'=>$user_id))
				);	 

	    	$user = $this->Users->JOIN('users_settings_mailing',' * ',array('user_id'=>':user_id'),$user);
		   		
	    	// /!\ request->data used by Form class
	    	$this->request->data = $user;

	    	$d['user'] = $user;

	    	//action
	    	if(!isset($action)) $action = '';
	    	$d['action'] = $action;

	    	$this->set($d);
	    }
	    else {

	    	$this->redirect('');	    	
	    }

    }


	public function recovery(){

		$this->loadModel('Users');

		$action='';
		
		//if user past the link we mailed him
		if($this->request->get('c') && $this->request->get('u') ){

			
			//find that user 
			$user_id = base64_decode(urldecode($this->request->get('u')));
			$user = $this->Users->findFirstUser(array(
				'fields'=>array('user_id','salt'),
				'conditions'=>array('user_id'=>$user_id)));
			
			//check the recovery code
			$code = base64_decode(urldecode($this->request->get('c')));
			$hash = md5($code.$user->salt);
			$user = $this->Users->findFirstUser(array(
				'table'=>$this->table_recovery,
				'fields'=>'user_id',
				'conditions'=>'user_id='.$user_id.' AND code="'.$hash.'" AND date_limit >= "'.unixToMySQL(time()).'"'));

			//if this is good
			if($user->exist()){

				//show password form
				$this->session->setFlash('Entrer votre nouveau mot de passe','success');
				$action = 'show_form_password';

			}
			else {
				//else the link isnot good anymmore
				$this->session->setFlash('Votre lien n\'est plus valide, veuillez demander une nouvelle réinitialisation de mot de passe','error');
				$action = 'show_form_email';
				
			}

			$d['code'] = $code;
			$d['user_id'] = $user_id;

		}

		//if user enter a new password
		if($this->request->post('password') && $this->request->post('confirm') && $this->request->post('code') && $this->request->post('user')){


			$data    = $this->request->post();
			
			//find that user
			$user_id = $data->user;
			$user = $this->Users->findFirstUser(array(
				'fields'=>array('user_id','salt'),
				'conditions'=>array('user_id'=>$user_id)));

			//check the recovery code
			$code = md5($data->code.$user->salt);
			$user = $this->Users->findFirstUser(array(
				'table'=>$this->table_recovery,
				'fields'=>'user_id',
				'conditions'=>'user_id='.$user_id.' AND code="'.$code.'" AND date_limit >= "'.unixToMySQL(time()).'"'));

			//if the code is good
			if($user->exist()){

				unset($data->code);
				unset($data->user);
				
				//validates the password
				if($this->Users->validates($data,'recovery_mdp')){

					//save new password
					$new = new stdClass();
					$new->salt = randomString(10);
					$new->hash = md5($new->salt.$data->password);
					$new->user_id = $user->user_id;					

					if($this->Users->save($new)){

						//find the recovery data 
						$rec = $this->Users->findFirstUser(array(
							'table'=>$this->table_recovery,
							'fields'=>array('id'),
							'conditions'=>array('user_id'=>$user_id,'code'=>$code)));

						//supress recovery data
						$del = new stdClass();
						$del->table = $this->table_recovery;
						$del->key = 'id';
						$del->id = $rec->id;
						$this->Users->delete($del);

						//redirect to connexion page
						$this->session->setFlash("Votre mot de passe a été changé !","success");
						$this->redirect('users/login');
					}
					else {
						$action = 'show_form_password';
						$this->session->setFlash("Error while saving. Please retry","error");
					}
				}
				else {
					$action = 'show_form_password';
					$this->session->setFlash("Veuillez revoir vos données","error");
				}

			}
			else
			{
				$action = 'show_form_email';
				$this->session->setFlash("Veuillez demander une nouvelle réinitialisation de mot de passe","error");
			}

			$d['code'] = $code;
			$d['user_id'] = $user_id;



		}

		//If user enter his email address
		if( $this->request->post('email') ) {

			$action = 'show_form_email';

			//check his email
			$user = $this->Users->findFirstUser(array(
				'fields'=>array('user_id','email','login','salt'),
				'conditions'=>array('email'=>$this->request->post('email')),				
			));

			if($user->exist()){

				//check if existant recovery data
				$recov = $this->Users->find(array(
					'table'=>$this->table_recovery,
					'fields'=>array('id'),
					'conditions'=>array('user_id'=>$user->user_id)
					));

				//if exist, delete it
				if(!empty($recov)){

					$del = new stdClass();
					$del->table = $this->table_recovery;
					$del->key = 'id';
					$del->id = $recov[0]->id;
					$this->Users->delete($del);
				}

				//create new recovery data
				$code = randomString(100);

				$rec = new stdClass();				
				$rec->user_id = $user->user_id;
				$rec->code = md5($code.$user->salt);
				$rec->date_limit = unixToMySQL(time() + (2 * 24 * 60 * 60));
				$rec->table = $this->table_recovery;
				$rec->key = 'id';

				//save it
				if($this->Users->save($rec)){

					//send email to user
					if($this->sendRecoveryMail(array('dest'=>$user->email,'user'=>$user->login,'code' =>$code,'user_id'=>$user->user_id))){

						$this->session->setFlash('Un email vous a été envoyé !','success');
						$this->session->setFlash("Pensez à vérifier dans les indésirables ou spam !","warning");

					}
					else{
						$this->session->setFlash('Error while sending the email. users/recovery','error');
						
					}
				}
				else{
					$this->session->setFlash('Error while saving data. users/recovery','error');
					
				}
			}
			else {
				$this->session->setFlash('This email is not in our database','error');
			}


		}

		$d['action'] = $action;
		$this->set($d);

	}

	public function check(){

		$this->loadModel('Users');
		$this->layout = 'none';
		$this->view = 'json';

		$d = array();

		if($this->request->get){

			$data = $this->request->get;
			$type = $data->type;
			$value = $data->value;	

			//if empty
			if(empty($value)){

				$d['error'] = 'Must not be empty';
			}
			else {
				
				//check validation model
				$check = new stdClass();
				$check->$type = $value;
				if(!$this->Users->validates($check,'account_info',$type)){
					
					$d['error'] = $this->Users->errors[$type];
				}

				//check reserved words
				if(in_array(strtolower($value),Conf::$reserved[$type]['array'])){

					$d['error'] = Conf::$reserved[$type]['errorMsg'];
				}
			}

			//if no error check existing
			if(empty($d['error'])){


				$user = $this->Users->findFirstUser(array('conditions' => array($type=>$value)));

					if($user->exist()) {
						$d['error'] = '<strong>'.$value."</strong> is already in use!";
					}
					else {
						$d['available'] = "";
					}

			}
				
		}	
		$this->set($d);	
	}

	public function requestActivationMail(){

		$this->view = 'users/login';

		if($data = $this->request->get()){

			if($data->c == $_SESSION['secu_request_activation_mail']){

				$this->loadModel('Users');
				$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$data->u),'fields'=>'email,login,codeactiv,user_id'));

				if($this->sendValidateMail(array('dest'=>$user->email,'user'=>$user->login,'codeactiv' =>$user->codeactiv,'user_id'=>$user->user_id))){

					$this->session->setFlash("Un email d'activation vient d'être envoyé à l'adresse <strong>".$user->email."</strong> ! (Pensez à vérifier les indésirables)",'info');

					$_SESSION['secu_request_activation_mail'] = '';
				}
				else{
					$this->session->setFlash('Hum... Une erreur inconnue a eu lieu. Veuillez nous contacter directement');
				}
			}
			else{
				$this->e404('Erreur inconnue','Error');
			}
		}

	}


	public function sendRecoveryMail($data)
    {
    	extract($data);

		$lien = Conf::getSiteUrl()."/users/recovery?c=".urlencode(base64_encode($code))."&u=".urlencode(base64_encode($user_id));
       
        //Récupère le template et remplace les variables
        $body = file_get_contents('../view/email/recoveryPassword.html');
        $body = str_replace("{site}", Conf::$website, $body);
        $body = str_replace("{user}", $user, $body);
        $body = str_replace("{lien}", $lien, $body);

        //Création du mail
        $message = Swift_Message::newInstance()
          ->setSubject("Change ton mot de passe")
          ->setFrom(Conf::$contactEmail, Conf::$websiteDOT)
          ->setTo($dest, $user)
          ->setBody($body, 'text/html', 'utf-8')
          ->addPart("Hey ".$user.", copy this link ".$lien." in your browser to change your password", 'text/plain');
       
        //Envoi du message et affichage des erreurs éventuelles
        if (!Conf::getMailer()->send($message, $failures))
        {
            echo "Erreur lors de l'envoi du email à :";
            print_r($failures);

            Conf::getMailLog();

            exit();
        }
        else return true;
    }

	public function sendValidateMail($data)
    {
    	extract($data);

		$lien = Conf::getSiteUrl()."/users/validate?c=".urlencode($codeactiv)."&u=".urlencode($user_id);

        //Création d'une instance de swift mailer
        $mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());
       
        //Récupère le template et remplace les variables
        $body = file_get_contents('../view/email/validateAccount.html');
        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{user}~i", $user, $body);
        $body = preg_replace("~{lien}~i", $lien, $body);

        //Création du mail
        $message = Swift_Message::newInstance()
          ->setSubject("Validation de l'inscription à ".Conf::$website)
          ->setFrom('noreply@'.Conf::$websiteDOT, Conf::$website)
          ->setTo($dest, $user)
          ->setBody($body, 'text/html', 'utf-8')
          ->addPart("Hey {$user}, copy this link ".$lien." in your browser. Welcome on the Protest.", 'text/plain');
       
        //Envoi du message et affichage des erreurs éventuelles
        if (!$mailer->send($message, $failures))
        {
            echo "Erreur lors de l'envoi du email à :";
            print_r($failures);
        }
        else return true;
    }


    public function view( $uid, $login = null ) {

    	$this->loadModel('Users');
    	$this->loadModel('Events');
    	$this->loadModel('Worlds');

    	//find user
    	$user = $this->Users->findUsers(array('conditions'=>array('user_id'=>$uid)));
    	$user = $user[0];
    	//404 is not exist
    	if(!$user->exist()) $this->e404('Cette page n\'existe pas !');

    	//Locate user
    	$user->location =  $this->Worlds->findStatesNames($user);
    	if(!empty($user->city)){
	    	$city = $this->Worlds->findCityById($user->city,' FULLNAMEND as name');
	    	$user->city = $city->name;
	    }

    	//find events
    	$futurParticipation = $this->Events->findUserFuturParticipations($uid);
    	$futurParticipation = EventsController::arrangeEventsBySerie($futurParticipation);
    	$d['futurParticipation'] = $futurParticipation;

    	$pastParticipation = $this->Events->findUserPastParticipations($uid);
    	$d['pastParticipation'] = $pastParticipation;
    	
    	$organiseEvents = $this->Events->findEventsUserOrganize($uid);
    	$organiseEvents = EventsController::arrangeEventsBySerie($organiseEvents);
    	$d['organiseEvents'] = $organiseEvents;

    	$hasOrganized = $this->Events->findEventsUserHasOrganized($uid);
    	$d['hasOrganized'] = $hasOrganized;
    	
    	foreach ($d as $key => $events) {

    		$d[$key] = $this->Events->joinSports($events,$this->getLang());
    	}
    	//find reviewed events
    	$d['eventsReviewed'] = $this->Events->findReviewByOrga($uid); 
    	$d['eventsReviewed'] = $this->Events->joinUser($d['eventsReviewed']); 

    	//count week participation
    	$d['weekParticipation'] = $this->Events->findWeekParticipation($uid);
    	$d['monthParticipation'] = $this->Events->findMonthParticipation($uid);

    	//Sport practiced
    	$d['sportsPracticed'] = $this->Events->getSportsPracticed($uid);

    	$d['user'] = $user;
 

    	$this->set($d);
    }


	public function getCountTotalUsers(){

		$this->loadModel('Users');
		return $this->Users->countTotalUsers();
	}  

	public function getCountTotalUsersByCity($city_id){

		$this->loadModel('Users');
		return $this->Users->CountTotalUsersByCity($city_id);
	}  

    // public function index(){

    // 	if($this->session->user()->isLog()){
    // 		$this->thread();
    // 	}
    // 	else {
    // 		$this->redirect('users/login');
    // 	}
    	
    // }

    /**
    *    
	*	User Thread
	*
    */ 
    // public function thread(){

    // 	$this->view = 'users/thread';
    // 	$this->loadModel('Users');
    // 	$this->loadModel('Manifs');
    // 	$this->loadModel('Comments');

    // 	//if user is logged
    // 	if($this->session->user()->isLog()){

    // 		//if user is numeric
    // 		if(is_numeric($this->session->user()->getID())){

    // 			//if user is a group, redirect to group page
    // 			if($this->session->user()->getRole()=='group'){

    // 				$group = $this->Users->findFirstUser(array(
    // 					'table'=>'groups',
    // 					'fields'=>'group_id as id, slug',
    // 					'conditions'=>array('user_id'=>$this->session->user()->getID())
    // 				));

    // 				$this->redirect('groups/view/'.$group->id.'/'.$group->slug);
    // 			} 
    				
    // 			//set $user_id
    // 			$user_id = $this->session->user()->getID();

    // 			//User
    // 			$d['user'] = $this->Users->findUsers(array('fields'=>'user_id,login,avatar,bonhom', 'conditions'=>array('user_id'=>$user_id)));
    // 			$d['user'] = $d['user'][0];
    // 			//$d['user']->context = 'userThread';
    // 			//$d['user']->context_id = $user_id;
    // 			//Participations
    // 			$d['protests'] = $this->Users->findParticipations('P.id,P.manif_id,M.logo,D.nommanif',array($user_id));

	   //  		/*	
    // 			//Timeline
    // 			$timing = $this->Users->findUserThread($user_id);
    // 			$thread = array();
    // 			//Fill the timeline
    // 			foreach($timing as $t){

				// 	$a          = array();
				// 	$a['TYPE']  = $t->TYPE;
				// 	$a['DATE']  = $t->date;
				// 	$a['RNAME'] = $t->relatedName;
				// 	$a['RID']   = $t->relatedID;
				// 	$a['RSLUG'] = $t->relatedSlug;
				// 	$a['RLOGO'] = $t->relatedLogo;

    // 				if( $t->TYPE == 'PROTEST'){

    // 					$a['OBJ'] = $this->Manifs->findProtesters(array(
    // 						'fields'=>array('P.id as pid','U.user_id','U.login','P.date','M.logo','M.manif_id','D.name','D.slug'),
    // 						'conditions'=>array('P.id'=>$t->id)
    // 						));
    // 				}
    // 				elseif( $t->TYPE == 'COMMENT'){

    // 					$a['OBJ'] = $this->Comments->findComments(array(
    // 						'fields'=>array('*'),
    // 						'comment_id'=> $t->id
    // 						));

    // 				}
    // 				elseif( $t->TYPE == 'NEWS'){

    // 					$a['OBJ'] = $this->Comments->findComments(array(
    // 						'fields'=>array('*'),
    // 						'comment_id'=> $t->id
    // 						));
    // 				}

    // 				$thread[] = $a;
    // 			}
				// */

    			

    // 		}
    // 	}
    // 	else {
    // 		$this->redirect('/');
    // 	}


    // 	$this->set($d);

    // }  

}

 ?>