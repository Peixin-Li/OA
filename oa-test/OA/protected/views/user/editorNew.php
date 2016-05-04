<?php
echo "<script type='text/javascript'>";
echo "console.log('editorNew');";
echo "</script>";
?>

<html>
<head>
  <!-- 头信息 -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="en">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Cache" content="no-cache">
    <base href="<?php echo Yii::app()->request->baseUrl; ?>">
    <title>OA－文档库</title>

  <!-- JS -->
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/js/bootstrap.js"></script>
    <script type="text/javascript" src="/js/user.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/module.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/hotkeys.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/uploader.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor-mark.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor-checklist.js"></script>
    
  <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/oa.css">
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor-checklist.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor-mark.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/main.css" />
    
</head>
<body style="background-color: #f2f5e9;">
<!-- 新建文本 -->
<div class="low_right" id='editor-div' style="padding-top: 0px;">
    <div class="wrapper">
        <input class="inline editor-title" id="edior-name" placeholder="这里输入文件标题">
        <textarea readonly="readonly" id="editor" placeholder="这里输入内容" autofocus></textarea>
    </div>
    <!-- 文档编辑者设置 -->
    <div class="pd20" style="margin-left: -50px;">
        <table class="table table-unbordered m0">
            <tbody>
                <tr><th class="w130 right">文档编辑者：</th>
                    <td id="editor-td">
                        <a class="pointer ml20" onclick="addNewCoEditor();">增加</a>
                    </td>
                </tr>
                <tr class="hidden h70" id="new_editor_tr">
                    <th class="w130 right">新增接收人：</th>
                    <td>
                        <input id="new_editor_input">
                        <a class="pointer ml5" onclick="ensureEditor();">确定</a>
                        <a class="pointer ml5" onclick="cancelEditorBack();">取消</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="wrapper" style="text-align:center">
        <div style="margin-left: auto; margin-right: auto;">
            <button class="mt-5 btn btn-primary mr20 mb15 w100" onclick="sendEditor();">
                &nbsp;保&nbsp;&nbsp;存&nbsp;
            </button>
            <button class="mt-5 btn btn-primary mr20 mb15 w100" onclick="cancleEditor();">&nbsp;取&nbsp;&nbsp;消&nbsp;</button>
        </div>
    </div>
</div>   
</body>
</html>

<!-- js -->
<script type="text/javascript" >
    var user_list = <?php echo $user_list_js; ?>;
    var cn_name_list = new Array()
    var editor;
    var save_flag = 0;
    $.each(user_list, function(){
        cn_name_list.push(this['cn_name']);
    });

    $("#new_editor_input").autocomplete({
        source: cn_name_list
    });

    $(document).ready(function(){
        editor = new Simditor ({
            textarea: $('#editor'),
            toolbar : ['title','bold','strikethrough','underline','color',
                        'ol','ul','blockquote','code','link','table',
                        'hr','indent','outdent','alignment','mark',
                        'image',
                        ],
            pasteImage: true,
            imageButton:['upload', 'external'],
            codeLanguages: [{ name: 'Python', value: 'python' }],

            upload : {
                url: '/ajax/uploadeditorpic',
                params:null,
                fileKey: 'upload_file',
                connectionCount: 3,
                leaveConfirm: '正在上传文件，如果离开上传会自动取消',
            },
        });
    });

    function cancleEditor() {
        location.href="/user/editor";
    }

    function sendEditor() {
        var content = editor.getValue();
        var name = $("#edior-name").val();
        var c_editor;
        $("#editor-td").children('button').each(function(){
            if (c_editor)
                c_editor = c_editor + "," + $(this).attr('name');
            else
                c_editor = $(this).attr('name');
        });
        // alert(c_editor);
        if (name=="" || content == "") {
            showHint("提示信息",'文件内容或者文件名不能为空');
        }
        else {
            $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/editorCreate',
            data:{'content':content, 'title': name, 'c_editor':c_editor},
            success:function(result){
                if(result.code == 0){
                    showHint("提示信息","保存成功");
                    save_flag = 1;
                    setTimeout( function() {
                        window.location.href='/user/editor';
                    }, 1200);
                    
                }else if(result.code == -1){
                    showHint("提示信息","保存失败");
                }else if(result.code == -2){
                    showHint("提示信息","参数错误！");
                }else{
                    showHint("提示信息","你没有权限执行此操作！");
                }
            },
            error: function(arg1,arg2,arg3){
                showHint('提示信息',arg3);
            }
          });
        }
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
                        '&nbsp;<span class="glyphicon glyphicon-remove middle mt-2" onclick="deleteCoEditor(this);"></span></button>';
            $("#editor-td").prepend(html_content);
        }
        else
            showHint('提示信息','找不到该用户,或者重复添加');
    }

    function cancelEditorBack(){
        $("#new_editor_tr").addClass("hidden");
    }

    function deleteCoEditor(obj) {
        $(obj).parent().remove(); 
    }

    window.onbeforeunload=function(){                        //关闭窗口时解锁文件状态
        if(!save_flag)
            return "是否确认退出？";
    }
</script>