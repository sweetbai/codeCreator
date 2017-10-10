<?php
namespace commons\framework;

/**
 * 数据库连接类，建议不直接使用DB，而是对DB封装一层
 */
use PDO;
use PDOException;
class PDOSingletonDB {
   //pdo对象
   private $pdo = null;
   //默认错误码
   private $PDO_ERR_CODE = 10099;

   private $finalSql;
   //数据库链接参数
   static protected $inits = array();
   //用于存放实例化的对象
   static private $Instance = null;
    /**
     * 公共静态方法获取实例化的对象(创建数据库链接实例的唯一入口)
     *
     *@param array $params  数据库配置
     *
     * @return PDOSingletonDB|null
     */
    public static function getInstance(array $params) {
       if (!(self::$Instance instanceof self) || self::IsNewDB($params)) {
           self::$Instance = new self($params);
           self::$inits = $params;
       }
       return self::$Instance;
   }

    /**
     * 判断是否是一个新的数据库链接参数
     * @param array $params
     *
     * @return bool
     */
    private static function IsNewDB(array $params){
       foreach ($params as  $k=>$v){
           if (count(self::$inits)==0||self::$inits[$k]!=$params[$k])
               return true;
       }
       return false;
   }

    /**
     * 单例模式下 须私有克隆
     */
   private function __clone() {}

    /**
     * 单例模式下 须私有构造函数
     * DB constructor.
     *
     * @param array $params
     */
   private function __construct(array $params) {
       try {
           $dns = "mysql:host=".$params['DB_SERVER'].";dbname=".$params['DB_NAME'];
           $this->pdo = new PDO($dns, $params['DB_USER'], $params['DB_PASS'],
               array(
                   PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES '.$params['DB_CHARSET'],
                   PDO::ATTR_DRIVER_NAME=>'mysql',
                   PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
               )
           );
           //关闭使用PHP本地模拟prepare,可能会略微减低性能,但可以有效防止SQL注入
           $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
           $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       } catch (PDOException $e) {
           Tools::Error("DB.class",json_encode(array_merge(array('error'=>$e->getMessage(),'code'=>$e->getCode()),$params)));
           throw new PDOException($e->getMessage(),$e->getCode() or $this->PDO_ERR_CODE);
       }
   }

    /**
     * 添加数据,返回新曾数据的ID (必须为自增列)
     * 警告: $params 参数的Key 需要进行过滤. 比如与目标表的字段进行数据交集
     * @param $tableName
     * @param array $params
     * @param bool $removeAutoIncrement
     *
     * @return string
     */
    public function add($tableName, array $params,$removeAutoIncrement=true) {
       $fields = array();
       $ps = array();
       $values = array();
       foreach ($params as $k=>$v) {
           if(($k == $tableName.'_id'||$k == $tableName.'id'||$k=='id')&&$removeAutoIncrement) continue;
           $fields[] = '`'.$k.'`';
           $ps[] = ':'.$k;
           $values[':'.$k] = $v;
       }
       $_fields = implode(',', $fields);
       $_params = implode(',', $ps);
       $sql = "INSERT INTO ".$tableName." (".$_fields.") VALUES ( ".$_params.")  ";
        $result = $this->executeWithParams($sql,$values)->rowCount();
        $id = $this->pdo->lastInsertId();
        if(Tools::IsEmpty($id)){
            return $result;
        }else{
            return $id;
        }
   }

    /**
     * 更新,返回更新的行数
     * 警告: $params 参数的Key 需要进行过滤. 比如与目标表的字段进行数据交集
     * @param string $tableName 表名
     * @param string $where 条件语句
     * @param array $params 参数
     * @param bool $removeAutoIncrement
     *
     * @return int 更新的行数
     */
    public function update($tableName, $where , array $params ,$removeAutoIncrement=true) {
       $fields = $this->toolFixParams($params);
       $set = array();
       foreach ($fields['k'] as $k=>$v){
           if(($k == $tableName.'_id'||$k=='id')&&$removeAutoIncrement) continue;
           $set[] = $k.'=:'.$k;
       }
       $_set = implode(',',$set);
        $sql = "UPDATE  ".$tableName.' SET '.$_set.' WHERE '.$where;
        return $this->executeWithParams($sql,$fields['v'])->rowCount();
    }

    /**
     * 删除,返回删除的行数
     *
     * @param string $tableName
     * @param string $where
     * @param array $params
     *
     * @return int 删除的行数
     */
    public function delete($tableName, $where, array $params) {
       $sql = "DELETE FROM " . $tableName.' WHERE '.$where;
       return $this->executeWithParams($sql,$params)->rowCount();
   }

    /**
     * 获取下个自增列数据,列名通常为 id ,$tableName_id
     * @param $tableName
     *
     * @return mixed
     */
    public function nextAutoIncrement($tableName) {
        $_sql = "SHOW TABLE STATUS LIKE '$tableName'";
        $_stmt = $this->execute($_sql);
        return $_stmt->fetchObject()->Auto_increment;
    }

