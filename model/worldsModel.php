<?php /**
* 
*/
class WorldsModel extends Model
{
	private $cacheSystem = true;

	public function __construct(){

		parent::__construct();

		//Cache for location system
 		$this->cacheLocation = new Cache(Conf::getCachePath().'/location',10080); //(60*24*7));
		//cache for cluster of wesporter location
		$this->cacheWesporterLocation = new Cache(Conf::getCachePath().'/users_location',1440); //one day



	}
 	//Find all states in the database or the cache version
 	//@param array $ADM ['CC1'=>,'ADM1'=>...]
 	public function findAllStates($ADM){

 		$states = array();

 		$CC1 = $ADM['CC1'];
 		$parent = $CC1; 		

 		//loop in each level
 		foreach ($ADM as $level => $value) {

 					
 			if($level=='CC1'){

				$results = $this->findCountry();											
			} 
			elseif($level=='city' && $value!=''){
				
				$results = $this->findCities($CC1,$ADM);	
			}
 			elseif($value!='') {
 				$results = $this->findStates(array('CC1'=>$CC1,'level'=>$level,'parent'=>$parent));
 										 				 		
			}
			else
				continue;

			$states[$level] = $results;				
			$parent = $value;
 		}

 		return $states; 		
 	}


 	private function convertLang2LC($lang){

 		$array = array('FR'=>'fra','EN'=>'eng','IT'=>'ita');

 		return $array[$lang];
 	}

 	public function findStatesNames($arr,$lang='FR'){

 		$lang = $this->convertLang2LC($lang);

 		if(is_object($arr)) $arr = (array) $arr;

 		$states = new stdClass();
 		if(!empty($arr['CC1']) && trim($arr['CC1'])!='') {
 			$states->CC1 = $this->findFirst(array('table'=>'world_country','fields'=>'FULLNAME as name','conditions'=>array('CC1'=>$arr['CC1'])));
 			$states->CC1 = $states->CC1->name;
 		}
 		if(!empty($arr['ADM1']) && trim($arr['ADM1'])!='') {
 			$states->ADM1 = $this->findFirst(array('table'=>'world_states','fields'=>'FULLNAMEND as name','conditions'=>array('CC1'=>$arr['CC1'],'ADM_CODE'=>$arr['ADM1'],'LC'=>$lang)));
 			$states->ADM1 = $states->ADM1->name;
 		}
 		if(!empty($arr['ADM2']) && trim($arr['ADM2'])!='') {
 			$states->ADM2 = $this->findFirst(array('table'=>'world_states','fields'=>'FULLNAMEND as name','conditions'=>array('CC1'=>$arr['CC1'],'ADM_CODE'=>$arr['ADM2'],'LC'=>$lang)));
 			$states->ADM2 = $states->ADM2->name;
 		}
 		if(!empty($arr['ADM3']) && trim($arr['ADM3'])!='') {
 			$states->ADM3 = $this->findFirst(array('table'=>'world_states','fields'=>'FULLNAMEND as name','conditions'=>array('CC1'=>$arr['CC1'],'ADM_CODE'=>$arr['ADM3'],'LC'=>$lang)));
 			$states->ADM3 = $states->ADM3->name;
 		}
 		if(!empty($arr['ADM4']) && trim($arr['ADM4'])!='') {
 			$states->ADM4 = $this->findFirst(array('table'=>'world_states','fields'=>'FULLNAMEND as name','conditions'=>array('CC1'=>$arr['CC1'],'ADM_CODE'=>$arr['ADM4'],'LC'=>$lang)));
			$states->ADM4 = $states->ADM4->name;
		}
		if(!empty($arr['city']) && trim($arr['city'])!='') {
			$city = $this->findCityById($arr['city'],'FULLNAMEND as name');
			$states->city = $city->name;
		}
 		
 		return $states;
 	}


 	private function writeCacheVersion($path, $content){

 		//If cache system is off 
 		if($this->cacheSystem == false) return false;

 		//Write file into cache location
 		$this->cacheLocation->write($path,base64_encode($content));

 	}

