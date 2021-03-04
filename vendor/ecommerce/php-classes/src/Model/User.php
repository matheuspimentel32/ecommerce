<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSucesss";

    //Para pegar o id de seção do usuário
    public static function getFromSession()
    {

        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

            $user->setData($_SESSION[User::SESSION]);

            return $user;
            
        }

    }

    public static function getDataUser()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$_SESSION[User::SESSION]['iduser']
        ));

        $user = new User();

        $user->setData($results);

        return $user;

    }


    //Para checar login
    public static function checkLogin($inadmin = true)
    {
        //Usuário não está logado
        if ((!isset($_SESSION[User::SESSION])) 
            || 
            (!$_SESSION[User::SESSION]) 
            || 
            (!(int)$_SESSION[User::SESSION]["iduser"] > 0))
        {
            //Não está logado
            return false;

        } else {

            //Usuário está logado e é admin
            if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
                
                return true;
            
            // Usuário está logado mas não é admin
            } else if ($inadmin === false) {

                return true;

            // Usuário não está logado
            } else {

                return false;

            }

        }

    }


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

        if (password_verify($password, $data["despassword"]) === true)
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
    public static function verifyLogin($inadmin = true)
    {

        if (!User::checkLogin($inadmin)) {

			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
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
       
        $desperson = $this->getdesperson();
        $desemail = $this->getdesemail();
        $nrphone = $this->getnrphone();
        $deslogin = $this->getdeslogin();
        $despassword = User::getPasswordHash($this->getdespassword());
        $inadmin = $this->getinadmin();

        $results = $sql->select("INSERT INTO tb_persons (desperson, desemail, nrphone) VALUES ('$desperson', '$desemail', '$nrphone');
        SET @last_insert_id = LAST_INSERT_ID(); 
        SELECT @last_insert_id; 
        INSERT INTO tb_users (idperson, deslogin, despassword, inadmin) VALUES (@last_insert_id, '$deslogin', '$despassword', '$inadmin');");

    }


    public function get($iduser)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));

        $this->setData($results[0]);

    }


    public function update()
    {

        $sql = new Sql();
       
        $iduser = $this->getiduser();
        $desperson = $this->getdesperson();
        $desemail = $this->getdesemail();
        $nrphone = $this->getnrphone();
        $idperson = $this->getidperson();
        $deslogin = $this->getdeslogin();
        $inadmin = $this->getinadmin();

        $results = $sql->select("UPDATE tb_persons SET desperson = '$desperson', desemail = '$desemail', nrphone = '$nrphone' WHERE idperson = '$idperson';
                                UPDATE tb_users SET deslogin = '$deslogin', inadmin = '$inadmin' WHERE iduser = '$iduser';
                                ");
   
    }
    
    
    public function updatePass($password)
    {

        $sql = new Sql();

        $despassword = User::getPasswordHash($password);
        $user = User::getFromSession();
        $iduser = $user->getiduser();

        $results = $sql->select("UPDATE tb_users SET despassword = '$despassword' WHERE iduser = $iduser");

    }

    public function updateUser($user = array())
    {

        $sql = new Sql();
       
        $iduser = $user[0]['iduser'];
        $desperson = $user[0]['desperson'];
        $desemail = $user[0]['desemail'];
        $nrphone = $user[0]['nrphone'];
        $idperson = $user[0]['idperson'];
        $deslogin = $user[0]['deslogin'];
        $inadmin = $user[0]['inadmin'];

        $results = $sql->select("UPDATE tb_persons SET desperson = '$desperson', desemail = '$desemail', nrphone = '$nrphone' 
                                    WHERE idperson = '$idperson';
                                UPDATE tb_users SET deslogin = '$deslogin', inadmin = '$inadmin' 
                                    WHERE iduser = '$iduser';
                                ");
   
    }

    public function delete()
    {

        $sql = new Sql();

        $iduser = $this->getiduser();
        $idperson = $this->getidperson();

        $results = $sql->select("DELETE FROM tb_users WHERE iduser = '$iduser';
                                DELETE FROM tb_persons WHERE idperson = '$idperson';
                                ");

    }


    public static function getForgot($email, $inadmin = true)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = '$email'");

        if (count($results) === 0)
        {

            throw new \Exception ("Não foi possível recuperar a senha!");

        }
        else 
        {

            $data = $results[0];

			$results2 = $sql->select("INSERT INTO tb_userspasswordsrecoveries (iduser, desip) VALUES(':iduser', ':desip')", array(
				":iduser"=>$data['iduser'],
				":desip"=>$_SERVER['REMOTE_ADDR']
            ));

            if ($results2 === false)
            {

                throw new \Exception ("Não foi possível recuperar a senha!");

            }
            else
            {

                $dataRecovery = $data['iduser'];

                $code = openssl_encrypt($dataRecovery, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

                $code = base64_encode($code);
                
                if ($inadmin === true) {

					$link = "localhost/admin/forgot/reset?code=$code";

				} else {

					$link = "localhost/forgot/reset?code=$code";
					
				}				

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				));				

				$mailer->send();

				return $link; 

            }
        }

    }

    public static function getPage($page = 1, $itemsPerPage = 10)
	{

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_users a
            INNER JOIN tb_persons b USING(idperson)
            ORDER BY a.iduser ASC
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage) //Ceil arredonda para o próximo número inteiro
        ];

    }
    

    public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_users a
            INNER JOIN tb_persons b USING(idperson)
            WHERE b.desperson LIKE :search OR b.desemail = :searchEmail OR a.deslogin LIKE :search
            ORDER BY a.iduser ASC
			LIMIT $start, $itemsPerPage;
		", [
            ':search'=>'%' . $search . '%',
            ':searchEmail'=> $search
        ]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage) //Ceil arredonda para o próximo número inteiro
        ];

	}




    //Erros do cadastro
    public static function setError($msg)
    {

        $_SESSION[User::ERROR] = $msg;

    }

    public static function getError()
    {

        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";

        User::clearError();

        return $msg;

    }

    public static function clearError()
    {

        $_SESSION[User::ERROR] = NULL;

    }

    //Mensagens de sucesso
    public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;

	}

	public static function getSuccess()
	{

		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;

	}

    //Erros do Registro
    public static function setErrorRegister($msg)
    {
        
        $_SESSION[User::ERROR_REGISTER] = $msg;
      
    }

    public static function getErrorRegister()
    {

        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";

        //User::clearErrorRegister();

        return $msg;

    }

    public static function clearErrorRegister()
    {

        $_SESSION[User::ERROR_REGISTER] = NULL;

    }

    public static function checkLoginExist($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		return (count($results) > 0);

    }
    
    public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
        ]);

	}
    
    public function getOrders()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_orders a 
                            INNER JOIN tb_ordersstatus b USING(idstatus) 
                            INNER JOIN tb_carts c USING(idcart)
                            INNER JOIN tb_users d ON d.iduser = a.iduser
                            INNER JOIN tb_addresses e USING(idaddress)
                            INNER JOIN tb_persons f ON f.idperson = d.idperson
                            WHERE a.iduser = :iduser
                            ", [
                                ':iduser'=>$this->getiduser()
                            ]);

       return $results;

    }

}