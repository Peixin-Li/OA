<?php

/**
 *加班表 用来扫描是否有一些加班记录未被确认
 */

class OvertimeCommand extends CConsoleCommand
{
    public function run($args) 
    {
       $host = ""; 
       //所要执行的任务，如数据符合某条件更新，删除，修改
       $transaction=Yii::app()->db->beginTransaction();
       try
       {
            $result = Overtime::model()->findAll(array('condition'=>"status =:status", 'params'=>array(':status'=>'wait'), 'group'=>'head_id,overtime_date;'));

            foreach($result as $row)
            {
                $user = Users::model()->findByPk($row->head_id);
                //通知消息
                $title ="{$row->overtime_date}的部门加班表,请尽快确认";
                Notice::addNotice(array('user_id'=>$user->user_id,'content'=>"{$row->overtime_date}的部门加班表，前去确认",'url'=>"/oa/processOverTime/date/{$row->overtime_date}",'status'=>'wait','title'=>$title,'type'=>'overtime','msg_type'=>'todo','create_time'=>date('Y-m-d H:i:s')));
                //发送邮件
                $message = "<b>时间:{$row->overtime_date}</b><br>";
                $message.= "<b>{$row->overtime_date}的部门加班表，前去确认</b><br>";
                $message.="<b>详情请<a href='{$host}/oa/processOverTime/date/{$row->overtime_date}'>登录</a>查看</b>";
                $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email,'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                Mail::createMail($arr);
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
