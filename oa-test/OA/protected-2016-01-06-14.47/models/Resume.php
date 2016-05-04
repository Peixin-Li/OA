<?php

/**
 * This is the model class for table "resume".
 *
 * The followings are the available columns in table 'resume':
 * @property integer $id
 * @property integer $apply_id
 * @property string $name
 * @property string $status
 * @property string $resume_file
 * @property string $interview_time
 * @property string $update_time
 * @property string $create_time
 */
class Resume extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Resume the static model class
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
		return 'resume';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('apply_id, source, name, resume_file, interview_time, create_time', 'required'),
			array('apply_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>45),
			array('status', 'length', 'max'=>12),
			array('resume_file', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, apply_id, name, status, resume_file, interview_time, update_time, create_time', 'safe', 'on'=>'search'),
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
            'apply'=>array(self::BELONGS_TO, 'RecruitApply', 'apply_id'),
            'assessment'=>array(self::HAS_ONE, 'Assessment','resume_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'apply_id' => 'Apply',
			'name' => 'Name',
			'status' => 'Status',
			'source' => 'Source',
			'resume_file' => 'Record File',
			'interview_time' => 'Interview Time',
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
		$criteria->compare('apply_id',$this->apply_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('resume_file',$this->resume_file,true);
		$criteria->compare('interview_time',$this->interview_time,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加简历
     *@param object $model
     *@param array  $data
     *@return int
     */
    public static function processResume($model , $data)
    {
        try
        {
            foreach($data as $key => $value)
            {
                $model->$key = $value;
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
     *通知人事专员来安排面试时间
     *@param object $resume 面试人的对象
     */
    public static function noticeHr($resume)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $hr = Users::getHr(); 
            $url= "/oa/recruitApplyDetail/id/{$resume->apply->id}#{$resume->id}";
            $title = "请尽快安排{$resume->name}的面试时间";
            $hr_content = "{$resume->name}符合我们{$resume->apply->title}的要求，请尽快安排面试";
            //通知hr
            Notice::addNotice(array('user_id'=>$hr->user_id ,'content'=>$hr_content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
                
            //如果人事hr不在线
            if($hr->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>渠道:</b>{$resume->source}<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$hr->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$hr->email, 'subject'=>"请尽快安排{$resume->name}的面试时间", 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
     *安排好面试时间了通知申请人
     */
     public static function noticeApplyUser($resume)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $user = $resume->apply->user;
            $url= "/oa/recruitApplyDetail/id/{$resume->apply->id}#{$resume->id}";
            $title = "{$resume->name}的面试时间，已经安排好了";
            $content = "{$resume->name}的面试时间为 {$resume->interview_time}, 到时请安排好时间面试";
            //通知申请人
            Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
                
            //如果人事hr不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>面试时间:</b>{$resume->interview_time}<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$resume->apply->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>"{$resume->name}的面试时间，已经安排好了", 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
     *安排好面试时间了 通知部门负责人 设置面试官
     */
     public static function noticeSetInterviewer($resume)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $user = $resume->apply->user;
            $url= "/oa/recruitApplyDetail/id/{$resume->apply->id}#{$resume->id}";
            $title = "{$resume->name}的面试时间，已经安排好了, 请设置面试官";
            $content = "{$resume->name}的面试时间为 {$resume->interview_time}, 请尽快安排好面试官进行面试";
            //通知申请人
            Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
                
            //如果人事hr不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>面试时间:</b>{$resume->interview_time}, 请尽快安排好面试官进行面试<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$resume->apply->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
     *修改面试时间并且通知申请人
     */
     public static function noticeApplyUserByEdit($id, $time)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $resume = Resume::model()->findByPk($id);
            $time   = date('Y-m-d H:i', strtotime($time));
            $resume->interview_time   = date('Y-m-d H:i', strtotime($resume->interview_time));
            $user = $resume->apply->user;
            $url= "/oa/recruitApplyDetail/id/{$resume->apply->id}#{$resume->id}";
            $title = "{$resume->name}的面试时间由原来的{$resume->interview_time}变成{$time}";
            $content = "{$resume->name}的面试时间由原来的{$resume->interview_time}变成{$time}, 到时请安排好时间面试";
            //通知申请人
            Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
                
            //如果人事hr不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>原来的面试时间:</b>{$resume->interview_time}<br><b>变更后的面试时间:</b>{$time}<br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$resume->apply->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>"{$resume->name}的面试时间由原来的{$resume->interview_time}变成{$time}", 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
     *下载文件
     */
    public static function download($file_name, $dst_name)
    {
        //$file_name=iconv("utf-8","gb2312","$file_name");
        if(!file_exists($file_name)){//判断文件是否存在
            echo "file not found";
            exit();
        }
        $fp=fopen($file_name,"r+");//下载文件必须先要将文件打开，写入内存
        $file_size=filesize($file_name);//判断文件大小
        //返回的文件
        Header("Content-type: application/octet-stream");
        //按照字节格式返回
        Header("Accept-Ranges: bytes");
        //返回文件大小
        Header("Accept-Length: ".$file_size);
        //弹出客户端对话框，对应的文件名
        Header("Content-Disposition: attachment; filename={$dst_name}");
        //防止服务器瞬时压力增大，分段读取
        $buffer=1024;
        while(!feof($fp)){
            $file_data=fread($fp,$buffer);
            echo $file_data;
        }
        //关闭文件
        fclose($fp);
    }
    /**
     *搜索简历
     *@param string $condition 条件
     *@param string $params    数值
     */
    public static function searchResumt($condition, $params)
    {
        $count = Resume::model()->count(array('condition'=>$condition, 'params'=>$params));
        $page = new CPagination($count);
        $page->pageSize = 20;
        $limit = $page->pageSize;
        $offset = $page->currentPage * $page->pageSize ;
        $result = Resume::model()->findAll(array('condition'=>$condition, 'params'=>$params, 'limit'=>$limit, 'offset'=>$offset, 'order'=>'create_time desc'));     
        return array($page, $result);
    }
    
    /**
     *通知面试官
     *@param object $resume 面试人的对象
     */
    public static function noticeInterviewer($resume)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $user = Users::model()->findByPk($resume->interviewer); 
            $url= "/user/recruitApplyDetail/id/{$resume->apply->id}#{$resume->id}";
            $title = "请在{$resume->interview_time}给{$resume->name}面试";
            $content = "{$resume->name}符合我们{$resume->apply->title}的要求，请在{$resume->interview_time}给{$resume->name}安排面试。";
            //通知面试官
            Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
            //如果面试官不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>渠道:</b>{$resume->source}<br><b>符合我们{$resume->apply->title}的要求，请在{$resume->interview_time}给{$resume->name}安排面试</b><br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>"符合我们{$resume->apply->title}的要求，请在{$resume->interview_time}给{$resume->name}安排面试", 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
                Mail::createMail($arr);
            }
            //通知HR
            $hr = Users::getHr();
            $hr_url= "/oa/recruitApplyDetail/id/{$resume->apply->id}#{$resume->id}";
            $hr_title = "安排{$user->cn_name}为{$resume->apply->title}岗位的{$resume->name}进行面试";
            $hr_content = "应聘{$resume->apply->title}岗位的{$resume->name}面试官为{$user->cn_name},请在{$resume->interview_time}叫上面试官给{$resume->name}安排面试。";
            //通知hr
            Notice::addNotice(array('user_id'=>$hr->user_id ,'content'=>$hr_content, 'title'=>$hr_title, 'url'=>$hr_url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
                
            //如果人事hr不在线
            if($hr->online == 'off')
            {
                $hr_url = Yii::app()->request->hostInfo.$hr_url;
                $message = "<b>姓名:</b> {$resume->name}<br><b>申请招聘职位:</b>{$resume->apply->title}<br><b>渠道:</b>{$resume->source}<br><b>符合我们{$resume->apply->title}的要求，安排{$user->cn_name}对其进行面试，请在{$resume->interview_time}叫上面试官给{$resume->name}安排面试</b><br><b>详情:</b> 请<a href='{$hr_url}'>登录</a>查看";
                $arr = array('user_id'=>$hr->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$hr->email, 'subject'=>$hr_title, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
     *通知面试官填写评估表
     *@param object $resume 面试人的对象
     */
    public static function noticeInterviewerAssessment($resume)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $user = Users::model()->findByPk($resume->interviewer); 
            $url= "/user/interviewEvaluateDetail/id/{$resume->id}";
            $title = "请填写{$resume->name}的面试评估表";
            $content = "请填写{$resume->name}的面试评估表。";
            //通知面试官
            Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'recruit' , 'create_time'=>date('Y-m-d H:i:s')));
            //如果面试官不在线
            if($user->online == 'off')
            {
                $url = Yii::app()->request->hostInfo.$url;
                $message = "<b>{$resume->name}已面试完毕，请填写面试评估表。</b><br><b>详情:</b> 请<a href='{$url}'>登录</a>查看";
                $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>"填写{$resume->name}的面试评估表", 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
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
}
