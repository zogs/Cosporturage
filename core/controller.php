<?php

class Controller {

	public $request;	
	public $layout      = 'default';
	public $view        = 'default';
	private $rendered   = false;
	private $vars       = array();

	function __construct($request=null){
		
		$this->session = new Session($this);	

		$this->Form = new Form($this);
		$this->Date = new Date($this->session);
		$this->cache = new Cache(Conf::getCachePath(),60);
		
		//Si une request est passé on effectue des vérifications de sécurité
		if($request){
			$this->request = $request; //ON stocke la request dans l'instance
			$this->security($request); //On check le jeton de sécurité
			require ROOT.DS.'config'.DS.'hook.php'; //Systeme de hook pour changer le layer en fonction du prefixe
		}
		
		//Si cest un requete cron ne pas executé la suite
		if(get_class($request)=='Cron') return;

		//On instyancie le cookie de recherche
		$this->cookieEventSearch = new Cookie('Search',60*60*24*30,true);

		//Gestion des sous domains		
		$this->ifSubdomainExist();		

		//Initialisation autoconnection
		UsersController::auto_connect($this->session);


		//on instancie un objet facebook pour nous permettre de faire de appel à l'API
		require_once LIB.'/facebook-php-sdk-master/src/facebook.php';
		$this->facebook = new Facebook(array('appId'=>Conf::$facebook['appId'],'secret'=>Conf::$facebook['secret'],'cookie'=>true));
		$this->facebook->setAccessToken($this->session->user()->getFacebookToken());
		
		//redirect on facebook connect method if its a facebook log in request
		if((isset($_GET['fb_source']) || isset($_GET['fb_connect'])) && isset($_GET['code'])) $this->redirect('users/facebook_connect?code='.$_GET['code'].'&source='.(isset($_GET['fb_source'])? $_GET['fb_source'] : ''));		
		//redirect error if facebook return error
		if(isset($_GET['fb_connect']) && isset($_GET['error'])){
			$msg = "Facebook a retourné une erreur : <i>".(isset($_GET['error_description'])? $_GET['error_description']:'')."</i><br /><small>( Pensez à bien autoriser l'application si ce n'est pas encore fait ! )</small>";
			$this->e404($msg,"Aïe !");
		}

		
	}


	// Permet de rendre le resultat du controller
	// @params view : la page a rendre

	public function render($view = null){
		
		//Si la page a deja ete rendu on stop la function
		if($this->rendered) return false;

		//On extrait les variables de la méthode appelée
		extract($this->vars);
		
		//Si la vue par default
		if( $this->view == 'default') {
			//Si la page a rendre commence par un '/ '
			if(strpos($view,'/')===0){
				$view = ROOT.DS.'view'.$view.'.php'; //On rend la page demandé
			} else {
				$view = ROOT.DS.'view'.DS.$this->request->controller.DS.$view.'.php'; //Sinon on utlise le systeme MVC
			}
		}
		else { //Sinon une vue personnalisée est appelée
			$view = ROOT.DS.'view'.DS.$this->view.'.php';
		}

		//check if the view exist
		if(!file_exists($view)){
			
			if(DEBUG>=1)
				$this->e404('The controller :'.$this->request->controller.' has no view :'.$this->request->action);						
			else
				$this->e404('This page don\'t work... We\'re sorry :(');							
		}

		//if view exist start buffer
		ob_start();
		require $view; //execute the view
		$content_for_layout = ob_get_clean(); //get the buffer 

		//load layout and send buffer
		$layout = ROOT.DS.'view'.DS.'layout'.DS.$this->layout.'.php';
		if(!file_exists($layout)){
			if(Conf::$debug>=1)
				$this->e404('The layout :'.$layout.' is not found');				
			else
				$this->e404('This page don\'t work... We\'re sorry :(');
		}
		require $layout;


		//La page a été rendu
		$this->rendered = true;
	
	}

	//Rend la vue Erreur 404
	//@param string $message 
	//@param string $oups 
	public function e404($message = 'Page introuvable', $title = '404'){

		header("HTTP/1.0 404 Not Found"); 
		$this->set('message',$message);
		$this->set('title',$title);
		$this->view = 'errors/404';

		//reset GET params
		if(!empty($_GET)) $_GET = null;

		$this->render();
		
		die();
		
	}


	//Permet de créer une variable du controller
	//@params $key : nom de la variable
	//@params $value : valeur de la variable

	public function set($key,$value=null){

		if(is_array($key)) {
			$this->vars += $key;
		}
		else {
			$this->vars[$key] = $value;
		}
	}


	//Permet de charger un model
	public function loadModel($name){ 	
		
		$classname = $name.'Model';

		if(!isset($this->$name)) {
				
			$this->$name = new $classname();

			if(isset($this->Form)){
				$this->$name->Form = $this->Form;	
			}

			if(isset($this->session)){
				$this->$name->session = $this->session;
			}
		}
		else {
			//echo 'Model '.$name.' deja chargé';
		}			
	}

	//Charge les fichiers CSS à insérer dans le <head></head>	
	public function loadCSS(){

		/**		
		* array Conf::$css , css files to load each time, define in Conf
		* array Controller $css_load, css files asked to load by the controller
		*/
		$css = array();
		if(isset(Conf::$css)){
			$css = array_merge(Conf::$css,$css);
		}
		if(isset($this->loadCSS)){
			$c = $this->loadCSS;
			if(is_string($c)) $c = array($c);
			if(is_array($c)) $css = array_merge($css,$c);
		}
		
		foreach ($css as $name => $url) {

			if(strpos($url,'http')!==0) $url = Router::webroot($url);
			echo '<link rel="stylesheet" style="text/css" href="'.$url.'" />';		
		}

	}

