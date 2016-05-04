<?php
echo "<script type='text/javascript'>";
echo "console.log('editbook');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/books.css" />

<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-1-ddd">修改图书信息</h4>
    <!-- 搜索栏 -->
    <div class="bg-fa bor-l-1-ddd bor-r-1-ddd">
      <label class="fl mt10 pd10 ml20 f15px">选择分类</label>
      <select onchange="categoryChange(this);" class="mt15 fl f15px w130 form-control inline">
        <option value="所有分类">所有分类</option>
        <?php foreach($categorys as $category): ?>
        <?php echo "<option value='{$category->category_id}' >{$category->name}</option>"; ?>
        <?php endforeach; ?>
      </select>
      <label class="fl mt10 pd10 ml20 f15px">快速查找</label>
      <input class="form-control searchbook fl m12 w300" placeholder="请输入搜索的书名" id="book-search"></input>
      <button class="btn btn-success searchbutton fl m12 f15px w100 pd5" onClick="bookSearch($('#book-search').val());" id="searchBtn"><span class="glyphicon glyphicon-search"></span>&nbsp;查找</button>
      <button class="btn btn-success w100 fr f15px mt10 mr20" onClick="location.reload();"><span class="glyphicon glyphicon-refresh"></span>&nbsp;刷新</button>
      <button class="btn btn-primary w100 fr f15px mt10 mr20" onClick="showAddDiv();">添加图书</button>
      <div class="clear"></div>
  	</div>
    <!--图书列表-->
    <table class="table table-striped table-bordered table-hover booklist" id="booklist">
    <thead>
      <tr>
        <th class="hidden"></th>
        <th class="w130 center">序号</th>
        <th class="w130 center">类别</th>
        <th class="left">书名</th>
        <th class="w250 center">操作</th>
      </tr>
    </thead>
    <?php if(!empty($books)){
      echo '<tbody>';
      foreach(array_reverse($books) as $book){
        echo '<tr>';
        echo "<td id='book_id' style='display:none;'>{$book->book_id}</td>";
        echo "<td>{$book->serial_number}</td>";
        echo "<td>{$book->category->name}</td>";
		    echo "<td class='left'><a class='pointer' onclick='showBookDetail(this)'>{$book->name}</a></td>";
        echo "<td class='hidden'>{$book->publisher}</td>";
        echo "<td class='hidden'>{$book->author}</td>";
        echo "<td class='hidden'>{$book->descript_url}</td>";
        echo "<td><button class='btn btn-default w80 inline' onClick='edit(this,$(this).parent().parent().find(\"#book_id\").text());'><span class='glyphicon glyphicon-edit'></span>&nbsp;修改</button><button class='btn btn-default ml10 w80 inline' onClick='confirmDelete(this);'><span class='glyphicon glyphicon-remove-sign b2'></span>&nbsp;删除</button></td>";
        echo '</tr>';
      }  
      echo '</tbody>';
    }?>
  </table>
</div>

<!-- 修改图书信息模态框 -->
<div id="editbook-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">修改图书信息</h4>
    </div>

    <div class="modal-body">
      <table class="table table-bordered m0">
        <tr>
          <th class="center w100">序号</th>
          <td><div id="serial_number"></div></td>
          <td class="hidden" id="bookId"></td>
        </tr>
        <tr>
          <th class="center w100">分类</th>
          <td><select id="category" class="form-control w200 inline">
                <?php foreach($categorys as $category): ?>
                <?php echo "<option value='{$category->category_id}' >{$category->name}</option>"; ?>
                <?php endforeach; ?>
          </select></td>
        </tr>
        <tr>
          <th class="center w100">书名</th>
          <td><input class="form-control" id="book_name"></td>
        </tr>
        <tr>
          <th class="center w100">出版社</th>
          <td><input class="form-control" id="book_publisher"></td>
        </tr>
        <tr>
          <th class="center w100">作者</th>
          <td><input class="form-control" id="book_author"></td>
        </tr>
        <tr>
          <th class="center w130">详细介绍连接</th>
          <td><input class="form-control" id="book_link"></td>
        </tr>
      </table>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success w100" onClick="editBook();">确认修改</button>
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
            <td id="book-detail-record">还没有人借阅过该图书！</td>
          </tr>
        </table>
    </div>

    <div class="modal-footer" id="modal-footer"></div>
</div>

