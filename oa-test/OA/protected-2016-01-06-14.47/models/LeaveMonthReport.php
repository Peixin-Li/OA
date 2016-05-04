<?php

/**
 * This is the model class for table "leave_month_report".
 *
 * The followings are the available columns in table 'leave_month_report':
 * @property integer $id
 * @property integer $user_id
 * @property string $month
 * @property double $casual
 * @property double $sick
 * @property double $funeral
 * @property double $marriage
 * @property double $maternity
 * @property double $annual
 * @property double $compensatory
 * @property double $others
 * @property string $content
 * @property string $create_time
 */
class LeaveMonthReport extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LeaveMonthReport the static model class
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
		return 'leave_month_report';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, month, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('casual, sick, funeral, marriage, maternity, annual, compensatory, others', 'numerical'),
			array('content', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, month, casual, sick, funeral, marriage, maternity, annual, compensatory, others, content, create_time', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'month' => 'Month',
			'casual' => 'Casual',
			'sick' => 'Sick',
			'funeral' => 'Funeral',
			'marriage' => 'Marriage',
			'maternity' => 'Maternity',
			'annual' => 'Annual',
			'compensatory' => 'Compensatory',
			'others' => 'Others',
			'content' => 'Content',
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
		$criteria->compare('month',$this->month,true);
		$criteria->compare('casual',$this->casual);
		$criteria->compare('sick',$this->sick);
		$criteria->compare('funeral',$this->funeral);
		$criteria->compare('marriage',$this->marriage);
		$criteria->compare('maternity',$this->maternity);
		$criteria->compare('annual',$this->annual);
		$criteria->compare('compensatory',$this->compensatory);
		$criteria->compare('others',$this->others);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


   /**
     *添加一个请假报表
     */
    public static function processLeave($model, $data)
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
     *计算从开始到结束时间请了几天假
     */
    public static function calcMonthDays($user, $start)
    {
        $sql = "select sum(casual+sick+funeral+marriage+maternity+annual+compensatory+others)  FROM leave_month_report where user_id = :user_id and month=:month;";
        return  Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user->user_id, ':month'=>$start));#->queryScalar();
    }


    /**
     *计算从OA开始运营至今请了几天假
     */
    public static function calcLeaveDays($user)
    {
        $sql = "select sum(casual+sick+funeral+marriage+maternity+annual+compensatory+others)  FROM leave_month_report where user_id = :user_id";
        return  Yii::app()->db->createCommand($sql)->queryScalar(array(':user_id'=>$user->user_id));#->queryScalar();
    }



}
