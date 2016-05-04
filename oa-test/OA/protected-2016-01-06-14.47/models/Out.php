<?php

/**
 * This is the model class for table "out".
 *
 * The followings are the available columns in table 'out':
 * @property integer $out_id
 * @property integer $user_id
 * @property string $content
 * @property string $plan
 * @property string $type
 * @property string $delay
 * @property string $place
 * @property string $company
 * @property string $transport
 * @property string $status
 * @property integer $next
 * @property string $start_time
 * @property string $end_time
 * @property string $create_time
 */
class Out extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Out the static model class
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
		return 'out';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, content, place, company, next, start_time, end_time, create_time, cost', 'required'),
			array('user_id, next', 'numerical', 'integerOnly'=>true),
			array('content, place', 'length', 'max'=>255),
      		array('type, delay', 'length', 'max'=>256),
			array('company', 'length', 'max'=>80),
      		array('type', 'length', 'max'=>12),
			array('status', 'length', 'max'=>7),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('out_id, user_id, content, place, company, transport, status, next, start_time, end_time, create_time, cost', 'safe', 'on'=>'search'),
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
            'user' => array(self::BELONGS_TO, 'Users' , 'user_id'),
            'members'=>array(self::HAS_MANY, 'OutMember','out_id'),
            'logs' =>array(self::HAS_MANY, 'OutLog', 'out_id','condition'=>"approver_id != :user_id", 'params'=>array(':user_id'=>Yii::app()->session['user_id'])),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'out_id' => 'Out',
			'user_id' => 'User',
			'content' => 'Content',
			'place' => 'Place',
			'company' => 'Company',
			'transport' => 'Transport',
			'status' => 'Status',
			'next' => 'Next',
      		'plan'=>'Plan',
      		'delay'=>'Delay',
      		'type'=>'Type',
			'start_time' => 'Start Time',
			'end_time' => 'End Time',
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

		$criteria->compare('out_id',$this->out_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('place',$this->place,true);
		$criteria->compare('company',$this->company,true);
		$criteria->compare('transport',$this->transport,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('next',$this->next);
		$criteria->compare('start_time',$this->start_time,true);
		$criteria->compare('end_time',$this->end_time,true);

		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
  
   /**
     *添加出差申请表
     */
	public static function addOut($data)
	{
	   try
        {
            $data['total_days'] =  Out::countDays($data['start_time'],$data['end_time'],$data['date_type']);
            $model = new Out();
            foreach($data as $key => $row)
            {
                $model->$key = $row;

            }
            $model->save();
            Helper::processSaveError($model);
            //$transaction ->commit();
            return $model;
           
        }
       catch(Exception $e)
        {
        	//$transaction ->rollBack();
        }
        return false;
    }

    /**
     *处理出差记录 static
     *@param array $data
     *@param string $model
     *@return int
     */
    public static function processOut($model, $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model->out_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }


    
    /**
     *通知审批的人
     */
    public function noticeHeads($out,$status='已通过')
    {
        $type = ($out->type == 'out') ? '外出申请':'出差申请';
        $content = "{$out->user->cn_name}的{$type}单{$status}";
        foreach($this->logs as $row)
        {
            $user = Users::model()->findByPk($row->approver_id);
            Out::outNotice($out,$user,'heads_ceo');
            Out::outMail($out,$user,'heads_ceo');
        }
    }

    /**
     *通知申请人出差申请单审批结果
     *@param object out
     */
    public function noticeSelf($out,$status = '已通过')
    {
        $user = Users::model()->findByPk($out->user->user_id);
        Out::outNotice($out,$user,'self');
        Out::outMail($out,$user,'self');
        if($_members = $out->members)
        {
            if(count($_members) > 1)
            {
                foreach($_members as $_row)
                {
                    if($_row->user_id != $out->user_id)
                    {
                        Out::outNotice($out,$_row->user,'member');
                        Out::outMail($out,$_row->user,'member');
                    }
                }
            }
        }
    }

    /**
     *通知行政助理备案
     */
    public function noticeCcommissioner($out, $status = '已通过')
    {
        $user = Users::getCcommissioner();
        Out::outNotice($out,$user,'hr');
        Out::outMail($out,$user,'hr'); 
    }

    /**
     *发送通知的事务
     */
    public static function noticeHeadsTransaction($out , $status='已通过')
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
          $out->noticeHeads($out, $status);
          $out->noticeSelf($out, $status);
          if($out->status == 'success')
          {
              $out->noticeCcommissioner($out,$status);
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
    *同意 -- 审批流程
    **/
    public static function ApproveAgree($out)
    {
        try
        {
            $procedure_list = CJSON::decode($out->procedure_list, true);
            if(empty($procedure_list) || (Yii::app()->session['user_id'] == end($procedure_list))) {
                Out::processOut($out , array('next'=>0,'status'=>'success') ); 
                OutLog::addLog(array('out_id' => $out->out_id,'approver_id' => Yii::app()->session['user_id'] , 'status' => 'agree' , 'create_time'=> date("Y-m-d H:i:s")));
                Out::noticeHeadsTransaction($out , '已通过');
            }
            else {
                foreach ($procedure_list as $key => $value) {
                    if($out->next == $value)
                        break;
                }
                $next = $procedure_list[$key + 1];
                Out::processOut($out , array('next'=>$next) ); 
                OutLog::addLog(array('out_id' => $out->out_id,'approver_id' => Yii::app()->session['user_id'], 'status' => 'agree' , 'create_time'=> date("Y-m-d H:i:s"))); 

                $user = Users::model()->findByPk($next);
                Out::outNotice($out,$user,'heads');
                Out::outMail($out,$user,'heads');
            }
            return true;
        }
        catch(Exception $e)
        {
          // $transaction ->rollBack();
        }
        return false;
    }

    /**
    *出差的审批流程
    *@param  array $procedure
    **/
    public static function procedure($out)
    {
        $procedure = array();
        if ( $procedure_list = CJSON::decode($out->procedure_list, true) ) {
            $logs = OutLog::model()->findAll("out_id = :out_id order by create_time asc",array(':out_id'=>$out->out_id));
            $reject_flag = false;
            $count_id = 0;
            foreach ($procedure_list as $row) {
                $count_id = $count_id + 1;

                $tmp = array('department'=>'', 'name'=>'', 'status'=>'wait');
                if ( $user_info = Users::model()->findByPk($row) ) {
                    $tmp['department'] = $user_info->department->name;
                    $tmp['name'] = $user_info->cn_name;
                    if($reject_flag) {
                        $tmp['status'] = "reject";
                    }
                    else {
                        foreach ($logs as $log) {
                            if($log->approver_id == $row) {
                                $tmp['status'] = $log->status;
                                if($log->status == "reject")
                                    $reject_flag = true;
                            }
                        }
                    }
                    $procedure[] = $tmp;
                }
            }
        }
        return $procedure;
    }

    /**
    *求出差的中文类型
    *@param object $out
    *@return varchar 
    **/
    public static function cnOutType($out)
    {
        #array('business'=>'商务','meeting'=>'会议');之前设计为枚举类
        if($out->type == 'business')
        {
            return '商务';
        }
        elseif($out->type == 'meeting')
        {
            return '会议洽谈';
        }
        elseif($out->type == 'out')
        {
            return '外出申请';
        }
        else
        {
            return $out->type;
        }
    }

    /**
    *发送出差的邮件
    *$out_type 请假的消息类型 ，本人:self，通知下一位审批heads ,人事备案:hr ，通知审批的人和ceo:heads_ceo
    **/
    public static function outMail($out, $user, $out_type)
    {   
        //if($user->online == 'off')
        if(true)
        {
            $host =Yii::app()->getRequest()->getHostInfo();   //发送邮件
            $start_time = date('Y-m-d H:i',strtotime($out->start_time));
            $end_time = date('Y-m-d H:i',strtotime($out->end_time));
            $type = Out::cnOutType($out);
            $array = array('success'=>'已通过','reject'=>'未通过','wait'=>'已提交');
            $cn_status = $array[$out->status];
            $email = $user->email;
            $url = ($out_type == 'heads')? "<a href='{$host}/oa/outMsg/out/{$out->out_id}'>" : "<a href='{$host}/user/outMsg/out/{$out->out_id}'>";
            $applyType = ($out->type == 'out') ? '' : '出差申请单';
            switch ($out_type)
            {
                case 'self':
                  $subject = "你申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}{$cn_status}";
                  break;  
                case 'heads':
                  $subject = ($out->status == 'success')? "{$out->user->cn_name}提交 {$type} {$applyType}，累计{$out->total_days}天" : "{$out->user->cn_name}提交 {$type} {$applyType}，累计{$out->total_days}天,请尽快审批";
                  break;
                case 'hr':
                  $subject = "{$out->user->cn_name}申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}已通过，请备案";
                  break; 
                case 'heads_ceo':
                  $subject = "{$out->user->cn_name}申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}{$cn_status}";
                  break;  
                case 'member':

                  $subject = "{$out->user->cn_name}给你申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}{$cn_status}";
                  break;
                default:
                  $subject = "{$out->content}";
            }

            $message = "<b>姓名:</b> {$out->user->cn_name}<br><b>时间:</b> {$start_time}到{$end_time}，共{$out->total_days}天<br><b>出差类型:</b> {$type}<br>
                        <b>出差地点:</b>{$out->place}<br><b>对方公司名称：</b>{$out->company}<br><b>交通工具:</b>{$out->transport}<br>
                        <b>费用:</b> {$out->cost}<br><b>出差事由：</b>{$out->content}<br><b>出差行程：</b>{$out->plan} ";
            if($_members = $out->members)
            {
                if(count($_members) > 1)
                {
                    $message .= "<br><b>同行人:</b>";
                    foreach($_members as $_row)
                    {
                        $message .= " {$_row->user->cn_name}";
                    }
                }
            }
            $message .= "<br> <b>详情:</b> 请{$url}登录</a>查看";
            $arr = array('user_id'=>Yii::app()->session['user_id'], 'sender_email'=>'hr@shanyougame.com', 'receive_email'=>$email, 
                            'subject'=>$subject, 'message'=>$message,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s') );
            $mail_id = Mail::createMail($arr);
            return $mail_id;
        }
        return true;
    }

    /**
    *发送出差的消息
    *@param object $leave,$user,$leave_type
    *@param $out_type 请假的消息类型 ，本人:self，通知下一位审批heads ,人事备案:hr ，通知审批的人和ceo:heads_ceo
    **/
    public static function outNotice($out,$user,$out_type)
    {
        $start_time = date('Y-m-d H:i',strtotime($out->start_time));
        $end_time = date('Y-m-d H:i',strtotime($out->end_time));
        $type = Out::cnOutType($out);
        $array = array('success'=>'已通过','reject'=>'未通过','wait'=>'已提交');
        $cn_status = $array[$out->status];
        $email = $user->email;
        $url = ($out_type == 'heads')? "/oa/outMsg/out/{$out->out_id}" : "/user/outMsg/out/{$out->out_id}";
        $applyType = ($out->type == 'out') ? '' : '出差申请单';

        switch ($out_type)
        {
            case 'self':
                $title = "你申请的{$type} {$applyType} {$cn_status}";
                $content = "你申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}{$cn_status}，累计{$out->total_days}天";
                break;
            case 'heads':
                $title = ($out->status == 'success') ? "{$out->user->cn_name}提交 {$type} {$applyType}" : "{$out->user->cn_name}提交 {$type} {$applyType},请尽快审批";
                $content = "{$out->user->cn_name}的 {$type} {$applyType}，时间:{$start_time}到{$end_time},累计{$out->total_days}天";
                break;
            case 'hr':
                $title = "{$out->user->cn_name}申请的 {$type} {$applyType}已通过，请备案";
                $content = "{$out->user->cn_name}申请的 {$type} {$applyType}已通过，时间: {$start_time}到{$end_time} ,累计{$out->total_days}天，请备案";
                break; 
            case 'heads_ceo':
                $title = "{$out->user->cn_name}申请的 {$type} {$applyType}{$cn_status}";
                $content = "{$out->user->cn_name}申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}{$cn_status}，累计{$out->total_days}天";
                break;
            case 'member':
                $title = "{$out->user->cn_name}给你申请的 {$type} {$applyType}{$cn_status}";
                $content = "{$out->user->cn_name}给你申请的 {$start_time}到{$end_time} 的 {$type} {$applyType}{$cn_status}，累计{$out->total_days}天";
                break;
            default:
                $title = $content = "{$out->content}";
        }

        if($_members = $out->members)
        {
            if(count($_members) > 1)
            {
                $content .= ",同行人:";
                foreach($_members as $_row)
                {
                    $content .= " {$_row->user->cn_name}";
                }
                $content .="。";
            }
        }
        
        return  Notice::addNotice(array('user_id'=>$user->user_id, 'content'=>$content, 'url'=>$url, 'status'=>'wait', 'type'=>'out','title'=>$title, 'create_time'=>date('Y-m-d H:i:s')));
    }    

    /**
     *添加出差事务
     */
    public static function addOutTransaction($user, $content, $company, $place, $transport,$cost,$start_time,$end_time,$delay,$plan, $type, $date_type, $member)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $out_value = ( $type == 'out' ) ? 0 : 1;
            $procedure_list = Procedure::getProcedure('out', $out_value ,$user->user_id);
            $procedure_list = Procedure::removeRepeat($procedure_list, $user->user_id);
            if(empty($procedure_list)) {
                $next = 0;
                $status ='success';
            }
            else {
                $next = $procedure_list[0];
                $status ='wait';
            }

            $out = Out::addOut( array( 'user_id'=>$user->user_id, 'content'=>$content, 'company'=>$company, 
                'place'=>$place,'transport'=>self::json_transport($transport), 'cost'=>$cost, 'status'=>$status, 
                'start_time'=>$start_time, 'procedure_list'=>CJSON::encode($procedure_list),
                'end_time'=>$end_time,'create_time'=>date('Y-m-d H:i:s'), 'next'=>$next, 'reason'=>'',
                'delay'=>$delay, 'plan'=>$plan, 'type'=>$type, 'date_type'=>$date_type)
            );

            //发送给自己及同行的人
            if(!empty($member))
            {
                $member = array_unique($member);
                $time = date('Y-m-d H:i:s');
                foreach($member as $row)
                {
                    if(!OutMember::processMember(new OutMember(), array('out_id'=>$out->out_id, 'user_id'=>$row,'create_time'=>$time)))
                        throw new Exception('-1');
                }
                foreach($member as $_row)
                {
                    if($_row != $user->user_id)
                    {
                        $_user = Users::model()->findByPk($_row);
                        if(!Out::outNotice($out,$_user,'member'))
                            throw new Exception('-1');
                        if(!Out::outMail($out,  $_user,'member'))
                            throw new Exception('-1');
                    }
                }
            }
            Out::outNotice($out,$user,'self');
            if( empty($procedure_list) )
            {
                $ceo = Users::getCeo(); //发送给CEO
                if($user->leadId != $ceo->user_id)
                {
                    Out::outNotice($out,$ceo,'heads_ceo');
                    Out::outMail($out,$ceo,'heads_ceo');
                }
                if($commissioner = Users::getCcommissioner())
                {
                    Out::outNotice($out,$commissioner,'hr');
                    Out::outMail($out,$commissioner,'hr');
                }
            }
            else {          //发送给下一个审批者
                $next_uer = Users::model()->findByPk($procedure_list[0]);
                Out::outNotice($out,$next_uer,'heads');
                Out::outMail($out,$next_uer,'heads');
            }
            $transaction->commit();
            return $out;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }

    /**
     *计算出差时间
     *@param string $start
     *@param string $end
     *@param stirng $type ENUM('normal','aternoon','morning')
     *@return int
     */
    public static function countDays($start,$end, $type='normal')
    {
        $count = 0; //有效的工作天数
        $_start = date("Y-m-d",strtotime($start));
        $_end   = date("Y-m-d",strtotime($end));
        $_start_time = date("H:i",strtotime($start));
        $_end_time   = date("H:i",strtotime($end));
        for($i= $_start; $i<= $_end; $i = date('Y-m-d', strtotime('+1days', strtotime($i))))
        {
            if($type == 'normal')
            {
                $count ++;
                if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
                {
                    $count -= 0.5;
                }
            }
            else
            {
                    $count += 0.5;
            }
        }
        return $count;	
    }


    /**
     *计算本年度的出差天数
     */
    public static function countOutDays($user, $type = 'year')
    {
        if($type == 'year')
        {
            $start = date('Y-01-01 00:00:00');
            $end   = date('Y-12-31 23:59:59');
        }
        else
        {
            $start = date('Y-m-01 00:00:00');
            $end   = date('Y-m-t 23:59:59');
        }
        $sql = "select sum(total_days) as sum from `out` join out_member on (out.out_id = out_member.out_id) where out_member.user_id = :user_id and `out`.status = 'success' and `out`.start_time >= :start and `out`.end_time <= :end;";
        return Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user->user_id, ':start'=>$start, ':end'=>$end));
    }
    
    /**
     *交通方式的json编译，不编译中文
     */
    public static function json_transport($arr)
    {
        if(empty($arr)) return '';
        foreach($arr as $key => $row)
        {
            $arr[$key] = urlencode($row);
        }
        return urldecode(json_encode($arr));
    }

    /**
     *获取出差列表
     */
    public static function getOutList($user_id, $limit, $offset)
    {
        $sql = "select `out`.* from `out` join out_member on (out.out_id = out_member.out_id) where out_member.user_id = :user_id order by create_time desc limit :limit offset :offset; ";
        return Out::model()->findAllBySql($sql, array(':user_id'=>$user_id, ':limit'=>$limit, ':offset'=>$offset));
    }
    /**
     *获取出差条数
     */
    public static function getOutListCount($user_id)
    {
        $sql = "select count(*) from `out` join out_member on (out.out_id = out_member.out_id) where out_member.user_id = :user_id";
        return Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user_id));
    }


}
