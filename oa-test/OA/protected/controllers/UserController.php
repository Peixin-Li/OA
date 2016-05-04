<?php
/**
 *普通用户控制器
 */
class UserController extends Controller
{
	/**
     *@var $layout 前端所需要的布局的页面名称
     *@var $pageTitle 页面标题
     *@var $breadcrumbs 面包屑
     *@var $user 用户对象
     *@var $url  当前的方法
	 */
    public $layout = 'user';
    public $pageTitle = '';
    public $breadcrumbs= array(); 
    public $user;
    public $url;

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
            'verify -  login error',
        );


    }
 
    //定义的过滤方法
    public function FilterVerify($filterChain)
    {
        //判断什么的
        //过滤完后继续执行代码
        if( Yii::app()->session['user_id']==-1) {  //如果是财务账号，则跳转至财务的页面
            $this->redirect('/account/index');
        }
        if( empty(Yii::app()->session['user_id']) || !preg_match('/^\d+$/',Yii::app()->session['user_id']) )
        {
            Yii::app()->session['refer'] = Yii::app()->request->getUrl();
            header('Location: '.Yii::app()->request->hostInfo.'/user/login#'.Yii::app()->request->getUrl());
        }
        elseif(!$this->user = Users::model()->findByPk(Yii::app()->session['user_id']))
        {
            header('Location: '.Yii::app()->request->hostInfo.'/ajax/logout');
        }//权限设置
        else
          $filterChain->run();
    }

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
     * @url /user/index
     * @param bool $tag 如果参加了本期活动则为true ，否则为false
     * @param object $activity 本期活动 有可能为空
     * @param object $user     本人对象
     * @param object $notice    未读消息
	 */
	public function actionIndex()
	{
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－首页";
            //本人未读消息
           $count = Notice::model()->count('user_id = :user_id and status=:status',array(':user_id'=>Yii::app()->session['user_id'], ':status'=>'wait'));
           $notices = Notice::model()->findAll(array('condition'=>'user_id = :user_id and status=:status','params'=>array(':user_id'=>Yii::app()->session['user_id'], ':status'=>'wait'),'order'=>'create_time desc','limit'=>4)) ;
           //公告
           $notifys = Notification::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'display'),'order'=>'create_time desc','limit'=>3));
           $this->render('index', array('notifys'=>$notifys, 'user'=>$this->user, 'count'=>$count , 'notices'=>$notices));
        }
	}
  public function actiontestmsg() {
    echo CJSON::encode($this->user->msgCount);
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
     *请假填写单
     *@url /user/leave
     *@return object $user 当前用户
     *@return object $users 除了当前登录用户外的所有用户
     *@reutrn string $year  年假天数
     */
    public function actionLeave()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－请假申请";
            $count = Leave::model()->count('user_id=:user_id', array(':user_id'=>$this->user->user_id));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveRecords = Leave::model()->findAll( array('condition'=>'user_id=:user_id','params'=>array(':user_id'=>$this->user->user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            //计算当前可以请几天年假
            //$year = Leave::calcYearLeave($this->user);
            //本月已经请假几天
            $monthleaveTotal = LeaveMonthReport::calcMonthDays($this->user, date('Y-m-01'));
            //目前总请假天数
            $leaveTotal = LeaveMonthReport::calcLeaveDays($this->user);
            //总共请假天数
            $users = Users::model()->findAll("user_id !=:user_id and status =:status", array(':user_id'=>$this->user->user_id, ':status'=>'work'));
            //用户年假记录
            $annualLeave = $this->user->userAnnualLeaveDays;
            //可以补休天数
            $compensatTime = Overtime::getCompensatTime($this->user->user_id);
            $holidays = Holiday::model()->findAll(array('condition'=>"holiday >= :day",'params'=>array(':day'=>date('Y-01-01')), 'order'=>'holiday asc'));
            $this->render('leave', array('user'=>$this->user,'holidays'=>$holidays, 'users'=>$users,'monthleaveTotal'=>$monthleaveTotal, 'leaveTotal'=>$leaveTotal, 'leaveRecords'=> $LeaveRecords,'page'=>$page , 'compensatTime'=>$compensatTime, 'annual_days'=>empty($annualLeave->total)?0:(float)$annualLeave->total));
        }
    }

    //获取用户补休
    public function actionGetusersBuxiu() {
      $users_list = Users::model()->findAll('status=:t_status', array(':t_status'=>'work') );
      foreach ($users_list as $row) {
          $compensatTime = Overtime::getCompensatTime($row->user_id);
          $annualLeave = $row->userAnnualLeaveDays;
          // echo CJSON::encode($annualLeave);
          echo $row->cn_name.','.$compensatTime.','.$annualLeave['total'].'<br>';
      }
      // $compensatTime = Overtime::getCompensatTime($user_id);
      // echo CJSON::encode($compensatTime);
    }

    /**
      *自己的请假记录
      *通过
      **/
     public function actionLeaveRecord()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的请假";
            $user_id = Yii::app()->session['user_id'];
            $count = Leave::model()->count('user_id=:user_id and status=:status', array(':user_id'=>$user_id, ':status'=>'success'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveRecords = Leave::model()->findAll( array('condition'=>'status=:status and user_id=:user_id','params'=>array(':status'=>'success', ':user_id'=>$user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('leaveRecord', array( 'LeaveRecords'=> $LeaveRecords,'page'=>$page, 'count'=>$count,'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
     }

     /**
      *待审批
      */
     public function actionleaveRecordWait()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的请假";
            $user_id = Yii::app()->session['user_id'];
            $count = Leave::model()->count('user_id=:user_id and status=:status', array(':user_id'=>$user_id, ':status'=>'wait'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveRecords = Leave::model()->findAll( array('condition'=>'status=:status and user_id=:user_id','params'=>array(':status'=>'wait', ':user_id'=>$user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('leaveRecordWait', array( 'LeaveRecords'=> $LeaveRecords,'page'=>$page, 'count'=>$count,'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
        
     }
     /**
      *自己的请假记录
      *未通过
      **/
     public function actionLeaveRecordFailed()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的请假";
            $user_id = Yii::app()->session['user_id'];
            $count = Leave::model()->count('user_id=:user_id and status=:status', array(':user_id'=>$user_id, ':status'=>'reject'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $LeaveRecordFaileds = Leave::model()->findAll( array('condition'=>'status=:status and user_id=:user_id', 'params'=>array(':status'=>'reject', ':user_id'=>$user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('leaveRecordFailed', array( 'LeaveRecordFaileds'=>$LeaveRecordFaileds, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
     }

     /**
      *我的请假--搜索--按年月查询
      *@param yyyy-mm $date
      *@return object
      **/
     public function actionLeaveSearch( $date='')
     {  
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的请假--快速搜索";
            $users_names = Users::model()->findAll();
            $start_date = date('Y-m-01 00:00:00', strtotime($date));
            $url = "date/$date";
            $end_date = date('Y-m-01 00:00:00', strtotime('+1month', strtotime($date) ) );
            $count = Leave::model()->count(array( 'condition'=>'user_id=:user_id and ((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date ))','params'=>array(':user_id'=>Yii::app()->session['user_id'], ':start_date'=>$start_date, ':end_date'=>$end_date, ) ));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $my_leaves = Leave::model()->findAll( array( 'condition'=>'user_id=:user_id and ((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date ))','params'=>array(':user_id'=>Yii::app()->session['user_id'], ':start_date'=>$start_date, ':end_date'=>$end_date, ), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('leaveSearch', array('my_leaves'=>$my_leaves,'users_names'=>$users_names, 'page'=>$page,'url'=>$url ,'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
     }

     /**
    *出差
    **/
    public function actionBusinessTrip()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－出差申请";
            $users = Users::model()->findAll("user_id !=:user_id", array(':user_id'=>$this->user->user_id));
            $count = Out::getOutListCount($this->user->user_id);
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            //$outRecords = Out::model()->findAll( array('condition'=>'user_id=:user_id', 'params'=>array(':user_id'=>$this->user->user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $outRecords = Out::getOutList($this->user->user_id, $limit, $offset);
            $days = Out::countOutDays($this->user);
            $month_days = Out::countOutDays($this->user, 'month');
            $this->render('businessTrip' , array('month_days'=>$month_days,'user'=>$this->user ,'days'=>$days, 'users'=>$users,'outRecords'=>$outRecords, 'page'=>$page,));
        }
    }

    /**
      * 我的出差
      * 待审批
      */
     public function actionBusinessTripRecordWait()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的出差";
            $user_id = Yii::app()->session['user_id'];
            $count = Out::model()->count('user_id=:user_id and status=:status', array(':user_id'=>$user_id, ':status'=>'wait'));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $outRecords = Out::model()->findAll( array('condition'=>'status=:status and user_id=:user_id', 'params'=>array(':status'=>'wait', ':user_id'=>$user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('businessTripRecordWait', array( 'outRecords'=>$outRecords, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
     }

     /**
      *我的出差
      *已成功
      */
     public function actionBusinessTripRecord()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的出差";
            $user_id = Yii::app()->session['user_id'];
            $status = 'success';
            $count = Out::model()->count('user_id=:user_id and status=:status', array(':user_id'=>$user_id, ':status'=>$status));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $outRecords = Out::model()->findAll( array('condition'=>'status=:status and user_id=:user_id', 'params'=>array(':status'=>$status, ':user_id'=>$user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('businessTripRecord', array( 'outRecords'=>$outRecords, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
         
     }

     /**
      *我的出差
      *未通过
      */ 
     public function actionBusinessTripRecordFailed()
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的出差";
            $user_id = Yii::app()->session['user_id'];
            $status = 'reject';
            $count = Out::model()->count('user_id=:user_id and status=:status', array(':user_id'=>$user_id, ':status'=>$status));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $outRecords = Out::model()->findAll( array('condition'=>'status=:status and user_id=:user_id', 'params'=>array(':status'=>$status, ':user_id'=>$user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('businessTripRecordFailed', array( 'outRecords'=>$outRecords, 'page'=>$page, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
        }
     }

     /**
      *我的出差--搜索--按年月查询
      *@param yyyy-mm $date
      *@return object
      **/
     public function actionBusinessTripSearch( $date='' )
     {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的出差--快速搜索";
            $start_date = date('Y-m-01 00:00:00', strtotime($date));
            $end_date = date('Y-m-01 00:00:00', strtotime('+1month', strtotime($date) ) );
            $url = "date/$date";
            $users_names = Users::model()->findAll();
            $count = Out::model()->count(array( 'condition'=>'user_id=:user_id and ((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date ))','params'=>array(':user_id'=>Yii::app()->session['user_id'], ':start_date'=>$start_date, ':end_date'=>$end_date, ) ));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $my_outs = Out::model()->findAll( array( 'condition'=>'user_id=:user_id and ((:start_date<=start_time and start_time<:end_date) or (:start_date<=end_time and end_time<:end_date ))','params'=>array(':user_id'=>Yii::app()->session['user_id'], ':start_date'=>$start_date, ':end_date'=>$end_date, ), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $this->render('businessTripSearch', array('my_outs'=>$my_outs, 'users_names'=>$users_names,'page'=>$page,'url'=>$url, 'count'=>$count, 'size'=>$page->pageSize, 'total'=> ceil($count/$page->pageSize), ) );
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
            $categorys = BookCategory::model()->findAllBySql("SELECT book_category.* FROM book_category join books on (book_category.category_id=books.category_id) group by book_category.category_id;");
            $books = Books::model()->findAll('status != :status',array(':status'=>'loss'));
            $books_new = Books::model()->findAll(array('condition'=>'status != :status','params'=>array(':status'=>'loss'),'order'=>'book_id desc','limit'=>3));
            $borrows = Borrow::model()->findAll(array('condition'=>'user_id = :user_id and return_time=:time','params'=>array(':user_id'=>$this->user->user_id, ':time'=>'0000-00-00 00:00:00'),'order'=>'borrow_id desc'));
            $budget = Budget::model()->find('year=:year and department_id = :department_id and type=:type',array(':year'=>date('Y'),':department_id'=>$this->user->department_id, ':type'=>'welfare'));
            $this->render('books',array('borrows'=>$borrows, 'books_new'=>$books_new, 'books'=>$books, 'categorys'=>$categorys, 'budget'=>$budget, 'user_id'=>empty($this->user->user_id)?'':$this->user->user_id ) );
        }
    }

    /**
     *会议室管理
     *@url /oa/meetingRoomManage
     *@param stirng $date 2014-10
     *@return array $rooms array(array('id','name','location','status'))
     *@return array data array('2014-10-10'=>array(array('content','room_id','meeting_date','start_time',end_time,user_id)), ''2014-10-11'=>array(array()))
     */
    public function actionMeetingRoomManage($date='')
    {
        if(!empty($this->user))
        {
            $date = empty($date) ? date('Y-m') : $date;
            $start = date('Y-m-01' , strtotime($date.'-01'));
            $end   = date('Y-m-t'  , strtotime($start));
            $this->pageTitle = "OA－会议室管理";
            $result = array();
            $today_meetings = Meeting::model()->findAll(array('condition'=>'meeting_date = :date' , 'params'=>array(':date'=>date('Y-m-d')),'order'=>'room_id , start_time asc'));
            $rooms = MeetingRoom::model()->findAll('status=:status',array(':status'=>'enable'));
            if($meetings = Meeting::model()->findAll(array('condition'=>'meeting_date >= :start and meeting_date <=:end' , 'params'=>array(':start'=>$start, ':end'=>$end),'order'=>'start_time asc')))
            {
                foreach($meetings as $row)
                {
                    $result[$row->meeting_date][] = $row;
                }
            }
            $this->render('meetingRoomManage', array('data'=>$result ,'date'=>$date,'rooms'=>$rooms,'todays'=>$today_meetings));
        }
    }

    /**
     *我的离职申请
     */
    public function actionPersonalQuitRecord()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的离职申请";
            $this->breadcrumbs = array('离职处理'=>'/oa/PersonalQuitRecord','我的离职申请');
            $data = QuitApply::model()->findAll('user_id=:user_id', array(':user_id'=>Yii::app()->session['user_id']));

            $quit_handover_info = array();
            foreach ($data as $key => $value) {
                $quit_handover_info[$key] = QuitHandover::model()->find('apply_id=:id',array(':id'=>$value->id));
            }

            $this->render('personalQuitRecord', array('data'=>$data, 'quit_handover_info'=>$quit_handover_info));
        }
    }

    /**
     *转正申请
     */
    public function actionPositiveApply()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－转正申请";
            $this->render('positiveApply');
        }
    }

    /**
     *我的转正申请
     */
    public function actionPersonalPositiveApply()
    {   
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－我的转正申请";
            $applys = QualifyApply::model()->findAll('user_id=:user_id',array(':user_id'=>Yii::app()->session['user_id']));
            $this->render('personalPositiveApply', array('applys'=>$applys));
        }
    }

    /**
     *请假详情
     *@param string $leave  请假ID
     *@param string $notice 消息ID
     *@param string $type   根据此类型生成面包屑
     */
    public function actionMsg($leave='', $notice='', $type='leaveRecord')
    {
        if(!empty($this->user))
        {
            try
            {
                $this->pageTitle="OA－请假申请详情";
                //根据不同的类型生成不同的面包屑 
                if($type == 'leaveRecord' || $type == 'leaveRecordFailed' || $type == 'leaveRecordWait')
                {
                    $this->breadcrumbs = array('我的请假'=>"/oa/{$type}",'请假申请详情');
                }
                elseif($type == 'leaveSummary' || $type == 'leaveSummaryFailed' || $type == 'leaveSummaryWait')
                {
                    $this->breadcrumbs = array('请假记录'=>"/oa/{$type}",'请假申请详情');
                }
                else
                {
                    $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假申请详情');
                }
                if(!empty($notice))
                {
                    $notice = Notice::model()->findByPk($notice);
                    Notice::updateNotice($notice, array('status' => 'read'));
                }

                if(empty($leave) || !$leave = Leave::model()->findByPk($leave))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                $logs = $leave->allLogs; 
                $procedure = Leave::procedure($leave);              
                $this->render('msg', array('notice'=>$notice, 'leave'=>$leave,'logs'=>$logs,'procedure'=>$procedure ));
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
       
    }

    /**
     *出差消息详情
     *@param string $out  出差的ID
     *@param string $notice 通知的ID
     *@param string $type  类型生成
     */
    public function actionOutMsg($out, $notice='', $type='')
    {
        if(!empty($this->user))
        {
            try
            {
                $this->pageTitle="OA－出差申请详情";
                //通知
                if(!empty($notice))
                {
                    $notice = Notice::model()->findByPk($notice);
                    Notice::updateNotice($notice, array('status' => 'read'));
                }
                
                $logs = OutLog::model()->findAll("out_id = :out_id order by create_time asc",array(':out_id'=>$out));
                if( $out_info = Out::model()->findByPk($out) ) {
                  //出差进度条
                  $procedure = Out::procedure($out_info);
                  $this->render('msg_businessTrip', array('notice'=>$notice, 'out'=>$out_info, 'logs'=>$logs, 'procedure'=>$procedure, ));
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
     *转正申请
     *@param string $id  转正ID
     */
    public function actionPositiveApplyDetail($id)
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－转正申请详情";
            $this->breadcrumbs = array('转正'=>'/oa/positiveApply','转正申请详情');
            if(!$apply = QualifyApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            if(!in_array(Yii::app()->session['user_id'] , array(Users::getHr()->user_id, Users::getAdminId()->user_id, Users::getCeo()->user_id, $apply->user_id , $apply->user->LeadId)))
            {
                header("Content-type: text/html; charset=utf-8");
                echo "你没有权限查看此页面，请点击 <a href='".Yii::app()->request->urlReferrer."'>返回上一页</a>";
                Yii::app()->end();
            }
            $procedure = QualifyApply::procedure($apply);
            $qualify_type = QualifyApply::typeQualifyApply($apply);
            $admin_id = Users::getAdminId()->user_id;
            $ceo_id = Users::getCeo()->user_id;

            $view_params = array(
                'apply'=>$apply, 'user'=>$this->user, 'procedure'=>$procedure,
                'qualify_type'=>$qualify_type, 'admin_id'=>$admin_id,
                'ceo_id'=>$ceo_id, 
            );
            $this->render('positiveApplyDetail', $view_params);
        }
    }

    /**
     *离职申请详情
     *@param string $id 离职ID
     */
    public function actionQuitDetail($id)
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－离职处理";
            $this->breadcrumbs = array('离职处理'=>'/oa/quitProcess','离职申请详情');
            if(!$apply = QuitApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            //进度条
            $procedure = QuitApply::procedure($apply);
            $users = Users::model()->findAll('user_id != :user_id and status = :status', array(':user_id'=>Yii::app()->session['user_id'],':status'=>'work'));
            $this->render('quitDetail',array('apply'=>$apply,'procedure'=>$procedure, 'user'=>$this->user, 'users'=>$users));
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
            $user_id = Yii::app()->session['user_id'];
            $types = array('leave'=>'请假','seal'=>'印鉴申请', 'out'=>'出差', 'recruit'=>'招聘', 'qualify'=>'转正', 'quit'=>'离职','suggest'=>'反馈',
              'overtime'=>'加班','goods_apply'=>'请购', 'editor'=>'文档', 'vote'=>'投票'); 
            
            if($status=='wait')
            {
                $count = empty($this->user->msgCount)?0:$this->user->msgCount;
                $count_wait = empty($this->user->msgCount)?0:$this->user->msgCount;
            }
            else
            {
                $count = empty($this->user->readCount)?0:$this->user->readCount;
                $count_wait = empty($this->user->msgCount)?0:$this->user->msgCount;
            }
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            if(empty($status))
            {
                $msgs = Notice::model()->findAll(array('condition'=>"user_id=:user_id ", 'params'=>array(':user_id'=>$user_id,), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            }
            else
            {
                $msgs = Notice::model()->findAll(array('condition'=>"user_id=:user_id and status=:status", 'params'=>array(':user_id'=>$user_id, ':status'=>$status), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset));
            }
            // echo CJSON::encode($msgs);
            $this->render('msgs',array('page'=>$page,'msgs'=>$msgs, 'count'=>$count,'count_wait'=>$count_wait ,'size'=>$page->pageSize,'total'=> ceil($count/$page->pageSize), 'status'=>$status, 'types'=>$types));
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
            if(!$msg = Notice::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            Notice::updateNotice($msg, array('status' => 'read'));
            $types = array('leave'=>'请假', 'seal'=>'印鉴申请', 'out'=>'出差', 'goods_apply'=>'请购', 'recruit'=>'招聘', 'qualify'=>'转正', 'quit'=>'离职','suggest'=>'反馈','overtime'=>'加班'); 
            $this->render('msgDetail',array('msg'=>$msg, 'types'=>$types));
        }
    }

    /**
    *入职信息登记表
    **/
    public function actionPersonalInfo()
    {
      if(!empty($this->user))
        {
          $this->pageTitle="OA－我的资料";
          $entry = Entry::model()->find('user_id=:user_id',array(':user_id'=>$this->user->user_id));
          $family = Family::model()->findAll('user_id=:user_id',array(':user_id'=>$this->user->user_id));
          $edu = Educate::model()->findAll('user_id=:user_id',array(':user_id'=>$this->user->user_id));
          $work = Work::model()->findAll('user_id=:user_id',array(':user_id'=>$this->user->user_id));
          $this->render('personalInfo',array('user'=>$this->user ,'edu'=>$edu,'work'=>$work,'family'=>$family, 'entry'=>$entry));                
        }
    }

    /**
     *组织架构
     */
    public function actionStructure()
    {
        if(!empty($this->user))
        {
            $this->pageTitle = 'OA－公司架构';
            try
            {
                //人员按入职时间先后顺序排列(默认进来)
                $first_users = Users::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'work'),'order'=>'entry_day asc'));
                //部门的数组
                $department_result = array();
                $sortArray = array();
                $departments = Department::model()->findAll(array("order" => "parent_id asc"));
                $users_arr = Users::model()->findAll( array('condition'=>'status=:status', 'params'=>array(':status'=>'work' ) , 'order' => 'department_id asc') );
                //部门负责人的数组
                foreach($departments as $row)
                {   
                    $department_result[$row->department_id] = array('id'=>"0{$row->department_id}", 'name'=> $row->name, 'pId'=> "0{$row->parent_id}" , 'status' => $row->department_status, 'type'=>'department','admin'=>$row->admin, 'admin_name'=>empty($row->leader->cn_name)?'':$row->leader->cn_name);

                    $sortArray[$row->department_id] = "0{$row->parent_id}";
                    $user_count = 0;
                    $department_result[$row->department_id]['count'] = 0;

                    $sub_department = Department::subdepartment($row['department_id']);
                    $department_result[$row->department_id]['formation_count'] = 0;
                    foreach ($sub_department as $sub_id) {
                        foreach($users_arr as $_user)
                        {
                          if($sub_id == $_user->department_id) {
                              $department_result[$row->department_id]['count'] = empty($department_result[$row->department_id]['count'])? 1: ($department_result[$row->department_id]['count']+1);
                          }
                        }
                      $department_result[$row->department_id]['count'] = $department_result[$row->department_id]['count'];
                      $department_result[$row->department_id]['formation_count'] += Department::model()->getFormationCountByid($sub_id);
                    }
                    $department_result[$row->department_id]['lack_count'] =  $department_result[$row->department_id]['formation_count'] - $department_result[$row->department_id]['count'];
                    
                }
                //按照部门的pId来升序排序
                array_multisort($sortArray, SORT_ASC, $department_result);
                //用户的数组
                $users = array();
                $coditions = array();
                $total = empty($users_arr)? 0 : count($users_arr);
                
                foreach($users_arr as $user)
                {
                    $coditions[] = !empty($user->en_name) ? ($user->en_name.'-'.$user->cn_name) : $user->cn_name;
                    $users[$user->user_id] = array('id'=>$user->user_id, 'pId'=>"0{$user->department_id}",  'sex'=>$user->gender , 'type'=>'employee', 'job_status'=>$user->job_status);
                    $users[$user->user_id]['name'] = empty($user->en_name) ? "{$user->cn_name}" : "{$user->en_name}-{$user->cn_name}";
                }

                $this->render('structure' , array('total'=>$total, 'self_id'=>Yii::app()->session['user_id'], 'departments'=>$department_result , 'coditions'=>$coditions,  'users'=>$users, 'first_users'=>$first_users));
                // echo CJSON::encode($department_result);
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
    }

    /**
    *活动报名
    **/
    public function actionActivity()
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－活动报名";
            //自己的小组 小组预算记录 ->teamBudget
            if($team = InterestTeam::model()->find("admin=:user_id",array(':user_id'=>$this->user->user_id)))
            {
                //自己举办的活动
                $own_activity = InterestTeamActivity::model()->find('team_id = :team_id and (status=:enroll or status =:hold)',array(':team_id'=>$team->id, ':enroll'=>'enroll',':hold'=>'hold'));
            }
            else
            {
                $own_activity = array();
            }
            //参加的活动
            if(!empty($own_activity))
            {

                $joins = InterestTeamJoin::model()->findAll('activity_id != :activity_id and user_id= :user_id and status=:status' ,array(':activity_id'=>$own_activity->id,':user_id'=>$this->user->user_id, ':status'=>'enroll'));
            }
            else
            {
                $joins = InterestTeamJoin::model()->findAll('user_id= :user_id and status=:status' ,array(':user_id'=>$this->user->user_id, ':status'=>'enroll'));
            }
            //所有用户
            $users = Users::model()->findAll('status=:status',array(':status'=>'work'));
            //活动大厅
            $activitys = InterestTeamActivity::model()->findAll('activity_time >= :time and (status=:enroll or status =:hold)',array(':time'=>date('Y-m-d H:i:s'),':enroll'=>'enroll',':hold'=>'hold'));
            $this->render('activity', array('team'=>$team,'own_activity'=>$own_activity, 'joins'=>$joins, 'users'=>$users,'activitys'=>$activitys)); 
        }            
    }

    /**
    *加班登记
    **/
    public function actionOverTime()
    {
        if(!empty($this->user))
        {
            $this->pageTitle="OA－加班登记";
            $total = Overtime::model()->count('user_id=:user_id and status=:status',array(':user_id'=>$this->user->user_id, ':status'=>'success'));
            $start = date('Y-m-01 00:00:00');
            $end   = date('Y-m-d  H:i:s');
            $month_count = Overtime::model()->count('user_id=:user_id and status=:status and overtime_date >= :start and overtime_date <= :end',array(':user_id'=>$this->user->user_id, ':status'=>'success', ':start'=>$start, ':end'=>$end));

            $count = Overtime::model()->count('user_id=:user_id',array(':user_id'=>$this->user->user_id));
            $page = new CPagination($count);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;
            $result = Overtime::model()->findAll( array('condition'=>'user_id=:user_id','params'=>array(':user_id'=>$this->user->user_id), 'order'=>'create_time desc', 'limit'=>$limit, 'offset'=>$offset) );
            $holidays = Holiday::model()->findAll(array('condition'=>"holiday >= :day",'params'=>array(':day'=>date('Y-01-01')), 'order'=>'holiday asc'));
            $this->render('overTime',array('holidays'=>$holidays, 'total'=>$total, 'month_count'=>$month_count, 'page'=>$page,'data'=>$result));   
        }
    }

    	/**
     * Displays the login page
     */
    public function actionLogin()
    {
        $this->layout = 'blank';
        $this->pageTitle = Yii::app()->name.'--登录';
        $user = Users::__getCookie('user');
        $pwd  = Users::__getCookie('pwd');
        if(!empty($user) && !empty($pwd))
        {
            $pwd = Users::decodePwd($pwd);
            if(Users::ldapLogin($user, $pwd))
            {
                if($users = Users::model()->find('login=:login and status=:status',array(':login'=>$user, ':status'=>'work')))
                {
                    Users::updateUser($users, array('online'=>'on', 'heartbeat'=>time()));
                    Yii::app()->session['user_id'] = $users->user_id;
                    Yii::app()->session['user_name'] = $users->en_name;
                    Yii::app()->session['permission'] = $users->permission;
                    Yii::app()->session['admin'] = Users::getAdminTag($users);
                    if(empty(Yii::app()->session['refer']))
                    {
                        $this->redirect('/user/index');
                    }
                    else
                    {
                        $this->redirect(Yii::app()->session['refer']);
                    }   
                }
            }
        } 
        $this->render('login');
    }

    /**
     *费用申请页面
     *@url /user/subscribe
     *@param string $page_tag 如果为apply就是正常的申请  如果为book就是申请图书
     *@return string $tag ENUM('admin','leader','common') ceo及人数 ， 部门负责人， 普通员工
     *@return array  $budgets array('office'=>'999','test'=>'121221',....)
     *@return object $page
     *@return array  $data
     */
    public function actionSubscribe($page_tag='apply', $search="", $type="")
    {
        if(!empty($this->user))
        {
            //判断显示申购类型的标记
            $tag = 'common';
            if($this->user->user_id == Users::getCeo()->user_id || $this->user->department_id == Department::adminDepartment()->department_id)
            {
                $tag = 'admin';
            }
            elseif(Department::model()->find('admin=:admin',array(':admin'=>$this->user->user_id)))
            {
                $tag = 'leader';
            }
            //求出该部门本年度的各项剩余预算
            $budgets = array();
            if($_budgets = Budget::model()->findAll("department_id=:id and year=:year",array(':id'=>$this->user->department_id, ':year'=>date('Y'))))
            {
                foreach($_budgets as $_budget)
                {
                    $budgets[$_budget->type] = $_budget->total - $_budget->cost;
                }
            }
            //查历史记录
            $this->pageTitle="OA－申购";
            ///查看可以报销的申请
            $bank = BankCard::model()->find(array('condition'=>'user_id=:user_id','params'=>array(':user_id'=>$this->user->user_id),'order'=>'create_time desc'));
            //获取需要报销的记录
            $reimburses = GoodsApplyDetail::getTypeReimbursementApply($this->user->user_id);
            //查看报销的历史记录
            $history_count = Reimburse::model()->count('user_id=:user_id', array(':user_id'=>$this->user->user_id));
            $history_page = new CPagination($history_count);
            $history_page->pageSize = 10;
            $history_limit = $history_page->pageSize;
            $history_offset = $history_page->currentPage * $history_page->pageSize ;
            $history_reimburses = Reimburse::model()->findAll( array('condition'=>'user_id=:user_id','params'=>array(':user_id'=>$this->user->user_id), 'order'=>'create_time desc', 'limit'=>$history_limit, 'offset'=>$history_offset) );
            $history_reimburses_apply = array();
            foreach($history_reimburses as $hrow)
            {
              foreach($hrow->details as $hrow_detail)
              {
                $apply_detail_id = $hrow_detail['apply_detail_id'];
                $apply_detail = GoodsApplyDetail::model()->findByPk($apply_detail_id);
                $history_reimburses_apply[$apply_detail_id] = $apply_detail['create_time'];
              }
            }
            $categorys = array('office'=>'办公费','welfare'=>'福利费','travel'=>'差旅费','entertain'=>'业务招待费','hydropower'=>'水电费',
              'intermediary'=>'中介费','rental'=>'租赁费','test'=>'测试费','outsourcing'=>'外包费',
              'property'=>'物管费','repair'=>'修缮费','other'=>'其他'
            );

            $admin_id = Users::getAdminId()->user_id;
            $reimburses_add_info = array();
            foreach ($history_reimburses as $key => $value) {
                $reimburses_add_info[$key]['d_name'] = Users::model()->findByPk($value['user_id'])->department->name;
                $reimburses_add_info[$key]['cn_name'] = Users::model()->findByPk($value['user_id'])->cn_name;
            }

            //费用申请清单
            $db = Yii::app()->db;
            $sql_count = "SELECT count(1) as total FROM goods_apply_detail LEFT JOIN goods_apply on goods_apply_detail.apply_id=goods_apply.id WHERE user_id = " . $this->user->user_id;
            if(  strlen($search) >= 2) {
                $search = mysql_escape_string($search);
                $sql_count .= ' and (goods_apply_detail.name like "%'. $search .'%" or goods_apply_detail.reason like "%'. $search .'%" or goods_apply_detail.price="'. $search .'" or goods_apply_detail.type="'. $search.'" )';
            }
            if( strlen($type) >=2 ) {
                $type = mysql_escape_string($type);
                $sql_count .= ' and goods_apply_detail.category ="'.$type.'"';
            }
            $command = $db->createCommand($sql_count);
            $count = $command->queryAll();

            $page = new CPagination((int)$count[0]['total']);
            $page->pageSize = 10;
            $limit = $page->pageSize;
            $offset = $page->currentPage * $page->pageSize ;

            $sql = "SELECT goods_apply_detail.*, goods_apply.user_id, goods_apply.status FROM goods_apply_detail LEFT JOIN goods_apply on goods_apply_detail.apply_id=goods_apply.id WHERE user_id = ".$this->user->user_id ;
            if(  strlen($search) >= 2) {
                $search = mysql_escape_string($search);
                $sql .= ' and (goods_apply_detail.name like "%'. $search .'%" or goods_apply_detail.reason like "%'. $search .'%" or goods_apply_detail.price="'. $search .'" or goods_apply_detail.category="'. $search.'" )';
            }
            if( strlen($type) >=2 ) {
                $type = mysql_escape_string($type);
                $sql .= ' and goods_apply_detail.category ="'.$type.'"';
            }
            $sql .= " ORDER BY create_time DESC LIMIT ".$limit." OFFSET ".$offset;

            $command = $db->createCommand($sql);
            $goods_apply_list = $command->queryAll();

            $fee_div_tpl = FeeDivisionTpl::model()->findAll('enable=:t_enable', array(':t_enable'=>'yes'));
            $project_list = Project::model()->findAll('enable=:t_enable', array(':t_enable'=>'yes'));
            $view_params = array(
                'bank'=>$bank,'tag'=>$tag, 'page'=>$page,'page_tag'=>$page_tag, 'budgets'=>$budgets, 
                'categorys'=>$categorys,'reimburses'=>$reimburses,'history_page'=>$history_page,
                'history_reimburses'=>$history_reimburses, 'history_reimburses_apply'=>$history_reimburses_apply,
                'admin_id' => $admin_id, 'reimburses_add_info' => $reimburses_add_info,
                'goods_apply_list' => $goods_apply_list,
                'search_condition' => $search, 'fee_div_tpl'=>$fee_div_tpl,
                'type'=>$type, 'project_list'=>$project_list
            );
            $this->render('subscribe', $view_params);
        }
    }

    /**
    *我的申购记录
    **/
    public function actionSubscribeDetail($id)
    {
        if(!empty($this->user))
        {
            if(!$apply = GoodsApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            elseif ( $apply->status=='cancle' ) {
                throw new CHttpException(404, '申请者已撤回申请单');
            }
            $this->pageTitle = "OA－申购详情";
            $this->breadcrumbs = array('申购'=>'/oa/subscribeRecord','申购详情');
            $procedure = GoodsApply::procedure($apply);

            $add_info = array();
            $details = $apply->details;
            foreach ($details as $key => $value) {
                $content = "";
                if( $tmp_div_p = CJSON::decode($value->fee_div_p, true) ) {
                    foreach ($tmp_div_p as $key1 => $value1) {
                        $content .= Project::model()->findByPk($key1)->name .':'. $value1 .'%<br>';
                    }
                }
                $add_info[$key] = $content;
            }
			$this->render('subscribeDetail', array(
                'apply'=>$apply,'procedure'=>$procedure,
                'add_info'=>$add_info,
            ));
            // echo CJSON::encode($add_info);
        }
    }

    /*
    *费用报销
    */
    public function actionReimburse() {
        $this->pageTitle="OA－费用报销";
        $this->render('reimburse');
    }

    /**
     *公告
     */
    public function actionNotification()
    {
        if(!empty($this->user))
        {
            $notifys = Notification::model()->findAll(array('condition'=>'status=:status','params'=>array(':status'=>'display'),'order'=>'create_time desc'));
            $this->render('notification',array('notifys'=>$notifys));
        }
    }

    /**
     *公告详情
     *@param string $id 公告的ID
     */
    public function actionNotificationDetail($id)
    {
        if(!empty($this->user))
        {
            $notify = Notification::model()->findByPk($id);
            $this->render('notificationDetail',array('notify'=>$notify));
        }
    }

    /**
     * @ignore
     *部门加班签名
     */
    public function actionDepartmentOverTime()
    {
        if(preg_match('/^[1-9]\d*$/',Yii::app()->session['user_id']))
        {
            $this->user = Users::model()->findByPk(Yii::app()->session['user_id']);
            if(!empty($this->user))
            {
                $this->pageTitle = "OA－部门加班签名";
                $result = Overtime::model()->findAll(array('condition'=>"status =:status and head_id = :head_id", 'params'=>array(':status'=>'wait',':head_id'=>$this->user->user_id), 'group'=>'overtime_date'));
                $this->render('departmentOverTime', array('data'=>$result));
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
     * @ignore
     *处理加班（详情，负责人勾选，然后提交）
     */
    public function actionProcessOverTime($date)
    {
        if(preg_match('/^[1-9]\d*$/',Yii::app()->session['user_id']))
        {
            $this->user = Users::model()->findByPk(Yii::app()->session['user_id']);
            if(!empty($this->user) && (!empty(Yii::app()->session['admin']) || $this->user->title == "主策" ))
            {
                $this->pageTitle = "OA－加班详情";
                $result = Overtime::model()->findAll(array('condition'=>"status =:status and overtime_date = :date and head_id = :head_id", 'params'=>array(':date'=>$date, ':status'=>'wait',':head_id'=>$this->user->user_id)));
                $this->render('processOverTime',array('data'=>$result));
            }
            else
            {
                header('Location: '.Yii::app()->request->hostInfo.'/');
            }
        }
        else
        {
            header('Location: '.Yii::app()->request->hostInfo.'/');
        }
    }

    /**
     *修改
     */
    public function actionCreateAnnualLeave()
    {
        $users = Users::model()->findAll();
        foreach($users as $row)
        {
            $total = 0;
            $model = AnnualLeave::model()->find("user_id=:user_id",array(':user_id'=>$row->user_id));
            $total = $row->annualLeaveDays;
            AnnualLeave::processAnnualLeave($model, array('total'=>$total, 'refresh_time'=>date('Y-m-d H:i:s')));
        }
    }

    /**
     *加班申请详情
     *@url /oa/overtimeDetail/id/$id
     *加班时间 ：$overtime->countWorkTime
     *进度条  根据$overtime->status来判断
     *@日志   根据$overtime->logs
     */
    public function actionOvertimeDetail($id)
    {
        $this->pageTitle = "OA－加班申请详情";
        $this->breadcrumbs = array('加班管理'=>'/oa/overTimeList','部门加班管理');
        if(!$overtime = Overtime::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        if($overtime->type == 'normal')
        {
            //throw new CHttpException(404, '找不到此页面');
            $head = array();
        }
        else
        {
            $head = Users::model()->findByPk($overtime->head_id);
        }
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
        // echo CJSON::encode($overtime);
        $this->render('overtimeDetail', array('data'=>$overtime, 'head'=>$head, 'procedure'=>$procedure));
        
    }

    /**
     *报销支付审批单
     */
    public function actionPrintReimburse($id='')
    {
        $this->layout='blank';
        $this->pageTitle = "OA－报销支付审批单";
        if(!$data = Reimburse::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        elseif ($data->status == 'wait') {
            throw new CHttpException(404, '请将报销单补充完整');
        }

        $department_name = Users::model()->findByPk($data['user_id'])->department->name;
        $project_list = Project::model()->findAll();
        // $user = $data->user->cn_name;
        $this->render('printReimburse',array(
            'data'=>$data, 'department_name'=>$department_name,
            'project_list' => CJSON::encode($project_list),
        ));
        // echo $data->fee_div;
    }

    /**
     *打印报销清单页面
     */
    public function actionPrintReimburseList($id='')
    {
        $this->layout='blank';
        $this->pageTitle = "OA－报销支付审批单";
        if(!$data = Reimburse::model()->findByPk($id))
        {
            throw new CHttpException(404, '找不到此页面');
        }
        // 根据apply_detail_id去找申请日期
        $data_apply = array();
        foreach($data->details as $row){
          $apply_detail_id = $row['apply_detail_id'];
          $data_apply_detail = GoodsApplyDetail::model()->findByPk($apply_detail_id);
          $data_apply[$apply_detail_id] = $data_apply_detail['create_time'];
        }
        $user_name = Users::model()->findByPk($data['user_id'])->cn_name;

        $this->render('printReimburseList',array('data'=>$data, 'data_apply'=>$data_apply, 'user_name'=>$user_name));
    }

    /**
     *批请假单
     *@param string $id 请假记录ID
     */
    public function actionProcessLeave($id)
    {
        if(!empty($this->user))
        {
            try{
                $this->breadcrumbs = array('消息列表'=>'/oa/msgs','请假详情');
                $this->pageTitle="OA－请假批准";
                //请假单信息
                $leave_model = Leave::model()->findByPK($id);
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
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            } 
        }
        
    }

    /**
     *招聘申请详情
     *@param string $id 招聘记录的ID
     */
    public function actionRecruitApplyDetail($id)
    {
            $this->pageTitle = "OA－招聘申请详情";
            //$this->breadcrumbs = array('招聘管理'=>'/oa/recruitApplyRecord','招聘申请详情');
            if(!$apply = RecruitApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            //人事部和CEO和招聘发起人，和该招聘的所有面试官可以进入
            $permissions = array(Users::getHr()->user_id, Users::getAdminId()->user_id, Users::getCeo()->user_id, $apply->user_id);
            if($apply->resumes)
            {
                foreach($apply->resumes as $_resume)
                {
                    $permissions[] = $_resume->interviewer;
                }
            }
            if(!in_array(Yii::app()->session['user_id'] , $permissions))
            {
                header("Content-type: text/html; charset=utf-8");
                echo "你没有权限查看此页面，请点击 <a href='".Yii::app()->request->urlReferrer."'>返回上一页</a>";
                Yii::app()->end();
            }
            //如果是HR 就为true
            $tag = false;
            /*if(!empty(Yii::app()->session['user_id']) && Yii::app()->session['user_id'] == Users::getHr()->user_id)
            {
                $tag = true;
            }*/

            $users = Users::model()->findAll('status=:status', array(':status'=>'work'));
            $resumes = Resume::model()->findAll('apply_id=:id and interviewer=:interviewer', array(':id'=>$id,':interviewer'=>Yii::app()->session['user_id']));

            $resume_interviewer = array();
            foreach ($resumes as $key => $value) {
                $resume_interviewer[$key] = Users::model()->findByPk($value->interviewer);
            }

            $view_params = array(
                'users'=>$users, 'procedure'=>$apply->procedure, 'apply'=>$apply,
                'resumes'=>$resumes,  'user'=>$this->user,'tag'=>$tag,
            );
            $this->render('recruitApplyDetail', $view_params );
    }

    /**
     *面试评估详情页
     *@param string $id 简历ID
     */
    // public function actionInterviewEvaluateDetail($id)
    // {
    //     if(!$resume = Resume::model()->findByPk($id))
    //     {
    //         throw new CHttpException(404, '找不到此页面');
    //     }
    //     if(Yii::app()->session['user_id'] != $resume->interviewer)
    //     {
    //         header("Content-type: text/html; charset=utf-8");
    //         echo "你没有权限查看此页面，请点击 <a href='".Yii::app()->request->urlReferrer."'>返回上一页</a>";
    //         Yii::app()->end();
    //     }
    //     $procedure = empty($resume->assessment)?array():Assessment::procedure($resume->assessment); 
    //     $tag = false;

    //     $this->pageTitle = "OA－面试评估表";
    //     $this->render('interviewEvaluateDetail', array('resume'=>$resume, 'procedure'=>$procedure, 'tag'=>$tag));
    // }

    /**
     *工作交接表
     *@param string $id 为离职申请ID
     */
    public function actionDeliverWorkDetail($id)
    {
        if(!empty($this->user))
        {
            $this->pageTitle = "OA－工作交接详情";
            if(!$apply = QuitApply::model()->findByPk($id))
            {
                throw new CHttpException(404, '找不到此页面');
            }
            if($apply->status != 'success')
            {
                throw new CHttpException(404, '找不到此页面');
            }
            if(!in_array($this->user->user_id , array( $apply->handover_user_id, $apply->user_id)))
            {
                throw new CHttpException(404, '找不到此页面');
            }

            $handover_user = Users::model()->findByPk($apply->handover_user_id);
            $commissioner =  Users::getCcommissioner();
            $admin_user = Users::getAdminId();
            $hr_user = Users::getHr();
            $web_user = Users::getWebAdmin();
            $work = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'work'));
            $admin = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'admin'));
            $hr = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'hr'));
            $it = QuitHandover::model()->find('apply_id=:id and type=:type', array(':id'=>$apply->id,':type'=>'it'));
            $supervision_info = isset($work->supervision_id)? Users::model()->findByPk($work->supervision_id) : "";

            $admin_sid = isset($admin->supervision_id) ? Users::model()->findByPk($admin->supervision_id)->user_id : "";
            $hr_sid = isset($hr->supervision_id) ? Users::model()->findByPk($hr->supervision_id)->user_id : "";

            $it_sid = "";
            if(!empty($it->supervision_id))
                $it_sid = Users::model()->findByPk($it->supervision_id)->user_id;

            $admin_details_info = isset($admin->id) ? QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$admin->id)) : "";
            $hr_details_info =  isset($hr->id) ? QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$hr->id)) : "";
            $it_details_info = isset($it->id) ? QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$it->id)) : "";

            $work_details = array();
            if(!empty($work))
              $work_details = QuitHandoverDetail::model()->findAll('handover_id=:id',array(':id'=>$work->id));

            $view_params = array('apply'=>$apply ,'user'=>$this->user,
                'handover_user_info'=>$handover_user,
                'commissioner_info' => $commissioner,
                'admin_user_info' => $admin_user,
                'hr_user_info'=>$hr_user,
                'web_user_info'=>$web_user,
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

    
    /*
    *个人电脑管理界面
    */
    public function actionUserpc() {
        if(!empty($this->user))
        {
            $ch  = curl_init();
            curl_setopt( $ch, CURLOPT_URL, "http://192.168.200.31/ip_mac.json");
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
            $output = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($output, true);
            $login_name = $this->user['login'];
            $result_pc = array();
            foreach ($result as $row) {
                if(strpos($row['name'], $login_name) !== false)
                    $result_pc[] = $row;
            }
            $this->render('userpc', array('user_pc'=>json_encode($result_pc)) );
        }
    }

    public function actionEditor($dir_id=0, $file_type="") {
      $this->pageTitle = "OA－文档库";
      if(!empty($this->user)) {
            $editor_list = Editor::model()->findAll('status=:status and display=:display and dir_id=:parents',
                                            array(':status'=>'success', ':display'=>'yes', 'parents'=>$dir_id ));

            $editor_list_all = Editor::model()->findAll('status=:status and display=:display',
                                            array(':status'=>'success', ':display'=>'yes'));

            $editor_wait = Editor::model()->findAll('owner_id=:owner_id and display=:display and status=:status' ,
                            array(':owner_id'=>Yii::app()->session['user_id'], ':display'=>'yes', ':status' => 'wait', ));

            $c_editor_list = Editor::getCoEditor(Yii::app()->session['user_id']);
            $dir_list = EditorDir::model()->findAll('status=:status and parent_id=:parents', 
                      array(':status'=>'enable','parents'=>$dir_id));

            $dir_list_all = EditorDir::model()->findAll('status=:status', array(':status'=>'enable'));

            $users = Users::model()->findAll("status =:status", array(':status'=>'work'));

            $parent_dir = EditorDir::findParentDir($dir_id);

            foreach ($editor_wait as $key => $value) {
                $editor_wait[$key]['c_editor'] = CJSON::decode($editor_wait[$key]['c_editor']);          //将c_editor由json转换为数组
            }
            foreach ($c_editor_list as $key1 => $value1) {
                $c_editor_list[$key1]['c_editor'] = CJSON::decode($c_editor_list[$key1]['c_editor']);          //将c_editor由json转换为数组
            }
        }
        if ($file_type=="c_editor") {
          $editor_list = $c_editor_list;
          $parent_dir = array();
        }
        else if ($file_type=="w_editor") {
          $editor_list = $editor_wait;
          $parent_dir = array();
        }

        $c_editor_list = CJSON::encode($c_editor_list);
        $editor_list = CJSON::encode($editor_list);               //当前文件夹下的文件
        $editor_list_all = CJSON::encode($editor_list_all);       //所有文件
        $parent_dir = CJSON::encode($parent_dir);                 //父文件夹
        $editor_wait = CJSON::encode($editor_wait);               //
        $dir_list = CJSON::encode($dir_list);
        $dir_list_all = CJSON::encode($dir_list_all);
        $user_list = CJSON::encode($users);
  
        $user_id = Yii::app()->session['user_id'];
        $is_admin = 'no';
        if (($user_id == EditorRoles::getApproverId()) || ($user_id == EditorRoles::getAdminId())) {
            $is_admin = 'yes';
        }

        $this->render('editor', array('dir_list'=>$dir_list, 'dir_list_all'=>$dir_list_all, 'parent_dir'=>$parent_dir, 'user_list'=>$user_list, 
            'editor_list_all'=>$editor_list_all, 'parent_dir'=>$parent_dir, 'editor_list'=>$editor_list, 'editor_wait'=>$editor_wait , 
            'c_editor_list'=>$c_editor_list, 'user_id'=>$user_id, 'is_admin'=>$is_admin ));
    }

    //查看文档内容
    public function actionViewEditorContent($id) {
      $this->pageTitle = "OA－文档库";
      if (!empty($this->user) || !empty($id) ) {
        if ($editor = Editor::model()->findByPk($id) )
        {
            $users = array('cn_name'=>"测试");
            $users1 = Users::model()->findAll("status =:status", array(':status'=>'work'));
            $user_list = CJSON::encode($users1);
            if ($editor['status']=='wait')
                $dir = Yii::app()->params['editorTmpFilePath'];
            else
                $dir = Yii::app()->params['editorSuccessFilePath'];
            $filepath = $dir . $editor['real_file_name'];

            $content = file_get_contents( $filepath);

            $dir_list = EditorDir::model()->findAll('status=:status', array(':status'=>'enable'));
            $dir_list = CJSON::encode($dir_list);
            $editor_arr = $editor->attributes;
            $editor_arr['content'] = $content;
            $editor_js = CJSON::encode($editor_arr);
            $this->render('editorView',array('editor_js'=>$editor_js, 'user_info'=>CJSON::encode($users),'user_list'=>$user_list, 'dir_list'=>$dir_list ) );
        }
      }
    }

    // 编辑文件内容
    public function actionEditEditorContent($id) {
        $this->pageTitle = "OA－文档库";
        $this->layout = false;
        $editor_id = $id;
        if (!empty($this->user) || !empty($id) ) {
          if (!$editor = Editor::model()->findByPk($editor_id) ) {
            throw new CHttpException(404, '找不到此页面');
          }
          else if ( $editor['approve_user_id'] != 0 ) {           //正在审核的文档不能被编辑
              header("Content-type: text/html; charset=utf-8");
              echo "正在审核的文档不能被编辑";
          }
          else if ( $editor['display'] != 'yes' ) {           //正在审核的文档不能被编辑
              header("Content-type: text/html; charset=utf-8");
              echo "该文档已被删除";
          }
          else if ( !Editor::checkEditAuth($editor,Yii::app()->session['user_id']) ) {
              header("Content-type: text/html; charset=utf-8");
              echo "没有权限编辑";
          }
          else if ( !Editor::getFileLock($editor, Yii::app()->session['user_id'] ) ) {
              header("Content-type: text/html; charset=utf-8");
              echo "文件被锁定";
          }
          //文件创建者可以编辑已发布和未发布的文档，协同编辑者只能编辑未发布文档
          else if( ($editor['owner_id']==Yii::app()->session['user_id']) || ($editor['status']=='wait') ) { 
              if ( ($editor['status']=='wait') )
                  $dir = Yii::app()->params['editorTmpFilePath'];
              else
                  $dir = Yii::app()->params['editorSuccessFilePath'];

              $filepath = $dir . $editor['real_file_name'];
              if (file_exists($filepath)) {
                  $content = file_get_contents( $filepath);
                  $editor_arr = $editor->attributes;
                  $editor_arr['content'] = $content;
                  $editor_js = CJSON::encode($editor_arr);
                  $dir_list = EditorDir::model()->findAll('status=:status', array(':status'=>'enable'));
                  $dir_list = CJSON::encode($dir_list);
                  $this->render('editorEdit',array('editor_js'=>$editor_js,'dir_list'=>$dir_list));
              }
          }
          else {
            header("Content-type: text/html; charset=utf-8"); 
            echo "无法编辑该文档";
          }
        }
    }
    
    //新建文件
    public function actionNewEditor() {
        $this->pageTitle = "OA－文档库";
        $this->layout = false;
        if(!empty($this->user)) {
          $user_id = $this->user->user_id;
          if (EditorRoles::model()->find('user_id=:user_id',array(':user_id'=>$user_id))) {
            $user_list = Users::model()->findAll('status=:status', array(':status'=>'work'));
            $user_list_js = CJSON::encode($user_list);
            $this->render('editorNew',array('user_list_js'=>$user_list_js));
          }
          else {
            header("Content-type: text/html; charset=utf-8");
            echo '没有权限新建文件';
          }
        }
    }
    // public function actionVote() {
    //   if(!empty($this->user)) {
    //     $this->render('voteView',array());
    //   }
    // }

    public function actionMsg2($leave='', $notice='', $type='leaveRecord')
    {
        if(!empty($this->user))
        {
            try
            {
                $this->pageTitle="OA－请假申请详情";
                
                if(!empty($notice))
                {
                    $notice = Notice::model()->findByPk($notice);
                    Notice::updateNotice($notice, array('status' => 'read'));
                }

                if(empty($leave) || !$leave = Leave::model()->findByPk($leave))
                {
                    throw new CHttpException(404, '找不到此页面');
                }
                $logs = $leave->allLogs; 
                $procedure = Leave::procedure($leave);              
                $this->render('msg2', array('notice'=>$notice, 'leave'=>$leave,'logs'=>$logs,'procedure'=>$procedure ));
            }
            catch(Exception $e)
            {
                throw new CHttpException(404, '找不到此页面');
            }
        }
       
    }

}
