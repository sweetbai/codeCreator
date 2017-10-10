<?php
namespace commons\framework;
use PDO;
use commons\framework\systemError\ForWriteLogException;

class DBStructure
{
    const SELECT = '_select';
    const SELECT_BY_ID = '_select_by_id';
    const DELETE = '_delete';
    const DELETE_BY_ID = '_delete_by_id';
    const COUNT = '_count';
    const KEY_DB_STRUCTURE = 'DB_STRUCTURE';
    const DIR_CACHE_PATH = ROOT.'/datas/cache/site_init/';
    private  $db;
    public function __construct(PDOSingletonDB $db)
    {
        $this->db = $db;
    }
    public static function get_column($table,PDOSingletonDB $db){
        $cols = $db->execute('DESC '.$table)->fetchAll(PDO::FETCH_COLUMN);
        return $cols;
    }

    public static function get_tables(PDOSingletonDB $db){
        $tabs = $db->execute('SHOW tables')->fetchAll(PDO::FETCH_COLUMN);
        return $tabs;
    }

    public static function get_all_structure(PDOSingletonDB $db){
        $tabs = self::get_tables($db);
        $structure = array();
        foreach ($tabs as $k=>$tableName){
            $structure[$tableName] = self::get_column($tableName,$db);
        }
        return $structure;
    }
    public static function BuildInFileCache(PDOSingletonDB $db){
        $content = array();
        $tables = self::get_tables($db);
        foreach ($tables as $i=>$table) {
            $columns = self::get_column($table,$db);
            $select = "  select ";
            foreach ($columns as $k=>$col){
                $select .=" ".$col.",";
            }
            $select = substr($select,0,strlen($select)-1);
            $select.=" from ".$table." ";
            $sql = array(
                $table.self::SELECT =>$select,
                $table.self::SELECT_BY_ID=>$select." where ".$table."_id = :".$table."_id ",
                $table.self::DELETE =>" delete from ".$table." ",
                $table.self::DELETE_BY_ID=>" delete from ".$table." where ".$table."_id = :".$table."_id ",
                $table.self::COUNT=>"select count(*)  from ".$table." ",
            );
            $content[$table] = $columns;
            $content = array_merge($content,$sql);
        }
        FileCache::set(self::KEY_DB_STRUCTURE,$content,FileCache::EXPIRE_10_YEAR,self::DIR_CACHE_PATH);
    }
    public static function ReadFromFileCache(){
        return FileCache::get(self::KEY_DB_STRUCTURE,self::DIR_CACHE_PATH);
    }

    public  static function sql($sqlName){
        $fileCache = self::ReadFromFileCache();
        Verify::isFalseWillException(array_key_exists($sqlName,$fileCache),new ForWriteLogException('查找SQL失败',HTTP_SERVER_ERROR));
        return $fileCache[$sqlName];
    }
    public  static function columns($tableName){
        $fileCache = self::ReadFromFileCache();
        Verify::isFalseWillException(array_key_exists($tableName,$fileCache),new ForWriteLogException('查找Column失败',HTTP_SERVER_ERROR));
        return $fileCache[$tableName];
    }

}