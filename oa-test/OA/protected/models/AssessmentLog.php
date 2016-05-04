<?php

/**
 * This is the model class for table "assessment_log".
 *
 * The followings are the available columns in table 'assessment_log':
 * @property integer $id
 * @property integer $assessment_id
 * @property integer $user_id
 * @property integer $periods
 * @property string $probation_salary
 * @property string $official_salary
 * @property string $opinion
 * @property string $action
 * @property string $create_time
 */
class AssessmentLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AssessmentLog the static model class
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
		return 'assessment_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('assessment_id, user_id, periods, probation_salary, official_salary, opinion, action, create_time', 'required'),
			array('assessment_id, user_id, periods', 'numerical', 'integerOnly'=>true),
			array('probation_salary, official_salary', 'length', 'max'=>7),
			array('opinion', 'length', 'max'=>300),
			array('action', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, assessment_id, user_id, periods, probation_salary, official_salary, opinion, action, create_time', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'assessment_id' => 'Assessment',
			'user_id' => 'User',
			'periods' => 'Periods',
			'probation_salary' => 'Probation Salary',
			'official_salary' => 'Official Salary',
			'opinion' => 'Opinion',
			'action' => 'Action',
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
		$criteria->compare('assessment_id',$this->assessment_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('periods',$this->periods);
		$criteria->compare('probation_salary',$this->probation_salary,true);
		$criteria->compare('official_salary',$this->official_salary,true);
		$criteria->compare('opinion',$this->opinion,true);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加日志
     */
    public static function addLog($data)
    {
        try
        {
            $model = new self();
            foreach($data as $key => $row)
            {
                $model -> $key = $row;
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
