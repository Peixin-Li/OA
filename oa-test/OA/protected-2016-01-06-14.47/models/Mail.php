<?php

/**
 * This is the model class for table "mail".
 *
 * The followings are the available columns in table 'mail':
 * @property integer $mail_id
 * @property integer $user_id
 * @property string $sender_email
 * @property string $receive_email
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property integer $count
 * @property string $reason
 * @property string $create_time
 * @property string $update_time
 */
class Mail extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Mail the static model class
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
		return 'mail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, sender_email, receive_email, subject, message, create_time, update_time', 'required'),
			array('user_id, count', 'numerical', 'integerOnly'=>true),
			array('sender_email, receive_email', 'length', 'max'=>45),
			array('subject', 'length', 'max'=>155),
			array('status', 'length', 'max'=>7),
			array('reason', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('mail_id, user_id, sender_email, receive_email, subject, message, status, count, reason, create_time, update_time', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'mail_id' => 'Mail',
			'user_id' => 'User',
			'sender_email' => 'Sender Email',
			'receive_email' => 'Receive Email',
			'subject' => 'Subject',
			'message' => 'Message',
			'status' => 'Status',
			'count' => 'Count',
			'reason' => 'Reason',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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

		$criteria->compare('mail_id',$this->mail_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('sender_email',$this->sender_email,true);
		$criteria->compare('receive_email',$this->receive_email,true);
		$criteria->compare('subject',$this->subject,true);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('count',$this->count);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
     *新建发送邮件邮件
     *@ array $data
     **/
    public static function createMail($data)
    {
        try
        {
            $model = new self();
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model->mail_id;
        }
        catch(Exception $e)
        {
        }
       	return false;
    }
     
     ### ###
    /**
     *群发邮件
     *@param  array $emails 收件人的email
     *@param array  $data  包含（user_id 发送此邮件的用户id, 发件人sender_mail，邮件标题subject，邮件内容message）的数组
     **/
    public static function createMailMany($emails,$data)
    {	
    	$transaction=self::model()->dbConnection->beginTransaction();
    	try
        {
        	foreach ($emails as $email) 
        	{	
            	$model = new self();
            	$model->receive_email = $email;
            	foreach($data as $key => $row)
            	{
                	$model->$key = $row;
            	}
            	$model->save();
            	Helper::processSaveError($model);
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
     *处理邮件记录(添加记录、更新记录)
     *@param object $model
     *@param array  $data
     *@return int
     */
    public static function processMail($model , $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model->mail_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

}
