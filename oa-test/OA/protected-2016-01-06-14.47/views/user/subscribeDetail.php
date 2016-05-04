<?php
echo "<script type='text/javascript'>";
echo "console.log('subscribeDetail');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="center">
    <!-- 进度条 -->
    <div>
      <ul class="nav nav-justified bor-l-1-ddd bor-r-1-ddd">
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

  <!-- 费用申请详情表格 -->
  <table class="table table-bordered left">
    <tr>
      <th class="w130 center bg-fa">填表日期</th>
          <td><?php echo $apply->create_time; ?></td>
    </tr>
    <tr>
      <th class="w130 center bg-fa">申请人</th>
      <td><?php echo $apply->user->cn_name; ?></td>
    </tr>
    <tr>
      <th class="w130 center bg-fa">所属部门</th>
      <td><?php echo $apply->user->department->name; ?></td>
    </tr>
    <tr>
      <th class="w130 center bg-fa">职位</th>
      <td><?php echo $apply->user->title; ?></td>
    </tr>
    <tr>
      <th class="w130 center bg-fa">申请内容</th>
      <td>
          <?php if($details = $apply->details): ?>
        <table class="center table m0" style="background:transparent;" id="detail-table">
          <thead>
            <tr>
              <th class="center">类型</th>
              <th class="center">名称</th>
              <th class="center hidden">数量/单位</th>
              <th class="center hidden">预计单价</th>
              <th class="center">价格(元)</th>
              <th class="center">费用分摊方式</th>
              <!-- <th class="center">申请方式</th> -->
              <!-- <th class="center">使用时间</th> -->
              <!-- <th class="center w200">参考链接</th> -->
              <th class="center w200">申请原因</th>
            </tr>
          </thead>
          <tbody>
            <?php $attachment_path = ""; foreach($details as $key => $row): ?>
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
              </td>
              <td><?php echo $row->name; ?></td>
              <td class="quantity-td hidden"><?php echo $row->quantity; ?></td>
              <td class="price-td hidden"><?php echo $row->price; ?></td>
              <td><?php echo (float)$row->quantity * $row->price; ?></td>
              <td class="center">
                <a data-toggle="tooltip" title='<h5><?php echo $add_info[$key] ?></h5>' > <?php echo $row->fee_div_name ?> </a>
              </td>
              <!-- <td><?php //echo empty($row->buy_way) ? '' : $row['buy_way']; ?></td> -->
              <!-- <td><?php //echo (empty($row->use_time) || $row->use_time=="0000-00-00") ? '' : $row['use_time']; ?></td> -->
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
      <th class="w130 center bg-fa">附件</th>
      <td><a href="<?php echo $attachment_path; ?>" target="_blank">下载</a></td>
    </tr>
    <!-- 审批的框 -->
    <?php endif; ?>
      <?php if($logs = $apply->allLogs): ?>
      <?php foreach($logs as $log): ?>
    <tr>
      <th class="w130 center bg-fa"><?php echo $log->user->department->name; ?>审批</th>
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
  </table>
  <!-- 合计 -->
  <div class="right">
    <h2>合计：<span id="total-span">0</span>元</h2>
  </div>
  <!-- 返回按钮 -->
  <button class="btn btn-lg btn-default w100" onclick="location.href='/user/subscribe';">返回</button>
</div>

<!-- js -->
<script type="text/javascript">
  // 页面初始化
  $(document).ready(function(){
    // 计算总计
    var total = 0;
    $("#detail-table").find("tbody").find("tr").each(function(){
      var price = parseFloat($(this).find("td.price-td").text());
      var num = parseFloat($(this).find("td.quantity-td").text());
      total += price * num;
    });
    total = total.toFixed(2);
    $("#total-span").text(total);
    $("[data-toggle='tooltip']").tooltip({html:true});
  });
</script>
