<?php
echo "<script type='text/javascript'>";
echo "console.log('subcribe');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<style type="text/css">
    .type-select-fl {
        float: left;
        width: 110px;
        margin-left: 5px;
    }
</style>

<!-- 主界面 -->
<div>
	<!-- 新费用申请 -->
	<div id="add-subscribe-div" class="p20b0 bor-1-ddd hidden">
		<!-- 标题 -->
  		<div id="new-subscribe-p">
			<h4>
				<strong>新费用申请</strong>
			</h4>
		</div>
		<!-- 表格 -->
	  	<table class="table table-bordered m0 center" id="subscribe-any-table">
	  		<thead>
	  			<tr class="bg-fa">
	  				<th class="center w50">ID</th>
	  				<th class="w250 center">类型</th>
	  				<th class="w150 center">名称</th>
	  				<th class="w130 center ">总价</th>
	  				<th class="center">说明</th>
                    <th class="w150 center">费用分摊方式</th>
                    <th class="w150 center">费用分摊说明</th>
	  				<th class="w200 center">操作</th>
	  			</tr>
	  		</thead>
	  		<tbody>
	  			<tr class="new-subscribe-tr">
	  				<td>1</td>
	  				<td class="w250">
	  					<select class="type-select form-control inline type-select-fl" onchange="anyTypeSelect();subscribeDataChange();selectCheck();">
	  						<option value="office">办公费</option>
	  						<option value="travel">差旅费</option>
	  						<option value="welfare">福利费</option>
	  						<?php if($tag != "common"): ?>
	  						<option value="test">测试费</option>
	  						<option value="outsourcing">外包费</option>
	  						<option value="entertain">业务招待费</option>
	  						<?php if($tag != "leader"): ?>
	  						<option value="hydropower">水电费</option>
	  						<option value="intermediary">中介费</option>
	  						<option value="rental">租赁费</option>
	  						<option value="property">物管费</option>
	  						<option value="repair">修缮费</option>
	  						<?php endif; ?>
	  						<?php endif; ?>
	  						<option value="other">其他</option>
	  					</select>
	  					<!-- 办公费二级类型 -->
	  					<select class="office-select-any inline form-control inline type-select-fl" onchange="subscribeDataChange();">
	  						<option value="快递费">快递费</option>
	  						<option value="招聘费">招聘费</option>
	  						<option value="通讯费">通讯费</option>
	  						<option value="交通费">交通费</option>
	  						<option value="网络费">网络费</option>
	  						<option value="办公设备">办公设备</option>
	  						<option value="办公软件">办公软件</option>
	  						<option value="办公用品">办公用品</option>
	  						<option value="其他">其他</option>
	  					</select>
	  					<!-- 福利费二级类型 -->
	  					<select class="hidden welfare-select-any inline form-control inline type-select-fl" onchange="subscribeDataChange();">
	  						<option value="加班费">加班费</option>
	  						<option value="兴趣小组">兴趣小组</option>
	  						<option value="体检费">体检费</option>
	  						<?php if($tag != "common"): ?>
	  						<option value="图书">图书</option>
	  						<option value="工作餐">工作餐</option>
	  						<option value="下午茶">下午茶</option>
	  						<option value="生日礼物">生日礼物</option>
	  						<option value="生日会">生日会</option>
	  						<option value="婚育礼物">婚育礼物</option>
	  						<option value="部门经费">部门经费</option>
	  						<option value="旅游经费">旅游经费</option>
	  						<option value="培训费">培训费</option>
	  						<option value="游戏经费">游戏经费</option>
	  						<option value="年会费用">年会费用</option>
	  						<option value="其他">其他</option>
	  						<?php endif; ?>
	  					</select>
	  				</td>
	  				<td>
	  					<input class="w130 h30 form-control m0a input-name">
	  				</td>
	  				<td>
	  					<input class="w80 h30 form-control inline price-input">&nbsp;元
	  				</td>
	  				<td>
	  					<input class="form-control h30 reason-input">
	  				</td>
                    <td>
                        <select class="form-control inline input-fee-div" onchange="showNewDiv(this)">
                            <?php foreach ($fee_div_tpl as $row): ?>
                                <option value='<?php echo $row->fee_div_p?>'><?php echo $row->name?></option>
                            <?php endforeach;?>
                            <option value='user-defined' >自定义</option>
                        </select>
                    </td>
                    <td style="color: blue;"></td>
	  				<td>
	  					<button class="btn btn-default" onclick="deleteLine($(this))">删除</button>
	  				</td>
	  			</tr>
	  			<tr>
	  				<td colspan="9" class="center"><a class="pointer" onclick="newLine(this);subscribeDataChange();">增加一行</a></td>
	  			</tr>
	  		</tbody>
	  	</table>
	  	<!-- 操作 -->
	  	<div class="center pd10">
	  			<button class="btn btn-success w100 h34" onclick="sendSubApply()">提交</button>
	  			<button class="btn btn-default w100 ml20 h34" onclick="backToSub()">返回</button>
	  	</div>
	</div>
	<!-- 费用申请记录 -->
	<div class="pd20 bor-1-ddd hidden" id="new-sub">
		<div class="pb20 pl5 mb15">
			<!-- 费用申请记录和报销记录 -->
		    <div id="new-sub2">
		    	<!-- 费用申请、报销类型切换标签 -->
		  		<ul class="nav nav-tabs w700 bor-b-none">
			  		<li rol="presentation" class="active"><a class="pointer f16px w130 center" onclick="switchToSubscribe(this);" id="apply-switch-btn">费用申请</a></li>
			  		<li rol="presentation"><a class="pointer f16px w130 center" onclick="switchToReimburse(this);" id="reimburse-switch-btn">费用报销</a></li>
			  	</ul>
			  	<!-- 费用申请表格 -->
			  	<div  id="subscribe-table" class="bor-1-ddd pd20 center">
			  		<!-- <div id="new-subscribe-p">
						<h4>
							<strong>费用申请记录</strong>
						</h4>
					</div> -->
					
					<div id="new-subscribe-search_add" class="h40 mb10">
                        <div class="fl">
    						<label>类别：</label>
    			  			<select class="form-control inline w200" id="reimburse-type-select" onchange="reimburseTypeChange();">
    			  					<option value="all">所有</option>
                                <?php foreach($categorys as $_key => $__row): ?>
                                    <?php if(!empty($categorys[$_key])): ?>
                                    	<option value="<?php echo $_key; ?>"><?php echo $categorys[$_key]; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
    			  			</select>
                        </div>
                        <div class="fl ml20">
                            <label>搜索：</label>
                            <input class="form-control inline w200" placeholder="请输入搜索条件" id="search-condition" >
                            <button class="btn btn-success w80 mt-2 ml10" onclick="searchGoodsApply();">查询</button>
                        </div>
						<!-- <div id="new-subscribe-search" class="fl">
							<input class="w250 form-control inline h30 md5 mt5">&nbsp;
							<span class="glyphicon glyphicon-search"></span>
						</div> -->
						<div id="new-subscribe-add" class="fr">
							<button class="btn btn-primary w120 mr20 f18px" onclick="showSubscribeAdd()">新费用申请</button>
						</div>
					</div>
				  	<table class="table m0 table-bordered center" id="subscribe-table-new">
				  		<thead>
							<tr>
								<th class="hidden">Apply_ID</th>
								<th class="hidden">ID</th>
								<th><input type="checkbox" id="allCheckbox" onclick="changeAllChecked(this)"></th>
								<th>费用类型</th>
								<th>名称</th>
								<th>申请日期</th>
								<th>申请说明</th>
								<th>金额</th>
								<th>状态</th>
								<th>操作</th>
								<th class="hidden">type</th>
							</tr>
						</thead>
						<tbody id="tbody">
						<?php foreach ($goods_apply_list as $key): ?>
							<tr class="reimburse-detail-tr">
								<td class="hidden"><?php echo $key['apply_id'] ?></td>
								<td class="hidden"><?php echo $key['id'] ?></td>
								<td class="w40"><input type="checkbox" name="checkbox" class="checkbox" style="margin: 0 auto"></td>
								<td class="w120">
									<?php if($key['category']=='office'): ?>办公费
									<?php elseif($key['category']=='travel'): ?>差旅费
									<?php elseif($key['category']=='welfare'): ?>福利费
									<?php elseif($key['category']=='test'): ?>测试费
									<?php elseif($key['category']=='outsourcing'): ?>外包费
									<?php elseif($key['category']=='entertain'): ?>业务招待费
									<?php elseif($key['category']=='hydropower'): ?>水电费
									<?php elseif($key['category']=='rental'): ?>租赁费
									<?php elseif($key['category']=='intermediary'): ?>中介费
									<?php elseif($key['category']=='property'): ?>物管费
									<?php elseif($key['category']=='repair'): ?>修缮费
									<?php endif ?>
								</td>
								<td class="w130"><?php echo $key['name'] ?></td>
								<td class="w240"><?php echo $key['create_time'] ?></td>
								<td class="w150"><?php echo $key['reason'] ?></td>
								<td class="w105"><?php echo $key['price'] ?></td>
								<td class="w105">
									<?php if($key['status']=='cancel'): ?>已取消
									<?php elseif($key['status']=='success'): ?>
										<?php if($key['is_reimburse']=='yes'): ?>已报销
										<?php elseif($key['is_reimburse']=='no'): ?>已通过
										<?php endif ?>
									<?php elseif($key['status']=='wait'): ?>待审核
									<?php elseif($key['status']=='reject'): ?>未通过
									<?php endif ?>
								</td>
								<td class="w340">
									<?php if($key['is_reimburse']=='no'): ?>
										<?php if($key['status']=='success'): ?>
											<button class="btn btn-success nSR" onclick="toBatch(this.parentNode.parentNode);">生成报销单</button>
										<?php else: ?>
											
										<?php endif ?>

										<button class="btn btn-default" onclick="cancelApplyReason(this.parentNode.parentNode)">取消申请</button>
										<button class="btn btn-default" onclick='changeApply(this.parentNode.parentNode)'>修改申请</button>
									<?php elseif($key['is_reimburse']=='yes'): ?>
										
									<?php endif ?>
									
									
								</td>
								<td class="hidden">
									<?php echo $key['type'] ?>
								</td>
								<td class="hidden">
									<?php echo $key['fee_div_name'] ?>
								</td>
								<td class="hidden">
									<?php echo $key['fee_div_p'] ?>
								</td>
							</tr>
						<?php endforeach ?>
						</tbody>
				  	</table>
				  	<!-- <button id="batch" class="btn btn-default mt15" onclick="newShowReimburse2(document.getElementById('tbody'))">批量生成报销单</button> -->
				  	<button id="batch" class="btn btn-default mt15 block" onclick="newShowReimburse3(document.getElementById('tbody'))">批量生成报销单</button>
			  		<!-- 分页 -->
			  		<div class="mt20">
				        <?php 
				            $this->widget('CLinkPager',array(
				                'firstPageLabel'=>'首页',
				                'lastPageLabel'=>'末页',
				                'prevPageLabel'=>'上一页',
				                'nextPageLabel'=>'下一页',
				                'pages'=>$page,
				                'maxButtonCount'=>10,
				              )
				            );
				        ?>
				     </div>
			  	</div>
			  	<!-- 费用报销表格 -->
			  	<div id="reimburse-div" class="hidden bor-1-ddd pb30 center">
			  		<!-- <div id="new-reimbursement-p">
						<h4>
							<strong>费用报销记录</strong>
						</h4>
					</div> -->
					<div id="new-reimbursement-search" class="h40 mb10">
						<div id="new-reimbursement-search" class="fl hidden">
							<input class="w250 form-control inline h30 md5 mt5">
						</div>
					</div>
					<div id="new-reimbursement-list">
						<table class="table table-bordered"  id="reimburse-table">
							<thead>
								<tr>
									<th>报销单编号</th>
									<th>报销类型</th>
									<th>报销金额</th>
									<th>单据数量</th>
									<th>收款人</th>
									<th>报销日期</th>
									<th>状态</th>
									<th>操作</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($history_reimburses as $key): ?>
								<tr>
									<td><?php echo $key['id'] ?></td>
									<td>
									<?php if($key['category']=='office'): ?>办公费
									<?php elseif($key['category']=='travel'): ?>差旅费
									<?php elseif($key['category']=='welfare'): ?>福利费
									<?php elseif($key['category']=='test'): ?>测试费
									<?php elseif($key['category']=='outsourcing'): ?>外包费
									<?php elseif($key['category']=='entertain'): ?>业务招待费
									<?php elseif($key['category']=='hydropower'): ?>水电费
									<?php elseif($key['category']=='rental'): ?>租赁费
									<?php elseif($key['category']=='intermediary'): ?>中介费
									<?php elseif($key['category']=='property'): ?>物管费
									<?php elseif($key['category']=='repair'): ?>修缮费
									<?php endif ?>
									</td>
									<td><?php echo $key['total'] ?></td>
									<td><?php echo $key['receipt_num'] ?></td>
									<td><?php echo $key['payee'] ?></td>
									<td><?php echo $key['create_time'] ?></td>
									<td>
										<?php if($key['status']=='submitted'): ?>待付款
										<?php elseif($key['status']=='success'): ?>已付款
										<?php elseif($key['status']=='wait'): ?>未提交
										<?php endif ?>
									</td>
									<td>
										<?php if($key['status']=='wait'): ?>
										<button class="btn btn-success" onclick="showReimburseChange(this.parentNode.parentNode)">提交</button>
										<?php else: ?>
										<button class="btn btn-default" onclick="printReimburse(this);">打印报销单</button>
										<button class="btn btn-default" onclick="printReimburseList(this);">打印清单</button>
										<?php endif ?>
									</td>
								</tr>
							<?php endforeach ?>
							</tbody>
						</table>
						<div class="mt20" id="reimburse-pager">
					      <?php 
					          $this->widget('CLinkPager',array(
					              'firstPageLabel'=>'首页',
					              'lastPageLabel'=>'末页',
					              'prevPageLabel'=>'上一页',
					              'nextPageLabel'=>'下一页',
					              'pages'=>$history_page,
					              'maxButtonCount'=>10,
					            )
					          );
					      ?>
				      </div>
					</div>
			  	</div>
		    </div>
  			<!-- 确认报销单页面 -->
		  	<div class="center bor-1-ddd hidden" id="reimburse-bill-div">
				<div class="w800 m0a mt50 pb50">
					<div>
						<h3>报销支付审批单</h3> 
					</div>
					<div class="left">
						公司名称：广州善游网络科技有限公司
					</div>
					<div class="left mt5 mb5">
						<div class="w300 fl">
							报销部门：<span id="reimburse-department-name"></span>
						</div>
						<div class="w300 fl">
							报销人：
						</div>
						<div class="right pr20 fl">
							<?php echo date('Y 年 m 月 d 日');?>
						</div>
						<div class="clear"></div>
					</div>
					<table class="table table-bordered bor-1-ddd m0" style="line-height:40px;">
						<tbody>
							<tr>
								<th class="center" colspan="2">费用名称</th>
								<th class="center w100">金额(元)</th>
								<th class="center w100">单据(张)</th>
								<th rowspan="4" class="center w50">审<br>核</th>
								<td rowspan="4" class="w200"></td>
							</tr>
							<tr>
								<td colspan="2" class="left"><span>1、</span><span id="reimburse-type-name"></span></td>
								<td id="reimburse-money"></td>
								<td><input id="reimburse-bill-num-input" class="w80"></td>
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
								<td id="reimburse-total-money"></td>
								<td id="reimburse-bill-num"></td>
							</tr>
							<tr>
								<td colspan="6" class="left">
									<label class="w100">金额大写：</label>
									<span class="mr20">币别</span>
									<span id="unit-9" class="mr20 ml20"></span><span class="mr20">佰</span>
									<span id="unit-8" class="mr20"></span><span class="mr20">拾</span>
									<span id="unit-7" class="mr20"></span><span class="mr20">万</span>
									<span id="unit-6" class="mr20"></span><span class="mr20">仟</span>
									<span id="unit-5" class="mr20"></span><span class="mr20">佰</span>
									<span id="unit-4" class="mr20"></span><span class="mr20">拾</span>
									<span id="unit-3" class="mr20"></span><span class="mr20">元</span>
									<span id="unit-2" class="mr20"></span><span class="mr20">角</span>
									<span id="unit-1" class="mr20"></span><span class="mr20">分</span>
								</td>
							</tr>
							<tr>
								<th rowspan="2" class="w100">付款方式：</th>
								<td colspan="5">
									<div class="w80 fl left">
										<input type="radio" name="pay-way-checkbox" class="pointer" id="transform-checkbox" onclick="showTransform();">&nbsp;<span class="pointer bold" onclick="$(this).prev().click();showTransform();">转&nbsp;&nbsp;&nbsp;账</span>
									</div>
									<div class="w400 fl left">
										<span class="inline-block">开户行：</span>
										<span class="w100 inline-block">&nbsp;</span>
										<input class="w100 hidden" id="bank-input" value="<?php echo isset($bank) ? $bank['bank_info']: '';?>">
										<span class="ml5 inline-block">帐号：</span>
										<span style="width:170px;">&nbsp;</span>
										<input style="width:170px;" class="hidden" id="account-input" value="<?php echo isset($bank) ? $bank['bank_code']:'' ;?>">
									</div>
									<div class="w200 fl left">
										<span class="inline-block">收款人：</span>
										<input class="w80 hidden"  id="account-name-input" value="<?php echo isset($bank) ? $bank['payee']: '';?>">
									</div>
									<div class="clear"></div>
								</td>
							</tr>
							<tr>
								<td colspan="5">
									<div class="w80 fl left">
										<input type="radio" name="pay-way-checkbox" class="pointer" id="owe-checkbox" onclick="showOwe();">&nbsp;<span class="pointer bold" onclick="$(this).prev().click();showOwe();">冲借支</span>
									</div>
									<div class="w200 fl left">
										<span class="inline-block">原借款金额：</span>
										<input class="w100 hidden" id="inital-owe-money-input">
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="left mt5">
						<div class="w150 fl">核准：</div>
						<div class="w150 fl">财务复核：</div>
						<div class="w150 fl">会计：</div>
						<div class="w150 fl">出纳：</div>
						<div class="w150 fl">领款人：</div>
						<div class="clear"></div>
					</div>
					<div class="mt20 gray left">
						说明：请填写单据张数和付款方式
					</div>
					<div class="mt20">
						<button class="btn btn-lg btn-success w100" onclick="showSendReimburse();">提交</button>
						<button class="btn btn-lg btn-default w100 ml10" onclick="cancelSendReimburse();">返回</button>
					</div>
				</div>
		  	</div>
		  	<!-- 报销单提交页面 -->
		  	<div class="bor-1-ddd hidden" id="reimburse-change-div">
				<div class="w800 m0a mt50 pb50">
					<div class="center">
						<h3>报销清单</h3> 
					</div>
					<table class="table table-bordered center">
						<thead>
							<tr>
								<th class="hidden">报销单编号</th>
								<th>序号</th>
								<th>费用类型</th>
								<th>名称</th>
								<th>申请时间</th>
								<th>申请说明</th>
								<th>金额</th>
							</tr>
						</thead>
						<tbody id="reimburse-change-tbody">
							<!-- <tr>
								<td class="reimburse-change-id"></td>
								<td class="reimburse-change-type"></td>
								<td class="reimburse-change-name"></td>
								<td class="reimburse-change-date"></td>
								<td class="reimburse-change-state"></td>
								<td class="reimburse-change-price"></td>
							</tr> -->
						</tbody>
					</table>
					<div>
						<p>单据信息</p>
						<p class="inline">单据数量</p>
						<input type="text" id="reimburse-change-receipt" class=" ml10 mb30 form-control inline w100">
						<p>支付方式</p>
						<label><input name="payWay" type="radio" value="transfers" class="payWay"> 转账</label>
						<label class="ml20"><input name="payWay" type="radio" value="borrowing" class="payWay"> 借支</label>
						<div id="reimburse-change-transfers" class="mt25 hidden">
							<table>
								<tbody>
									<tr class="">
										<td><p class="inline">收款人</p></td>
										<td><input type="text" id="reimburse-change-payee" class="ml20 w100 mt5 mb5  form-control"></td>
									</tr>
									<tr class="">
										<td><p class="inline">开户银行</p></td>
										<td><input type="text" id="reimburse-change-bank" class="ml20 w130 mt5 mb5 form-control"></td>
									</tr>
									<tr class="">
										<td><p class="inline">账号</p></td>
										<td><input type="text" id="reimburse-change-account" class="ml20 w300 mt5 mb5 form-control"></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="reimburse-change-borrowing" class="mt25 hidden">
							<table>
								<tbody>
									<tr>
										<td><p class="inline">原借款金额</p></td>
										<td><input type="text" id="reimburse-change-borrow" class="ml20 w130 form-control"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					
					<div class="mt20 gray left">
						说明：请填写单据张数和付款方式
					</div>
					<div class="mt20">
						<button class="btn btn-lg btn-success w100" onclick="changeSendReimburse();">提交</button>
						<button class="btn btn-lg btn-default w100 ml10" onclick="cancelSendReimburse();">返回</button>
					</div>
				</div>
		  	</div>
		</div>
		<!-- 取消申请模态框 -->
		<div id="cancel-div" class="modal fade in hint bor-rad-5 w400">
		    <div class="modal-header bg-33 move">
		        <a class="close" data-dismiss="modal">×</a>
		        <h4 class="hint-title">请输入取消原因</h4>
		    </div>

		    <div class="modal-body">
		    	<input type="text" class="cancel-input hidden">
		        <textarea class="cancel-textarea h90 bor-rad-5 pd5" style="resize:none;width:100%;"></textarea>
		    </div>

		    <div class="modal-footer center">
		      <button class="btn btn-success" onclick="cancelApply()">提交</button>
		    </div>
		</div>
		<!-- 申请提示模态框 -->
        <div id="subscribe-remind-div" class="modal fade in hint bor-rad-5 w400">
            <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
                <a class="close" data-dismiss="modal">×</a>
                <h4 class="hint-title">提示消息</h4>
            </div>

            <div class="modal-body">
                请再细心确认一下，费用申请对应的类别和金额是否正确？
            </div>

            <div class="modal-footer center">
              <button class="btn btn-success pd10" data-dismiss="modal"><span class="glyphicon glyphicon-exclamation-sign"></span>&nbsp;好，我再核对一下</button>
            </div>
        </div>

        <!-- 报销提示模态框 -->
        <div id="reimburse-remind-div" class="modal fade in hint bor-rad-5" style="display: none;width:420px; ">
            <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
                <a class="close" data-dismiss="modal">×</a>
                <h4 class="hint-title">提示消息</h4>
            </div>

            <div class="modal-body">
                请再细心确认一下，费用有无发票和实际报销金额是否正确？
            </div>

            <div class="modal-footer center">
              <button class="btn btn-success" data-dismiss="modal"><span class="glyphicon glyphicon-exclamation-sign"></span>&nbsp;好，我再核对一下</button>
            </div>
        </div>

        <!-- 修改申请模态框 -->
        <div id="change-div" class="modal fade in hint bor-rad-5">
            <div class="modal-header bg-33 move">
                <a class="close" data-dismiss="modal">×</a>
                <h4 class="hint-title">修改费用申请</h4>
            </div>

            <div class="modal-body">
            	<table class="table table-bordered m0 center" id="change-apply-table">
        	  		<thead>
        	  			<tr class="bg-fa center">
        	  				<th class="center hidden">ID</th>
        	  				<th class="center hidden">apply_ID</th>
        	  				<th class="w250 center">类型</th>
        	  				<th class="w150 center">名称</th>
        	  				<th class="w150 center">总价</th>
        	  				<th class="center">说明</th>
        	  				<th class="w130 center">费用分摊方式</th>
        	  				<th class="w130 center">费用分摊说明</th>
        	  			</tr>
        	  		</thead>
        	  		<tbody>
        	  			<tr class="subscribe-tr center">
        	  				<td class="change-id hidden"></td>
        					<td class="change-applyid hidden"></td>
        	  				<td class="w250 change-type">
        	  					<select class="type-select f16px" onchange="anyTypeSelect();subscribeDataChange();selectCheck();">
        	  						<option value="office">办公费</option>
        	  						<option value="travel">差旅费</option>
        	  						<option value="welfare">福利费</option>
        	  						<?php if($tag != "common"): ?>
        	  						<option value="test">测试费</option>
        	  						<option value="outsourcing">外包费</option>
        	  						<option value="entertain">业务招待费</option>
        	  						<?php if($tag != "leader"): ?>
        	  						<option value="hydropower">水电费</option>
        	  						<option value="intermediary">中介费</option>
        	  						<option value="rental">租赁费</option>
        	  						<option value="property">物管费</option>
        	  						<option value="repair">修缮费</option>
        	  						<?php endif; ?>
        	  						<?php endif; ?>
        	  						<option value="other">其他</option>
        	  					</select>
        	  					<!-- 办公费二级类型 -->
        	  					<select class="office-select-any inline f16px " onchange="subscribeDataChange();">
        	  						<option value="快递费">快递费</option>
        	  						<option value="招聘费">招聘费</option>
        	  						<option value="通讯费">通讯费</option>
        	  						<option value="交通费">交通费</option>
        	  						<option value="网络费">网络费</option>
        	  						<option value="办公设备">办公设备</option>
        	  						<option value="办公软件">办公软件</option>
        	  						<option value="办公用品">办公用品</option>
        	  						<option value="其他">其他</option>
        	  					</select>
        	  					<!-- 福利费二级类型 -->
        	  					<select class="hidden welfare-select-any inline f16px" onchange="subscribeDataChange();">
        	  						<option value="加班费">加班费</option>
        	  						<option value="兴趣小组">兴趣小组</option>
        	  						<option value="体检费">体检费</option>
        	  						<?php if($tag != "common"): ?>
        	  						<option value="图书">图书</option>
        	  						<option value="工作餐">工作餐</option>
        	  						<option value="下午茶">下午茶</option>
        	  						<option value="生日礼物">生日礼物</option>
        	  						<option value="生日会">生日会</option>
        	  						<option value="婚育礼物">婚育礼物</option>
        	  						<option value="部门经费">部门经费</option>
        	  						<option value="旅游经费">旅游经费</option>
        	  						<option value="培训费">培训费</option>
        	  						<option value="游戏经费">游戏经费</option>
        	  						<option value="年会费用">年会费用</option>
        	  						<option value="其他">其他</option>
        	  						<?php endif; ?>
        	  					</select>
        	  				</td>
        	  				<td class="change-name">
        	  					<input class="w130 h30 form-control m0a name-input">
        	  				</td>
        	  				<td class="change-price">
        	  					<input class="w110 h30 form-control inline price-input">&nbsp;元
        	  				</td>
        	  				<td class="change-state">
        	  					<input class="form-control h30 reason-input">
        	  				</td>
        	  				<td class="chagne-tpl">
        	  					<select class="form-control inline input-fee-div" onchange="showNewDiv(this)">
                            		<?php foreach ($fee_div_tpl as $row): ?>
                                		<option value='<?php echo $row->fee_div_p?>'><?php echo $row->name?></option>
                            		<?php endforeach;?>
                           			<option value='user-defined' >自定义</option>
                        			</select>
        	  				</td>
        	  				<td style="color: blue;"></td>
        	  			</tr>
        	  		</tbody>
        	  	</table>
            </div>

            <div class="modal-footer center">
              <button class="btn btn-success" onclick="changeApplyCommit()">提交</button>
            </div>
        </div>
