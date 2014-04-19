<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <?php $this->loadCSS();?>
        <?php $this->loadJS();?>         
        <script type="text/javascript" src="<?php echo Router::webroot('js/jquery/tiny_mce/tiny_mce.js'); ?>"></script>  
        <script type="text/javascript" src="<?php echo Router::webroot('js/ckeditor/ckeditor.js'); ?>"></script>     
	<title><?php echo isset($title_for_layout)?$title_for_layout : 'Admin.'.Conf::$website;?></title>
	
</head>
<body class="backend_admin">

        <div class="navbar-ws navbar navbar-fixed-top">            
                <ul class="nav">
                        <li><a class="weSport" id="weSportBrand" href="<?php echo Router::url('admin/');?>" title="Du Sport ! Vite !">
                                <img class="logo" src="<?php echo Router::webroot('img/LOGO.gif');?>" alt="Logo">                           
                                <img class="wesport" src="<?php echo Router::webroot('img/BRAND.png');?>" alt="WeSport.fr">                     
                        </a>
                        </li>
                        <li><a href="<?php echo Router::url('/'); ?>"><span class="ws-icon-home"></span> Site</a></li>                             
                        <li><a href="<?php echo Router::url('admin/pages/index'); ?>"><span class="ws-icon-menu"></span> Pages</a></li>
                        <li><a href="<?php echo Router::url('admin/events/index'); ?>"><span class="ws-icon-menu"></span> New Events</a></li>
                        <li><a href="<?php echo Router::url('admin/pages/request');?>"><span class="ws-icon-cog"></span> Requete</a></li>
                        <li><a href="<?php echo Router::url('admin/sports/index');?>"><span class="ws-icon-star"></span> Sports</a></li>
                        <li><a href="<?php echo Router::url('admin/mailing/index');?>"><span class="ws-icon-envelop"></span> Mailing</a></li>
                        <li><a href="<?php echo Router::url('admin/pages/blog');?>"><span class="ws-icon-book"></span> Blog</a></li>
                        <li><a href="<?php echo Router::url('users/logout'); ?>"><span class="ws-icon-exit"></span> Deconnexion</a></li>
                </ul>
        </div>

        <div class="container mainContainer">
                <?php echo $this->session->flash();?>
                <?php echo $content_for_layout;?>
        </div>
</body>

<script type="text/javascript">

    /*===========================================================
        Set security token
    ============================================================*/
    var CSRF_TOKEN = '<?php echo $this->session->token(); ?>';

    /*===========================================================
        Language of the page
    ============================================================*/
    var Lang = '<?php echo $this->getLang(); ?>';   


    
    tinyMCE.init({
            // General options
            mode : "specific_textareas",
            editor_selector : "wysiwyg",
            theme : "advanced",
            plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

            //Valid element that will be not removed
            valid_elements : "em/i,strike,u,span,strong/b,h1[id|dir|class|align|style],h2[id|dir|class|align|style],h3[id|dir|class|align|style],h4[id|dir|class|align|style],h5[id|dir|class|align|style],div[align],br,#p[align],-ol[type|compact],-ul[type|compact],-li",
            // plugins : "paste",
            // paste_text_sticky : true,
            // setup : function(ed) {
            //     ed.onInit.add(function(ed) {
            //       ed.pasteAsPlainText = true;
            //     });
            //   },

            // Theme options
            theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,

            // Skin options
            skin : "o2k7",
            skin_variant : "silver",

            convert_urls:true,
            relative_urls:false,
            remove_script_host:false,

            // Example content CSS (should be your site CSS)
            //content_css : "css/example.css",

            // Drop lists for link/image/media/template dialogs
            template_external_list_url : "js/template_list.js",
            external_link_list_url : "js/link_list.js",
            external_image_list_url : "js/image_list.js",
            media_external_list_url : "js/media_list.js",

            // Replace values for the template plugin
            template_replace_values : {
                    username : "Some User",
                    staffid : "991234"
            }
    });
</script>

</html>