<?php
/**
 *依赖于php_ldap模块
 *OA控制器
 */
class OaController extends Controller
{
	/**
	 * @var $layout 布局
     * @var $pageTitle 页面名称
     * @var $breadcrumbs 面包屑
     * @var $user   登录用户对象
     * @var $url    当前方法的所在的页面字符串
     * @var $books  图书
     * @var $letters Excel需要用到的字符串
     * @var $recruit_tag 招聘标记 没有什么意义
	 */
    public $layout = 'oa';
    public $pageTitle = '';
    public $breadcrumbs= array(); 
    public $user;
    public $url;
    public $books;
    public $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P','Q','R','S','T','U','V','W','X','Y','Z','AA');
    public $recruit_tag = false;

	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

    public function filters()
    {
        $this->url = Yii::app()->request->getUrl();
        //过滤只用于actionEdit和actionCreate
        
        return array(
            'verify - interviewEvaluateDetail recruitApplyDetail deliverWorkDetail error',
        );
    }
 
    //定义的过滤方法
    public function FilterVerify($filterChain)
    {
        //判断什么的
        //过滤完后继续执行代码
        //acl设置
        if( empty(Yii::app()->session['user_id']) || !preg_match('/^\d+$/',Yii::app()->session['user_id']) )
        {
           Yii::app()->session['refer'] = Yii::app()->request->getUrl();
           header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
        }
        elseif(!$this->user = Users::model()->findByPk(Yii::app()->session['user_id']))
        {
            header('Location: '.Yii::app()->request->hostInfo.'/ajax/logout');
        }
        elseif( !(Yii::app()->session['admin'] || Roles::Check_User_in_roles($this->user)) )
        {
            header('Location: '.Yii::app()->request->hostInfo.'/ajax/logout');
        }
        else
            $filterChain->run();
    }

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        $this->actionMsgs();
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
        $this->layout = 'blank';
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
            {
				echo $error['message'];
            }
			else
            {
				$this->render('error', $error);
            }
		}
	}

	


    /**
     * 图书列表
     */
    public function actionBooks()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = 'OA－图书列表';
            $this->breadcrumbs = array('图书借阅'=>'/oa/books', '图书列表');
            //读取所有的图书分类，并且保证分类下有图书的存在
            $categorys = BookCategory::model()->findAllBySql("SELECT book_category.* FROM book_category join books on (book_category.category_id=books.category_id) group by book_category.category_id;");
            $books = Books::model()->findAll('status != :status',array(':status'=>'loss'));
            $this->render('books',array('books'=>$books, 'categorys'=>$categorys, 'user_id'=>empty($this->user->user_id)?'':$this->user->user_id ) );
        }
    }

    /**
     *添加图书
     **/
    public function actionAddbook()
    {
        if(!empty($this->user))
        {
            //获取最大的图书编号，然后加1
            $this->breadcrumbs = array('图书借阅'=>'/oa/books', '添加图书');
            //自动生成一个比原来最大序号大1的序号
            $max_book = Books::model()->find(array('select'=>'max(serial_number) as serial_number'));
            $serial = empty($max_book->serial_number) ? '000' : $max_book->serial_number;
            $categorys = BookCategory::model()->findAll();
            $this->render('addbook', array('serial'=>Books::plusSerial($max_book->serial_number), 'categorys'=>$categorys));
        }
    }

    /**
     *已借图书列表    
     **/
    public function actionBorrows()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = 'OA－已借图书';
            $this->breadcrumbs = array('图书借阅'=>'/oa/books', '已借图书');
            ///归还图书
            $borrows = Borrow::model()->findAll(array('condition'=>'user_id=:user_id', 'params'=>array(':user_id'=>Yii::app()->session['user_id'], ),'order'=>'return_time asc'));
            $this->render('borrows',array('borrows'=>$borrows, 'user_id'=>Yii::app()->session['user_id'], ));  
        }
    }

    /**
     *消息列表
     *分页
     *@param string $status ENUM('wait','read')
     */
    public function actionMsgs($status='',$page=1)
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－消息列表";
            $this->breadcrumbs = array('消息列表');
            $user_id = Yii::app()->session['user_id'];
            $types = array('leave'=>'请假', 'seal'=>'印鉴申请','out'=>'出差', 'recruit'=>'招聘', 'qualify'=>'转正',
             'quit'=>'离职','suggest'=>'反馈','overtime'=>'加班','goods_apply'=>'请购', 'editor'=>"文档", 'vote'=>'投票' ); 
            
            //读取未读消息有几条
            if($status=='wait')
            {
                $count = empty($this->user->msgCount)?0:$this->user->msgCount;
                $count_wait = empty($this->user->msgCount)?0:$this->user->msgCount;
            }
            else //读取已读消息有几条
            {
                $count = empty($this->user->readCount)?0:$this->user->readCount;
                $count_wait = empty($this->user->msgCount)?0:$this->user->msgCount;
            }
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            //如果没有状态就读取全部的消息
            if(empty($status))
            {
                $msgs = Notice::model()->findAll(array('condition'=>"user_id=:user_id ", 'params'=>array(':user_id'=>$user_id,), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            }
            else
            {
                $msgs = Notice::model()->findAll(array('condition'=>"user_id=:user_id and status=:status", 'params'=>array(':user_id'=>$user_id, ':status'=>$status), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            }
            $this->render('msgs',array('page'=>$page,'msgs'=>$msgs, 'count'=>$count,'count_wait'=>$count_wait ,'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize), 'status'=>$status, 'types'=>$types));
        }
        
    }
     
    /**
     * 消息
     * @param string $leave 请假单ID
     * @param string $notice 消息ID
     * @param string $type   从那个页面跳转过来的
     */
    public function actionMsg($leave='', $notice='', $type='msgDetail')
    {
        if(!empty($this->user))
        {
            try
            {
                $this->pageTitle="OA－请假申请详情";
                
                if($type == 'msgDetail')
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假申请详情');
                }
                else if($type == 'leaveSummary' || $type == 'leaveSummaryFailed' || $type == 'leaveSummaryWait')
                {
                    $this->breadcrumbs = array('请假'=>'processLeaveRecord','请假记录'=>"/oa/{$type}",'请假申请详情');
                }
                elseif($type == 'processLeaveRecord')
                {
                    $this->breadcrumbs = array('请假'=>'processLeaveRecord','审批记录'=>"/oa/{$type}",'请假申请详情');
                }
                else
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假申请详情');
                }
                //如果找到了有消息就直接更改成已读
                if(!empty($notice))
                {
                    $notice = Notice::model()->findByPk($notice);
                    Notice::updateNotice($notice, array('status' => 'read'));
                }

                if(empty($leave) || !$leave = Leave::model()->findByPk($leave))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                //用户的所有日志
                $logs = $leave->allLogs; 
                $procedure = Leave::procedure($leave);
                // echo CJSON::encode($procedure);
                $this->render('msg', array('notice'=>$notice, 'leave'=>$leave,'logs'=>$logs,'procedure'=>$procedure ));
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
       
    }

    /**
     *批请假单
     *@param string $id //请假消息
     */
    public function actionProcessLeave($id)
    {
        if(!empty($this->user))
        {
            try{
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假详情');
                $this->pageTitle="OA－请假批准";
                //请假单信息
                if( $leave_model = Leave::model()->findByPK($id) ) {
                    $logs = LeaveLog::model()->findAll('leave_id=:leave_id',array(':leave_id'=>$id));
                    //只有next为自己才可以进行处理
                    $tag =false;
                    if($leave_model->next == Yii::app()->session['user_id'])
                    {
                        $tag = true;
                    }
                    $procedure = Leave::procedure($leave_model);
                    $this->render('processLeave', array('tag'=>$tag, 'leave'=>$leave_model,'logs'=>$logs,'procedure'=>$procedure ));
                }
                else
                    throw new CHttpException(404, '找不到此页面');
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            } 
        }
        
    }

    /**
     * @ignore
     *物资申请表审批
     */
    public function actionProcessGoodsApply($id, $notice)
    {
        if(!empty($this->user))
        {
            try
            {
                $notice = Notice::model()->findByPk($notice);
                Notice::updateNotice($notice, array('status' => 'read'));
                $admin_department_id = Department::adminDepartment()->department_id;
                $apply = GoodsApply::model()->findByPk($id);
                $this->render('goods_apply_check', array('notice'=>$notice, 'apply'=>$apply , 'admin_department_id'=>$admin_department_id));
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
        
    }

   /**
    * @ignore
    *查找能转正的人,记录事务
    */
    public function actionConfirmNotice()
    {  
        if(!empty($this->user))
        {
            $transaction=Yii::app()->db->beginTransaction();  
            try 
            {     
                $run=1; 
                $cn_name=Yii::app()->session['cn_name'];  
                $user_id=Yii::app()->session['user_id'];
                $entry_day=Yii::app()->session['entry_day'];
                $notices=Notice::model()->findAll('content=:content',array(':content' => '您好，请到前台领取转正资料并于7天内做好相关的资料准备,以免耽误转正时间'));
                foreach($notices as $notice)
                {
                 
                    if($user_id == $notice->user_id)
                    {
                       $run=2;   
                       break;
                    }   
                }

                if($run==1)//若没有收到过转正通知
                {

                    if(Yii::app()->session['job_status'] == 'probation_employee') 
                    {
                         if(date('Y-m-d')== date('Y-m-d',strtotime('+1month -7days',strtotime($entry_day))))
                         {
                              $hr=Users::getCcommissioner()->user_id;  
                              Notice::addNotice(array('user_id'=>$hr,'content'=>"您好，请及时通知{$cn_name}同事领取转正资料！！",'url'=>"/oa/confirm",'status'=>'wait', 'type'=>'leave' , 'create_time'=>date('Y-m-d H:i:s')));  //通知
                              Notice::addNotice(array('user_id'=>$user_id,'content'=>"您好，请到前台领取转正资料并于7天内做好相关的资料准备,以免耽误转正时间",'url'=>"/oa/confirm", 'status'=>'wait', 'type'=>'leave' , 'create_time'=>date('Y-m-d H:i:s')));
                         }
                    }
                }
              $transaction->commit(); 
            }      
            catch (Exception $e) 
            {
                $transaction->rollback();
                  throw new CHttpException(404, '测试找不到此页面');
            }    
        }
                  
    }

    /**
     *转正通知
     *@param string $notice 消息ID
     */
     public function actionConfirm($notice)  //转正通知详情
    {
        if(!empty($this->user))
        {
            try         
            {
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs/','通知');
                $notice = Notice::model()->findByPk($notice);
                Notice::updateNotice($notice, array('status' => 'read'));
                $this->render('ConfirmNotice', array('Notice'=>$notice));//把数据库notice的实例传到ConfirmNotice页面中
            }
            catch(Exception $e)      //报错
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
       
    }
    
    /**
     *组织架构
     */
    public function actionStructure()
    {
        if(!empty($this->user))
        {
            $this->breadcrumbs = array('公司'=>'/oa/structure', '公司架构'=>'oa/structure');
            $this->pageTitle = 'OA－公司架构';
            try
            {
                //部门的数组
                $department_result = array();
                $departments = Department::model()->findAll(array("order" => "parent_id asc"));
                $users_arr = Users::model()->findAll( array('condition'=>'status=:status', 'params'=>array(':status'=>'work' ) , 'order' => 'department_id asc') );
                //部门负责人的数组
                foreach($departments as $row)
                {   
                    //读取部门下的所有人
                    $department_result[$row->department_id] = array('id'=>"0{$row->department_id}", 'name'=> $row->name, 'pId'=> "0{$row->parent_id}" , 'status' => $row->department_status, 'type'=>'department','admin'=>$row->admin, 'admin_name'=>empty($row->leader->cn_name)?'':$row->leader->cn_name);
                    foreach($users_arr as $_user)
                    {
                        if($row->department_id == $_user->department_id)
                        {
                            $department_result[$row->department_id]['count'] = empty($department_result[$row->department_id]['count'])? 1: ($department_result[$row->department_id]['count']+1);
                        }
                    }
                   
                }
                //用户的数组
                $users = array();
                $coditions = array();
                $total = empty($users_arr)? 0 : count($users_arr);
                //读出所有人的名字， 方便搜索 
                foreach($users_arr as $user)
                {
                    $coditions[] = !empty($user->en_name) ? ($user->en_name.'-'.$user->cn_name) : $user->cn_name;
                    $users[$user->user_id] = array('id'=>$user->user_id, 'pId'=>"0{$user->department_id}",  'sex'=>$user->gender , 'type'=>'employee', 'job_status'=>$user->job_status);
                    $users[$user->user_id]['name'] = empty($user->en_name) ? "{$user->cn_name}" : "{$user->en_name}-{$user->cn_name}";
                }
                $this->render('structure' , array('total'=>$total, 'self_id'=>Yii::app()->session['user_id'], 'departments'=>$department_result , 'coditions'=>$coditions,  'users'=>$users));
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
        
    }

    /**
    *修改图书信息
    **/
    public function actionEditBook()
    {    
        if(!empty($this->user))
        {
            $this->pageTitle = 'OA－图书管理';
            $this->breadcrumbs = array('图书'=>'/oa/editbook', '图书管理'=>'/oa/editbook');
            //读取所有分类
            $categorys = BookCategory::model()->findAll();
            //生成最大的序号
            $max_book = Books::model()->find(array('select'=>'max(serial_number) as serial_number'));
            $serial = empty($max_book->serial_number) ? '000' : $max_book->serial_number;
            $books = Books::model()->findAll('status != :status',array(':status'=>'loss'));
            $this->render('editbook',array('books'=>$books, 'serial'=>Books::plusSerial($max_book->serial_number), 'categorys'=>$categorys, 'user_id'=>$this->user->user_id ));
        }
        
    }

    /**
    *查看已经丢失的图书
    **/
    public function actionDeleteBook()
    {    
        if(!empty($this->user))
        {
            $this->pageTitle = 'OA－删除图书';
            $this->breadcrumbs = array('图书借阅'=>'/oa/books', '删除图书');
            $categorys = BookCategory::model()->findAll();
            $books = Books::model()->findAll('status != :status',array(':status'=>'loss'));
            $this->render('deletebook',array('books'=>$books, 'categorys'=>$categorys, 'user_id'=>$this->user->user_id ));
        }
        
    }

    /**
    *借阅记录-已借出
    *@param varchar status
    *已分页 
    **/
    public function actionBorrowRecord()
    {   
        if(!empty($this->user))
        {
            $this->breadcrumbs = array('图书'=>'/oa/editbook', '借阅记录'=>'/oa/BorrowRecord');
            $count = Borrow::model()->count('return_time=:return_time',array(':return_time'=>'0000-00-00 00:00:00'));
            $page = new CPagination($count);
            $page->pageSize = 20;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $borrowRecord_borrowed = Borrow::model()->findAll(array('select'=>'book_id, user_id, borrow_time, default_returntime ' , 'condition'=>'return_time=:return_time', 'params'=>array(':return_time'=>'0000-00-00 00:00:00'), 'limit'=>$limit, 'offset'=>$offset, 'order'=>'default_returntime ASC'));      
            $this->render('borrowRecord_borrowed',array('borrowRecord_borrowed'=>$borrowRecord_borrowed, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize)));
        }
        
    }

    /**
    *借阅记录-在库
    **/
    public function actionBorrowRecord_instore()
    {   
        if(!empty($this->user))
        {
            $this->breadcrumbs = array('图书'=>'/oa/editbook', '借阅记录'=>'/oa/borrowRecord_instore');
            $count = Books::model()->count('status=:status',array(':status'=>'wait' ));
            $page = new CPagination($count);
            $page->pageSize = 20;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $borrowRecord_instore = Books::model()->findAll(array('select'=>'serial_number,name,category_id','condition'=>'status=:status', 'params'=>array(':status'=>'wait'), 'limit'=>$limit, 'offset'=>$offset));
            $this->render('borrowRecord_instore', array('borrowRecord_instore'=>$borrowRecord_instore, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize)));
        }
        
    }

    /**
    *借阅记录-已还
    **/
    public function actionBorrowRecord_returned()
    {
        if(!empty($this->user))
        {
            $this->breadcrumbs = array('图书'=>'/oa/editbook', '借阅记录'=>'/oa/borrowRecord_returned');
            $count = Borrow::model()->count('return_time!=:return_time', array(':return_time'=>'0000-00-00 00:00:00'));
            $page = new CPagination($count);
            $page->pageSize = 20;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $borrowRecord_returned = Borrow::model()->findAll(array('select'=>'book_id, user_id, borrow_time, default_returntime,return_time ' , 'condition'=>'return_time!=:return_time', 'params'=>array(':return_time'=>'0000-00-00 00:00:00'), 'limit'=>$limit, 'offset'=>$offset, 'order'=>'return_time DESC'));
            $this->render('borrowRecord_returned', array('borrowRecord_returned'=>$borrowRecord_returned, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize)));
        }
        
    }

    /**
     * @ignore
    *图书申购
    **/
    public function actionBookApply()
    {
        if(!empty($this->user))
        {
            $this->breadcrumbs = array('图书借阅'=>'/oa/books', '图书申购');
            $this->render('bookApply');
        }
        
    }

    /**
     * @ignore
    *出差
    */
    public function actionBusinessTrip()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－出差申请";
            $this->breadcrumbs = array('出差'=>'/oa/businessTrip','出差申请');
            if(!$user = $this->user)
            {
                throw new CHttpException(404, '找不到此页面');
            }
            $users = Users::model()->findAll("user_id !=:user_id", array(':user_id'=>$user->user_id));
            $this->render('businessTrip' , array('user'=>$user , 'users'=>$users,));
        }
        
    }
   /**
    *请假报表
    *
    */
    public function actionLeaveForm($month='')//wk
    {
        if(!empty($this->user))
        {
            $month = !preg_match('/^\d{4}-\d{2}$/', $month)? date('Y-m') : $month;
            $start = date('Y-m-01',strtotime($month));
            $this->pageTitle = "OA－请假报表";
            $this->breadcrumbs = array('请假'=>'/oa/processLeaveRecord','报表'=>'/oa/leaveForm');

            $leavemonthreport =LeaveMonthReport::model()->findAllBySql("SELECT leave_month_report.* FROM leave_month_report  join users on (leave_month_report.user_id = users.user_id) where month='{$start}' order by department_id asc;");
            $tag = false;
            if(!empty($this->user) && $this->user->department_id == Department::adminDepartment()->department_id)
            {
                $tag = true;
            }
            $this->render('leaveForm',array('leavemonthreport'=>$leavemonthreport,'department'=>$tag, 'month'=>$month));
        }
        
    }

    /**
     *导出报表
     *@param string $month YYYY-DD
     */
    public function actionLeaveReport($month)
    {
        if(!empty($this->user))
        {
           $pretty_modtime = gmdate('D,d M Y H:i:s' , time()+ (8*3600) );
           @header("Last-Modified:{$pretty_modtime}");
           @header('Cache-Control:no-cache,must-revalidate');  
           @header("Expires: {$pretty_modtime}");
           @header('Pragma:no-cache');
           $month = !preg_match('/^\d{4}-\d{2}$/', $month)? date('Y-m') : $month;
           $file = Yii::getPathOfAlias('webroot.reports').DIRECTORY_SEPARATOR."leaveReport-{$month}.xlsx";
           $url=Yii::app()->request->hostInfo;
           if(file_exists($file) && is_file($file) && is_readable($file))
           {
               header("location:{$url}/reports/leaveReport-{$month}.xlsx");
               exit;
           }
           $start = date('Y-m-01',strtotime($month));
           $leavemonthreport =LeaveMonthReport::model()->findAllBySql("SELECT leave_month_report.* FROM leave_month_report  join users on (leave_month_report.user_id = users.user_id) where month='{$start}' order by department_id asc;");
           //算出部门和姓名
            $result = array();

            $users = Users::model()->findAll("status =:status order by department_id", array(':status'=>'work'));
            // $users = Users::model()->findAll();
            foreach($users as $user){
                try {
                    @$result[] = array('name'=>$user->department->name, 'cn_name'=>$user->cn_name);
                }
                catch(Exception $e) {
                    $result[] = array('name'=>"NONE", 'cn_name'=>$user->cn_name);
                }
            }
            //调用execel
           spl_autoload_unregister(array('YiiBase','autoload'));
           $objPHPExcel =  Yii::createComponent('application.extensions.excel.PHPExcel');
            // Add some data
           $objPHPExcel->setActiveSheetIndex(0);
           $sheet = $objPHPExcel->getActiveSheet();
           $titles = array('月份','部门','姓名','应出勤','事假','病假','婚假','丧假','年假','陪产假','补休','其他假','备注');
           $numCol=0;
           foreach($titles as $title)
           {
               $sheet->setCellValue("{$this->letters[$numCol]}1", "{$title}");
               $numCol++;
           }
            $count = 0;
            for($i = date('Y-m-01', strtotime($month)); $i <= date('Y-m-t', strtotime($month)); $i = date('Y-m-d', strtotime("+1days",strtotime($i)))){
                if(date('w', strtotime($i)) >= 1 && date('w', strtotime($i)) <= 5){
                    $count ++;
                }
            }

            foreach($users as $key => $row){
                $find_tag = false;
                foreach($leavemonthreport as $lrow){
                    if($row['user_id'] == $lrow['user_id']){
                        $numCol=0;
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$month}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$result[$key]['name']}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$result[$key]['cn_name']}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$count}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->casual}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->sick}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->marriage}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->funeral}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->annual}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->maternity}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->compensatory}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->others}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$lrow->content}");
                       $find_tag = true;
                    }
                }
                // 没有请过假的
                if(!$find_tag){
                    $numCol=0;
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$month}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$result[$key]['name']}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$result[$key]['cn_name']}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "{$count}");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "0");
                       $sheet->setCellValue("{$this->letters[$numCol++]}".($key+2), "");
                }
            }
           $sheet->setTitle('请假报表');
           //$objPHPExcel->setActiveSheetIndex(0);
           $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);    
           $objWriter->save($file);
            //恢复Yii自动加载功能          
           spl_autoload_register(array('YiiBase','autoload')); 
           header("location:{$url}/reports/leaveReport-{$month}.xlsx");
        }
       
    }

    /**
     *出差消息详情
     *@param string $out   出差的ID
     *@param string $notice 消息ID
     *@param string $type   进入页面的标记 根据不同标记 跳回不同页面
     */
    public function actionOutMsg($out, $notice='', $type='')
    {
        if(!empty($this->user))
        {
            try
           {
                $this->pageTitle="OA－出差申请详情";
                if($type == 'businessTrip_summary_wait' || $type == 'businessTrip_summary' || $type == 'businessTrip_summary_failed' || $type == 'businessTripSummarySearch')
                {
                    $this->breadcrumbs = array('出差'=>'/oa/processBusinessTripRecord','出差记录'=>"/oa/{$type}",'出差申请详情');
                }
                else if($type == 'processBusinessTripRecord')
                {
                    $this->breadcrumbs = array('出差'=>'/oa/processBusinessTripRecord','审批记录'=>'/oa/processBusinessTripRecord','出差申请详情');
                }
                else
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','出差申请详情');
                }
                //通知
                if(!empty($notice))
                {
                    $notice = Notice::model()->findByPk($notice);
                    Notice::updateNotice($notice, array('status' => 'read'));
                }
                //读取出出差记录的所有日志     
                $logs = OutLog::model()->findAll("out_id = :out_id order by create_time asc",array(':out_id'=>$out));
                //读取出差记录
                if($out_info = Out::model()->findByPk($out)) {
                    //出差记录进度条
                    $procedure = Out::procedure($out_info);
                    // echo CJSON::encode($procedure);
                    $this->render('msg_businessTrip', array('notice'=>$notice, 'out'=>$out_info, 'logs'=>$logs, 'procedure'=>$procedure, ));
				}
				else
                    echo "此出差记录已经从数据库删除";
            }
           catch(Exception $e)
           {
                throw new CHttpException(404, '找不到此页面');
           }
        }
       
    }

    /**
    *出差记录
    *通过
    *@param string $status ENUM('success','reject','wait')
    **/
    public function actionBusinessTrip_Summary( $status='success')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－出差记录";
            $this->breadcrumbs = array('出差'=>'/oa/businessTrip','出差记录');
            $users_names = Users::model()->findAll();
            $departments = Department::model()->findAll();
            //获取该状态所有记录
            $count = Out::model()->count('status=:status',array(':status'=>$status));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            //查找当前页所需要的记录
            $msgs = Out::model()->findAll( array('condition'=>"status=:status", 'params'=>array(':status'=>$status), 'order'=>'create_time desc','limit'=>$limit, 'offset'=>$offset));
            $this->render('businessTrip_summary',array('msgs'=>$msgs, 'departments'=>$departments, 'page'=>$page,'users_names'=>$users_names, 'count'=>$count,'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize), 'status'=>$status));
        }
        
    }
    /**
     *出差记录
     *未审批
     */
    public function actionBusinessTrip_summary_wait()
    {
        if(!empty($this->user))
        {
            $status = 'wait';
            $this->pageTitle = "OA－出差记录";
            $this->breadcrumbs = array('出差'=>'/oa/businessTrip','出差记录'=>'/oa/businessTrip_summary_wait');
            $users_names = Users::model()->findAll();
            $departments = Department::model()->findAll();
            $count = Out::model()->count('status=:status',array(':status'=>$status));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $msgs = Out::model()->findAll( array('condition'=>"status=:status", 'params'=>array(':status'=>$status), 'order'=>'create_time desc','limit'=>$limit, 'offset'=>$offset));
            $this->render('businessTrip_summary_wait',array('msgs'=>$msgs, 'departments'=>$departments, 'page'=>$page,'users_names'=>$users_names, 'count'=>$count,'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize), 'status'=>$status));
        }
        
    }
     /**
    *出差记录
    *未通过
    **/
    public function actionBusinessTrip_Summary_failed( $status='reject')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－出差记录";
            $this->breadcrumbs = array('出差'=>'/oa/businessTrip','出差记录');
            $users_names = Users::model()->findAll();
            $departments = Department::model()->findAll();
            $count = Out::model()->count( "status=:status", array(':status'=>$status));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $msgs = Out::model()->findAll( array('condition'=>"status=:status", 'params'=>array(':status'=>$status), 'order'=>'create_time desc','limit'=>$limit, 'offset'=>$offset));
            $this->render('businessTrip_summary_failed',array('page'=>$page,'users_names'=>$users_names, 'departments'=>$departments, 'msgs'=>$msgs, 'count'=>$count,'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), 'status'=>$status));
        }
        
    }

    /**
     * @ignore
    *费用申请
    **/
    public function actionCost_Apply()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－费用申请";
            $this->breadcrumbs = array('费用'=>'/oa/cost_apply','费用申请');
            if(!$user = $this->user)
            {
                throw new CHttpException(404, '找不到此页面');
            }
            $users = Users::model()->findAll("user_id !=:user_id", array(':user_id'=>$user->user_id));
            $this->render('cost_apply', array('user'=>$user , 'users'=>$users,));
        }
        
    }

    /**
     * @ignore
    *费用报表
    **/
    public function actionCost_Form()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－费用报表";
            $this->breadcrumbs = array('费用'=>'/oa/cost_apply','费用报表');
            $this->render('cost_form');
        }
        
    }

    /**
     * @ignore
    *报销
    **/
    public function actionReimburse()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－报销";
            $this->breadcrumbs = array('资产'=>'/oa/property','报销');
            $this->render('reimburse');
        }
        
    }
     /**
      *管理员才可以查看的请假汇总
      *通过
      **/
     public function actionLeaveSummary()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－请假记录";
            $this->breadcrumbs = array('请假'=>'/oa/ProcessLeaveRecord','请假记录'=>'/oa/leaveSummaryWait');
            $users_names = Users::model()->findAll();
            $departments = Department::model()->findAll();
            $count = Leave::model()->count('status=:status', array(':status'=>'success'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveSummarys = Leave::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'success'), 'order'=>'create_time desc','limit'=>$limit, 'offset'=>$offset));
            $this->render('leaveSummary',array('LeaveSummarys'=>$LeaveSummarys,'page'=>$page,'users_names'=>$users_names, 'count'=>$count,'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize),'departments'=>$departments));
        }
     }
     /**
      *管理员才可以查看的请假汇总待审批
      *未通过
      **/
     public function actionLeaveSummaryWait()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－请假记录";
            $this->breadcrumbs = array('请假'=>'/oa/ProcessLeaveRecord','请假记录'=>'/oa/leaveSummaryWait');
            $users_names = Users::model()->findAll();
            $departments = Department::model()->findAll();
            $count = Leave::model()->count('status=:status', array(':status'=>'wait'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveSummarys = Leave::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'wait'), 'order'=>'create_time desc','limit'=>$limit, 'offset'=>$offset));
            $this->render('leaveSummaryWait',array('LeaveSummarys'=>$LeaveSummarys,'page'=>$page,'users_names'=>$users_names, 'count'=>$count,'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize),'departments'=>$departments));
        }
        
     }
      /**
      *管理员才可以查看的请假汇总
      *未通过
      **/
     public function actionLeaveSummaryFailed()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－请假记录";
            $this->breadcrumbs = array('请假'=>'/oa/ProcessLeaveRecord','请假记录'=>'/oa/leaveSummaryWait');
            $users_names = Users::model()->findAll();
            $departments = Department::model()->findAll();
            $count = Leave::model()->count('status=:status', array(':status'=>'reject'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveSummarys = Leave::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'reject'), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('leaveSummaryFailed', array('LeaveSummarys'=>$LeaveSummarys,'page'=>$page,'users_names'=>$users_names,  'count'=>$count,'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize),'departments'=>$departments));
        }
     }

     /**
      *邮件通知
      *@param object $departments  全部部门
      *@param object $users  全部员工
      *@param array $result
      **/ 
     public function actionMail()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－群发邮件";
             $this->breadcrumbs = array('邮件通知'=>'/oa/mail','群发邮件'=>'/oa/mail');
             $result = array();
             $departments = Department::model()->findAll(array('select'=>'department_id,name,department_status'));
             
             if($users = Users::model()->findAll(array('select'=>'cn_name,email,user_id,department_id','condition'=>'status=:status', 'params'=>array(':status'=>'work'))))
             {
                 foreach($users as $user)
                 {
                    $result[] = "\"{$user->cn_name}\" [{$user->email}];";                
                 }
                 $result[] = "\"all\" [all@shanyougame.com];";
             }

             $this->render('mail', array('result'=>$result, 'users'=>$users, 'departments'=>$departments) );
        }
         
     }

     /**
      *显示office文件在先预览
      *@param string $path 文件目录
      *@param string $file 文件名称
      */
     public function actionViewer($path,$file)
     {
        $pretty_modtime = gmdate('D,d M Y H:i:s' , time()+ (8*3600) );
        @header("Last-Modified:{$pretty_modtime}");
        @header('Cache-Control:no-cache,must-revalidate');  
        @header("Expires: {$pretty_modtime}");
        @header('Pragma:no-cache');
        $src_path = "{$path}{$file}";
        if(!file_exists($src_path))
        {
            echo 'file not found';
            exit;
        }
        $ext = pathinfo($src_path,PATHINFO_EXTENSION);
        if($ext == 'pdf')
        {   
            if (strstr($file, 'apply') !== false)
                $this->actionPreview("/attachment/resumes/{$file}");
            else
                $this->actionPreview("/attachment/{$file}");
        }
        else if(in_array($ext , array('jpg', 'gif' , 'bmp', 'png')))
        {
            echo "<img src='/attachment/{$file}' />";
        }
        else if(in_array($ext , array('doc', 'docx' , 'xls', 'xlsx', 'ppt', 'pptx')))
        {
            $dst_dir = Yii::getPathOfAlias('webroot.pdf').DIRECTORY_SEPARATOR;
            $dst_name = basename($src_path, ".{$ext}");
            $src = $src_path;
            $dst = $dst_dir.$dst_name.".pdf";
            if(file_exists($dst))
            {
                $src_mtime= filemtime($src_path);
                $dst_mtime= filemtime($dst);
                if($src_mtime > $dst_mtime)
                {
                    $command="java -jar /usr/local/jodc/jodconverter-2.2.2/lib/jodconverter-cli-2.2.2.jar {$src} {$dst}";
                    @exec($command);
                }
            }
            else
            {
                    $command="java -jar /usr/local/jodc/jodconverter-2.2.2/lib/jodconverter-cli-2.2.2.jar {$src} {$dst}";
                    @exec($command);
            }

            if(!file_exists($dst))
            {
                echo 'system error';
                exit;
            }
            $this->actionPreview("/pdf/{$dst_name}.pdf");
        }
        else
        {
            header('Location: '.Yii::app()->request->hostInfo.'/attachment/'.$file);  
        }
     }

     /**
      *用PDF显示office文档
      *@param stirng $path 文件绝对目录
      */
     private function actionPreview($path='')
     {
         $this->layout = 'pdf';
         $this->render('preview', array('path'=>$path));
     }
      
      /**
      *请假记录--搜索--按姓名，部门，年月查询
      *@param yyyy-mm $date string $name , string $department
      *@return object
      **/
     public function actionleaveSummarySearch($name='', $department='',$date='' )
     {  
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－请假记录";
            $this->breadcrumbs = array('请假'=>'/oa/ProcessLeaveRecord','请假记录'=>'/oa/leaveSummaryWait');
            $users_names = Users::model()->findAll();//输入user提示
            $departments = Department::model()->findAll();
            $count = $limit = $offset = $i = 0;
            $page = new CPagination($count);
            $leave_summarys = array();
            $department_condition= '';
            $user_condition = '';
            $error_msg = 1;

            if(!empty($department) )
            {
                foreach($departments as $department1 )//查找部门
                {
                    if($department1->name == $department)
                    {
                        $department_id = $department1->department_id;
                        $department_url = "/department/$department";
                        $department_condition = "1";
                    }
                }
                if(empty($department_id))
                {
                    $error_msg = '部门不存在';
                    $this->render('leaveSummarySearch',array( 'leave_summarys'=>$leave_summarys,'error_msg'=>$error_msg, 'users_names'=>$users_names, 'departments'=>$departments, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ));
                
                }
            }

            if(!empty($name))//查找人员，也许有多个同名的人
            {
                foreach ($users_names as $user) 
                {
                    if($user->cn_name == $name)
                    {
                        $user_id = $user->user_id;
                        $user_ids["$user_id"] = $user_id;
                        $name_url = "/name/$name";
                        $user_condition = "1";
                    }
                }
                if(empty($user_ids))
                {
                    $error_msg = '用户不存在';
                    $this->render('leaveSummarySearch',array( 'leave_summarys'=>$leave_summarys,'error_msg'=>$error_msg,'users_names'=>$users_names, 'departments'=>$departments, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ));
                
                }
            }

            if(!empty($date))//判断时间是否为空
            {  
                if(preg_match('/^\d{4}\-\d{2}$/', $date))
                {
                $start_date = date('Y-m-01 00:00:00', strtotime($date));
                $end_date = date('Y-m-01 00:00:00', strtotime('+1month', strtotime($date) ) );
                $date_url = "/date/$date";
                }
                else
                {
                    $error_msg = '时间错误，正确格式：yyyy-mm';
                    $this->render('leaveSummarySearch',array( 'leave_summarys'=>$leave_summarys,'error_msg'=>$error_msg,'users_names'=>$users_names, 'departments'=>$departments, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ));
                
                }
            }
            
            if(!empty($department_condition)) //确定查找用户的条件!empty($department_condition)
            {
                if(!empty($user_condition))
                {
                    foreach ($user_ids as $user_id) 
                    {
                        $users = Users::model()->findAll( array('condition'=>"user_id=:user_id and department_id =:department_id", 'params'=>array(':user_id'=>$user_id, ':department_id'=>$department_id )));//查找满足条件的人
                    }
                    if(empty($users))
                    {
                        $error_msg = '此部门没有该用户';
                        $this->render('leaveSummarySearch',array( 'leave_summarys'=>$leave_summarys,'error_msg'=>$error_msg,'users_names'=>$users_names, 'departments'=>$departments, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ));
                    
                    }
                }
                else
                {
                    $users = Users::model()->findAll( array('condition'=>"department_id =:department_id",'params'=>array( ':department_id'=>$department_id )));//查找满足条件的人
                }
            }
            else
            {
                if(!empty($user_condition))
                {
                    foreach ($user_ids as $user_id) 
                    {
                        $users = Users::model()->findAll( array('condition'=>"user_id=:user_id", 'params'=>array(':user_id'=>$user_id)));//查找满足条件的人
                    }
                }
                else
                {
                    $users = 0;
                }
            }
            
            if($users != 0)
            {
                if(!empty($date))
                {
                    foreach($users as $user)
                    {
                        $count = Leave::model()->count(array( 'condition'=>"user_id=:user_id and ((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date )) "
                                                        , 'params'=>array(':user_id'=>$user->user_id, ':start_date'=>$start_date, ':end_date'=>$end_date, )));
                        $page = new CPagination($count);
                        $page->pageSize = 10;
                        $limit = $page->pageSize;
                        $offset = $page->currentPage * $page->pageSize ;    
                        $leave_record = Leave::model()->findAll( array( 'condition'=>"user_id=:user_id and ((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date )) "
                                                        , 'params'=>array(':user_id'=>$user->user_id, ':start_date'=>$start_date, ':end_date'=>$end_date, ), 'limit'=>$limit, 'offset'=>$offset,
                                                    'order'=>'create_time desc', ) );
                        if(!empty($leave_record))
                        {
                            $leave_summarys["$i"] = $leave_record;
                            $i++;
                        }
                    }
                }
                else
                {   
                    foreach($users as $user)
                    {
                        $count = Leave::model()->count(array( 'condition'=>"user_id=:user_id", 'params'=>array(':user_id'=>$user->user_id),));
                        $page = new CPagination($count);
                        $page->pageSize = 10;
                        $limit = $page->pageSize;
                        $offset = $page->currentPage * $page->pageSize ;
                        $leave_record = Leave::model()->findAll( array( 'condition'=>"user_id=:user_id", 'params'=>array(':user_id'=>$user->user_id,) ,'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
                        if(!empty($leave_record))
                        {
                            $leave_summarys["$i"] = $leave_record;
                            $i++;
                        }
                    }                               
                }
            }
            else
            { 
                if(!empty($date))
                {
                    $count = Leave::model()->count(array( 'condition'=>"((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date )) ", 'params'=>array(':start_date'=>$start_date, ':end_date'=>$end_date) ));
                    $page = new CPagination($count);
                    $page->pageSize = 10;
                    $limit = $page->pageSize;
                    $offset = $page->currentPage * $page->pageSize ;
                    $leave_summarys[] = Leave::model()->findAll( array( 'condition'=>"((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date )) ", 'params'=>array(':start_date'=>$start_date, ':end_date'=>$end_date),'order'=>'create_time desc',  'limit'=>$limit, 'offset'=>$offset) );
                } 
            }
            $url = (empty($department_url)?'':$department_url).(empty($date_url)?'':$date_url).(empty($name_url)?'':$name_url);
            $department_post = (empty($department)?'':$department);
            $date_post = (empty($date)?'':$date);
            $name_post = (empty($name)?'':$name);
            if($error_msg ==1)
            {
            $this->render('leaveSummarySearch', array('leave_summarys'=>$leave_summarys,'error_msg'=>$error_msg, 'url'=>$url,'department_post'=>$department_post,'date_post'=>$date_post,'name_post'=>$name_post, 'users_names'=>$users_names, 'departments'=>$departments, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
            }
        }
        
     }
     
     /**
      *出差记录--搜索--按姓名，部门，年月查询
      *@param yyyy-mm $date string $name , string $department
      *@return object
      */
     public function actionbusinessTripSummarySearch($user_id='', $department_id='',$date='' )
     {  
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－出差记录--快速搜索";
            $this->breadcrumbs = array('出差'=>'/oa/businessTrip_summary_wait','出差记录'=>'/oa/businessTripSummarySearch');
            $users_names = Users::model()->findAll("status=:status",array(':status'=>'work'));//用于前端输入user提示
            $departments = Department::model()->findAll();
            $params = array();
            $count_sql = "select count(*) from `out` join out_member on (out.out_id = out_member.out_id) where 1";
            $sql = "select `out`.* from `out` join out_member on (out.out_id = out_member.out_id) where 1";
            if(preg_match('/^[1-9]\d*$/', $user_id))
            {
                $count_sql .= " and out_member.user_id = :user_id";
                $sql .= " and out_member.user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            if(preg_match('/^[1-9]\d*$/', $department_id))
            {
                $_user_ids = array();
                foreach($users_names as $row)
                {
                    if($row->department_id == $department_id)
                    $_users_ids[] = $row->user_id;
                }
                if(!empty($_user_ids))
                {
                    $count_sql .= " and user_id in (".joins($_user_ids, ',').")";
                    $sql .= " and user_id in (".joins($_user_ids, ',').")";
                }
            }
            if(preg_match('/^\d{4}-\d{2}/', $date))
            {
                $count_sql .= " and start_time >= :start and end_time <= :end";
                $sql .= " and start_time >= :start and end_time <= :end";
                $start = date('Y-m-01 00:00:00', strtotime($date));
                $end   = date('Y-m-t 23:59:59' , strtotime($date));
                $params[':start'] = $start;
                $params[':end'] = $end;
            }
            $count = Yii::app()->db->createCommand($count_sql)->queryScalar($params);
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $sql .= " order by create_time desc limit :limit offset :offset; ";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            $out_summarys = Out::model()->findAllBySql($sql,$params);
            $this->render('businessTripSummarySearch', array('out_summarys'=>$out_summarys,
                'department_id'=>$department_id,'date'=>$date,'user_id'=>$user_id, 'users_names'=>$users_names,
             'departments'=>$departments, 'page'=>$page) );
        }
     }

    /**
    *消息详情页面
    *@param string $id 消息ID
    */
    public function actionMsgDetail($id='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－消息列表";
            $this->breadcrumbs = array('消息列表'=>'/oa/msgs','消息详情');
            if(!$msg = Notice::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            Notice::updateNotice($msg, array('status' => 'read'));
            $types = array('leave'=>'请假', 'out'=>'出差',  'seal'=>'印鉴申请','goods_apply'=>'请购', 'recruit'=>'招聘', 'qualify'=>'转正', 'quit'=>'离职','suggest'=>'反馈','overtime'=>'加班'); 
            $this->render('msgDetail',array('msg'=>$msg, 'types'=>$types));
        }
    }

    /**
    *审批记录
    **/
    public function actionProcessLeaveRecord()
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－审批记录";
            $this->breadcrumbs = array('请假'=>'/oa/processLeaveRecord','审批记录'=>'/oa/ProcessLeaveRecord');
            $count = Leave::model()->countBySql("select count(*) from `leave` join leave_log on(leave.leave_id=leave_log.leave_id) where leave_log.user_id = :user_id",array(':user_id'=>Yii::app()->session['user_id']));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $processLeaveRecord = Leave::model()->findAllBySql("select `leave`.* from `leave` join leave_log on(leave.leave_id=leave_log.leave_id) where leave_log.user_id = :user_id order by create_time limit :offset,:limit"
                ,array(':user_id'=>Yii::app()->session['user_id'],':limit'=>$limit, ':offset'=>$offset));
            $this->render('processLeaveRecord',array('processLeaveRecord'=>$processLeaveRecord,'page'=>$page,'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize),  ));        
        }
    }  

    /**
    *邮件列表页面
    *@param string $status '',wait','success','fail'
    */
    public function actionMailList($status='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－邮件列表";
            $this->breadcrumbs = array('邮件通知'=>'/oa/mail','邮件列表'=>'/oa/mailList');

            if(empty($status))
            {
                $count = Mail::model()->count();
                $count_wait = Mail::model()->count('status=:status',array(':status'=>'wait'));
            }
            else
            {
                $count_wait = $count = Mail::model()->count('status=:status',array(':status'=>$status));
            }
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            if(empty($status))
            {
                $mails = Mail::model()->findAll(array('order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            }
            else
            {
                $mails = Mail::model()->findAll(array('condition'=>"status=:status", 'params'=>array(':status'=>$status), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            }
            $this->render('mailList' , array('mails'=>$mails,'status'=>$status,'count_wait'=>$count_wait, 'page'=>$page));
        }
    }

    /**
     *邮件详情
     *@param string $id 邮件ID
     */
    public function actionMailDetail($id)
    {
        if(!empty($this->user))
        {
            $this->pageTitle   = "OA－邮件详情";
            $this->breadcrumbs = array('邮件通知'=>'/oa/mail', '邮件列表'=>'/oa/mailList','邮件详情');
            if(!$mail = Mail::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            $this->render('mailDetail', array('mail'=>$mail));
        }
    }

    /**
    *出差审批记录
    **/
    public function actionprocessBusinessTripRecord()
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－审批记录";
            $this->breadcrumbs = array('出差'=>'/oa/processBusinessTripRecord','审批记录'=>'/oa/processBusinessTripRecord');
            $count = Out::model()->countBySql("select count(*) from `out` join out_log on(out.out_id=out_log.out_id) where out_log.approver_id = :user_id",array(':user_id'=>Yii::app()->session['user_id']));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $processBusinessTripRecord = Out::model()->findAllBySql("select `out`.* from `out` join out_log on(out.out_id=out_log.out_id) where out_log.approver_id = :user_id order by create_time desc limit :offset,:limit",
                                                                        array(':user_id'=>Yii::app()->session['user_id'],':limit'=>$limit, ':offset'=>$offset));
            $this->render('processBusinessTripRecord',array('processBusinessTripRecord'=>$processBusinessTripRecord,'page'=>$page,'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize),  ));        
        }
        
    }

    /**
    *入职信息登记表
    **/
    public function actionentryDetails()
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－入职信息登记";
            $this->breadcrumbs = array('入职信息登记');
            $this->render('entryDetails',array('user'=>empty($this->user)?array():$this->user)); 
        }
    }

    /**
     *编制页面
     *@param string $department_id  部门ID
     */
    public function actionFormation($department_id = '')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = 'OA－人员编制';
            $this->breadcrumbs = array('公司'=>'/oa/structure','人员编制'=>'oa/formation'); 
             //部门的数组
            $department_result = array();
            $result = array();
            $departments = Department::model()->findAll(array("order" => "parent_id asc"));
            //部门负责人的数组
            foreach($departments as $row)
            {   
                $department_result[$row->department_id] = array('id'=>"0{$row->department_id}", 'name'=> $row->name, 'pId'=> "0{$row->parent_id}", 'status' => $row->department_status);
            }
            if(preg_match('/^[1-9]\d*$/', $department_id))
            {
                $users_arr = array();
                $users = array();

                if($department = Department::model()->findByPk($department_id))
                {
                    $department_sub_parent_id = array($department_id);
                    $all_department = $departments;
                    do {
                        $flag = 0;
                        foreach ($all_department as $key => $d_row) {
                            if ( in_array($d_row['parent_id'], $department_sub_parent_id) && ($d_row['department_status']=='display')) {
                                $department_sub_parent_id[] = $d_row['department_id'];
                                unset($all_department[$key]);
                                $flag = 1;
                            }
                        }
                    } while ($flag);                 //查找子部门ID

                    $_department_count = 0;
                    $_formation_count = 0;

                    foreach ($department_sub_parent_id as $row_id) {
                        if($users_r = Users::model()->findAll( array('condition'=>'department_id = :id and status=:status', 'params'=>array(':id'=>$row_id, ':status'=>'work' ))))
                        {
                            foreach($users_r as $user)
                            {
                                $users_arr[$user->title][] = $user;
                                $_department_count += 1;
                            }
                            $users = array_merge($users, $users_r);
                            // $users = $users + $users_r;
                        }
                    }
                    
                    foreach ($department_sub_parent_id as $row_id) {
                        if($formations   = Formation::model()->findAll('department_id=:id',array(':id'=>$row_id)))
                        {
                            foreach($formations as $f)
                            {
                                if(empty($f->title)) continue;
                                $_formation_count += $f->number;

                                $result[$f->title]['formation_id'] = empty($f->formation_id) ? 0 : $f->formation_id;
                                $result[$f->title]['num'] = empty($f->number) ? 0 : $f->number;
                                $result[$f->title]['department_num'] = empty($users_arr[$f->title]) ? 0 : count($users_arr[$f->title]);
                                $result[$f->title]['lack_num'] = $result[$f->title]['num'] - $result[$f->title]['department_num'];
                                $result[$f->title]['list'] = '';
                                if(!empty($users_arr[$f->title]))
                                {
                                    foreach($users_arr[$f->title] as $_row)
                                    {
                                        $result[$f->title]['list'] .= $_row['cn_name'].' ';
                                    }
                                }
                            }
                        }
                    }
                    $_lack_count = $_formation_count -  $_department_count;
                }
            }
            $total_formation_number = Formation::getTotalFormationNum(); //总编制人数
            $total_user_number = Users::model()->count('status=:status',array(':status'=>'work')); //总在职人数
            $this->render('formation' , array('total_formation_number'=>$total_formation_number, 'total_user_number'=>$total_user_number, 'result'=>$department_result,'department'=>empty($department)?array():$department, 'department_id'=>$department_id, 'department_count'=>empty($_department_count)?0:$_department_count, 'formation_count'=>empty($_formation_count)?0:$_formation_count, 'lack_count'=>empty($_lack_count)?0:$_lack_count, 'data'=>empty($result)?array():$result, 'users'=>empty($users)?array():$users));
        }
    }

    /**
     *招聘申请
     */
    public function actionRecruitApply()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－招聘申请";
            $departments = array();
            if(in_array($this->user->department_id , array(1,2)))
            {
                $departments = Department::model()->findAll();
            }
            else
            {
                $departments = Department::model()->findAll(array('condition'=>'department_id = :department_id or parent_id = :department_id','params'=>array(':department_id'=>$this->user->department_id)));
            }
            $users = Users::model()->findAll('status=:status',array(':status'=>'work'));
            $this->breadcrumbs = array('招聘'=>'/oa/recruitApply','招聘申请'=>'/oa/recruitApply');
            $this->render('recruitApply' , array('tag'=>$this->recruit_tag ,'users'=>$users, 'departments'=>$departments) );
        }
        
    }

    /**
     *我的招聘申请
     */
    public function actionRecruitApplyRecord()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的招聘申请";
            $this->breadcrumbs = array('招聘'=>'/oa/recruitApply','我的招聘申请'=>'/oa/recruitApplyRecord');
            $count = RecruitApply::model()->count('user_id = :user_id', array(':user_id'=>Yii::app()->session['user_id']));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $data = RecruitApply::model()->findAll(array('condition'=>"user_id = :user_id", 'params'=>array(':user_id'=>Yii::app()->session['user_id']), 'order'=>'create_date desc', 'limit'=>$limit, 'offset'=>$offset));
            $this->render('recruitApplyRecord', array('page'=>$page, 'data'=>$data));
        }
        
    }

    /**
     *招聘申请详情
     *@param string $id 招聘记录的ID
     */
    public function actionRecruitApplyDetail($id,$type='msgDetail')
    {
        if(preg_match('/^[1-9]\d*$/',Yii::app()->session['user_id']))
        {
            if($this->user = Users::model()->findByPk(Yii::app()->session['user_id']))
            {
                $this->pageTitle = "OA－招聘申请详情";
                // $this->breadcrumbs = array('招聘'=>'/oa/recruitApplyRecord','招聘申请详情');
                if($type == 'msgDetail')
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','招聘申请详情');
                }
                else if($type == 'recruitApply')
                {
                    $this->breadcrumbs = array('招聘'=>'/oa/recruitApplyRecord','招聘申请'=>"/oa/{$type}",'招聘申请详情');
                }
                else if($type == 'recruitApplyRecord')
                {
                    $this->breadcrumbs = array('招聘'=>'/oa/recruitApplyRecord','我的招聘申请'=>"/oa/{$type}",'招聘申请详情');
                }
                else if($type == 'recruitApplySummary')
                {
                    $this->breadcrumbs = array('招聘'=>'/oa/recruitApplyRecord','招聘申请记录'=>"/oa/{$type}",'招聘申请详情');
                }
                else if($type == 'interviewEvaluateDetail')
                {
                    $this->breadcrumbs = array('面试'=>'/oa/interviewEvaluateRecord','面试评估记录'=>"/oa/interviewEvaluateRecord",'面试评估表','招聘申请详情');
                }
                else
                {
                    $this->breadcrumbs = array('error');
                }

                if(!$apply = RecruitApply::model()->findByPk($id))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                //人事部和CEO和招聘发起人，和该招聘的所有面试官可以进入
                $permissions = array(Users::getHr()->user_id, Users::getAdminId()->user_id, Users::getCeo()->user_id, $apply->user_id);
                //该申请单所有审批者有权限查看
                $permissions = array_merge($permissions, CJSON::decode($apply->procedure_list, true));
                if($apply->resumes)
                {
                    foreach($apply->resumes as $_resume)
                    {
                        $permissions[] = $_resume->interviewer;
                    }
                }
                if(!(in_array(Yii::app()->session['user_id'] , $permissions) || Roles::Check_role('hr', $this->user) ))
                {
                    header("Content-type: text/html; charset=utf-8");
                    echo "你没有权限查看此页面，请点击 <a href='".Yii::app()->request->urlReferrer."'>返回上一页</a>";
                    Yii::app()->end();
                }
                $tag = false;
                if(!empty(Yii::app()->session['user_id']) && Yii::app()->session['user_id'] == Users::getHr()->user_id)
                {
                    $tag = true;
                }
                $users = Users::model()->findAll('status=:status', array(':status'=>'work'));
                $resumes = Resume::model()->findAll('apply_id=:id', array(':id'=>$id));
                // $interviewer_apply_info = Users::model()->findByPk($resume->interviewer);
                $apply_add_info = array();
                foreach ($resumes as $key => $value) {
                    $apply_add_info[$key] = Users::model()->findByPk($value->interviewer);
                }

                $view_params = array(
                    'users'=>$users, 'procedure'=>$apply->procedure, 'apply'=>$apply,
                    'resumes'=>$resumes,  'user'=>$this->user, 'tag'=>$tag,
                    'apply_add_info' => $apply_add_info,
                );

                $this->render('recruitApplyDetail', $view_params );
            }
            else
            {
               header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
            }
        }
        else
        {
           header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
        }
    }

    /**
     *招聘申请记录
     *@param string $status
     *@param string $date
     *@param string $department
     */
    public function actionRecruitApplySummary($status = 'wait', $date='', $department='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－招聘申请记录";
            $this->breadcrumbs = array('招聘'=>'/oa/recruitApply','招聘申请记录'=>'/oa/recruitApplySummary');
            $condition = "1";
            //状态
            if($status != 'all')
            {
                $condition .= " and status = :status ";
                $params[':status']   = $status;
            }
            //日期
            if(preg_match('/^\d{4}-\d{2}$/', $date))
            {
                $condition .= " and create_date >= :start and create_date <= :end";
                $params[':start']   = date('Y-m-01 00:00:00',strtotime($date));
                $params[':end']   = date('Y-m-t 23:59:59',strtotime($date));
            }
            //部门搜索
            if(preg_match('/^\d+$/', $department) && $_department = Department::model()->findByPk($department))
            {
                $condition .= " and department=:department";
                $params[':department']   = $_department->name;
            }
            $count = RecruitApply::model()->count(array('condition'=>$condition, 'params'=>$params));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $data = RecruitApply::model()->findAll(array('condition'=>$condition, 'params'=>$params, 'order'=>'create_date desc', 'limit'=>$limit, 'offset'=>$offset));
            //所有部门
            $departments = Department::model()->findAll();
            $this->render('recruitApplySummary', array('page'=>$page, 'data'=>$data , 'status'=>$status , 'departments'=> $departments ,'date'=>$date, 'department'=>$department) );
        }
        
    }

	/**
     *查看简历
     *@param string $id 就是简历的ID
     */
    public function actionViewResume($id)
    {

        if(!$resume = Resume::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        $path = Yii::getPathOfAlias('webroot.attachment.resumes').DIRECTORY_SEPARATOR;
        $this->actionViewer($path,$resume->resume_file);
    }


    /**
     *查看简历
     *@param string $id 评估表的ID
     */
    public function actionViewRecord($id)
    {
        if(!$assessment = Assessment::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        $path = Yii::getPathOfAlias('webroot.attachment.records').DIRECTORY_SEPARATOR;
        $file_name="{$path}{$assessment->record_file}";//需要下载的文件
        if(!file_exists($file_name))
        {
            throw new CHttpException(404, '找不到该文件');
        }
        $this->actionViewer($path,$assessment->record_file);
    }

    /**
     *面试安排页面
     *@param string $date  YYYY-MM
     */
	public function actionInterviewManage($date='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－面试安排";
            $start = !preg_match('/^\d{4}-\d{2}$/', $date) ? date('Y-m-01') : date('Y-m-01',strtotime($date.'-01'));
            $end   = date('Y-m-t' , strtotime($start));
            $status = "'arrange','nonarrival','success','assessment','reject','entry'";
            //结果
            $count = Resume::model()->count("interview_time >= :start and interview_time <= :end and status in ('arrange','nonarrival','success','assessment','reject','entry')",array(':start'=>$start, ':end'=>$end));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $resumes = Resume::model()->findAll(array('condition'=>"interview_time >= :start and interview_time <= :end and status in ('arrange','nonarrival','success','assessment','reject','entry','giveup')", 'params'=>array(':start'=>$start, ':end'=>$end), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            $this->breadcrumbs = array('面试管理'=>'/oa/interviewManage','面试安排');
            $this->render('interviewManage', array('resumes'=>$resumes,'page'=>$page,'date'=>$date));
        }
    }

    /**
     *面试评估详情页
     *@param string $id 简历ID
     */
    public function actionInterviewEvaluateDetail($id)
    {
        if(preg_match('/^[1-9]\d*$/',Yii::app()->session['user_id']))
        {
            if($this->user = Users::model()->findByPk(Yii::app()->session['user_id']))
            {
                if(!$resume = Resume::model()->findByPk($id))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                if(!(in_array(Yii::app()->session['user_id'] , array(Users::getHr()->user_id, Users::getAdminId()->user_id, Users::getCeo()->user_id, $resume->apply->user_id , $resume->interviewer)) || Roles::Check_role('hr', $this->user) ))
                {
                    header("Content-type: text/html; charset=utf-8");
                    echo "你没有权限查看此页面，请点击 <a href='".Yii::app()->request->urlReferrer."'>返回上一页</a>";
                    Yii::app()->end();
                }
                $procedure = empty($resume->assessment)?array():Assessment::procedure($resume->assessment); 
                $tag = false;
                if(!empty(Yii::app()->session['user_id']) && Yii::app()->session['user_id'] == Users::getHr()->user_id)
                {
                    $tag = true;
                }             
                $this->pageTitle = "OA－面试评估表";
                $this->breadcrumbs = array('面试'=>'/oa/interviewEvaluateRecord','面试评估记录'=>'/oa/interviewEvaluateRecord','面试评估表');
                $hr_id = Users::getHr()->user_id;
                $admin_id = Users::getAdminId()->user_id;
                $ceo_id = Users::getCeo()->user_id;

                $view_params = array(
                    'resume'=>$resume, 'procedure'=>$procedure,
                    'tag'=>$tag, 'hr_id'=>$hr_id,
                    'admin_id'=>$admin_id, 'ceo_id'=>$ceo_id,
                );
                $this->render('interviewEvaluateDetail', $view_params );
            }
            else
            {
               header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
            }
        }
        else
        {
           header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
        }
    }

    /**
     *面试评估记录
     *@param string $status ENUM('success','reject','wait')
     */
    public function actionInterviewEvaluateRecord($status = 'wait')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－面试评估记录";
            $this->breadcrumbs = array('面试'=>'/oa/interviewEvaluateRecord','面试评估记录'=>'/oa/interviewEvaluateRecord');
            $count = Assessment::model()->count('status=:status', array(':status'=>$status));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $assessments = Assessment::model()->findAll(array('condition'=>"status=:status", 'params'=>array(':status'=>$status), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            $this->render('interviewEvaluateRecord',array('assessments'=>$assessments, 'page'=>$page,'status'=>$status));
        }
    }

    /**
     *下载简历
     *@param string $id 简历的ID
     */
    public function actionDownloadResume($id)
    {
       $pretty_modtime = gmdate('D,d M Y H:i:s' , time()+ (8*3600) );
       @header("Last-Modified:{$pretty_modtime}");
       @header('Cache-Control:no-cache,must-revalidate');  
       @header("Expires: {$pretty_modtime}");
       @header('Pragma:no-cache');
        if(!$resume = Resume::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        $path = Yii::getPathOfAlias('webroot.attachment.resumes').DIRECTORY_SEPARATOR;
        $file_name="{$path}{$resume->resume_file}";//需要下载的文件
        $dst_name = "{$resume->apply->title}-{$resume->name}-{$resume->source}-".date('Ymd',strtotime($resume->create_time)).substr($resume->resume_file,8);
        //$file_name=iconv("utf-8","gb2312","$file_name");
        Resume::download($file_name, $dst_name);
    }

    /**
     *下载记录表
     *@param string $id 评估表的ID
     */
    public function actionDownloadRecord($id)
    {
        $pretty_modtime = gmdate('D,d M Y H:i:s' , time()+ (8*3600) );
       @header("Last-Modified:{$pretty_modtime}");
       @header('Cache-Control:no-cache,must-revalidate');  
       @header("Expires: {$pretty_modtime}");
       @header('Pragma:no-cache');
        if(!$assessment = Assessment::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        $path = Yii::getPathOfAlias('webroot.attachment.records').DIRECTORY_SEPARATOR;
        $file_name="{$path}{$assessment->record_file}";//需要下载的文件
        if(!file_exists($file_name))
        {
            throw new CHttpException(404, '找不到该文件');
        }
        list($_name,$_ext) = explode('.', $file_name);
        $dst_name = "{$assessment->resume->apply->title}-{$assessment->resume->name}-{$assessment->resume->source}-".'评估表.'.$_ext;
        echo $dst_name;
        //$file_name=iconv("utf-8","gb2312","$file_name");
        Resume::download($file_name, $dst_name);
    }

    /**
     *转正申请
     */
    public function actionPositiveApply()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－转正申请";
            $this->breadcrumbs = array('转正'=>'/oa/positiveApply','发起转正申请'=>'/oa/positiveApply');
            $users = Users::model()->findAll('status=:status', array(':status'=>'work'));
            $this->render('positiveApply',array('users'=>$users));
        }
        
    }

    /**
     *转正申请
     *@param string $id 招聘申请的ID
     */
    public function actionPositiveApplyDetail($id,$type='msgDetail')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－转正申请详情";
            if($type == 'msgDetail'){
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs','转正申请详情');
            }
            else if($type == 'positiveApplyRecord'){
                $this->breadcrumbs = array('转正'=>'/oa/positiveApply','转正申请记录'=>'/oa/positiveApplyRecord','转正申请详情');
            }
            else{
                $this->breadcrumbs = array('error(此处有bug，请通知管理员)');
            }
            //$this->breadcrumbs = array('转正'=>'/oa/positiveApply','转正申请记录'=>'/oa/positiveApplyRecord','转正申请详情');
            if(!$apply = QualifyApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            if(!(in_array(Yii::app()->session['user_id'] , array(Users::getHr()->user_id, Users::getAdminId()->user_id, Users::getCeo()->user_id, $apply->user_id , $apply->user->LeadId)) || Roles::Check_role('hr', $this->user) ))
            {
                header("Content-type: text/html; charset=utf-8");
                echo "你没有权限查看此页面，请点击 <a href='".Yii::app()->request->urlReferrer."'>返回上一页</a>";
                Yii::app()->end();
            }
            $procedure = QualifyApply::procedure($apply);

            $qulify_type =  QualifyApply::typeQualifyApply($apply);
            $ceo_id = Users::getCeo()->user_id;
            $admin_id = Users::getAdminId()->user_id;

            $view_params = array(
                'apply'=>$apply, 'user'=>$this->user, 'procedure'=>$procedure,
                'qulify_type'=>$qulify_type, 'ceo_id'=>$ceo_id,
                'admin_id'=>$admin_id,
                // ''
            );
            $this->render('positiveApplyDetail', $view_params);
        }
        
    }

    /**
     *转正申请记录
     *@url /oa/positiveApplyRecord
     *@param stirng $status enum('wait','success', 'other')
     *@return stirng $status
     *@return object $page
     *@return object $result
     */
    public function actionPositiveApplyRecord($status = 'wait')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－转正申请记录";
            $this->breadcrumbs = array('转正'=>'/oa/positiveApply','转正申请记录'=>'/oa/positiveApplyRecord');
            if(in_array($status , array('wait','success')))
            {
                $condition = 'status=:status';
                $params    = array(':status'=>$status);
            }
            else
            {
                $condition = "status in ('reject','delay')";
                $params    = array();
            }
            $count = QualifyApply::model()->count(array('condition'=>$condition, 'params'=>$params));
            $page = new CPagination($count);
            $page->pageSize = 20;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $result = QualifyApply::model()->findAll(array('condition'=>$condition, 'params'=>$params, 'limit'=>$limit, 'offset'=>$offset, 'order'=>'create_time desc'));      
            $this->render('positiveApplyRecord',array('page'=>$page,'status'=>$status, 'result'=>$result));
        }
        
    }

    /**
     *简历中心
     *@param string　$name  求职人姓名
     *@param string　$title 求职岗位
     *@param string　$department 求职部门
     *@reutrn array array('page'=>$page,'titles'=>$titles,'names'=>$names, 'data'=>$result,'name'=>$name, 'title'=>$title, 'department'=>$department));
     */
    public function actionResumeManage($name='' , $title='', $department='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－简历存档";
            $this->breadcrumbs = array('简历中心'=>'/oa/resumeManage','简历存档'=>'/oa/resumeManage');
            
            if(!empty($name))
            {
                list($page,$result) = Resume::searchResumt("name=:name", array(':name'=>$name));
            }
            else if(!empty($title))
            {
                list($page,$result) = RecruitApply::searchResumeByApply('title',$title);
            }
            else if(!empty($department))
            {
                list($page,$result) = RecruitApply::searchResumeByApply('department',$department);
            }
            else
            {
                list($page,$result) = Resume::searchResumt("", array());
            }

            $applys = RecruitApply::model()->findAll(array('select'=>'DISTINCT department,title'));
            $titles = $departments = array();
            foreach($applys as $apply)
            {
                $titles[]= $apply->title;
                $departments[]= $apply->department;
            }
            $names = Resume::model()->findAll(array('select'=>'DISTINCT name'));
            $this->render('resumeManage', array('page'=>$page,'departments'=>array_unique($departments), 'titles'=>$titles,'names'=>$names, 'data'=>$result,'name'=>$name, 'title'=>$title, 'department'=>$department));
        }
        
    }

    /**
     *离职处理
     */
    public function actionQuitProcess()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－发起离职申请";
            $this->breadcrumbs = array('离职'=>'/oa/quitProcess','发起离职申请'=>'/oa/quitProcess');
            $users = Users::model()->findAll('status=:status', array(':status'=>'work'));
            $this->render('quitProcess', array('users'=>$users));
        }
        
    }

    /**
     *离职记录
     *@param string $status ENUM('wait','success','reject')
     *@return array $data
     *@return string $status
     *@return object $page
     */
    public function actionQuitRecord($status = 'wait')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－离职申请记录";
            $this->breadcrumbs = array('离职'=>'/oa/quitProcess','离职申请记录'=>'/oa/QuitRecord');
            $condition = 'status=:status';
            $params    = array(':status'=>$status);
            $count = QuitApply::model()->count(array('condition'=>$condition, 'params'=>$params));
            $page = new CPagination($count);
            $page->pageSize = 20;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $result = QuitApply::model()->findAll(array('condition'=>$condition, 'params'=>$params, 'limit'=>$limit, 'offset'=>$offset, 'order'=>'create_time desc'));      
            $this->render('quitRecord', array('status'=>$status, 'page'=>$page, 'data'=>$result));
        }
        
    }

    /**
     *离职申请详情
     *@param string $id 为离职申请的ID
     */
    public function actionQuitDetail($id,$type='msgDetail')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－离职处理";
            if($type == 'msgDetail'){
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs','离职申请详情');
            }
            else if($type == 'quitRecord'){
                $this->breadcrumbs = array('离职'=>'/oa/quitProcess','离职申请记录'=>'/oa/quitRecord','离职申请详情');
            }
            else{
                $this->breadcrumbs = array('error(此处有bug，请通知管理员)');
            }
            //$this->breadcrumbs = array('离职'=>'/oa/quitProcess','离职申请记录'=>'/oa/quitRecord','离职申请详情');
            if(!$apply = QuitApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            $procedure = QuitApply::procedure($apply);
            $users = Users::model()->findAll('user_id != :user_id and status = :status', array(':user_id'=>Yii::app()->session['user_id'],':status'=>'work'));
            $this->render('quitDetail',array('apply'=>$apply,'procedure'=>$procedure, 'user'=>$this->user, 'users'=>$users));
        }
        
    }


    /**
     *工作交接表
     *@param string $id 为离职申请ID
     */
    public function actionDeliverWorkDetail($id)
    {
        if(preg_match('/^[1-9]\d*$/',Yii::app()->session['user_id']))
        {
            $this->user = Users::model()->findByPk(Yii::app()->session['user_id']);
            if(!empty($this->user))
            {
                $this->pageTitle = "OA－工作交接详情";
                $this->breadcrumbs = array('离职'=>'/oa/quitProcess','工作交接记录'=>'/oa/deliverWorkRecord','工作交接详情');
                if(!$apply = QuitApply::model()->findByPk($id))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                if($apply->status != 'success')
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                //if($apply->user_id != $this->user->user_id && $this->user->user_id != Users::getWebAdmin()->user_id && empty(Yii::app()->session['admin']))
                $webAdmin = Users::getWebAdmin();
                if(!in_array($this->user->user_id , array($apply->user_id, $apply->handover_user_id, empty($webAdmin->user_id)?'':$webAdmin->user_id )) && empty(Yii::app()->session['admin']) )
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                $handover_user = Users::model()->findByPk($apply->handover_user_id);
                $commissioner =  Users::getCcommissioner();
                $admin_user = Users::getAdminId();
                $hr_user = Users::getHr();
                // $web_user = Users::getWebAdmin();
                $work = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'work'));
                $admin = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'admin'));
                $hr = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'hr'));
                $it = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'it'));
                $supervision_info = isset($work->supervision_id) ? Users::model()->findByPk($work->supervision_id) : "";

                $admin_sid = isset($admin->supervision_id) ? Users::model()->findByPk($admin->supervision_id)->user_id : "";
                $hr_sid = isset($hr->supervision_id)?Users::model()->findByPk($hr->supervision_id)->user_id : "";

                $it_sid = "";
                if(!empty($it->supervision_id))
                    $it_sid = Users::model()->findByPk($it->supervision_id)->user_id;

                $admin_details_info = isset($admin->id) ? QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$admin->id)): "";
                $hr_details_info =  isset($hr->supervision_id)? QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$hr->id)) : "";
                $it_details_info = isset($it->supervision_id)? QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$it->id)) : "";

                $work_details = array();
                if(!empty($work))
                    $work_details = QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$work->id));

                $view_params = array('apply'=>$apply ,'user'=>$this->user,
                    'handover_user_info'=>$handover_user,
                    'commissioner_info' => $commissioner,
                    'admin_user_info' => $admin_user,
                    'hr_user_info'=>$hr_user,
                    'web_user_info'=>$webAdmin,
                    'work_info'=>$work,
                    'admin_info'=>$admin,
                    'hr_info'=>$hr,
                    'it_info'=>$it,
                    'supervision_info'=>$supervision_info,
                    'admin_sid'=>$admin_sid, 'hr_sid'=>$hr_sid,'it_sid'=>$it_sid,
                    'admin_details_info' => $admin_details_info,
                    'hr_details_info'=> $hr_details_info,
                    'it_details_info' => $it_details_info,
                    'work_details' => $work_details
                );

                $this->render('deliverWorkDetail', $view_params);
            }
            else
            {
               header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
            }
        }
        else
        {
           header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
        }
    }

    /**
     *工作交接记录
     */
    public function actionDeliverWorkRecord()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－工作交接记录";
            $this->breadcrumbs = array('离职'=>'/oa/quitProcess','工作交接记录'=>'/oa/deliverWorkRecord');
            $count = Yii::app()->db->createCommand("SELECT count(distinct quit_apply.id) FROM quit_apply join quit_handover on(quit_apply.id = quit_handover.apply_id);")->queryScalar();
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $result = QuitApply::model()->findAll(array('condition'=>"status in ('wait','success')",  'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            $result = QuitApply::model()->findAllBySql("SELECT distinct quit_apply.*  FROM quit_apply join quit_handover on(quit_apply.id = quit_handover.apply_id) limit {$limit} offset {$offset};");
            $this->render('deliverWorkRecord', array('page'=>$page, 'data'=>$result));
        }
        
    }

  
    /**
     *加班报表统计
     *@url /oa/overTimeList/month/$month
     *@return array  array('data'=>$result));
     */
    public function actionOverTimeList($month='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－".date('m',strtotime($month))."月加班统计";
            // $this->breadcrumbs = array('加班'=>'/oa/departmentOverTime',$this->pageTitle=>'/oa/overTimeList');
            $this->breadcrumbs = array('加班'=>'/oa/departmentOverTime','加班统计'=>'/oa/overTimeList');
            $start = date('Y-m-01 00:00:00',strtotime($month));
            $end   = date('Y-m-t 23:59:59', strtotime($month));
            $result = array();
            if($list  = Overtime::model()->findAll("start_time >= :start and start_time <= :end and status=:status", array(':start'=>$start, ':end'=>$end,':status'=>'success')))
            {
                foreach($list as $row)
                {
                    //加班记录列表
                    $result[$row->user->department_id]['department_name'] = empty($result[$row->user->department_id]['department_name']) ? $row->user->department->name : $result[$row->user->department_id]['department_name'];

                    $result[$row->user->department_id]['list'][$row->user_id]['name'] = empty($result[$row->user->department_id]['list'][$row->user_id]['name']) ? $row->user->cn_name : $result[$row->user->department_id]['list'][$row->user_id]['name'];
                    $result[$row->user->department_id]['list'][$row->user_id]['days'] = empty($result[$row->user->department_id]['list'][$row->user_id]['days']) ? 0 : $result[$row->user->department_id]['list'][$row->user_id]['days'];
                    $result[$row->user->department_id]['list'][$row->user_id]['count'] = empty($result[$row->user->department_id]['list'][$row->user_id]['count']) ? 0 : $result[$row->user->department_id]['list'][$row->user_id]['count'];
                    if($row['type'] == 'holiday')
                    {
                        $result[$row->user->department_id]['list'][$row->user_id]['days'] +=  $row->countWorkTime; 
                    }
                    else
                    {
                        $result[$row->user->department_id]['list'][$row->user_id]['count'] +=  1; 
                    }
                }
            }
            $this->render('overTimeList', array('data'=>$result,'month'=>$month));
        }
    }

    /**
     * @ignore
     *活动统计
     */
    public function actionProcessActivity()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－活动出席统计";
            $this->breadcrumbs = array('公司活动'=>'/oa/newActivity','活动出席统计');
             //本周活动
            $start = date('w') == 1 ? date('Y-m-d 00:00:00') : date('Y-m-d 00:00:00',strtotime('last monday'));
            $end = date('Y-m-d 23:59:59', strtotime('+6days',strtotime($start)));
            $activity = Activity::model()->find('end_time >= :start and end_time <= :end',array(':start'=>$start,':end'=>$end));
            $this->render('processActivity', array('activity'=>$activity));
        }
        
    }

    /**
     * @ignore
     *处理加班（详情，负责人勾选，然后提交）
     */
    public function actionProcessOverTime($date)
    {
            if(!empty($this->user))
            {
                $this->pageTitle = "OA－加班详情";
                $this->breadcrumbs = array('加班管理'=>'/oa/overTimeList','加班详情');
                $result = Overtime::model()->findAll(array('condition'=>"status =:status and overtime_date = :date and head_id = :head_id", 'params'=>array(':date'=>$date, ':status'=>'wait',':head_id'=>$this->user->user_id)));
                $this->render('processOverTime',array('data'=>$result));
            }
    }

    /**
     *部门加班记录（列表）
     *@url /oa/departmentOverTime/month/$month/user_id/$users_id/status/$status ENUM('all','wait','success','rejct')
     *@return array
     *array('page'=>$page, 'data'=>$result, 'users'=>$users, 'month'=>$month, 'user_id'=>$user_id, 'status'=>$status));
     */
    public function actionDepartmentOverTime($month='', $user_id='', $status='wait')
    {
            if(!empty($this->user))
            {
                $start = date('Y-m-01 00:00:00',strtotime($month));
                $end   = date('Y-m-t 23:59:59',strtotime($month));
                $this->pageTitle = "OA－部门加班记录";
                $this->breadcrumbs = array('加班'=>'/oa/departmentOvertime','部门加班管理'=>'/oa/departmentOvertime');
                $condition = "start_time >= :start and start_time <= :end";
                $params = array(':start'=>$start, ':end'=>$end);
                if($_department_users = Users::model()->findAll('status=:status and department_id=:id',array(':status'=>'work',':id'=>$this->user->department_id)))
                {
                    $_users_ids = array();
                    foreach($_department_users as $_user)
                    {
                        $_users_ids[] = $_user->user_id;
                    }
                    $condition .= " and user_id in (".join($_users_ids,',').")";
                }
                if(in_array($status, array('wait','reject','success')))
                {
                    $condition .= " and status=:status";
                    $params[':status'] = $status;
                }
                if(preg_match('/^\d+$/', $user_id))
                {
                    $condition .= " and user_id=:user_id";
                    $params[':user_id'] = $user_id;
                }
                $count = Overtime::model()->count(array('condition'=>$condition, 'params'=>$params));
                $page = new CPagination($count);
                $page->pageSize = 15;
                $limit = $page->pageSize;
                $offset = $page->currentPage * $page->pageSize ;
                $result = Overtime::model()->findAll(array('condition'=>$condition, 'params'=>$params, 'order'=>'create_time desc','limit'=>$limit, 'offset'=>$offset));
                $users = Users::model()->findAll();
                $this->render('departmentOverTime', array('page'=>$page, 'data'=>$result, 'users'=>$users, 'month'=>$month, 'user_id'=>$user_id, 'status'=>$status));
            }
    }


    /**
     *公司加班统计模块
     *@url /oa/overTime/month/$month/department_id/$deparetment_id/user_id/$user_id/status/$status
     *@return array array('page'=>$page, 'data'=>$result, 'users'=>$users, 'month'=>$month, 'user_id'=>$user_id,'departments'=>$departments, 'department_id'=>$department_id, 'status'=>$status));
     */
    public function actionOverTime($month='', $department_id = '', $user_id='', $status='wait')
    {
        if(!empty($this->user))
        {
            $start = date('Y-m-01 00:00:00',strtotime($month));
            $end   = date('Y-m-t 23:59:59',strtotime($month));
            $this->pageTitle = "OA－部门加班记录";
            $this->breadcrumbs = array('加班'=>'/oa/departmentOvertime','公司加班查询'=>'/oa/overTime');
            $count = Overtime::getOvertimeCountByDepartment($start, $end, $department_id,$user_id,$status);
            $page = new CPagination($count);
            $page->pageSize = 15;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $result = Overtime::getOvertimeDataByDepartment($start, $end, $department_id,$user_id,$status, $limit, $offset);
            $users = Users::model()->findAll();
            $departments = Department::model()->findAll();
            $this->render('overTime', array('page'=>$page, 'data'=>$result, 'users'=>$users, 'month'=>$month, 'user_id'=>$user_id,'departments'=>$departments, 'department_id'=>$department_id, 'status'=>$status));
        }
    }

    /**
     *申购详情
     *@param string $id 物品申请的ID
     */
    public function actionSubscribeDetail($id,$type="msgDetail")
    {
        if(!empty($this->user))
        {   
            if($type == 'msgDetail'){
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs','申请详情');
            }
            else if($type == 'subscribeProcessRecord'){
                $this->breadcrumbs = array('费用'=>'/oa/budget','审批记录'=>'/oa/subscribeProcessRecord','申请详情');
            }
            else if($type == 'subcribeRecord'){
                $this->breadcrumbs = array('费用'=>'/oa/budget','申请记录'=>'/oa/subscribeRecord','申请详情');
            }else{
                $this->breadcrumbs = array('error');
            }

            if(!$apply = GoodsApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此申请单');
            }
            elseif ( $apply->status=='cancle' ) {
                throw new CHttpException(404, '申请者已撤回申请单');
            }
            $this->pageTitle = "OA－申请详情";
            
            $procedure = GoodsApply::procedure($apply);
            $excess_tag = GoodsApplyDetail::excessBudget($apply->user, $apply->details); //true就是超出预算了

            $admin_id = Users::getAdminId()->user_id;

            $add_info = array();
            $toolstip_info = array();

            foreach ($apply->details as $key => $value) {
                $add_info[$key] = Budget:: getDepartmentTypeBudget($apply->user->department_id, $value->category);

                $content = "";
                if( $tmp_div_p = CJSON::decode($value->fee_div_p, true) ) {
                    foreach ($tmp_div_p as $key1 => $value1) {
                        $content .= Project::model()->findByPk($key1)->name .':'. $value1 .'%<br>';
                    }
                }
                $toolstip_info[$key] = $content;
            }

            $view_params = array(
                'excess_tag'=>$excess_tag,'apply'=>$apply,'procedure'=>$procedure,
                'admin_id'=>$admin_id, 'add_info'=>$add_info, 'toolstip_info'=>$toolstip_info
            );

            $this->render('subscribeDetail', $view_params);
        }
    }

    /**
     *申购记录
     *@param string $status ENUM('wait','success','reject')
     *@param string $month YYYY-MM 月份
     *@param string $user_id 用户ID
     *@param string $department_id 部门ID
     */
    public function actionSubscribeRecord($status='wait', $month='', $user_id='', $department_id='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－申请记录";
            $this->breadcrumbs = array('费用'=>'/oa/subscribeProcessRecord','申请记录'=>'/oa/subscribeRecord');
            $condition = "";
            $params = array();
            //状态
            if(in_array($status,array('wait','reject','success')))
            {
                $condition = "status=:status ";
                $params[':status'] = $status;
            }
            //月份
            if(preg_match('/^\d{4}-\d{2}$/', $month))
            {
                if(!empty($condition)) $condition .= " and ";
                $condition .= "create_time >= :start and create_time <=:end";
                $params[':start'] = date('Y-m-01 00:00:00',strtotime($month.'-01'));
                $params[':end'] = date('Y-m-t 23:59:59',strtotime($month.'-01'));
            }
            //用户ID
            if(preg_match('/^[1-9]\d*$/', $user_id))
            {
                if(!empty($condition)) $condition .= " and ";
                $condition .= "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            //部门ID
            $user_ids = array();
            if(preg_match('/^[1-9]\d*$/', $department_id))
            {
                $department_users = Users::model()->findAll('status=:status and department_id=:department_id',array(':status'=>'work',':department_id'=>$department_id));
                foreach($department_users as $row)
                {
                   $user_ids[] = $row->user_id; 
                }
                if(!empty($user_ids))
                {
                    if(!empty($condition)) $condition .= " and ";
                    $condition .= " user_id in (".join($user_ids,',').")";
                }
            }
            if(preg_match('/^[1-9]\d*$/', $department_id) && empty($user_ids))
            {
                $count=0;
                $page = new CPagination($count);
                $goodsApplys = array();
            }
            else
            {
                $count = GoodsApply::model()->count(array('condition'=>$condition, 'params'=>$params));
                $page = new CPagination($count);
                $page->pageSize = 10;
                $limit = $page->pageSize;
                $offset = $page->currentPage * $page->pageSize ;
                $goodsApplys = GoodsApply::model()->findAll( array('condition'=>$condition,'params'=>$params, 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            }
            $departments = Department::model()->findAll();
            $users = Users::model()->findAll('status=:status',array(':status'=>'work'));

            $department_info = array();
            foreach ($goodsApplys as $key => $value) {
                $department_info[$key] = Users::model()->findByPk($value->user_id)->department->name;
            }

            $view_params = array(
                'departments'=>$departments, 'user_id'=>$user_id, 'department_id'=>$department_id,
                'users'=>$users,'page'=>$page,'status'=>$status, 'month'=>$month,'data'=>$goodsApplys,
                'department_name_info' => $department_info,
            );
            $this->render('subscribeRecord', $view_params);
        }
    }
    /**
     *审批记录
     *@param string $status ENUM('wait','success','reject')
     *@param string $month YYYY-MM
     *@param string $user_id 搜索用户的ID
     */
    public function actionSubscribeProcessRecord($status='wait',$month='',$user_id='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－审批记录";
            $this->breadcrumbs = array('费用'=>'/oa/subscribeProcessRecord','审批记录'=>'/oa/SubscribeProcessRecord');
            $user_ids = array();
            $users = Users::model()->findAll('status=:status and department_id=:department_id',array(':status'=>'work',':department_id'=>$this->user->department_id));
            $condition = "";
            $params = array();
            //状态
            if(in_array($status,array('wait','reject','success')))
            {
                $condition = "status=:status ";
                $params[':status'] = $status;
            }
            //月份
            if(preg_match('/^\d{4}-\d{2}$/', $month))
            {
                if(!empty($condition)) $condition .= " and ";
                $condition .= "create_time >= :start and create_time <=:end";
                $params[':start'] = date('Y-m-01 00:00:00',strtotime($month.'-01'));
                $params[':end'] = date('Y-m-t 23:59:59',strtotime($month.'-01'));
            }
            //搜索用户ID
            if(preg_match('/^[1-9]\d*$/', $user_id))
            {
                if(!empty($condition)) $condition .= " and ";
                $condition .= "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            else
            {
                //搜索自己部门的所有人
                foreach($users as $row)
                {
                   $user_ids[] = $row->user_id; 
                }
                if(!empty($condition)) $condition .= " and ";
                $condition .= " user_id in (".join($user_ids,',').")";
            }
            $count = GoodsApply::model()->count(array('condition'=>$condition, 'params'=>$params));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $goodsApplys = GoodsApply::model()->findAll( array('condition'=>$condition,'params'=>$params, 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $users = Users::model()->findAll('status=:status',array(':status'=>'work'));
            $this->render('subscribeProcessRecord', array('user_id'=>$user_id,'users'=>$users,'page'=>$page,'status'=>$status, 'month'=>$month,'data'=>$goodsApplys));     
        }
    }

    /**
     *报销记录
     *@url /oa/reimburseRecord/month/$month/user_id/$user_id/department_id/$department_id
     */
    public function actionReimburseRecord($month='',$user_id='',$department_id='', $no='')
    {
        $this->pageTitle = "OA－报销记录";
        $this->breadcrumbs = array('费用'=>'/oa/subscribeProcessRecord','报销记录'=>'/oa/reimburseRecord');
        $condition = "";
        $params = array();
        //月份搜索
        if(preg_match('/^\d{4}-\d{2}$/', $month))
        {
            if(!empty($condition)) $condition .= " and ";
            $condition .= "create_time >= :start and create_time <=:end";
            $params[':start'] = date('Y-m-01 00:00:00',strtotime($month.'-01'));
            $params[':end'] = date('Y-m-t 23:59:59',strtotime($month.'-01'));
        }
        // 编号搜索
        if(preg_match('/^\d+$/', $no))
        {
            if(!empty($condition)) $condition .= " and ";
            $condition .= "id = :no";
            $params[':no'] = $no;
        }
        //员工搜素
        if(preg_match('/^[1-9]\d*$/', $user_id))
        {
            if(!empty($condition)) $condition .= " and ";
            $condition .= "user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        $user_ids = array();
        if(preg_match('/^[1-9]\d*$/', $department_id))
        {
            //查询该部门的人
            $department_users = Users::model()->findAll('status=:status and department_id=:department_id',array(':status'=>'work',':department_id'=>$department_id));
            foreach($department_users as $row)
            {
               $user_ids[] = $row->user_id; 
            }
            if(!empty($condition)) $condition .= " and ";
            $condition .= " user_id in (".join($user_ids,',').")";
        }

        if(preg_match('/^[1-9]\d*$/', $department_id) && empty($user_ids))
        {
            $count = 0;
            $page = new CPagination($count);
            $reimburses = array();
        }
        else
        {
            $count = Reimburse::model()->count(array('condition'=>$condition, 'params'=>$params));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $reimburses = Reimburse::model()->findAll(array('condition'=>$condition,'params'=>$params, 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
        }
        $departments = Department::model()->findAll();
        $users = Users::model()->findAll('status=:status',array(':status'=>'work'));

        $add_info = array();
        foreach ($reimburses as $key=>$value) {
            $add_info[$key]['department_name'] = Users::model()->findByPk($value->user_id)->department->name ;
            $add_info[$key]['cn_name'] = Users::model()->findByPk($value->user_id)->cn_name ;;
        }

        $view_params = array(
            'month'=>$month,'user_id'=>$user_id,'department_id'=>$department_id,
            'no'=>$no,'users'=>$users,'departments'=>$departments,
            'page'=>$page,'data'=>$reimburses, 'add_info'=>$add_info,
        );
        $this->render('reimburseRecord', $view_params);
    }


    /**
     *发布公告
     */
    public function actionNewNotification()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－发布公告";
            $this->breadcrumbs = array('公告'=>'/oa/notificationManage','发布公告'=>'/oa/newNotification');
            $this->render('newNotification');
        }
        
    }

    /**
     *公告管理
     *@param string $status ENUM('display','hidden')
     */
    public function actionNotificationManage($status='display')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－公告管理";
            $this->breadcrumbs = array('公告'=>'/oa/notificationManage','公告管理'=>'oa/notificationManage');
            $count = Notification::model()->count(array('condition'=>"status = :status", 'params'=>array(':status'=>$status)));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $result = Notification::model()->findAll( array('condition'=>"status = :status",'params'=>array(':status'=>$status), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('notificationManage',array('status'=>$status,'page'=>$page,'data'=>$result));
        }
        
    }

    /**
     *OA操作人员设置表
     */
    public function actionAdminSet()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－操作人员设置";
            $this->breadcrumbs = array('公司'=>'/oa/structure','操作人员设置'=>'/oa/adminSet');
            $departments = Department::model()->findAll();
            $users = Users::model()->findAll('status=:status',array(':status'=>'work'));
            $operators = Operator::model()->findAll();
            $this->render('adminSet',array('users'=>$users,'operators'=>$operators, 'departments'=>$departments));
        }
    }

    public function actionRolesSet()
    {
        if(!empty($this->user) && Roles::Check_User_in_roles($this->user))
        {
            $this->pageTitle = "OA－系统权限设置";
            $this->breadcrumbs = array('公司'=>'/oa/structure','系统权限设置'=>'/oa/rolesSet');

            $users = Users::model()->findAll('status=:status',array(':status'=>'work'));
            $roles = Roles::model()->findAll('status=:status',array(':status'=>'enable'));
            $roles_user = Roles::getRolesUser();
            //echo CJSON::encode($roles_user);
            $roles_comment = array('super'=>'超级管理员','normal'=>'普通管理员', 'hr'=>'人事', 'account'=>'财务');
            $this->render('rolesSet',array('users'=>$users,'roles'=>$roles,'roles_user'=>$roles_user,'roles_comment'=>$roles_comment));
        }
    }

    /**
     *求取加班数据
     *@param string $month 搜索月份
     */
    public function getOvertime($month)
    {
        //求数据
            $start = date('Y-m-01 00:00:00', strtotime($month));
            $end = date('Y-m-t 23:59:59', strtotime($month));
            $result = array();
            if($_result = Overtime::getOvertimeDataByDepartment($start, $end))
            {
                foreach($_result as $_row)
                {
                    $result[]=array('month'=>date('m月份',strtotime($month)), 'department'=>$_row->user->department->name, 
                        'count'=>$_row->countWorkTime, 'name'=>$_row->user->cn_name,
                        'time'=>date('Y-m-d H:i',strtotime($_row->start_time)).'到'.date('Y-m-d H:i',strtotime($_row->end_time)),
                        'type'=>$_row->type);
                }
            }
            if(empty($result))
            {
                header("Content-type:text/html;charset=utf-8");
                echo '没有数据';
                Yii::app()->end();
            }
            return $result;
    }
    /**
     *下载加班天数表
     */
    public function actionDownloadHolidayOvertime($month)
    {
        //下载头
            $pretty_modtime = gmdate('D,d M Y H:i:s' , time()+ (8*3600) );
            @header("Last-Modified:{$pretty_modtime}");
            @header('Cache-Control:no-cache,must-revalidate');  
            @header("Expires: {$pretty_modtime}");
            @header('Pragma:no-cache');
            $_start = date('Y-m-01', strtotime($month));
            $_end = date('Y-m-t', strtotime($month));
            $result = $this->getOvertime($month);
            //导入phpExecl
            spl_autoload_unregister(array('YiiBase','autoload'));
            $objPHPExcel =  Yii::createComponent('application.extensions.excel.PHPExcel');
            //添加加班天数表
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            $numCol=0;
            $sheet->setCellValue("{$this->letters[$numCol++]}1", "月份");
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '部门');
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '姓名');
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '加班天数');
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '加班时间');
            $i=0;
            foreach($result as $row)
            {
                 if($row['type'] != 'holiday') continue;
                 $numCol=0;
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['month']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['department']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['name']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['count']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['time']);
                 $i++;
            }
            // Rename worksheet
            $sheet->setTitle('加班天数报表');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);    
            $file = Yii::getPathOfAlias('webroot.reports').DIRECTORY_SEPARATOR."holidayOvertime-{$_start}-{$_end}.xlsx";
            $objWriter->save($file);
            //恢复Yii自动加载功能          
            spl_autoload_register(array('YiiBase','autoload')); 
            $url=Yii::app()->request->hostInfo;
            header("location:{$url}/reports/holidayOvertime-{$_start}-{$_end}.xlsx");
    }
    
    /**
     *下载加班次数表
     */
    public function actionDownloadOvertime($month)
    {
        //下载头
            $pretty_modtime = gmdate('D,d M Y H:i:s' , time()+ (8*3600) );
            @header("Last-Modified:{$pretty_modtime}");
            @header('Cache-Control:no-cache,must-revalidate');  
            @header("Expires: {$pretty_modtime}");
            @header('Pragma:no-cache');
            $_start = date('Y-m-01', strtotime($month));
            $_end = date('Y-m-t', strtotime($month));
            $result = $this->getOvertime($month);
            //导入phpExecl
            spl_autoload_unregister(array('YiiBase','autoload'));
            $objPHPExcel =  Yii::createComponent('application.extensions.excel.PHPExcel');
            //添加加班天数表
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            $numCol=0;
            $sheet->setCellValue("{$this->letters[$numCol++]}1", "月份");
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '部门');
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '姓名');
            $sheet->setCellValue("{$this->letters[$numCol++]}1", '加班时间');
            $i=0;
            foreach($result as $row)
            {
                 if($row['type'] == 'holiday') continue;
                 $numCol=0;
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['month']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['department']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), $row['name']);
                 $sheet->setCellValue("{$this->letters[$numCol++]}".($i+2), mb_substr($row['time'],19));
                 $i++;
            }
            // Rename worksheet
            $sheet->setTitle('加班次数报表');
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);    
            $file = Yii::getPathOfAlias('webroot.reports').DIRECTORY_SEPARATOR."overtime-{$_start}-{$_end}.xlsx";
            $objWriter->save($file);
            //恢复Yii自动加载功能          
            spl_autoload_register(array('YiiBase','autoload')); 
            $url=Yii::app()->request->hostInfo;
            header("location:{$url}/reports/overtime-{$_start}-{$_end}.xlsx");
    }  
    /**
     *加班申请详情
     *@url /oa/overtimeDetail/id/$id
     *加班时间 ：$overtime->countWorkTime
     *进度条  根据$overtime->status来判断
     *@日志   根据$overtime->logs
     */
    public function actionOvertimeDetail($id,$type='msgDetail')
    {
        $this->pageTitle = "OA－加班申请详情";
        // $this->breadcrumbs = array('加班'=>'/oa/departmentOvertime','部门加班管理'=>'/oa/departmentOvertime','加班申请详情');
        if($type == 'msgDetail'){
            $this->breadcrumbs = array('消息列表'=>'/oa/msgs','加班申请详情');
        }
        else if($type == 'departmentOverTime'){
            $this->breadcrumbs = array('加班'=>'/oa/departmentOverTime','部门加班管理'=>"/oa/{$type}",'加班申请详情');
        }
        else if($type == 'overTime'){
            $this->breadcrumbs = array('加班'=>'/oa/departmentOverTime','公司加班管理'=>"/oa/{$type}",'加班申请详情');
        }
        else{
             $this->breadcrumbs = array('error');
        }

        if(!$overtime = Overtime::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        if($overtime->type == 'normal')
        {
            throw new CHttpException(404, '找不到此页面');
        }
        else
        {
            $head = Users::model()->findByPk($overtime->head_id);
        }
        $head = Users::model()->findByPk($overtime->head_id);
        $procedure = array();
        if( $procedure_list = CJSON::decode($overtime->procedure_list, true) ) {
            $logs = $overtime->logs;
            $reject_flag = false;
            foreach ($procedure_list as $row) {
                $tmp = array();
                $flag = false;
                $uses_info = Users::model()->findByPK($row);
                $tmp['name'] = $uses_info->cn_name;
                $tmp['department'] = $uses_info->department->name;
                $tmp['status'] = 'wait';
                foreach ($logs as $log) {
                    if($uses_info->user_id == $log->user_id) {
                        $tmp['status'] = $log->action;
                        $flag = true;
                        if($log->action == 'reject')
                            $reject_flag = true;
                        break;
                    }
                }
                if($reject_flag)
                    $tmp['status'] = 'reject';
                $procedure[] = $tmp;
            }
        }
        // echo CJSON::encode($procedure);
        $this->render('overtimeDetail', array('data'=>$overtime, 'head'=>$head, 'procedure'=>$procedure));
    }

    /**
     *公司加班查询
     */
    public function actionCompanyOverTime()
    {
        $this->pageTitle = "OA－公司加班查询";
        $this->breadcrumbs = array('加班管理'=>'/oa/overTimeList','公司加班查询');
        $this->render('companyOverTime');
    }

    /**
     *预算列表
     */
    public function actionBudget()
    {
        $this->pageTitle = "OA－公司预算列表";
        $this->breadcrumbs = array('费用'=>'/oa/subscribeProcessRecord','费用预算'=>'/oa/budget');
        $list = Budget::model()->findAll(array('group'=>'year','select'=>'year', 'order'=>'year desc'));
        $this->render('budget', array('data'=>$list));
    }

    /**
     *新增预算
     */
    public function actionNewBudget()
    {
        $this->pageTitle = "OA－新增预算";
        $this->breadcrumbs = array('费用'=>'/oa/budget','费用预算'=>'/oa/budget','新增年度费用预算');
        $list = Budget::model()->findAll(array('group'=>'year','select'=>'year', 'order'=>'year desc'));
        $departments = Department::model()->findAll(array('order'=>'department_id desc'));
        $this->render('newBudget',array('data'=>$list, 'departments'=>$departments));
    }


    /**
     *预算详情
     *@url /oa/budgetDetail/year/$year
     *@return array $departments
     *@return array $data  本年度的预算
     */
    public function actionBudgetDetail($year='')
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－预算详情";
            $this->breadcrumbs = array('费用'=>'/oa/budget','费用预算'=>'/oa/budget','费用预算详情');
            $budgets = array();
            $changes = array();
            $departments = Department::model()->findAll(array('order'=>'department_id desc'));
            if(preg_match('/^\d{4}$/',$year))
            {
                if($budgets = Budget::model()->findAll(array('order'=>'department_id asc', 'condition'=>"year=:year",'params'=>array(':year'=>$year))))
                {
                    foreach($budgets as $row)
                    {
                        $changes[$row->department_id][$row->type] = $row;
                    }
                }
            }
            $this->render('budgetDetail', array('year'=>$year, 'changes'=>$changes, 'departments'=>$departments, 'data'=>$budgets));
        }
    }

    /**
     *预算修改
     *@param string $year YYYY
     */
    public function actionBudgetEdit($year='')
    {
        $this->pageTitle = "OA－预算修改";
        $this->breadcrumbs = array('费用'=>'/oa/budget','费用预算'=>'oa/budget','修改费用预算');
        $budgets = array();
        $departments = Department::model()->findAll(array('order'=>'department_id desc'));
        if(preg_match('/^\d{4}$/',$year))
        {
            $budgets = Budget::model()->findAll(array('order'=>'department_id asc', 'condition'=>"year=:year",'params'=>array(':year'=>$year)));
        }
        $this->render('budgetEdit', array('year'=>$year, 'departments'=>$departments, 'data'=>$budgets));
    }

    /**
     *费用报表
     *@param string $year YYYY 要搜索的年份
     */
    public function actionCostForm($year='')
    {
        $this->pageTitle = "OA－费用报表";
        $this->breadcrumbs = array('费用'=>'/oa/subscribeProcessRecord','费用报表'=>'/oa/costForm');
        $report = 0;
        if(!preg_match('/^\d{4}$/', $year))
        {
            $year = date('Y');
        }
        $ceo_id = Users::getCeo()->user_id;
        $admin_id = Users::getAdminId()->user_id;
        $hr_id = Users::getHr()->user_id;
        $commissioner_id =  Users::getCcommissioner()->user_id;
        $report = ExpenseReport::model()->count('year=:year',array(':year'=>$year));

        $view_params = array(
            'report'=>$report, 'year'=>$year,
            'ceo_id'=>$ceo_id, 'admin_id'=>$admin_id,
            'hr_id' =>$hr_id, 'commissioner_id'=>$commissioner_id,
        );
        $this->render('costForm', $view_params);
        echo CJSON::encode($report);
    }
    /**
     *一级费用报表
     *@param string $month 要搜素的年月 YYYY-MM
     */
    public function actionCostFormFirDetail($month='')
    {
        $this->pageTitle = "OA－一级费用报表";
        $this->breadcrumbs = array('费用'=>'/oa/cost_apply','一级费用报表');
        $this->layout = "blank";
        $category = array('office'=>'办公费','welfare'=>'福利费','travel'=>'差旅费','entertain'=>'业务招待费','hydropower'=>'水电费','intermediary'=>'中介费','rental'=>'租赁费','test'=>'测试费','outsourcing'=>'外包费','property'=>'物管费','repair'=>'修缮费','other'=>'其他');
        $reports = array();
        $budgets = array();
        if(preg_match('/^\d{4}-\d{2}$/', $month))
        {
            $year = date('Y',strtotime($month));
            if($_budgets = Budget::model()->findAll(array('condition'=>'year=:year','params'=>array(':year'=>$year),'order'=>"field(type, 'office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other')")))
            {
                foreach($_budgets as $row)
                {
                    $budgets[$row->department_id]=empty($budgets[$row->department_id]) ? 0 : $budgets[$row->department_id];
                    $budgets[$row->department_id] += $row->total;
                }
            }
            $reports = ExpenseReport::model()->findAll(array('condition'=>'year=:year','params'=>array(':year'=>$year)));#,'order'=>"field(type, 'office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other')"));
        }
        $departments = Department::model()->findAll();
        $this->render('costFormFirDetail', array('month'=>$month, 'category'=>$category, 'budgets'=>$budgets, 'reports'=>$reports, 'departments'=>$departments));
        // echo CJSON::encode($departments);
    }

    /**
     *二级费用报表
     *@param string $month 要搜素的年月 YYYY-MM
     */
    public function actionCostFormSecDetail($month='')
    {
        $this->pageTitle = "OA－二级费用报表";
        $this->breadcrumbs = array('费用'=>'/oa/cost_apply','二级费用报表');
        $this->layout = "blank";
        $category = array('office'=>'办公费','welfare'=>'福利费','travel'=>'差旅费','entertain'=>'业务招待费','hydropower'=>'水电费','intermediary'=>'中介费','rental'=>'租赁费','test'=>'测试费','outsourcing'=>'外包费','property'=>'物管费','repair'=>'修缮费','other'=>'其他');
        $reports = array();
        if(preg_match('/^\d{4}-\d{2}$/', $month))
        {
            $start = date('Y-m-01 00:00:00', strtotime($month));
            $end   = date('Y-m-t 23:59:59', strtotime($month));
            if($details = ReimburseDetail::model()->findAll(array('condition'=>'create_time >= :start and create_time <= :end', 'params'=>array(':start'=>$start,':end'=>$end))))
            {
                foreach($details as $row)
                {
                    $_key = empty($row->applyDetail->type) ? 'key' : $row->applyDetail->type;
                    $_department_name = $row->reimburse->user->department->name;
                    $reports[$row->applyDetail->category][$_department_name][$_key][] = $row;
                }
            }
        }
        $this->render('costFormSecDetail',array('month'=>$month, 'category'=>$category, 'reports'=>$reports));
    }

     /**
     *小组预算查询
     *@param string $year YYYY 要搜索的年份
     */
    public function actionActivityBudget($year='')
    {
        $this->pageTitle = "OA－费用预算";
        $this->breadcrumbs = array('兴趣小组'=>'/oa/activityRecord','费用预算'=>'/oa/activityBudget');
        if(!preg_match('/^\d{4}$/', $year)) $year = date('Y');
        $teams = InterestTeam::model()->findAll();
        $budgets = InterestTeamBudget::model()->findAll('year=:year',array(':year'=>$year));
        $list = array();
        if($_list = InterestTeamBudget::model()->findAll(array('group'=>'year','select'=>'year', 'order'=>'year desc')))
        {
            foreach($_list as $row)
            {
                $list[]= $row->year;
            }
        }
        $this->render('activityBudget', array('year'=>$year,'teams'=>$teams, 'budgets'=>$budgets, 'list'=>$list));
    }

    /**
     *兴趣小组组长设置
     */
    public function actionActivityHeadSet()
    {
        $this->pageTitle = "OA－组长设置";
        $this->breadcrumbs = array('兴趣小组'=>'/oa/activityRecord','组长设置'=>'/oa/activityHeadSet');
        $teams = InterestTeam::model()->findAll();
        $users = Users::model()->findAll("status =:status", array( ':status'=>'work'));
        $this->render('activityHeadSet', array('teams'=>$teams ,'users'=>$users));
    }
