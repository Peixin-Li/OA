<?php

/**
 * This is the model class for table "leave".
 *
 * The followings are the available columns in table 'leave':
 * @property integer $leave_id
 * @property integer $user_id
 * @property string $content 
 * @property string $delay
 * @property string $type
 * @property string $start_time
 * @property string $end_time
 * @property string $head_reply
 * @property string $admin_reply
 * @property string $ceo_reply
 */
class Leave extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Leave the static model class
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
		return 'leave';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id,  content, start_time, end_time, next, create_time', 'required'),
			array('user_id, ', 'numerical', 'integerOnly'=>true),
			array('content, delay', 'length', 'max'=>256),
			array('type', 'length', 'max'=>12),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('leave_id, user_id, content, delay , type, start_time, end_time, next, status', 'safe', 'on'=>'search'),
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
            'logs' =>array(self::HAS_MANY, 'LeaveLog', 'leave_id','condition'=>"user_id != '".Yii::app()->session['user_id']."'"),
            'allLogs'=>array(self::HAS_MANY, 'LeaveLog', 'leave_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'leave_id' => 'Leave',
			'user_id' => 'User',
			'content' => 'Content',
			'type' => 'Type',
            'delay'=>'Delay',
			'start_time' => 'Start Time',
			'end_time' => 'End Time',
			'next' => 'Next',
			'status' => 'Status',
            'create_time'=>'Create Time',
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

		$criteria->compare('leave_id',$this->leave_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('content',$this->content,true);
        $criteria->compare('delay',$this->delay,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('start_time',$this->start_time,true);
		$criteria->compare('end_time',$this->end_time,true);
		$criteria->compare('next',$this->head_reply,true);
		$criteria->compare('status',$this->admin_reply,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加请假单
     *@param $title 为消息的标题
     */
    public static function addLeave($data)
    {
       try
       {
            $data['total_days'] = Leave::countDays($data);
            $procedure_list = Procedure::getProcedure('leave', $data['total_days'] ,$data['user_id']);

            //如果获取到的流程为空，则默认添加部门主管
            if(empty($procedure_list))
                $procedure_list[] = Users::model()->findByPk($data['user_id'])->leadId;
            $data['procedure_list'] = CJSON::encode($procedure_list);
            $data['next'] = $procedure_list[0];
            $model = new Leave();
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *处理请假
     *@param object $model
     *@param array $data
     *@return boolean
     */
    public static function processLeave($model , $data)
    {
       try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }

            $model->save();
            Helper::processSaveError($model);
            return $model->leave_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *求请假天数
     */
    public function getCountDays()
    {
        return Holiday::countDays($this->start_time,$this->end_time);
        #return abs(floor((strtotime($this->end_time) - strtotime($this->start_time))/(86400)));
    }

    /**
     *求出中文类型
     */
    public function getCntype()
    {
        $types = array('casual'=>'事假','sick'=>'病假','funeral'=>'丧假','marriage'=>'婚假',
                       'maternity'=>'产假','annual'=>'年假','compensatory'=>'补假', 'others'=>'其他假');
        return empty($types[$this->type]) ? '事假' : ($types[$this->type]);
    }


    /**
     *成功处理的事务
     */
    public static function successTransaction($leave, $user)
    { 
            $transaction=self::model()->dbConnection->beginTransaction();
            try
            {
                self::successLeave($leave, $user);
                //如果是年假 就要把目前的可以休的年假总数减少
                if($leave->type == 'annual')
                {
                    $annualLeave = AnnualLeave::model()->find("user_id=:user_id",array(':user_id'=>$leave->user_id));
                    $annualLeave->total -= $leave->total_days;
                    $annualLeave->save();
                }
                $transaction ->commit();
                return true;
            }
            catch(Exception $e)
            {
                $transaction ->rollBack();
            }
            return false;
    }


    /**
     *请假成功
     *
     */
    public static function successLeave($leave, $user) 
    {
        Leave::processLeave($leave , array('status'=>'success','next'=>'0'));
        $log_id = LeaveLog::addLog(array('leave_id' => $leave->leave_id,'user_id' => $user->user_id , 'action' => 'agree' , 'create_time'=> date("Y-m-d H:i:s")));
                
        $user_self = Users::model()->findByPk($leave->user_id);//发送通知申请人
        Leave::leaveNotice($leave,$user_self,'self');        
        Leave::leaveMail($leave, $user_self,'self');
        if($user->user_id != Users::getCeo()->user_id)//如果不是ceo审批的请假单，而且请假单又通过则通知ceo
        {
            $lead_id = empty($leave->user->leadId) ? 0 : $leave->user->leadId;
            $user_ceo = Users::getCeo();
            if($lead_id != $user_ceo->user_id)
            {
                Leave::leaveNotice($leave,$user_ceo,'heads_ceo');
                Leave::leaveMail($leave, $user_ceo,'heads_ceo');
            }
        }
        //如果请假小于3天并且非人事部的人请假需要通知人事总监备案
        if($leave->countDays < 3 && $leave->user->department_id != Department::adminDepartment()->department_id)
        {
            $admin = Users::getAdminId();
            Leave::leaveNotice($leave,$admin,'heads_ceo');
            Leave::leaveMail($leave, $admin,'heads_ceo');
        }
        Leave::noticeCcommissioner($leave);//发给给行政助理备案
        Leave::noticeHeads($leave); //通知审批的人,直属上司
        Leave::leaveForm($leave);//产生报表汇总

    }

    /**
     *发给给行政助理备案
     */
    public static function noticeCcommissioner($leave)
    {
        $user = Users::getCcommissioner();
        Leave::leaveNotice($leave,$user,'hr');
        Leave::leaveMail($leave,$user,'hr');//通知人事备案
        $hr = Users::getHr();
        Leave::leaveNotice($leave,$hr,'hr');
        Leave::leaveMail($leave,$hr,'hr');//通知人事备案
    }

    /**
     *通知审批的人
     */
    public static function noticeHeads($leave , $status='已通过')
    {
        $array = array('success'=>'已通过','reject'=>'未通过');
        $status = $array[$leave->status];
        foreach($leave->logs as $row)
        {     
            Leave::leaveNotice($leave,$row->user,'heads_ceo');
            Leave::leaveMail($leave,$row->user,'heads_ceo');       
        }
    }

    /**
     *发送通知的事务
     */
    public static function noticeHeadsTransaction($leave , $status='已通过')
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
          Leave::noticeHeads($leave , $status);
          $transaction ->commit();
          return true;
        }
        catch(Exception $e)
        {
          $transaction ->rollBack();
        }
        return false;
    }

    /**
     *传递给下一位leader审核,直属主管
     *@param string $leave 这个是leave的对象
     *@param string $next  就是下以为领导的ID
     *@param string $user_id 就是当前登录用户的ID
     */
    public static function passNext($leave,$next,$user_id)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            Leave::processLeave($leave , array('next'=>$next)); 
            LeaveLog::addLog(array('leave_id' => $leave->leave_id,'user_id' => $user_id , 'action' => 'agree' , 'create_time'=> date("Y-m-d H:i:s")));
            
            $user = Users::model()->findByPk($next);//通知下一位审批人
            Leave::leaveNotice($leave,$user,'heads');
            Leave::leaveMail($leave,$user,'heads');        
            $transaction ->commit();
            return true;
        }
        catch(Exception $e)
        {
          $transaction ->rollBack();
        }
        return false;           
    }
    
    /**
     *请假的报表 事务
     **/
    public static function leaveFormTransaction($leave)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            self::leaveForm($leave);
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
     *请假的报表
     **/
    public static function leaveForm($leave)
    {
            $start_month = date('Y-m',strtotime($leave->start_time));    
            $end_month = date('Y-m',strtotime($leave->end_time));    
            if($start_month == $end_month)
            {
                $count = Holiday::countDays($leave->start_time, $leave->end_time);
                self::addLeaveForm(date('Y-m-01',strtotime($leave->start_time)), $count, $leave);
            }
            else
            {
                for($i = $start_month; $i <= $end_month; $i= date('Y-m', strtotime('+1months',strtotime($i.'-01'))))
                {
                    if($i == $start_month)
                    {
                        //开始第一个月
                        $end = date('Y-m-t 18:30',strtotime($i));
                        $count = Holiday::countDays($leave->start_time, $end);
                        self::addLeaveForm(date('Y-m-01',strtotime($leave->start_time)), $count, $leave);
                    }
                    else if($i == $end_month)
                    {
                        //结束最后一个月
                        $start = date('Y-m-01 09:30',strtotime($i));
                        $count = Holiday::countDays($start, $leave->end_time);
                        self::addLeaveForm(date('Y-m-01',strtotime($start)), $count, $leave);
                    }
                    else
                    {
                        //中间月份
                        $start = date('Y-m-01 09:30',strtotime($i));
                        $end = date('Y-m-t 18:30',strtotime($i));
                        $count = Holiday::countDays($start, $end);
                        self::addLeaveForm(date('Y-m-01',strtotime($start)), $count, $leave);
                    }
                }
            }
    }

    /**
     *添加请假报表
     *@param string $month // XXXX-XX-XX 
     *@param string $count //天数
     *@param object $leave //请假对象
     */
    public static function addLeaveForm($month,$count, $leave)
    {
        if(!empty($count))
        {
            if($leave_report = LeaveMonthReport::model()->find("month=:month and user_id=:user_id",array(":month"=>$month, ':user_id'=>$leave->user_id)))
            {
                $type = $leave->type;
                $leave_report->$type = $leave_report->$type + $count;
                $leave_report->save();
                Helper::processSaveError($leave_report);
            }
            else
            {
                $leave_report = new LeaveMonthReport();
                $types = array('casual','sick','funeral','marriage','maternity','annual','compensatory','others');
                $leave_report->user_id = $leave->user_id;
                $leave_report->month = $month;
                foreach($types as $type)
                {
                    $leave_report->$type = 0;
                }
                $_type = $leave->type ;
                $leave_report->$_type = $leave_report->$_type + $count;
                $leave_report->content  = '';
                $leave_report->create_time = date('Y-m-d H:i:s');
                $leave_report->save();
                Helper::processSaveError($leave_report);
            }
        }
    }

    /**
     *计算请假有多少天
     *@return float $total_days 总共的天数
     **/
    public static function countDays($data)//
    {
        $start = $data['start_time'];
        $end = $data['end_time'];
        return Holiday::countDays($start,$end);
    }

    /**
     *计算年假的天数
     *@param object $user
     *@reutn int 
     **/
    public static function countYearLeave($user)
    {
        if((strtotime(date('Y-m-d')) - strtotime($user->entry_day))/86400 >= 365)
        {
            return 7;
        }
        /*elseif((strtotime(date('Y-m-d')) - strtotime($user->entry_day))/86400 >= 180)
        {
            return 3.5;
        }*/
        return 0;

    }

    /**
     *计算当前可以请几天年假
     */
    public static function calcYearLeave($user)
    {
        //计算本年度所请年假的天数
        $sql = "select sum(total_days) from `leave` where user_id = :user_id and type = 'annual' and status = 'success' ;";
        $count =  Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user->user_id));#->queryScalar();
        //计算本年度有几天年假
        $total = self::countYearLeave($user);
        //返回当前可以请的年假
        return (float)$total - (empty($count)?0:(float)$count);
    }

    /**
    *请假流程
    *@param  array $procedure 
    **/
    public static function procedure($leave)
    {   
        $procedure = array();
        if(empty($leave))             //如果传入的参数有误，则返回空
            return $procedure;

        if( $procedure_list = CJSON::decode($leave['procedure_list'], true) ) {
            foreach ($procedure_list as $row) {
                $user_info = Users::model()->findByPk($row);
                $procedure[] = array(
                    'name' => $user_info->cn_name,
                    'department' => $user_info->department->name,
                );
            }
        }
        //兼容并修正历史已经完成的请假单
        else if( ($leave->status == 'success') && ($logs = $leave->allLogs) ) {
            $procedure_list = array();
            foreach ($logs as $row) {
                $procedure[] = array(
                    'name' => $row->user->cn_name,
                    'department' => $row->user->department->name,
                );
                $procedure_list[] = $row->user->user_id;
            }
            $leave->procedure_list = CJSON::encode($procedure_list);
            @$leave->save();
        }
        //兼容未完成的请假单
        else {
            $leader = Users::model()->findByPk($leave->user->leadId);
            $procedure[] = array(
                'name' => $leader->cn_name,
                'department' => $leader->department->name,
            );
        }
        return $procedure;
    }

    /**
    *日期友好化显示
    *@return date $date 
    **/
    public static function dateFriendly($date)
    {
        if(date('Y-m-d',strtotime($date)) == date('Y-m-d') )
            {
                $time = floor((strtotime(date('Y-m-d H:i:s'))-strtotime($date))/60);
                if($time < 1)
                {
                    return $date = "刚刚"; 
                }
                elseif($time < 60)
                {
                    return $date = "{$time}分钟前";
                }
                else
                {
                    return $date = floor($time/60).'小时前';
                }
            }
            else
            {
                if( date('Y-m-d',strtotime($date)) == date('Y-m-d', strtotime('-1day', strtotime(date('Y-m-d')))) )
                {
                    return $date = '昨天&nbsp'.date('H:i');
                }
                elseif( date('Y-m-d',strtotime($date)) == date('Y-m-d', strtotime('-2day', strtotime(date('Y-m-d')))) )
                {
                    return $date = '前天&nbsp'.date('H:i');
                }
                else
                {
                    return $date = date('Y-m-d',strtotime($date));
                }   
            }
    }
    

    /**
     *求中文请假类型
     **/
    public static function cnLeaveType($type)
    {
        $types = array('casual'=>'事假','sick'=>'病假','funeral'=>'丧假','marriage'=>'婚假','maternity'=>'产假','annual'=>'年假','compensatory'=>'补假','others'=>'其他假');
        return $types["$type"];
    }

    /**
    *发送请假的邮件
    *$leave_type 请假的消息类型 ，本人:self，通知下一位审批heads ,人事备案:hr ，通知审批的人和ceo:heads_ceo
    **/
    public static function leaveMail($leave,$user,$leave_type)
    {
        if($user->online == 'off')
        {
            $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
            $start_time = date('Y-m-d H:i',strtotime($leave->start_time));
            $end_time = date('Y-m-d H:i',strtotime($leave->end_time));
            $type = Leave::cnLeaveType($leave->type);
            $array = array('success'=>'已通过','reject'=>'未通过','wait'=>'已提交');
            $cn_status = $array[$leave->status];
            $email = $user->email;
            $url =($leave_type == 'heads') ? "<a href='{$host}/oa/msg/leave/{$leave->leave_id}'>" : "<a href='{$host}/user/msg/leave/{$leave->leave_id}'>";
            
            switch ($leave_type)
            {
                case 'self':
                  $subject = "你申请的 {$start_time}到{$end_time} 的 {$type} 请假单{$cn_status}";
                  break;  
                case 'heads':
                  $subject = "{$leave->user->cn_name}提交 {$type} 请假单，累计{$leave->total_days}天,请尽快审批";
                  $url = "<a href='{$host}/oa/processLeave/id/{$leave->leave_id}'>";
                  break;
                case 'hr':
                  $subject = "{$leave->user->cn_name}申请的 {$start_time}到{$end_time} 的 {$type} 请假单已通过，请备案";
                  break; 
                case 'heads_ceo':
                  $subject = "{$leave->user->cn_name}申请的 {$start_time}到{$end_time} 的 {$type} 请假单{$cn_status}";
                  break;  
                default:
                  $subject = "{$leave->content}";
            }

            $message = "<b>姓名:</b> {$leave->user->cn_name}<br><b>时间:</b> {$start_time}到{$end_time}<br><b>天数:</b>{$leave->total_days}天<br><b>请假类型:</b> {$type}<br><b>请假原因:</b>{$leave->content}<br><b>详情:</b> 请{$url}登录</a>查看";
            $arr = array('user_id'=>Yii::app()->session['user_id'], 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$email, 
                            'subject'=>$subject, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
            $mail_id = Mail::createMail($arr);
            return $mail_id;
        }
        return false;
    }

    /**
    *发送请假的消息
    *@param 
    **/
    public static function leaveNotice($leave,$user,$leave_type)
    {
        $start_time = date('Y-m-d H:i',strtotime($leave->start_time));
        $end_time = date('Y-m-d H:i',strtotime($leave->end_time));
        $type = Leave::cnLeaveType($leave->type);
        $array = array('success'=>'已通过','reject'=>'未通过','wait'=>'已提交');
        $cn_status = $array[$leave->status];
        $url = ($leave_type != 'self')? "/oa/msg/leave/{$leave->leave_id}" : "/user/msg/leave/{$leave->leave_id}";

        switch ($leave_type)
        {
            case 'self':
              $title = "你申请的{$type} 请假单 {$cn_status}";
              $content = "你申请的 {$start_time}到{$end_time} 的 {$type} 请假单{$cn_status}，累计{$leave->total_days}天";
              break;  
            case 'heads':
              $title = "{$leave->user->cn_name}提交 {$type} 请假单,请尽快审批";
              $content = "{$leave->user->cn_name}的 {$type} 请假单，时间:{$start_time}到{$end_time},累计{$leave->total_days}天";
              $url = "/oa/processLeave/id/{$leave->leave_id}";
              break;
            case 'hr':
              $title = "{$leave->user->cn_name}申请的 {$type} 请假单已通过，请备案";
              $content = "{$leave->user->cn_name}申请的 {$type} 请假单已通过，时间: {$start_time}到{$end_time} ,累计{$leave->total_days}天，请备案";
              break; 
            case 'heads_ceo':
              $title = "{$leave->user->cn_name}申请的 {$type} 请假单{$cn_status}";
              $content = "{$leave->user->cn_name}申请的 {$start_time}到{$end_time} 的 {$type} 请假单{$cn_status}，累计{$leave->total_days}天";
              break;  
            default:
              $title = $content = "{$leave->content}";
        }
        
        return  Notice::addNotice(array('user_id'=>$user->user_id, 'content'=>$content, 'url'=>$url, 'status'=>'wait', 'type'=>'leave',
                                                'title'=>$title, 'create_time'=>date('Y-m-d H:i:s')));
    }


    /**
     *获取一个部门的人在当前时段的请假情况
     *@param string $start
     *@param string $end
     *@param string $department_id
     */
    public static function getDepartmentLeaveInfo($start, $end, $department_id)
    {
        $_start = date('Y-m-d 00:00:00', strtotime($start));
        $_end   = date('Y-m-d 23:59:59', strtotime($end));
        $sql = "SELECT `leave`.*,cn_name,title FROM `leave` join users on(users.user_id = leave.user_id) where users.department_id = '{$department_id}' and leave.status='success' and ";
        $sql .= "((start_time <= '{$_start}' and end_time >= '{$_end}') ";
        $sql .= "or (start_time >= '{$_start}' and end_time <= '{$_end}') ";
        $sql .= "or (start_time <= '{$_start}' and end_time >= '{$_start}') ";
        $sql .= "or (start_time <= '{$_end}' and end_time >= '{$_end}' ))";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

}
