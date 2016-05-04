<?php
/**
 *这个是帮助脚本
 */
class Helper
{
    /**
     *判断$model有产生错误
     *如果有错误就抛出异常
     */
    public static function processSaveError($model)
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

    /**
     *输出问候语
     */
    public static function printGreetings()
    {
          echo '，';
          //写一些问候语
          $morning = array('早上好','每天都是新的自己','早晨给个微笑吧','新的一天好心情');
          $moon = array('幸福是吃到饱','做个快乐吃货','吃饭时间到了');
          $rest = array('饭饱神虚歇一歇','静下来听首歌','午间小憩一下');
          $afternoon = array('下午好','忙碌是最迷人剪影','抖擞精神工作');
          $dinner = array('晚餐时间到了');
          $evening = array('晚上好','累了音乐走起','辛勤一天你是最棒');
          //深夜
          $night = array('夜深了早点休息','晚安做个好梦','夜深了晚安','晚安地球人');
          $current_time = date('H:i');
          
          if($current_time > '23:00')
          {
              if(empty(Yii::app()->session['night']))
              {
                  Yii::app()->session['night']= $night[array_rand($night,1)];
              }
              echo Yii::app()->session['night'];
          }
          elseif($current_time > '19:00')
          {
              if(empty(Yii::app()->session['evening']))
              {
                  Yii::app()->session['evening']= $evening[array_rand($evening,1)];
              }
              echo Yii::app()->session['evening'];
          }
          elseif($current_time > '18:30')
          {
              if(empty(Yii::app()->session['dinner']))
              {
                  Yii::app()->session['dinner']= $dinner[array_rand($dinner,1)];
              }
              echo Yii::app()->session['dinner'];
          }
          elseif($current_time > '13:40')
          {
              if(empty(Yii::app()->session['afternoon']))
              {
                  Yii::app()->session['afternoon']= $afternoon[array_rand($afternoon,1)];
              }
              echo Yii::app()->session['afternoon'];
          }
          elseif($current_time > '12:40')
          {
              if(empty(Yii::app()->session['rest']))
              {
                  Yii::app()->session['rest']= $rest[array_rand($rest,1)];
              }
              echo Yii::app()->session['rest'];
          }
          elseif($current_time > '12:00')
          {
              if(empty(Yii::app()->session['moon']))
              {
                  Yii::app()->session['moon']= $moon[array_rand($moon,1)];
              }
              echo Yii::app()->session['moon'];
          }
          else//if($current_time > '06:00')
          {
              if(empty(Yii::app()->session['morning']))
              {
                  Yii::app()->session['morning']= $morning[array_rand($morning,1)];
              }
              echo Yii::app()->session['morning'];
          }
    }
}
