<?php 
/**
 * GESTION DES DATES
 */
 class Date
 {
 	private $Session;

 	public function __construct($session)
 	{
 		$this->session = $session;
 	}

 	public static function MysqlNow(){

 		return self::timestamp2MysqlDate(time());
 	}

 	public static function timestamp2MysqlDate($timestamp){

 		return date('Y-m-d H:i:s',$timestamp);
 	}

 	public static function dayoftheweek($day,$lang = ''){

 		if(empty($lang)) $lang = Conf::$languageDefault;
 		$days = array(
 						'fr'=>array('Today'=>"Aujourd'hui",'Mon'=>'Lundi','Tue'=>'Mardi','Wed'=>'Mercredi','Thu'=>'Jeudi','Fri'=>'Vendredi','Sat'=>'Samedi','Sun'=>'Dimanche')
		);

		return $days[$lang][$day];
 	}

 	public static function yearsSince($year){

 		$age = 0;
		$current = Date('Y');
		for($i=$year; $i<$current; $i++){
			$age++;
		}
		return $age;
 	}

 	public static function datefr($date) { 

		//tableau des mois de l'année en francais
		$type_mois['00']='00';
		$type_mois['01']='Janvier';   $type_mois['02']='Février';
		$type_mois['03']='Mars';      $type_mois['04']='Avril';
		$type_mois['05']='Mai';       $type_mois['06']='Juin';
		$type_mois['07']='Juillet';   $type_mois['08']='Août';
		$type_mois['09']='Septembre'; $type_mois['10']='Octobre';
		$type_mois['11']='Novembre';  $type_mois['12']='Décembre';

		//si le format est 2013/05/25 corrige en 2013-05-25
		$date = str_replace('/', '-', $date);
		//on separe la date en jour mois annee
		$split = explode("-",$date); 
		$annee = $split[0]; 
		$num_mois = $split[1]; 
		$jour = $split[2]; 
		//on associe le numero du mois au nom correspondant dans le tableau
		$mois = $type_mois[$num_mois];
		//on retourne la valeur
		return "$jour"." "."$mois"." "."$annee"; 
	}	

	public static function day_month($date){

		//tableau des mois de l'année en francais
		$type_mois['00']='00';
		$type_mois['01']='Janvier';   $type_mois['02']='Février';
		$type_mois['03']='Mars';      $type_mois['04']='Avril';
		$type_mois['05']='Mai';       $type_mois['06']='Juin';
		$type_mois['07']='Juillet';   $type_mois['08']='Août';
		$type_mois['09']='Septembre'; $type_mois['10']='Octobre';
		$type_mois['11']='Novembre';  $type_mois['12']='Décembre';

		//si le format est 2013/05/25 corrige en 2013-05-25
		$date = str_replace('/', '-', $date);
		//on separe la date en jour mois annee
		$split = explode("-",$date); 
		$annee = $split[0]; 
		$num_mois = $split[1]; 
		$jour = $split[2]; 
		//on associe le numero du mois au nom correspondant dans le tableau
		$mois = $type_mois[$num_mois];
		//on retourne la valeur
		return "$jour"." "."$mois"; 
	}

 	// public function month($num){

 	// 	$array = array('fr' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre')
 	// 			);

 	// 	return $array[$this->session->getLang()][$num - 1];

 	// }

	 	/**
	 * Checks date if matches given format and validity of the date.
	 * Examples:
	 * <code>
	 * is_date('22.22.2222', 'mm.dd.yyyy'); // returns false
	 * is_date('11/30/2008', 'mm/dd/yyyy'); // returns true
	 * is_date('30-01-2008', 'dd-mm-yyyy'); // returns true
	 * is_date('2008 01 30', 'yyyy mm dd'); // returns true
	 * </code>
	 * @param string $value the variable being evaluated.
	 * @param string $format Format of the date. Any combination of <i>mm<i>, <i>dd<i>, <i>yyyy<i>
	 * with single character separator between.
	 */
	public static function is_valid_date($value, $format = 'dd.mm.yyyy'){
	    if(strlen($value) >= 6 && strlen($format) == 10){
	       
	        // find separator. Remove all other characters from $format
	        $separator_only = str_replace(array('m','d','y'),'', $format);
	        $separator = $separator_only[0]; // separator is first character
	       
	        if($separator && strlen($separator_only) == 2){
	            // make regex
	            $regexp = str_replace($separator, "\\" . $separator, $format);
	            $regexp = str_replace('mm', '(0?[1-9]|1[0-2])', $regexp);
	            $regexp = str_replace('dd', '(0?[1-9]|[1-2][0-9]|3[0-1])', $regexp);
	            $regexp = str_replace('yyyy', '(19|20)?[0-9][0-9]', $regexp);

	            if($regexp != $value && preg_match('/'.$regexp.'\z/', $value)){

	                return true;
	            }
	        }
	    }	    
	    return false;
	} 

	/**
	 * 
	 */
	public static function dateDiff($start, $end) {

		$start_ts = strtotime($start);
		$end_ts = strtotime($end);
		$diff = $end_ts - $start_ts;
		return round($diff / 86400);
	}

	public static function dateStatus($date){

		if($date==date('Y-m-d')) return 'now';
		if(strtotime($date) > time())  return 'futur';
		if(strtotime($date) < time())  return 'past';
	}
		
 } 
?>