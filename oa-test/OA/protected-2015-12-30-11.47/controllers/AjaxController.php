<?php
/**
 *AJAX接口控制器
 */
class AjaxController extends Controller
{
    /**
     *@access private $user 是保存登录用户对象的成员变量
     *@access private $BookCategory 保存图书分类
     */
    private $user,$BookCategory;
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
	}
    public function filters()
    {
        //过滤只用于actionEdit和actionCreate方法
        return array(
            'verify -  ldapLogin test1',
        );

    }
    /**
     *定义的过滤方法
     */
    public function FilterVerify($filterChain)
    {
        //判断什么的
        //过滤完后继续执行代码
        ////admin才可以的操作的菜单
        $admin_acl = array('addBook','addRestaurant','addMenu','addWaste');
        if( empty(Yii::app()->session['user_id']) || !preg_match('/^-?\d+$/',Yii::app()->session['user_id']) )
        {
            #header('Location: '.Yii::app()->request->hostInfo.'/oa/login');
            if(Yii::app()->getController()->getAction()->id == 'logout')
            {
                header('Location: '.Yii::app()->request->hostInfo.'/user/login');
            }
            else
            {
                echo CJSON::encode(array('code'=>'-99','msg'=>'permission denied'. Yii::app()->session['user_id'] ));
                exit;
            }
        }
        elseif((!$this->user = Users::model()->findByPk(Yii::app()->session['user_id'])) &&(Yii::app()->session['user_id']!=-1) )
        {
            echo CJSON::encode(array('code'=>'-99','msg'=>'permission denied'));
            exit;
        }//acl权限设置
        elseif(empty(Yii::app()->session['admin']) && in_array(Yii::app()->getController()->getAction()->id,$admin_acl))
        {
            echo CJSON::encode(array('code'=>'-99','msg'=>'permission denied'));
            exit;
        }
        //记录日志信息
        @$log_format = "%s %s/%s %s";
        @$log_params = array(
            Yii::app()->session['cn_name'],
            Yii::app()->getController()->id,
            Yii::app()->getController()->getAction()->id,
            file_get_contents("php://input"),
        );
        $ignore_list = array('heartbeat');
        if(!in_array($log_params[2], $ignore_list)) {
            @$log_content = sprintf($log_format, $log_params[0], $log_params[1], $log_params[2], $log_params[3]);
            @Yii::log($log_content , 'info' , 'operation.ajax');
        }
        $filterChain->run();
    }
    
    /**
     *添加图书
     *url ajax/AddBook
     *@param array $book_arr [{"serial":XXX, "name":XXX,"category":xxx},'publisher':xx,'author':xxx,'descript_url':xxx] 图书序号
     *@return
     #{"code":0,"msg":"add book success"}
     #{"code":-1,"msg":"add book failed"}
     #{"code":-2,"msg":"serial duplicate"}
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限    
     */
    public function actionAddBook()
    {
        $books = empty($_POST['book_arr']) ? array() : $_POST['book_arr'];
        $response = array('code'=>-1,'msg'=>'add book failed');

        //验证数据
        if(empty($books))
        {
                echo CJSON::encode(array('code'=>'-1', 'msg'=>'param error'));
                exit;
        }

        foreach($books as $book)
        {
            if(empty($book['name']) || !preg_match('/^\d+$/', $book['category']) || !preg_match('/^SY\d{3}$/', $book['serial']))
            {
                echo CJSON::encode(array('code'=>'-1', 'msg'=>'param error'));
                exit;
            }
        }
        //批量添加图书
        $response = Books::batchAddBook($books);
        echo CJSON::encode($response);
    }    

    /**
     *LDAP登陆
     *@param string $user  用户名称
     *@param string $pwd   用户密码
     *@return array
     *{'code'=>'0','msg'=>'login success'}
     *{'code'=>'-1','msg'=>'Verification failed'}
     *{'code'=>'-2','msg'=>'invalid user'}
     *{'code'=>'-3','msg'=>'user status is quit'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限    
     */
    public function actionLdapLogin()
    {
        $user = empty($_POST['user']) ? '' : $_POST['user'];
        $pwd = empty($_POST['pwd']) ? '' : $_POST['pwd'];

        $response = array('code'=>'-1' , 'msg'=>'Verification failed');
        if(empty($user) || empty($pwd))
        {
            $response['code'] = -1;
            $response['msg']  = 'Verification failed';
        }
        elseif (($user=="shanyou")&&($pwd=="shanyougame")) {   //善游财务账号则将id置为-1
            Yii::app()->session['user_id'] = -1;
            $response['code'] = 0;
            $response['msg'] = 'login success';
        }
        elseif(!Users::ldapLogin($user, $pwd))
        {
            $response['code'] = -1;
            $response['msg']  = 'Verification failed';
        }
        elseif(!$users = Users::model()->find('login=:login',array(':login'=>$user)))
        {
            $response['code'] = -2;
            $response['msg'] = 'invalid user';
        }
        else if($users->status == 'quit')
        {
            $response['code'] = -3;
            $response['msg'] = 'user status is quit';
        }
        else
        {
            Users::updateUser($users, array('online'=>'on', 'heartbeat'=>time()));
            Yii::app()->session['user_id'] = $users->user_id;
            Yii::app()->session['user_name'] = $users->en_name;
            Yii::app()->session['cn_name'] = $users->cn_name;
            Yii::app()->session['permission'] = $users->permission;
            Yii::app()->session['admin'] = Users::getAdminTag($users);
            Yii::app()->session['is_leader'] = Users::is_leader($users);
            Users::saveCookie($users, $pwd); 
            $response['code'] = 0;
            $response['msg'] = 'login success';
        }
        echo CJSON::encode($response);
    }

    /**
     *借书
     *@url /ajax/borrow
     *@param string $book_id 图书的ID
     *@return array
     #{'code':'0','msg':'borrow success'}
     #{'code':'-1','msg':'borrow fail'}
     #{'code':'-2','msg':'book is not found'}
     #{'code':'-3','msg':'book can\'t borrow'}
     */
    public function actionBorrow()
    {
        $book_id = empty($_POST['book_id']) ? '' : $_POST['book_id'];
        $response = array('code'=>-1, 'msg'=>'borrow fail');

        if(!$book = Books::model()->findByPk($book_id))
        {
            $response['code'] = -2;
            $response['msg']  = 'book is not found';
        }
        elseif($book->status == 'borrow')
        {
            $response['code'] = -3;
            $response['msg']  = 'book can\'t borrow';
        }
        elseif(Borrow::model()->borrowBook($book_id,Yii::app()->session['user_id']))
        {
            $response['code'] = 0;
            $response['msg'] = 'borrow success';
        }
        echo CJSON::encode($response);
    }

    /**
     *还书
     *@url /ajax/returnBook
     *@param string $book_id 图书的ID
     *@return array()
     #{'code':0,'msg':'book return success'}
     #{'code':-1,'msg':'borrow fail'}
     #{'code':-2,'msg':'borrow is not found'}
     #{'code':-3,'msg':'book is returned'}
     */
    public function actionReturnBook()
    {
        $borrow_id = empty($_POST['borrow_id']) ? '' : $_POST['borrow_id'];
        $response = array('code'=>-1, 'msg'=>'borrow fail');

        if(!$borrow = Borrow::model()->findByPk($borrow_id))
        {
            $response['code'] = -2;
            $response['msg']  = 'borrow is not found';
        }
        elseif($borrow->return_time != '0000-00-00 00:00:00')
        {
            $response['code'] = -3;
            $response['msg']  = 'book is returned';
        }
        elseif(Borrow::returnBook($borrow_id))
        {
            $response['code'] = 0;
            $response['msg'] = 'book return success';
        }
        echo CJSON::encode($response);
    }

    /**
     *请假上传图片
     *@url /ajax/leaveUploadPicture
     *@param string $id   请假的ID
     *@param object $file 上传的图片
     *@return xml
     *{code:0,'url':/images/leave/593.jpg,'msg'=>'upload picture success'}
     *{code:-1,'url':'','msg'=>'upload picture fail'}
     *{code:-2,'url':'','msg'=>'param error'}
     *{code:-3,'url':'','msg'=>'leave not found'}
     *{code:-4,'url':'','msg'=>'size error'}
     *{code:-5,'url':'','msg'=>'type error'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionLeaveUploadPicture()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $image = CUploadedFile::getInstanceByName('file');
        $dir = Yii::getPathOfAlias('webroot.images.leave').DIRECTORY_SEPARATOR;
        $response = array('url'=>'0', 'code'=>-1, 'msg'=>'upload picture fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || empty($image))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$leave = Leave::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'leave not found';
        }
        elseif($leave->user_id != $this->user->user_id)
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        elseif(!empty($image) && ($image->getSize() == 0 || $image->getSize() > 2*1024*1024))
        {
            $response['code'] = -4;
            $response['msg'] = 'size error';
        }
        elseif(!empty($image) && !in_array($image->type, array('image/jpeg', 'image/png', 'image/gif')))
        {
            $response['code'] = -5;
            $response['msg']  =  'type error';
        }
        elseif($image->saveAs($dir."{$leave->leave_id}.jpg") && !$image->hasError && Leave::processLeave($leave , array('image'=>"/images/leave/{$leave->leave_id}.jpg")))
        {
            $response['code'] = 0;
            $response['url'] = "/images/leave/{$leave->leave_id}.jpg";
            $response['msg']  =  'upload picture success';
        }
        echo "<?xml version='1.0' encoding='utf-8'?>
        <response>
        <code>{$response['code']}</code>
        <msg>{$response['msg']}</msg>
        <url>{$response['url']}</url>
        </response>";
    }
    /**
     *添加请假单
     *@url /ajax/AddLeave 
     *@param string $type 请假类型
     *@param string $start_time 开始时间
     *@param string $end_time   结束时间
     *@param string $content    请假事由
     *@param string $delay  延迟说明
     *@param string $image  上传的图片
     *@return array()
     *{'code':0  'url':'{$host}/oa/msg/leave/{$id}' 'msg':'add leave success'}//添加请假单成功
     *{'code':-1   'url':'0', 'msg':'add leave fail'}//添加请假单失败
     *{'code':-2   'url':'0', 'msg':'please input content'}//请输入请假理由
     *{'code':-3   'url':'0', 'msg':'please input a right time'}//请输入正确时间
     *{'code':-4   'url':'0', 'msg':'start_time can not bigger than end_time}//开始时间不能大于结束时间
     *{'code':-5   'url':'0', 'msg':'please input dealy'}//请输入延迟提交原因
     *{'code':-6   'url':'0', 'msg':'cannot input dealy'}//没有超时，不能输入延迟提交原因
     *{'code':-7   'url':'0', 'msg':'size error'}//图片大小错误
     *{'code':-8   'url':'0', 'msg':'type error'}//图片类型错误
     *{'code':-9   'url':'0', 'msg':'upload picture fail'}//图片上传失败
     *{'code':-10   'url':'0', 'msg':'leave duplicate'}//请假时间有重复
     */
    public function actionAddLeave()
    {
        #$_POST = array('type'=>'casual', 'end_time'=>'2015-03-29 18:30', 'start_time'=>'2015-03-29 09:30','content'=>'测试申请请假' );
        $type = empty($_POST['type']) ? 'casual' : $_POST['type'];
        $start_time = empty($_POST['start_time']) ? '' : date('Y-m-d H:i:s', strtotime($_POST['start_time'].':00'));
        $end_time = empty($_POST['end_time']) ? '' : date('Y-m-d H:i:s', strtotime($_POST['end_time'].':00')); 
        $content = empty($_POST['content']) ? '' : htmlspecialchars($_POST['content']);
        $delay = empty($_POST['delay'])?'':htmlspecialchars($_POST['delay']);
        $create_time = date('Y-m-d H:i:s');
        $image = CUploadedFile::getInstanceByName('file');
        $dir = Yii::getPathOfAlias('webroot.images.leave').DIRECTORY_SEPARATOR;
        $response = array('code'=>-1 ,'url'=>'0' ,'msg'=>'add leave fail');

        $user_id = Yii::app()->session['user_id'];

        if(empty($content))
        {
            $response['code'] = -2;
            $response['msg'] = 'please input content';
        }
        elseif(!preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}:\d{2}$/', $start_time) || !preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}:\d{2}$/', $end_time))
        {
            $response['code'] = -3;
            $response['msg'] = 'please input a right time';
        }
        elseif(!empty($image) && ($image->getSize() == 0 || $image->getSize() > 2*1024*1024))
        {
            $response['code'] = -7;
            $response['msg'] = 'size error';
        }
        elseif(!empty($image) && !in_array($image->type, array('image/jpeg', 'image/png', 'image/gif')))
        {
            $response['code'] = -8;
            $response['msg']  =  'type error';
        }
        elseif( date('Y-m-d H:i',strtotime($_POST['start_time'] )) > date('Y-m-d H:i',strtotime($_POST['end_time'] )) )
        {
            $response['code'] = -4;
            $response['msg'] = 'start_time can not bigger than end_time';
        }
        elseif( date('Y-m-d H:i', strtotime($_POST['start_time'].':00')) < date('Y-m-d H:i') && empty($delay) )
        {
            $response['code'] = -5;
            $response['url'] = '';
            $response['msg'] = 'please input delay';
        }
        elseif( date('Y-m-d H:i', strtotime($_POST['start_time'].':00')) >= date('Y-m-d H:i') && !empty($delay) )
        {
            $response['code'] = -6;
            $response['msg'] = 'cannot input delay';
        }
        elseif($leave_rcords = Leave::model()->find("status != 'reject' and user_id = :user_id and ( 
            (start_time <= :start1 and end_time >= :start2) or (start_time <= :end1 and end_time >= :end2) or 
            (start_time >= :start3 and end_time <= :end3) );", array(':user_id'=>$user_id , ':start1'=>$start_time, ':end1'=>$end_time, ':start2'=>$start_time, ':end2'=>$end_time, ':start3'=>$start_time, ':end3'=>$end_time)))
        {
            $response['code'] = -10;
            $response['msg'] = 'leave duplicate';
        }
        elseif($leave = Leave::addLeave( array('user_id'=>$user_id,'type'=>$type,'start_time'=>$start_time,'end_time'=>$end_time,'content'=>$content,'delay'=>$delay ,'create_time'=>$create_time) ))
        {
            if(empty($image) || (!empty($image) && $image->saveAs($dir."{$leave->leave_id}.jpg") && !$image->hasError))
            {
                if(!empty($image))
                {
                    Leave::processLeave($leave , array('image'=>"/images/leave/{$leave->leave_id}.jpg"));
                }
                //发送消息
                Leave::leaveNotice($leave,$this->user,'self');//发送消息给本人
                $heads = Users::model()->findByPk($leave->next);//通知下一位审批人
                Leave::leaveNotice($leave,$heads,'heads');
                Leave::leaveMail($leave,$heads,'heads');
                
                $response['code'] = 0;
                $response['url'] = "/user/msg/leave/{$leave->leave_id}";
                $response['msg'] = 'add leave success';
            }
            else
            {
                $response['code'] = -9;
                $response['msg'] = 'upload picture fail';
            }
        }
        //echo CJSON::encode($response);
        echo "<?xml version='1.0' encoding='utf-8'?>
        <response>
        <code>{$response['code']}</code>
        <msg>{$response['msg']}</msg>
        <url>{$response['url']}</url>
        </response>";
    }

    /**
     *拒绝请假
     *@URl /ajax/rejectLeave
     *@param string $id 请假的ID
     *@param string $reason 拒绝理由
     *@return array()
     *{"code":0,"msg":"leave reject success"} //请假拒绝成功
     *{"code":-1,"msg":"reject leave fail"} //请假拒绝失败
     *{"code":-2,"msg":"leave not found"}//请假记录未发现
     *{"code":-3,"msg":"leave update fail"}//请假记录修改失败
     *{"code":-4,"msg":"add log fail"}//添加请假记录日志失败
     *{"code":-5,"msg":"add notice fail"}//添加请假通知失败
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionRejectLeave()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id']; //这是leave_id
        $reason = empty($_POST['reason']) ? '' : $_POST['reason'];
        $user_id = Yii::app()->session['user_id'];
        $user = $this->user;
        $response = array('code'=>-1, 'msg'=>'reject leave fail');
        
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg']= 'leave not found';
        }
        else if(!$leave = Leave::model()->findByPk($id))
        {
            $response['code'] = -2;
            $response['msg']= 'leave not found';
        }
        else if($leave->next != $user_id)
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        elseif(!Leave::processLeave($leave , array('status'=>"reject", 'reason'=>$reason, 'next'=>'0')))
        {
            $response['code'] = -3;
            $response['msg'] = 'leave update fail';
        }
        elseif(!LeaveLog::addLog(array('leave_id' => $id,'user_id' => $user_id , 'action' => "reject",'create_time'=> date("Y-m-d H:i:s"))))
        {
            $response['code'] = -4;
            $response['msg'] = 'add log fail';
        }
        elseif(!$notice_id = Leave::leaveNotice($leave,$leave->user,'self')) 
        {
            $response['code'] = -5;
            $response['msg'] = 'add notice fail';
        }
        elseif(!Leave::noticeHeadsTransaction($leave , "审批未通过"))
        {
            $response['code'] = -5;
            $response['msg'] = 'add notice fail';
        }
        else
        { 
            Leave::leaveMail($leave,$leave->user,'self');
            $response['code'] = 0;
            $response['msg'] = 'reject leave success';
        }
        echo CJSON::encode($response);
    }

    /**
     *拒绝物资申请
     *@url /ajax/rejectGoodsApply
     *@param string $id 物品申购ID
     *@param string $reason 拒绝理由
     *@result array
     *{"code":0,"msg":"reject goods apply success"} //请假拒绝成功
     *{"code":-1,"msg":"reject apply fail"} //请假拒绝失败
     *{"code":-2,"msg":"param error"}       //参数错误
     *{"code":-3,"msg":"goods apply not found"}//请假记录未发现
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionRejectGoodsApply()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $reason= empty($_POST['reason'])? '' : htmlspecialchars($_POST['reason']);

        $response = array('code'=>-1 , 'msg'=>'reject apply failed');

        if(!preg_match('/^[1-9]\d*$/', $id) || empty($reason))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$apply = GoodsApply::model()->findByPk($id))
        {
            $response['code'] = '-3' ;
            $response['msg']  = 'goods apply not found';
        }
        elseif(empty($this->user) || $apply->next != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        else if($apply->status != 'wait') {
            $response['code'] = -4;
            $response['msg']  = 'apply status not wait';
        }
        elseif(GoodsApply::rejectGoodsApply($apply, $this->user, $reason))
        {
            $response['code'] = '0';
            $response['msg']  = 'reject goods apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *@ignore
     *人事部的后续物资申请处理
     */
    // public function actionProcessGoodsApply()
    // {
    //     $id = empty($_POST['id']) ? '' : $_POST['id'];
    //     $type = empty($_POST['type']) ? '' : $_POST['type'];
    //     $response = array('code'=>-1 , 'msg'=>'process goods apply failed');
    //     if(!$apply = GoodsApply::model()->findByPk($id))
    //     {
    //         $response['code'] = '-2';
    //         $response['msg']  = 'goods apply not found';
    //     }
    //     elseif($this->user->department_id != Department::adminDepartment()->department_id)
    //     {
    //         $response['code'] = '-3';
    //         $response['msg']  = 'permission denied';
    //     }
    //     elseif(!GoodsApplyLog::addLog(array('apply_id'=>$id, 'user_id'=>$this->user->user_id, 'action'=>$type, 'create_time'=>date('Y-m-d H:i:s'))))
    //     {
    //         $response['code'] = '-4';
    //         $response['msg']  = 'add log failed';
    //     }
    //     elseif(!GoodsApply::updateGoodsApply($apply, array('status'=>$type)))
    //     {
    //         $response['code'] = '-5';
    //         $response['msg']  = 'update goods apply status failed';
    //     }
    //     else
    //     {
    //         $response['code'] = '0';
    //         $response['msg']  = 'process goods apply success';
    //     }
    //     echo CJSON::encode($response);
    // }

    /**
     *@ignore
     *行政专员比较后下单
     */
    // public function actionOrder()
    // {
    //     $id = empty($_POST['id'])? '' : $_POST['id'];
    //     $total = empty($_POST['total'])? '' : $_POST['total'];
    //     $url = empty($_POST['url'])? '' : $_POST['url'];
    //     $name = empty($_POST['name'])? '' : $_POST['name'];
    //     $response = array('code'=>'-1','msg'=>"order failed");
    //     if(!$apply = GoodsApply::model()->findByPk($id))
    //     {
    //         $response['code'] = '-2';
    //         $response['msg'] = 'apply not found';
    //     }
    //     elseif(!GoodsApplyLog::addLog(array('apply_id'=>$id, 'user_id'=>$this->user->user_id, 'action'=>'orders', 'create_time'=>date('Y-m-d H:i:s'))))
    //     {
    //         $response['code'] = '-3';
    //         $response['msg'] = 'add log failed';
    //     }
    //     elseif(GoodsApply::processApply($apply, array('real_name'=>$name, 'real_url'=>$url, 'real_total'=>$total, 'status'=>'orders')))
    //     {
    //         $response['code'] = '0';
    //         $response['msg'] = 'order success';
    //     }
    //     echo CJSON::encode($response);
    // }

    /**
     *@ignore
     *组织架构的拖动部门
     *@url /ajax/drag
     *@param string $type ENUM('department','employee')
     *@param string $pId  上级部门的ID
     *@param string $id   本部门的ID
     *@return array
     *{'code':0 , 'msg':'drag  success'} //成功
     *{'code':-1 , 'msg':'drag  fail'}   //失败
     *{'code':-2 , 'msg':'not found'};
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */ 
    public function actionDrag()
    {
        $id       = empty($_POST['id']) ? '' : $_POST['id'];
        $pId      = empty($_POST['pId']) ? '' : $_POST['pId'];
        $type     = empty($_POST['type']) ? '' : $_POST['type'];
        $response = array('code' => -1 , 'msg'=>'drag fail');

        if(!in_array($type , array('department','employee')))
        {
            $response = array('code' => -1 , 'msg'=>'drag fail');
        }//没有找到相应的部门
        elseif($type == 'department' && !$model = Department::model()->findByPk($id))
        {
            $response = array('code' => -2 , 'msg'=>'not found');
        }//修改部门失败
        elseif($type == 'department' && Department::updateDepartment($model , array('parent_id'=>$pId)))
        {
            $response = array('code' => 0 , 'msg'=>'drag  success');
        }//没有找到相应的用户
        elseif($type == 'employee' && !$model = Users::model()->findByPk($id))
        {
            $response = array('code' => -2 , 'msg'=>'not found');
        }//修改相应的用户失败
        elseif($type == 'employee' && Users::updateUser($model , array('department_id'=>$pId)))
        {
            $response = array('code' => 0 , 'msg'=>'drag  success');
        }

        echo CJSON::encode($response);
    }
    /**
     *更新部门名称
     *@url /ajax/updateDepartment
     *@param string $id   部门ID 
     *@param string $name 新的部门名称
     *@return array
     #*{'code':0 , 'msg':'update success'} //成功
     #*{'code':-1 , 'msg':'update fail'}   //失败
     #*{'code':-2 , 'msg':'not found'};   //没有找到部门
     #*{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionUpdateDepartment()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $name = empty($_POST['name']) ? '' : $_POST['name'];
        $response = array('code'=>-1,'msg'=>'update fail');

        if(!$department = Department::model()->findByPk($id))
        {
            $response['code'] = -2;
            $response['msg'] = 'not found';
        }
        elseif(Department::updateDepartment($department , array('name'=>$name)))
        {
            $response = array('code' => 0 , 'msg'=>'updaet success');
        }
        echo CJSON::encode($response);
    }

    /**
     *删除部门 (只有部门下面没有人才可以删除)
     *@url /ajax/removeDepartment
     *@param string $id 部门ID
     *@return array
     #{'code':0 , 'msg':'remove success'} //删除成功
     #{'code':-1 , 'msg':'remove fail'}   //删除失败
     #{'code':-2 , 'msg':'not found'};    //部门未找到
     #{'code':-3 , 'msg':'department is not null'}; //部门不为空
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionRemoveDepartment()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1 , 'msg'=>'remove fail');

        if(!$department = Department::model()->findByPk($id))
        {
            $response['code'] = -2;
            $response['msg'] = 'not found';
        }
        elseif($users = Users::model()->find("department_id = :id and status = :status", array(':id'=>$id, ':status'=>'work')))
        {
            $response['code'] = -3;
            $response['msg'] = 'department is not null';
        }
        elseif(Department::model()->find("parent_id = :id", array(':id'=>$id)))
        {
            $response['code'] = -4;
            $response['msg'] = 'department has children';
        }
        // elseif($department->delete())
        // {
        //     $response['code'] = 0;
        //     $response['msg'] = 'remove success';
        // }
        elseif(Department::model()->updateDepartment($department, array('department_status'=>'hidden')))
        {
            $response['code'] = 0;
            $response['msg'] = 'remove success';
        }
        echo CJSON::encode($response);
    }
		  
    /**
     *新建部门
     *@url /ajax/createDepartment
     *@param string $pId 部门的上级部门ID
     *@param string $name 新部门的名称
     *@return array
     #{'code':0 , 'id':9, 'msg':'create success'} //成功 id为部门ID
     #{'code':-1 , 'id':0, 'msg':'create fail'}   //失败
     #{'code':-2 , 'id':0, 'msg':'not found'};    //上级部门未找到
     #{'code':-3 , 'id':0, 'msg':'department name duplicate'}; //部门名称重复
     #{'code'=>'-99', 'id':0,'msg'=>'permission denied'}   //没有权限
     */
    public function actionCreateDepartment()
    {
        $pId = empty($_POST['pId']) ? '' : $_POST['pId'];
        $name = empty($_POST['name']) ? '' : $_POST['name'];
        $response = array('code'=>-1 ,'id'=>0, 'msg'=>'remove fail');

        if(!$department = Department::model()->findByPk($pId))
        {
            $response['code'] = -2;
            $response['msg']  = 'not found';
        }
        elseif(Department::model()->find("name=:name" , array(':name'=>$name)))
        {
            $response['code'] = -3;
            $response['msg']  = 'department name duplicate';
        }
        elseif($id = Department::createDepartment(array('name'=>$name, 'parent_id'=>$pId, 'admin'=>0)))
        {
            $response['code'] = 0;
            $response['id'] = $id;
            $response['msg']  = 'create success';
        }
        echo CJSON::encode($response);
    }

    /**
     *获取部门或者个人信息
     *@url /ajax/getInfo
     *@param string $id    部门ID或者用户ID
     *@param string $type  ENUM('department','employee') 部门或者用户
     *@return array
     #{'code':-1 , 'result'=>array(), 'msg':'get info fail'}   //失败
     #{'code'=>'-99', 'result'=>array(),'msg'=>'permission denied'}   //没有权限
     #{'code':0 , 'result'=>array('user_id'=>array('name'=>xxxx,'mobile'=>'xxxxxx)) ,'msg':'remove success'} //成功
     #*******result的值**********
     * "name":"刘凌志",
     * "en_name":"Tom",
     * "sex":"m",
     * "title":"项目总监",
     * "mobile":"18602380913",
     * "email":"tom.liu@shanyougame.com",
     * "qq":"2850850616",
     * "department":项目部
     */
    public function actionGetInfo()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $type = empty($_POST['type']) ? '' : $_POST['type'];
        $response = array('code'=>-1 ,'result'=>array(),  'msg'=>'get info fail');
        if(!in_array($type , array('department','employee')))
        {
            $response = array('code'=>-1 , 'msg'=>'param error');
        }
        elseif($type == 'employee' && $user = Users::model()->findByPk($id))
        {
            $response['code'] = 0;
            $response['result'][$user->user_id] = array('id'=>$user->user_id, 'name'=>$user->cn_name, 'en_name'=>$user->en_name,'sex'=>$user->gender,'title'=>$user->title, 'mobile'=>$user->mobile, 'email'=>$user->email, 'qq'=>$user->qq, 'department'=>$user->department->name, 'job_status'=>$user->job_status, 'regularized_date'=>$user->regularized_date, 'job_description'=>$user->job_description, 'native_place'=>$user->native_place, 'entry_day'=>$user->entry_day, 'birthday'=>$user->birthday, 'photo'=>$user->photo, 'department_id'=>$user->department->department_id);
        }
        elseif($type == 'department' && $users = Users::model()->findAll("department_id=:id and status=:status" , array(':id'=>$id, ':status'=>'work')))
        {
            $response['code'] = 0;
            $response['result']=array();
            foreach($users as $user)
            {
                $response['result'][$user->user_id] = array('id'=>$user->user_id, 'name'=>$user->cn_name, 'en_name'=>$user->en_name,'sex'=>$user->gender,'title'=>$user->title, 'mobile'=>$user->mobile, 'email'=>$user->email, 'qq'=>$user->qq, 'department'=>$user->department->name,'job_status'=>$user->job_status, 'regularized_date'=>$user->regularized_date, 'job_description'=>$user->job_description, 'native_place'=>$user->native_place, 'entry_day'=>$user->entry_day,'birthday'=>$user->birthday, 'photo'=>$user->photo, 'department_id'=>$user->department->department_id);
            }

        }elseif($type == 'department' && Department::model()->findAll("department_id=:id" , array(':id'=>$id))){
            $response['code'] = 0;
            $response['result']='';
        }
        echo CJSON::encode($response);
    }
         	  
    /**
     *设置部门负责人
     *@url /ajax/departmentAdmin
     *@param string $pId 部门ID
     *@param string $id  用户ID
     *@return array
     #{'code':0 , 'msg':'set department leader success'} //成功
     #{'code':-1 , 'msg':'set department leader failed'}   //失败
     #{'code':-2 , 'msg':'department not found'};    //部门未找到
     #{'code':-3 , 'msg':'user not found'}; //用户未找到
     #{'code':-4 , 'msg':'user not belong to department'}; //用户不属于该部门
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */ 
    public function actionDepartmentAdmin()
    {
        $pId = empty($_POST['pId']) ? '' : $_POST['pId'];
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'set department leader failed');
        
        if(!$department = Department::model()->findByPk($pId))
        {
            $response['code'] = -2;
            $response['msg'] = 'department not found';
        }
        elseif(!$user = Users::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'user not found';
        }
        elseif($user->department_id != $pId)
        {
            $response['code'] = -4;
            $response['msg'] = 'user not belong to department';
        }
        elseif(Department::updateDepartment($department , array('admin'=>$id)))
        {
            $response['code'] = 0;
            $response['msg'] = 'set department leader success';
        }
        echo CJSON::encode($response);
    }

    /**
     *取消部门负责人(慎重，二次确认)
     *@url /ajax/cancelDepartmentAdmin
     *@param string $pId 部门ID
     *@return array
     *{'code':0 ,  'msg':'cancel department leader success'} //成功
     *{'code':-1 , 'msg':'cancel department leader failed'}   //失败
     *{'code':-2 , 'msg':'department not found'};    //部门未找到
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    // public function actionCancelDepartmentAdmin()
    // {
    //     $pId = empty($_POST['pId']) ? '' : $_POST['pId'];
    //     $response = array('code'=>-1, 'msg'=>'cancle department leader failed');

    //     if(!$department =  Department::model()->findByPk($pId))
    //     {
    //         $response['code'] = -2;
    //         $response['msg']  = 'department not found';
    //     }
    //     elseif(Department::updateDepartment($department , array('admin'=>'0')))
    //     {
    //         $response['code'] = 0;
    //         $response['msg']  = 'cancel department leader success';
    //     }
    //     echo CJSON::encode($response);
    // }
  
   /**
     *增加图书类别
     *@url /ajax/Addbookcategory
     *@param string $name        图书类别的名称
     *@return array   result就是类型ID
     #{'code':0 , 'result':1, 'msg':''add bookcategory success'.'  and the   category_id is :'.$category_id;'} //成功
     #{'code':-1 ,'result':0, 'msg':'error'}   //失败
     #{'code':-2 ,'result':0, 'msg':'The BookCategory is existed;'};    //书类已存在
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
     public function actionAddbookcategory()
     {
        $name = empty($_POST['name'])?' ':$_POST['name'];
        $response = array('code'=>-1, 'msg'=>'error', 'result'=>0);

        if(BookCategory::model()->find('name=:name',array(':name'=>$name)))
        {
            $response['code']='-2';  //-2,就是类名已经存在
            $response['msg']='The BookCategory is existed;';
        }
        elseif($id = BookCategory::addCategory($name))
        {
                $response['result'] = $id;
                $response['code']='0';                      //代表添加类成功
                $response['msg']='add bookcategory success'.'  and the   category_id is :'.$id;
        }
        echo CJSON::encode($response);
     }

    /**
     *编辑用户
     *@url /ajax/editUser
     *@param string $id 用户ID
     *@param string $cn_name 中文名
     *@param string $en_name 英文名
     *@param string $sex      ENUM('m','f') 性别
     *@param string $title   职位
     *@param string $mobile 手机
     *@param string $email 邮件
     *@param string $qq    QQ
     *@param varchar $native_place 籍贯
     *@param varchar $job_description 岗位说明
     *@param string  $job_status ENUM(' intern', 'probation_employee', 'formal_employee') 职位状态 
     *@param date   $regularized_date 转正日期
     *@param date   $birthday 出生日期
     *@param date   $entry_day 入职日期
     *@param string $department_id 部门ID
     *@return array()
     #{'code':0 ,  'msg':'edit user success'} //编辑成功
     #{'code':-1 , 'msg':'edit user failed'}   //编辑失败
     #{'code':-2 , 'msg':'user not found'};    //用户未找到
     #{'code':-3 , 'msg':'the info can not be null'}//信息不能为空
     #{'code':-4 , 'msg':'the sex is wrong'}//性别错误
     #{'code':-5 , 'msg':'the job_status is wrong'}//职位状态错误
     #{'code':-6 , 'msg':'the regularized_date format is wrong'}//转正日期格式错误
     #{'code':-7 , 'msg':'please input a right email'} //请输入正确的email
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionEditUser()
    {
        //$_POST = array('id'=>32,'cn_name'=>'张文','department_id'=>7, 'en_name'=>'Kaka','sex'=>'m','title'=>'wenka','mobile'=>'15626183525','email'=>'wenka.zhang@shanyougame.com','qq'=>'290144041','native_place'=>'广东省增城', 'job_description'=>'test', 'job_status'=>'intern', 'regularized_date'=>'2014-9-3', 'birthday'=>'2014-9-13','entry_day'=>'2014-08-13' );
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $department_id = empty($_POST['department_id']) ? '' : $_POST['department_id'];
        $cn_name = empty($_POST['cn_name']) ? '' : htmlspecialchars($_POST['cn_name']);
        $en_name = empty($_POST['en_name']) ? '' : htmlspecialchars($_POST['en_name']);
        $sex = empty($_POST['sex']) ? '' : $_POST['sex'];
        $title = empty($_POST['title']) ? '' : htmlspecialchars($_POST['title']);
        $mobile = empty($_POST['mobile']) ? '' : $_POST['mobile'];
        $email = empty($_POST['email']) ? '' : $_POST['email'];
        $qq = empty($_POST['qq']) ? '' : $_POST['qq'];
        $native_place = empty($_POST['native_place']) ? '' : htmlspecialchars($_POST['native_place']); //添加员工字段
        $job_description = empty($_POST['job_description']) ? '' : htmlspecialchars($_POST['job_description']);
        $job_status = empty($_POST['job_status']) ? '' : $_POST['job_status'];
        $regularized_date = empty($_POST['regularized_date']) ? '' : $_POST['regularized_date'];
        $birthday = empty($_POST['birthday']) ? '' : $_POST['birthday'];
        $entry_day = empty($_POST['entry_day']) ? '' : $_POST['entry_day'];
        $response = array('code'=>'-1' , 'msg'=>'edit user failed');//

        if(!preg_match('/^[1-9]\d*$/', $id) || !preg_match('/^[1-9]\d*$/', $department_id) || empty($cn_name) or empty($title) or empty($mobile) or empty($mobile) or empty($qq) or empty($native_place))
        {
            $response['code']=-3;
            $response['msg'] = 'the info can not be null';
        }
        else if(!$user = Users::model()->findByPk($id))
        {
            $response['code']=-2;
            $response['msg'] = 'user not found';
        }
        else if (!in_array("$sex", array('f','m')))
        {
            $response['code']=-4;
            $response['msg'] = 'the sex is wrong';
        }
        else if(!in_array($job_status, array('formal_employee', 'probation_employee', 'intern')))
        {
            $response['code']=-5;
            $response['msg'] = 'the job_status is wrong';
        }
        else if(!preg_match('/^\d{4}-?\/?\d{1,2}-?\/?\d{1,2}$/', $regularized_date))
        {
            $response['code']=-6;
            $response['msg'] = 'the regularized_date format is wrong';
        }
        else if(!preg_match('/^[\w\.\-\_]+@[\.\w\-\_]+$/' , $email))
        {
            $response['code']=-7;
            $response['msg'] = 'please input a right email';
        }
        else if(Users::updateUser($user , array('cn_name'=>$cn_name, 'en_name'=>$en_name,'department_id'=>$department_id, 'gender'=>$sex,'title'=>$title,'mobile'=>$mobile,'email'=>$email,'qq'=>$qq, 'native_place'=>$native_place, 'job_description'=>$job_description, 'job_status'=>$job_status, 'regularized_date'=>$regularized_date, 'birthday'=>$birthday, 'entry_day'=>$entry_day)))
        {
            $response['code']=0;
            $response['msg'] = 'edit user success';
        }
        echo CJSON::encode($response);
    }


    
    /**
     *创建用户
     *@url /ajax/createUser
     *@param string $pId 部门ID
     *@param string $entry_day 入职日期 
     *@param string $job_status  ENUM('intern','probation_employee','formal_employee') intern' 实习生 , 'probation_employee' 试用员工 ,'formal_employee' 正式员工
     *@param string $login 域用户名
     *@param string $cn_name 中文名
     *@param string $en_name 英文名
     *@param string $sex      ENUM('m','f') 性别
     *@param string $title   职位
     *@param string $mobile 手机
     *@param string $email 邮件
     *@param string $qq    QQ
     *@param varchar $native_place 籍贯
     *@param varchar $job_description 岗位说明
     *@param string  $job_status ENUM(' intern', 'probation_employee', 'formal_employee') 职位状态 
     *@param date   $regularized_date 转正日期
     *@param date   $birthday 出生日期
     *@param date   $entry_day 入职日期
     *@return array()
     #{'code':0 ,  'msg':'create user success'} //编辑成功
     #{'code':-1 , 'msg':'create user failed'}   //编辑失败
     #{'code':-2 , 'msg':'login name duplicate'};    //域用户名重复
     #{'code':-3 , 'msg':'the info can not be null'}//信息不能为空
     #{'code':-4 , 'msg':'the sex is wrong'}//性别错误
     #{'code':-5 , 'msg':'the job_status is wrong'}//职位状态错误
     #{'code':-6 , 'msg':'the regularized_date format is wrong'}//转正日期格式错误
     #{'code':-7 , 'msg':'please input a right email'} //请输入正确的email
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionCreateUser()
    {
        #$_POST = array('pId'=>'7','cn_name'=>'','en_name'=>'Kaka','sex'=>'m','title'=>'wenka1','mobile'=>'15626183525','email'=>'wenka.zhang@shanyougame.com','qq'=>'290144041','native_place'=>'广东省增城', 'job_description'=>'test', 'job_status'=>'intern',  'birthday'=>'2014-9-13', 'entry_day'=>'2015-08-13','login'=>'wenka1.zhang@shanyougame.com');
        $department_id = empty($_POST['pId']) ? '' : $_POST['pId'];
        $login = empty($_POST['login']) ? '' : $_POST['login'];
        $entry_day = empty($_POST['entry_day']) ? '' : $_POST['entry_day'];
        $job_status = empty($_POST['job_status']) ? '' : $_POST['job_status'];
        $cn_name = empty($_POST['cn_name']) ? '' : $_POST['cn_name'];
        $en_name = empty($_POST['en_name']) ? '' : $_POST['en_name'];
        $sex = empty($_POST['sex']) ? '' : $_POST['sex'];
        $title = empty($_POST['title']) ? '' : $_POST['title'];
        $mobile = empty($_POST['mobile']) ? '' : $_POST['mobile'];
        $email = empty($_POST['email']) ? '' : $_POST['email'];
        $qq = empty($_POST['qq']) ? '' : $_POST['qq'];
        $native_place = empty($_POST['native_place']) ? '' : htmlspecialchars($_POST['native_place']); //添加员工字段
        $job_description = empty($_POST['job_description']) ? '' : htmlspecialchars($_POST['job_description']);
        $job_status = empty($_POST['job_status']) ? '' : $_POST['job_status'];
        $birthday = empty($_POST['birthday']) ? '' : $_POST['birthday'];
        $create_time = date('Y-m-d');
        $regularized_date = date('Y-m-d',strtotime('+1month',strtotime($entry_day)));
        
        $response = array('code'=>'-1' , 'msg'=>'create user failed');

        if(empty($_POST))
        {
            $response['code']=-1;
            $response['msg'] = 'create user failed';
        }
        elseif(Users::model()->find("login=:login",array(":login"=>$login)))
        {
            $response['code']=-2;
            $response['msg'] = 'login name duplicate';
        }
        else if(empty($cn_name) or empty($title) or empty($mobile) or empty($mobile) or empty($qq) or empty($native_place))
        {
            $response['code']=-3;
            $response['msg'] = 'the info can not be null';
        }
        else if (!in_array("$sex", array('f','m')))
        {
            $response['code']=-4;
            $response['msg'] = 'the sex is wrong';
        }
        else if(!in_array($job_status, array('formal_employee', 'probation_employee', 'intern')))
        {
            $response['code']=-5;
            $response['msg'] = 'the job_status is wrong';
        }
        else if(!preg_match('/^\d{4}-?\/?\d{1,2}-?\/?\d{1,2}$/', $regularized_date))
        {
            $response['code']=-6;
            $response['msg'] = 'the regularized_date format is wrong';
        }
        else if(!preg_match('/^[\w\.\-\_]+@[\.\w\-\_]+$/' , $email))
        {
            $response['code']=-7;
            $response['msg'] = 'please input a right email';
        }
        elseif(Users::updateUser(new Users() , array('department_id'=>$department_id, 'cn_name'=>$cn_name, 'en_name'=>$en_name,'gender'=>$sex,'title'=>$title,'mobile'=>$mobile,'email'=>$email,'qq'=>$qq, 
            'second_department'=>'0',  'login'=>$login,'native_place'=>$native_place, 'job_description'=>$job_description, 'job_status'=>$job_status, 
            'regularized_date'=>$regularized_date, 'photo'=>'/images/portrait/default.jpg','birthday'=>$birthday, 'entry_day'=>$entry_day, 'login'=>$login,'create_time'=>$create_time )))
        {
            $response['code']=0;
            $response['msg'] = 'create user success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *书本续借 @wk
     *@url /ajax/borrowagain
     *@param INT $borrow_id  图书借阅的ID
     *@return array
     #{'code':0 ,  'msg':''borrow again success'.'  and the   category_id is :'.$category_id;'} //成功
     #{'code':-1 , 'msg':'borrow again fail'}   //失败
     #{'code':-2 , 'msg':'The Book of borrow_id is not found'};    //借书记录不存在
     #{'code':-3 , 'msg':'borrow total_time has larger than 6 month'};
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
     public function actionBorrowagain()
     {
        //$_POST['borrow_id'] = 459 ;  //测试
        $borrow_id = empty($_POST['borrow_id'])?'':$_POST['borrow_id'];
        $response = array('code'=>-1, 'msg'=>'borrow again fail', 'result'=>0);

        if(!$borrow = Borrow::model()->findByPk($borrow_id))
        {
            $response['code']='-2';  
            $response['msg']='The Book of borrow_id is not found;';
        }//默认还书时间减去
        elseif( ((strtotime($borrow->default_returntime) - strtotime($borrow->borrow_time))/(60*60*24)) >= 180 )
        {
                $response['code']='-3';
                $response['msg']='borrow total_time has larger than 6 month';         
        }
        elseif(Borrow::borrowAgain($borrow_id))
        {
          $response['code']='0';                     
          $response['msg']='borrow again success';
        }
        echo CJSON::encode($response);
     }

    /**
     *修改图书
     *url ajax/EditBook
     *@param INT $book_id  图书ID
     *@param int $category_id 图书分类ID
     *@param string $name    图书名称
     *@param string $descript_url  购买的URL
     *@param string $publisher  出版社
     *@param string $author     作者
     #{code:0  'msg':'edit book success'}
     #{code:-1 'msg':'edit book fail'}
     #{code:-2 'msg':'the category_id  can not be zero '} 图书类型ID错误
     #{code:-3 'msg':'the book name can not be null or zero'} 图书名称错误
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     **/
     public function actionEditBook()
     {
           $book_id = empty($_POST['book_id'])? '': $_POST['book_id'];
           $name = empty($_POST['name'])? '': $_POST['name'];
           $category_id = empty($_POST['category_id'])? '': $_POST['category_id'];
           $descript_url = empty($_POST['descript_url'])? '': $_POST['descript_url'];
           $publisher = empty($_POST['publisher'])? '': $_POST['publisher'];
           $author = empty($_POST['author'])? '': $_POST['author'];
           $response = array('code' => -1,'msg' => 'edit book fail');

           if(!preg_match('/^[1-9]\d*$/',$category_id))
           {
                $response['code'] = -2;
                $response['msg'] = 'the category_id can not be zero';
           }
           else if(!$category = BookCategory::model()->findByPk($category_id))
           {
                $response['code'] = -2;
                $response['msg'] = 'the category_id can not be zero';
           }
           else if(empty($name))
           {
                $response['code'] = -3;
                $response['msg'] = 'the book name can not be null or zero';
           }
           else if(!$book = Books::model()->findByPk($book_id))
           {
                $response['code'] = -3;
                $response['msg'] = 'the book name can not be null or zero';
           }
           else if(Books::EditBook($book, array('book_id'=>$book_id, 'name'=>$name, 'category_id'=>$category_id, 
               'descript_url'=>$descript_url, 'publisher'=>$publisher, 'author'=>$author, 'update_time'=>date('Y-m-d H:i:s'))))
           {
                $response['code'] = 0;
                $response['msg'] = 'edit book success';
           }
           echo CJSON::encode($response);
     }
    
    /**
     *删除图书
     *url ajax/DeleteBook
     *@param INT $book_id       图书ID
     *@param varchar $loss_note 删除备注
     *{code:0  'msg':'delete book success'}
     *{code:-1 'msg':'delete book fail'}//删除失败
     *{code:-2 'msg':'please input delete reason'} //输入删除原因
     *{code:-3 'msg':'book is not found'}//此书不存在
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     **/
     public function actionDeleteBook()
     {
            $book_id = empty($_POST['book_id'])? '': $_POST['book_id'];
            $loss_note = empty($_POST['loss_note'])? '': htmlspecialchars($_POST['loss_note']);
            $response = array('code' => -1,'msg' => 'delete book fail');
            if(empty($loss_note) )
            {
                $response['code'] = -2;
                $response['msg'] = 'please input delete reason';
            }
            elseif( !$book = Books::model()->findByPK($book_id))
            {
                $response['code'] = -3;
                $response['msg'] = 'the book is not found';
            }
            else
            {
                $response = Books::DeleteBook($book,$loss_note);
            }
            echo CJSON::encode($response);
     }
     
     /**
     *出差申请
     *url ajax/AddOut
     *@param string $content 出差理由，
     *@param string $company 出差公司 ,
     *@param string $place 出差地点，
     *@param string $transport 交通工具，
     *@param DECIMAL(7,2) $cost 预算费用,
     *@param datetime $start_time   开始时间
     *@param datetime $end_time   结束时间
     *@param string $delay        延迟说明
     *@param string $plan         行程计划
     *@param string $type ENUM('business','meeting','out','recruit') 商务洽谈 会议 市内外出 校园招聘
     *@param stirng $date_type ENUM('normal','morning','afternoon')  正常  一上午 一下午
     *@param array  $member  同行人ID
     *@return array
     #{code:0  'url':'' 'msg':'add Out sucess'}
     #{code:-1 'url':'' 'msg':'add Out fail'}
     #{code:-2 'url':'' 'msg':'param error'}
     #{'code':-5   'url':'' 'msg':'please input dealy'}//请输入延迟提交原因
     #{'code':-7   'url':'' 'msg':'type error'}//不是本市的不能选择外出类型
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     **/
     public function actionAddOut()
     {  
        #$_POST = array('content'=>'出差', 'place'=>'广东省深圳市增城','company'=>'阳光乐园', 'transport'=>array('飞机','汽车'),'cost'=>99, 'start_time'=>'2014-09-29 09:30','end_time'=>'2014-09-30 12:00','create_time' => date('Y-m-d H:i:s'),'plan'=>'planplanplan', 'delay'=>'x','tilte'=>'测试出差','type'=>'meeting', 'member'=>array(1,2),'date_type'=>'normal' );
        $content = empty($_POST['content']) ? '' : htmlspecialchars($_POST['content']);
        $place = empty($_POST['place']) ? '' : htmlspecialchars($_POST['place']);
        $company = empty($_POST['company']) ? '' : htmlspecialchars($_POST['company']);
        $start_time = empty($_POST['start_time']) ? '' : date('Y-m-d H:i:s', strtotime($_POST['start_time'].':00'));
        $end_time = empty($_POST['end_time']) ? '' : date('Y-m-d H:i:s', strtotime($_POST['end_time'].':00')); 
        $type = empty($_POST['type'])?'': htmlspecialchars($_POST['type']);
        $date_type = empty($_POST['date_type'])?'': htmlspecialchars($_POST['date_type']);
        //如果不是补交的话 就没有这项
        $delay = empty($_POST['delay'])?'':htmlspecialchars($_POST['delay']);
        //也可能没有同行的人
        $member = empty($_POST['member']) ? array() : $_POST['member'];
        //如果是市内出差的，就没有这三项
        $transport = empty($_POST['transport']) ? array() : $_POST['transport'];
        $plan = empty($_POST['plan'])?'': htmlspecialchars($_POST['plan']);
        $cost = empty($_POST['cost']) ? '0' : $_POST['cost'];

        $response = array('code'=>-1 ,'url'=>'', 'msg'=>'add Out fail');

        if(!preg_match('/^\d+(\.\d{1,2})?$/', $cost) || empty($content) || empty($place) || empty($company) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\:00$/', $start_time)|| !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\:00$/', $end_time) || !in_array($type , array('business','meeting','out','recruit')) || !in_array($date_type,array('normal','morning','afternoon')) || $start_time > $end_time || (!empty($member) && !is_array($member)))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        elseif($type == 'out' && !preg_match('/^广东省广州市/', $place))
        {
            $response['code'] = -7;
            $response['msg'] = 'type error';
        }
        elseif($start_time < date('Y-m-d H:i') && empty($delay) )
        {
            $response['code'] = -5;
            $response['msg'] = 'please input delay';
        }
        else if($out = Out::addOutTransaction($this->user, $content, $company, $place, $transport,$cost,$start_time,$end_time,$delay,$plan, $type, $date_type, array_merge($member,array(Yii::app()->session['user_id']))))
        {
                $response['code'] = 0;
                $response['url'] = "/user/outMsg/out/{$out->out_id}";
                $response['msg'] = 'add out success';
        }
        echo CJSON::encode($response);
     }
     
     /**
      *同意出差的接口
      *@url ajax/agreeOut
      *@param int id  出差ID
      *@return array
      #{code:0  'msg':'agree success'}
      #{code:-1 'msg':'agree out failed'}
      #{code:-2 'msg':'param error'}
      #{code:-3 'msg':'not found'}
      #{'code'=>'-99','msg'=>'permission denied'}   //没有权限      
      */
     public function actionAgreeOut()
     {
         $id = empty($_POST['id']) ? '' : $_POST['id'];
         $response = array('code'=>'-1' , 'msg'=>'agree out failed');
         //不符合要求的数字
         if(!preg_match('/^[1-9]\d*$/' , $id))
         {
             $response['code'] = -2;
             $response['msg'] = 'param error';
         }
         else if(!$out = Out::model()->findByPk($id))
         {
             $response['code'] = -3;
             $response['msg'] = 'not found';
         }
         else if($out->next != $this->user->user_id)
         {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
         }
         else if( Out::ApproveAgree($out) )
         {
             $response['code'] = 0;
             $response['msg'] = 'agree success';
         }
         echo CJSON::encode($response);
     }

     /**
      *不同意出差的接口
      *@url /ajax/rejectOut
      *@param int $id          出差ID
      *@param string　$reason  拒绝理由
      *@return array
      *{"code":0,"msg":"reject out success"} //拒绝成功
      *{"code":-1,"msg":"reject out failed"} //拒绝失败
      *{"code":-2,"msg":"param error"}       //参数错误
      *{"code":-3,"msg":"not found"}         //没有找到此出差单
      *{"code'=>'-99','msg'=>'permission denied'}   //没有权限  
      */
     public function actionRejectOut()
     {
         $id = empty($_POST['id']) ? '' : $_POST['id'];
         $reason = empty($_POST['reason']) ? '' : htmlspecialchars($_POST['reason']);
         $response = array('code'=>'-1' , 'msg'=>'reject out failed');
         //不符合要求的数字
         if(!preg_match('/^[1-9]\d*$/' , $id) || mb_strlen($reason) == 0 )
         {
             $response['code'] = -2;
             $response['msg'] = 'param error';
         }
         else if(!$out = Out::model()->findByPk($id))
         {
             $response['code'] = -3;
             $response['msg'] = 'not found';
         }
         else if($out->next != $this->user->user_id)
         {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
         }
         else if(!$rs=Out::processOut($out, array('status'=>'reject', 'reason'=>$reason, 'next'=>'0')))
         {
             $response['code'] = -1;
             $response['msg'] = 'reject out failed';
         }
         else if(!OutLog::addLog(array('out_id'=>$id, 'approver_id'=>$this->user->user_id, 'status'=>'reject', 'create_time'=>date('Y-m-d H:i:s'))))
         {
             $response['code'] = -1;
             $response['msg'] = 'reject out failed';
         }
         else if(Out::noticeHeadsTransaction($out , '未通过'))
         {
             $response['code'] = 0;
             $response['msg'] = 'reject out success';
         }
         echo CJSON::encode($response);
     }
     
      /**
      *邮件通知
      *@url /ajax/Mail
      *@param array() $emails 存储收件人邮箱地址以及收件人的名称的数组
      *@param int $user_id 发送邮件的用户ID
      *@param varchar sender_email  发件人
      *@param varchar $subject 邮件标题
      *@param varchar    $message  邮件正文
      *{"code":0,"msg":"send success "}    //发送成功
      *{"code":-1,"msg":"failed to send email"} //发送失败
      *{"code":-2,"msg":"please input a right email"}          //请输入正确的邮箱地址
      *{"code":-3,"msg":"subject or message can not be null"}            //标题或正文不能为空
      *{"code":-4,"msg":"please input right sender_email"}            //请输入正确发送人邮件
      *{"code'=>'-99','msg'=>'permission denied'}   //没有权限
      */  
     public function actionMail()
     {  
        #$_POST = array('emails'=>array('wenka.zhang@shanyougame.com','wenka.zhang@shanyougame.com','wenka.zhang@shanyougame.com','wenka.zhang@shanyougame.com'), 'user_id'=>32, 'subject'=>'<script>sfgf</script>ceshi jiekou', 'message'=>'message','sender_email'=>'hr@shanyougame.com');
        $emails = empty($_POST['emails']) ? '' : $_POST['emails'];
        $user_id = empty($_POST['user_id'])?'':$_POST['user_id'];
        $sender_email = empty($_POST['sender_email'])?'':$_POST['sender_email'];
        $update_time = $create_time = date('Y-m-d H:i:s');

        $subject = empty($_POST['subject']) ? '' : $_POST['subject'];
        $subject = str_replace('script', '', $subject);
        $subject = empty($subject) ? '' : $subject;

        $message = empty($_POST['message']) ? '' : $_POST['message'];
        $message = str_replace('script', '', $message);
        $message = str_replace('/script', '', $message);
        $message = empty($message) ? '' : $message;

        $response = array('code'=>'-1' , 'msg'=>'failed to send email');

        

        if(!Notice::validEmail($emails))
        {
            $response['code'] = -2;
            $response['msg'] = 'please input a right email';  
        }
        elseif(empty($subject) || empty($message))
        {
            $response['code'] =-3;
            $response['msg'] = 'subject or message can not be null';
        }
        elseif(!preg_match('/^[\w\.\-\_]+@[\.\w\-\_]+$/' , $sender_email))
        {
            $response['code'] = -4;
            $response['msg'] = 'please input right sender_email';
        }
        else if(Mail::createMailMany($emails, array('user_id'=>$user_id, 'sender_email'=>$sender_email
                 ,'subject'=>$subject,'message'=>$message,'update_time'=>$update_time, 'create_time'=>$create_time )))
        {
            $response['code'] = 0;
            $response['msg'] = 'send success';
        }
        echo CJSON::encode($response);
    }
    
    /**
    *删除员工  //只把user的status状态改为quit
    *@url /ajax/DeleteUser
    *@param int user_id 用户ID
    *@return array
    #{'code':0  'msg':'delete success'}//删除成功
    #{'code':-1 'msg':'failed to delete this user'}//删除员工失败
    #{'code':-2 'msg':'please input a right user_id'}//请输入正确员工号
    #{'code':-3 'msg':'failed to found this user'}//没有找到该员工
    #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
    */
    public function actionDeleteUser()
    {
        $user_id = empty($_POST['user_id']) ? '' : $_POST['user_id'];
        $response = array('code'=>'-1' , 'msg'=>'failed to delete this user');
        if(!preg_match('/^[1-9]\d*$/' , $user_id))
        {
            $response['code']=-2;
            $response['msg'] = 'please input a right user_id';
        }
        else if(!$user = Users::model()->findByPk($user_id))
        {
            $response['code']=-3;
            $response['msg'] = 'failed to found this user';
        }
        else if(Users::updateUser($user , array('status'=>'quit')))
        {
            $response['code']=0;
            $response['msg'] = 'edit user success';
        }
        echo CJSON::encode($response);
    }

    /**
     *上传头像
     *@url /ajax/uploadPic
     *@param object $upload_head  上传图片的资源
     *@param string $x            从X坐标开始截取
     *@param string $y            从Y坐标开始截取
     *@param string $width        截取长度
     *@param string $user_id      用户ID
     *@return array()
     #{'code':0  ,'img'=>'/image/portrait/4.jpg' ,'msg':'upload success'}//上传成功
     #{'code':-1 ,'img'=>'','msg':'upload fail'}//上传失败
     #{'code':-2 ,'img'=>'', 'msg':'param error'}//参数错误
     #{'code':-3 ,'img'=>'', 'msg':'not found the user'}//用户不存在
     #{'code':-4 ,'img'=>'', 'msg':'type error'}//图片类型错误
     #{'code':-5 ,'img'=>'', 'msg':'size error'}//图片大小错误
     #{'code'=>'-99' ,'img'=>'','msg'=>'permission denied'}   //没有权限
     */
    public function actionUploadPic()
    {
        $dir = Yii::getPathOfAlias('webroot.images.portrait').DIRECTORY_SEPARATOR;
        $response = array('code'=>'-1' , 'msg'=>'upload file', 'img'=>'');
        $user_id = empty($_POST['user_id'])?'':$_POST['user_id'];
        $x = empty($_POST['x']) ? 0 : $_POST['x'];
        $y = empty($_POST['y']) ? 0 : $_POST['y'];
        $width = empty($_POST['width']) ? 0 : $_POST['width'];
        $pattern = "/^\d+(\.\d+)?$/";
        if(!preg_match($pattern, $x) || !preg_match($pattern, $y) || !preg_match($pattern, $width) || $width < 100 || !preg_match('/^[1-9]\d*$/', $user_id) )
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }   
        else if( empty(Yii::app()->session['admin']) &&  Yii::app()->session['user_id'] != $user_id )
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(!$user = Users::model()->findByPk($user_id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found the user';
        }
        else if(!$image = CUploadedFile::getInstanceByName('upload_head'))
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }
        else if($image->getSize() == 0 || $image->getSize() > 2*1024*1024)
        {
            $response['code'] = -5;
            $response['msg']  =  'size error';
        }
        else if(!in_array($image->type, array('image/jpeg', 'image/png', 'image/gif')))
        {
            $response['code'] = -4;
            $response['msg']  =  'type error';
        }
        else if($image->saveAs($dir.$user_id.".".strtolower($image->extensionName)) && !$image->hasError && 
                Users::cutPic($dir.$user_id.".".strtolower($image->extensionName), $x, $y, $width) && 
                Users::updateUser($user , array('photo'=>"/images/portrait/{$user_id}.".strtolower($image->extensionName))) )
        {
            $response['code'] = 0;
            $response['msg']  =  'upload success';
        }
        echo CJSON::encode($response['code']);
    }

    /**
	 * Logs out the current user and redirect to homepage.
     * @url /ajax/logout
	 */
	public function actionLogout()
	{
        Yii::app()->session->clear();
        Yii::app()->session->destroy();
        Users::__gcCookie('user');
        Users::__gcCookie('pwd');
        if(!empty($this->user))
        {
            Users::updateUser($this->user, array('online'=>'off'));
        }
        $this->user = array();
        header('Location: '.Yii::app()->request->hostInfo.'/user/login');
		//$this->redirect(Yii::app()->homeUrl);
    }

    /**
     *ajax 心跳包 
     *每隔一分钟请求一次
     *@URL /ajax/heartbeat
     *@return array  
     #{'code':'0,'count':'1','msg':'upload heartbeat success'} count为当前未读消息条数
     #{'code':'-1,'count':'0','msg':'upload heartbeat fail'}
     */ 
    public function actionHeartbeat()
    {
        //如果$this->users（当前有用户登录），就更新该用户的心跳时间为当前时间戳
        $response = array('code'=>'-1' , 'count'=>0, 'msg'=>'update heartbeat fail');
        if(!empty($this->user) &&
            Users::updateUser($this->user , array('online'=>'on', 'heartbeat'=>time())))
        {
            $response['code'] = 0;
            $response['count'] = $this->user->msgCount;
            $response['msg']  =  'upload heartbeat success';
        }
        echo CJSON::encode($response);
    }

     /**
      *标记全部消息为已读
      *@URL /ajax/markAllRead
      *@return array
      #{'code':0  ,'msg':'set read success'}//设置已读成功
      #{'code':-1 ,'msg':'set read fail'}//设置已读失败
      #{'code':-2 ,'msg':'no wait msg'}//没有未读消息
      #{'code'=>'-99' ,'msg'=>'permission denied'}   //没有权限
      **/
     public function actionMarkAllRead()
     {
        $models = Notice::model()->findAll('status=:status and user_id=:user_id', array(':status'=>'wait', ':user_id'=>$this->user->user_id));
        $data = array('status'=>'read');
        $response = array('code'=>'-1' , 'msg'=>'set read file' );
        if(empty($models))
        {
            $response['code'] = -2;
            $response['msg']  =  'no wait msg'; 
        }
        else if(Notice::MarkAllRead($models, $data))
        {
            $response['code'] = 0;
            $response['msg']  =  'set read success'; 
        }
        echo CJSON::encode($response);
     }
      
     /**
      *公司内部邮件发送
      *@URL /ajax/CreateMailMany
      *@param int $user_id 发送邮件的用户ID
      *@param array  $user_ids  接收用户id的数组
      *@param varchar subject 邮件标题
      *@param varchar message 邮件内容
      *@param varchar sender_email  发件人
      *{'code':0  ,'msg':'send success'}//发送成功
      *{'code':-1 ,'msg':'send mail fail'}//发送失败
      *{'code':-2 ,'msg':' array $user_ids can not be null'}//收件人不能为空
      *{'code':-3 ,'msg':'some user_id are not found in Users'}//收件人不存在
      *{'code':-4 ,'msg':'please input right sender_email'}//发送邮件人邮件格式不正确
      *{'code':-5 ,'msg':'sender_user is not found'}//发送邮件人不存在
      *{'code':-6 ,'msg':'subject or message can not be null'}//标题或内容不能为空
      *{'code'=>'-99' ,'msg'=>'permission denied'}   //没有权限
      **/
     public function actionCreateMailMany()
     {  
        #$_POST = array('user_ids'=>array(1,2,3,4), 'user_id'=>32, 'subject'=>'<script>sfgf</script>ceshi jiekou', 'message'=>'message','sender_email'=>'hr@shanyougame.com');
        $user_ids = empty($_POST['user_ids'])?'':$_POST['user_ids'];
        //data = array('user_id'=>$user_id, 'sender_email'=>$sender_email, 'subject'=>$subject, 'message'=$message);
        $user_id = empty($_POST['user_id'])?'':$_POST['user_id'];
        // $user_id = $this->user->user_id;
        $sender_email = empty($_POST['sender_email'])?'':$_POST['sender_email'];
        
        $subject = empty($_POST['subject'])?'':$_POST['subject'];
        $subject = str_replace('<script>', '', $subject);
        $subject = str_replace('</script>', '', $subject);
        $subject = empty($subject) ? '' : $subject;
        
        $message = empty($_POST['message'])?'':$_POST['message'];
        $message = str_replace('script', '', $message);
        $message = str_replace('/script', '', $message);
        $message = empty($message) ? '' : $message;
        
        $update_time = $create_time = date('Y-m-d H:i:s');
        $response = array('code'=>-1 , 'msg'=>'send email failed');

        if(empty($user_ids))
        {
            $response['code'] = -2;
            $response['msg'] = 'array $user_ids can not be null';
        }
        elseif( !$emails = Users::foundUser($user_ids) )
        {
            $response['code'] = -3;
            $response['msg'] = 'some user_id are not found in Users';
        }
        elseif(!preg_match('/^[\w\.\-\_]+@[\.\w\-\_]+$/' , $sender_email))
        {
            $response['code'] = -4;
            $response['msg'] = 'please input right sender_email';
        }
        elseif(!Users::model()->findByPk($user_id))
        {
            $response['code'] = -5;
            $response['msg'] = 'sender_user is not found';
        }
        elseif(empty($subject) || empty($message))
        {
            $response['code'] =-6;
            $response['msg'] = 'subject or message can not be null';
        }
        elseif(Mail::createMailMany($emails, array('user_id'=>$user_id, 'sender_email'=>$sender_email
                 ,'subject'=>$subject,'message'=>$message,'update_time'=>$update_time, 'create_time'=>$create_time )))
        {
            $response['code'] = 0;
            $response['msg'] = 'send emial success';            
        } 
        
        echo CJSON::encode($response);
     }

    /*
     *批准请假
     *@url /ajax/agreeLeave
     *@param string $id  请假记录ID
     *@return array
     *{"code":0,"msg":"leave agree success"}//同意成功
     *{"code":-1,"msg":"agree leave fail"}//同意失败
     *{"code":-2,"msg":"leave not found"}//请假记录未发现
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionAgreeLeave()
    {
        
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $user_id = Yii::app()->session['user_id'];
        $user = $this->user;
        $response = array('code'=>-1, 'msg'=>'agree leave fail');

        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg'] = "leave not found";
        }
        elseif(!$leave = Leave::model()->findByPk($id))
        {
            $response['code'] = -2;
            $response['msg'] = "leave not found";
        }
        elseif($leave->next != $user->user_id)
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        elseif( $procedure_list = CJSON::decode($leave['procedure_list'], true) )
        {
            //如果当前审批者为最后一个审批者，则请假单完成
            //判断用户是否还有补休的天数
            if( ($leave->type == 'compensatory') && ( Overtime::getCompensatTime($leave->user_id) < $leave->total_days ) ) {
                $response['code'] = -100;
                $response['msg'] = 'compensatory over flow';
            }
            //判断用户是否还有年假
            else if ( ($leave->type == 'annual') && ( $leave->user->userAnnualLeaveDays->total < $leave->total_days ) ) {
                $response['code'] = -101;
                $response['msg'] = 'annual over flow';
            }
            else if($user->user_id == end($procedure_list) ) {
                if( Leave::successTransaction($leave, $user) ) {
                    $response['code'] = 0;
                    $response['msg'] = 'leave agree success';
                }
            }
            else{     //通知下一个审批者
                foreach ( $procedure_list as $key => $value) {
                    if($value == $user->user_id) {
                        break;
                    }
                }
                $next_user_id = $procedure_list[$key+1];
                if( Leave::passNext($leave,$next_user_id,$user_id) ) {
                    $response['code'] = 0;
                    $response['msg'] = 'leave agree success';
                }
            }
        }
        //如果流程信息为空，则部门主管审批完之后就算结束
        elseif(Leave::successTransaction($leave, $user) )
        {
            $response['code'] = 0;
            $response['msg'] = 'leave agree success';
        }
        echo CJSON::encode($response);
    }
    /**
     *给请假报表添加注释
     *@url /ajax/addLeaveFormComment
     *@param string $id  请假报表的记录ID
     *@param string $content 备注内容
     *@return array()
     #{"code":0,"msg":"add comment success"}//同意成功
     #{"code":-1,"msg":"add comment  fail"}//同意失败
     #{"code":-2,"msg":"param error"}//参数错误
     #{"code":-3,"msg":"leave form not found"}//请假记录未发现
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
     public function actionAddLeaveFormComment()
     {
         //$_POST = array('id'=>2, 'content'=>'你好你好');
         $id = empty($_POST['id']) ? '' : $_POST['id'];
         $content = empty($_POST['content']) ? '' : htmlspecialchars($_POST['content']);
         $response = array('code'=>'-1','msg'=>'add comment  fail');
         if(!preg_match('/^[1-9]\d*$/', $id) || empty($content))
         {
            $response['code'] = -2; 
            $response['msg'] = 'param error'; 
         }
         else if(!$leave_report = LeaveMonthReport::model()->findByPk($id))
         {
            $response['code'] = -3; 
            $response['msg'] = "leave form not found";
         }
         else if(empty($this->user) || $this->user->department_id != Department::adminDepartment()->department_id)
         {
            $response['code'] = -99; 
            $response['msg'] = "permission denied";
         }
         else if(LeaveMonthReport::processLeave($leave_report, array('content'=>$content)))
         {
            $response['code'] = 0; 
            $response['msg'] = "add comment success";
         }
         echo CJSON::encode($response);
     }

    /**
    *图书详情
    *URL ajax/BooksDetail
    *@param int $book_id 图书ID
    *@return array
    #{"code":0,'borrow_record':'$borrow_record','book_detail':'$book_detail' "msg":"get book success"}//获取成功
    #{"code":-1,'borrow_record':'', 'book_detail':'', "msg":"get book detail fail"}//获取失败
    #{"code":-2,'borrow_record':'', 'book_detail':'', "msg":"the book_id is wrong"}//书本id错误
    #{"code":-3,'borrow_record':'', 'book_detail':'', "msg":"the borrowrecord is not found"}//书本借阅记录不存在
    #{'code'=>'-99','detail':'','msg'=>'permission denied'}   //没有权限
    */
    public function actionBooksDetail()
    {
        #$_POST['book_id'] = 2;
        $book_id = empty($_POST['book_id']) ? '' : $_POST['book_id'];
        $response = array('code'=>-1,'detail'=>'','book_detail'=>'', 'msg'=>'get book detail fail');

        if(!preg_match('/^\d+$/', $book_id))
        {
            $response['code'] = -2;
            $response['borrow_record'] = '';
            $response['msg'] = 'the book_id is wrong';            
        }
        elseif(!$borrow_record = Borrow::booksDetail($book_id))
        {
            $response['code'] = -3;
            $response['borrow_record'] = '';
            $response['msg'] = 'the borrowrecord is not found';
        }
        else
        {   
            $response['code'] = 0;
            $response['borrow_record'] = $borrow_record;
            $response['msg'] = 'get book success';
        }
        echo CJSON::encode($response);
    } 

    /**
    *计算请假时间
    *url ajax/countdays
    *@param  DATE $start： 开始时间
    *@param  DATE $end：结束时间
    *@return array
    #{"code":0, "count":'count' ,"msg":"count time success"}//计算成功 count为天数以0.5为单位
    #{"code":-1, "count":'' ,"msg":"count fail"}//计算失败
    #{"code":-2, "count":'' ,"msg":"start or end is wrong"}//不能为空
    #{"code":-3, "count":'' ,"msg":"you need't to add leave"}//不需要请假
    #{"code":-4, "count":'' ,"msg":"start can not bigger than end"}//不需要请假
    #{'code'=>'-99','detail':'','msg'=>'permission denied'}   //没有权限
    */
    public function actioncountdays()
    {
        #$_POST['start'] = '2014-09-30 09:30';
        #$_POST['end'] = '2014-09-31 18:30';
        $start = empty($_POST['start']) ? '' : $_POST['start'];
        $end = empty($_POST['end']) ? '' : $_POST['end'];
        $response = array('code'=>-1,'count'=>'','msg'=>'count fail');
        
        if(!preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/', $start) || !preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/', $end))
        {
            $response['code'] = -2;
            $response['count'] = '';
            $response['msg'] = 'start or end is wrong';
        }
        elseif(strtotime($start) >= strtotime($end))
        {
            $response['code'] = -4;
            $response['count'] = '';
            $response['msg'] = 'start can not bigger than end'; 
        }
        elseif($count = Holiday::countDays($start,$end))
        {
            $response['code'] = 0;
            $response['count'] = $count;
            $response['msg'] = 'count time success';            
        }
        elseif($count==0)
        {
            $response['code'] = -3;
            $response['count'] = '';
            $response['msg'] = 'you need\'t to add leave';           
        }
        echo CJSON::encode($response);
    }

    /**
     *计算出差日期
     *@url /ajax/outCountDays
     *@param string $start 开始时间
     *@param string $end   结束时间
     *@param string $type ENUM('normal','morning','afternoon') 正常 一上午 一下午
     *@return array
     #{"code":0, "count":'3' ,"msg":"count time success"}//计算成功 count为天数 以0.5为单位
     #{"code":-1, "count":'' ,"msg":"count time fail"}//计算失败
     #{"code":-2, "count":'' ,"msg":"param error"}//参数错误
     #{"code":-3, "count":'' ,"msg":"start can not bigger than end"}//开始时间要大于结束时间
     #{'code'=>'-99','count':'','msg'=>'permission denied'}   //没有权限
     */
    public function actionOutCountDays()
    {
        //$_POST = array('start'=>'2014-11-28 09','end'=>'2014-11-23 18:30');
        $start = empty($_POST['start']) ? '' : $_POST['start'];
        $end = empty($_POST['end']) ? '' : $_POST['end'];
        $type = empty($_POST['type']) ? 'normal' : $_POST['type']; 
        $response = array('code'=>-1,'count'=>'','msg'=>'count time fail');
        
        if(!preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/', $start) || !preg_match('/^\d{4}\-\d{2}\-\d{2}\s\d{2}:\d{2}$/', $end) || !in_array($type,array('normal','morning','afternoon')))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99; 
            $response['msg'] = "permission denied";
        }
        elseif($start >= $end)
        {
            $response['code'] = -3;
            $response['msg'] = 'start can not bigger than end'; 
        }
        elseif($count = Out::countDays($start,$end, $type))
        {
            $response['code'] = 0;
            $response['count'] = $count;
            $response['msg'] = 'count time success';            
        }
        echo CJSON::encode($response);
    }
    /**
     *发邮件时上传通知
     * @url /ajax/imgUpload
     * @param object $upload_pic  上传图片资源
     * @return xml
     # {'code':0,'url':'http://xxxx.com//images/mail/1.jpg','msg':'upload success'}
     # {'code':-1,'url':'','msg':'upload image fail'}
     # {'code':-2,'url':'','msg':'param error'} 参数错误
     # {'code':-4,'url':'','msg':'type error'} //图片类型错误
     # {'code':-5,'url':'','msg':'size error'} //大小错误
     # {'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionImgUpload()
    {
        $response = array('code'=>-1,'url'=>'0', 'msg'=>'upload image fail');
        $dir = Yii::getPathOfAlias('webroot.images.mail').DIRECTORY_SEPARATOR;
        if(!$image = CUploadedFile::getInstanceByName('upload_pic'))
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }
        else if($image->getSize() == 0 || $image->getSize() > 2*1024*1024)
        {
            $response['code'] = -5;
            $response['msg']  =  'size error';
        }
        else if(!in_array($image->type, array('image/jpeg', 'image/png', 'image/gif')))
        {
            $response['code'] = -4;
            $response['msg']  =  'type error';
        }
        else if($image->saveAs($dir.$image->name) && !$image->hasError )
        {
            $response['code'] = 0;
            $response['msg']  =  'upload success';
            $response['url']  = Yii::app()->getRequest()->getHostInfo()."/images/mail/{$image->name}";
        }
        echo "<?xml version='1.0' encoding='utf-8'?>
        <response>
        <code>{$response['code']}</code>
        <msg>{$response['msg']}</msg>
        <url>{$response['url']}</url>
        </response>";
    }


    /**
     *添加入职基本信息
     *@url /ajax/entryDetail
     *@param st4ing $nation 民族
     *@param string $marital_status 婚姻情况 ENUM('married','unmarried','divorce')
     *@param string $fertility enum(yes,no) 生育情况
     *@param string $id_number 身份证号码
     *@param string $education ENUM('high','college','undergraduate','graduate ','master','dr') 高中 大专 本科 研究生 硕士 博士
     *@param string $professional 专业
     *@param string $school 学校
     *@param string $graduation_time 毕业时间
     *@param string $residence      户口所在地
     *@param string $residence_type ENUM('city','rural')户口性质
     *@param string $working_life 工作年限
     *@param string $id_address  身份证号码
     *@param string $present_address 现住地址
     *@param string $hobby   兴趣爱好
     *@param string $forte 特长
     *@param string $emergency_contact 紧急联系人
     *@param string $emergency_telephone 紧急电信
     *@param string $relation 与本人的关系
     *@param string $emergency_address 紧急联系人地址
     *@return array
     #{'code':'0' ,'msg':'add entry detail success'} 添加入职消息成功
     #{'code':'-1','msg':'add entry detail fail'}    添加入职消息失败
     #{'code':'-2','msg':'param error'}              参数错误
     #{'code':'-99','msg':'permission denied'}       没有权限
     */
    public function actionEntryDetail()
    {
        $response = array('code'=>'-1', 'msg'=>'add entry detail fail');
        $data = empty($_POST) ? array() : $_POST;
        $data['graduation_time'] = empty($data['graduation_time']) ? '' : date('Y-m-01', strtotime($data['graduation_time']));
        //找到分类
        if(!$entry = Entry::model()->find('user_id=:user_id' , array(':user_id'=>Yii::app()->session['user_id'])))
        {
            $entry = new Entry();
        }
        if(!$this->actionValidate($data))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        else if(Entry::processEntry($entry,array_merge(array('user_id'=>Yii::app()->session['user_id'] , 'create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = '0';
            $response['msg'] = 'add entry detail success';
        }
        echo CJSON::encode($response);

    }
    private function actionValidate($data)
    {
        if(empty($data['nation'])) return false;
        if(!in_array($data['marital_status'], array('married','unmarried','divorce'))) return false;
        if(!in_array($data['fertility'], array('yes','no'))) return false;
        if(!preg_match('/^\w{18}$/',$data['id_number']) && !preg_match('/^\w{15}$/',$data['id_number'])) return false;
        if(!in_array($data['education'],array('high','college','undergraduate','graduate ','master','dr'))) return false;
        if(empty($data['professional'])) return false;
        if(empty($data['school'])) return false;
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['graduation_time'])) return false;
        if(empty($data['residence'])) return false;
        if(!in_array($data['residence_type'], array('city','rural'))) return false;
        if(!preg_match('/^\d+(\.\d+)?$/' , $data['working_life']))    return false;
        if(empty($data['id_address'])) return false;
        if(empty($data['present_address'])) return false;
        if(empty($data['emergency_contact'])) return false;
        if(!preg_match('/^\d{11}$/', $data['emergency_telephone']) && !preg_match('/^[0-9\-]{12,13}$/', $data['emergency_telephone'])) return false;
        if(empty($data['relation'])) return false;
        if(empty($data['emergency_address'])) return false;
        return true;
    }

    /**
     *添加家庭信息的接口
     *@url /ajax/addFamilyDetail
     *@param array $family array(array('name', 'relation','work','phone'),...) 家庭信息的数据
     *@return array
     #{'code':'0' ,'msg':'add family detail success'} 添加家庭信息成功
     #{'code':'-1','msg':'add family detail fail'}    添加家庭信息失败
     #{'code':'-2','msg':'param error'}               参数错误
     #{'code':'-99','msg':'permission denied'}        没有权限
     */
    // public function actionAddFamilyDetail()
    // {
    //     $data = empty($_POST['family']) ? array() : $_POST['family'];
    //     $response = array('code'=>'-1', 'msg'=>'add family detail fail');

    //     if(!$data = $this->processIndex(array('name', 'relation','work','phone'),$data))
    //     {
    //         $response['code']='-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(!$this->validate('validateFamily',$data))
    //     {
    //         $response['code']='-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(Family::processTransaction(Yii::app()->session['user_id'], $data))
    //     {
    //         $response['code']='0';
    //         $response['msg'] = 'add family detail success';
    //     }
    //     echo CJSON::encode($response);
    // }
    /**
     *封装ajax addfamildetail接口的提交过来的数据
     */
    private function processIndex($keys , $data)
    {
        $result = array();
        $i = 0;
        if(empty($data)) return false;
        foreach($data as $row)
        {
            foreach($row as $key => $value)
            {
                $result[$i][$keys[$key]] =  $value;
            }
            $i++;
        }
        return $result;
    }
    private function validate($func , $data)
    {
        foreach($data as $row)
        {
            if(!$this->$func($row))
            {
                return false;
            }
        }
        return true;
    }
    /**
     *验证家庭信息的数据
     */
    private function validateFamily($data)
    {
        if(empty($data['name'])) return false;
        if(empty($data['relation'])) return false;
        if(empty($data['work'])) return false;
        if(empty($data['phone']) || !preg_match('/^(\d{11})|(\w{12})$/', $data['phone'])) return false;
        return true;
    }

    /**
     *添加教育信息
     *@url /ajax/addEduInfo
     *@param array $edu = array(array('start_date', 'end_date', 'school', 'professional') 教育信息的数据
     *@return array
     #{'code':'0' ,'msg':'add edu detail success'} 添加教育信息成功
     #{'code':'-1','msg':'add edu detail fail'}    添加教育信息失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-99','msg':'permission denied'}     没有权限
     */
    // public function actionAddEduInfo()
    // {
    //     $data = empty($_POST['edu']) ? array() : $_POST['edu'];
    //     $response = array('code'=>-1, 'msg'=>'add edu detail fail');
    //     if(!$data = $this->processIndex(array('start_date', 'end_date', 'school', 'professional'),$data))
    //     {
    //         $response['code'] = '-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(!$this->validate('validateEdu',$data))
    //     {
    //         $response['code'] = '-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(Educate::processTransaction(Yii::app()->session['user_id'], $data))
    //     {
    //         $response['code'] = '0';
    //         $response['msg'] = 'add edu detail success';
    //     }
    //     echo CJSON::encode($response);
    // }

    
    /**
     *验证教育信息的数据
     */
    private function validateEdu($row)
    {
        if(empty($row['start_date']) || !preg_match('/^\d{4}-\d{2}$/',$row['start_date'])) return false;
        if(empty($row['end_date']) || !preg_match('/^\d{4}-\d{2}$/',$row['end_date'])) return false;
        if(empty($row['school'])) return false;
        if(empty($row['professional'])) return false;
        return true;
    }

    /**
     *添加工作信息
     *@url /ajax/addWorkInfo
     *@param array $work = array(array('start_date','end_date','company', 'title') 工作信息的数据
     *@return array
     #{'code':'0' ,'msg':'add work detail success'}  添加工作信息成功
     #{'code':'-1','msg':'add work detail fail'}     添加工作信息失败
     #{'code':'-2','msg':'param error'}              参数错误
     #{'code':'-99','msg':'permission denied'}       没有权限
     */
    // public function actionAddWorkInfo()
    // {
    //     $data = empty($_POST['work']) ? array() : $_POST['work'];
    //     $response = array('code'=>-1, 'msg'=>'add work detail fail');
    //     if(!$data = $this->processIndex(array('start_date','end_date','company', 'title'),$data))
    //     {
    //         $response['code'] = '-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(!$this->validate('validateWork',$data))
    //     {
    //         $response['code'] = '-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(Work::processTransaction(Yii::app()->session['user_id'], $data))
    //     {
    //         $response['code'] = '0';
    //         $response['msg'] = 'add work detail success';
    //     }
    //     echo CJSON::encode($response);
    // }

    /**
     *验证工作信息的数据
     */
    private function validateWork($row)
    {
        if(empty($row['start_date']) || !preg_match('/^\d{4}-\d{2}$/',$row['start_date'])) return false;
        if(empty($row['end_date']) || !preg_match('/^\d{4}-\d{2}$/',$row['end_date'])) return false;
        if(empty($row['company'])) return false;
        if(empty($row['title'])) return false;
        return true;
    }   
    /**
     *个人中心 修改家庭信息
     *@url /ajax/addFamily
     *@param string $name       人员名称
     *@param string $relation   与自己的关系
     *@param string $work       工作单位
     *@param string $phone      手机号码
     *@return array
     #{'code':'0' ,'id':'9','msg':'add family detail success'} //ID为家庭信息记录的ID
     #{'code':'-1','id':'','msg':'add family detail fail'}  添加失败
     #{'code':'-2','id':'','msg':'param error'}             参数错误
     #{'code':'-99','id':'','msg':'permission denied'}      没有权限
     */
    public function actionAddFamily()
    {
        $data = empty($_POST)? array() : $_POST;
        $response = array('code'=>'-1','id'=>'','msg'=>'add family detail fail');
        if(!$this->validateFamily($data))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif($id = Family::processFamily(new Family(), array_merge(array('create_time'=>date('Y-m-d H:i:s'),'user_id'=>Yii::app()->session['user_id']),$data)))
        {
            $response['code'] = '0';
            $response['id'] = $id;
            $response['msg'] = 'add family detail success';
        }
        echo CJSON::encode($response);
    }
    /**
     *个人中心 删除家庭信息
     *@url /ajax/deleteRowFamily
     *@param string $id    家庭信息记录的ID
     *@return array
     #{'code':'0' ,'msg':'delete family detail success'} 删除成功
     #{'code':'-1','msg':'delete family detail fail'}    删除失败
     #{'code':'-2','msg':'param error'}                  参数错误
     #{'code':'-99','msg':'permission denied'}           没有权限
     */
    public function actionDeleteRowFamily()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>'-1','msg'=>'delete family detail fail');

        if(!is_int((int)$id))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif(!$family = Family::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg'] = 'not found';
        }
        elseif(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $family->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        }
        else if(Family::model()->deleteByPk($id))
        {
            $response['code'] = '0';
            $response['msg'] = 'delete family detail success';
        }
        echo CJSON::encode($response);
    }
    /**
     *个人中心 修改家庭信息
     *@url /ajax/updateFamily
     *@param string $id          家庭记录ID
     *@param string $name        人员名称
     *@param string $relation    与自己的关系
     *@param string $work        工作单位
     *@param string $phone       手机号码
     *@return array
     #{'code':'0' ,'msg':'update family detail success'} 更新成功
     #{'code':'-1','msg':'update family detail fail'}    更新失败
     #{'code':'-2','msg':'param error'}                  参数错误
     #{'code':'-3','msg':'not found'}                    没有找到家庭信息记录
     #{'code':'-99','msg':'permission denied'}           没有权限
     */
    public function actionUpdateFamily()
    {
        $response = array('code'=>'-1','msg'=>'update family detail fail');
        $data = empty($_POST)? array() : $_POST;
        if(!$this->validateFamily($data) || empty($data['id']) || !is_int((int)$data['id']))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif(!$family = Family::model()->findByPk($data['id']))
        {
            $response['code'] = '-3';
            $response['msg'] = 'not found';
        }
        elseif(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $family->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        }
        elseif(Family::processFamily($family, $data))
        {
            $response['code'] = '0';
            $response['msg'] = 'update family detail success';
        }
        echo CJSON::encode($response);
    }

    /**
     *添加教育信息
     *@url /ajax/addRowEdu
     *@param string $start_date   开始时间
     *@param string $end_date     结束时间
     *@param string $school       学校名称
     *@param string $professional 专业名称
     *@return array
     #{'code':'0' ,'msg':'add edu detail success'} 添加教育信息成功
     #{'code':'-1','msg':'add edu detail fail'}    添加教育信息失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-99','msg':'permission denied'}     没有权限
     */
    public function actionAddRowEdu()
    {
        $data = empty($_POST) ? array(): $_POST;
        $response = array('code'=>-1,'id'=>'','msg'=>'add edu detail fail');
        if(!$this->validateEdu($data))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif($id = Educate::processDateEdu(new Educate(), array_merge(array('create_time'=>date('Y-m-d H:i:s'),'user_id'=>Yii::app()->session['user_id']),$data)))
        {
            $response['code'] = '0';
            $response['id']   = $id;
            $response['msg'] = 'add edu detail success';
        }
        echo CJSON::encode($response);
    }
    /**
     *个人中心 删除教育信息
     *@url /ajax/deleteRowEdu
     *@param string $id  教育记录的ID
     *@return array
     #{'code':'0' ,'msg':'delete edu detail success'} 删除教育记录成功
     #{'code':'-1','msg':'delete edu detail fail'}    删除教育记录失败
     #{'code':'-2','msg':'param error'}               参数错误
     #{'code':'-3','msg':'not found'}                 没有找到该教育记录
     #{'code':'-99','msg':'permission denied'}        没有权限
     */
    public function actionDeleteRowEdu()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>'-1','msg'=>'delete edu detail fail');
        if(!is_int((int)$id))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif(!$educate = Educate::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg'] = 'not found';
        }
        elseif(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $educate->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        }
        else if(Educate::model()->deleteByPk($id))
        {
            $response['code'] = '0';
            $response['msg'] = 'delete edu detail success';
        }
        echo CJSON::encode($response);
    }
    /**
     *修改教育信息
     *@url /ajax/updateRowEdu
     *@param string $id             教育记录ID
     *@param string $start_date     教育开始时间
     *@param string $end_date       教育结束时间
     *@param string $school         学校名称
     *@param string $professional   专业名称
     *@return array
     #{'code':'0' ,'msg':'add edu detail success'} //更新教育信息成功
     #{'code':'-1','msg':'add edu detail fail'}    更新教育信息失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-3','msg':'not found'}              没有找到该教育记录
     #{'code':'-99','msg':'permission denied'}     没有权限
     */
    public function actionUpdateRowEdu()
    {
        $data = empty($_POST) ? array(): $_POST;
        $response = array('code'=>-1,'msg'=>'add edu detail fail');
        if(!$this->validateEdu($data) || empty($data['id']) || !is_int((int)$data['id'])) 
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif(!$educate = Educate::model()->findByPk($data['id']))
        {
            $response['code'] = '-3';
            $response['msg'] = 'not found';
        }
        elseif(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $educate->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        }
        elseif(Educate::processDateEdu($educate, $data))
        {
            $response['code'] = '0';
            $response['msg'] = 'add edu detail success';
        }
        echo CJSON::encode($response);
    }


    /**
     *添加工作信息
     *@url /ajax/addRowWork
     *@param string $start_date  工作开始时间
     *@param string $date_date   工作结束时间
     *@param string $company     公司名称
     *@param string $title       职位
     *@return array
     #{'code':'0' ,'msg':'add work detail success'} 添加工作信息成功
     #{'code':'-1','msg':'add work detail fail'}    添加工作信息失败
     #{'code':'-2','msg':'param error'}             参数错误
     #{'code':'-99','msg':'permission denied'}      没有权限
     */
    public function actionAddRowWork()
    {
        $data = empty($_POST)?array():$_POST;
        $response = array('code'=>-1,'id'=>'','msg'=>'add work detail fail');
        if(!$this->validateWork($data))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if($id = Work::processDateWork(new Work(),array_merge(array('user_id'=>Yii::app()->session['user_id'],'create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = 0;
            $response['id']   = $id;
            $response['msg'] = 'add work detail success';
        }
        echo CJSON::encode($response);
    }
    /**
     *修改工作信息
     *@url /ajax/editRowWork
     *@param string $id             工作信息记录ID
     *@param string $start_date     工作开始时间
     *@param string $date_date      工作结束时间
     *@param string $company        公司名称
     *@param string $title          公司职位
     *@return array
     *{'code':'0' ,'msg':'edit work detail success'} 更新工作信息成功
     *{'code':'-1','msg':'edit work detail fail'}    更新工作信息失败
     *{'code':'-2','msg':'param error'}              参数错误
     *{'code':'-3','msg':'not found'}                没有找到该工作信息记录
     *{'code':'-99','msg':'permission denied'}       没有权限
     */
    public function actionUpdateRowWork()
    {
        $data = empty($_POST)?array():$_POST;
        $response = array('code'=>-1,'msg'=>'edit work detail fail');
        if(!$this->validateWork($data) || empty($data['id']) || !is_int((int)$data['id']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$work = Work::model()->findByPk($data['id']))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $work->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        }
        else if(Work::processDateWork($work,$data))
        {
            $response['code'] = 0;
            $response['msg'] = 'edit work detail success';
        }
        echo CJSON::encode($response);
    }
    /**
     *个人中心 删除工作信息
     *@url /ajax/deleteRowWork
     *@param string $id        工作记录ID
      *@return array
     *{'code':'0' ,'msg':'delete work detail success'} 删除工作记录成功
     *{'code':'-1','msg':'delete work detail fail'}    删除工作记录失败
     *{'code':'-2','msg':'param error'}                参数错误
     *{'code':'-3','msg':'not found'}                  没有找到工作信息
     *{'code':'-99','msg':'permission denied'}         没有权限
     */
    public function actionDeleteRowWork()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>'-1','msg'=>'delete work detail fail');
        if(!is_int((int)$id))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        else if(!$work = Work::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        elseif(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $work->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        }
        else if(Work::model()->deleteByPk($id))
        {
            $response['code'] = '0';
            $response['msg'] = 'delete work detail success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *修改编制的接口
     *@url /ajax/editFormation
     *@param string $department_id  部门ID
     *@param string $title          职位
     *@param string $number         人数
     *@return array
     #{'code':'0' ,'msg':'edit formation success'} 更新编制成功
     #{'code':'-1','msg':'edit formation fail'}    更新编制失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-3','msg':'not found'}              没有找到该编制
     #{'code':'-99','msg':'permission denied'}     参数错误
     */
    public function actionEditFormation()
    {
//        $_POST = array('department_id'=>1,'title'=>'总经理1','number'=>2);
        $data['department_id'] = empty($_POST['department_id']) ? '' : $_POST['department_id'];
        $data['title']         = empty($_POST['title']) ? '' : $_POST['title'];
        $data['number']        = empty($_POST['number'])? '' : $_POST['number'];
        $response = array('code'=>-1, 'msg'=>'edit formation fail');
            
        if(empty($data['title']) || !preg_match('/^[1-9]\d*$/', $data['number']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!Department::model()->findByPk($data['department_id']))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if(Formation::processTransaction($data))
        {
            $response['code'] = 0;
            $response['msg'] = 'edit formation success';
        }
        echo CJSON::encode($response);
    }
    /**
     *删除编制的接口
     *@url /ajax/deleteFormation
     *@param string $formation_id  编制ID
     *@return array
     #{'code':'0' ,'msg':'delete formation success'} 删除编制成功
     #{'code':'-1','msg':'delete formation fail'}　  删除编制失败
     #{'code':'-2','msg':'param error'}　　　　　　  参数错误
     #{'code':'-3','msg':'not found'}　　　　　　　  没有找到该编制
     #{'code':'-99','msg':'permission denied'}　　　 没有权限
     */
    public function actionDeleteFormation()
    {
        $formation_id = empty($_POST['formation_id']) ? '' : $_POST['formation_id'];
        $response = array('code'=>-1, 'msg'=>'edit formation fail');
            
        if(!preg_match('/^[1-9]\d*$/', $formation_id))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        else if(!Formation::model()->findByPk($formation_id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if(Formation::model()->deleteByPk($formation_id))
        {
            $response['code'] = 0;
            $response['msg'] = 'edit formation success';
        }
        echo CJSON::encode($response);
    }

    /**
     *@ignore
     *编辑资产信息
     *@url /ajax/editProperty
     *@param string $id
     *@param string $name
     *@param string $type
     *@param string $buy_time
     *@return array
     #{'code':'0' ,'msg':'edit property success'}
     #{'code':'-1','msg':'edit property fail'}
     #{'code':'-2','msg':'param error'}
     #{'code':'-3','msg':'not found'}
     #{'code':'-99','msg':'permission denied'}
     */
    // public function actionEditProperty()
    // {
    //     //$_POST = array('id'=>1, 'name'=>'主机','price'=>'3000','buy_time'=>'2014-09-09','type'=>'other');
    //     $data['id'] = empty($_POST['id']) ? '' : $_POST['id'];
    //     $data['name'] = empty($_POST['name']) ? '' : $_POST['name'];
    //     $data['type'] = empty($_POST['type']) ? '' : $_POST['type'];
    //     $data['buy_time'] = empty($_POST['buy_time']) ? '' : $_POST['buy_time'];
    //     $data['price'] = empty($_POST['price']) ? '' : $_POST['price'];
    //     $response = array('code'=>-1, 'msg'=>'edit property fail');
    //     $types = array('it','office','other');

    //     if(empty($data['name'])  || !in_array($data['type'] , $types) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['buy_time']) || !preg_match('/^[1-9]\d*$/', $data['id']) || !preg_match('/^[1-9]\d*(\.\d{1,2})?$/', $data['price']))
    //     {
    //         $response['code']='-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(!$property = Property::model()->findByPk($data['id']))
    //     {
    //         $response['code']='-3';
    //         $response['msg'] = 'not found';
    //     }
    //     else if(Property::processProperty($property , $data))
    //     {
    //         $response['code']='0';
    //         $response['msg'] = 'edit property success';
    //     }
    //     echo CJSON::encode($response);
    // }
    /**
     *@ignore
     *报废资产
     *@url /ajax/discard
     *@param string $id
     *@param string $reason
     *return array
     #{'code':'0' ,'msg':'discard property success'}
     #{'code':'-1','msg':'discard property fail'}
     #{'code':'-2','msg':'param error'}
     #{'code':'-3','msg':'not found'}
     #{'code':'-99','msg':'permission denied'}
     */
    // public function actionDiscard()
    // {
    //     //$_POST = array('id'=>1, 'reason'=>'被vincent打坏了');
    //     $data['id'] = empty($_POST['id']) ? '' : $_POST['id'];
    //     $data['reason'] = empty($_POST['reason']) ? '' : $_POST['reason'];

    //     $response = array('code'=>-1, 'msg'=>'discard property fail');

    //     if(empty($data['reason']) || !preg_match('/^[1-9]\d*$/', $data['id']))
    //     {
    //         $response['code']='-2';
    //         $response['msg'] = 'param error';
    //     }
    //     else if(!$property = Property::model()->findByPk($data['id']))
    //     {
    //         $response['code']='-3';
    //         $response['msg'] = 'not found';
    //     }
    //     else if(Property::processProperty($property , array_merge(array('status'=>'discard'),$data)))
    //     {
    //         $response['code']='0';
    //         $response['msg'] = 'discard property success';
    //     }
    //     echo CJSON::encode($response);
    // }

    /**
     *@ignore
     *添加资产
     *@url /ajax/addProperty
     *@param string $number
     *@param string $type
     *@param string $name
     *@param string $price    最多限制两位小数
     *@param string $buy_time 2014-10-10
     *@param stirng $comment  可以不填写
     *return array
     *{'code':'0' ,'msg':'add property success'}
     *{'code':'-1','msg':'add property fail'}
     *{'code':'-2','msg':'param error'}
     *{'code':'-99','msg':'permission denied'}
     */
    // public function actionAddProperty()
    // {
    //     //$_POST = array('number'=>3 , 'type'=>'it', 'name'=>'airport苹果路由器','price'=>'522.55','buy_time'=>'2014-10-10','comment'=>'222');
    //     $number = empty($_POST['number']) ? '' : $_POST['number'];
    //     $data['type'] = empty($_POST['type']) ? '' : $_POST['type'];
    //     $data['name'] = empty($_POST['name']) ? '' : $_POST['name'];
    //     $data['price'] = empty($_POST['price']) ? '' : $_POST['price'];
    //     $data['buy_time'] = empty($_POST['buy_time']) ? '' : $_POST['buy_time'];
    //     $data['comment'] = empty($_POST['comment']) ? '' : htmlspecialchars($_POST['comment']);

    //     $types = array('it','office','other');
    //     $response = array('code'=>-1, 'msg'=>'add property fail');
        
    //     if(!in_array($data['type'] , $types) || empty($data['name']) || !preg_match('/^\d+(\.\d{1,2})?$/', $data['price']) 
    //         || !preg_match('/^[1-9]\d*$/', $number) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['buy_time']))
    //     {
    //         $response['code'] = -2;
    //         $response['msg'] = 'param error';
    //     }
    //     else if(Property::addProperty($number , $data))
    //     {
    //         $response['code'] = 0;
    //         $response['msg'] = 'add property success';
    //     }
    //     echo CJSON::encode($response);
    // }

    /**
     *预约会议室
     *@url /ajax/bookingMeeting
     *@param string $meeting_date 会议日期
     *@param string $start_time   会议开始时间
     *@param stirng $end_time     会议结束时间
     *@param stirng $room_id      会议室ID
     *@param string $content      开会内容
     *@return array
     #{'code':'0' ,'msg':'booking meeting room success'}  预约会议室成功
     #{'code':'-1','msg':'booking meeting room fail'}     预约会议室失败
     #{'code':'-2','msg':'param error'}                   参数错误
     #{'code':'-3','msg':'room not found'}                没有找到会议室
     #{'code':'-4','msg':'the room has been booked'}      会议室已被预定
     #{'code':'-99','msg':'permission denied'}            没有权限
     */
    public function actionBookingMeeting()
    {
        //$_POST = array('room_id'=>1,'meeting_date'=>'2014-10-10' , 'start_time'=>'13:30:00' ,'end_time'=>'15:00:00','content'=>'xxx');
        $data['meeting_date'] = empty($_POST['meeting_date']) ? '' : $_POST['meeting_date'];
        $data['start_time'] = empty($_POST['start_time']) ? '' : date('H:i:s',strtotime($_POST['start_time']));
        $data['end_time'] = empty($_POST['end_time']) ? '' : date('H:i:s',strtotime($_POST['end_time']));
        $data['content'] = empty($_POST['content']) ? '' : $_POST['content'];
        $data['room_id'] = empty($_POST['room_id']) ? '' : $_POST['room_id'];
        $response = array('code'=>'-1', 'msg'=>'booking meeting room success');

        if(!preg_match('/^[1-9]\d*$/', $data['room_id']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['meeting_date']) || !preg_match('/^\d{2}:\d{2}:\d{2}$/',$data['start_time']) || !preg_match('/^\d{2}:\d{2}:\d{2}$/',$data['start_time']) || empty($data['content']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ($data['end_time'] <= $data['start_time'])
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$room = MeetingRoom::model()->findByPk($data['room_id']))
        {
            $response['code'] = -3;
            $response['msg'] = 'room not found';
        }
        else if(Meeting::model()->find('room_id=:room_id and meeting_date=:meeting_date and start_time <= :start and end_time > :start', array(':room_id'=>$data['room_id'], ':meeting_date'=>$data['meeting_date'], ':start'=>$data['start_time'])) 
            || Meeting::model()->find('room_id=:room_id and meeting_date=:meeting_date and start_time < :end and end_time >= :end', array(':room_id'=>$data['room_id'], ':meeting_date'=>$data['meeting_date'], ':end'=>$data['end_time']))) 
        {
            $response['code'] = -4;
            $response['msg'] = 'the room has been booked';
        }
        else if (Meeting::processMeeting(new Meeting(), array_merge(array('user_id'=>Yii::app()->session['user_id'], 'create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = 0;
            $response['msg'] = 'booking meeting room success';
        }
        echo CJSON::encode($response);

    }

    /**
     *修改预约会议室
     *@url /ajax/editBookingMeeting
     *@param string $id            预约记录ID
     *@param string $meeting_date  会议日期
     *@param string $start_time    会议开始时间
     *@param stirng $end_time      会议结束时间
     *@param stirng $room_id       会议室ID
     *@param string $content       开会内容
     *@return array
     #{'code':'0' ,'msg':'edit booking meeting success'}  修改预约成功
     #{'code':'-1','msg':'edit booking meeting fail'}     修改预约失败
     #{'code':'-2','msg':'param error'}                   参数错误
     #{'code':'-3','msg':'not found'}                     没有找到该会议记录
     #{'code':'-4','msg':'the room has been booked'}      会议室也被约定
     #{'code':'-5','msg':'room not found'}                没有找到会议室
     #{'code':'-99','msg':'permission denied'}            没有权限
     */
    public function actionEditBookingMeeting()
    {
        //$_POST = array('id'=>5,'room_id'=>1,'meeting_date'=>'2014-10-10' , 'start_time'=>'13:30:00' ,'end_time'=>'16:00:00','content'=>'vincent');
        $data['id'] = empty($_POST['id']) ? '' : $_POST['id'];
        $data['meeting_date'] = empty($_POST['meeting_date']) ? '' : $_POST['meeting_date'];
        $data['start_time'] = empty($_POST['start_time']) ? '' : date('H:i:s',strtotime($_POST['start_time']));
        $data['end_time'] = empty($_POST['end_time']) ? '' : date('H:i:s', strtotime($_POST['end_time']));
        $data['content'] = empty($_POST['content']) ? '' : $_POST['content'];
        $data['room_id'] = empty($_POST['room_id']) ? '' : $_POST['room_id'];
        $response = array('code'=>'-1', 'msg'=>'edit booking meeting success');

        if( !preg_match('/^[1-9]\d*$/', $data['id']) || !preg_match('/^[1-9]\d*$/', $data['room_id']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['meeting_date']) || !preg_match('/^\d{2}:\d{2}:\d{2}$/',$data['start_time']) || !preg_match('/^\d{2}:\d{2}:\d{2}$/',$data['start_time']) || empty($data['content']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ($data['end_time'] <= $data['start_time'])
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$meeting = Meeting::model()->findByPk($data['id']))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        elseif(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $meeting->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        } 
        else if(!$room = MeetingRoom::model()->findByPk($data['room_id']))
        {
            $response['code'] = -5;
            $response['msg'] = 'room not found';
        }
        else if(Meeting::model()->find('id != :id and room_id=:room_id and meeting_date=:meeting_date and start_time <= :start and end_time > :start', array(':id'=>$data['id'], ':room_id'=>$data['room_id'], ':meeting_date'=>$data['meeting_date'], ':start'=>$data['start_time'])) 
            || Meeting::model()->find('id != :id and room_id=:room_id and meeting_date=:meeting_date and start_time < :end and end_time >= :end', array(':id'=>$data['id'], ':room_id'=>$data['room_id'], ':meeting_date'=>$data['meeting_date'], ':end'=>$data['end_time']))) 
        {
            $response['code'] = -4;
            $response['msg'] = 'the room has been booked';
        }
        else if(Meeting::processMeeting($meeting ,$data))
        {
            $response['code'] = 0;
            $response['msg'] = 'edit booking meeting success';
        }
        echo CJSON::encode($response);
    }

     /**
     *删除预约会议室
     *@url /ajax/deleteBookingMeeting
     *@param string $id               预约会议室记录ID
     *@return array
     #{'code':'0' ,'msg':'delete booking meeting success'} 删除预约会议室成功
     #{'code':'-1','msg':'delete booking meeting fail'}    删除预约会议室失败
     #{'code':'-2','msg':'param error'}                    参数错误 
     #{'code':'-3','msg':'not found'}                      没有找到该预约记录
     #{'code':'-99','msg':'permission denied'}             没有权限
     */
    public function actionDeleteBookingMeeting()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>'-1', 'msg'=>'delete booking meeting success');

        if( !preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$meeting = Meeting::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if(Yii::app()->session['permission'] != 'admin' && Yii::app()->session['user_id'] != $meeting->user_id)
        {
            $response['code'] = '-99';
            $response['msg'] = 'permission denied';
        } 
        else if(Meeting::model()->deleteByPk($id))
        {
            $response['code'] = 0;
            $response['msg'] = 'delete booking meeting success';
        }
        echo CJSON::encode($response);
    }

    /**
     *根据部门ID来获取编制职位
     *@url /ajax/getTitleByDepartment
     *@param string $id        部门ID
     *@return array()　　count为该部门的定编人数  user_count为该部门的在职人数， titles 各个编制的详情
     #{"code":0,'count'=>5,'user_count'=>4, "titles":[{"formation_id":"12","department_id":"4","title":"\u5de5\u4f5c\u5ba4\u8d1f\u8d23\u4eba","number":"1","create_time":"2014-09-17 11:00:00"},{"formation_id":"13","department_id":"4","title":"\u5de5\u4f5c\u5ba4\u52a9\u7406","number":"1","create_time":"2014-09-17 11:00:00"}],"msg":"get title success"}  获取编制成功
     #{'code':'-1','titles'=>'','msg':'get title fail'}   获取编制失败
     #{'code':'-2','titles'=>'','msg':'param error'}      参数错误
     #{'code':'-3','titles'=>'','msg':'not found'}        没有找到该部门
     #{'code':'-99','titles'=>'','msg':'permission denied'} 没有权限
     */

    public function actionGetTitleByDepartment()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>'-1','count'=>0, 'user_count'=>0, 'titles'=>'', 'msg'=>'get title fail');

        if(!preg_match('/^[1-9]\d*$/' , $id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$deparment = Department::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if($titles = Formation::model()->findAll('department_id=:id',array(':id'=>$id)))
        {
            $response['count'] = $deparment->getFormationCount(); //编制人数
            $response['user_count'] = Users::model()->count('status=:status and department_id=:id',array(':status'=>'work',':id'=>$id)); //在编人数
            $response['code'] = 0;
            $response['titles'] = $titles;
            $response['msg'] = 'get title success';
        }
        echo CJSON::encode($response);
    }

    /**
     *申请招聘
     *@url /ajax/recruitApply
     *@param string $department  部门
     *@param string $title     职位
     *@param string $number    要求人数
     *@param string $entry_day 期望入职时间
     *@param string $pay     薪酬范围
     *@param string $type ENUM('replace','add','internal') 编制内替换 编制外增补 编制内增补
     *@param string $quit_user_id 离职人ID
     *@param string $quit_date    离职日期
     *@param string $add_reason   填补原因
     *@param string $work_content   工作概要
     *@param string $work_life      工作年限
     *@param string $individuality  个性
     *@param string $comment        备注
     *@param string $gender ENUM('m','f','none')  性别
     *@param string $age          年龄
     *@param string $education ENUM('junior','high','college','undergraduate','graduate ','master','dr')初中  高中 大专 本科 研究生 硕士 博士
     *@param string $professional 专业
     *@param string $computer ENUM('great','good','general','none') 计算机
     *@param string $mandarin ENUM('good','general','none') 国语
     *@param string $cantonese ENUM('good','general','none') 粤语
     *@param string $foreign ENUM('good','general','none')  外语
     *@param string $residence 户籍 ENUM('local','nonlocal','none') 本地 外地 不要求
     *@return array()
     *{'code':'0' ,'id':'2','msg':'apply recruit success'} 申请招聘成功 id为申请招聘记录的ID
     *{'code':'-1','msg':'apply recruit fail'}   申请招聘失败
     *{'code':'-2','msg':'param error'}          参数错误
     *{'code':'-4','msg':'more than the preparation'} 超过编制人数
     *{'code':'-5','msg':'preparation not found'}     没有找到编制
     *{'code':'-99','msg':'permission denied'}     没有权限
     */
    public function actionRecruitApply()
    {
        /*$_POST = array( 'department'=>'IT运维部', 'title'=>'运维工程师', 'number'=>'1',
            'entry_day'=>'2014-11-11', 'pay'=>'100-200', 'type'=>'internal', 'quit_user_id'=>'0',
            'quit_date'=>'', 'work_content'=>'xxxxxx', 'work_life'=>'1', 'gender'=>'m',
            'age'=>'0', 'education'=>'high', 'computer'=>'general', 'mandarin'=>'general',
            'cantonese'=>'general', 'foreign'=>'general', 'residence'=>'local', 'add_reason'=>'ddd',
        );*/

        $data['department'] = empty($_POST['department']) ? "" : $_POST['department'];
        $data['title'] = empty($_POST['title']) ? "" : $_POST['title'];
        $data['number'] = empty($_POST['number']) ? "" : $_POST['number'];
        $data['entry_day'] = empty($_POST['entry_day']) ? "" : $_POST['entry_day'];
        $data['pay'] = empty($_POST['pay']) ? "" : $_POST['pay'];
        $data['type'] = empty($_POST['type']) ? "" : $_POST['type'];
        $data['quit_user_id'] = empty($_POST['quit_user_id']) ? "" : $_POST['quit_user_id'];
        $data['quit_date'] = empty($_POST['quit_date']) ? "" : $_POST['quit_date'];
        $data['add_reason'] = empty($_POST['add_reason']) ? "" : $_POST['add_reason'];
        $data['work_content'] = empty($_POST['work_content']) ? "" : $_POST['work_content'];
        $data['work_life'] = empty($_POST['work_life']) ? "0" : $_POST['work_life'];
        $data['individuality'] = empty($_POST['individuality']) ? "" : $_POST['individuality'];
        $data['comment'] = empty($_POST['comment']) ? "" : $_POST['comment'];
        $condition['gender'] = empty($_POST['gender']) ? "" : $_POST['gender'];
        $condition['age'] = empty($_POST['age']) ? "0" : $_POST['age'];
        $condition['education'] = empty($_POST['education']) ? "" : $_POST['education'];
        $condition['professional'] = empty($_POST['professional']) ? "" : $_POST['professional'];
        $condition['computer'] = empty($_POST['computer']) ? "" : $_POST['computer'];
        $condition['mandarin'] = empty($_POST['mandarin']) ? "" : $_POST['mandarin'];
        $condition['cantonese'] = empty($_POST['cantonese']) ? "" : $_POST['mandarin'];
        $condition['foreign'] = empty($_POST['foreign']) ? "" : $_POST['mandarin'];
        $condition['residence'] = empty($_POST['residence']) ? "" : $_POST['residence'];

        $response = array('code'=>'-1','id'=>0,'msg'=>'apply recruit fail');
        $admin = Users::getAdminId();

        $procedure_list = Procedure::getProcedure('recruit',0 ,Yii::app()->session['user_id'] );
        $next = empty($procedure_list)? 0 : $procedure_list[0];
        $data['procedure_list'] = CJSON::encode($procedure_list);

        $language = array('good','general','none');
        if($department = Department::model()->find('name=:name',array(':name'=>$data['department'])))
        {
            $data['parent_id'] = $department->parent->department_id;
        }
        else
        {
            $data['parent_id'] = $this->user->department_id;
        }

        if(empty($data['department'])|| !preg_match('/^\d+$/', $data['parent_id']) || empty($data['title']) || !preg_match('/^\d+$/', $data['number']) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['entry_day']) || empty($data['pay']) || !in_array($data['type'], array('add','internal','replace')) ||
            empty($data['work_content']) || !preg_match('/^\d+$/' , $data['work_life']) ||  !in_array($condition['gender'], array('m','f','none')) || 
            !in_array($condition['education'],array('junior','high','college','undergraduate','graduate','master','dr')) 
            || !in_array($condition['computer'], array('great','good','general','none')) 
            || !preg_match('/^\d+$/' , $condition['age']) ||  !in_array($condition['mandarin'],$language) || !in_array($condition['cantonese'],$language) || 
            !in_array($condition['foreign'],$language) || !in_array($condition['residence'], array('local','nonlocal','none')))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(in_array($data['type'], array('internal','add')) && empty($data['add_reason']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if($data['type'] == 'replace' && (!preg_match('/^\d+$/',$data['quit_user_id']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['quit_date'])))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif($data['type'] == 'internal' && (empty($department->department_id) || !$formation = Formation::model()->find("department_id=:department_id and title=:title",array(':department_id'=>$department->department_id,':title'=>$data['title']))))
        {
            $response['code'] = -5;
            $response['msg'] = 'preparation not found';
        }
        elseif($data['type'] == 'internal' && Formation::getVacancyNum($formation) < $data['number'])
        {
            $response['code'] = -4;
            $response['msg'] = 'more than the preparation';
        }
        else if(!$id = RecruitApply::addRecruitApply(new RecruitApply() , array_merge(array('user_id'=>Yii::app()->session['user_id'],'status'=>'wait','next'=>$next, 'create_date'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = '-1';
            $response['msg'] = 'apply recruit fail';
        }
        else if(RecruitCondition::addRecruitCondition(new RecruitCondition() , array_merge(array('recruit_id'=>$id,'create_time'=>date('Y-m-d H:i:s')),$condition )) && RecruitApply::applyNotitce($this->user, $id, $data['title'], $data['number']))
        {
            $response['code'] = '0';
            $response['id'] = $id;
            $response['msg'] = 'apply recruit success';
        }
        echo CJSON::encode($response);
    }

    /**
     *同意招聘申请
     *@url /ajax/agreeRecruitApply
     *@param string $id       招聘记录的ID
     *@return array()
     #{'code':'0' ,'msg':'agree apply recruit success'} 同意招聘申请成功
     #{'code':'-1','msg':'agree apply recruit fail'}    同意招聘申请失败
     #{'code':'-2','msg':'param error'}                 参数错误
     #{'code':'-3','msg':'not found'}                   没有找到该招聘申请
     #{'code':'-99','msg':'permission denied'}          没有权限
     */
    public function actionAgreeRecruitApply()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>'-1','msg'=>'agree apply recruit fail');

        if(!preg_match('/^\d+$/', $id))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        else if(!$apply = RecruitApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if($apply->next != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        else if( $procedure_list = CJSON::decode($apply->procedure_list) ){
            if($this->user->user_id == end($procedure_list)) {
                if( RecruitApply::finishRecruitApply($apply, $this->user) ) {
                    $response['code'] = '0';
                    $response['msg']  = 'agree apply recruit success';
                }
            }
            else {
                foreach ($procedure_list as $key => $value) {
                    if($this->user->user_id == $value)
                        break;
                }
                if( $next_user = Users::model()->findByPK($procedure_list[$key + 1]) ) {
                    if( RecruitApply::passNext($apply, $this->user, $next_user) ) {
                        $response['code'] = '0';
                        $response['msg']  = 'agree apply recruit success';
                    }
                }
            }
        }
        // else if($this->user->user_id == Users::getCeo()->user_id && RecruitApply::finishRecruitApply($apply, $this->user))
        // {
        //     $response['code'] = '0';
        //     $response['msg']  = 'agree apply recruit success';
        // }
        // else if($this->user->user_id == Users::getAdminId()->user_id && RecruitApply::passCeo($apply, $this->user))
        // {
        //     $response['code'] = '0';
        //     $response['msg']  = 'agree apply recruit success';
        // }
        echo CJSON::encode($response);
    }

    /**
     *不同意招聘申请
     *@url /ajax/rejectRecruitApply
     *@param string $id              拒绝招聘申请
     *@return array()
     #{'code':'0' ,'msg':'reject apply recruit success'} 拒绝招聘申请成功
     #{'code':'-1','msg':'reject apply recruit fail'}    拒绝招聘申请失败
     #{'code':'-2','msg':'param error'}                  参数错误
     #{'code':'-3','msg':'not found'}                    没有找到该招聘申请
     #{'code':'-99','msg':'permission denied'}           没有权限
     */
    public function actionRejectRecruitApply()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $reason = empty($_POST['reason']) ? '' : $_POST['reason'];
        $response = array('code'=>'-1','msg'=>'reject apply recruit fail');

        if(!preg_match('/^\d+$/', $id) || empty($reason))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        else if(!$apply = RecruitApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if($apply->next != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        else if(RecruitApply::rejectRecruitApply($apply,$this->user, $reason))
        {
            $response['code'] = '0';
            $response['msg']  = 'reject apply recruit success';
        }
        echo CJSON::encode($response);
    }

    /**
     *批量提交简历
     *@url /ajax/batchCommitResume
     *@param string $id //招聘申请的ID
     *@param object $resume  简历资源
     *@param object $name    简历名称
     *@param object $source  简历来源
     *@return array()
     #{'code':'0' ,'msg':'commit resume success'} 提交简历成功
     #{'code':'-1','msg':'commit resume fail'}    提交简历失败
     #{'code':'-2','msg':'param error'}           参数错误
     #{'code':'-3','msg':'not found'}             没有找到该招聘申请
     #{'code':'-4','msg':'type error'}            简历类型错误
     #{'code':'-99','msg':'permission denied'}    没有权限
     */    
    public function actionBatchCommitResume()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $name = empty($_POST['name']) ? '' : iconv('utf-8','gbk',$_POST['name']);
        $source = empty($_POST['resource']) ? '' : iconv('utf-8','gbk',$_POST['resource']);
        $dir = Yii::getPathOfAlias('webroot.attachment.resumes').DIRECTORY_SEPARATOR;
        $response = array('code'=>-1,'msg'=>'commit resume failed');
        $temp = sprintf('%08s.',rand(0,99999999));
        $allow_file_type = array('application/msword','application/pdf',
                        'application/zip', 'CDF V2 Document, corrupt: Can\'t expand summary_info');    //docx文件获取的类型为zip

        if(preg_match('/^[1-9]\d*$/', $id) && !empty($name))
        {
            $obj_resume = Resume::model()->find('apply_id=:id and name=:name', array(':id'=>$id , ':name'=>iconv('gbk','utf-8',$name)));
        }
        $obj_resume = empty($obj_resume) ? new Resume() : $obj_resume;

        if(!preg_match('/^[1-9]\d*$/', $id) || empty($name) || empty($source))
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }   
        else if(!$apply = RecruitApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(!$resume = CUploadedFile::getInstanceByName('resume'))
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }
        else if($resume->getSize() == 0 || $resume->getSize() > 5*1024*1024)
        {
            $response['code'] = -5;
            $response['msg']  =  'size error';
        }
        //else if(!in_array($resume->type , $allow_file_type))
        else if( !$resume->saveAs($dir."apply-{$id}-".$temp.$resume->extensionName) || $resume->hasError)
        {
            $response['code'] = -1;
            $response['msg']  = 'commit resume failed';
        }

        //有些doc文件检测出的类型为 CDF V2 Document, corrupt: Can't expand summary_info
        elseif (!in_array(mime_content_type($dir."apply-{$id}-".$temp.$resume->extensionName), $allow_file_type)) {
            $file_name = $dir."apply-{$id}-".$temp.$resume->extensionName;
            $command = "rm {$file_name}";
            @exec($command);
            $response['code'] = -4;
            $response['msg']  = 'file type error';
        }

        else if(Resume::processResume($obj_resume , array('apply_id'=>$id, 'name'=>iconv('gbk','utf-8',$name), 'source'=>iconv('gbk','utf-8',$source), 'resume_file'=>"apply-{$id}-".$temp.$resume->extensionName, 'status'=>'create','interview_time'=>'0000-00-00 00:00:00','create_time'=>date('Y-m-d H:i:s'))) && RecruitApply::noticeApplyUserByResume($apply, $name))
        {
            $response['code'] = 0;
            $response['msg']  =  'commit resume success';
        }
        echo CJSON::encode($response['code']);
    }
    /**
     *设置符合要求的简历
     *@url /ajax/conformResume
     *@param string $id    简历ID
     *@param string $status enum('conform','inconformity','nonarrival','success') 符合要求 不符合要求 没有到 面试通过
     *@return array
     #{'code':'0' ,'msg':'conform resume success'} 确认简历成功
     #{'code':'-1','msg':'conform resume fail'}    确认简历失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-3','msg':'not found'}              没有找到该简历
     #{'code':'-99','msg':'permission denied'}     没有权限
     */
    public function actionConformResume()
    {
        //$_POST = array('id'=>1,'status'=>'conform');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $status = empty($_POST['status']) ? '' : $_POST['status'];
        $response = array('code'=>-1, 'msg'=>'conform resume fail');
        
        if(!preg_match('/^[1-9]\d*$/', $id) || !in_array($status, array('conform','inconformity','nonarrival','success')))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$resume = Resume::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if($resume->apply->user_id != Yii::app()->session['user_id'] && Yii::app()->session['user_id'] != Users::getHr()->user_id)
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }//符合岗位要求 可以通知面试
        else if(($status == 'conform') && (Resume::processResume($resume , array('status'=>$status))) && (Resume::noticeHr($resume)))
        {
            $response['code'] = 0;
            $response['msg']  = 'conform resume success';
        }//不符合岗位要求，存档
        else if($status == 'inconformity' && Resume::processResume($resume , array('status'=>$status)) )
        {
            $response['code'] = 0;
            $response['msg']  = 'conform resume success';
        }//通知面试时间后没有来，存档
        else if($status == 'nonarrival' && Resume::processResume($resume , array('status'=>$status)) )
        {
            $response['code'] = 0;
            $response['msg']  = 'conform resume success';
        }//面试完成，要填写评估表了
        else if($status == 'success' && Resume::processResume($resume , array('status'=>$status)) && (Resume::noticeInterviewerAssessment($resume)))
        {
            $response['code'] = 0;
            $response['msg']  = 'conform resume success';
        }
        echo CJSON::encode($response);
    }

    /**
     *添加面试的时间并且通知申请人
     *@url /ajax/interviewTime
     *@param stirng $id   简历ID
     *@param string $time 面试通知时间 格式：2014-10-20 15:20:00
     *@return array()
     #{'code':'0' ,'msg':'set interview time success'} 设置面试时间成功
     #{'code':'-1','msg':'set interview time fail'}    设置面试时间失败
     #{'code':'-2','msg':'param error'}                参数错误
     #{'code':'-3','msg':'not found'}                  没有找到简历
     #{'code':'-99','msg':'permission denied'}         没有权限
     */
    public function actionInterviewTime()
    {
        #$_POST = array('id'=>55, 'time'=>'2015-01-31 12:00:00');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $time = empty($_POST['time']) ? '' : $_POST['time'];
        $response = array('code'=>-1, 'msg'=>'set interview time fail');
        
        if(!preg_match('/^[1-9]\d*$/', $id) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $time))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$resume = Resume::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(Yii::app()->session['user_id'] != Users::getHr()->user_id)
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Resume::processResume($resume , array('status'=>'arrange', 'interview_time'=>$time)) && Resume::noticeSetInterviewer($resume))
        {
            $response['code'] = 0;
            $response['msg']  = 'set interview time success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *更改面试时间
     *@url /ajax/editInterviewTime  
     *@param stirng $id   简历ID
     *@param string $time 面试时间 格式：2014-10-20 15:20:00
     *@return array()
     #{'code':'0' ,'msg':'edit interview time success'} 修改面试时间成功
     #{'code':'-1','msg':'edit interview time fail'}    修改面试时间失败
     #{'code':'-2','msg':'param error'}                 参数错误
     #{'code':'-3','msg':'not found'}                   没有找到该简历
     #{'code':'-99','msg':'permission denied'}          没有权限
     */
    public function actionEditInterviewTime()
    {
        //$_POST = array('id'=>1, 'time'=>'2014-10-20 15:20:00');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $time = empty($_POST['time']) ? '' : $_POST['time'];
        $response = array('code'=>-1, 'msg'=>'edit interview time fail');
        
        if(!preg_match('/^[1-9]\d*$/', $id) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $time))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$resume = Resume::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(Yii::app()->session['user_id'] != Users::getHr()->user_id)
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Resume::noticeApplyUserByEdit($resume->id, $time) && Resume::processResume($resume , array('status'=>'arrange', 'interview_time'=>$time)))
        {
            $response['code'] = 0;
            $response['msg']  = 'edit interview time success';
        }
        echo CJSON::encode($response);
    }

    /**
     *添加评估表
     *@url /ajax/addAssessment
     *@param string $resume_id 简历ID
     *@param string $opinion   意见
     *@return array()
     #{'code':'0' ,'msg':'add assessment success'} 添加面试评估成功
     #{'code':'-1','msg':'add assessment fail'}    添加面试评估失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-3','msg':'not found'}              没有找到该简历
     #{'code':'-4','msg':'assessment record duplicate'} 评估记录已经重复提交了
     #{'code':'-99','msg':'permission denied'}     没有权限
     */
    public function actionAddAssessment()
    {
        //$_POST = array('resume_id'=>1, 'grooming'=>'5','skill'=>'10','ability'=>10,'attitude'=>'10','opinion'=>'我要测试中文','action'=>'agree');
        //$_POST = array('resume_id'=>55,'opinion'=>'testtest');
        $data['resume_id'] = empty($_POST['resume_id']) ? '' : $_POST['resume_id'];
        $opinion   = empty($_POST['opinion']) ? '' : $_POST['opinion'];
        $action    = 'agree'; //empty($_POST['action']) ? '' : $_POST['action'];

        $response  = array('code'=>-1, 'msg'=>'add assessment fail');
        $pattern = '/^[1-9]\d*$/';
        if(!preg_match($pattern, $data['resume_id'])|| empty($opinion) || !in_array($action,array('agree','reject')))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$resume = Resume::model()->findByPk($data['resume_id']))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(Yii::app()->session['user_id'] != Users::getHr()->user_id)
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Assessment::model()->find('resume_id=:id',array(':id'=>$data['resume_id'])))
        {
            $response['code'] = -4;
            $response['msg']  = 'assessment record duplicate';
        }
        else if(!$id = Assessment::processAssessment(new Assessment(), array_merge($data,array('entry_day'=>'0000-00-00 00:00:00','next'=>$resume->interviewer,'periods'=>'0','probation_salary'=>'0','official_salary'=>'0','experience'=>0, 'skill'=>0,   'execution'=>0, 'attitude'=>0, 'communicate'=>0, 'learning'=>0,'status'=>$action=='agree'?'wait':'reject','reason'=>'','update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s')))))
        {
            $response['code'] = -1;
            $response['msg'] = 'add assessment fail';
        }
        else if(!AssessmentLog::addLog(array('assessment_id'=>$id, 'user_id'=>Yii::app()->session['user_id'], 'periods'=>'0','probation_salary'=>'0','official_salary'=>'0','opinion'=>$opinion,'action'=>$action,'create_time'=>date('Y-m-d H:i:s'))))
        {
            $response['code'] = -1;
            $response['msg'] = 'add assessment fail';
        }
        else if($action == 'agree' && Assessment::noticeApproval($resume) && Resume::processResume($resume, array('status'=>'assessment')))
        {
            $response['code'] = 0;
            $response['msg'] = 'add assessment success';
        }
        else if($action == 'reject' && Resume::processResume($resume, array('status'=>'assessment')))
        {
            $response['code'] = 0;
            $response['msg'] = 'add assessment success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *拒绝评估表
     *@url /ajax/rejectAssessment
     *@param string $id 这个是评估表的ID
     *@param string $opinion 意见
     *@return array
     #{'code':'0' ,'msg':'reject assessment success'}  拒绝面试评估记录成功
     #{'code':'-1','msg':'reject assessment fail'}     拒绝面试评估记录失败
     #{'code':'-2','msg':'param error'}                参数错误
     #{'code':'-3','msg':'not found'}                  没有找到该面试评估
     #{'code':'-99','msg':'permission denied'}         没有权限
     */
    public function actionRejectAssessment()
    {
        //$_POST = array('id'=>1,'opinion'=>'xxxxx');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $opinion = empty($_POST['opinion']) ? '' : $_POST['opinion'];
        $response = array('code'=>-1, 'msg'=>'reject assessment fail');

        if(!preg_match('/^[1-9]\d*$/', $id) || empty($opinion))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$assessment = Assessment::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if($assessment->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Assessment::rejectAssessment($assessment, $this->user, $opinion))
        {
            $response['code'] = 0;
            $response['msg']  = 'reject assessment success';
        }
        echo CJSON::encode($response);
    }

    /**
     *同意评估表
     *@url /ajax/agreeAssessment
     *@param string $id 这个是评估表的ID
     *@param string $periods 试用期限
     *@param stirng $probation_salary 试用薪资 
     *@param stirng $official_salary 转正薪资 
     *@param stirng $opinion        意见
     *@param stirng $entry_day         入职时间
     *@return array()
     #{'code':'0' ,'msg':'agree assessment success'}   同意面试评估记录成功
     #{'code':'-1','msg':'agree assessment fail'}      同意面试评估记录失败
     #{'code':'-2','msg':'param error'}                参数错误
     #{'code':'-3','msg':'not found'}                  没有找到该面试评估记录
     #{'code':'-99','msg':'permission denied'}         没有权限
     */
    public function actionAgreeAssessment()
    {
        //$_POST = array('id'=>1,'official_salary'=>'5000','opinion'=>'建议录用','periods'=>1,'probation_salary'=>4000,'entry_day'=>'2014-10-25');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $data['periods'] = empty($_POST['periods']) ? '0' : $_POST['periods'];
        $data['probation_salary'] = empty($_POST['probation_salary']) ? '0' : $_POST['probation_salary'];
        $data['official_salary'] = empty($_POST['official_salary']) ? '0' : $_POST['official_salary'];
        $data['opinion'] = empty($_POST['opinion']) ? '' : $_POST['opinion'];
        $entry_day      = empty($_POST['entry_day'])? '':$_POST['entry_day'];

        $response = array('code'=>-1,'msg'=>'agree assessment fail');
        $admin = Users::getAdminId();
        $hr = Users::getHr();
        $ceo   = Users::getCeo();
        $pattern = '/^\d+$/';
        if(!preg_match($pattern,$id) || !preg_match($pattern,$data['periods']) || !preg_match($pattern,$data['probation_salary']) || !preg_match($pattern,$data['official_salary']) || empty($data['opinion']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$assessment = Assessment::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if($assessment->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }//部门负责人审批
        else if(Yii::app()->session['user_id'] != $admin->user_id && $assessment->resume->apply->user_id == Yii::app()->session['user_id'] && Assessment::passNext($assessment,$hr,$entry_day,$data))
        {
            $response['code'] = 0;
            $response['msg']  = 'agree assessment success';
        }//人事部总监审批
        else if(Yii::app()->session['user_id'] == $hr->user_id && Assessment::passNext($assessment,$ceo,$entry_day,$data))
        {
            $response['code'] = 0;
            $response['msg']  = 'agree assessment success';
        }//总监理审批
        else if(Yii::app()->session['user_id'] == $ceo->user_id && Assessment::finishAssessment($assessment,$data))
        {
            $response['code'] = 0;
            $response['msg']  = 'agree assessment success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *放弃入职的接口
     *商量好了却没有入职的接口 (hr才有权限操作)
     *@url /ajax/giveUp
     *@param string $id 评估表的ID
     *@return array()
     #{'code':'0' ,'msg':'give up success'} 放弃入职成功
     #{'code':'-1','msg':'give up fail'}    放弃入职失败
     #{'code':'-2','msg':'param error'}     参数错误
     #{'code':'-3','msg':'not found'}       没有找到该面试评估
     #{'code':'-99','msg':'permission denied'} 没有权限
     */
    public function actionGiveUp()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $pattern = '/^\d+$/';
        $response = array('code'=>'-1','msg'=>'give up fail');
        if(!preg_match($pattern,$id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$assessment = Assessment::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if(Users::getHr()->user_id != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        /*else if($assessment->resume->apply->status != 'entry')
        {
            $response['code'] = 0;
            $response['msg']  = 'give up success';
        }*/
        else if(Assessment::giveUp($assessment))
        {
            $response['code'] = 0;
            $response['msg']  = 'give up success';
        }
        echo CJSON::encode($response);
    }

    /**
     *处理上传记录文件
     *@url /ajax/uploadRecordFile
     *@param string $id  评估表的ID
     *@param object $record_file  面试评估记录的文件
     *@return array()
     #{code:0 ,msg:upload record file success}  上传记录文件成功
     #{code:-1,msg:upload record file fail}     上传记录文件失败
     #{code:-2,msg:param error}                 参数错误
     #{code:-3,msg:not found}                   没有找到该面试评估记录ID
     #{code:-5,msg:size error}                  附件大小错误
     #{code:-99,msg:permission denied}          没有权限
     */
    public function actionUploadRecordFile()
    {
        $id = empty($_POST['id'])? '' : $_POST['id'];
        $record_file = empty($_POST['record_file'])? '' : $_POST['record_file'];
        $response = array('code'=>'-1', 'msg'=>'upload record file fail');
        $dir = Yii::getPathOfAlias('webroot.attachment.records').DIRECTORY_SEPARATOR;

        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }   
        else if(!$assessment = Assessment::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(!$record = CUploadedFile::getInstanceByName('record_file'))
        {
            $response['code'] = -2;
            $response['msg']  =  'param error';
        }
        else if($record->getSize() == 0 || $record->getSize() > 5*1024*1024)
        {
            $response['code'] = -5;
            $response['msg']  =  'size error';
        }
        else if($record->saveAs($dir."assessment{$id}.".$record->extensionName) && !$record->hasError && Assessment::processAssessment($assessment, array('record_file'=>"assessment{$id}.".$record->extensionName)))
        {
            $response['code'] = 0;
            $response['msg']  =  'upload record file success';
        }
        echo CJSON::encode($response['code']);
    }
    
    /**
     *申请转正的接口
     *@url /ajax/applyQualify
     *@param string $trial_salary 转正前薪资
     *@param stirng $work_life    工作年限
     *@param string $evaluation   员工自评
     *@param string $plan         个人规划
     *@param string $suggest      意见和建议
     *@param array  $contents     array(array('serial','content','proportion','reference','quantity','completion_rate','delay_rate','rework_rate') )                  序号     工作内容 占比  参考内容 工作量 完成率 延误率 返工率
     *@return array()
     #{code:0 ,msg:commit qualify apply success} 添加转正申请成功
     #{code:-1,msg:upload qualify apply fail}    添加转正申请失败
     #{code:-2,msg:param error}                  参数错误
     #{code:-3,msg:you have positive} 你已经转正了 不需要重复提交
     #{code:-99,msg:permission denied}           没有权限
     */

    public function actionApplyQualify()
    {
        //$_POST= array('trial_salary'=>'5000','work_life'=>4,'evaluation'=>'灭有什么','plan'=>'OK','suggest'=>'没有');
        //$_POST['contents'] = array(array('serial'=>1,'content'=>'OK','proportion'=>'100','reference'=>'','quantity'=>5,'completion_rate'=>50,'delay_rate'=>20,'rework_rate'=>30));
        $data['trial_salary'] = empty($_POST['trial_salary']) ? '0' : $_POST['trial_salary'];
        $data['work_life'] = empty($_POST['work_life']) ? '0' : $_POST['work_life'];
        $data['evaluation'] = empty($_POST['evaluation']) ? '' : $_POST['evaluation'];
        $data['plan'] = empty($_POST['plan']) ? '' : $_POST['plan'];
        $data['suggest'] = empty($_POST['suggest']) ? '' : $_POST['suggest'];
        $contents = empty($_POST['contents']) ? '' : $_POST['contents'];
        $next = $this->user->leadId;

        $response = array('code'=>-1, 'id'=>'0','msg'=>'upload qualify apply fail');
        if(!preg_match('/^[1-9]\d*$/',$data['trial_salary']) || !preg_match('/^\d+$/', $data['work_life']) ||
            empty($data['evaluation']) || empty($data['plan']) || empty($data['suggest']) || 
            !QualifyReport::validateData($contents))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }//job_status 状态  intern' 实习生 , 'probation_employee' 试用员工 ,'formal_employee' 正式员工
        else if($this->user->job_status == 'formal_employee')
        {
            $response['code'] = '-3';
            $response['msg']  = 'you have positive';
        }
        /*else if($this->user->job_status == 'intern')
        {
            $response['code'] = '-4';
            $response['msg']  = 'you are an intern';
        }*/
        else if(!$id = QualifyApply::processQualifyApply(new QualifyApply(), array_merge(array('user_id'=>Yii::app()->session['user_id'],'next'=>$next, 'status'=>'wait','type'=>'contract', 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = -1;
            $response['msg']  = 'upload qualify apply fail';
        }
        else if(QualifyReport::addReport($id , $contents) && QualifyApply::noticeUserById($id,$this->user, "已提交",'self') &&  QualifyApply::noticeUserById($id, $next, "已提交,请尽快审批",'other'))
        {
            $response['code'] = 0;
            $response['id']   = $id;
            $response['msg']  = 'upload qualify apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *同意转正申请
     *@url /ajax/agreeApplyQualify
     *@param string $id           转正申请记录ID
     *@param string $qualify_date 转正日期
     *@param string $qualify_salary 转正薪资
     *@param stirng $comment      评语
     *@param string $type  enum('contract','modify') 薪资调整类型 约定 调整. 部门负责人必须填写
     *@return array()
     #{'code':0 , 'msg':'agree qualify apply success'}  同意转正成功
     #{'code':-1 , 'msg':'agree qualify apply fail'}    同意转正失败
     #{'code':-2, 'msg':'param error'}                  参数错误
     #{'code':-3, 'msg':'not found'}                    没有找到该转正申请
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionAgreeApplyQualify()
    {
        //$_POST = array('id'=>13, 'type'=>'contract','qualify_date'=>'2014-11-11','qualify_salary'=>'5000','comment'=>'dssfdsdf');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $type = empty($_POST['type']) ? '' : $_POST['type'];
        $data['qualify_date'] = empty($_POST['qualify_date']) ? '' : $_POST['qualify_date'];
        $data['qualify_salary'] = empty($_POST['qualify_salary']) ? '' : $_POST['qualify_salary'];
        $data['comment'] = empty($_POST['comment']) ? '' : $_POST['comment'];
        $response = array('code'=>-1, 'msg'=>'agree qualify apply fail');

        if(!preg_match('/^[1-9]\d*$/', $id) || empty($data['comment']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['qualify_date']) || !preg_match('/^[1-9]\d*$/', $data['qualify_salary']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$apply = QualifyApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if($apply->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        else if(!QualifyApplyLog::addLog(array_merge(array('apply_id'=>$id,'user_id'=>Yii::app()->session['user_id'],'action'=>'agree','create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = -4;
            $response['msg'] = 'comment to long';
        }//部门负责人
        else if(!$apply_type = QualifyApply::typeQualifyApply($apply))
        {
            $response['code'] = -1;
            $response['msg'] = 'agree qualify apply fail';
        }
        else if(QualifyApply::agreeQualifyApply($apply_type , $apply , $this->user, $type, $data['qualify_date'], $data['qualify_salary']))
        {
            $response['code'] = 0;
            $response['msg'] = 'agree qualify apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *拒绝转正的接口
     *@url /ajax/rejectApplyQualify
     *@param stirng $id    转正申请的ID
     *@param string $comment 评语 必须要
     *@return array
     #{'code':0 , 'msg':'reject qualify apply success'}  拒绝转正申请成功
     #{'code':-1 , 'msg':'reject qualify apply fail'}    拒绝转正申请失败
     #{'code':-2, 'msg':'param error'}                   参数错误
     #{'code':-3, 'msg':'not found'}                     没有该转正申请记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionRejectApplyQualify()
    {
        $id = empty($_POST['id']) ? '': $_POST['id'];
        $comment = empty($_POST['comment']) ? '': $_POST['comment'];
        $response = array('code'=>-1, 'msg'=>'reject qualify apply fail');

        if(!preg_match('/^[1-9]\d*$/', $id) || empty($comment))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$apply = QualifyApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if($apply->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        else if(!QualifyApplyLog::addLog(array('apply_id'=>$id,'user_id'=>Yii::app()->session['user_id'],'action'=>'reject','create_time'=>date('Y-m-d H:i:s'),'qualify_salary'=>0,'qualify_date'=>'0000-00-00','comment'=>$comment)))
        {
            $response['code'] = -1;
            $response['msg'] = 'reject qualify apply fail';
        }
        else if(QualifyApply::processQualifyApply($apply,array('next'=>0,'status'=>'reject')) && QualifyApply::noticeAllTransaction($apply, '未通过'))
        {
            $response['code'] = 0;
            $response['msg'] = 'reject qualify apply success';
        }
        echo CJSON::encode($response);
    }
    /**
     *延迟转正的接口
     *@url /ajax/delayApplyQualify
     *@param stirng $id    转正申请的ID
     *@param string $comment 评语 必须要
     *@param stirng $qualify_date 转正日期
     *@return array
     #{'code':0 , 'msg':'delay qualify apply success'}  延迟转正申请成功
     #{'code':-1 , 'msg':'delay qualify apply fail'}    延迟转正申请失败
     #{'code':-2, 'msg':'param error'}                  参数错误
     #{'code':-3, 'msg':'not found'}                    没有找到该转正申请
     #{'code'=>'-99','msg'=>'permission denied'}        没有权限
     */
    public function actionDelayApplyQualify()
    {
        //$_POST = array('id'=>9, 'comment'=>'延迟', 'qualify_date'=>'2014-12-11');
        $id = empty($_POST['id']) ? '': $_POST['id'];
        $comment = empty($_POST['comment']) ? '': $_POST['comment'];
        $qualify_date = empty($_POST['qualify_date']) ? '': $_POST['qualify_date'];
        $response = array('code'=>-1, 'msg'=>'delay qualify apply fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || empty($comment) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $qualify_date))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$apply = QualifyApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not found';
        }
        else if($apply->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        else if(!QualifyApplyLog::addLog(array('apply_id'=>$id,'user_id'=>Yii::app()->session['user_id'],'action'=>'delay','create_time'=>date('Y-m-d H:i:s'),'qualify_salary'=>0,'qualify_date'=>$qualify_date,'comment'=>$comment)))
        {
            $response['code'] = -1;
            $response['msg'] = 'delay qualify apply fail';
        }
        else if(QualifyApply::processQualifyApply($apply,array('next'=>0,'status'=>'delay')) && QualifyApply::noticeAllTransaction($apply, '被延期到'.$qualify_date)  && Users::updateUser($apply->user, array('default_regularized_date'=>$qualify_date)))
        {
            $response['code'] = 0;
            $response['msg'] = 'delay qualify apply success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *提交离职申请
     *@url /ajax/submitQuitApply 
     *@param string $user_id   用户ID
     *@return array
     #{'code':'0','msg':'submit quit apply success'} 提交离职申请成功
     #{'code':'-1','msg':'submit quit apply fail'}   提交离职申请失败
     #{'code':'-2','msg':'param error'}              参数错误
     #{'code':'-3','msg':'not found'}                没有找到该用户
     #{'code':'-4','msg':'this person has quit'}     该用户已经离职了
     #{'code':'-5','msg':'please transfer position'} //请交接好你的岗位
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionSubmitQuitApply()
    {
        $user_id = empty($_POST['user_id']) ? '' : $_POST['user_id'];
        $response = array('code'=>'-1','id'=>'', 'msg'=>'submit quit apply fail');
        if(!preg_match('/^[1-9]\d*$/', $user_id))
        {
            $response['code'] = -2;
            $response['msg'] =  'param error';
        }
        else if($this->user->department->name != Department::adminDepartment()->name)
        {
            $response['code'] = -99;
            $response['msg'] =  'permission denied';
        }
        else if(!$user = Users::model()->findByPk($user_id))
        {
            $response['code'] = -3;
            $response['msg'] =  'not found';
        }
        else if($user->status != 'work')
        {
            $response['code'] = -4;
            $response['msg'] =  'this person has quit';
        }
        else if(in_array($user->user_id, Users::getOaAdminList()))
        {
            $response['code'] = -5;
            $response['msg'] =  'please transfer position';
        }
        else if( !$procedure_list = Procedure::getProcedure('quit_apply',0 ,$user_id) ) {
            $response['code'] = -6;
            $response['msg'] =  'procedure_list empty';
        }
        else if(!$id = QuitApply::processQuitApply(new QuitApply(), array('submit_id'=>Yii::app()->session['user_id'],'user_id'=>$user_id, 'quit_reason'=>'','handover_status'=>'create','next'=>$user->user_id,'status'=>'wait','reason'=>'','create_time'=>date('Y-m-d H:i:s'), 'procedure_list'=>CJSON::encode($procedure_list) ) ))
        {
            $response['code'] = '-1';
            $response['msg']  = 'submit quit apply fail';
        }
        else if(QuitApply::noticeSelf($id,$user) && QuitApplyLog::addLog(array('apply_id'=>$id,'action'=>'create','user_id'=>Yii::app()->session['user_id'],'create_time'=>date('Y-m-d H:i:s') )))
        {
            $response['code'] = 0;
            $response['id']   = $id;
            $response['msg'] =  'submit quit apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *填写离职原因
     *@url /ajax/writeQuitReason
     *@param string $id           离职申请ID
     *@param stirng $quit_reason  离职原因
     *@return array()
     #{'code':'0','msg':'write quit reason success'} 填写离职原因成功
     #{'code':'-1','msg':'write quit reason fail'}   填写离职原因失败
     #{'code':'-2','msg':'param error'}              参数错误
     #{'code':'-3','msg':'not found'}                没有找到该离职记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionWriteQuitReason()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $quit_reason = empty($_POST['quit_reason']) ? '' : $_POST['quit_reason'];
        $response = array('code'=>-1, 'msg'=>'write quit reason fail');

        $apply_null = true;                      //通知第一个审批者
        $next = "";
        if( $apply = QuitApply::model()->findByPk($id) )
        {
            $procedure_list = CJSON::decode($apply->procedure_list, true);
            $next = $procedure_list[0];
            $apply_null = false;
        }

        if(!preg_match('/^[1-9]\d*$/', $id) || empty($quit_reason))
        {
            $response['code'] = -2;
            $response['msg']   = 'param error';
        }
        else if( $apply_null )
        {
            $response['code'] = -3;
            $response['msg']   = 'not found';
        }
        else if($apply->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }
        else if(QuitApply::processQuitApply($apply, array('quit_reason'=>$quit_reason,'next'=>$next,'status'=>'wait')) && QuitApply::noticeLeader($apply,Users::model()->findByPk($next),"已经提交,请尽快审批"))
        {
            $response['code'] = 0;
            $response['msg']  = 'write quit reason success';
        }
        echo CJSON::encode($response);
    }

    /**
     *同意离职申请
     *@url /ajax/agreeQuitApply
     *@param string $id         离职申请ID
     *@param stirng $quit_date  离职日期
     *@return array()
     #{'code':'0','msg':'agree quit apply success'} 同意离职成功
     #{'code':'-1','msg':'agree quit apply fail'}   同意离职失败
     #{'code':'-2','msg':'param error'}             参数错误
     #{'code':'-3','msg':'not found'}               没有找到该离职记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionAgreeQuitApply()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $quit_date = empty($_POST['quit_date']) ? '' : $_POST['quit_date'];
        $response = array('code'=>-1, 'msg'=>'agree quit apply fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $quit_date))
        {
            $response['code'] = -2;
            $response['msg']   = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']   = 'not found';
        }
        else if($apply->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }
        else if(QuitApply::agreeQuitApply($apply , $this->user, $quit_date))
        {
            $response['code'] = 0;
            $response['msg']   = 'agree quit apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *拒绝离职申请
     *@url /ajax/rejectQuitApply
     *@param string $id     离职申请ID
     *@param stirng $reason 拒绝原因
     *@return array()
     #{'code':'0','msg':'reject quit apply success'} 拒绝离职成功
     #{'code':'-1','msg':'reject quit apply fail'}   拒绝离职失败
     #{'code':'-2','msg':'param error'}              参数错误
     #{'code':'-3','msg':'not found'}                没有找到该离职记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionRejectQuitApply()
    {
        //$_POST = array('id'=>6,'reason'=>'你好');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $reason = empty($_POST['reason']) ? '' : $_POST['reason'];
        $response = array('code'=>-1, 'msg'=>'reject quit apply fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || empty($reason))
        {
            $response['code'] = -2;
            $response['msg']   = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']   = 'not found';
        }
        else if($apply->next != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }
        else if(QuitApply::rejectApply($apply,$this->user, $reason))
        {
            $response['code'] = 0;
            $response['msg']   = 'reject quit apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *提交工作交接
     *@url /ajax/submitHandover
     *@param string $id 离职申请的ID
     *@param string $handover_user_id 工作交接人的User_id
     *@return array
     #{'code':'0', 'msg':'submit handover success'}  提交工作交接成功
     #{'code':'-1','msg':'submit handover fail'}     提交工作交接失败
     #{'code':'-2','msg':'param error'}              参数错误
     #{'code':'-3','msg':'quit apply not found'}     没有找到该离职记录
     #{'code':'-4','msg':'user not found'}           没有找到该交接人
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionSubmitHandover()
    {
        //$_POST = array('id'=>'12','handover_user_id'=>'32');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $handover_user_id = empty($_POST['handover_user_id']) ? '' : $_POST['handover_user_id'];
        $response = array('code'=>-1, 'msg'=>"submit handover fail");
        if(!preg_match('/^[1-9]\d*$/', $id) || !preg_match('/^[1-9]\d*$/', $handover_user_id))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'quit apply not found';
        }
        else if($apply->user_id != Yii::app()->session['user_id'] || $apply->status != 'success')
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(!$user = Users::model()->findByPk($handover_user_id, 'status=:status',array(':status'=>'work')))
        {
            $response['code'] = -4;
            $response['msg']  = 'user not found';
        }
        else if(QuitApply::processQuitApply($apply , array('handover_type'=>'work','handover_status'=>'wait','handover_user_id'=>$handover_user_id))
        && QuitApply::noticeHandover($apply,$user,"已经提交,请尽快和他交接",'handover'))
        {
            $response['code'] = 0;
            $response['msg']  = 'submit handover success';
        }
        echo CJSON::encode($response);
    }

    /**
     *交接物品
     *@url /ajax/handoverWork
     *@param string $id     离职申请单的ID
     *@param array $contents  离职交接的数据
     *@return array
     #{'code':'0', 'msg':'handover work success'}  交接成功
     #{'code':'-1','msg':'handover work fail'}     交接失败
     #{'code':'-2','msg':'param error'}            参数错误
     #{'code':'-3','msg':'not found'}              没有找到该离职记录
     #{'code':'-4','msg':'duplicate commit'}       重复提交离职记录了
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function  actionHandoverWork()
    {
//        $_POST = array('id'=>12, 'contents'=>array('工作内容','PHP代码'));
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $contents = empty($_POST['contents']) ? '' : $_POST['contents'];
        $response = array('code'=>-1, 'msg'=>'handover work fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || empty($contents))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if(!$apply->getPermission(Yii::app()->session['user_id']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(QuitHandover::model()->find("apply_id=:id and type=:type",array(':id'=>$id, ':type'=>$apply->handover_type)))
        {
            $response['code'] = -4;
            $response['msg']  = 'duplicate commit';
        }
        else if(QuitHandover::processTransaction($apply,$this->user,$contents))
        {
            $response['code'] = 0;
            $response['msg']  = 'handover work success';
        }
        echo CJSON::encode($response);
    }

    /**
     *监督人确认工作交接
     *@url /ajax/confirmWorkHandover
     *@param string $id   离职申请单的ID
     *@return array
     #{'code':'0', 'msg':'confirm handover work success'}  确认交接成功
     #{'code':'-1','msg':'confirm handover work fail'}     确认交接失败
     #{'code':'-2','msg':'param error'}                    参数错误
     #{'code':'-3','msg':'not found'}                      没有找到该离职记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionConfirmWorkHandover()
    {
        //$_POST['id'] = 12;
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'confirm handover work fail');
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if(!$handover = QuitHandover::model()->find('apply_id=:id and type=:type',array(':id'=>$apply->id,'type'=>$apply->handover_type)))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if($handover->status != 'wait' || $handover->supervision_id != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if($handover->confirmHandler($apply))
        {
            $response['code'] = 0;
            $response['msg']  = 'confirm handover work success';
        }
        echo CJSON::encode($response);
    }

    /**
     * @ignore
     *行政部门 创建每周活动
     *@url /ajax/createActivity
     *@param string $title
     *@param string $content
     *@param string $end_time yyyy-mm-dd hh:ii:ss
     *@return array
     *{'code':'0', 'msg':'create activity success'}
     *{'code':'-1','msg':'create activity fail'}
     *{'code':'-2','msg':'param error'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionCreateActivity()
    {
        //$_POST = array('title'=>'你好活动','content'=>'杨新安xxxxdsdsdsdsd','end_time'=>'2014-11-11 10:10:10');
        $data['title'] = empty($_POST['title']) ? '' : $_POST['title'];
        $data['content'] = empty($_POST['content']) ? '' : $_POST['content'];
        $data['end_time'] = empty($_POST['end_time']) ? '' : $_POST['end_time'];

        $response = array('code'=>-1,'id'=>'', 'msg'=>'create activity fail');
        if(!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$data['end_time']) || empty($data['title']) || 
            empty($data['content']) || $data['end_time'] <= date('Y-m-d H:i:s') )
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user) || $this->user->department_id != Department::adminDepartment()->department_id)
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if($id = Activity::processActivity(new Activity(), array_merge(array('status'=>'wait','create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = 0;
            $response['msg'] = 'create activity success';
            $response['id'] = $id;
        }
        echo CJSON::encode($response);
    }

    /**
     *@ignore
     *参加活动
     *@url /ajax/activityJoin
     *@param string $activity_id
     *@return array()
     *{'code':0,'msg':'join activity success'}
     *{'code':-1,'msg':'join activity fail'}
     *{'code':-2,'msg':'param error'}
     *{'code':-3,'msg':'activity not found'}
     *{'code':-4,'msg':'time has passed'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionActivityJoin()
    {
        //$_POST = array('activity_id'=>1);
        $data['activity_id'] = empty($_POST['activity_id']) ? '' : $_POST['activity_id'];
        $response = array('code'=>-1, 'msg'=>'join activity fail');

        if(!preg_match('/^[1-9]\d*$/', $data['activity_id']))
        {
            $response['code'] =  -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(!$activity = Activity::model()->findByPk($data['activity_id']))
        {
            $response['code'] =  -3;
            $response['msg'] = 'activity not found';
        }
        else if($activity->end_time < date('Y-m-d H:i:s'))
        {
            $response['code'] =  -4;
            $response['msg'] = 'time has passed';
        }
        else if(ActivityJoin::processActivityJoin(new ActivityJoin(), array_merge(array('user_id'=>$this->user->user_id,'create_time'=>date('Y-m-d H:i:s'),'tag'=>'wait'),$data)))
        {
            $response['code'] = 0;
            $response['msg'] = 'join activity success';
        }
        echo CJSON::encode($response);
    }
    /**
     *@ignore
     *参加活动
     *@url /ajax/activityExit
     *@param string $activity_id
     *@return array()
     *{'code':0,'msg':'exit activity success'}
     *{'code':-1,'msg':'exit activity fail'}
     *{'code':-2,'msg':'param error'}
     *{'code':-3,'msg':'activity not found'}
     *{'code':-4,'msg':'time has passed'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionActivityExit()
    {
        //$_POST = array('activity_id'=>1);
        $data['activity_id'] = empty($_POST['activity_id']) ? '' : $_POST['activity_id'];
        $response = array('code'=>-1, 'msg'=>'exit activity fail');

        if(!preg_match('/^[1-9]\d*$/', $data['activity_id']))
        {
            $response['code'] =  -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(!$activity = Activity::model()->findByPk($data['activity_id']))
        {
            $response['code'] =  -3;
            $response['msg'] = 'activity not found';
        }
        else if($activity->end_time < date('Y-m-d H:i:s'))
        {
            $response['code'] =  -4;
            $response['msg'] = 'time has passed';
        }
        else if(!$join = ActivityJoin::model()->find('user_id = :user_id and activity_id = :activity_id',array(':user_id'=>$this->user->user_id,':activity_id'=>$activity->id)))
        {
            $response['code'] = 0;
            $response['msg'] = 'exit activity success';
        }
        else if($join->delete())
        {
            $response['code'] = 0;
            $response['msg'] = 'exit activity success';
        }
        echo CJSON::encode($response);
    }

    /**
     * @ignore
     *处理参加活动
     *@url /ajax/processJoin
     *@param string $id  //活动的ID
     *@param string $id_arr array(1,2,3,5) Id的一维数组
     *@param string $tag 'join','absent'
     *@return array()
     *{'code':0,'msg':'process join activity success'}
     *{'code':-1,'msg':'process join activity fail'}
     *{'code':-2,'msg':'param error'}
     *{'code':-3,'msg':'not found'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionProcessJoin()
    {
        //$_POST = array('tag'=>'absent','id_arr'=>array('1','2'));
        $data['id_arr'] = empty($_POST['id_arr']) ? array() : $_POST['id_arr'];
        $data['tag']    = empty($_POST['tag']) ? '' : $_POST['tag'];
        $data['id']    = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'process join activity fail');
        if(!ActivityJoin::validateIds($data['id_arr']) || !preg_match('/^[1-9]\d*$/',$data['id']) ||  !in_array($data['tag'], array('join','absent')))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(empty($this->user) || $this->user->department_id != Department::adminDepartment()->department_id)
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(!Activity::model()->findByPk($data['id']))
        {
            $response['code'] = -3;
            $response['msg'] = "not found";
        }
        else if(ActivityJoin::tagActivityJoin($data['id_arr'], $data['tag'], $data['id']))
        {
            $response['code'] = 0;
            $response['msg'] = "process join activity success";
        }
        echo CJSON::encode($response);
    }

    /**
     * 创建一个节假日加班的接口
     * @url /ajax/createHolidayOvertime
     * @param string $start      开始时间 格式 YYYY-MM-DD HH:II
     * @param string $end        结束时间 特式 YYYY-MM-DD HH:II
     * @param string $content    加班内容
     * *@return array
     #{'code':0,'msg':'create holiday overtime success'} 创建节假日加班成功
     #{'code':-1,'msg':'create holiday overtime fail'}   创建节假日加班失败
     #{'code':-2,'msg':'param error'}                    参数错误
     #{'code':-3,'msg':'overtime duplicate'}             重复提交加班了
     #{'code':-4,'msg':'notice send fail'}               审批通知发送失败
     #{'code':-99,'msg':'permission denied'}             没有权限
     */
    public function actionCreateHolidayOvertime()
    {
        $data['start_time'] = empty($_POST['start']) ? '' : $_POST['start'];
        $data['end_time'] = empty($_POST['end']) ? '' : $_POST['end'];
        $data['content'] = empty($_POST['content']) ? '' : htmlspecialchars($_POST['content']);
        $response = array('code'=>'-1','msg'=>'create holiday overtime fail');
        if(!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $data['start_time']) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $data['end_time']) || empty($data['content']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(Overtime::model()->find('start_time = :start and end_time = :end and user_id =:user_id and type =:type',array(':start'=>$data['start_time'],':end'=>$data['end_time'],  ':user_id'=>$this->user->user_id, ':type'=>'holiday')))
        {
            $response['code'] = -3;
            $response['msg'] = "overtime duplicate";
        }
        else if($id = Overtime::processOvertime(new Overtime() , array_merge($data,array('type'=>'holiday','user_id'=>$this->user->user_id,'create_time'=>date('Y-m-d H:i:s'), 'total_day'=>Holiday::countRestDays($data['start_time'],$data['end_time']) , 'status'=>'wait','head_id'=>Overtime::getLeader($this->user)))))
        {
            $procedure_list = Procedure::getProcedure('overtime', 1, $this->user->user_id);
            //去除审批流程中的自己审批的节点
            $procedure_list = Procedure::removeRepeat($procedure_list, $this->user->user_id);
            $overtime_info = Overtime::model()->findByPK($id);
            $overtime_info['procedure_list'] = CJSON::encode($procedure_list);
            $overtime_info['next'] = $procedure_list[0];
            $overtime_info->save();

            if(empty($procedure_list)) {
                $action_data = array('action'=>'agree');
                Overtime::approveOvertime($overtime, $action_data, false);
            }
            elseif(Overtime::noticeHeadApprove($id, $procedure_list[0]))
            {
                $response['code'] = 0;
                $response['msg'] = "create holiday overtime success";
            }
            else
            {
                $response['code'] = -4;
                $response['msg'] = 'notice send fail';
            }
        }
        echo CJSON::encode($response);
    }
    /**
     *创建加班次数信息
     *@url /ajax/createOvertime
     *@param string $overtime_date  加班日期 格式 2015-02-05
     *@param string $content        加班内容
     *@param string $overtime_time  加班时间 格式 '11:11:11'
     *@return array
     #{'code':0,'msg':'create overtime success'} 创建加班次数成功
     #{'code':-1,'msg':'create overtime fail'}   创建加班次数失败
     #{'code':-2,'msg':'param error'}            参数错误
     #{'code':-3,'msg':'overtime duplicate'}     重复提交
     #{'code':-99,'msg':'permission denied'}     没有权限
     */
    public function actionCreateOvertime()
    {
        //$_POST = array('overtime_date'=>'2014-11-14','content'=>'dd','overtime_time'=>'11:11:11');
        $overtime_date = empty($_POST['overtime_date']) ? '' : $_POST['overtime_date'];
        $overtime_time = empty($_POST['overtime_time']) ? '' : $_POST['overtime_time'];
        $data['content'] = empty($_POST['content']) ? '' : htmlspecialchars($_POST['content']);
        $response = array('code'=>'-1','msg'=>'create overtime fail');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $overtime_date) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $overtime_time) || empty($data['content']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(Overtime::model()->find('end_time >= :start and end_time <= :end and user_id=:user_id and type = :type',array(':start'=>"{$overtime_date} 00:00:00",':end'=>"{$overtime_date} 23:59:59", ':user_id'=>$this->user->user_id, ':type'=>'normal')))
        {
            $response['code'] = -3;
            $response['msg'] = "overtime duplicate";
        }
        else if(Overtime::createOvertime($data['content'],$overtime_date, $overtime_time, $this->user))
        {
            $response['code'] = 0;
            $response['msg'] = "create overtime success";
        }
        echo CJSON::encode($response);
    }
    
    /**
     * @ignore
     *负责人加班签名
     *@url /ajax/overtimeProcess
     *@param string $id_arr array(1,2,3,5) Id的一维数组
     *@param string $tag    array('success','reject')
     *@return array()
     *{'code':0,'msg':'tag overtime success'}
     *{'code':-1,'msg':'tag overtime fail'}
     *{'code':-2,'msg':'param error'}
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionOvertimeProcess()
    {
        //$_POST = array('tag'=>'reject','id_arr'=>array('1','2'));
        $data['id_arr'] = empty($_POST['id_arr']) ? array() : $_POST['id_arr'];
        $data['tag']    = empty($_POST['tag']) ? '' : $_POST['tag'];
        $response = array('code'=>-1, 'msg'=>'tag overtime fail');
        if(!ActivityJoin::validateIds($data['id_arr']) || !in_array($data['tag'], array('success','reject')))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(Overtime::tagOvertime($data['id_arr'], $data['tag'], $this->user->user_id))
        {
            $response['code'] = 0;
            $response['msg'] = "tag overtime success";
        }
        echo CJSON::encode($response);
    }

    // public function actionTest()
    // {
    //     $users =  Users::model()->findAll("status != 'quit'");
    //     foreach($users as $user)
    //     {
    //         echo $user->user_id;
    //         echo ' ';
    //         echo $user->cn_name;
    //         echo ' ';
    //         //Users::getAdminTag($user);
    //         echo '<br>';
    //     }
    //     //echo '<pre>';
    //     //var_dump($action->joins);
    //     $data = array('work','admin','hr','it');
    //     $pos = array_search('it',$data);
    //     $count = count($data)-1;
    //     echo ($pos == $count) ? $data[$pos] : $data[$pos+1] ;
    // }

    /**
     *意见反馈
     *@url /ajax/suggest
     *@param string $content 意见反馈的内容
     *@param string $url   前端可以用echo $this->url获取
     *@return array()
     #{'code':o,'msg':'commit suggest success'}  提交意见反馈成功
     #{'code':-1,'msg':'commit suggest fail'}    提交意见反馈失败
     #{'code':-2,'msg':'param error'}            参数错误
     #{'code':-99,'msg':'permission denied'}     没有权限
     */
    public function actionSuggest()
    {
        //$_POST = array('content'=>'ddd','url'=>'ddd');
        $content = empty($_POST['content']) ? '' : htmlspecialchars($_POST['content']);
        $content = strip_tags($content);
        $url     = empty($_POST['url']) ? '' : htmlspecialchars($_POST['url']);
        $response = array('code'=>'-1','msg'=>'commit suggest fail');
        if(empty($content) || empty($url))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        }
        else if(Notice::addSuggest($content, $url,$this->user))
        {
            $response['code'] = 0;
            $response['msg'] = 'commit suggest success';
        }
        echo CJSON::encode($response);
    }

    /**
     *判断今天（当前登录用户）是否已经提交过了加班
     *@url /ajax/isSubmitOvertime
     *@param sting $date     加班日期
     *@return array
     #{'code':0, 'overtime is submitted'} 加班已经提交
     #{'code':-1, 'msg':'ajax fail'}      ajax请求失败
     #{'code':-2, 'msg':'param error'}    参数错误
     #{"code":-3,"msg":"overtime is not submit"} 没有加班
     #{'code':-99,'msg':'permisssion denied'} 没有权限
     */
    // public function actionIsSubmitOvertime()
    // {
    //     $date = empty($_POST['date'])?'':$_POST['date'];
    //     $response = array('code'=>'-1','msg'=>'ajax fail');
    //     if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
    //     {
    //         $response['code'] = -2;
    //         $response['msg']  = 'param error';
    //     }
    //     else if(empty($this->user))
    //     {
    //         $response['code'] = -99;
    //         $response['msg']  = 'permission denied';
    //     }
    //     else 
    //     {
    //         if(Overtime::model()->find('overtime_date=:date and user_id = :user_id',array(':date'=>$date, ':user_id'=>$this->user->user_id)))
    //         {
    //             $response['code'] = 0;
    //             $response['msg']  = 'overtime is submitted';
    //         }
    //         else
    //         {
    //             $response['code'] = -3;
    //             $response['msg']  = 'overtime is not submit';
    //         }
    //     }
    //     echo CJSON::encode($response);
    // }

    /**
     *发起公告的方法
     *@url /ajax/addNotify
     *@param string $type 类型 ENUM('admin','holiday','internal','activity','appointments') 行政通知  放假通知 内部悬赏 活动通知 人事任命
     *@param string $title   公告标题
     *@param string $content 公告内容
     *@param string $expire_time  过期时间 格式 YYYY-MM-DD
     *@reutrn array
     #{'code':-1,'msg':'add notify fail'}  添加公告失败
     #{'code':-2,'msg':'param error'}      参数错误
     #{'code':-99,'msg':'permission denied'}没有权限
     #{'code':0, 'msg':'add notify success'}添加公告成功
     */
    public function actionAddNotify()
    {
        #$_POST = array('type'=>'internal','title'=>'<script>tt</script>','content'=>'test','expire_time'=>'2014-11-26');
        $data['type'] = empty($_POST['type']) ? '' : $_POST['type'];
        $data['title'] = empty($_POST['title']) ? '' : strip_tags($_POST['title']);
        $data['content'] = empty($_POST['content']) ? '' : strip_tags($_POST['content']);
        $data['expire_time'] = empty($_POST['expire_time']) ? '0000-00-00' : $_POST['expire_time'];
        $response = array('code'=>-1,'msg'=>'add notify fail');
        if(!in_array($data['type'],array('admin','holiday','internal','activity','appointments')) || empty($data['title']) || 
            empty($data['content']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$data['expire_time']))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if($data['expire_time'] != '0000-00-00' && $data['expire_time'] < date('Y-m-d'))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(empty($this->user) || empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Notification::processNotify(new Notification(),array_merge($data,array('user_id'=>$this->user->user_id,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s'),'status'=>'display'))))
        {
            $response['code'] = 0;
            $response['msg']  = 'add notify success';
        }
        echo CJSON::encode($response);
    }

    /**
     *撤销公告
     *@url /ajax/revokeNotify
     *@param string $id  公告记录ID
     *@return array
     #{'code':0,'msg':'revoke notify success'} 撤销工作记录成功
     #{'code':-1,'msg':'revoke notify fail'}   撤销工作记录失败
     #{'code':-2,'msg':'param error'}          参数错误
     #{'code':-3,'msg':'not found'}            没有找到该公告
     #{'code':-99,'msg':'permission denied'}   没有权限
     */
    public function actionRevokeNotify()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1,'msg'=>'revoke notify fail');
        if(!preg_match('/^[1-9]\d*$/',$id))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        elseif(!$notify = Notification::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(empty($this->user) || empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Notification::processNotify($notify, array('expire_time'=>date('Y-m-d'),'status'=>'hidden')))
        {
            $response['code'] = 0;
            $response['msg']  = 'revoke notify success';
        }
        echo CJSON::encode($response);
    }

    /**
     *编辑公告
     *@url /ajax/editNotify
     *@param string $id           公告ID
     *@param string $title        公告标题
     *@param string $content      公告内容
     *@param string $expire_time  过期时间 格式 YYYY-MM-DD
     *@reutrn array
     #{'code':0, 'msg':'edit notify success'}  编辑公告通知
     #{'code':-1,'msg':'edit notify fail'}     编辑公告失败
     #{'code':-2,'msg':'param error'}          参数错误
     #{'code':-3,'msg':'not found'}            没有找到公告的记录
     #{'code':-99,'msg':'permission denied'}   没有权限
     */
    public function actionEditNotify()
    {
        //$_POST = array('id'=>1,'title'=>'<script>tt</script>','content'=>'tesdsdffdst','expire_time'=>'2014-11-28');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $data['title'] = empty($_POST['title']) ? '' : strip_tags($_POST['title']);
        $data['content'] = empty($_POST['content']) ? '' : strip_tags($_POST['content']);
        $data['expire_time'] = empty($_POST['expire_time']) ? '0000-00-00' : $_POST['expire_time'];
        $response = array('code'=>-1,'msg'=>'edit notify fail');
        if(empty($data['title'])|| empty($data['content'])|| 
            !preg_match('/^[1-9]\d*$/',$id) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/',$data['expire_time']))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if($data['expire_time'] != '0000-00-00' && $data['expire_time'] < date('Y-m-d'))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$notify = Notification::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'not found';
        }
        else if(empty($this->user) || empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Notification::processNotify($notify,$data))
        {
            $response['code'] = 0;
            $response['msg']  = 'edit notify success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *修改OA操作人员的接口
     *@url /ajax/editOperate
     *@param string $id       操作记录的ID
     *@param stirng $object_id 用户的ID
     *@return array()
     #{'code':'0','msg':'write operate success'} 修改操作人员成功
     #{'code':'-1','msg':'write operate fail'}   修改操作人员失败
     #{'code':'-2','msg':'param error'}          参数错误
     #{'code':'-3','msg':'not found'}            没有找到该操作人员记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionEditOperate()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $object_id = empty($_POST['object_id']) ? '' : $_POST['object_id'];

        $response = array('code'=>-1, 'msg'=>'write quit reason fail');
        $pattern = '/^[1-9]\d*$/';
        if(!preg_match($pattern, $id) || !preg_match($pattern, $object_id))
        {
            $response['code'] = -2;
            $response['msg']   = 'param error';
        }
        else if(!$operator = Operator::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']   = 'not found';
        }
        else if(empty($this->user) || empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }
        else if(Operator::processOperator($operator, array('object_id'=>$object_id)))
        {
            $response['code'] = 0;
            $response['msg']  = 'write quit reason success';
        }
        echo CJSON::encode($response);
    }

    /**
     *删除操作人员的接口
     *@url /ajax/deleteOperate
     *@param string $id 操作人员对象的ID
     *@return array()
     #{'code':'0','msg':'delete operate success'} 删除操作人员成功
     #{'code':'-1','msg':'delete operate fail'}   删除操作人员失败
     #{'code':'-2','msg':'param error'}           参数错误
     #{'code':'-3','msg':'not found'}             没有找到操作人员记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionDeleteOperate()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'delete operate fail');
        $pattern = '/^[1-9]\d*$/';
        if(!preg_match($pattern, $id))
        {
            $response['code'] = -2;
            $response['msg']   = 'param error';
        }
        else if(!$operator = Operator::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']   = 'not found';
        }
        else if(empty($this->user) || empty(Yii::app()->session['admin']) || $operator->type != 'feedback')
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }
        else if(Operator::model()->deleteByPk($id)) 
        {
            $response['code'] = 0;
            $response['msg']  = 'delete operate success';
        }
        echo CJSON::encode($response);
    }

    /**
     *添加操作人员的接口
     *@url /ajax/createOperate
     *@param string $type  类型
     *@param string $comment 备注
     *@param string $object_id 用户的ID
     *@return array()
     #{'code':'0','msg':'create operate success'} 添加操作人员记录成功
     #{'code':'-1','msg':'create operate fail'}   添加操作人员记录成功
     #{'code':'-2','msg':'param error'}           参数错误
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionCreateOperate()
    {
        //$_POST = array('type'=>'feedback','comment'=>'反馈的人','object_id'=>4);
        $data['type'] = empty($_POST['type']) ? '' : $_POST['type'];
        $data['comment'] = empty($_POST['comment']) ? '' : strip_tags($_POST['comment']);
        $data['object_id'] = empty($_POST['object_id']) ? '' : $_POST['object_id'];
        $pattern = '/^[1-9]\d*$/';
        $response = array('code'=>-1,'msg'=>'create operate fail');
        if(!preg_match($pattern, $data['object_id']) || empty($data['comment']) || empty($data['type']))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(empty($this->user) || empty(Yii::app()->session['admin']) || $data['type'] != 'feedback')
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }
        else if(Operator::processOperator(new Operator(), array('type'=>$data['type'],'comment'=>$data['comment'],'object_id'=>$data['object_id'],'create_time'=>date('Y-m-d H:i:s'), 'update_time'=>date('Y-m-d H:i:s'))))
        {
            $response['code'] = 0;
            $response['msg']   = 'create operate success';
        }
        echo CJSON::encode($response);
    }

    /*
     *删除用户角色的接口
     *@url /ajax/deleteRoles
     *@param string $id 操作人员对象的ID
     *@return array()
     {'code':'0','msg':'delete operate success'} 删除成功
     {'code':'-1','msg':'delete operate fail'}   删除失败
     {'code':'-2','msg':'param error'}           参数错误
     {'code':'-3','msg':'not found'}             没有找到记录
     {'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionDeleteRoles()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'delete operate fail');
        $pattern = '/^[1-9]\d*$/';
        $super_count = count(Roles::model()->findAll('role_name=:role_name and status=:status',
            array(':role_name'=>'super', ':status'=>'enable')));
        $del_item = Roles::model()->find('id=:id',array(':id'=>$id));
        if(!preg_match($pattern, $id))
        {
            $response['code'] = -2;
            $response['msg']   = 'param error';
        }
        else if(!$row_roles = Roles::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']   = 'not found';
        }
        else if(empty($this->user) || !(Roles::Check_role('super', $this->user)) )
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }

        else if (($super_count==1) && ($del_item->role_name=='super')) {
            $response['code'] = -4;
            $response['msg']   = 'can not been delete';
        }

        else if(Roles::model()->deleteByPk($id)) 
        {
            $response['code'] = 0;
            $response['msg']  = 'delete operate success';
        }
        echo CJSON::encode($response);
    }

    public function actionCreateRole()
    {
        //$_POST = array('type'=>'feedback','comment'=>'反馈的人','object_id'=>4);
        $data['roles_name'] = empty($_POST['roles_name']) ? '' : $_POST['roles_name'];
        $data['user_id'] = empty($_POST['user_id']) ? '' : strip_tags($_POST['user_id']);
        $pattern = '/^[1-9]\d*$/';
        $response = array('code'=>-1,'msg'=>'create operate fail');
        if(!preg_match($pattern, $data['user_id']))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(empty($this->user) || !(Roles::Check_role('super', $this->user)) )   // 只有超级管理员才可以删除权限
        {
            $response['code'] = -99;
            $response['msg']   = 'permission denied';
        }

        else if(Roles::processRoles(new Roles(), array('role_name'=>$data['roles_name'],'user_id'=>$data['user_id'])))
        {
            $response['code'] = 0;
            $response['msg']   = 'create operate success';
        }
        echo CJSON::encode($response);
    }

    /**
     *HR给员工提交一个转正申请
     *@param string $user_id    用户ID
     *@param string $trial_salary 转正钱薪资
     *@param string $promise_salary 转正后薪资
     *@param string $work_life     工作年限
     *@reutrn array()
     #{'code':0,'msg':'add qualify apply success'} 添加转正申请成功
     #{'code':-1,'msg':'add qualify apply fail'}   添加转正申请失败
     #{'code':-2,'msg':'param error'}              参数错误
     #{'code':-3,'msg':'user not found'}           没有找到该用户记录
     #{'code':-4,'msg':'formal employee'} //此user已经转正了
     #{'code':-5,'msg':'add log fail'} //添加log失败
     #{'code':-99,'msg':'permission denied'}
     */
    public function actionCreateQualifyApply()
    {
        #$_POST = array('user_id'=>32, 'trial_salary'=>'2500','promise_salary'=>'4000','work_life'=>0);
        $user_id = empty($_POST['user_id']) ? '' : $_POST['user_id'];
        $trial_salary = empty($_POST['trial_salary']) ? '' : $_POST['trial_salary'];
        $promise_salary = empty($_POST['promise_salary']) ? '' : $_POST['promise_salary'];
        $work_life  = empty($_POST['work_life']) ? '0' : $_POST['work_life'];
        $response = array('code'=>-1,'msg'=>'add qualify apply fail');

        $pattern = "/^[1-9]\d*$/";
        if(!preg_match($pattern, $user_id) || !preg_match($pattern, $trial_salary) || !preg_match($pattern, $promise_salary)
            || !preg_match('/^\d+$/', $work_life))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(empty(Yii::app()->session['user_id']) || empty(Users::getHr()->user_id) || Users::getHr()->user_id != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        else if(!$user = Users::model()->findByPk($user_id))
        {
            $response['code'] = -3;
            $response['msg'] = 'user not found';
        }
        else if($user->job_status == 'formal_employee')
        {
            $response['code'] = -4;
            $response['msg'] = 'formal employee';
        }
        else if(!$procedure_list = Procedure::getProcedure('positive_apply',0 ,$user_id) ) {
            $response['code'] = -4;
            $response['msg'] = 'positive_apply procedure_list empty';
        }
        elseif($id = QualifyApply::processQualifyApply(new QualifyApply() ,array('submit_id'=>Yii::app()->session['user_id'],'next'=>$user_id,'user_id'=>$user_id, 'status'=>'wait','type'=>'contract', 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'),'trial_salary'=>$trial_salary,'promise_salary'=>$promise_salary,'work_life'=>$work_life, 'procedure_list'=>CJSON::encode($procedure_list) )))
        {
            if(QualifyApply::noticeUserById($id,$user_id, "已提交",'self'))
            {
                $response['code'] = 0;
                $response['msg'] = 'add qualify apply success';
            }
            else
            {
                $response['code'] = -5;
                $response['msg'] = 'add log fail';
            }
        }
        echo CJSON::encode($response);
    }
    
    /**
     *申请转正的接口 修改自actionApplyQualify
     *@url /ajax/userQualify
     *@param string $id           转正申请的ID
     *@param string $evaluation   员工自评
     *@param string $plan         个人规划
     *@param string $suggest      意见和建议
     *@param array  $contents     array(array('serial','content','proportion','reference','quantity','completion_rate','delay_rate','rework_rate') )                  序号     工作内容 占比  参考内容 工作量 完成率 延误率 返工率
     *@return array()
     #{code:0 ,msg:commit qualify apply success} 提交转正详情成功
     #{code:-1,msg:commit qualify apply fail}    提交转正详情失败
     #{code:-2,msg:param error}                  参数错误
     #{code:-3,msg:apply not found} 申请没有找到
     #{code:-4,msg:formal employee} 你已经转正了
     #{code:-99,msg:permission denied} 没有权限
     */
    public function actionUserQualify()
    {
        #$_POST= array('id'=>'37','evaluation'=>'灭有什么','plan'=>'OK','suggest'=>'没有');
        #$_POST['contents'] = array(array('serial'=>1,'content'=>'OK','proportion'=>'100','reference'=>'','quantity'=>5,'completion_rate'=>50,'delay_rate'=>20,'rework_rate'=>30));
        $data['id'] = empty($_POST['id']) ? '' : $_POST['id'];
        $data['evaluation'] = empty($_POST['evaluation']) ? '' : $_POST['evaluation'];
        $data['plan'] = empty($_POST['plan']) ? '' : $_POST['plan'];
        $data['suggest'] = empty($_POST['suggest']) ? '' : $_POST['suggest'];
        $contents = empty($_POST['contents']) ? '' : $_POST['contents'];
        
        $apply_null = true;                      //通知第一个审批者
        $next = "";
        if($apply = QualifyApply::model()->findByPk($data['id']))
        {
            $procedure_list = CJSON::decode($apply->procedure_list, true);
            $next = $procedure_list[0];
            $apply_null = false;
        }

        $response = array('code'=>-1, 'id'=>'0','msg'=>'commit qualify apply fail');
        if(!preg_match('/^[1-9]\d*$/', $data['id']) || empty($data['evaluation']) || empty($data['plan']) || empty($data['suggest']) ||  !QualifyReport::validateData($contents))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        elseif( $apply_null )
        {
            $response['code'] = '-3';
            $response['msg']  = 'apply not found';
        }//job_status 状态  intern' 实习生 , 'probation_employee' 试用员工 ,'formal_employee' 正式员工
        else if($apply->user->job_status == 'formal_employee')
        {
            $response['code'] = '-4';
            $response['msg']  = 'formal employee';
        }
        elseif(empty(Yii::app()->session['user_id'])  || Yii::app()->session['user_id'] != $apply->next)
        {
            $response['code'] = -99;
            $response['msg'] = 'permission denied';
        }
        else if(!QualifyApply::processQualifyApply($apply, array_merge(array('next'=>$next,'update_time'=>date('Y-m-d H:i:s')),$data)))
        {
            $response['code'] = -1;
            $response['msg']  = 'commit qualify apply fail';
        }
        else if(QualifyReport::addReport($apply->id , $contents) &&  QualifyApply::noticeUserById($apply->id, $next, "已提交,请尽快审批",'other'))
        {
            $response['code'] = 0;
            $response['msg']  = 'commit qualify apply success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *部门负责人指定相应的面试官
     *@url /ajax/interviewer 
     *@param stirng $id  简历ID
     *@param string $interviewer 面试官用户ID
     *@return array()
     #{'code':'0' ,'msg':'set interviewer success'} 设置面试官成功
     #{'code':'-1','msg':'set interviewer fail'}    设置面试官失败
     #{'code':'-2','msg':'param error'}             参数错误
     #{'code':'-3','msg':'apply not found'}         没有找到该申请
     #{'code':'-4','msg':'interviewer not found'}   没有找到该面试官用户信息
     #{'code':'-99','msg':'permission denied'}      没有权限
     */
    public function actionInterviewer()
    {
        #$_POST=array('id'=>'55','interviewer'=>'32');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $interviewer = empty($_POST['interviewer']) ? '' : $_POST['interviewer'];
        $response = array('code'=>-1, 'msg'=>'set interviewer fail');
        
        if(!preg_match('/^[1-9]\d*$/', $id) || !preg_match('/^[1-9]\d*$/', $interviewer))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        else if(!$resume = Resume::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg']  = 'apply not found';
        }
        else if(!Users::model()->findByPk($interviewer))
        {
            $response['code'] = -4;
            $response['msg']  = 'interviewer not found';
        }
        else if(empty(Yii::app()->session['user_id']) || $resume->apply->user_id != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(Resume::processResume($resume , array('interviewer'=>$interviewer)) && Resume::noticeInterviewer($resume))
        {
            $response['code'] = 0;
            $response['msg']  = 'set interviewer success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *面试官提交面试评估表
     *@url /ajax/interviewerAssessment
     *@param string $id        面试评估的ID
     *@param string $experience  工作经验 
     *@param string $skill       专业技能 
     *@param string $execution   执行力
     *@param string $attitude    工作态度
     *@param string $communicate 沟通能力
     *@param string $learning    主动学习
     *@return array()
     #{'code':'0' ,'msg':'interviewer assessment success'} 提交面试官评估成功
     #{'code':'-1','msg':'interviewer assessment fail'}    提交面试官评估失败
     #{'code':'-2','msg':'param error'}                    参数错误
     #{'code':'-3','msg':'not found'}                      没有找到该面试评估记录
     #{'code':'-99','msg':'permission denied'}             没有权限
     */
    public function actionInterviewerAssessment()
    {
        //$_POST = array('id'=>19,'experience'=>'10','skill'=>'10','execution'=>'10','attitude'=>'10','communicate'=>'10','learning'=>'10');
        $data['id'] = empty($_POST['id']) ? '' : $_POST['id'];
        $data['experience'] = empty($_POST['experience']) ? '' : $_POST['experience'];
        $data['skill'] = empty($_POST['skill']) ? '' : $_POST['skill'];
        $data['execution'] = empty($_POST['execution']) ? '' : $_POST['execution'];
        $data['attitude'] = empty($_POST['attitude']) ? '' : $_POST['attitude'];
        $data['communicate'] = empty($_POST['communicate']) ? '' : $_POST['communicate'];
        $data['learning'] = empty($_POST['learning']) ? '' : $_POST['learning'];
        $resume = Resume::model()->find('id=:id', array(':id'=>$data['id']));
        $response  = array('code'=>-1, 'msg'=>'interviewer assessment fail');

        $action = "agree";
        $pattern = '/^[1-9]\d*$/';
        $int_pattern = '/^\d+$/';
        if(!preg_match($pattern, $data['id'])|| !preg_match($int_pattern, $data['experience']) || !preg_match($int_pattern, $data['skill']) || !preg_match($int_pattern, $data['execution']) || !preg_match($int_pattern, $data['attitude']) || !preg_match($int_pattern, $data['communicate']) || !preg_match($int_pattern, $data['learning']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(!$id = Assessment::processAssessment(new Assessment(), array('resume_id'=>$data['id'], 'entry_day'=>'0000-00-00 00:00:00','next'=>$resume->interviewer,'periods'=>'0','probation_salary'=>'0','official_salary'=>'0','experience'=>0, 'skill'=>0,   'execution'=>0, 'attitude'=>0, 'communicate'=>0, 'learning'=>0,'status'=>$action=='agree'?'wait':'reject','reason'=>'','update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'))))
        {
            $response['code'] = -1;
            $response['msg'] = 'add assessment fail';
        }
        else if(empty(Yii::app()->session['user_id']) ||  Yii::app()->session['user_id'] != $resume->interviewer)
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(!$assessment = Assessment::model()->find('id=:id', array(':id'=>$id)))
        {
            $response['code'] = -1;
            $response['msg'] = 'add assessment fail';
        }
        else if(Resume::processResume($resume, array('status'=>'assessment')) && Assessment::processAssessment($assessment, array_merge($data,array('next'=>$assessment->resume->apply->user_id))) && Assessment::noticeDepartmentLeader($assessment, $assessment->resume->apply->user_id))
        {
            $response['code'] = 0;
            $response['msg'] = 'interviewer assessment success';
        }
        
        echo CJSON::encode($response);
    }


    /**
     *交接物品
     *@url /ajax/newHandoverWork
     *@param string $id     离职申请单的ID
     *@param array $contents  交接的详情
     *@return array
     #{'code':'0', 'msg':'handover work success'} 交接物品成功
     #{'code':'-1','msg':'handover work fail'}    交接物品失败
     #{'code':'-2','msg':'param error'}           参数错误
     #{'code':'-3','msg':'not found'}             没有找到该离职记录
     #{'code':'-4','msg':'duplicate commit'}      重复提交离职交接
     #{'code'=>'-99','msg'=>'permission denied'}  //没有权限
     */
    public function  actionNewHandoverWork()
    {
        #$_POST = array('id'=>32, 'contents'=>array('工作交接'=>array('文档','daima'),'领用物品'=>array('PHP代码','zentao')));
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $contents = empty($_POST['contents']) ? '' : $_POST['contents'];
        $response = array('code'=>-1, 'msg'=>'handover work fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || empty($contents))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if(!$apply->getPermission(Yii::app()->session['user_id']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(QuitHandover::model()->find("apply_id=:id and type=:type",array(':id'=>$id, ':type'=>$apply->handover_type)))
        {
            $response['code'] = -4;
            $response['msg']  = 'duplicate commit';
        }
        else if(QuitHandover::processTransaction($apply,$this->user,$contents))
        {
            $response['code'] = 0;
            $response['msg']  = 'handover work success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *监督人确认工作交接
     *@url /ajax/newConfirmWorkHandover
     *@param string $id   离职申请单的ID
     *@return array
     #{'code':'0', 'msg':'confirm handover work success'}  确认交接成功
     #{'code':'-1','msg':'confirm handover work fail'}     确认交接失败
     #{'code':'-2','msg':'param error'}                    参数错误
     #{'code':'-3','msg':'not found'}                      没有找到该离职记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionNewConfirmWorkHandover()
    {
        //$_POST['id'] = 32;
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'confirm handover work fail');
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        else if(!$apply = QuitApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if(!$handover = QuitHandover::model()->find('apply_id=:id and type=:type',array(':id'=>$apply->id,'type'=>$apply->handover_type)))
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        else if($handover->status != 'wait' || $apply->handover_type != 'hr' || $handover->supervision_id != Yii::app()->session['user_id'])
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        else if(QuitHandover::newConfirmHandler($apply))
        {
            $response['code'] = 0;
            $response['msg']  = 'confirm handover work success';
        }
        echo CJSON::encode($response);
    }

    /**
     *审批周末节假日加班
     *@url /ajax/approveOvertime
     *@param string $id   周末节假日加班ID
     *@param string $action 操作  ENUM('agree','reject')
     *@param string $reason 拒绝原因  action='reject'的时候必须要有
     *@return array
     #{code:0,msg:approve overtime success} 同意假期加班成功
     #{code:-1,msg:approve overtime fail}   同意假期加班失败
     #{code:-2,msg:param error}             参数错误
     #{code:-3,msg:overtime not found}      没有找到该记录
     #{code:-99,msg:permission denied}      没有权限
     */
    public function actionApproveOvertime()
    {
        //$_POST = array('id'=>69, 'action'=>'reject','reason'=>'dsd');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $action = empty($_POST['action']) ? '' : $_POST['action'];
        $reason = empty($_POST['reason']) ? '' : $_POST['reason'];
        $response = array('code'=>-1, 'msg'=>'approve overtime fail');

        if(!preg_match('/^[1-9]\d*$/', $id) || !in_array($action, array('agree','reject')) || ($action=='reject' && empty($reason)))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$overtime = Overtime::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'overtime not found';
        }
        elseif($overtime->type != 'holiday' || $overtime->status !='wait' || Yii::app()->session['user_id'] != $overtime->next)
        {
            $response['code'] ='-99';
            $response['msg'] = 'permission denied';
        }
        elseif(Overtime::approveOvertime($overtime, array('overtime_id'=>$overtime->id, 'action'=>$action,'reason'=>$reason,'user_id'=>$this->user->user_id, 'create_time'=>date('Y-m-d H:i:s'))))
        {
            $response['code'] ='0';
            $response['msg'] = 'approve overtime success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *获取该月份本部门的加班情况表
     *@url /ajax/getDepartmentOvertime
     *@param string $month  月份 格式 YYYY-MM
     *@return array
     #{code:0,msg:get data success,data:{"4":{"name":"Jeff","title":"IT","days":0,"times":3},....} 获取数据成功
     #{code:-1,msg:get data fail,data:[]}   获取数据失败
     #{code:-2,msg:param error,data:[]}     参数错误
     #{code:-3,msg:vertime record not found,data:[]}   没有找到加班记录
     #** name为用户名称 title为职位  days天数  times次数
     */ 
    public function actionGetDepartmentOvertime()
    {
        $month = empty($_POST['month']) ? '' : $_POST['month'];
        $start = date('Y-m-01 00:00:00',strtotime($month));
        $end   = date('Y-m-t 23:59:59',strtotime($month));
        $response = array('code'=>-1, 'data'=>array(), 'msg'=>'get data fail');
        $data = array();
        if(!preg_match('/^\d{4}-\d{2}$/', $month))
        {
            $response['code'] =-2;
            $response['msg']  ='param error';
        }
        elseif(!$result = Overtime::getOvertimeDataByDepartment($start, $end, $this->user->department_id))
        {
            $response['code'] =-3;
            $response['msg']  ='overtime record not found';
        }
        else
        {
            foreach($result as $row)
            {
                if(empty($data[$row->user_id]['name']))  $data[$row->user_id]['name'] = $row->user->cn_name;
                if(empty($data[$row->user_id]['title']))  $data[$row->user_id]['title'] = $row->user->title;
                $data[$row->user_id]['days'] = empty($data[$row->user_id]['days']) ? 0 :  $data[$row->user_id]['days'];
                $data[$row->user_id]['times'] = empty($data[$row->user_id]['times']) ? 0 :  $data[$row->user_id]['times'];
                if($row['type'] == 'normal')
                {
                    $data[$row->user_id]['times'] += 1;
                }
                else
                {
                    $data[$row->user_id]['days'] +=$row->countWorkTime;
                }
            }
            $response['code'] = 0;
            $response['msg'] = 'get data success';
            $response['data'] = $data;
        }
        echo CJSON::encode($response);
    }

    /**
     *获取用户自己该月份的加班情况表
     *@url /ajax/getUserOvertime
     *@param string $month 月份 格式：YYYY-MM
     *@return array
     #{code:0,msg:get data success,data:{"name":"\u8d56\u957f\u6c5f","title":"\u8fd0\u7ef4\u5de5\u7a0b\u5e08","days":0,"times":3,"amount":150,"leaveCount":0}}   获取记录成功
     #{code:-1,msg:get data fail,data:[]} 获取记录失败
     #{code:-2,msg:param error,data:[]}  参数错误
     #{code:-3,msg:vertime record not found,data:[]} 没有数据
     #**name用户名称 title职位 days加班天数 times加班次数 amount可以领取的补贴 leaveCount还有几天补休
     */ 
    public function actionGetUserOvertime()
    {
        $month = empty($_POST['month']) ? '' : $_POST['month'];
        $start = date('Y-m-01 00:00:00',strtotime($month));
        $end   = date('Y-m-t 23:59:59',strtotime($month));
        $response = array('code'=>-1, 'data'=>array(), 'msg'=>'get data fail');
        $data = array();
        if(!preg_match('/^\d{4}-\d{2}$/', $month))
        {
            $response['code'] =-2;
            $response['msg']  ='param error';
        }
        elseif(!$result = Overtime::model()->findAll("user_id=:user_id and status=:status and start_time >= :start and start_time <= :end",
            array(':user_id'=>$this->user->user_id, ':status'=>'success',':start'=>$start, ':end'=>$end)
        ))
        {
            $response['code'] =-3;
            $response['msg']  ='overtime record not found';
        }
        else
        {
            foreach($result as $row)
            {
                if(empty($data['name']))  $data['name'] = $row->user->cn_name;
                if(empty($data['title']))  $data['title'] = $row->user->title;
                $data['days'] = empty($data['days']) ? 0 :  $data['days'];
                $data['times'] = empty($data['times']) ? 0 :  $data['times'];
                if($row['type'] == 'normal')
                {
                    $data['times'] += 1;
                }
                else
                {
                    $data['days'] +=$row->countWorkTime;
                }
            }
            $data['amount'] = $data['times'] * 50;
            $data['leaveCount'] = Overtime::getCompensatTime($this->user->user_id);
            $response['code'] = 0;
            $response['msg'] = 'get data success';
            $response['data'] = $data;
        }
        echo CJSON::encode($response);
    }

    /**
     *获取当前时段 部门中人请假情况
     *@url /ajax/getDepartmentLeaveInfo
     *@param string $start 开始时间
     *@param string $end   结束时间
     *@return array
     #{code:0,msg:get data success,data:[array(3) {
    ["name"]=>
    string(9) "黄永生"
    ["title"]=>
    string(15) "运维工程师"
    ["list"]=>
    array(3) {
      ["2014-09-05"]=>
      string(6) "casual"
      ["2014-09-06"]=>
      string(6) "casual"
      ["2014-09-10"]=>
      string(6) "casual"
    }]}  获取数据成功
     #{code:-1,msg:get data fail,data:[]} 获取数据错误
     #{code:-2,msg:param error,data:[]}   参数错误
     */
    public function actionGetDepartmentLeaveInfo()
    {
        //$_POST = array('start'=>'2015-02-08','end'=>'2015-02-14');
        $start = empty($_POST['start']) ? '' : $_POST['start'];
        $end = empty($_POST['end']) ? '' : $_POST['end'];
        $response = array('code'=>-1, 'msg'=>'get data fail', 'data'=>array());
        $data = array();
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) 
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        elseif($leaves = Leave::getDepartmentLeaveInfo($start, $end, $this->user->department_id))
        {
            foreach($leaves as $row)
            {
                $data[$row['user_id']]['name'] = $row['cn_name'];
                $data[$row['user_id']]['title'] = $row['title'];
                $data[$row['user_id']]['list'] = empty($data[$row['user_id']]['list'])?array():$data[$row['user_id']]['list'];
                $_start = date('Y-m-d',strtotime($row['start_time']));
                $_end = date('Y-m-d',strtotime($row['end_time']));
                for($i=$_start; $i<=$_end; $i=date('Y-m-d',strtotime('+1days',strtotime($i))))
                {
                    if($i < $start) continue;
                    $data[$row['user_id']]['list'][$i]=$row['type'];
                    if($i == $end) break;
                }
            }
            $response['code'] = '0';
            $response['data'] = $data;
            $response['msg']  = 'get data success';
        }
        echo CJSON::encode($response);
    }

    /**
     *添加年度预算
     *@url /ajax/addYearBudget
     *@param string $year 年份
     *@param array  $data array('office'=>array('1'=>'100','2'=>'200'),......) 十二钟预算类型的各部门金额
     *@return array
     #{'code':'0', 'msg':'add budget success'}  添加预算成功
     #{'code':'-1', 'msg':'add budget fail'}    添加预算失败
     #{'code':-2, 'msg':'param error'}          参数错误
     #{'code':'-3', 'msg':'year duplicate'}     该年份已经添加过了
     #{'code':'-99','msg':'permission denied'}  没有权限
     */
    public function actionAddYearBudget()
    {
        #$_POST['year'] = '2015';
        #$_POST['data'] = array('office'=>array('1'=>'2000','2'=>'3000'));
        $year = empty($_POST['year']) ? '' : $_POST['year'];
        $arr = empty($_POST['data']) ? array() : $_POST['data'];
        $data = $this->processBudget($arr);
        $response = array('code'=>-1, 'msg'=>'add budget fail');
        if(!preg_match('/^\d{4}$/', $year) || !$this->validateBudget($data))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        elseif(empty($this->user) || ($this->user->user_id !=Users::getCeo()->user_id && $this->user->department_id != Department::adminDepartment()->department_id))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif(Budget::model()->find("year=:year",array(':year'=>$year)))
        {
            $response['code'] = -3;
            $response['msg']  = 'year duplicate';
        }
        elseif(Budget::addYearBudget($year, $data))
        {
            $response['code'] = '0';
            $response['msg']  = 'add budget success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *更新年度预算
     *@url /ajax/edityearbudget
     *@param string $year 年份
     *@param array  $data array('office'=>array('1'=>'100','2'=>'200'),......)十二钟预算类型的各部门金额
     *@return array
     #{'code':'0', 'msg':'edit budget success'} 编辑预算成功
     #{'code':'-1', 'msg':'edit budget fail'}   编制预算失败
     #{'code':-2, 'msg':'param error'}          参数错误
     #{'code':'-99','msg':'permission denied'}  没有权限
     */
    public function actionEditYearBudget()
    {
        #$_POST['year'] = '2015';
        #$_POST['data'] = array('office'=>array('1'=>'8000','2'=>'9000'));
        $year = empty($_POST['year']) ? '' : $_POST['year'];
        $arr = empty($_POST['data']) ? array() : $_POST['data'];
        $data = $this->processBudget($arr);
        $response = array('code'=>-1, 'msg'=>'edit budget fail');
        if(!preg_match('/^\d{4}$/', $year) || !$this->validateBudget($data))
        {
            $response['code'] = -2;
            $response['msg']  = 'param error';
        }
        elseif(empty($this->user) || ($this->user->user_id !=Users::getCeo()->user_id && $this->user->department_id != Department::adminDepartment()->department_id))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif(Budget::updateYearBudget($year, $data))
        {
            $response['code'] = '0';
            $response['msg']  = 'edit budget success';
        }
        echo CJSON::encode($response);
    }

    /*
     *由于提交的budget的数组有问题，所有用这个转换下
     */
    private function processBudget($arr)
    {
        $data = array();
        foreach($arr as $row)
        {
            $data[$row['type']] = array();
            if(empty($row['data_list']) || !is_array($row['data_list'])) 
            {
                continue;
            }
            foreach($row['data_list'] as $_row)
            {
                $data[$row['type']][$_row['department_id']] = $_row['num'];
            }
        }
        return $data;
    }
    /**
     *验证预算数据
     *验证通过 返回true  否则 返回false
     */
    private function validateBudget($data)
    {
        $departments = Department::model()->findAll(array('order'=>'department_id desc'));
        $budget_types = array('office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other');
        $pattern = '/^\d+$/';
        foreach($budget_types as $type)
        {
            foreach($departments as $row)
            {
                if(!isset($data[$type][$row->department_id]))
                {
                    return false;
                }
                if(!preg_match($pattern, $data[$type][$row->department_id]))
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     *查找此年度是否有添加预算
     *@url /ajax/searchYearBudget
     *@param string $year 年份
     *@return array
     #{'code':'0', 'msg':'search budget success'} 查找该年份预算成功
     #{'code':'-1', 'msg':'search budget fail'}   查找该年份预算失败
     #{'code':-2, 'msg':'param error'}            参数错误
     #{'code':'-3', 'msg':'not found'} //没有找到数据
     */
    public function actionSearchYearBudget()
    {
        $year = empty($_POST['year']) ? '' : $_POST['year'];
        $response = array('code'=>'-1','msg'=>'search budget fail');
        if(!preg_match('/^\d{4}$/', $year))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(Budget::model()->find("year=:year",array(':year'=>$year)))
        {  
            $response['code'] = '0';
            $response['msg']  = 'search budget success';
        }
        else
        {
            $response['code'] = '-3';
            $response['msg']  = 'not found';
        }
        echo CJSON::encode($response);
    }


    /**
     * @ignore
     *删除年度预算
     *@url /ajax/removeYearBudget
     *@param string $year
     *@return array
     *{'code':'0', 'msg':'remove budget success'}
     *{'code':'-1', 'msg':'remove budget fail'}
     *{'code':-2, 'msg':'param error'}
     *{'code':'-99','msg':'permission denied'}
     */
    public function actionRemoveYearBudget()
    {
        $year = empty($_POST['year']) ? '' : $_POST['year'];
        $response = array('code'=>-1, 'msg'=>'remove budget fail');
        if(!preg_match('/^\d{4}$/', $year))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty($this->user) || 
            ($this->user->user_id !=Users::getCeo()->user_id && $this->user->department_id != Department::adminDepartment()->department_id))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif(!Budget::model()->findAll("year=:year",array(':year'=>$year)))
        {
            $response['code'] = 0;
            $response['msg']  = 'remove budget success';
        }
        elseif(Budget::model()->deleteAll("year=:year",array(':year'=>$year)))
        {
            $response['code'] = 0;
            $response['msg']  = 'remove budget success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *提交物资申请单
     *@url /ajax/singleGoodsApply
     *@param array $data
     #*******$data的键********
     #$category, $type , $name , $quantity , $price , $url , $reason.$buy_way,$use_time 在多维数组内
     #@param string $category  类型
     #分类：Office 办公费, welfare 福利费, travel  差旅费, entertain 业务招待费, hydropower  水电费 ,Intermediary 中介费,rental 租赁费,test   测试费,outsourcing 外包费,property 物管费,repair  修缮费,other   其他
     #@param string $type 发送二级分类的名称
     #@param string $name  申请名称
     #@param string $quantity 数量
     #@param string $price   单价
     #@param string $url     网购的URL
     #@param string $reason  申请原因
     #@param string $buy_way 购买方式
     #@param string $use_time  试用时间
     *@param stirng $tag ENUM(0,1)不发给总经理审批  发给总经理审批
     *@result array
     #{"code":0,"id":"10","msg":"apply goods success"} //请假拒绝成功
     #{"code":-1,"id":"0","msg":"apply goods fail"} //请假拒绝失败
     #{"code":-2,"id":"0","msg":"param error"}//参数错误
     #{"code":-4,"id":"0","msg":"size error"} 附件不能超过10M
     #{"code":-5,"id":"0","msg":"attachments upload fail"}附件上传失败
     #{'code'=>'-99',"id":"0",'msg'=>'permission denied'}   //没有权限
     */
    public function actionSingleGoodsApply()
    {
        #$_POST = array('data'=>array(array('category'=>'welfare', 'name'=>'测试','type'=>'兴趣小组','name'=>'名称','quantity'=>'1个','price'=>'2323','url'=>'http://baidu.com','reason'=>'dsdsd','buy_way'=>'xxx','use_time'=>'2015-02-09')));
        $data = empty($_POST) ? array() : $_POST;
        $tag = empty($_POST['tag']) ? false : true;
        if(isset($data['tag']))  unset($data['tag']);
        $file = CUploadedFile::getInstanceByName('file');
        $response = array('code'=>-1,'id'=>'0', 'msg'=>'apply goods fail');

        if(!GoodsApplyDetail::validateNewData(array($data)))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!empty($file) && ($file->getSize() == 0 || $file->getSize() > 10*1024*1024))
        {
            $response['code'] = -4;
            $response['msg'] = 'size error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        } 
        else if($id = GoodsApply::addGoodsApply(array($data) , $this->user, $tag))
        {
            $model = GoodsApply::model()->findByPk($id);
            if(!empty($file))
            {
                  $dir = Yii::getPathOfAlias('webroot.attachment.goods').DIRECTORY_SEPARATOR;
                  $detail = empty($model->details[0]) ? '0' : $model->details[0];
                  $filename = $temp = '';
                  $temp=$file->name; 
                  $filename = iconv('utf-8','gbk',$file->name);
                  // var_dump($file->saveAs($dir."{$filename}"), !$file->hasError, GoodsApplyDetail::processGoodsDetail($detail , array('path'=>"/attachment/goods/{$temp}")));exit();
                  if($file->saveAs($dir."{$filename}") && !$file->hasError && GoodsApplyDetail::processGoodsDetail($detail , array('path'=>"/attachment/goods/{$temp}")) != false)
                  {
                        $response['code'] = 0;
                        $response['msg'] = "apply goods success";
                        $response['id'] = $id;
                  }
                  else
                  {
                        $response['code'] = -5;
                        $response['msg'] = "attachments upload fail";
                        $response['id'] = $id;
                  }
  
            }
            else
            {
                $response['code'] = 0;
                $response['msg'] = "apply goods success";
                $response['id'] = $id;
            }
        }
        #echo CJSON::encode($response);
        echo "<?xml version='1.0' encoding='utf-8'?>
        <response>
        <code>{$response['code']}</code>
        <id>{$response['id']}</id>
        <msg>{$response['msg']}</msg>
        </response>";
    }

    /**
     *提交物资申请单
     *@url /ajax/goodsApply
     *@param stirng $tag ENUM(0,1)不发给总经理审批  发给总经理审批
     *@param array $data
     #*******$data的键********
     #$category, $type , $name , $quantity , $price , $url , $reason.$buy_way,$use_time 在多维数组内
     #@param string $category  类型
     #分类：Office 办公费, welfare 福利费, travel  差旅费, entertain 业务招待费, hydropower  水电费 ,Intermediary 中介费,rental 租赁费,test   测试费,outsourcing 外包费,property 物管费,repair  修缮费,other   其他
     #@param string $type 发送二级分类的名称
     #@param string $name  申请名称
     #@param string $quantity 数量
     #@param string $price   单价
     #@param string $url     网购的URL
     #@param string $reason  申请原因
     #@param string $buy_way 购买方式
     #@param string $use_time  试用时间
     *@result array
     #{"code":0,"id":"10","msg":"apply goods success"} //请假拒绝成功
     #{"code":-1,"id":"","msg":"apply goods fail"} //请假拒绝失败
     #{"code":-2,"id":"","msg":"param error"}//参数错误
     #{'code'=>'-99',"id":"",'msg'=>'permission denied'}   //没有权限
     */
    public function actionGoodsApply()
    {
        #$_POST = array('data'=>array(array('category'=>'welfare', 'name'=>'测试','type'=>'兴趣小组','name'=>'名称','quantity'=>'1个','price'=>'2323','url'=>'http://baidu.com','reason'=>'dsdsd','buy_way'=>'xxx','use_time'=>'2015-02-09')));
        $data = empty($_POST['data']) ? array() : $_POST['data'];
        $tag = empty($_POST['tag']) ? false : true;
        $response = array('code'=>-1,'id'=>'', 'msg'=>'apply goods fail');

        if(!GoodsApplyDetail::validateNewData($data))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if(empty($this->user))
        {
            $response['code'] = -99;
            $response['msg'] = "permission denied";            
        } 
        else if($id = GoodsApply::addGoodsApply($data , $this->user, $tag))
        {
            $response['code'] = 0;
            $response['msg'] = "apply goods success";
            $response['id'] = $id;
        }
        echo CJSON::encode($response);
    }
    
    /**
     *同意物资申请
     *@url /ajax/agreeGoodsApply
     *@param string $id   物资申购的记录ID
     *@param stirng $tag ENUM(0,1)不发给总经理审批  发给总经理审批
     *@result array
     #{"code":0,"msg":"agree goods apply success"} //请假拒绝成功
     #{"code":-1,"msg":"agree goods apply fail"} //请假拒绝失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"goods apply not found"}//请假记录未发现
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionAgreeGoodsApply()
    {
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $tag = empty($_POST['tag']) ? false : true;
        $response = array('code'=>-1, 'msg'=>'agree goods apply fail');
        //同意 就是修改next
        //添加日志
        //发给下一级
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = '-2';
            $response['msg']  = 'param error';
        }
        elseif(!$apply = GoodsApply::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'goods apply not found';
        }
        elseif(empty($this->user) || $apply->next != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        else if($apply->status != 'wait') {
            $response['code'] = -4;
            $response['msg']  = 'apply status not wait';
        }
        elseif(GoodsApply::agreeApply($apply, $this->user, $tag))
        {
            $response['code'] = '0';
            $response['msg']  = 'agree goods apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *报销接口
     *@url /ajax/reimburse  
     *@param string $way array('transfer','borrow') 报销方式 转账/借支
     *@param string $bank_info  开户支行信息
     *@param string $bank_code  银行卡号
     *@param string $payee      收款人
     *@param string $borrow_amount 借支金额
     *@param string $receipt_num   发票张数
     *@param array $details 多维数组 array(array('apply_id'=>'','apply_detail_id'=>'','content'=>'','have_receipt'=>'','amount'=>''),......)
     *@return array
     *{"code":0,"msg":"reimburse success"} //报销成功
     *{"code":-1,"msg":"reimburse fail"} //报销失败
     *{"code":-2,"msg":"param error"}       //参数错误
     *{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionReimburse()
    {
        $data['way'] = 'transfe';
        $data['bank_info'] = '0';
        $data['bank_code'] = '0';
        $data['payee'] = '<a></a>';
        $data['borrow_amount'] = 0;
        $data['receipt_num'] = 0;
        $details = empty($_POST['details']) ? array() : $_POST['details'];
        $response = array('code'=>-1, 'msg'=>'reimburse fail');

        if( !ReimburseDetail::validateDetails($details) )
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty($this->user) || !ReimburseDetail::validatePremission($details, $this->user->user_id))
        {
            $response['code'] = '-98';
            $response['msg']  = 'permission denied';
        }
        elseif(Reimburse::addReimburse($data,$details, $this->user))
        {
            $response['code'] = '0';
            $response['msg']  = 'reimburse success';
        }
        echo CJSON::encode($response);
    }

    /**
    *修改报销接口
    *@param string $way array('transfer','borrow') 报销方式 转账/借支
    *@param string $bank_info  开户支行信息
    *@param string $bank_code  银行卡号
    *@param string $payee      收款人
    *@param string $borrow_amount 借支金额
    *@param string $receipt_num   发票张数
    */
    public function actionEditReimburse() {
        // $_PST = array( 'id'=>381, 'way'=>'transfer', 'bank_info'=>'招商', 'bank_code' => '12345678', 
        // 'payee' => '测试', 'borrow_amount'=>"", 'receipt_num'=>2,
        // );
        $id = empty($_POST['id']) ? 0 : (int)$_POST['id'];
        $data['way'] = empty($_POST['way']) ? "" : htmlspecialchars($_POST['way']);
        $data['bank_info'] = empty($_POST['bank_info']) ? "" : htmlspecialchars($_POST['bank_info']);
        $data['bank_code'] = empty($_POST['bank_code']) ? "" : htmlspecialchars($_POST['bank_code']);
        $data['payee'] = empty($_POST['payee']) ? "" : htmlspecialchars($_POST['payee']);
        $data['borrow_amount'] = empty($_POST['borrow_amount']) ? "" : htmlspecialchars($_POST['borrow_amount']);
        $data['receipt_num'] = empty($_POST['receipt_num']) ? 0 : (int)$_POST['receipt_num'];
        $data['status'] = 'submitted'; //将状态改为已提交报销单

        $response = array('code'=>-1, 'msg'=>'reimburse fail');
        if(!in_array($data['way'],array('transfer','borrow')) || ($data['way']=='transfer' && (empty($data['bank_info']) || !preg_match('/^\d+$/', $data['bank_code']) || empty($data['payee']))) || ($data['way']=='borrow' && !preg_match('/^\d+(\.\d+)?$/', $data['borrow_amount'])) || !preg_match('/^\d+$/', $data['receipt_num']) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif ( !$reimburse_info = Reimburse::model()->findByPK($id) ) {
            $response['code'] = -3;
            $response['msg'] = 'not find';
        }
        elseif ($reimburse_info->status != 'wait') {
            $response['code'] = -4;
            $response['msg'] = 'can not been edited';
        }
        elseif ($reimburse_info->user_id != Yii::app()->session['user_id'] ) {
            $response['code'] = -98;
            $response['msg'] = 'not this user';
        }
        elseif ( !$data['bank_info'] .= " {$data['bank_code']}" ) {
            $response['code'] = -5;
            $response['msg'] = 'operation failed';
        }
        elseif ( Reimburse::model()->updateByPK($id, $data) ) {
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    /**
     *修改兴趣小组的组长
     *@url /ajax/editTeam
     *@param string $team_id //小组ID
     *@param string $user_id //用户ID
     *@return array
     #{"code":0,"msg":"edit team admin success"} //编辑组长成功
     #{"code":-1,"msg":"edit team admin fail"} //编辑组长失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"team not found"}       //没有找到该组
     #{"code":-4,"msg":"user not found"}       //没有找到该人
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionEditTeam()
    {
        //$_POST = array('team_id'=>1, 'user_id'=>4);
        $team_id = empty($_POST['team_id']) ? '' : $_POST['team_id'];
        $user_id = empty($_POST['user_id']) ? '' : $_POST['user_id'];
        $response = array('code'=>'-1', 'msg'=>'edit team admin fail');
        if(!preg_match('/^[1-9]\d*$/', $team_id) || !preg_match('/^[1-9]\d*$/', $user_id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty(Yii::app()->session['admin']))
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif(!$team = InterestTeam::model()->findByPk($team_id))
        {
            $response['code'] = '-3';
            $response['msg']  = 'team not found';
        }
        elseif(!Users::model()->findByPk($user_id ,"status='work'"))
        {
            $response['code'] = '-4';
            $response['msg']  = 'user not found';
        }
        elseif($team->admin == $user_id || InterestTeam::model()->updateByPk($team_id, array('admin'=>$user_id)))
        {
            $response['code'] = '0';
            $response['msg']  = 'edit team admin success';
        }
        echo CJSON::encode($response);
    }

    /**
     *添加兴趣小组的预算
     *@url /ajax/addTeamBudget
     *@param string $year  年份
     *@param array  $data array(array('team_id'=>1,'total'=>2),array('team_id'=>2,'total'=>2),....)9个小组的预算
     *@return array
     #{"code":0,"msg":"add budget success"} //添加预算成功
     #{"code":-1,"msg":"add budget fail"} //添加预算失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"year duplicate"}       //该年份的预算已经提交
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionAddTeamBudget()
    {
        #$_POST['year'] = '2015';
        #$_POST['data'] = array(array('team_id'=>1,'total'=>2),array('team_id'=>2,'total'=>2),array('team_id'=>3,'total'=>2),array('team_id'=>4,'total'=>2),array('team_id'=>5,'total'=>2),array('team_id'=>6,'total'=>2),array('team_id'=>7,'total'=>2),array('team_id'=>8,'total'=>2),array('team_id'=>9,'total'=>2));
        $year = empty($_POST['year']) ? '' : $_POST['year'];
        $arr = empty($_POST['data']) ? array() : $_POST['data'];
        $data = InterestTeamBudget::parseData($arr);
        $response = array('code'=>-1,'msg'=>'add budget fail');
        if(!preg_match('/^\d{4}$/', $year) || !InterestTeamBudget::validateData($data))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty(Yii::app()->session['admin']))
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif(InterestTeamBudget::model()->find("year=:year",array(':year'=>$year)))
        {
            $response['code'] = -3;
            $response['msg'] = 'year duplicate';
        }
        elseif(InterestTeamBudget::batchAddBudget($data,$year))
        {
            $response['code'] = '0';
            $response['msg']  = 'add budget success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *编辑兴趣小组的预算
     *@url /ajax/editTeamBudget
     *@param string $year 年份
     *@param array  $data array(array('team_id'=>1,'total'=>2),array('team_id'=>2,'total'=>2),....) 9个小组的预算数据
     *@return array
     #{"code":0,"msg":"edit budget success"} //编辑预算成功
     #{"code":-1,"msg":"edit budget fail"} //编辑预算失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"year budget not found"}       //年份预算没有找到
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionEditTeamBudget()
    {
        #$_POST['year'] = '2015';
        #$_POST['data'] = array(array('team_id'=>1,'total'=>9),array('team_id'=>2,'total'=>2),array('team_id'=>3,'total'=>2),array('team_id'=>4,'total'=>2),array('team_id'=>5,'total'=>2),array('team_id'=>6,'total'=>2),array('team_id'=>7,'total'=>2),array('team_id'=>8,'total'=>2),array('team_id'=>9,'total'=>2));
        $year = empty($_POST['year']) ? '' : $_POST['year'];
        $arr = empty($_POST['data']) ? array() : $_POST['data'];
        $data = InterestTeamBudget::parseData($arr);
        $response = array('code'=>-1,'msg'=>'edit budget fail');
        if(!preg_match('/^\d{4}$/', $year) || !InterestTeamBudget::validateData($data))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty(Yii::app()->session['admin']))
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif(!InterestTeamBudget::model()->find("year=:year",array(':year'=>$year)))
        {
            $response['code'] = -3;
            $response['msg'] = 'year budget not found';
        }
        elseif(InterestTeamBudget::editBudget($data,$year))
        {
            $response['code'] = '0';
            $response['msg']  = 'edit budget success';
        }
        echo CJSON::encode($response);
    }

    /**
     *创建小组活动
     *@url /ajax/addTeamActivity
     *@param string $team_id  小组ID
     *@param string $end_time 格式：2015-01-01 10:10 报名截止时间
     *@param string $activity_time 格式：2015-01-01 10:10 报名举办时间
     *@return array
     #{"code":0,"msg":"add activity success"} //添加预算成功
     #{"code":-1,"msg":"add activity fail"} //添加预算失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"team not found"}       //没有找到该小组
     #{"code":-4,"msg":"budget not enough"}       //小组预算不足
     #{"code":-5,"msg":"activity holding"}       //有活动在举办中
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionAddTeamActivity()
    {
        //$_POST=array('team_id'=>3,'end_time'=>'2015-03-01 10:10','activity_time'=>'2015-03-02 10:10');
        $team_id = empty($_POST['team_id']) ? '': $_POST['team_id'];
        $end_time = empty($_POST['end_time']) ? '': $_POST['end_time'].":00";
        $activity_time = empty($_POST['activity_time']) ? '': $_POST['activity_time'].":00";
        $response = array('code'=>'-1','msg'=>'add activity fail');
        $time_pattern = "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/";
        if(!preg_match('/^[1-9]\d*$/', $team_id) || !preg_match($time_pattern, $end_time) || !preg_match($time_pattern, $activity_time))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$team = InterestTeam::model()->findByPk($team_id))
        {
            $response['code'] = -3;
            $response['msg'] = 'team not found';
        }
        elseif($team->admin != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif(InterestTeamActivity::model()->find("team_id=:team_id and (status='enroll' or status='hold')",array(':team_id'=>$team_id)))
        {
            $response['code'] = -5;
            $response['msg'] = 'activity holding';
        }
        elseif(!InterestTeamBudget::getYearBudget($team_id))
        {
            $response['code'] = '-4';
            $response['msg']  = 'budget not enough';
        }
        elseif($id = InterestTeamActivity::processTeamActivity(new InterestTeamActivity(),array('team_id'=>$team_id,'end_time'=>$end_time,'status'=>'enroll', 'activity_time'=>$activity_time, 'update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'))))
        {
            InterestTeamJoin::processJoinActivity(new InterestTeamJoin(), array('activity_id'=>$id, 'user_id'=>$this->user->user_id, 'status'=>'enroll','update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s')));
            $response['code'] = 0;
            $response['msg'] = 'add activity success';
        }
        echo CJSON::encode($response);
    }

    /**
     *取消参加活动
     *@url /ajax/cancelJoinActivity
     *@param stirng $id 小组活动的ID
     *@return array
     #{"code":0,"msg":"join activity success"} //加入活动成功
     #{"code":-1,"msg":"join activity fail"} //加入活动失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"activity not found"}       //没有找到该活动
     #{"code":-4,"msg":"do not sign up"}       //该活动目前不在报名阶段
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionCancelJoinActivity()
    {
        //$_POST['id'] = 3;
        $id = empty($_POST['id'])?'':$_POST['id'];
        $response = array('code'=>'-1','msg'=>'cancel join activity fail');
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$activity = InterestTeamActivity::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'activity not found';
        }
        elseif($activity->status != 'enroll')
        {
            $response['code'] = -4;
            $response['msg'] = 'do not sign up';
        }
        elseif(!$join = InterestTeamJoin::model()->find("activity_id=:activity_id and user_id=:user_id",array(':activity_id'=>$id, ':user_id'=>$this->user->user_id)))
        {
            $response['code'] = 0;
            $response['msg'] = 'cancel join activity success';
        }
        elseif($join->delete())
        {
            $response['code'] = 0;
            $response['msg'] = 'cancel join activity success';
        }
        echo CJSON::encode($response);
    }

    /**
     *发送活动路线
     *@url /ajax/sendActivityLine
     *@param string $id            活动的ID
     *@param stirng $activity_time 活动举办时间
     *@param stirng $contact       联系人
     *@param stirng $mobile        联系手机
     *@param stirng $address       联系地址
     *@param stirng $line          路线信息
     *@return array
     #{"code":0,"msg":"send activity success"} //加入活动成功
     #{"code":-1,"msg":"send activity fail"} //加入活动失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"activity not found"}       //没有找到该活动
     #{"code":-4,"msg":"only edit hour and minute"}       //只能修改举办活动的小时和分钟
     #{"code":-5,"msg":"activity not hold"}       //目前活动没有举办
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionSendActivityLine()
    {
        //#$_POST = array('id'=>'3','activity_time'=>'2015-03-02 10:10','contact'=>'jeff','mobile'=>'1582164','address'=>'ddddd','line'=>'ddddd');
        $data['id'] = empty($_POST['id']) ? '' :$_POST['id'];
        $data['activity_time'] = empty($_POST['activity_time']) ? '' :$_POST['activity_time'].":00";
        $data['contact'] = empty($_POST['contact']) ? '' :$_POST['contact'];
        $data['mobile'] = empty($_POST['mobile']) ? '' :$_POST['mobile'];
        $data['address'] = empty($_POST['address']) ? '' :$_POST['address'];
        $data['line'] = empty($_POST['line']) ? '' :$_POST['line'];
        $response = array('code'=>'-1','msg'=>'send activity fail');
        if(!preg_match('/^[1-9]\d*$/', $data['id']) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['activity_time']) || empty($data['contact']) || empty($data['mobile']) || empty($data['address']) || emptY($data['line']))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$activity = InterestTeamActivity::model()->findByPk($data['id']))
        {
            $response['code'] = -3;
            $response['msg'] = 'activity not found';
        }
        elseif($activity->team->admin != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif($activity->status != 'hold')
        {
            $response['code'] = -5;
            $response['msg'] = 'activity not hold';
        }
        elseif(date('Y-m-d',strtotime($data['activity_time'])) != date('Y-m-d',strtotime($activity->activity_time)))
        {
            $response['code'] = -4;
            $response['msg'] = 'only edit hour and minute';
        }
        elseif(InterestTeamActivity::model()->processTeamActivity($activity, $data) && InterestTeamActivity::sendMail($activity))
        {
            $response['code'] = 0;
            $response['msg'] = 'send activity success';
        }
        echo CJSON::encode($response);
    }


    /**
     *参加活动
     *@url /ajax/joinActivity
     *@param string $id  活动ID
     *@return array
     #{"code":0,"msg":"join activity success"} //加入活动成功
     #{"code":-1,"msg":"join activity fail"} //加入活动失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"activity not found"}       //没有找到该活动
     #{"code":-4,"msg":"do not sign up"}       //该活动目前不在报名阶段
     #{'code':-5,"msg":"time duplicate"}       //不能同时参加同一时间举办的活动
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionJoinActivity()
    {
        //$_POST['id'] = 3;
        $id = empty($_POST['id'])?'':$_POST['id'];
        $response = array('code'=>'-1','msg'=>'join activity fail');
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$activity = InterestTeamActivity::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'activity not found';
        }
        elseif($activity->status != 'enroll')
        {
            $response['code'] = -4;
            $response['msg'] = 'do not sign up';
        }
        elseif(InterestTeamJoin::isTimeDuplicate($activity->activity_time, $this->user->user_id))
        {
            $response['code'] = -5;
            $response['msg'] = 'time duplicate';
        }
        elseif(InterestTeamJoin::model()->find("activity_id=:activity_id and user_id=:user_id",array(':activity_id'=>$id, ':user_id'=>$this->user->user_id)))
        {
            $response['code'] = 0;
            $response['msg'] = 'join activity success';
        }
        elseif(InterestTeamJoin::processJoinActivity(new InterestTeamJoin(), array('activity_id'=>$id, 'user_id'=>$this->user->user_id, 'status'=>'enroll','update_time'=>date('Y-m-d H:i:s'), 'create_time'=>date('Y-m-d H:i:s'))))
        {
            $response['code'] = 0;
            $response['msg'] = 'join activity success';
        }
        echo CJSON::encode($response);
    }
    
    /**
     *组长取消小组活动
     *@url /ajax/cancelTeamActivity
     *@param string $id  活动小组ID
     *@return array
     #{"code":0,"msg":"cancel activity success"} //取消活动成功
     #{"code":-1,"msg":"cancel activity fail"} //取消活动失败
     #{"code":-2,"msg":"param error"}       //参数错误
     #{"code":-3,"msg":"activity not found"}       //没有找到该活动
     #{"code":-4,"msg":"activity can't cancel"}       //该活动不能取消
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionCancelTeamActivity()
    {
        //$_POST=array('id'=>3);
        $id = empty($_POST['id']) ? '': $_POST['id'];
        $response = array('code'=>'-1','msg'=>'cancel activity fail');
        if(!preg_match('/^[1-9]\d*$/', $id))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$activity = InterestTeamActivity::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'activity not found';
        }
        elseif($activity->team->admin != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif($activity->activity_time < date('Y-m-d H:i:s'))
        {
            $response['code'] = -4;
            $response['msg'] = "activity can't cancel";
        }
        elseif(in_array($activity->status, array('cancel','success')))
        {
            $response['code'] = -4;
            $response['msg'] = "activity can't cancel";
        }
        elseif($activity->status=='cancel' || InterestTeamActivity::cancelActivity($activity))
        {
            $response['code'] = 0;
            $response['msg'] = 'cancel activity success';
        }
        echo CJSON::encode($response);
    }

    /**
     *编辑费用报表说明
     *@url /ajax/editExpenseReport
     *@param string $id  费用报表的ID
     *@param string $description 费用说明
     *@return array
     #{"code":0,"msg":"edit expense report success"}  编辑费用报表成功
     #{"code":-1,"msg":"edit expense report fail"}    编辑费用报表失败
     #{"code":-2,"msg":"param error"}                 参数错误
     #{"code":-3,"msg":"record not found"}            没有找到该记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionEditExpenseReport()
    {
        //$_POST=array('id'=>1,'description'=>'你好OKOK不好');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $description = empty($_POST['description']) ? '' : $_POST['description'];
        $response = array('code'=>'-1','msg'=>'edit expense report fail');
        if(!preg_match('/^[1-9]\d*$/', $id) || emptY($description))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif(empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif(!$report = ExpenseReport::model()->findByPk($id))
        {
            $response['code'] = '-3';
            $response['msg'] = 'record not found';
        }
        elseif($report->description == $description || ExpenseReport::processReportDescription($report, array('description'=>$description)))
        {
            $response['code'] = '0';
            $response['msg'] = 'edit expense report success';
        }
        echo CJSON::encode($response);
    }

    /**
     *设置参加活动的人和设置活动经费
     *@url /ajax/setActivityJoin
     *@param stirng $activity_id  活动ID
     *@param stirng $outlay       活动花费的费用
     *@param string $users        参与用户ID的一维数组
     *@return array()
     #{"code":0,"msg":"set activity success"}  举办活动成功
     #{"code":-1,"msg":"set activity fail"}    举办活动失败
     #{"code":-2,"msg":"param error"}          参数失败
     #{"code":-3,"msg":"activity not found"}   没有找到该活动
     #{"code":-4,"msg":"activity not hold"}    该活动不在举办阶段
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionSetActivityJoin()
    {
        $activity_id = empty($_POST['activity_id']) ? '' : $_POST['activity_id'];
        $outlay = empty($_POST['outlay']) ? '0' : $_POST['outlay'];
        $users  = empty($_POST['users']) ? array() : $_POST['users'];
        $response = array('code'=>-1, 'msg'=>'set activity fail');
        if(!preg_match('/^[1-9]\d*$/', $activity_id) || !preg_match('/^\d+(\.\d+)?$/', $outlay) 
            || empty($users) || !is_array($users))
        {
            $response['code'] = '-2';
            $response['msg'] = 'param error';
        }
        elseif(!$activity = InterestTeamActivity::model()->findByPk($activity_id))
        {
            $response['code'] = '-3';
            $response['msg'] = 'activity not found';
        }
        elseif($activity->team->admin != $this->user->user_id)
        {
            $response['code'] = '-99';
            $response['msg']  = 'permission denied';
        }
        elseif($activity->status != 'hold')
        {
            $response['code'] = -4;
            $response['msg'] = 'activity not hold';
        }
        elseif(InterestTeamActivity::setActivityInfo($activity, $outlay, $users))
        {
            $response['code'] = 0;
            $response['msg'] = 'set activity success';
        }
        echo CJSON::encode($response);
    }

    /**
     *处理申请印章
     *@url /ajax/applySeal
     @param object $file  上传的文件
     @param string $use_time 使用时间
     @param string $number   文件份数
     @param string $address  地址
     @param string $reason   试用原因
     @param array() $type  盖章类型 ENUM('official','financial','legal')公章 财务章 法人章 
     *@return array
     #{"code":0,"msg":"apply seal success"}   申请成功
     #{"code":-1,"msg":"apply seal fail"}     申请失败
     #{"code":-2,"msg":"param error"}         参数错误
     #{"code":-3,"msg":"size error"}   //附件大小错误
     #{"code":-4,"msg":"attachments upload fail"} //附件上传失败
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionApplySeal()
    {
        //$_POST= array('use_time'=>'2015-02-28','number'=>'1','reason'=>'dddd','type'=>array('legal'));
        $file = CUploadedFile::getInstanceByName('file');
        $data['use_time'] = empty($_POST['use_time']) ? '' : $_POST['use_time'];
        $data['number'] = empty($_POST['number']) ? '0' : $_POST['number'];
        $data['address'] = empty($_POST['address']) ? '' : htmlspecialchars($_POST['address']);
        $data['reason'] = empty($_POST['reason']) ? '' : htmlspecialchars($_POST['reason']);
        $data['type'] = empty($_POST['type']) ? '' : $_POST['type'];
        $response = array('code'=>-1, 'id'=>'0','msg'=>'apply seal fail');
        $dir = Yii::getPathOfAlias('webroot.images.seal').DIRECTORY_SEPARATOR;
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['use_time']) || !preg_match('/^\d+$/', $data['number']) || 
            empty($data['reason'])|| empty($data['type']) || !is_array($data['type']) || 
            array_intersect($data['type'],array('official','financial','legal'))!=$data['type'])
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif(!empty($file) && ($file->getSize() == 0 || $file->getSize() > 10*1024*1024))
        {
            $response['code'] = -3;
            $response['msg'] = 'size error';
        }
        elseif($seal = Seal::processApply(new Seal(), array_merge(array('user_id'=>$this->user->user_id, 'update_time'=>date('Y-m-d H:i:s'),'create_time'=>date('Y-m-d H:i:s')),$data)))
        {
            Seal::noticeSeal($seal);
            $filename = $temp = '';
            if(!empty($file)) 
            {
                $temp=$file->name; 
                $filename = iconv('utf-8','gbk',$file->name);
            }
            if(empty($file) || (!empty($file) && $file->saveAs($dir."{$filename}") && !$file->hasError && Seal::processApply($seal , array('path'=>"/images/seal/{$temp}"))))
            {
                $response['code'] = 0;
                $response['id'] = $seal->id;
                $response['msg'] = 'apply seal success';

                $command="/usr/bin/convmv -f GBK -t UTF-8 --notest {$dir}{$filename}";
                @exec($command);
            }
            else
            {
                $response['code'] = -4;
                $response['msg'] = 'attachments upload fail';
            }
        }
         echo "<?xml version='1.0' encoding='utf-8'?>
        <response>
        <code>{$response['code']}</code>
        <msg>{$response['msg']}</msg>
        <id>{$response['id']}</id>
        </response>";
        //echo CJSON::encode($response);
    }
    
    /**
     *编辑申请印章
     *@url /ajax/editSeal
     @param object $file  上传的文件
     @param string $id    印章申请的ID
     @param string $use_time 试用时间
     @param string $number   文件份数
     @param string $address  地址
     @param string $reason   申请原因
     @param array() $type  盖章类型 ENUM('official','financial','legal')公章 财务章 法人章 
     *@return array
     #{"code":0,"msg":"edit seal success"}  编辑印章记录成功
     #{"code":-1,"msg":"edit seal fail"}  编辑印章记录失败
     #{"code":-2,"msg":"param error"}      参数错误
     #{"code":-3,"msg":"size error"}   //附件大小错误
     #{"code":-4,"msg":"attachments upload fail"} //附件上传失败
     #{"code":-5,"msg":"seal not found"} //没有找到记录
     #{'code'=>'-99','msg'=>'permission denied'}   //没有权限
     */
    public function actionEditSeal()
    {
        //$_POST= array('use_time'=>'2015-02-28','number'=>'1','address'=>'qitabo','reason'=>'dddd','type'=>array('legal'));
        $file = CUploadedFile::getInstanceByName('file');
        $data['id'] = empty($_POST['id']) ? '' : $_POST['id'];
        $data['use_time'] = empty($_POST['use_time']) ? '' : $_POST['use_time'];
        $data['number'] = empty($_POST['number']) ? '0' : $_POST['number'];
        $data['address'] = empty($_POST['address']) ? '' : htmlspecialchars($_POST['address']);
        $data['reason'] = empty($_POST['reason']) ? '' : htmlspecialchars($_POST['reason']);
        $data['type'] = empty($_POST['type']) ? '' : $_POST['type'];
        $response = array('code'=>-1, 'msg'=>'edit seal fail');
        $dir = Yii::getPathOfAlias('webroot.images.seal').DIRECTORY_SEPARATOR;
        if(!preg_match('/^[1-9]\d*$/', $data['id']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['use_time']) || !preg_match('/^\d+$/', $data['number']) || 
            empty($data['reason'])|| empty($data['type']) || !is_array($data['type']) || 
            array_intersect($data['type'],array('official','financial','legal'))!=$data['type'])
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(empty(Yii::app()->session['admin']))
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif(!$seal = Seal::model()->findByPk($data['id']))
        {
            $response['code'] = -5;
            $response['msg'] = 'seal not found';
        }
        elseif(!empty($file) && ($file->getSize() == 0 || $file->getSize() > 2*1024*1024))
        {
            $response['code'] = -3;
            $response['msg'] = 'size error';
        }
        elseif(Seal::processApply($seal, array_merge(array( 'update_time'=>date('Y-m-d H:i:s')),$data)))
        {
            Seal::noticeSeal($seal,'修改了');
            $filename = $temp = '';
            if(!empty($file)) 
            {
                $temp=$file->name; 
                $filename = iconv('utf-8','gbk',$file->name);
            }
            if(empty($file) || (!empty($file) && $file->saveAs($dir."{$filename}") && !$file->hasError && Seal::processApply($seal , array('path'=>"/images/seal/{$temp}"))))
            {
                $response['code'] = 0;
                $response['msg'] = 'edit seal success';
            }
            else
            {
                $response['code'] = -4;
                $response['msg'] = 'attachments upload fail';
            }
        }
        //echo CJSON::encode($response);
         echo "<?xml version='1.0' encoding='utf-8'?>
        <response>
        <code>{$response['code']}</code>
        <msg>{$response['msg']}</msg>
        </response>";
    }

    /**
     *通过职位来获取该部门该职位的编制情况
     *@url /ajax/getTitleFormation
     *@param string $department_id  部门ID
     *@param string $title          职位名称
     *@return array  count为在职人数 total为编制人数
     #{'code':0,'msg':'get data success','count'=>0,'total'=>0} 获取数据成功
     #{'code':-1,'msg':'get data fail','count'=>0,'total'=>0}   获取数据失败
     #{'code':-2,'msg':'param error','count'=>0,'total'=>0}   参数错误
     */
    public function actionGetTitleFormation()
    {
        //$_POST = array('title'=>'运维工程师','department_id'=>'7');
        $department_id = empty($_POST['department_id']) ? '0' : $_POST['department_id'];
        $title = empty($_POST['title']) ? '' : htmlspecialchars($_POST['title']);
        $response = array('code'=>-1,'total'=>'0','count'=>'0','msg'=>'get data fail');
        if(!preg_match('/^[1-9]\d*$/', $department_id) || empty($title))
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif($formation = Formation::model()->find("department_id=:id and title=:title",array(':id'=>$department_id,':title'=>$title)))
        {
            $response['code'] = 0;
            $response['msg'] = 'get data success';
            $response['total'] = $formation->number;
            $response['count'] = Formation::getWorkNum($department_id, $title);
        }
        echo CJSON::encode($response);
    }

    // simditor 编辑器图片上传接口
    public function actionUploadEditorPic() {
        $dir = Yii::getPathOfAlias('webroot.images.editor').DIRECTORY_SEPARATOR;
        $response = array('success'=>false , 'msg'=>'upload file', 'img'=>'');
        $user_id = empty(Yii::app()->session['user_id'])? '' : Yii::app()->session['user_id'];
        $timestapm = time();
 
        if( empty($user_id) )
        {
            $response['success'] = false;
            $response['msg']  = 'permission denied';
        }
        else if(!$image = CUploadedFile::getInstanceByName('upload_file'))
        {
            $response['success'] = false;
            $response['msg']  =  'param error';
        }
        else if($image->getSize() == 0 || $image->getSize() > 2*1024*1024)
        {
            $response['success'] = false;
            $response['msg']  =  'size error';
        }
        else if($image->saveAs($dir.$user_id .'-'.$timestapm .".png") && !$image->hasError )
        {
            $response['success'] = true;
            $response['msg']  =  'upload success';
            $response['file_path']  = "/images/editor/{$user_id}-{$timestapm}.png";
            $response['file_name']  = "{$user_id}-{$timestapm}.png";
        }
        echo CJSON::encode($response);
    }

    // 创建文件
    public function actionEditorCreate()
    {
        $data = array();
        $content = empty($_POST['content']) ? '' : $_POST['content'];
        $c_editor_str = empty($_POST['c_editor']) ? '' : $_POST['c_editor'];
        $c_editor = explode(',', $c_editor_str);
        foreach ($c_editor as $key => $value) {
            if ($value == Yii::app()->session['user_id'] )
                unset($c_editor[$key]);
        }
        $data['title'] = empty($_POST['title']) ? '' : $_POST['title'];
        $data['owner_id'] = Yii::app()->session['user_id'];
        $data['last_editor_id'] = Yii::app()->session['user_id'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        $data['approve_user_id'] = 0;                         // 新建的文件暂时先不予提交审批
        $data['status'] = 'wait';
        $data['c_editor'] = CJSON::encode($c_editor);

        $response = array('code'=>-1,'total'=>'0','count'=>'0','msg'=>'get data fail');
        if( empty($content) || empty($data['title']) )
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if( !EditorRoles::model()->find('user_id=:user_id', array(':user_id'=>$data['owner_id'])) ) {
            $response['code'] = -3;
            $response['msg'] = 'cant no create';
        } 
        else {
            $dir = Yii::app()->params['editorTmpFilePath'];
            $data['real_file_name'] = $data['owner_id'] . '.' . $data['title'] . '.' . time();
            $filepath = $dir . $data['real_file_name'];
            Editor::writeData( $filepath, $content);

            if ($result = Editor::createEditor($data)) {
                // 通知审批者审批
                $response['code'] = 0;
                $response['msg'] = 'save data success';
            }
        }
        echo CJSON::encode($response);
    }

    //解锁文件
    public function actionUnlockEditor()
    {
        $editor_id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'get file fail');
        if( empty($editor_id) )
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }

        else if (!$editor = Editor::model()->findByPk($editor_id) ) {
            $response['code'] = -3;
            $response['msg'] = 'file not find';
        }

        elseif ( Editor::releaseFileLock($editor, Yii::app()->session['user_id'] ) ) {
            $response['code'] = 0;
            $response['msg'] = 'success';
        }
        echo CJSON::encode($response);
    }

    public function actionLockEditor() {
        $editor_id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'get file fail');
        if( empty($editor_id) )
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }

        else if (!$editor = Editor::model()->findByPk($editor_id) ) {
            $response['code'] = -3;
            $response['msg'] = 'file not find';
        }
        else if ( !Editor::checkEditAuth($editor,Yii::app()->session['user_id']) ) {
            $response['code'] = 4;
            $response['msg'] = 'no right';
        }
        else if (Editor::getFileLock($editor, Yii::app()->session['user_id']) ) {
            $response['code'] = 0;
            $response['msg'] = 'succes';
        }
        
    }

    //申请发布文件
    public function actionApplyPublish() {
        $editor_id = empty($_POST['id']) ? '' : $_POST['id'];
        $dir_id = empty($_POST['dir_id']) ? 0 : $_POST['dir_id'];
        $response = array('code'=>-1, 'msg'=>'get file fail');
        if ( !$editor = Editor::model()->findByPk($editor_id) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if( ($dir_id!=0)&&(!$dir = EditorDir::model()->findByPk($dir_id)) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ( ($editor['status'] != "wait") || ($editor['approve_user_id'] != 0) || ($editor['owner_id'] != Yii::app()->session['user_id']) ) 
        {
            $response['code'] = -3;
            $response['msg'] = 'apply repeat';
        }
        else if (($editor['lock_status'] != "unlock")) {          //被锁定的文件不能被编辑
            $response['code'] = -4;
            $response['msg'] = 'locked ,can not be apply';
        }
        else if (Editor::sendApplyPublish($editor, $dir_id) ) {  
            $response['code'] = 0;
            $response['msg'] = 'apply success';
        }
        echo CJSON::encode($response);
    }

    //取消发布申请
    public function actionCancelApplyPublish() {
        $editor_id = empty($_POST['id']) ? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'get file fail');
        if ( !$editor = Editor::model()->findByPk($editor_id) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ( ($editor['status'] == "success") || ($editor['approve_user_id'] == 0) )
        {
            $response['code'] = -3;
            $response['msg'] = 'apply repeat';
        }
        else if ($editor['owner_id'] != Yii::app()->session['user_id']) {
            $response['code'] = -4;
            $response['msg'] = 'not owner';
        }
        else if(Editor::cancelApplyPublish($editor)) {
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    //同意文档发布申请
    public function actionAgreePublish() {
        $apply_id = empty($_POST['apply_id']) ? '' : $_POST['apply_id'];
        $response = array('code'=>-1, 'msg'=>'get file fail');
        if ( !$editor_apply = EditorApply::model()->findByPk($apply_id) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ( $editor_apply['status'] != 'wait' ) 
        {
            $response['code'] = -3;
            $response['msg'] = 'apply repeat';
        }
        else if ( $editor_apply['next'] != Yii::app()->session['user_id'] ) 
        {
            $response['code'] = -3;
            $response['msg'] = 'apply repeat';
        }
        elseif ( !$editor = Editor::model()->findByPk($editor_apply['editor_id']) ) {
            $response['code'] = -4;
            $response['msg'] = 'can not find file';
        }
        elseif ( $editor['approve_user_id'] != Yii::app()->session['user_id'] ) {
            $response['code'] = -5;
            $response['msg'] = 'apply been cancel';
        }
        else if ( EditorApply::successApplyPublish($editor_apply) ) {
            $response['code'] = 0;
            $response['msg'] = 'apply success';
        }
        echo CJSON::encode($response);
    }

    //拒绝文档发布申请
    public function actionRejectPublish() {
        $apply_id = empty($_POST['apply_id']) ? '' : $_POST['apply_id'];
        $response = array('code'=>-1, 'msg'=>'get file fail');
        if ( !$editor_apply = EditorApply::model()->findByPk($apply_id) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ( $editor_apply['status'] != 'wait' ) 
        {
            $response['code'] = -3;
            $response['msg'] = 'apply repeat';
        }
        else if ( $editor_apply['next'] != Yii::app()->session['user_id'] ) 
        {
            $response['code'] = -3;
            $response['msg'] = 'no right';
        }
        elseif ( !$editor = Editor::model()->findByPk($editor_apply['editor_id']) ) {
            $response['code'] = -4;
            $response['msg'] = 'can not find file';
        }
        elseif ( $editor['approve_user_id'] != Yii::app()->session['user_id'] ) {
            $response['code'] = -5;
            $response['msg'] = 'apply been cancel';
        }
        else if ( EditorApply::rejectApplyPublish($editor_apply) ) {
            $response['code'] = 0;
            $response['msg'] = 'reject success';
        }
        echo CJSON::encode($response);
    }

    //修改文档 角色
    public function actionChangeEditorRoles() {
        $user_id = empty($_POST['user_id'])? '' : $_POST['user_id'];
        $role = empty($_POST['role'])? '' : $_POST['role'];
        $role_arr = array('admin', 'approver');
        $response = array('code'=>-1, 'msg'=>'set fail');
        // if $user_id = Yii::app()->session['user_id'];
        if ( empty($user_id) || empty($role) || (!in_array($role, $role_arr)) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if ( Yii::app()->session['user_id'] != EditorRoles::getAdminId() ) {      //判断当前用户是否有权限修改
            $response['code'] = -3;
            $response['msg'] = 'no right';
        }
        elseif (EditorRoles::addRoles($user_id, $role)) {
            $response['code'] = 0;
            $response['msg'] = 'succes';
        }
        echo CJSON::encode($response);
    }

    // 删除文档编辑者
    public function actionDeleteRolesEditor() {
        $id = empty($_POST['id'])? '' : $_POST['id'];
        $response = array('code'=>-1, 'msg'=>'set fail');
        $user_id = Yii::app()->session['user_id'];
        if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
            $response['code'] = -3;
            $response['msg'] = 'no right';
        }
        else if ( EditorRoles::model()->deleteByPk($id) ) {
            $response['code'] = 0;
            $response['msg'] = 'succes';
        }
        echo CJSON::encode($response);
    }

    // 增加文档编辑者
    public function actionAddRolesEditor() {
        $user_id_add = empty($_POST['user_id'])? '' : $_POST['user_id'];
        $response = array('code'=>-1, 'msg'=>'set fail');
        $data = array('user_id'=>$user_id_add, 'type'=>'editor', 'create_time'=>date("Y-m-d H:i:s"));
        $user_id = Yii::app()->session['user_id'];
        if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
            $response['code'] = -3;
            $response['msg'] = 'no right';
        }
        else if (EditorRoles::model()->findAll('user_id = :user_id and type=:editor', array(':user_id'=>$user_id_add, ':editor'=>'editor')) ) 
        {
            $response['code'] = -5;
            $response['msg'] = 'exit';
        }
        else if ( EditorRoles::createEditorRoles($data) ) {
            $response['code'] = 0;
            $response['msg'] = 'succes';
        }
        echo CJSON::encode($response);
    }

    //编辑文件的共同编辑者
    public function actionChangeCoEditor() {
        $user_id = Yii::app()->session['user_id'];
        $editor_id = empty($_POST['editor_id'])? "" : $_POST['editor_id'];
        $c_editor_str = empty($_POST['c_editor_list'])? "" : $_POST['c_editor_list'];
        $c_editor_arr = explode(',', $c_editor_str);
        foreach ($c_editor_arr as $key => $value) {
            if($value == $user_id)
                unset($c_editor_arr[$key]);
        }

        $response = array('code'=>-1, 'msg'=>'create fail');

        if( empty($user_id) || empty($editor_id) || empty($c_editor_arr)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (!$editor = Editor::model()->findByPk($editor_id)) {
            $response['code'] = -3;
            $response['msg'] = 'file not exsist';
        }
        else if ($editor['lock_status'] !='unlock' ){       //处于审核状态的文件不能被修改
            $response['code'] = -4;
            $response['msg'] = 'file been locked';
        }
        else if ($editor['approve_user_id'] !=0 ){       //处于审核状态的文件不能被修改
            $response['code'] = -5;
            $response['msg'] = 'file applying';
        }
        else if (($editor['status']=="wait") && ($editor['owner_id'] == $user_id) ){
            $editor['c_editor'] = CJSON::encode($c_editor_arr);
            $editor->save();
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    //保存重新编辑文件
    public function actionSaveReEditor() {
        $id = empty($_POST['id'])? '' : $_POST['id'];
        $title = empty($_POST['title'])? '' : $_POST['title'];
        $content = empty($_POST['content']) ? '' : $_POST['content'];
        $response = array('code'=>-1, 'msg'=>'set fail');
        $user_id = Yii::app()->session['user_id'];

        if (empty($id) || empty($title) || empty($content)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (!$editor = Editor::model()->findByPk($id)) {
            $response['code'] = -3;
            $response['msg'] = 'file not exit';
        }
        else if ($editor['approve_user_id']!=0 ) {
            $response['code'] = -4;
            $response['msg'] = 'file is applying, can not be edit';
        }
        else if( ($editor['status'] =='success') && ($editor['owner_id'] == $user_id) ) {
            $data = array('parent_id'=>$editor['id'], 'owner_id'=>$editor['owner_id'], 'title'=>$title, 'create_time'=>date("Y-m-d H:i:s"),
                    'update_time'=>date("Y-m-d h:i:s"), 'last_editor_id'=>$editor['owner_id'] );
            $dir = Yii::app()->params['editorTmpFilePath'];
            $data['real_file_name'] = $user_id . '.' . $title . '.' . time();
            $filepath = $dir . $data['real_file_name'];
            Editor::writeData( $filepath, $content);
            Editor::createEditor($data);
            $response['code'] = 0;
            $response['msg'] = 'save data success';
        }
        //文件所有者可以编辑已发布和未发布的文档，文件共同编辑者只可以编辑未发布的文档
        else if( Editor::checkEditAuth($editor,$user_id) && ($editor['status'] =='wait') ) {
            $editor['update_time'] = date("Y-m-d H:i:s");
            $dir = Yii::app()->params['editorTmpFilePath'];
            $filepath = $dir . $editor['real_file_name'];
            Editor::writeData( $filepath, $content);
            $editor['last_editor_id'] = Yii::app()->session['user_id'];
            $editor['title'] = $title;
            $editor['lock_status'] = 'unlock';
            $editor['lock_user'] = 0;
            $editor->save(); 
            $response['code'] = 0;
            $response['msg'] = 'save data success';
        }
        echo CJSON::encode($response);
    }

    //新建文件夹  管理员--文档审批者
    public function actionNewEditorDir() {
        $user_id = Yii::app()->session['user_id'];
        $data['parent_id'] = empty($_POST['parent_id'])? '' : $_POST['parent_id'];
        $data['dir_name'] = empty($_POST['dir_name'])? '' : $_POST['dir_name'];
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['update_time'] = date("Y-m-d H:i:s");
        $data['create_user'] = $user_id;

        $response = array('code'=>-1, 'msg'=>'create fail');

        if( empty($data['dir_name']) || empty($user_id)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
            $response['code'] = -99;
            $response['msg'] = 'no auth';
        }
        else if (EditorDir::model()->find('parent_id=:parent_id and dir_name=:dir_name and status=:status',array(':parent_id'=>$data['parent_id'], ':dir_name'=>$data['dir_name'], ':status'=>'enable'))) {
            $response['code'] = -3;
            $response['msg'] = 'dir can not been overwrite';
        }
        else if (EditorDir::createEditorDir($data)) {
            $response['code'] = 0;
            $response['msg'] = 'dir can not been overwrite';
        }
        echo CJSON::encode($response);
    }

    //移动文件
    public function actionRelocationEditorFile() {
        $user_id = Yii::app()->session['user_id'];
        $file_id = empty($_POST['file_id'])? 0 : intval($_POST['file_id']);
        $dir_id = empty($_POST['dir_id'])? 0 : intval($_POST['dir_id']);
        $response = array('code'=>-1, 'msg'=>'create fail');

        if( empty($user_id) || empty($dir_id)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        // else if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
        else if ( $user_id != EditorRoles::getAdminId() ) {        //仅管理员可以移动已经审批的文件
            $response['code'] = -99;
            $response['msg'] = 'no auth';
        }
        else if (!$editor_dir = EditorDir::model()->find('dir_id=:dir_id and status=:status',array(':dir_id'=>$dir_id, ':status'=>'enable'))) {
            $response['code'] = -3;
            $response['msg'] = 'dir can not find';
        }
        else if ( $editor = Editor::model()->findByPk($file_id)) {
            $editor['dir_id'] = $dir_id;
            $editor->save();
            $response['code'] = 0;
            $response['msg'] = 'relocation success';
        }
        echo CJSON::encode($response);
    }

    //移动文件夹
    public function actionRelocationEditorDir() {
        $user_id = Yii::app()->session['user_id'];
        $parent_id = empty($_POST['parent_id'])? 0 : intval($_POST['parent_id']);
        $dir_id = empty($_POST['dir_id'])? 0 : intval($_POST['dir_id']);
        $response = array('code'=>-1, 'msg'=>'create fail');

        if( empty($user_id) || empty($dir_id)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (in_array($parent_id, EditorDir::findSubDir($dir_id)) ) {        //判断父文件id是否是当前文件或者当前文件的子文件夹
            $response['code'] = -7;
            $response['msg'] = 'over';
        }
        else if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
            $response['code'] = -99;
            $response['msg'] = 'no auth';
        }
        else if (!$editor_dir = EditorDir::model()->find('dir_id=:dir_id and status=:status',array(':dir_id'=>$dir_id, ':status'=>'enable'))) {
            $response['code'] = -3;
            $response['msg'] = 'dir can not find';
        }
        else if ( ($parent_id==0)|| (EditorDir::model()->find('dir_id=:dir_id and status=:status',array(':dir_id'=>$parent_id, ':status'=>'enable'))) ) {
            $editor_dir['parent_id'] = $parent_id;
            $editor_dir->save();
            $response['code'] = 0;
            $response['msg'] = 'relocation success';
        }
        echo CJSON::encode($response);
    }

    //删除文件夹
    public function actionDeleteEditorDir() {
        $user_id = Yii::app()->session['user_id'];
        $dir_id = empty($_POST['dir_id'])? 0 : $_POST['dir_id'];
        $response = array('code'=>-1, 'msg'=>'create fail');

        if( empty($user_id) || empty($dir_id)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
            $response['code'] = -99;
            $response['msg'] = 'no auth';
        }
        else if (!$editor_dir = EditorDir::model()->find('dir_id=:dir_id and status=:status',array(':dir_id'=>$dir_id, ':status'=>'enable'))) {
            $response['code'] = -3;
            $response['msg'] = 'dir can not find';
        }
           //存在子文件
        else if ( Editor::model()->find('dir_id=:dir_id and display=:display',array(':dir_id'=>$editor_dir['dir_id'], ':display'=>'yes')) ) {
            $response['code'] = -4 ;
            $response['msg'] = 'can be delete';
        }
        else if ( EditorDir::findSubDir($dir_id) == array($dir_id) ) {         //是否存在子目录
            $editor_dir['status'] = 'disable';
            $editor_dir->save();
            $response['code'] = 0 ;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    //重命名文件夹
    public function actionRenameEditorDir() {
        $user_id = Yii::app()->session['user_id'];
        $new_dir_name = empty($_POST['new_name'])? "" : $_POST['new_name'];
        $dir_id = empty($_POST['dir_id'])? 0 : $_POST['dir_id'];
        $response = array('code'=>-1, 'msg'=>'create fail');

        if( empty($user_id) || empty($dir_id) || empty($new_dir_name) ) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (($user_id != EditorRoles::getApproverId())&&($user_id != EditorRoles::getAdminId())) {
            $response['code'] = -99;
            $response['msg'] = 'no auth';
        }
        else if ($editor_dir = EditorDir::model()->find('dir_id=:dir_id and status=:status',array(':dir_id'=>$dir_id, ':status'=>'enable'))) {
            $editor_dir['dir_name'] = $new_dir_name;
            $editor_dir['update_time'] = date("Y-m-d H:i:s");
            $editor_dir->save();
            $response['code'] = 0;
            $response['msg'] = 'success';
        }
        echo CJSON::encode($response);
    }

    // 删除文件接口
    public function actiondeleteEditor() {
        $editor_id = empty($_POST['editor_id'])? 0 : $_POST['editor_id'];
        $user_id = Yii::app()->session['user_id'];
        $response = array('code'=>-1, 'msg'=>'create fail');

        if(empty($editor_id) || empty($user_id)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (!$editor = Editor::model()->findByPk($editor_id)) {
            $response['code'] = -3;
            $response['msg'] = 'file not exsist';
        }
        else if ($editor['lock_status']!="unlock") {     //被锁定的文件不能被删除
            $response['code'] = -4;
            $response['msg'] = 'file been locked';
        }
        else if ($editor['approve_user_id'] !=0 ){       //处于审核状态的文件不能被删除
            $response['code'] = -5;
            $response['msg'] = 'file applying';
        }
        else if (($editor['status']=="wait") && ($editor['owner_id'] == $user_id) ){   //草稿文件，且为创建人可以删除
            $editor['display'] = 'no';
            $editor->save();
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        else if (($editor['status']=="success") && ($user_id == EditorRoles::getAdminId() ) ){   //已成功发布的文件,仅管理员可以删除
            $editor['display'] = 'no';
            $editor->save();
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    //获取文件内容
    public function actionGetFileContent() {
        $editor_id = empty($_POST['id'])? 0 : $_POST['id'];
        $user_id = Yii::app()->session['user_id'];
        $response = array('code'=>-1, 'msg'=>'create fail', 'content'=>"");
        if(empty($editor_id) || empty($user_id)) {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        else if (!$editor = Editor::model()->findByPk($editor_id)) {
            $response['code'] = -3;
            $response['msg'] = 'file not exsist';
        }
        else {
            if ($editor['status']=='wait')
                $dir = Yii::app()->params['editorTmpFilePath'];
            else
                $dir = Yii::app()->params['editorSuccessFilePath'];
            $filepath = $dir . $editor['real_file_name'];
            $response['code'] = 0;
            $response['msg'] = 'ok';
            $response['content'] = file_get_contents( $filepath);
        }
        echo CJSON::encode($response);
    }

    //内网发送 WOL 远程开机数据包
    public function actionSendWol() {
        $ip_net = empty($_POST['ip_net'])? "" : $_POST['ip_net'];
        $mac_addr = empty($_POST['mac_addr'])? "" : $_POST['mac_addr'];
        $user_id = Yii::app()->session['user_id'];
        $response = array('code'=>-1, 'msg'=>'failed');
        if( empty($user_id) ){
            $response['code'] = -99;
            $response['msg'] = 'not aauth';
		$response = CJSON::encode($response);
        }
        else {
            $url = "http://192.168.0.32/index.php?net=".$ip_net."&mac=".$mac_addr;
            $ch  = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
            $response = curl_exec($ch);
            curl_close($ch);
        }
        echo $response;
    }

    /*
    yeqingwen   2015-12-14 14:48
    删除流程节点接口
    */
    public function actionDelProcedure() {
        $procedure_id = empty($_POST['procedure_id'])? "" : $_POST['procedure_id'];
        $response = array('code'=>-1, 'msg'=>'failed');
        if(Yii::app()->session['user_id'] != Users::getCeo()->user_id) {
            $response['code'] = -90;
            $response['msg'] = 'no auth';
        }
        elseif( empty($procedure_id) ) {
            $response['code'] = -3;
            $response['msg'] = 'param error';
        }
        elseif ( Procedure::model()->deleteByPk($procedure_id) ) {
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    /*
    yeqingwen   2015-12-14 14:48
    编辑流程节点接口
    */
    public function actionEditProcedure() {
        $procedure_id = empty($_POST['procedure_id'])? "" : $_POST['procedure_id'];
        $user_role = empty($_POST['user_role'])? "" : $_POST['user_role'];
        $type = empty($_POST['type'])? "" : $_POST['type'];
        $value = empty($_POST['value'])? 0 : (int)$_POST['value'];

        $type_allow = array('out', 'overtime', 'goods_apply', 'leave', 'recruit','positive_apply','quit_apply','seal','leave');
        $user_role_allow = array('ceo','hr_admin','d2_admin','d_admin');
        $order_arr = array('ceo'=>40, 'hr_admin'=>30, 'd2_admin'=>20, 'd_admin'=>10);

        $response = array('code'=>-1, 'msg'=>'failed');
        if(Yii::app()->session['user_id'] != Users::getCeo()->user_id) {
            $response['code'] = -90;
            $response['msg'] = 'no auth';
        }
        elseif( empty($procedure_id) || empty($user_role) || empty($type) ) {
            $response['code'] = -3;
            $response['msg'] = 'param error';
        }
        elseif ( !in_array($type, $type_allow) || !in_array($user_role, $user_role_allow) ) {
            $response['code'] = -4;
            $response['msg'] = 'type or user_role error';
        }
        elseif ( ! $procedure_info = Procedure::model()->findByPk($procedure_id) ) {
            $response['code'] = -5;
            $response['msg'] = 'can not find the procedure';
        }
        else {
            $procedure_info['user_role'] = $user_role;
            $procedure_info['type'] = $type;
            $procedure_info['value'] = $value;
            $procedure_info['procedure_order'] = $order_arr[$user_role];
            if($procedure_info->save()) {
                $response['code'] = 0;
                $response['msg'] = 'ok';
            }
        }
        echo CJSON::encode($response);
    }

    /*
    yeqingwen   2015-12-14 14:48
    添加流程节点接口
    */
    public function actionAddProcedure() {
        $user_role = empty($_POST['user_role'])? "" : $_POST['user_role'];
        $type = empty($_POST['type'])? "" : $_POST['type'];
        $value = empty($_POST['value'])? 0 : (int)$_POST['value'];
        $procedure_order = empty($_POST['procedure_order'])? "" : (int)$_POST['procedure_order'];

        $type_allow = array('out', 'overtime', 'goods_apply', 'leave', 'recruit','positive_apply','quit_apply','seal','leave');
        $user_role_allow = array('ceo','hr_admin','d2_admin','d_admin');
        $order_arr = array('ceo'=>40, 'hr_admin'=>30, 'd2_admin'=>20, 'd_admin'=>10);

        $response = array('code'=>-1, 'msg'=>'failed');
        if(Yii::app()->session['user_id'] != Users::getCeo()->user_id) {
            $response['code'] = -90;
            $response['msg'] = 'no auth';
        }
        elseif( empty($user_role) || empty($type) ) {
            $response['code'] = -3;
            $response['msg'] = 'param error';
        }
        elseif ( !in_array($type, $type_allow) || !in_array($user_role, $user_role_allow) ) {
            $response['code'] = -4;
            $response['msg'] = 'type or user_role error';
        }
        elseif ( Procedure::model()->find('user_role =:t_user_role and type=:t_type', array(':t_user_role'=>$user_role, ':t_type'=>$type) ) ) {
            $response['code'] = -5;
            $response['msg'] = 'procedure_user_role exsist';
        }
        elseif ( Procedure::model()->find('type=:t_type and procedure_order=:t_order', array(':t_type'=>$type , ':t_order'=>$order_arr[$user_role])) ) {
            $response['code'] = -6;
            $response['msg'] = 'procedure_order exsist';
        }
        else {
            $procedure_info  = new Procedure;
            $procedure_info['user_role'] = $user_role;
            $procedure_info['type'] = $type;
            $procedure_info['value'] = $value;
            $procedure_info['procedure_order'] = $order_arr[$user_role];
            if($procedure_info->save()) {
                $response['code'] = 0;
                $response['msg'] = 'ok';
            }
        }
        echo CJSON::encode($response);
    }

    /**
     *yeqingwen 2015-12-24
     *取消物资申请
     *@url /ajax/rejectGoodsApply
     *@param string $id 物品申购ID
     *@param string $reason 原因
     *@result array
     */
    public function actionCancelGoodsApply()
    {
        // $_POST = array('detail_id'=>1257, 'reason'=>'测试');
        $id = empty($_POST['id']) ? '' : $_POST['id'];
        $reason = empty($_POST['reason']) ? '' : htmlspecialchars($_POST['reason']);

        $response = array('code'=>-1 , 'msg'=>'reject apply failed');
        if( !preg_match('/^[1-9]\d*$/', $id) )
        {
            $response['code'] = -2;
            $response['msg'] = 'param error';
        }
        elseif(!$apply_detail = GoodsApplyDetail::model()->findByPk($id))
        {
            $response['code'] = -3 ;
            $response['msg']  = 'goods apply not found';
        }
        elseif(!$apply = GoodsApply::model()->findByPk($apply_detail->apply_id))
        {
            $response['code'] = -3 ;
            $response['msg']  = 'goods apply not found';
        }
        //仅申请人才可以取消申请
        elseif(empty($this->user) || $apply->user_id != $this->user->user_id)
        {
            $response['code'] = -99;
            $response['msg']  = 'permission denied';
        }
        elseif ( $apply_detail->is_reimburse=='yes' ) {
            $response['code'] = -5 ;
            $response['msg']  = 'reimbursed';
        }
        else if( GoodsApplyDetail::cancleGoodsApplyDetail($apply_detail, $this->user, $reason) ) {    //取消申请单
            $response['code'] = '0';
            $response['msg']  = 'cancel goods apply success';
        }
        echo CJSON::encode($response);
    }

    /**
     *yeqingwen 2015-12-25
     *更改报销单的状态
     *@url /ajax/changeReimburseStatus
     *@param string $id ID
     *@param string $action 状态类型
     *@result array
     */
    public function actionReimburseStatus() {
        // $_POST = array('id'=>1, 'action'=>'wait');
        $id = empty($_POST['id']) ? '' : (int)$_POST['id'];
        $action = empty($_POST['action']) ? '' : htmlspecialchars($_POST['action']);

        $response = array('code'=>-1, 'msg'=>'failed');
        $allow_value = array('success', 'submitted');

        if(empty($id) || empty($action)) {
            $response['code'] = -3;
            $response['msg'] = 'params error';
        }
        elseif (!in_array($action, $allow_value)) {
            $response['code'] = -4;
            $response['msg'] = 'action value not allowed';
        }
        else{
            Reimburse::model()->updateByPk($id, array('status'=>$action));
            $response['code'] = 0;
            $response['msg'] = 'ok';
        }
        echo CJSON::encode($response);
    }

    //获取报销清单信息
    public function actionGetReimburseList() {
        $id = empty($_POST['id']) ? '' : (int)$_POST['id'];
        $response = array('code'=>-1, 'msg'=>'failed', 'data'=>array() );

        if( empty($id) ) {
            $response['code'] = -2;
            $response['msg'] = 'parmas error';
        }
        else if(!$reimburse_info = Reimburse::model()->findByPk($id))
        {
            $response['code'] = -3;
            $response['msg'] = 'not find';
        }
        else {
            $response['code'] = 0;
            $response['msg'] = 'ok';
            foreach ($reimburse_info->details as $row) {
                $response['data'][] = $row->apply_detail;
            }
        }
        echo CJSON::encode($response);
    }

    public function actionTest1() {
        $db = Yii::app()->db;
        $sql_count = "SELECT count(1) as count FROM goods_apply_detail LEFT JOIN goods_apply on goods_apply_detail.apply_id=goods_apply.id WHERE user_id = 80";
        $command = $db->createCommand($sql_count);
        $count = $command->queryAll();
        echo CJSON::encode($count[0]['count']);
    }

}