	//Charge les fichiers JS à insérer dans le <head></head>
	public function loadJS(){

		/**
		 * array Conf::$js , main JS file of the app
		 * array Conf::$js_dependency , dependency JS files
		 * array Controller $js_load, JS file asked to load by the controller
		 */
		$js = array();
		if(isset(Conf::$js_dependency)){
			$js = array_merge(Conf::$js_dependency,$js);
		}
		if(isset($this->loadJS)){

			if(is_string($this->loadJS)) $this->loadJS = array($this->loadJS);
			if(is_array($this->loadJS)) $js = array_merge($js,$this->loadJS);
		}
		if(isset(Conf::$js_main)){
			if(is_string(Conf::$js_main)) $js[] = Conf::$js_main;
			if(is_array(Conf::$js_main)) $js = array_merge($js,$js_main);
		}
		foreach ($js as $name => $url) {
			
			if(strpos($url,'http')===0) $url = $url;
			else $url = Router::webroot($url);		
			echo '<script type="text/javascript" src="'.$url.'"></script>';
		}
	}



	//Permet d'appeler un controller depuis une vue
	// @params 
	// $controller prefixe du Controller .ex: users
	// $action action à appeler
	public function request($controller,$action, $params = array() ){

		$controller .= 'Controller';
		//require_once ROOT.DS.'controller'.DS.$controller.'.php';
		$c = new $controller;
		$c->request = $this->request;
		$c->Form = $this->Form;
		
		return call_user_func_array(array($c,$action),$params);

	}

	//Permet d'appeler un model depuis une vue
	// @params 
	// $controller prefixe du Controller .ex: users
	// $action action à appeler
	public function requestModel($model,$action, $params = array() ){

		$model .= 'Model';
		//require_once ROOT.DS.'controller'.DS.$controller.'.php';
		$c = new $model;
		
		return call_user_func_array(array($c,$action),$params);

	}

	public function redirect($url,$code = null){

		if (headers_sent()) {
		    die("Redirect failed");
		}
		else{
			if($code == 301) {
				header("HTTP/1.1 301 Moved Permanently");
			}

			if(strpos($url,'http')===0){
				header("Location: ".$url);
				exit();
			}

			header("Location: ".Router::url($url));
		}
		exit();		
	}

	public function reload(){

		if (headers_sent()) {
		    die("Redirect failed");
		}
		else{
		    if(isset($_SERVER['HTTP_REFERER']))
				exit(header("Location: ".$_SERVER['HTTP_REFERER']));
			else
				$this->redirect('/');
		}
		
		exit();

	}


	//Tcheck if subdomain exist and launch the handleSubdomain function if exist
	private function ifSubdomainExist(){
		if($url = Conf::getParsedUrl()){
			if(!empty($url['subdomain']) && $url['domain']!='zogs'){
				if(method_exists($this,'handleSubdomain')){
					$this->handleSubdomain($url['subdomain']);
				}
			}
		}
	}

	public function getCountryCode(){


		if($this->session->getPays()){
			return $this->session->getPays();
		}
		else if($this->CookieRch->read('CC1')){
			return $this->CookieRch->read('CC1');
		}
		else {
			return Conf::$pays;
		}
	}

	public function getFlagLang( $lang = null){

		if(!$lang) $lang = $this->getLang();
		$array = array('en'=>'uk');		
		if(isset($array[$lang])) return $array[$lang];
		else return $lang;
	}

	public function getLang(){
		
		if(isset($this->request) && $this->request->get('lang')) return $this->request->get('lang');
		if(isset($this->session) && $this->session->user()->getLang()) return $this->session->user()->getLang();
		if(isset($this->CookieRch) && $this->CookieRch->read('lang')) return $this->CookieRch->read('lang');

		return Conf::$languageDefault;
	}

	public function has($property){

		if(isset($this->$property)) return $this->$property;
		else return false;
	}


	public function isHomepage(){
		
		if($this->pageName()=='pages/home') return true;
		return false;
	}

	public function pageName(){

		return $this->request->controller.'/'.$this->request->action;
	}


	//Permet de vérifier le jeton de securité
	//@params request object
	public function security($request){

		if($request->get()){

			if($request->get('token')){

				if($request->get('token')!=$this->session->read('token')){

					//$this->session->setFlash("bad token","error");
					$this->session->setFlash("<strong>Oups</strong>, il faut se reconnecter ...",'info');
					$this->redirect('users/login');
				}
				else {
					unset($this->get->token);
				}
			}
		}

		if($request->post()){

			if(!$request->post('token')){

				$this->session->setFlash("Le jeton de validation est manquant","error");
				$this->reload();
			}
			else {

				if($request->post('token')!=$this->session->read('token')){
					
					$this->session->setFlash("Votre session a expiré... Veuillez vous reconnecter","error");
					$this->redirect('users/login');
					
				}
				if($request->post('token')==$this->session->read('token')){
					unset($this->request->data->token);
				}
			}			
		}
	}

	public function sendEmails($emails,$subject,$body){

		//Si il ny a pas d'email retourne vrai
		if(empty($emails)) return true;

		//Création d'une instance de swift mailer
		if(!isset($mailer))
			$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject($subject)
		  ->setFrom(Conf::$contactEmail, Conf::$website)
		  ->setTo($emails)
		  ->setBody($body, 'text/html', 'utf-8');          
		       
		//Envoi du message et affichage des erreurs éventuelles si échoue 
		if (!$mailer->send($message, $failures))
		{
		    debug("Erreur lors de l'envoi du email à :");
		    debug($failures);            
		    return false;
		}

		return true;
	}
}
?>