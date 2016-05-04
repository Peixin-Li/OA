    $(document).ready(function(){
        var w_table = $("#wait-editor");
        var c_table = $("#c-worker-editor");
        // initTable(w_table , editor_wait);
        // initTable(c_table , c_editor);
        var dir_id = 0;
        if(location.href.indexOf('dir_id') != -1) {
            //获得目录id
            dir_id = parseInt(location.href.slice(location.href.indexOf('dir_id')+7)) || 0;
        }

        var file_type = "";
        if(location.href.indexOf('file_type')!=-1) {

            file_type = location.href.substr(location.href.indexOf('file_type')+10,8);
            if(file_type == 'w_editor') {
                initDraft();
            } else {
                initDirDiv(dir_id);
            }
        } else {
            initDirDiv(dir_id);
            // alert(dir_id);
        }
        $.each(dir_list_all, function(){
            // var dir_list_option = '<option value=' + this['dir_id'] + '>' + this['dir_name'] + '</option>';
            var dir_list_option = '<option value=' + this['dir_id'] + '>' + getAllPath(this) + '</option>';
            $("#new_parent_dir_select").append(dir_list_option);
            $("#publish_dir_select").append(dir_list_option);
            $("#relocation_dir_select").append(dir_list_option);
            $("#relocation_file_select").append(dir_list_option);
        });

        $.each(user_list, function(){
            cn_name_list.push(this['cn_name']);
        });

        $("#new_editor_input").autocomplete({
            source: cn_name_list
        });

        if(location.href.indexOf('/file/') != -1) {
            file_id = parseInt(location.href.slice(location.href.indexOf('file')+5)) || 0;
            jumpEditorContent(file_id);
            console.log(file_id);
        }
        window.stop();
    });
    //查找出改文件夹的绝对路径
    function getAllPath(dir_itms) {
        var count = 0;
        var path = "";
        if(dir_itms) {
            path = '/'+ dir_itms['dir_name'];
            var dir = dir_itms;
            while((dir['parent_id'] != 0)&&(count<20)) {
                $.each(dir_list_all, function() {
                    if(dir['parent_id']==this['dir_id']) {
                        path = '/' + this['dir_name'] + path;
                        dir = this;
                    }
                });
                count = count + 1;
            }
        }
        return path;
    }
    //根据文件id查找文件名(全路径)
    function getWaitFilePathById(file_id) {
        var file_editor, dir;
        $.each(editor_list_all, function(){
            if(this.id==file_id){
                file_editor = this;
                return false;
            }
        });
        if(file_editor) {
            $.each(dir_list_all, function() {
                if(this.dir_id == file_editor.dir_id) {
                    dir = this;
                    // alert(this.dir_name);
                    return false;
                }
            });
        }
        if(dir || file_editor) {
            return getAllPath(dir) + '/' + file_editor['title'];
        }
        else {
            return "";
        }
    }

    //初始化 个人文档、协同编辑文档
    function initTable(obj , data) {
        $.each(data , function (key, value) {
            var c_editor_js = value['c_editor'];                     //将共同编辑者id转换为姓名
            var c_editor_name = '';
            var status = '待发布';                                         //翻译文件状态
            if (c_editor_js) {
                for (var i = 0; i < c_editor_js.length; i++) {
                    if ( (c_editor_js[i]!="")&&(c_editor_name!="") )
                        c_editor_name = c_editor_name + findNameById(c_editor_js[i]) +'、';
                    else if(c_editor_js[i]!="")
                        c_editor_name = findNameById(c_editor_js[i]) + '、';
                };
            }
            if (value['lock_status'] == 'lock')
                status = "已锁定"
            else if (value['approve_user_id'] != 0)
                status = "已提交审核"
            var button_div = "";
            var article_status, publish_dir;
            if ( $(obj).attr('id') == "wait-editor" ) {
                if(value['approve_user_id'] != 0)
                    article_status = "审核中";
                else if(value['lock_status'] == 'lock')
                    article_status = "已锁定";
                else
                    article_status = "未发布";
                button_div = '<tr>' +
                '<td class="hidden">' + value['id'] + '</td>' +  
                '<td class="left" style="color:#428bca;cursor:pointer;" onclick="jumpEditorContent(' + value['id'] +')">'+ '<img src="images/doc.png">' + value['title'] + '</td>' +
                '<td >' + findNameById(value['last_editor_id']) + '</td>' +
                '<td>' + value['update_time'].substr(5,5) + '</td>' +
                '<td>' + article_status + '</td>' +
                '<td>' + getWaitFilePathById(value['parent_id']) + '</td>' +
                '<td>';
                if(value['owner_id'] != userId) {
                    //判断是否协同编辑
                    button_div += '<button class="btn btn-success w50 pd3 mr5" onclick="editContent(' + value['id'] + ')">编辑</button>';
                } else {
                    if(article_status == '审核中') {
                        button_div += '<button class="btn btn-success w80 pd3 mr5" onclick="cancelApply(' + value['id'] + ')">取消申请</button>';
                    } else if(article_status == '未发布') {
                    button_div += '<button class="btn btn-success w50 pd3 mr5" onclick="editContent(' + value['id'] + ')">编辑</button><button class="btn btn-success w50 pd3 mr5" onclick="showDeleteEditorFile(this)">删除</button><button class="btn btn-success w80 pd3" onclick="showApplyEditor(this)">申请发布</button>';
                    }
                }
                button_div += '</td></tr>';
            } 

            $(obj).append(button_div);
        });
    }

    function lastNavLose() {
        $('#editor-head-guide span a').css('color','#428bca');
        // $('#editor-head-guide span:last').find('a').css('color','#000');
    }
    var oneinit = true;
    function initDirDiv(dir_level) {        //文件夹初始化
        if(!$('#newdir').length) {
            $('#newfile').length && $('#newfile').remove();
            var btn = '<button id="newdir" class="btn btn-success fr" onclick="newEditorDir();">新建文件夹</button>';
            $('#editor-head-guide span').append(btn);
        }
        $("#editor-dir-div").children('div').remove();
        if(!oneinit) {
            if(dir_level !=0 ){
                var guide_content =  '<a class="pointer" onclick="removeNextA(this);tourl('+ dir_level +');">&nbsp;/&nbsp;'+
                findDirNameById(dir_level) +'</a>';
                $("#editor-head-span a:last").after(guide_content);
                lastNavLose();
            }
            
        } else {
            //加载面包屑导航,
            oneinit = false;
            if(dir_level != 0) {
                var guide_content = "";
                $.each(parent_list,function(key,value) {
                     guide_content = '<a class="pointer" onclick="removeNextA(this);tourl('+ value['dir_id'] +');">&nbsp;/&nbsp;'+
                                         value['dir_name'] +'</a>';
                    $('#editor-home-a').after(guide_content);
                    // $('#editor-head-guide span:last').animate({'margin-left':'50px','margin-right':'50px'});
                    lastNavLose();
                });
            }
        }

        $("#editor-dir-div").children().remove();
        var top_div = '<div class="dir_div" style="padding-bottom: 30px;margin-top:5px;">' + 
        '<div style="float:left;width: 400px;"><span class="dir_name">名称</span></div>' +
        '<div style="width:800px" class="fl">' + 
        '<div class="wp30 fl"><span class="dir_content">作者</span></div>'+
        // '<div class="wp30 fl"><span class="dir_content">创建时间</span></div>' + 
        '<div class="wp30 fl"><span class="dir_content">最后修改时间</span></div>' + 
        '</div></div>';
        $("#editor-dir-div").append(top_div);

        $.each(dir_list_all,function() {
            if (this['parent_id'] == dir_level) {
                var dir_content =  '<hr style="margin:5px;">' + 
                '<div class="dir_div">' + 
                '<div style="float:left;width: 400px;" onMouseOver="showDirOptions(' + this['dir_id'] + ')" onMouseOut="hideDirOptions(' + this['dir_id'] + ')">' +
                '<img src="images/dir-small.png" onclick="tourl(' + this['dir_id'] + ');">' +
                '<span class="dir_name" onclick="tourl(' + this['dir_id'] + ');">'+this['dir_name']+'</span>';
                if(is_admin=="yes") {
                    dir_content += '<span id="dir-option-span-' + this['dir_id'] + '" class="hidden" style="font-size: 12px;font-family: inherit;">' +
                    '<a class="pointer pl5 f10" onclick="showRelocationEditorDir(' + this['dir_id'] + ')">移动</a>' + 
                    '<a class="pointer pl5 f10" onclick="showRenameEditorDir(' + this['dir_id'] + ')">重命名</a>' + 
                    '<a class="pointer pl5 f10" onclick="showDeleteEditorDir(' + this['dir_id'] + ')">删除</a>' + 
                    '<span>';
                }
                dir_content += '</div> <div style="width:800px;" class="fl">' +
                    '<div class="wp30 fl"><span class="dir_content">' + findNameById(this['create_user']) +'</span></div>' +
                    '<div class="wp30 fl"><span class="dir_content">' + this['update_time'].substr(5,5) +'</span></div>' +
                    '</div></div>';
                $("#editor-dir-div").append(dir_content);
            }
        });

        $.each(editor_list_all,function() {
            if (this['dir_id'] == dir_level) {
                var file_content =  '<hr style="margin:5px">' + 
                '<div class="file_div">' + 
                '<div style="float:left;width: 400px;" onMouseOver="showFileOptions(' + this['id'] + ')" onMouseOut="hideFileOptions(' + this['id'] + ')">' +
                '<img src="images/doc.png" onclick="jumpEditorContent(' + this['id'] + ',this);">' +
                '<span class="file_name text-cut-200" onclick="jumpEditorContent(' + this['id'] + ',this);">'+this['title']+'</span>';
                if(is_admin=="yes") {
                    file_content += '<span id="file-option-span-' + this['id'] + '" class="hidden" style="font-size: 12px;font-family: inherit;">' +
                    '<a class="pointer pl5 f10" onclick="showRelocationEditorFile(' + this['id'] + ',\'' + this['title'] + '\')">移动</a>' + 
                    '<a class="pointer pl5 f10" onclick="showDeleteEditorFileS(' + this['id'] + ',\'' + this['title'] + '\')">删除</a>' + 
                    '<span>';
                }
                file_content += '</div> <div style="width:800px;" class="fl">' +
                '<div class="wp30 fl"><span class="dir_content">' + findNameById(this['owner_id']) +'</span></div>' +
                // '<div class="wp30 fl"><span class="dir_content">' + this['create_time'].substr(5,5) +'</span></div>' +
                '<div class="wp30 fl"><span class="dir_content">' + this['update_time'].substr(5,5) +'</span></div>' +
                '</div></div>';
                $("#editor-dir-div").append(file_content);

            }
        });

        if(dir_level == 0) {
            //草稿
            var str = '<hr style="margin:5px"><div class="dir_div"><div id="draft-btn" style="float:left;width: 400px;" ><img src="images/draft.png" ><span class="dir_name" >草稿</span></div> <div style="width:800px;" class="fl"><div class="wp30 fl"><span class="dir_content">系统默认</span></div><div class="wp30 fl"><span class="dir_content">08-25</span></div></div></div>';
            $('#editor-dir-div .dir_div').eq(0).after(str);
            $("#draft-btn img,#draft-btn span").on('click', function(){
              var state = history.state;
              history.pushState(state, "", '/user/editor/file_type/w_editor');
              initDraft();
            });
        }
        $('.dir_div,.file_div').hover(function(){
          $(this).css('background-color','#f9f9f9');
        },function(){
          $(this).css('background-color','#fff');
        }); 
    }

    function tourl(id) {
        var state = history.state;
        history.pushState(state, "", '/user/editor/dir_id/'+id);
        initDirDiv(id);
    }

    //初始化草稿
    function initDraft() {
        //default dir_level = -1
        //导航
        var guide_content =  '<a class="pointer" onclick="removeNextA(this);initDraft();">&nbsp;/&nbsp;草稿</a>';
        $("#editor-home-a").after(guide_content);
        // $('#editor-head-guide span:last').animate({'margin-left':'50px','margin-right':'50px'});
        lastNavLose();
        //新建文件夹 改为 新建文件
        var newFile = '<button id="newfile" class="btn btn-success fr" onclick="window.open(\'/user/neweditor\');">新建文件</button>';
        $('#newdir').before(newFile).remove();
        //文件
        $("#editor-dir-div").children().remove();
        var tableTitle = '<div class=\'pl50 pr50\'>' +
                            '<table class=" m0 table center table-hover pl50">' +
                                '<thead><tr class="">' +
                                   '<th class="w10 center hidden">ID</th>' +
                                    '<th class="w150 left">标题</th>' +
                                    '<th class="w80 center">最后修改者</th>' +
                                    '<th class="w80 center">最后修改时间</th>' +
                                    '<th class="w80 center">当前状态</th>' +
                                    '<th class="w150 center">默认发布路径</th>' +
                                    '<th class="w200 center">操作</th>' +
                                  '</tr></thead>' +
                                '<tbody id="wait-editor"></tbody>' + 
                            '</table>' +
                        '</div>';
        $("#editor-dir-div").append(tableTitle);

        initTable($("#wait-editor") , editor_wait);
        initTable($("#wait-editor"), c_editor_list);
    }

    function initCEditor() {
        var guide_content =  '<span><a class="pointer" onclick="removeNextSpan(this);initCEditor();">协同编辑</a></span>';
        $("#editor-head-guide").append(guide_content);
        // $('#editor-head-guide span:last').animate({'margin-left':'50px','margin-right':'50px'});
        lastNavLose();
        //不允许在此新建文件以及新建文件夹
        // $('#newdir').remove();

        $('#editor-dir-div').children().remove();
        var tableTitle = '<div class=\'pl50 pr50\'>' +
                            '<table class=" m0 table center table-hover pl50">' +
                                '<thead><tr class="">' +
                                   '<th class="w10 center hidden">ID</th>' +
                                    '<th class="w150 left">标题</th>' +
                                    '<th class="w80 center">最后修改者</th>' +
                                    '<th class="w200 center">最后修改时间</th>' +
                                    '<th class="w200 center">当前状态</th>' +
                                  '</tr></thead>' +
                                '<tbody id="c-editor"></tbody>' + 
                            '</table>' +
                        '</div>';
        $("#editor-dir-div").append(tableTitle);
        initTable($("#c-editor"), c_editor_list);
    }

    function jumpEditorContent(id) {
        var state = history.state;
        var local_url = window.location.href;
        local_url= local_url.replace(/\/file.*/,"");
        console.log(local_url);
        history.pushState(state, "", local_url + '/file/'+id);
        // window.location.href ="/user/ViewEditorContent/id/"+id;
        showEditorContent(id);
    }
    function showEditorContent(id) {
        // window.location.href ="/user/ViewEditorContent/id/"+id;
        $("#editor-dir-div").children().remove();
        $('#newdir').remove();
        $('#newfile').remove();
        var fileTitle,last_editor_name,update_time,article_status,author,this_owner_id, is_editor;
        var c_editor_name = [];
        $.each(editor_list_all,function() {
            if (this['id'] == id) {
                fileTitle = this['title'];
                this_owner_id = this['owner_id'];
                last_editor_name = findNameById(this['last_editor_id']);
                update_time = this['update_time'].substr(5,5);
                article_status = "已发布";
                author = findNameById(this['owner_id']);
            }
        });
        $.each(editor_wait,function() {
            if (this['id'] == id) {
                fileTitle = this['title'];
                last_editor_name = findNameById(this['last_editor_id']);
                author = findNameById(this['owner_id']);
                this_owner_id = this['owner_id'];
                update_time = this['update_time'].substr(5,5);
                if(this['approve_user_id'] != 0)
                    article_status = "审核中";
                else if(this['lock_status'] == 'lock')
                    article_status = "已锁定";
                else
                    article_status = "未发布";
                if(this['c_editor'] != null) {
                    $.each(this['c_editor'],function(key,value){
                        c_editor_name.push(findNameById(value));
                    });
                }
            }
        });
        $.each(c_editor_list, function() {
            if(this['id'] == id) {
                fileTitle = this['title'];
                last_editor_name = findNameById(this['last_editor_id']);
                author = findNameById(this['owner_id']);
                update_time = this['update_time'].substr(5,5);
                if(this['approve_user_id'] != 0)
                    article_status = "审核中";
                else if(this['lock_status'] == 'lock')
                    article_status = "已锁定";
                else
                    article_status = "未发布";
                if(this['c_editor'] != null) {
                    $.each(this['c_editor'],function(key,value){
                        if(userId==value) {
                            is_editor = 'yes';
                        }
                        c_editor_name.push(findNameById(value));
                    });
                }

            }
        });
        $.ajax({
            url:"/ajax/getFileContent",
            type:"post",
            dataType:"json",
            data: {"id":id},
            success:function(data){
                if(!data.code) {
                    $("#input-content").html(data.content);
                }
            },
            error:function(err){
                console.log('error:'+err);
            }
        });
        var str = '<div class="simditor" id="input-content-div">' +
                    '<div class="box-left" style="width: 82%;">' +
                       ' <div class="pl50 pr50 pt20">' +
                            '<div style="height:80px;text-align:center;line-height:80px;">' +
                                '<span class="mt20" id="show-editor-title" style="font-size: 23px;">'+fileTitle+'</span>' +
                            '</div>' +
                               ' <span class="fl" id="show-editor-name" style="display:block;width: 100%;text-align: center;font-size: 16px;color: gray;">'+last_editor_name+' 保存于 '+update_time+'</span>';
                               if(article_status != "已发布" && c_editor_name.length && c_editor_name[0])
                               str += ' <span class="fl mt10" id="show-c-editor-name" style="display:block;width: 100%;text-align: center;font-size: 16px;color: gray;">创建者: '+ author +'  协同编辑者: '+c_editor_name.join('、')+'</span>';
                   str += ' </div>' +
                        '<div class="simditor-wrapper pl50 pr50 pt20" style="clear:both;">' +
                            '<div id="input-content" class="simditor-body" style="overflow:hidden;text-align:justify;" contenteditable="false"></div>' +
                        '</div>' +
                   ' </div>' +
                    '<div class="editor-list fr" style="padding-top: 80px;">' +
                        '<ul style="list-style:none;margin-right: 30px;">';
                        console.log(this_owner_id);
                    if( this_owner_id == userId) {
                        if(article_status == '未发布') {
                            str += ' <li><button class="btn btn-success  mt20 mr20" onclick="fileCoEditor(\''+fileTitle+'\',' + id + ')">协同编辑者</button></li>' ;
                            str += ' <li><button class="btn btn-success  mt20 mr20" onclick="applyFile('+id+')">发布</button></li>' +
                                    ' <li><button class="btn btn-danger  mt20 mr20" onclick="deleteFile('+id+')">删除</button></li>';
                        }
                        str += ' <li>' +
                            '<span class="hidden" id="show-editor-id"></span>' +
                            '<button class="btn btn-success mt20 mr20" onclick="editContent('+id+')">&nbsp;编&nbsp;辑&nbsp;</button>' +
                            '</li>';

                    }
                    else if(is_editor=="yes") {     //共同编辑者显示编辑
                        str += ' <li>' +
                            '<span class="hidden" id="show-editor-id"></span>' +
                            '<button class="btn btn-success mt20 mr20" onclick="editContent('+id+')">&nbsp;编&nbsp;辑&nbsp;</button>' +
                            '</li>';
                    }
                    str += ' </ul>' +
                    '</div>' +
                '</div>';
        $("#editor-dir-div").append(str);


    }

    //显示发布申请对话框
    function showApplyEditor(obj){
        var editor_id = $(obj).parent().parent().children().eq(0).text();
        var editor_title = $(obj).parent().parent().children().eq(1).text();
        var ySet = (window.innerHeight - $("#publish-dir-div").height())/3;
        var xSet = (window.innerWidth - $("#publish-dir-div").width())/2;
        $("#publish-dir-div").css("top",ySet);
        $("#publish-dir-div").css("left",xSet);
        $("#publish-dir-div").modal({show:true});
        $("#publish_editor_id").text(editor_id);
        $("#publish_editor_title").text(editor_title);
    }
    function applyFile(id) {
        var editor_id = id;
        var editor_title = $('#show-editor-title').text();
        var ySet = (window.innerHeight - $("#publish-dir-div").height())/3;
        var xSet = (window.innerWidth - $("#publish-dir-div").width())/2;
        $("#publish-dir-div").css("top",ySet);
        $("#publish-dir-div").css("left",xSet);
        $("#publish-dir-div").modal({show:true});
        $("#publish_editor_id").text(editor_id);
        $("#publish_editor_title").text(editor_title);
    }
    //发送发布申请
    function sendApply() {
        var id = $("#publish_editor_id").text();
        var dir_id = parseInt($("#publish_dir_select").val());
        if (id) {
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/applyPublish',
                data:{'id':id, 'dir_id':dir_id },
                success:function(result){
                    if(result.code == 0) {
                        showHint("提示消息", "申请发布成功");
                        setTimeout(function(){location.reload()}, 1200);
                    }
                    else if (result.code == -2)
                        showHint("提示消息", "参数错误");
                    else if (result.code == -3)
                        showHint("提示消息", "重复申请、或者申请人不是文档的创建者");
                    else if (result.code == -1)
                        showHint("提示消息", "申请失败");
                    else
                        showHint('提示消息','申请失败');
                },
                error: function(arg1,arg2,arg3){
                    showHint("提示消息", arg3);
                }
            });
                // location.reload();
        }
    }

    function cancelApply(id) {
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/cancelApplyPublish',
            data:{'id':id},
            success:function(result) {
                if(result.code==0) {
                    showHint("提示信息",'操作成功');
                    setTimeout(function(){location.reload();},1200);
                }
                else
                    showHint("提示信息",'操作失败');
            },
            error:function(arg1, arg2, arg3) {
                showHint("提示信息",arg3);
            }
        });
    }


    function newEditorDir(){
        var ySet = (window.innerHeight - $("#new-dir-div").height())/2;
        var xSet = (window.innerWidth - $("#new-dir-div").width())/2;
        $("#new-dir-div").css("top",ySet);
        $("#new-dir-div").css("left",xSet);
        $("#new-dir-div").modal({show:true});
    }

    function sendNewEditorDir() {
        var dir_name = $("#new_dir_name_input").val();
        var pId = parseInt($("#new_parent_dir_select").val());
        if ( dir_name=="") {
            $("#new_dir_name_input").focus();
            showHint("提示信息",'文件名不能为空');
        }
        else {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "/ajax/newEditorDir",
                data: {'parent_id':pId, 'dir_name':dir_name},
                success:function(result) {
                    if (result['code']==0) {
                        showHint("提示信息",'新建成功');
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if (result['code']==-1)
                        showHint("提示信息",'新建失败');
                    else if (result['code']==-2)
                        showHint("提示信息",'参数错误');
                    else if (result['code']==-3)
                        showHint("提示信息",'新建失败，已存在该文件夹');
                    else if (result['code']==-99)
                        showHint("提示信息",'无权限新建文件夹');
                },
                error:function(arg1, arg2, arg3) {
                    showHint('提示信息', arg3);
                }
            });
        }
    }

    function findNameById(id) {
        var cn_name = "";
        $.each(user_list,function(){
            if(this['user_id']==id) {
                cn_name = this['cn_name'];
                return false;
            }
        });
        return cn_name;
    }

    function findDirNameById(id) {
        var dir_name = "";
        $.each(dir_list_all,function(){
            if(this['dir_id']==id) {
                dir_name = this['dir_name'];
                return false;
            }
        });
        return dir_name;
    }

    function showDirOptions(id) {
        $("#dir-option-span-"+id).removeClass("hidden");
    }

    function hideDirOptions(id) {
        $("#dir-option-span-"+id).addClass("hidden");
    }

    function showFileOptions(id) {
        $("#file-option-span-"+id).removeClass("hidden");
    }

    function hideFileOptions(id) {
        $("#file-option-span-"+id).addClass("hidden");
    }

    //移动文件夹
    function showRelocationEditorDir(id){
        var dir_name = $("#dir-option-span-"+id).prev().text();
        var dir_id = id;
        var ySet = (window.innerHeight - $("#relocation-dir-div").height())/3;
        var xSet = (window.innerWidth - $("#relocation-dir-div").width())/2;
        $("#relocation-dir-div").css("top",ySet);
        $("#relocation-dir-div").css("left",xSet);
        $("#relocation-dir-div").modal({show:true});
        $("#relocation_dir_id").text(dir_id);
        $("#relocation_dir_name").text(dir_name);
    }
    //移动文件
    function showRelocationEditorFile(id, name) {
        var ySet = (window.innerHeight - $("#relocation-file-div").height())/3;
        var xSet = (window.innerWidth - $("#relocation-file-div").width())/2;
        $("#relocation-file-div").css("top",ySet);
        $("#relocation-file-div").css("left",xSet);
        $("#relocation-file-div").modal({show:true});
        $("#relocation_file_id").text(id);
        $("#relocation_file_name").text(name);
    }

    function showRenameEditorDir(id){
        var dir_name = $("#dir-option-span-"+id).prev().text();
        var dir_id = id;
        var ySet = (window.innerHeight - $("#rename-dir-div").height())/3;
        var xSet = (window.innerWidth - $("#rename-dir-div").width())/2;
        $("#rename-dir-div").css("top",ySet);
        $("#rename-dir-div").css("left",xSet);
        $("#rename-dir-div").modal({show:true});
        $("#rename_dir_id").text(dir_id);
        $("#rename_dir_name").text(dir_name);
    }

    function showDeleteEditorDir(id){
        var dir_name = $("#dir-option-span-"+id).prev().text();
        var dir_id = id;
        var ySet = (window.innerHeight - $("#delete-dir-div").height())/3;
        var xSet = (window.innerWidth - $("#delete-dir-div").width())/2;
        $("#delete-dir-div").css("top",ySet);
        $("#delete-dir-div").css("left",xSet);
        $("#delete-dir-div").modal({show:true});

        $("#delete_dir_id").text(dir_id);
        $("#delete_dir_name").text(dir_name);
    }

    //发送文件夹移动操作
    function sendRelocationRir() {
        var dir_id = $("#relocation_dir_id").text();
        var parent_id = $("#relocation_dir_select").val();
        if(dir_id && parent_id && (dir_id != parent_id)) {
            $.ajax({
                type:'post',
                dataType: 'json',
                url: '/ajax/relocationEditorDir',
                data: {'dir_id':dir_id, 'parent_id': parent_id},
                success:function(result) {
                    if(result['code']==0) {
                        showHint("提示信息", "移动成功");
                        setTimeout(function(){location.reload()},1200);
                    }
                    else if (result['code']==-1)
                        showHint("提示信息", "移动失败");
                    else if (result['code']==-2)
                        showHint("提示信息", "参数错误");
                    else if (result['code']==-7)
                        showHint("提示信息", "目标文件不能为自己");
                    else if (result['code']==-3)
                        showHint("提示信息", "找不到文件夹");
                    else if (result['code']==-99)
                        showHint("提示信息", "无权限移动");
                },
                error:function(arg1, arg2, arg3) {
                    showHint("提示信息", arg3);
                }
            });
        }
        else
            showHint("提示信息", "错误！！！");
    }

    //发送文件移动操作
    function sendRelocationFile() {
        var file_id = $("#relocation_file_id").text();
        var dir_id = $("#relocation_file_select").val();
        if(file_id && dir_id) {
            $.ajax({
                type:'post',
                dataType: 'json',
                url: '/ajax/relocationEditorFile',
                data: {'file_id':file_id, 'dir_id': dir_id},
                success:function(result) {
                    if(result['code']==0) {
                        showHint("提示信息", "移动成功");
                        setTimeout(function(){location.reload()},1200);
                    }
                    else if (result['code']==-1)
                        showHint("提示信息", "移动失败");
                    else if (result['code']==-2)
                        showHint("提示信息", "参数错误");
                    else if (result['code']==-3)
                        showHint("提示信息", "找不到文件夹");
                    else if (result['code']==-99)
                        showHint("提示信息", "无权限移动");
                },
                error:function(arg1, arg2, arg3) {
                    showHint("提示信息", arg3);
                }
            });
        }
        else
            showHint("提示信息", "错误！！！");
    }

    //发送文件夹重命名操作
    function sendRenameRir() {
        var dir_id = $("#rename_dir_id").text();
        var new_dir_name = $("#rename_dir_name_input").val();
        if (dir_id && new_dir_name) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "/ajax/renameEditorDir",
                data: {'dir_id':dir_id, 'new_name':new_dir_name},
                success: function(result) {
                    if (result['code']==0) {
                        showHint("提示信息", "重命名成功");
                        setTimeout(function(){location.reload()},1200);
                    }
                    else
                        showHint("提示信息", "重命名失败");
                },
                error:function(arg1, arg2, arg3) {
                    showHint("提示信息", arg3);
                }
            });
        }
        else
            showHint('提示信息', '错误！！！')
    }

    //发送删除文件夹操作
    function sendDeleteDir() {
        var dir_id = $("#delete_dir_id").text();
        if (dir_id) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "/ajax/deleteEditorDir",
                data: {'dir_id':dir_id },
                success: function(result) {
                    if (result['code']==0) {
                        showHint("提示信息", "删除成功");
                        setTimeout(function(){location.reload()},1200);
                    }
                    else if (result['code']==-2)
                        showHint("提示信息", "参数错误");
                    else if (result['code']==99)
                        showHint("提示信息", "无权限操作");
                    else if (result['code']==-3)
                        showHint("提示信息", "参数错误");
                    else if (result['code']==4)
                        showHint("提示信息", "存在子文件，不能删除");
                    else
                        showHint("提示信息", "删除失败,请确认该目录是否为空");

                }
            });
        }
        else
            showHint('提示信息', '错误！！！')
    }

    function removeNextA(obj) {
        while($(obj).next('a').length != 0) {
            $(obj).next('a').remove();
        }
        $(obj).remove();
    }
    function removeAlla(obj) {
        while($(obj).next('a').length != 0) {
            $(obj).next('a').remove();
        }
    }

    //删除文件
    function showDeleteEditorFile(obj) {
        var id = $(obj).parent().parent().children().eq(0).text();
        var name = $(obj).parent().parent().children().eq(1).text();  
        var ySet = (window.innerHeight - $("#delete-editor-div").height())/3;
        var xSet = (window.innerWidth - $("#delete-editor-div").width())/2;
        $("#delete-editor-div").css("top",ySet);
        $("#delete-editor-div").css("left",xSet);
        $("#delete-editor-div").modal({show:true});
        $("#delete_editor_id").text(id);
        $("#delete_editor_name").text(name);
    }
    //根据id删除文件
    function showDeleteEditorFileS(id, name) {
        var ySet = (window.innerHeight - $("#delete-editor-div").height())/3;
        var xSet = (window.innerWidth - $("#delete-editor-div").width())/2;
        $("#delete-editor-div").css("top",ySet);
        $("#delete-editor-div").css("left",xSet);
        $("#delete-editor-div").modal({show:true});
        $("#delete_editor_id").text(id);
        $("#delete_editor_name").text(name);
    }
    function deleteFile(id) {
        var id = id;
        var name = $('#show-editor-title').text();  
        var ySet = (window.innerHeight - $("#delete-editor-div").height())/3;
        var xSet = (window.innerWidth - $("#delete-editor-div").width())/2;
        $("#delete-editor-div").css("top",ySet);
        $("#delete-editor-div").css("left",xSet);
        $("#delete-editor-div").modal({show:true});
        $("#delete_editor_id").text(id);
        $("#delete_editor_name").text(name);
    }

    //发送删除文件操作
    function sendDeleteEditor() {
        var editor_id = $("#delete_editor_id").text();
        if (editor_id) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "/ajax/deleteEditor",
                data: {'editor_id':editor_id},
                success:function(result) {
                    if(result['code']==0) {
                        showHint("提示信息", "删除成功");
                        setTimeout(function(){location.reload()},1200);
                    }
                    else if (result['code']==-2)
                        showHint("提示信息", "参数错误");
                    else if (result['code']==-3)
                        showHint("提示信息", "文件不存在");
                    else if (result['code']==-4)
                        showHint("提示信息", "文件处于锁定状态，不能删除");
                    else if (result['code']==-5)
                        showHint("提示信息", "文件处于审核状态，不能删除");
                    else
                        showHint("提示信息", "删除失败");
                }
            });
        }
        else
            showHint("提示信息","错误！")
    }

    //共同编辑者对话框 
    function showCoEditor(obj, editor_id) {
        var editor_name = $(obj).parent().parent().prev().prev().text();
        var ySet = (window.innerHeight - $("#co-editor-div").height())/3;
        var xSet = (window.innerWidth - $("#co-editor-div").width())/2;
        $("#co-editor-div").css("top",ySet);
        $("#co-editor-div").css("left",xSet);
        $("#co-editor-div").modal({show:true});

        $("#co_editor_id").text(editor_id);
        $("#co_editor_name").text(editor_name);

        $("#editor-td").children('button').remove();
        $.each(editor_wait, function(){
            if (this['id']==editor_id) {
                var c_id_list = this['c_editor'];
                $.each(c_id_list, function() {
                    if (this!="") {
                        var html_content = '<button class="btn btn-success pd3 w100 mr5 mb5" name="' + this + '">' + findNameById(this) +
                            '&nbsp;<span class="glyphicon glyphicon-remove middle mt-2" onclick="$(this).parent().remove();"></span></button>';
                        $("#editor-td").prepend(html_content);
                    }
                });
            }
        });
    }

    function fileCoEditor(editor_name, editor_id) {
        var editor_name = editor_name.trim();
        var ySet = (window.innerHeight - $("#co-editor-div").height())/3;
        var xSet = (window.innerWidth - $("#co-editor-div").width())/2;

        $("#co-editor-div").css("top",ySet);
        $("#co-editor-div").css("left",xSet);
        $("#co-editor-div").modal({show:true});

        $("#co_editor_id").text(editor_id);
        $("#co_editor_name").text(editor_name);

        $("#editor-td").children('button').remove();
        $.each(editor_wait, function(){
            if (this['id']==editor_id) {
                var c_id_list = this['c_editor'];
                $.each(c_id_list, function() {
                    if (this!="") {
                        var html_content = '<button class="btn btn-success pd3 w100 mr5 mb5" name="' + this + '">' + findNameById(this) +
                            '&nbsp;<span class="glyphicon glyphicon-remove middle mt-2" onclick="$(this).parent().remove();"></span></button>';
                        $("#editor-td").prepend(html_content);
                    }
                });
            }
        });
    }

    function addNewCoEditor(){
        $("#new_editor_tr").removeClass("hidden");
        $("#new_editor_input").val("");
        $("#new_editor_input").focus();
    }

    function ensureEditor() {
        var user_name = $("#new_editor_input").val();
        var user_id;
        var exsist_flag = 1;

        $.each(user_list, function(){
            if (this['cn_name']==user_name) {
                user_id = this['user_id'];
                return false;
            }
        });
        $("#editor-td").children('button').each(function(){
            if ($(this).attr('name')==user_id) {
                exsist_flag = 0;
                return false;
            }
        });

        if (user_id && exsist_flag) {
            var html_content = '<button class="btn btn-success pd3 w100 mr5" name="' + user_id + '">' + user_name +
                        '&nbsp;<span class="glyphicon glyphicon-remove middle mt-2" onclick="$(this).parent().remove();"></span></button>';
            $("#editor-td").prepend(html_content);
        }
        else
            showHint('提示信息','找不到该用户,或者重复添加');
    }

    function cancelEditorBack(){
        $("#new_editor_tr").addClass("hidden");
    }

    //发送共同编辑者的结果
    function sendChangeCoEditor(){
        var editor_id = $("#co_editor_id").text();
        var c_editor_list = ""
        $.each($("#editor-td").children('button'),function(){
            if(c_editor_list)
                c_editor_list = c_editor_list + ',' +$(this).attr('name');
            else
                c_editor_list = $(this).attr('name');
        });
        if(c_editor_list && editor_id) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '/ajax/changeCoEditor',
                data: {'editor_id':editor_id, 'c_editor_list':c_editor_list},
                success:function(result) {
                    if(result['code']==0) {
                        showHint('提示信息', '编辑成功');
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(result['code']==-2)
                        showHint('提示信息', '参数错误');
                    else if(result['code']==-3)
                        showHint('提示信息', '文件不存在');
                    else if(result['code']==-4)
                        showHint('提示信息', '文件被锁定，暂时不能修改');
                    else if(result['code']==-5)
                        showHint('提示信息', '审核状态的文件不能修改');
                    else
                        showHint('提示信息', '编辑失败');
                },
                error:function(arg1, arg2, arg3) {
                    showHint('提示信息', arg3);
                }
            });
        }
    }

    function editContent(id) {
        var editor_id = id;
        if (editor_id) {
            // location.href = "/user/editEditorContent/id/" + editor_id;
            window.open("/user/editEditorContent/id/" + editor_id);
        }
    }

    function returnList() {
        $('#editor-head-guide span:last a').click();
    }

    window.onpopstate = function() {
        location.reload();
    }