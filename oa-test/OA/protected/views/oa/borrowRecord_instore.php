<?php
echo "<script type='text/javascript'>";
echo "console.log('borrowRecord_instore');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/books.css" />

<div class="bor-1-ddd">
    <!-- 标签 -->
    <h4 class="pd10 m0 b33 bor-b-1-ddd">借阅记录</h4>
    <div class="m0 p0 pd20">
        <!-- 类别标签 -->
        <ul class="nav nav-tabs" role="tablist">
        	<li role="presentation" ><a class="f18px pointer" href="/oa/borrowRecord" >已借出</a></li>
            <li role="presentation" class="active"><a class="f18px pointer" href="/oa/borrowRecord_instore" >在库</a></li>
            <li role="presentation" ><a class="f18px pointer" href="/oa/borrowRecord_returned" >已归还</a></li>
        </ul>
        <!-- 在库图书表格 -->
        <table class="table table table-striped table-bordered table-hover bor-t-none">
        	<thead>
        		<tr>
        			<th class="center w200">序号</th>
        			<th class="center">类别</th>
        			<th>书名</th>
        		</tr>
        	</thead>
        <?php
            echo '<tbody>';
            $tag = 1;
                foreach($borrowRecord_instore as $instore)
                {   
                    echo '<tr>';
                    echo "<td id='book_id' class='hidden'>{$tag}</td>";
                    $tag = $tag + 1;
                    echo "<td class='center'>".(empty($instore->serial_number)?'':$instore->serial_number)."</td>";
                    echo "<td class='center'>".(empty($instore->category->name) ? '' : $instore->category->name)."</td>";
                    echo "<td class='left'>".(empty($instore->name)?'':$instore->name)."</td>";
                    echo '</tr>';
                }  
            echo '</tbody>';
        ?>
        </table>
        <!-- 分页栏 -->
        <div id="page" class="w100%">
            <div class="w600 m0a">
                <?php 
                    $this->widget('CLinkPager',array(
                        'firstPageLabel'=>'首页',
                        'lastPageLabel'=>'末页',
                        'prevPageLabel'=>'上一页',
                        'nextPageLabel'=>'下一页',
                        'pages'=>$page,
                        'maxButtonCount'=>9,
                    )
                );
                ?>
                <?php if($count>$size): ?>
                <p class="pd5 f15px inline ml20">跳转到：</p>
                <button class="btn btn-default pd3" onclick="showPager();">&nbsp;<?php echo $page->currentPage+1; ?>&nbsp;&nbsp;<span class="right caret"></span></button>
                <p class="pd5 f15px inline ">页</p>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- 跳页模态框 -->
<div id="msgs_pager" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">跳转</h4>
    </div>

    <div class="modal-body">
        <div class="overflow-a xh400">
            <label>点击页数进行跳转：</label>
            <?php
                for($i=1;$i<=$total;$i++){
                    if($page->currentPage+1 == $i){
                        echo "<a class='btn btn-default btn-block left mt10 active' href='/oa/borrowRecord_instore?page=".$i."'>".$i."</a>";
                    }else{
                        echo "<a class='btn btn-default btn-block left mt10' href='/oa/borrowRecord_instore?page=".$i."'>".$i."</a>";
                    }
                }
            ?>
        </div>
    </div>
</div>

<!--js-->
<script type="text/javascript">
    // 显示跳页模态框
    function showPager(){
        var ySet = (window.innerHeight - $("#msgs_pager").height())/3;
        var xSet = (window.innerWidth - $("#msgs_pager").width())/2;
        $("#msgs_pager").css("top",ySet);
        $("#msgs_pager").css("left",xSet);
        $('#msgs_pager').modal({show:true});
    }
</script>