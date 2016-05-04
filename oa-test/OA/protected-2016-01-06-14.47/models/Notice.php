<?php

/**
 * This is the model class for table "notice".
 *
 * The followings are the available columns in table 'notice':
 * @property integer $id
 * @property integer $user_id
 * @property string $content
 * @property string $url
 * @property string $status
 * @property string $create_time
 */
class Notice extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Notice the static model class
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
		return 'notice';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, content, url, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('content, url', 'length', 'max'=>300),
			array('status', 'length', 'max'=>4),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, content, url, status, create_time', 'safe', 'on'=>'search'),
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
			'content' => 'Content',
			'url' => 'Url',
			'status' => 'Status',
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
		$criteria->compare('content',$this->content,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加一条记录
     */
    public static function addNotice($data)
    {
        try
        {
            $model = new Notice();
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

    public static function addSuggest($content, $url, $user)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //发送邮件给vincent,jeff,verky
            $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
            $message = "{$content}  反馈的页面:<a href='{$host}{$url}'>{$host}{$url}</a>";
            $subject = "{$user->cn_name}的意见反馈";
            $dtime = date('Y-m-d H:i:s');
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com',
                         'subject'=>$subject, 'message'=>$message,
                         'create_time'=>$dtime,'update_time'=>$dtime );
            if($users = Operator::model()->findAll(array('condition'=>"type=:type",'params'=>array(':type'=>'feedback'),'group'=>'object_id')))
            {
                foreach($users as $row)
                {
                    if(!Mail::createMail(array_merge($arr, array('receive_email'=>$row->user->email))))
                    {
                        throw Exception('error');
                    }
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
     *更新通知记录
     *@param object $model
     *@param array $data
     *@return int
     */
    public static function updateNotice($model, $data)
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
     *发送邮件的函数
     *单个邮件发送
     */
    public static function sendEmail($email , $message, $subject)
    {
        try
        {
            $mailer = Yii::createComponent('application.extensions.mailer.EMailer');
            $mailer->Host = Yii::app()->params['smtp_host'];
            $mailer->IsSMTP();
            $mailer->SMTPAuth = true;
            $mailer->From = Yii::app()->params['smtp_email'];
			$mailer->AddAddress($email);
            $mailer->FromName = '善游';
            $mailer->Username = Yii::app()->params['smtp_email'];
            $mailer->Password = Yii::app()->params['stmp_password'];
            //$mailer->SMTPDebug = true;   
            $mailer->IsHTML(true);
            $mailer->CharSet = 'UTF-8';
            $mailer->Subject = $subject;
            $mailer->Body = $message;
            $mailer->Send();
            return true;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *返回发邮件的内容
     *@param array $result
     *@return string 
     */
    public static function mailContent($result)
    {
         $body = "<table><tr><th style='margin-right:10px;'>菜名</th><th>份数</th><th>价格</th></tr>";
         $total = 0;
         foreach($result as $key => $row)
         {
            $body .= "<tr><td>{$key}</td><td>{$row['count']}</td><td>{$row['price']}</td></tr>";
            $total += $row['price'];
         }
         $body .= "<tr><td colspan='3'>合计:{$total}元</td></tr>";
         $body .= "</table>";
        return $body;
    }

    /**
     *验证邮件地址
     *@function validEmail
     *@param array $emails
     *@return boolean
     */
    public static function validEmail($emails)
    {
        if(empty($emails))
        {
            return false;
        }
        foreach($emails as $emailAddress)
        {   
            if(!preg_match('/^[\w\.\-\_]+@[\.\w\-\_]+$/' , $emailAddress))
            {
               return false;
            }
        }
        return true;
    }

    /**
     *一键标记全部信息为已读
     *@param array $models
     *@param array $data
     *@return bool
     */
    public static function MarkAllRead($models, $data)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            foreach($models as $model)
            {
                foreach($data as $key => $value)
                {
                    $model->$key = $value;
                }
                $model->save();
                Helper::processSaveError($model);
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
