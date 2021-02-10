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


    public function save()
    {

        $sql = new Sql();

        $descategory = $this->getdescategory();

        $results = $sql->select("INSERT INTO tb_categories (descategory) VALUES ('$descategory')");

        Category::updateFile();
    
    }

    public function get($idcategory)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = $idcategory");

        $this->setData($results[0]);

    }


    public function update($descategory)
    {

        $sql = new Sql();

        $idcategory = $this->getidcategory();

        $results = $sql->select("UPDATE tb_categories SET descategory = '$descategory' WHERE idcategory = '$idcategory'");
        
        Category::updateFile();

    }

    public function delete()
    {

        $sql = new Sql();

        $idcategory = $this->getidcategory();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = $idcategory");

        Category::updateFile();

    }


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


}

?>