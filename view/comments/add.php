<?php

    //Fichier qui contient la fonction qui cré le code du commentaire
    require('html.php');


    //If fail
    if(isset($fail)) {
        echo json_encode(array('fail'=>$fail));
        exit();
    }


    //If there are comments
    if(!empty($com)){

        //set the config to not display a form reply
        $this->levelFormReplyToDisplay=0;

        //Create the html 
        $html = show_comments($com,$this->session->user(),$this);
        
        echo json_encode(array('content'=>$html),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT);
    }
    else {

        echo '';
    }

?>