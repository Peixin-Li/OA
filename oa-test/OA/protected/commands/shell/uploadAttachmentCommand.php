<?php
/**
 * 自动化执行 通知上传附件
 */
class uploadAttachmentCommand extends CConsoleCommand
{
    public function run($args) {
       $host = "http://oa.i.shanyougame.com";  
       //所要执行的任务，如数据符合某条件更新，删除，修改
       $transaction=Yii::app()->db->beginTransaction();
       try
       {
            $types = array('sick'=>'病假','marriage'=>'婚嫁','maternity'=>'产假');
            $start = date('Y-m-d 00:00:00',strtotime('-1days'));
            $end   = date('Y-m-d 23:59:59',strtotime('-1days'));
            $result = Leave::model()->findAll("status=:status and image=:image and end_time >=:start and end_time <= :end and type in ('sick','marriage','maternity')",array(':status'=>'success',':start'=>$start, ':end'=>$end, ':image'=>''));

            foreach($result as $row)
            {
                $user = $row->user;
                $type = emptY($types[$row->type])?'请假':$types[$row->type];
                //通知消息
                $title ="你请的{$type}已结束，请尽快上传相应的证明附件";
                Notice::addNotice(array('user_id'=>$row->user_id,'content'=>"你请的{$type}已结束，请尽快上传相应的证明附件",'url'=>"/user/msg/leave/{$row->leave_id}",'status'=>'wait','title'=>$title,'type'=>'leave','msg_type'=>'todo','create_time'=>date('Y-m-d H:i:s')));
                //发送邮件
                $message = "<b>你请的{$type}已结束，请尽快上传相应的证明附件</b><br>";
                $message .= "<b>请假时间:{$row->start_time}到{$row->end_time}</b><br>";
                $message .="<b>详情请<a href='{$host}/user/msg/leave/{$row->leave_id}'>登录</a>查看</b>";
                $arr = array('user_id'=>$row->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email,'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
