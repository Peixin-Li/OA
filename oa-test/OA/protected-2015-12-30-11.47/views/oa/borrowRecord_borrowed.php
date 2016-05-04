<?php
echo "<script type='text/javascript'>";
echo "console.log('borrowRecord_borrowed');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/books.css" />

<div class="bor-1-ddd">
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-b-1-ddd">借阅记录</h4>
    <div class="m0 p0 pd20">
    <!-- 类别标签 -->
    <ul class="nav nav-tabs" role="tablist">
    	<li role="presentation" class="active"><a class="f18px pointer" href="/oa/borrowRecord" >已借出</a></li>
        <li role="presentation" ><a class="f18px pointer" href="/oa/borrowRecord_instore" >在库</a></li>
        <li role="presentation" ><a class="f18px pointer" href="/oa/borrowRecord_returned" >已归还</a></li>
    </ul>
    <!-- 已借出图书表格 -->
    <table class="table table table-striped table-bordered table-hover bor-t-none">
    	<thead>
    		<tr>
    			<th class="center w100">姓名</th>
    			<th>书名</th>
          <th class="center">类别</th>
    			<th class="center">借书时间</th>
    			<th class="center">应还书时间</th>
    		</tr>
    	</thead>
    <?php
        echo '<tbody>';
        $tag = 1;
        foreach($borrowRecord_borrowed as $borrow)
        {   
            $borrow_time = date('Y-m-d',strtotime($borrow->borrow_time));
            $default_returntime = date('Y-m-d',strtotime($borrow->default_returntime));
            echo '<tr>';
            echo "<td id='book_id' class='hidden'>{$tag}</td>";
            $tag = $tag + 1;
            echo "<td class='center'>".(empty($borrow->user->cn_name)?'':$borrow->user->cn_name)."</td>";
            echo "<td class='left'>".(empty($borrow->book->name)?'':$borrow->book->name)."</td>";
            echo "<td class='center'>".(empty($borrow->book->category->name) ? '' :$borrow->book->category->name)."</td>";
            echo "<td class='center'>".(empty($borrow_time)?'':$borrow_time)."</td>";
            if($default_returntime<=date("Y-m-d")){
                echo "<td class='center red'>".(empty($default_returntime)?'':$default_returntime)."(超时)</td>";
            }else{
                echo "<td class='center'>".(empty($default_returntime)?'':$default_returntime)."</td>";
            }
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
                      echo "<a class='btn btn-default btn-block left mt10 active' href='/oa/borrowRecord?page=".$i."'>".$i."</a>";
                  }else{
                      echo "<a class='btn btn-default btn-block left mt10' href='/oa/borrowRecord?page=".$i."'>".$i."</a>";
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