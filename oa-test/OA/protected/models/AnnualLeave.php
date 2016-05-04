<?php

/**
 * This is the model class for table "annual_leave".
 *
 * The followings are the available columns in table 'annual_leave':
 * @property integer $id
 * @property integer $user_id
 * @property string $total
 * @property string $refresh_time
 * @property string $update_time
 * @property string $create_time
 */
class AnnualLeave extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AnnualLeave the static model class
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
		return 'annual_leave';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, total, refresh_time, update_time, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('total', 'length', 'max'=>3),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, total, refresh_time, update_time, create_time', 'safe', 'on'=>'search'),
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
			'total' => 'Total',
			'refresh_time' => 'Refresh Time',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('total',$this->total,true);
		$criteria->compare('refresh_time',$this->refresh_time,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

     /**
     *处理年假
     *@param object $model
     *@param array $data
     *@return boolean
     */
    public static function processAnnualLeave($model , $data)
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

}
