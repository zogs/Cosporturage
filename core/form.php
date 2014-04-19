<?php 
class Form{


	public $controller;
	private $errors = array();

	public function __construct($controller){

		$this->controller = $controller;

	}

	public function setErrors($errors){

		$this->errors = $errors;
		
	}

	public function wrapInput($id, $label, $input, $params = null){		

		//Gestion des erreurs
		$error = false;
		$classError ='';

		//Si il y a une erreur qui correspond a ce champ
		if(isset($this->errors[$id])){
			
			$error = $this->errors[$id]; // recup l'erreur
			$classError = 'control-error'; // défini la class css d'une erreur
		}

		$groupclass = '';
		if(isset($params['group-class'])) $groupclass = $params['group-class'];

		//If hidden input only return input
		if($label=='hidden') return $input;

		//if submit button return input
		if($label=='submit') return $input;

		//Bootstrap html
		$html = '<div class="control-group '.$groupclass.' '.$classError.'" id="control-'.$id.'">';

		//add label if different of NULL
		if($label!='NULL')
			$html .= '<label class="control-label">'.$label.'</label>';			
		
		$html .= '<div class="controls">';

		//Icon
		if(isset($params['icon']))
			$html .= "<i class='icon-form ".$params['icon']."'></i>";

		//Input code
		$html .= $input;	

		//Error helper
		if($error) {
			$html .= '<p class="help-inline help-error">'.$error.'</p>';	
		}		

		//Info helper	
		if(isset($params['helper']))
			$html .= '<p class="help-inline">'.$params['helper'].'</p>';				
		else
			$html .= '<p class="help-inline hide"></p>';	

		$html .= '</div>';
		$html .= '</div>';

		return $html;


	}


	public function Input($id,$label,$params = null){

		return $this->wrapInput($id,$label,$this->_input($id, $label, $params),$params);
	}



