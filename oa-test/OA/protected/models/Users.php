<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property integer $user_id
 * @property string $en_name
 * @property string $cn_name
 * @property string $email
 * @property string $qq
 * @property string $mobile
 * @property string $gender
 * @property integer $department_id
 * @property string $title
 * @property integer $second_department
 * @property string $status
 * @property string $permission
 */
class Users extends CActiveRecord
{
    /**
     *获取LDAP的链接对象
     **/
    //这个是加密的密钥
    private static $salt = "f5546e9897b824f49b32c2d2c59a2281";
    public  static $ldap_instance = null;
    public  static $ldap_host = "ldap://ad.i.shanyougame.com";//LDAP 服务器地址
    public  static $ldap_port = "389";//LDAP 服务器端口号
    public static function getLdapInstance()
    {
        if(is_null(self::$ldap_instance))
        {
            self::$ldap_instance = ldap_connect(self::$ldap_host,self::$ldap_port);//建立与 LDAP 服务器的连接
        }
        return self::$ldap_instance;
    }
    
    /**
     *LDAP 验证登陆
     *@param string $login 域用户名
     *@param string $pwd   域用户密码
     *@return boolean
     */
    public static function ldapLogin($login , $pwd)
    {
         $ldap_conn = Users::getLdapInstance();
         @$result = ldap_bind($ldap_conn, "I\\{$login}", $pwd);//与服务器绑定
         //@$result = ldap_bind($ldap_conn, "cn={$login},cn=Users,dc=i,dc=shanyougame,dc=com", $pwd);//与服务器绑定
         return (bool)$result ? true : false;
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Users the static model class
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
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('cn_name, email, qq, mobile, department_id, title,second_department', 'required'),
			array('department_id,second_department ', 'numerical', 'integerOnly'=>true),
			array('en_name, email, title,login', 'length', 'max'=>45),
			array('cn_name, qq', 'length', 'max'=>10),
			array('mobile', 'length', 'max'=>11),
			array('gender', 'length', 'max'=>1),
			array('status', 'length', 'max'=>4),
			array('permission', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, login, en_name, cn_name, email, qq, mobile, gender, department_id, title, second_department, status, permission', 'safe', 'on'=>'search'),
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
            'department'=>array(self::BELONGS_TO, 'Department', 'department_id'),
            'msgCount'  =>array(self::STAT , 'Notice' , 'user_id','condition'=>"status='wait'"),
            'readCount' =>array(self::STAT, 'Notice' , 'user_id' , 'condition'=>"status='read'"),
            'quitApply' =>array(self::HAS_ONE, 'QuitApply','user_id', 'condition'=>"status='wait'"),
            'quit'      =>array(self::HAS_ONE, 'QuitApply','user_id', 'condition'=>"status='success'"),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
            'login'   => 'Login',
			'en_name' => 'En Name',
			'cn_name' => 'Cn Name',
			'email' => 'Email',
			'qq' => 'Qq',
			'mobile' => 'Mobile',
			'gender' => 'Gender',
			'department_id' => 'Department',
			'title' => 'Title',
			'second_department' => 'Second Department',
			'status' => 'Status',
			'permission' => 'Permission',
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('login',$this->login,true);
		$criteria->compare('en_name',$this->en_name,true);
		$criteria->compare('cn_name',$this->cn_name,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('qq',$this->qq,true);
		$criteria->compare('mobile',$this->mobile,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('department_id',$this->department_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('second_department',$this->second_department);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('permission',$this->permission,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     *找出lead的ID
     */
    public function getLeadId()
    {
        $department = $this->department;
        $next = empty($department->leader->user_id)?0:$department->leader->user_id;
        //如果下级负责人非空，并且不是自己就返回
        if(!empty($next) && $next != $this->user_id)
        {
            return $next;
        }

       //如果上级负责人为空或者上级负责人是自己，就继续搜索
        while(empty($next) || $next == $this->user_id)
        {
            $next = empty($department->parent->leader->user_id)?0:$department->parent->leader->user_id;
            if(empty($department->parent))
            {
                return Users::getCeo()->user_id;
            }
            else
            {
                $department = $department->parent;
            }
        }
        return $next;
    }

    /**
     *获取用户
     */
    public static function getUsers($type)
    {
        if($operator = Operator::model()->find(array('select'=>'object_id','condition'=>'type=:type','params'=>array(':type'=>$type))))
        {
            return Users::model()->find("user_id=:user_id and status=:status" , array(':status'=>'work', ':user_id'=>$operator->object_id));
        }
        return false;
    }
    /**
     *找出人事行政部总监的OBJECT
      */
    public static function getAdminId()
    {
        return self::getUsers('admin');
    }

    /**
     *找出人事专员的OBJECT
     */
    public static function getHr()
    {
        return self::getUsers('hr');
    }
    /**
     *找出行政专员的OBJECT
     */
    public static function getCcommissioner()
    {
        return self::getUsers('commissioner');
    }

    /**
     *找出网络管理员
     */
    public static function getWebAdmin()
    {
        return self::getUsers('webadmin');
    }

    /**
     *更新用户
     *@param array $data
     *@return boolean
     */
    public static function updateUser($model , $data)
    {
        try
        {
            foreach($data as $key => $row)
            {
                $model->$key = $row;
            }
            $model->save();
            Helper::processSaveError($model);
            return true;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *获取CEO的用户object
     */
    public static function getCeo()
    {
        return self::getUsers('ceo');
    }

    /**
     *获取公司抬头
     */
    public static function getTitle($user)
    {
        if(empty($user)) return false;
        return $user->title;
    }

    /**
     *获取主策
     */
    public static function getMainPlan($user)
    {
        return (self::getTitle($user) == '主策') ? true : false;
    }

    /**
     *设置yii的cookie
     *@param string $name 就是cookie的名称
     *@param string $value 就是cookie的值
     */
    public static function __setCookie($name, $value)
    {
        $cookie = new CHttpCookie($name, $value);
        $cookie->expire = time()+60*60*24*30;  //有限期30天
        Yii::app()->request->cookies[$name]=$cookie;
    }

    /**
     *获取yii的cookie
     *@param string $name
     */
    public static function __getCookie($name)
    {
        $cookie = Yii::app()->request->getCookies();
        return empty($cookie[$name]->value) ? '' : $cookie[$name]->value;
    }
    
    /**
     *销毁yii的cookie
     */
    public static function __gcCookie($name)
    {
        $cookie = Yii::app()->request->getCookies();
        unset($cookie[$name]);
    }

    /**
     *加密cookie的密码
     */
    public static function encodePwd($password)
    {
        $encode_password = base64_encode($password);
        return base64_encode(self::$salt.$encode_password.self::$salt);
    }

    /**
     *解密cookie的密码
     */
    public static function decodePwd($password)
    {
        return base64_decode(trim(base64_decode($password), self::$salt));
    }

    /**
     *裁剪图片
     *@param string $file  图片路径
     *@param strint $x     原图开始裁剪的X轴
     *@param strint $y     原图开始裁剪的Y轴
     *@param string $width 原图的裁剪的长度
     *@return boolean
     */
    public static function cutPic($file, $x, $y, $width)
    {
        try
        {
            //源图像
            list($_width, $height, $source_mime) = getimagesize($file);
            switch ($source_mime)
            {
                case '1':
                    $src_im = imagecreatefromgif($file);
                    break;
                case '2':
                    $src_im = imagecreatefromjpeg($file);
                    break;
                case '3':
                    $src_im = imagecreatefrompng($file);
                    break;
                default:
                    return false;
                    break;
            }
            $dst_im = imagecreatetruecolor($width, $width);
            $thumbnail = imagecreatetruecolor(100, 100);
            //拷贝源图像左上角起始 150px 150px
            imagecopy( $dst_im, $src_im, 0, 0, $x, $y, $_width, $height );
            // 缩放
            imagecopyresampled($thumbnail, $dst_im, 0, 0, 0, 0, 100, 100, $width, $width);

            //输出拷贝后图像
            imagejpeg($thumbnail, $file);
            imagedestroy($dst_im);
            imagedestroy($src_im);
            imagedestroy($thumbnail);
            return true;
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    /**
     *查找多个用户的email
     *@param array $user_ids 用户user_id的数组
     **/
    public static function foundUser($user_ids)
    {
        foreach($user_ids as $user_id)
        {
            if($user = Users::model()->findByPK($user_id))
            {
                $emails[] = $user->email;
            }
            else
            {
                return false;
            }
        }
        return $emails;
    }

   /**
    *获取客户端的ip
    *
    **/
    public static function GetIP()
    { 
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        { 
            $ip = getenv("HTTP_CLIENT_IP"); 
        }
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
        { 
            $ip = getenv("HTTP_X_FORWARDED_FOR"); 
        }
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
        {
            $ip = getenv("REMOTE_ADDR");
        } 
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        } 
        else 
        {
        $ip = "unknown";
        } 
        return($ip); 
    }
    /**
     *返回一个用户的管理标记
     *如果是人事部或者部门负责人就是true
     *否则返回false
     */
    public static function getAdminTag($user)
    {
        $tag = false;
        //如果为部门负责人 就是管理员
        if(Department::model()->find('admin=:admin', array(':admin'=>$user->user_id)))
        {
            $tag = true;
        }
        //人事部都所有成员和主策 和 OA管理员都为管理员
        else if($user->department_id == Department::adminDepartment()->department_id || $user->permission == 'admin')
        {
            $tag = true;
        }
        return $tag;
    }


    /**
     *存储cookie
     *@param object $user 当前登录用户的对象
     *@param string $pwd  当前登录用户的密码
     */
    public static function saveCookie($user, $pwd)
    {
        if($user->ip == Users::GetIP())
        {
            Users::__setCookie('user', $user->login);
            Users::__setCookie('pwd' , Users::encodePwd($pwd));
        }
    }

    /**
     *OA管理人员的员工IDlist
     */
    public static function getOaAdminList()
    {
        $data = array();
        if($departments = Department::model()->findAll())
        {
            foreach($departments as $row)
            {
                if(!empty($row->admin))
                {
                    $data[] = $row->admin;
                }
            }
        }
        $Commissioner = Users::getCcommissioner();
        if(!empty($Commissioner->user_id))
        {
            $data[]= $Commissioner->user_id;
        }
        $hr = Users::getHr(); 
        if(!empty($hr->user_id))
        {
            $data[]= $hr->user_id;
        }
        $webAdmin = Users::getWebAdmin();
        if(!empty($webAdmin->user_id))
        {
            $data[]= $webAdmin->user_id;
        }
        return $data;
    }


    /**
     *获取当前用户的年假
     */
    public function getAnnualLeaveDays()
    {
        $today = date('Y-m-d');
        $one_year = date('Y-m-d', strtotime('+1years',strtotime($this->entry_day)));
        $three_year = date('Y-m-d', strtotime('+3years',strtotime($this->entry_day)));
        $five_year = date('Y-m-d', strtotime('+5years',strtotime($this->entry_day)));
        if($today >= $five_year)
        {
            return 15;
        }
        elseif($today >= $three_year)
        {
            return 10;
        }
        elseif($today >= $one_year)
        {
            return 7;
        }
        return 0;
    }

    /**
     *获取上一年假周期内事假累计超过15天，一年内病假累计超过30天，本年年假获取为0
     */
    public function getAnnualLeaveTag()
    {
        //上一个年假的开始
        $start = date('Y',strtotime('-1years')).'-'.date('m-d',strtotime($this->entry_day));
        //本次年假的开始
        $end = date('Y').'-'.date('m-d',strtotime($this->entry_day));
        //如果未满一年 就没有年假
        if($end == $this->entry_day)
        {
            return false;
        }
        //如果$end 大于今天 就是没有到分配其年假的时候  返回false
        if($end > date('Y-m-d'))
        {
            return false;
        }
        //没有请假 就有年假
        if(!$leaves = Leave::model()->findAll("user_id=:user_id and status=:status and type in ('casual', 'sick') and start_time >= :start and end_time < :end", array(':user_id'=>$this->user_id, ':status'=>'success', ':start'=>date('Y-m-d 00:00:00',strtotime($start)), ':end'=>date('Y-m-d 00:00:00',strtotime($end))) ))
        {
            return true;
        }
        $casual_count = $sick_count = 0;
        foreach($leaves as $row)
        {
            if($row->type == 'casual')
            {
                $casual_count += $row->total_days;
            }
            elseif($row->type == 'sick')
            {
                $sick_count += $row->total_days;
            }
        }
        if(!($casual_count >= 15 or $sick_count >= 30))
        {
            return true;
        }
        return false;
    }

    /**
     *用户年假
     */
    public function getUserAnnualLeaveDays()
    {
        return AnnualLeave::model()->find('user_id=:user_id',array(':user_id'=>$this->user_id));
    }

    /**
     *通过$user对象查找此人是否为部门负责人
     */
    public static function is_leader($user)
    {
        if(Department::model()->find('admin=:admin', array(':admin'=>$user->user_id)))
        {
            return true;
        }
        return false;
    }

    /**
     *获取一个转正标记
     */
    public function getQualifyTag()
    {
        return QualifyApply::model()->find("user_id=:user_id and status !=:status",array(':user_id'=>$this->user_id,':status'=>'success'));
    }

}
