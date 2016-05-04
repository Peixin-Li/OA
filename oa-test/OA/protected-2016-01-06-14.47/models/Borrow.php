<?php

/**
 * This is the model class for table "borrow".
 *
 * The followings are the available columns in table 'borrow':
 * @property integer $borrow_id
 * @property integer $book_id
 * @property integer $user_id
 * @property string $borrow_time
 * @property string $return_time
 * @property string $comment
 */
class Borrow extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Borrow the static model class
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
		return 'borrow';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('book_id, user_id, borrow_time', 'required'),                      
			array('book_id, user_id', 'numerical', 'integerOnly'=>true),
			array('comment', 'length', 'max'=>300),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('borrow_id, book_id, user_id, borrow_time, return_time, comment ', 'safe', 'on'=>'search'), 
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
            'book'=>array(self::BELONGS_TO, 'Books', 'book_id'),
            'user'=>array(self::BELONGS_TO, 'Users', 'user_id'),
           // 'book_category'=>array(self::BELONGS_TO, 'BookCategory', 'category_id'),
           // 'borrow'=>array(self::HAS_ONE, 'Borrow', 'user_id'), /*/
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'borrow_id' => 'Borrow',
			'book_id' => 'Book',
			'user_id' => 'User',
			'borrow_time' => 'Borrow Time',
			'return_time' => 'Return Time',
			'comment' => 'Comment',
		//'default_returntime'=>'Borrow',  /*/
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

		$criteria->compare('borrow_id',$this->borrow_id);
		$criteria->compare('book_id',$this->book_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('borrow_time',$this->borrow_time,true);
		$criteria->compare('return_time',$this->return_time,true);
		$criteria->compare('comment',$this->comment,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    /**
     *图书借阅
     */
    public static function borrowBook($book_id , $user_id)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $obj_borrow = new Borrow();
            $obj_borrow->book_id = $book_id;
            $obj_borrow->user_id = $user_id;
            $obj_borrow->borrow_time = date('Y-m-d H:i:s');
            $obj_borrow->default_returntime=date('Y-m-d H:i:s',strtotime('+1 month',strtotime(date('Y-m-d H:i:s'))));
            $obj_borrow->return_time = '';
            $obj_borrow->comment = '';
            $obj_borrow->save();
            Helper::processSaveError($obj_borrow);
            $obj_borrow->book->status = 'borrow';
            $obj_borrow->book->save();
            Helper::processSaveError($obj_borrow->book);
            $transaction->commit();
            return $obj_borrow->borrow_id;
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
        }
        return false;
    }

    /**
     *还书 
     *@param string $borrow_id
     *@return array()
     */
    public static function returnBook($borrow_id)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
            $obj_borrow = self::model()->findByPk($borrow_id);
            $obj_borrow->return_time = date('Y-m-d H:i:s');
            $obj_borrow->save();
            Helper::processSaveError($obj_borrow);
            $obj_borrow->book->status = 'wait';
            $obj_borrow->book->save();
            Helper::processSaveError($obj_borrow->book);
            $transaction->commit();
            return true;
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
        }
        return false;
    }
    /**
     *书本续借 @wk
     *@param int $borrow_id
     *@return array()
     **/
    public static function borrowAgain($borrow_id)
    {
      $transaction=self::model()->dbConnection->beginTransaction();
       try
        { 
          $obj_borrow  = Borrow::model()->findByPk($borrow_id);
          $obj_borrow->default_returntime = date('Y-m-d ',strtotime('+1month',strtotime($obj_borrow->default_returntime))) ;
          $obj_borrow->save();
          Helper::processSaveError($obj_borrow);
          $transaction->commit();
          return true;
        }
        catch(Exception $e)
        {
          $transaction->rollBack();
        }
        return false;
    }

    /**
     *获取最近借阅这本书的5个记录
     *@param int $borrow_id
     *@return array()
     **/
    public static function booksDetail($book_id)
    {
        $records = Borrow::model()->findAll(array('condition'=>'book_id =:book_id','params'=>array(':book_id'=>$book_id), 
              'order'=>'borrow_time desc','limit'=>5));
        $borrow_record = $array = array();
        $i = 0;
        foreach($records as $key=>$record)
        {
            $array[0] = $record->user->cn_name;
            $array[1] = $record->borrow_time;
            if($record->return_time != '0000-00-00 00:00:00' )
            {
              $array[2] = $record->return_time;
            }
            else
            {
              $array[2] ='';
            }
            $borrow_record["$i"] = $array;
            $i++;  
        }
      
      return $borrow_record;
    }

}
