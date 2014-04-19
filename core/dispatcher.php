<?php

class Dispatcher{

	var $request;

	function __construct( $request ) {

		//Intanciation d'un objet requete
		$this->request = $request;

		//Appel de la class Router pour decortiquer la requete url
		Router::parse($this->request->url,$this->request);

		//Appel de la methode loadController
		$controller = $this->loadController();
		$action = $this->request->action;
		
		
		
		//Si le prefixe d'action est admin, on change l'action en admin_action
		if(isset($this->request->prefix) && $this->request->prefix == 'admin'){
			$action = $this->request->prefix.'_'.$action;
		}
		
		 //Si l'action demandé n'est pas une methode du controlleur on renvoi sur error()
		if(!in_array($action,array_diff(get_class_methods($controller),get_class_methods('Controller')))){
			$this->error("Le controller ".$this->request->controller." n'a pas de méthode ".$action);
		}
			
		//Appel de la methode demandé sur le controller demandé
		call_user_func_array(array($controller,$action),$this->request->params);


		//On change le layout en fonction des prefixes (optionnel)
		if(isset($this->request->prefix)){
			if($this->request->prefix == 'nolayout' ){
				$controller->layout = 'none';
			}
			if($this->request->prefix == 'json' ){
				$controller->layout = 'none';
				$controller->view = 'json';
			}

		}

		//Appel le rendu du controlleur Auto rendering
		$controller->render($action);
		
	}

 
	// Permet d'inclure le bon controlleur
	function loadController() {
		
		//nom du controller
		$name = ucfirst($this->request->controller).'Controller'; //On recupere le nom du controller ( en lui mettant une majuscule ^^)

		//autoload du controller
		try{ //try to get the controller
			$controller =  new $name($this->request); //retourne une instance du bon controleur ( representé par le $name ! )			
		} catch(Exception $e){ //if controller class dont exist
			$this->error("La page est introuvable"); //error 404
		}
		
		return $controller;
	}

	//Renvoi un controlleur error
	function error($message){

		$controller = new Controller($this->request);
		$controller->e404($message);

	}
}