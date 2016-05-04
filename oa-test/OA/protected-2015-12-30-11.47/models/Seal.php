<?php

/**
 * This is the model class for table "seal".
 *
 * The followings are the available columns in table 'seal':
 * @property integer $id
 * @property integer $user_id
 * @property string $use_time
 * @property string $type
 * @property integer $number
 * @property string $address
 * @property string $reason
 * @property string $path
 * @property string $update_time
 * @property string $create_time
 */
class Seal extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Seal the static model class
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
		return 'seal';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, use_time, number, reason, update_time, create_time', 'required'),
			array('user_id, number', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>25),
			array('address, path', 'length', 'max'=>100),
			array('reason', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, use_time, type, number, address, reason, path, update_time, create_time', 'safe', 'on'=>'search'),
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
			'use_time' => 'Use Time',
			'type' => 'Type',
			'number' => 'Number',
			'address' => 'Address',
			'reason' => 'Reason',
			'path' => 'Path',
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
		$criteria->compare('use_time',$this->use_time,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('number',$this->number);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('path',$this->path,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


    /**
     *处理申请印章
     */
    public static function processApply($model, $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                if($key == 'type' && is_array($row))
                {
                    $row = join($row,",");
                }
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model;
            //return $model->id;
        }
        catch(Exception $e)
        {
            //var_dump($e->getMessage());
        }
        return false;
    }

    /**
     *发送通知
     */
    public static function noticeSeal($seal,$status='提交了')
    {
    	    $transaction=self::model()->dbConnection->beginTransaction();
            try
            {
            	$host = Yii::app()->request->hostInfo;
            	$url = "/oa/printSeal/id/{$seal->id}";
            	$title = "{$seal->user->cn_name}{$status}印鉴使用申请";
            	$content = "{$seal->user->cn_name}{$status}印鉴使用申请，请先查阅；";
            	$message = "<a href='{$host}{$url}'>{$content}</a>";
            	self::sendNotice(Users::getCeo(), $url, $title, $content , $message);
            	self::sendNotice(Users::getCcommissioner(), $url, $title, $content , $message);
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
     *通知消息 非事务
     */
    public static function sendNotice($user, $url, $title, $content , $message)
    {
        $time = date('Y-m-d H:i:s');
        //通知用户
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'seal' , 'create_time'=>$time));
            
        //如果被通知的用户不在线 就发邮件通知
        if($user->online == 'off')
        {
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>$time,'update_time'=>$time);
            Mail::createMail($arr);
        }
        return true;
    }
}
