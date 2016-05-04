<?php
/**
 * 自动化执行 命令行模式
 * 执行语句 "e:\Program Files (x86)\php\php.exe" "current web app"\protected\yiic.php Test
 * 此脚本为发送邮件脚本
 * 首先 找出未发送的邮件
 * 第二 选择邮件的发送人
 * 第三 发送
 * 第四 根据返回结果更新此邮件的状态 （如果失败就发送3次，3次失败就标记为无效）
 */
class MailCommand extends CConsoleCommand
{
    public function run($args) 
    {
        set_time_limit(0);
        //找到所有的待发邮件
         if($mails = Mail::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'wait'))))
         {
             foreach($mails as $obj_mail)
             {
                try
                {
                     if($this->sendMail($obj_mail->receive_email , $obj_mail->subject , $obj_mail->message))
                     {
                        Mail::processMail($obj_mail, array('status'=>'success','update_time'=>date('Y-m-d H:i:s'), 'count'=>($obj_mail->count+1)));
                     }
                     else if($obj_mail->count >= 2)
                     {
                        Mail::processMail($obj_mail, array('status'=>'fail','update_time'=>date('Y-m-d H:i:s'), 'count'=>($obj_mail->count+1), 'reason'=>'发送失败'));
                     }
                     else
                     {
                        Mail::processMail($obj_mail, array('status'=>'wait','update_time'=>date('Y-m-d H:i:s'), 'count'=>($obj_mail->count+1)));
                     }
                 }
                 catch(Exception $e)
                 {
                 }
             }
         }
    }

    public function sendMail($email , $subject , $message)
    {
        $mailer = Yii::createComponent('application.extensions.mailer.EMailer');
        $mailer->IsSMTP();
        $mailer->SMTPAuth = true;
        //$mailer->SMTPDebug = true;   
        $mailer->IsHTML(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->Host = Yii::app()->params['smtp_host'];
        $mailer->From = Yii::app()->params['smtp_email'];
        $mailer->AddAddress($email);
        $mailer->FromName = '善游';
        $mailer->Username = Yii::app()->params['smtp_email'];
        $mailer->Password = Yii::app()->params['stmp_password'];
        $mailer->Subject = $subject;
        $mailer->Body = $message;
        return $mailer->Send();
    }
}