</div>

<!-- 用户自定义模板模态框  yeqingwen 2016-01-05 -->
<div id="add-Tpl-div" class="modal fade in hint bor-rad-5 w600">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="cancelTpl()">×</a>
        <h4 class="hint-title">自定义分配比例</h4>
        <span class="hidden" id="new-tpl-select-id"></span>
    </div>
    <div class="modal-body">
        <table class="table table-unbordered m0">
            <tbody id="newTpl-tbody">
                <tr>
                    <th class="w80 va-t center">名称</th>
                    <td class="w80"><input class="form-control w200" id="add_Tpl_name" value=''></td>
                </tr>
                <tr>
                    <th class="w80 va-t center">摊销项目</th>
                    <td class="w80 add_Tpl_pro">
                        <select class="form-control w200 inline-block add-Tpl-select">
                        <?php foreach ($project_list as $value): ?>
                            <option><?php echo $value['name'] ?></option>
                        <?php endforeach ?>
                        </select>
                        <input class="form-control w150 inline-block add-Tpl-input" value='' placeholder="比例">
                        <a href="javascript:;" onclick="deleteLine(this)">删除</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="center">
        <a href="javascript:;" class="" onclick="addLine()">增加一行</a>
    </div>

    <div class="modal-footer center" id="modal-footer">
            <button class="btn btn-success w100 ml10 mr120" onclick="newTpl()">确认</button>
            <button class="btn btn-default w100" data-dismiss="modal" onclick="cancelTpl()">取消</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
    var condition = "<?php echo $search_condition ?>";
    if(categoryToCN(condition) != null) {
        $("#search-condition").val(categoryToCN(condition));
        console.log(condition);
    }
    else {
        $("#search-condition").val(condition);
    }

/*-------------------------------------------------------打印----------------------------------------------------------------------*/
	function deleteLine(obj){
		// debugger;
		var id = $(obj).parent().parent()[0].children[0].innerText;
		// console.log($(obj).parent().parent().parent());
		if($(obj).parent().parent().parent().children().length > 2){
			$(obj).parent().parent()[0].remove();
		}

	}

	// 打印清单
	function printReimburseList(obj){
		var id = $(obj).parent().parent().children().first().text();
		var href = "/user/printReimburseList/id/"+id;
		window.open(href);
	}

	// 打印报销单
	function printReimburse(obj){
		var id = $(obj).parent().parent().children().first().text();
		var href = "/user/printReimburse/id/"+id;
		window.open(href);
	}

