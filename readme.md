### PHP DB查询库
##### create：2021-8-27
##### version：0.1.0 
##### description
关于DB查询库，其用法主要模仿至TP，从中在使用细节上做了一些优化，特别简化了where的条件生成，
并增强了多表查询的优化
###### 环境要求
`mysql: >=5.7`
`php: >=7.4`

安装

    composer require doit/db dev-master
    
### 初始化
######
    $config = \EasyDb\Config\MysqlConfig::set('192.168.200.3','iot','root','root');//静态设置 配置对象
    $drive  = new \EasyDb\Drive\MysqlPdoDrive();//静态获取 驱动对象

    \EasyDb\Db::setConfig($config);//注入配置和驱动
    \EasyDb\Db::setDrive($drive);
######

### Query
基本是模仿TP DB操作流程

常用方法
1. `toArray` 与TP一样，获得select 数组结果集

###### 基本查询
    $db = \EasyDb\Db::table('user');
    $db->where(["id"=>1])->select();
###### AND
    $db->where(["id"=>1,"name"=>"john"])->select();
* SELECT * FROM user WHERE id=1 AND name=2

传入数组，元素不被数组包裹的情况下，为AND连接
###### OR
    $db->where(["id"=>1,["name"=>"john"]])->select();
* SELECT * FROM user WHERE id=1 OR name="john"

跟随条件<font color=red>单层</font>数组包裹，将变为OR


###### 括号优先级
    $db->where(["id"=1,[["name"=>"john","id"=>1]]])->select();
* SELECT * FROM user WHERE id=1 OR (name="john" AND id=1 )

当查询条件被<font color=red>双层</font>数组包裹时，这将进行 "()" 包裹起来

###### JOIN
    $db->where(["id"=1,[["name"=>"john","id"=>1]]]);
    $db->join('group','`group`.`id` = `user`.`group_id`');
    











