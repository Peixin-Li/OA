<?php
echo "<script type='text/javascript'>";
echo "console.log('vote');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/vote.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
  <h4 class="pd10 m0 b33">发起投票</h4>
  <table class="table bor-t-none m0">
    <tr>
      <th class="w130 center">游戏名字</th>
      <td><input class="form-control" id="title" placeholder="请输入游戏名，50字以内"></td>
    </tr>
    <tr>
      <th class="w130 center">游戏描述</th>
      <td><textarea class="form-control" rows="10" id="content" placeholder="请输入游戏描述，不超过1500字"></textarea></td>
    </tr>
    <tr>
      <th class="w130 center">操作</th>
      <td><button class="btn btn-success w100" onclick="sendVote();">确认</button></td>
    </tr>
  </table>
</div>

<!-- js -->
<script>
    function sendVote() {
      var title = $('#title').prop('value').trim();
      var content = $('#content').prop('value').trim();
      if(!!title) {
        alert('发布成功');
      } else {
        alert('游戏名不能为空');
      }
    }
</script>