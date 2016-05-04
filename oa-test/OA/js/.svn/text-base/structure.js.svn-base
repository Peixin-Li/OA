/*------------------------------------树的初始化-------------------------------------*/
    var users = new Array();
    var departments = new Array();
    var i=0;
    $.each(arr_users, function(key, value){
      users[i++]=value;
    });
    var j=0;
    $.each(arr_departments, function(key, value){
      departments[j++]=value;
    });
    var data = new Array();
    data = data.concat(users);
    data = data.concat(departments);

    var setting = {
      edit: {
        editNameSelectAll: true, //默认修改时全选状态
        enable: true,   //是否可以修改
        showRemoveBtn: true,  //显示删除按钮
        showRenameBtn: true,   //显示修改按钮
        removeTitle: "删除该部门",
        renameTitle:"修改该部门信息",
        editNameFlag: true, //记录节点是否处于编辑状态
        drag:{
          isCopy: true,
          isMove: true,
          prev: true,
          next: true,
          inner: true,
          borderMax: 10
        }
      },
      view: {
        fontCss: {},
        selectedMulti: true,
        addHoverDom: addHoverDom,
        removeHoverDom: removeHoverDom
      },
      data: {
        simpleData: {
          enable: true
        }
      },
      callback: {
        beforeEditName: zTreeBeforeEditName,
        beforeDrop: beforeDrop,
        onClick: zTreeOnClick,
        onRename: zTreeOnRename,
        onDrop: zTreeOnDrop,
        onRemove: zTreeOnRemove
      }
    };

    /**
    *拖拽释放前函数
    *释放目标不是部门就不让释放
    **/
    function beforeDrop(treeId, treeNodes, targetNode, moveType) {
      if(user_type!="admin"){
        showHint("提示信息","你没有权限执行此操作！");
        return false;
      }else if(targetNode.type!="department"){
        showHint("提示信息","请把员工拖拽到部门上！");
        return false;
      }else return true;
    }

    /**
    *删除按钮
    *删除空的部门
    **/
    function zTreeOnRemove(event, treeId, treeNode){
      var deleteid = treeNode.id;
      $.ajax({
      type:'post',
            url: '/ajax/removeDepartment',
            dataType: 'json',
            data:{'id':deleteid},
            success:function(result){
              if(result.code == 0)
              {
                showHint("提示信息","操作成功！");
                setTimeout(function(){location.reload();},1200);
              }else if(result.code == -1){
                showHint("提示信息","删除失败！");
                init();
              }else if(result.code == -2){
                showHint("提示信息","寻找不到该部门！");
                init();
              }else if(result.code == -3){
                showHint("提示信息","部门不为空，不能删除！");
                init();
              }else if(result.code == -99){
                showHint("提示信息","你没有权限执行此操作！");
                init();
              }
            }
        });
    }

    /**
    *拖拽回调函数
    *完成后发送数据到后台
    **/
    function zTreeOnDrop(event, treeId, treeNodes, targetNode, moveType){
      var dropnode = treeNodes[0];
      var dropid = dropnode.id;
      var droppid = dropnode.pId;
      var droptype = dropnode.type;
      // 发送拖动修改过的节点id，pid和type
      $.ajax({
      type:'post',
            url: '/ajax/drag',
            dataType: 'json',
            data:{'id':dropid,'pId':droppid,'type':droptype},
            success:function(result){
              if(result.code == 0)
              {
                showHint("提示信息","操作成功！");
              }else if(result.code == -1){
                showHint("提示信息","移动失败！");
                init();
              }else if(result.code == -2){
                showHint("提示信息","寻找不到该人或部门！");
                init();
              }else if(result.code == -99){
                showHint("提示信息","你没有权限执行此操作！");
                init();
              }
            }
        });
    }

    /**
    *鼠标停留在树节点上
    *添加新建按钮、隐藏修改和删除按钮
    **/
    var newCount = 1;
    function addHoverDom(treeId, treeNode) {
      //只有部门后面有添加按钮，添加的只能是部门
      if(treeNode.type=="department"&&user_type=="admin"){
        var sObj = $("#" + treeNode.tId + "_span");
        if (treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
        var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
         + "' title='添加员工' onfocus='this.blur();'></span>";
        sObj.after(addStr);

        var btn = $("#addBtn_"+treeNode.tId);

        if (btn) btn.bind("click", function(){
          $.ajax({
            type:'post',
            url: '/ajax/getTitleByDepartment',
            dataType: 'json',
            data:{'id':parseInt(treeNode.id)},
            success:function(result){
              if(result.code == 0)
              {
                $("#input_title").children().remove();
                  $.each(result['titles'],function(){
                    var str = "<option value='"+this['title']+"'>"+this['title']+"</option>";
                    $("#input_title").append(str);
                  });
                }
              }
            });
          $("#edit-btn2").hide();
          if(treeNode.type=="department"){
            //初始化显示
            $("#user-head").parent().parent().addClass("hidden");
            $("#input_job_status").parent().show();
            $("#input_login").parent().parent().show();
            $("#input_department").parent().parent().show();
            $('#entry_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
            $("#regularized_date").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
            $("#input_birthday").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
            $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
            $("#edit-btn2").click();
            $("#input_department").attr("readonly","readonly");
            $("#hint-title").text("新增员工");
            $("#input_id").text(treeNode.id);

            if(treeNode!=null){
              $("#input_department").val(treeNode.name.split(" ")[0]);
              $("#input_cn_name").val("");
              $("#input_en_name").val("");
              $("#input_title").val("");
              $("#input_mobile").val("");
              $("#input_email").val("");
              $("#input_qq").val("");
              $("#input_native_place").val("");
              $("#input_job_description").val("");
            }

            //弹出新建员工框
            var ySet = (window.innerHeight - $("#editEmployee-div").height())/3;
            var xSet = (window.innerWidth - $("#editEmployee-div").width())/2;
            $("#editEmployee-div").css("top",ySet);
            $("#editEmployee-div").css("left",xSet);
            $("#editEmployee-div").modal({show:true});

          }else{
            showHint("提示信息","请先选择部门！");
          }
        });

        if(treeNode.isParent){
          $("#"+treeNode.tId+"_remove").css("display","none");
        }

      }else{//隐藏个人的修改和删除按钮
        $("#"+treeNode.tId+"_remove").remove();
        $("#"+treeNode.tId+"_edit").remove();
      }
    };

    /**
    *鼠标离开树节点
    *删除新建按钮
    **/
    function removeHoverDom(treeId, treeNode){
      $("#addBtn_"+treeNode.tId).unbind().remove();
    };

    /**
    *更新节点名称之前
    **/
    function zTreeBeforeEditName(treeId, treeNode) {
      var name = treeNode.name;
      treeNode.name = name.split(" ")[0];
      if(treeNode.type == "department"){
        return true;
      }else{
        return false;
      }
    }

    /**
    *更新节点名称
    **/
    function zTreeOnRename(event, treeId, treeNode, isCancel){
      var node = treeNode;
      var nodeid = node.id;
      var newpid = node.pId;
      var newname = node.name;
      $.ajax({
          type:'post',
          url: '/ajax/updateDepartment',
          dataType: 'json',
          data:{'id':nodeid,'name':newname},
          success:function(result){
            if(result.code == 0)
            {
              showHint("提示信息","操作成功！");
              setTimeout(function(){location.reload();},1200);
              init();
            }else if(result.code == -1){
              showHint("提示信息","更新失败！");
              init();
            }else if(result.code == -2){
              showHint("提示信息","找不到该部门！");
              init();
            }else if(result.code == -99){
              showHint("提示信息","你没有权限执行此操作！");
              init();
            }
          }
      });
    }

    /**
    *树的初始化
    **/
    function init(){
      $.fn.zTree.init($("#treeDemo"), setting, data);
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");

      //遍历树，修改图标
      var nodes = zTree.transformToArray(zTree.getNodes());
      var admin_arr = new Array();
      $.each(nodes,function(){
        //部门的图标修改
        if(this.type=="department"){
          this.icon="../css/img/persons.png";

          // 添加部门的下拉列表
          var str = "<option value='"+this.id+"'>"+this.name+"</option>";
          $("#newDepartment-select").append(str);

          //部门后面人数的显示
          var count_str = " [0]";
          if(typeof(this.count)!="undefined") count_str = " ["+this.count+"]";
          this.name += count_str;
          if(this.name.indexOf("总经理办公室")>-1){
            $("#treeDemo_1_span").text(this.name);
          }

          var id=this.id;
          var admin=this.admin;
          admin_arr.push({"department":id, "admin":admin});
        }else{
          
          //实习生、试用员工的突出显示
          if(this.job_status == "intern"){
            this.name += " [实习生]";
          }else if(this.job_status == "probation_employee"){
            this.name += " [试用期]";
          }

          //性别的显示
          if(this.sex=="m"){
            this.icon="../css/img/male.png";
          }else{
            this.icon="../css/img/female.png";
          }
        }
      });

      //管理员图标的修改
      $.each(admin_arr,function(){
        var department_id=this.department;
        var admin_id=this.admin;
        $.each(nodes, function(){
          if(this.id==admin_id&&this.pId==department_id) this.icon="../css/img/admin.png";
        });
      });

      //默认展开第一层
      zTree.expandNode(nodes[0]);
    }

    $(document).ready(init());


/*------------------------------------新建员工的操作-------------------------------------*/
    /**
    *新增员工操作
    **/
    function addEmployee(){
      $.ajax({
          type:'post',
          url: '/ajax/getTitleByDepartment',
          dataType: 'json',
          data:{'id':click_department_id},
          success:function(result){
            if(result.code == 0)
            {
              $("#input_title").children().remove();
              $.each(result['titles'],function(){
                var str = "<option value='"+this['title']+"'>"+this['title']+"</option>";
                $("#input_title").append(str);
              });
            }
          }
        });


      $("#edit-btn2").hide();
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var selectednode = zTree.getSelectedNodes();
      if(selectednode[0].type=="department"){
        //初始化显示
        $("#user-head").parent().parent().addClass("hidden");
        $("#input_job_status").parent().show();
        $("#input_login").parent().parent().show();
        $("#input_department").parent().parent().show();
        $('#entry_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $("#regularized_date").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $("#input_birthday").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
        $("#edit-btn2").click();
        $("#input_department").attr("readonly","readonly");
        $("#hint-title").text("新增员工");
        $("#input_id").text(selectednode[0].id);

        if(selectednode[0]!=null){
          $("#input_department").val(selectednode[0].name.split(" ")[0]);
          $("#input_cn_name").val("");
          $("#input_en_name").val("");
          $("#input_title").val("");
          $("#input_mobile").val("");
          $("#input_email").val("");
          $("#input_qq").val("");
          $("#input_native_place").val("");
          $("#input_job_description").val("");
        }

        //弹出新建员工框

        var ySet = (window.innerHeight - $("#editEmployee-div").height())/3;
        var xSet = (window.innerWidth - $("#editEmployee-div").width())/2;
        $("#editEmployee-div").css("top",ySet);
        $("#editEmployee-div").css("left",xSet);
        $("#editEmployee-div").modal({show:true});

      }else{
        showHint("提示信息","请先选择部门！");
      }
    }


/*------------------------------------显示员工详情-------------------------------------*/
    /**
    *点击树节点触发函数-显示详细信息
    **/
    var click_department_id = "";
    function zTreeOnClick(event, treeId, treeNode){
      var id = treeNode.id;
      var name = treeNode.name;
      var type = treeNode.type;
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      zTree.expandNode(treeNode);
      $.ajax({
      type:'post',
            url: '/ajax/getInfo',
            dataType: 'json',
            data:{'id':id,'type':type},
            success:function(result){
              if(result.code == 0)
              {
                  $("#details-table").parent().find("h3").remove();
                  $("#details-table").find("tbody").remove();
                  $("#details-table").append("<tbody>");
                  $.each(result['result'], function(key,value){
                    //性别中英转换
                    var sex = "";
                    if(value.sex=="m"){
                      sex="男";
                    }else sex="女";

                    //插入数据到表格中
                    var str = "<tr><td>"+value.name+"</td><td>"+value.en_name+"</td><td>"+value.department+
                              "</td><td class='hidden'>"+sex+"</td><td>"+value.title+"</td><td>"+value.mobile+
                              "</td><td class='hidden'>"+value.email+"</td><td>"+value.qq+
                              "</td><td class='hidden'>"+value.entry_day+
                              "</td><td class='hidden'>"+value.regularized_date+
                              "</td><td class='hidden'>"+value.native_place+
                              "</td><td class='hidden'>"+value.job_status+
                              "</td><td class='hidden'>"+value.job_description+
                              "</td><td class='hidden'>"+value.birthday+
                              "</td><td class='hidden photo-td'>"+value.photo+
                              "</td><td class='hidden department-id-td'>"+value.department_id+
                              "</td><td><button class='btn btn-default'>查看详情</button></td></tr>";
                    $("#details-table").append(str);
                  });
                  $("#details-table").append("</tbody>");
                  $("#details-table").find("td").each(function(){
                    $(this).addClass("pointer");
                    $(this).click(function(){showEmployee(this);});
                  });
                  click_department_id = $("#details-table").find(".department-id-td").first().text();
                  if(treeNode.type=="employee"){
                    var photo_src = $("#details-table").find(".photo-td").text();
                    $("#user-head").attr("src",photo_src);
                    $("#details-table").find("td").first().click();
                    click_department_id = $("#details-table").find(".department-id-td").text();
                  }
              }else if(result.code == -1){
                if(type=="employee"){
                  showHint("提示信息","获取详细资料失败！");
                  init();
                }else{
                  $("#details-table").find("tbody").remove();
                  $("#details-table").parent().find("h3").remove();
                  $("#details-table").parent().append("<h3 class='w100% center'>该部门还没有员工！<h3>")
                }
              }else if(result.code == -99){
                showHint("提示信息","你没有权限执行此操作！");
                init();
              }
            }
      });

      //点击部门则隐藏设置按钮, 点击个人立刻弹出详情框
      if(treeNode.type=="employee"){
        $("#edit-btn").removeClass("disabled");
        $("#set-btn").removeClass("disabled");
      }else{
        $("#edit-btn").addClass("disabled");
        $("#set-btn").removeClass("disabled");
      }

      checkSelect();
    }


    /**
    *查看员工信息详情
    **/
    function showEmployee(obj){
      //初始化隐藏
      $('#submit-btn').hide();
      $("#edit-btn2").show();
      var clickobj = obj;
      $("#hint-title").text("查看员工信息");
      $("#input_login").parent().parent().hide();
      $("#user-head").parent().parent().removeClass("hidden");

      //删除之前加载的信息，隐藏输入框
      $('#editEmployee-div').find('div.info-div').remove();
      $('#editEmployee-div').find('input').addClass("hidden");
      $("#input_sex").addClass("hidden");
      $("#input_job_status").addClass("hidden");
      $("#input_job_description").addClass("hidden");
      $("#input_title").addClass("hidden");
      
      //赋值到详情各项中
      var cn_name = $(clickobj).parent().children().first();
      var en_name = cn_name.next();
      var department = en_name.next();
      var sex = en_name.next().next();
      var title = sex.next();
      var mobile = title.next();
      var email = mobile.next();
      var qq = email.next();
      var entry_day = qq.next();
      var regularized_date = entry_day.next();
      var native_place = regularized_date.next();
      var job_status = native_place.next();
      var job_description = job_status.next();
      var birthday = job_description.next();
      click_department_id = birthday.next().next().text();

      var job_status_text;
      if(job_status.text()=="intern"){
        job_status_text="实习生";
      }else if(job_status.text()=="probation_employee"){
        job_status_text="试用期";
      }else if(job_status.text()=="formal_employee"){
        job_status_text="正式员工";
      }

      //将详情加入div，显示出来
      $("#input_cn_name").parent().append("<div class='info-div'>"+cn_name.text()+"</div>");
      $("#input_en_name").parent().append("<div class='info-div'>"+en_name.text()+"</div>");
      $("#input_sex").parent().append("<div class='info-div'>"+sex.text()+"</div>");
      $("#input_title").parent().append("<div class='info-div'>"+title.text()+"</div>");
      $("#input_mobile").parent().append("<div class='info-div'>"+mobile.text()+"</div>");
      $("#input_email").parent().append("<div class='info-div'>"+email.text()+"</div>");
      $("#input_qq").parent().append("<div class='info-div'>"+qq.text()+"</div>");
      $("#entry_date").parent().append("<div class='info-div'>"+entry_day.text()+"</div>");
      $("#regularized_date").parent().append("<div class='info-div'>"+regularized_date.text()+"</div>");
      $("#input_native_place").parent().append("<div class='info-div'>"+native_place.text()+"</div>");
      $("#input_department").parent().append("<div class='info-div'>"+department.text()+"</div>");
      $("#input_job_description").parent().append("<div class='info-div'>"+job_description.text()+"</div>");
      $("#input_job_status").parent().append("<div class='info-div'>"+job_status_text+"</div>");
      $("#input_birthday").parent().append("<div class='info-div'>"+birthday.text()+"</div>");

      //将详情填入输入框中（隐藏状态）
      setInfo(clickobj);

      //设置ID
      var s_name = cn_name.text();
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var nodes = zTree.transformToArray(zTree.getNodes());
      var input_id="";
      $.each(nodes,function(){
        var id=this.id;
        if(this.name.indexOf(s_name) > -1){
          input_id = id;
          if(this.icon=="../css/img/admin.png"){
            $("#setAdmin-btn").addClass("hidden");
            $("#cancelAdmin-btn").removeClass("hidden");
          }else{
            $("#setAdmin-btn").removeClass("hidden");
            $("#cancelAdmin-btn").addClass("hidden");
          }
        }
      });
      $("#input_id").text(input_id);

      //权限设置，如果不是管理员并且不是本人的话，就隐藏编辑按钮
      if(user_type!="admin" && user_id != input_id){
        $("#details-btn").hide();
      }else{
        $("#details-btn").show();
      }

      //设置高度，弹出详情框
      var ySet = (window.innerHeight - $("#editEmployee-div").height())/3;
            var xSet = (window.innerWidth - $("#editEmployee-div").width())/2;
            $("#editEmployee-div").css("top",ySet);
            $("#editEmployee-div").css("left",xSet);
      $("#editEmployee-div").modal({show:true});


    }


    //切换基本信息和工作信息
    function switchTable(id){
      if(id=="basic-tab"){
        $("#basic-tab").parent().addClass("active");
        $("#work-tab").parent().removeClass("active");
        $("#basic-info").show();
        $("#work-info").hide();
      }else if(id=="work-tab"){
        $("#basic-tab").parent().removeClass("active");
        $("#work-tab").parent().addClass("active");
        $("#work-info").show();
        $("#basic-info").hide();
      }
    }

    //将信息赋值到输入框中
    click_title_text = "";
    function setInfo(obj){
      var clickobj = obj;
      //赋值到详情各项中
      var cn_name = $(clickobj).parent().children().first();
      var en_name = cn_name.next();
      var department = en_name.next();
      var sex = en_name.next().next();
      var title = sex.next();
      var mobile = title.next();
      var email = mobile.next();
      var qq = email.next();
      var entry_day = qq.next();
      var regularized_date = entry_day.next();
      var native_place = regularized_date.next();
      var job_status = native_place.next();
      var job_description = job_status.next();
      var birthday = job_description.next();

      //将信息填入输入框中
      $("#input_cn_name").val(cn_name.text());
      $("#input_en_name").val(en_name.text());
      if(sex.text()=="男"){
        $("#input_sex").find("input").each(function(){
          if($(this).val()=="m") $(this).attr("checked","checked");
        });
      }else{
        $("#input_sex").find("input").each(function(){
          if($(this).val()=="f") $(this).attr("checked","checked");
        });
      }

      if(job_status.text()=="intern"){
          $("#input_job_status").find("input").each(function(){
            if($(this).val()=="intern") $(this).attr("checked","checked");
          });
        }else if(job_status.text()=="probation_employee"){
          $("#input_job_status").find("input").each(function(){
            if($(this).val()=="probation_employee") $(this).attr("checked","checked");
          });
        }else if(job_status.text()=="formal_employee"){
          $("#input_job_status").find("input").each(function(){
            if($(this).val()=="formal_employee") $(this).attr("checked","checked");
          });
        }

        $.ajax({
          type:'post',
          url: '/ajax/getTitleByDepartment',
          dataType: 'json',
          data:{'id':click_department_id},
          success:function(result){
            if(result.code == 0)
            {
              $("#input_title").children().remove();
              $.each(result['titles'],function(){
                var str = "<option value='"+this['title']+"'>"+this['title']+"</option>";
                $("#input_title").append(str);
              });
            }
          }
        });
       click_title_text = title.text();
      // $("#input_title").val(title.text());
      $("#input_mobile").val(mobile.text());
      $("#input_email").val(email.text());
      $("#input_qq").val(qq.text());
      $("#entry_date").val(entry_day.text());
      $("#regularized_date").val(regularized_date.text());
      $("#input_department").val(department.text());
      $("#input_native_place").val(native_place.text());
      $("#input_job_description").val(job_description.text());
      $("#input_birthday").val(birthday.text());

      
    }

/*------------------------------------修改员工信息-------------------------------------*/
    /**
    *在详情窗口点击编辑
    **/
    function editswitch(){
      
      $("#input_title").val(click_title_text);

      //初始化隐藏
      $("#hint-title").text("编辑员工信息");
      $("#edit-btn2").hide();
      $('#submit-btn').show();

      //删除信息显示的div、显示输入框
      $("#input_sex").removeClass("hidden");
      if(user_type!="admin"){
        //其他信息只读、只有电话，英文名，籍贯，性别，头像可以修改
        $('#editEmployee-div').find('input').each(function(){
          var id = $(this).attr("id");
          if(id!="input_en_name" && id!="input_mobile" && id!="input_native_place"){
            $(this).attr("readonly","readonly");
          }
        });

        //工作描述、工作类型不能修改
        $('#editEmployee-div').find('div.info-div').each(function(){
          var id = $(this).prev().attr("id");
          if(id!="input_job_description" && id!="input_job_status"){
            $(this).remove();
          }
        });
        $('#editEmployee-div').find('input').removeClass("hidden");
        $('#input_title').removeClass("hidden");
      }else{
        $('#editEmployee-div').find('div.info-div').remove();
        $('#editEmployee-div').find('input').removeClass("hidden");
        $('#input_title').removeClass("hidden");
        $("#input_job_status").removeClass("hidden");
        $("#input_job_description").removeClass("hidden");
        $('#entry_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $("#regularized_date").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $("#input_birthday").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
      }
    }

    /**
    *工具栏编辑按钮
    **/
    function editEmployee(){
      $("#edit-btn2").show();
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var selectednode = zTree.getSelectedNodes();
      $("#hint-title").text("编辑员工信息");

      if(selectednode[0].type=="employee"){
        //初始化隐藏
        $("#user-head").parent().parent().removeClass("hidden");
        $("#input_sex").removeClass("hidden");
        $("#input_job_status").removeClass("hidden");
        $("#input_login").parent().parent().hide();
        $("#input_id").text(selectednode[0].id);

        //将详情填入输入框中
        var cn_name = $("#details-table").find("tbody").children().children().first();
        var en_name = cn_name.next();
        var sex = en_name.next().next();
        var title = sex.next();
        var mobile = title.next();
        var email = mobile.next();
        var qq = email.next();
        var entry_day = qq.next();
        var regularized_date = entry_day.next();
        var native_place = regularized_date.next();
        var job_status = native_place.next();
        var job_description = job_status.next();
        var birthday = job_description.next();

        $("#input_cn_name").val(cn_name.text());
        $("#input_en_name").val(en_name.text());
        if(sex.text()=="男"){
          $("#input_sex").find("input").each(function(){
            if($(this).val()=="m") $(this).attr("checked","checked");
          });
        }else{
          $("#input_sex").find("input").each(function(){
            if($(this).val()=="f") $(this).attr("checked","checked");
          });
        }

        if(job_status.text()=="intern"){
          $("#input_job_status").find("input").each(function(){
            if($(this).val()=="intern") $(this).attr("checked","checked");
          });
        }else if(job_status.text()=="probation_employee"){
          $("#input_job_status").find("input").each(function(){
            if($(this).val()=="probation_employee") $(this).attr("checked","checked");
          });
        }else if(job_status.text()=="formal_employee"){
          $("#input_job_status").find("input").each(function(){
            if($(this).val()=="formal_employee") $(this).attr("checked","checked");
          });
        }

        $("#input_title").val(title.text());
        $("#input_mobile").val(mobile.text());
        $("#input_email").val(email.text());
        $("#input_qq").val(qq.text());
        $("#entry_date").val(entry_day.text());
        $("#regularized_date").val(regularized_date.text());
        $("#input_native_place").val(native_place.text());
        $("#input_job_description").val(job_description.text());
        $("#input_birthday").val(birthday.text());
        $("#edit-btn2").click();

        //删除信息显示的div、显示输入框
        $('#editEmployee-div').find('div.info-div').remove();
        $('#editEmployee-div').find('input').removeClass("hidden");

        //弹出详情框
        var ySet = (window.innerHeight - $("#editEmployee-div").height())/3;
            var xSet = (window.innerWidth - $("#editEmployee-div").width())/2;
            $("#editEmployee-div").css("top",ySet);
            $("#editEmployee-div").css("left",xSet);
        $("#editEmployee-div").modal({show:true});
      }else{
        showHint("提示信息","请先选择员工！");
      }
    }

    /**
    *提交编辑过的员工信息
    **/
    function submitEdit(){
      var id = $("#input_id").text();
      var cn_name = $("#input_cn_name").val();
      var en_name = $("#input_en_name").val();
      var sex = $('input[name="input_sex"]:checked').val();
      var title = $("#input_title").val();
      var mobile = $("#input_mobile").val();
      var email = $("#input_email").val();
      var qq = $("#input_qq").val();
      var native_place = $("#input_native_place").val();
      var regularized_date = $("#regularized_date").val();
      var job_description = $("#input_job_description").val();
      var birthday = $("#input_birthday").val();
      var entry_date = $("#entry_date").val();
      var job_status = $('input[name="input_job_status"]:checked').val();

      var mobile_pattern = /^\d{1}\d{10}$/;
      var qq_pattern = /^\d+$/;
      var email_pattern = /^[\w\-\_\.]+\@[\w\-\_\.]+$/;
      var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
      
      if(!mobile_pattern.exec(mobile)){
        showHint("提示信息","电话格式不正确！");
      }else if(cn_name==""){
        showHint("提示信息","姓名不能为空！");
      }else if(title==""){
        showHint("提示信息","职位不能为空！");
      }else if(!email_pattern.exec(email)){
        showHint("提示信息","email格式不正确！");
      }else if(!qq_pattern.exec(qq)){
        showHint("提示信息","QQ格式不正确！");
      }else if(!date_pattern.exec(entry_date)){
        showHint("提示信息","入职日期格式不正确！");
      }else if(!date_pattern.exec(regularized_date)){
        showHint("提示信息","转正日期格式不正确！");
      }else if(!date_pattern.exec(birthday)){
        showHint("提示信息","出生日期格式不正确！");
      }else if(native_place==""){
        showHint("提示信息","籍贯不能为空！");
      }else if(job_description==""){
        showHint("提示信息","职位描述不能为空！");
      }else{
      

      if($("#hint-title").html()=="新增员工"){
        var login = $("#input_login").val();

        if(login==""){
          showHint("提示信息","域用户名不能为空！");
        }else{

        $.ajax({
          type:'post',
          url: '/ajax/createUser',
          dataType: 'json',
          data:{'pId':id, 'entry_day':entry_date,'birthday':birthday,'job_description':job_description,'regularized_date':regularized_date, 'native_place':native_place, 'job_status':job_status, 'login':login, 'cn_name':cn_name, 'en_name':en_name, 'sex':sex, 'title':title, 'mobile':mobile, 'email':email, 'qq':qq},
          success:function(result){
            if(result.code == 0)
            {
              showHint("提示信息","新增员工成功！");
              $("#editEmployee-div").fadeOut(200);
              setTimeout(function(){location.href="/oa/structure"},1200);
              init();
            }else if(result.code == -1){
              showHint("提示信息","新增员工失败！");
              init();
            }else if(result.code == -2){
              showHint("提示信息","域用户名重复！");
              init();
            }else if(result.code == -3){
              showHint("提示信息","信息不能为空！");
              init();
            }else if(result.code == -4){
              showHint("提示信息","性别错误！");
              init();
            }else if(result.code == -5){
              showHint("提示信息","职位状态错误！");
              init();
            }else if(result.code == -6){
              showHint("提示信息","转正日期错误！");
              init();
            }else if(result.code == -7){
              showHint("提示信息","请输入正确的email！");
              init();
            }else if(result.code == -99){
              showHint("提示信息","你没有权限执行此操作！");
              init();
            }
          }
        });
        }
      }else{
        $.ajax({
          type:'post',
          url: '/ajax/editUser',
          dataType: 'json',
          data:{'id':id,'entry_day':entry_date,'cn_name':cn_name,'birthday':birthday,'job_description':job_description,'regularized_date':regularized_date, 'native_place':native_place,'job_status':job_status, 'en_name':en_name, 'sex':sex, 'title':title, 'mobile':mobile, 'email':email, 'qq':qq},
          success:function(result){
            if(result.code == 0)
            {
              showHint("提示信息","修改员工信息成功！");
              $("#editEmployee-div").fadeOut(200);
              setTimeout(function(){location.reload();},1200);
              init();
            }else if(result.code == -1){
              showHint("提示信息","修改员工信息失败！");
              init();
            }else if(result.code == -2){
              showHint("提示信息","找不到该员工！");
              init();
            }else if(result.code == -3){
              showHint("提示信息","信息不能为空！");
              init();
            }else if(result.code == -4){
              showHint("提示信息","性别错误！");
              init();
            }else if(result.code == -5){
              showHint("提示信息","职位状态错误！");
              init();
            }else if(result.code == -6){
              showHint("提示信息","转正日期格式错误！");
              init();
            }else if(result.code == -7){
              showHint("提示信息","邮件格式错误！");
              init();
            }else if(result.code == -99){
              showHint("提示信息","你没有权限执行此操作！");
              init();
            }
          }
        });
      }
      }
    }


/*------------------------------------删除员工的操作-------------------------------------*/
/**
*删除员工提醒
**/
function deleteConfirm(){
  var zTree = $.fn.zTree.getZTreeObj("treeDemo");
  var selectednode = zTree.getSelectedNodes();
  if(selectednode[0].type!="employee"){
    showHint("提示信息","请先选择员工！");
  }else if(selectednode[0].icon=="../css/img/admin.png"){
    showHint("提示信息","请先取消部门负责人！");
  }else{
    var id = selectednode[0].id;
    var name = selectednode[0].name;
    var content  = "确认删除 "+name+" 吗?";
    var f1 = "deleteEmployee("+id+")";
    showConfirm("删除员工",content,"确认",f1,"取消");
  }
}

function deleteEmployee(id){
  var id = id;
  $.ajax({
    type:'post',
    url: '/ajax/deleteuser',
    dataType: 'json',
    data:{'user_id':id},
    success:function(result){
      if(result.code == 0)
      {
        showHint("提示信息","删除成功！");
        setTimeout(function(){location.reload();},1200);
      }else if(result.code == -1){
        showHint("提示信息","删除失败！");
        init();
      }else if(result.code == -2){
        showHint("提示信息","员工ID不正确！");
        init();
      }else if(result.code == -3){
        showHint("提示信息","没有找到该员工！");
        init();
      }else if(result.code == -4){
        showHint("提示信息","用户不属于该部门！");
        init();
      }else if(result.code == -99){
        showHint("提示信息","你没有权限执行此操作！");
        init();
      }
    }
  });
}



/*------------------------------------设置部门负责人-------------------------------------*/
    /**
    *添加部门负责人
    **/
    function setAdmin(){
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var selectednode = zTree.getSelectedNodes();
      var selectedpid = "";
      var selectedid = "";

      var nodes = zTree.transformToArray(zTree.getNodes());

      if(selectednode[0].type == "department"){
        selectedid = $("#input_id").text();
        $.each(nodes,function(){
          if(this.id == selectedid ){
            if(this.type == "admin"||this.type == "employee"){
              selectedpid = this.pId;
            }
          }
        });
      }else{
        selectedid = selectednode[0].id;
        selectedpid = selectednode[0].pId;
      }

      $.ajax({
            type:'post',
            url: '/ajax/departmentAdmin',
            dataType: 'json',
            data:{'pId':selectedpid,'id':selectedid},
            success:function(result){
              if(result.code == 0)
              {
                showHint("提示信息","操作成功！");
                setTimeout(function(){location.reload();},1200);
              }else if(result.code == -1){
                showHint("提示信息","添加部门负责人失败！");
                init();
              }else if(result.code == -2){
                showHint("提示信息","找不到该部门！");
                init();
              }else if(result.code == -3){
                showHint("提示信息","找不到该用户！");
                init();
              }else if(result.code == -4){
                showHint("提示信息","用户不属于该部门！");
                init();
              }else if(result.code == -99){
                showHint("提示信息","你没有权限执行此操作！");
                init();
              }
            }
          });
    }

    /**
    *删除部门负责人
    **/
    function cancelAdmin(){
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var selectednode = zTree.getSelectedNodes();
      var selectedpid = "";
      var selectedid = "";
      var nodes = zTree.transformToArray(zTree.getNodes());

      if(selectednode[0].type == "department"){
        selectedid = $("#input_id").text();
        $.each(nodes,function(){
          if(this.id == selectedid ){
            if(this.type == "admin"||this.type == "employee"){
              selectedpid = this.pId;
            }
          }
        });
      }else{
        selectedpid = selectednode[0].pId;
      }
      $.ajax({
            type:'post',
            url: '/ajax/cancelDepartmentAdmin',
            dataType: 'json',
            data:{'pId':selectedpid},
            success:function(result){
              if(result.code == 0)
              {
                showHint("提示信息","操作成功！");
                setTimeout(function(){location.href="/oa/structure";},1200);
              }else if(result.code == -1){
                showHint("提示信息","删除部门负责人失败！");
                init();
              }else if(result.code == -2){
                showHint("提示信息","找不到该部门！");
                init();
              }else if(result.code == -99){
                showHint("提示信息","你没有权限执行此操作！");
                init();
          }
        }
      });
    }

    /**
    *检测选择的节点是否可以进行负责人设置
    **/
    function checkSelect(){
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var selectednode = zTree.getSelectedNodes();
      if(selectednode[0]==null){
        $("#setAdmin").css("display","none");
        $("#cancelAdmin").css("display","none");
        $("#addEmployee").css("display","none");
        $("#deleteConfirm").css("display","none");
      }else if(selectednode[0].type=="department"){
        $("#setAdmin-btn").addClass("hidden");
        $("#cancelAdmin-btn").addClass("hidden");
        $("#setAdmin").css("display","none");
        $("#cancelAdmin").css("display","none");
        $("#addEmployee").css("display","block");
        $("#deleteConfirm").css("display","none");
      }else if(selectednode[0].icon=="../css/img/admin.png"){
        $("#setAdmin-btn").addClass("hidden");
        $("#cancelAdmin-btn").removeClass("hidden");
        $("#setAdmin").css("display","none");
        $("#cancelAdmin").css("display","block");
        $("#addEmployee").css("display","none");
        $("#deleteConfirm").css("display","block");
      }else{
        $("#setAdmin-btn").removeClass("hidden");
        $("#cancelAdmin-btn").addClass("hidden");
        $("#setAdmin").css("display","block");
        $("#cancelAdmin").css("display","none");
        $("#addEmployee").css("display","none");
        $("#deleteConfirm").css("display","block");
      }
    }

    /**
    *取消部门负责人提示
    **/
    function cancelRemind(){
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      var selectednode = zTree.getSelectedNodes();
      var nodes = zTree.transformToArray(zTree.getNodes());
      if(selectednode[0].type=="department"){
        var id = $("#input_id").text();
        var name = "";
        $.each(nodes,function(){
          if(this.id == id){
            if(this.type == "admin"||this.type == "employee"){
              name = this.name;
            }
          }
        });
        showConfirm("取消部门负责人","是否确认取消"+name+"的负责人职位？","是","cancelAdmin();","否");
      }else{
        showConfirm("取消部门负责人","是否确认取消"+selectednode[0].name+"的负责人职位？","是","cancelAdmin();","否");
      }
    }


/*------------------------------------全部折叠、展开-------------------------------------*/
    /**
    *全部展开
    **/
    function expandTree(){
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      zTree.expandAll(true);
      $("#expandTree-btn").addClass("hidden");
      $("#closeTree-btn").removeClass("hidden");
    }

    /**
    *全部折叠
    **/
    function closeTree(){
      var zTree = $.fn.zTree.getZTreeObj("treeDemo");
      zTree.expandAll(false);
      $("#expandTree-btn").removeClass("hidden");
      $("#closeTree-btn").addClass("hidden");
    }

/*------------------------------------搜索-------------------------------------*/
/**
*搜索
**/
function search(){
  $("#search").blur();
  var search_str = $("#search").val();
  var zTree = $.fn.zTree.getZTreeObj("treeDemo");
  var nodes = zTree.transformToArray(zTree.getNodes());
  var s_flag = 0;
  $.each(nodes,function(){
    if(this.name.indexOf(search_str)>-1 && this.type=="employee"){
      zTree.expandNode(this.getParentNode(), true, true, true);
      zTreeOnClick('click',this.tId,this);
      zTree.selectNode(this);
      s_flag = 1;
      return false;
    }
  });
  if(s_flag == 0){
    showHint("提示信息","查找不到该员工！");
  }
}

/*------------------------------------搜索-------------------------------------*/
/**
*搜索
**/
function newDepartment(){
  var department_name = $("#newDepartment-input").val();
  var pid = $("#newDepartment-select").val();
  if(department_name==""){
    showHint("提示信息","请输入新部门的名称");
  }else{
    $.ajax({
      type:'post',
      url: '/ajax/createDepartment',
      dataType: 'json',
      data:{'pId':pid,'name':department_name},
      success:function(result){
        if(result.code == 0)
        {
          showHint("提示信息","操作成功！");
          setTimeout(function(){location.reload();},1200);
          init();
        }else if(result.code == -1){
          showHint("提示信息","新建部门失败！");
          init();
        }else if(result.code == -2){
          showHint("提示信息","找不到该部门！");
          init();
        }else if(result.code == -3){
          showHint("提示信息","部门名称重复！");
          init();
        }else if(result.code == -99){
          showHint("提示信息","你没有权限执行此操作！");
          init();
        }
      }
    });
  }
}