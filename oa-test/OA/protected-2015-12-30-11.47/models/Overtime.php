<?php

/**
 * This is the model class for table "overtime".
 *
 * The followings are the available columns in table 'overtime':
 * @property integer $id
 * @property string $overtime_date
 * @property string $overtime_time
 * @property integer $user_id
 * @property integer $head_id
 * @property string $status
 * @property string $update_time
 * @property string $create_time
 */
class Overtime extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Overtime the static model class
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
		return 'overtime';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('start_time, end_time, type, total_day, user_id, content, head_id, create_time', 'required'),
			array('user_id, head_id', 'numerical', 'integerOnly'=>true),
			array('status', 'length', 'max'=>7),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, overtime_date, overtime_time, user_id, head_id, status, update_time, create_time', 'safe', 'on'=>'search'),
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
            'user'=>array(self::BELONGS_TO, 'Users', 'user_id'),
            'logs'=>array(self::HAS_MANY, 'OvertimeLog','overtime_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'overtime_date' => 'Overtime Date',
			'overtime_time' => 'Overtime Time',
			'user_id' => 'User',
            'content' => 'Content',
			'head_id' => 'Head',
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
		$criteria->compare('overtime_date',$this->overtime_date,true);
		$criteria->compare('overtime_time',$this->overtime_time,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('head_id',$this->head_id);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *处理加班表
     *@param object $model
     *@param array $data
     *@return boolean
     */
    public static function processOvertime($model , $data)
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
     *标记活动参加
     *@param array $data array(1,2,3,5) Id的一维数组
     *@param string $tag enum('success','reject')
     *@param string $user_id 负责人ID
     */
    public static function tagOvertime($data, $tag, $user_id)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            foreach($data as $row)
            {
                $_overtime = Overtime::model()->findByPk($row);
                if($_overtime->head_id != $user_id)
                {
                    throw new Exception('permission denied','-99');
                }
                self::processOvertime($_overtime, array('status'=>$tag));
            }
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            //echo $e->getCode(); echo $e->getMessage();
            $transaction->rollback();
        }
        return false;
    }

    /**
     *获取leader的ID
     *@param object $user
     *@return int
     */
    public static function getLeader($user)
    {
        if(in_array($user->department->name ,array('AC','ULT')))
        {
            if($_user = Users::model()->find('department_id = :id and title=:title and status=:status and user_id != :user_id',array(':id'=>$user->department_id,'title'=>'主策',':status'=>'work', ':user_id'=>$user->user_id)))
            {
                return $_user->user_id;
            }
        }
        return $user->leadId;
    }
    /**
     *查找工作的天数
     */
    public function getCountWorkTime()
    {
        $_start = date("Y-m-d",strtotime($this->start_time));
        $_end   = date("Y-m-d",strtotime($this->end_time));
        $_start_time = date("H:i",strtotime($this->start_time));
        $_end_time   = date("H:i",strtotime($this->end_time));
        $count = 0; 
        if($this->type == 'normal') return $count;
        for($i= $_start; $i<= $_end; $i = date('Y-m-d', strtotime('+1days', strtotime($i))))
        {
            $count ++;
            if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
            {
                $count -= 0.5;
            }
        }
        return $count;
    }

    /**
     *通过部门来求取人数
     */
    public static function getOvertimeCountByDepartment($start, $end, $department_id,$user_id,$status)
    {
        $params = array(':start'=>$start, ':end'=>$end);
        $sql = "select count(*) as num from overtime join users on (overtime.user_id = users.user_id) where start_time >= :start and start_time <= :end  ";

        if(in_array($status, array('wait','success','reject')))
        {
            $sql .= "and overtime.status =:status ";
            $params[':status'] = $status;
        }
        if(preg_match('/^\d+$/', $user_id))
        {
            $sql .= "and overtime.user_id =:user_id ";
            $params[':user_id'] = $user_id;
        }
        if(preg_match('/^\d+$/', $department_id))
        {
            $sql .= "and users.department_id =:department_id ";
            $params[':department_id'] = $department_id;
        }
        return Yii::app()->db->createCommand($sql)->queryScalar($params);
    }


    /**
     *通过部门来求取人数
     */
    public static function getOvertimeDataByDepartment($start, $end, $department_id='' ,$user_id='' ,$status='success' , $limit='' , $offset='')
    {
        $params = array(':start'=>$start, ':end'=>$end);
        $sql = "select overtime.* from overtime join users on (overtime.user_id = users.user_id) where start_time >= :start and start_time <= :end ";
        if(in_array($status, array('wait','success','reject')))
        {
            $sql .= "and overtime.status =:status ";
            $params[':status'] = $status;
        }
        if(preg_match('/^\d+$/', $user_id))
        {
            $sql .= "and overtime.user_id =:user_id ";
            $params[':user_id'] = $user_id;
        }
        if(preg_match('/^\d+$/', $department_id))
        {
            $sql .= "and users.department_id =:department_id ";
            $params[':department_id'] = $department_id;
        }
        $sql .= " order by users.department_id, users.user_id asc";
        if(preg_match('/^\d+$/', $limit))
        {
            $sql .= " limit {$limit}  offset {$offset} ";
        }
        //return Yii::app()->db->createCommand($sql)->queryScalar($params);
        return self::model()->findAllBySql($sql, $params);
    }

    /**
     *处理请假审批
     *@param object $ovedrtime 加班对象
     *@param array  $data 日志数据
     *@param array  $is_log 是否记录日志
     */
    public static function approveOvertime($overtime, $data, $is_log = true)
    {
        $transaction = Yii::app()->db->beginTransaction();
        $status_arr_zh = array('agree'=>'已通过', 'reject'=>'未通过');
        $status_arr_en = array('agree'=>'success', 'reject'=>'reject');
        try
        {
            if($is_log) {
                if(!OvertimeLog::processOvertimeLog(new OvertimeLog(), $data))
                    throw new Exception('-1');
            }
            // Overtime::noticeHeadApprove($procedure_list[0]);
            $procedure_list = CJSON::decode($overtime->procedure_list, true);
            //如果流程为空、审批的人员为流程的最后一个人、审批者拒绝申请，则改变申请的状态
            if(empty($procedure_list)|| ($data['user_id'] == end($procedure_list)) || $data['action'] =='reject' ) {
                $overtime->status = $status_arr_en[$data['action']] ;
                $status = $status_arr_zh[$data['action']];
                $overtime->next = 0;

                $url = "/oa/overtimeDetail/id/{$overtime->id}";
                $host = Yii::app()->request->hostInfo;
                //成功了发送大家备案
                if($overtime->status == 'success')
                {
                    $title = "{$overtime->user->cn_name}提交的".date('Y-m-d',strtotime($overtime->start_time))."的加班申请{$status},请备案";
                    $content = "{$overtime->user->cn_name}提交的从".date('Y-m-d H:i',strtotime($overtime->start_time))."到".date('Y-m-d H:i',strtotime($overtime->end_time))."的加班申请{$status},请备案";
                    $message = "<b>姓名:</b> {$overtime->user->cn_name}<br><b>时间:</b>".date('Y-m-d H:i',strtotime($overtime->start_time))."到".date('Y-m-d H:i',strtotime($overtime->end_time))."{$status}<br><b>详情:</b> 请<a href='{$host}{$url}'>登录</a>查看";
                    self::sendNotice(Users::getAdminId(), $url, $title, $content , $message);
                    self::sendNotice(Users::getHr(), $url, $title, $content , $message);
                    self::sendNotice(Users::getCcommissioner(), $url, $title, $content , $message);
                }
                //当前加班申请不是等待状态（已通过或被退回），则发给自己的消息
                if($overtime->status != 'wait') {
                    $url = "/user/overtimeDetail/id/{$overtime->id}";
                    $title = "你提交的".date('Y-m-d',strtotime($overtime->start_time))."的加班申请{$status}";
                    $content = "你提交的从".date('Y-m-d H:i',strtotime($overtime->start_time))."到".date('Y-m-d H:i',strtotime($overtime->end_time))."的加班申请{$status}";
                    $message = "<b>姓名:</b> {$overtime->user->cn_name}<br><b>时间:</b>".date('Y-m-d H:i',strtotime($overtime->start_time))."到".date('Y-m-d H:i',strtotime($overtime->end_time))."{$status}<br><b>详情:</b> 请<a href='{$host}{$url}'>登录</a>查看";
                    self::sendNotice($overtime->user, $url, $title, $content , $message);
                }
                $overtime->save();
                $transaction->commit();
            }
            //通知下一个审批者
            else {
                foreach ($procedure_list as $key => $value) {
                    if($data['user_id'] == $value)
                        break;
                }
                $overtime->next = $procedure_list[$key+1];  //更改next为下一个审批者
                $overtime->save();
                $transaction->commit();
                Overtime::noticeHeadApprove($overtime->id, $procedure_list[$key+1]);
            }
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            Yii::log($e , 'info' , 'operation.log');
        }
        return false;
    }


    /**
     *通过user_id求补休天数 从2015-01-01起的
     */
    public static function getCompensatTime($user_id)
    {
        $start = "2014-09-01 00:00:00";
        //求出从2015-01-01开始的 你可以补休的天数
        $sql="select sum(total_day) as num from overtime where type='holiday' and status='success' and start_time >=:start and user_id = :user_id;";
        $overtimeCount = Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user_id, ':start'=>$start));
        //求出从2015-01-01开始 你已经补休的天数
        $sql="select sum(total_days) as num from `leave` where status='success' and user_id = :user_id  and start_time >= :start and type ='compensatory';";
        $leaveCount = Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user_id, ':start'=>$start));
        $count = $overtimeCount - $leaveCount;
        return ($count <= 0) ? 0 : $count;
    }

    public static function noticeHeadApprove($id, $user_id="")
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $overtime = Overtime::model()->findByPk($id);
            //如果通知的玩家ID为空，则默认为该申请人的主管
            if(empty($user_id))
                $user_id = $overtime->head_id;

            $head = Users::model()->findByPk($user_id);
            //发送消息
            $url = "/oa/overtimeDetail/id/{$id}";
            $title = "{$overtime->user->cn_name}提交了".date('Y-m-d',strtotime($overtime->start_time))."的加班申请，请尽快审批";
            $content = "{$overtime->user->cn_name}提交了从".date('Y-m-d H:i',strtotime($overtime->start_time))."到".date('Y-m-d H:i',strtotime($overtime->end_time))."的加班申请，请尽快审批";
            $host = Yii::app()->request->hostInfo;
            $message = "<b>姓名:</b> {$overtime->user->cn_name}<br><b>时间:</b>".date('Y-m-d H:i',strtotime($overtime->start_time))."到".date('Y-m-d H:i',strtotime($overtime->end_time))."<br><b>详情:</b> 请<a href='{$host}{$url}'>登录</a>查看";
            self::sendNotice($head, $url, $title, $content , $message);
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }

    /**
     *通知消息 非事务
     */
    public static function sendNotice($user, $url, $title, $content , $message)
    {
        $time = date('Y-m-d H:i:s');
        //通知用户
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'overtime' , 'create_time'=>$time));
        //如果被通知的用户不在线 就发邮件通知
        if($user->online == 'off')
        {
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>$time,'update_time'=>$time);
            Mail::createMail($arr);
        }
        return true;
    }

    /**
     *添加加班记录 并且发送给相关人员备案
     */
    public static function createOvertime($content,$overtime_date, $overtime_time, $user)
    {
            $transaction=self::model()->dbConnection->beginTransaction();
            try
            {
                $id = Overtime::processOvertime(new Overtime() , 
                    array('content'=>$content, 'user_id'=>$user->user_id,'create_time'=>date('Y-m-d H:i:s'),
                    'start_time'=>"{$overtime_date} 18:30:00", 'total_day'=>0, 
                    'type'=>'normal','end_time'=>"{$overtime_date} {$overtime_time}:00", 
                    'status'=>'success','head_id'=>0));
                $url = "/oa/overtimeDetail/id/{$id}";
                $title = "{$user->cn_name}提交了".date('Y-m-d',strtotime("{$overtime_date}{$overtime_time}"))."的加班签到";
                $content = "{$user->cn_name}提交了从".date('Y-m-d H:i',strtotime("{$overtime_date}{$overtime_time}"))."的加班签到,请备案";
                $host = Yii::app()->request->hostInfo;
                $message = "<b>姓名:</b> {$user->cn_name}<br><b>时间:</b>".date('Y-m-d H:i',strtotime("{$overtime_date}{$overtime_time}"))."<br><b>详情:</b> 请<a href='{$host}{$url}'>登录</a>查看";
                $commissioner = Users::getCcommissioner();
                self::sendNotice($commissioner, $url, $title, $content , $message);
                $transaction ->commit();
                return true;
            }
            catch(Exception $e)
            {
                $transaction ->rollBack();
            }
            return false;
    }


}
