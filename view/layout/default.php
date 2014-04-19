<?php 
								
	$menu = $this->request('pages','getMenu',array('top'));				
	$bottommenu = $this->request('pages','getMenu',array('bottom'));
				
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<!-- 
    888       888                 .d8888b.                            888         .d888             
    888   o   888                d88P  Y88b                           888        d88P"              
    888  d8b  888                Y88b.                                888        888                
    888 d888b 888  .d88b.         "Y888b.   88888b.   .d88b.  888d888 888888     888888 888d888 
    888d88888b888 d8P  Y8b           "Y88b. 888 "88b d88""88b 888P"   888        888    888P"   
    88888P Y88888 88888888 888888      "888 888  888 888  888 888     888        888    888         
    8888P   Y8888 Y8b.           Y88b  d88P 888 d88P Y88..88P 888     Y88b.  d8b 888    888     
    888P     Y888  "Y8888         "Y8888P"  88888P"   "Y88P"  888      "Y888 Y8P 888    888     
                                            888                                                     
                                            888                                                     
                                            888                                                     

-->
<?php 

//Si cette page est un objet OpenGraph on recupere les balises metas qui vont bien

if(isset($this->OpenGraphObject)) $openGraph = $this->OpenGraphObject;;

?>
<head <?php if(isset($openGraph['head'])) echo $openGraph['head'];?>>
	
	<title><?php echo isset($title_for_layout)?$title_for_layout : Conf::$website;?></title>
	<meta charset='utf-8'> 
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="description"content="<?php echo (!empty($description_for_layout))? $description_for_layout : '';?>" />
	<meta name="keywords" content="<?php echo (!empty($keywords_for_layout))? $keywords_for_layout : '';?>" />
	<meta name="google-site-verification" content="SeofOTfUyHE48_yX-s-Q2BUdqWuTiXZiX9fSkdGgZ9w" />
	<meta name="robots" content="index,follow" />

	<?php 
	//Open Graph special meta tags
	if(isset($openGraph['metas'])) echo $openGraph['metas']; 

	?>

	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=3">
	<meta http-equip="cache-control" content="no-cache">
	<link rel="icon" type="image/png" href="<?php echo Router::webroot('img/LOGO.gif');?>">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo Router::webroot('img/wesport.ico');?>">
	<?php $this->loadCSS();?>
	<?php $this->loadJS();?>	
	
