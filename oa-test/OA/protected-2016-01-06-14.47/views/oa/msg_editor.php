<?php
echo "<script type='text/javascript'>";
echo "console.log('msg_editor');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="w1300">
    <!-- 返回按钮 -->
    <!-- <button class="btn btn-default mt10 f18px " style="margin-bottom:10px" onclick="location.href='/user/msgs'">
        <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回
    </button> -->
    <!-- 标题 -->
  	<h4 class="pd10 m0 b33 bor-1-ddd">申请详情</h4>
		<!-- 进度 -->
    <div class="bor-l-1-ddd bor-r-1-ddd">
      <ul class="nav nav-justified">
          <li class="bg-66 flow-li">
              <h4 class="white m0 mt5 center">1.提交申请</h4>
              <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
          </li>
          <li id="editor-approve-title">
              <h4 class="m0 mt5 center">2.文档审批</h4>
              <div class="center"><span class="mt5 mb10 f18px glyphicon glyphicon-time"></span></div>
          </li>
          <li id="editor-finished-title">
              <h4 class="m0 mt5 center">3.完成</h4>
              <div class="center"><span class="mt5 mb10 f18px glyphicon glyphicon-time"></span></div>
          </li>

        </ul>
    </div>
    <!-- 文档申请详情表 -->
    <table class="table table-bordered m0" >
      <tr class="hidden">
        <td id="editor-id"></td>
      </tr>
  		<tr>
  			<th class="w130 center">填表日期</th>
        <td id="apply-time"></td>
  		</tr>
  		<tr>
  			<th class="w130 center">申请人</th>
  			<td id="apply-name"> </td>
  		</tr>
  		<tr>
  			<th class="w130 center">所属部门</th>
  			<td id="apply-department"> </td>
  		</tr>
  		<tr>
  			<th class="w130 center">职位</th>
  			<td id ='apply-user-title'> </td>
  		</tr>
  		<tr>
  			<th class="w130 center">文档标题</th>
  			<td id="editor-title"> </td>
  		</tr>
      <tr>
        <th class="w130 center">文档发布目录</th>
        <td id="editor-publish-dir-name"> </td>
      </tr>
      <tr>
          <th class="w130 center">文件内容</th>
          <td>
            <button class="btn btn-success" id="show-file-content">查看文件内容</button>
          </td>
      </tr>
      <tr id="finished-reply-tr">
          <th class="w130 center">回复操作</th>
          <td>
            <button class="btn btn-success w100" id="agree" onclick="agreePublish();">同意</button>
            <button class="btn btn-primary w100 ml20" id="reject" onclick="showRejectPublish();">不同意</button>
          </td>
      </tr>
      <tr id="approver-tr" class="hidden">
        <th class="w130 center">文档审批</th>
          <td>
            <div class="fl"> <div style="display:table-cell;" class="middle h80">
                <h5 class="w200 f15px" id="approve-result"></h5>
            </div></div>
            <div class="fr"><div style="display:table-cell;" class="middle h80">
              <h5 class="w300 center">签名：<span id="approve-user-name"></span></h5>
                <h5 class="w300 center">审批日期：<span id="approve-time"></span></h5>
            </div></div>
          </td>
      </tr>
  	</table>

</div>

<!-- 不同意模态框 -->
<div id="reject-reason-div" class="modal fade in hint bor-rad-5 w500" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">输入不同意原因</h4>
    </div>

    <div class="modal-body">
        <label>不同意原因：</label>
        <textarea type="text" class="form-control inline" id="reject-input"></textarea>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="rejectSubmit()" >提交</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
var editor_js = <?php echo $editor_info ?> ;
var editor_apply_js = <?php echo $editor_apply_js ?>

$(document).ready(function(){
    $("#guide-div").remove();
    $("#editor-id").text(editor_apply_js['apply_id']);
    $("#apply-time").text(editor_apply_js['create_time']);
    $("#apply-name").text(editor_js['apply_user']);
    $("#editor-title").text(editor_js['title']);
    $("#apply-department").text(editor_js['apply_user_dp']);
    $("#apply-user-title").text(editor_js['apply_user_title']);
    $("#editor-publish-dir-name").append(editor_js['dir_apply_name']);
    $("#show-file-content").attr("onclick","window.open('/user/ViewEditorContent/id/' + editor_js['id'] +');')" );

    if(editor_apply_js['status']=="success") {
      initSuccessApply();
    }
    else if (editor_apply_js['status']=="reject") {
      initRejectApply();
    }

});

