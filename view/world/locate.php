<input type="hidden" id="submit-state" data-url="<?php echo Router::url('world/nextStateLevel'); ?>">
<?php 
		
	foreach($states as $ADMlvl=>$state){
			
		//init javascript 
		if(!isset($javascript)){

			$javascript = '';
			if($ADMlvl!='city') $javascript = 'onchange="showRegion(this.value,\''.$ADMlvl.'\')"';						
		}
		//init class
		if(!isset($class)) $class = 'geo-select';
		//init style
		if(!isset($style)) $style = 'width:180px';
		
		//display select box			
		echo $this->Form->_select(
									$ADMlvl,
									$state['list'],
									array('placeholder'=>$state['title'],
											"class"=>$class,
											"default"=>$obj->$ADMlvl,
											"style"=>$style,
											"javascript"=>$javascript));


		//reset javascript
		unset($javascript);

	}
 ?>		