<?php
namespace commons\framework;
use commons\framework\systemError\ForWriteLogException;

class Action
{
    //protected attr
    // 模板对象(当前版本使用smarty)
    protected $tpl;
    //模板文件目录
    protected $tpl_dir;
    //模板文件编译目录
    protected $tpl_compile_dir;
    //模板目录静态文件夹
    protected $tpl_static_dir;
    //模板目录下载文件夹
    protected $tpl_download_dir;
    //站点信息
    protected $site;
    //站点品牌名称
    protected $sign;
    /**
     * @var PDOSingletonDB|null 数据库操作对象 (当前为PDO)
     */
    protected $db;
    //数据库结构操作对象
    protected $db_structure_cache;
    //参数前缀
    protected $param_prefix;
    //表前缀
    protected $table_prefix;
    //表名(除去表前缀 )
    protected $table_name;
    //UI端参数名的前缀
    protected $prefix = 'post_';
    //路由对象
    protected $route;
    //分页:每页行数
    protected static $ITEM_ROWS = 10;
    //默认错误页位置(相对当前tpl_dir)
    protected $UI_ERROR_PAGE = "ui/error";
    //end protected attr
    private $isAdminOn = false;
    private $isRouteOn = false;
    private $isTPLOn = false;
    private $isDBOn = false;
    private $isDBNewOn = false;
    private $isDBStructureOn = false;
    private $isMultiSiteOn = false;
    private $isRequestFilterOn = false;
    private $isComplexControlOn = false;

    /**
     * Action constructor.
     *
     * @param int $settings
     * @param null $newDB
     */
    public function __construct($settings=ACTION_ALL_OFF,$newDB=null)
    {
        try{
            $this->isAdminOn = Tools::BitKeyInSetting(ACTION_ADMIN_ON,$settings);
            $this->isRouteOn = Tools::BitKeyInSetting(ACTION_ROUTE_ON,$settings);
            $this->isTPLOn = Tools::BitKeyInSetting(ACTION_TPL_ON,$settings);
            $this->isDBOn = Tools::BitKeyInSetting(ACTION_DB_ON,$settings);
            $this->isDBNewOn = Tools::BitKeyInSetting(ACTION_DB_NEW_ON,$settings);
            $this->isDBStructureOn = Tools::BitKeyInSetting(ACTION_DB_STRUCTURE_ON,$settings);
            $this->isMultiSiteOn = Tools::BitKeyInSetting(ACTION_MULTI_SITES_ON,$settings);
            $this->isRequestFilterOn = Tools::BitKeyInSetting(ACTION_REQUEST_FILTER_ON,$settings);
            $this->isComplexControlOn = Tools::BitKeyInSetting(ACTION_COMPLEX_CONTROL_ON,$settings);
            if($this->isTPLOn) {
                global $tpl;
                $this->tpl = $tpl;
                $this->initSmarty();
            }
            if($this->isRouteOn) $this->initRoute();
            if($this->isDBOn) $this->initDB($newDB);
            if($this->isMultiSiteOn) $this->initSite();
            if($this->isRequestFilterOn) $this->paramFilter();
            if($this->isComplexControlOn) $this->initComplexControl();
        }catch (\Exception $e){
            $this->Error($e);
        }
    }
    public function __destruct()
    {
        unset($this->tpl);
        unset($this->route);
        unset($this->db);
        unset($this->db_structure_cache);
        unset($this->site);
    }
    /*BEGIN 系统方法*/
    private function initComplexControl()
    {
        //TODO 复杂层次限权管理
    }

