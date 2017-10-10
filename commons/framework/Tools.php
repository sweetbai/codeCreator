<?php
namespace commons\framework;
use commons\framework\systemError\ForWriteLogException;
class Tools
{
    /**
     * 接受客户端请求.
     * 解密字符串,
     * 设置请求语言
     * @return array|mixed|string
     */
    public static function request() {
        $data = file_get_contents("php://input");

        $out = self::Rc4Decrypt($data);

        $out = filterArr($out);

        return $out;
    }



    /**
     * 获取时间字符串
     * @return false|string
     */
    public static function getDatetime() {
        return date("Y-m-d H:i:s");
    }

    /**
     * 获取日期字符串
     * @return false|string
     */
    public static function getDate() {
        return date("Y-m-d");
    }

    /**
     * 用于session 的无重复字符串 [32位]
     * @return string
     */
    public static function UID32() {
        $data = uniqid().$_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . time() . rand();
//        return sha1($data);
        return md5($data);
    }

    /**
     * 获取RC4MD5 pwd
     * @return string
     */
    public static function getRc4Md5Key(){
        return md5(md5(RC4MD4_KEY1).RC4MD4_KEY2);
    }

    /**
     * RC4MD5 解密
     * @param $data
     *
     * @return mixed
     */
    public static function Rc4Decrypt($data){
        $pwd = self::getRc4Md5Key();
        return json_decode(self::rc4($pwd,base64_decode($data)),true);
    }
    /**
     * RC4MD5 加密
     * @param $data
     *
     * @return mixed
     */
    public static function Rc4Encrypt($data){
        $pwd = self::getRc4Md5Key();
        return base64_encode(self::rc4($pwd, json_encode($data,JSON_UNESCAPED_UNICODE)));
    }

