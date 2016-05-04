<?php

/**
 * This is the model class for table "recruit_condition".
 *
 * The followings are the available columns in table 'recruit_condition':
 * @property integer $id
 * @property integer $recruit_id
 * @property string $gender
 * @property integer $age
 * @property string $education
 * @property string $professional
 * @property string $computer
 * @property string $mandarin
 * @property string $cantonese
 * @property string $foreign
 * @property string $residence
 * @property string $create_time
 */
class RecruitCondition extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return RecruitCondition the static model class
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
		return 'recruit_condition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('recruit_id, gender, age, education,  computer, mandarin, cantonese, foreign, residence, create_time', 'required'),
			array('recruit_id, age', 'numerical', 'integerOnly'=>true),
			array('gender', 'length', 'max'=>4),
			array('education', 'length', 'max'=>13),
			array('professional', 'length', 'max'=>80),
			array('computer, mandarin, cantonese, foreign', 'length', 'max'=>7),
			array('residence', 'length', 'max'=>8),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, recruit_id, gender, age, education, professional, computer, mandarin, cantonese, foreign, residence, create_time', 'safe', 'on'=>'search'),
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
			'recruit_id' => 'Recruit',
			'gender' => 'Gender',
			'age' => 'Age',
			'education' => 'Education',
			'professional' => 'Professional',
			'computer' => 'Computer',
			'mandarin' => 'Mandarin',
			'cantonese' => 'Cantonese',
			'foreign' => 'Foreign',
			'residence' => 'Residence',
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
		$criteria->compare('recruit_id',$this->recruit_id);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('age',$this->age);
		$criteria->compare('education',$this->education,true);
		$criteria->compare('professional',$this->professional,true);
		$criteria->compare('computer',$this->computer,true);
		$criteria->compare('mandarin',$this->mandarin,true);
		$criteria->compare('cantonese',$this->cantonese,true);
		$criteria->compare('foreign',$this->foreign,true);
		$criteria->compare('residence',$this->residence,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加招聘条件
     */
    public static function addRecruitCondition($model , $data)
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
}
