<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new \Slim\Slim();

$app->config('debug', true);

//Para carregar o site
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

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

//Para salvar o Criar Usuário
$app->post('/admin/users/create', function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST['inadmin']))?1:0;		//Se Acesso administrador foi definido, valor 1, senão, valor 0

	$user->setData($_POST);
	
	$user->save();

	header("Location: /admin/users");

	exit;

	//var_dump($user);

});

//Para Deletar Usuário
$app->get('/admin/users/:iduser/delete', function($iduser) {

	User::verifyLogin();

});

//Para acessar tela de Alterar Usuário
$app->get('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-update");

});

//Para salvar o Alterar Usuário
$app->post('/admin/users/:iduser', function($iduser) {

	User::verifyLogin();

});




$app->run();

 ?>