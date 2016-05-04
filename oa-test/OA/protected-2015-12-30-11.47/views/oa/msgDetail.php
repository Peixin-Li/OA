<?php
echo "<script type='text/javascript'>";
echo "console.log('msgDetail');";
echo "</script>";
?>

<?php
$text = $msg->url;
$text = preg_replace("/user/", "oa", $text);
$msg->url = $text;
?>
<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
    <?php if(!empty($msg) && !empty($msg->title)):?>
    <h5 class="pd10 m0 b33"><?php echo $msg->title;?></h5>
    <?php else: ?>
    <h5 class="pd10 m0 b33 ">消息详情</h5>
    <?php endif; ?>
    <!-- 消息信息 -->
    <table class="table m0">
    	<tr>
        <?php if(!empty($msg)):?>
    		<th class="w80 center">来源</th>
    		<td><?php echo empty($types["{$msg->type}"]) ? " ":$types["{$msg->type}"];?></td>
    	</tr>
    	<tr>
    		<th class="w80 center">日期</th>
    		<td><?php echo empty($msg->create_time) ? " ":$msg->create_time; ?></td>
    	</tr>
    	<tr>
    		<th class="w80 center">内容</th>
             <?php if(!empty($msg->content)): ?>
                <?php if($msg->type == 'suggest'): ?>
                            <td><?php echo "{$msg->content} (发现问题的页面:<a href='{$msg->url}'>{$msg->url}</a>)";?></td>
                <?php else: ?>
                            <td><?php echo "<a href='{$msg->url}'>{$msg->content}</a>";?></td>
                <?php endif; ?>
            <?php else: ?>
                <?php if($msg->type == 'suggest'): ?>
                            <td><?php echo "{$msg->title} (发现问题的页面:<a href='{$msg->url}'>{$msg->url}</a>)";?></td>
                <?php else: ?>
                            <td><?php echo "<a href='{$msg->url}'>{$msg->title}</a>";?></td>
                <?php endif; ?>
            <?php endif; ?>
    	</tr>
    <?php else:?>
        <td class="w80 center"><?php echo '该消息不存在';?></td>  
    <?php endif; ?>
    </table>
</div>