    /**
     * @param null $newDB
     */
    private function initDB($newDB=null)
    {
        if( $this->isDBNewOn){
            //TODO DB Setting check will has a method, "$newDB!=null&&is_array($newDB)" is not nicety
            Verify::isFalseWillException($newDB!=null&&is_array($newDB),new ForWriteLogException("设置了 ACTION_DB_NEW_ON 但没有传入数据库的配置 'newDB'",HTTP_SERVER_ERROR));
            $this->db = PDOSingletonDB::getInstance($newDB);
        }else{
            $this->db = PDOSingletonDB::getInstance(array(
                'DB_SERVER'         => DB_SERVER,
                'DB_NAME'           => DB_NAME,
                'DB_USER'             => DB_USER,
                'DB_PASS'             => DB_PASS,
                'DB_CHARSET'       => DB_CHARSET
            ));
        }
        if($this->isDBStructureOn){
            if(IS_APP_FIRST_RUN) $this->BuildDBStructureCache();
            $this->initDBStructure();
        }
    }
    private function initRoute(){
        global $routeConfig;
        $this->route = $routeConfig;
        $this->route['action'] = ucfirst(Tools::IsEmpty($this->route['action']) ? 'Index' : $this->route['action']);
        $this->route['method'] = lcfirst(Tools::IsEmpty($this->route['method']) ? 'index' : $this->route['method']);
    }
    /**
     * 初始化当前域名的站点信息(从数据缓存中读取)
     * @return bool
     * @throws \Exception
     */
    private function initSite(){
        if($this->isAdminOn) return false;
        $siteCache = FileCache::get(APP_DOMAIN);
        Verify::isFalseWillException(isset($siteCache)&&is_array($siteCache)&&count($siteCache)>0,
            new ForWriteLogException('站点数据缓存读取失败',HTTP_SERVER_ERROR));
        $this->site = $siteCache;
        $this->sign = $this->site[RouteUtil::KEY_SITE_SIGN];
        return true;
    }
    /**
     * 初始化当前域名当前访问目录的模板对象
     * @throws \Exception
     */
    private function initSmarty(){

        if ($this->isAdminOn){
            $this->tpl_dir = TPL_ROOT.'/admin';
            $this->tpl_compile_dir = TPL_ROOT.DIR_SEPARATOR."/admin/cache";
            $this->tpl_static_dir = TPL_URL.DIR_SEPARATOR.'/admin/static';
            $this->tpl_download_dir = TPL_URL.DIR_SEPARATOR.'/admin/static/download';
        }else{
            $siteDir = $this->isRouteOn ? $this->site[RouteUtil::KEY_SITE_DIR_NAME] : "";
            $siteDir = Tools::IsEmpty($siteDir)?"":$siteDir.DIR_SEPARATOR;
            $this->tpl_dir = TPL_ROOT.DIR_SEPARATOR.$siteDir;
            $this->tpl_compile_dir = TPL_ROOT.DIR_SEPARATOR.$siteDir."cache";
            $this->tpl_static_dir = TPL_URL.DIR_SEPARATOR.$siteDir.'static';
            $this->tpl_download_dir = TPL_URL.DIR_SEPARATOR.$siteDir.'static/download';
        }
        Tools::createDir($this->tpl_dir);
        Tools::createDir($this->tpl_compile_dir);
        Verify::isFalseWillException(is_dir($this->tpl_dir),'模板目录未找到',HTTP_SERVER_ERROR);
        Verify::isFalseWillException(is_dir($this->tpl_compile_dir),'模板缓存目录未找到',HTTP_SERVER_ERROR);
        $this->tpl->setTemplateDir($this->tpl_dir);
        $this->tpl->setCompileDir($this->tpl_compile_dir);
        $this->tpl->cache_lifetime = 3660;
        $this->tpl->caching = false;
        $this->tpl->left_delimiter = "<{";
        $this->tpl->right_delimiter = "}>";
        $this->tpl->assign('ROOT_URL',ROOT_URL);
        $this->tpl->assign('PUBLIC_URL',PUBLIC_URL);
        $this->tpl->assign('PUBLIC_STATIC_URL',PUBLIC_STATIC_URL);
        $this->tpl->assign('TPL_URL',TPL_URL);
        if($this->isRouteOn){
            $this->tpl->assign('ACTION_NAME', $this->route['action']);
            $this->tpl->assign('CURRENT_STATIC_URL',$this->tpl_static_dir);
            $this->tpl->assign('CURRENT_DOWNLOAD_URL',$this->tpl_download_dir);
            $this->tpl->unregisterPlugin('function','url');
            $this->tpl->registerPlugin('function','url','url');//注册url函数
        }
    }
    private function initDBStructure(){
        $this->db_structure_cache=DBStructure::ReadFromFileCache();
    }
    protected function BuildDBStructureCache(){
        DBStructure::BuildInFileCache($this->db);
    }

