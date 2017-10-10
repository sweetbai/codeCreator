<?php
namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/2/24
 * Time: 15:44
 */
class Model extends PDOSingletonDB
{
    protected $db;
    protected  $table_pre = "";
    protected  $table_name = "";
    public function __construct(){
        $this->db = PDOSingletonDB::getInstance(array(
            'DB_SERVER'         => DB_SERVER,
            'DB_NAME'           => DB_NAME,
            'DB_USER'             => DB_USER,
            'DB_PASS'             => DB_PASS,
            'DB_CHARSET'     => DB_CHARSET
        ));
    }
    protected function GetDB(){
        return $this->db;
    }
    protected function GetDateTime(){
        $dt = new \DateTime();
        return $dt->format('Y-m-d H:i:s');
    }

    protected function GetTableName(){
        return $this->table_pre.$this->table_name;
    }


}