<?php

/**
 * This is the model class for table "activity_join".
 *
 * The followings are the available columns in table 'activity_join':
 * @property integer $id
 * @property integer $activity_id
 * @property integer $user_id
 * @property string $tag
 * @property string $update_time
 * @property string $create_time
 */
class ActivityJoin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ActivityJoin the static model class
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
		return 'activity_join';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('activity_id, user_id, create_time', 'required'),
			array('activity_id, user_id', 'numerical', 'integerOnly'=>true),
			array('tag', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, activity_id, user_id, tag, update_time, create_time', 'safe', 'on'=>'search'),
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
			'activity_id' => 'Activity',
			'user_id' => 'User',
			'tag' => 'Tag',
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
		$criteria->compare('tag',$this->tag,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *处理参加活动表
     *@param object $model
     *@param array $data
     *@return boolean
     */
    public static function processActivityJoin($model , $data)
    {
       try
       {
       		if(empty($model)) return false;
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
     *验证参加活动的ID
     */
    public static function validateIds($data)
    {
        if(empty($data)) return false;
        foreach($data as $row)
        {
            if(!preg_match('/^[1-9]\d*$/', $row))
            {
                return false;
            }
        }
        return true;
    }

    /**
     *标记活动参加
     *@param array $data array(1,2,3,5) Id的一维数组
     *@param string $tag enum('join','absent')
     */
    public static function tagActivityJoin($data, $tag, $id)
    {
        $transaction = Yii::app()->db->beginTransaction();
        try
        {
            foreach($data as $row)
            {
                if(!self::processActivityJoin(ActivityJoin::model()->findByPk($row), array('tag'=>$tag)))
                {
                	throw new Exception("Error", -1);
                }
            }
            //把活动标记成已经处理过了
            $model = Activity::model()->findByPk($id);
            $model->status = 'process';
            $model->save();
            Helper::processSaveError($model);
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }
}
