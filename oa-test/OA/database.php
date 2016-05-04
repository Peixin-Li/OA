<?php
/**
*@authr jeff
*@data 2014-04-07
*@用ＰＨＰ查看MYSQL信息的工具
**/
header("Content-type: text/html; charset=utf-8");

//pdo数据库类，单例模式
class db
{
     //数据库配置
    //在此设置mysql host
    const MYSQL_HOST = '192.168.0.253';
    //在此设置 mysql user
    const MYSQL_USRE = 'root';
    //在此设置 mysql password
    const MYSQL_PASSWORD = '#Edcvfr4';
    //在此设置 要查看的 数据库名
    const MYSQL_DATABASE = 'OADEV';
    public $dsn;
    public $dbh;

    //保存类实例的静态成员变量
    private static $_instance;
    /**
     * 连接数据库
     * @function __construct 构造函数
     */

    private function __construct()
    {
        try
        {      
            $this->dsn = "mysql:host=".self::MYSQL_HOST.";dbname=".self::MYSQL_DATABASE;
            $this->dbh = new PDO($this->dsn, self::MYSQL_USRE, self::MYSQL_PASSWORD);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE,  PDO::ERRMODE_EXCEPTION);
            $this->dbh->exec("SET CHARACTER SET utf8");
        }
        catch (PDOException $e)
        {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    /**
     *单例方法，用来访问实例的公共方法
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     *显示表的信息详情
     *@function tablesInfo
     *@return array()
     **/
    public function tablesInfo()
    {
       //查询字段
       $sql = "SELECT TABLE_NAME,ENGINE,round(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024),2) as DATA_LENGTH, TABLE_COMMENT";
       //查询哪个表
       $sql .=" FROM INFORMATION_SCHEMA.TABLES";
       //条件
       $sql .=" WHERE table_schema = :database";
       $stmt = $this->dbh->prepare($sql);
       $stmt->execute(array(':database'=> self::MYSQL_DATABASE));
       if(!$data = $stmt->fetchAll(PDO::FETCH_ASSOC))
       {
           die("没有获取到所有表的数据");
       }
       $result = '<table border="1">';
       $result .= "<caption>所有表信息</caption>";
       $result .= "<tr><th>表名</th><th>表引擎</th><th>表大小(M)</th><th>说明</th></tr>";
       foreach($data as  $record)
        {
            $result .= '<tr>';
            $result .= "<td><a target='_blank' href='database.php?tableName={$record['TABLE_NAME']}'>{$record['TABLE_NAME']}</a></td>";
            $result .= "<td>{$record['ENGINE']}</td>";
            $result .= "<td>{$record['DATA_LENGTH']}</td>";
            $result .= "<td>{$record['TABLE_COMMENT']}</td>";
            $result .= '</tr>';
            $tag  = true;
        }
        $result .= '</table>';
        echo $result;
    }
   
    /**
     *单个表信息
     *@function tableInfo
     *@param string $tableName
     *@return array
     */
    public function tableInfo($tableName)
    {
        $sql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,COLUMN_COMMENT,COLUMN_KEY";
        $sql.= " FROM INFORMATION_SCHEMA.COLUMNS";
        $sql.= " WHERE table_name = :tablename";
        $sql.= " AND table_schema = :database;";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array(':tablename'=>$tableName, ':database'=> self::MYSQL_DATABASE));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     *打印表格信息
     *@function printTable
     *@param array $data
     **/
    public function printTable($tableName,$data)
    {
        $result = '<table border="1">';
        $result .= "<caption>{$tableName}表信息</caption>";
        $result .= "<tr><th>字段名</th><th>字段类型</th><th>是否为空</th><th>默认值</th><th>备注</th><th>主键</th></tr>";
        foreach($data as  $record)
        {
            $result .= '<tr>';
            $result .= "<td>{$record['COLUMN_NAME']}</td>";
            $result .= "<td>{$record['COLUMN_TYPE']}</td>";
            $result .= "<td>{$record['IS_NULLABLE']}</td>";
            $result .= "<td>{$record['COLUMN_DEFAULT']}</td>";
            $result .= "<td>{$record['COLUMN_COMMENT']}</td>";
            $result .= "<td>{$record['COLUMN_KEY']}</td>";
            $result .= '</tr>';
        }
        $result .= '</table>';
        echo $result;
    }

    /**
     *虚构函数
     **/
    public function __destruct()
    {
        $this->dsn = null;
        $this->dbh = null;
    }
}

//这里是内容
$pd = db::getInstance();
//就是打印所有表
if(empty($_GET['tableName']))
{
    $pd->tablesInfo();
}
else //打印表信息
{
    $tablename = $_GET['tableName'];
    $re = $pd->tableInfo($tablename);
    $pd->printTable($tablename,$re);
}

