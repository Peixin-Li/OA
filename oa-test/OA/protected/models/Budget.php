<?php

/**
 * This is the model class for table "budget".
 *
 * The followings are the available columns in table 'budget':
 * @property integer $id
 * @property integer $department_id
 * @property string $type
 * @property string $total
 * @property string $cost
 * @property string $year
 * @property string $update_time
 * @property string $create_time
 */
class Budget extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Budget the static model class
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
		return 'budget';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('department_id, type, total, cost, year, update_time, create_time', 'required'),
			array('department_id', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>12),
			array('total, cost', 'length', 'max'=>10),
			array('year', 'length', 'max'=>4),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, department_id, type, total, cost, year, update_time, create_time', 'safe', 'on'=>'search'),
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
            'changes'=>array(self::HAS_MANY, 'BudgetChange', 'budget_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'department_id' => 'Department',
			'type' => 'Type',
			'total' => 'Total',
			'cost' => 'Cost',
			'year' => 'Year',
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
		$criteria->compare('department_id',$this->department_id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('total',$this->total,true);
		$criteria->compare('cost',$this->cost,true);
		$criteria->compare('year',$this->year,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *处理预算
     *@param object $model
     *@param array $data
     *@return boolean
     */
    public static function processBudget($model , $data)
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
     *添加年度预算
     */
    public static function addYearBudget($year, $data)
    {
        try
        {
           $cost = 0;
           $create_time = $update_time = date('Y-m-d H:i:s'); 
           $transaction=self::model()->dbConnection->beginTransaction();
           foreach($data as $type => $row)
           {
               foreach($row as $department_id => $total)
               {
                   //如果添加失败就抛出异常
                   if(!self::processBudget(new Budget() , array('department_id'=>$department_id, 'type'=>$type, 'total'=>$total, 'original'=>$total, 'cost'=>$cost,'year'=>$year, 'update_time'=>$update_time, 'create_time'=>$create_time)))
                   {
                       throw new Exception('-1');
                   }
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
    
    /**
     *更新年度预算
     */
    public static function updateYearBudget($year, $data)
    {
        try
        {
            $cost = 0;
           $time = date('Y-m-d H:i:s');
           $transaction=self::model()->dbConnection->beginTransaction();
           foreach($data as $type => $row)
           {
               foreach($row as $department_id => $total)
               {
                   $model = Budget::model()->find("department_id=:department_id and type=:type and year=:year", array(':department_id'=>$department_id, ':type'=>$type, ':year'=>$year)); 
                   // 如果没有这个部门的时候就插入一条数据
                   if($model == NULL){
                    //如果添加失败就抛出异常
                     if(!self::processBudget(new Budget() , array('department_id'=>$department_id, 'type'=>$type, 'total'=>$total, 'original'=>$total, 'cost'=>$cost,'year'=>$year, 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'))))
                     {
                         throw new Exception('-1');
                     }
                   }else{
                    //添加预算变更表日志
                     if($model->total != $total)
                     {
                         if(!BudgetChange::processBudgetChange(new BudgetChange(), array('budget_id'=>$model->id, 'amount'=>$total - $model->total, 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'))))
                         {
                             throw new Exception('-1');
                         }
                     }
                     //如果添加失败就抛出异常
                     if(!self::processBudget($model , array('update_time'=>$time, 'total'=>$total)))
                     {
                         throw new Exception('-1');
                     }
                   }
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


    /**
     *求出该部门的该类型的剩余预算
     */
    public static function getDepartmentTypeBudget($department_id, $type)
    {
        $year = date('Y-m-d');
        if($budget = Budget::model()->find("year=:year and department_id=:department_id and type=:type", array(':year'=>$year, ':department_id'=>$department_id, ':type'=>$type)))
        {
            return $budget->total - $budget->cost;
        }
        return false;
    }
}
