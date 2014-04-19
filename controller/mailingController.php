<?php

class MailingController extends Controller {

	private static $mailBody  = array();
	
	public function admin_index(){

		$this->loadModel('Mailing');
		$mailings = $this->Mailing->findMailing();

		$d['mailings'] = $mailings;
		$this->set($d);
	}

	public function admin_listmailing(){

		$this->loadModel('Mailing');

		$lists = $this->Mailing->findMailingList();

		$this->set('lists',$lists);

	}

	public function admin_deleteEmail($lid,$eid){

		$this->loadModel('Mailing');

		if($this->Mailing->deleteEmail($eid)){
			$this->session->setFlash('Email supprimé');
		}

		$this->redirect('admin/mailing/editlist/'.$lid);
	}

	public function admin_deletelist($lid){

		$this->loadModel('Mailing');

		if($this->Mailing->deleteList($lid)){
			$this->session->setFlash('Liste supprimé');
		}

		$this->redirect('admin/mailing/listmailing');
	}

	public function admin_editlist($lid = null){

		$this->loadModel('Mailing');

		if($data = $this->request->post()){

			if($this->Mailing->validates($data,'editlist')){

				if($lid = $this->Mailing->saveMailingList($data)){

					$this->session->setFlash('La mailing list a bien été enregistré !');
					$this->redirect('admin/mailing/editlist/'.$lid);
				}
				else {
					$this->session->setFlash('Error save mailing list','error');
				}
			}
		}

		if($lid){

			$list = $this->Mailing->getListByID($lid);
			$list->users = $this->Mailing->getEmailsByListID($lid);
			$emails = '';
			foreach ($list->users as $u) {
				$emails .= $u->email.';';				
			}
			$list->emails = $emails;
		}
		else{
			$list = new stdClass();
		}


		$this->request->data = $list;

		$this->set('list',$list);

	}

	public function admin_editmailing($mid = null){

		$this->loadModel('Mailing');

		if($data = $this->request->post()){

			if($data = $this->Mailing->validates($data,'editmailing')){

				$new = new stdClass();
				$new->table = 'mailing_sending';
				$new->date_created = Date::MysqlNow();					

				if(!empty($data->list_id)){
					$new->mailinglist_id = $data->list_id;
				}
				if(!empty($data->emails_added)){
					$new->emails_added = $data->emails_added;
				}
				if(!empty($data->title)){
					$new->title = $data->title;
				}
				if(!empty($data->object)){
					$new->object = $data->object;
				}
				if(!empty($data->content)){
					$new->content = $data->content;
				}
				if(!empty($data->method)){
					$new->method = $data->method;
				}
				if(!empty($_FILES['addpj']['name'])){					
					if($path = $this->Mailing->saveFile('addpj')){
						$new->path = WEBROOT.DS.$path;					
					}
				}
				if(!empty($data->grouped)){
					$new->grouped = $data->grouped;
				}
				if(!empty($data->recipients)){
					$new->recipients = $data->recipients;
				}
				if(!empty($data->signature)){
					$new->signature_id = $data->signature;
				}

				//update if exist
				if(!empty($data->id)){
					$new->id = $data->id;
					$new->key = 'id';
					$new->date_created = '';
				}

				if($id = $this->Mailing->save($new)){
					$this->redirect('admin/mailing/index');
				}

			}
		}

		//find existing mailing
		$mailing = $this->Mailing->getMailingById($mid);
		//find mailing list
		$mailinglists = $this->Mailing->findMailingList();
		$selectLists = array();
		foreach ($mailinglists as $key => $l) {
			$selectLists[$l->list_id] = $l->name;
		}		
		//set form data
		$this->request->data = $mailing;

		//find signatures
		$signatures = $this->Mailing->findSignatures();
		$a = array();
		foreach ($signatures as $k => $v) {
			$a[$v->id] = $v->name;
		}
		$this->set('signatures',$a);
		$this->set('mailingLists',$selectLists);
		$this->set('mailing',$mailing);
	}

	public function admin_deletemailing($mid){

		$this->loadModel('Mailing');

		if($this->Mailing->deleteMailing($mid)){
			$this->session->setFlash('Mailing supprimé');
		}

		$this->redirect('admin/mailing/index');
	}

