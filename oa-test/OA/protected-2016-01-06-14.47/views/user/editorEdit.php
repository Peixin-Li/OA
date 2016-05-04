<?php
echo "<script type='text/javascript'>";
echo "console.log('editorEdit');";
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
<!-- 编辑文本 -->
<div class="low_right" id='editor-edit-div' style="padding-top: 0;">
    <div class="wrapper">
        <input class="inline editor-title" id="edior-edit-name">
        <textarea readonly="readonly" id="editor-edit" placeholder="这里输入内容" autofocus></textarea>
    </div>

    <div class="wrapper" style="text-align:center; margin-top: 30px;">
        <div style="margin-left: auto; margin-right: auto;">
            <input class="form-control w50 inline hidden" id="edior-edit-id" readonly="readonly">
            <button class="mt-5 btn btn-primary mr20 mb15 w100" onclick="saveEditEditor();">
                &nbsp;保&nbsp;&nbsp;存&nbsp;
            </button>
            <button class="mt-5 btn btn-primary mr20 mb15 w100" onclick="window.close();">&nbsp;取&nbsp;&nbsp;消&nbsp;</button>
        </div>
    </div>
</div>   

</body>
</html>

<!-- js -->
<script type="text/javascript" >
    var editor_info = <?php echo $editor_js?>;
    var dir_list = <?php echo $dir_list ?>;
    var editor_edit;
    var save_flag=0;

    $(document).ready(function(){
        $("#edior-edit-id").val(editor_info['id']);
        $("#edior-edit-name").val(editor_info['title']);
        editor_edit = new Simditor ({
            textarea: $('#editor-edit'),
            toolbar : ['title','bold','strikethrough','underline','color',
                    'ol','ul','blockquote','code','link','table',
                    'hr','indent','outdent','alignment','mark', 'image',],
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
        editor_edit.setValue(editor_info['content']);
        lockfile(editor_info['id']);
    });

    function saveEditEditor() {
        var id = $("#edior-edit-id").val();
        var content = editor_edit.getValue();
        var name = $("#edior-edit-name").val();
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/saveReEditor',
            data:{'id':id, 'title':name, 'content':content},
            success:function(result){
                if(result.code == 0) {
                    showHint("提示信息", "保存成功");
                    save_flag = 1;
                    setTimeout( function() {
                        window.close();
                    }, 1200);
                }
                else
                    showHint("提示信息", "保存失败");
            },
            error: function(arg1,arg2,arg3){
                showHint("提示信息", arg3);
            }
        });
    }

    window.onbeforeunload=function(){                        //关闭窗口时解锁文件状态
        var id = $("#edior-edit-id").val();
        if ( id ) {
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/unlockEditor',
                data:{'id':id},
                success:function(result){},
                error: function(){}
            });
        }
        if(!save_flag)
            return "是否确认退出？";
    }

    function lockfile(id) {
        if ( id ) {
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/lockEditor',
                data:{'id':id},
                success:function(result){},
                error: function(){}
            });
        }
    }

    function findDirById(id) {
        for(var i=0,len=dir_list.length;i<len;i++) {
            if(dir_list[i]['dir_id'] == id) {
                return {"name":dir_list[i]['dir_name'],"dir_id":dir_list[i]['parent_id']};
            }
        }
    }
    
    function createNavList(dir_id) {
        if(dir_id != 0) {
            // create 
            $('#editor-head-guide').prepend('<span><a class="pointer" href="' + dir_id + '">' + findDirById(dir_id).name + '</a></span>');
            // $('#editor-head-guide span:first').animate({'margin-left':'50px'});
            createNavList(findDirById(dir_id).dir_id);
            
        } else {
            $('#editor-head-guide').prepend('<span><a class="pointer" href="/user/editor">文档库</a></span>');
            // $('#editor-head-guide span:first').animate({'margin-left':'50px'});
        }
    }
    // createNavList(editor_info['dir_id']);
</script>