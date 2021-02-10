<?php

//Acessar categorias
$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page =  new PageAdmin();

	$page->setTpl("categories", array(
		"categories"=>$categories
	));

});


//Criar categorias
$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page =  new PageAdmin();

	$page->setTpl("categories-create");

});

//Post do criar categorias
$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");

	exit;

});

//Deletar uma categoria
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();
	
	header("Location: /admin/categories");

	exit;

});

//Editar uma categoria
$app->get("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page =  new PageAdmin();

	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);

});

//Post do editar uma categoria
$app->post("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->update($_POST['descategory']);

	header("Location: /admin/categories");

	exit;

});

?>