    /**
     * 统一的错误处理函数
     * @param \Exception $e
     * @param string $error_url
     *
     * @return bool
     * @throws \Exception
     */
    protected function Error(\Exception $e,$error_url=''){
        if (IS_APP_DEBUG)
            throw $e;
        if($e instanceof \PDOException){
            $e = new ForWriteLogException("数据库错误",$e->getCode());
        }
        if($e instanceof ForWriteLogException){
            $msg = ' [file: '.$e->getFile().'][line:'.$e->getLine().' ][msg:'.$e->getMessage().'] ';
            $this->log($msg);
            return false;
        }
        if($this->isAjax()){
            $this->ajaxReturn(array(KEY_STATUS=>$e->getCode(),KEY_MSG=>$e->getMessage()));
        }else{
            if (Tools::IsEmpty($error_url))
                $error_url = $this->UI_ERROR_PAGE;
            $this->assign('e',array('msg'=>$e->getMessage(),'code'=>$e->getCode()));
            $this->display($error_url);
        }
        return true;
    }
    protected function apiError(\Exception $e){
        if($e instanceof \PDOException){
            $e = new ForWriteLogException("数据库错误",$e->getCode());
        }
        if($e instanceof ForWriteLogException){
            $msg = ' [file: '.$e->getFile().'][line:'.$e->getLine().' ][msg:'.$e->getMessage().'] ';
            $this->log($msg);
            return false;
        }
        $this->apiDesAndBase64Return(['status'=>$e->getCode(),'msg'=>$e->getMessage()]);
        return true;
    }
    protected function log($msg){
        Tools::Error($this->routeToString(),$msg);
    }