    /**
     * RC4MD5 加密解密算法
     * @param $pwd //密钥
     * @param $data //需加密字符串
     *
     * @return string
     */
    public static function rc4 ($pwd, $data)
    {
        $key [] = "";
        $box [] = "";
        $cipher= "";
//        $pwd = str_pad($pwd, 256, chr(0)); //填充成256位,确保加密成功
        $pwd_length = strlen ( $pwd );
        $data_length = strlen ( $data );
        for($i = 0; $i < 256; $i ++) {
            $key [$i] = ord ( $pwd [$i % $pwd_length] );
            $box [$i] = $i;
        }
        for($j = $i = 0; $i < 256; $i ++) {
            $j = ($j + $box [$i] + $key [$i]) % 256;
            $tmp = $box [$i];
            $box [$i] = $box [$j];
            $box [$j] = $tmp;
        }
        for($a = $j = $i = 0; $i < $data_length; $i ++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box [$a]) % 256;
            $tmp = $box [$a];
            $box [$a] = $box [$j];
            $box [$j] = $tmp;
            $k = $box [(($box [$a] + $box [$j]) % 256)];
            $cipher .= chr ( ord ( $data [$i] ) ^ $k );
        }
        return $cipher;
    }


    /**
     * 获取客户端访问IP
     * @return mixed
     */
    public static function IP() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 发送成功信息到客户端
     * @param array $data //返回的数据包  [可选]
     * @param int $code  //错误码 [可选]
     * @param string $msg //错误信息 [可选]
     */
    public static function SendSuccess( $data = [], $code = 200, $msg='') {
        $response = [
            'status' => $code,
            'msg' => $msg
        ];
        $response = array_merge($response,$data);
        $responseEncrypt = self::Rc4Encrypt($response);
        echo $responseEncrypt;
        static::ThrowSuccessException($response['msg']); //软结束
    }

    /**
     *  异常处理通用方法
     *  如果是 ForWriteLogException 异常,且 IS_APP_LOG 开关值为 true 则将异常信息写入log文件
     *  否则 返回请求者(客户端)错误信息
     * @param \Exception $e
     */
    public static function CatchException(\Exception $e) {
        if ($e instanceof ForWriteLogException) {
            if (IS_APP_LOG)
                Tools::Error($e->getFile(), $e->getMessage());
        }else {
            self::Error($e->getMessage(), $e->getCode());
        }
    }

    /**
     *  发送错误信息到客户端
     * @param int $code  //错误码 [可选]
     * @param string $msg //错误信息 [可选]
     */
    public static function SendError( $code = 400, $msg='') {
        $response = [
            'status' => $code,
            'msg' =>$msg
        ];
        $responseEncrypt = self::Rc4Encrypt($response);
        echo $responseEncrypt;
        static::ThrowLogException(json_encode($response));
    }

    /**
     * 当客户端的请求响应(回应信息发送完毕)成功时,为了不使用exit 的软退出方法
     * @param $msg
     *
     * @throws ForWriteLogException
     */
    public static function ThrowSuccessException($msg) {
        throw new ForWriteLogException('[success][' . $msg);
    }

    /**
     * 与 ThrowSuccessException 同理
     * @param $msg
     *
     * @throws ForWriteLogException
     */
    public static function ThrowLogException($msg) {
        throw new ForWriteLogException('[Error][' . $msg . ']');
    }

    /**
     * 位运算: 添加 $key 到 $target
     * @param $key
     * @param $target
     *
     * @return int
     */
    public static function BitKeyAndSetting($key, $target)
    {
        return $target | $key;
    }

    /**
     * 位运算: 从 $target 中 去掉 $key 值
     * @param $key
     * @param $target
     *
     * @return int
     */
    public static function BitKeyRemoveSetting($key, $target)
    {
        return $target^$key;
    }
    /**
     *  位运算: 检测 $target 是否包含 $key
     *
     * @param $key
     * @param $target
     *
     * @return bool
     */
    public static function BitKeyInSetting($key, $target)
    {
        return ($target & $key) == $key;
    }

    /**
     * 去除空格, 换行, 制表符
     *
     * @param $target
     *
     * @return mixed
     */
    public static function trimAll($target)
    {
        return str_replace(array(" ", "　", "\t", "\n", "\r"), '', $target);
    }

    /**
     * des + base64 加密
     *
     * @param $key
     * @param $target
     *
     * @return string
     */
    public static function DESEncrypt($key, $target)
    {
        $des = new DES($key, 0);
        return base64_encode($des->encrypt($target));
    }

    /**
     * des + base64 解密
     *
     * @param $key
     * @param $target
     *
     * @return string
     */
    public static function DESDecrypt($key, $target)
    {
        $des = new DES($key, 0);
        return $des->decrypt(base64_decode($target));
    }

    /**
     * 重命名文件夹
     *
     * @param $old
     * @param $new
     *
     * @throws \Exception
     */
    public static function renameDir($old, $new)
    {
        if (!is_dir($old)) throw new \Exception('目标文件夹不存在', HTTP_SERVER_ERROR);
        if (is_dir($new)) throw new \Exception('已存在修改后的名称', HTTP_SERVER_ERROR);
        rename($old, $new);
    }

    /**
     * 获取目录下文件列表
     *
     * @param $dir
     *
     * @return array
     */
    public static function getDir($dir)
    {
        $dirArray[] = NULL;
        if (false != ($handle = opendir($dir))) {
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != "..") {
                    $dirArray[$i] = $file;
                    $i++;
                }
            }
            //关闭句柄
            closedir($handle);
        }
        return $dirArray;
    }

    /**
     * 创建目录
     *
     * @param $path
     * @param int $access
     * @param bool $recursive
     *
     * @return bool
     */
    public static function createDir($path, $access = 0777, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        if (mkdir($path, $access, $recursive)) {
            return chmod($path, $access);
        } else {
            return false;
        }
    }

    /**
     * 查找数据
     * @param $key
     * @param array $target
     *
     * @return bool|mixed
     */
    public static function findInArray($key, array $target)
    {
        if (is_array($target)) {
            return array_key_exists($key, $target) ? $target[$key] : false;
        } else {
            return false;
        }
    }

    /**
     * 分割数组
     * @param $key
     * @param array $target
     *
     * @return array
     */
    public static function spiltArray($key, array $target)
    {
        $bigger = array();
        $smaller = array();
        foreach ($target as $k => $v) {
            if ($k == $key) {
                $smaller[$k] = $v;
                continue;
            }
            $bigger[$k] = $v;
        }
        return array('bigger' => $bigger, 'smaller' => $smaller);
    }

    /**
     * 生成一个数组 并存入从1 到 $max
     * 主要用于分页的页码
     * @param $max
     *
     * @return array
     */
    public static function numArray4Paging($max)
    {
        $out = array();
        for ($i = 1; $i <= $max; $i++) {
            $out[] = $i;
        }
        return $out;
    }

    /**
     * 移除数据元素
     * @param $key
     * @param array $target
     *
     * @return array
     * @throws \Exception
     */
    public static function removeArrayElement($key, array $target)
    {
        $index = array_search($key, $target);
        if ($index !== false) {
            array_splice($target, $index, 1);
        } else {
            throw new \Exception("key:" . $key . " in array  not fount");
        }
        return $target;
    }

    /**
     * 打印log Error级别
     * @param $from
     * @param $msg
     */
    public static function Error($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_ERROR_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[ERROR]' . $msg);
    }
    /**
     * 打印log Debug级别
     * @param $from
     * @param $msg
     */
    public static function Debug($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_DEBUG_LEVEL, WF_LOG_DEBUG)) self::Log($from, '[DEBUG]' . $msg);
    }
    /**
     * 打印log Wring 级别
     * @param $from
     * @param $msg
     */
    public static function Wring($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_WRING_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[WRING]' . $msg);
    }
    /**
     * 打印log Info 级别
     * @param $from
     * @param $msg
     */
    public static function Info($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_INFO_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[INFO]' . $msg);
    }
    /**
     * 打印log ApiError 级别 API 调用专用
     * @param $from
     * @param $msg
     */
    public static function ApiError($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_API_ERROR_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[API_ERROR]' . $msg);
    }
    /**
     * 打印log ApiDebug 级别 API 调用专用
     * @param $from
     * @param $msg
     */
    public static function ApiDebug($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_API_DEBUG_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[API_DEBUG]' . $msg);
    }
    /**
     * 打印log ApiWring 级别 API 调用专用
     * @param $from
     * @param $msg
     */
    public static function ApiWring($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_API_WRING_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[API_WRING]' . $msg);
    }
    /**
     * 打印log ApiInfo 级别 API 调用专用
     * @param $from
     * @param $msg
     */
    public static function ApiInfo($from, $msg)
    {
        if (self::BitKeyInSetting(LOG_API_INFO_LEVEL, WF_LOG_LEVEL)) self::Log($from, '[API_INFO]' . $msg);
    }

    /**
     * 打印log 的通用方法
     * @param $from
     * @param $msg
     *
     * @throws \Exception
     */
    private static function Log($from, $msg)
    {
        try {
            $log_path = ROOT . '/datas/logs/';
            $date = new \DateTime();
            $now = $date->format('YmdHis');
            $now_hour = $date->format('YmdH');
            $log_file_name = $now_hour . '.log';
            $log_obj = new Logs($log_path, $log_file_name);
            $log_obj->LogDebug('[' . $from . '] ' . $msg);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 简单过滤数组键&值
     * @param array $params
     */
    public static function safetyTest(array $params)
    {
        foreach ($params as $k => $v) {
            $k = htmlspecialchars($k);
            $k = addslashes($k);
            $params[$k] = htmlspecialchars($v);
            $params[$k] = addslashes($v);
        }
    }

    /**
     * 转换成SQL Param 例 key -> :key
     *
     * @param array $params
     *
     * @return array
     */
    public static function replaceDBParams(array $params)
    {
        $r = array();
        foreach ($params as $k => $v) {
            $r[':' . $k] = $v;
        }
        return $r;
    }

    /**
     * 打印DeBug log
     *
     * @param array $params
     */
    public static function Debug2Page(array $params)
    {
        if (IS_APP_DEBUG) {
            foreach ($params as $k => $v) {
                if (is_array($v))
                    self::Debug2PageArray($k, $v);
                else
                    self::Debug2PageLine($k, $v);
            }
        }
    }

    /**
     * 普通打印
     *
     * @param $k
     * @param $v
     */
    public static function Debug2PageLine($k, $v)
    {
        echo $k . ' : ' . $v . '<br>';
    }

    /**
     * 打印数组
     *
     * @param $k
     * @param $v
     */
    public static function Debug2PageArray($k, $v)
    {
        echo $k . ':' . var_dump($v) . '<br>';
    }

    /**
     * 检测是否为空,并且可以设置默认值
     *
     * @param $v
     * @param null $default
     *
     * @return null
     */
    public static function I($v, $default = null)
    {
        return static::IsEmpty($v) ? $default : $v;
    }

    /**
     * 根据提供的函数$fun 处理过滤网页或客户端的值
     *
     * @param $v //原值
     * @param null $default //默认值
     * @param null $func //处理函数
     *
     * @return string
     */
    public static function IH($v, $default = null, $func = null)
    {
        $out = htmlspecialchars(static::IsEmpty($v) ? $default : $v);
        if (!self::IsEmpty($func)) call_user_func($func, $out);
        return $out;
    }

    /**
     * 为空或为0 返回 true
     *
     * @param $value
     *
     * @return bool
     */
    public static function IsEmpty($value)
    {
        return !isset($value) || empty($value) || $value == null;
    }

    /**
     * 为空 返回 true , 但为 0 时 被认为非空 返回 false
     *
     * @param $value
     *
     * @return bool
     */
    public static function IsEmptyButZero($value)
    {
        if ($value === 0) return false;
        return self::IsEmpty($value);
    }
}