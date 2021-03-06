<?php

/**
 * This is the model class for table "qualify_apply_log".
 *
 * The followings are the available columns in table 'qualify_apply_log':
 * @property integer $id
 * @property integer $apply_id
 * @property integer $user_id
 * @property string $action
 * @property string $qualify_date
 * @property integer $qualify_salary
 * @property string $comment
 * @property string $create_time
 */
class QualifyApplyLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return QualifyApplyLog the static model class
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
		return 'qualify_apply_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('apply_id, user_id, action, qualify_date, qualify_salary, comment, create_time', 'required'),
			array('apply_id, user_id, qualify_salary', 'numerical', 'integerOnly'=>true),
			array('action', 'length', 'max'=>7),
			array('comment', 'length', 'max'=>5000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, apply_id, user_id, action, qualify_date, qualify_salary, comment, create_time', 'safe', 'on'=>'search'),
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
			'apply_id' => 'Apply',
			'user_id' => 'User',
			'action' => 'Action',
			'qualify_date' => 'Qualify Date',
			'qualify_salary' => 'Qualify Salary',
			'comment' => 'Comment',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('qualify_date',$this->qualify_date,true);
		$criteria->compare('qualify_salary',$this->qualify_salary);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    
    /**
     *添加log
     */
    public static function addLog($data)
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
            return $model->id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }
}
