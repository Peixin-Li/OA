<?php
echo "<script type='text/javascript'>";
echo "console.log('overtimeDetail');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 标题 -->
    <div class="bor-l-1-ddd bor-r-1-ddd">
        <!-- 进度 -->
        <ul class="nav nav-justified">
            <?php if(!empty($procedure)): ?>
            <li class="bg-66 flow-li">
                <h4 class="white m0 mt5 center">提交加班申请</h4>
                <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
            </li>
            <?php endif; ?>
            <?php foreach($procedure as $row) : ?>
                <?php if($row['status'] == "agree"): ?>
                    <li class="bg-66 flow-li">
                        <h4 class="m0 mt5 center white"> <?php echo $row['department'] ?> </h4>
                        <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
                    </li>
                <?php elseif($row['status'] == "reject"): ?>
                    <li class="bg-99">
                        <h4 class="m0 mt5 center white"> <?php echo $row['department'] ?> </h4>
                        <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-remove-sign"></span></div>
                    </li>
                <?php else: ?>
                    <li>
                        <h4 class="m0 mt5 center"> <?php echo $row['department'] ?> </h4>
                        <div class="center"><span class="mt5 mb10 f18px  glyphicon glyphicon-time"></span></div>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if($data->status == "success"): ?>
                <li class="bg-66">
                    <h4 class="m0 mt5 center white">加班申请结果</h4>
                    <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
                </li>
            <?php elseif($data->status == "reject"): ?>
                <li class="bg-99">
                    <h4 class="m0 mt5 center white">加班申请结果</h4>
                    <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-remove-sign"></span></div>
                </li>
            <?php else: ?>
                <li>
                    <h4 class="m0 mt5 center">加班申请结果</h4>
                    <div class="center"><span class="mt5 mb10 f18px  glyphicon glyphicon-time"></span></div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <!-- 加班申请详情 -->
    <?php if(!empty($data)): ?>
    <table  class="table table-bordered table-hover">
        <tr>
            <th class="w130 center">填写时间</th>
            <td class="w200"><?php echo $data->create_time;?></td>
            <th class="w130 center">姓名</th>
            <td class="w200"><?php echo $data->user->cn_name;?></td>
            <th class="w130 center">部门</th>
            <td class="w200"><?php echo $data->user->department->name;?></td>
        </tr>
        <tr>
            <th class="w130 center">加班时间</th>
            <td colspan="5" class="left"><?php echo ($data['type'] == "holiday") ? substr($data['start_time'], 0, 16)." 至 ".substr($data['end_time'], 0, 16) : substr($data['end_time'], 0, 16);?></td>
        </tr>
        <tr>
            <th class="w130 center">工作内容</th>
            <td colspan="5"><?php echo $data['content']; ?></td>
        </tr>
        <?php if(!empty($data->logs)): ?>
        <?php foreach($data->logs as $log): ?>
        <tr>
            <th class="w130 center"><?php echo $log->user->department->name?></th>
            <td colspan="5" class="left">
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php else: ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;"><?php echo $log->reason; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name;?></span></h5>
                        <?php endif; ?>
                        <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d',strtotime($log->create_time));?></span></h5>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <?php endif; ?>
</div>

