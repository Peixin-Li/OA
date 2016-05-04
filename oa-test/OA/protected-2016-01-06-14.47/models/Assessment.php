<?php

/**
 * This is the model class for table "assessment".
 *
 * The followings are the available columns in table 'assessment':
 * @property integer $id
 * @property integer $resume_id
 * @property integer $grooming
 * @property integer $skill
 * @property integer $ability
 * @property integer $attitude
 * @property string $entry_day
 * @property integer $periods
 * @property string $probation_salary
 * @property string $official_salary
 * @property string $status
 * @property integer $next
 * @property string $reason
 * @property string $update_time
 * @property string $create_time
 */
class Assessment extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Assessment the static model class
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
		return 'assessment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('resume_id, experience, skill, execution, attitude, communicate,learning, entry_day, periods, probation_salary, official_salary, next, update_time, create_time', 'required'),
			array('resume_id, experience, skill, execution, attitude,communicate,learning,  periods, next', 'numerical', 'integerOnly'=>true),
			array('probation_salary, official_salary, status', 'length', 'max'=>7),
			array('reason', 'length', 'max'=>500),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, resume_id, experience, skill, execution, attitude,communicate,learning,  entry_day, periods, probation_salary, official_salary, status, next, reason, update_time, create_time', 'safe', 'on'=>'search'),
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
            'resume'=>array(self::BELONGS_TO, 'Resume', 'resume_id'),
            'logs' =>array(self::HAS_MANY, 'AssessmentLog', 'assessment_id','condition'=>"user_id != '".Yii::app()->session['user_id']."'"),
            'allLogs'=>array(self::HAS_MANY, 'AssessmentLog', 'assessment_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'resume_id' => 'Resume',
			'experience' => 'Experience',
			'skill' => 'Skill',
			'execution' => 'Execution',
			'attitude' => 'Attitude',
			'communicate' => 'Communicate',
			'learning' => 'Learning',
			'entry_day' => 'Entry Day',
			'periods' => 'Periods',
			'probation_salary' => 'Probation Salary',
			'official_salary' => 'Official Salary',
			'status' => 'Status',
			'next' => 'Next',
			'reason' => 'Reason',
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
		$criteria->compare('resume_id',$this->resume_id);
		$criteria->compare('experience',$this->experience);
		$criteria->compare('skill',$this->skill);
		$criteria->compare('execution',$this->execution);
		$criteria->compare('attitude',$this->attitude);
		$criteria->compare('communicate',$this->communicate);
		$criteria->compare('learning',$this->learning);
		$criteria->compare('entry_day',$this->entry_day,true);
		$criteria->compare('periods',$this->periods);
		$criteria->compare('probation_salary',$this->probation_salary,true);
		$criteria->compare('official_salary',$this->official_salary,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('next',$this->next);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    /**
     *处理评估表
     *@param object $model
     *@param array $data
     *@return boolean
     */
    public static function processAssessment($model , $data)
    {
       try
       {
            if(empty($model)) return false;
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
       }
       return false;
    }
    
    /**
     *通知审批人
     */
    public static function noticeApproval($resume)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            if($resume->interviewer == $resume->apply->user_id)
            {
                $url= "/oa/interviewEvaluateDetail/id/{$resume->id}";
            }
            else
            {
                $url= "/user/interviewEvaluateDetail/id/{$resume->id}";
            }
            $user = Users::model()->findByPk($resume->interviewer);
            $title = "请尽快填写{$resume->name}的面试评估表";
            $content = "请尽快填写{$resume->name}应聘{$resume->apply->title}的面试评估表";
            //通知审批人
            if(!Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s'))))
            {
                throw new Exception("Error", -1);
            }
                
            //如果人事总监不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                if(!Mail::createMail($arr))
                {
                    throw new Exception("Error", -1);
                }
            }
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
     *同意审批结果并且把发送消息给下一位审批人
     *@param object $model 这个是评估对象
     *@param object $user  下一位审批人的对象
     *@param string $entry_day 入职时间
     *@param array  $data  数据
     */
    public static function passNext($model, $user, $entry_day , $data)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //第一步添加日志
            AssessmentLog::addLog( array_merge(array('assessment_id'=>$model->id,'user_id'=>$model->next,'action'=>'agree','create_time'=>date('Y-m-d H:i:s')),$data));
            //第二步修改状态
            $assessment_data = array('next'=>$user->user_id,'update_time'=>date('Y-m-d H:i:s'));
            $assessment_data['entry_day'] = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry_day)) ? $entry_day : $model->entry_day;
            self::processAssessment($model , $assessment_data);
            //第三步 通知下一位
            self::noticeNext($model, $user);
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
     *发送消息
     */
    public static function noticeDepartmentLeader($model, $user)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //如果部门负责人不是面试官就发送消息 ， 否则就不发送
            if($user != $model->resume->interviewer)
            {
                self::noticeNext($model, $user);
            }
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
     *通知下一位
     */
    public static function noticeNext($model, $user)
    {
        $resume = $model->resume;
        $url= "/oa/interviewEvaluateDetail/id/{$resume->id}";
        $title = "请尽快填写{$resume->name}的面试评估表";
        $content = "请尽快填写{$resume->name}应聘{$resume->apply->title}的面试评估表";
        if(!is_object($user))
        {
            $user = Users::model()->findByPk($user);
        }
        //通知审批人
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
            
        //如果人事总监不在线
        if($user->online == 'off')
        {
            $url = Yii::app()->request->hostInfo.$url;
            $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
            Mail::createMail($arr);
        }
    }
    /**
     *完成审批
     *@param object $model 模型
     *@param array  $data 数据
     *@return boolean
     */
    public static function finishAssessment($model,$data)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //第一步添加日志
            AssessmentLog::addLog( array_merge(array('assessment_id'=>$model->id,'user_id'=>$model->next,'action'=>'agree','create_time'=>date('Y-m-d H:i:s')),$data));
            //第二步修改状态
            $assessment_data = array('next'=>0,'status'=>'success','update_time'=>date('Y-m-d H:i:s'));
            unset($data['opinion']);
            self::processAssessment($model , array_merge($data,$assessment_data));
            //第三步 修改简历的状态
            Resume::processResume($model->resume,array('status'=>'entry'));
            //第四步 就是判断职位审批的状态
            $count = Resume::model()->count("apply_id=:id and status=:status",array(":id"=>$model->resume->apply_id, ':status'=>'entry'));
            //如果人数够了就把职位设置成入职
            if($count == $model->resume->apply->number)
            {
                RecruitApply::addRecruitApply($model->resume->apply , array('status'=>'entry'));
            }
            //第五步通知所有审批人除自己外
            $logs = $model->logs;
            foreach($logs as $log)
            {
                Assessment::noticeApprove($model, $log->user,'已通过');
            }
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
     *决绝评估审批表
     */
    public static function rejectAssessment($model, $user, $opinion)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //第一步添加日志表
            $log_data = array('assessment_id'=>$model->id, 'user_id'=>$user->user_id, 'periods'=>'0','probation_salary'=>'0','official_salary'=>'0','opinion'=>$opinion,'action'=>'reject','create_time'=>date('Y-m-d H:i:s')); 
            AssessmentLog::addLog($log_data);
            //第二不修改本身
            $model_data = array('next' => '0' , 'status' => 'reject', 'reason' => $opinion);
            self::processAssessment($model,$model_data);
            //第三步
            Resume::processResume($model->resume,array('status'=>'reject'));
            //第四步通知所有审批人除自己外
            $logs = $model->logs;
            foreach($logs as $log)
            {
                Assessment::noticeApprove($model, $log->user,'未通过');
            }
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }

    public static function noticeApprove($model, $user,$status='已通过')
    {
        $resume = $model->resume;
        $url= "/oa/interviewEvaluateDetail/id/{$resume->id}";
        $title = "{$resume->name}的面试评估表，{$status}";
        $content = "{$resume->name}应聘{$resume->apply->title}的面试评估表, {$status}";
        //通知审批人
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
            
        //如果人事总监不在线
        if($user->online == 'off')
        {
            $url = Yii::app()->request->hostInfo.$url;
            $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>状态:</b>{$status}<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
            Mail::createMail($arr);
        }
    }

    /**
     * 进度条
     */
    public static function procedure($assessment)
    {
        $procedure = array();
        //判断HR与面试官的标记
        $hr_tag = false;
        $admin_tag = false;
        $hr = Users::getHr();
        //已经审批通过
        if($assessment->status == 'success')
        {
            $logs = $assessment->allLogs;
            foreach($logs as $log)
            {
                if(empty($hr_tag) && $hr->user_id == $log->user_id)
                {
                    // $procedure[] = array('HR', $log->action);
                    $procedure[] = array('面试官', $log->action);
                    $hr_tag = true;
                }
                else
                {
                    $procedure[] = array($log->user->department->name, $log->action);
                }
            }
            return $procedure;
        }

        //未审批通过
        $leaders = array(); //审批人
        // $leaders[] = $hr->user_id;
        $admin = Users::getAdminId();
        $tag = false;//如果为true就是说明了 面试官和部门负责人不是同一人
        $admin_interviewer = false; //如果人事总监同时是面试官和申请人就为TRUE 
        if($assessment->resume->apply->user_id != $admin->user_id)
        {
            if($assessment->resume->interviewer != $assessment->resume->apply->user_id)
            {
                $leaders[] = $assessment->resume->interviewer;
                $tag = true;
            }
            $leaders[] = $assessment->resume->apply->user_id;
        }
        else
        {
            if($assessment->resume->interviewer != $admin->user_id)
            {
                $leaders[] = $assessment->resume->interviewer;
                $tag = true;
            }
            else
            {
                $admin_interviewer = true; 
            }
        }
        $leaders[] = $hr->user_id;
        // $leaders[] = $admin->user_id;
        $ceo = Users::getCeo();
        $leaders[] = $ceo->user_id;
        $interviewer_status = 'agree';
        $admin_tag = false; //如果是人事总监和CEO为面试官的话就设置为true
        //处理成
        foreach($leaders as $leader)
        {
            if($_log =  AssessmentLog::model()->find('user_id=:user_id and assessment_id=:assessment_id', array(':user_id'=>$leader, ':assessment_id'=>$assessment->id)))
            {
                if(empty($hr_tag) && $leader == $hr->user_id)
                {
                    $procedure[] =  array('HR', $_log->action);
                    $interviewer_status = $_log->action;
                    $hr_tag = true;
                }
                elseif(!empty($hr_tag) && $leader == $hr->user_id && $hr->user_id == $assessment->resume->interviewer)
                {
                    $procedure[] =  array('面试官', $_log->action);
                }
                else
                {
                    if(empty($admin_tag) && in_array($assessment->resume->interviewer, array($ceo->user_id, $admin->user_id)))
                    {
                        if(empty($admin_interviewer))
                        {
                            $procedure[] =  array('面试官', $_log->action);
                            $admin_tag = true;
                        }
                        else
                        {
                            $procedure[] =  array($_log->user->department->name, $_log->action);
                        }
                    }
                    else
                    {
                        $procedure[] =  array($_log->user->department->name, $_log->action);
                    }
                }
            }
            else
            {
                $user = Users::model()->findByPk($leader);
                // 第一次输出hr
                if(empty($hr_tag) && $leader == $hr->user_id)
                {
                    $procedure[] = array('HR', $assessment->status);
                    $hr_tag = true;
                }
                // hr是面试官
                elseif(!empty($hr_tag) && $leader == $hr->user_id && $hr->user_id == $assessment->resume->interviewer)
                {
                    $procedure[] =  array('面试官', $assessment->status);
                }
                // 面试官非人事总监或ceo
                elseif(empty($admin_tag) && !empty($tag) && $leader == $assessment->resume->interviewer)
                {
                    if($interviewer_status == 'agree' && (!empty($assessment->experience) || !empty($assessment->skill)  || !empty($assessment->execution) || !empty($assessment->attitude) || !empty($assessment->communicate) || !empty($assessment->learning) ))
                    {
                        
                        $procedure[] = array('面试官', $interviewer_status); 
                    }
                    else
                    {
                        $procedure[] = array('面试官', 'wait');
                    }
                    $admin_tag = true;
                }
                else
                {
                    // 人事总监或者ceo
                    if(empty($admin_tag) && in_array($assessment->resume->interviewer, array($ceo->user_id, $admin->user_id)))
                    {
                        if(empty($admin_interviewer))
                        {
                            if($interviewer_status == 'agree' && (!empty($assessment->experience) || !empty($assessment->skill)  || !empty($assessment->execution) || !empty($assessment->attitude) || !empty($assessment->communicate) || !empty($assessment->learning) ))
                            {

                                $procedure[] = array('面试官', $interviewer_status); 
                            }
                            else
                            {
                                $procedure[] = array('面试官', 'wait');
                            }
                            $admin_tag = true;
                        }
                        else
                        {
                            $procedure[] = array($user->department->name, $assessment->status);
                        }
                    }
                    else
                    {
                        $procedure[] = array($user->department->name, $assessment->status);
                    }
                }
            }
        }
        return $procedure;
    }

    /**
     *放弃入职的
     */
    public static function giveUp($model)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            self::processAssessment($model , array('status'=>'giveup'));
            Resume::processResume($model->resume , array('status'=>'giveup'));
            RecruitApply::addRecruitApply($model->resume->apply , array('status'=>'success'));
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }


}
