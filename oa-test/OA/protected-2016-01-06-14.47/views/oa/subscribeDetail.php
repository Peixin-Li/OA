<?php
echo "<script type='text/javascript'>";
echo "console.log('subcribeDetail');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 标题 -->
  	<h4 class="pd10 m0 b33 bor-1-ddd">申请详情</h4>

    <div class="bor-l-1-ddd bor-r-1-ddd">
      <ul class="nav nav-justified">
        <?php if(!empty($procedure)):?>
          <li class="bg-66 flow-li">
              <h4 class="white m0 mt5 center">1.提交申请</h4>
              <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
          </li>
        <?php else: ?>
          <li class="bg-66">
              <h4 class="white m0 mt5 center">申请成功</h4>
              <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
          </li>
        <?php endif;?>
          <?php 
            if(!empty($procedure)){
              $no_num = 2;
              $count = count($procedure);
              $last_status = "";
              foreach($procedure as $row){
                if($row[1] == "agree"){
              echo "<li class='bg-66 flow-li'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}审批</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
                }else if($row[1] == "reject" || $row[1] == "delay"){
                  echo "<li class='flow-li-red bg-99'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}审批</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
                }else{
                  echo "<li><h4 class='m0 mt5 center'>{$no_num}.{$row[0]}审批</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
                }
                $last_status = $row[1];
                $no_num++;
              }
              if($last_status == "agree"){
                echo "<li class='bg-66'><h4 class='white m0 mt5 center'>{$no_num}.申请结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
              }else if($last_status == "reject"  || $row[1] == "delay"){
                echo "<li class='bg-99'><h4 class='white m0 mt5 center'>{$no_num}.申请结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
              }else{
                echo "<li><h4 class='m0 mt5 center'>{$no_num}.申请结果</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
              }
            }
          ?>
      </ul>
  </div>
    <!-- 申请表 -->
    <table class="table table-bordered m0" >
  		<tr>
  			<th class="w130 center">填表日期</th>
        <td class="hidden" id="apply-id"><?php echo $apply->id; ?></td>
        <td><?php echo $apply->create_time; ?></td>
  		</tr>
  		<tr>
  			<th class="w130 center">申请人</th>
  			<td><?php echo $apply->user->cn_name; ?></td>
  		</tr>
  		<tr>
  			<th class="w130 center">所属部门</th>
  			<td><?php echo $apply->user->department->name; ?></td>
  		</tr>
  		<tr>
  			<th class="w130 center">职位</th>
  			<td><?php echo $apply->user->title; ?></td>
  		</tr>
  		<tr>
  			<th class="w130 center">申请内容</th>
  			<td>
            <?php if($details = $apply->details): ?>
  				<table class="center table m0"  id="detail-table">
  					<thead>
  						<tr>
                <th class="center w200">类型</th>
                <th class="center w130">名称</th>
                <th class="center w130 hidden">数量/单位</th>
                <th class="center w130 hidden">预计单价</th>
                <th class="center w100">价格(元)</th>
                <th class="center">费用分摊方式</th>
                <th class="center w100 hidden">申请方式</th>
                <th class="center w100 hidden">使用时间</th>
                <th class="center w130 hidden">参考链接</th>
                <th class="center w150">申请原因</th>
              </tr>
  					</thead>
  					<tbody>
              <?php $attachment_path = ""; foreach($details as $key=>$row): ?>
  						<tr>
  							<td>
                  <?php 
                    if(!empty($row->category)){
                      switch($row->category){
                        case "office":{echo "办公费";break;}
                        case "welfare":{echo "福利费";break;}
                        case "travel":{echo "差旅费";break;}
                        case "entertain":{echo "业务招待费";break;}
                        case "hydropower":{echo "水电费";break;}
                        case "intermediary":{echo "中介费";break;}
                        case "rental":{echo "租赁费";break;}
                        case "test":{echo "测试费";break;}
                        case "outsourcing":{echo "外包费";break;}
                        case "property":{echo "物管费";break;}
                        case "repair":{echo "修缮费";break;}
                        case "other":{echo "其他";break;}
                      }
                    }
                    
                  ?>
                  <?php if(!empty($row->type)): ?>
                  (
                  <?php 
                    switch($row->type){
                      case "fixed":{echo '固定资产';break;}
                      case "benefit":{echo '员工福利';break;}
                      case "office":{echo '办公费';break;}
                      case "travel":{echo '差旅费';break;}
                      case "management":{echo '物业费';break;}
                      case "project":{echo '项目费';break;}
                      case "propaganda":{echo '媒介宣传费';break;}
                      case "recruit":{echo '招聘费用';break;}
                      case "book":{echo '图书';break;}
                      default: {echo $row->type;break;}
                    }
                    
                  ?>)
                  <?php endif; ?>
                  <div class="bold" style="font-size:10px;">年度剩余预算：<?php echo $add_info[$key]; ?></div>
                </td>
                <td><?php echo $row->name; ?></td>
  							<td class="quantity-td hidden"><?php echo $row->quantity; ?></td>
  							<td class="price-td hidden"><?php echo $row->price; ?></td>
                <td><?php echo (float)$row->quantity * $row->price; ?></td>
                <td class="center">
                    <a data-toggle="tooltip" title='<h5><?php echo $toolstip_info[$key] ?></h5>' > <?php echo $row->fee_div_name ?> </a>
                </td>
                <td class="hidden"><?php echo empty($row->buy_way) ? '' : $row['buy_way']; ?></td>
                <td class="hidden"><?php echo (empty($row->use_time) || $row->use_time=="0000-00-00") ? '' : $row['use_time']; ?></td>
                <td class="hidden"><?php if(!empty($row->url) && $row->url != 'http://'): ?><a href="<?php echo $row->url; ?>"><?php echo $row->url; ?></a><?php endif; ?></td>
  							<td><?php echo $row->reason; ?></td>
  						</tr>
                <?php $attachment_path = empty($row->path) ? '': $row->path; ?>
  					    <?php endforeach; ?>	
  					</tbody>
  				</table>
                <?php endif; ?>
  			</td>
  		</tr>
      <?php if(!empty($attachment_path)): ?>
      <tr>
        <th class="w130 center">附件</th>
        <td><a href="<?php echo $attachment_path; ?>" target="_blank">下载</a></td>
      </tr>
      <?php endif; ?>
      <!-- 审批的框 -->
      <?php if($logs = $apply->allLogs): ?>
      <?php foreach($logs as $log): ?>
  		<tr>
        <th class="w130 center"><?php echo $log->user->department->name; ?>审批</th>
            <td>
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php else: ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;"><?php echo $apply->reject_reason; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name; ?></span></h5>
                        <?php endif; ?>
                        <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d',strtotime($log->create_time)); ?></span></h5>
                    </div>
                </div>
            </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
        
        <?php if($apply->next == $this->user->user_id): ?>
        <?php if(!empty($this->user) && $this->user->user_id == $admin_id && !$excess_tag): ?>
        <tr style="display:none">
            <th class="w130 center">是否需要<br>总经理审批</th>
            <td>
              <input type="radio" name="selection" value="1" >&nbsp;<span class="mr20 pointer" onclick="$(this).prev().click();">需要</span>
              <input type="radio" name="selection" value="0" checked>&nbsp;<span class="mr20 pointer" onclick="$(this).prev().click();">不需要</span>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="w130 center">回复操作</th>
            <td>
                <button class="btn btn-success w100" id="agree">同意</button>
                <button class="btn btn-primary w100 ml20" id="reject">不同意</button>
            </td>
        </tr>
      <?php endif; ?>
  	</table>
    <!-- 右下角合计 -->
    <div class="right">
      <h2>合计：<span id="total-span">0</span>元</h2>
    </div>
