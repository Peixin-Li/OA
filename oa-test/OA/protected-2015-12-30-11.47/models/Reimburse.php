<?php

/**
 * This is the model class for table "reimburse".
 *
 * The followings are the available columns in table 'reimburse':
 * @property integer $id
 * @property string $category
 * @property string $total
 * @property integer $user_id
 * @property integer $receipt_num
 * @property string $way
 * @property string $bank_info
 * @property string $payee
 * @property string $borrow_amount
 * @property string $update_time
 * @property string $create_time
 */
class Reimburse extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Reimburse the static model class
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
		return 'reimburse';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category, total, user_id, receipt_num, way, update_time, create_time', 'required'),
			array('user_id, receipt_num', 'numerical', 'integerOnly'=>true),
			array('category', 'length', 'max'=>12),
			array('total, borrow_amount', 'length', 'max'=>10),
			array('way', 'length', 'max'=>8),
			array('bank_info, payee', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, category, total, user_id, receipt_num, way, bank_info, payee, borrow_amount, update_time, create_time', 'safe', 'on'=>'search'),
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
            'details'=>array(self::HAS_MANY,'ReimburseDetail', 'reimburse_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category' => 'Categroy',
			'total' => 'Total',
			'user_id' => 'User',
			'receipt_num' => 'Receipt Num',
			'way' => 'Way',
			'bank_info' => 'Bank Info',
			'payee' => 'Payee',
			'borrow_amount' => 'Borrow Amount',
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
		$criteria->compare('category',$this->category,true);
		$criteria->compare('total',$this->total,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('receipt_num',$this->receipt_num);
		$criteria->compare('way',$this->way,true);
		$criteria->compare('bank_info',$this->bank_info,true);
		$criteria->compare('payee',$this->payee,true);
		$criteria->compare('borrow_amount',$this->borrow_amount,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *处理报销记录 static
     *@param array $data
     *@param string $model
     *@return int
     */
    public static function processReimburse($model, $data)
    {
        try
        {
            $data = array_merge(array('bank_info'=>'','payee'=>'','borrow_amount'=>''), $data);
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
            //var_dump($e->getMessage());
        }
        return false;
    }

    /**
     *添加报销的记录
     */
    public static function addReimburse($data,$details, $user)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
          //添加银行卡信息
            if( (!empty($data['bank_code'])) && (!BankCard::model()->find("bank_code=:code",array(':code'=>$data['bank_code']))) )
            {
                BankCard::processBankCard(new BankCard(), array('user_id'=>$user->user_id, 'bank_info'=>$data['bank_info'], 'bank_code'=>$data['bank_code'], 'payee'=>$data['payee'], 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s')));
            }
          //找出 category total 
          $data['bank_info'] .= " {$data['bank_code']}";
          unset($data['bank_code']);
          $data['category'] = '';
          $data['total'] = 0;
          $data['user_id'] = $user->user_id;
          $data['update_time'] = $data['create_time'] = date('Y-m-d H:i:s');
          foreach($details as $row)
          {
              if(!$detail = GoodsApplyDetail::model()->findByPk($row['apply_detail_id']))
              {
                  throw new Exception('-1');
              }
              if(empty($data['category']))
              {
                  $data['category'] = $detail['category'];
              }
              elseif($data['category'] != $detail['category'])
              {
                  throw new Exception('category error');
              }
              $data['total'] += $row['amount'];
          }
          $id = self::processReimburse(new Reimburse(), $data);
          unset($row);
          foreach($details as $row)
          {
              $row['reimburse_id'] = $id;
              $row['update_time'] = $row['create_time'] = date('Y-m-d H:i:s');
              if(!ReimburseDetail::processReimburseDetail(new ReimburseDetail(), $row))
              {
                  throw new Exception('-1');
              }
              //报申请单设置成已经报销的状态
              GoodsApplyDetail::model()->updateByPk($row['apply_detail_id'], array('is_reimburse'=>'yes'));
          }
          //减去该部门的预算的该类型预算
          $budget= Budget::model()->find("department_id=:department_id and type=:type and year=:year", array(':department_id'=>$user->department_id, ':type'=>$data['category'], ':year'=>date('Y')));
          $budget->cost += $data['total'];
          $budget->save();
          Helper::processSaveError($budget);
          //处理费用报表
          if($report = ExpenseReport::model()->find("year=:year and month=:month and department_id=:department_id",array(':year'=>date('Y'),':month'=>date('m'), ':department_id'=>$user->department_id)))
          {
              //如果有就更新
              $report->$data['category']+= $data['total'];
              $report->save();
              Helper::processSaveError($report);
          }
          else//没有就添加一条记录
          {
              $expense_data = array('year'=>date('Y'), 'month'=>date('m'),'department_id'=>$user->department_id, 'create_time'=>date('Y-m-d H:i:s'), 'update_time'=>date('Y-m-d H:i:s'), 'description'=>'');
              $types = array('office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other');
              foreach($types as $type)
              {
                   if($type == $data['category'])
                   {
                     $expense_data[$type] =$data['total']; 
                   }
                   else
                   {
                     $expense_data[$type] = 0; 
                   }
              }
              if(!ExpenseReport::processReport(new ExpenseReport(), $expense_data))
              {
                  throw new Exception('-1');
              }
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

}
