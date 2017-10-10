<?php
namespace commons\framework;

class DES
{

    var $key;

    var $iv;
    // 偏移量
    function __construct($key, $iv = 0)
    {
        $this->key = $key;
        if ($iv == 0) {
            $this->iv = $key;
        } else {
            $this->iv = $iv;
        }
    }
    
    // 加密
    function encrypt($str, $my = "")
    {
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($str, $size);

        $data = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
        
        return base64_encode($data);
    }
    
    // 加密cookie
    function encryptco($str, $username = '', $password = '')
    {
        $my = $str;
        
        $llxx = $this->getBrowser() . $this->getBrowserVer();
        $str = $_SERVER['REMOTE_ADDR'] . "|" . $str . "|" . $llxx;
        if ($username != "" && $password != "") {
            
            $str .= "|" . $username . "|" . $password;
        }
        
        // var_dump($str);
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($str, $size);
        
        $data = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
        // 根据hash计算把密码串的数字换成QjZhd
        $zhi = $this->get_hash_zhi($_SERVER['REMOTE_ADDR']);
        $mi = str_replace($zhi, $my, base64_encode($data));
        
        return $mi;
    }
    
    // 解密cookie
    function decryptco($str, $my)
    {
        // 根据hash计算把密码串的数字换成混淆秘钥$my
        $zhi = $this->get_hash_zhi($_SERVER['REMOTE_ADDR']);
        $str = str_replace($my, $zhi, $str);
        
        $str = base64_decode($str);
        // $strBin = $this->hex2bin( strtolower($str));
        $str = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv);
        $str = $this->pkcs5Unpad($str);
        $arr = explode('|', $str);
        $ip = $_SERVER['REMOTE_ADDR'];
        $llxx = $this->getBrowser() . $this->getBrowserVer();
        if ($arr[0] == $ip && $arr[1] == $my && $arr[2] == $llxx) {
            
            return '{"code":1,"value":' . json_encode($arr) . '}';
        } else {
            
            return '{"code":0}';
        }
    }

    function get_hash_zhi($ip, $s = 5)
    {
        $hash = sprintf("%u", crc32($ip));
        $hash1 = intval(fmod($hash, $s));
        
        return $hash1;
    }
    
    // 解密
    function decrypt($str, $my = "")
    {
        $str = base64_decode($str);
//        $strBin = $this->hex2bin( strtolower($str));
        $str = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv);
        $str = $this->pkcs5Unpad($str);
        return $str;
    }

    function hex2bin($hexData)
    {
        $binData = "";
        for ($i = 0; $i < strlen($hexData); $i += 2) {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }

    function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, - 1 * $pad);
    }

    function getBrowser()
    {
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) // ie11判断
            return "ie";
        else 
            if (strpos($agent, 'Firefox') !== false)
                return "firefox";
            else 
                if (strpos($agent, 'Chrome') !== false)
                    return "chrome";
                else 
                    if (strpos($agent, 'Opera') !== false)
                        return 'opera';
                    else 
                        if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false)
                            return 'safari';
                        else
                            return 'unknown';
    }

    function getBrowserVer()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) { // 当浏览器没有发送访问者的信息的时候
            return 'unknow';
        }
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs))
            return $regs[1];
        elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs))
            return $regs[1];
        else
            return 'unknow';
    }
}

// $str = 'abcd';
// $key= 'asdfwef5';
// $crypt = new CDES($key);
// $mstr = $crypt->encrypt($str);
// $str = $crypt->decrypt($mstr);
//
// echo $str.' <=> '.$mstr;
?>