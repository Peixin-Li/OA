<?php
echo "<script type='text/javascript'>";
echo "console.log('editor');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor-checklist.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor-mark.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<!-- 文档库界面显示样式在animate.css文件底部 -->
<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/animate.css">


<!-- 主界面 -->
<div class="tab-content">
<!--     <div class="tab-pane fade in active" id='pag1'> -->
        
        <div class="mt23 mb15" id="editor-head-guide">
            <span id="editor-head-span">
              <a id="editor-home-a" class="pointer" onclick="removeAlla(this);tourl(0);">文档库</a>
              <button id="newdir" class="btn btn-success fr" onclick="newEditorDir();">新建文件夹</button>
            </span>
        </div>
        <div id="editor-dir-div" style="min-height: 600px; border: 0px">
        </div>
<!--     </div> -->
</div>
<!-- 新建文件夹 -->
<div id="new-dir-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">新建文件夹</h4>
    </div>
    <div class="modal-body">
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件夹名称</th>
            <td><input class="form-control" id="new_dir_name_input"></td>
          </tr>
          <tr>
            <th class="w130">上级文件夹</th>
            <td><select class="form-control" id="new_parent_dir_select">
                <option value='0'>/</option>
            </select></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendNewEditorDir();">提交</button>
    </div>
</div>    
<!-- 移动文件夹 -->
<div id="relocation-dir-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">移动文件夹</h4>
    </div>
    <div class="modal-body">
      <span id="relocation_dir_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件夹名</th>
            <td id="relocation_dir_name"></td>
          </tr>
          <tr>
            <th class="w130">移动到文件夹</th>
            <td><select class="form-control" id="relocation_dir_select">
                <option value='0'>/</option>
            </select></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendRelocationRir();">提交</button>
    </div>
</div>    
<!-- 移动文件 -->
<div id="relocation-file-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">移动文件</h4>
    </div>
    <div class="modal-body">
      <span id="relocation_file_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件名</th>
            <td id="relocation_file_name"></td>
          </tr>
          <tr>
            <th class="w130">移动到文件夹</th>
            <td><select class="form-control" id="relocation_file_select">
                <option value='0'>/</option>
            </select></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendRelocationFile();">提交</button>
    </div>
</div>    
<!-- 重命名文件夹 -->
<div id="rename-dir-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">重命名文件夹</h4>
    </div>
    <div class="modal-body">
      <span id="rename_dir_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件夹名</th>
            <td id="rename_dir_name"></td>
          </tr>
          <tr>
            <th class="w130">更改文件名为：</th>
            <td><input class="form-control" id="rename_dir_name_input"></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendRenameRir();">提交</button>
    </div>
</div>    
<!-- 删除文件夹 -->
<div id="delete-dir-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">是否删除文件夹？</h4>
    </div>
    <div class="modal-body">
      <span id="delete_dir_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件夹名: </th>
            <td id="delete_dir_name"></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendDeleteDir();">是</button>
      <button class="btn btn-success w100" onclick="location.reload();">否</button>
    </div>
</div>    
<!-- 删除文件 -->
<div id="delete-editor-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">是否删除文件？</h4>
    </div>
    <div class="modal-body">
      <span id="delete_editor_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件名: </th>
            <td id="delete_editor_name"></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendDeleteEditor();">是</button>
      <button class="btn btn-success w100" onclick="$('#delete-editor-div').modal('hide');;">否</button>
    </div>
</div>    
<!-- 申请发布文件 -->
<div id="publish-dir-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">申请发布文件</h4>
    </div>
    <div class="modal-body">
      <span id="publish_editor_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件名：</th>
            <td id="publish_editor_title"></td>
          </tr>
          <tr>
            <th class="w130">发布的文件夹</th>
            <td><select class="form-control" id="publish_dir_select">
                <option value='0'>/</option>
            </select></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendApply();">提交</button>
    </div>
</div>    
<!-- 文档共同编辑者修改 -->
<div id="co-editor-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" >
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">文档共同编辑者设置</h4>
    </div>
    <div class="modal-body">
      <span id="co_editor_id" class="hidden"></span>
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">文件名: </th>
            <td id="co_editor_name"></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div style="margin-left: -15px;">
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
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendChangeCoEditor()">保存</button>
      <button class="btn btn-success w100" onclick="$('#co-editor-div').modal('hide');">取消</button>
    </div>
</div>    

<!-- js -->
<script type="text/javascript" >
    var c_editor; 
    var cn_name_list = new Array();
    //有刷新数据
    var editor_list = <?php echo $editor_list ?>; //当前目录下的文件
    var user_list = <?php echo $user_list?>; // 所有用户信息
    var dir_list = <?php echo $dir_list?>; // 当前文件夹下的子文件夹
    var parent_list = <?php echo $parent_dir ?>; //当前文件夹的前级文件夹
    var userId = <?php echo $user_id ?>;
    //无刷新数据
    var editor_wait = <?php echo $editor_wait ?>; //未发布的所有文章、即草稿
    var editor_list_all = <?php echo $editor_list_all ?>;//所有文章
    var dir_list_all = <?php echo $dir_list_all ?>;//所有文件夹

    //协同开发者数据
    var c_editor_list = <?php echo $c_editor_list ?>;
    var is_admin = <?php echo '"'.$is_admin.'"' ?>;
</script>

 <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/user-editor.js"></script>