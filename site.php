<?php

//Para carregar o site
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

?>