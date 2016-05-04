<?php

/**
 * This is the model class for table "interest_team_budget".
 *
 * The followings are the available columns in table 'interest_team_budget':
 * @property integer $id
 * @property integer $team_id
 * @property string $year
 * @property string $total
 * @property string $cost
 * @property string $update_time
 * @property string $create_time
 */
class InterestTeamBudget extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return InterestTeamBudget the static model class
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
		return 'interest_team_budget';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('team_id, year, total, cost, update_time, create_time', 'required'),
			array('team_id', 'numerical', 'integerOnly'=>true),
			array('year', 'length', 'max'=>4),
			array('total, cost', 'length', 'max'=>8),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, team_id, year, total, cost, update_time, create_time', 'safe', 'on'=>'search'),
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
            'team'=>array(self::BELONGS_TO,'InterestTeam' , 'team_id'),
            'changes'=>array(self::HAS_MANY, 'InterestTeamBudgetChange', 'budget_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'team_id' => 'Team',
			'year' => 'Year',
			'total' => 'Total',
			'cost' => 'Cost',
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
		$criteria->compare('team_id',$this->team_id);
		$criteria->compare('year',$this->year,true);
		$criteria->compare('total',$this->total,true);
		$criteria->compare('cost',$this->cost,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


    /**
     *处理兴趣小组预算
     */
    public static function processTeamBudget($model, $data)
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
     *处理数组
     */
    public static function parseData($arr)
    {
        $data = array();
        if(empty($arr) || !is_array($arr)) return $data;
        foreach($arr as $row)
        {
            $data[$row['team_id']] = $row['total'];
        }
        return $data;
    }

    /**
     *验证此输入数据是否正确
     */
    public static function validateData($data)
    {
        if(empty($data)) return false;
        $teams = InterestTeam::model()->findAll();
        foreach($teams as $team)
        {
            if(!isset($data[$team->id])) return false;
            if(!preg_match('/^\d+$/',$data[$team->id])) return false; 
        }
        return true;
    }

    /**
     *批量添加预算
     *@param array $data array('team_id'=>'total',.......)
     *@param string $year
     *@return boolean
     */
    public static function batchAddBudget($data,$year)
    {
        try
        {
            $transaction = Yii::app()->db->beginTransaction();
            $time = date('Y-m-d H:i:s');
            foreach($data as $key => $row)
            {
                if(!InterestTeamBudget::processTeamBudget(new InterestTeamBudget(), array('team_id'=>$key, 'year'=>$year,'total'=>$row, 'original'=>$row, 'cost'=>0, 'update_time'=>$time, 'create_time'=>$time)))
                {
                    throw new Exception('-1');
                }
            }
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }


    /**
     *修改预算
     *@param array $data array('team_id'=>'total',.......)
     *@param string $year
     *@return boolean
     */
    public static function editBudget($data,$year)
    {
        try
        {
            $transaction = Yii::app()->db->beginTransaction();
            $time = date('Y-m-d H:i:s');
            foreach($data as $key => $row)
            {
                $model = self::model()->find("year=:year and team_id=:team_id",array(':year'=>$year,':team_id'=>$key));
                //添加预算变更表日志
                if($model->total != $row)
                {
                   if(!InterestTeamBudgetChange::processBudgetChange(new InterestTeamBudgetChange(), array('budget_id'=>$model->id, 'amount'=>$row - $model->total, 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'))))
                   {
                       throw new Exception('-1');
                   }
                }
                if(!self::processTeamBudget($model, array('total'=>$row,'update_time'=>$time)))
                {
                    throw new Exception('-1');
                }
            }
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        return false;
    }

    /**
     *获取组该年度的剩余预算
     *@param string $team_id
     *@return boolean true就是还有预算
     */
    public static function getYearBudget($team_id)
    {
        if(!$budget = self::model()->find("year=:year and team_id=:team_id",array(':year'=>date('Y'), ':team_id'=>$team_id)))
        {
            return false;
        }
        return ($budget->total - $budget->cost) > 0 ? true : false;
    }

}