<!-- 删除图书模态框 -->
<div id="book-delete-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">删除图书</h4>
    </div>

    <div class="modal-body">
        <table class="table bor-1-ddd m0">
          <tr>
            <th class="w100 center">书名</th>
            <td id="book-delete-name"></td>
          </tr>
          <tr>
            <th class="w100 center">删除原因</th>
            <td><input class="form-control" id="book-delete-reason"></td>
          </tr>
        </table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100" onclick="deleteBook();">确认删除</button>
    </div>
</div>

<!-- 添加图书模态框 -->
<div id="book-add-div" class="modal fade in hint bor-rad-5 w1000" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">添加图书</h4>
    </div>

    <div class="modal-body overflow-a xh400">
      <table class="table bor-1-ddd m0 f15px ">
        <tbody id="addBook-table">
          <tr class="bookinfo">
              <td>
                <table class="table bor-1-ddd">
                  <tr>
                    <th class="w100 center">书名</th>
                    <td>
                      <input id="serial" class="bookserial" type="hidden" value="SY<?php echo $serial; ?>" ></input>
                      <input type="text" class="form-control inputBooks" placeholder="请输入书名" autofocus></input>
                    </td>
                  </tr>
                  <tr>
                    <th class="w100 center">分类</th>
                    <td>
                      <select id="category" class="w200 category form-control inline" onchange="newCategory(this)">
                        <?php foreach($categorys as $category): ?>
                        <?php echo "<option value='{$category->category_id}'>{$category->name}</option>"; ?>
                        <?php endforeach; ?>
                        <option value="0">...新增分类...</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <th class="w100 center">出版社</th>
                    <td>
                      <input type="text" class="form-control inputPublisher" placeholder="请输入出版社"></input>
                    </td>
                  </tr>
                  <tr>
                    <th class="w100 center">作者</th>
                    <td>
                      <input type="text" class="form-control inputAuthor" placeholder="请输入作者"></input>
                    </td>
                  </tr>
                  <tr>
                    <th class="w130 center">详细介绍连接</th>
                    <td>
                      <input type="text" class="form-control inputLink" placeholder="请输入详细介绍连接"></input>
                    </td>
                  </tr>
                </table>
              </td>
              <td class="center w200">
                  <button class="bor-none bg-trans" onClick="newLine(<?php echo $serial; ?>)">
                      <span class="glyphicon glyphicon-plus-sign"></span>
                      (增加一行)
                  </button>
              </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="modal-footer">
        <button type="button" id="btn_submit" class="btn btn-success w100" onClick="addBook()">确认添加</button> 
    </div>
</div>

<!-- 新的类名模态框 -->
<div id="newCategory-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">添加图书分类</h4>
    </div>

    <div class="modal-body">
        <label>新的类名：</label>
        <input type="text" class="form-control" id="newCategoryInput"></input>
    </div>

    <div class="modal-footer">
        <button class="fr w100 btn btn-success" onclick="addCategory()" data-dismiss="modal">添加分类</button>
    </div>
</div>

<!--js-->
<!-- 图书列表初始化 -->
<script type="text/javascript">
  // 搜索图书
  var books = new Array();
  function bookSearch(str){
    var bookname = str;
    if(bookname==""){
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
      var finds = 0;
      for(var i=0;i<books.length;i++){
         var str = String(books[i].innerHTML);
         if(str.indexOf(bookname)>=0){
           $("#booklist").append("<tr>"+books[i].innerHTML+"</tr>");
           finds++;
         }
       }

      // 如果找不到，则提示用户，并且加载初始数据
      if(finds==0){
        showHint("提示信息","查找不到符合要求的图书");
      }
    }
  }

  // 注册回车键为搜索
  document.onkeydown = function(e){
    if(!e) e = window.event;//火狐中是 window.event
    if((e.keyCode || e.which) == 13) $("#searchBtn").click();
  }

  // 选择图书分类，一选择就改变
  function categoryChange(obj){
    var searchval = $(obj).val();
    var searchstr = "";
    if(searchval == "所有分类"){
      searchstr = " ";
    }else{
      $(obj).find("option").each(function(){
        if($(this).attr("value")==searchval) searchstr = $(this).text(); 
      });
    }
    bookSearch(searchstr);
  }

