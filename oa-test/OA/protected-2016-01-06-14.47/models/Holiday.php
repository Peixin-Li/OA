<?php

/**
 * This is the model class for table "holiday".
 *
 * The followings are the available columns in table 'holiday':
 * @property integer $id
 * @property string $holiday
 * @property string $status
 * @property string $comment
 * @property string $create_time
 */
class Holiday extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Holiday the static model class
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
		return 'holiday';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('holiday, comment, create_time', 'required'),
			array('status', 'length', 'max'=>4),
			array('comment', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, holiday, status, comment, create_time', 'safe', 'on'=>'search'),
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
			'holiday' => 'Holiday',
			'status' => 'Status',
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
		$criteria->compare('holiday',$this->holiday,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	*计算工作日
	*@param array $date array('start_time'=>'xxxx-xx-xx xx-xx', 'end_time'=>'xxxx-xx-xx xx-xx')
	*@param date $start,$end 
	**/
	public static function countDays($start,$end)
	{ 
        $count = 0; //有效的工作天数
        $_start = date("Y-m-d",strtotime($start));
        $_end   = date("Y-m-d",strtotime($end));
        $_start_time = date("H:i",strtotime($start));
        $_end_time   = date("H:i",strtotime($end));
        
        for($i= $_start; $i<= $_end; $i = date('Y-m-d', strtotime('+1days', strtotime($i))))
        {
            if($temp = Holiday::model()->find("holiday=:holiday", array(':holiday'=>$i)))
            {
                if($temp->status == 'work')
                {
                    $count ++;
                    if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
                    {
                        $count -= 0.5;
                    }
                }
            }
            else if(date('w', strtotime($i)) >= 1 and date('w', strtotime($i)) <= 5)
            {
                    $count ++;
                    if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
                    {
                        $count -= 0.5;
                    }
            }
        }
        
        return $count;	
    }
    
    /**
	*计算周末或者节假日的加班调休天数
	*@param array $date array('start_time'=>'xxxx-xx-xx xx-xx', 'end_time'=>'xxxx-xx-xx xx-xx')
	*@param date $start,$end 
	**/
	public static function countRestDays($start,$end)
	{ 
        $count = 0; //有效的工作天数
        $_start = date("Y-m-d",strtotime($start));
        $_end   = date("Y-m-d",strtotime($end));
        $_start_time = date("H:i",strtotime($start));
        $_end_time   = date("H:i",strtotime($end));
        
        for($i= $_start; $i<= $_end; $i = date('Y-m-d', strtotime('+1days', strtotime($i))))
        {
            if($temp = Holiday::model()->find("holiday=:holiday", array(':holiday'=>$i)))
            {
                if($temp->status == 'legal')
                {
                    $count += 3;
                    if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
                    {
                        $count -= 1.5;
                    }
                   
                }
                elseif($temp->status == 'rest')
                {
                    $count ++;
                    if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
                    {
                        $count -= 0.5;
                    }
                }
            }
            else if(date('w', strtotime($i)) == 6 || date('w', strtotime($i)) == 0)
            {
                    $count ++;
                    if(($i == $_start && $_start_time == '13:30') || ($i == $_end   &&  $_end_time  == '12:00'))
                    {
                        $count -= 0.5;
                    }
            }
        }
        
        return $count;	
	}

}
