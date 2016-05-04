<?php
echo "<script type='text/javascript'>";
echo "console.log('books');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/books.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
  <!-- 我借的图书 -->
  <div class="pd20 bor-l-1-ddd bor-r-1-ddd">
    <div class="bor-b-1-ddd pb20">
      <!-- 标题 -->
      <h4 class="pd10 m0">
        <strong>我借的图书</strong>
      </h4>
      <?php if(!empty($borrows)): ?>
      <?php $loss_tag = 0;foreach($borrows as $row){if($row->book->status != "loss") $loss_tag = 1;}  // 判断是否全部都已经丢失了 ?>
      <?php if($loss_tag != 0): ?>
      <table class="bor-1-ddd m0 table table-bordered center table-hover">
        <thead>
          <tr class="bg-fa">
            <th class="w130 center">编号</th>
            <th class="w150 center">类型</th>
            <th class="left">书名</th>
            <th class="w150 center">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($borrows as $row): ?>
          <?php if($row->book->status != "loss"): ?>
          <tr>
            <td class="hidden"><?php echo $row->borrow_id; ?></td>
            <td><?php echo $row->book->serial_number; ?></td>
            <td><?php echo $row->book->category->name; ?></td>
            <td class="left"><?php echo $row->book->name; ?></td>
            <td><button class="btn btn-success w100 pd3" onclick="returnBorrowedBook(this);">图书归还</button></td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else:?>
      <h4 class="pd20 center bor-1-ddd m0" >你没有已借的图书</h4>
      <?php endif; ?>
      <?php else: ?>
      <h4 class="pd20 center bor-1-ddd m0" >你没有已借的图书</h4>
      <?php endif; ?>
    </div>
  </div>

  <div class="pd20 bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd" style="padding-top:0px;">
    <!-- 标签 -->
    <ul class="nav nav-tabs">  
      <li role="presentation" class="active"><a class="pointer w150 f18px center" onclick="switchTo('all');" id="all-switch-btn">所有图书</a></li>
      <li role="presentation"><a class="pointer w150 f18px center" onclick="switchTo('new');"  id="new-switch-btn">最新图书</a></li>
      <button class="btn btn-primary w100 fr mr20 f18px" onclick="showBuy();">我要买书</button>
    </ul>
    <!-- 最新图书 -->
    <div class="center hidden" id="new-book-div">
      <?php if(!empty($books_new)): ?>
        <table class="bor-1-ddd m0 table table-bordered center table-hover">
          <thead>
            <tr class="bg-fa">
              <th class="w130 center">编号</th>
              <th class="w150 center">类型</th>
              <th class="left">书名</th>
              <th class="w150 center">在读</th>
              <th class="w150 center">操作</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($books_new as $row): ?>
            <tr>
              <?php $borrow_tag = 0; // 是否已借的标记 ?>
              <td class="hidden"><?php echo $row->book_id; ?></td>
              <td><?php echo $row->serial_number; ?></td>
              <td><?php echo $row->category->name; ?></td>
              <td class="left"><?php echo $row->name; ?></td>
              <td>
                <?php if($row->status == 'borrow'):?>
                <?php $tmp_user_id = empty($row->bookWhere->user_id) ? 0 : $row->bookWhere->user_id; // 获取借的人的id ?>
                <?php if($tmp_user_id == $this->user->user_id){  // 如果是自己，就输出自己
                    echo '自己';
                    $borrow_tag = 1;
                  }else{   // 如果是其他人就输出姓名
                    echo $en_name =  empty($row->bookWhere->user->en_name) ? $row->bookWhere->user->cn_name : $row->bookWhere->user->en_name;
                    $borrow_tag = 1;
                  }
                ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if($borrow_tag == 0): // 如果没有借的话 ?>
                <button class="btn btn-success w100 pd3" onclick="borrowBook(this);">借书</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <h4 class="bor-1-ddd pd20 center">没有新图书的信息</h4>
        <?php endif; ?>
    </div>
    <!-- 所有图书div -->
    <div id="all-book-div">
      <!-- 搜索分类栏 -->
      <div class="bg-240 bor-l-1-ddd bor-r-1-ddd">
        <div>
          <div>
            <label class="fl pd5 ml10 mt10">图书名称：</label>
            <input class="form-control searchbook fl w300 " style="margin-top:12px;" placeholder="请输入关键字" id="book-search"></input>
            <button class="btn btn-primary searchbutton fl pd5 w100 ml20" style="margin-top:12px;" onClick="bookSearch($('#book-search').val());" id="searchBtn"><span class="glyphicon glyphicon-search"></span>&nbsp;查找</button>
            <button class="btn btn-success w100 pd5 ml20 fl " style="margin-top:12px;" onClick="location.reload();"><span class="glyphicon glyphicon-refresh"></span>&nbsp;全部图书</button>
            <div class="clear"></div>
          </div>
          <div class="mt5 pb10">
            <label class="fl pd5 ml10 mt5">图书类别：</label>
            <div class="fl mt5">
              <button class="btn btn-success pd5" name="所有分类" onclick="categoryChange(this);">所有分类</button>
              <?php foreach($categorys as $category): ?>
              <button class="btn btn-default pd5" name="<?php echo $category->category_id;?>" onclick="categoryChange(this);"><?php echo $category->name;?></button>
              <?php endforeach; ?>
            </div>
            <div class="clear"></div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
      <!-- 图书列表表格 -->
      <table class="table table-striped table-bordered table-hover booklist" id="booklist">
        <thead>
          <tr>
            <th class="hidden"></th>
            <th class="w130 center">序号</th>
            <th class="w130 center">类别</th>
            <th class="left">书名</th>
            <th class="center">在读</th>
            <th class="w130 center">状态</th>
          </tr>
        </thead>
        <?php if(!empty($books)){
          echo '<tbody>';
          foreach($books as $book){
            echo '<tr>';
            echo "<td id='book_id' style='display:none;'>{$book->book_id}</td>";
            echo "<td>{$book->serial_number}</td>";
            echo "<td>{$book->category->name}</td>";
            if($book->status == 'borrow'){   // 如果这本数已经被借的话
              $tmp_user_id = empty($book->bookWhere->user_id) ? 0 : $book->bookWhere->user_id;    // 借书人的id
              $tmp_borrow_id = empty($book->bookWhere->borrow_id) ? 0 :$book->bookWhere->borrow_id;   // 借书记录的id
              if($tmp_user_id == $user_id){  // 如果是自己借的话，就输出自己
                echo "<td class='left'><a class='pointer' onclick='showBookDetail(this)'>{$book->name}</a></td>";
                echo "<td class='hidden'>{$book->publisher}</td>";
                echo "<td class='hidden'>{$book->author}</td>";
                echo "<td class='hidden'>{$book->descript_url}</td>";
                echo "<td class='center BurlyWood'>自己</td>";
                echo "<td class='nw80 xw80'><button id='btn_return' class='hidden' onClick='returnBook(this,$(this).attr(\"name\"));' name='{$tmp_borrow_id}' class='btn btn-success w100'>图书归还</button></td>"; 
              }else{   // 如果是其他人借的话，就输出姓名
                $en_name =  empty($book->bookWhere->user->en_name) ? $book->bookWhere->user->cn_name : $book->bookWhere->user->en_name;
                echo "<td class='left'><a class='pointer' onclick='showBookDetail(this)'>{$book->name}</a></td>";
                echo "<td class='hidden'>{$book->publisher}</td>";
                echo "<td class='hidden'>{$book->author}</td>";
                echo "<td class='hidden'>{$book->descript_url}</td>";
                echo "<td class='center BurlyWood'>{$en_name}</td>";
                echo "<td class='gray'>已借出</td>";
              }
            }else{     // 如果没有被借的话就显示借书的按钮
              echo "<td class='left'><a class='pointer' onclick='showBookDetail(this)'>{$book->name}</a></td>";
              echo "<td class='hidden'>{$book->publisher}</td>";
              echo "<td class='hidden'>{$book->author}</td>";
              echo "<td class='hidden'>{$book->descript_url}</td>";
              echo "<td></td>";
              echo "<td class='nw80 xw80'><button id='btn' class='btn btn-success w100' onClick='borrow($(this).parent().parent().find(\"#book_id\").text(),this);'>借书</button></td>";
            }
            echo '</tr>';
          }  
          echo '</tbody>';
        }?>
      </table>
    </div>
  </div>