 	private function findCacheVersion($path){

 		//If cache system is off 
 		if($this->cacheSystem == false) return false;

 		//If cache location existe return cache version
 		if($content = $this->cacheLocation->read($path)){

 			
 			return base64_decode($content);
 		}
 		else //else return false;
 			return false;
 	}


 	/*
 	* find Country list
 	* param $CC1 country code
 	*/
	public function findCountry($CC1 = ''){

		$path = $this->path_location(array('CC1'=>$CC1));

		if($cache = $this->findCacheVersion($path)){

			$cache = unserialize($cache);
			return $cache;
		}

 		$countries = $this->find(array(
 			'table' => "world_country",
 			'fields'=>array('CC1 as code','FULLNAME as name'),
 			'conditions'=>$CC1,
 			'order'=>'FULLNAME'
 			));

 		$result = array(
 			'lvl'=>'CC1',
 			'parent'=>'list',
 			'country'=>'country',
 			'title'=>'Séléctionner un pays',
 			'list'=>$countries
 			);

 		$this->writeCacheVersion($path,serialize($result));

 		return $result;

 	}
 	//Renvoi les regions/states/departements/cantons suivant son parent
 	//$params array $data (
 	//				CC1 => code du pays
 	//				level => niveau de la region
 	//				parent => code de la region pere
 	public function findStates($data){

 		extract($data);

 		//cache location
 		$path = $this->path_location($data);

 		//find cache version
 		if($cache = $this->findCacheVersion($path)){
 			return unserialize($cache); //if exist return cache version
 		}
 

 		//state query
 		$sql = "SELECT WR.ADM_CODE as code, WR.FULLNAME as name 
 				FROM world_states as WR
 				JOIN world_country as WC ON WC.CC1=WR.CC1";

	 		if ($level == 'ADM1'){

			    $sql .= "	WHERE WR.CC1=:CC1 AND WR.DSG=:level AND WR.LC=WC.LO";
			    $values = array(':CC1'=>$CC1,':level'=>$level);
	 		}
			else {
			    $sql .= "	WHERE WR.CC1=:CC1 AND WR.ADM_PARENT=:parent AND WR.DSG=:level AND WR.LC=WC.LO";
				$values = array(':CC1'=>$CC1,':parent'=>$parent,':level'=>$level);
			}

		$sql .= " GROUP BY WR.FULLNAME ORDER BY WR.FULLNAME";

		 // debug($sql);
 		$states = $this->query($sql,$values);

 		$result = array(
 			'lvl'=>$level,
 			'parent'=>$parent,
 			'country'=>$CC1,
 			'title'=>'Select a state',
 			'list'=>$states
 			);

 		//write cache version
 		$this->writeCacheVersion($path,serialize($result));

 		return $result;

 	}
 	

 	/**
 	* find city by id
 	*
 	* @param id int
 	*/
 	public function findCityById($id,$fields='*'){

 		return $this->findFirst(array('table'=>'world_cities','fields'=>$fields,'conditions'=>array('UNI'=>$id)));
 	}



