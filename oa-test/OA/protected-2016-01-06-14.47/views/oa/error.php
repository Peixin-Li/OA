<?php
echo "<script type='text/javascript'>";
echo "console.log('error');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />

<!-- 主界面 -->
<div class="container">
	<div class="w500 m0a" style="margin-top:20%;">
		<?php if($code != '404'): ?>
	      <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/logo_lg.png" class="w500"/>
	    <?php else: ?>
	      <img src="<?php echo Yii::app()->request->baseUrl; ?>/images/logo_lg.png" class="w500"/>
	    <?php endif; ?>
	    <div class="pt30">
	      <p class="center f18px"><?php echo CHtml::encode($message); ?>，无法访问，请<a class="pointer" onclick="location.reload();">刷新</a>重试或<a href="/user/">返回主页</a></p>
	    </div>
	</div>
</div>
