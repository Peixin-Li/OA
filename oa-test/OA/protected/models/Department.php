<?php

/**
 * This is the model class for table "department".
 *
 * The followings are the available columns in table 'department':
 * @property integer $department_id
 * @property string $name
 * @property integer $parent_id
 * @property integer $admin
 */
class Department extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Department the static model class
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
		return 'department';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, admin', 'required'),
			array('parent_id, admin', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('department_id, name, parent_id, admin', 'safe', 'on'=>'search'),
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
            'leader'=>array(self::BELONGS_TO, 'Users', 'admin'),
            'parent'=>array(self::BELONGS_TO, 'Department' , 'parent_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'department_id' => 'Department',
			'name' => 'Name',
			'parent_id' => 'Parent',
			'admin' => 'Admin',
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

		$criteria->compare('department_id',$this->department_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('admin',$this->admin);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *找出人事行政部
     */ 
    public static function adminDepartment()
    {
        if($operator = Operator::model()->find(array('select'=>'object_id','condition'=>'type=:type','params'=>array(':type'=>'admin_department'))))
        {
            return Department::model()->find('department_id=:id' , array(':id'=>$operator->object_id));
        }
        return false;
    }

    /**
     *找出IT运维部
     */
    public static function operationDepartment()
    {
        if($operator = Operator::model()->find(array('select'=>'object_id','condition'=>'type=:type','params'=>array(':type'=>'operation_department'))))
        {
            return Department::model()->find('department_id=:id' , array(':id'=>$operator->object_id));
        }
        return false;
    }

    /**
     *根据部门id获取子部门
     */
    public static function subdepartment($department_id){
        if (!$department_id)
            return null;
        $all_department = Department::model()->findAll('department_status=:status' , array(':status'=>'display'));
        $department_sub = array($department_id);
        do{
            $flag = 0;
            foreach ($all_department as $key=>$row) {
                if (in_array($row['parent_id'], $department_sub)) {
                    $department_sub[] = $row['department_id'];
                    unset($all_department[$key]);
                    $flag = 1;
                }
            }
        } while ($flag);
        return $department_sub;
    }

    /**
     *获取部门管理员的用户对象
     */
    public function getAdminUser()
    {
        if(empty($this->admin)) return Users::getCeo()->user_id;
        return Users::model()->findByPk($this->admin, 'status=:status', array(':status'=>'work'));
    }

    /**
     *更新部门信息
     */
    public static function updateDepartment($model , $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return true;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *新建部门
     */
    public static function createDepartment($data)
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
            return $model->department_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *算出该部门的编制人数
     */
    public function getFormationCount()
    {
        $sql = "SELECT sum(number) FROM formation where department_id = '{$this->department_id}';";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }

    public function getFormationCountByid($d_id) {
        $sql = "SELECT sum(number) FROM formation where department_id = '{$d_id}';";
        return Yii::app()->db->createCommand($sql)->queryScalar();
    }
}
