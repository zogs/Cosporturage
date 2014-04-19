var spanLoader = '<span class="ajaxLoader" id="ajaxLoader"></span>';
var ajaxLoader = "#ajaxLoader";

// enable vibration support
navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
 

/*===========================================================
	JQUERY 
============================================================*/
$(document).ready(function(){
	

/*===========================================================
	// Autocomplete cityName input
============================================================*/

$('input#cityName').click(function(e){ 		
		$(this).val('');
		$('input#cityID').val('');
});
	
    $('#cityName').typeahead({
    	name:'city',
    	valueKey:'name',
		limit: 10,
		minLength: 3,
		allowDuplicates: true,	
		//local: array of datums,
		//prefetch: link to a json file with array of datums,
		remote: $("#cityName").attr('data-autocomplete-url')+'?query=%QUERY',			
		template: [ '<p class="tt-name">{{name}}</p>',
					'<p class="tt-sub">{{state}}</p>',
					'<p class="tt-id">{{id}} (à cacher)</p>',
					].join(''),
		engine: Hogan ,

		//header: 'header',
		//footer: 'footer',

	}).on('typeahead:selected',function( evt, datum ){
		$(this).val(datum.name);		
		$('#cityID').val( datum.id );
		$('#cityName').val(datum.name);
	}).on('typeahead:opened',function(e){
		$("#cityName").addClass('open');		
	}).on('typeahead:closed',function(e){
		$("#cityName").removeClass('open');
		
	});


	/*===========================================================
		Security token send with AJAX /!\
	============================================================*/
	jQuery.support.cors = true; // important for IE8
	
	$(document)	
		//Add token in POST data	
		.ajaxSend(function(elm,xhr,settings){

			// console.log(elm);
			// console.log(xhr);
			xhr.overrideMimeType('text/html; charset=UTF-8');
			if (settings.type == "POST") {
				if(settings.data) {
					settings.data += "&token="+CSRF_TOKEN;				
				}		
			}
		})
		//Log the ajax object
		.ajaxComplete(function(event,xhr,settings){
			console.log(settings);
		})
		//alert error in url
		.ajaxError(function(event,jqxhr,settings,thrownError){
				
			console.log(thrownError);
			
		});
		//Default value for all ajax request
		$.ajaxSetup({			
				cache: false,
				data : null,
				// async: false, //fait planter firefox
				crossDomain: true,				
				xhrFields: {
				    withCredentials: true,
				 }
			})

	/*===========================================================
		Disable hover when scrolling
	============================================================*/
	/*
		var body = document.body,
   		 timer;

		window.addEventListener('scroll', function() {
		  clearTimeout(timer);
		  if(!body.classList.contains('disable-hover')) {
		    body.classList.add('disable-hover')
		  }
		  
		  timer = setTimeout(function(){
		    body.classList.remove('disable-hover')
		  },100);
		}, false);
	*/

	/*===========================================================
		Tooltip bootstrap
	============================================================*/
	
	$('.tooltiptop').tooltip( { delay: { show: 200, hide: 100 }} );

	$('.tooltipbottom').tooltip( { placement : 'bottom', delay: { show: 200, hide: 100 }} );

	/*=====================
		Submit Button
	=======================*/
	bindEvent_submitButton();
	function bindEvent_submitButton(){
		$('input.btn-ws-submit,a.btn-ws-submit,button.btn-ws-submit').on('click',function(){
			var btn = $(this);
			btn.addClass('btn-ws-submit-clicked');
			setTimeout(function(){ btn.removeClass('btn-ws-submit-clicked');},4000);
		})
	}



	/*===========================================================
		Time Ago
	============================================================*/
	// French translation
	jQuery.timeago.settings.strings = {
	   prefixAgo: "il y a",
	   prefixFromNow: "d'ici",
	   seconds: "moins d'une minute",
	   minute: "une minute",
	   minutes: "%d minutes",
	   hour: "une heure",
	   hours: "%d heures",
	   day: "un jour",
	   days: "%d jours",
	   month: "un mois",
	   months: "%d mois",
	   year: "un an",
	   years: "%d ans"
	};
		
	bindEvent_timeago();
	function bindEvent_timeago(){
		$('abbr.timeago').timeago();
	}

	/*===========================================================
		EXPANDABLE
		@param data-maxlenght
		@param data-expandtext
		@param data-collapsetext
	============================================================*/
	//bindEvent_expandable();
	function bindEvent_expandable(){
		$('.expandable').on('click',function(e){
			$(this).expander({
	    		slicePoint: $(this).attr('data-maxlength'),
	    		expandPrefix: ' ',
	    		expandText: $(this).attr('data-expandtext'),
	    		userCollapseText: $(this).attr('data-collapsetext'),
	    		userCollapsePrefix: ' ',
	    	});
		});
	}


		
	/*===========================================================
		GEO LOCATE
	============================================================*/
	bindEvent_geoSelect();
	function bindEvent_geoSelect(){
		$("select.geo-select").select2();
    	$("select#CC1").select2({ formatResult: addCountryFlagToSelectState, formatSelection: addCountryFlagToSelectState});
	}
   




    /*==================================
    	MOBILE MENU
    ===================================*/

    if($("#mobileMenus").length!=0 && $("#mobileMenus").css('display')!='none'){
    	
		$("#mobMenuLeft").mmenu({

		});	

		$("#mobMenuRight").mmenu({
			position: "right",
			zposition: "front",
			slidingSubmenus: false
		});
    	
    }


    /*==================================
    	SPORTS
    ===================================*/

    if($("select#sport").length != 0){

    	$("select#sport").select2({ formatResult: addSportIcon, formatSelection: addSportIcon});
    	
    }


    /*==================================
    	TOGGLE SERIE OF EVENTS
    ===================================*/
    $(".showListSerie").click(function(){			
			$(this).parent().find('.listserie').toggle();
			return false;
	});

	/*===========================================================
		COMMENT SYSTEM
	============================================================*/
    if($("#comments").length != 0){
		
		//Default params		       
        pageComments = 1;
        newestCommentId = 0;
        loadingComments = false;
        showComments_url = $("#comments").attr('data-comments-url'); 
        config = $("#comments").attr('data-comments-config');
        config = stripslashes(config); 
        config = JSON.parse(config);                      
        enableInfiniteScrolling = config.enableInfiniteScrolling;

		showComments_params = {};
		CurrentUrlPreview = '';

        //Allowed preview comment
        enablePreviewComment = true;

        //automatic refreshing comments
        refreshComments = false;
        refreshComments_s = 600;
        setIntervalRefresh(refreshComments_s);       
        
        //tchecking new comments
        tcheckComments = false;
        tcheckComments_s = 60;
        setIntervalTcheck(tcheckComments_s);                

        //Launch display comments
        show_comments();
        //Init infinite comments
        if( true === enableInfiniteScrolling ){
        	infiniteComment();        	
        }
        
		

        /*===========================================================
        	refresh button
        ============================================================*/
        $("a#refresh_com").on('click',function(){            
            clean_params('page','order','type','newer','bottom');
            pageComments = 1;
            construct_params('?page=1');
            show_comments('clear');
            return false;
        });
        /*===========================================================
        	type of comment (not use yet)
        ============================================================*/
        $("a.type_com").bind('click',function(){
            $("a.type_com").each(function(){ $(this).removeClass('dropdown_active'); });
            $(this).addClass('dropdown_active');
            var param = $(this).attr('href');            
            construct_params(param);
            construct_params('?page=1');
            pageComments=1;
            show_comments('clear');
            return false;            
        });
        /*===========================================================
        	select refresh timer (not use yet)
        ============================================================*/
        $("a.set_refresh").bind('click',function(){
            $("a.set_refresh").each(function(){ $(this).removeClass('dropdown_active'); });
            $(this).addClass('dropdown_active');
            var second = $(this).attr('href');
            setIntervalRefresh(second);
            return false;
        });



        /*===========================================================
        	show more comments Button
        ============================================================*/
        $("#showMoreComments").bind('click',function(){

        	showMoreComments();

        	return false;
        });
		

	    /*===========================================================	        
	    	Save comment in ajax request
	    ============================================================*/
	    $("#submitComment").on('click',function(e){


	    	//Stop event propagation
	    	e.preventDefault();

	    	//Update the CKeditor field
	    	CKupdate();
	    	
	        //get the data from the form
	        var form = $("#commentForm");		        
	        var data = form.serialize();
	    	
	        var url = form.attr('action');
	        var textarea = $("#commentTextarea");
	        var text = textarea.val();
	        var title = $("#commentTitle");
	        var preview = $("#commentPreview");
	        var media = $("input#media");
	        var media_url = $('input#media_url');

	        //if there is a media preview
	        if(preview.html()!="") {

	        	//remove uneccessery preview elements
	            preview.find(".previewMedia-totalthumbnails").remove();
	            preview.find(".previewMedia-thumbnail.hide").remove();
	            preview.find(".previewMedia-close").remove();
	            //set media field with preview content
	            media.val(preview.html());

	            //set media_url with currentUrlPreview
	            media_url.val(CurrentUrlPreview);           
	        }
	        

	        //if comment not empty
	        if( trim(text) != "") {

	        	//send POST request
	            $.ajax({
	            	url:url, 
	            	type:"POST", 
	            	data: data, 
	            	dataType: 'json',
	                success: function( com ){

	                    if(!com.fail){
	                    	//display new comments
	                        $("#comments").prepend(com.content);	                        
	                        //reset textarea
	                        textarea.val('');
	                        //reset title
	                        title.val('');
	                        //reset preview container  
	                        preview.empty();
	                        //reset hidden media wrapper
	                        media.val('');
	                        //bind event
	                        bindEventToPosts();
                      
	                    }   
	                    else {
	                        alert(com.fail);
	                    }                                                                   		                     
	                  }	                          	               
	            });
            } 
	        
	        return false;

	    });

	    $("#commentTextarea").on('focus',function(){ $(this).css('height','80px'); });




	    /*===========================================================
	    	Autodetect URL in comment textarea
	    ============================================================*/

	    $('#commentTextarea').bind('keyup change',function(event){

	    	if(!enablePreviewComment) return false;

	        var content = $(this).val();        
	        var previewURL = $(this).attr('data-url-preview');
	        var urlRegex = /\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:(?:[^\s()<>.]+[.]?)+|\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\))+(?:\((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/gi;
	        //var pattern = new RegExp("http\:\/\/[a-zA-Z0-9\-\.\_]+\.[a-zA-Z]{2,4}\/?/\\S*\\s*","gi"); 

	        //on each key entered
	        if(event.type=='keyup'){

	        	//pour éviter l'appel ajax trop fréquent on vérifie que la chaine finisse par un espace
	        	var spaceRegex = /\s$/;
	        	var spaceRegex = new RegExp(spaceRegex);
	        	var space = spaceRegex.exec(content);	        	
	        	if(space==null) return false;

	        	//On vérifie et récupére la presence d'une URL dans le contenu	        		        	  
	        	var keyUpMatches = new RegExp(urlRegex);		        							
	        	var urlMatches = keyUpMatches.exec(content); 
	        }

	        //on mouse event ( like focus out textarea)
	        if(event.type=='change'){

	        	var changeMatches = new RegExp(urlRegex);
	        	var urlMatches = changeMatches.exec(content);
	        }
	                                            

	        // console.log('event'+event.type+' content='+content);
	        // console.log('match= --'+urlMatches+'--');
	        // console.log('currenturl= --'+CurrentUrlPreview+'--');

	        if(urlMatches!=null && trim(urlMatches[0])!=trim(CurrentUrlPreview)){

	            $("#commentPreview").empty().html('loading...');

	            var url = urlMatches[0];
	            CurrentUrlPreview = url; 
	            url = encodeURIComponent(url); //good encode for GET parameter      

	            $.ajax({
	                type : 'GET',
	                url : previewURL,
	                data : {url:url},
	                success: function( data ){

	                    var preview = data.content; 
	                    
	                    $("#commentPreview").empty().html(preview);
	                    $("input#media").val(preview);
	                    $("input#type").val(data.type);

	                    bindEvent_previewMedia();

	                },
	                dataType : 'json'
	            });
	            

	        }
	        if(urlMatches == null) {
	            $("#commentPreview").empty();
	        }
	        

	    });



	   
	} 

	//end jquery listener
	//=================================================
	//==================================================



    /*===========================================================	        
    SHOW COMMENTS
    @param use params in showComments_params[]
    @param use $arguments[] , string clear,newest,start
    ============================================================*/ 
	function show_comments( action ){
		
		$("#ajaxLoader").show();		
		$("#loadingComments").show();

        if(action==undefined) action = 'clear';

        clean_params('start','page','newest','lang');
      	
        if(action=='new') {
        	 
             construct_params("?newest="+newestCommentId);             
        }

        if(action=='bottom'){

            construct_params("?start="+newestCommentId);
            construct_params("?page="+pageComments);    
        }

        if( Lang != undefined )
        	construct_params("?lang="+Lang);

        
        //clean_params(showComments_params);
        construct_params("?config="+JSON.stringify(config));
        
        //console.log(JSON.stringify(showComments_params));
		$.ajax({
		  type: 'GET',
		  url: showComments_url,
		  data: arrayParams2string(showComments_params),
		  success: function( data ) 
	          {	   

	          	//console.log(data);
	    		//Si pas de commentaires return false
	    		if(data.commentsNumber==0 && data.commentsTotal==0) {
	    			
	    			$("#noCommentYet").show();
	    			$("#loadingComments").hide();
	    			$("#ajaxLoader").hide();
	    			return false;    			
	    		}

	    		
	            //var html = $('<div />').html(data.html).text(); //Jquery trick to decode html entities
	            var html = data.html;
	    		

	            if(html!=''){

		            

					if(action=='clear'){

						//id datedesc Get id of the first comment
						if(showComments_params['order']=='datedesc' || showComments_params['order']==undefined){
		                	
			                newestCommentId = data.firstCommentID;
			               
			            }

		                $("#badge").empty().hide();                        
		                $("#noMoreComments").hide();
		                $('#comments').empty().append(html);

		            }
		            else if(action == 'new'){
		            	$("#comments").prepend(html)
		            }
		            else if(action=='bottom') {                           
		                $('#comments').append(html);                       
		            }
		        }           	

		       if(action!='new') {
	            	
	            	if(data.commentsNumber<=0 || data.commentsNumber != data.commentsPerPage){
	            	
		            	 $("#showMoreComments").hide();
		       	     	 $("#noMoreComments").show();		       	     
	       	    	}
		       	    else {
		   	    		 $("#showMoreComments").show();			       	     
			       	     $("#noMoreComments").hide();
		       	    }
		       	}

		       	//bind events
		       	bindEventToPosts();

	            $("#ajaxLoader").hide();	                
	            $("#loadingComments").hide();
	            loadingComments = false;    
				
			},
		  dataType: 'json'
		});

	}

	/*===========================================================	        
	INFINITE SCROLL
	if scroll to the bottom of page
	increment page and call show_comments
	==========================================================*/								
    function infiniteComment() {

        $(window).scroll(function(){
            
            var ylastCom = $("#bottomComments").offset(); 
            var scrollPos = parseInt($(window).scrollTop()+$(window).height());
            //console.log(ylastCom.top+' <= '+scrollPos);
            if( (ylastCom.top <= scrollPos ) && loadingComments===false ) 
            {   
            	
                loadingComments = true;
                new_page        = pageComments+1;
                pageComments   = new_page;
                construct_params("?page="+new_page);                    
                show_comments('bottom');		                    
                
            }

        });
    };	    


    /*===========================================================
    	Bind Event
    	to comments
    ============================================================*/
    function bindEventToPosts(){
			bindEvent_Post();
			bindEvent_ShowReplies();
			bindEvent_btnCommentReply();
			bindEvent_formCommentReply();
			bindEvent_btnVote();
			bindEvent_moderated();
			bindEvent_launchMedia();
		}


    
    function bindEvent_ShowReplies(){
    	$('#comments')
    	.off('click','a.showReplies')
    	.on('click','a.showReplies',function(e){
    		$(e.currentTarget).parent().next('.hiddenReplies').show();
    		$(e.currentTarget).parent().remove();
    		e.preventDefault();
    		return false;
    	});
    }
    function bindEvent_Post(){
    	$("#comments")
    	.off('mouseenter','.post')
    	.on('mouseenter','.post',function(e){
    		$(e.currentTarget).find('.actions').css('visibility','visible'); 
    	})
    	.on('mouseleave','.post',function(e){
    		$(e.currentTarget).find('.actions').css('visibility','hidden'); 
    	});
    }
    function bindEvent_btnCommentReply(){
    	$('#comments')
		.off('click','a.btn-comment-reply')
		.on('click','a.btn-comment-reply',function(e){
    		var form = $('#formCommentReply').detach();
            var reply_to = $(e.currentTarget).attr('href');
            var reply_login = $(e.currentTarget).attr('data-comlogin');
            var comment_id = $(e.currentTarget).attr('data-comid');

            form.find('input[name=reply_to]').val(reply_to);
            form.find('input[name=content]').val('');
            form.find('textarea').attr('placeholder','Répondre à '+reply_login);
            $("#com"+comment_id).after(form);  

            e.preventDefault();
            return false; 
    	});
    }        
    function bindEvent_formCommentReply(){
    	$('#comments')
    	.off('submit','form.formCommentReply')
    	.on('submit','form.formCommentReply',function(e){
    		
    		var url = $(e.currentTarget).attr('action');
            var datas = $(e.currentTarget).serialize();
            var parent_id = $(e.currentTarget).find('input[name=reply_to]').val();            

            $.ajax({
                type:'POST',
                url: url,
                data: datas,
                success: function( com ){

                   if(!com.fail){
					
					$("#formCommentReply").detach().appendTo('#hiddenFormReply');	                                                            

                    $("#replies"+parent_id).remove();
                    $("#com"+parent_id).replaceWith(com.content);

                   }
                   else {
                        alert( com.fail );
                   }
                },
                dataType:'json'
                });
            
            return false;
    	});
    }
    
    function bindEvent_btnVote(){
    	$('#comments')
    	.off('click','a.btn_vote')
    	.on('click','a.btn_vote',function(e){
    		var badge = $(e.currentTarget).find('.badge');
            var id = $(e.currentTarget).attr('data-id');
            var url = $(e.currentTarget).attr('data-url');
                
            $.post(url,{id:id},function(data){ 

                if(is_numeric(data.note)){
                    badge.html(data.note);
                    badge.show();
                }
                else{
                    alert(data.erreur);
                }
            },'json');
    	});
    }
    function bindEvent_moderated(){
    	$('#comments')
    	.off('click','.commentIsModerate')
    	.on('click','.commentIsModerate',function(e){
    		$(e.currentTarget).next().hide();
	    	$(e.currentTarget).find('a').click(function(){
	    		$(e.currentTarget).parent().next().toggle();
	    		return false;
	    	});
    	});
    }


     /*===========================================================
    	Bind Event
    	to Media Preview
    ============================================================*/
	function bindEvent_previewMedia(){
		bindEvent_closePreviewMedia();
		bindEvent_nextThumbnail();
		bindEvent_prevThumbnail();
		
	}
    function bindEvent_closePreviewMedia(){
    	$('#commentPreview')
    	.off('click','.previewMedia-close')
    	.on('click','.previewMedia-close',function(e){
    		$("#commentPreview").empty();
	        $("input#media").val('');
	        $("input#type").val('com');
    	});
    }
    function bindEvent_nextThumbnail(){
    	$('#next_thumb')
    	.off('click')
    	.on('click',function(e){
    		var img = $('#commentPreview .previewMedia-thumbnails').find('.previewMedia-thumbnail:visible');
	        var next = img.next('.previewMedia-thumbnail');
	        if(next.length>0) {
	            img.addClass('hide');
	            next.removeClass('hide');
	        }
	        e.preventDefault();
	        return false;
    	});
    }
    function bindEvent_prevThumbnail(){
    	$('#prev_thumb')
    	.off('click')
    	.on('click',function(){
    		var img = $('#commentPreview .previewMedia-thumbnails').find('.previewMedia-thumbnail:visible');
	        var prev = img.prev('.previewMedia-thumbnail');     
	        if(prev.length>0){
	            prev.removeClass('hide');   
	            img.addClass('hide');
	        } 
	        e.preventDefault();
	        return false;
    	});
    }	    
    function bindEvent_launchMedia(){
    	$('#comments')
    	.off('click','.previewMedia-thumbnail')
    	.on('click','.previewMedia-thumbnail',function(e){

    		var id = $(e.currentTarget).attr('data-comid');
	        var media = $(e.currentTarget).attr('data-media-url');
	        var type = $(e.currentTarget).attr('data-type');
	       
	        if(type=='video'){

	        	var container = $(e.currentTarget).parent().parent();
	        	container.empty().html(urldecode(media));		            
	        }
	        if(type=='img'){
	            window.open(media,'_newtab');
	        }
	        if(type=='link'){
	            window.open(media,'_newtab');
	        }	  
    	});
    }

    /*===========================================================
    	SHOW MORE COMMENTS
    	add the next page of comments
    ============================================================*/
    function showMoreComments() {

    	loadingComments = true;
    	new_page = parseFloat(pageComments)+1;
    	pageComments = new_page;
    	construct_params("?page="+new_page);
    	show_comments('bottom');

    }

    /*===========================================================
    	CONSTRUCT PARAMS
    	@param string ?param=value
    ============================================================*/
	function construct_params(param){
		if(param!=''){
			var p = [];
			if(strpos(param,'?',0)==0){
				param = str_replace('?','',param);
				p = explode('=',param);
				showComments_params[p[0]] = p[1];	
			}
			else alert('href doit commencer par ?');                
			return param;
		}
	}

    /*===========================================================
    	CLEAN PARAMS
    ============================================================*/
    function clean_params(){
        for(var key in arguments) {   
            for(var cle in showComments_params){                    
                //console.debug(' key:'+arguments[key]+'    cle:'+cle+'   value:'+showComments_params[cle]);
                if(arguments[key]==cle){
                    showComments_params[cle] = 0;
                }                    
            }
        }                         
    }

    /*===========================================================
    	CKEditor update function in order to retrieve html
    ============================================================*/
    function CKupdate(){

        	if(typeof CKEDITOR == 'undefined') return true;
		    for ( instance in CKEDITOR.instances )
		        CKEDITOR.instances[instance].updateElement();
	}

    /*===========================================================
    	??
    ============================================================*/
    function arrayParams2string(array){            
        var str ='';
        for(key in array){  

                str += key+'='+array[key]+'&';
                
        }
        str = str.substring(0,str.length-1);
        return str;
    }

    /*===========================================================
    	SET INTERVAL REFRESH
    ============================================================*/
    function setIntervalRefresh(ms){

    	if(!refreshComments) clearInterval(refreshComments);
    	else refreshComments = setInterval(function(){ show_comments('new');}, ms*1000)       
    }
    /*===========================================================
    	SET INTERVAL TCHECK
    ============================================================*/
    function setIntervalTcheck(ms){
    	
        if(!tcheckComments) clearInterval(tcheckComments);            
        else tcheckComments = setInterval(tcheckcomments,ms*1000);        
    }

    /*===========================================================
    	TCHECK COMMENTS
    ============================================================*/
    function tcheckcomments(){

    	//if not datedesc, cancel tcheck comment
        if(showComments_params['order']=='datedesc' || showComments_params['order']==undefined) ;
        else return false;

        var obj = $('#comments');
        var badge = obj.find('#badge');
        var url = obj.attr('data-url-count-com');
        url += '&newest='+newestCommentId;        

        $.ajax({
            type: 'GET',
            url: url,
            success: function(data){
                
                if(is_numeric(data.count)){
                    if(data.count>0){
                        badge.empty().html(trim(data.count));
                        badge.show();
                    }
                    else {
                        badge.hide();
                    }
                }

            },
            dataType: 'json'
        });
    }





	/*===========================================================
		FORM AJAX
	============================================================*/
	/*$('form.form-ajax').livequery('submit',function(){

		var url = $(this).attr('action');
		var params = $(this).serialize();

		$.ajax({
			type : 'POST',
			url : url,
			data : params,
			contentType: 'multipart/form-data',
			success : function( data ){
				$('#myModal').empty().html( data );
			},
			dataType: 'html'
		});
		return false;
	});
	*/

	/*===========================================================
		VALIDATE PASSWORD FORM
	============================================================*/
	$("#form_register input#password").bind('change',function(){
			
			$("#control-password").removeClass('control-error').addClass('control-success');
	});

	$("#form_register input#confirm").bind('change',function(){

			if($(this).val()==$('#form_register input#password').val()){
				$('#control-password, #control-confirm').removeClass('control-error').addClass('control-success');
				$('#control-confirm .controls').find('p.help-inline').addClass('hide').empty();					
			}
			else{
				$('#control-password, #control-confirm').removeClass('control-success').addClass('control-error');
				$('#control-confirm .controls').find('p.help-inline').removeClass('hide').empty().html('Les mots de passe ne sont pas identique');				
			}
	});

	


	/*===========================================================
		CHECK DUPLICATE MAIL AND LOGIN
	============================================================*/

	$("#form_register .inputLogin,#form_register #email").bind('change',function(){

		var input = $(this);
		var control = input.parent().parent();
		var help = input.next('p.help-inline');
		var value = $(this).val();
		var url = $(this).attr('data-url');
		var type = $(this).attr('name');

		var c = forbiddenchar(value);
		if(c && type=='login'){
			control.addClass('control-error');
			help.removeClass('hide').empty().html("Le caractère suivant n'est pas autorisé : "+c);
		}

		if(type=='email') {

			var regex = new RegExp("[_a-zA-Z0-9-+]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,4})","g");
			var valid = value.match(regex);
			if(valid){
				control.removeClass('control-error');
				help.addClass('hide').empty();

				$.ajax({
					type: 'GET',
					url: url,
					data: {type : type, value : value},
					success: function(data){
						console.log(data);					
						if(data.error){						
							control.removeClass('control-success');					
							control.addClass('control-error');
							help.removeClass('hide').empty().html( data.error );
						}
						else {
							control.removeClass('control-error');
							control.addClass('control-success');						
						}
					},
					dataType: 'json'
				});				
			}
			else{
				control.addClass('control-error');
				help.removeClass('hide').empty().html("L'adresse doit être une adresse email valide");
			}

		}

		return false;
	});

	function forbiddenchar(string){		
		var carac = new RegExp("[ @,\.;:\/\\!&$£*§~#|)(}{]","g");
		var c = string.match(carac);
		if(c){
			if(c==' ') return '-espace-';
			return c;	
		} 
	}



});



/*============================
	SELECTION GEOGRAPHIQUE
=============================*/

function showRegion(value,region)
{

	$("#"+region).nextAll('select').empty().remove();
	$("#"+region).next('.select2-container').nextAll('.select2-container').empty().remove();

	if(region=='city') return false;

	if(value!='')
	{		
		CC1 = $('#CC1').val();
		ADM1 = $('#ADM1').val();	
		ADM2 = $('#ADM2').val();
		ADM3 = $('#ADM3').val();
		ADM4 = $('#ADM4').val();

		var url = $('#submit-state').attr('data-url');

		$.ajax({
			type : 'GET',
			url : url,
			data : { parent:value, ADM: region, CC1:CC1, ADM1:ADM1, ADM2:ADM2, ADM3:ADM3, ADM4:ADM4 },
			dataType: 'json',
			success: function(data){
				
				if(trim(data)!='empty'){ 
					//append the select box after the preivous one				
					$('#'+region).next('.select2-container').after(data.SelectELEMENT);
					//pass style and class from parent elect boxe to new select box
					var classes = $('#'+region).attr('class');
					$('#'+data.SelectID).addClass(classes);
					var css = $('#'+region).attr('style');
					$("#"+data.SelectID).attr('style',css);
					//Init the select2 plugin
					$("#"+data.SelectID).select2();
				}
			}
		});
	}
}

//Function for select2 plugin
function addCountryFlagToSelectState(state) {

	return "<img class='flag flag-"+state.id.toLowerCase()+"' />"+state.text;
}


function addSportIcon(sport){

	if(trim(sport.id)!='')
		return '<span class="ws-icon ws-icon-small ws-icon-'+sport.id+'"></span> '+sport.text;		
	else 
		return sport.text;
}

/*============================
	SELECTION CATEGORY
=============================*/
function showCategory(parent,level){

	var url = $('#submit-category').attr('data-url');

	$.ajax({
		type:'POST',
		url:url,
		data: { parent:parent, level:level},
		success: function(data){
			//alert(data);
			if(trim(data)!='empty'){
				$('#cat'+level).empty().remove();
				$('#cat'+(level-1)).after(data);
			}
		}
	});
}



//=============================
//    LOCAL STORAGE
//============================

jQuery(function($){

	$.fn.formBackUp = function(){

		if(!localStorage){
			return false;
		}

		var forms = this;
		var datas = {};
		var ls = false;
		datas.href = window.location.href;

		if(localStorage['formBackUp']){
			ls = JSON.parse(localStorage['formBackUp']);
			if(ls.href = datas.href){
				for( var id in ls){
					if(id != "href"){
						$("#"+id).val(ls[id]);
						datas[id] = ls[id];
					}
				}
			}
		}

		forms.find('input,textarea').keyup(function(){
			datas[$(this).attr('id')] = $(this).val();
			localStorage.setItem('formBackUp',JSON.stringify(datas));
		});

		forms.submit(function(e){
			localStorage.removeItem('formBackUp');
		});
	}

});