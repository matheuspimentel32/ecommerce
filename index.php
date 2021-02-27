<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Products;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;

$app = new \Slim\Slim();

$app->config('debug', true);

require_once("functions.php");

//Para carregar o site
$app->get('/', function() {
	
	$products = Products::listAll();
	
	$page = new Page();

	$page->setTpl("index", [
		'products'=>Products::checkList($products)
	]);

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

//Para Esqueci minha senha do admin
$app->get('/admin/forgot', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

//Post do Esqueci minha senha do admin
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

//Para listar produtos por categorias
$app->get("/admin/categories/:idcategory/products", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page =  new PageAdmin();

	$page->setTpl("categories-products", [
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts($idcategory, true),
		"productsNotRelated"=>$category->getProducts($idcategory, false)
	]);

});


//Para carregar os produtos das categorias
$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) {
		
		array_push($pages, [
			'link'=>'/categories/' . $category->getidcategory() . '?page=' . $i,
			'page'=>$i
		]);

	}

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);

});


$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Products();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/" . $idcategory . "/products");

	exit;

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Products();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/" . $idcategory . "/products");

	exit;

});

//Acessar produtos
$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Products::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
	]);

});


//Criar produtos
$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");

});

//Post do criar produtos
$app->post("/admin/products/create", function(){

	User::verifyLogin();

	$product = new Products();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");
	
	exit;

});

//Editar produtos
$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Products();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", [
		"product"=>$product->getValues()
	]);
});

//Post do editar produtos
$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Products();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->update($idproduct);

	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	
	exit;

});


//Para descrição dos produtos
$app->get("/products/:desurl", function($desurl){

	$product = new Products();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories()
	]);

});


//Para o carrinho de compras
$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();
	
	$page->setTpl("cart",[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);

});

//Para adicionar produtos no carrinho de compras
$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Products();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i = 0; $i < $qtd; $i++){

		$cart->addProduct($product);

	}

	header("Location: /cart#woocommerce");

	exit;

});

//Para remover 1 unidade do produto no carrinho de compras
$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Products();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart#woocommerce");

	exit;
	
});


//Para remover todo o produto do produto no carrinho de compras
$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Products();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart#woocommerce");

	exit;
	
});


//Para calcular o frete
$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart#calcFreight");

	exit;
	
});

//Para o usuário fazer login
$app->get("/checkout", function(){

	User::verifyLogin(false);
	
	$address = new Address();
	$cart = Cart::getFromSession();

	if(isset($_GET['zipcode'])) {
		
		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();

	}

	if (!$address->getdesaddress()) $address->setdesaddres('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'error'=>Address::getMsgError(),
		'products'=>$cart->getProducts()
	]);

});


//Post do checkout
$app->post("/checkout", function(){

	User::verifyLogin(false);

	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		User::setMsgError("Informe o CEP.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		User::setMsgError("Informe o endereço.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		User::setMsgError("Informe o bairro.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		User::setMsgError("Informe a cidade.");
		header("Location: /checkout");
		exit;
	}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		User::setMsgError("Informe o estado.");
		header("Location: /checkout");
		exit;
	}


	$user = User::getFromSession();
	//$user = User::getDataUser();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	header("Location: /order/".$order->getidorder);
	exit;

});


//Para o usuário fazer login
$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);

});


$app->post("/login", function(){

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch (Exception $e) {
		
		User::setError($e->getMessage());
		
	}

	header("Location: /");

	exit;

});

//Para fazer logout
$app->get("/logout", function(){

	User::logout();

	header("Location: /login");

	exit;

});

//Post do registrar
$app->post("/register", function(){

	$_SESSION['registerValues'] = $_POST;

	if (!isset($_POST['name']) || $_POST['name'] == '') {
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;

	}

	if (!isset($_POST['email']) || $_POST['email'] == '') {

		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;

	}

	if (!isset($_POST['password']) || $_POST['password'] == '') {

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;

	}

	if (User::checkLoginExist($_POST['email']) === true) {

		User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;

});

$app->get("/forgot", function() {

	$page = new Page();

	$page->setTpl("forgot");	

});

$app->post("/forgot", function(){

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

$app->get("/forgot/sent", function(){

	$page = new Page();

	$page->setTpl("forgot-sent");	

});

$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

//Perfil
$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getDataUser();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);

});

//Perfil
$app->post("/profile", function(){

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Preencha o seu nome.");
		header('Location: /profile');
		exit;
	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Preencha o seu e-mail.");
		header('Location: /profile');
		exit;
	}

	if (!isset($_POST['nrphone']) || $_POST['nrphone'] === '') {
		User::setError("Preencha o seu telefone.");
		header('Location: /profile');
		exit;
	}

	$user = User::getDataUser();	

	$dataUser = $user->getValues();

	if ($_POST['desemail'] !== $dataUser[0]['desemail']) {

		if (User::checkLoginExist($_POST['desemail']) === true) {

			User::setError("Este endereço de e-mail já está cadastrado.");
			header('Location: /profile');
			exit;

		}

	}

	$_POST['inadmin'] = $dataUser[0]['inadmin'];
	$_POST['despassword'] = $dataUser[0]['despassword'];
	$dataUser[0]['desperson'] = $_POST['desperson'];
	$dataUser[0]['desemail'] = $_POST['desemail'];
	$dataUser[0]['deslogin'] = $_POST['desemail'];
	$dataUser[0]['nrphone'] = $_POST['nrphone'];

	$user->updateUser($dataUser);

	User::setSuccess("Dados alterados com sucesso!");

	header('Location: /profile');
	exit;

});


//Para gerar o boleto
$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(".", "",$valor_cobrado);
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder;  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $$order->getdesstate() . " " . $order->getdescountry() . " - CEP: " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157
 
	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	// NÃO ALTERAR!
	require_once($path . "funcoes_itau.php"); 
	require_once($path . "layout_itau.php");

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder());

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);

});

//Para ver o que ele já comprou
$app->get("/profile/orders", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()
	]);

});


//Para ver o que ele já comprou
$app->get("/profile/orders/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl("profile-orders-details", [
		'orders'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$products->getProducts()
	]);

});


//Para ver o que ele já comprou
$app->get("/profile/change-password", function(){

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
	]);

});

$app->post("/profile/change-password", function(){

	User::verifyLogin(false);

	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {

		User::setError("Digite a senha atual.");
		header("Location /profile/change-password");
		exit;

	}

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {

		User::setError("Digite a nova senha.");
		header("Location /profile/change-password");
		exit;

	}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {

		User::setError("Confirme a nova senha.");
		header("Location /profile/change-password");
		exit;

	}

	if ($_POST['current_pass'] === $_POST['new_pass']){

		User::setError("Sua nova senha deve ser diferente da atual.");
		header("Location /profile/change-password");
		exit;

	}

	if ($_POST['new_pass'] !== $_POST['new_pass_confirm']) {

		User::setError("A senhas digitadas não são iguais.");
		header("Location /profile/change-password");
		exit;

	}

	//$user = User::getDataUser();
	$user = User::getFromSession();

	if (!password_verify($_POST['current_pass'], $user->getdespassword())) {

		User::setError("Senha inválida.");
		header("Location /profile/change-password");
		exit;

	}

	$user->updatePass($_POST['new_pass']);

	User::setSuccess("Senha alterada com sucesso.");
	header("Location: /profile/change-password");
	exit;

});

$app->run();

 ?>