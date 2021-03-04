<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{




    // Para listar usuários
    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

    }

    //Para salvar
    public function save()
    {

        $sql = new Sql();

        $descategory = $this->getdescategory();

        $results = $sql->select("INSERT INTO tb_categories (descategory) VALUES ('$descategory')");

        Category::updateFile();
    
    }

    //Para pegar os dados pelo id passado
    public function get($idcategory)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = $idcategory");

        $this->setData($results[0]);

    }

    //Para fazer update da categoria
    public function update($descategory)
    {

        $sql = new Sql();

        $idcategory = $this->getidcategory();

        $results = $sql->select("UPDATE tb_categories SET descategory = '$descategory' WHERE idcategory = '$idcategory'");
        
        Category::updateFile();

    }

    //Para deletar
    public function delete()
    {

        $sql = new Sql();

        $idcategory = $this->getidcategory();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = $idcategory");

        Category::updateFile();

    }

    //Para enviar foto
    public static function updateFile()
    {

        $categories = Category::listAll();

        $html = [];

        foreach ($categories as $row)
        {

            array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');

        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));


    }

    // Para listar os produtos daquela categoria
    public function getProducts($idcategory, $related = true)
    {

        $sql = new Sql();

        if ($related === true) {

            return $sql->select("SELECT * FROM tb_products WHERE idproduct IN (
                            SELECT a.idproduct 
                            FROM tb_products a 
                            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
                            WHERE b.idcategory = $idcategory
                            );
                        ");

        } else {

            return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN (
                            SELECT a.idproduct 
                            FROM tb_products a 
                            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
                            WHERE b.idcategory = $idcategory
                            )
                        ");
        }

    }

    public static function getPage($page = 1, $itemsPerPage = 3)
	{

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>Products::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage) //Ceil arredonda para o próximo número inteiro
		];

    }
    

    public static function getPageSearch($search, $page = 1, $itemsPerPage = 3)
	{

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories
            WHERE descategory LIKE :search
            ORDER BY descategory DESC
			LIMIT $start, $itemsPerPage;
		", [
            ':search'=>'%' . $search . '%'
        ]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage) //Ceil arredonda para o próximo número inteiro
        ];

	}


    //Para adicionar produto naquela categoria
    public function addProduct(Products $product)
    {

        $sql = new Sql();

        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);

    }

     //Para retirar produto naquela categoria
     public function removeProduct(Products $product)
     {
 
         $sql = new Sql();
 
         $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory and idproduct = :idproduct", [
             ':idcategory'=>$this->getidcategory(),
             ':idproduct'=>$product->getidproduct()
         ]);
 
     }


}

?>