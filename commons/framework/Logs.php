<?php
namespace commons\framework;


class Logs{
    private $FilePath;
    private $FileName;
    private $m_MaxLogFileNum;
    private $m_RotaType;
    private $m_RotaParam;
    private $m_InitOk;
    private $m_Priority;
    private $m_LogCount;


    public function __construct($dir, $filename, $priority = Logs::DEBUG, $maxlogfilenum = 3, $rotatype = 1, $rotaparam = 5000000)
    {
        try{
            $this->FileName = $filename;
            $this->FilePath = $dir;
            $this->m_MaxLogFileNum = intval($maxlogfilenum);
            $this->m_RotaParam = intval($rotaparam);
            $this->m_RotaType = intval($rotatype);
            $this->m_Priority = intval($priority);
            $this->m_LogCount = 0;
            $this->m_InitOk = $this->InitDir();
            umask(0000);
            $path=$this->FilePath.$this->FileName;
            if(!$this->isExist($path))
            {
                if(!$this->createDir($this->FilePath))
                {
                    throw new \Exception("创建日志目录失败!");
                }
                if(!$this->createLogFile($path)){
                    throw new \Exception("创建日志文件失败!");
                }
            }
        }catch (\Exception $e){
            throw $e;
        }

    }
    private function InitDir()
    {
        try{
            if (is_dir($this->FilePath) === false)
            {
                if(!$this->createDir($this->FilePath))
                {
                    return false;
                }
            }
            return true;
        }catch (\Exception $e){
            throw $e;
        }

    }

    /**
     * @abstract 写入日志
     * @param String $log 内容
     */

    public function setLog($log)
    {
        $this->Log(Logs::NOTICE, $log);
    }
    public  function LogDebug($log)
    {
        $this->Log(Logs::DEBUG, $log);
    }
    public function LogError($log)
    {
        $this->Log(Logs::ERROR, $log);
    }
    public function LogNotice($log)
    {
        $this->Log(Logs::NOTICE, $log);
    }
    public function Log($priority, $log)
    {
        try{
            if ($this->m_InitOk == false)
                return;
            if ($priority > $this->m_Priority)
                return;
            $path = $this->getLogFilePath($this->FilePath, $this->FileName);
            $handle=@fopen($path,"a+");
            if ($handle === false)
            {
                return;
            }
            $datestr = strftime("%Y-%m-%d %H:%M:%S ");
            $caller_info = $this->get_caller_info();
            if(!@fwrite($handle, $caller_info.$datestr.$log."\n")){//写日志失败
                throw new \Exception("写入日志失败");
            }
            @fclose($handle);
            //$this->RotaLog();
        }catch (\Exception $e){
            throw $e;
        }

    }
    private function get_caller_info()
    {
        try{
            $ret = debug_backtrace();
            foreach ($ret as $item)
            {
                if(isset($item['class']) && 'Logs' == $item['class'])
                {
                    continue;
                }
                $file_name = basename($item['file']);
                return <<<S
{$file_name}:{$item['line']}  
S;

            }
            return null;
        }catch (\Exception $e){
            throw $e;
        }

    }
//    private function RotaLog()
//    {
//        try{
//            $file_path = $this->getLogFilePath($this->FilePath, $this->FileName);
//            if ($this->m_LogCount%10==0)
//                clearstatcache();
//            ++$this->m_LogCount;
//            $file_stat_info = stat($file_path);
//            if ($file_stat_info === FALSE)
//                return;
//            if ($this->m_RotaType != 1)
//                return;
//
//            //echo "file: ".$file_path." vs ".$this->m_RotaParam."\n";
//            if ($file_stat_info['size'] < $this->m_RotaParam)
//                return;
//
//            $raw_file_path = $this->getLogFilePath($this->FilePath, $this->FileName);
//            $file_path = $raw_file_path.($this->m_MaxLogFileNum - 1);
//            //echo "lastest file:".$file_path."\n";
//            if ($this->isExist($file_path))
//            {
//                unlink($file_path);
//            }
//            for ($i = $this->m_MaxLogFileNum - 2; $i >= 0; $i--)
//            {
//                if ($i == 0)
//                    $file_path = $raw_file_path;
//                else
//                    $file_path = $raw_file_path.$i;
//
//                if ($this->isExist($file_path))
//                {
//                    $new_file_path = $raw_file_path.($i+1);
//                    if (rename($file_path, $new_file_path) < 0)
//                    {
//                        continue;
//                    }
//                }
//            }
//        }catch (\Exception $e){
//            throw $e;
//        }
//
//    }

    function isExist($path){
        try{
            return file_exists($path);
        }catch (\Exception $e){
            throw $e;
        }
    }


    function createDir($dir){
        try{
            return is_dir($dir) or ($this->createDir(dirname($dir)) and @mkdir($dir, 0777));
        }catch (\Exception $e){
            throw $e;
        }
    }


    function createLogFile($path){
        try{
            $handle=@fopen($path,"w"); //创建文件
            @fclose($handle);
            return $this->isExist($path);
        }catch (\Exception $e){
            throw $e;
        }
    }


    function getLogFilePath($dir,$filename){
        return $dir."/".$filename;
    }
    const EMERG  = 0;
    const FATAL  = 0;
    const ALERT  = 100;
    const CRIT   = 200;
    const ERROR  = 300;
    const WARN   = 400;
    const NOTICE = 500;
    const INFO   = 600;
    const DEBUG  = 700;
}
