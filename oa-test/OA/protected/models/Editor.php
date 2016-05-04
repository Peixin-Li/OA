<?php

/**
 * This is the model class for table "editor".
 *
 * The followings are the available columns in table 'editor':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $owner_id
 * @property string $title
 * @property integer $file_version
 * @property string $real_file_name
 * @property string $create_time
 * @property string $update_time
 * @property integer $last_editor_id
 * @property integer $approve_user_id
 * @property integer $lock_user
 * @property string $lock_status
 * @property string $status
 * @property string $display
 */
class Editor extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Editor the static model class
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
		return 'editor';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('owner_id, title, real_file_name, create_time, update_time, last_editor_id, approve_user_id', 'required'),
			array('dir_id,parent_id, owner_id, file_version, last_editor_id, approve_user_id, lock_user', 'numerical', 'integerOnly'=>true),
			array('title, real_file_name', 'length', 'max'=>200),
            array('c_editor', 'length', 'max'=>500),
			array('lock_status', 'length', 'max'=>6),
			array('status', 'length', 'max'=>7),
			array('display', 'length', 'max'=>3),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, dir_id, parent_id, owner_id, c_editor, title, file_version, real_file_name, create_time, update_time, last_editor_id, approve_user_id, lock_user, lock_status, status, display', 'safe', 'on'=>'search'),
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
			'parent_id' => 'Parent',
            'dir_id' => 'Dir',
			'owner_id' => 'Owner',
            'c_editor' => 'C',
			'title' => 'Title',
			'file_version' => 'File Version',
			'real_file_name' => 'Real File Name',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'last_editor_id' => 'Last Editor',
			'approve_user_id' => 'Approve User',
			'lock_user' => 'Lock User',
			'lock_status' => 'Lock Status',
			'status' => 'Status',
			'display' => 'Display',
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
        $criteria->compare('dir_id',$this->dir_id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('owner_id',$this->owner_id);
        $criteria->compare('c_editor',$this->c_editor);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('file_version',$this->file_version);
		$criteria->compare('real_file_name',$this->real_file_name,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);
		$criteria->compare('last_editor_id',$this->last_editor_id);
		$criteria->compare('approve_user_id',$this->approve_user_id);
		$criteria->compare('lock_user',$this->lock_user);
		$criteria->compare('lock_status',$this->lock_status,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('display',$this->display,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	public static function editNotice($editor, $user_id, $apply_id)
    {
        $url = "/oa/editorMsg/editor_apply_id/{$apply_id}" ;

        $apply = EditorApply::model()->findByPk($apply_id);
        $apply_user = Users::model()->findByPk($apply['user_id']);
        $dir = EditorDir::model()->findByPk($apply['dir_id']);
        if($apply['dir_id']==0)
            $dir_name = "根目录";
        else
            $dir_name = EditorDir::getFullPathName($dir['dir_id']);
            // $dir_name = $dir['dir_name'];

        $title = "{$apply_user->cn_name}提交了文档发布申请,请尽快审批";
        $content = "文件名称：{$editor->title} , 申请发布的目录: {$dir_name}";
        return  Notice::addNotice(array('user_id'=>$user_id, 'content'=>$content, 'url'=>$url, 'status'=>'wait', 'type'=>'editor',
                'title'=>$title, 'create_time'=>date('Y-m-d H:i:s')));
    }

	public static function createEditor($data)
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

    public static function writeData( $filepath, $data )
    {
        $fp = fopen( $filepath, 'w' );
        @flock( $fp, 2 );
        fwrite( $fp, $data );
        fclose( $fp );
    }

    // $editor 为数据库查询的结果
    public static function getFileLock($editor , $user_id) {
    	$lock_user = Users::model()->findByPk($editor['lock_user']);             //查找当前锁定的用户

    	if ( ($editor['lock_status'] == 'unlock') || ($editor['lock_user']==$user_id) || ($lock_user['online'] =='off') ) {
    		$editor['lock_status'] = 'lock';
    		$editor['lock_user'] = $user_id;
    		$editor->save();
    		return true;
    	}
    	else
    		return false;
    }

    public static function releaseFileLock($editor, $user_id) {
    	if ( $editor['lock_user']==$user_id ) {
    		$editor['lock_status'] = 'unlock';
    		$editor['lock_user'] = 0;
    		$editor->save();
    		return true;
    	}
    	else
    		return false;
    }

    public static function sendApplyPublish($editor, $dir_id) {
    	$approve_user_id = EditorRoles::getApproverId();
        $data = array();
        $data['editor_id'] = $editor['id'];
        $data['user_id'] = $editor['owner_id'];
        $data['dir_id'] = $dir_id;
        $data['next'] = $approve_user_id;
        $data['user_id'] = $editor['owner_id'];
        $data['create_time'] = date('Y-m-d H:i:s');

        if ( $apply_id = EditorApply::createEditorApply($data) ) {
            if (Editor::editNotice($editor, $approve_user_id, $apply_id)) {
                $editor['approve_user_id'] = $approve_user_id;
                $editor->save();
                return true;
            }
        }
        return false;
    }

    public static function cancelApplyPublish($editor) {
        $editor['approve_user_id'] = 0;
        $editor->save();
        return true;
    }

    public static function getCoEditor($user_id) {                    //找出共同编辑者的文档(未发布文档)
        $result = array();
        $editor_all = Editor::model()->findAll('status=:status and display=:display', 
                                array(':status'=>'wait', ':display'=>'yes' ));

        foreach ($editor_all as $row) {
            $c_user_list = CJSON::decode($row['c_editor']);
            if (!empty($c_user_list)) {
                if (in_array($user_id, $c_user_list))
                    $result[] = $row;
                }
            }
        return $result;
    }

    //判断用户是否具有编辑该文件的权限
    public static function checkEditAuth($editor, $user_id) {
        $c_editor = CJSON::decode($editor['c_editor']);
        if ( $user_id==$editor['owner_id'] )
            return true;
        else if ( $c_editor && (in_array($user_id, $c_editor)) )
            return true;
        else
            return false;
    }
}