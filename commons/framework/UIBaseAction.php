<?php
namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/3/3
 * Time: 15:53
 */
class UIBaseAction extends Action
{
    protected $UI_ERROR_PAGE = "site/error.html";
    public function __construct($settings=ACTION_ALL_OFF,$newDB=null)
    {
        parent::__construct($settings,$newDB);
    }
    public function __destruct()
    {
        parent::__destruct();
    }
    public function ip()
    {
        return parent::ip();
    }
    public function url($url)
    {
        return parent::url($url);
    }
    public function columns($tableName)
    {
        return parent::columns($tableName);
    }
    public function sql($sqlName) { return parent::sql($sqlName); }
    public function Error(\Exception $e, $error_url="") { parent::Error($e, $error_url); }
    public function checkToken($token){
        if(isset($_SESSION[KEY_SESSION.KEY_USER_TOKEN])){
            return $_SESSION[KEY_SESSION.KEY_USER_TOKEN]!=$token;
        }else{
            $_SESSION[KEY_SESSION.KEY_USER_TOKEN] = $token;
            return true;
        }
    }
    public function checkUILogin(){
        if($_SESSION[KEY_SESSION.KEY_USER_ID]>0||is_array($_SESSION[KEY_SESSION.KEY_USER]))
            return true;
        else
            $this->redirect('login.php');
        return false;//for IDE
    }
    public function currentUser()
    {
        return parent::currentUser();
    }

    public function redirect($url)
    {
        parent::redirect($url);
    }
    public function apiError(\Exception $e)
    {
        return parent::apiError($e);
    }

    public function apiDesAndBase64Return(array $msg)
    {
        parent::apiDesAndBase64Return($msg);
    }

    public function ajaxReturn(array $msg)
    {
        parent::ajaxReturn($msg);
    }
    public function assign($key, $value)
    {
        parent::assign($key, $value);
    }
    public function prepare_display()
    {
        $this->assign('user', $this->currentUser());
        $this->assign('timestamp', time());
    }
    public function displayUI($template, $out = false)
    {
        $this->prepare_display();
        if ($out) {
            return $this->tpl->fetch($template);
        } else {
            $this->tpl->display($template);
        }
        return true;
    }
    public function display($template, $out = false)
    {
        $this->prepare_display();
        return parent::display($template, $out);
    }
    public function getParams(array $params, $prefix = ""){
        return parent::getParams($params,$prefix);
    }
    public function fixPageInfo(array $pageInfo){
        return parent::fixPageInfo($pageInfo);
    }
    public function replaceAjaxParamPrefix(array $params, $search, $replace){
        return parent::replaceAjaxParamPrefix($params,$search,$replace);
    }
    public function replaceParamPrefix(array $params, $search, $replace){
        return parent::replaceParamPrefix($params,$search,$replace);
    }
    public function isAjaxGet()
    {
        return parent::isAjaxGet(); 
    }
    public function isAjaxPost()
    {
        return parent::isAjaxPost(); 
    }
    public function isGeneralGet()
    {
        return parent::isGeneralGet(); 
    }
    public function isGeneralPost()
    {
        return parent::isGeneralPost(); 
    }
    public function isAjax()
    {
        return parent::isAjax();
    }
    public function isGet()
    {
        return parent::isGet();
    }
    public function isPost()
    {
        return parent::isPost();
    }
    /**
     * @return PDOSingletonDB|null
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return mixed
     */
    public function getParamPrefix()
    {
        return $this->param_prefix;
    }
    public function setParamPrefix($value)
    {
        $this->param_prefix=$value;
    }
    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function setPrefix($value)
    {
        $this->prefix=$value;
    }
    /**
     * @return mixed
     */
    public function getTablePrefix()
    {
        return $this->table_prefix;
    }
    public function setTablePrefix($value)
    {
        $this->table_prefix = $value;
    }
    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->table_name;
    }
    public function setTableName($value){
        $this->table_name=$value;
    }

    public function getPageRows(){
        return self::$ITEM_ROWS;
    }
    public function setErrorPage($page){
        $this->UI_ERROR_PAGE = $page;
    }

}