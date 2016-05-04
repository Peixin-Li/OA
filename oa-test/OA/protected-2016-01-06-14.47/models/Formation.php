<?php

/**
 * This is the model class for table "formation".
 *
 * The followings are the available columns in table 'formation':
 * @property integer $formation_id
 * @property integer $department_id
 * @property string $title
 * @property integer $number
 * @property string $create_time
 */
class Formation extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Formation the static model class
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
		return 'formation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('department_id, title, number, create_time', 'required'),
			array('department_id, number', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('formation_id, department_id, title, number, create_time', 'safe', 'on'=>'search'),
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
			'formation_id' => 'Formation',
			'department_id' => 'Department',
			'title' => 'Title',
			'number' => 'Number',
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

		$criteria->compare('formation_id',$this->formation_id);
		$criteria->compare('department_id',$this->department_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('number',$this->number);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *处理formation
     */
    public static function processFormation($model, $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model->formation_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *查找配置和修改编制
     *找不到则添加编制
     *@param array data = array('title','number','department_id')
     */
    public static function processTransaction($data)
    {
        if($formation = Formation::model()->find('department_id=:department_id and title=:title', array(':title'=>$data['title'],':department_id'=>$data['department_id'])))
        {
            return Formation::processFormation($formation , array('number'=>$data['number']));
        }
        else
        {
            return Formation::processFormation(new Formation() , array('department_id'=>$data['department_id'],'title'=>$data['title'],'number'=>$data['number'],'create_time'=>date('Y-m-d H:i:s')));
        }
    }

    /**
     *首先判断这个部门是否存在,然后根据招聘申请修改编制表
     */
    public static function createDepartment($apply)
    {
        //如果部门存在就根据职位名称修改人数或添加职位
        //如果不存在就创建部门和创建职位
        if($department = Department::model()->find('parent_id=:id and name=:name', array(':id'=>$apply->parent_id, ':name'=>$apply->department)))
        {
            //如果存在该职位
            if($title = Formation::model()->find('department_id=:department_id and title=:title', array(':department_id'=>$department->department_id, ':title'=>$apply->title)))
            {
                $title->number += $apply->number;
                $title->save();
                Helper::processSaveError($title);
                return true;
            }
            else
            {
                return Formation::processFormation(new Formation(), array('department_id'=>$department->department_id, 'title'=>$apply->title, 'number'=>$apply->number, 'create_time'=>date('Y-m-d H:i:s')));
            }
        }//先创建出这个部门
        elseif($id = Department::createDepartment(array('name'=>$apply->department,'parent_id'=>$apply->parent_id,'admin'=>'0')))
        {
             return Formation::processFormation(new Formation(), array('department_id'=>$id, 'title'=>$apply->title, 'number'=>$apply->number, 'create_time'=>date('Y-m-d H:i:s')));
        }
        return false;
    }

    /**
     *求出目前该部门该编制缺编的人数
     */
    public static function getVacancyNum($formation)
    {
        if(empty($formation)) return false;
        $count = self::getWorkNum($formation->department_id, $formation->title);
        return $formation->number - $count;
    }

    /**
     *获取目前该部门该职位在职人数
     */
    public static function getWorkNum($department_id, $title)
    {
        $sql="SELECT count(*) as num FROM users where status='work' and department_id = :department_id and title=:title;";
        return $count =  Yii::app()->db->createCommand($sql)->queryScalar(array(':department_id'=>$department_id, ':title'=>$title));
    }

    /**
     *获取所有编制人数
     */
    public static function getTotalFormationNum()
    {
        $department_a = Department::model()->findAll(array(
            'select' => array('department_id'),
            'condition' => 'department_status = :status',
            'params' => array(':status'=>'display')
        ));

        $formation_a = Formation::model()->findAll();

        $count = 0;
        foreach ($department_a as $d_row) {
            foreach ($formation_a as $f_row) {
                if($d_row['department_id'] == $f_row['department_id'])
                    $count += $f_row['number'];
            }
        }
        return $count;
    }
}
