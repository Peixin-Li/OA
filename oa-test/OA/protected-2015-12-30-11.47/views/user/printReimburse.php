<?php
echo "<script type='text/javascript'>";
echo "console.log('printReimburse');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/user.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />

<!-- 主界面 -->
<div class="container center">
	<div class="w800 m0a" id="bill-div">
		<!-- 标题 -->
		<div>
			<h3>报销支付审批单</h3> 
		</div>
		<!-- 公司名称 -->
		<div class="left">
			<div class="w400 fl">公司名称：广州善游网络科技有限公司</div>
			<div class="w200 fr">编号：<?php echo $data['id']; ?></div>
		</div>
		<div class="left mt5 mb5">
			<!-- 报销部门 -->
			<div class="w300 fl">
				报销部门：<span>
				<?php 
					if($department_name == "总经理办公室" || $department_name == "人事行政部" || $department_name == "商务部" || $department_name == "IT运维部" || $department_name == "项目管理部"){
						echo "公共部门";
					}else{
						echo $department_name;
					}
				?>
				</span>
			</div>
			<!-- 报销人 -->
			<div class="w300 fl">
				报销人：
			</div>
			<!-- 报销日期 -->
			<div class="right pr20 fl" id="reimburse-date-detail">
				<?php echo date('Y 年 m 月 d 日', strtotime($data['create_time']));?>
			</div>
			<div class="clear"></div>
		</div>
		<!-- 报销表格 -->
		<table class="table bordered bor-1-ddd m0" style="line-height:40px;">
			<tbody>
				<tr>
					<th class="center" colspan="2">费用名称</th>
					<th class="center w100">金额(元)</th>
					<th class="center w100">单据(张)</th>
					<th rowspan="4" class="center w50">审<br>核</th>
					<td rowspan="4" class="w200"></td>
				</tr>
				<tr>
					<td colspan="2" class="left"><span>1、</span><span id="reimburse-type-name-detail"></span></td>
					<td class="center"><?php echo empty($data['total']) ? '' : $data['total'];?></td>
					<td class="center"><?php echo empty($data['receipt_num']) ? '0' : $data['receipt_num'];?></td>
				</tr>
				<tr>
					<td colspan="2" class="left">2、</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="2" class="left">3、</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="2" class="left">4、</td>
					<td></td>
					<td></td>
					<th rowspan="4" class="center">总</br>经</br>理</br>审</br>批</th>
					<td rowspan="4"></td>
				</tr>
				<tr>
					<td colspan="2" class="left">5、</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="2" class="left">6、</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<th class="center" colspan="2">合计</th>
					<td class="center"><?php echo empty($data['total']) ? '' : $data['total'];?></td>
					<td class="center"><?php echo empty($data['receipt_num']) ? '' : $data['receipt_num'];?></td>
				</tr>
				<tr>
					<td colspan="6" class="left">
						<label class="w100">金额大写：</label>
						<span>币别</span>
						<span id="detail-unit-blank" class="ml50 hidden mr15" style="text-decoration:line-through;"></span>
						<span id="detail-unit-9" class="mr15 ml50"></span><span class="mr15">佰</span>
						<span id="detail-unit-8" class="mr15"></span><span class="mr15">拾</span>
						<span id="detail-unit-7" class="mr15"></span><span class="mr15">万</span>
						<span id="detail-unit-6" class="mr15"></span><span class="mr15">仟</span>
						<span id="detail-unit-5" class="mr15"></span><span class="mr15">佰</span>
						<span id="detail-unit-4" class="mr15"></span><span class="mr15">拾</span>
						<span id="detail-unit-3" class="mr15"></span><span class="mr15">元</span>
						<span id="detail-unit-2" class="mr15"></span><span class="mr15">角</span>
						<span id="detail-unit-1" class="mr15"></span><span>分</span>
					</td>
				</tr>
				<tr>
					<th rowspan="2" class="w100">付款方式：</th>
					<td colspan="5">
						<div class="w80 fl left">
							<span><?php if($data['way'] && $data['way'] == "transfer"){echo "√";}else{echo "&nbsp;&nbsp;";} ?></span>
							<span id="transform-detail">转&nbsp;&nbsp;&nbsp;账</span>
						</div>
						<div class="fl left" style="width:425px;">
							<span class="inline-block">开户行：</span>
							<span class="inline-block" style="width:100px;vertical-align:top;" id="bank_info"><?php if($data['bank_info'] && $data['way'] == "transfer"){$bank_info = explode(" ", $data['bank_info']);echo $bank_info[0];} ?></span>
							<span class="ml5 inline-block">帐号：</span>
							<span style="width:200px;"><?php if($data['bank_info'] && $data['way'] == "transfer"){$bank_info = explode(" ", $data['bank_info']);echo $bank_info[1];} ?></span>
						</div>
						<div class="fl left" style="width:175px;">
							<span class="inline-block">收款人：</span>
							<span id="payee"><?php echo (!empty($data['payee']) && $data['way'] == "transfer") ? $data['payee']: ''; ?></span>
						</div>
						<div class="clear"></div>
					</td>
				</tr>
				<tr>
					<td colspan="5">
						<div class="w80 fl left">
							<span><?php if($data['way'] && $data['way'] == "borrow"){echo "√";}else{echo "&nbsp;&nbsp;";} ?></span>
							<span id="borrow-detail">冲借支</span>
						</div>
						<div class="w200 fl left">
							<span class="inline-block">原借款金额：</span>
							<span><?php echo (!empty($data['borrow_amount']) && $data['way'] == "borrow") ? $data['borrow_amount']: ''; ?></span>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- 底部信息 -->
		<div class="left mt5">
			<div class="w150 fl">核准：</div>
			<div class="w150 fl">财务复核：</div>
			<div class="w150 fl">会计：</div>
			<div class="w150 fl">出纳：</div>
			<div class="w150 fl">领款人：</div>
			<div class="clear"></div>
		</div>
	</div>
	<button class="btn btn-lg btn-primary noprint w100 mt50" onclick="printBill();">打印</button><!-- 打印按钮 -->
