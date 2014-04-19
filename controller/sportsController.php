<?php

class SportsController extends Controller {



		public function admin_index(){

			$this->loadModel('Sports');


			$sports = $this->Sports->findSports('fr');

			$this->set(array('sports'=>$sports));

		}

		public function admin_view($id = 0){

			$this->loadModel('Sports');

			$sport = $this->Sports->findSportById($id);

			$this->request->data = $sport;

			$this->set(array(
				'sport'=>$sport
				));
		}

		public function admin_edit(){

			$this->view = '';
			$this->loadModel('Sports');

			if($data = $this->request->post()){

				if($data = $this->Sports->validates($data,'edit')){

					if($data = $this->Sports->saveSport($data))
						$this->session->setFlash("Sport sauvegardé !");
					else
						$this->session->setFlash("Erreur","error");

					
				}
			}

			$this->redirect('admin/sports/index');
		}


		public function admin_delete(){

			$this->view = '';
			$this->loadModel('Sports');

			if($data = $this->request->post()){

				if($data = $this->Sports->validates($data,'delete')){

					if($data = $this->Sports->deleteSport($data))
						$this->session->setFlash("Sport Supprimé !");
					else
						$this->session->setFlash("Erreur","error");

					
				}
			}

			$this->redirect('admin/sports/index');
		}

}

?>