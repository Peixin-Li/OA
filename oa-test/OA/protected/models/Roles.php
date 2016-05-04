<?php

/**
 * This is the model class for table "roles".
 *
 * The followings are the available columns in table 'roles':
 * @property integer $id
 * @property string $role_name
 * @property integer $user_id
 * @property string $status
 */
class Roles extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Roles the static model class
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
		return 'roles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('role_name, user_id', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('role_name, status', 'length', 'max'=>7),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, role_name, user_id, status', 'safe', 'on'=>'search'),
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
			'role_name' => 'Role Name',
			'user_id' => 'User',
			'status' => 'Status',
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
		$criteria->compare('role_name',$this->role_name,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public static function Check_role($role, $user) {
        $result = Roles::model()->find("role_name=:role and user_id=:user_id and status=:status",
            array(':status'=>'enable', ':user_id'=>$user->user_id, ':role'=>$role));
        if ($result)
            return True;
        else {
            $super_flag = Roles::model()->find("role_name=:role and user_id=:user_id and status=:status",
                array(':status'=>'enable', ':role'=>'super', ':user_id'=>$user->user_id));
            if ($super_flag)               // super 具有所有权限
                return True;
            else
                return false;
        }
    }

    //查找用户是否在Rolse表中，并且状态为enable
    public static function Check_User_in_roles($user) {
        $result = Roles::model()->find("user_id=:user_id and status=:status",
            array(':status'=>'enable', ':user_id'=>$user->user_id));
        if ($result)
            return True;
        else
            return false;
    }

    //获取角色的用户列表
    public static function getRolesUser() {
    	$role_users_id = array();
    	$role_users = array();
        $result = Roles::model()->findAll(array(
        	'select'=>array('user_id'),
			'order' => 'id ASC',
  			'condition' => 'status=:state',
  			'params' => array(':state'=>'enable'),
        ));
        foreach ($result as $row) {
        	if (!in_array($row->user_id, $role_users_id)) {
        		$role_users_id[] = $row->user_id;
        		$user_object = Users::model()->find('status=:status and user_id=:user_id',array(':status'=>'work', ':user_id'=>$row->user_id));
        		if ($user_object){
        			$role_users[] = $user_object;
        		}
        	}
        }
        return $role_users;
    }

    /**
     *操作人员修改
     */
    public static function processRoles($model, $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }

            $exit_item = Roles::model()->find('role_name=:role_name and user_id=:user_id',
                array(':role_name'=>$model->role_name, ':user_id'=>$model->user_id));

            if ($exit_item) {
                $model = $exit_item;
                $model->status = 'enable';
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
