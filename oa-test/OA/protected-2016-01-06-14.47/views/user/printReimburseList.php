<?php
echo "<script type='text/javascript'>";
echo "console.log('printReimburseList');";
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
		<!-- 报销清单表格 -->
		<table class="list-table bordered mt10 center" id="reimburse-list-table">
			<tbody>
				<tr>
					<th colspan="2" class="center">项目</th>
					<th class="center w100" name='name-td'><input type='checkbox' onclick="hideCol('name',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">名称</span></th>
					<th class="center w50" name='price-td'><input type='checkbox' onclick="hideCol('price',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">单价</span></th>
					<th class="center w50" name='quantity-td'><input type='checkbox' onclick="hideCol('quantity',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">数量</span></th>
					<th class="center w80" name='count-td'><input type='checkbox' onclick="hideCol('count',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">小计(元)</span></th>
					<th class="center w80" name='username-td'><input type='checkbox' onclick="hideCol('username',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">经手人</span></th>
					<th class="center w50" name='receipt-td'><input type='checkbox' onclick="hideCol('receipt',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">发票</span></th>
					<th class="center w80" name='total-td'><input type='checkbox' onclick="hideCol('total',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">报销金额</span></th>
					<th class="center w100" name='time-td'><input type='checkbox' onclick="hideCol('time',this);" class='noprint' checked><span onclick="$(this).prev().click();" class="pointer">申请日期</span></th>
				</tr>
			</tbody>
		</table>
	</div>
	<p class="center gray noprint">(请勾选需要打印的内容)</p>
	<button class="btn btn-lg btn-primary noprint w100 mt20" onclick="printBill();">打印</button><!-- 打印按钮 -->
</div>

<!-- js -->
<script type="text/javascript">
	// 页面初始化
	$(document).ready(function(){
		var th_tag = false;  // 已输出表头标记
		var total = 0; // 总计
		var total_num = 0; // 总件数
		var summary = 0; // 报销总金额
		var row_index = 1; // 行数

		// 遍历报销清单数组
		$.each(reimburse_list_arr, function(){
			var th_str = "";
			if(!th_tag){
				th_str = "<td  class='w100' id='reimburse-list-th' name='project-td'>"+categoryToCN(this['category'])+"</td>";
				th_tag = true;
			}
			var str = "<tr>"+th_str+"<td class='w100' name='project-td'>"+this['type']+"</td>"+
			"<td name='name-td'>"+this['name']+"</td><td name='price-td'>"+this['price']+"</td>"+
			"<td name='quantity-td'>"+parseInt(this['quantity'])+"</td>"+
			"<td name='count-td'>"+accMultiply(parseFloat(this['price']), parseFloat(this['quantity']))+"</td>"+
			"<td name='username-td'>"+this['user_name']+"</td>"+
			"<td name='receipt-td'>"+this['receipt']+"</td>"+
			"<td name='total-td'>"+this['total']+"</td>"+
			"<td name='time-td'>"+this['create_time']+"</td>"+
			"</tr>";

			// 计算总计
			total = accAdd(total, accMultiply(parseFloat(this['price']), parseFloat(this['quantity'])));

			// 计算总件数
			total_num = accAdd(total_num, parseInt(this['quantity']));

			// 计算报销总金额
			summary = accAdd(summary, parseFloat(this['total']));

			$("#reimburse-list-table").find("tbody").append(str);
			row_index++;
		});

		var total_str = "<tr><td name='project-td'>合计</td><td name='name-td'></td><td name='price-td'></td><td name='quantity-td'>"+total_num+"</td><td name='count-td'></td><td name='username-td'></td><td name='receipt-td'></td><td name='total-td'>"+summary+"</td><td name='time-td'></td></tr>";
		$("#reimburse-list-table").find("tbody").append(total_str);
		$("#reimburse-list-th").attr("rowspan",row_index);
	});

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

	// 精确加法
	function accAdd(arg1,arg2){  
		var r1,r2,m;  
		try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}  
		try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}  
		m=Math.pow(10,Math.max(r1,r2))  
		return (arg1*m+arg2*m)/m  
	}

	// 精确乘法
	function accMultiply(arg1,arg2){  
	    var m=0,s1=arg1.toString(),s2=arg2.toString();  
	    try{m+=s1.split(".")[1].length}catch(e){}  
	    try{m+=s2.split(".")[1].length}catch(e){}  
	    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);  
	}

	// 打印
	function printBill(){
		window.print();
	}

	// 报销清单数组初始化
	var reimburse_list_arr = new Array();
	<?php 
		if(!empty($data) && !empty($data_apply)){
			$category = $data['category'];
			$reimburse_id = $data['id'];
			foreach($data->details as $ddrow){
				$create_time = date('Y/m/d',strtotime($data_apply[$ddrow['apply_detail_id']]));
				$receipt = ($ddrow['have_receipt'] == "yes") ? '有' : '无';
				$total = $ddrow['amount'];
				echo "reimburse_list_arr.push({'id':'{$reimburse_id}', 'category':'{$category}', 'type':'{$ddrow->applyDetail['type']}', 'name':'{$ddrow->applyDetail['name']}', 'price':'".(Float)$ddrow->applyDetail['price']."', 'quantity':'{$ddrow->applyDetail['quantity']}', 'user_name':'".$user_name."', 'create_time':'{$create_time}', 'receipt':'".$receipt."', 'total':'".(Float)$total."'});";
			}
		}
	?>

	// 选择不打印的列
	function hideCol(colname, obj){
		if(obj.checked){
			$("td[name='"+colname+"-td']").removeClass("noprint").removeClass("gray");
			$("th[name='"+colname+"-td']").removeClass("noprint").removeClass("gray");
		}else{
			$("td[name='"+colname+"-td']").addClass("noprint").addClass("gray");
			$("th[name='"+colname+"-td']").addClass("noprint").addClass("gray");
		}
	}
</script>
