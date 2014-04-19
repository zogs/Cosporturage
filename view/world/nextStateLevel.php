<?php if($state != false) {


	$style = (!empty($style))? $style : '';
	$class = (!empty($class))? $class : '';
	$javascript = (!empty($javascript))? $javascript : '';

	if(empty($javascript)){
		if($state['lvl']!='city') $javascript = 'onchange="showRegion(this.value,\''.$state['lvl'].'\')"';
		
	}

	$array = array(
		'SelectID'=>$state['lvl'],
		'SelectELEMENT'=> $this->Form->_select(
												$state['lvl'],
												$state['list'],
												array("class"=>$class,
														"placeholder"=>$state['title'],
														"style"=>$style,
														"javascript"=>(isset($javascript)? $javascript:""),
														)
												)
				);

	echo json_encode($array);
	//echo $this->Form->Select($ADM,'btn geo-select',$states,'code','name','','onchange="showRegion(this.value,\''.$ADM.'\')"');
}else {
	echo 'empty';
}?>