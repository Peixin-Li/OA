<?php

/**
 * This is the model class for table "fee_division_tpl".
 *
 * The followings are the available columns in table 'fee_division_tpl':
 * @property integer $tpl_id
 * @property string $name
 * @property string $fee_div_p
 * @property string $update_time
 */
class FeeDivisionTpl extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return FeeDivisionTpl the static model class
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
		return 'fee_division_tpl';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, fee_div_p', 'length', 'max'=>255),
			array('update_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('tpl_id, name, fee_div_p, update_time', 'safe', 'on'=>'search'),
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
			'tpl_id' => 'Tpl',
			'name' => 'Name',
			'fee_div_p' => 'Fee Div P',
			'update_time' => 'Update Time',
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

		$criteria->compare('tpl_id',$this->tpl_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('fee_div_p',$this->fee_div_p,true);
		$criteria->compare('update_time',$this->update_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    //验证分摊比例是否正确
    public static function verification($fee_div_p) {
        try {
            $count = 0;
            foreach ($fee_div_p as $project_id => $persent) {
                if(!$project_info = Project::model()->findBypk($project_id) )
                    return false;
                elseif ($project_info->enable!='yes')
                    return false;
                elseif ( !preg_match('/^\d+$/', $persent) )
                    return false;
                $count += (int)$persent;
            }
            if($count !=100)
                return false;
        }
        catch(Exception $e)
        {
            @Yii::log($e , 'info' , 'operation.fee_division_tpl');
            return false;
        }
        return true;
    }

    //添加费用分摊模板
    public static function addTpl($data) {
        $data['fee_div_p'] = CJSON::encode($data['fee_div_p']);

        $new_obj = new self;
        foreach ($data as $key => $value) {
            $new_obj[$key] = $value;
        }
        return $new_obj->save();
    }

    //修改项目相关信息
    public static function delProjectInfo($project_id) {
        $transaction = Yii::app()->db->beginTransaction();
        try{
            $tpl_list = self::model()->findAll('enable =:t_enable', array(':t_enable'=>'yes'));
            foreach ($tpl_list as $row) {
                $div_p_arr= CJSON::decode($row->fee_div_p, true);
                $old_len = count($div_p_arr);
                $count = 0;
                foreach ($div_p_arr as $key => $value) {
                    if( $key == $project_id) {
                        unset($div_p_arr[$project_id]);
                        continue;
                    }
                    $count += (int)$value;
                }
                $new_len = count($div_p_arr);
                if(empty($new_len)) {
                    self::model()->deleteByPk($row->tpl_id);
                }
                else if($count==100) {
                    return true;
                }
                else {
                    $scale = 100 / $count;
                    $items_num = 1;
                    $new_count = 0;
                    foreach ($div_p_arr as $key => $value) {
                        if( $items_num==$new_len ) {
                            $div_p_arr[$key] = 100 - $new_count;
                            break;
                        }
                        $div_p_arr[$key] = (int)($scale * $div_p_arr[$key]);
                        $items_num += 1;
                        $new_count += $div_p_arr[$key];
                    }
                    self::model()->updateByPk($row->tpl_id, array('fee_div_p'=>CJSON::encode($div_p_arr) ) );
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


}