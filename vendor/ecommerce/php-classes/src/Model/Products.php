<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Products extends Model{




    // Para listar usuários
    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

    }


    public function save()
    {

        $sql = new Sql();

        $desproduct = $this->getdesproduct();
        $vlprice = $this->getvlprice();
        $vlwidth = $this->getvlwidth();
        $vllenght = $this->getvllenght();
        $vlweight = $this->getvlweight();
        $desurl = $this->getdesurl();

        $results = $sql->select("INSERT INTO tb_products (desproduct, vlprice, vlwidth, vlheight, vllength, vlweight, desurl) 
                            VALUES('$desproduct', '$vlprice', '$vlwidth', '$vlheight', '$vllength', '$vlweight', '$desurl')");

       $this->setData($results[0]);
    
    }

    
    public function get($idproduct)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = $idproduct");

        $this->setData($results[0]);

    }


   /* public function update($desproduct)
    {

        $sql = new Sql();

        $idproduct = $this->getidproduct();

        $results = $sql->select("UPDATE tb_products SET desproduct = '$desproduct' WHERE idproduct = '$idproduct'");
        
        

    }*/

    public function delete()
    {

        $sql = new Sql();

        $idproduct = $this->getidproduct();

        $sql->query("DELETE FROM tb_products WHERE idproduct = $idproduct");

    }


}

?>