/*-------------------------------------------------------报销单详情----------------------------------------------------------------------*/

	// 显示报销单详情
	function showReimburseDetail(obj){
		var id = $(obj).parent().parent().children().first().text();
		$.each(reimburse_bill_detail_arr, function(){
			if(this['id'] == id){
				// 填充数据
				$("#reimburse-money-detail").text(this['total']);
				$("#reimburse-bill-num-detail-1").text(this['receipt_num']);
				$("#reimburse-type-name-detail").text(categoryToCN(this['category']));
				if(this['department_name'] == "总经理办公室" || this['department_name'] == "人事行政部" || this['department_name'] == "商务部" || this['department_name'] == "IT运维部" || this['department_name'] == "项目管理部"){
					$("#reimburse-department-name-detail").text("公共部门");
				}else{
					$("#reimburse-department-name-detail").text(this['department_name']);
				}
				$('#reimburse-no-detail').text(this['id']);
				if(this['way'] == "borrow"){
					$("#borrow-detail").prev().text("√");
					$("#transform-detail").prev().html("&nbsp;&nbsp;");
					$("#owe-money-detail").text(this['borrow_amount']);
				}else{
					$("#transform-detail").prev().text("√");
					$("#borrow-detail").prev().html("&nbsp;&nbsp;");
					var bank = this['bank_info'].split(" ")[0];
					var account = this['bank_info'].split(" ")[1];
					$("#bank-detail").text(bank);
					$("#account-detail").text(account);
					$("#payee-detail").text(this['payee']);
				}
				$("#reimburse-total-money-detail").text(this['total']);
				$("#reimburse-bill-num-detail-2").text(this['receipt_num']);
				$("#reimburse-date-detail").text(this['create_time']);


				$("#detail-unit-blank").addClass("hidden");
				for(var j = 9; j >= 1; j--){
					$("#detail-unit-"+j).removeClass("hidden");
					$("#detail-unit-"+j).text("");
					$("#detail-unit-"+j).next().removeClass("hidden");
				}

				// 填写到大写中
				var index = 3;
				var integer_str = "";
				var else_str = "";
				if(String(this['total']).indexOf(".") > -1){
					integer_str = String(this['total']).split(".")[0]; // 整数
					else_str = String(this['total']).split(".")[1]; // 小数
				}else{
					integer_str = String(this['total']);
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
			}
		});

		// 显示报销单详情
		$("#subscribe-info-div").fadeOut(400,function(){
			$("html,body").animate({scrollTop:0}, 400,function(){
				$("#reimburse-detail-div").slideDown(400);
			});
		});
	}

	// 隐藏报销单详情
	function hideReimburseDetail(obj){
		$("#reimburse-detail-div").fadeOut(400,function(){
			$("#subscribe-info-div").slideDown(400);
		});
	}

	// 报销单数据初始化
	var reimburse_bill_detail_arr = new Array();
	<?php 
		if(!empty($history_reimburses)){
			foreach($history_reimburses as $key=>$hrow){
				$create_time = date('Y 年 m 月 d 日', strtotime($hrow['create_time']));
				$department_name = $reimburses_add_info[$key]['d_name'];
				echo "reimburse_bill_detail_arr.push({'department_name':'{$department_name}', 'id':'{$hrow['id']}', 'category':'{$hrow['category']}', 'total':'{$hrow['total']}', 'receipt_num':'{$hrow['receipt_num']}', 'bank_info':'{$hrow['bank_info']}', 'payee':'{$hrow['payee']}','borrow_amount':'{$hrow['borrow_amount']}','way':'{$hrow['way']}', 'create_time':'".$create_time."'});";
			}
		}
	?>

/*-------------------------------------------------------报销清单----------------------------------------------------------------------*/

	// 显示报销清单
	function showReimburseList(obj){
		var id = $(obj).parent().parent().children().first().text();
		var th_tag = false;  // 是否已输出表头标记
		var total = 0;  // 总计
		var total_num = 0;  // 总件数
		var summary = 0; // 报销总金额
		var row_index = 1;  // 行数

		// 清空报销清单表格
		$("#reimburse-list-table").find("tbody").children().remove();

		// 遍历报销清单数组
		$.each(reimburse_list_arr, function(){
			if(this['id'] == id){
				var th_str = "";
				if(!th_tag){
					th_str = "<td  class='w100' id='reimburse-list-th'>"+categoryToCN(this['category'])+"</td>";
					th_tag = true;
				}
				var str = "<tr>"+th_str+"<td class='w100'>"+this['type']+"</td>"+
				"<td>"+this['name']+"</td><td>"+this['price']+"</td>"+
				"<td>"+parseInt(this['quantity'])+"</td>"+
				"<td>"+accMultiply(parseFloat(this['price']), parseFloat(this['quantity']))+"</td>"+
				"<td>"+this['user_name']+"</td>"+
				"<td>"+this['receipt']+"</td>"+
				"<td>"+this['total']+"</td>"+
				"<td>"+this['create_time']+"</td>"+
				"</tr>";
				total = accAdd(total, accMultiply(parseFloat(this['price']), parseFloat(this['quantity'])));
				total_num = accAdd(total_num, parseInt(this['quantity']));
				summary = accAdd(summary, parseFloat(this['total']));
				$("#reimburse-list-table").find("tbody").append(str);
				row_index++;
			}
		});
		var total_str = "<tr><td>合计</td><td></td><td></td><td>"+total_num+"</td><td></td><td></td><td></td><td>"+summary+"</td><td></td></tr>";
		$("#reimburse-list-table").find("tbody").append(total_str);
		$("#reimburse-list-th").attr("rowspan",row_index);

		// 显示报销清单
		$("#subscribe-info-div").fadeOut(400,function(){
			$("html,body").animate({scrollTop:0}, 400, function(){
				$("#reimburse-list-div").slideDown(400);
			});
		});
	}

	// 隐藏报销清单
	function hideReimburseList(){
		$("#reimburse-list-div").fadeOut(400,function(){
			$("#subscribe-info-div").slideDown(400);
		});
	}

	// 报销清单数组初始化
	var reimburse_list_arr = new Array();
	<?php 
		if(!empty($history_reimburses) && !empty($history_reimburses_apply)){
			foreach($history_reimburses as $key=>$hrow){
				$category = $hrow['category'];
				$reimburse_id = $hrow['id'];
				$user_name = $reimburses_add_info[$key]['cn_name'];
				foreach($hrow->details as $ddrow){
					$create_time = date('Y/m/d',strtotime($history_reimburses_apply[$ddrow['apply_detail_id']]));
					$total = $ddrow['amount'];
					$receipt = ($ddrow['have_receipt'] == "yes") ? '有' : '无';
					echo "reimburse_list_arr.push({'id':'{$reimburse_id}', 'category':'{$category}', 'type':'{$ddrow->applyDetail['type']}', 'name':'{$ddrow->applyDetail['name']}', 'price':'".(Float)$ddrow->applyDetail['price']."', 'quantity':'{$ddrow->applyDetail['quantity']}', 'user_name':'".$user_name."', 'create_time':'{$create_time}', 'total':'".(Float)$total."', 'receipt':'".$receipt."'});";
				}
			}
		}
	?>

/*-------------------------------------------------------确认报销单----------------------------------------------------------------------*/

	// 提交报销单
	var reimburse_remind_tag = false;
	function showSendReimburse(){
		// 获取数据
		var bill_num = $("#reimburse-bill-num-input").val();
		var type = "";
		var bank = "";
		var account = "";
		var name = "";
		var owe_money_initial = "";
		if(document.getElementById("owe-checkbox").checked){
			type = "borrow";
			owe_money_initial = $("#inital-owe-money-input").val();
		}
		if(document.getElementById("transform-checkbox").checked){
			type = "transfer";
			bank = $("#bank-input").val();
			account = $("#account-input").val();
			name = $("#account-name-input").val();
		}

		// 验证数据
		var d_pattern = /^\d+$/;
		var money_pattern = /^\d+(\.\d{1,2})?$/;
		if(type == ""){
			showHint("提示信息","请选择付款方式");
		}else if(bill_num == ""){
			showHint("提示信息", "请输入单据张数");
			$("#reimburse-bill-num-input").focus();
		}else if(!d_pattern.exec(bill_num)){
			showHint("提示信息", "单据张数输入格式错误");
			$("#reimburse-bill-num-input").focus();
		}else if(type == "borrow"  && !money_pattern.exec(owe_money_initial)){
			showHint("提示信息", "原借款金额输入格式错误");
			$("#inital-owe-money-input").focus();
		}else if(type == "transfer" && bank == ""){
			showHint("提示信息", "请输入开户行");
			$("#bank-input").focus();
		}else if(type == "transfer" && account == ""){
			showHint("提示信息", "请输入帐号");
			$("#account-input").focus();
		}else if(type == "transfer" && !d_pattern.exec(account)){
			showHint("提示信息", "帐号输入格式错误");
			$("#account-input").focus();
		}else if(type == "transfer" && name == ""){
			showHint("提示信息", "请输入收款人姓名");
			$("#account-name-input").focus();
		}else{

			// console.log([type,bank,account,name,owe_money_initial,bill_num,reimburse_detail_arr]);
			$.ajax({
				type:'post',
				dataType:'json',
				url:'/ajax/reimburse',
				data:{'details':reimburse_detail_arr},
				success:function(result){
					if(result.code == 0){
						showHint("提示信息","提交报销单成功！");
						setTimeout(function(){location.reload();},1200);
					}else if(result.code == -1){
						showHint("提示信息","提交报销单失败，请重试！");
					}else if(result.code == -2){
						showHint("提示信息","参数错误！");
					}else{
						console.log(result.code);
						showHint("提示信息","你没有权限执行此操作！");
					}
				}
			});

			reimburse_remind_tag = false;
		}
	}

	// 取消提交报销单
	function cancelSendReimburse(){
		location.hash = "#reimburse";
		$("#reimburse-change-div").fadeOut(400,function(){
			$("#new-sub2").slideDown(400);
		});
	}

	// 显示冲借支
	function showOwe(){
		if(document.getElementById("owe-checkbox").checked){
			// 显示冲借支输入框
			$("#inital-owe-money-input").removeClass("hidden");

			// 隐藏转账输入框
			$("#bank-input").addClass("hidden");
			$("#bank-input").prev().removeClass("hidden");
			$("#account-input").addClass("hidden");
			$("#account-input").prev().removeClass("hidden");
			$("#account-name-input").addClass("hidden");

			$("#inital-owe-money-input").focus();
		}
	}

	// 显示转账输入框
	function showTransform(){
		if(document.getElementById("transform-checkbox").checked){
			// 显示转账输入框
			$("#bank-input").removeClass("hidden");
			$("#bank-input").prev().addClass("hidden");
			$("#account-input").removeClass("hidden");
			$("#account-input").prev().addClass("hidden");
			$("#account-name-input").removeClass("hidden");

			// 隐藏冲借支输入框
			$("#inital-owe-money-input").addClass("hidden");

			$("#bank-input").focus();
		}
	}

/*-------------------------------------------------------报销申请----------------------------------------------------------------------*/

	// 报销记录数组初始化
	var reimburse_arr = new Array();
	<?php 
		if(!empty($reimburses)){
			foreach($reimburses as $detail){
				foreach($detail as $row){
					echo "reimburse_arr.push({'apply_id':'{$row['apply_id']}', 'id':'{$row['id']}', 'name':'{$row['name']}', 'category':'{$row['category']}', 'quantity':'{$row['quantity']}', 'price':'{$row['price']}', 'create_time':'{$row['create_time']}'});";
				}
			}
		}
	?>

	// 报销类别变更
	var bill_detail_arr = new Array();
	function reimburseTypeChange(){
		
		var category = $("#reimburse-type-select").val();  // 报销类别
		// console.log(category);
		// if(category == "all"){
		// 	$('#batch').addClass('hidden');
		// }else{
		// 	$('#batch').removeClass('hidden');
		// }
		// 清空报销表格
		// var url = location.href;
		// var add = "&type="+category;
		// url = url + add;
		if(category == "all"){
			window.location.href="/user/subscribe";
		}else{
			window.location.href="/user/subscribe?type=" + category;
		}
		
		// $("#subscribe-table-new").find("tbody").children().remove();

		// var exist_tag = false;  // 是否存在可报销的记录标记
		
		// // 遍历报销记录数组
		// if(category == "all"){
		// 	$.each(goods_apply_list, function(){
		// 		var str = '';	
		// 		// exist_tag = true;
		// 		if(this['status'] == 'cancel'){
		// 			this['status'] = "已取消";
		// 		}else if(this['status'] == 'success'){
		// 			this['status'] = "已通过";
		// 		}else if(this['status'] == 'wait'){
		// 			this['status'] = "待审批";
		// 		}

		// 		if(this['is_reimburse'] == "no"){
		// 			var button = "<button class='btn btn-success nSR' onclick='newShowReimburse(this.parentNode.parentNode);'>生成报销单</button>";
		// 			button += "<button class='btn btn-default ml5' onclick='cancelApplyReason(this.parentNode.parentNode)'>取消申请</button>";
		// 			button += "<button class='btn btn-default ml5' onclick='changeApply(this.parentNode.parentNode)'>修改申请</button>";					
		// 		}else{
		// 			// var button = "<button class='btn btn-default nSR bgeaeaea' onclick='javaScript:;''>已完成报销</button>";
		// 			var button = "";
		// 		}
		// 		var category_str = categoryToCN(this['category']);
		// 		str += "<tr class='reimburse-detail-tr'>"+
		// 		"<td class='hidden'>"+this['apply_id']+"</td>"+
		// 		"<td class='hidden'>"+this['id']+"</td>"+
		// 		"<td class='w40'>"+"<input type='checkbox' name='checkbox' class='checkbox'>"+"</td>"+
		// 		"<td class='w120'>"+category_str+"</td>"+
		// 		"<td class='reimburse-content w80'>"+this['name']+"</td>"+
		// 		"<td class='w240'>"+this['create_time']+"</td>"+
		// 		"<td class='w150'>"+this['reason']+"</td>"+
		// 		"<td class='w105'>"+this['price']+"</td>"+
		// 		"<td class='w105'>"+this['status']+"</td>"+
		// 		"<td class='w340'>"+
		// 		button+
		// 		"</td>"+
		// 		"</tr>";
				
		// 		$("#subscribe-table-new").find("tbody").append(str);
		// 		bill_detail_arr.push(this['id']);
		// });
		// }else{
		// 	$.each(goods_apply_list, function(){
		// 	var str = '';
		// 	if(this['category'] == category){
					
		// 		// exist_tag = true;
		// 		if(this['status'] == 'cancel'){
		// 			this['status'] = "已取消";
		// 		}else if(this['status'] == 'success'){
		// 			this['status'] = "已通过";
		// 		}else if(this['status'] == 'wait'){
		// 			this['status'] = "待审批";
		// 		}

		// 		if(this['is_reimburse'] == "no"){
		// 			var button = "<button class='btn btn-success nSR' onclick='newShowReimburse(this.parentNode.parentNode);'>生成报销单</button>";
		// 			button += "<button class='btn btn-default ml5' onclick='cancelApplyReason(this.parentNode.parentNode)'>取消申请</button>";
		// 			button += "<button class='btn btn-default ml5' onclick='changeApply(this.parentNode.parentNode)'>修改申请</button>";					
		// 		}else{
		// 			// var button = "<button class='btn btn-default nSR bgeaeaea' onclick='javaScript:;''>已完成报销</button>";
		// 			var button = "";
		// 		}
		// 		var category_str = categoryToCN(this['category']);
		// 		str += "<tr class='reimburse-detail-tr'>"+
		// 		"<td class='hidden'>"+this['apply_id']+"</td>"+
		// 		"<td class='hidden'>"+this['id']+"</td>"+
		// 		"<td class='w40'>"+"<input type='checkbox' name='checkbox' class='checkbox'>"+"</td>"+
		// 		"<td class='w120'>"+category_str+"</td>"+
		// 		"<td class='reimburse-content w80'>"+this['name']+"</td>"+
		// 		"<td class='w240'>"+this['create_time']+"</td>"+
		// 		"<td class='w150'>"+this['reason']+"</td>"+
		// 		"<td class='w105'>"+this['price']+"</td>"+
		// 		"<td class='w105'>"+this['status']+"</td>"+
		// 		"<td class='w340'>"+
		// 		button+
		// 		"</td>"+
		// 		"</tr>";
				
		// 		$("#subscribe-table-new").find("tbody").append(str);
		// 		bill_detail_arr.push(this['id']);
		// 	}

		// });
		// }
		
		// if($("#subscribe-table-new").find("tbody").children().length == 0){
		// 	var str = "";
		// 	str += "<tr class='reimburse-detail-tr'>"+
		// 	"<td class='hidden'></td>"+
		// 	"<td class='hidden'></td>"+
		// 	"<td class='w40'></td>"+
		// 	"<td class='w120'></td>"+
		// 	"<td class='reimburse-content w80'></td>"+
		// 	"<td class='w240'></td>"+
		// 	"<td class='w150'></td>"+
		// 	"<td class='w105'></td>"+
		// 	"<td class='w105'></td>"+
		// 	"<td class='w340'></td>"+
		// 	"</tr>";
		// 	$("#subscribe-table-new").find("tbody").append(str);
		// }

		// // 判断是否有可报销的记录
		// if(!exist_tag){
  //           $("#reimburse-show-btn").addClass('hidden');
  //       }else{
  //           $("#reimburse-show-btn").removeClass('hidden');
  //       }
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

	// 加载可报销的类别
	function loadReimburseType(){
		var first_category = "";
		$("#reimburse-type-select").children().each(function(){
			var category = $(this).val();
			var find_tag = false;  // 查找标记

			// 判断是否有可报销的记录
			$.each(reimburse_arr,function(){
				if(this['category'] == category){
					first_category = category;
					find_tag = true;
					return false;
				}
			});

			// 如果有就显示选项,没有就隐藏
			if(find_tag){
				$(this).removeClass("hidden");
			}else{
				$(this).addClass("hidden");
			}
		});
		$("#reimburse-type-select").val(first_category);
	}

	// 显示确认报销单窗口
	var reimburse_detail_arr = new Array();
	function showReimburse(){
		if(apply_detail_arr.length < 1){
			showHint("提示信息","请至少选择一项以报销");
		}else{
			reimburse_detail_arr = new Array();
			var d_pattern = /^\d+(\.\d{1,2})?$/;
			var f_tag = false; // 错误标记
			var total = 0; // 总计
			var reimburse_type = $("#reimburse-type-select").val(); // 报销类型

			// 遍历每一行报销记录
			$("tr.reimburse-detail-tr").each(function(){

				var find_tag = false; 
				var apply_detail_id = $(this).find("td.reimburse-apply-detail-id").text();
				var apply_id = $(this).find("td.reimburse-apply-id").text();
				var content = $(this).find("td.reimburse-content").text();

				// 查找是否有选发票的
				$.each(apply_detail_arr, function(key, value){
					if(value == apply_detail_id){
						find_tag = true;
						return false;
					}
				});
				if(find_tag){
					// 查找有没有发票
					var bill_find_tag = false;
					$.each(bill_detail_arr, function(key, value){
						if(value == apply_detail_id){
							bill_find_tag = true;
							return false;
						}
					});	
					if(bill_find_tag){
						var have_bill = "yes";
					}else{
						var have_bill = "no";
					}

					// 金额的获取
					var amount = "";
					amount = $(this).find("input.pay-input").val();
					if(amount == ""){
						showHint("提示信息", "请输入已付金额");
						$(this).find("input.pay-input").focus();
						f_tag = true;
						return false;
					}else if(!d_pattern.exec(amount)){
						showHint("提示信息", "已付金额输入格式错误");
						$(this).find("input.pay-input").focus();
						f_tag = true;
						return false;
					}

					// 计算总计
					total = accAdd(total, parseFloat(amount));

					// 填充到数组里面
					reimburse_detail_arr.push({'have_receipt':have_bill, 'amount':amount, 'apply_id':apply_id, 'apply_detail_id':apply_detail_id, 'content':content});
				}
			});

			// 填写部门名称、报销费用名称、报销金额
			$("#reimburse-type-name").text(categoryToCN(reimburse_type));
			$("#reimburse-money").text(total);
			$("#reimburse-total-money").text(total);
			var department_name = "<?php echo empty($this->user) ? '' : $this->user->department->name; ?>";
			if(department_name == "总经理办公室" || department_name == "人事行政部" || department_name == "商务部" || department_name == "IT运维部" || department_name == "项目管理部"){
				$("#reimburse-department-name").text("公共部门");
			}else{
				$("#reimburse-department-name").text(department_name);
			}

			// 填写到大写中
			var index = 3;
			var integer_str = "";
			var else_str = "";
			if(String(total).indexOf(".") > -1){
				integer_str = String(total).split(".")[0]; // 整数
				else_str = String(total).split(".")[1];  // 小数
			}else{
				integer_str = String(total);
			}
			for(var n = integer_str.length; n > 0; n--){
			    var num = parseInt(integer_str.substring(n-1, n));
			    $("#unit-"+index++).text(toCapital(num));
			}
			if(else_str){
				var k = 2;
				for(var n = 0; n < else_str.length; n++){
				    var num = parseInt(else_str.substring(n, n+1));
				    $("#unit-"+k--).text(toCapital(num));
				}
			}

			// 显示确认报销单页面
			if(!f_tag){
				if(!reimburse_remind_tag){
					showReimburseRemind();
					reimburse_remind_tag = true;
				}else{
					$("#subscribe-info-div").fadeOut(400,function(){
						$("#reimburse-bill-div").slideDown(400);
					});
				}
			}
		}
	}

	function newShowReimburse(row){
		
		console.log(row);
			
		reimburse_detail_arr = new Array();
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		var f_tag = false; // 错误标记
		var total = 0; // 总计
		var reimburse_type = row.children[3].innerText; // 报销类型

		// 遍历每一行报销记录
		
			var find_tag = false;
			var apply_detail_id = row.children[1].innerText;
			var apply_id = row.children[0].innerText;
			var content = row.children[4].innerText;//名称
			
			have_bill = "yes";//默认有发票
			
			// 查找是否有选发票的
			// $.each(apply_detail_arr, function(key, value){
			// 	if(value == apply_detail_id){
			// 		find_tag = true;
			// 		return false;
			// 	}
			// });
			find_tag = true;
			if(find_tag){
				// 查找有没有发票
				// var bill_find_tag = false;
				// $.each(bill_detail_arr, function(key, value){
				// 	if(value == apply_detail_id){
				// 		bill_find_tag = true;
				// 		return false;
				// 	}
				// });	
				// if(bill_find_tag){
				// 	var have_bill = "yes";
				// }else{
				// 	var have_bill = "no";
				// }

				// 金额的获取
				var amount = "";
				amount = row.children[7].innerText;
				if(amount == ""){
					showHint("提示信息", "请输入已付金额");
					f_tag = true;
					return false;
				}else if(!d_pattern.exec(amount)){
					showHint("提示信息", "已付金额输入格式错误");
					f_tag = true;
					return false;
				}

				// 计算总计
				total = accAdd(total, parseFloat(amount));

				// 填充到数组里面
				reimburse_detail_arr.push({'have_receipt':have_bill, 'amount':amount, 'apply_id':apply_id, 'apply_detail_id':apply_detail_id, 'content':content});
			}
		

			// 填写部门名称、报销费用名称、报销金额
			// $("#reimburse-type-name").text(categoryToCN(reimburse_type));
			$("#reimburse-type-name").text(reimburse_type);
			$("#reimburse-money").text(total);
			$("#reimburse-total-money").text(total);
			var department_name = "<?php echo empty($this->user) ? '' : $this->user->department->name; ?>";
			if(department_name == "总经理办公室" || department_name == "人事行政部" || department_name == "商务部" || department_name == "IT运维部" || department_name == "项目管理部"){
				$("#reimburse-department-name").text("公共部门");
			}else{
				$("#reimburse-department-name").text(department_name);
			}

			// 填写到大写中
			var index = 3;
			var integer_str = "";
			var else_str = "";
			if(String(total).indexOf(".") > -1){
				integer_str = String(total).split(".")[0]; // 整数
				else_str = String(total).split(".")[1];  // 小数
			}else{
				integer_str = String(total);
			}
			for(var n = integer_str.length; n > 0; n--){
			    var num = parseInt(integer_str.substring(n-1, n));
			    $("#unit-"+index++).text(toCapital(num));
			}
			if(else_str){
				var k = 2;
				for(var n = 0; n < else_str.length; n++){
				    var num = parseInt(else_str.substring(n, n+1));
				    $("#unit-"+k--).text(toCapital(num));
				}
			}
			
			// 显示确认报销单页面
			$("#new-sub2").fadeOut(400,function(){
			$("#reimburse-bill-div").removeClass('hidden');
			$("#reimburse-bill-div").slideDown(400);
			});
	}
	//批量
	function newShowReimburse2(row){
		reimburse_detail_arr = new Array();
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		var f_tag = false; // 错误标记
		var total = 0; // 总计
		var reimburse_type = row.children[0].children[3].innerText; // 报销类型

		// 遍历每一行报销记录
		// debugger;
		$("tr.reimburse-detail-tr").each(function(){
				if(this.children[2].children[0].checked == true){
					var reimburse_type = this.children[3].innerText; // 报销类型
					var find_tag = false; 
					var apply_detail_id = this.children[1].innerText;
					var apply_id = this.children[0].innerText;
					var content = this.children[4].innerText;
					var have_bill = "yes";

					// 金额的获取
					var amount = "";
					amount = this.children[7].innerText;
					if(amount == ""){
						showHint("提示信息", "请输入已付金额");
						f_tag = true;
						return false;
					}else if(!d_pattern.exec(amount)){
						showHint("提示信息", "已付金额输入格式错误");
						f_tag = true;
						return false;
					}

					// 计算总计
					total = accAdd(total, parseFloat(amount));

					// 填充到数组里面
					reimburse_detail_arr.push({'have_receipt':have_bill, 'amount':amount, 'apply_id':apply_id, 'apply_detail_id':apply_detail_id, 'content':content});
					// }
				}
				
			});

		

			// 填写部门名称、报销费用名称、报销金额
			// $("#reimburse-type-name").text(categoryToCN(reimburse_type));
			$("#reimburse-type-name").text(reimburse_type);
			$("#reimburse-money").text(total);
			$("#reimburse-total-money").text(total);
			var department_name = "<?php echo empty($this->user) ? '' : $this->user->department->name; ?>";
			if(department_name == "总经理办公室" || department_name == "人事行政部" || department_name == "商务部" || department_name == "IT运维部" || department_name == "项目管理部"){
				$("#reimburse-department-name").text("公共部门");
			}else{
				$("#reimburse-department-name").text(department_name);
			}

			// 填写到大写中
			var index = 3;
			var integer_str = "";
			var else_str = "";
			if(String(total).indexOf(".") > -1){
				integer_str = String(total).split(".")[0]; // 整数
				else_str = String(total).split(".")[1];  // 小数
			}else{
				integer_str = String(total);
			}
			for(var n = integer_str.length; n > 0; n--){
			    var num = parseInt(integer_str.substring(n-1, n));
			    $("#unit-"+index++).text(toCapital(num));
			}
			if(else_str){
				var k = 2;
				for(var n = 0; n < else_str.length; n++){
				    var num = parseInt(else_str.substring(n, n+1));
				    $("#unit-"+k--).text(toCapital(num));
				}
			}

			// 显示确认报销单页面
			$("#new-sub2").fadeOut(400,function(){
				$("#reimburse-bill-div").removeClass('hidden');
				$("#reimburse-bill-div").slideDown(400);
			});
	}

	// 精确加法
	function accAdd(arg1,arg2){  
		var r1,r2,m;  
		try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}  
		try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}  
		m=Math.pow(10,Math.max(r1,r2));
		// console.log(m);
		return (arg1*m+arg2*m)/m;  
	}

	// 精确乘法
	function accMultiply(arg1,arg2){  
	    var m=0,s1=arg1.toString(),s2=arg2.toString();  
	    try{m+=s1.split(".")[1].length}catch(e){}  
	    try{m+=s2.split(".")[1].length}catch(e){}  
	    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);  
	}  

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

	// 显示金额输入框
	var apply_detail_arr = new Array();
	function showMoneyInput(obj){
		if(obj.checked){
			$(obj).parent().next().removeClass("hidden");
			var apply_detail_id = $(obj).parent().parent().parent().find("td.reimburse-apply-detail-id").text();
			apply_detail_arr.push(apply_detail_id);
		}else{
			$(obj).parent().next().addClass("hidden");
			var apply_detail_id = $(obj).parent().parent().parent().find("td.reimburse-apply-detail-id").text();
			$.each(apply_detail_arr, function(key, value){
				if(value == apply_detail_id) apply_detail_arr.splice(key,1);
			});
		}
	}

	// 发票选择操作
	function billChange(obj){
		if(obj.checked){
			var apply_detail_id = $(obj).parent().parent().parent().find("td.reimburse-apply-detail-id").text();
			bill_detail_arr.push(apply_detail_id);
		}else{
			var apply_detail_id = $(obj).parent().parent().parent().find("td.reimburse-apply-detail-id").text();
			$.each(bill_detail_arr, function(key, value){
				if(value == apply_detail_id) bill_detail_arr.splice(key,1);
			});
		}
	}