function agreePublish() {
  apply_id = $("#editor-id").text();
  if (apply_id) {
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: '/ajax/agreePublish',
      data:{"apply_id":apply_id },
      success:function(result) {
        if(result.code == 0) {
          showHint("提示信息", "审核成功");
          setTimeout(function() {
            location.reload();
          }, 2000);
        }
        else if(result.code == -1){
          showHint("提示信息", "审核失败1");
        }
        else if(result.code == -2){
          showHint("提示信息", "审核失败2");
        }
        else if(result.code == -3){
          showHint("提示信息", "审核失败3");
        }
        else if(result.code == -4){
          showHint("提示信息", "找不到该文件");
        }
        else if(result.code == -5){
          showHint("提示信息", "申请已经撤销");
        }
      },
      error:function(arg1, arg2, arg3) {
        showHint("提示信息", arg3);
      }
      });
  }
}

function rejectSubmit() {
  apply_id = $("#editor-id").text();
  if (apply_id) {
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: '/ajax/RejectPublish',
      data:{"apply_id":apply_id },
      success:function(result) {
        if(result.code == 0) {
          showHint("提示信息", "操作成功");
          setTimeout(function() {
            location.reload();
          }, 2000);
        }
        else if(result.code == -1){
          showHint("提示信息", "操作失败1");
        }
        else if(result.code == -2){
          showHint("提示信息", "操作失败2");
        }
        else if(result.code == -3){
          showHint("提示信息", "操作失败3");
        }
        else if(result.code == -4){
          showHint("提示信息", "找不到该文件");
        }
        else if(result.code == -5){
          showHint("提示信息", "申请已经撤销");
        }
        else
          showHint("提示信息", "操作失败4");
      },
    });
  }
}

function showRejectPublish() {
    var ySet = (window.innerHeight - $("#reject-reason-div").height())/3;
    var xSet = (window.innerWidth - $("#reject-reason-div").width())/2;
    $("#reject-reason-div").css("top",ySet);
    $("#reject-reason-div").css("left",xSet);
    $("#reject-reason-div").modal({show:true});
}

function initSuccessApply() {
    $("#finished-reply-tr").remove();
    $("#editor-approve-title").addClass("bg-66 flow-li");
    $("#editor-approve-title").children().eq(0).addClass("white");
    $("#editor-approve-title").children().eq(1).children().removeClass("glyphicon-time");
    $("#editor-approve-title").children().eq(1).children().addClass("glyphicon-ok-sign white");
    $("#editor-finished-title").addClass("bg-66");
    $("#editor-finished-title").children().eq(0).addClass("white");
    $("#editor-finished-title").children().eq(1).children().removeClass("glyphicon-time");
    $("#editor-finished-title").children().eq(1).children().addClass("glyphicon-ok-sign white");
    $("#approver-tr").removeClass("hidden");
    $("#approve-user-name").text(editor_js['approve_user']);
    $("#approve-time").text(editor_apply_js['update_time']);
    $("#approve-result").text("同意");
}

function initRejectApply() {
    $("#finished-reply-tr").remove();
    $("#editor-approve-title").addClass("flow-li-red bg-99");
    $("#editor-approve-title").children().eq(0).addClass("white");
    $("#editor-approve-title").children().eq(1).children().removeClass("glyphicon-time");
    $("#editor-approve-title").children().eq(1).children().addClass("glyphicon-remove-sign white");
    $("#editor-finished-title").addClass("bg-99");
    $("#editor-finished-title").children().eq(0).addClass("white");
    $("#editor-finished-title").children().eq(1).children().removeClass("glyphicon-time");
    $("#editor-finished-title").children().eq(1).children().addClass("glyphicon-remove-sign white");
    $("#approver-tr").removeClass("hidden");
    $("#approve-user-name").text(editor_js['approve_user']);
    $("#approve-time").text(editor_apply_js['update_time']);
    $("#approve-result").text("拒绝");
}

</script>