</div>
<!-- 图书详情模态框 -->
<div id="book-detail-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">图书详情</h4>
    </div>
    <div class="modal-body">
        <table class="table bor-1-ddd m0">
          <tr>
            <th class="w100 center">编号</th>
            <td id="book-detail-num"></td>
            <td class="hidden" id="book-detail-id"></td>
          </tr>
          <tr>
            <th class="w100 center">类别</th>
            <td id="book-detail-category"></td>
          </tr>
          <tr>
            <th class="w100 center">书名</th>
            <td id="book-detail-name"></td>
          </tr>
          <tr>
            <th class="w100 center">出版社</th>
            <td id="book-detail-publisher"></td>
          </tr>
          <tr>
            <th class="w100 center">作者</th>
            <td id="book-detail-author"></td>
          </tr>
          <tr>
            <th class="w130 center">详细介绍连接</th>
            <td id="book-detail-link"></td>
          </tr>
          <tr>
            <th class="w100 center">借阅记录</th>
            <td id="book-detail-record"></td>
          </tr>
        </table>
    </div>
    <div class="modal-footer"></div>
</div>
<!-- 购买新书模态框 -->
<div id="book-buy-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">购买图书</h4>
    </div>
    <div class="modal-body">
        <table class="table bor-1-ddd m0">
          <tr>
            <th class="w100 center">书名</th>
            <td>
              <input class="form-control w300 inline" placeholder="请准确输入书名" id="buy-book-name" onchange="bookNameCheck();">
              <span class="hidden b2" id="buy-remind">(书架上已有该书!)</span>
            </td>
          </tr>
          <tr>
            <th class="w100 center">数量</th>
            <td>
              <input class="form-control w100 inline" id="buy-book-num" placeholder="请输入数量">&nbsp;<span>本</span>
            </td>
          </tr>
          <tr>
            <th class="w100 center">预计单价</th>
            <td>
              <input class="form-control w100 inline" id="buy-book-price" placeholder="请输入单价">&nbsp;<span>元</span>
            </td>
          </tr>
          <tr>
            <th class="w100 center">申请方式</th>
            <td>
              自行支付
            </td>
          </tr>
          <tr>
            <th class="w100 center">使用时间</th>
            <td>
              <input class="form-control w150" id="buy-book-usetime" placeholder="选填">
            </td>
          </tr>
          <tr>
            <th class="w100 center">参考链接</th>
            <td>
              <input class="form-control" id="buy-book-link" placeholder="选填">
            </td>
          </tr>
        </table>
    </div>
    <div class="modal-footer">
      <?php 
        if(empty($budget)){
          echo "<span class='b2'>(年度预算未出，暂停申请)</span>";
        }
      ?>
      <button class="btn btn-success w100 <?php if(empty($budget)){echo 'disabled';} ?>" onclick="sendBuy();" id="send-new-btn">提交</button>
    </div>
