<?php

/**
 * This is the model class for table "goods_apply_detail".
 *
 * The followings are the available columns in table 'goods_apply_detail':
 * @property integer $id
 * @property integer $apply_id
 * @property string $name
 * @property string $url
 * @property string $price
 * @property string $quantity
 * @property string $type
 * @property string $create_time
 */
class GoodsApplyDetail extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return GoodsApplyDetail the static model class
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
		return 'goods_apply_detail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('apply_id, name, price, quantity,  create_time', 'required'),
			array('apply_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>100),
			array('quantity', 'length', 'max'=>45),
			array('url', 'length', 'max'=>500),
			array('price', 'length', 'max'=>20),
			array('type', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('apply_id, name, url, price, quantity, type, create_time', 'safe', 'on'=>'search'),
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
            'goods_apply'=>array(self::BELONGS_TO, 'GoodsApply', 'apply_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'apply_id' => 'Apply',
			'name' => 'Name',
			'url' => 'Url',
			'price' => 'Price',
			'quantity' => 'Quantity',
			'type' => 'Type',
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
		$criteria->compare('apply_id',$this->apply_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('quantity',$this->quantity,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *添加申购详情
     */
    public static function processGoodsDetail($model, $data)
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
            @Yii::log($e , 'info' , 'operation.error.GoodsApplyDetail');
        }
        return false;
    }

    /*public static function isBook($data)
    {
        if(empty($data)) return false;
        foreach($data as $row)
        {
            if($row['type'] != 'book')
            {
                return false;
            }
        }
        return true;
    }*/

    public static function validateData($data)
    {
        if(empty($data)) return false;
         
        foreach($data as $row)
        {
            if(!in_array($row['type'],array('fixed','benefit','office','travel','management','project','propaganda','recruit','book')) || empty($row['name']) || empty($row['quantity']) || !preg_match('/^\d+(\.\d+)?$/', $row['price']) || empty($row['url']) || empty($row['reason']))
            {
                return false;
            }
        }
        return true;
    }


    /**
     *验证提交过来的申购数据
     */
    public static function validateNewData($data)
    {
        try
        {
            $data = array_filter($data);
            if(empty($data)) return false;
            foreach($data as $row)
            {
                if(empty($row['category']) || !in_array($row['category'],array('office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other')) 
                    || empty($row['name']) || empty($row['quantity']) 
                    || empty($row['price']) || !preg_match('/^\d+(\.\d+)?$/', $row['price']) || !isset($row['url']) || empty($row['reason']) 
                    || (!empty($row['use_time']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['use_time']))
                )
                {
                    return false;
                }
                //检查项目摊分是否符合
                else if(!empty($row['fee_div_p']) ){
                    try {
                        $count = 0;
                        foreach ($row['fee_div_p'] as $key => $value) {
                            if(!preg_match('/^\d+$/', $key) || !preg_match('/^\d+$/', $value) ) {
                                Yii::log('key,value error' , 'info' , 'operation.ajax');
                                return false;
                            }
                            else if( !$project_info = Project::model()->findByPk($key) ) {
                                return false;
                            }
                            else if ($project_info->enable != "yes"){
                                Yii::log('project status error' , 'info' , 'operation.ajax');
                                return false;
                            }
                            $count += (int)$value;
                        }
                        if( $count!=100 ) { //各个项目分摊比例综合必须为100
                            return false;
                        }
                    }
                    catch(Exception $e) {
                        return false;
                    }
                }
            }
            return true;
        }
        catch(Exception $e)
        {
            @Yii::log($e , 'info' , 'operation.error');
            return false;
        }
    }
    /**
     *是否为图书
     *@return boolean true就是图书
     */
    public static function isBook($data)
    {
        if(empty($data)) return false;
        foreach($data as $row)
        {
            if(!isset($row['category']) || !isset($row['type'])) return false;
            if($row['category'] != 'welfare' || $row['type'] != '图书')
            {
                return false;
            }
        }
        return true;
    }

    /**
     *是否有超出预算
     *@param object $user 申请人
     *@param object $data 申请详情
     *@return boolean true 就是超出 false 就是没有超出
     */
    public static function excessBudget($user, $data) 
    {
        $budgets = array();
        //先把这些的申请详情的根据每个类型写出总价
        foreach($data as $row)
        {
            $budgets[$row['category']] = empty($budgets[$row['category']]) ? 0 : $budgets[$row['category']];
            $budgets[$row['category']] += ($row['price'] * (int)$row['quantity']);
        }
        //用类型和该类型的总价 和 该部门的该类型的预算比较
        foreach($budgets as $category => $budget)
        {
            if(Budget::getDepartmentTypeBudget($user->department_id,$category) < $budget)
            {
                return true;
            }
        }
        return false;
    }
    /**
     *找出那些没有报销的成功申请详情
     */
    public static function getReimbursementApply($user_id)
    {
        $sql="select goods_apply_detail.* from goods_apply join goods_apply_detail on (goods_apply.id=goods_apply_detail.apply_id) where goods_apply.user_id = :user_id and goods_apply.status=:status and goods_apply_detail.is_reimburse=:tag order by goods_apply_detail.id desc;";
        return GoodsApplyDetail::model()->findAllBySql($sql,array(':user_id'=>$user_id, ':status'=>'success',':tag'=>'no'));
    }

    /**
     *给可以报销的申请详情分类
     */
    public static function getTypeReimbursementApply($user_id)
    {
        if(!$data = self::getReimbursementApply($user_id))
        {
            return false;
        }
        $result = array();
        foreach($data as $row)
        {
            $result[$row->category][] = $row;
        }
        return $result;
    }

    /*
    * 取消该申请单
    */
    public static function cancleGoodsApplyDetail($apply_detail, $user, $reason) {
        $old_apply_id = $apply_detail->apply_id;
        $apply_detail->apply_id = 0;
        $apply_detail->remark = $old_apply_id . '-' . $reason;
        $apply_detail->save();

        if( $goods_apply = GoodsApply::model()->findByPk($old_apply_id) ) {
            if( empty($goods_apply->details) ) {
                $goods_apply->status = 'cancle';
                $goods_apply->save();
            }
        }
        return true;
    }

    //检查分摊的数组是否正确, 并返回JSON字符串
    public static function checkFeeDiv($fee_div_p){
        try {
            $count = 0;
            foreach ($fee_div_p as $key => $value) {
                if(!preg_match('/^\d+$/', $key) || !preg_match('/^\d+$/', $value) ) {
                    Yii::log('key,value error' , 'info' , 'operation.ajax');
                    return false;
                }
                else if( !$project_info = Project::model()->findByPk($key) ) {
                    return false;
                }
                else if ($project_info->enable != "yes"){
                    Yii::log('project status error' , 'info' , 'operation.ajax');
                    return false;
                }
                $count += (int)$value;
            }
            if( $count!=100 ) { //各个项目分摊比例综合必须为100
                return false;
            }
            return CJSON::encode($fee_div_p);
        }
        catch(Exception $e) {
            return false;
        }
    }

}