	public function admin_editsignature(){

		$this->loadModel('Mailing');

		if($data = $this->request->post()){

			if($data = $this->Mailing->validates($data,'editsignature')){

				$new = new stdClass();
				$new->table = 'mailing_signature';
				$new->name = $data->name;

				foreach ($data as $field => $value) {					
					if(preg_match('/content/',$field)){
						$new->content = $value;
					}
				}

				if(!empty($data->id)){
					$new->key = 'id';
					$new->id = $data->id;
				}				

				if($this->Mailing->save($new)){
					$this->session->setFlash("Signature sauvegardé","success");
				}
			}
		}

		$this->request->data = '';

		$s = $this->Mailing->findSignatures();

		$this->set('signatures',$s);
	}


	public function admin_launchmailing($mid){

		$this->loadModel('Mailing');

		$mailing = $this->Mailing->getMailingById($mid);

		if(!$mailing->exist()) $this->e404('Ce mailing n\'existe pas');

		if($mailing->getMethod() == 'allinone'){

			$this->sendAllInOne($mailing);
		}

		if($mailing->getMethod() == 'cron'){

			$this->setByCron($mailing);
		}

		if($mailing->getMethod() == 'refresh'){

			$this->setByRefresh($mailing);
		}

	}

	private function setByRefresh($mailing){

		$this->loadModel('Mailing');

		//get emails of the mailing
		$emails = $this->getEmailsForMailing($mailing);

		//save emails to send in the db
		$this->Mailing->saveMailToSend($emails,$mailing->id,'refresh');

		//update status of the mailing
		$mailing->status = 'current';
		$mailing->date_sended = Date::MysqlNow();
		if($this->Mailing->saveMailing($mailing))
			$this->session->setFlash('Le mailing est en cours de traitement. Ne pas fermer la fenetre','success');
		
		//redirect on index
		$this->redirect('admin/mailing/sendByRefresh/'.$mailing->id.'/'.$this->session->token());

	}


