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
        
        echo $idcategory;

    }

    public function delete()
    {

        $sql = new Sql();

        $idcategory = $this->getidcategory();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = $idcategory");

    }


}