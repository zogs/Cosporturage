<?php

class Conf {

	
	//Prefix to access backend
	static $adminPrefix = 'vestiaire';
	static $emailsAdmins = array(
								'guichardsim@gmail.com',
								'pierresimon.gossot@gmail.com',
								'contact@we-sport.fr'
								);

	//Website information
	static $website = 'we-Sport';
	static $websiteURL = 'wesport.zogs.org';
	static $websiteURLs = array(
		'localwesport'=>'http://localwesport.fr',
		'zogs'=>'http://wesport.zogs.org',
		'we-sport' => 'http://we-sport.fr',
		'default' => 'http://we-sport.fr'
		);
	static $parsed_url;
	static $websiteDOT = 'we-sport.fr';
	static $contactEmail = 'contact@we-sport.fr';
	static $debugIpAddress = array('127.0.0.1','193.52.250.230','88.162.98.17','80.119.95.169');
	static $debugErrorEmails = array('guichardsim@gmail.com');
	static $mailer = null;
	static $mailLogger = null;
	static $mailingNbSendingByCron = 1;
	static $lang = 'fr';
	static $pays = 'FR';

	static $facebook = array('appId'=>'153720748148187','secret'=>'7a181d394b1f1dab0054176f9031a637');

	//Database connexion
	static $databases = array(
			
		'localhost'  => array(
			'host'     => 'localhost',
			'database' => 'wesport',
			'login'    => 'root',
			'password' => ''
			),
		'zogs' =>array(
			'host'     => 'localhost',
			'database' => 'wesport',
			'login'    => 'root',
			'password' => 'XSgvEPbG'
			),
		'we-sport' => array(
			'host'    => 'localhost',
			'database'=> 'wesport',
			'login'   => 'root',
			'password'=> 'XSgvEPbG'
			),
		'localwesport' => array(
			'host'    => 'localhost',
			'database'=> 'wesport',
			'login'   => 'root',
			'password'=> ''
			),
		'default' => array(
			'host'    => 'localhost',
			'database'=> 'wesport',
			'login'   => 'root',
			'password'=> 'XSgvEPbG'
			) 		
		);

	//Language code
	static $languageCodes = array("aa" => "Afar","ab" => "Abkhazian","ae" => "Avestan","af" => "Afrikaans","ak" => "Akan","am" => "Amharic","an" => "Aragonese","ar" => "Arabic","as" => "Assamese","av" => "Avaric","ay" => "Aymara","az" => "Azerbaijani","ba" => "Bashkir","be" => "Belarusian","bg" => "Bulgarian","bh" => "Bihari","bi" => "Bislama","bm" => "Bambara","bn" => "Bengali","bo" => "Tibetan","br" => "Breton","bs" => "Bosnian","ca" => "Catalan","ce" => "Chechen","ch" => "Chamorro","co" => "Corsican","cr" => "Cree","cs" => "Czech","cu" => "Church Slavic","cv" => "Chuvash","cy" => "Welsh","da" => "Danish","de" => "German","dv" => "Divehi","dz" => "Dzongkha","ee" => "Ewe","el" => "Greek","en" => "English","eo" => "Esperanto","es" => "Spanish","et" => "Estonian","eu" => "Basque","fa" => "Persian","ff" => "Fulah","fi" => "Finnish","fj" => "Fijian","fo" => "Faroese","fr" => "French","fy" => "Western Frisian","ga" => "Irish","gd" => "Scottish Gaelic","gl" => "Galician","gn" => "Guarani","gu" => "Gujarati","gv" => "Manx","ha" => "Hausa","he" => "Hebrew","hi" => "Hindi","ho" => "Hiri Motu","hr" => "Croatian","ht" => "Haitian","hu" => "Hungarian","hy" => "Armenian","hz" => "Herero","ia" => "Interlingua (International Auxiliary Language Association)","id" => "Indonesian","ie" => "Interlingue","ig" => "Igbo","ii" => "Sichuan Yi","ik" => "Inupiaq","io" => "Ido","is" => "Icelandic","it" => "Italian","iu" => "Inuktitut","ja" => "Japanese","jv" => "Javanese","ka" => "Georgian","kg" => "Kongo","ki" => "Kikuyu","kj" => "Kwanyama","kk" => "Kazakh","kl" => "Kalaallisut","km" => "Khmer","kn" => "Kannada","ko" => "Korean","kr" => "Kanuri","ks" => "Kashmiri","ku" => "Kurdish","kv" => "Komi","kw" => "Cornish","ky" => "Kirghiz","la" => "Latin","lb" => "Luxembourgish","lg" => "Ganda","li" => "Limburgish","ln" => "Lingala","lo" => "Lao","lt" => "Lithuanian","lu" => "Luba-Katanga","lv" => "Latvian","mg" => "Malagasy","mh" => "Marshallese","mi" => "Maori","mk" => "Macedonian","ml" => "Malayalam","mn" => "Mongolian","mr" => "Marathi","ms" => "Malay","mt" => "Maltese","my" => "Burmese","na" => "Nauru","nb" => "Norwegian Bokmal","nd" => "North Ndebele","ne" => "Nepali","ng" => "Ndonga","nl" => "Dutch","nn" => "Norwegian Nynorsk","no" => "Norwegian","nr" => "South Ndebele","nv" => "Navajo","ny" => "Chichewa","oc" => "Occitan","oj" => "Ojibwa","om" => "Oromo","or" => "Oriya","os" => "Ossetian","pa" => "Panjabi","pi" => "Pali","pl" => "Polish","ps" => "Pashto","pt" => "Portuguese","qu" => "Quechua","rm" => "Raeto-Romance","rn" => "Kirundi","ro" => "Romanian","ru" => "Russian","rw" => "Kinyarwanda","sa" => "Sanskrit","sc" => "Sardinian","sd" => "Sindhi","se" => "Northern Sami","sg" => "Sango","si" => "Sinhala","sk" => "Slovak","sl" => "Slovenian","sm" => "Samoan","sn" => "Shona","so" => "Somali","sq" => "Albanian","sr" => "Serbian","ss" => "Swati","st" => "Southern Sotho","su" => "Sundanese","sv" => "Swedish","sw" => "Swahili","ta" => "Tamil","te" => "Telugu","tg" => "Tajik","th" => "Thai","ti" => "Tigrinya","tk" => "Turkmen","tl" => "Tagalog","tn" => "Tswana","to" => "Tonga","tr" => "Turkish","ts" => "Tsonga","tt" => "Tatar","tw" => "Twi","ty" => "Tahitian","ug" => "Uighur","uk" => "Ukrainian","ur" => "Urdu","uz" => "Uzbek","ve" => "Venda","vi" => "Vietnamese","vo" => "Volapuk","wa" => "Walloon","wo" => "Wolof","xh" => "Xhosa","yi" => "Yiddish","yo" => "Yoruba","za" => "Zhuang","zh" => "Chinese","zu" => "Zulu");
	static $languageAvailable = array('fr'=>'Francais','en'=>'English');
	static $languageDefault = 'fr';	

