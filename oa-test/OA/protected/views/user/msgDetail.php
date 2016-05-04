<?php
echo "<script type='text/javascript'>";
echo "console.log('msgDetail');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 返回按钮 -->
    <div class="bor-l-1-ddd bor-r-1-ddd pb10">
        <button class="btn btn-default ml10 mt10 f18px" onclick="location.href='/user/msgs'"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回</button>
    </div>
    <div class="bor-1-ddd pd20">
        <!-- 消息详情表格 -->
        <table class="table-bordered table">
            <?php if(!empty($msg)):?>
            <tr>
                <th class="w100 center bg-fa">标题</th>
                <td><?php echo empty($msg->title) ? " ":$msg->title; ?></td>
            </tr>
            <tr>
                <th class="w100 center bg-fa">来源</th>
                <td><?php echo empty($types["{$msg->type}"]) ? " ":$types["{$msg->type}"];?></td>
            </tr>
            <tr>
                <th class="w100 center bg-fa">日期</th>
                <td><?php echo empty($msg->create_time) ? " ":$msg->create_time; ?></td>
            </tr>
            <tr>
                <th class="w100 center bg-fa">内容</th>
                <?php if(!empty($msg->content)): ?>
                    <?php if($msg->type == 'suggest'): ?>
                                <td><?php echo "{$msg->content} (发现问题的页面:<a href='{$msg->url}'>{$msg->url}</a>)";?></td>
                    <?php else: ?>
                                <td><?php echo ($msg->type == 'seal') ? "<a href='{$msg->url}/type/msgDetail' target='_blank'>{$msg->content}</a>": "<a href='{$msg->url}/type/msgDetail'>{$msg->content}</a>";?></td>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if($msg->type == 'suggest'): ?>
                                <td><?php echo "{$msg->title} (发现问题的页面:<a href='{$msg->url}'>{$msg->url}</a>)";?></td>
                    <?php else: ?>
                                <td><?php echo  ($msg->type == 'seal') ? "<a href='{$msg->url}/type/msgDetail' target='_blank'>{$msg->title}</a>" : "<a href='{$msg->url}/type/msgDetail'>{$msg->title}</a>";?></td>
                    <?php endif; ?>
                <?php endif; ?>
            </tr>
        <?php else:?>
            <tr><td class="w80 center"><?php echo '该消息不存在';?></td></tr>
        <?php endif; ?>
        </table>
    </div>
</div>
