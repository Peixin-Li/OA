<?php
/**
 *财务用户控制器
 */
class AccountController extends Controller
{
    public $layout = false;
    public function filters()
    {
        return array( 'verify', );
    }

    //定义的过滤方法
    public function FilterVerify($filterChain)
    {
        //判断什么的
        //过滤完后继续执行代码
        if( (Yii::app()->session['user_id']!=-1) || (empty( Yii::app()->session['user_id'] )) ) {
            $this->redirect('/user/login');
        }
        else {
            header('Content-Type: text/html; charset=utf-8');
            $filterChain->run();
        }
    }

    public function actionIndex($start_time="") {
        $start_time = strtotime($start_time)? strtotime($start_time) : time();
        $end_time = strtotime('+1 month', $start_time);

        $this->layout = 'account';

        $reimburse_count = Reimburse::model()->count(array(
            'condition' => 'status!=:t_status and create_time >= :t_this_time and create_time <= :t1_this_time',
            'params' => array(':t_this_time'=>date("Y-m", $start_time), ':t1_this_time'=>date("Y-m", $end_time), ':t_status'=>'wait' ),
        ));
        $page = new CPagination($reimburse_count);
        $page->pageSize = 10;
        $limit = $page->pageSize;
        $offset = $page->currentPage * $page->pageSize ;

        $reimburse_list = Reimburse::model()->findAll(array(
            'condition' => 'status!=:t_status and create_time >= :t_this_time and create_time <= :t1_this_time',
            'params' => array(':t_this_time'=>date("Y-m", $start_time), ':t1_this_time'=>date("Y-m", $end_time), ':t_status'=>'wait' ),
            'order' => 'create_time DESC',
            'limit' => $limit,
            'offset' => $offset,
        ));

        // echo CJSON::encode(date("Y-m", $start_time));
        $this->render('index', array(
            'reimburse_list'=>CJSON::encode($reimburse_list),
            'page'=>$page,
            'current_date' => date("Y-m", $start_time),
        ));
    }

}
