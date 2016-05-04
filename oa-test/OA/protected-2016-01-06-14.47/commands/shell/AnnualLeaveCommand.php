<?php

/**
 *处理年假的事情
 */

class AnnualLeaveCommand extends CConsoleCommand
{
    public function run($args) 
    {
       $transaction=Yii::app()->db->beginTransaction();
       try
       {
          $today = date('m-d');
          $users = Users::model()->findAll();
          foreach($users as $row)
          {
              $total = 0;
              $tag = true;
              if(!$model = $this->getAnnualLeaveRecord($row))
              {
                  $tag = false;
              }
              if($today == date('m-d',strtotime($row->entry_day)))
              {
                  if($row->annualLeaveTag == true)
                  {
                      $total = $row->annualLeaveDays;
                  }
                  if(!empty($tag))
                  {
                      AnnualLeave::processAnnualLeave($model, array('total'=>$total, 'refresh_time'=>date('Y-m-d H:i:s')));
                  }
                  else
                  {
                      AnnualLeave::processAnnualLeave(new AnnualLeave(), array('user_id'=>$row->user_id, 'total'=>$total, 
                          'refresh_time'=>date('Y-m-d H:i:s'),
                          'create_time'=>date('Y-m-d H:i:s') , 'update_time'=>date('Y-m-d H:i:s')));
                  }
              }
              elseif(empty($tag))
              {
                      AnnualLeave::processAnnualLeave(new AnnualLeave(), array('user_id'=>$row->user_id, 'total'=>$total, 
                          'refresh_time'=>date('Y-m-d H:i:s'),
                          'create_time'=>date('Y-m-d H:i:s') , 'update_time'=>date('Y-m-d H:i:s')));
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
     *查询当前用户的记录
     */
    public function getAnnualLeaveRecord($user)
    {
        if($model = AnnualLeave::model()->find("user_id=:user_id",array(':user_id'=>$user->user_id)))
        {
            return $model;
        }
        return false;
    }

}