/*-------------------------------------------------------单个费用申请----------------------------------------------------------------------*/

	// 计算合计，用于判断是否需要给sara选择
	function singleTotalCal(){
		var price = $("#price").val();
		var unit = $("#unit").val();
		var budget = $("#rest-budget-span").text();
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		var unit_pattern = /^\d+([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/;
		if(parseFloat(budget) < 0){
			$("#single-select-tr").addClass("hidden");
			$("input[name='single-selection']").each(function(){
				if($(this).val() == "1"){
					this.checked = true;
				}else{
					this.checked = false;
				}
			});
		}else{
			if(d_pattern.exec(price) && unit_pattern.exec(unit)){
				unit = parseInt(unit);
				price = parseFloat(price);
				budget = parseFloat(budget);
				var total = unit*price;
				var rest = budget - total;
				if(rest < 0){
					$("#single-select-tr").addClass("hidden");
					$("input[name='single-selection']").each(function(){
						if($(this).val() == "1"){
							this.checked = true;
						}else{
							this.checked = false;
						}
					});
				}else{
					$("#single-select-tr").removeClass("hidden");
					$("input[name='single-selection']").each(function(){
						if($(this).val() == "1"){
							this.checked = false;
						}else{
							this.checked = true;
						}
					});
				}
			}
		}
	}

	// 提示和验证申请
	var data = new FormData();
	var single_remind_tag = false;  // 提示标记
	function showNewSubscribe(){
		// 初始化表单对象
		data = new FormData();

		// 获取数据
		var name = $("#name").val();
		var first_type = $("#type-select").val();
		var second_type = "";
		if(first_type == "office"){
			second_type = $("#office-select").val();
		}else if(first_type == "welfare"){
			second_type = $("#welfare-select").val();
		}
		var unit = $("#unit").val();
		var price = $("#price").val();
		var use_time = $("#user-time").val();
		var link = $("#link").val();
		var reason = $("#reason").val();
		var buy_type = $("#buy-type").val();
		var attachment = document.getElementById("attachment").files[0];
		<?php if(!empty($this->user) && $this->user->user_id == $admin_id): ?>
		var selection = $("input[name='single-selection']:checked").val();
		<?php endif; ?>

		// 验证数据
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		var unit_pattern = /^\d+([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		if(!name){
			showHint("提示信息","请输入名称");
			$("#name").focus();
		}else if(!unit_pattern.exec(unit)){
			showHint("提示信息","数量单位输入格式错误");
			$("#unit").focus();
		}else if(!d_pattern.exec(price)){
			showHint("提示信息","预计单价输入格式错误");
			$("#price").focus();
		}else if(parseFloat(price) == 0){
			showHint("提示信息","预计单价不能为0");
			$("#price").focus();
		}else if(use_time != "" && !date_pattern.exec(use_time)){
			showHint("提示信息","使用时间输入格式错误");
			$("#use_time").focus();
		}else if(!reason){
			showHint("提示信息","请输入申请原因");
			$("#reason").focus();
		}else{
			// 将数据填入表单中
			data.append('category',first_type);
			data.append('type',second_type);
			data.append('name',name);
			data.append('quantity',unit);
			data.append('price',price);
			data.append('url',link);
			data.append('reason',reason);
			data.append('buy_way',buy_type);
			data.append('use_time',use_time);
			<?php if(!empty($this->user) && $this->user->user_id == $admin_id): ?>
			data.append('tag', selection);
			<?php else: ?>
			data.append('tag', '0');
			<?php endif; ?>

			// 验证附件
			var file_f_tag = false;
			if(typeof(attachment) != "undefined"){
				var type = attachment.type;
				var file_name = attachment.name;
				var file_size = attachment.size;
				file_name = file_name.toLowerCase();
				if(type.indexOf('officedocument') < 0 && type.indexOf('pdf') < 0 && type.indexOf('image') < 0 && type.indexOf('word') < 0 && type.indexOf('powerpoint') < 0 && type.indexOf('excel') < 0 && file_name.indexOf('.rar') < 0 && file_name.indexOf('.7z') < 0 && file_name.indexOf('.zip') < 0 && file_name.indexOf('.bz2') < 0 && file_name.indexOf('.kz') < 0 && file_name.indexOf('.tar') < 0){
					showHint("提示信息","请上传office文件、图片、pdf或压缩包");
					file_f_tag = true;
				}else if(file_size >= <?php $upload_max = ini_get('upload_max_size'); echo empty($upload_max) ? '10' : (int)$upload_max; ?>*1024*1024){
					showHint("提示信息","上传附件太大，请重新选择");
					file_f_tag = true;
				}else{
					data.append("file", attachment);
					file_f_tag = false;
				}
			}

			if(!file_f_tag){
				// 判断是否已经提示
				if(single_remind_tag){
					sendSingleSubscribe();
				}else{
					// 二次提醒
					showSubscribeRemind(); // 显示申请提示
					single_remind_tag = true;
				}
			}
		}
	}

	// 发送单个申请
	function sendSingleSubscribe(){
		var FileController = "/ajax/singleGoodsApply"; // 接口地址

		// XMLHttpRequest对象
		var xhr = new XMLHttpRequest();
		xhr.open("post", FileController, true);
		xhr.send(data);

		xhr.onreadystatechange = function(){
			if(xhr.readyState==4 && xhr.status==200){
				var response = xhr.responseText;
				// 从xml字符串转换成xml对象
				try{
					domParser = new DOMParser();
					xmlDoc = domParser.parseFromString(response, 'text/xml');
					var code = xmlDoc.getElementsByTagName("code")[0].childNodes[0].nodeValue;
					var id = xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue;
					// 回调提示
					if(code == 0){
						showHint("提示信息","提交申请成功，请等待审批结果！");
						setTimeout(function(){location.href = "/user/subscribeDetail/id/"+id},1200);
					}else if(code == -1){
						showHint("提示信息","提交申请失败，请重试！");
					}else if(code == -2){
						showHint("提示信息","参数错误！");
					}else if(code == -4){
						showHint("提示信息","附件不能超过10M");
					}else if(code == -5){
						showHint("提示信息","附件上传失败");
					}else{
						showHint("提示信息","你没有权限执行此操作！");
					}
				}catch(e){
					showHint("提示信息","解析返回信息失败，请重试");
				}
			}
		}

		single_remind_tag = false;
	}

	// 类型选择
	function typeSelect(){
		var type = $("#type-select").val();
		if(type == "office"){
			$("#office-select").removeClass("hidden");
			$("#welfare-select").addClass("hidden");
		}else if(type == "welfare"){
			$("#office-select").addClass("hidden");
			$("#welfare-select").removeClass("hidden");
		}else{
			$("#office-select").addClass("hidden");
			$("#welfare-select").addClass("hidden");
		}

		// 显示年度预算
		if(typeof(d_budget_arr[type]) != "undefined"){
			$("#rest-budget-span").text(d_budget_arr[type]);
			<?php if(!empty($this->user) && $this->user->user_id == $admin_id): ?>
			if(parseFloat(d_budget_arr[type]) < 0){
				$("#single-select-tr").addClass("hidden");
				$("input[name='single-selection']").each(function(){
					if($(this).val() == "1"){
						this.checked = true;
					}else{
						this.checked = false;
					}
				});
			}else{
				$("#single-select-tr").removeClass("hidden");
			}
			<?php endif; ?>
			$("#rest-budget-remind").addClass("hidden");
			$("#rest-budget-label").removeClass("hidden");
			$("#send-btn").removeClass("disabled");
		}else{
			<?php if(!empty($this->user) && $this->user->user_id == $admin_id): ?>
			$("#single-select-tr").addClass("hidden");
			$("input[name='single-selection']").each(function(){
					if($(this).val() == "1"){
						this.checked = true;
					}else{
						this.checked = false;
					}
				});
			<?php endif; ?>
			$("#rest-budget-remind").removeClass("hidden");
			$("#rest-budget-label").addClass("hidden");
			$("#send-btn").addClass("disabled");
		}
	}

/*-------------------------------------------------------多个费用申请----------------------------------------------------------------------*/

	// 批量申购的类型选择
	function anyTypeSelect(){
		// debugger;
		$("tr.new-subscribe-tr").each(function(){
			$(this).find("select.type-select").each(function(){
				var obj = this;
				var type = $(obj).val();
				if(type == "office"){
					$(obj).next().removeClass("hidden");
					$(obj).next().next().addClass("hidden");
				}else if(type == "welfare"){
					$(obj).next().addClass("hidden");
					$(obj).next().next().removeClass("hidden");
				}else{
					$(obj).next().addClass("hidden");
					$(obj).next().next().addClass("hidden");
				}

				// 显示年度预算
				if(typeof(d_budget_arr[type]) != "undefined"){
					$(obj).parent().find("label.any-rest-budget-label").removeClass("hidden");
					$(obj).parent().find("span.any-rest-budget-span").text(d_budget_arr[type]);
					$(obj).parent().find("label.any-rest-budget-remind").addClass("hidden");
					$("#send-any-btn").removeClass("disabled");
				}else{
					$(obj).parent().find("label.any-rest-budget-label").addClass("hidden");
					$(obj).parent().find("label.any-rest-budget-remind").removeClass("hidden");
					$("#send-any-btn").addClass("disabled");
				}
			});
		});
		
	}

	// 批量申购
	var data_arr = new Array();
	var any_remind_tag = false;  // 多个申购的提示标记
	function sendAnySubscribe(){
		data_arr = new Array();
		var f_tag = false; // 错误标记

		// 遍历每一行申购
		$("tr.subscribe-tr").each(function(){
			// 获取数据
			var first_type = $(this).find("select.type-select").val();
			var second_type = "";
			if(first_type == "office"){
				second_type = $(this).find("select.office-select-any").val();
			}else if(first_type == "welfare"){
				second_type = $(this).find("select.welfare-select-any").val();
			}
			var name = $(this).find("input.name-input").val();
			var unit = $(this).find("input.unit-input").val();
			var price = $(this).find("input.price-input").val();
			var use_time = $(this).find("input.time-input").val();
			var link = $(this).find("input.link-input").val();
			var reason = $(this).find("input.reason-input").val();
			var buy_type = $(this).find("select.buy-type-input").val();

			// 验证数据
			var d_pattern = /^\d+(\.\d{1,2})?$/;
			var unit_pattern = /^\d+([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/;
			var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
			if(!name){
				showHint("提示信息","请输入名称");
				$(this).find("input.name-input").focus();
				f_tag = true;
				return false;
			}else if(!unit_pattern.exec(unit)){
				showHint("提示信息","数量单位输入格式错误");
				$(this).find("input.unit-input").focus();
				f_tag = true;
				return false;
			}else if(!d_pattern.exec(price)){
				showHint("提示信息","预计单价输入格式错误");
				$(this).find("input.price-input").focus();
				f_tag = true;
				return false;
			}else if(parseFloat(price) == 0){
				showHint("提示信息","预计单价不能为0");
				$(this).find("input.price-input").focus();
				f_tag = true;
				return false;
			}else if(use_time != "" && !date_pattern.exec(use_time)){
				showHint("提示信息","使用时间输入格式错误");
				$(this).find("input.time-input").focus();
				f_tag = true;
				return false;
			}else if(!reason){
				showHint("提示信息","请输入申请原因");
				$(this).find("input.reason-input").focus();
				f_tag = true;
				return false;
			}else{
				// 将数据填入数组中
				data_arr.push({'category':first_type, 'type':second_type, 'name':name, 'quantity':unit, 'price':price, 'url':link, 'reason':reason, 'buy_way':buy_type, 'use_time':use_time});
			}
		});

		// 判断是否有错误
		if(!f_tag){
			if(any_remind_tag){
				sendSubscribe();
			}else{
				showSubscribeRemind(); // 显示申请提示
				any_remind_tag = true;
			}
		}
	}

	// 发送申请
	function sendSubscribe(){
		<?php if(!empty($this->user) && $this->user->user_id == $admin_id): ?>
		var selection = $("input[name='any-selection']:checked").val();
		<?php else: ?>
		var selection = '0';
		<?php endif; ?>
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/goodsApply',
			data:{'data':data_arr, 'tag':selection},
			success:function(result){
				if(result.code == 0){
					showHint("提示信息","提交申请成功，请等待审批结果！");
					setTimeout(function(){location.href = "/user/subscribeDetail/id/"+result.id},1200);
				}else if(result.code == -1){
					showHint("提示信息","提交申请失败，请重试！");
				}else if(result.code == -2){
					showHint("提示信息","参数错误！");
				}else{
					showHint("提示信息","你没有权限执行此操作！");
				}
			}
		});

		any_remind_tag = false;
	}

	String.prototype.trim=function() {
    return this.replace(/(^\s*)|(\s*$)/g,'');
	}

	// 批量申购-新增一行
	var current_line = 1;
	function newLine(obj){
		// debugger;
		current_line ++ ;
		line_html2 = line_html2.trim();
		var id = line_html2.split('</td>')[0].split('<td>')[1];
		var len = id.length;
		var beforeHtml = "<td>"
		var afterHtml = line_html2.substring(4+len);
		id++;
		line_html2 = beforeHtml + id + afterHtml;
		$(obj).parent().parent().before("<tr class='new-subscribe-tr'>"+line_html2+"</tr>");
		$(obj).parent().parent().prev().find("input.time-input").attr("id", "time-input-"+current_line).removeClass("hasDatepicker");
		$(obj).parent().parent().prev().find('input.time-input').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
		anyTypeSelect();
	}

	// 计算小计
	function totalCal(obj){
		var unit = $(obj).parent().parent().find("input.unit-input").val();
		var price = $(obj).parent().parent().find("input.price-input").val();
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		var unit_pattern = /^\d+([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/;
		if(d_pattern.exec(price) && unit_pattern.exec(unit)){
			var num = parseInt(unit);
			var total = accMultiply(num, parseFloat(price));
			$(obj).parent().parent().find("td.total-price-td").text(total+"元");
		}else{
			$(obj).parent().parent().find("td.total-price-td").text("0元");
		}

		// 计算小计
		summaryCal();
	}

	// 计算合计
	function summaryCal(){
		var summary = 0;
		$("tr.subscribe-tr").each(function(){
			var total = parseFloat($(this).find("td.total-price-td").text());
			summary = accAdd(summary, total);
		});
		$("#any-total-span").text(summary);
	}

	// 精确乘法
	function accMultiply(arg1,arg2){  
	    var m=0,s1=arg1.toString(),s2=arg2.toString();  
	    try{m+=s1.split(".")[1].length}catch(e){}  
	    try{m+=s2.split(".")[1].length}catch(e){}  
	    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);  
	}

	// 检测是否需要显示给总经理审批
	function selectCheck(){
		var open_tag = true;
		$("tr.subscribe-tr").each(function(){
			// var total = $(this).find("td.total-price-td").text();
			// total = parseFloat(total);
			var total = 0 ;
			var type = $(this).find("select.type-select").val();
			var budget = $(this).find("span.any-rest-budget-span").text();
			budget = parseFloat(budget);

			if(budget < 0){
				open_tag = false;
			}else{
				$("tr.subscribe-tr").each(function(){
					var tr_type = $(this).find("select.type-select").val();
					if(type == tr_type){
						total += parseFloat($(this).find("td.total-price-td").text());
					}
				});
				var rest = budget - total;
				if(rest < 0){
					open_tag = false;
				}
			}
		});

		if(open_tag){
			$("#any-select-tr").removeClass("hidden");
		}else{
			$("#any-select-tr").addClass("hidden");
			$("input[name='any-selection']").each(function(){
				if($(this).val() == "1"){
					this.checked = true;
				}else{
					this.checked = false;
				}
			});
		}
	}

/*-------------------------------------------------------页面初始化----------------------------------------------------------------------*/
	// 各类型部门预算
	var d_budget_arr = new Array();

	d_budget_arr['office'] = "<?php echo isset($budgets['office']) ? $budgets['office']: ''; ?>";
	d_budget_arr['welfare'] = "<?php echo isset($budgets['welfare']) ? $budgets['welfare']: ''; ?>";
	d_budget_arr['travel'] = "<?php echo isset($budgets['travel']) ? $budgets['travel']: ''; ?>";
	d_budget_arr['entertain'] = "<?php echo isset($budgets['entertain']) ? $budgets['entertain']: ''; ?>";
	d_budget_arr['hydropower'] = "<?php echo isset($budgets['hydropower']) ? $budgets['hydropower']: ''; ?>";
	d_budget_arr['intermediary'] = "<?php echo isset($budgets['intermediary']) ? $budgets['intermediary']: ''; ?>";
	d_budget_arr['rental'] = "<?php echo isset($budgets['rental']) ? $budgets['rental']: ''; ?>";
	d_budget_arr['property'] = "<?php echo isset($budgets['property']) ? $budgets['property']: ''; ?>";
	d_budget_arr['repair'] = "<?php echo isset($budgets['repair']) ? $budgets['repair']: ''; ?>";
	d_budget_arr['test'] = "<?php echo isset($budgets['test']) ? $budgets['test']: ''; ?>";
	d_budget_arr['outsourcing'] = "<?php echo isset($budgets['outsourcing']) ? $budgets['outsourcing']: ''; ?>";
	d_budget_arr['other'] = "<?php echo isset($budgets['other']) ? $budgets['other']: ''; ?>";

	// 页面初始化
	var line_html = "";
	var line_html2 = "";
	$(document).ready(function(){

		window.onhashchange = function(){
			// debugger;
			var hash = location.hash;

		if(hash == "#new"){
			$('#new-sub').addClass('hidden');
			var i = document.getElementById('add-subscribe-div');
			i.style.display = "block";
			$('#add-subscribe-div').removeClass('hidden');
		}else if(hash == "#applyRecord" || hash == "#reimburseRecord"){
			$('#add-subscribe-div').addClass('hidden');
			$('#reimburse-change-div').addClass('hidden');
			$('#new-sub').removeClass('hidden');
			$('#new-sub2').removeClass('hidden');
			var i = document.getElementById('new-sub');
			i.style.display = "block";
			i = document.getElementById('new-sub2');
			i.style.display = "block";
			if(hash == "#applyRecord"){
				$("#apply-switch-btn").click();
			}else if(hash == "#reimburseRecord"){
				$("#reimburse-switch-btn").click();
			}
		}
		}

		var hash = location.hash;
		
		if(hash == "#new"){
			$('#new-sub').addClass('hidden');
			$('#add-subscribe-div').removeClass('hidden');
		}else if(hash == "#applyRecord" || hash == "#reimburseRecord"){
			$('#add-subscribe-div').addClass('hidden');
			$('#new-sub').removeClass('hidden');
			if(hash == "#applyRecord"){
				$("#apply-switch-btn").click();
			}else if(hash == "#reimburseRecord"){
				$("#reimburse-switch-btn").click();
			}
		}
		// 获取用户的部门
		var department = '<?php echo $this->user->department->name ?>';
		console.log(department);
		// 获取所有的项目


		var selectType = '<?php echo $type ?>';
		if(selectType == ""){
			selectType = "all";
		}
		$('#reimburse-type-select').val(selectType);

		var goods_apply_list = <?php echo CJSON::encode($goods_apply_list) ?>;
		console.log(goods_apply_list);


		var page_tag = "<?php echo empty($page_tag) ?  'apply' : $page_tag;?>";
		// if(hash != "#reimburseRecord"){
			if(page_tag == "apply"){
				$("#apply-switch-btn").click();
				$("#subscribe-info-div").removeClass("hidden");
			}else{
				$("#reimburse-switch-btn").click();
				$("#subscribe-info-div").removeClass("hidden");
			}
		// }
		
		$("#new-subscribe-div").removeClass("hidden");
		// 日期控件初始化
		$('#user-time').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
		$("#subscribe-any-table").find("input.time-input").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
      	$.datepicker.setDefaults($.datepicker.regional['zh-CN']);

      	// 判断是否有预算
      	typeSelect();
      	$("tr.subscribe-tr").find("select.type-select").each(function(){
      		anyTypeSelect(this);
      	});

      	line_html = $("tr.subscribe-tr").html();

      	// 为申请记录分页添加tag
      	$("#yw0").find("a").each(function(){
      		if($(this).attr("href").indexOf("?") < 0){
      			var href_str = "";
      			href_str = $(this).attr("href")+"?page_tag=apply";
      		}else{
      			href_str = $(this).attr("href")+"&page_tag=apply";
      		}
      		$(this).attr("href",href_str);
      	});
      	// 为报销记录分页添加tag
      	$("#yw1").find("a").each(function(){
      		
      		if($(this).attr("href").indexOf("?") < 0){
      			var href_str = "";
      			href_str = $(this).attr("href")+"?page_tag=reimburse";
      		}else{
      			href_str = $(this).attr("href")+"&page_tag=reimburse";
      		}
      		$(this).attr("href",href_str);
      	});

      	// reimburseTypeChange();
	});

	// 切换到费用申请
	function switchToSubscribe(obj){
		location.hash = "#applyRecord";
		<?php if(!empty($page_tag) && $page_tag == "reimburse"): ?>
		location.href = "/user/subscribe#applyRecord";
		<?php else: ?>
		$(obj).parent().addClass("active");
		$(obj).parent().next().removeClass("active");
		$("#subscribe-table").removeClass("hidden");
		$("#reimburse-div").addClass("hidden");
		$("#subscribe-history-div").removeClass("hidden");
		$("#reimburse-history-div").addClass("hidden");
		<?php endif; ?>
	}

	// 切换到费用报销
	function switchToReimburse(obj){
		location.hash = "#reimburseRecord";
		$(obj).parent().addClass("active");
		$(obj).parent().prev().removeClass("active");
		$("#subscribe-table").addClass("hidden");
		$("#reimburse-div").removeClass("hidden");
		$("#subscribe-history-div").addClass("hidden");
		$("#reimburse-history-div").removeClass("hidden");
		<?php if(!empty($reimburse)): ?>
		loadReimburseType();
		reimburseTypeChange();
		<?php endif; ?>
		
	}

	// 显示批量申购
	function showSubscribeAny(){
		$("#subscribe-info-div").fadeOut(400,function(){
			$("#subscribe-any-div").slideDown(400);
		});
	}

	function showSubscribeAdd(){
		location.hash = "#new";
		var feeTplName = setDefaultFeeTpl();
		if(feeTplName != ""){
			$('.new-subscribe-tr .input-fee-div').find("option:contains('"+feeTplName+"')").attr("selected",true);
		}
		showNewDiv($('.new-subscribe-tr .input-fee-div'));
		line_html2 = $('.new-subscribe-tr').html();
		$("#new-sub").fadeOut(400,function(){
			$("#add-subscribe-div").removeClass("hidden");
			$("#add-subscribe-div").slideDown(400);
		});
	}

	// 显示申购信息页面
	function showSubscribeInfo(){
		$("#subscribe-any-div").fadeOut(400,function(){
			$("#subscribe-info-div").slideDown(400);
			var first_tag = false;
			$("#subscribe-any-table").find("tr.subscribe-tr").each(function(){
				if(!first_tag){
					first_tag = true;
				}else{
					$(this).remove();
				}
			});
		});
	}

/*-----------------------------------------------显示二次提示-----------------------------------------------*/

	// 显示申请提示
	function showSubscribeRemind(){
		var ySet = (window.innerHeight - $("#subscribe-remind-div").height())/3;
		var xSet = (window.innerWidth - $("#subscribe-remind-div").width())/2;
		$("#subscribe-remind-div").css("top",ySet);
		$("#subscribe-remind-div").css("left",xSet);
		$('#subscribe-remind-div').modal({show:true});
	}

	// 显示申请提示
	function showReimburseRemind(){
		var ySet = (window.innerHeight - $("#reimburse-remind-div").height())/3;
		var xSet = (window.innerWidth - $("#reimburse-remind-div").width())/2;
		$("#reimburse-remind-div").css("top",ySet);
		$("#reimburse-remind-div").css("left",xSet);
		$('#reimburse-remind-div').modal({show:true});
	}

	// 数据一旦更改就要显示提示
	function subscribeDataChange(){
		single_remind_tag = false;
		any_remind_tag = false;
	}
	function reimburseDataChange(){
		reimburse_remind_tag = false;
	}

</script>
<script type="text/javascript">
	function backToSub(){
		location.hash = "applyRecord";
		$('#add-subscribe-div').fadeOut(400,function(){
			$("#new-sub").removeClass('hidden');
			$("#new-sub").slideDown();
		});
	}

	function sendSubApply(){
		var data_arr = new Array();
		var f_tag = false; // 错误标记
		var successNum = 0;
        var fee_div_type = "";
        var fee_div_value="";
		// 遍历每一行申购
		var trLength = $("tr.new-subscribe-tr").length;
		$("tr.new-subscribe-tr").each(function(){
			// console.log($(this));
			// debugger;
			// 获取数据
			var first_type = $(this).find("select.type-select").val();
			var second_type = "";
			if(first_type == "office"){
				second_type = $(this).find("select.office-select-any").val();
			}else if(first_type == "welfare"){
				second_type = $(this).find("select.welfare-select-any").val();
			}

			var name = $(this).find("input.input-name").val();
			var unit = "1次";
			var price = $(this).find("input.price-input").val();
			var reason = $(this).find("input.reason-input").val();
            try {
                var fee_div_p = (new Function("return " + $(this).find("select.input-fee-div").val() ))();
                var fee_div_name = $(this).find("select.input-fee-div").find("option:selected").text();
                 //判断分摊类型是否为同一种类型
                var fee_div_p_count = 0;
                $.each(fee_div_p, function(){ 
                    fee_div_p_count++;
                    console.log(12);
                });
                if(fee_div_p_count==1) {
                    if(fee_div_value=="") {
                        fee_div_value = fee_div_p;
                    }
                    if( fee_div_type=="" ) {
                        fee_div_type = 1;
                    }
                    else if( JSON.stringify(fee_div_value) != JSON.stringify(fee_div_p) || (fee_div_type != fee_div_p_count) ){
                        showHint("提示信息","多种分摊方式的费用申请单请分开提交");
                        // console.log(JSON.stringify(fee_div_value));
                        // console.log(JSON.stringify(fee_div_p));
                        return false;
                    }
                }
                else if( fee_div_type == 1 ) {
                    showHint("提示信息","多种分摊方式的费用申请单请分开提交");
                    return false;
                }
                console.log([fee_div_type, fee_div_value]);
            }
            catch(err) {
                console.log(err);
                showHint("提示信息","费用分摊方式选择错误");
                $(this).find("select.input-fee-div").focus();
                return false;
            }
			// console.log([first_type,second_type,name,unit,price,reason]);
			// 验证数据
			var d_pattern = /^\d+(\.\d{1,2})?$/;
			var unit_pattern = /^\d+([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/;
			var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
			if(!name){
				showHint("提示信息","请输入名称");
				$(this).find("input.input-name").focus();
				f_tag = true;
				return false;
			}else if(!d_pattern.exec(price)){
				showHint("提示信息","请输入正确的价格！");
				$(this).find("input.price-input").focus();
				f_tag = true;
				return false;
			}else if(parseFloat(price) == 0){
				showHint("提示信息","预计单价不能为0");
				$(this).find("input.price-input").focus();
				f_tag = true;
				return false;
			}
			else if(!reason){
				showHint("提示信息","请输入申请说明");
				$(this).find("input.reason-input").focus();
				f_tag = true;
				return false;
			}else{
				var link = "";
				var buy_type = "";
				var use_time = "";
				// 将数据填入数组中
				data_arr.push({'category':first_type, 'type':second_type, 'name':name, 'quantity':unit, 'price':price, 'url':link, 'reason':reason, 'buy_way':buy_type, 'use_time':use_time, 'fee_div_p':fee_div_p, 'fee_div_name':fee_div_name});
			}
		// });

		    // 判断是否有错误
    		successNum++;
    		if(f_tag){
    			showSubscribeRemind(); // 显示申请提示
    		}
            else if(data_arr.length == trLength){
    			var selection = 0;
    			$.ajax({
    			type:'post',
    			dataType:'json',
    			url:'/ajax/goodsApply',
    			data:{'data':data_arr, 'tag':selection},
    			success:function(result){
    				// debugger;
    				console.log(result.code);
    				if(result.code == 0){
    					if(trLength == successNum){
    						showHint("提示信息","提交申请成功，请等待审批结果！");
    						setTimeout(function(){location.href = "/user/subscribe"},1200);
    					}
    				}else if(result.code == -1){
    					showHint("提示信息","第"+(successNum+1)+"项提交申请失败，请重试！");
    				}else if(result.code == -2){
    					showHint("提示信息","第"+(successNum+1)+"项参数错误！");
    				}else{
    					showHint("提示信息","你没有权限执行此操作！");
    				}
    			}
    			});
    		}
    	});
	}

	function changeSubApply(){
		// debugger;
		var data_arr = new Array();
		var f_tag = false; // 错误标记
		
		
		// 获取数据
		var first_type = $('.change-type .type-select').val();
		var second_type = "";
		if(first_type == "office"){
			second_type = $('.change-type').find("select.office-select-any").val();
		}else if(first_type == "welfare"){
			second_type = $('.change-type').find("select.welfare-select-any").val();
		}
		var name = $('.change-name input').val();
		var unit = "1次";
		var price = $('.change-price input').val();
		var reason = $('.change-state input').val();
		// console.log([first_type,second_type,name,unit,price,reason]);
		// 验证数据
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		var unit_pattern = /^\d+([\u4E00-\uFA29]|[\uE7C7-\uE7F3])+$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		
		var type1 = first_type;

		var details_id = $('.change-id').text();
		var fee_div_name = $('.subscribe-tr .input-fee-div').find("option:selected").text();
		var fee_div_p_str = $('.subscribe-tr .input-fee-div').val();
		fee_div_p = JSON.parse(fee_div_p_str);
		switch(type1){
			case "office":
				type1 = "办公费";
				break;
			case "travel":
				type1 = "差旅费";
				break;
			case "welfare":
				type1 = "福利费";
				break;
			case "test":
				type1 = "测试费";
				break;
			case "outsourcing":
				type1 = "外包费";
				break;
			case "entertain":
				type1 = "业务招待费";
				break;
			case "hydropower":
				type1 = "水电费";
				break;
			case "intermediary":
				type1 = "中介费";
				break;
			case "rental":
				type1 = "租赁费";
				break;
			case "property":
				type1 = "物管费";
				break;
			case "repair":
				type1 = "修缮费";
				break;
		}
		if(type1 == storeArray['type1'] && second_type == storeArray['type2'] && name == storeArray['name'] && price == storeArray['price'] && reason == storeArray['state']){
			showHint("提示信息","请修改申请单后再提交！");
			f_tag = true;
			return false;
		}else if(!name){
			showHint("提示信息","请输入名称");
			$('.change-name input').focus();
			f_tag = true;
			return false;
		}else if(!d_pattern.exec(price)){
			showHint("提示信息","价格格式错误");
			$('.change-price input').focus();
			f_tag = true;
			return false;
		}else if(parseFloat(price) == 0){
			showHint("提示信息","价格不能为0");
			$('.change-price input').focus();
			f_tag = true;
			return false;
		}
		else if(!reason){
			showHint("提示信息","请输入申请说明");
			$('.change-state input').focus();
			f_tag = true;
			return false;
		}else{
			var link = "";
			var buy_type = "";
			var use_time = "";
			// 将数据填入数组中
			data_arr.push({'category':first_type, 'type':second_type, 'name':name, 'quantity':unit, 'price':price, 'url':link, 'reason':reason, 'buy_way':buy_type,'use_time':use_time,'fee_div_name':fee_div_name,'fee_div_p':fee_div_p});
		}
		// // 判断是否有错误
		if(f_tag){
				showSubscribeRemind(); // 显示申请提示
		}else{
			var selection = 0;
			console.log([data_arr,selection]);
			$.ajax({
				type:'post',
				dataType:'json',
				url:'/ajax/goodsApply',
				data:{'data':data_arr, 'tag':selection},
				success:function(result){
					console.log([data_arr,selection,result.code]);
					if(result.code == 0){
						$('#change-div').addClass('hidden');
						showHint("提示信息","修改申请成功，请等待审批结果！");
						setTimeout(function(){location.reload();},1200);
					}else if(result.code == -1){
						showHint("提示信息","修改申请失败，请重试！");
					}else if(result.code == -2){
						showHint("提示信息","参数错误！");
					}else{
						showHint("提示信息","你没有权限执行此操作！");
					}
				}
			});
		}
	}

	var storeArray = {};
	function changeApply(row){
		
		var id = row.children[1].innerText;
		var apply_id = row.children[0].innerText;
		$('.change-id').text(id);
		$('.change-applyid').text(apply_id);
		var type = row.children[3].innerText;
		var type2 = row.children[10].innerText.trim();

		var fee_div_name = row.children[11].innerText.trim();
		var fee_div_p = row.children[12].innerText.trim();
		console.log([fee_div_name,fee_div_p]);
		switch(type)
		{
			case "办公费":
				type = "office";
				break;
			case "差旅费":
				type = "travel";
				break;
			case "福利费":
				type = "welfare";
				break;
			case "测试费":
				type = "test";
				break;
			case "外包费":
				type = "outsourcing";
				break;
			case "业务招待费":
				type = "entertain";
				break;
			case "水电费":
				type = "hydropower";
				break;
			case "租赁费":
				type = "rental";
				break;
			case "中介费":
				type = "intermediary";
				break;
			case "物管费":
				type = "property";
				break;
			case "修缮费":
				type = "repair";
				break;
		}
		$('.change-type select:first').val(type);
		if(type == "office"){
			$('.change-type select:first').next().removeClass('hidden');
			$('.change-type select:first').next().val(type2);
		}else if(type == "welfare"){
			$('.change-type select:last').removeClass('hidden');
			$('.change-type select:last').val(type2);
		}else{
			$('.change-type select:first').next().addClass('hidden');
			$('.change-type select:last').addClass('hidden');
		}
		$('.change-name input').val(row.children[4].innerText);
		$('.change-price input').val(row.children[7].innerText);
		$('.change-state input').val(row.children[6].innerText);
		$('.subscribe-tr .input-fee-div').val(fee_div_p);

		showNewDiv($('.subscribe-tr .input-fee-div'));

		
		var ySet = (window.innerHeight - $("#change-div").height())/3;
		var xSet = (window.innerWidth - $("#change-div").width())/2;
		$("#change-div").css("top",ySet);
		$("#change-div").css("left",xSet);
		$('#change-div').modal({show:true});

		storeArray['type1'] = row.children[3].innerText;
		if(type == "office"){
			storeArray['type2'] = $('.change-type select:first').next().val();
		}else if(type == "welfare"){
			storeArray['type2'] = $('.change-type select:last').val();
		}else{
			storeArray['type2'] = "";
		}
		storeArray['name'] = row.children[4].innerText;
		storeArray['price'] = row.children[7].innerText;
		storeArray['state'] = row.children[6].innerText;
		storeArray['fee_div_name'] = row.children[11].innerText.trim();
		storeArray['fee_div_p'] = row.children[12].innerText.trim();
	}

	function changeApplyCommit(){
		var type1 = $('.change-type .type-select').val();
		var second_type = "";
		if(type1 == "office"){
			second_type = $('.change-type').find("select.office-select-any").val();
		}else if(type1 == "welfare"){
			second_type = $('.change-type').find("select.welfare-select-any").val();
		}
		var name = $('.change-name input').val();
		var price = $('.change-price input').val();
		var reason = $('.change-state input').val();
		var id = $('.change-id').text();

		var fee_div_name = $('.subscribe-tr .input-fee-div').find("option:selected").text();
		var fee_div_p = $('.subscribe-tr .input-fee-div').val();
		
		switch(storeArray['type1']){
			case "办公费":
				storeArray['type1'] = "office";
				break;
			case "差旅费":
				storeArray['type1'] = "travel";
				break;
			case "福利费":
				storeArray['type1'] = "welfare";
				break;
			case "测试费":
				storeArray['type1'] = "test";
				break;
			case "外包费":
				storeArray['type1'] = "outsourcing";
				break;
			case "业务招待费":
				storeArray['type1'] = "entertain";
				break;
			case "水电费":
				storeArray['type1'] = "hydropower";
				break;
			case "中介费":
				storeArray['type1'] = "intermediary";
				break;
			case "租赁费":
				storeArray['type1'] = "rental";
				break;
			case "物管费":
				storeArray['type1'] = "property";
				break;
			case "修缮费":
				storeArray['type1'] = "repair";
				break;
		}
		if(parseFloat(price) <= 0){
			showHint("提示信息","总价输入错误，请重新输入！");
			$('.price-input').focus();
			return false;
		}

		if(price < storeArray['price'] && type1 == storeArray['type1'] && second_type == storeArray['type2'] && name == storeArray['name'] && reason == storeArray['state'] && fee_div_name == storeArray['fee_div_name'] && fee_div_p == storeArray['fee_div_p']){
			$.ajax({
    			type:'post',
    			dataType:'json',
    			url:'/ajax/EditGoodsApply',
    			data:{'id':id, 'category':type1,'type':second_type,'price':price,'reason':reason,'name':name,'fee_div_name':fee_div_name,'fee_div_p':fee_div_p},
    			success:function(result){
    				// console.log([id,type1,second_type,price,reason,name,result.code]);
    				if(result.code == 0){
    						showHint("提示信息","修改成功！");
    						setTimeout(function(){location.href = "/user/subscribe"},1200);
    				// }else if(result.code == -1){
    				// 	showHint("提示信息","失败，请重试！");
    				}else if(result.code == -2){
    					showHint("提示信息","参数错误！");
    				}else{
    					showHint("提示信息","你没有权限执行此操作！");
    				}
    			}
    		});
		}else{
			// debugger;
			var id = $('.change-id').text();
			var reason = "修改申请";
			$.ajax({
				type:'post',
				dataType:'json',
				url:'/ajax/cancelGoodsApply',
				data:{'id':id, 'reason':reason},
				success:function(result){
					console.log([id,reason,result.code]);
					if(result.code == 0){
						changeSubApply();
					}else if(result.code == -99){
						showHint("提示信息","无权限取消申请单！");
					}else if(result.code == -2){
						showHint("提示信息","参数错误！");
					}else if(result.code == -3){
						showHint("提示信息","找不到该申请单！");
					}else if(result.code == -4){
						showHint("提示信息","此申请单已取消！");
						setTimeout(function(){location.reload();},1200);
					}else if(result.code == -5){
						showHint("提示信息","此申请单已报销！");
						setTimeout(function(){location.reload();},1200);
					}else{
						showHint("提示信息","未知错误！");
					}
				}
			});
		}
		
	}

	function cancelApplyReason(row){
		var ySet = (window.innerHeight - $("#cancel-div").height())/3;
		var xSet = (window.innerWidth - $("#cancel-div").width())/2;
		$("#cancel-div").css("top",ySet);
		$("#cancel-div").css("left",xSet);
		$('#cancel-div').modal({show:true});
		var id = row.children[1].innerText;
		$('.cancel-input').val(id);
		// console.log(row.children[1].innerText);
		// 
	}

	function cancelApply(){
		var id = $('.cancel-input').val();
		var reason = $('.cancel-textarea').val();

		if($('.cancel-textarea').val() == ""){
			showHint("提示信息","请输入取消原因！");
			$('.cancel-textarea').focus();
		}else{
			$('.cancel-textarea').val("");
			$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/cancelGoodsApply',
			data:{'id':id, 'reason':reason},
			success:function(result){
				// console.log([id,reason]);
				if(result.code == 0){
					$('#cancel-div').css('display','none');
					showHint("提示信息","取消费用申请成功！");
					setTimeout(function(){location.reload();},1200);
				}else if(result.code == -99){
					showHint("提示信息","无权限取消申请单！");
				}else if(result.code == -2){
					showHint("提示信息","参数错误！");
				}else if(result.code == -3){
					showHint("提示信息","找不到该申请单！");
				}else if(result.code == -4){
					showHint("提示信息","此申请单已取消！");
					setTimeout(function(){location.reload();},1200);
				}else if(result.code == -5){
					showHint("提示信息","此申请单已报销！");
					setTimeout(function(){location.reload();},1200);
				}else{
					showHint("提示信息","未知错误！");
				}
			}
		});
		}
	}
	
	var test1 = <?php echo CJSON::encode($goods_apply_list) ?>;

	function toBatch(row){
		$(row).children()[2].children[0].checked = true;
		newShowReimburse3($('#tbody'));
	}
	function newShowReimburse3(row){
		//需要提交的信息
		var type = "";
		var bank = "";
		var account = "";
		var name = "";
		var owe_money_initial = "";
		var bill_num = "";
		var reimburse_detail_arr = new Array();
		// var total = 0;

		var allType = new Array();
		allType = ["office","travel","welfare","test","outsourcing","entertain","hydropower","intermediary","property","repair","rental"];
		allTypeCN = ["办公费","差旅费","福利费","测试费","外包费","业务招待费","水电费","中介费","物管费","修缮费","租赁费"];
		var typeNow_Arr = new Array();
		var arr = {};
		$("tr.reimburse-detail-tr").each(function(){
			if(this.children[2].children[0].checked == true){
				
				var typeNow = this.children[3].innerText;
				if($.inArray(typeNow,typeNow_Arr) == -1){
					typeNow_Arr.push(typeNow);
					arr[typeNow] = {};
					arr[typeNow].total = 0;
					arr[typeNow].reimburse_detail_arr = new Array();
				}
				reimburse_type = this.children[3].innerText; // 报销类型
				find_tag = false; 
				apply_detail_id = this.children[1].innerText;
				apply_id = this.children[0].innerText;
				content = this.children[4].innerText;
				have_bill = "yes";
				// debugger;
				// 金额的获取
				amount = "";
				amount = this.children[7].innerText;
				// 计算总计
				arr[typeNow].total = accAdd(arr[typeNow].total, parseFloat(amount));
				// 填充到数组里面
				arr[typeNow].reimburse_detail_arr.push({'have_receipt':have_bill, 'amount':amount, 'apply_id':apply_id, 'apply_detail_id':apply_detail_id, 'content':content});
			}
		});
		if (typeNow_Arr == "") {
			showHint("提示信息","请勾选申请记录以生成报销单！");
		};
		var successNum = 0;
		for(var i=0; i<typeNow_Arr.length;i++){
			//提交报销单接口
			$.ajax({
				type:'post',
				dataType:'json',
				url:'/ajax/reimburse',
				data:{'way':"", 'bank_info':"", 'bank_code':"", 'payee':"", 'borrow_amount':"", 'receipt_num':"", 'details':arr[typeNow_Arr[i]].reimburse_detail_arr},
				success:function(result){
					if(result.code == 0){
						successNum++;
						if(successNum == typeNow_Arr.length){
							showHint("提示信息","提交报销单成功！");
							setTimeout(function(){location.href="/user/subscribe/page_tag/reimburse"},1200);
						}
					}else if(result.code == -1){
						showHint("提示信息","报销单"+(successNum+1)+"提交失败，请重试！");
					}else if(result.code == -2){
						showHint("提示信息","报销单"+(successNum+1)+"参数错误！");
					}else{
						// console.log(result.code);
						showHint("提示信息","你没有权限执行此操作！");
					}
				}
			});
		}
	}

	// 提交报销单
	var reimburse_remind_tag = false;
	function showSendReimburse2(){
		// 获取数据
		var bill_num = $("#reimburse-bill-num-input").val();
		var type = "";
		var bank = "";
		var account = "";
		var name = "";
		var owe_money_initial = "";
		if(document.getElementById("owe-checkbox").checked){
			type = "borrow";
			owe_money_initial = $("#inital-owe-money-input").val();
		}
		if(document.getElementById("transform-checkbox").checked){
			type = "transfer";
			bank = $("#bank-input").val();
			account = $("#account-input").val();
			name = $("#account-name-input").val();
		}

		// 验证数据
		var d_pattern = /^\d+$/;
		var money_pattern = /^\d+(\.\d{1,2})?$/;
		if(type == ""){
			showHint("提示信息","请选择付款方式");
		}else if(bill_num == ""){
			showHint("提示信息", "请输入单据张数");
			$("#reimburse-bill-num-input").focus();
		}else if(!d_pattern.exec(bill_num)){
			showHint("提示信息", "单据张数输入格式错误");
			$("#reimburse-bill-num-input").focus();
		}else if(type == "borrow"  && !money_pattern.exec(owe_money_initial)){
			showHint("提示信息", "原借款金额输入格式错误");
			$("#inital-owe-money-input").focus();
		}else if(type == "transfer" && bank == ""){
			showHint("提示信息", "请输入开户行");
			$("#bank-input").focus();
		}else if(type == "transfer" && account == ""){
			showHint("提示信息", "请输入帐号");
			$("#account-input").focus();
		}else if(type == "transfer" && !d_pattern.exec(account)){
			showHint("提示信息", "帐号输入格式错误");
			$("#account-input").focus();
		}else if(type == "transfer" && name == ""){
			showHint("提示信息", "请输入收款人姓名");
			$("#account-name-input").focus();
		}else{
			$.ajax({
				type:'post',
				dataType:'json',
				url:'/ajax/reimburse',
				data:{'way':type, 'bank_info':bank, 'bank_code':account, 'payee':name, 'borrow_amount':owe_money_initial, 'receipt_num':bill_num, 'details':reimburse_detail_arr},
				success:function(result){
					if(result.code == 0){
						showHint("提示信息","提交报销单成功！");
						setTimeout(function(){location.href="/user/subscribe/page_tag/reimburse"},1200);
					}else if(result.code == -1){
						showHint("提示信息","提交报销单失败，请重试！");
					}else if(result.code == -2){
						showHint("提示信息","参数错误！");
					}else{
						// console.log(result.code);
						showHint("提示信息","你没有权限执行此操作！");
					}
				}
			});

			reimburse_remind_tag = false;
		}
	}
	// 查询功能
    function searchGoodsApply() {
        var condition_input = $("#search-condition").val();
        var zhToen = {
                '办公费':'office', '福利费':'welfare', '差旅费':'travel', '修缮费':'repair','其他':'other',
                '业务招待费':'entertain', '水电费':'hydropower', '中介费':'intermediary',
                '租赁费':'rental', '测试费':'test', '外包费':'outsourcing', '物管费':'property',
            };
        if(zhToen[condition_input] != null) {
            condition_input = zhToen[condition_input];
        }
        location.href = '/user/subscribe?search='+ condition_input;
    }
    // 显示报销单提交页面
    function showReimburseChange(row){
    	location.hash = "#reimburseList";
    	var id = row.children[0].innerText;
    	$('#reimburse-change-id').text(id);
    	$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/getReimburseList',
			data:{'id':id},
			success:function(data){
				console.log(data['data']);
				if(data.code == 0){
					var i = 1;
					$('#reimburse-change-tbody').children().remove();
					for(var x in data['data']){
						var category = data['data'][x]['category'];
						if(category == "office"){
							categoryCN = "办公费";
						}else if(category == "travel"){
							categoryCN = "差旅费";
						}else if(category == "welfare"){
							categoryCN = "福利费";
						}else if(category == "test"){
							categoryCN = "测试费";
						}else if(category == "outsourcing"){
							categoryCN = "外包费";
						}else if(category == "entertain"){
							categoryCN = "业务招待费";
						}else if(category == "hydropower"){
							categoryCN = "水电费";
						}else if(category == "rental"){
							categoryCN = "租赁费";
						}else if(category == "intermediary"){
							categoryCN = "中介费";
						}else if(category == "property"){
							categoryCN = "物管费";
						}else if(category == "repair"){
							categoryCN = "修缮费";
						}
						var list = "";
						list += "<tr>"+
								"<td class='reimburse-change-num hidden'>"+id+"</td>"+
								"<td class='reimburse-change-id w80'>"+i+"</td>"+
								"<td class='reimburse-change-type w105'>"+categoryCN+"</td>"+
								"<td class='reimburse-change-name w130'>"+data['data'][x]['name']+"</td>"+
								"<td class='reimburse-change-date w200'>"+data['data'][x]['create_time']+"</td>"+
								"<td class='reimburse-change-state w200'>"+data['data'][x]['reason']+"</td>"+
								"<td class='reimburse-change-price w130'>"+data['data'][x]['price']+"</td>"+
								"</tr>";
						i++;
						$('#reimburse-change-tbody').append(list);
						}	

						$("#new-sub2").fadeOut(400,function(){
							$("#reimburse-change-div").removeClass("hidden");
							$("#reimburse-change-div").slideDown(400);
						});
				};
			}
		});
    }

    $('.payWay').click(function(){
    	if($(".payWay[value='transfers']").is(":checked")){
    		$('#reimburse-change-borrowing').addClass('hidden');
    		$('#reimburse-change-transfers').removeClass('hidden');
    	}else if($(".payWay[value='borrowing']").is(":checked")){
    		$('#reimburse-change-transfers').addClass('hidden');
    		$('#reimburse-change-borrowing').removeClass('hidden');
    	}
    });
    // 报销单提交
    function changeSendReimburse(){
    	var flag = 0;
    	var way = "";
    	var d_pattern = /^\d+$/;
		var money_pattern = /^\d+(\.\d{1,2})?$/;
    	if($('#reimburse-change-receipt').val() == ""){
			showHint("提示信息","请输入单据张数！");
    	}else if(!d_pattern.exec($('#reimburse-change-receipt').val())){
    		showHint("提示信息","单据张数格式不正确！");
    		$('#reimburse-change-receipt').focus();
    	}else if(!$(".payWay[value='transfers']").is(":checked") && !$(".payWay[value='borrowing']").is(":checked")){
    		showHint("提示信息","请选择付款方式！");
    	}else if($(".payWay[value='transfers']").is(":checked")){
    		if($('#reimburse-change-payee').val() == ""){
    			showHint("提示信息","请输入收款人姓名！");
    		}else if($('#reimburse-change-bank').val() == ""){
    			showHint("提示信息","请输入开户银行！");
    		}else if($('#reimburse-change-account').val() == ""){
    			showHint("提示信息","请输入银行账号！");
    		}else if(!d_pattern.exec($('#reimburse-change-account').val())){
    			showHint("提示信息","银行账号格式不正确！");
    		}else{
    			way = "transfer";
    			flag = 1;
    		}
    	}else if($(".payWay[value='borrowing']").is(":checked")){
    		if($('#reimburse-change-borrow').val() == ""){
    			showHint("提示信息","请输入原借款金额！");
    		}else if(!money_pattern.exec($('#reimburse-change-borrow').val())){
    			showHint("提示信息","原借款金额格式错误！");
    			$('#reimburse-change-borrow').focus();
    		}else{
    			way = "borrow";
    			flag = 1;
    		}
    	}
    	if(flag == 1 && way != ""){
    		var id = $('.reimburse-change-num:first').text();
    		var bank = $('#reimburse-change-bank').val();
    		var account = $('#reimburse-change-account').val();
    		var name = $('#reimburse-change-payee').val();
    		var owe_money_initial = $('#reimburse-change-borrow').val();
    		var bill_num = $('#reimburse-change-receipt').val();

    		console.log([id,way,bank,account,name,owe_money_initial,bill_num]);
    		$.ajax({
				type:'post',
				dataType:'json',
				url:'/ajax/editReimburse',
				data:{'id':id,'way':way, 'bank_info':bank, 'bank_code':account, 'payee':name, 'borrow_amount':owe_money_initial, 'receipt_num':bill_num},
				success:function(result){

					if(result.code == 0){
						showHint("提示信息","提交报销单成功！");
						setTimeout(function(){location.href="/user/subscribe/page_tag/reimburse"},1200);
					}else if(result.code == -1){
						showHint("提示信息","提交报销单失败，请重试！");
					}else if(result.code == -2){
						showHint("提示信息","参数错误！");
					}else if(result.code == -3){
						showHint("提示信息","找不到该报销单！");
					}else if(result.code == -4){
						showHint("提示信息","该报销单已提交！");
					}else{
						// console.log(result.code);
						showHint("提示信息","你没有权限执行此操作！");
					}
				}
			});
    	}
    }
    // 全选功能
	function changeAllChecked(this_select) {
		var checked_item = $('[name=checkbox]');
		$.each(checked_item ,function(key,obj){
	    	obj.checked = this_select.checked;
	    });
	}

	/*
	    yeqingwen 2016-01-05  
	    自定义分配比例触发函数
	*/
	function showNewDiv(obj) {
		// debugger;
	    $(obj).parent().next().html("");
	    var select_vaulue= $(obj).val();
	    if(select_vaulue == null){
	    	select_vaulue = "";
	    }
	    if(select_vaulue=="user-defined"){
	        var ySet = (window.innerHeight - $("#add-Tpl-div").height())/3;
	        var xSet = (window.innerWidth - $("#add-Tpl-div").width())/2;
	        var timestamp=new Date().getTime();
	        $("#add-Tpl-div").css("top",ySet);
	        $("#add-Tpl-div").css("left",xSet);
	        $("#add-Tpl-div").modal({show:true});
	        $("#new-tpl-select-id").text(timestamp);
	        $(obj).attr('id', timestamp);
	    }
	    else{
	        var content = "";
	        try {
	            var fee_div_list = JSON.parse($(obj).val());
	            if(fee_div_list == null){
	            	fee_div_list = "";
	            }
	            $.each(fee_div_list, function(key, value){
	                content += findProjectName(key) + ':' + value + "%<br>";
	            });
	        }
	        catch (err) {
	            console.log(err);
	        }
	        $(obj).parent().next().html(content);
	    }
	}
	//自定义分配比例触发函数，用于修改申请
	function showNewDiv2(obj) {
	    $(obj).parent().next().html("");
	    var select_vaulue= $(obj).val();
	    if(select_vaulue=="user-defined"){
	        var ySet = (window.innerHeight - $("#add-Tpl-div").height())/3;
	        var xSet = (window.innerWidth - $("#add-Tpl-div").width())/2;
	        var timestamp=new Date().getTime();
	        $("#add-Tpl-div").css("top",ySet);
	        $("#add-Tpl-div").css("left",xSet);
	        $("#add-Tpl-div").modal({show:true});
	        $("#new-tpl-select-id").text(timestamp);
	        $(obj).attr('id', timestamp);
	    }
	    else{
	        var content = "";
	        try {
	            var fee_div_list = JSON.parse($(obj).val());
	            $.each(fee_div_list, function(key, value){
	                content += findProjectName(key) + ':' + value + "%<br>";
	            });
	        }
	        catch (err) {
	            console.log(err);
	        }
	        $(obj).parent().next().html(content);
	    }
	}
	// 增加一行
	function addLine(){
	    var tbody = $('#newTpl-tbody');
	    var tr = "<tr class='addAfter'>"+
	            "<th class='w80 va-t center'></th>"+
	            "<td class='w80 add_Tpl_pro'>"+
	                "<select class='form-control w200 inline-block add-Tpl-select'>"+
	                "<?php foreach ($project_list as $value): ?>"+
	                    "<option><?php echo $value['name'] ?></option>"+
	                "<?php endforeach ?>"+
	                "</select>"+" "+
	                "<input class='form-control w150 inline-block add-Tpl-input' value='' placeholder='比例''>"+" "+
	                "<a href='javascript:;' onclick='deleteLine(this)'>删除</a>"
	            "</td>"+
	        "</tr>";
	    tbody.append(tr);
	}
	//自定义分摊模板
	function newTpl(){
	    var name = $('#add_Tpl_name').val()+ "(自定义)";
	    var td = $('.add_Tpl_pro');
	    var proArr = {};
	    var proNameArr = new Array();
	    var total = 0;
	    var flag_repeat = true;
	    var flag_empty = true;

	    $.each(td,function(){
	        var projectName = this.children[0].value;
	        var projectId = findProjectId(projectName);
	        if($.inArray(projectId,proNameArr) == -1){
	            proNameArr.push(projectId);
	        }else{
	            flag_repeat = false;
	        }
	        var pro = this.children[1].value;
	        if(pro == ""){
	            pro = "0";
	            flag_empty = false;
	        }
	        total += parseInt(pro);
	        proArr[projectId] = pro;
	    });
	    if(name == ""){
	        showHint("提示信息","请输入模板名称！");
	    }else if(total != 100){
	        showHint("提示信息","比例之和必须为100,请重新输入！");
	    }else if(flag_repeat == false){
	        showHint("提示信息","所选项目出现重复，请重新选择！");
	    }else if(flag_empty == false){
	        showHint("提示信息","项目分摊费用比例未填写完整！");
	    }else{
	        console.log(proArr);
	        var seleect_id = $("#new-tpl-select-id").text();
	        var content = '<option value=\''+ JSON.stringify(proArr) + '\'>' + name + '</option>';
	        //将自定义的模板添加至倒数第一个选择框
	        $("#" + seleect_id + " option:last").before(content); 
	        //指定新加的为选择
	        $("#" + seleect_id + " option:last").prev().attr('selected', true);

	        //修改费用分摊说明
	        var obj = $("#" + seleect_id);
	        var fee_div_list = JSON.parse($(obj).val());
	        var content = "";
	        $.each(fee_div_list, function(key, value){
	            content += findProjectName(key) + ':' + value + "%,";
	        });
	        $(obj).parent().next().text(content);

	        // $("#1451975421687 option:selected").text()
	        $("#add-Tpl-div").modal('hide');
	    }
	}

	//根据项目查找ID
	function findProjectId(name){
	    var proArr = <?php echo CJSON::encode($project_list) ?>;
	    for(var x in proArr){
	        if(proArr[x]['name'] == name){
	            return proArr[x]['project_id'];
	        }
	    }
	}

	//根据项目查找ID
	function findProjectName(id){
	    var proArr = <?php echo CJSON::encode($project_list) ?>;
	    for(var x in proArr){
	        if(proArr[x]['project_id'] == id){
	            return proArr[x]['name'];
	        }
	    }
	}

    function setDefaultFeeTpl() {
        var department_id = '<?php echo $this->user->department->department_id;?>';
        var project_list = <?php echo empty($project_list)? "''" : CJSON::encode($project_list) ?>;
        var tpl_list = <?php echo empty($fee_div_tpl)? "''" : CJSON::encode($fee_div_tpl) ?>;
        var project_id = "";
        var tpl_name = "";
        var tpl_name2 = "";
        $.each(project_list, function(){
            if(department_id== this['department_id']) {
                project_id = this['project_id'];
                return false;
            }
        });
        $.each(tpl_list, function(tpl_id, tpl_value){
            $.each(JSON.parse(tpl_value['fee_div_p']), function(key, value) {
                if(project_id==key) {
                    if(value==100) {                //如果找到100则返回
                        tpl_name = tpl_value['name'];
                        return false;
                    }
                    tpl_name2 = tpl_value['name'];
                }
            });

            if(tpl_name !=""){
                tpl_name = tpl_value['name'];
                return false;
            }
        });
        return tpl_name;
    }

    function cancelTpl(){
		var id = $('#new-tpl-select-id').text();
		var feeTplName = setDefaultFeeTpl();
		if(feeTplName != ""){
			$("#"+id+"").find("option:contains('"+feeTplName+"')").attr("selected",true);
		}else{
			$("#"+id+"").children().eq(0).attr("selected",true);
		}
		showNewDiv($("#"+id+""));
    }

</script>