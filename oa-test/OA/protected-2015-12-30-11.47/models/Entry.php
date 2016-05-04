<?php

/**
 * This is the model class for table "entry".
 *
 * The followings are the available columns in table 'entry':
 * @property integer $entry_id
 * @property integer $user_id
 * @property string $nation
 * @property string $marital_status
 * @property string $fertility
 * @property string $id_number
 * @property string $education
 * @property string $professional
 * @property string $school
 * @property string $graduation_time
 * @property string $residence
 * @property string $residence_type
 * @property string $working_life
 * @property string $id_address
 * @property string $present_address
 * @property string $hobby
 * @property string $forte
 * @property string $emergency_contact
 * @property string $emergency_telephone
 * @property string $relation
 * @property string $emergency_address
 * @property string $create_time
 */
class Entry extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Entry the static model class
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
		return 'entry';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, nation, marital_status, id_number, education, professional, school, graduation_time, residence, working_life, id_address, present_address, emergency_contact, emergency_telephone, relation, emergency_address, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('nation', 'length', 'max'=>10),
			array('marital_status', 'length', 'max'=>9),
			array('fertility, working_life', 'length', 'max'=>3),
			array('id_number', 'length', 'max'=>18),
			array('education', 'length', 'max'=>13),
			array('professional, school', 'length', 'max'=>80),
			array('residence', 'length', 'max'=>150),
			array('hobby, forte, emergency_contact, relation', 'length', 'max'=>45),
			array('id_address, present_address, emergency_address', 'length', 'max'=>100),
			array('residence_type', 'length', 'max'=>5),
			array('emergency_telephone', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('entry_id, user_id, nation, marital_status, fertility, id_number, education, professional, school, graduation_time, residence, residence_type, working_life, id_address, present_address, hobby, forte, emergency_contact, emergency_telephone, relation, emergency_address, create_time', 'safe', 'on'=>'search'),
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
			'entry_id' => 'Entry',
			'user_id' => 'User',
			'nation' => 'Nation',
			'marital_status' => 'Marital Status',
			'fertility' => 'Fertility',
			'id_number' => 'Id Number',
			'education' => 'Education',
			'professional' => 'Professional',
			'school' => 'School',
			'graduation_time' => 'Graduation Time',
			'residence' => 'Residence',
			'residence_type' => 'Residence Type',
			'working_life' => 'Working Life',
			'id_address' => 'Id Address',
			'present_address' => 'Present Address',
			'hobby' => 'Hobby',
			'forte' => 'Forte',
			'emergency_contact' => 'Emergency Contact',
			'emergency_telephone' => 'Emergency Telephone',
			'relation' => 'Relation',
			'emergency_address' => 'Emergency Address',
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

		$criteria->compare('entry_id',$this->entry_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('nation',$this->nation,true);
		$criteria->compare('marital_status',$this->marital_status,true);
		$criteria->compare('fertility',$this->fertility,true);
		$criteria->compare('id_number',$this->id_number,true);
		$criteria->compare('education',$this->education,true);
		$criteria->compare('professional',$this->professional,true);
		$criteria->compare('school',$this->school,true);
		$criteria->compare('graduation_time',$this->graduation_time,true);
		$criteria->compare('residence',$this->residence,true);
		$criteria->compare('residence_type',$this->residence_type,true);
		$criteria->compare('working_life',$this->working_life,true);
		$criteria->compare('id_address',$this->id_address,true);
		$criteria->compare('present_address',$this->present_address,true);
		$criteria->compare('hobby',$this->hobby,true);
		$criteria->compare('forte',$this->forte,true);
		$criteria->compare('emergency_contact',$this->emergency_contact,true);
		$criteria->compare('emergency_telephone',$this->emergency_telephone,true);
		$criteria->compare('relation',$this->relation,true);
		$criteria->compare('emergency_address',$this->emergency_address,true);
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
    public static function processEntry($model, $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return $model->entry_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }
}
