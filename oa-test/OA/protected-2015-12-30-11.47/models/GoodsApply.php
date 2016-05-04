<?php

/**
 * This is the model class for table "goods_apply".
 *
 * The followings are the available columns in table 'goods_apply':
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $url
 * @property string $price
 * @property string $quantity
 * @property string $type
 * @property string $reason
 * @property integer $next
 * @property string $status
 * @property string $reject_reason
 * @property string $create_time
 * @property string $update_time
 */
class GoodsApply extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return GoodsApply the static model class
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
		return 'goods_apply';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, next, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('reject_reason', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, name, url, price, quantity, type, reason, next, status, reject_reason, create_time, update_time', 'safe', 'on'=>'search'),
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
            'logs' =>array(self::HAS_MANY, 'GoodsApplyLog', 'apply_id','condition'=>"user_id != '".Yii::app()->session['user_id']."'"),
            'allLogs'=>array(self::HAS_MANY, 'GoodsApplyLog', 'apply_id'),
            'details'=>array(self::HAS_MANY, 'GoodsApplyDetail', 'apply_id'),
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
			'next' => 'Next',
			'status' => 'Status',
			'reject_reason' => 'Reject Reason',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('quantity',$this->quantity,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('next',$this->next);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('reject_reason',$this->reject_reason,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public static function processGoodsApply($model, $data)
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

    public static function addApplyAndDetail($data, $arr)
    {
        $id = self::processGoodsApply(new self(), $data);
        foreach($arr as $row)
        {
            $row['apply_id'] = $id;
            $row['is_reimburse'] = 'no';
            $row['create_time'] = date('Y-m-d H:i:s');
            if(!GoodsApplyDetail::processGoodsDetail(new GoodsApplyDetail() , $row))
            {
                throw new Exception("error");
            }
        }
        return $id;
    }

     /**
     *通知消息 非事务
     */
    public static function sendNotice($user, $url, $title, $content , $message)
    {
        $time = date('Y-m-d H:i:s');
        //通知用户
        Notice::addNotice(array('user_id'=>$user->user_id ,'content'=>$content, 'title'=>$title, 'url'=>$url, 'status'=>'wait', 'type'=>'goods_apply' , 'create_time'=>$time));
            
        //如果被通知的用户不在线 就发邮件通知
        if($user->online == 'off')
        {
            $arr = array('user_id'=>$user->user_id, 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$user->email, 'subject'=>$title, 'message'=>$message,'create_time'=>$time,'update_time'=>$time);
            Mail::createMail($arr);
        }
        return true;
    }

    /**
     *同意申请
     *@param object $apply
     *@param object $user
     * @param bool $tag 要不要发给总经理审批 true是要
     */
    public static function agreeApply($apply, $user, $tag)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $leader_id = $apply->user->leadId;
            $admin  = Users::getAdminId();
            $admin_id = $admin->user_id;
            $ceo    = Users::getCeo();
            $ceo_id = $ceo->user_id;
            $commissioner = Users::getCcommissioner();
            $hr = Users::getHr();
            GoodsApplyLog::addLog(array('apply_id'=>$apply->id,'user_id'=>$user->user_id,'action'=>'agree','create_time'=>date('Y-m-d H:i:s')));

            $procedure_list = CJSON::decode($apply->procedure_list, true);

            if( (empty($procedure_list))||($user->user_id == end($procedure_list)) )
            {
                $apply->status='success';
                $apply->next = '0';
                self::notice($apply, $commissioner, '已经完成,请备案');
                self::notice($apply, $hr, '已经完成,请备案');
                self::notice($apply, $ceo, '已经完成,请备案');
                self::notice($apply, $apply->user, '已经完成', 'self');
            }
            else
            {   //通知下一个审批者
                foreach ( $procedure_list as $key => $value) {
                    if($value == $user->user_id) {
                        break;
                    }
                }
                $apply->next = $procedure_list[$key+1];
                $next_user_info = Users::model()->findByPk($apply->next);
                self::notice($apply, $next_user_info, '已提交，请尽快审批');
            }
            $apply->save();
            Helper::processSaveError($apply);
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
     *拒绝申请
     */
    public static function rejectGoodsApply($apply, $user, $reason)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            GoodsApplyLog::addLog(array('apply_id'=>$apply->id,'user_id'=>$user->user_id,'action'=>'reject','create_time'=>date('Y-m-d H:i:s')));
            $apply->status='reject';
            $apply->reject_reason=$reason;
            $apply->next = '0';
            $apply->save();
            Helper::processSaveError($apply);
            $book_tag = GoodsApplyDetail::isBook($apply->details);
            self::noticeHeads($apply , '未通过', $book_tag);
            self::notice($apply, $apply->user, '未通过','self', $book_tag);
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }

    public static function noticeById($id, $user, $status, $type = 'other', $is_book=false)
    {
        $apply = GoodsApply::model()->findByPk($id);
        self::notice($apply, $user, $status , $type, $is_book);
    }
    public static function notice($apply, $user, $status , $type = 'other', $is_book=false)
    {
        $host = Yii::app()->request->hostInfo;
        $goods_type = !empty($is_book) ? '图书申请':'物资请购';
        $url = ($type == 'self')?"/user/subscribeDetail/id/{$apply->id}":"/oa/subscribeDetail/id/{$apply->id}";
        $title = ($type=='self')?"你提交的{$goods_type}，{$status}":"{$apply->user->cn_name}提交的{$goods_type}，{$status}";
        $content =($type=='self')?"你提交的{$goods_type}，{$status}。详情请点击":"{$apply->user->cn_name}提交的{$goods_type}，{$status}。详情请点击";
        $message = ($type == 'self')?"<a href='{$host}{$url}'>你提交的{$goods_type}，{$status}。详情请点击</a>":"<a href='{$host}{$url}'>{$apply->user->cn_name}提交的{$goods_type}，{$status}。详情请点击</a>";
        self::sendNotice($user , $url, $title, $content, $message);
    }
     /**
     *通知审批的人
     */
    public static function noticeHeads($apply , $status='已通过',$is_book=false)
    {
        $host = Yii::app()->request->hostInfo;
        $url = "/oa/subscribeDetail/id/{$apply->id}";
        $goods_type = !empty($is_book) ? '图书申请': '物资请购';
        $title = "{$apply->user->cn_name}提交的{$goods_type}，{$status}";
        $content = "{$apply->user->cn_name}提交的{$goods_type}，{$status}。详情请点击";
        $message = "<a href='{$host}{$url}'>{$apply->user->cn_name}提交的{$goods_type}，{$status}。详情请点击</a>";
        foreach($apply->logs as $row)
        {     
            self::sendNotice($row->user, $url, $title, $content , $message);
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
     * 进度条
     * 1.正常流程的 ->部门负责人 -> sara -> verky ->完成
     * 2.是人事部的同事转正 ->部门负责人 -> verky ->完成
     * 3.部门负责人转正（非人事总监） ->verky -> sara ->完成
     * 4.人事总监转正  ->verky  ->完成
     */
    public static function procedure($apply)
    {
        $procedure = array();
        $procedure_list = CJSON::decode($apply->procedure_list);
        $logs = $apply->allLogs;                                    //获取所有审批日志
        foreach ($procedure_list as $row) {
            $user = Users::model()->findByPk($row);
            $status = $apply->status;
            foreach ($logs as $log) {
                if($log->user->user_id == $user->user_id) {
                    $status = $log->action;
                    break;
                }
            }
            $procedure[] = array(
                $user->department->name , $status
            );
            // $procedure[] = array(
            //     'name' => $user->cn_name,
            //     'department'=> $user->department->name,
            // );
        }
        return $procedure;
    }

    /**
     *物资申购
     */
    public static function addGoodsApply($arr , $user, $tag)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $commissioner = Users::getCcommissioner();
            $data['user_id'] = $user->user_id;
            $data['create_time'] = date('Y-m-d H:i:s');
            $excess_tag = GoodsApplyDetail::excessBudget($user, $arr);

            $procedure_value = $excess_tag ? 1 : 0;
            $procedure_list = Procedure::getProcedure('goods_apply', $procedure_value, $user->user_id);
            //去除审批流程中的自己审批的节点
            $procedure_list = Procedure::removeRepeat($procedure_list, $user->user_id);
            $data['procedure_list'] = CJSON::encode($procedure_list);

            if( empty($procedure_list) ) //审批流程为空，直接通过
            {
                $data['next'] = '0';
                $data['status'] = 'success';
                $id = self::addApplyAndDetail($data, $arr);
                self::noticeById($id, $user, '已经完成', 'self');
                self::noticeById($id, $commissioner, '已经完成,请备案');
            }
            else
            {
                $data['status'] = 'wait';
                $data['next'] =  $procedure_list[0];
                $id = self::addApplyAndDetail($data, $arr);
                $next_user_info = Users::model()->findByPk($data['next']);
                self::noticeById($id, $next_user_info, '已提交,请尽快审批');
                self::noticeById($id, $user, '已提交','self');
            }
            $transaction->commit();
            return $id; 
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }

}
