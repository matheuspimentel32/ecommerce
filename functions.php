<?php

use \Hcode\Model\User;

function formatPrice($vlprice)
{

    return number_format($vlprice, 2, ",", ".");

}

function checkLogin($inadmin = true)
{

    return User::checkLogin($inadmin);

}


function getUserName()
{

    $user = User::listAll();

    $user = $user[0]['desperson'];

    return $user;

}


?>