	public function admin_sendByRefresh($mid,$token){

		$this->loadModel('Mailing');
		$this->view ='mailing/admin_refreshwaitingroom';

		if($token!=$this->session->token()) exit('false token');
		if(!empty($mid) && !is_numeric($mid)) exit('wrong mid');


		$timer = microtime(true);

		$m = $this->Mailing->getMailingById($mid);

		//if no mailing exit
		if(empty($m)) {
			$res = date('Y-m-d').': no mailing to send';
		}

		//find emails to send
		$tosend = $this->Mailing->findEmailsToSendByMailingId($m->id,$m->grouped);

		//if no mailing
		if(empty($tosend)){
			$this->redirect('admin/mailing/index');			
		}

		//format array emails
		$emails = array();
		foreach ($tosend as $k => $v) {
			$emails[$v->id] = unserialize($v->email);
		}

		//if no email
		if(empty($emails)){
			//update as finished
			$m->date_finished = Date::MysqlNow();
			$m->status = 'finished';
			$m->finished = 1;
			$this->Mailing->saveMailing($m);
			continue;
		}

		//get signature
		$sign = $this->Mailing->getSignatureById($m->signature_id);
		
		//récupère le template et remplace les variables
		$body = file_get_contents(ROOT.'/view/email/freeMailing.html');
		$body = preg_replace("~{content}~i", $m->content, $body);
		$body = preg_replace("~{site}~i", Conf::getSiteUrl(), $body);
		$body = preg_replace("~{signature}~i",$sign, $body);

		$body = preg_replace("~{mailing_name}~i",Conf::$website.'-mailing_-_'.String::slugify($m->title), $body);
		$body = preg_replace("~{mailing_count}~i",count($emails).'_sended', $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		 ->setSubject($m->object)
		 ->setFrom(Conf::$contactEmail,Conf::$websiteDOT)
		 ->setBody($body, 'text/html', 'utf-8');

		//attach pj
		 if(!empty($m->path)){
		  	$pj = Swift_Attachment::FromPath($m->path);
		  	$message->attach($pj);
		}

		//sending varialbe
		$sending = array();
		$sending['sended'] = array();
		$sending['errors'] = array();
		$sending['total'] = 0;

		//make group of recipients
		$groups = array_chunk($emails, $m->recipients, true);
	
		//send each groups
		foreach ($groups as $emails) {
				
			if(!$failures = $this->sendMail(array($emails),$message)){
				$sending['errors'] = array_merge($sending['errors'],$failures);
			}
			else{
				$sending['sended'] = array_merge($sending['sended'],$emails);
			}

			//mark the emails as sended
			foreach ($emails as $id => $email) {
				$this->Mailing->markEmailAsSended($id);
				$sending['total']++;
			}				
		}

		//varialbe
		$nbSuccess = count($sending['sended']);
		$nbError = count($sending['errors']);
		$timer = round(microtime(true) - $timer,5);


		//update mailing
		$m->total_count = $m->total_count + $sending['total'];
		$m->total_success = $m->total_success + $nbSuccess;
		$m->total_error = $m->total_error + $nbError;
		$m->emails_failed = $m->emails_failed.' '.implode(',',$sending['errors']);			
		$m->duration = $m->duration + $timer;		
		$this->Mailing->saveMailing($m);					
		
		
		//if finished
		if($this->Mailing->isNoMoreMailToSendForMailing($m->id)){

			//update as finished
			$m->date_finished = Date::MysqlNow();
			$m->status = 'finished';
			$m->finished = 1;
			$this->Mailing->saveMailing($m);
		
			//delete from the db the mail to send
			$this->Mailing->deleteMailSendedByMailingId($m->id);
			
		}			

		$rest = $this->Mailing->findNumberRestRefreshMailing($m->id);
	
		//if no rest
		if(empty($rest)){
			//update as finished
			$m->date_finished = Date::MysqlNow();
			$m->status = 'finished';
			$m->finished = 1;
			$this->Mailing->saveMailing($m);
			$this->redirect('admin/mailing/index');
		}


		$this->session->setFlash('Cet page va se rafraichir dans 60 secondes. Ne pas fermer la fenetre tant que le mailing n\'est pas terminé');	

		$this->session->setFlash('Il reste '.$rest.' email à envoyer','info');		


	}



	private function setByCron($mailing){

		$this->loadModel('Mailing');

		//get emails of the mailing
		$emails = $this->getEmailsForMailing($mailing);

		//save emails to send in the db
		$this->Mailing->saveMailToSend($emails,$mailing->id,'cron');

		//update status of the mailing
		$mailing->status = 'current';
		$mailing->date_sended = Date::MysqlNow();
		if($this->Mailing->saveMailing($mailing))
			$this->session->setFlash('Le mailing sera envoyé par la tâche Cron du server','success');
		
		//redirect on index
		$this->redirect('admin/mailing/index');

	}


	public function sendByCron(){

		//only if cron
		if(get_class($this->request)!='Cron') exit();

		//load
		$this->loadModel('Mailing');

		//set timer
		$timer = microtime(true);

		//set return result
		$res = '';

		//find mailing to send
		$mailing = $this->Mailing->findMailingToSendByCron();

		//if no mailing exit
		if(empty($mailing)) {
			$res = date('Y-m-d').': no mailing to send';
		}

		//variable
		$total = array();
		$total['sending'] = 0;
		$total['errors'] = array();
		$total['sended'] = array();

		//create message for each mailing
		foreach ($mailing as $m) {

			//find emails to send
			$tosend = $this->Mailing->findEmailsToSendByMailingId($m->id,$m->grouped);

			//if no mailing
			if(empty($tosend)){
				$res = date('Y-m-d').': No email to send';
				continue;
			}

			//format array emails
			$emails = array();
			foreach ($tosend as $k => $v) {
				$emails[$v->id] = unserialize($v->email);
			}

			//if no email
			if(empty($emails)){
				//update as finished
				$m->date_finished = Date::MysqlNow();
				$m->status = 'finished';
				$m->finished = 1;
				$this->Mailing->saveMailing($m);
				continue;
			}

			//make group of recipients
			$groups = array_chunk($emails, $m->recipients, true);

			//get signature
			$sign = $this->Mailing->getSignatureById($m->signature_id);

			//récupère le template et remplace les variables
			$body = file_get_contents(ROOT.'/view/email/freeMailing.html');
			$body = preg_replace("~{content}~i", $m->content, $body);
			$body = preg_replace("~{site}~i", Conf::getSiteUrl(), $body);
			$body = preg_replace("~{signature}~i", $sign->content, $body);

			$body = preg_replace("~{mailing_name}~i",Conf::$website.'-mailing_-_'.String::slugify($m->title), $body);
			$body = preg_replace("~{mailing_count}~i",count($emails).'_sended', $body);

			//Création du mail
			$message = Swift_Message::newInstance()
			 ->setSubject($m->object)
			 ->setFrom(Conf::$contactEmail,Conf::$websiteDOT)
			 ->setBody($body, 'text/html', 'utf-8');

			//attach pj
			 if(!empty($m->path)){
			  	$pj = Swift_Attachment::FromPath($m->path);
			  	$message->attach($pj);
			}

			//sending varialbe
			$sending = array();
			$sending['sended'] = array();
			$sending['errors'] = array();
			$sending['total'] = 0;

			//send each groups
			foreach ($groups as $emails) {
					
				if(!$failures = $this->sendMail(array($emails),$message)){
					$sending['errors'] = array_merge($sending['errors'],$failures);
					$total['errors'] = array_merge($total['errors'],$failures);
				}
				else{
					$sending['sended'] = array_merge($sending['sended'],$emails);
					$total['sended'] = array_merge($total['sended'],$emails);
				}

				//mark the emails as sended
				foreach ($emails as $id => $email) {
					$this->Mailing->markEmailAsSended($id);
					$sending['total']++;
					$total['sending']++;
				}				
			}

			//varialbe
			$nbSuccess = count($sending['sended']);
			$nbError = count($sending['errors']);
			$timer = round(microtime(true) - $timer,5);


			//update mailing
			$m->total_count = $m->total_count + $sending['total'];
			$m->total_success = $m->total_success + $nbSuccess;
			$m->total_error = $m->total_error + $nbError;
			$m->emails_failed = $m->emails_failed.' '.implode(',',$sending['errors']);			
			$m->duration = $m->duration + $timer;		
			$this->Mailing->saveMailing($m);					
			
			
			//if finished
			if($this->Mailing->isNoMoreMailToSendForMailing($m->id)){

				//update as finished
				$m->date_finished = Date::MysqlNow();
				$m->status = 'finished';
				$m->finished = 1;
				$this->Mailing->saveMailing($m);
			
				//delete from the db the mail to send
				$this->Mailing->deleteMailSendedByMailingId($m->id);
				
			}			
		}

		$res .= date('Y-m-d').':';
		$res .= ' La tache cron denvoi de mailing a envoyé '.$total['sending'].' emails';
		$res .= ' ERRORS:'.count($total['errors']).' '.implode(',',$total['errors']);
		$res .= ' SUCCESS:'.count($total['sended']).' '.implode(',',$total['sended']);

		exit($res);

	}


	private function sendMail($emails = array(), $message){		

		if(count($emails)==1){
			if(!is_array($emails[0]))
				$message->setTo($emails);
			else
				$message->setTo($emails[0]);	
		}
		else {
			foreach ($emails as $name => $address) {
				if(is_int($name))
					$message->addTo($address);
				else
					$message->addTo(array($address => $name));			
			}		
		}

		if (!Conf::getMailer()->send($message, $failures))
		  	return $failures;
		else 
			return true;
	}



	private function sendAllInOne($mailing){

		$this->loadModel('Mailing');

		//set timer
		$timer = microtime(true);

		//get emails
		$emails = $this->getEmailsForMailing($mailing);

		//get signature
		$sign = $this->Mailing->getSignatureById($mailing->signature_id);
		//creating message
		//récupère le template et remplace les variables
		$body = file_get_contents('../view/email/freeMailing.html');
		$body = preg_replace("~{content}~i", $mailing->content, $body);
		$body = preg_replace("~{site}~i", Conf::getSiteUrl(), $body);
		$body = preg_replace("~{signature}~i", $sign, $body);

		$body = preg_replace("~{mailing_name}~i",Conf::$website.'-mailing_-_'.String::slugify($mailing->title), $body);
		$body = preg_replace("~{mailing_count}~i",count($emails).'_sended', $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		 ->setSubject($mailing->object)
		 ->setFrom(Conf::$contactEmail,Conf::$websiteDOT)
		 ->setBody($body, 'text/html', 'utf-8');

		 //attach pj
		  if(!empty($mailing->path)){
		  	$pj = Swift_Attachment::FromPath($mailing->path);
		  	$message->attach($pj);
		  }

		//sending message
		$results = array();
		$results['sended'] = array();
		$results['errors'] = array();
		$total = 0;

		//set time mailing started
		$mailing->date_sended = Date::MysqlNow();

		//make group of recipients
		$emailsNbRecipients = array_chunk($emails, $mailing->recipients);

		foreach ($emailsNbRecipients as $emails) {

			if(!$failures = $this->sendMail($emails,$message)){
				$results['errors'] = array_merge($results['errors'],$failures);
			}
			else{
				$results['sended'] = array_merge($results['sended'],$emails);
			}

			$total++;
			//sleep(Conf::$mailingTimeBetween2Sending);
		}				

		//variable
		$nbSuccess = count($results['sended']);
		$nbError = count($results['errors']);
		$timer = round(microtime(true) - $timer,5);

		//set flash session
		$this->session->setFlash(count($results['sended']).' emails envoyés en '.$timer.' secondes');
		if(!empty($results['errors'])) $this->session->setFlash(count($results['errors']). ' erreurs d\'envoi... ('.implode(' ; ',$results['errors']).')');
		
		//update mailing
		$mailing->total_count = $total;
		$mailing->total_success = $nbSuccess;
		$mailing->total_error = $nbError;
		$mailing->emails_failed = implode(',',$results['errors']);
		$mailing->date_finished = Date::MysqlNow();
		$mailing->status = 'finished';
		$mailing->duration = $timer;		

		//save mailing
		if($this->Mailing->saveMailing($mailing)){
			$this->redirect('admin/mailing/');
		}

	}



	private function getEmailsForMailing($mailing){

		//get emails
		$emails = $this->Mailing->getEmailsByListID($mailing->getMailingListId());
		$emails = array_merge($emails,String::findEmailsInString($mailing->emails_added));

		//format emails array
		foreach ($emails as $k => $v) {
			if(is_object($v)){
				$emails[$k] = $v->email;
				if(!empty($v->prenom) || !empty($nom))
					$emails[$k] = array($v->email => $v->prenom.' '.$v->nom);
			}
			elseif(is_string($v)){
				$emails[$k] = $v;
			}
		}

		return $emails;
	}

	public function admin_freemailing(){

		$this->loadModel('Mailing');

		$timer = microtime(true);

		if($data = $this->request->post()){

			if($data = $this->Mailing->validates($data,'freemailing')){

				$emails = array();
				$content = '';
				$title = '';
				$path = '';

				if(!empty($data->list_id)){
					$e = $this->Mailing->getEmailsByListID($data->list_id);
					foreach ($e as $k => $v) {
						$e[$k] = $v->email; 
					}
					$emails = array_merge($emails,$e);
				}

				if(!empty($data->emails)){

					$e = String::findEmailsInString($data->emails);
					$emails = array_merge($emails,$e);
				}

				if(!empty($data->title)){
					$title = $data->title;
				}

				if(!empty($data->content)){

					$content = $data->content;
				}
				
				if(!empty($_FILES['pj']['name'])){

					if($path = $this->Mailing->saveFile('pj')){

						$path = WEBROOT.DS.$path;
					}

				}
				
				//creating message
				//récupère le template et remplace les variables
				$body = file_get_contents('../view/email/freeMailing.html');
				$body = preg_replace("~{content}~i", $content, $body);
				$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
				$body = preg_replace("~{contact}~i", Conf::$congressContactEmail, $body);
				$body = preg_replace("~{title}~i", $title, $body);

				//Création du mail
				$message = Swift_Message::newInstance()
				 ->setSubject($title)
				 ->setFrom(Conf::$congressContactEmail,Conf::$congressName)
				 ->setBody($body, 'text/html', 'utf-8');

				 //attach pj
				  if(!empty($path)){
				  	$pj = Swift_Attachment::FromPath($path);
				  	$message->attach($pj);
				  }

				//sending message
				$results = array();
				$results['sended'] = array();
				$results['errors'] = array();
				//make group of recipients
				$emailsNbRecipients = array_chunk($emails, Conf::$mailingNbRecipients);

				foreach ($emailsNbRecipients as $emails) {

					if(!$failures = $this->sendMail($emails,$message)){
						$results['errors'] = array_merge($results['errors'],$failures);
					}
					else{
						$results['sended'] = array_merge($results['sended'],$emails);
					}

					sleep(Conf::$mailingTimeBetween2Sending);
				}				

				if(!empty($results['sended'])){
					$this->session->setFlash(count($results['sended']).' emails envoyés ! ','success');
				}

				if(!empty($results['errors'])){
					$this->session->setFlash(count($results['errors']). ' erreurs d\'envoi... ('.implode(' ; ',$results['errors']).')');
				}

				$this->session->setFlash('Envoi effectué en '.round(microtime(true) - $timer,5).' secondes','warning');
				
			}

		}

		//data for the page
		$lists = $this->Mailing->findMailingList();
		$selectLists = array();
		foreach ($lists as $key => $l) {
			$selectLists[$l->list_id] = $l->name;
		}

		$this->set('selectLists',$selectLists);
	}


	




}