<?php

//Para carregar o admin
$app->get('/admin', function() {
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});

//Para carregar o login
$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false,	// Desabilitando o header
		"footer"=>false		// Desabilitando o footer
	]);

	$page->setTpl("login");

});

//Para receber o login
$app->post('/admin/login', function() {
    
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

//Para fazer logout
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;

});

//Para acessar tela de usuário
$app->get("/admin/users", function() {

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));

});

//Para acessar tela de Criar Usuário
$app->get('/admin/users/create', function() {

	User::verifyLogin();
	
	$page = new PageAdmin();

	$page->setTpl("users-create");

});

//Para acessar tela de Alterar Usuário
$app->get('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

//Para salvar o Criar Usuário
$app->post('/admin/users/create', function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");

	exit;

});

//Para Deletar Usuário
$app->get('/admin/users/:iduser/delete', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");

	exit;

});

//Para salvar o Alterar Usuário
$app->post('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

	exit;

});

//Para Esqueci minha senha
$app->get('/admin/forgot', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

//Post do Esqueci minha senha
$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST['email']);

	header("Location: /admin/forgot/sent");
	
	exit;

});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset");

});