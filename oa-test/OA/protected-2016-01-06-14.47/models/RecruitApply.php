<?php

/**
 * This is the model class for table "recruit_apply".
 *
 * The followings are the available columns in table 'recruit_apply':
 * @property integer $id
 * @property integer $user_id
 * @property string $department
 * @property integer $parent_id
 * @property string $title
 * @property integer $number
 * @property string $entry_day
 * @property string $pay
 * @property string $type
 * @property integer $quit_user_id
 * @property string $quit_date
 * @property string $add_reason
 * @property string $work_content
 * @property integer $work_life
 * @property string $individuality
 * @property string $comment
 * @property string $create_date
 */
class RecruitApply extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return RecruitApply the static model class
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
		return 'recruit_apply';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, department, parent_id, title, number, entry_day, pay, type, work_content, work_life,  create_date, status', 'required'),
			array('user_id, parent_id, number, quit_user_id, work_life', 'numerical', 'integerOnly'=>true),
			array('department, title, pay', 'length', 'max'=>45),
			array('type', 'length', 'max'=>8),
			array('status', 'length', 'max'=>7),
			array('add_reason, work_content, individuality, comment', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, department, parent_id, title, number, entry_day, pay, type, quit_user_id, quit_date, add_reason, work_content, work_life, individuality, comment, create_date', 'safe', 'on'=>'search'),
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
            'condition'=>array(self::HAS_ONE, 'RecruitCondition', 'recruit_id'),
            'logs' =>array(self::HAS_MANY, 'RecruitApplyLog', 'apply_id','condition'=>"user_id != '".Yii::app()->session['user_id']."'"),
            'alllogs' =>array(self::HAS_MANY, 'RecruitApplyLog', 'apply_id'),
            'resumes'=>array(self::HAS_MANY, 'Resume', 'apply_id'),
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
			'department' => 'Department',
			'parent_id' => 'Parent',
			'title' => 'Title',
			'number' => 'Number',
			'entry_day' => 'Entry Day',
			'pay' => 'Pay',
			'type' => 'Type',
			'quit_user_id' => 'Quit User',
			'quit_date' => 'Quit Date',
			'add_reason' => 'Add Reason',
			'work_content' => 'Work Content',
			'work_life' => 'Work Life',
			'individuality' => 'Individuality',
			'comment' => 'Comment',
			'create_date' => 'Create Date',
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
		$criteria->compare('department',$this->department,true);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('number',$this->number);
		$criteria->compare('entry_day',$this->entry_day,true);
		$criteria->compare('pay',$this->pay,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('quit_user_id',$this->quit_user_id);
		$criteria->compare('quit_date',$this->quit_date,true);
		$criteria->compare('add_reason',$this->add_reason,true);
		$criteria->compare('work_content',$this->work_content,true);
		$criteria->compare('work_life',$this->work_life);
		$criteria->compare('individuality',$this->individuality,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('create_date',$this->create_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加招聘申请
     */
    public static function addRecruitApply($model , $data)
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
     *招聘申请通知 也就发给自己和人事
     *@param object $user 当前用户
     *@param string $id   招聘申请ID
     */
    public static function applyNotitce($user, $id, $title, $number)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $url= "/oa/recruitApplyDetail/id/{$id}";
            $content = "你申请招聘{$title}{$number}人";
            //通知自己
            Notice::addNotice(array('user_id'=>$user->user_id , 'content'=>$content,'title'=>"你的招聘申请已经提交", 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
            if( $apply_info = self::model()->findByPk($id) ) {
                $next = $apply_info->next;
                if ( $next_user = Users::model()->findByPk($next) )
                    self::noticeApproval($next_user , $user,$id, $title, $number, ",请尽快审批");
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
     *通知审批人
     */
    public static function noticeApproval($next_user, $user,$id, $title, $number, $status=",请尽快审批", $type = 'leader')
    {
        $url= "/oa/recruitApplyDetail/id/{$id}";
        $name = ($type=='leader') ? $user->cn_name : '你';
        $hr_content = "{$name}的申请招聘{$title}{$number}人";
        //通知审批人
        Notice::addNotice(array('user_id'=>$next_user->user_id ,'content'=>$hr_content, 'title'=>"{$name}的招聘申请{$status}", 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
            
        //如果人事总监不在线
        if($next_user->online == 'off')
        {
            $url = Yii::app()->request->hostInfo.$url;
            $message = "<b>申请人:</b> {$name}<br><b>申请招聘职位:</b>{$title}<br><b>申请招聘人数:</b>{$number}人<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$next_user->email, 'subject'=>"{$name}的招聘申请{$status}", 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
            Mail::createMail($arr);
        }
    }

    /**
     *上传了简历通知申请人
     */
     public static function noticeApplyUserByResume($apply, $name)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $user = $apply->user;
            $name = iconv('gbk','utf-8',$name);
            $url= "/oa/recruitApplyDetail/id/{$apply->id}";
            $title = "HR提交了一份{$apply->title}的简历";
            $content = "{$name}应聘{$apply->title}, 请尽快查阅";
            //通知申请人
            Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
                
            //如果人事hr不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$name}<br><b>申请招聘职位:</b>{$apply->title}<br><b>简历已经提交</b><br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$apply->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                Mail::createMail($arr);
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
     *获取替补离职人员
     */
    public function getQuitUser()
    {
        return Users::model()->findByPk($this->quit_user_id);
    }

    /**
     *@param object $apply 当前处理的申请单
     *@param object $user  当前登录用户
     *@param object $user  下一个审批者
     */ 
    public static function passNext($apply, $user, $next_user)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $apply->next = $next_user->user_id;
            $apply->save();
            Helper::processSaveError($apply);
            RecruitApplyLog::addLog(array('apply_id'=>$apply->id,'user_id'=>$user->user_id , 'action'=>'agree', 'create_time'=>date('Y-m-d H:i:s')));
            self::noticeApproval($next_user, $apply->user,$apply->id, $apply->title, $apply->number);
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
     *CEO完成审核
     *@param object $apply 当前处理的申请单
     *@param object $user  当前登录用户
     */
    public static function finishRecruitApply($apply, $user)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $apply->next = 0;
            $apply->status = 'success';
            $apply->save();
            Helper::processSaveError($apply);
            RecruitApplyLog::addLog(array('apply_id'=>$apply->id,'user_id'=>$user->user_id , 'action'=>'agree', 'create_time'=>date('Y-m-d H:i:s')));
            
            //如果是编制外增补就修改编制表
            if($apply->type == 'add')
            {
                if(!Formation::createDepartment($apply))
                {
                    throw new Exception('-1');
                }
            }
            //申请人
            $user = $apply->user;
            $admin = Users::getAdminId();
            //通知人事总监
            if($user->user_id != $admin->user_id)
            {
                self::noticeApproval($admin, $user,$apply->id, $apply->title, $apply->number, '已完成');
            }
            self::noticeApproval($user, $user, $apply->id,$apply->title, $apply->number, '已完成' , 'self');
            //通知一下HR让他去搜简历
            self::noticeApproval(Users::getHr(), $user,$apply->id, $apply->title, $apply->number, '已完成,请尽快去招人');
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
     *拒绝审批
     *@param object $apply   当前处理的申请单
     *@param object $user  当前登录用户
     *@param string $reason  当前登录用户
     */
    public static function rejectRecruitApply($apply,$user,$reason)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $apply->next = 0;
            $apply->status = 'reject';
            $apply->reason = $reason;
            $apply->save();
            Helper::processSaveError($apply);
            RecruitApplyLog::addLog(array('apply_id'=>$apply->id,'user_id'=>$user->user_id , 'action'=>'reject', 'create_time'=>date('Y-m-d H:i:s')));
            //申请人
            $user = $apply->user;
            //通知人事总监
            $logs = $apply->logs;
            foreach($logs as $log)
            {
                self::noticeApproval($log->user, $user,$apply->id, $apply->title, $apply->number, '未通过');
            }
            self::noticeApproval($user, $user, $apply->id,$apply->title, $apply->number, '未通过' , 'self');
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
     *获取进度条
     */
    public function getProcedure()
    {
        $procedure = array();
        //已经审批通过
        if($this->status == 'success')
        {
            $logs = $this->alllogs;
            foreach($logs as $log)
            {
                $procedure[] = array($log->user->cn_name, $log->user->department->name, $log->action);
            }
            return $procedure;
        }
        $leaders = CJSON::decode($this->procedure_list, true);
        //处理成
        foreach($leaders as $leader)
        {
            if($_log =  RecruitApplyLog::model()->find('user_id=:user_id and apply_id=:apply_id', array(':user_id'=>$leader, ':apply_id'=>$this->id)))
            {
                $procedure[] = array($_log->user->cn_name, $_log->user->department->name, $_log->action);
            }
            else
            {
                $user = Users::model()->findByPk($leader);
                $procedure[] = array($user->cn_name, $user->department->name, $this->status);
            }
        }
        return $procedure;

    }

    /**
     *统计简历的条数
     *@param string $type 要搜索的类别
     *@param string $value 要搜索的值
     *@return int
     */
    public static function countResume($type,$value)
    {
        $sql = "SELECT count(*) FROM recruit_apply join resume on(resume.apply_id=recruit_apply.id) where {$type}='{$value}';";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }

    /**
     *搜索简历
     *@param string $type 要搜索的类别
     *@param string $value 要搜索的值
     *@param string $limit  限制的值
     *@param string $offset 偏移量
     *@return array
     */
    public static function searchResume($type,$value, $limit, $offset)
    {
        $sql = "SELECT resume.* FROM recruit_apply join resume on(resume.apply_id=recruit_apply.id) where {$type}='{$value}' order by create_time desc limit {$limit} offset {$offset};";
        #return Yii::app()->db->createCommand($sql)->queryAll();
        return Resume::model()->findAllBySql($sql);
    }
    /**
     *搜索
     */
    public static function searchResumeByApply($type, $name)
    {
        $count = RecruitApply::countResume($type, $name);
        $page = new CPagination($count);
        $page->pageSize = 20;
        $limit = $page->pageSize;
        $offset = $page->currentPage * $page->pageSize ;
        $result = RecruitApply::searchResume($type, $name, $limit, $offset);
        return array($page, $result);
    }

}
