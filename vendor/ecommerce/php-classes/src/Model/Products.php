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

        return $sql->select("SELECT * FROM tb_products ORDER BY idproduct");

    }

    public static function checkList($list)
    {

        foreach ($list as &$row)
        {

            $p = new Products();
            $p->setData($row);
            $row = $p->getValues();

        }

        return $list;

    }


    public function save()
    {

        $sql = new Sql();

        $desproduct = $this->getdesproduct();
        $vlprice = $this->getvlprice();
        $vlwidth = $this->getvlwidth();
        $vllength = $this->getvllength();
        $vlheight = $this->getvlheight();
        $vlweight = $this->getvlweight();
        $desurl = $this->getdesurl();

        $results = $sql->select("INSERT INTO tb_products (desproduct, vlprice, vlwidth, vlheight, vllength, vlweight, desurl) 
                            VALUES('$desproduct', $vlprice, $vlwidth, $vlheight, $vllength, $vlweight, '$desurl')");
        
        $this->setData($results);
    }

    
    public function get($idproduct)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = $idproduct");

        $this->setData($results[0]);

    }


    public function update($idproduct)
    {

        $sql = new Sql();

        $idproduct = $this->getidproduct();
        $desproduct = $this->getdesproduct();
        $vlprice = $this->getvlprice();
        $vlwidth = $this->getvlwidth();
        $vllength = $this->getvllength();
        $vlheight = $this->getvlheight();
        $vlweight = $this->getvlweight();
        $desurl = $this->getdesurl();

        $results = $sql->select("UPDATE tb_products SET desproduct = '$desproduct', 
                                                        vlprice = $vlprice,
                                                        vlwidth = $vlwidth,
                                                        vllength = $vllength,
                                                        vlheight = $vlheight,
                                                        vlweight = $vlweight,
                                                        desurl = $desurl
                                WHERE idproduct = '$idproduct'");
    }

    public function delete()
    {

        $sql = new Sql();

        $idproduct = $this->getidproduct();

        $sql->query("DELETE FROM tb_products WHERE idproduct = $idproduct");

    }

    public function checkPhoto()
    {
        
        $idproduct = $this->getidproduct();
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
            "res" . DIRECTORY_SEPARATOR . 
            "site" . DIRECTORY_SEPARATOR . 
            "img" . DIRECTORY_SEPARATOR . 
            "products" . DIRECTORY_SEPARATOR .
            $idproduct . ".jpg"
        )){

            $url = "/res/site/img/products/" . $idproduct . ".jpg";

        } else {

            $url = "/res/site/img/products/product.jpg";

        }

        return $this->setdesphoto($url);

    }


    //Para pegar os valores
    public function getValues()
    {

        $this->checkPhoto();

        $values = parent::getValues();

        return $values;

    }

    //Para inserir foto
    public function setPhoto($files)
    {

        if($files["tmp_name"] !== ""){
        
            // Para pegar a extensão do arquivo
            $extension = explode('.', $files['name']);
            $extension = end($extension);

            switch ($extension) {

                case "jpg":
                    $image = imagecreatefromjpeg($files["tmp_name"]);
                break;

                case "jpeg":
                    $image = imagecreatefromjpeg($files["tmp_name"]);
                break;

                case "gif":
                    $image = imagecreatefromgif($files["tmp_name"]);
                break;

                case "png":
                    $image = imagecreatefrompng($files["tmp_name"]);
                break;

            }

            $dir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
            "res" . DIRECTORY_SEPARATOR . 
            "site" . DIRECTORY_SEPARATOR . 
            "img" . DIRECTORY_SEPARATOR . 
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg";

            imagejpeg($image, $dir);

            imagedestroy($image);

            $this->checkPhoto();

        }

    }


    public function getFromURL($desurl)
    {

        $sql = new Sql();

        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
            ':desurl'=>$desurl
        ]);

        $this->setData($rows[0]);

    }

    //Para buscar as categorias quando estiver vendo a descrição do produto
    public function getCategories()
    {

        $sql = new Sql();

        return $sql->select("SELECT *
                    FROM tb_categories a 
                    INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory 
                    WHERE b.idproduct = :idproduct",[
                        ':idproduct'=>$this->getidproduct()
                    ]);                    

    }

}

?>