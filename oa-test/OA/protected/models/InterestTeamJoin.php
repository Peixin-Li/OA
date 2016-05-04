<?php

/**
 * This is the model class for table "interest_team_join".
 *
 * The followings are the available columns in table 'interest_team_join':
 * @property integer $id
 * @property integer $activity_id
 * @property integer $user_id
 * @property string $status
 * @property string $update_time
 * @property string $create_time
 */
class InterestTeamJoin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return InterestTeamJoin the static model class
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
		return 'interest_team_join';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('activity_id, user_id, update_time, create_time', 'required'),
			array('activity_id, user_id', 'numerical', 'integerOnly'=>true),
			array('status', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, activity_id, user_id, status, update_time, create_time', 'safe', 'on'=>'search'),
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
            'user'=>array(self::BELONGS_TO, 'Users','user_id'),
            'activity'=>array(self::BELONGS_TO, 'InterestTeamActivity','activity_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'activity_id' => 'Activity',
			'user_id' => 'User',
			'status' => 'Status',
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
		$criteria->compare('activity_id',$this->activity_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    /**
     *处理兴趣小组预算
     */
    public static function processJoinActivity($model, $data)
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
     *判断时间
     */
    public static function isTimeDuplicate($time, $user_id)
    {
        if(!$joins = InterestTeamJoin::model()->findAll("user_id=:user_id and status=:status",array(':user_id'=>$user_id,':status'=>'enroll')))
        {
            return false; //没有冲突
        }
        foreach($joins as $join)
        {
            if($join->activity->activity_time == $time)
            {
                return true;
            }
        }
        return false;
    }
}