 	//Renvoi les villes suivant la/les regions
 	//$params string $CC1
 	//$params array $ADM (
 	//				ADM1 => code region 1
 	//				ADM2 => code region 2
 	//				ADM3 => 
 	//				ADM..=>
 	public function findCities($CC1, $ADM){


 		//first, return false if there is no city
 		if(isset($ADM['city'])&&($ADM['city']==0||empty($ADM['city']))) return false;

 		//reformat ADM array 
	 	$ADM = $this->formatADMArray($ADM);

	 	//set location of cache file
	 	$a = array('CC1'=>$CC1,'city'=>'yes');
	 	$a = array_merge($a,$ADM);
	 	$path = $this->path_location($a);

	 	//if cache file exist
	 	if($cache = $this->findCacheVersion($path)){

	 		return unserialize($cache); //return cache version
	 	}

	 	//the values for sql pdo
	 	$values = array(':CC1'=>$CC1);

	 	//City request
 		$sql = "SELECT C.UNI as code, C.FULLNAME as name
				FROM world_cities as C	
				JOIN world_country as WC ON C.CC1=WC.CC1				
				WHERE C.CC1=:CC1 ";


			$sql .= " AND (";
			$cond = array();
 			foreach ($ADM as $k => $v) {

				$parent = $v;	 						 		
 				$cond[] = "C.$k=:$k";
 				$values[":$k"] = $v;

	 				 				 				 			
 			}
 			$sql .= implode(' AND ',$cond);
 			$sql .= " ) ";
			
		$sql .=" AND ( C.LC=WC.LO OR C.LC='') ORDER BY C.FULLNAME";		

		$cities = $this->query($sql,$values);

		$result = array(
			'lvl'=>'city',
			'parent'=>$parent,
			'country'=>$CC1,
			'title'=>'Select a city',
			'list'=>$cities
			);

		// debug(mb_detect_encoding($result['list'][47]->name));
		// debug($result);
		//write cache version
		$this->writeCacheVersion($path,serialize($result));

		return $result;
 	}


 	public function clusterOfWesportersCities($title,$location,$users){

 		$path = $this->path_location($location,'.kml');

 		if($this->cacheWesporterLocation->read($path)){
 				
 				return Conf::getCachePath().'/users_location/'.$path;
 		}

 		$cities = array(); 		
		foreach ($users as $user) {			
			if($user->exist()==true && !empty($user->city))				
				$c = $this->findCityById($user->city,'FULLNAMEND as name,LATITUDE as lat,LONGITUDE as lon');
				$cities[] = array('user_id'=>$user->user_id,'login'=>$user->login,'city'=>$c->name,'lat'=>$c->lat,'lon'=>$c->lon);
		}

		if(empty($cities)) return;

 		$xml = "<?xml version='1.0' encoding='UTF-8'?>
				<kml xmlns='http://earth.google.com/kml/2.2'>
				<Document>
				<Description>Test du cluster</Description>
				<name>Cluster des sportifs</name>
				<Folder>
				<name>Cluster des sportifs</name>				
 		";

 		foreach ($cities as $city) {
 		
 			$xml .= '<Placemark>
					<name>'.$city['login'].'</name>
					<Point>
					<coordinates>'.$city['lon'].','.$city['lat'].'</coordinates>
					</Point>
					</Placemark>
					';
 		}

 		$xml .= "</Folder>
				</Document>
				</kml>
				";

		$this->cacheWesporterLocation->write($path,$xml);

		return Conf::getCachePath().'/users_location/'.$path;		
 	}

