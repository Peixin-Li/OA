<?php

/**
 *活动脚本
 */

class ActivityCommand extends CConsoleCommand
{
    public function run($args) 
    {
       //所要执行的任务，如数据符合某条件更新，删除，修改
       $transaction=Yii::app()->db->beginTransaction();
       $host = "http://oa.i.shanyougame.com"; 
       $url  = '/user/activity';
       $time = date('Y-m-d');
       $start = date('Y-m-d 00:00:00', strtotime($time));
       $end = date('Y-m-d 23:59:59', strtotime($time));
       try
       {
            //找到于今天结束报名的活动
           if($result = InterestTeamActivity::model()->findAll("end_time >=:start and end_time <= :end and status=:status", array(":start"=>$start, ':end'=>$end, ':status'=>'enroll')))
           {
                //活动名称
                $names = array();
                foreach($result as $activity)
                {
                    $names[]= $activity->team->name;
                }
                if(!empty($names))
                {
                    $name = join($names, '、');
                    $title    = "{$name}活动今天将截止报名，大家尽快报名";
                    $message  = "亲爱的同事们：<br>";
                    $message .= "    {$name}活动今天将截止报名，在忙碌的工作中，抽出点时间发展自己的兴趣和加强锻炼。<br>";
                    $message .= "    请大家登录OA，找到兴趣小组，报名参加活动吧。<br>";
                    $message .= "    OA兴趣小组链接：<a href='{$host}{$url}'>{$host}{$url}</a><br>";
                    $arr = array('user_id'=>0, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>'all@shanyougame.com','subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                   if(!Mail::createMail($arr))
                   {
                        throw new Exception('error');
                   }
                }
           }
            //找到昨天截止报名的活动
            $_start = date('Y-m-d 00:00:00', strtotime('-1days'));
            $_end = date('Y-m-d 23:59:59', strtotime('-1days'));
            if($_result = InterestTeamActivity::model()->findAll("end_time >=:start and end_time <= :end and status=:status", array(":start"=>$_start, ':end'=>$_end, ':status'=>'enroll')))
            {
                foreach($_result as $_row)
                {
                    $_joins = $_row->joins;
                    $_join_num = empty($_joins) ? 0 : count($_joins);
                    if($_join_num >= $_row->team->min_num)
                    {
                        $_row->status = 'hold';
                        $title    = "{$_row->team->name}活动报名人数符合要求，尽快去举办吧";
                        $message  = "组长：<br>";
                        $message .= "    你的{$_row->team->name}活动已截止报名，可以开始计划具体活动信息(联系方式、时间、地点、路线)，计划完后，登录OA发送确认的活动信息通知给大家！<br>";
                        $arr = array('user_id'=>$_row->team->admin, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$_row->team->user->email,'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                        if(!Mail::createMail($arr))
                        {
                            throw new Exception('error');
                        }
                    }
                    else
                    {
                        $_row->status = 'cancel';
                        $title    = "{$_row->team->name}活动取消通知";
                        $message  = "亲爱的同事们：<br>";
                        $message .= "    {$_row->team->name}活动已截止报名，但未符合举办最低人数要求，已关闭活动！<br>";
                        $message .= "    请大家及时留意OA兴趣小组最新活动信息，一起活动起来吧：<a href='{$host}{$url}'>{$host}{$url}</a><br>";
                        if(!empty($_joins))
                        {
                            foreach($_joins as $_join)
                            {
                                $_join->status = 'fail';
                                $_join->save();
                                Helper::processSaveError($_join);
                                $arr = array('user_id'=>$_join->user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$_join->user->email,'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                               if(!Mail::createMail($arr))
                               {
                                    throw new Exception('error');
                               }
                            }
                        }
                    }
                    $_row->save();
                    Helper::processSaveError($_row);
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