/**
 *参与统计
 *@param string $month YYYY-MM 要搜索的年月
 *@param string $team_id 小组ID
 */
    public function actionActivityRecord($month='', $team_id='')
    {
        $this->pageTitle = "OA－参与统计";
        $this->breadcrumbs = array('兴趣小组'=>'/oa/ActivityRecord','参与统计'=>'/oa/activityRecord');
        $activitys = array();
        $condition = "status=:status";
        $params[':status'] = 'success';
        if(preg_match('/^[1-9]\d*$/', $team_id))
        {
            if(!empty($condition)) $condition .= " and ";
            $condition .= "team_id = :team_id";
            $params[':team_id'] = $team_id;
        }
        if(preg_match('/^\d{4}-\d{2}$/', $month))
        {
            $start = date('Y-m-01 00:00:00', strtotime($month));
            $end   = date('Y-m-t 23:59:59', strtotime($month));
            if(!empty($condition)) $condition .= " and ";
            $condition .= "activity_time >= :start and activity_time <= :end";
            $params[':start'] = $start;
            $params[':end'] = $end;
        }
        $activitys = InterestTeamActivity::model()->findAll(array('condition'=>$condition, 'params'=>$params));
        $teams = InterestTeam::model()->findAll();
        $users = Users::model()->findAll(array('condition'=>'status=:status', 'params'=>array(':status'=>'work'), 'order'=>'department_id asc'));
        $joins = array();
        if($_joins = InterestTeamJoin::model()->findAll('status=:status',array(':status'=>'join')))
        {
            foreach($_joins as $_row)
            {
                $joins[$_row->user_id][$_row->activity->team_id] = empty($joins[$_row->user_id][$_row->activity->team_id] ) ? 0 :  $joins[$_row->user_id][$_row->activity->team_id] ;
                $joins[$_row->user_id][$_row->activity->team_id] += 1;
            }
        }
        $this->render('activityRecord',array( 'month'=>$month, 'team_id'=>$team_id,'users'=>$users, 'teams'=>$teams,'joins'=>$joins, 'activitys'=>$activitys ));
    }

    /**
     *印鉴表
     *@param string $department_id //部门ID
     *@param string $user_id       //用户ID
     */
    public function actionSeal($department_id='', $user_id='')
    {
        $this->pageTitle = "OA－印鉴申请";
        $this->breadcrumbs = array('其他'=>'/oa/seal','印鉴申请'=>'/oa/seal');
        $departments = Department::model()->findAll();
        $users = Users::model()->findAll(array('condition'=>'status=:status', 'params'=>array(':status'=>'work'), 'order'=>'department_id asc'));
        $pattern = '/^[1-9]\d*$/';
        $condition = "";
        $params = array();
        if(preg_match($pattern, $user_id))
        {
            $condition = "user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        if(preg_match($pattern, $department_id))
        {
            $user_ids = array();
            foreach($users as $row)
            {
                if($row->department_id == $department_id)
                {
                    $user_ids[]= $row->user_id;
                }
            }
            if(!empty($user_ids))
            {
                if(!empty($condition)) $condition .= " and ";
                $str_user_ids = join($user_ids , ',');
                $condition .= "user_id in ({$str_user_ids})";
            }
        }
        $count = Seal::model()->count(array('condition'=>$condition, 'params'=>$params));
        $page = new CPagination($count);
        $page->pageSize = 15;
        $limit = $page->pageSize;
        $offset = $page->currentPage * $page->pageSize ;
        $seals = Seal::model()->findAll(array('condition'=>$condition, 'params'=>$params, 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
        $this->render('seal', array('page'=>$page,'department_id'=>$department_id, 'user_id'=>$user_id, 'seals'=>$seals, 'users'=>$users, 'departments'=>$departments));
    }
    /**
     *印鉴查看
     *@param string $id 印章申请的ID
     */
    public function actionPrintSeal($id)
    {
        $this->layout = 'blank';
        $this->pageTitle = "OA－印鉴查看";
        $this->breadcrumbs = array('其他'=>'/oa/seal','印鉴查看');
        if(!$seal = Seal::model()->findByPk($id))
        {
                throw new CHttpException(404, '找不到此页面');
        }
        $this->render('printSeal',array('id'=>$id, 'seal'=>$seal));
    }

    /**
     *部门负责人看到自己部门的一级报表
     */
    public function actionCostFormFirDetailForAdmin($month='')
    {
        $this->pageTitle = "OA－一级费用报表";
        $this->breadcrumbs = array('费用'=>'/oa/cost_apply','一级费用报表');
        $this->layout = "blank";
        $category = array('office'=>'办公费','welfare'=>'福利费','travel'=>'差旅费','entertain'=>'业务招待费','hydropower'=>'水电费','intermediary'=>'中介费','rental'=>'租赁费','test'=>'测试费','outsourcing'=>'外包费','property'=>'物管费','repair'=>'修缮费','other'=>'其他');
        $reports = array();
        $budgets = array();
        if(preg_match('/^\d{4}-\d{2}$/', $month))
        {
            $year = date('Y',strtotime($month));
            if($_budgets = Budget::model()->findAll(array('condition'=>'year=:year','params'=>array(':year'=>$year),'order'=>"field(type, 'office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other')")))
            {
                foreach($_budgets as $row)
                {
                    $budgets[$row->department_id]=empty($budgets[$row->department_id]) ? 0 : $budgets[$row->department_id];
                    $budgets[$row->department_id] += $row->total;
                    //$budgets[$row->type] = empty($budgets[$row->type]) ? 0 : $budgets[$row->type];
                    //$budgets[$row->type] += $row->total;
                }
            }
            $reports = ExpenseReport::model()->findAll(array('condition'=>'year=:year','params'=>array(':year'=>$year)));#,'order'=>"field(type, 'office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other')"));
        }
        $departments = Department::model()->findAll();
        $this->render('costFormFirDetailForAdmin', array('month'=>$month, 'category'=>$category, 'budgets'=>$budgets, 'reports'=>$reports, 'departments'=>$departments));
    }

    /**
     *人事变动表
     *@param string $year 年份
     */
    public function actionPersonnelChange($year='')
    {
        $this->pageTitle = "OA－人事变动统计";
        $this->breadcrumbs = array('公司'=>'/oa/structure', '人事变动统计'=>'oa/personnelChange');
        if(preg_match('/^\d{4}$/', $year))
        {
            $users = Users::model()->findAll(array('order' => 'department_id asc'));
        }
        $this->render('personnelChange',array('users'=>empty($users) ? array() : $users, 'year'=>$year));
    }


    // yeqingwen 2015.08.20js
    public function actioneditorMsg($editor_apply_id)
    {
        if(!empty($this->user))
        {
            try
           {
                $this->pageTitle = "OA－文档发布详情";
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs', '文档发布详情');
                if ( $editor_apply_info = EditorApply::model()->findByPk($editor_apply_id) ) {
                    $approve_user_id = EditorRoles::getApproverId();
                    $approve_user = Users::model()->findByPk($approve_user_id);

                    $editor = Editor::model()->findByPk($editor_apply_info['editor_id']);
                    $apply_user = Users::model()->findByPk($editor_apply_info['user_id']);

                    $user_department = Department::model()->findByPk($apply_user['department_id']);
                    $dir_name = "";
                    if ($editor_apply_info['dir_id']) {
                        $dir = EditorDir::model()->findByPk($editor_apply_info['dir_id']);
                        $dir_name = EditorDir::getFullPathName($dir['dir_id']);
                    }
                    else
                        $dir_name = "根目录";

                    if( $parent_editor = Editor::model()->findByPk($editor['parent_id']) ) {
                        $now_dir = EditorDir::getFullPathName($parent_editor['dir_id']);
                        $dir_name .= '<span style="color:red"> (注意:此文件将会替换"'.$now_dir.'/'.$parent_editor['title'].'")</span>';
                    }

                    $editor_apply = $editor->attributes;               //转换为数组

                    $result = array('apply_user'=>$apply_user['cn_name'], 'apply_user_title' => $apply_user['title'] ,
                        'apply_user_dp' => $user_department['name'] , 'dir_apply_name'=>$dir_name, 'approve_user'=>$approve_user['cn_name']);

                    $editor_apply_js = CJSON::encode($editor_apply_info);

                    $editor_info = $editor_apply + $result;
                    $editor_info_js = CJSON::encode($editor_info);
                    $this->render('msg_editor', array('editor_info' => $editor_info_js, 'editor_apply_js'=>$editor_apply_js) );
                }
            }
           catch(Exception $e)
           {
                throw new CHttpException(404, '找不到此页面');
           }
        }
    }

    // yeqingwen 2015.08.21
    public function actionEditorRolesSet()
    {
        if(!empty($this->user))
        {
            if ( EditorRoles::checkUserInRolesTable($this->user->user_id) ) {
                $this->pageTitle = "OA－文档库权限设置";
                $this->breadcrumbs = array('公司'=>'/oa/structure','文档库权限设置'=>'/oa/editorRolesSet');
                $users = Users::model()->findAll('status=:status',array(':status'=>'work'));
                $editor_roles = EditorRoles::model()->findAll();

                $users_js = CJSON::encode($users);
                $editor_roles_js = CJSON::encode($editor_roles);

                $this->render('editorRolesSet',array('users_js'=>$users_js,'editor_roles_js'=>$editor_roles_js ));
            }
            else {
                header("Content-type: text/html; charset=utf-8");
                echo "无权限访问此页面";
            }
        }
    }

    public function actionVote() {
        if(!empty($this->user)) {
            $this->pageTitle = "OA－发起投票";
            $this->breadcrumbs = array('其他'=>'/oa/seal','发起投票'=>'oa/vote');
            $this->render('vote',array());
        }
    }

    public function actionProcessManage() {
        if(!empty($this->user)) {
            $this->pageTitle = "OA－流程管理";
            $this->breadcrumbs = array('公司'=>'/oa/structure','流程管理'=>'oa/processManage');
            $procedure_list = Procedure::model()->findAll(array(
                'order' => 'procedure_order ASC',
            ));
            $procedure_list = CJSON::encode($procedure_list);  //所有流程清单
            $this->render('processManage',array('procedure_list'=>$procedure_list));
            // echo $procedure_list;
        }
    }

    //项目管理 yeqingwen 2015-12-31
    public function actionProjectManage() {
        $this->pageTitle = "OA－项目管理";
        $this->breadcrumbs = array('其他'=>'/oa/seal','项目管理'=>'oa/projectManage');
        $department_list = Department::model()->findAll('department_status=:t_status', array(':t_status'=>'display'));
        $user_list = Users::model()->findAll('status =:t_status', array(':t_status'=>'work'));
        $project_list = Project::model()->findAll('enable =:t_status', array(':t_status'=>'yes'));
        $view_params = array(
            'department_list' => $department_list,
            'user_list' => $user_list,
            'project_list' => $project_list,
        );
        $this->render('projectManage', $view_params);
        // echo CJSON::encode($department_list);
    }

    //费用模板管理 yeqingwen 2015-12-31
    public function actionFeeTplManage() {
        $this->pageTitle = "OA－费用分摊模板管理";
        $this->breadcrumbs = array('其他'=>'/oa/seal','费用分摊模板管理'=>'oa/feeTplManage');
        $department_list = Department::model()->findAll('department_status=:t_status', array(':t_status'=>'display'));
        $user_list = Users::model()->findAll('status =:t_status', array(':t_status'=>'work'));
        $fee_tpl_list = FeeDivisionTpl::model()->findAll('enable =:t_status', array(':t_status'=>'yes'));
        $project_list = Project::model()->findAll('enable =:t_status', array(':t_status'=>'yes'));
        $view_params = array(
            'department_list' => $department_list,
            'user_list' => $user_list,
            'fee_tpl_list' => $fee_tpl_list,
            'project_list' => $project_list,
        );
        $this->render('feeTplManage', $view_params);
        // echo CJSON::encode($fee_tpl_list);
    }

    public function actionMsg2($leave='', $notice='', $type='msgDetail')
    {
        if(!empty($this->user))
        {
            try
            {
                $this->pageTitle="OA－请假申请详情";
                
                if($type == 'msgDetail')
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假申请详情');
                }
                else if($type == 'leaveSummary' || $type == 'leaveSummaryFailed' || $type == 'leaveSummaryWait')
                {
                    $this->breadcrumbs = array('请假'=>'processLeaveRecord','请假记录'=>"/oa/{$type}",'请假申请详情');
                }
                elseif($type == 'processLeaveRecord')
                {
                    $this->breadcrumbs = array('请假'=>'processLeaveRecord','审批记录'=>"/oa/{$type}",'请假申请详情');
                }
                else
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假申请详情');
                }
                //如果找到了有消息就直接更改成已读
                if(!empty($notice))
                {
                    $notice = Notice::model()->findByPk($notice);
                    Notice::updateNotice($notice, array('status' => 'read'));
                }

                if(empty($leave) || !$leave = Leave::model()->findByPk($leave))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                //用户的所有日志
                $logs = $leave->allLogs; 
                $procedure = Leave::procedure($leave);
                // echo CJSON::encode($procedure);
                $this->render('msg2', array('notice'=>$notice, 'leave'=>$leave,'logs'=>$logs,'procedure'=>$procedure ));
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
       
    }
}