</div>

<!-- 不同意按钮模态框 -->
<div id="reject-reason-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
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
$(document).ready(function(){
    var total = 0;
    $("#detail-table").find("tbody").find("tr").each(function(){
      var price = parseFloat($(this).find("td.price-td").text());
      var num = parseFloat($(this).find("td.quantity-td").text());
      total += price * num;
    });
    total = total.toFixed(2);
    $("#total-span").text(total);
    $("[data-toggle='tooltip']").tooltip({html:true});

    var pattern = /^\d+$/;
    $("#agree").click(function(){
        var id = $("#apply-id").text();
        <?php if(!empty($this->user) && $this->user->user_id == $admin_id): ?>
        var selection = $("input[name='selection']:checked").val();
        <?php else: ?>
        var selection = '0';
        <?php endif; ?>

        if(!pattern.exec(id))
        {
            showHint("提示信息","请刷新页面");
        }
        else
        {
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/agreeGoodsApply',
                data:{'id':id,'tag':selection},
                success:function(result){
                    if(result.code == 0)
                    {
                        showHint("提示信息","同意成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(result.code == -1)
                    {
                        showHint("提示信息","同意失败");
                    }
                    else if(result.code == -2)
                    {
                        showHint("提示信息","参数错误");
                    }
                    else if(result.code == -3)
                    {
                        showHint("提示信息","找不到该申请");
                    }
                    else
                    {
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
        }
    });
    $("#reject").click(function(){
        $("#reject-reason-div").css("top","30%");
        $("#reject-reason-div").modal({show:true});
        $('#agree').addClass('disabled');
        $('#reject').addClass('disabled');
    });
});

function rejectSubmit(){
    var pattern = /^\d+$/;
    var id = $("#apply-id").text();
        var reject_reason = $("#reject-input").val();

        if(!pattern.exec(id))
        {
            showHint("提示信息","请刷新页面");
        }
        else if(reject_reason == ""){
        	showHint("提示信息","请输入不同意原因");
        }
        else
        {
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/rejectGoodsApply',
                data:{'id':id,'reason':reject_reason},
                success:function(result){
                    if(result.code == 0)
                    {
                        showHint("提示信息","退回申请单成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(result.code == -1)
                    {
                        showHint("提示信息","退回申请单失败");
                    }
                    else if(result.code == -2)
                    {
                        showHint("提示信息","参数错误");
                    }
                    else if(result.code == -3)
                    {
                        showHint("提示信息","找不到该申请");
                    }
                    else
                    {
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
        }
}
</script>
