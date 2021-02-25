<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model{

    public function save()
    {

        $sql = new Sql();

        $idcart = $this->getidcart();
        $iduser = $this->getiduser();
        $idstatus = $this->getidstatus();
        $idaddress = $this->getidaddress();
        $vltotal = $this->getvltotal();
        $idorder = $this->getidorder();

        $results = $sql->select("UPDATE tb_orders 
                    SET idcart = '$idcart', iduser = '$iduser', idstatus = '$idstatus', idaddress = '$idaddress', vltotal = '$vltotal' 
                    WHERE idorder = '$idorder';");

        if (count($results) > 0) {
            $this->setData($results[0]);
        }



    }


    public function get($idorder)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_orders a 
                            INNER JOIN tb_ordersstatus b USING(idstatus) 
                            INNER JOIN tb_carts c USING(idcart)
                            INNER JOIN tb_users d ON d.iduser = a.iduser
                            INNER JOIN tb_addresses e USING(idaddress)
                            INNER JOIN tb_persons f ON f.idperson = d.idperson
                            WHERE a.idorder = :idorder
                            ", [
                                ':idorder'=>$idorder
                            ]);

        if (count($results) > 0) {

            $this->setData($results[0]);

        }

    }

}

?>