	//Cache directory ( from /webroot )
	static $cacheDirectory = array( 
		'localwesport' => 'cache',
		'zogs' => 'cache',
		'we-sport' => 'cache',
		'default' => 'cache'
		);


	//Css to load
	static $css = array(	
			'bootstrap/css/bootstrap.css',
			"http://fonts.googleapis.com/css?family=Viga",
			'css/default.css',
			'css/style.css',
			'css/font-icons/style.css',
			'js/jquery/select2-2.1/select2.css',
			'js/jquery/datepicker/css/datepicker.css',
			'js/jquery/mmenu/mmenu.css',
			'js/jquery/mmenu/mmenu-positioning.css'
			);

	//Js to load
	static $js_main = 'js/main.js';
	static $js_dependency = array(	
					'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',
					//'js/jquery/jquery.livequery.min.js',
					'bootstrap/js/bootstrap.js',
					'js/jquery/jquery.flowslider.js',
					'js/jquery/jquery.typeahead.js',
					'js/jquery/hogan.mustache.js',
					'js/phpfunctions.js',
					'js/jquery/select2-2.1/select2.min.js',
					'js/jquery/jquery.timeago.js',
					'js/jquery/jquery.expander.min.js',
					'js/jquery/datepicker/js/bootstrap-datepicker.js',
					'js/jquery/datepicker/js/locales/bootstrap-datepicker.fr.js',
					'js/jquery/mmenu/jquery.mmenu.js'
				);


	static $reserved = array(
		'login'=>array(
			'array'=>array('admin','administrator','administrateur'),
			'errorMsg'=>'This login is reserved'
			),
		'email'=>array(
			'array'=>array(),
			'errorMsg'=>'This email is reserved')
		);