/*-------------------------------------------------------------修改图书-------------------------------------------------------------*/

	// 点击修改
	function edit(obj,id){
    if(obj!=""){
      // 获取数据
      var bookid = id;
      var serial_number = $(obj).parent().parent().find('#book_id').next();
      var category = $(serial_number).next();
      var bookname = $(category).next();
      var category_id = "";
      var publisher = bookname.next();
      var author = publisher.next();
      var descript_url = author.next();

      // 填充数据
      $("#bookId").text(bookid);
      $("#category").find("option").each(function(){
        if($(this).text()==category.text()){
          category_id = $(this).val();
        }
      });
      $("#category").val(category_id);
      $("#book_name").val(bookname.text());
      $("#serial_number").text(serial_number.text());
      $("#book_publisher").val(publisher.text());
      $("#book_author").val(author.text());
      $("#book_link").val(descript_url.text());
    }else{
      // 填充数据
      $("#bookId").text($("#book-detail-id").text());
      var category_id = "";
      $("#category").find("option").each(function(){
        if($(this).text()==$("#book-detail-category").text()){
          category_id = $(this).val();
        }
      });
      $("#category").val(category_id);
      $("#book_name").val($("#book-detail-name").text());
      $("#serial_number").text($("#book-detail-num").text());
      $("#book_publisher").val($("#book-detail-publisher").text());
      $("#book_author").val($("#book-detail-author").text());
      $("#book_link").val($("#book-detail-link").text());
    }
		
    // 显示修改模态框
  	var ySet = (window.innerHeight - $("#editbook-div").height())/3;
    var xSet = (window.innerWidth - $("#editbook-div").width())/2;
    $("#editbook-div").css("top",ySet);
    $("#editbook-div").css("left",xSet);
  	$("#editbook-div").modal({show:true});
	}


	function editBook(obj){
    // 获取数据
		var book_id = $("#bookId").text();
		var bookname = $("#book_name").val();
		var category = $("#category").val();
    var publisher = $("#book_publisher").val();
    var author = $("#book_author").val();
    var link = $("#book_link").val();
    // var link_pattern = /^(https?\:\/\/)?([a-zA-Z0-9-_]+\.)+[\?\/\&\.\#\-\_\=0-9a-zA-Z]+$/;

    // 验证数据
		var pattern = /^\d+$/;
    	if(!pattern.exec(book_id)){
      	showHint("提示信息","序号错误，请刷新页面");
    	}else if(!pattern.exec(category)){
    		showHint("提示信息","序号错误，请刷新页面");
    	}else if(bookname == ""){
        showHint("提示信息","请输入书名！");
      }else if(publisher == ""){
        showHint("提示信息","请输入出版社！");
      }else if(author == ""){
        showHint("提示信息","请输入作者！");
      }else{
        $("#editbook-div").modal('hide');
      		$.ajax({
        		type:'post',
        		url: '/ajax/editbook',
        		dataType:'json',
        		data:{'book_id':book_id,'name':bookname,'category_id':category,'publisher':publisher,'author':author,'descript_url':link},
        		success:function(result){
          			if(result.code == '0'){
          				showHint("提示信息","修改成功！");
          				setTimeout(function(){location.reload();},1000);
          			}else if(result.code == '-2'){
            			showHint("提示信息","书的类别号不能为零，修改失败！");
          			}else if(result.code == '-3'){
            			showHint("提示信息","书的名字不能为零或空，修改失败！");
          			}else{
            			showHint("提示信息","你没有权限进行此操作！");
          			}
        		}
      		});
    	}
	}

