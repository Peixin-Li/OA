<?php

/**
 * This is the model class for table "family".
 *
 * The followings are the available columns in table 'family':
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $relation
 * @property string $work
 * @property string $phone
 * @property string $create_time
 */
class Family extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Family the static model class
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
		return 'family';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, name, relation, work, phone, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('name, relation, work', 'length', 'max'=>45),
			array('phone', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, name, relation, work, phone, create_time', 'safe', 'on'=>'search'),
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
			'name' => 'Name',
			'relation' => 'Relation',
			'work' => 'Work',
			'phone' => 'Phone',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('relation',$this->relation,true);
		$criteria->compare('work',$this->work,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

     /**
     *添加入职信息
     *@param array $data
     *@return boolean
     */
    public static function processFamily($model, $data)
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
            if($family = Family::model()->find('user_id=:user_id', array(':user_id'=>$user_id)))
            {
                Family::model()->deleteAll('user_id=:user_id', array(':user_id'=>$user_id));
            }
            foreach($data as $row)
            {
                $row = array_merge(array('user_id'=>$user_id, 'create_time'=>date('Y-m-d H:i:s')), $row);
                Family::processFamily(new Family() , $row);
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
