<?php

/**
 * This is the model class for table "project".
 *
 * The followings are the available columns in table 'project':
 * @property integer $project_id
 * @property string $serial_number
 * @property string $name
 * @property integer $public_ps
 * @property integer $department_id
 * @property integer $project_admin
 * @property string $enable
 */
class Project extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Project the static model class
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
		return 'project';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('public_ps, department_id, project_admin', 'numerical', 'integerOnly'=>true),
			array('serial_number, name', 'length', 'max'=>100),
			array('enable', 'length', 'max'=>3),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('project_id, serial_number, name, public_ps, department_id, project_admin, enable', 'safe', 'on'=>'search'),
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
            'admin'=>array(self::BELONGS_TO, 'Users', 'project_admin'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'project_id' => 'Project',
			'serial_number' => 'Serial Number',
			'name' => 'Name',
			'public_ps' => 'Public Ps',
			'department_id' => 'Department',
			'project_admin' => 'Project Admin',
			'enable' => 'Enable',
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

		$criteria->compare('project_id',$this->project_id);
		$criteria->compare('serial_number',$this->serial_number,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('public_ps',$this->public_ps);
		$criteria->compare('department_id',$this->department_id);
		$criteria->compare('project_admin',$this->project_admin);
		$criteria->compare('enable',$this->enable,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    //新建项目
    public static function addProject($data) {
        $new_project = new self;
        foreach ($data as $key => $value) {
            $new_project[$key] = $value;
        }
        return $new_project->save();
    }
}