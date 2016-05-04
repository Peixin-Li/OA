<?php

/**
 * This is the model class for table "editor_roles".
 *
 * The followings are the available columns in table 'editor_roles':
 * @property integer $id
 * @property string $type
 * @property integer $user_id
 * @property string $create_time
 */
class EditorRoles extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EditorRoles the static model class
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
		return 'editor_roles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, user_id, create_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>8),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, user_id, create_time', 'safe', 'on'=>'search'),
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
			'type' => 'Type',
			'user_id' => 'User',
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
		$criteria->compare('type',$this->type,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public static function createEditorRoles($data)
    {
        try
        {
            $model = new self();
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

    public static function addRoles($user_id, $role) {
    	if ($editor_role = EditorRoles::model()->find('type = :type', array(':type'=>$role) ) ) {
    		$editor_role['user_id'] = $user_id;
    		$editor_role->save();
    		return $editor_role['id'];
    	}
    	else {
    		$data = array('type'=>$role, 'user_id'=>$user_id, 'create_time'=>date('Y-m-d H:i:s'));
    		return EditorRoles::createEditorRoles($data);
    	}
    }

    public static function getApproverId() {
        $editor_approver = EditorRoles::model()->find('type = :type', array(':type'=>'approver'));
        return $editor_approver['user_id'];
    }

    public static function getAdminId() {
        $editor_approver = EditorRoles::model()->find('type = :type', array(':type'=>'admin'));
        return $editor_approver['user_id'];
    }

    public static function checkUserInRolesTable($user_id) {   //判断用户是否在角色表里面
        if (EditorRoles::model()->find('user_id=:user_id', array(":user_id"=>$user_id)) )
            return true;
        else
            return false;
    }
}