 	public function path_location($location,$ext='.list'){

 		$a = array();
 		$path = '';
 		$order = array('CC1','ADM1','ADM2','ADM3','ADM4','city');
 		if(is_object($location)) $location = (array) $location;
 		foreach ($order as $v) {
 			if(!empty($location[$v])) $a[$v] = $location[$v];
 		}


 		if(empty($a['CC1'])){
 			return 'CC1'.$ext;
 		}

		if(!empty($a['city'])){		
			$path .= $a['CC1'];
			$path .= '/city/';
			unset($a['city']);
			unset($a['CC1']);
			foreach ($a as $key => $value) {
				$path .= $value.'/';
			}
			$path .= 'city'.$ext;

			return $path;
		}
			
		if(!empty($location['level'])) {
			if($location['level']=='ADM1') $path = $a['CC1'].'/ADM1/'.$a['CC1'].$ext;
			if($location['level']=='ADM2') $path = $a['CC1'].'/ADM2/'.$location['parent'].$ext;
			if($location['level']=='ADM3') $path = $a['CC1'].'/ADM3/'.$location['parent'].$ext;
			if($location['level']=='ADM4') $path = $a['CC1'].'/ADM4/'.$location['parent'].$ext;			
 		
 			return $path;
		}

		//else
		$path = implode('/',$a).$ext;
		return $path;

 	}
 	/**
 	* SuggestCities
 	* Find city from the autocompletion
 	* @param CC1 string : country code 1
 	* @param limit int 
 	* @param prefix string : first letter of the city
 	*/
 	public function suggestCities($params){

 		

 			(isset($params['CC1']))? $CC1 = $params['CC1'] : $CC1 = Conf::$pays;
 			(isset($params['limit'])&&is_numeric($params['limit']))? $nbResult = $params['limit'] : $nbResult = 10;
 			if(isset($params['prefix'])) $QUERY = $params['prefix'];
 			else return false;

			$values = array(':CC1'=>$CC1,':QUERY'=>$QUERY.'%');

 			$sql = "SELECT DISTINCT City.UNI as city_id, City.FULLNAMEND as name, City.CC1, City.ADM1, City.ADM2, City.ADM3, City.ADM4, City.LATITUDE, City.LONGITUDE 
 								FROM world_cities as City
								LEFT JOIN world_country as Pays ON Pays.CC1=City.CC1
								WHERE City.CC1=:CC1 AND (City.LC=Pays.LO OR City.LC='') 

								AND ( City.FULLNAME LIKE :QUERY ";

								//replace space by -								
								if(strpos($QUERY,' ')!==0) {
									$QUERY_2 = str_replace(' ', '-', $QUERY).'%';
									$values[':QUERY_2'] = $QUERY_2;
									$sql .= " OR City.FULLNAME LIKE :QUERY_2 ";
								}
								$sql.= ' ) ';

			$sql .= " LIMIT ".$nbResult;

		
			$cities = $this->query($sql,$values);

			$array=array();
			foreach ($cities as $city) {
				
				if($city->ADM4=='') unset($city->ADM4);
				if($city->ADM3=='') unset($city->ADM3);
				if($city->ADM2=='') unset($city->ADM2);
				if($city->ADM1=='') unset($city->ADM1);

				$city = $this->JOIN_GEO($city);
				$array[] = $city;
			}
						
			return $array;

 		
 	}


 	/*
		Find Cities arround a radius
		@param {array} $params
			arround :  radius in km or miles
			Lat : Latitude of the point
			Lon : Latitude of the point
			location : array of location data
			km : boolean
			miles : boolean

		ex : ['arround'=>10,Lat'=>16.4,'Lon'=>46.4,location=>array('CC1'=>,ADM1=>,...),'km'=>true,'miles'=>true]
 	*/

 	public function findCitiesArround($params){

 		extract($params);

 		//If extend arround a point
		// set params to modified the query
		if(!empty($arround)){

			if(!empty($Lat) && !empty($Lon)){

				$distance= $arround;
				$onedegree = 111.045;
				$earthradius = 6366.565;
				if(!empty($km) && $km == true){ // in km
					$onedegree = 111.045;
					$earthradius = 6366.565;
				}
				if(!empty($miles) && $miles==true) {
					$onedegree = 69;
					$earthradius = 3956;
				}				

				//calcul of the box
				$lon1 = $Lon-$distance/abs(cos(deg2rad($Lat))*$onedegree);
				$lon2 = $Lon+$distance/abs(cos(deg2rad($Lat))*$onedegree);
				$lat1 = $Lat-($distance/$onedegree);
				$lat2 = $Lat+($distance/$onedegree);

				//The Haversine Formula
				$distance_field = ", $earthradius * 2 * ASIN(SQRT( POWER(SIN(($Lat - C.LATITUDE) *  pi()/180 / 2), 2) +COS($Lat * pi()/180) * COS(C.LATITUDE * pi()/180) * POWER(SIN(($Lon - C.LONGITUDE) * pi()/180 / 2), 2) )) as distance";
				
			}
			else debug('city missing');
		}

 		$sql = 'SELECT ';
 		if(!empty($fields))
			$sql .= $this->sqlfields($fields);
		else
			$sql .= $this->sqlfields('*');

		$sql .= $distance_field;

		$sql .= " FROM world_cities as C ";

		$sql .= ' WHERE 1=1';

		//location speed up the search
		if(!empty($location)){

			$arr = array('city','CC1','ADM1','ADM2','ADM3','ADM4');
			$ADM = array();
			foreach ($arr as $key) {

				if(!empty($location[$key]) && trim($location[$key])!=''){

					$ADM[] = $key.'="'.$location[$key].'" ';
				}								
			}
			if(count($ADM)>0)
				$sql .= ' AND '.implode(' AND ',$ADM);
		}

		$sql .= ' AND C.LONGITUDE BETWEEN '.$lon1.' AND '.$lon2.' AND C.LATITUDE BETWEEN '.$lat1.' AND '.$lat2.' ';

		$sql .= 'having distance < '.$distance;
				
		$pre = $this->db->prepare($sql);
		$pre->execute();
		$cities = $pre->fetchAll(PDO::FETCH_OBJ);
		
		return $cities;		

 	}

 	public function formatADMArray($array){

 		//Delete first row
 		array_shift($array);

 		$i = 1;
 		$new = array();
 		//Create new array with ADM1,ADM2,ADM3... levels
 		foreach ($array as $level => $value) {

 			if($level!='city' && $value!='') {
 			$new['ADM'.$i] = $value;
 			}
 			$i++; 			
 		}

 		return $new;
 	}

 	/**
 	*	JOIN_GEO
 	* join geo informations to an object
 	* param obj OR array(objs)
 	* obj should have ( $obj->CC1, $obj->ADM1, $obj->AMD2, ...)
	*/
 	public function JOIN_GEO($object){
 		
 		//if object set new array
 		if(is_object($object)) $objs = array($object);

 		else if(is_array($object)) $objs = $object;

 		else return false;

		$res = array();
		foreach ($objs as $obj) {
				
				if(isset($obj->ADM1)){

	 			$obj = $this->JOIN('world_states','FULLNAMEND as ADM1',array(
																					'CC1'=>$obj->CC1,
																					'DSG'=>'ADM1',
																					'ADM_CODE'=>$obj->ADM1,
																					'NT'=>'N'

																				),$obj
									);
		 		}

		 		if(isset($obj->ADM2)){

		 			$obj = $this->JOIN('world_states','FULLNAMEND as ADM2',array(
																						'CC1'=>$obj->CC1,
																						'DSG'=>'ADM2',
																						'ADM_CODE'=>$obj->ADM2,
																						'NT'=>'N'

																					),$obj
									);
		 		}

		 		if(isset($obj->ADM3)){

		 			$obj = $this->JOIN('world_states','FULLNAMEND as ADM2',array(
																						'CC1'=>$obj->CC1,
																						'DSG'=>'ADM3',
																						'ADM_CODE'=>$obj->ADM3,
																						'NT'=>'N'

																					),$obj
									);
		 		}

		 		if(isset($obj->ADM4)){

		 			$obj = $this->JOIN('world_states','FULLNAMEND as ADM4',array(
																						'CC1'=>$obj->CC1,
																						'DSG'=>'ADM4',
																						'ADM_CODE'=>$obj->ADM4,
																						'NT'=>'N'

																					),$obj
									);
		 		}
		 		//join
		 		if(!empty($obj->city) && is_numeric($obj->city)) {
		 					 			
		 			$obj = $this->JOIN('world_cities','FULLNAMEND as city',array('UNI'=>$obj->city),$obj);		 			 
		 			
		 		}		 	

		 		if(isset($obj->CC1)){

		 			$obj = $this->JOIN('world_country','FULLNAME as CC1',array('CC1'=>$obj->CC1),$obj);
		 		}

		 		$res[] = $obj;
		}
		
 		if(is_object($object)) return $res[0];

 		if(is_array($object)) return $res;

 	}

} ?>