    protected function email($emailContext){
        $to=urlencode($emailContext['to']);
        $title=urlencode($emailContext['title']);
        $subject=urlencode($emailContext['body']);
        $api = API_EMAIL_URI."?dizhi=".$to."&zhuti=".$title."&content=".$subject;
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'timeout'=>3,//单位秒
            )
        );
        $result =  file_get_contents($api,false,stream_context_create($opts));
        return $result;
    }
    protected function download($fileName, $dir = '')
    {
        Verify::isFalseWillException(!Tools::IsEmpty($fileName),"文件名称不可为空",HTTP_REQUEST_NOT_ALLOW);
        $url = $this->tpl_download_dir.DIR_SEPARATOR.$fileName;
        if(!Tools::IsEmpty($dir)) $url = $dir.DIR_SEPARATOR.$fileName;
        header('Content-Description: File Transfer');
        header('Content-type: application/force-download');
        header('Content-Disposition: attachment; filename=' . $fileName );
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        readfile($url);
    }
    /**
     * 编译并显示 smarty 模板
     *
     * @param $template //模板位置
     * @param bool $out //是否输出为html字符串
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function display($template, $out = false)
    {
        $this->prepareDisplay();
        if (!strstr($template, '/')) {
            if (is_array($this->route)
                && !Tools::IsEmpty($this->route['door'])
                && !Tools::IsEmpty($this->route['action'])
            ) {
                $tpl_root = '/' . $this->route['door'] . '/' . $this->route['action'] . '/';
                $template = $this->tpl_dir. $tpl_root . $template . '.html';
            } else {
                throw new \Exception('无法加载模板文件:' . $template);
            }
        } else {
            $template = $this->tpl_dir .DIR_SEPARATOR. $template . '.html';
        }
        if ($out) {
            return $this->tpl->fetch($template);
        } else {
            $this->tpl->display($template);
        }
        return true;
    }

    /**
     *  向 smarty 模板注册变量
     * @param $key
     * @param $value
     */
    protected function assign($key, $value)
    {
        $this->tpl->assign($key, $value);
    }

    /**
     * 判断 请求是否为 ajax 的 get 请求
     * @return bool
     */
    protected function isAjaxGet(){
        return $this->isAjax()&&$this->isGet();
    }
    /**
     * 判断 请求是否为 ajax 的 post 请求
     * @return bool
     */
    protected function isAjaxPost(){
        return $this->isAjax()&&$this->isPost();
    }
    /**
     * 判断 请求是否为 普通(非ajax) 的 get 请求
     * @return bool
     */
    protected function isGeneralGet(){
        return !$this->isAjax()&&$this->isGet();
    }
    /**
     * 判断 请求是否为 普通(非ajax) 的 post 请求
     * @return bool
     */
    protected function isGeneralPost(){
        return !$this->isAjax()&&$this->isPost();
    }

    /**
     * 判断是否为ajax 请求
     * @return bool
     */
    protected function isAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断是否为Get 请求
     * @return bool
     */
    protected function isGet()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'GET');
    }

    /**
     * 判断是否为post 请求
     * @return bool
     */
    protected function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    /**
     *  向客户端发送 ajax 应答信息
     * @param array $msg
     *  msg 结构如: array('status'=>200,'msg'=>'ok');
     * @return bool
     */
    protected function ajaxReturn(array $msg)
    {
        echo json_encode($msg);
//        return true;
        exit();
    }
    protected function apiDesAndBase64Return(array $msg)
    {
        echo Tools::DESEncrypt(APP_DES_KEY , json_encode($msg));
//        return true;
        exit();
    }
    /**
     * 重定向(向客户端发送 重定向 请求头)
     * @param string $url \\目标URL
     *
     * @return bool
     */
    protected function redirect($url)
    {
        header('location:' . $url , true, 302);
        return true;
//        exit;
    }
    protected function url($url){
        return url($url);
    }
    protected function getCurrentUrl()
    {
        if (!is_array($this->route)
            || Tools::IsEmpty($this->route['area'])
            || Tools::IsEmpty($this->route['action'])
            || Tools::IsEmpty($this->route['method'])
        ) {
            throw new \Exception("Rout 数据不完整");
        }
        if($this->route['method']=="index"){
            return url($this->route['area'] . "/" . $this->route['action'] );
        }else{
            return url($this->route['area'] . "/" . $this->route['action'] . "/" . $this->route['method']);
        }
    }
    /*END 系统方法*/
    /*BEGIN 系统工具方法*/
    protected function ip()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $IP = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $IP = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $IP = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $IP = getenv("HTTP_CLIENT_IP");
            } else {
                $IP = getenv("REMOTE_ADDR");
            }
        }
        return $IP;
    }
    protected function sql($sqlName){
        Verify::isFalseWillException(array_key_exists($sqlName,$this->db_structure_cache),new ForWriteLogException('查找SQL失败',HTTP_SERVER_ERROR));
        return $this->db_structure_cache[$sqlName];
    }
    protected function columns($tableName){
        Verify::isFalseWillException(array_key_exists($tableName,$this->db_structure_cache),new ForWriteLogException('查找Column失败',HTTP_SERVER_ERROR));
        return $this->db_structure_cache[$tableName];
    }
    protected function getTableName()
    {
        return $this->table_prefix . $this->table_name;
    }
    protected function replaceParamPrefix(array $params, $search, $replace)
    {
        if (Tools::IsEmpty($search)) throw new \Exception("替换目标不可为空.");
        $out = array();
        foreach ($params as $k => $v) {
            if (is_array($v)) $this->replaceParamPrefix($v,$search,$replace);
            //if (!strstr($k,$this->prefix)) continue;
            $ok = str_replace($search,$replace, $k);
            $out[$ok] = $v;
        }
        return $out;
    }
    protected function replaceAjaxParamPrefix(array $params, $search, $replace)
    {
        if (Tools::IsEmpty($search)) throw new \Exception("数据前缀上未设置.");
        $out = array();
        foreach ($params as $key => $param) {
            $param['k'] = str_replace($search,$replace, $param['k']);
            $out[$param['k']] = $param['v'];
        }
        return $out;
    }
    protected function fixPageInfo(array $pageInfo)
    {
        if (!isset($pageInfo['count'])) throw new \Exception("paging count 为空");
        $pageInfo['rows'] = self::$ITEM_ROWS;
        $pageInfo['currentpage'] = $pageInfo['page'];
        $pageInfo['url'] = $this->getCurrentUrl();
        $pageInfo['pagecount'] = ceil(intval($pageInfo['count']) / intval($pageInfo['rows']));
        $pageInfo['pageNum'] = Tools::numArray4Paging($pageInfo['pagecount']);
        $pageInfo['next'] = intval($pageInfo['pagecount']) <= intval($pageInfo['page']) ? intval($pageInfo['page']) + 1 : $pageInfo['pagecount'];
        $pageInfo['pre'] = intval($pageInfo['page']) != 1 ? intval($pageInfo['page']) - 1 : 1;
        return $pageInfo;
    }
    protected function getParams(array $params, $prefix = "")
    {
        if (!Tools::IsEmpty($prefix)) {
            $this->prefix = $prefix;
        }
        if ($this->isAjax()) {
            return $this->replaceAjaxParamPrefix($params,$this->prefix, $this->param_prefix);
        } else {
            return $this->replaceParamPrefix($params,$this->prefix, $this->param_prefix);
        }
    }
    private function paramFilter()
    {

        if ($this->isGet() && is_array($_GET)) {
            foreach ($_GET as $k => $v) {
                $_GET[$k] = Tools::IH($v);
            }
        }
        if ($this->isPost() && is_array($_POST)) {
            foreach ($_GET as $k => $v) {
                $_POST[$k] = Tools::IH($v);
            }
        }
        if ($this->isAjax() && is_array($_REQUEST)) {
            foreach ($_GET as $k => $v) {
                $_REQUEST[$k] = Tools::IH($v);
            }
        }
    }
    /*END 系统工具方法*/
    /*BEGIN UI逻辑方法*/
    protected function currentUser(){
        $user = json_decode($_SESSION[KEY_SESSION.KEY_USER],true);
        return $user;
    }
    protected function checkLogin($area='site'){
        if($_SESSION[KEY_SESSION.KEY_USER_ID]>0)
            return true;
        else
            $this->redirect(url($area."/login"));
        return false;//for IDE None Notice
    }
    protected function logout($area='site'){
        unset($_SESSION[KEY_SESSION.KEY_USER]);
        unset($_SESSION[KEY_SESSION.KEY_USER_ID]);
        unset($_SESSION[KEY_SESSION.KEY_USER_TYPE]);
        $this->redirect(url($area."/login"));
    }
    protected function adminLogout($area='operation'){
        unset($_SESSION[KEY_SESSION.KEY_ADMIN_ID]);
        $this->redirect(url($area."/login"));
    }
    protected function checkAdminLogin(){
        if($_SESSION[KEY_SESSION.KEY_ADMIN_ID]>0)
            return true;
        else
            $this->redirect(url("operation/login"));
        return false;//for IDE None Notice
    }
    private function prepareDisplay()
    {
        $this->tpl->assign('timestamp', time());
    }
    private function routeToString()
    {
        if(!isset($this->route)||!is_array($this->route)||count($this->route)==0){
            return 'Action';
        }
        return $this->route[RouteUtil::KEY_AREA]
            .DIR_SEPARATOR.$this->route[RouteUtil::KEY_ACTION]
            .DIR_SEPARATOR.$this->route[RouteUtil::KEY_METHOD];
    }
    /*END UI逻辑方法*/
}