	public function _input($id,$label,$options = array()){

		$html ='';

		//Gestion des erreurs
		$error = false;
		$classError ='';

		//Si il y a une erreur qui correspond a ce champ
		if(isset($this->errors[$id])){
			$error = $this->errors[$id]; // recup l'erreur
			$classError = 'error'; // défini la class css d'une erreur
		}

		
		if(!empty($options['value'])){
			$value = $options['value'];
		}
		elseif(!isset($this->controller->request->data->$id)){ //Si le champ n'a pas de valeur pré-rempli
			$value='';
		}
		else{ //Sinon on recupere la valeur dans les données passé en post dans l'objet request
			$value = $this->controller->request->data->$id;
		}



		//Attribut html à rajouter en fin de champs	
		$attr='';	
		if(!empty($options)){

			//helper not useful here
			unset($options['helper']);	

			//auto placeholder if not set
			if(!isset($options['placeholder'])) $attr.= ' placeholder="'.$label.'" ';

			//add options
			foreach ($options as $k => $v) {
				if($k!='type' && $k!='icon'){
					$attr .= " $k=\"$v\"";
				}
			}
		}


		//return if submit button
		if($label=='submit'){

			if(isset($options['class'])) $class = $options['class'];
			else $class = '';
			return '<input type="submit" value="'.$id.'" class="'.$class.'" '.$attr.'/>';
		}

		//return if hidden button
		if($label=='hidden'){

			return '<input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$value.'" '.$attr.'/>';
		}		

		
		//Type de input
		if(!isset($options['type'])){
			$html .= '<input type="text" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='email'){
			$html .= '<input type="email" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='url'){
			$html .= '<input type="url" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='textarea'){
			$html .= '<textarea id="'.$id.'" name="'.$id.'" '.$attr.'>';
			if(isset($value)) $html .= $value;
			$html .='</textarea>';
		}
		elseif($options['type']=='checkbox'){
			$html .= '<input type="hidden" name="'.$id.'" value="0"><input type="checkbox" name="'.$id.'" value="1" '.(!empty($value)? 'checked' : '').' '.$attr.' />';
		}
		elseif($options['type']=='file'){
			$html .= '<input type="file" class="input-file" id="'.$id.'" name="'.$id.'" '.$attr.'>';

			if( !empty($options['src']) && $options['src']!=''){

				$html .= '<div class="input-file-thumbnail"><img src="'.$options['src'].'" /></div>';
			}
		}
		elseif($options['type']=='password'){
			$html .= '<input type="password" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='submit'){
			$html .= '<input type="submit" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		else{
			$html .= '<input type="'.$options['type'].'" id="'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		
		return $html;
	}



	public function Select($id,$label,$options,$params){

		$html = $this->wrapInput($id,$label,$this->_select($id,$options,$params),$params);

		return $html;
	}


	public function SelectNumber( $id, $label, $start = 0, $end = 10, $params){

		$html = $this->wrapInput($id, $label,$this->_selectNumber($id,$start,$end,$params),$params);

		return $html;
	}


	public function _selectNumber( $id, $start = 0, $end = 10, $params = null){

		(isset($params['class']))? $class = $params['class'] : $class = '' ;
		(isset($params['default']))? $selected = $params['default'] : $selected = '';
		(isset($params['placeholder']))? $placeholder = $params['placeholder'] : $placeholder = '';
		(isset($params['javascript']))? $javascript = $params['javascript'] : $javascript = '';
		(isset($params['style']))? $style = $params['style'] : $style = '';

		$years = array();

		if($start<$end) {
			for( $i=$start; $i<=$end; $i++){
				$years[$i] = $i;
			}
		}
		elseif($start>$end) {
			for( $i=$start; $i>=$end; $i--){
				$years[$i] = $i;
			}
		}

		return $this->_select($id,$years,$params);

	}

	/** _SELECT
	* Genere un code html <select...> avec <option></option...>
	*
	* @param $id l'id du champ select
	* @param $class la class du champ select
	* @param $options tableaux des options : array('code'=>'name')
	* @param $selected valeur dont le champ est selected
	* @param $javascript (facultatif)
	* @return $html code html
	*/ 
	public function _select($id,$options,$params){

		(isset($params['class']))? $class = $params['class'] : $class = '' ;
		(isset($params['default']))? $selected = $params['default'] : $selected = '';
		(isset($params['placeholder']))? $placeholder = $params['placeholder'] : $placeholder = '';
		(isset($params['javascript']))? $javascript = $params['javascript'] : $javascript = '';
		(isset($params['style']))? $style = $params['style'] : $style = '';
		(isset($params['icon']))? $icon = $params['icon'] : $icon = '';

		if(!empty($options))
		{

			$html = '';
			
			if(!empty($params['icon'])) $html .= '<i class="icon-form '.$params['icon'].'"></i>';

			$html .='<select id="'.$id.'" name="'.$id.'" class="'.$class.'" style="'.$style.'" '.$javascript.' >';
			$html .= '<option value=" ">'.$placeholder.'</options>';
			
			if(is_object($options)) $options = (array) $options;

			foreach ($options as $key => $line) {
					
					if(is_object($line)) $line = (array) $line;

					if(is_array($line)){

						$keys = array_keys($line);
						$value = $line[$keys[0]];
						$name = $line[$keys[1]];
					}
					else {
						$value = $key;
						$name = $line;
					}
				
					if(isset($selected)&&$selected==$value) $if_selected = 'selected="selected"';
					else $if_selected = '';

					$html .='<option value="'.$value.'" '.$if_selected.'>'.$name.'</option>';								
				
			}
			$html .='</select>';
			return $html;
		}
		else return false;


	}

	/** OPTIONS
	* Genere un code html <option></option...>
	*
	* @param $options tableaux associatifs des options : array('code'=>14,'nom'=>Cal)
	* @param $value nom de la valeur propre au tableau
	* @param $name nom de l'option propre au talbeau
	* @param $selected valeur dont le champ est selected
	* @return $html code html ou flase
	*/ 	
	public function Options($options, $value, $name, $selected){

		if(!empty($options))
		{
			$html = '<option value="">- Toutes -</options>';
			$attr = 'selected="selected"';
			if(is_object($options)) $options = get_objet_vars($options);

			foreach ($options as $line) {
				if(is_object($line)) {				
					$html .='<option value="'.$line->$value.'" '.($line->$value === $selected ? $attr:'').'>'.$line->$name.'</option>';								
				}				
				elseif(is_array($line))	{
					if($line[$value] == $selected) $attr='selected="selected';		
					$html .= '<option value='.$line[$value].' '.($line->$value === $selected ? $attr:'').'>'.$line[$name].'</option>';
				}
			}
			return $html;
		}
		else return false;
	}

	
	/**
	*	Radio
	*	$id name of the field
	*	$label title of the field
	*	$options array of option
	*	$params array('class','default','javascript')
	*/
	public function Radio( $id, $label, $options, $params = null){


		$html = $this->wrapInput($id, $label, $this->_radio($id, $label, $options, $params) ,$params);		

		return $html;
	}

	public function _radio($id, $label, $options, $params){

		(isset($params['class']))? $class = "class='".$params['class']."'" : $class = '' ;
		(isset($params['default']))? $default = $params['default'] : $default = '';
		(isset($arams['javascript']))? $javascript = $params['javascript'] : $javascript = '';
		(isset($params['style']))? $style = $params['style'] : $style = '';

		$html='';

		if(is_object($options)) $options = (array) $options;

		foreach($options as $value => $text){

			$checked = '';
				if(is_array($default)){
					if(in_array($value,$default)) $checked = 'checked="checked"';
				}
				elseif($value==$default) $checked = 'checked="checked"';
			else $checked = '';

			(isset($params['openwrap']))? $html .= $params['openwrap'] : $html .= '';
			$html .= '<input type="radio" '.$class.' name="'.$id.'" value="'.$value.'" id="'.$value.'" style="'.$style.'" '.$checked.' '.$javascript.'>';
			$html .= '<label class="radio" for="'.$value.'">'.$text.'</label>';
			(isset($params['closewrap']))? $html .= $params['closewrap'] : $html .= '';
		}

		return $html;
	}


	public function Checkbox($id, $label, $options, $params = null){
	
		return $this->wrapInput($id, $label,$this->_checkbox($id, $label, $options, $params),$params);
	
	}

	public function _checkbox($id, $label, $options, $params=null){

		(isset($params['class']))? $class = "class='".$params['class']."'" : $class = '' ;
		(isset($params['default']))? $default = $params['default'] : $default = '';
		(isset($params['javascript']))? $javascript = $params['javascript'] : $javascript = '';

		$field = str_replace('[]','',$id);

		if(isset($options['value'])){
			$default = $params['value'];
		}
		elseif(isset($this->controller->request->data->$field)){ //Si le champ n'a pas de valeur pré-rempli

			$default = $this->controller->request->data->$field;
		}

		if(is_object($options)) $options = (array) $options;
		
		$html = '';		
			foreach($options as $value => $text){

				//if array of object , get the object value
				if(is_object($text)) $text = (array) $text;
				if(is_array($text)){
					$keys = array_keys($text);
					$value = $text[$keys[0]];
					$name = $text[$keys[1]];
				}
				else {
					$value = $value;
					$name = $text;
				}

				//set checked value if exist
				$checked = '';

				if(is_array($default)){
					if(in_array($value,$default) || (array_key_exists($value, $default) && $default[$value]==1)) $checked = 'checked="checked"';
				}
				elseif($value==$default) {
					$checked = 'checked="checked"';;
				}
				
				(isset($params['openwrap']))? $html .= $params['openwrap'] : $html .= '';
				(isset($params['switch']))? $html .= '<input type="hidden" name="'.$id.'" value="0">' : $html .='';
				
				$html .= '<input type="checkbox" '.$class.' name="'.$id.'" value="'.$value.'" id="'.$field.$value.'" '.$checked.' '.$javascript.'>';
				$html .= '<label class="checkbox" for="'.$value.'">'.$name.'</label>';
				(isset($params['closewrap']))? $html .= $params['closewrap'] : $html .= '';
			}

		return $html;

	}
	/*
	public function Textarea($id, $label, $params = null){

		return $this->wrapInput($id,$label,$this->_textarea($id,$label,$params),$params);
	}	
	

	public function _textarea($id, $label, $params = null){

		(isset($params['class']))? $class = "class='".$params['class']."'" : $class = '' ;
		(isset($params['default']))? $default = $params['default'] : $default = '';
		(isset($arams['javascript']))? $javascript = $params['javascript'] : $javascript = '';

		$html = '';

	}
	*/


	public static function Hours(){

		return array('00'=>'00','01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17','18'=>'18','19'=>'19','20'=>'20','21'=>'21','22'=>'22','23'=>'23');
	}

	public static function Minutes(){

		return array('00'=>'00','10'=>'10','20'=>'20','30'=>'30','40'=>'40','50'=>'50');
	}

	public static function MonthDays(){

		return array('01'=>'01','02'=>'02','03'=>'03','04'=>'04','05'=>'05','06'=>'06','07'=>'07','08'=>'08','09'=>'09','10'=>'10','11'=>'11','12'=>'12','13'=>'13','14'=>'14','15'=>'15','16'=>'16','17'=>'17','18'=>'18','19'=>'19','20'=>'20','21'=>'21','22'=>'22','23'=>'23','24'=>'24','25'=>'25','26'=>'26','27'=>'27','28'=>'28','29'=>'29','30'=>'30','31'=>'31');
	}

	public static function Months(){

		return array('01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre');
	}

	public static function WeekDays(){

		return array('Monday'=>'Lun','Tuesday'=>'Mar','Wednesday'=>'Mer','Thursday'=>'Jeu','Friday'=>'Ven','Saturday'=>'Sam','Sunday'=>'Dim');
	}

}

 ?>