<?php
namespace commons\framework;

class Verify
{
    /**
     * 判断为false 时抛出异常
     * @param $var
     * @param null $msg
     * @param int $code
     *
     * @return mixed
     * @throws \Exception
     */
    public static function isFalseWillException($var, $msg = null, $code = HTTP_SERVER_ERROR)
    {
        if (!$var) {
            if($msg === null || is_string($msg)){
                throw new \Exception($msg,$code);
            }else{
                if($msg instanceof \Exception)
                    throw $msg;
            }
        } else {
            return $var;
        }
    }

    /**
     * 判断为true 时抛出异常
     * @param $var
     * @param null $msg
     * @param int $code
     *
     * @return mixed
     * @throws \Exception
     */
    public static function isTrueWillException($var, $msg = null, $code=HTTP_SERVER_ERROR)
    {
        if ($var) {
            if($msg === null || is_string($msg)){
                throw new \Exception($msg,$code);
            }else{
                if($msg instanceof \Exception)
                    throw $msg;
            }
        } else {
            return $var;
        }
    }

    /**
     * 判断是否是URL
     * @param $url
     *
     * @return bool
     */
    public static function isUrl($url)
    {
        $regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
        if (!preg_match($regex, $url)) {
            return false;
        }
        return true;
    }

    /**
     * 判断是否是手机号(仅国内)
     * @param $phone
     *
     * @return int
     */
    public static function isMobile($phone)
    {
        $isMob = "/^1[3-5,7,8]{1}[0-9]{9}$/";
        if (preg_match($isMob, $phone)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 判断是否是电话号码
     *
     * @param $tel
     *
     * @return int
     */
    public static function isTel($tel)
    {
        $isTel = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";
        if (preg_match($isTel, $tel)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 判断是否是Email
     * @param $email
     *
     * @return int
     */
    public static function isEmail($email)
    {
        $pattern = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if (!preg_match($pattern, $email)) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * 判断是否是汉字
     * @param $target
     *
     * @return int
     */
    public static function isChineseString($target){
        return preg_match("/[\x7f-\xff]/", $target);
//        return preg_match("/[\u4E00-\u9FFF]+/", $target);
    }
}