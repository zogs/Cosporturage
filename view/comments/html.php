 <?php 
    header ('Content-type: text/html; charset=utf-8');

    //level of replies
    global $levelReplies;
    $levelReplies=1; //bu default to 1

    function show_comments($coms,$user,$config,$main=true){

        global $levelReplies;

        $html='';
        if(!is_array($coms)) $coms = array($coms);
        foreach ($coms as $com) {            

            //create html comment
            $html.= html_comment($com, $user, $config);

            //if we want to display the reply form for this level of replies
            if($levelReplies <= $config->levelFormReplyToDisplay){
                $html .= html_formReply($com,$user,$config);
            }

            $html .= '<div class="replies" id="replies'.$com->id.'">'; 
            //create html replies
            if(!empty($com->replies)){  
                                  
                $levelReplies++;    
                $html.= show_replies($com,$user,$config);                 
              
            }
            $html .= '</div>';  
        
            //if this is the main thread, reset the level of replies to 1
            if($main==true) $levelReplies = 1;
            
        }

        return $html;
    }




    function show_replies($com,$user,$config){        


        $html='';
        //if display reply if there are replies
        if($config->displayReply && !empty($com->replies)){

            $replyshowed = array_slice($com->replies, 0, $config->repliesDisplayPerComment);
            $replyhidden = array_slice($com->replies, $config->repliesDisplayPerComment);

            $html .= show_comments($replyshowed,$user,$config,false);

            if(!empty($replyhidden)){
               
            $html .= '<div class="showHiddenReplies">';
            if($config->repliesDisplayPerComment==0){
                $html .= '<a href="#" class="showReplies" rel="nofollow">Afficher les '.count($replyhidden).' réponse(s)</a>';
            }
            else {
                $html .= '<a href="#" class="showReplies" rel="nofollow">Afficher '.count($replyhidden).' autres réponse(s)</a>';
            }
            $html .= '</div>';
            $html .='<div class="hiddenReplies">';
            $html .= show_comments($replyhidden,$user,$config,false);
            $html .= '</div>';
            }
        }
       
        

        return $html;

    }

    function html_formReply($com,$user,$config){

        $html= "<form class='formCommentReply' action='".Router::url('comments/reply')."' method='POST'>                
                <img class='userAvatarCommentForm' src='".$user->getAvatar()."' />
                ";
        if($user->user_id!=0){
            $html .= "<textarea name='content' class='formComment' placeholder='".$config->placeholderReplyForm."'></textarea> 
                        <input class='btn btn-small' type='submit' name='' value='Envoyer'>";
        }
        else 
        {
            $html .= "<textarea disabled='disabled' name='content' placeholder='".$config->placeholderNeedLog."'></textarea>";
        }
            
            $html .= "  <input type='hidden' name='context' value='".$config->context."' />
                        <input type='hidden' name='context_id' value='".$config->context_id."'/>
                        <input type='hidden' name='type' value='com' />
                        <input type='hidden' name='reply_to' value='".$com->id."' />                            
                        
                    </form>" ; 

        return $html;
    }


    //Renvoi le html d'un commenaitre
    //@param $com {objet} du com
    //@param $cuser {objet} de l'user
    function html_comment($com,$cuser,$config){
        
        ob_start();
        ?>
            <div class="thread post <?php echo ($com->reply_to!=0)? 'reply':'';?> <?php echo 'type_'.$com->type;?> <?php echo (!empty($com->title))? 'type_news':'';?>" id="<?php echo 'com'.$com->id; ?>">  
                
                <div class="logo">
                    <img src="<?php echo $com->user->getAvatar() ?>" alt="Logo/Avatar" />
                </div>
                    
            
                <div class="content">   
               

                    <?php if(!empty($com->title)): ?>                 
                    <div class="title"><a href="<?php echo Router::url($com->context.'/post/'.$com->id.'/'.String::slugify($com->title));?>"><?php echo $com->title; ?></a></div>                                
                    <?php endif; ?>


                    <div class="user"><?php echo $com->user->getLogin();?></div>
                    <abbr class="date timeago" title="<?php echo $com->date;?>"><?php echo $com->date;?></abbr>
                    
                    <div class="content_txt comment_<?php echo $com->type;?>">    
                                            
                        <?php if($com->isModerate() ): ?>
                        <span class="commentIsModerate"><?php echo $com->isModerate('msg'); ?> <a href="#" rel="nofollow"> Afficher quand même </a></span>
                        <?php endif; ?>

                        <div>
                        <?php

                        switch ($com->type) {
                            case 'com':
                                $content = $com->content;
                                break;
                            
                            case 'video':
                                $content = $com->media . $com->content ;
                                break;

                            case 'img':
                                $content = $com->media . $com->content ;
                                break;

                            case 'link':
                                $content = $com->media . $com->content ;
                                break;

                            default:
                                $content = $com->content;
                                break;
                           
                        }

                         echo str_replace("\\",'',$content);

                        ?>
                        </div>

                        <?php if ($cuser): ?>
                        <div class="actions">                                 
                            
                            <?php if($cuser->user_id!=0): ?>

                                <?php if($config->allowVoting): ?>
                                <a class="btn-vote bubbtop" title="Like this comment" data-url="<?php echo Router::url('comments/vote/'.$com->id); ?>" rel="nofollow" >                      
                                        <span class="badge badge-info" <?php if ($com->note == 0): ?>style="display:none"<?php endif ?>><?php echo $com->note; ?></span>
                                    Like                         
                                </a> 
                                <?php endif; ?>

                                <?php if($config->allowReply): ?>             
                                <a class="btn-comment-reply" data-comid="<?php echo $com->id; ?>" data-comlogin="<?php echo $com->user->getLogin();?>" href="<?php echo $com->id;?>" rel="nofollow"><?php echo $config->linkReply;?></a>                                
                                <?php endif; ?>

                                

                                
                            <?php else: ?>
                                <span>Log in to reply</span>
                            <?php endif;?>
                                                
                        </div>
                        <?php endif; ?>
                    </div> 
                </div>             
            </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }


?>