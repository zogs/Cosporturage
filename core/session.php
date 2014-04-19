<?php 
class Session {

	function __construct($controller){

		$this->controller = $controller;

		if(!isset($_SESSION)){
			session_start();


			if(!isset($_SESSION['token'])){
				$this->setToken();
			}

			if(!isset($_SESSION['user'])){

				$user = new User();
				$user->setlang($this->get_client_language(array_keys(Conf::$languageAvailable,Conf::$languageDefault)));
				$this->write('user',$user);

			}

			//destroy session if user in not from User class
			if(!$_SESSION['user'] instanceof User){

				if ( isset( $_COOKIE[session_name()] ) ){
					setcookie( session_name(), '', time()-3600, '/' );
				}
				$_SESSION = array();
				session_destroy();
			}		
			
		}
		
	}

	public function setToken(){

		if(!isset($_SESSION['token'])){
			$_SESSION['token'] = md5(time()*rand(111,777));	
		}
	}

	public function token(){

		return $this->read('token');
	}

	public function setFlash($message, $type = 'success', $duration = 0){

		$flash = array('message'=>$message,'type'=>$type,'duration'=>$duration);

		if(isset($_SESSION['flash'])){
			array_push($_SESSION['flash'],$flash);			
		}
		else {
			$_SESSION['flash'] = array($flash);
		}
		
	}

	public function flash(){

		$icons = array('success'=>'<span class="ws-icon-checkmark"></span>',
				'info'=>'<span class="ws-icon-info"></span>',
				'warning'=>'<span class="ws-icon-spam"></span>',
				'error'=>'<span class="ws-icon-blocked"></span>',
				'danger'=>'<span class="ws-icon-blocked"></span>');

		if(isset($_SESSION['flash'])){
			$html='';
			$html.='<div class="flash">';
			foreach($_SESSION['flash'] as $v){
				$v['duration'] = 0; //no css animation
				if(isset($v['message'])){
					$html .= '<div class="ws-alert alert-'.$v['type'].' alert-hide-'.$v['duration'].'s">
								<span class="flash-icon">'.$icons[$v['type']].'</span>
								<div class="alert-progress alert-progress-'.$v['duration'].'s"></div>							
								<p>'.$v['message'].'</p>								
								<button class="close" data-dismiss="alert"><span class="ws-icon-close"></span></button>
							</div>';													
				}
			}
			$html .= '</div>';
			$_SESSION['flash'] = array();
			return $html;
		}
	}

	public function write($key,$value){
		$_SESSION[$key] = $value;
	}


	public function read($key = null){

		if($key){

			if(!empty($_SESSION[$key])){
				return $_SESSION[$key];		
			}			
			else{

				return false;			}
		}
		else{
			return $_SESSION;
		}
	}

	public function role(){
		return isset($_SESSION['user']->statut); 

	}

	public function isLogged(){
		if($this->user('user_id')!=0)
			return true;		
	}

	public function allow($statuts){

		if(in_array($this->user('statut'),$statuts))
			return true;
		else
			$this->controller->e404('Vous n\'avez pas les droits pour voir cette page');

	}

	public function noUserLogged(){

		$params = new stdClass();
		$params->user_id = 0;
		return $params;
	}

	// public function user($key = null){

	// 	if($this->read('user')){

	// 		if($key){

	// 			if($key=='obj'){
	// 				return $this->read('user');
	// 			}

	// 			$val = trim($this->read('user')->$key);

	// 			if(!empty($val)){

	// 				return $val;
	// 			}
	// 			else 
	// 				return false;				
	// 		}	

	// 		else {
	// 			return $this->isLogged();
	// 		}
	// 	}
	// 	else 
	// 	{

	// 		if( $key == 'user_id' )
	// 			return 0;
	// 		else			
	// 			return false;
	// 		if( $key == 'statut' )
	// 			return 'visitor';
	// 		else
	// 			return false;
	// 	}
	// }

	public function user(){

		return $this->read('user');
	}

	public function user_id(){

		if( $this->read('user') ){
			return $this->user('user_id');
		}
		else return 0;
	}
	public function getLang(){
		if(isset($this->read('user')->lang) && $this->read('user')->lang!='')
			return $this->read('user')->lang;		
		else 
			return Conf::$languageDefault;		
	}

	public function getPays(){
		if(isset($this->read('user')->pays))
			return $this->read('user')->pays;		
		else 
			return Conf::$pays;
	}

	public function get_client_language($availableLanguages, $default='fr'){     	     		
	   
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	     
		    $langs=explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		     
		    //start going through each one
		    foreach ($langs as $value){
		     
			    $choice=substr($value,0,2);
			    if(in_array($choice, $availableLanguages)){
			    	return $choice;
			     
			    }
		     
		    }
	    }
	    return $default;
    }

    public function getOnlineUsers(){ // http://www.devarticles.com/c/a/PHP/The-Quickest-Way-To-Count-Users-Online-With-PHP/1/#sthash.cHfuOYb9.dpuf

    	$max_online_time = 5; //temps qu'une session est active en minute

		if ( $directory_handle = opendir( session_save_path() ) ) {			
			$count = 0;
			while ( false !== ( $file = readdir( $directory_handle ) ) ) {
				if($file != '.' && $file != '..'){
				
					// Comment the 'if(...){' and '}' lines if you get a significant amount of traffic
					if(time()- filemtime(session_save_path() . DS . $file) < $max_online_time * 60) {
					$count++;
					}
				}
			}
			closedir($directory_handle);
			return $count;

		} 
		else {
		return false;
		}

	} 

		    
}

?>