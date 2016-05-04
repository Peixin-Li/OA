<?php
/**
 * 自动化执行 命令行模式
 * 执行语句 "e:\Program Files (x86)\php\php.exe" "current web app"\protected\yiic.php Test
 */
class OnlineCommand extends CConsoleCommand
{
    public function run($args) {
        //所要执行的任务，如数据符合某条件更新，删除，修改
        if($users = Users::model()->findAll(array('condition'=>'online=:online', 'params'=>array(':online'=>'on'), 'select'=>'user_id,heartbeat,online,cn_name,email,qq,mobile,department_id,title,second_department')))
        {
            $current = time();
            foreach($users as $user)
            {
                //如果心跳时间距离当前时间超过3分钟就把online更新为off
                if($current - $user->heartbeat >= 180)
                {
                    Users::updateUser($user, array('online'=>'off'));
                }
            }
        }
    }
}
