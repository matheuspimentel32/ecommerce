<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

    const SESSION = "User";

    // Para autenticar usuário
    public static function login ($login, $password)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0) 
        {

            throw new \Exception("Usuário inexistente ou senha inválida.");

        }

        $data = $results[0];

        if (md5($password) === $data["despassword"])
        {

            $user = new User();

            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        } else {

            throw new \Exception("Usuário inexistente ou senha inválida.");

        }

    }

    // Para verificar se está logado
    public static function verifyLogin()
    {

        if ((!isset($_SESSION[User::SESSION])) || (!$_SESSION[User::SESSION]) || (!(int)$_SESSION[User::SESSION]["iduser"] > 0))
        {

            header("Location: /admin/login");
            exit;

        }
        
    }

    // Para realizar logout
    public static function logout()
    {
    
        $_SESSION[User::SESSION] = NULL;
        
    }


    // Para listar usuários
    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) ORDER BY b.desperson");

    }

    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));

        var_dump($results);

        $this->setData($results[0]);

    }
    
}



?>