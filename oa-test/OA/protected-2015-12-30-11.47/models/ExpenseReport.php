<?php

/**
 * This is the model class for table "expense_report".
 *
 * The followings are the available columns in table 'expense_report':
 * @property integer $id
 * @property string $year
 * @property string $month
 * @property integer $department_id
 * @property string $office
 * @property string $welfare
 * @property string $travel
 * @property string $entertain
 * @property string $hydropower
 * @property string $intermediary
 * @property string $rental
 * @property string $test
 * @property string $outsourcing
 * @property string $property
 * @property string $repair
 * @property string $other
 * @property string $description
 * @property string $update_time
 * @property string $create_time
 */
class ExpenseReport extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ExpenseReport the static model class
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
		return 'expense_report';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('year, month, department_id, office, welfare, travel, entertain, hydropower, intermediary, rental, test, outsourcing, property, repair, other, update_time, create_time', 'required'),
			array('id, department_id', 'numerical', 'integerOnly'=>true),
			array('year', 'length', 'max'=>4),
			array('month', 'length', 'max'=>2),
			array('office, welfare, travel, entertain, hydropower, intermediary, rental, test, outsourcing, property, repair, other', 'length', 'max'=>10),
			array('description', 'length', 'max'=>500),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, year, month, department_id, office, welfare, travel, entertain, hydropower, intermediary, rental, test, outsourcing, property, repair, other, description, update_time, create_time', 'safe', 'on'=>'search'),
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
            'department'=>array(self::BELONGS_TO, 'Department', 'department_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'year' => 'Year',
			'month' => 'Month',
			'department_id' => 'Department',
			'office' => 'Office',
			'welfare' => 'Welfare',
			'travel' => 'Travel',
			'entertain' => 'Entertain',
			'hydropower' => 'Hydropower',
			'intermediary' => 'Intermediary',
			'rental' => 'Rental',
			'test' => 'Test',
			'outsourcing' => 'Outsourcing',
			'property' => 'Property',
			'repair' => 'Repair',
			'other' => 'Other',
			'description' => 'Description',
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
		$criteria->compare('year',$this->year,true);
		$criteria->compare('month',$this->month,true);
		$criteria->compare('department_id',$this->department_id);
		$criteria->compare('office',$this->office,true);
		$criteria->compare('welfare',$this->welfare,true);
		$criteria->compare('travel',$this->travel,true);
		$criteria->compare('entertain',$this->entertain,true);
		$criteria->compare('hydropower',$this->hydropower,true);
		$criteria->compare('intermediary',$this->intermediary,true);
		$criteria->compare('rental',$this->rental,true);
		$criteria->compare('test',$this->test,true);
		$criteria->compare('outsourcing',$this->outsourcing,true);
		$criteria->compare('property',$this->property,true);
		$criteria->compare('repair',$this->repair,true);
		$criteria->compare('other',$this->other,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *处理费用报表 static
     *@param array $data
     *@param string $model
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
            //var_dump($e->getMessage());
        }
        return false;
    }

    /**
     *设置说明
     */
    public static function  processReportDescription($model, $data)
    {
        try
        {
            $transaction=self::model()->dbConnection->beginTransaction();
            $commons = array('总经理办公室','IT运维部','人事行政部','商务部','项目管理部');
            //如果是公共部门
            if(in_array($model->department->name , $commons))
            {
                foreach($commons as $department_name)
                {
                    if($department_name != $model->department->name)
                    {
                        $_department = Department::model()->find("name=:name",array(':name'=>$department_name));
                        if($_model = ExpenseReport::model()->find("year=:year and month=:month and department_id=:department_id",array(':year'=>$model->year, ':month'=>$model->month, ':department_id'=>$_department->department_id)))
                        {
                            foreach($data as $key => $row)
                            {
                                $_model->$key = $row;
                            }
                            $_model->save();
                            Helper::processSaveError($_model);
                        }
                        else
                        {
                              $expense_data = array('year'=>$model->year, 'month'=>$model->month,'department_id'=>$_department->department_id, 'create_time'=>date('Y-m-d H:i:s'), 'update_time'=>date('Y-m-d H:i:s'), 'description'=>'');
                              $types = array('office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other');
                              foreach($types as $type)
                              {
                                     $expense_data[$type] = 0; 
                              }
                              foreach($data as $key => $row)
                              {
                                    $expense_data[$key] = $row;
                              }
                              if(!ExpenseReport::processReport(new ExpenseReport(), $expense_data))
                              {
                                  throw new Exception('-1');
                              }
                        }
                    }
                }
            }
            if(!self::processReport($model, $data))
            {
                throw new Exception('-1');
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
