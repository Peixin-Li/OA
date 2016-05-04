<?php

/**
 * This is the model class for table "qualify_report".
 *
 * The followings are the available columns in table 'qualify_report':
 * @property integer $id
 * @property integer $apply_id
 * @property integer $serial
 * @property string $content
 * @property integer $proportion
 * @property string $reference
 * @property string $quantity
 * @property integer $completion_rate
 * @property integer $delay_rate
 * @property integer $rework_rate
 * @property string $create_time
 */
class QualifyReport extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return QualifyReport the static model class
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
		return 'qualify_report';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('apply_id, serial, content, proportion, quantity, completion_rate, delay_rate, rework_rate, create_time', 'required'),
			array('apply_id, serial, proportion, completion_rate, delay_rate, rework_rate', 'numerical', 'integerOnly'=>true),
			array('content, reference', 'length', 'max'=>100),
			array('quantity', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, apply_id, serial, content, proportion, reference, quantity, completion_rate, delay_rate, rework_rate, create_time', 'safe', 'on'=>'search'),
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
			'serial' => 'Serial',
			'content' => 'Content',
			'proportion' => 'Proportion',
			'reference' => 'Reference',
			'quantity' => 'Quantity',
			'completion_rate' => 'Completion Rate',
			'delay_rate' => 'Delay Rate',
			'rework_rate' => 'Rework Rate',
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
		$criteria->compare('serial',$this->serial);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('proportion',$this->proportion);
		$criteria->compare('reference',$this->reference,true);
		$criteria->compare('quantity',$this->quantity,true);
		$criteria->compare('completion_rate',$this->completion_rate);
		$criteria->compare('delay_rate',$this->delay_rate);
		$criteria->compare('rework_rate',$this->rework_rate);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * 验证提交的数值数据
     * *@param array  $data     array(array('serial','content','proportion','reference','quantity','completion_rate','delay_rate','rework_rate') )                  序号     工作内容 占比  参考内容 工作量 完成率 延误率 返工率
     *$reference可以不写
     */
    public static function validateData($data)
    {
        if(empty($data))
        {
            return false;
        }
        $sum = 0;
        foreach($data as $row)
        {
            // if(!preg_match('/^[1-9]\d*$/', $row['serial']) || empty($row['content']) || !preg_match('/^[1-9]\d*$/', $row['proportion']) || empty($row['quantity']) || !preg_match('/^\d+$/', $row['completion_rate']) || !preg_match('/^\d+$/', $row['delay_rate']) || !preg_match('/^\d+$/', $row['rework_rate']))

            if(!preg_match('/^[1-9]\d*$/', $row['serial']) || empty($row['content']) || !preg_match('/^[1-9]\d*$/', $row['proportion']) || !preg_match('/^\d+$/', $row['completion_rate']) || !preg_match('/^\d+$/', $row['rework_rate']))
            {
                return false;
            }
            if($row['completion_rate'] + $row['rework_rate'] != 100)
            {
                return false;
            }
            $sum += $row['proportion'];
        }
        if($sum != '100')
        {
            return false;
        }
        return true;
    }

    /**
     *添加述职表记录（多条）
     *@param sting $id   申请ID
     *@param array $data 述职表信息  array(array('serial','content','proportion','reference','quantity','completion_rate','delay_rate','rework_rate') )            
     *@return boolean
     */
    public static function addReport($id , $data)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            $temp = array('apply_id'=>$id , 'create_time'=>date('Y-m-d H:i:s'));
            foreach($data as $row)
            {   
                $row['reference'] = "no";
                $row['quantity'] = "no";
                $row['delay_rate'] = 0;
                self::processReport(new self(), array_merge($temp, $row));
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
     *处理述职表的信息
     *@param object $model 模型
     *@param array  $data  数据
     *@return int
     */
    public static function processReport($model, $data)
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
}