    /**
     * 通过系统表查询当前表的总行数
     * @param $tableName
     *
     * @return mixed
     */
    public function rowsTotalCount($tableName) {
        $_sql = "SHOW TABLE STATUS LIKE '$tableName'";
        $_stmt = $this->execute($_sql);
        return $_stmt->fetchObject()->Rows;
    }
    /**
     * 无参数查询单个字段
     * @param $sql
     *
     * @return mixed
     */
    public function queryGetColumn($sql){
        return $this->execute($sql)->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * 有参数查询单个字段
     * @param $sql
     * @param $params
     *
     * @return mixed
     */
    public function queryGetColumnWithParams($sql,$params){
        return $this->executeWithParams($sql,$params)->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * 无参数查询单行
     * @param $sql
     *
     * @return array
     */
    public function queryOne($sql){
        return $this->execute($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * 无参数查询多行
     * @param $sql
     *
     * @return array
     */
    public function queryAll($sql){
        return $this->execute($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询单行,带参数
     * @param $sql
     * @param array $params
     *
     * @return mixed
     */
    public function queryOneWithParams($sql,array $params){
        return $this->executeWithParams($sql,$params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 查询多行,带参数
     * @param $sql
     * @param array $params
     *
     * @return array
     */
    public function queryAllWithParams($sql,array $params){
        return $this->executeWithParams($sql,$params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *  执行无参数的SQL,返回行数
     * @param string $sql 带参数的SQL语句
     * @return int 行数
     */
    public function exec($sql){
        return $this->execute($sql)->rowCount();
    }

    /**
     * 执行带参数的SQL,返回行数
     * @param string $sql 无参数的SQL
     * @param array $params 参数
     * @return int 行数
     */
    public function execWithParams($sql, array $params){
        return $this->executeWithParams($sql,$params)->rowCount();
    }

    /**
     * 替换SQL中的参数为实时数据
     * @param $sql
     * @param array $params
     *
     * @return mixed
     */
    public function finalSql($sql,array $params){
        $out=$sql;
        foreach ($params as $k=>$v){
           if (is_string($v)) $v = "'".$v."'";
           $out = str_replace($k,$v,$out);
        }
        $this->finalSql = $out;
        return $out;
    }

    /**
     * 执行带参数SQL语句 此函数为预执行,还需要继续调用fetch,fetchAll,rowCount等函数,才可以获得执行结果
     * @param $sql
     * @param array $params
     *
     * @return \PDOStatement
     */
    public function executeWithParams($sql, array $params){
       try {
           $this->finalSql($sql,$params);
           $_stmt = $this->pdo->prepare($sql);
           foreach ($params as $k=>&$v){
               if (is_string($v))
                    $db_type = PDO::PARAM_STR;
               elseif (is_int($v))
                   $db_type = PDO::PARAM_INT;
               elseif (is_bool($v))
                   $db_type = PDO::PARAM_BOOL;
               elseif (is_null($v))
                   $db_type = PDO::PARAM_NULL;
               else
                   $db_type =false;
               if ($db_type)
                    $_stmt->bindParam($k,$v,$db_type);
               else
                   throw new PDOException("无法将'Null'或空值绑定至参数");
           }
           $_stmt->execute();
           return $_stmt;
       } catch (PDOException  $e) {
//           Tools::Error("DB.class",json_encode(array_merge(array('finalSql'=>$this->finalSql($sql,$params),'error'=>$e->getMessage()),$params)));
//           Tools::Error("DB.class",json_encode(array_merge(array('error'=>$e->getMessage()))));
           throw new PDOException($e->getMessage(),$e->getCode() or $this->PDO_ERR_CODE);
       }
   }

    /**
     * 执行无参数SQL语句 此函数为预执行,还需要继续调用fetch,fetchAll,rowCount等函数,才可以获得执行结果
     * @param $sql
     *
     * @return \PDOStatement
     */
    public function execute($sql) {
        try {
           $_stmt = $this->pdo->prepare($sql);
            $_stmt->execute();
           return $_stmt;
       } catch (PDOException  $e) {
//            Tools::Error("DB.class",json_encode(array_merge(array('error'=>$e->getMessage()))));
//            Tools::Error("DB.class",json_encode(array_merge(array('finalSql'=>$sql,'error'=>$e->getMessage()))));
            throw new PDOException($e->getMessage(),$e->getCode() or $this->PDO_ERR_CODE);
        }
   }

    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * 回滚事务
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * 组装参数数据
     * @param array $data
     *
     * @return array
     */
    public function toolFixParams(array $data){
        $r = array();
        foreach ($data as $k=>$v){
            $r['k'][$k] =':'.$k;
            $r['v'][':'.$k] =$v;
        }
        return $r;
    }

    /**
     * 组装普通 '=' 的条件语句 和参数值数组
     * @param array $data
     *
     * @return array
     */
    public function toolFixWhere(array $data)
    {
        $w = '';
        $params = $this->toolFixParams($data);
        foreach ($params['k'] as $k => $v) {
            $w .= ' ' . $k . '=' . $v . ' AND ';
        }
        $w .= ' 1=1 ';
        return array($w, $params['v']);
    }

    public function toolFixRealWhere(array $params){
        $w = '';
        foreach ($params as $k => $v) {
            $w .=" ".$k." = '".$v."' AND ";
        }
        $w .=' 1=1 ';
        return $w;
    }
    /**
     * 组装in(a,b,c) 的条件语句 和参数值数组
     * @param $key
     * @param $value
     *
     * @return array
     */
    public function toolFixWhereIn($key,$value)
    {
        $w = ' and '.$key.' in (';
        $params = explode(',',$value);
        $r = array();
        foreach ($params as  $k=>$v) {
            $p = ':p'.$k;
            $w .= $p.',';
            $r[$p] = $v;
        }
        $w = substr($w,0,strlen($w)-1);
        $w .= ') ';
        return array($w, $r);
    }

    /**
     * 自毁函数,谨慎调用,切记切记切记
     */
    public function destruct(){
        unset($this->pdo);
        self::$Instance = null;
        self::$inits = null;
    }
}