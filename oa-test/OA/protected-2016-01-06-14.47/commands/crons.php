<?php
    defined('YII_DEBUG') or define('YII_DEBUG',true);
    //including Yii
    require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/framework/yii.php');
    //we'll use a separate config file
    $configFile=dirname(dirname(__FILE__)).'/config/console.php';
    //creating and running console application
    Yii::createConsoleApplication($configFile)->run();
?>
