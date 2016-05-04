<?php

/**
 * This is the model class for table "work".
 *
 * The followings are the available columns in table 'work':
 * @property integer $id
 * @property integer $user_id
 * @property string $start_date
 * @property string $end_date
 * @property string $company
 * @property string $title
 * @property string $create_time
 */
class Work extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Work the static model class
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
		return 'work';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, start_date, end_date, company, title, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('company, title', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, start_date, end_date, company, title, create_time', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'company' => 'Company',
			'title' => 'Title',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('company',$this->company,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    /**
     *添加入职工作信息
     *@param array $data
     *@return boolean
     */
    public static function processDateWork($model, $data)
    {
       $data['start_date'] .= '-01';
       $data['end_date'] .= '-01';
       return self::processWork($model, $data);
    }
    /**
     *添加入职工作信息
     *@param array $data
     *@return boolean
     */
    public static function processWork($model, $data)
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
     *处理添加入职家庭信息的接口
     *@param string $user_id
     *@param array  $data
     *@return boolean
     */
    public static function processTransaction($user_id, $data)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            if($family = Work::model()->find('user_id=:user_id', array(':user_id'=>$user_id)))
            {
                Work::model()->deleteAll('user_id=:user_id', array(':user_id'=>$user_id));
            }
            foreach($data as $row)
            {
                $row = array_merge(array('user_id'=>$user_id, 'create_time'=>date('Y-m-d H:i:s')), $row);
                $row['start_date'] .= '-01';
                $row['end_date'] .= '-01';
                Work::processWork(new Work() , $row);
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
