<?php

/**
 * This is the model class for table "quit_apply".
 *
 * The followings are the available columns in table 'quit_apply':
 * @property integer $id
 * @property integer $submit_id
 * @property integer $user_id
 * @property string $quit_reason
 * @property integer $next
 * @property string $status
 * @property string $reason
 * @property string $update_time
 * @property string $create_time
 */
class QuitApply extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return QuitApply the static model class
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
		return 'quit_apply';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('submit_id, user_id, next, create_time', 'required'),
			array('submit_id, user_id, next', 'numerical', 'integerOnly'=>true),
			array('quit_reason', 'length', 'max'=>255),
			array('status', 'length', 'max'=>7),
			array('reason', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, submit_id, user_id, quit_reason, next, status, reason, update_time, create_time', 'safe', 'on'=>'search'),
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
            'logs' =>array(self::HAS_MANY, 'QuitApplyLog', 'apply_id','condition'=>"user_id != '".Yii::app()->session['user_id']."'"),
            'allLogs'=>array(self::HAS_MANY, 'QuitApplyLog', 'apply_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'submit_id' => 'Submit',
			'user_id' => 'User',
			'quit_reason' => 'Quit Reason',
			'next' => 'Next',
			'status' => 'Status',
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
		$criteria->compare('submit_id',$this->submit_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('quit_reason',$this->quit_reason,true);
		$criteria->compare('next',$this->next);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加一个离职审批
     */
    public static function processQuitApply($model, $data)
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
     *发送通知
     */
    public static function noticeSelf($id,$user,$status="已经提交")
    {
        $url = "/user/quitDetail/id/{$id}";
        $title = "你的离职申请单{$status}";
        $content = "你的离职申请单{$status}，请点击查看详情";
        $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
        $message = "<a href='{$host}{$url}'>你的离职申请单{$status}，请点击查看详情</a>";
        return self::sendNotice($user, $url, $title, $content , $message);
    }

    /**
     *审批离职流程的类型
     *@param object $apply 这个是审批的转正申请对象
     */
    public static function typeQuitApply($apply)
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
            $procedure[] = array($apply->user->cn_name, 'agree');
            foreach($logs as $log)
            {
                if($log->action == 'create')
                {
                    continue;
                }
                $procedure[] = array($log->user->department->name, $log->action);
            }
            return $procedure;
        }

        $leaders = CJSON::decode($apply->procedure_list, true); //审批人
        $procedure[] = array($apply->user->cn_name, 'agree');
        foreach($leaders as $leader)
        {
            if($_log =  QuitApplyLog::model()->find('user_id=:user_id and apply_id=:apply_id', array(':user_id'=>$leader, ':apply_id'=>$apply->id)))
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
     *同意离职的方法
     *@param object $apply 申请对象
     *@param object $user  用户对象
     *@param string $date
     *@return boolean
     */
    public static function agreeQuitApply($apply , $user, $date)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $procedure_list = CJSON::decode($apply->procedure_list, true);
            if($user->user_id == end($procedure_list) ) {
                self::finishAgreeApply($apply,$user, $date);
            }
            else {
                foreach ($procedure_list as $key => $value) {
                    if($apply->next == $value)
                        break;
                }
                $next_user = Users::model()->findByPk($procedure_list[$key + 1]);
                self::passNext($apply,$user,$date,$next_user);
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
     *传递给下一位处理
     *@param object $apply 申请对象
     *@param object $user  用户对象
     *@param string $date  日期
     *@param object $next  下一位用户对象
     */
    public static function passNext($apply,$user,$date,$next)
    {
        QuitApplyLog::addLog(array('apply_id'=>$apply->id,'action'=>'agree','user_id'=>$user->user_id, 'quit_date'=>$date, 'create_time'=>date('Y-m-d H:i:s') ));
        self::processQuitApply($apply, array('next'=>$next->user_id));
        self::noticeLeader($apply,$next,"已经提交,请尽快审批");
    }
    /**
     *同意审批 最后一步
     *@param object $apply 申请对象
     *@param object $user  用户对象
     *@param string $date  日期
     */
    public static function finishAgreeApply($apply,$user, $date)
    {
        QuitApplyLog::addLog(array('apply_id'=>$apply->id,'action'=>'agree','user_id'=>$user->user_id, 'quit_date'=>$date, 'create_time'=>date('Y-m-d H:i:s') ));
        self::processQuitApply($apply, array('next'=>0,'status'=>'success','quit_date'=>$date));
        $logs = $apply->logs;
        foreach($logs as $log)
        {
            $status = ($log->action == 'create') ? "已经通过,请备案": "已经通过";
            self::noticeLeader($apply,$log->user,$status);
        }
        self::noticeSelf($apply->id,$apply->user,"已经通过");
    }

    /**
     *拒绝离职
     *@param object $apply 申请对象
     *@param object $user  当前登录用户对象
     *@param string $reason 拒绝原因
     */
    public static function rejectApply($apply,$user, $reason)
    {
        
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            QuitApplyLog::addLog(array('apply_id'=>$apply->id,'action'=>'reject','user_id'=>$user->user_id, 'quit_date'=>'0000-00-00', 'create_time'=>date('Y-m-d H:i:s') ));
            self::processQuitApply($apply, array('next'=>0,'status'=>'reject','reason'=>$reason));
            $logs = $apply->logs;
            foreach($logs as $log)
            {
                $status = ($log->action == 'create') ? "未通过,请备案": "未通过";
                self::noticeLeader($apply,$log->user,$status);
            }
            self::noticeSelf($apply->id,$apply->user,"未通过");
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
     *发送通知 负责人
     */
    public static function noticeLeader($apply,$user,$status="已经提交,请尽快审批")
    {
        $url = "/oa/quitDetail/id/{$apply->id}";
        $title = "{$apply->user->cn_name}的离职申请单{$status}";
        $content = "{$apply->user->cn_name}的离职申请单{$status}，点击查看详情";
        $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
        $message = "<a href='{$host}{$url}'>{$apply->user->cn_name}的离职申请单{$status}，点击查看详情</a>";
        return self::sendNotice($user, $url, $title, $content , $message);
    }
    /**
     *发送通知 工作交接单
     *@param string $type ENUM('other','handover') 其他情况 交接情况
     */
    public static function noticeHandover($apply,$user,$status="已经提交,请尽快审批", $type='other')
    {
        $url = ($type=='handover') ? "/user/deliverWorkDetail/id/{$apply->id}":"/oa/deliverWorkDetail/id/{$apply->id}";
        $title = "{$apply->user->cn_name}的工作交接单{$status}";
        $content = "{$apply->user->cn_name}的工作交接单{$status}，点击查看详情";
        $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
        $message = "<a href='{$host}{$url}'>{$apply->user->cn_name}的工作交接单{$status}，点击查看详情</a>";
        return self::sendNotice($user, $url, $title, $content , $message);
    }

     /**
     *通知消息 非事务
     */
    public static function sendNotice($user, $url, $title, $content , $message)
    {
        $time = date('Y-m-d H:i:s');
        //通知用户
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'quit' , 'create_time'=>$time));
            
        //如果被通知的用户不在线 就发邮件通知
        if($user->online == 'off')
        {
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>$time,'update_time'=>$time);
            Mail::createMail($arr);
        }
        return true;
    }

    /**
     *获取下一种交换类型
     */
    public static function getNextType($type)
    {
        $data = array('work','admin','hr','it');
        if(!in_array($type, $data))
        {
            return 'work';
        }
        $pos = array_search($type,$data);
        $count = count($data)-1;
        return ($pos == $count) ? $data[$pos] : $data[$pos+1] ;
    }

    /**
     *获取该类型的处理人
     */
    public function getHandler()
    {
        switch($this->handover_type)
        {
            case 'work':
                return  Users::model()->findByPk($this->handover_user_id);
                break;
            case 'admin':
                return Users::getCcommissioner();
                break;
            case 'hr':
                return Users::getHr();
                break;
            case 'it':
                return Users::getWebAdmin();
                break;
        }
        return false;
    }

    /**
     *判读权限
     */
    public function getPermission($user_id)
    {
        switch($this->handover_type)
        {
            case 'work':
                if($user_id == $this->handover_user_id)
                {
                    return true;
                }
                break;
            case 'admin':
                if($user_id == Users::getCcommissioner()->user_id)
                {
                    return true;
                }
                break;
            case 'hr':
                if($user_id == Users::getHr()->user_id)
                {
                    return true;
                }
                break;
            case 'it':
                if($user_id == Users::getWebAdmin()->user_id)
                {
                    return true;
                }
                break;
        }
        return false;
    }
}
