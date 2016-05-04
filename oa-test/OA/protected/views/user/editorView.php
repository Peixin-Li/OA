<?php
echo "<script type='text/javascript'>";
echo "console.log('editorView');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/module.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/hotkeys.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/uploader.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor-mark.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor-checklist.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor-checklist.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor-mark.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/main.css" />
<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/animate.css">

<!-- 查看文件内容 -->
<div class="simditor" id="input-content-div">
    <div>
        <div class="pl50 pr50 pt20">
            <div>
                <span class='fl mt20' id="show-editor-title" style="display:block;width: 80%;text-align: center;font-size: 23px;"></span>
            </div>
                <span class='fl mt10' id="show-editor-name" style="display:block;width: 80%;text-align:center;font-size: 14px;color:gray"></span>
        </div>
        <div class="simditor-wrapper pl50 pr50" style="clear:both;">
            <div id='input-content' class="simditor-body" contenteditable="false"></div>
        </div>
    </div>
</div>    

<!-- js -->
<script type="text/javascript">
    var editor_info = <?php echo $editor_js?>;
    var user_list = <?php echo $user_list ?>;
    var dir_list = <?php echo $dir_list ?>;

    var cn_name_list = [];
    $.each(user_list, function(){
            cn_name_list.push(this['cn_name']);
    });

    $(document).ready(function(){
        $("#show-editor-title").text(editor_info['title']);
        $("#show-editor-id").text(editor_info['id']);
        $("#input-content").append(editor_info['content']);
    });

    function editContent() {
        var editor_id = $("#show-editor-id").text();
        if (editor_id) {
            location.href = "/user/editEditorContent/id/" + editor_id;
        }
    }
    
    $('#show-editor-name').html(findNameById(editor_info['last_editor_id']) + '保存于' + editor_info['update_time'].substr(5,5));
    
    var c_editor_name = [];
    for(var i=0,len=JSON.parse(editor_info['c_editor']).length;i<len;i++) {
        c_editor_name.push(findNameById(JSON.parse(editor_info['c_editor'])[i]));
    }

    function findNameById(id) {
        for(var i=0,len=user_list.length;i<len;i++) {
            if(user_list[i]['user_id'] == id) {
                return user_list[i]['cn_name'];
            }
        }
        return "错误";
    }
</script>