</div>

<!-- js -->
<script type="text/javascript">
	// 页面初始化
	$(document).ready(function(){
		// 总计
		var total = "<?php echo empty($data['total']) ? '' : $data['total'];?>";

		// 填写到大写中
		var index = 3;
		var integer_str = "";
		var else_str = "";
		if(total.indexOf(".") > -1){
			integer_str = total.split(".")[0]; // 整数
			else_str = total.split(".")[1]; // 小数
		}else{
			integer_str = total;
		}
		for(var n = integer_str.length; n > 0; n--){
		    var num = parseInt(integer_str.substring(n-1, n));
		    $("#detail-unit-"+index++).text(toCapital(num));
		}
		if(else_str){
			var k = 2;
			for(var n = 0; n < else_str.length; n++){
			    var num = parseInt(else_str.substring(n, n+1));
			    $("#detail-unit-"+k--).text(toCapital(num));
			}
		}
		var blank_content = "";
		for(var j = 9; j >= 1; j--){
			if($("#detail-unit-"+j).text() == ""){
				if($("#detail-unit-blank").hasClass("hidden")) $("#detail-unit-blank").removeClass("hidden");
				$("#detail-unit-"+j).addClass("hidden");
				$("#detail-unit-"+j).next().addClass("hidden");
				var unit = $("#detail-unit-"+j).next().text();
				blank_content += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+unit;
			}
		}
		if(blank_content){
			$("#detail-unit-blank").html(blank_content);
		}

		// 填写报销名称
		$("#reimburse-type-name-detail").text(categoryToCN("<?php echo empty($data['category']) ? '' : $data['category'];?>"));

	});


	// 将数字转换成中文大写
	function toCapital(num){
		switch(num){
			case 1 :{return "壹";break;}
			case 2 :{return "贰";break;}
			case 3 :{return "叁";break;}
			case 4 :{return "肆";break;}
			case 5 :{return "伍";break;}
			case 6 :{return "陆";break;}
			case 7 :{return "柒";break;}
			case 8 :{return "捌";break;}
			case 9 :{return "玖";break;}
			case 0 :{return "零";break;}
		}
	}

	// 打印
	function printBill(){
		window.print();
	}

	// 类别翻译
	function categoryToCN(category){
		switch(category){
			case "office":{return "办公费";break;}
			case "welfare":{return "福利费";break;}
			case "travel":{return "差旅费";break;}
			case "entertain":{return "业务招待费";break;}
			case "hydropower":{return "水电费";break;}
			case "intermediary":{return "中介费";break;}
			case "rental":{return "租赁费";break;}
			case "test":{return "测试费";break;}
			case "outsourcing":{return "外包费";break;}
			case "property":{return "物管费";break;}
			case "repair":{return "修缮费";break;}
			case "other":{return "其他";break;}
		}
	}
</script>