<?php

/**
 * This is the model class for table "quit_handover".
 *
 * The followings are the available columns in table 'quit_handover':
 * @property integer $id
 * @property integer $apply_id
 * @property string $type
 * @property integer $user_id
 * @property integer $supervision_id
 * @property string $status
 * @property string $update_time
 * @property string $create_time
 */
class QuitHandover extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return QuitHandover the static model class
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
		return 'quit_handover';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('apply_id, type, user_id, supervision_id, create_time', 'required'),
			array('apply_id, user_id, supervision_id', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>5),
			array('status', 'length', 'max'=>7),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, apply_id, type, user_id, supervision_id, status, update_time, create_time', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'apply_id' => 'Apply',
			'type' => 'Type',
			'user_id' => 'User',
			'supervision_id' => 'Supervision',
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
		$criteria->compare('apply_id',$this->apply_id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('supervision_id',$this->supervision_id);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加交接事务
     *@param object $apply 离职申请的ID
     *@param object $user  当前登录用户的对象
     *@param array  $content 交接的内容
     *@return boolean
     */
    public static function processTransaction($apply,$user,$contents)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $next =  $user->leadId;
            $next = empty($next) ? Users::getCeo()->user_id : $next;
            $dtime = date('Y-m-d H:i:s');
            $status = 'wait';
            $type = $apply->handover_type;
            if($apply->handover_type == 'it')
            {
                $status = 'success';
                $next = 0;
            }
            $id = self::processQuitHandover(new self(), array('apply_id'=>$apply->id,'type'=>$apply->handover_type,'user_id'=>$user->user_id, 'supervision_id'=>$next, 'status'=>$status,'create_time'=>$dtime));
            foreach($contents as $key=>$row)
            {
                $row = urldecode(json_encode(self::urlencodeArray(array($key=>$row))));
                QuitHandoverDetail::processQuitHandoverDetail(new QuitHandoverDetail(), array('handover_id'=>$id, 'content'=>$row,'create_time'=>$dtime));
            }
            if($apply->handover_type == 'admin')
            {
                $next = Users::getHr()->user_id;
                QuitApply::processQuitApply($apply, array('handover_type'=>'hr'));
            }
            elseif($apply->handover_type == 'it')
            {
                QuitApply::processQuitApply($apply, array('handover_status'=>'success'));
                QuitApply::noticeHandover($apply,Users::getHr(),"已经完成,请备案");
                Users::updateUser($apply->user , array('status'=>'quit'));
                QuitApply::noticeHandover($apply,Users::getCeo(),"已经完成,将于今天(".date('Y-m-d').")离职");
            }
            if(!empty($next))
            {
                QuitApply::noticeHandover($apply, Users::model()->findByPk($next), "已经提交,请尽快去处理");
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
     *用urlencode编译key和value
     */
    public static function urlencodeArray($array)
    {
        $data = array();
        if(!is_array($array)) return $array;
        foreach($array as $key => $row)
        {
            $_key = urlencode($key);
            if(is_array($row)) $data[$_key]= self::urlencodeArray($row);
            else $data[$_key] = urlencode($row);
        }
        return $data;
    }

    /**
     *添加一个工作交接
     */
    public static function processQuitHandover($model, $data)
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
     *确认处理工作交接
     *@param object $apply 离职申请的对象
     */
    public function confirmHandler($apply)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //最后一步 成功并且发送给人事备案
            $type = QuitApply::getNextType($apply->handover_type);
            if($apply->handover_type == 'it')
            {
                QuitApply::processQuitApply($apply, array('handover_type'=>$type, 'handover_status'=>'success'));
                QuitApply::noticeHandover($apply,Users::getHr(),"已经完成,请备案");
                Users::updateUser($apply->user , array('status'=>'quit'));
                QuitApply::noticeHandover($apply,Users::getCeo(),"已经完成,将于今天(".date('Y-m-d').")离职");
            }
            else //处理一下quit apply的类型 并且发送通知给下一位处理的人
            {
                QuitApply::processQuitApply($apply, array('handover_type'=>$type));
                QuitApply::noticeHandover($apply,$apply->handler,"已经提交,请尽快和他交接");
            }
            QuitHandover::processQuitHandover($this, array('status'=>'success'));
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
     *确认处理工作交接 NEW
     *@param object $apply 离职申请的对象
     */
    public static function newConfirmHandler($apply)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            //最后一步 成功并且发送给人事备案
            $type = QuitApply::getNextType($apply->handover_type);
            if($apply->handover_type == 'hr')
            {
                if(!QuitApply::processQuitApply($apply, array('handover_type'=>$type)))
                {
                    throw new Exception('error');
                }
                QuitApply::noticeHandover($apply,$apply->handler,"已经提交,请尽快和他交接");
            }
            if($res = QuitHandover::model()->findAll("apply_id = :id and type in ('admin','hr')", array(':id'=>$apply->id)))
            {
                foreach($res as $row)
                {
                    if(!QuitHandover::processQuitHandover($row, array('status'=>'success')))
                    {
                        throw new Exception('error');
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
}