/*-------------------------------------------------------------显示图书详情-------------------------------------------------------------*/
  
  // 显示图书详情
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
    $.ajax({
      type:'post',
      url: '/ajax/booksDetail',
      dataType:'json',
      data:{'book_id':book_id},
      success:function(result){
          if(result.code == '0'){
            $('#book-detail-record').html('');
            $('#book-detail-record').append('<table class=\"table bor-1-ddd m0\"><thead><tr><th>借阅人</th><th>借书日期</th><th>还书日期</th></tr></thead><tbody></tbody></table>');
            $.each(result.borrow_record,function(){
              var str = "<tr><td>"+this[0]+"</td><td>"+this[1]+"</td><td>"+this[2]+"</td></tr>";
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

    // 添加按钮到模态框底部
    if($(obj).parent().parent().find("button").text()==""){
      $("#modal-footer").html("");
    }else{
      $("#modal-footer").html("");
      $(obj).parent().parent().find("button").each(function(){
        var str = "<button class='w100 btn btn-success'>"+$(this).text()+"</button>";
        $("#modal-footer").append(str);
        var btn_str = $(this).text().replace(/\s+/g, "");
        if(btn_str == "修改"){
          $("#modal-footer").find("button").last().attr("data-dismiss","modal");
          $("#modal-footer").find("button").last().bind("click",function(){
            edit("",$("#book-detail-id").text());
          });
        }else if(btn_str == "删除"){
          $("#modal-footer").find("button").last().attr("data-dismiss","modal");
          $("#modal-footer").find("button").last().removeClass("btn-success").addClass("btn-primary");
          $("#modal-footer").find("button").last().bind("click",function(){
            confirmDelete("",$("#book-detail-name").text());
          });
        }
      });
    }

    // 显示图书详情模态框
    var ySet = (window.innerHeight - $("#book-detail-div").height())/3;
    var xSet = (window.innerWidth - $("#book-detail-div").width())/2;
    $("#book-detail-div").css("top",ySet);
    $("#book-detail-div").css("left",xSet);
    $("#book-detail-div").modal({show:true});
  }

/*-------------------------------------------------------------删除图书-------------------------------------------------------------*/

  // 显示删除图书
  var clickobj = "";
  var delete_book_id = "";
  function confirmDelete(obj,book_str){
    if(obj==""){
      delete_book_id = $("#book-detail-id").text();
      $("#book-delete-name").text($("#book-detail-name").text());
    }else{
      clickobj = obj;
      delete_book_id = $(clickobj).parent().parent().find('#book_id').text();
      $("#book-delete-name").text($(obj).parent().parent().find("td.left").text());
    }

    // 显示删除图书模态框
    var ySet = (window.innerHeight - $("#book-delete-div").height())/3;
    var xSet = (window.innerWidth - $("#book-delete-div").width())/2;
    $("#book-delete-div").css("top",ySet);
    $("#book-delete-div").css("left",xSet);
    $("#book-delete-div").modal({show:true});
  }

  // 发送删除图书
  function deleteBook(){
    var pattern = /^\d+$/;
    var reason = $("#book-delete-reason").val();
      if(!pattern.exec(delete_book_id)){
          showHint("提示信息","序号错误，请刷新页面");
      }else if(reason==""){
        showHint("提示信息","请输入删除原因");
      }else{
          $.ajax({
            type:'post',
            url: '/ajax/deletebook',
            dataType:'json',
            data:{'book_id':delete_book_id,'loss_note':reason},
            success:function(result){
                if(result.code == '0'){
                  showHint("提示信息","删除成功！");
                  setTimeout(function(){location.reload();},1000); 
                }else if(result.code == '-1'){
                  showHint("提示信息","删除失败！");
                }else if(result.code == '-2'){
                  showHint("提示信息","请输入删除原因！");
                }else if(result.code == '-3'){
                  showHint("提示信息","找不到该图书");
                }else{
                  showHint("提示信息","你没有权限进行此操作！");
                }
            }
          });
      }
  }

/*-------------------------------------------------------------添加书本-------------------------------------------------------------*/

  // 显示添加图书
  function showAddDiv(){
    // 显示添加图书模态框
    var ySet = (window.innerHeight - $("#book-add-div").height())/3;
    var xSet = (window.innerWidth - $("#book-add-div").width())/2;
    $("#book-add-div").css("top",ySet);
    $("#book-add-div").css("left",xSet);
    $('#book-add-div').modal({show:true});
  }

  // 发送添加图书
  function addBook(){
    // 获取数据
    var book_arr = new Array();
    var f_tag = false;
    $("#addBook-table").find(".bookinfo").each(function(){
      var serial = $(this).find(".bookserial").val();
      var name = $(this).find(".inputBooks").val();
      var category = $(this).find(".category").val();
      var publisher = $(this).find(".inputPublisher").val();
      var author = $(this).find(".inputAuthor").val();
      var link = $(this).find(".inputLink").val();

      // 验证数据
      var parttern = /^SY\d{3}$/;
      // var link_pattern = /^(https?\:\/\/)?([a-zA-Z0-9-_]+\.)+[\?\/\&\.\#\-\_\=0-9a-zA-Z]+$/;
      if(!parttern.exec(serial)){
        showHint("提示信息","序号错误，请刷新页面重试");
        f_tag = true;
      }else if(name == ""){
        showHint("提示信息","请输入书名!");
        $(this).find(".inputBooks").focus();
        f_tag = true;
      }else if(publisher == ""){
        showHint("提示信息","请输入出版社!");
        $(this).find(".inputPublisher").focus();
        f_tag = true;
      }else if(author == ""){
        showHint("提示信息","请输入作者!");
        $(this).find(".inputAuthor").focus();
        f_tag = true;
      }else{
        f_tag = false;
        book_arr.push({"serial":serial, "name":name, "category":category, "publisher": publisher, "author": author, "descript_url":link});
      }

      // 判断是否出错，如果出错就终止循环
      if(f_tag) return false;
    });

    if(!f_tag){
      $.ajax({
          type:'post',
          url:'/ajax/addBook',
          dataType:'json',
          data:{'book_arr':book_arr},
          success:function(result){
              if(result.code == 0){
                  showHint("提示信息","添加成功");
                  setTimeout(function(){location.reload();},1000);    
              }else if(result.code == '-1'){
                  showHint("提示信息","添加失败");
              }else if(result.code == '-2'){
                  showHint("提示信息","序号重复");
              }else{
                  showHint("提示信息","系统错误，请联系管理员");
              }
          }
      });
    }
  }

  //读取上面select的option选项
  var bookserial = 0;
  function newLine(serial){
      var str = $("#category").html();
      if(bookserial<serial) bookserial = serial + 1;

      var addstr = "<tr class='bookinfo'><td><table class='table bor-1-ddd'>"+
            "<tr><th class='w100 center'>书名</th><td><input id='serial' class='bookserial' type='hidden' value='SY"+bookserial+"'></input><input class='form-control inputBooks' placeholder='请输入书名' autofocus></input></td></tr>"+
            "<tr><th class='w100 center'>分类</th><td><select id='category' class='w200 category form-control inline' onchange='newCategory(this)'>"+str+"</select></td></tr>"+
            "<tr><th class='w100 center'>出版社</th><td><input class='form-control inputPublisher' placeholder='请输入出版社'></input></td></tr>"+
            "<tr><th class='w100 center'>作者</th><td><input  class='form-control inputAuthor' placeholder='请输入作者''></input></td></tr>"+
            "<tr><th class='w130 center'>详细介绍连接</th><td><input type='text' class='form-control inputLink' placeholder='请输入详细介绍连接'></input></td></tr></table></td>"+
            "<td class='center w200'><button class='bor-none bg-trans' onClick='$(this).parent().parent().remove();'><span class='glyphicon glyphicon-minus-sign'></span>(删除一行)</button></td></tr>";
      $("#addBook-table").append(addstr);

      bookserial += 1;
  }

  // 显示添加类别
  function newCategory(obj){
    if($(obj).val()==0){
        var ySet = (window.innerHeight - $("#newCategory-div").height())/3;
        var xSet = (window.innerWidth - $("#newCategory-div").width())/2;
        $("#newCategory-div").css("top",ySet);
        $("#newCategory-div").css("left",xSet);
        $("#newCategory-div").modal({show:true});
    }
  }

  // 发送添加新的图书
  function addCategory(){
      
    var categoryname = $("#newCategoryInput").val();
    $("#addBook-table").find("select").each(function(){
        $(this).find("option").each(function(){
            if($(this).val()==0) $(this).remove();
        });
        $(this).append("<option value='-1'>"+categoryname+"</option>"+
            "<option value='0'>...新建分类...</option>");
    });

    $.ajax({
        type:'post',
        url:'/ajax/Addbookcategory',
        dataType:'json',
        data:{'name':categoryname},
        success:function(result){
            if(result.code == 0){
                showHint("提示信息","添加分类成功");
                $("#addBook-table").find("select").each(function(){
                    $(this).find("option").each(function(){
                        if($(this).val()==-1) $(this).val(result['result']);
                    });
                });
            }else if(result.code == '-1'){
                showHint("提示信息","添加分类失败");
            }else if(result.code == '-2'){
                showHint("提示信息","序号错误");
            }else{
                showHint("提示信息","系统错误，请联系管理员");
            }
        }
    });
    $("#newCategory-div").fadeOut(800);
  }
</script>