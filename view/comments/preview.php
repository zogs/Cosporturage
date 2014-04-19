<?php 
header ('Content-type: text/html; charset=utf-8');


if($type!='404'){
	
	$html ='<div class="previewMedia previewMedia-'.$type.'">';


		$html .='<div class="previewMedia-thumbnails">';

		if(count($thumbnails)>1){
			$html .='<div class="previewMedia-totalthumbnails">
						Choose a thumbnail<br />
						<a href="" id="prev_thumb">Previous</a> Total '.count($thumbnails).' images <a href="" id="next_thumb">Next</a>
					</div>';
		}

		foreach ($thumbnails as $key) {
			if($key == $thumbnails_first)
			$html .="<div class='previewMedia-thumbnail' data-type='".$type."' data-comid='01' data-media-url='".$media."'><span></span><img src='".$key."' alt=''></div>";
			else
			$html .="<div class='previewMedia-thumbnail hide' data-type='".$type."' data-comid='01' data-media-url='".$media."'><span></span><img src='".$key."' alt='' ></div>";
		}
		$html .='</div>';

		$html .='<div class="previewMedia-info">';
			$html .='<div class="previewMedia-close"><strong>x</strong></div>';
			$html .='<div class="previewMedia-title"><a href="'.$url.'" target="_blank" title="'.$title.'">'.$title.'</a></div>';
			$html .='<div class="previewMedia-desc">'.$description.'</div>';
		$html .='</div>';
	$html .='</div>';
}
else{
	$html = 'Le lien est inaccessible...';
}


// $html = utf8_decode($html); // json_encode ne fonctionne qu'avec des donnees utf8
// $html = htmlentities($html);

//echo $html;

$array = array(
	"type" => $type,
	"content" => $html 
	);



echo json_encode($array,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT);



?>