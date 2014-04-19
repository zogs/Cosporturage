<?php

class Cron{

	public $url; 
	public $page = 1; 
	public $prefix =false; 
	public $data = false; 
	public $get = false;



	//Permet de récupérer la requete url demandé
	function __construct( $controller , $action ) {
		
		$this->url = $controller.'/'.$action;
		//$this->url = str_replace(BASE_URL."/", "", $_SERVER['REQUEST_URI']); //Recuperation du PATH_INFO 		
	}

	public function get(){
		return false;
	}

	public function post(){
		return false;
	}	

}