<?php
/**
 * 自动化执行 命令行模式
 * 执行语句 "e:\Program Files (x86)\php\php.exe" "current web app"\protected\yiic.php Test
 * 此脚本为转正脚本
 * 首先 待转正的试用员工
 * 第二 判断他们的转正日子（默认入职日期的后一个月，在添加员工的时候就设置regularized_date）
 * 第三 如果当天是转正日期的前7天就给提示
 */
class BecomeFormalCommand extends CConsoleCommand
{
    public function run($args) 
    {
        //时间为7天后的时间
        $date = date('Y-m-d' , strtotime('+6days'));
        $commissioner = Users::getCcommissioner();
        //找出待转正的试用员工
        if($users = Users::model()->findAll('job_status=:job_status',array(':job_status'=>'probation_employee')))
        {
            foreach($users as $user)
            {
                //如果转正日志为7天后就发消息通知
                if($user->regularized_date == $date)
                {
                    $this->sendMsg($user);
                    $this->sendMsg($commissioner);
                    $this->sendMail($user);
                    $this->sendMail($commissioner);
                }
            }
        }
    }

    public function sendMsg($user)
    {
        //发送转正消息
        //add notice row
    }

    public function sendMail($user)
    {
        if($user->online == 'on')
        {
            return true;
        }
        //sendmail
    }
}
