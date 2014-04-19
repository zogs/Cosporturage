<?php
class Giphy{
	
	private $apiKey = '';
	private $defaultImg = '';

	public function __construct(){

		//public key
		$this->apiKey = 'dc6zaTOxFJmzC';
		$this->defaultImg = 'img/Sport-fail.jpg';
	}

	public function setApiKey($key){

		$this->apiKey = $key;
	}

	public function getRandomGifByKeywords($string){

		$url = 'http://api.giphy.com/v1/gifs/screensaver?api_key='.$this->apiKey.'&tag='.urlencode(str_replace(array("'","_"," "), array('-','-','-'),$string));

		$json = $this->curl_get_file_contents($url);
		$json = json_decode($json);

		if(isset($json->meta) && $json->meta->status==200)
			return $json;
		else
			return false;
	}

	public function getRandomGifUrlByKeywords($string){

		if($gif = $this->getRandomGifByKeywords($string))
			return $gif->data->image_original_url;
		else
			return $this->defaultImg;
	}

	private function curl_get_file_contents($url) {

	    $c = curl_init();
	    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($c, CURLOPT_URL, $url);
	    $contents = curl_exec($c);
	    $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
	    curl_close($c);
	    if ($contents) return $contents;
	    else return FALSE;
  	}
}
?>