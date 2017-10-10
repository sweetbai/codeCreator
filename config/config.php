<?php
/*DB Settings*/
//本项目 DB
define("DB_SERVER", 'localhost');
define("DB_NAME", 'tour');
define("DB_USER", 'root');
define("DB_PASS", '123456');
define("DB_CHARSET", '\'utf8\'');
//ADMIN DB

/* End DB Settings*/



define("KEY_SESSION", "SESSION_KEY_");
define("KEY_SIGN", "SIGN");
define("KEY_CAO_TOKEN", "SIGN");
define("KEY_USER", "USER_INFO");
define("KEY_USER_ID", "USER_ID");
define("KEY_USER_TYPE", "USER_TYPE");
define("KEY_USER_TOKEN", "USER_TOKEN");
define("KEY_ADMIN", "ADMIN_INFO");
define("KEY_ADMIN_ID", "ADMIN_INFO");
define("KEY_ADMIN_TOKEN", "ADMIN_TOKEN");
define("KEY_SMS_CODE", "USER_SMS_CODE");
define("KEY_SMS_SEND_TIME", "USER_SMS_SEND_TIME");
define("KEY_VERIFY_IMAGE_CODE", "VERIFY_IMAGE_CODE");
/* END APP GLOBAL*/
/*HTTP  CODE*/
define("HTTP_REQUEST_SUCCESS", 200);                  //请求成功
define("HTTP_REQUEST_NOT_ARRIVE", 400);           //错误请求 — 请求中有语法问题，或不能满足请求
define("HTTP_REQUEST_NOT_ALLOW", 403);           //禁止访问
define("HTTP_REQUEST_NOT_FOUND", 404);           //找不到资源
define("HTTP_SERVER_ERROR", 500);                         //服务器内部错误
/*END HTTP  CODE */
define("KEY_STATUS", 'status');
define("KEY_MSG", 'msg');
/*ACTION SETTING*/
define("ACTION_ALL_OFF", 0);                               //默认全部功能关闭,只保留基本功能
define("ACTION_DB_ON", 1);                                 //启用默认数据库
define("ACTION_TPL_ON",2);                                 //启用模板(此版本为smarty)
define("ACTION_ADMIN_ON",4);                           //是系统管理员功能
define("ACTION_DB_NEW_ON",8);                          //使用新数据库
define("ACTION_ROUTE_ON",16);                              //启用路由
define("ACTION_MULTI_SITES_ON",32);                    //启用多站点
define("ACTION_DB_STRUCTURE_ON",64);               //启用SQL,与表字段缓存
define("ACTION_REQUEST_FILTER_ON",128);            //启用数据过滤
define("ACTION_COMPLEX_CONTROL_ON",256);     //启用复杂的权限管理

//普通页面设置
define("ACTION_PAGE_DEFAULT_USE",ACTION_DB_ON | ACTION_TPL_ON | ACTION_DB_STRUCTURE_ON | ACTION_REQUEST_FILTER_ON);
//系统管理后台页面设置
define("ACTION_PAGE_ADMIN_USE",ACTION_DB_ON | ACTION_TPL_ON | ACTION_ADMIN_ON | ACTION_DB_STRUCTURE_ON | ACTION_REQUEST_FILTER_ON);
//API专用设置
define("ACTION_API_USE",ACTION_DB_ON  | ACTION_ADMIN_ON | ACTION_DB_STRUCTURE_ON );
/*END ACTION SETTING */

/*FILESYSTEM LOG LEVEL*/

define("LOG_ALL_OFF", 0);                               //默认关闭LOG
define("LOG_INFO_LEVEL", 1);                          //开启LOG等级 INFO
define("LOG_DEBUG_LEVEL",2);                        //开启LOG等级 DEBUG
define("LOG_WRING_LEVEL",4);                        //开启LOG等级 WRING
define("LOG_ERROR_LEVEL",8);                         //开启LOG等级 ERROR
define("LOG_API_INFO_LEVEL",16);                   //开启LOG等级 API INFO
define("LOG_API_DEBUG_LEVEL",32);                //开启LOG等级 API DEBUG
define("LOG_API_WRING_LEVEL",64);               //开启LOG等级 API WRING
define("LOG_API_ERROR_LEVEL",128);              //开启LOG等级 API ERROR

//打印所有级别的LOG
define("WF_LOG_ALL",PHP_INT_MAX-1);
//打印ERROR级别的LOG
define("WF_LOG_ERROR",LOG_ERROR_LEVEL | LOG_API_ERROR_LEVEL );
//打印WRING级别以上的LOG
define("WF_LOG_WRING",LOG_WRING_LEVEL | LOG_API_WRING_LEVEL | LOG_ERROR_LEVEL | LOG_API_ERROR_LEVEL  );
//打印DEBUG级别以上的LOG
define("WF_LOG_DEBUG",LOG_DEBUG_LEVEL | LOG_API_DEBUG_LEVEL | LOG_WRING_LEVEL | LOG_API_WRING_LEVEL | LOG_ERROR_LEVEL | LOG_API_ERROR_LEVEL  );

//LOG打印的级别,系统会根据此级别打印LOG, 正式上线请使用 LOG_ERROR 级别,避免LOG文件过大.
define("WF_LOG_LEVEL", WF_LOG_ERROR);
/*END FILESYSTEM LOG LEVEL */

define("RC4MD4_KEY1",'@$s1j4jz');
define("RC4MD4_KEY2",'*&jz');