</div>
<!-- 购买新书成功提示模态框 -->
<div id="buy-remind-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">提示信息</h4>
    </div>
    <div class="modal-body">
        提交成功，可自行购买，购买后在费用处完成报销
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="location.reload();">确认</button>
    </div>
</div>

<!-- js -->
<!-- 借书与还书 -->
<script type="text/javascript">
  // 书名检测
  function bookNameCheck(){
    var name = $("#buy-book-name").val();
    // 查找是否有存在书库里面
    var find_tag = false;
    $.each(books_arr, function(){
      if(this['name'] == name){
        find_tag = true;
        return false;
      }
    });
    if(find_tag){  // 如果找到了就提示该书已在书架上
      $("#buy-remind").removeClass("hidden");
      $("#send-new-btn").addClass("disabled");
    }else{
      $("#buy-remind").addClass("hidden");
      $("#send-new-btn").removeClass("disabled");
    }
  }

  // 书本数组初始化
  var books_arr = new Array();
  <?php 
    if(!empty($books)){
      foreach($books as $brow){
        echo "books_arr.push({\"name\":\"{$brow->name}\", \"id\":\"{$brow->book_id}\"});\n";
      }
    }
  ?>

  // 发送购买图书
  function sendBuy(){
    // 获取数据
    var name = $("#buy-book-name").val();
    var num = $("#buy-book-num").val();
    var price = $("#buy-book-price").val();
    var use_time = $("#buy-book-usetime").val();
    var link = $("#buy-book-link").val();
    var d_pattern = /^\d+(\.\d{1,2})?$/;
    var unit_pattern = /^\d+$/;
    var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
    var data = new Array();

    // 验证数据
    if(!name){
      showHint("提示信息","请输入名称");
      $("#buy-book-name").focus();
    }else if(!unit_pattern.exec(num)){
      showHint("提示信息","数量单位输入格式错误");
      $("#buy-book-num").focus();
    }else if(!d_pattern.exec(price)){
      showHint("提示信息","预计单价输入格式错误");
      $("#buy-book-price").focus();
    }else if(use_time != "" && !date_pattern.exec(use_time)){
      showHint("提示信息","使用时间输入格式错误");
      $("#buy-book-usetime").focus();
    }else{
      // 发送数据
      num = num +"本";
      data.push({'category':'welfare', 'type':'图书', 'name':name, 'quantity':num, 'price':price, 'url':link, 'reason':' ', 'buy_way':'自行支付', 'use_time':use_time});
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/goodsApply',
        data:{'data':data},
        success:function(result){
          if(result.code == 0){
            $("#book-buy-div").modal('hide');
            setTimeout(function(){
              var ySet = (window.innerHeight - $("#buy-remind-div").height())/2;
              var xSet = (window.innerWidth - $("#buy-remind-div").width())/2;
              $("#buy-remind-div").css("top",ySet);
              $("#buy-remind-div").css("left",xSet);
              $("#buy-remind-div").modal({show:true});
            },400);
          }else if(result.code == -1){
            showHint("提示信息","提交申请失败，请重试！");
          }else if(result.code == -2){
            showHint("提示信息","参数错误！");
          }else{
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 显示购买图书
  function showBuy(){
    var ySet = (window.innerHeight - $("#book-buy-div").height())/2;
    var xSet = (window.innerWidth - $("#book-buy-div").width())/2;
    $("#book-buy-div").css("top",ySet);
    $("#book-buy-div").css("left",xSet);
    $("#book-buy-div").modal({show:true});
  }

  // 借书
  function borrow(id,obj){
    // 获取数据
    var book_id = id;
    // 验证数据
    var pattern = /^\d+$/;
    if(!pattern.exec(book_id)){
      showHint("提示信息","请刷新页面");
    }else{
      // 发送数据
      $.ajax({
        type:'post',
        url: '/ajax/borrow',
        dataType:'json',
        data:{'book_id':book_id},
        success:function(result){
          if(result.code == '0'){
            btnSuccessChange(obj);
          }else if(result.code == '-2'){
            showHint("提示信息","请刷新页面");
          }else if(result.code == '-3'){
            showHint("提示信息","图书已借出");
          }else{
            showHint("提示信息","系统错误，请联系管理员");
          }
        }
      });
    }
  }

  // 借书成功按钮变化
  function btnSuccessChange(obj){
    // $(obj).parent().append("<a class='btn btn-success btn-block fr xw150 m0'>借书成功</a>");
    // $(obj).remove();
    location.reload();
  }

  // 还书成功按钮变化
  function btnReturnChange(obj){
    $(obj).parent().append("<p style='text-align:center;color:green;padding:0px;margin:0px;width:100%;max-width:130px;float:right;'>还书成功</p>");
    $(obj).remove();
  }

  // 还书
  function returnBook(obj, borrow_id){
    // obj 按钮对象
    // borrow_id 借书记录id
    var pattern = /^\d+$/;
    if(!pattern.exec(borrow_id)){
        showHint("提示信息","请刷新网页");
    }else{
      $.ajax({
          type:'post',
          url: '/ajax/returnBook',
          dataType:'json',
          data:{'borrow_id':borrow_id},
          success:function(result){
              if(result.code == '0'){
                  btnReturnChange(obj);
              }else if(result.code == '-2'){
                  showHint("提示信息","请刷新网页");
              }else if(result.code == '-3'){
                  showHint("提示信息","图书已经归还");
              }else{
                  showHint("提示信息","系统错误，请联系管理员");
              }
          }
      });
    }
  }

  // 图书列表的加载
  var books = new Array();
  function bookSearch(str){
    var bookname = str;   // 搜索的关键字
    if(bookname==""){   // 如果为空则提示
      showHint("提示信息","请输入图书名称");
    }else{
      // 初始化books数组，将所有行加入到数组中
      if(books[0]==null){             
        var k=0;
        $("#booklist").find("tr").each(function(){
          if($(this).find("td").text()!=""){
            books[k]=this;
            k++;
          }
        });
      }

      // 清除booklist表格
      $("#booklist").find("tbody").remove();

      // 查找书本，并将查找到的书本加载到booklist表格中
      $("#booklist").append("<tbody>");
      var finds = 0;
      for(var i=0;i<books.length;i++){
         var str = String(books[i].innerHTML);
         if(str.indexOf(bookname)>=0){
           $("#booklist").append("<tr>"+books[i].innerHTML+"</tr>");
           finds++;
         }
       }
       $("#booklist").append("</tbody>");

      // 如果找不到，则提示用户，并且加载初始数据
      if(finds==0){
        showHint("提示信息","查找不到符合要求的图书");
      }

      placeFirst();   // 将已借的书置顶
    }
  }

  // 绑定回车键，回车即搜索
  document.onkeydown = function(e){
    if(!e) e = window.event;//火狐中是 window.event
    if((e.keyCode || e.which) == 13) $("#searchBtn").click();
  }

  // 选择图书分类，一选择就改变
  function categoryChange(obj){
    $(obj).parent().children().removeClass("btn-success").addClass("btn-default");
    $(obj).removeClass("btn-default").addClass("btn-success");
    var searchval = $(obj).attr("name");
    var searchstr = "";
    if(searchval == "所有分类"){
      searchstr = " ";
    }else{
      searchstr = $(obj).text();
    }
    bookSearch(searchstr);  // 去搜索分类
  }

  // 将已借的书置顶
  function placeFirst(){
    $("#booklist").find("button").each(function(){
      if($(this).text()=="图书归还"){
        $("#booklist").find("tbody").find("tr").first().before("<tr>"+$(this).parent().parent().html()+"</tr>");
        $(this).parent().parent().remove();
      }
    });
  }

  // 页面初始化
  $(document).ready(function(){
    // 初始化数组
    if(books[0]==null){             
      var k=0;
      $("#booklist").find("tr").each(function(){
        if($(this).find("td").text()!=""){
          books[k]=this;
          k++;
        }
      });
    }

    // 日期控件初始化
    $('#buy-book-usetime').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});

    // 自己借的图书置顶
    placeFirst();
  });

  function showBookDetail(obj){
    // 获取数据
    var id = $(obj).parent().parent().find("#book_id");
    var serial_number = id.next();
    var category = serial_number.next();
    var bookname = category.next();
    var publisher = bookname.next();
    var author = publisher.next();
    var descript_url = author.next();
    var book_id = id.text();

    // 发送数据
    $.ajax({
      type:'post',
      url: '/ajax/booksDetail',
      dataType:'json',
      data:{'book_id':book_id},
      success:function(result){
          $('#book-detail-record').html('');
          if(result.code == '0'){
            $('#book-detail-record').append('<table class=\"table bor-1-ddd m0\"><thead><tr><th>借阅人</th><th>借书日期</th><th>还书日期</th></tr></thead><tbody></tbody></table>');
            $.each(result.borrow_record,function(){
              var str = "<tr><td>"+this[0]+"</td><td>"+this[1].split(" ")[0]+"</td><td>"+this[2].split(" ")[0]+"</td></tr>";
              $('#book-detail-record').find("tbody").append(str);
            });
          }else if(result.code == '-1'){
            $('#book-detail-record').html('获取书本详细信息失败！');
          }else if(result.code == '-2'){
            $('#book-detail-record').html('书本序号错误！');
          }else if(result.code == '-3'){
            $('#book-detail-record').html('该书没有借阅记录');
          }else if(result.code == '-4'){
            $('#book-detail-record').html('找不到该图书');
          }else{
            $('#book-detail-record').html('你没有权限进行此操作！');
          }
      }
    });

    // 填充数据
    $("#book-detail-name").text(bookname.text());
    $("#book-detail-category").text(category.text());
    $("#book-detail-id").text(id.text());
    $("#book-detail-num").text(serial_number.text());
    $("#book-detail-publisher").text(publisher.text());
    $("#book-detail-author").text(author.text());
    $("#book-detail-link").text(descript_url.text());

    // 根据点击的书本的状态来显示不同的按钮
    if($(obj).parent().parent().find("button").text()==""){   // 如果没有按钮
      $(".modal-footer").html("");
    }else{
      $(".modal-footer").html("");
      $(obj).parent().parent().find("button").each(function(){
        var str = "<button class='w100 btn btn-success'>"+$(this).text()+"</button>";
        $(".modal-footer").append(str);
        if($(this).text()=="图书归还"){   // 如果是图书归还的按钮
          $(".modal-footer").find("button").attr("name",$(this).attr("name"));
          $(".modal-footer").find("button").bind("click",function(){
            returnBook(this,$(this).attr("name"));
          });
        }else if($(this).text()=="借书"){ // 如果是借书的按钮
          $(".modal-footer").find("button").bind("click",function(){
            borrow($("#book-detail-id").text(),this);
          });
        }
      });
    }

    // 显示详情窗口
    var ySet = (window.innerHeight - $("#book-detail-div").height())/2;
    var xSet = (window.innerWidth - $("#book-detail-div").width())/2;
    $("#book-detail-div").css("top",ySet);
    $("#book-detail-div").css("left",xSet);
    $("#book-detail-div").modal({show:true});
  }

  // 最新图书和所有图书的切换
  function switchTo(type){
    $("#"+type+"-book-div").removeClass("hidden");
    $("#"+type+"-switch-btn").parent().addClass("active");
    if(type == "all"){
      $("#new-book-div").addClass("hidden");
      $("#new-switch-btn").parent().removeClass("active");
    }else{
      $("#all-book-div").addClass("hidden");
      $("#all-switch-btn").parent().removeClass("active");
    }
  }

  // 归还图书
  function returnBorrowedBook(obj){
    var id = $(obj).parent().parent().children().first().text();
    $.ajax({
          type:'post',
          url: '/ajax/returnBook',
          dataType:'json',
          data:{'borrow_id':id},
          success:function(result){
              if(result.code == '0'){
                  location.reload();
              }else if(result.code == '-2'){
                  showHint("提示信息","请刷新网页");
              }else if(result.code == '-3'){
                  showHint("提示信息","图书已经归还");
              }else{
                  showHint("提示信息","系统错误，请联系管理员");
              }
          }
      });
  }

  // 借书
  function borrowBook(obj){
    var id = $(obj).parent().parent().children().first().text();
    $.ajax({
          type:'post',
          url: '/ajax/borrow',
          dataType:'json',
          data:{'book_id':id},
          success:function(result){
            if(result.code == '0'){
              location.reload();
            }else if(result.code == '-2'){
              showHint("提示信息","请刷新页面");
            }else if(result.code == '-3'){
              showHint("提示信息","图书已借出");
            }else{
              showHint("提示信息","系统错误，请联系管理员");
            }
          }
      });
  }
</script>
