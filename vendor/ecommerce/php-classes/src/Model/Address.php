<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;

    const SESSION_ERROR = "AddressError";

class Address extends Model{

    public static function getCEP($nrcep)
    {

        $nrcep = str_replace("-", "", $nrcep);

        $ch = curl_init();  //Para iniciar um curl

        curl_setopt($ch, CURLOPT_URL, "http://viace.com.br/ws/$nrcep/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    //Se irá retornar algum dado
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //Se irá exibir autenticação
        
        
        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $data;
    
    }

    //Carregar por CEP
    public function loadFromCEP($nrcep)
    {

        $data = Address::getCEP($nrcep);

        if (isset($data['logradouro']) && $data['logradouro']) {

            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescoutry("Brasil");
            $this->setdeszipcode($nrcep);

        }

    }

    //Para salvar os address
    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("UPDATE tb_addresses 
                                SET idperson = :idperson, 
                                desaddress = :desaddress, 
                                descomplement = :descomplement, 
                                descity = :descity, 
                                desstate = :desstate,
                                descountry = :descountry,
                                deszipcode = :deszipcode,
                                desdistrict = :desdistrict
                                WHERE idaddress = :idaddress;", [
                                    ':idaddress'=>$this->getidaddress(),
                                    ':idperson'=>$this->getidperson(),
                                    ':desaddress'=>utf8_decode($this->getdesaddress()),
                                    ':descomplement'=>utf8_decode($this->getdescomplement()),
                                    ':descity'=>utf8_decode($this->getdescity()),
                                    ':desstate'=>utf8_decode($this->getdesstate()),
                                    ':descountry'=>utf8_decode($this->getdescountry()),
                                    ':deszipcode'=>$this->getdeszipcode(),
                                    ':desdistrict'=>$this->getdesdistrict()
                                ]);
        if (count($results) > 0)  {
            $this->setData($results[0]);
        }
    
    }


    public static function setMsgError($msg)
    {

        $_SESSION[Address::SESSION_ERROR] = $msg;

    }

    public static function getMsgError()
    {

        $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

        Address::clearMsgError();

        return $msg;

    }

    public static function clearMsgError()
    {

        $_SESSION[Address::SESSION_ERROR] = NULL;

    }

}

?>