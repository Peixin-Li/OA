<style type="text/css">
    button {
        margin-left: 5px;
    }
    .div_page {
        text-align: center;
        margin-top: 10px;
        margin-bottom: 20px;
    }
</style>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/menu.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<div class="bor-1-ddd">
    <div class="m0 p0 bg-fa pd8">
        <label class="ml10">请选择日期：</label>
        <input class="form-control w130 inline" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1);" id="search-input" oninput="console.log(23);">
        <button class="btn btn-success m0 ml10 mt-2" onclick="changeDate();">查询</button>
        <button class="btn btn-success m0 ml10 fr mr50" onclick="payAll('success');">合并付款</button>
        <button class="btn btn-success m0 ml10 fr" onclick="payAll('submitted');">取消付款</button>
    </div>
    <div class="pd20 center" style="padding-bottom: 5px;">
        <table class="table table-bordered" id="data-table">
            <tbody>
                <tr class="bg-fa">
                    <th class="w50 center ">
                        <input  type="checkbox" onclick="changeAllChecked(this)" />
                    </th>
                    <th class="w50 center ">ID</th>
                    <th class="w50 center">报销金额</th>
                    <th class="w80 center">收款人</th>
                    <th class="w80 center">收款账号</th>
                    <th class="w80 center">报销日期</th>
                    <th class="w50 center">当前状态</th>
                    <th class="w80 center">操作</th>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="div_page">
        <?php 
            $this->widget('CLinkPager',array(
                'firstPageLabel'=>'首页',
                'lastPageLabel'=>'末页',
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'pages'=>$page,
                'maxButtonCount'=>10,
              )
            );
        ?>
     </div>
</div>

<script type="text/javascript">
var reimburse_list = <?php echo empty($reimburse_list)? "" : $reimburse_list ?>;
var current_date = '<?php echo empty($current_date)? "" : $current_date ?>';

$(document).ready(function(){
    initTable(reimburse_list);
    $("#datepicker").datepicker();
    $("#search-input").val(current_date);
});

function initTable(data) {
    var status, content;
    var status_arr = {'wait':'未提交', 'success': '已付款', 'submitted':'待付款'};
    $.each(data, function(key, value){
        if(value['status'] == null )
            status = "";
        else {
            status = status_arr[value['status']];
        }
        content = '<tr class="bg-fa">' + 
            '<td class="w50 center"><input name="pay-reimburse" type="checkbox" value="'+ value['id'] +'" /></td>' +
            '<td class="w50 center">'+ value['id'] +'</td>' +
            '<td class="w50 center">'+ value['total'] +'</td>' +
            '<td class="w80 center">'+ value['payee'] +'</td>' +
            '<td class="w80 center">'+ value['bank_info'] +'</td>' + 
            '<td class="w80 center">'+ value['create_time'] +'</td>' +
            '<td class="w50 center">'+ status +'</td>' +
            '<td class="w80 center">';
        if(value['status'] == 'submitted') {
            content += '<button class="btn btn-success fl" onclick="changeStatus(this,\'success\')" style="white-space: normal;">已付款</button>';
        }
        else if( value['status'] == 'success' ) {
            content += '<button class="btn btn-success fr" onclick="changeStatus(this, \'submitted\')" style="white-space: normal;">取消付款</button>';
        }
        content += '</td></tr>'
        $('#data-table').append(content);
    });
}

//修改报销单状态
function changeStatus(obj, action) {
    var id = $(obj).parent().parent().children().eq(1).text();
    $.ajax({
        'url': '/ajax/reimburseStatus',
        'type' : 'post',
        'dataType' : 'json',
        'data': {'id':id, 'action': action},
        'success' : function(result) {
            if(result.code==0){
                showHint("提示信息", "操作成功");
                setTimeout(function(){location.reload()}, 1200);
            }
            else {
                showHint("提示信息", "操作失败");
            }
        },
        'error' : function(arg1, arg2, arg3) {
            showHint("提示信息", arg3);
        }
    });
}

//修改时间
function changeDate() {
    var date_time = $("#search-input").val();
    location.href = '/account/index/start_time/' + date_time;
}

function payAll(action) {
    var checked_item = $('[name=pay-reimburse]');
    var checked_list=[];
    var send_count=0;
    var items_id;
    $.each(checked_item ,function(key,obj){
        if(obj.checked) {
            itmes_id = obj.value;
            checked_list.push(itmes_id);
        }
    });
    if(checked_list.length <=0 ){
        showHint('提示信息', '未选择任何报销单');
    }
    else {
        $.each(checked_list, function(key, id){
            $.ajax({
                'url': '/ajax/reimburseStatus',
                'type' : 'post',
                'dataType' : 'json',
                'data': {'id':id, 'action': action},
                'success' : function(result) {
                    if(result.code==0){
                        send_count = send_count + 1;
                        if(send_count==checked_list.length) {
                            showHint('提示信息', '操作成功');
                            setTimeout(function(){location.reload();}, 1200);
                        }
                    }
                }
            });
        });
    }
}

//全选、取消全选
function changeAllChecked(this_select) {
    var checked_item = $('[name=pay-reimburse]');
    $.each(checked_item ,function(key,obj){
        obj.checked = this_select.checked;
    });
}
</script>