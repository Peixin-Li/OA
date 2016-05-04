<?php

/**
 * This is the model class for table "books".
 *
 * The followings are the available columns in table 'books':
 * @property integer $book_id
 * @property string $serial_number
 * @property string $name
 * @property string $status
 */
class Books extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Books the static model class
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
		return 'books';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('serial_number, name', 'required'),    //
			array('serial_number', 'length', 'max'=>45),
			array('name', 'length', 'max'=>80),
			array('status', 'length', 'max'=>6),
			array('category_id', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('book_id, serial_number, name, status,category_id', 'safe', 'on'=>'search'),
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
            'category'=>array(self::BELONGS_TO , 'BookCategory' , 'category_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'book_id' => 'Book',
			'serial_number' => 'Serial Number',
			'name' => 'Name',
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

		$criteria->compare('book_id',$this->book_id);
		$criteria->compare('serial_number',$this->serial_number,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *给图书序号加1
     **/
    public static function plusSerial($serial)
    {
        $result = empty($serial) ? '000' : str_replace("SY","",$serial);
        $result = (int)$result + 1;
        return sprintf('%03s',$result);
    }

    /**
     *虚拟属性
     *获取此图书现在在谁那里
     */
    public function getBookWhere()
    {
        if($this->status == 'borrow')
        {
           $borrow = Borrow::model()->find(array(
                'order' => 'borrow_time desc',
                'limit' => 1,
                'condition' => 'book_id=:book_id',
                'params'=>array(':book_id'=>$this->book_id)
            ));
        }
        return !empty($borrow) ? $borrow : array();
    }

    /**
     *添加图书 批量
     *@param array $data
     */
    public static function batchAddBook($data)
    {
        $transaction=self::model()->dbConnection->beginTransaction();
        try
        {
          foreach($data as $row)
          {
               //如果序号存在,就跑出异常
              if(Books::model()->find('serial_number=:serial', array(':serial'=>$row['serial'])))
              {
                  throw new CHttpException(-2, 'serial duplicate');
              }
              //如果序号不存在就添加该图书
              $model = new Books();
              $model->serial_number = $row['serial'];
              $model->name = htmlspecialchars($row['name']);
              $model->category_id = $row['category'];
              $model->publisher = $row['publisher'];
              $model->author = $row['author'];
              $model->descript_url = $row['descript_url'];
              $model->create_time = DATE('Y-m-d h:i:sa');
              $model->save();
              Helper::processSaveError($model);
        }
         $transaction ->commit();
         return array('code'=>0,'msg'=>'add book success');
        }
        catch(Exception $e)
        {
          $transaction ->rollBack();
        }
        return array('code'=>'-1', 'msg'=>'add book fail');
    }
    
    /**
     *修改图书信息
     *@param int $book_id 
     **/
      public static function EditBook($book,$data)
      { 
      	   try
      	   {   
                foreach($data as $key => $row)
                {
                    $book->$key = $row;
                }
      	        $book->save();
                Helper::processSaveError($book);
                return $book->book_id;
      	   }
      	   catch(Exception $e)
      	   {
      	   }
            return false;
      }
    
     /**
     *删除图书信息
     *@param int $book_id
     *删除就是把book状态改为loss
     **/
    public static function DeleteBook($book,$loss_note)
    {
    	$transaction=self::model()->dbConnection->beginTransaction();
      	    try
      	    {  
      	    	 $book->status = 'loss';
               $book->loss_note = $loss_note;
      	    	 $book->save();
               Helper::processSaveError($book);
      	    	 $transaction ->commit();
               return array('code'=>0,'msg'=>'delete book success');
      	    }
      	     catch(Exception $e)
            {
                 $transaction ->rollBack();
            }
        return array('code' => -1,'msg' => 'delete book fail');

    }
    
}