		/**
		 * getMailer
		 * return instance of Swift_Mailer
		 */
		public static function getMailer(){

			if(isset(self::$mailer)) return self::$mailer;

			self::$mailer = Swift_Mailer::newInstance(self::getTransportSwiftMailer());
			self::$mailLogger = new Swift_Plugins_Loggers_ArrayLogger();
			self::$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin(self::$mailLogger));
			return self::$mailer;
		}

		/**
		* getMailLog
		* return swiftmailer error and logs
		**/
		public static function getMailLog(){

			return self::$mailLogger->dump();
		}

		/**
		 * getTransportSwiftMailer
		 * return instance of Swift_SmtpTransport
		 * set the method of sending mail of the application
		 * configure with smtp info 
		 * or choose php mail() function (not recommended)
		 */
	    public static function getTransportSwiftMailer(){

	    	return $transport = Swift_SmtpTransport::newInstance('smtp.we-sport.fr', 587, 'tls')
		    	->setUsername('contact@we-sport.fr')
				->setPassword('justdoit');

	    }


	    public static function getHost(){

	  		$url = self::getParsedUrl();

	    	if(isset($url['domain'])) return $url['domain'];	    	
	    	return 'default';
	    }

	    public static function getParsedUrl(){
	    	if(isset(self::$parsed_url)) return self::$parsed_url;
	    	if(!isset($_SERVER['HTTP_HOST'])) return false;
	    	
	    	$url = Conf::getProtocol().'://'.$_SERVER['HTTP_HOST'];
	    	self::$parsed_url = String::parse_url($url);
	    	return self::$parsed_url;
	    }

	    public static function getProtocol(){

	    	if(!isset($_SERVER['SERVER_PROTOCOL'])) return 'http';
	    	if(strpos($_SERVER['SERVER_PROTOCOL'],'HTTP/')===0) return 'http';
	    	if(strpos($_SERVER['SERVER_PROTOCOL'],'HTTPS/')===0) return 'https'; //to test
	    	return 'http';
	    }

	    public static function getDatabase($field = null){

	    	if(isset(self::$databases[self::getHost()])) $db = self::$databases[self::getHost()];
	    	else $db = self::$databases['default'];

	    	if($field==null) return $db;
	    	if(isset($db[$field])) return $db[$field];
	    	else throw new zException("No field -".$field."- in the database configuration , {conf:getDatabase()}", 1);
	    	
	    }

	    public static function getSiteUrl(){	    	
	    	$host = self::getHost();	    	
	    	if(isset(self::$websiteURLs[$host])) return self::$websiteURLs[$host];
	    	return self::$websiteURL['default'];
	    }

	    public static function getCachePath(){
	    	$host = self::getHost();
	    	if(isset(self::$cacheDirectory[$host])) return WEBROOT.DS.self::$cacheDirectory[$host];
	    	return WEBROOT.DS.self::$cacheDirectory['default'];
	    }

	    
	static $villes = array(		
		'paris'=>array(
			'name'=>'Paris',
			'id'=>'-2085971'),
		'lyon'=>array(
			'name'=>'Lyon',
			'id'=>'-2074875'
			),
		'marseille'=>array(
			'name'=>'Marseille',
			'id'=>'-2076832'
			),
		'toulouse'=>array(
			'name'=>'Toulouse',
			'id'=>'-2108604'
			),
		'lille'=>array(
			'name'=>'Lille',
			'id'=>'-2073017'
			),
		'bordeaux'=>array(
			'name'=>'Bordeaux',
			'id'=>'-2028797'
			),
		'nice'=>array(
			'name'=>'Nice',
			'id'=>'-2083425'
			),
		'nantes'=>array(
			'name'=>'Nantes',
			'id'=>'-2082685'
			),
		'strasbourg'=>array(
			'name'=>'Strasbourg',
			'id'=>'-2106720'
			),
		'rennes'=>array(
			'name'=>'Rennes',
			'id'=>'-2091750'
			),
		'grenoble'=>array(
			'name'=>'Grenoble',
			'id'=>'-2050459'
			),
		'rouen'=>array(
			'name'=>'Rouen',
			'id'=>'-2093460'
			),	
		'toulon'=>array(
			'name'=>'Toulon',
			'id'=>'-2108587'
			),
		'montpellier'=>array(
			'name'=>'Montpellier',
			'id'=>'-2081122'
			),
		'lens'=>array(
			'name'=>'Lens',
			'id'=>'-2066898'
			),
		'avignon'=>array(
			'name'=>'Avignon',
			'id'=>'-2023517'
			),	
		'saint-etienne'=>array(
			'name'=>'Saint-Etienne',
			'id'=>'-2096986'
			),	
		'tours'=>array(
			'name'=>'Tours',
			'id'=>'-2108776'
			),
		'clermont-ferrand'=>array(
			'name'=>'Clermont-ferrand',
			'id'=>'-2037731'
			),
		'nancy'=>array(
			'name'=>'Nancy',
			'id'=>'-2082645'
			),
		'orleans'=>array(
			'name'=>'Orléans',
			'id'=>'-2084904'
			),
		'caen'=>array(
			'name'=>'Caen',
			'id'=>'-2031765'
			),
		'angers'=>array(
			'name'=>'Angers',
			'id'=>'-2020931'
			),
		'metz'=>array(
			'name'=>'Metz',
			'id'=>'-2078532'
			),
		'dijon'=>array(
			'name'=>'Dijon',
			'id'=>'-2041884'
			),	
		);

}



?>