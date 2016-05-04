<?php

/**
 *处理转正的通知
 */

class QualifiedCommand extends CConsoleCommand
{
    public function run($args) 
    {
       //所要执行的任务，如数据符合某条件更新，删除，修改
       $time = date('Y-m-d',strtotime('-2months +15days'));
       $host = "http://oa.i.shanyougame.com"; 
       $url  = "/oa/positiveApply";
       $transaction=Yii::app()->db->beginTransaction();
       try
       {
          $user = Users::getHr();
          if($result = Users::model()->findAll("status = :status and job_status = :job_status and entry_day = :time",array(':status'=>'work', ':job_status'=>'probation_employee',':time'=>$time)))
          {
            foreach($result as $row)
            {
                $title   = "现在可以去给{$row->cn_name}提交申请转正了";
                $content = "现在可以去给{$row->cn_name}提交申请转正了。";
                if(!$this->addNotice(array('user_id'=>$user->user_id, 'content'=>$content, 'url'=>$url, 'status'=>'wait', 'type'=>'qualify','title'=>$title, 'create_time'=>date('Y-m-d H:i:s'))))
                {
                    throw new Exception('notice error');
                }
                if(!$this->createMail(array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email,'subject'=>$title, 'message'=>"<a href='{$host}{$url}'>$content</a>",'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') )))
                {
                    throw new Exception('mail error');
                }
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


    /**
     *发邮件
     */
    public  function createMail($data)
    {
        try
        {
            $model = new Mail();
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            $this->processSaveError($model);
            return $model->mail_id;
        }
        catch(Exception $e)
        {
        }
       	return false;
    }

    /**
     *发消息
     */
    public  function addNotice($data)
    {
        try
        {
            $model = new Notice();
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            $this->processSaveError($model);
            return $model->id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *处理异常
     */
    public  function processSaveError($model)
    {
        if($model->hasErrors())
        {
            $message = '';
            if($errors = $model->getErrors())
            {
                foreach($errors as $error)
                {
                    $error[0] = empty($error[0]) ? '' : rtrim($error[0], '.');
                    $message .=", {$error[0]}";
                }
            }
            $message = trim($message, ',').'.';
            throw new Exception($message);
        }
    }
}
