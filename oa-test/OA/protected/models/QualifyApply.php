<?php

/**
 * This is the model class for table "qualify_apply".
 *
 * The followings are the available columns in table 'qualify_apply':
 * @property integer $id
 * @property integer $user_id
 * @property integer $trial_salary
 * @property integer $work_life
 * @property string $evaluation
 * @property string $plan
 * @property string $suggest
 * @property integer $next
 * @property string $status
 * @property string $type
 * @property string $update_time
 * @property string $create_time
 */
class QualifyApply extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return QualifyApply the static model class
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
		return 'qualify_apply';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, trial_salary, work_life,  next, type, update_time, create_time', 'required'),
			array('user_id, trial_salary, work_life, next', 'numerical', 'integerOnly'=>true),
			array('evaluation, plan, suggest', 'length', 'max'=>800),
			array('status', 'length', 'max'=>7),
			array('type', 'length', 'max'=>8),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, trial_salary, work_life, evaluation, plan, suggest, next, status, type, update_time, create_time', 'safe', 'on'=>'search'),
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
            'logs' =>array(self::HAS_MANY, 'QualifyApplyLog', 'apply_id','condition'=>"user_id != '".Yii::app()->session['user_id']."'"),
            'allLogs'=>array(self::HAS_MANY, 'QualifyApplyLog', 'apply_id'),
            'report'=>array(self::HAS_MANY, 'QualifyReport', 'apply_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'trial_salary' => 'Trial Salary',
			'work_life' => 'Work Life',
			'evaluation' => 'Evaluation',
			'plan' => 'Plan',
			'suggest' => 'Suggest',
			'next' => 'Next',
			'status' => 'Status',
			'type' => 'Type',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('trial_salary',$this->trial_salary);
		$criteria->compare('work_life',$this->work_life);
		$criteria->compare('evaluation',$this->evaluation,true);
		$criteria->compare('plan',$this->plan,true);
		$criteria->compare('suggest',$this->suggest,true);
		$criteria->compare('next',$this->next);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加一个转正申请
     *@param object $model
     *@param array $data
     *@return int
     */

    public static function processQualifyApply($model, $data)
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
        }
        return false;
    }

    /**
     *审批转正流程的类型
     *@param object $apply 这个是审批的转正申请对象
     */
    public static function typeQualifyApply($apply)
    {
        $ceo = Users::getCeo();
        $admin = Users::getAdminId();
        $admin_department = Department::adminDepartment();
        $user  =  $apply->user;
        $leader = $user->leadId;
        //1.正常流程的 ->部门负责人 -> sara -> verky ->完成
        if($leader != $ceo->user_id && $user->department->name != $admin_department->name)
        {
            return 1;
        }
        //2.是人事部的同事转正 ->部门负责人 -> verky ->完成
        else if($leader != $ceo->user_id && $user->department->name == $admin_department->name)
        {
            return 2;
        }
        //3.部门负责人转正（非人事总监） ->verky -> sara ->完成
        else if($leader == $ceo->user_id && $user->user_id != $admin->user_id)
        {
            return 3;
        }
        //4.人事总监转正  ->verky  ->完成
        else if($leader == $ceo->user_id && $user->user_id == $admin->user_id)
        {
            return 4;
        }
        return false;
    }

    /**
     * 进度条
     //1.正常流程的 ->部门负责人 -> sara -> verky ->完成
     //2.是人事部的同事转正 ->部门负责人 -> verky ->完成
     //3.部门负责人转正（非人事总监） ->verky -> sara ->完成
     //4.人事总监转正  ->verky  ->完成
     */
    public static function procedure($apply)
    {
        $procedure = array();
        //已经审批通过
        if($apply->status == 'success')
        {
            $logs = $apply->allLogs;
            foreach($logs as $log)
            {
                $procedure[] = array($log->user->department->name, $log->action);
            }
            return $procedure;
        }

        $leaders = CJSON::decode($apply->procedure_list, true);

        foreach($leaders as $leader)
        {
            if($_log =  QualifyApplyLog::model()->find('user_id=:user_id and apply_id=:apply_id', array(':user_id'=>$leader, ':apply_id'=>$apply->id)))
            {
                $procedure[] =  array($_log->user->department->name, $_log->action);
            }
            else
            {
                $user = Users::model()->findByPk($leader);
                $procedure[] = array($user->department->name, $apply->status);
            }
        }
        return $procedure;
    }

    /**
     *审批转正流程
     *@param string $type  
     //1.正常流程的 ->部门负责人 -> sara -> verky ->完成
     //2.是人事部的同事转正 ->部门负责人 -> verky ->完成
     //3.部门负责人转正（非人事总监） ->verky -> sara ->完成
     //4.人事总监转正  ->verky  ->完成
     *@param object $apply 这个是审批的转正申请对象
     *@param object $user  当前登录用户对象
     *@param string $salary_type  enum('contract','modify') 薪资调整类型 约定 调整
     *@param string $qualify_date 转正日期
     *@param string $qualify_salary 转正薪资
     *@return boolean
     */
    public static function agreeQualifyApply($type , $apply , $user, $salary_type, $qualify_date, $qualify_salary)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $procedure_list = CJSON::decode($apply->procedure_list, true);
            if($user->user_id == end($procedure_list) ) {                      //若为最后一个审批者，则审批流程结束
                self::processQualifyApply($apply, array('next'=>'0','status'=>'success', 'qualify_date'=>$qualify_date, 'qualify_salary'=>$qualify_salary));
                Users::updateUser($apply->user, array('job_status'=>'formal_employee', 'regularized_date'=>$qualify_date)); //并且修改USER的状态 
                self::noticeUser($apply,$apply->user, "已通过", 'self');
                self::noticeAll($apply, '已通过');
            }
            else {
                foreach ($procedure_list as $key => $value) {
                    if($apply->next == $value)
                        break;
                }
                self::processQualifyApply($apply, array('next'=>$procedure_list[$key + 1], 'qualify_date'=>$qualify_date, 'qualify_salary'=>$qualify_salary));
                self::noticeUser($apply, Users::model()->findByPk( $procedure_list[$key + 1] ), "已提交,请尽快审批",'other');
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
     *通知用户
     */
    public static function noticeUserById($apply_id , $user, $status="已通过", $type = 'self')
    {
        $apply = QualifyApply::model()->findByPk($apply_id);
        //如果传进来的是user_id就搜索用户
        if(!is_object($user))
        {
            $user = Users::model()->findByPk($user);
        }
        return self:: noticeUser($apply,$user, $status, $type);
    }
    /**
     *通知申请人
     *@param object $apply 处理申请对象的object
     *@param stirng $status 
     *@return boolean
     */
    public static function noticeUser($apply,$user, $status="已通过", $type = 'self')
    {
        $name = ($type=='self')? '你': $apply->user->cn_name;
        $url = ($type == 'self') ? "/user/positiveApplyDetail/id/{$apply->id}" : "/oa/positiveApplyDetail/id/{$apply->id}";
        $content = "{$name}的转正申请{$status}，详情请点击查看";
        $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
        $message = "<a href='{$host}{$url}'>{$name}的转正申请{$status}，详情请点击查看</a>";
        return self::sendNotice($user, $url, "{$name}的转正申请{$status}", $content , $message);
    }

    /**
     *通知所有人和自己 事务
     */
    public static function noticeAllTransaction($apply, $status='已通过')
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            self::noticeUser($apply,$apply->user, $status, 'self');
            self::noticeAll($apply, $status);
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
     *通知所有审批人
     */
    public static function noticeAll($apply, $status='已通过')
    {
        $logs = $apply->logs;
        foreach($logs as $log)
        {
            self::noticeUser($apply,$log->user, $status,  'hr');
        }
        self::noticeUser($apply,Users::getHr(), "{$status}请备案",  'hr');
    }

    /**
     *通知消息 事务
     */
    public static function sendNoticeTransaction($user, $url, $title, $content , $message)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            self::sendNotice($user, $url, $title, $content , $message);
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
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'qualify' , 'create_time'=>$time));
            
        //如果被通知的用户不在线 就发邮件通知
        if($user->online == 'off')
        {
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>$time,'update_time'=>$time);
            Mail::createMail($arr);
        }
        return true;
    }

}