</head>
<body data-user_id="<?php echo $this->session->user()->getID(); ?>" data-display-demo="<?php echo (!empty($display_demo))? 1 : 0;?>">
	
	<div>
		<div class="navbar navbar-fixed-top navbar-ws">

			<div class="mobileMenus" id="mobileMenus">
				<div class="mobileHead">
					<a href="#mobMenuLeft"><span class="ws-icon-menu"></span></a>
					<a class="weSport" href="<?php echo Router::url('');?>" title="Du Sport ! Vite !">
			      			<img class="logo" src="<?php echo Router::webroot('img/LOGO.gif');?>" alt="Logo">				      	  	
			      			<img class="wesport" src="<?php echo Router::webroot('img/BRAND.png');?>" alt="WeSport.fr">
					</a>					
					<a href="#mobMenuRight"><span class="ws-icon-home"></span></a>
												
				</div>
				<div id="mobMenuLeft">								
					<ul>
					<?php 
						$mobmenu = array_merge($menu,$bottommenu);
						foreach($mobmenu as $page):
						 ?>
						<li class="<?php echo ($page->isCurrentPage($this->request))? 'mm-selected' : '';?>"><a href="<?php echo Router::url($page->slug);?>" ><span class="mm-icon ws-icon-<?php echo $page->icon;?>"></span> <?php echo $page->title;?></a></li>

					<?php endforeach; ?>																
					</ul>
				</div>	
				<div id="mobMenuRight">								
					<ul>											
					<?php if($this->session->user()->isLog()):?>	
						<li><a href="<?php echo Router::url('users/account');?>"><span class="mm-icon ws-icon-cog"></span> Mon Compte</a>
							<ul>
								<li><a href="<?php echo Router::url('users/account/account');?>">Compte</a></li>
								<li><a href="<?php echo Router::url('users/account/profil');?>">Profil</a></li>
								<li><a href="<?php echo Router::url('users/account/avatar');?>">Avatar</a></li>
								<li><a href="<?php echo Router::url('users/account/password');?>">Mot de passe</a></li>
								<li><a href="<?php echo Router::url('users/account/mailing');?>">Mailing</a></li>
							</ul>
						</li>							
						<li><a href="<?php echo Router::url('users/logout'); ?>"><span class="mm-icon ws-icon-exit"></span> Déconnexion</a></li>
					<?php else: ?>
						<li><a href="<?php echo Router::url('users/login');?>"><span class="mm-icon ws-icon-enter"></span> Connexion</a></li>
							
						<li><a href="<?php echo Router::url('users/register');?>"><span class="mm-icon ws-icon-quill"></span> Inscription</a></li>
					<?php endif; ?>						
					
						<li class="social-btn"><a target="_blank" rel="nofollow" title="Nous suivre sur Twitter" href="https://twitter.com/weSportfr"  ><img src="<?php echo Router::webroot('img/social_flatls_twitter.png');?>" alt="Twitter"/>  Twitter</a></li>
						<li class="social-btn"><a target="_blank" rel="nofollow" title="Notre page facebook" href="https://www.facebook.com/pages/We-sport/642996032385245" ><img src="<?php echo Router::webroot('img/social_flatls_facebook.png');?>" alt="Facebook"/>  Facebook</a></li>	
						
					</ul>
				</div>					
			</div>


			<div class="desktop-menu" id="desktop-menu">
				<ul class="nav">
					<li>
						<a class="weSport" id="weSportBrand" href="<?php echo Router::url('');?>" title="Du Sport ! Vite !">
				      			<img class="logo" src="<?php echo Router::webroot('img/LOGO.gif');?>" alt="Logo">				      	  	
		      					<img class="wesport" src="<?php echo Router::webroot('img/BRAND.png');?>" alt="WeSport.fr">			      	  	
						</a>
					</li>
					<li class="searchbar" id="menu-searchbar">
						<?php if($this->isHomepage()): ?>
						<form action="<?php echo Router::url('home/'.$this->cookieEventSearch->read('date'));?>" method="GET" id="formCity">
							<span class="search-bar-icons">
								<label class="search-bar-icon search-bar-icon-default" for="cityName"><span class="ws-icon ws-icon-office"></span></label>
								<a class="search-bar-icon search-bar-icon-reset  tooltipbottom" title="Supprimer la ville" href="?cityName=&cityID=" rel="nofollow"><span class="ws-icon ws-icon-close"></span></a>						
							</span>

							<input 
							type="text" 
							id="cityName" 
							name="cityName" 
							class="cityName tooltipbottom" 							
							title="Taper les premières lettres et sélectionner votre ville dans la liste"
							value="<?php echo ($this->cookieEventSearch->notempty('cityName'))? $this->cookieEventSearch->read('cityName') : 'Votre ville ?';?>" 
							placeholder="Votre ville ?" 
							autocomplete='off' 
							data-autocomplete-url="<?php echo Router::url('world/suggestCity');?>"
							>																							
							<?php echo $this->Form->input('cityID','hidden',array("value"=>$this->cookieEventSearch->read('cityID'))) ;?>							
							<?php echo $this->Form->_select("extend",array(0=>'+0km',10=>'+10km',30=>'+30km', 50=>'+50km',100=>'+100km'),array("default"=>$this->cookieEventSearch->read('extend'),"placeholder"=>"etendre à",'class'=>"tooltipbottom","javascript"=>'title="Etendre la zone de recherche"')) ;?>															
							
							<button class="citySubmit tooltipbottom" title="Lancer la recherche"><span class="ws-icon-loupe"></span></button>
						</form>
						<?php else: ?>	
							
								<a class="search-bar-icon search-bar-icon-back" href="/"><span class="ws-icon ws-icon-arrow-left"></span> Retour au calendrier</a>								
							

						<?php endif; ?>
					</li>
					<li id="menu-createevent">
						<a class="mainbutton" href="<?php echo Router::url('events/create');?>">Poster un sport</a>
					</li>
					<li class="menu-full">
						<a href="<?php echo Router::url('blog');?>">Blog</a>
					</li>
					<?php if($this->session->user()->isAdmin()):?>
						<li>
							<a href="<?php echo Router::url('admin/');?>"><span class="ws-icon ws-icon-quill "></span></a>
						</li>
					<?php endif;?>
				</ul>

				<ul class="menu-full nav pull-right" id="registerMenu">
					<li class="social-btn"><a target="_blank" rel="nofollow" title="Nous suivre sur Twitter" href="https://twitter.com/weSportfr"  ><img src="<?php echo Router::webroot('img/social_flatls_twitter.png');?>" alt="Twitter"/></a></li>
					<li class="social-btn"><a target="_blank" rel="nofollow" title="Notre page facebook" href="https://www.facebook.com/pages/We-sport/642996032385245" ><img src="<?php echo Router::webroot('img/social_flatls_facebook.png');?>" alt="Facebook"/></a></li>	
					<?php if ($this->session->user()->isLog()): ?>
						<li><a href="<?php echo Router::url('users/account');?>">
								<img class="nav-avatar" src="<?php echo $this->session->user()->getAvatar(); ?>" />	
								<span class="nav-login"><?php echo $this->session->user()->getLogin(); ?></span>
						</a></li>
						<li class="dropdown">				
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<b class="caret"></b>
							</a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo Router::url('users/account'); ?>">Mon Compte</a></li>						
								<li class="divider"></li>
								<li><a href="<?php echo Router::url('users/logout'); ?>">Déconnexion</a></li>
							</ul>
						</li>
					<?php else: ?>
						<form class="loginForm" action="<?php echo Router::url('users/login'); ?>" method='post'>
							<input type="login" name="login" required="required" placeholder="Login or email" autofocus="autofocus" value="admin"/>
							<input type="password" name="password" required="required" placeholder="Password" value="fatboy" />
							<input type="hidden" name="token" value="<?php echo $this->session->token();?>" />
							<input type="submit" value="OK" />
						</form>
						<li><a href="<?php echo Router::url('users/login');?>">Connexion</a></li>	
						<li><a class="secondbutton" href="<?php echo Router::url('users/register');?>" >Inscription</a></li>
					<?php endif ?>
				</ul>	

				<ul class="menu-small nav pull-right">
					<li class="dropdown">							
						<?php if($this->session->user()->isLog()): ?>
							<a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo Router::url('users/account');?>">
								<img class="nav-avatar" src="<?php echo $this->session->user()->getAvatar(); ?>" />	
								<span class="nav-login"><?php echo $this->session->user()->getLogin(); ?></span>
								<b class="caret"></b>
							</a>
						<?php else: ?>
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">
								Menu <b class="caret"></b>
							</a>
						<?php endif;?>
						<ul class="dropdown-menu">
							<?php if($this->session->user()->isLog()): ?>
								<li><a href="<?php echo Router::url('users/account'); ?>">Mon Compte</a></li>						
								<li class="divider"></li>
								<li><a href="<?php echo Router::url('users/logout'); ?>">Déconnexion</a></li>
							<?php else: ?>
								<li><a href="<?php echo Router::url('users/login');?>">Connexion</a></li>	
								<li><a href="<?php echo Router::url('users/register');?>" >Inscription</a></li>
							<?php endif;?>
								<li><a href="<?php echo Router::url('blog');?>">Blog</a></li>
						</ul>
					</li>
				</ul>
			</div>

			<div id="userCounter">
				<span><b class="circle circle-grey"></b><?php echo $this->request('users','getCountTotalUsers',array());?> inscrits</span>
				<span><b class="circle circle-green"></b><?php echo $this->session->getOnlineUsers();?></span>
				<?php 
				if($id = $this->cookieEventSearch->read('cityID')):?>
					<br><span><b class="circle circle-blue"></b></span>
					<?php echo $this->request('users','getCountTotalUsersByCity',array($id)); ?>
					à
					<?php echo $this->cookieEventSearch->read('cityName'); ?>
				<?php endif; ?>													
			</div>				
		</div>


		<div style="display:none" class="navbar navbar-fixed-top">


				

				<div class="desktopMenus">

					<ul class="nav">
						<li>
							<a class="weSport" href="<?php echo Router::url('');?>" title="Du Sport ! Vite !">
				      			<img src="<?php echo Router::webroot('img/LOGO.gif');?>" alt="">
					      	  	<i>we</i><strong>Sport</strong>
							</a>
						</li>
						<?php 
							reset($menu);
							foreach ($menu as $page):
						 ?>
						 <li>
						 	<a href="<?php echo Router::url($page->slug);?>" class="<?php echo ($page->isCurrentPage($this->request))? 'currentPage':'';?>"><?php echo $page->title;?></a>
						 </li>
						<?php endforeach;

						//Admin section button
						if($this->session->user()->getRole()=='admin'):?>
						<li>
							<a href="<?php echo Router::url('admin/pages/home');?>"><span class="ws-icon-pacman"></span></a>
						</li>
						<?php endif;
						
						?>			
					</ul>

					<ul class="nav pull-right" id="registerMenu">
						<?php if ($this->session->user()->isLog()): ?>
							<li><a href="<?php echo Router::url('users/account');?>">
									<img class="nav-avatar" src="<?php echo $this->session->user()->getAvatar(); ?>" />	
									<span class="nav-login"><?php echo $this->session->user()->getLogin(); ?></span>
							</a></li>
							<li class="dropdown">				
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">
									<b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li><a href="<?php echo Router::url('users/account'); ?>">Mon Compte</a></li>						
									<li class="divider"></li>
									<li><a href="<?php echo Router::url('users/logout'); ?>">Déconnexion</a></li>
								</ul>
							</li>
						<?php else: ?>
							<form class="loginForm" action="<?php echo Router::url('users/login'); ?>" method='post'>
								<input type="login" name="login" required="required" placeholder="Login or email" autofocus="autofocus" value="admin"/>
								<input type="password" name="password" required="required" placeholder="Password" value="fatboy" />
								<input type="hidden" name="token" value="<?php echo $this->session->token();?>" />
								<input type="submit" value="OK" />
							</form>
							<li><a href="<?php echo Router::url('users/login');?>">Connexion</a></li>	
							<li><a href="<?php echo Router::url('users/register');?>" >Inscription</a></li>
						<?php endif ?>
					</ul>					
				</div>

				
		</div>

		<div class="container-fluid mainContainer">	
			<?php echo $content_for_layout;?>
		</div>


		<div class="footer container-fluid">
			<div class="container">
				<ul class="footer-menu">
					<?php 					
					reset($bottommenu);
					foreach ($bottommenu as $page):
					?>
						<li><a href="<?php echo Router::url($page->slug);?>" id="<?php echo $page->slug;?>"><?php echo $page->title;?></a></li>
					<?php endforeach;?>
					 			
				</ul>

				<ul class='footer-cities'>
					<?php
					foreach (Conf::$villes as $sub => $ville): 
					?>	
						<li><a href="http://<?php echo $sub;?>.we-sport.fr"><?php echo $ville['name'];?></a></li>
					<?php
					endforeach;
					?>
				</ul>
				<div class="copyright">2013 © weSport</div>
			</div>
		</div>

		<div class="modal fade" id="myModal"></div>
		
		<!-- facebook code for like buttons -->
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1&appId=153720748148187";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>

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

 	
 	/*===========================================================
 		GOOGLE ANALYTICS
 	============================================================*/
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-42468932-1', 'we-sport.fr');
	  ga('send', 'pageview');

	  //prevent GA bounce event after 10s
	  setTimeout(function(){ ga('send','event','PasDeRebond','Timer');},10000);

</script>




</script>





</html>