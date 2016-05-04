<?php
echo "<script type='text/javascript'>";
echo "console.log('voteView');";
echo "</script>";
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>投票界面</title>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/simditor/styles/simditor.css" />
	<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/module.js"></script>
	<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/hotkeys.js"></script>
	<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/uploader.js"></script>
	<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor.js"></script>
	<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/simditor/scripts/simditor-mark.js"></script>	
	<style>
		.votebox {
			width: 1300px;
			margin:0 auto;
			text-align: center;
		}
		.select {
			font-size: 20px;

		}
		.select input[type="radio"] {
			width: 20px;
			height: 20px;
			vertical-align: -10%;
		}
		.select label {
			cursor: pointer;
			margin-right: 15px;
		}
		.suggest h3, .collect-bug h3 {
			border: 1px solid #aaa;
			margin-bottom: 0;
			padding: 5px 0;
			border-bottom: none;
		}
		.simditor {
			text-align: justify;
			border: 1px solid #aaa;
			margin-bottom: 10px;
		}
	</style>
</head>
<body>
	<div class="votebox">
		<h1>游戏名字：合金先锋</h1>
		<h3>游戏描述：枪战类游戏</h3>
		<hr/>
		<div class="select" >
			<label>游戏评价：</label>
			<input id="fun" type="radio" value="1" name="feel"><label for="fun">好玩</label>
			<input id="ordinary" type="radio" value="0" name="feel"><label for="ordinary">一般</label>
			<input id="disappointment" type="radio" value="-1" name="feel"><label for="disappointment">不好玩</label>
		</div>
		<div class="suggest">
			<h3>为什么？请写出你的意见</h3>
			<textarea id="suggest-text" placeholder="亲，不允许为空哟" autofocus></textarea>
		</div>
		<div class="collect-bug">
			<h3>有遇到什么bug吗？</h3>
			<textarea id="bug-text" placeholder="亲，不允许为空哟" autofocus></textarea>
		</div>
		<div class="submit"><button class="btn btn-success">提交</button></div>
	</div>

	<script>
	$(function() {
		//smiditor
		var editor = new Simditor({
		  textarea: $('#suggest-text'),
		  toolbar: false
		  //optional options
		});
		var editor2 = new Simditor({
		  textarea: $('#bug-text'),
		  toolbar: false
		  //optional options
		});

		$('.submit').on('click',function(){
			var cheackName = "";
			var suggestText = "";
			var collectBug = "";
			$.each($('.select input'),function(){
				if($(this).prop('checked')) {
					cheackName = $(this).attr('id');
				}
			});
			
			suggestText = $('#suggest-text').prop('value').trim()=="亲，不允许为空哟"?"":$('#suggest-text').prop('value').trim();
			collectBug = $('#bug-text').prop('value').trim()=="亲，不允许为空哟"?"":$('#bug-text').prop('value').trim();

			if(!cheackName) {
				alert('请填写游戏感觉，再提交');
			} else if(!suggestText) {
				alert('请填写意见，再提交');
			} else if(!collectBug) {
				alert('请填写bug，再提交');
			}
		});
	});
	</script>
</body>
</html>