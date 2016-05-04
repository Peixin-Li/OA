<?php

/**
 * This is the model class for table "editor_apply".
 *
 * The followings are the available columns in table 'editor_apply':
 * @property integer $apply_id
 * @property integer $editor_id
 * @property integer $user_id
 * @property integer $next
 * @property string $status
 * @property string $create_time
 * @property string $update_time
 */
class EditorApply extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EditorApply the static model class
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
		return 'editor_apply';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('editor_id, user_id, dir_id,next, create_time', 'required'),
			array('editor_id, user_id, next', 'numerical', 'integerOnly'=>true),
			array('status', 'length', 'max'=>7),
			array('update_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('apply_id, editor_id, user_id, dir_id,next, status, create_time, update_time', 'safe', 'on'=>'search'),
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
			'apply_id' => 'Apply',
			'editor_id' => 'Editor',
			'user_id' => 'User',
            'dir_id' => 'Dir',
			'next' => 'Next',
			'status' => 'Status',
			'create_time' => 'Create Time',
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

		$criteria->compare('apply_id',$this->apply_id);
		$criteria->compare('editor_id',$this->editor_id);
		$criteria->compare('user_id',$this->user_id);
        $criteria->compare('dir_id',$this->dir_id);
		$criteria->compare('next',$this->next);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public static function createEditorApply($data)
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
            return $model->apply_id;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    public static function successApplyPublish($editor_apply) {
    	$editor_id = $editor_apply['editor_id'];
        $dir_id = $editor_apply['dir_id'];
    	if ( $editor = Editor::model()->findByPk($editor_id) ) {
            
            if ($editor['parent_id']==0) {                              //判断文件是否为发布之后再次编辑的文档
            	$editor['approve_user_id'] = 0;
                $editor['dir_id'] = $dir_id;
            	$editor['status'] = 'success';
            	$editor->save();
            }
            else if ($parent_editor = Editor::model()->findByPk($editor['parent_id'])) {  //文档为已经发布的文档再编辑
                $editor['display'] = 'no';
                $editor['update_time'] = date("Y-m-d H:i:s");
                
                $parent_editor['update_time'] = date("Y-m-d H:i:s");
                $parent_editor['title'] = $editor['title'];
                $parent_editor['real_file_name'] = $editor['real_file_name'];
                $parent_editor['dir_id'] = $dir_id;

                $editor->save();
                $parent_editor->save();
            }
            $editor_apply['update_time'] = date('Y-m-d H:i:s');
            $editor_apply['status'] = 'success';
            $editor_apply['next'] = 0;
            $editor_apply->save();
            $src_dir = Yii::app()->params['editorTmpFilePath'] .$editor['real_file_name'];
            $dis_dir = Yii::app()->params['editorSuccessFilePath'] .$editor['real_file_name'];
            copy($src_dir, $dis_dir);
            return true;
        }
    	return false;
    }

    public static function rejectApplyPublish($editor_apply) {
        $editor_id = $editor_apply['editor_id'];
        $dir_id = $editor_apply['dir_id'];
        if ( $editor = Editor::model()->findByPk($editor_id) ) {
            $editor['approve_user_id'] = 0;
            $editor['status'] = 'wait';
            $editor->save();

            $editor_apply['update_time'] = date('Y-m-d H:i:s');
            $editor_apply['status'] = 'reject';
            $editor_apply['next'] = 0;
            $editor_apply->save();
            return true;
        }
        return false;
    }

}