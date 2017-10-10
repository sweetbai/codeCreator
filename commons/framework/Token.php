<?php
namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/26
 * Time: 16:31
 */
class Token
{
    const SESSION_KEY = KEY_SESSION;
    public static function GetToken($formName)
    {
        $key = self::GrantedKey();
        $_SESSION[self::SESSION_KEY . $formName] = $key;
        $token = md5($key . $formName . $_SERVER["REMOTE_ADDR"]);
        return $token;
    }

    public static function IsToken($formName, $token)
    {
        $key = $_SESSION[self::SESSION_KEY . $formName];
        $old_token = md5($key . $formName . $_SERVER["REMOTE_ADDR"]);
        $f = $old_token == $token;
        return $old_token == $token;
    }
    public static function DropToken($formName)
    {
        unset($_SESSION[self::SESSION_KEY . $formName]);
    }
    public static function GrantedKey()
    {
        $encrypt_key = md5(((float) date("YmdHis") + rand(100, 999)) . rand(1000, 9999));
        return $encrypt_key;
    }
}