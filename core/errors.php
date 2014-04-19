<?php 
class zHandlingErrors {

	public static function handler($Exception){

		$error = self::objectError($Exception);

		$IP = isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

		if(DEBUG==0){
			$controller = new Controller();
			$controller->e404('Veuillez nous excusez pour cet erreur...','Oups une erreur !');
		}

		if(DEBUG==1){
			self::alertError();
		}

		if(DEBUG==2){			
			if(in_array($IP,Conf::$debugIpAddress))
				self::showError($error);			
			else
				self::alertError();
		}

		if($IP!='127.0.0.1')
			self::emailError($error);
	
	}

	private static function objectError(Exception $e){
		$error = new stdClass();
		$error->msg = $e->getMessage();
		$error->code = $e->getCode();
		$error->line = $e->getLine();
		$error->file = $e->getFile();
		$error->context = $e->getTraceAsString();
		return $error;
	}

	public static function emailError($error){

		//Si il ny a pas d'email retourne vrai
		if(empty(Conf::$debugErrorEmails)) return true;

        $body = file_get_contents(ROOT.'/view/errors/debugmail.html');
        $body = preg_replace("~{site}~i", Conf::$website, $body);
        $body = preg_replace("~{msg}~i", $error->msg, $body);
        $body = preg_replace("~{code}~i", $error->code, $body);
        $body = preg_replace("~{line}~i", $error->line, $body);
        $body = preg_replace("~{file}~i", $error->file, $body);
        $body = preg_replace("~{uri}~i", $_SERVER['REQUEST_URI'], $body);
        $body = preg_replace("~{useragent}~i", $_SERVER['HTTP_USER_AGENT'] . ' IP:'.$_SERVER['REMOTE_ADDR'], $body);
        $body = preg_replace("~{date}~i", date("Y-m-d H:i:s"), $body);
        $body = preg_replace("~{context}~i", $error->context, $body);
        $body = preg_replace("~{post}~i",serialize($_POST),$body);

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

        //Création du mail
        $message = Swift_Message::newInstance()
          ->setSubject(date('Y-m-d').' '.$error->msg)
          ->setFrom(Conf::$contactEmail, Conf::$website)
          ->setTo(Conf::$debugErrorEmails)
          ->setBody($body, 'text/html', 'utf-8');          
       
        //Envoi du message et affichage des erreurs éventuelles si échoue 
        if (!$mailer->send($message, $failures))
        {                       
            return false;
        }

        return true;

	}

	public static function showError($error){

		include ROOT.'/view/errors/exception.php';

	}

	public static function alertError(){

		echo '<div class="zerror"><strong>Attention, une erreur a lieu !</strong> Le reste de la page est peut être incorrect...</div>';
	}
}

class zErrorException extends ErrorException {



	public function __construct($errno, $errstr, $errfile, $errline){
			
		$this->message = $errstr;
		$this->code = $errno;
		$this->line = $errline;
		$this->file = $errfile;
		zHandlingErrors::handler($this);
	}


}

class zException extends Exception {

	public function __construct($msg='Errors class default message' ,$code = 0, Exception $previous = NULL){

		$this->message = $msg;
		$this->code = $code;
		$this->previous = $previous;
		zHandlingErrors::handler($this);
			
	}

} 



?>