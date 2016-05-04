<?php

/**
 *处理公告表的自动过期
 */

class NotifyCommand extends CConsoleCommand
{
    public function run($args) 
    {
       //所要执行的任务，如数据符合某条件更新，删除，修改
       $transaction=Yii::app()->db->beginTransaction();
       $time = date('Y-m-d');
       try
       {
            //找到昨天包括以前过期的公告
            $result = Notification::model()->findAll("expire_time != '0000-00-00' and expire_time < :time", array(":time"=>$time));
            //每条进行修改 把状态设置成hidden,如果有错误就抛出异常
            foreach($result as $row)
            {
               $row->status = 'hidden';
               $row->save();
               if($row->hasErrors())
               {
                    throw new Exception('error');
               }
            }
            $transaction ->commit();
       }
       catch(Exception $e)
       {
            $transaction ->rollBack();
            echo $e->getCode();
            echo $e->getMessage();
       } 
    }
}
