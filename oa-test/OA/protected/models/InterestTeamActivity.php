<?php

/**
 * This is the model class for table "interest_team_activity".
 *
 * The followings are the available columns in table 'interest_team_activity':
 * @property integer $id
 * @property integer $team_id
 * @property string $end_time
 * @property string $activity_time
 * @property string $contact
 * @property string $mobile
 * @property string $address
 * @property string $line
 * @property string $outlay
 * @property string $status
 * @property string $update_time
 * @property string $create_time
 */
class InterestTeamActivity extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return InterestTeamActivity the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'interest_team_activity';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('team_id, end_time, activity_time,  update_time, create_time', 'required'),
			array('team_id', 'numerical', 'integerOnly'=>true),
			array('contact', 'length', 'max'=>45),
			array('mobile', 'length', 'max'=>11),
			array('address', 'length', 'max'=>100),
			array('line', 'length', 'max'=>500),
			array('outlay', 'length', 'max'=>6),
			array('status', 'length', 'max'=>7),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, team_id, end_time, activity_time, contact, mobile, address, line, outlay, status, update_time, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'team'=>array(self::BELONGS_TO, 'InterestTeam', 'team_id'),
            'joins'=>array(self::HAS_MANY, 'InterestTeamJoin','activity_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'team_id' => 'Team',
			'end_time' => 'End Time',
			'activity_time' => 'Activity Time',
			'contact' => 'Contact',
			'mobile' => 'Mobile',
			'address' => 'Address',
			'line' => 'Line',
			'outlay' => 'Outlay',
			'status' => 'Status',
			'update_time' => 'Update Time',
			'create_time' => 'Create Time',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('team_id',$this->team_id);
		$criteria->compare('end_time',$this->end_time,true);
		$criteria->compare('activity_time',$this->activity_time,true);
		$criteria->compare('contact',$this->contact,true);
		$criteria->compare('mobile',$this->mobile,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('line',$this->line,true);
		$criteria->compare('outlay',$this->outlay,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *取消活动
     *@param object $activity
     */
    public static function cancelActivity($activity)
    {
       try
       {
            $transaction=Yii::app()->db->beginTransaction();
            if(!InterestTeamActivity::processTeamActivity($activity, array('status'=>'cancel', 'update_time'=>date('Y-m-d H:i:s'))))
            {
                throw new Exception('-1');
            }
            $host = Yii::app()->request->hostInfo;
            $url  = '/user/activity';
            $joins = $activity->joins;
            $title = "{$activity->team->name}活动已经被组长取消";
            $message = "亲爱的同事们：<br>";
            $message .= "    组长已经取消本次{$activity->team->name}活动，下次举办时间待定。<br>";
            $message .= "    请大家及时留意OA兴趣小组最新活动信息，一起活动起来吧：<a href='{$host}{$url}'>{$host}{$url}</a><br>";
            if(!empty($joins))
            {
                foreach($joins as $join)
                {
                    if(!InterestTeamJoin::processJoinActivity($join, array('status'=>'fail', 'update_time'=>date('Y-m-d H:i:s'))))
                    {
                        throw new Exception('-1');
                    }
                    $arr = array('user_id'=>$join->user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$join->user->email,'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                    Mail::createMail($arr);
                }
            }
            $transaction ->commit();
            return true;
       }
       catch(Exception $e)
       {
            $transaction ->rollBack();
            //echo $e->getCode();
            //echo $e->getMessage();
       } 
       return false;
    }

    /**
     *处理兴趣小组预算
     */
    public static function processTeamActivity($model, $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model->id;
        }
        catch(Exception $e)
        {
            //var_dump($e->getMessage());
        }
        return false;
    }

    /**
     *发邮件通知已经报名的人
     */
    public static function sendMail($activity)
    {
       try
       {
            $transaction=Yii::app()->db->beginTransaction();
            $joins = $activity->joins;
            $title = "{$activity->team->name}活动将于".date('Y-m-d H:i',strtotime($activity->activity_time))."开始";
            $message = "亲爱的同事们：<br>";
            $message .= "    {$activity->team->name}活动信息已确认，如下：<br>";
            $message .= "    联系人:{$activity->contact}；手机号码:{$activity->mobile}；活动时间".date('Y-m-d H:i',strtotime($activity->activity_time))."；活动地点:{$activity->address}；路线说明：{$activity->line}<br>";
            $message .= "    本活动有任何疑问的，直接与联系人联系，祝大家玩的愉快<br>";
            foreach($joins as $row)
            {
                $arr = array('user_id'=>$row->user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$row->user->email,'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                Mail::createMail($arr);
            }
            $transaction ->commit();
            return true;
       }
       catch(Exception $e)
       {
            $transaction ->rollBack();
            //echo $e->getCode();
            //echo $e->getMessage();
       } 
       return false;
    }


    /**
     *成功举办后 设置活动
     */
    public static function setActivityInfo($activity, $outlay, $users)
    {
       try
       {
           $transaction=Yii::app()->db->beginTransaction();
           if(!self::processTeamActivity($activity, array('status'=>'success','outlay'=>$outlay)))
           {
               throw new Exception('-1');
           }
           //减少活动预算
           $budget = InterestTeamBudget::model()->find("year=:year and team_id=:team_id",array(':year'=>date('Y'),':team_id'=>$activity->team_id));
           $budget->cost += $outlay;
           $budget->save();
           Helper::processSaveError($budget);
           //设置参与的人员
           $joins = array();
           $_joins = $activity->joins;
           foreach($_joins as $row)
           {
               $joins[$row->user_id] = $row;
           }
           $keys = array_keys($joins);
           //新加入的人
           $news = array_diff($users, $keys);
           $time = date('Y-m-d H:i:s');
           if(!empty($news))
           {
               foreach($news as $new)
               {
                   if(!InterestTeamJoin::processJoinActivity(new InterestTeamJoin(), array('activity_id'=>$activity->id, 'user_id'=>$new, 'status'=>'join', 'update_time'=>$time, 'create_time'=>$time)))
                   {
                       throw new Exception('-1');
                   }
               }
           }
            //缺席的人
           $absent = array_diff($keys , $users);
           //参加的人
           $attend = array_intersect($keys, $users);
           foreach($joins as $key=>$join)
           {
               if(in_array($key, $attend))
               {
                   $join->status = 'join';
               }
               elseif(in_array($key, $absent))
               {
                   $join->status = 'absent';
               }
               $join->save();
               Helper::processSaveError($join);
           }
           $transaction ->commit();
           return true;
       }
       catch(Exception $e)
       {
            $transaction ->rollBack();
            //echo $e->getCode();
            //echo $e->getMessage();
       } 
       return false;
    }
}
