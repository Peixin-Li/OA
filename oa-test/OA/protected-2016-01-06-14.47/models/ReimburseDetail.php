<?php

/**
 * This is the model class for table "reimburse_detail".
 *
 * The followings are the available columns in table 'reimburse_detail':
 * @property integer $id
 * @property integer $reimburse_id
 * @property string $content
 * @property integer $apply_id
 * @property integer $apply_detail_id
 * @property string $have_receipt
 * @property string $amount
 * @property string $update_time
 * @property string $create_time
 */
class ReimburseDetail extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReimburseDetail the static model class
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
		return 'reimburse_detail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('reimburse_id, content, apply_id, apply_detail_id, have_receipt, amount, update_time, create_time', 'required'),
			array('reimburse_id, apply_id, apply_detail_id', 'numerical', 'integerOnly'=>true),
			array('content', 'length', 'max'=>100),
			array('have_receipt', 'length', 'max'=>3),
			array('amount', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, reimburse_id, content, apply_id, apply_detail_id, have_receipt, amount, update_time, create_time', 'safe', 'on'=>'search'),
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
            'reimburse'=>array(self::BELONGS_TO, 'Reimburse', 'reimburse_id'),
            'apply_detail'=>array(self::BELONGS_TO,'GoodsApplyDetail', 'apply_detail_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'reimburse_id' => 'Reimburse',
			'content' => 'Content',
			'apply_id' => 'Apply',
			'apply_detail_id' => 'Apply Detail',
			'have_receipt' => 'Have Receipt',
			'amount' => 'Amount',
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
		$criteria->compare('reimburse_id',$this->reimburse_id);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('apply_id',$this->apply_id);
		$criteria->compare('apply_detail_id',$this->apply_detail_id);
		$criteria->compare('have_receipt',$this->have_receipt,true);
		$criteria->compare('amount',$this->amount,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    /**
     *处理报销详情记录 static
     *@param array $data
     *@param string $model
     *@return int
     */
    public static function processReimburseDetail($model, $data)
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
     *验证提交的数据
     *@param array $data
     *@------需要的字段----
     *content
     *apply_id
     *apply_detail_id
     *have_receipt
     *amount
     */
    public static function validateDetails($data)
    {
        if(empty($data) || !is_array($data)) return false;
        foreach($data as $row)
        {
            if(empty($row['content']) || !preg_match('/^\d+$/', $row['apply_id']) || !preg_match('/^\d+$/', $row['apply_detail_id']) || !in_array($row['have_receipt'], array('yes','no')) || !preg_match('/^\d+(\.\d+)?$/', $row['amount']))
            {
                return false;
            }
        }
        return true;
    }

    /**
     *查找权限
     */
    public static function validatePremission($data, $user_id)
    {
         if(empty($data) || !is_array($data)) return false;
        foreach($data as $row)
        {
            if(!$apply = GoodsApply::model()->findByPk($row['apply_id']))
            {
                return false;
            }
            if($apply->user_id != $user_id)
            {
                return false;
            }
            if($apply->status != 'success')
            {
                return false;
            }
            //如果已经报销 就不可以重复的报销
            if(!$apply_detail = GoodsApplyDetail::model()->findByPk($row['apply_detail_id']))
            {
                return false;
            }
            if($apply_detail->is_reimburse != 'no')
            {
                return false;
            }
        }
        return true;
    }

    /**
     *查找申请详情
     */
    public function getApplyDetail()
    {
    	return GoodsApplyDetail::model()->findByPk($this->apply_detail_id);
    }
}
