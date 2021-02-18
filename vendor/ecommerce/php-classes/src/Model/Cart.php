<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model{

    const SESSION = "Cart";

    public static function getFromSession()
    {

        $cart = new Cart();

        // Verifica se existe uma seção (Usuário conectado)
        //If não tenha seção logada // Else tenha seção logada
        if (isset($_SESSION[Cart::SESSION]['idcart']) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        } else {

            $cart->getFromSessionID();

            if (!(int)$cart->getidcart() > 0) {

                $data = [
                    'dessessionid'=>session_id()
                ];

                //Se estiver logado 
                if (User::checkLogin(false)) {
                   
                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();
                
                }

                $cart->setData($data);

                $cart->save();

                $cart->setToSession();

            }

        }

        return $cart;

    }


    public function setToSession()
    {

        $_SESSION[Cart::SESSION] = $this->getValues();

    }


    public function getFromSessionID()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ':dessessionid'=>session_id()
        ]);

        if (count($results) > 0) {
            
            $this->setData($results[0]);

        }

    }


    public function get(int $idcart)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ':idcart'=>$idcart
        ]);

        if (count($results) > 0) {
            
            $this->setData($results[0]);

        }

    }


    public function save()
    {

        $sql = new Sql();

        $dessessionid = $this->getdessessionid();
        $iduser = $this->getiduser();
        $deszipcode = $this->deszipcode();
        $vlfreight = $this->getvlfreight();
        $nrdays = $this->getnrdays();

        $results = $sql->select("INSERT INTO tb_carts (dessessionid, iduser, deszipcode, vlfreight, nrdays)
                    VALUES(:dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
                        ':dessessionid'=>$this->getdessessionid(),
                        ':iduser'=>$this->getiduser(),
                        ':deszipcode'=>$this->getdeszipcode(),
                        ':vlfreight'=>$this->getvlfreight(),
                        ':nrdays'=>$this->getnrdays()
                    ]);

        $this->setData($results);


    }

}

?>