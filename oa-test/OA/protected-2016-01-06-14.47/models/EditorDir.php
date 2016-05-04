<?php

/**
 * This is the model class for table "editor_dir".
 *
 * The followings are the available columns in table 'editor_dir':
 * @property integer $dir_id
 * @property string $dir_name
 * @property integer $parent_id
 * @property integer $create_user
 * @property string $create_time
 * @property string $update_time
 * @property string $status
 */
class EditorDir extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EditorDir the static model class
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
		return 'editor_dir';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dir_name, create_time', 'required'),
			array('parent_id, create_user', 'numerical', 'integerOnly'=>true),
			array('dir_name', 'length', 'max'=>60),
			array('status', 'length', 'max'=>7),
			array('update_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('dir_id, dir_name, parent_id, create_user, create_time, update_time, status', 'safe', 'on'=>'search'),
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
			'dir_id' => 'Dir',
			'dir_name' => 'Dir Name',
			'parent_id' => 'Parent',
			'create_user' => 'Create User',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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

		$criteria->compare('dir_id',$this->dir_id);
		$criteria->compare('dir_name',$this->dir_name,true);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('create_user',$this->create_user);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public static function createEditorDir($data)
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
            return $model->dir_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    public static function findSubDir($dir_id) {
    	$result = array(intval($dir_id));
    	$dir_list = EditorDir::model()->findAll('status=:status', array(':status'=>'enable'));
    	do {
    		$flag = false;
    		foreach ($dir_list as $key => $value) {
    			if(in_array($value['parent_id'], $result) ) {
    				$result[] = intval($value['dir_id']);
    				unset($dir_list[$key]);
    				$flag = true;
    			}
    		}
    	} while($flag);
    	return $result;
    }

    public static function findParentDir($dir_id) {
        $parent_dir_id = $dir_id;
        $result = array();
        $max_count = 20;
        $count = 1;

        while (($parent_dir_id != 0)&&($count < $max_count)) {
            $tmp = EditorDir::model()->findByPk($parent_dir_id);
            if($tmp) {
                $result[] = $tmp->attributes;
            }
            $parent_dir_id = $tmp['parent_id'];
            $count = $count + 1;
        }
        return $result;
    }

    public static function getFullPathName($dir_id) {
        $parent_dir_id = $dir_id;
        $result = "";
        $max_count = 20;
        $count = 1;

        while (($parent_dir_id != 0)&&($count < $max_count)) {
            $tmp = EditorDir::model()->findByPk($parent_dir_id);
            if($tmp) {
                $result .= '/'. $tmp['dir_name'];
            }
            $parent_dir_id = $tmp['parent_id'];
            $count = $count + 1;
        }
        return $result;
    }
}