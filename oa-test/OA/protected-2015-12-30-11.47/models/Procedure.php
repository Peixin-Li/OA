<?php

/**
 * This is the model class for table "procedure".
 *
 * The followings are the available columns in table 'procedure':
 * @property integer $procedure_id
 * @property string $user_role
 * @property string $type
 * @property integer $value
 * @property integer $procedure_order
 */
class Procedure extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Procedure the static model class
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
		return 'procedure';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('value, procedure_order', 'numerical', 'integerOnly'=>true),
			array('user_role', 'length', 'max'=>8),
			array('type', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('procedure_id, user_role, type, value, procedure_order', 'safe', 'on'=>'search'),
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
			'procedure_id' => 'Procedure',
			'user_role' => 'User Role',
			'type' => 'Type',
			'value' => 'Value',
			'procedure_order' => 'Procedure Order',
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

		$criteria->compare('procedure_id',$this->procedure_id);
		$criteria->compare('user_role',$this->user_role,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('value',$this->value);
		$criteria->compare('procedure_order',$this->procedure_order);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	//根据指定的值获取流程
	public static function getProcedure($type, $value, $user_id) {
		$procedure_arr = array();
		$procedure_list = self::model()->findAll( array(
			'condition' =>'type=:t_type and value<=:t_value',
			'params' => array( ':t_type'=>$type, ':t_value'=>$value),
			'order' => 'procedure_order ASC',
		));

		$ceo_flag = false;                   //判断是否含CEO
		$hr_admin_flag = false;				 //是否包含人事主管

		$hr_admin_id = Users::getAdminId()->user_id;
		$ceo_id = Users::getCeo()->user_id;

		foreach ($procedure_list as $row) {
			$procedure_user_id = "";
			if($row->user_role == 'd_admin') {
				if( $t_user = Users::model()->findByPk($user_id) ) {
					$procedure_user_id = $t_user->leadId;
				}
			}
			elseif ($row->user_role == 'd2_admin') {
				if( $t_user = Users::model()->findByPk($user_id) ) {
					$t_user_leader = $t_user->leadId;					   //查询当前部门主管ID
					if($t2_user = Users::model()->findByPk($t_user_leader) ) {
						$procedure_user_id = $t2_user->leadId;
					}
				}
			}
			elseif($row->user_role == 'hr_admin') {    //需要人事主管审批
				$hr_admin_flag = true;
			}
			elseif($row->user_role == 'ceo') {
				$ceo_flag = true;
			}

			//判断该流程ID是否为CEO 或者 人事主管
			if($procedure_user_id == $hr_admin_id)
				$hr_admin_flag = true;
			elseif ($procedure_user_id == $ceo_id)
				$ceo_flag = true;
			//确保流程中ID不为空，且ID不重复
			elseif( (!empty($procedure_user_id))&&(!in_array($procedure_user_id, $procedure_arr)) )
				$procedure_arr[] = $procedure_user_id;
		}

		// 确保人事主管和CEO处于审批流程的末端
		if($hr_admin_flag)
			$procedure_arr[] = $hr_admin_id;
		if($ceo_flag)
			$procedure_arr[] = $ceo_id;

		// 如果当前用户是CEO，则只需要CEO审批即可
		if($user_id == $ceo_id)
			$procedure_arr = array($ceo_id);

		return $procedure_arr;
	}

	//去除数组中指定的元素
	public static function removeRepeat($arr ,$value) {
		$result_arr = array();
		foreach ($arr as $row) {
			if($row != $value)
				$result_arr[] = $row;
		}
		return $result_arr;
	}
}