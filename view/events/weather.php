<?php 
	if(isset($error)){
		echo 'Les données météo sont indisponibles pour l\'instant ('.$error.')';
		return;
	}
	else{
 ?>

<img src="<?php echo $weather->weatherIconUrl[0]->value;?>" alt="">
<span><strong><?php echo $weather->weatherDesc[0]->value;?></strong></span><br>
<span>Température max:<?php echo $weather->tempMaxC;?>°C, min:<?php echo $weather->tempMinC;?>°C</span><br>
<span>Vent : <?php echo $weather->windspeedKmph;?> km/h, direction: <?php echo $weather->winddirection;?></span>
<?php
	}
?>