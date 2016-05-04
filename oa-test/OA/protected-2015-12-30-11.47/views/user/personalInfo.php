<?php
echo "<script type='text/javascript'>";
echo "console.log('personalInfo');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.Jcrop.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui-timepicker-addon.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery.Jcrop.css" />

<!-- 主界面 -->
<div>
	<div class="pd20 bor-1-ddd">
		<!-- 标题 -->
		<h4>
			<strong>我的资料</strong>
		</h4>
		<!-- 操作按钮 -->
		<h4 class="m0 pb10">
			<span class="glyphicon glyphicon-pencil f15px fr pointer info-edit-span" onclick="userEdit();" id="userEdit-btn">编辑</span>
			<span class="glyphicon glyphicon-floppy-disk f15px fr pointer info-edit-span hidden" onclick="userSave();" id="userSave-btn">保存</span>
		</h4>
		<!-- 主要信息 -->
		<div class="bor-b-1-ddd pb30">	
            <img src="<?php echo empty($user->photo) ? '' : $user->photo; ?>" width="100" height:"100" class="fl pointer mt20 ml20" onclick="changeHead();" title="修改头像"><!-- 头像 -->
			<!-- 信息块左 -->
			<div class="w250 fl mt20">
				<h3 class="m0 pl20 mb10 mt5"><?php echo empty($user->cn_name) ? '' : $user->cn_name;?></h3>
                <h4 class="pl20 m0 mb10 f20px"><?php echo empty($user->title) ? '' : $user->title; ?></h4>
                <h4 class="pl20 m0 mb10 f15px"><?php echo empty($user->department->name) ? '' : $user->department->name; ?><span class="ml10 f15px"><?php echo empty($user->entry_day)? '': $user->entry_day; ?> 入职</span></h4>
			</div>
			<!-- 信息块中 -->
			<div class="w400 fl bor-l-1-ddd pl20">
				<table class="lh30">
					<tbody>
						<tr>
							<th class="w100">性别</th>
							<td><?php echo (empty($user->gender) || $user->gender=='m')? '男': '女'; ?></td>
						</tr>
						<tr>
							<th class="w100">英文名</th>
							<td>
								<span><?php echo empty($user->en_name)? '': $user->en_name; ?></span>
								<input id="user-enname-input" value="<?php echo empty($user->en_name)? '': $user->en_name; ?>" class="hidden" style="line-height:normal;">
							</td>
						</tr>
						<tr>
							<th class="w100">籍贯</th>
							<td >
								<span><?php echo empty($user->native_place)? '': $user->native_place; ?></span>
								<input id="user-native-place-input" value="<?php echo empty($user->native_place)? '': $user->native_place; ?>" class="hidden" style="line-height:normal;">
							</td>
						</tr>
						<tr>
							<th class="w100">联系电话</th>
	                        <td >
	                        	<span><?php echo empty($user->mobile)?'':$user->mobile; ?></span>
	                        	<input id="user-mobile-input" value="<?php echo empty($user->mobile)?'':$user->mobile; ?>" class="hidden" style="line-height:normal;">
	                        </td>
						</tr>
						<tr>
							<th class="w100">出生日期</th>
	                        <td><?php echo empty($user->birthday)?'':$user->birthday; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- 信息块右 -->
			<div class="w400 fl bor-l-1-ddd pl20">
				<table class="lh30">
					<tbody>
						<tr>
							<th class="w100">QQ</th>
							<td><?php echo empty($user->qq)? '': $user->qq; ?></td>
						</tr>
						<tr>
							<th class="w100">E-mail</th>
							<td><?php echo empty($user->email)?'':$user->email;?></td>
						</tr>
						<tr>
							<th class="w100">转正日期</th>
							<td><?php echo empty($user->regularized_date)?'':$user->regularized_date;?></td>
						</tr>
						<tr>
							<th class="w100">工作类型</th>
							<td>
								<?php 
									if($user->job_status == "formal_employee")
									{
										echo '正式员工';
									}else if($user->job_status == "intern"){
										echo '实习生';
									}else{
										echo '试用期';
									}
								?>
							</td>
						</tr>
						<tr>
							<th class="w100">职位描述</th>
							<td><?php echo empty($user->job_description)? '': $user->job_description; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="clear"></div>
		</div>
		
		<!-- 基本资料 -->
		<div class="m0 p00 pt30" id="basic-detail-div"  >
			<h4 class="m0 pb10">
				<strong>基本信息</strong>
				<span class="glyphicon glyphicon-pencil f15px fr pointer info-edit-span" onclick="basicEdit();" id="basicEdit-btn">编辑</span>
				<span class="glyphicon glyphicon-floppy-disk f15px fr pointer info-edit-span hidden" onclick="basicSave();" id="basicSave-btn">保存</span>
			</h4><!-- 标题 -->
			<table class="table center table-bordered">
				<tbody>
					<tr>	
						<th class="w130 bg-fa center">婚姻状况</th>
						<td class="w200">
	                        <span>
	                        	<?php 
	                        		if(empty($entry->marital_status) || $entry->marital_status == 'unmarried'){
	                            		echo '未婚';
	                        		}else if($entry->marital_status == 'married'){
	                            		echo '已婚';
	                        		}else{
	                            		echo '离婚';
	                        		}
	                            ?>
	                        </span>
							<select id="basic-edit-marriage" class="center hidden w100 m0a form-control">
			    				<option value="unmarried">未婚</option>
			    				<option value="married">已婚</option>
			    				<option value="divorce">离婚</option>
			    			</select>
						</td>
						<th class="w130 bg-fa center">生育状况</th>
						<td class="center">
	                        <span>
	                        	<?php
	                            	if(empty($entry->fertility) || $entry->fertility == 'yes'){
	                                	echo '已生育';
	                            	}else{
	                                	echo '未生育';
	                            	}
								?>
							</span>
							<select id="basic-edit-birth" class="form-control center m0a w100 hidden">
			    				<option value="no">未生育</option>
			    				<option value="yes">已生育</option>
			    			</select>
						</td>
						<th class="w130 bg-fa center">民族</th>
						<td>
                        	<span><?php echo empty($entry->nation)?'':$entry->nation;?></span>
							<select id="basic-edit-nation" class="center hidden form-control w130 m0a">
			    				<option value="汉族">汉族</option>
								<option value="蒙古族">蒙古族</option>
								<option value="彝族">彝族</option>
								<option value="侗族">侗族</option>
								<option value="哈萨克族">哈萨克族</option>
								<option value="畲族">畲族</option>
								<option value="纳西族">纳西族</option>
								<option value="仫佬族">仫佬族</option>
								<option value="仡佬族">仡佬族</option>
								<option value="怒族">怒族</option>
								<option value="保安族">保安族</option>
								<option value="鄂伦春族">鄂伦春族</option>
								<option value="回族">回族</option>
								<option value="壮族">壮族</option>
								<option value="瑶族">瑶族</option>
								<option value="傣族">傣族</option>
								<option value="高山族">高山族</option>
								<option value="景颇族">景颇族</option>
								<option value="羌族">羌族</option>
								<option value="锡伯族">锡伯族</option>
								<option value="乌孜别克族">乌孜别克族</option>
								<option value="裕固族">裕固族</option>
								<option value="赫哲族">赫哲族</option>
								<option value="藏族">藏族</option>
								<option value="布依族">布依族</option>
								<option value="白族">白族</option>
								<option value="黎族">黎族</option>
								<option value="拉祜族">拉祜族</option>
								<option value="柯尔克孜族">柯尔克孜族</option>
								<option value="布朗族">布朗族</option>
								<option value="阿昌族">阿昌族</option>
								<option value="俄罗斯族">俄罗斯族</option>
								<option value="京族">京族</option>
								<option value="门巴族">门巴族</option>
								<option value="维吾尔族">维吾尔族</option>
								<option value="朝鲜族">朝鲜族</option>
								<option value="土家族">土家族</option>
								<option value="傈僳族">傈僳族</option>
								<option value="水族">水族</option>
								<option value="土族">土族</option>
								<option value="撒拉族">撒拉族</option>
								<option value="普米族">普米族</option>
								<option value="鄂温克族">鄂温克族</option>
								<option value="塔塔尔族">塔塔尔族</option>
								<option value="珞巴族">珞巴族</option>
								<option value="苗族">苗族</option>
								<option value="满族">满族</option>
								<option value="哈尼族">哈尼族</option>
								<option value="佤族">佤族</option>
								<option value="东乡族">东乡族</option>
								<option value="达斡尔族">达斡尔族</option>
								<option value="毛南族">毛南族</option>
								<option value="塔吉克族">塔吉克族</option>
								<option value="德昂族">德昂族</option>
								<option value="独龙族">独龙族</option>
								<option value="基诺族">基诺族</option>
			    			</select>
						</td>
					</tr>
					<tr>	
						<th class="w130 bg-fa center">学历</th>
						<td>
	                        <span>
	                        	<?php 
		                        	if(empty($entry->education) || $entry->education == 'high'){
			                            echo '高中';
			                        }elseif($entry->education == 'college'){
			                            echo '大专';
			                        }elseif($entry->education == 'undergraduate'){
			                            echo '本科';
			                        }elseif($entry->education == 'graduate'){
			                            echo '研究生';
			                        }elseif($entry->education == 'master'){
			                            echo '硕士';
			                        }elseif($entry->education == 'dr'){
			                            echo '博士';
			                        }
		 						?>
		 					</span>
							<select class="center w100 m0a hidden form-control" id="basic-edit-education-background">
			                    <option value="high">高中</option>
			                    <option value="college">大专</option>
			                    <option value="undergraduate">本科</option>
			                    <option value="graduate">研究生</option>
			                    <option value="master">硕士</option>
			                    <option value="dr">博士</option>
			                </select>
						</td>
						<th class="w130 bg-fa center">专业</th>
                        <td><span><?php echo empty($entry->professional) ? '' : $entry->professional; ?></span><input class="form-control hidden center" id="basic-edit-major" placeholder="请输入专业"></td>
						<th class="w130 bg-fa center">特长</th>
						<td><span><?php echo empty($entry->forte) ? '' : $entry->forte; ?></span><input class="form-control hidden   center" id="basic-edit-skill" placeholder="请输入特长"></td>
					</tr>
					<tr>	
						<th class="w130 bg-fa center">毕业学校</th>
						<td><span><?php echo empty($entry->school) ? '' : $entry->school; ?></span><input class="form-control hidden   center" id="basic-edit-school" placeholder="请输入毕业学校"></td>
						<th class="w130 bg-fa center">毕业时间</th>
						<td><span><?php echo empty($entry->graduation_time) ? '' : date('Y-m',strtotime($entry->graduation_time)); ?></span><input class="form-control hidden   center" id="basic-edit-graduate-time" placeholder="请选择毕业时间" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)"></td>
						<th class="w130 bg-fa center">兴趣爱好</th>
						<td><span><?php echo empty($entry->hobby) ? '' : $entry->hobby; ?></span><input class="form-control hidden   center" id="basic-edit-hobby" placeholder="请输入兴趣爱好"></td>
					</tr>
					<tr>	
						<th class="w130 bg-fa center">工作年限</th>
						<td><span><?php echo empty($entry->working_life) ? '' : (int)$entry->working_life; ?>年</span><input class="form-control hidden   center" id="basic-edit-work-time" placeholder="请输入工作年限"></td>
						<th class="w130 bg-fa center">现住地址</th>
						<td colspan="3"><span><?php echo empty($entry->present_address) ? '' : $entry->present_address; ?></span><input class="form-control hidden   center" id="basic-edit-contact-address" placeholder="请输入现住地址"></td>
					</tr>
					<tr>	
						<th class="w130 bg-fa center">户口性质</th>
						<td>
	                        <span>
	                        	<?php 
									if(empty($entry->residence_type) || $entry->residence_type == 'city'){
									    echo '城镇';
									}else{
									    echo '农村';
									}
								?>
							</span>
							<select id="basic-edit-account-property" class="m0a w100 center hidden form-control w100">
			    				<option value="city">城镇</option>
			    				<option value="rural">农村</option>
			    			</select>
						</td>
						<th class="w130 bg-fa center">户口所在地</th>
						<td colspan="3"><span><?php echo empty($entry->residence) ? '' : $entry->residence; ?></span><input class="form-control hidden   center" id="basic-edit-account-address" placeholder="请输入户口所在地"></td>
					</tr>
					<tr>
						<th class="w130 bg-fa center">身份证号码</th>
						<td><span><?php echo empty($entry->id_number)?'':$entry->id_number;?></span><input class="form-control hidden   center" id="basic-edit-id-num" placeholder="请输入身份证号码"></td>
						<th class="w130 bg-fa center">身份证地址</th>
						<td colspan="3"><span><?php echo empty($entry->id_number) ? '' : $entry->id_number; ?></span><input class="form-control hidden   center" id="basic-edit-id-address" placeholder="请输入身份证地址"></td>
					</tr>
					<tr>	
						<th class="w130 bg-fa center">紧急联系人</th>
						<td><span><?php echo empty($entry->emergency_contact) ? '' : $entry->emergency_contact; ?></span><input class="form-control hidden   center" id="basic-edit-urgency-name" placeholder="请输入紧急联系人"></td>
						<th class="w130 bg-fa center">联系电话</th>
						<td><span><?php echo empty($entry->emergency_telephone) ? '' : $entry->emergency_telephone; ?></span><input class="form-control hidden   center" id="basic-edit-urgency-mobile" placeholder="请输入紧急联系电话"></td>
						<th class="w130 bg-fa center">与本人关系</th>
						<td><span><?php echo empty($entry->relation) ? '' : $entry->relation; ?></span><input class="form-control hidden   center" id="basic-edit-urgency-relation" placeholder="请输入紧急联系人关系"></td>
					</tr>
					<tr>
						<th class="w130 bg-fa center">联系地址</th>
						<td colspan="5"><span><?php echo empty($entry->emergency_address) ? '' : $entry->emergency_address; ?></span><input class="form-control hidden   center" id="basic-edit-urgency-address" placeholder="请输入紧急联系地址"></td>
					</tr>
				</tbody>
			</table>
		</div>

		
		<!-- 家庭资料 -->
		<div class="p00 hidden mt50 pt20" id="family-detail-div">
			<h4 class="m0 pb10"><strong>家庭信息</strong>
				<span class="pointer f15px fr glyphicon glyphicon-plus info-edit-span" onclick="newFamily();" id="familyNew-btn">添加</span>
			</h4>
			<table class="table table-bordered center">
				<tr class="bg-fa">
					<th class="center w250">姓名</th>
					<th class="center">与本人关系</th>
					<th class="center">工作单位</th>
					<th class="center w250">联系电话</th>
					<th class="center w200">操作</th>
				</tr>
				<?php if(!empty($family)):?>
				<?php foreach($family as $f_row):?>
				<tr>
                	<td class="hidden"><?php echo $f_row->id;?></td>
					<td><span><?php echo $f_row->name;?></span><input class="form-control hidden   center"  placeholder="请输入家庭成员姓名"></td>
					<td><span><?php echo $f_row->relation;?></span><input class="form-control hidden   center"  placeholder="请输入家庭成员关系"></td>
					<td><span><?php echo $f_row->work;?></span><input class="form-control hidden   center"  placeholder="请输入家庭成员工作单位"></td>
					<td><span><?php echo $f_row->phone;?></span><input class="form-control hidden   center"  placeholder="请输入联系电话"></td>
					<td>
						<button class="btn btn-default b33 familyEdit-btn" onclick="familyEdit(this);"><span class="glyphicon glyphicon-pencil"></span>&nbsp;编辑</button>
						<button class="btn btn-default b66 hidden familySave-btn" onclick="familySave(this)"><span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;保存</button>
						<button class="btn btn-default b2 familyRemove-btn" onclick="familyRemove(this)"><span class="glyphicon glyphicon-remove"></span>&nbsp;删除</button>
					</td>
                </tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</table>
		</div>
		<!-- 教育资料 -->
		<div class="m0 p00 mt50 pt20 hidden" id="education-detail-div">
			<h4 class="m0 pb10"><strong>教育信息</strong>
				<span class="pointer f15px fr glyphicon glyphicon-plus info-edit-span" onclick="newEducation();" id="educationNew-btn">添加</span>
			</h4>
			<table class="table table-bordered center">
				<tr class="bg-fa">
					<th class="center w250">时间</th>
					<th class="center">学校/培训机构</th>
					<th class="center w250">专业</th>
					<th class="center w200">操作</th>
				</tr>
				<?php if(!empty($edu)):?>
				<?php foreach($edu as $e_row):?>
				<tr>
                <td class="hidden"><?php echo $e_row->id;?></td>
					<td>
						<span><?php echo date("Y-m", strtotime($e_row->start_date));?></span><input class="form-control hidden   center w100 inline pointer"  placeholder="起始时间">
						到
						<span><?php echo date("Y-m", strtotime($e_row->end_date));?></span><input class="form-control hidden   center w100 inline pointer"  placeholder="结束时间">
					</td>
					<td><span><?php echo $e_row->school;?></span><input class="form-control hidden   center"  placeholder="请输入学校或教育机构名称"></td>
					<td><span><?php echo $e_row->professional;?></span><input class="form-control hidden   center"  placeholder="请输入所学专业"></td>
					<td>
						<button class="btn btn-default b33 educationEdit-btn" onclick="educationEdit(this)" ><span class="glyphicon glyphicon-pencil"></span>&nbsp;编辑</button>
						<button class="btn btn-default b66 hidden educationSave-btn" onclick="educationSave(this)" ><span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;保存</button>
						<button class="btn btn-default b2 educationRemove-btn" onclick="educationRemove(this)" ><span class="glyphicon glyphicon-remove"></span>&nbsp;删除</button>
					</td>
                </tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</table>
		</div>
		<!-- 工作资料 -->
		<div class="m0 p00 mt50 pt20 hidden" id="work-detail-div">
			<h4 class="m0 pb10"><strong>工作信息</strong>
				<span class="pointer f15px fr glyphicon glyphicon-plus info-edit-span" onclick="newWork();" id="workNew-btn">添加</span>
			</h4>
			<table class="table table-bordered center">
				<tr class="bg-fa">
					<td class="hidden">id</td>
					<th class="center w250">时间</th>
					<th class="center">公司</th>
					<th class="center w250">职位</th>
					<th class="center w200">操作</th>
				</tr>
				<?php if(!empty($work)):?>
				<?php foreach($work as $w_row):?>
                <tr>
                    <td class="hidden"><?php echo $w_row->id;?></td>
                    <td>
						<span><?php echo date("Y-m", strtotime($w_row->start_date));?></span><input class="form-control hidden   center w100 inline pointer"  placeholder="起始时间">
						到
						<span><?php echo date("Y-m", strtotime($w_row->end_date));?></span><input class="form-control hidden   center w100 inline pointer"  placeholder="结束时间">
					</td>
					<td><span><?php echo $w_row->company;?></span><input class="form-control hidden   center"  placeholder="请输入联系电话"></td>
					<td><span><?php echo $w_row->title;?></span><input class="form-control hidden   center"  placeholder="请输入联系电话"></td>
					<td>
						<button class="btn btn-default b33 workEdit-btn" onclick="workEdit(this)"><span class="glyphicon glyphicon-pencil"></span>&nbsp;编辑</button>
						<button class="btn btn-default b66 hidden workSave-btn" onclick="workSave(this)"><span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;保存</button>
						<button class="btn btn-default b2 workRemove-btn" onclick="workRemove(this)" ><span class="glyphicon glyphicon-remove"></span>&nbsp;删除</button>
					</td>
                </tr>
				<?php endforeach; ?>
				<?php endif; ?>
			</table>
		</div>

		<h5 class="center m0 pointer" id="showDetial-h5" onclick="showDetail();"><a>查看完整资料</a></h5>
		<h5 class="center m0 pointer hidden" id="hideDetial-h5" onclick="hideDetail();"><a>隐藏详细资料</a></h5>
	</div>
</div>
<!-- 修改头像模态框 -->
<div id="newhead-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
	<!-- 模态框头部 -->
  	<div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
    	<a class="close" data-dismiss="modal">×</a>
    	<h4 class="hint-title">修改头像</h4>
  	</div>
  	<!-- 模态框主体 -->
  	<div class="modal-body">
  		<!-- 等待动画 -->
  		<div class="w50 m0a hidden" id="loading-div">
  			<img src="./images/loading.gif" class="h50 w50">
  		</div>
  	
    	<div class="example hidden nh200" id="example">
      		<span class="w100 inline-block">你上传的图片:</span>
      		<span class="w100" style="margin-left:195px;">你截取的头像:</span>
      		<!-- 截取区域 -->
        	<img id="imgPre" src="" alt="[Jcrop Example]" onload="setImgInfo(this);">
        	<!-- 截取预览图 -->
        	<div id="preview-pane">
          		<div class="preview-container" style="overflow:hidden;width:100px;height:100px;margin-left:300px;">
            	<img src="" class="jcrop-preview " id="imgPre2" alt="Preview">
          		</div>
        	</div>
    	</div>
  	</div>
  	<!-- 模态框底部 -->
  	<div class="modal-footer">
    	<div class="fl">
      		<input type="file" name="imgOne"  id="imgOne" onchange="reset();preImg(this.id,'imgPre');" /><!-- 文件选择器 -->
      		<p class="m0">(请选择大于100像素,小于2M,格式为jpg/png格式的图片)</p><!-- 提示 -->
    	</div>      
    	<button class="btn btn-success w100 disabled fr" id="upload-btn" onclick="UploadFile();">上传</button>
    	<div class="clear"></div>
  	</div>
</div>

<!-- js -->
<script type="text/javascript">
/*------------------------------------------详细信息显示、隐藏------------------------------------------*/

	// 显示详细信息
	function showDetail(){
		$("#family-detail-div").removeClass("hidden");
		$("#education-detail-div").removeClass("hidden");
		$("#work-detail-div").removeClass("hidden");
		$("#showDetial-h5").addClass("hidden");
		$("#hideDetial-h5").removeClass("hidden");
	}

	// 隐藏详细信息
	function hideDetail(){
		$("#family-detail-div").addClass("hidden");
		$("#education-detail-div").addClass("hidden");
		$("#work-detail-div").addClass("hidden");
		$("#showDetial-h5").removeClass("hidden");
		$("#hideDetial-h5").addClass("hidden");
	}

/*------------------------------------------主要信息------------------------------------------*/

	// 主要信息-编辑
	function userEdit(){
		$("#user-mobile-input").removeClass("hidden");
		$("#user-enname-input").removeClass("hidden");
		$("#user-native-place-input").removeClass("hidden");
		$("#user-mobile-input").prev().addClass("hidden");
		$("#user-enname-input").prev().addClass("hidden");
		$("#user-native-place-input").prev().addClass("hidden");
		$("#userEdit-btn").addClass("hidden");
		$("#userSave-btn").removeClass("hidden");
		$("#user-enname-input").focus();
	}

	// 主要信息-保存
	function userSave(){
		// 获取数据
		var id = '<?php echo $user->user_id; ?>';
		var cn_name = '<?php echo $user->cn_name; ?>';
		var en_name = $("#user-enname-input").val();
		var sex = '<?php echo $user->gender; ?>';
		var title = '<?php echo $user->title; ?>';
		var mobile = $("#user-mobile-input").val();
		var email = '<?php echo $user->email; ?>';
		var qq = '<?php echo $user->qq; ?>';
		var native_place = $("#user-native-place-input").val();
		var regularized_date = '<?php echo $user->regularized_date; ?>';
		var job_description = '<?php echo $user->job_description; ?>';
		var birthday = '<?php echo $user->birthday; ?>';
		var entry_date = '<?php echo $user->entry_day; ?>';
		var job_status = '<?php echo $user->job_status; ?>';
		var department_id = "<?php echo $user->department_id; ?>";

		// 验证数据
		var mobile_pattern = /^\d{1}\d{10}$/;
		var qq_pattern = /^\d+$/;
		var email_pattern = /^[\w\-\_\.]+\@[\w\-\_\.]+$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		if(!mobile_pattern.exec(mobile)){
			showHint("提示信息","电话格式不正确！");
		}else if(cn_name==""){
			showHint("提示信息","姓名不能为空！");
		}else if(title==""){
			showHint("提示信息","职位不能为空！");
		}else if(!email_pattern.exec(email)){
			showHint("提示信息","email格式不正确！");
		}else if(!qq_pattern.exec(qq)){
			showHint("提示信息","QQ格式不正确！");
		}else if(!date_pattern.exec(entry_date)){
			showHint("提示信息","入职日期格式不正确！");
		}else if(!date_pattern.exec(regularized_date)){
			showHint("提示信息","转正日期格式不正确！");
		}else if(!date_pattern.exec(birthday)){
			showHint("提示信息","出生日期格式不正确！");
		}else if(native_place==""){
			showHint("提示信息","籍贯不能为空！");
		}else if(job_description==""){
			showHint("提示信息","职位描述不能为空！");
		}else{
			$.ajax({
				type:'post',
				url: '/ajax/editUser',
				dataType: 'json',
				data:{'id':id,'entry_day':entry_date,'department_id':department_id,'cn_name':cn_name,'birthday':birthday,'job_description':job_description,'regularized_date':regularized_date, 'native_place':native_place,'job_status':job_status, 'en_name':en_name, 'sex':sex, 'title':title, 'mobile':mobile, 'email':email, 'qq':qq},
				success:function(result){
					if(result.code == 0){
						showHint("提示信息","修改资料成功！");
						setTimeout(function(){location.reload();},1200);
					}else if(result.code == -1){
						showHint("提示信息","修改信息失败！");
					}else if(result.code == -2){
						showHint("提示信息","找不到该用户！");
					}else if(result.code == -3){
						showHint("提示信息","信息不能为空！");
					}else if(result.code == -4){
						showHint("提示信息","性别错误！");
					}else if(result.code == -5){
						showHint("提示信息","职位状态错误！");
					}else if(result.code == -6){
						showHint("提示信息","转正日期格式错误！");
					}else if(result.code == -7){
						showHint("提示信息","邮件格式错误！");
					}else if(result.code == -99){
						showHint("提示信息","你没有权限执行此操作！");
						init();
					}
				}
			});
		}
	}

/*------------------------------------------基本信息------------------------------------------*/

	// 基本信息-编辑
	function basicEdit(){
		// 显示输入框并且将数据填充入输入框
		$("#basic-detail-div").find("input").each(function(){
			var str = $(this).parent().find("span").text();
			if(str.indexOf("年")>-1) str = str.split("年")[0];
			$(this).val(str);
			$(this).removeClass("hidden");
			$(this).parent().find("span").remove();
		});

		// 显示下拉框并且选择好对应的选项
		$("#basic-detail-div").find("select").each(function(){
			var str = $(this).parent().find("span").text();
			switch(str){
				case "已婚":{
					str = "married";
					break;
				}
				case "未婚":{
					str = "unmarried";
					break;
				}
				case "离婚":{
					str = "divorce";
					break;
				}
				case "城镇":{
					str = "city";
					break;
				}
				case "农村":{
					str = "rural";
					break;
				}
				case "高中":{
					str = "high";
					break;
				}
				case "大专":{
					str = "college";
					break;
				}
				case "本科":{
					str = "undergraduate";
					break;
				}
				case "研究生":{
					str = "graduate";
					break;
				}
				case "硕士":{
					str = "master";
					break;
				}
				case "博士":{
					str = "dr";
					break;
				}
				case "已生育":{
					str = "yes";
					break;
				}
				case "未生育":{
					str = "no";
					break;
				}
			}
			$(this).val(str);
			$(this).removeClass("hidden");
			$(this).parent().find("span").remove();
		});

		// 操作按钮的显示和隐藏
		$("#basicSave-btn").removeClass("hidden");
		$("#basicEdit-btn").addClass("hidden");
	}

	// 基本信息的初始化
	var name,sex,birthday,nation,mobile,email,native_place,marriage,birth,hobby;
    var skill,id_num,id_address,account_property,account_address,contact_address,urgency_name,urgency_mobile,urgency_relation,urgency_address;
    var school,graduate_time,education_background,major;
    var work_time;

	// 基本信息-保存
	function basicSave(){
		// 获取数据
		nation = $("#basic-edit-nation").val();
		id_num = $("#basic-edit-id-num").val();
		marriage = $("#basic-edit-marriage").val();
		birth = $("#basic-edit-birth").val();
		education_background = $("#basic-edit-education-background").val();
		major = $("#basic-edit-major").val();
		skill = $("#basic-edit-skill").val();
		school = $("#basic-edit-school").val();
		graduate_time = $("#basic-edit-graduate-time").val();
		hobby = $("#basic-edit-hobby").val();
		contact_address = $("#basic-edit-contact-address").val();
		account_property = $("#basic-edit-account-property").val();
		account_address = $("#basic-edit-account-address").val();
		id_address = $("#basic-edit-id-address").val();
		work_time = $("#basic-edit-work-time").val();
		urgency_name = $("#basic-edit-urgency-name").val();
		urgency_mobile = $("#basic-edit-urgency-mobile").val();
		urgency_relation = $("#basic-edit-urgency-relation").val();
		urgency_address = $("#basic-edit-urgency-address").val();

		// 验证数据
		var d_pattern = /^\w{18}$/;
		var d_pattern_s = /^\w{15}$/;
        var mobile_pattern = /^1\d{10}$/;
        var cellphone_pattern = /^[0-9\-]{12,13}$/;
        var date_pattern = /^\d{4}-\d{2}$/;
        var num_pattern = /^\d+$/;
        if(school==""){
            showHint("提示信息","请输入毕业学校！");
            $("#basic-edit-school").focus();
        }else if(graduate_time==""){
            showHint("提示信息","请选择毕业时间！");
            $("#basic-edit-graduate-time").focus();
        }else if(!date_pattern.exec(graduate_time)){
            showHint("提示信息","毕业时间输入格式错误！");
            $("#basic-edit-graduate-time").focus();
        }else if(major==""){
            showHint("提示信息","请输入专业！");
            $("#basic-edit-major").focus();
        }else if(id_num==""){
            showHint("提示信息","请输入身份证号码！");
            $("#basic-edit-id-num").focus();
        }else if(!d_pattern.exec(id_num) && !d_pattern_s.exec(id_num)){
            showHint("提示信息","身份证号码格式错误！");
            $("#basic-edit-id-num").focus();
        }else if(id_address==""){
            showHint("提示信息","请输入身份证地址！");
            $("#basic-edit-id-address").focus();
        }else if(account_address==""){
            showHint("提示信息","请输入户口所在地！");
            $("#basic-edit-account-address").focus();
        }else if(work_time==""){
            showHint("提示信息","请输入工作年限！");
            $("#basic-edit-work-time").focus();
        }else if(!num_pattern.exec(work_time)){
            showHint("提示信息","工作年限只可以输入数字！");
            $("#basic-edit-work-time").focus();
        }else if(contact_address==""){
            showHint("提示信息","请输入联系地址！");
            $("#basic-edit-contact-address").focus();
        }else if(urgency_name==""){
            showHint("提示信息","请输入紧急联系人姓名！");
            $("#basic-edit-urgency-name").focus();
        }else if(urgency_mobile==""){
            showHint("提示信息","请输入紧急联系人电话！");
            $("#basic-edit-urgency-mobile").focus();
        }else if(!mobile_pattern.exec(urgency_mobile) && !cellphone_pattern.exec(urgency_mobile)){
            showHint("提示信息","紧急联系人电话格式错误！");
            $("#basic-edit-urgency-mobile").focus();
        }else if(urgency_relation==""){
            showHint("提示信息","请输入紧急联系人关系！");
            $("#basic-edit-urgency-relation").focus();
        }else if(urgency_address==""){
            showHint("提示信息","请输入紧急联系人地址！");
            $("#basic-edit-urgency-address").focus();
        }else{
        	// 发送基本信息
            sendBasic();
        }
	}

	// 基本信息-发送
	function sendBasic(){
        $.ajax({
          type:'post',
          url: '/ajax/entryDetail',
          dataType: 'json',
          data:{'nation':nation,'marital_status':marriage,'fertility':birth,'id_number':id_num,'education':education_background,'professional':major,'school':school,'graduation_time':graduate_time,'residence':account_address,'residence_type':account_property,'working_life':work_time,'id_address':id_address,'present_address':contact_address,'hobby':hobby,'forte':skill,'emergency_contact':urgency_name,'emergency_telephone':urgency_mobile,'relation':urgency_relation,'emergency_address':urgency_address},
          success:function(result){
            if(result.code == 0){
                showHint("提示信息","保存基本信息成功！");
                // 将信息填回表格中并且隐藏输入框
                $("#basic-detail-div").find("input").each(function(){
					if($(this).attr("id")=="basic-edit-work-time"){
						var str = "<span>"+$(this).val()+"年</span>";
					}else{
						var str = "<span>"+$(this).val()+"</span>";
					}
					$(this).before(str);
					$(this).addClass("hidden");
				});

                // 将选项的值填回表格中并且隐藏下拉框
				$("#basic-detail-div").find("select").each(function(){
					var str = "";
					switch($(this).val()){
						case "married":{
							str = "已婚";
							break;
						}
						case "unmarried":{
							str = "未婚";
							break;
						}
						case "divorce":{
							str = "离婚";
							break;
						}
						case "city":{
							str = "城镇";
							break;
						}
						case "rural":{
							str = "农村";
							break;
						}
						case "high":{
							str = "高中";
							break;
						}
						case "college":{
							str = "大专";
							break;
						}
						case "undergraduate":{
							str = "本科";
							break;
						}
						case "graduate":{
							str = "研究生";
							break;
						}
						case "master":{
							str = "硕士";
							break;
						}
						case "dr":{
							str = "博士";
							break;
						}
						case "yes":{
							str = "已生育";
							break;
						}
						case "no":{
							str = "未生育";
							break;
						}
					}
					if(str != ""){
						var str2 = "<span>"+str+"</span>";
					}else{
						var str2 = "<span>"+$(this).val()+"</span>";
					}
					$(this).before(str2);
					$(this).addClass("hidden");
				});

				// 操作按钮的显示和隐藏
				$("#basicSave-btn").addClass("hidden");
				$("#basicEdit-btn").removeClass("hidden");
            }else if(result.code == -1){
                showHint("提示信息","保存基本信息失败！");
            }else if(result.code == -2){
                showHint("提示信息","参数错误！");
            }else if(result.code == -99){
                showHint("提示信息","你没有权限执行此操作！");
            }
          }
        });
    }

/*-------------------------------------------------------------家庭信息-------------------------------------------------------------*/

    // 家庭信息-新增
    function newFamily(){
    	var str = "<tr>"+
    	"<td class='hidden'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入家庭成员姓名'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入家庭成员关系'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入工作单位'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入联系电话'></td>"+
		"<td><button class='btn btn-default b33 familySave-btn mr5' onclick='familySave(this)'><span class='glyphicon glyphicon-floppy-disk'></span>&nbsp;保存</button>"+
		"<button class='btn btn-default b33 familyEdit-btn mr5 hidden' onclick='familyEdit(this)'><span class='glyphicon glyphicon-pencil'></span>&nbsp;编辑</button>"+
		"<button class='btn btn-default b2 familyRemove-btn' onclick='familyRemove(this)'><span class='glyphicon glyphicon-remove'></span>&nbsp;删除</button></td>"+
		"</tr>";
    	$("#family-detail-div").find("tbody").append(str);
    	$("#family-detail-div").find("tbody").children().last().children().first().find("input").focus();
    }

	// 家庭信息-编辑
	function familyEdit(obj){
		// 显示输入框并且将数据填充入输入框
		$(obj).parent().parent().find("input").each(function(){
			$(this).removeClass("hidden");
			var str = $(this).parent().find("span").text();
			$(this).val(str);
			$(this).parent().find("span").remove();
		});
		$(obj).parent().parent().find("input").first().focus();
		$(obj).parent().find(".familySave-btn").removeClass("hidden");
		$(obj).addClass("hidden");
	}

	// 家庭信息-保存
	function familySave(obj){
		// 获取数据
		var newfamily_arr = new Array();
		$(obj).parent().parent().find("input").each(function(){
			newfamily_arr.push($(this).val());
		});

		// 验证信息
		if(newfamily_arr[0]==""){
			showHint("提示信息","请输入家庭成员姓名！");return false;
		}else if(newfamily_arr[1]==""){
			showHint("提示信息","请输入家庭成员关系！");return false;
		}else if(newfamily_arr[2]==""){
			showHint("提示信息","请输入家庭成员工作单位！");return false;
		}else if(newfamily_arr[3]==""){
			showHint("提示信息","请输入联系电话！");return false;
		}else{
			var mobile_pattern = /^1\d{10}$/;
	        var telephone_pattern = /^0\d{3}-\d{7}$/;
	        var telephone_pattern_2 = /^0\d{3}-\d{8}$/;
	        var telephone_pattern_ext = /^0\d{2}-\d{8}$/;
	        var f_tag = 0;
			if(!mobile_pattern.exec(newfamily_arr[3])){         // 手机检验
	            if(!telephone_pattern_ext.exec(newfamily_arr[3])){         // 020开头的检验
	                if(!telephone_pattern.exec(newfamily_arr[3])){         // 0757开头的检验
	                    if(!telephone_pattern_2.exec(newfamily_arr[3])){
	                        showHint("提示信息","联系电话格式错误！");
	                        f_tag = 1;
	                        return false;
	                    }
	                }
	            }
	        }
	        if(f_tag == 0){
	        	// 获取id
	        	var id = "";
				$(obj).parent().parent().find("td").each(function(){
					if($(this).hasClass("hidden")){
						id = $(this).text();
					}
				});

				// 判断是否有id, 有的话就为保存，没有的话就为新增
				if(id != ""){
					$.ajax({
				        type:'post',
				        dataType:'json',
				        url:'/ajax/updateFamily',
				        data:{'id':id, 'name':newfamily_arr[0],'relation':newfamily_arr[1],'work':newfamily_arr[2],'phone':newfamily_arr[3]},
				        success:function(result){
				          	if(result.code == 0){
				          		showHint("提示信息","更新家庭信息成功！");

				          		// 将信息填回表格中并且隐藏输入框
				          		$(obj).parent().parent().find("input").each(function(){
				          			var str = "<span>"+$(this).val()+"</span>";
				          			$(this).before(str);
				          			$(this).addClass("hidden");
				          		});
				          		$(obj).parent().find(".familyEdit-btn").removeClass("hidden");
				          		$(obj).addClass("hidden");
				          	}else if(result.code == -1){
				          		showHint("提示信息","更新家庭信息失败！");
				          	}else if(result.code == -2){
				          		showHint("提示信息","参数错误！");
				          	}else if(result.code == -99){
				          		showHint("提示信息","你没有权限进行此操作！");
				          	}
				        }
				    });
				}else{
					$.ajax({
				        type:'post',
				        dataType:'json',
				        url:'/ajax/addFamily',
				        data:{'name':newfamily_arr[0],'relation':newfamily_arr[1],'work':newfamily_arr[2],'phone':newfamily_arr[3]},
				        success:function(result){
				          	if(result.code == 0){
				          		showHint("提示信息","保存家庭信息成功！");

				          		// 将信息填回表格中并且隐藏输入框
				          		$(obj).parent().parent().find("input").each(function(){
				          			var str = "<span>"+$(this).val()+"</span>";
				          			$(this).before(str);
				          			$(this).addClass("hidden");
				          		});
				          		$(obj).parent().parent().find("td").each(function(){
									if($(this).hasClass("hidden")){
										$(this).text(result.id);
									}
								});
				          		$(obj).parent().find(".familyEdit-btn").removeClass("hidden");
				          		$(obj).addClass("hidden");
				          	}else if(result.code == -1){
				          		showHint("提示信息","保存家庭信息失败！");
				          	}else if(result.code == -2){
				          		showHint("提示信息","参数错误！");
				          	}else if(result.code == -99){
				          		showHint("提示信息","你没有权限进行此操作！");
				          	}
				        }
				    });
				}
	        }
		}
	}

	// 家庭信息-删除
	function familyRemove(obj){
		// 获取id
		var id = "";
		$(obj).parent().parent().find("td").each(function(){
			if($(this).hasClass("hidden")){
				id = $(this).text();
			}
		});

		// 判断是否有id，有则发送数据，没有则在本地删除
		if(id != ""){
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/deleteRowFamily',
		        data:{'id':id},
		        success:function(result){
		          	if(result.code == 0){
		          		showHint("提示信息","删除家庭信息成功！");
		          		$(obj).parent().parent().remove();
		          	}else if(result.code == -1){
		          		showHint("提示信息","删除家庭信息失败！");
		          	}else if(result.code == -2){
		          		showHint("提示信息","参数错误！");
		          	}else if(result.code == -99){
		          		showHint("提示信息","你没有权限进行此操作！");
		          	}
		        }
		    });
		}else{
			$(obj).parent().parent().remove();
		}
	}

/*-------------------------------------------------------------教育信息-------------------------------------------------------------*/

	// 教育信息-新增
    function newEducation(){
    	var str = "<tr>"+
    	"<td class='hidden'></td>"+
    	"<td><input class='form-control   center w100 inline pointer'  placeholder='起始时间'> 到 <input class='inline form-control   center w100 pointer' placeholder='结束时间'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入学校或教育机构名称'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入所学专业'></td>"+
		"<td><button class='btn btn-default b33 educationSave-btn mr5' onclick='educationSave(this)'><span class='glyphicon glyphicon-floppy-disk'></span>&nbsp;保存</button>"+
		"<button class='btn btn-default b33 educationEdit-btn mr5 hidden' onclick='educationEdit(this)'><span class='glyphicon glyphicon-pencil'></span>&nbsp;编辑</button>"+
		"<button class='btn btn-default b2 educationRemove-btn' onclick='educationRemove(this)'><span class='glyphicon glyphicon-remove'></span>&nbsp;删除</button></td>"+
		"</tr>";
    	$("#education-detail-div").find("tbody").append(str);
    	$("#education-detail-div").find("tbody").children().last().children().first().next().find("input").each(function(){
    		$(this).bind("click",function(){
    			setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1);
    		});
    	});
    }

	// 教育信息-编辑
	function educationEdit(obj){
		// 显示输入框并且将数据填充入输入框
		var count = 0 ;
		$(obj).parent().parent().find("input").each(function(){
			$(this).removeClass("hidden");
			if(count == 0 || count == 1){
				var str = $(this).prev().text();
				$(this).val(str);
				$(this).bind("click",function(){
	    			setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1);
	    		});
				if(count == 1) $(this).parent().find("span").remove();
				count ++;
			}else{
				var str = $(this).parent().find("span").text();
				$(this).val(str);
				$(this).parent().find("span").remove();
				count ++;
			}
		});
		$(obj).parent().parent().find("input").first().focus();
		$(obj).parent().find(".educationSave-btn").removeClass("hidden");
		$(obj).addClass("hidden");
	}

	// 教育信息-保存
	function educationSave(obj){
		// 获取数据
		var neweducation_arr = new Array();
		$(obj).parent().parent().find("input").each(function(){
			neweducation_arr.push($(this).val());
		});

		// 验证数据
		if(neweducation_arr[0]==""){
			showHint("提示信息","请选择起始时间！");return false;
		}else if(neweducation_arr[1]==""){
			showHint("提示信息","请选择结束时间！");return false;
		}else if(neweducation_arr[1]<neweducation_arr[0]){
			showHint("提示信息","起始时间不能在结束时间之后！");return false;
		}else if(neweducation_arr[2]==""){
			showHint("提示信息","请输入学校或教育机构名称！");return false;
		}else if(neweducation_arr[3]==""){
			showHint("提示信息","请输入所学专业！");return false;
		}else{
			// 获取id
        	var id = "";
			$(obj).parent().parent().find("td").each(function(){
				if($(this).hasClass("hidden")){
					id = $(this).text();
				}
			});

			// 判断是否有id,有则编辑,无则新增
			if(id != ""){
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/updateRowEdu',
			        data:{'id':id, 'start_date':neweducation_arr[0],'end_date':neweducation_arr[1],'school':neweducation_arr[2],'professional':neweducation_arr[3]},
			        success:function(result){
			          	if(result.code == 0){
			          		showHint("提示信息","更新教育信息成功！");
			          		// 将信息填回表格中并且隐藏输入框
			          		$(obj).parent().parent().find("input").each(function(){
			          			var str = "<span>"+$(this).val()+"</span>";
			          			$(this).before(str);
			          			$(this).addClass("hidden");
			          		});
			          		$(obj).parent().find(".educationEdit-btn").removeClass("hidden");
			          		$(obj).addClass("hidden");
			          	}else if(result.code == -1){
			          		showHint("提示信息","更新教育信息失败！");
			          	}else if(result.code == -2){
			          		showHint("提示信息","参数错误！");
			          	}else if(result.code == -99){
			          		showHint("提示信息","你没有权限进行此操作！");
			          	}
			        }
			    });
			}else{
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/addRowEdu',
			        data:{'start_date':neweducation_arr[0],'end_date':neweducation_arr[1],'school':neweducation_arr[2],'professional':neweducation_arr[3]},
			        success:function(result){
			          	if(result.code == 0){
			          		showHint("提示信息","保存教育信息成功！");
			          		// 将信息填回表格中并且隐藏输入框
			          		$(obj).parent().parent().find("input").each(function(){
			          			var str = "<span>"+$(this).val()+"</span>";
			          			$(this).before(str);
			          			$(this).addClass("hidden");
			          		});
			          		$(obj).parent().parent().find("td").each(function(){
								if($(this).hasClass("hidden")){
									$(this).text(result.id);
								}
							});
			          		$(obj).parent().find(".educationEdit-btn").removeClass("hidden");
			          		$(obj).addClass("hidden");
			          	}else if(result.code == -1){
			          		showHint("提示信息","保存教育信息失败！");
			          	}else if(result.code == -2){
			          		showHint("提示信息","参数错误！");
			          	}else if(result.code == -99){
			          		showHint("提示信息","你没有权限进行此操作！");
			          	}
			        }
			    });
			}
		}
	}

	// 教育信息-删除
	function educationRemove(obj){
		// 获取id
		var id = "";
		$(obj).parent().parent().find("td").each(function(){
			if($(this).hasClass("hidden")){
				id = $(this).text();
			}
		});

		// 判断是否有id，有则发送数据，没有则在本地删除
		if(id != ""){
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/deleteRowEdu',
		        data:{'id':id},
		        success:function(result){
		          	if(result.code == 0){
		          		showHint("提示信息","删除教育信息成功！");
		          		$(obj).parent().parent().remove();
		          	}else if(result.code == -1){
		          		showHint("提示信息","删除教育信息失败！");
		          	}else if(result.code == -2){
		          		showHint("提示信息","参数错误！");
		          	}else if(result.code == -99){
		          		showHint("提示信息","你没有权限进行此操作！");
		          	}
		        }
		    });
		}else{
			$(obj).parent().parent().remove();
		}
	}

/*-------------------------------------------------------------工作信息-------------------------------------------------------------*/

	// 工作信息-新增
    function newWork(){
    	var str = "<tr>"+
    	"<td class='hidden'></td>"+
    	"<td><input class='form-control   center w100 inline pointer'  placeholder='起始时间'> 到 <input class='inline form-control   center w100 pointer' placeholder='结束时间'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入公司名称'></td>"+
    	"<td><input class='form-control   center'  placeholder='请输入职位名称'></td>"+
		"<td><button class='btn btn-default b33 workSave-btn mr5' onclick='workSave(this)'><span class='glyphicon glyphicon-floppy-disk'></span>&nbsp;保存</button>"+
		"<button class='btn btn-default b33 workEdit-btn mr5 hidden' onclick='workEdit(this)'><span class='glyphicon glyphicon-pencil'></span>&nbsp;编辑</button>"+
		"<button class='btn btn-default b2 workRemove-btn' onclick='workRemove(this)'><span class='glyphicon glyphicon-remove'></span>&nbsp;删除</button></td>"+
		"</tr>";
    	$("#work-detail-div").find("tbody").append(str);
    	$("#work-detail-div").find("tbody").children().last().children().first().next().find("input").each(function(){
    		$(this).bind("click",function(){
    			setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1);
    		});
    	});
    }

	// 工作信息-编辑
	function workEdit(obj){
		// 显示输入框并且将数据填充入输入框
		var count = 0;
		$(obj).parent().parent().find("input").each(function(){
			$(this).removeClass("hidden");
			if(count == 0 || count == 1){
				var str = $(this).prev().text();
				$(this).val(str);
				$(this).bind("click",function(){
	    			setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1);
	    		});
				if(count == 1) $(this).parent().find("span").remove();
				count ++;
			}else{
				var str = $(this).parent().find("span").text();
				$(this).val(str);
				$(this).parent().find("span").remove();
				count ++;
			}
		});
		$(obj).parent().parent().find("input").first().focus();
		$(obj).parent().find(".workSave-btn").removeClass("hidden");
		$(obj).addClass("hidden");
	}

	// 工作信息-保存
	function workSave(obj){
		// 获取数据
		var newwork_arr = new Array();
		$(obj).parent().parent().find("input").each(function(){
			newwork_arr.push($(this).val());
		});

		// 验证数据
		if(newwork_arr[0]==""){
			showHint("提示信息","请选择起始时间！");return false;
		}else if(newwork_arr[1]==""){
			showHint("提示信息","请选择结束时间！");return false;
		}else if(newwork_arr[1]<newwork_arr[0]){
			showHint("提示信息","起始时间不能在结束时间之后！");return false;
		}else if(newwork_arr[2]==""){
			showHint("提示信息","请输入学校或教育机构名称！");return false;
		}else if(newwork_arr[3]==""){
			showHint("提示信息","请输入所学专业！");return false;
		}else{
			// 获取id
        	var id = "";
			$(obj).parent().parent().find("td").each(function(){
				if($(this).hasClass("hidden")){
					id = $(this).text();
				}
			});

			// 判断是否有id，有则编辑,无则新增
			if(id != ""){
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/updateRowWork',
			        data:{'id':id, 'start_date':newwork_arr[0],'end_date':newwork_arr[1],'company':newwork_arr[2],'title':newwork_arr[3]},
			        success:function(result){
			          	if(result.code == 0){
			          		showHint("提示信息","更新工作信息成功！");
			          		// 将信息填回表格中并且隐藏输入框
			          		$(obj).parent().parent().find("input").each(function(){
			          			var str = "<span>"+$(this).val()+"</span>";
			          			$(this).before(str);
			          			$(this).addClass("hidden");
			          		});
			          		$(obj).parent().find(".workEdit-btn").removeClass("hidden");
			          		$(obj).addClass("hidden");
			          	}else if(result.code == -1){
			          		showHint("提示信息","更新工作信息失败！");
			          	}else if(result.code == -2){
			          		showHint("提示信息","参数错误！");
			          	}else if(result.code == -99){
			          		showHint("提示信息","你没有权限进行此操作！");
			          	}
			        }
			    });
			}else{
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/addRowWork',
			        data:{'start_date':newwork_arr[0],'end_date':newwork_arr[1],'company':newwork_arr[2],'title':newwork_arr[3]},
			        success:function(result){
			          	if(result.code == 0){
			          		showHint("提示信息","保存工作信息成功！");
			          		// 将信息填回表格中并且隐藏输入框
			          		$(obj).parent().parent().find("input").each(function(){
			          			var str = "<span>"+$(this).val()+"</span>";
			          			$(this).before(str);
			          			$(this).addClass("hidden");
			          		});
			          		$(obj).parent().parent().find("td").each(function(){
								if($(this).hasClass("hidden")){
									$(this).text(result.id);
								}
							});
			          		$(obj).parent().find(".workEdit-btn").removeClass("hidden");
			          		$(obj).addClass("hidden");
			          	}else if(result.code == -1){
			          		showHint("提示信息","保存工作信息失败！");
			          	}else if(result.code == -2){
			          		showHint("提示信息","参数错误！");
			          	}else if(result.code == -99){
			          		showHint("提示信息","你没有权限进行此操作！");
			          	}
			        }
			    });
			}
		}
	}

	// 工作信息-删除
	function workRemove(obj){
		// 获取id
		var id = "";
		$(obj).parent().parent().find("td").each(function(){
			if($(this).hasClass("hidden")){
				id = $(this).text();
			}
		});

		// 判断是否有id，有则发送数据，没有则在本地删除
		if(id != ""){
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/deleteRowWork',
		        data:{'id':id},
		        success:function(result){
		          	if(result.code == 0){
		          		showHint("提示信息","删除工作信息成功！");
		          		$(obj).parent().parent().remove();
		          	}else if(result.code == -1){
		          		showHint("提示信息","删除工作信息失败！");
		          	}else if(result.code == -2){
		          		showHint("提示信息","参数错误！");
		          	}else if(result.code == -3){
		          		showHint("提示信息","没有找到该工作信息！");
		          	}else if(result.code == -99){
		          		showHint("提示信息","你没有权限进行此操作！");
		          	}
		        }
		    });
		}else{
			$(obj).parent().parent().remove();
		}
	}

/*-----------------------------------------------------上传头像-----------------------------------------------------*/

	// 显示上传头像
	function changeHead(){
		var ySet = (window.innerHeight - $("#newhead-div").height())/4;
		var xSet = (window.innerWidth - $("#newhead-div").width())/2;
		$("#newhead-div").css("top",ySet);
		$("#newhead-div").css("left",xSet);
		$('#newhead-div').modal({show:true});
	}

	// 初始化预览区
	var reset_flag = 0;
	function reset(){
	  if(reset_flag == 1){
	    $("#newhead-div").find(".modal-body").children().remove();
	    var str = "<div class='w50 m0a hidden' id='loading-div'>"+
	  		"<img src='./images/loading.gif' class='h50 w50'>"+
	  	"</div><div class='example hidden nh200' id='example'>"+
	    "<span class='w100 inline-block'>你上传的图片:</span>"+
	    "<span class='w100' style='margin-left:195px;'>你截取的头像:</span>"+
	    "<img id='imgPre' src='' alt='[Jcrop Example]' onload='setImgInfo(this);'>"+
	    "<div id='preview-pane'>"+
	    "<div class='preview-container' style='overflow:hidden;width:100px;height:100px;margin-left:300px;'>"+
	    "<img src='' class='jcrop-preview' id='imgPre2' alt='Preview'>"+
	    "</div></div></div>";
	    $("#newhead-div").find(".modal-body").append(str);
	  }
	}

	//图片预览设置
	function preImg(sourceId, targetId) { 
		// 判断是否为图片
	  	if(document.getElementById(sourceId).files[0].type.indexOf("image") < 0){
	    	showHint("提示信息","请选择jpg或png或gif格式的图片");
	  	}else{
	    	//先隐藏，判断图片大于100像素才显示
	    	$("#example").addClass("hidden");

	    	//浏览器支持的判断
		    if (typeof FileReader === 'undefined') {  
		        alert('Your browser does not support FileReader...');  
		        return;  
		    }  
	    	var reader = new FileReader();  

		    reader.onload = function(e) {  
			    //给预览图src赋值
			    var src = this.result;
			    var img = document.getElementById(targetId);
			    var img2 = document.getElementById(targetId+"2");   
			    img.src = src; 
			    img2.src = src;   
		    }  
		    reader.readAsDataURL(document.getElementById(sourceId).files[0]); 

		    //设置大预览图的宽度 
		    $("#imgPre").attr("width","250");

		    changeFlag = 0;  // 更改选择的文件的标记

		    reset_flag = 1;  // 初始化标记

		    $("#loading-div").removeClass("hidden");
		}
	}

	// 获取图片真实高度和宽度,并且判断是否符合尺寸要求
	var real_width,real_height,percent,imgObj;
	function setImgInfo(obj){
		$("#loading-div").addClass("hidden");
		imgObj = new Image();
		imgObj.src = obj.src;

		real_width = imgObj.width;
		real_height = imgObj.height;
	  	percent = real_width/250;

		//检测图像是否大于100像素
		if(real_width < 100 || real_height < 100){
			showHint("提示信息","请选择像素大于100的照片！");
			$("#example").addClass("hidden");
			$("#upload-btn").addClass("disabled");
			$("#imgPre").attr("src","");
			$("#imgPre2").attr("src","");
			return false;
		}else{
			$("#upload-btn").removeClass("disabled");
			$("#example").removeClass("hidden");
		}

		// 加载头像截取工具
		loadJcrop();

		// 更换截取用图片
		changeImg();
	}

	var jcrop_api;
	var xLoc;
	var yLoc;
	var wSize;
	var boundx,boundy;
	//初始化截取框
	function loadJcrop(){
	  var $preview = $('#preview-pane'),
	      $pcnt = $('#preview-pane .preview-container'),
	      $pimg = $('#preview-pane .preview-container img'),
	  
	  xsize = $pcnt.width(),
	  ysize = $pcnt.height();

	  $('#imgPre').Jcrop({
	    onChange: updatePreview,
	    onSelect: getSize,
	    aspectRatio: xsize / ysize
	  },function(){
	    jcrop_api = this;

	    var bounds = this.getBounds();
	    boundx = real_width;
	    boundy = real_height;
	    
	    $preview.appendTo(jcrop_api.ui.holder);
	  });

	  // 更新预览
	  function updatePreview(c){
	    if (parseInt(c.w) > 0) {
	      var rx = real_width/250;
	      var ry = real_width/250;
	      
	      var pic_width = (c.w/250) * real_width;
	      var pic_height = (c.w/250) * real_height;

	      $pimg.css({
	        width: Math.round(250*100/c.w) + 'px',
	        height: Math.round(real_height/rx*100/c.w) + 'px',
	        marginLeft: '-' + Math.round(c.x *100/c.w) + 'px',
	        marginTop: '-' + Math.round(c.y *100/c.w) + 'px'
	      });
	    }
	  };

	  //获取选框数据
	  function getSize(c){
	    xLoc = c.x;
	    yLoc = c.y;
	    wSize = c.w;
	  }
	};

	// 更换截取用图片
	var changeFlag = 0;
	function changeImg(){
	  if(changeFlag == 0){
	    $(".jcrop-holder").find("img").attr("src",$("#imgPre").attr("src"));
	    $(".jcrop-holder").attr("width",250);
	    $(".jcrop-holder").attr("height",real_height*250/real_width);
	    changeFlag = 1;
	  }
	}

	//上传头像
	function UploadFile() {
		// 判断是否符合尺寸要求
		if(real_width<100||real_height<100){
		    showHint("提示信息","请选择像素大于100的照片！");
		    return false;
		}else{
		    var fileObj = document.getElementById("imgOne").files[0]; // 获取文件对象
		    var FileController = "/ajax/uploadPic";                    // 接收上传文件的后台地址 
		    
		    // FormData 对象
		    var form = new FormData();
		    var x = xLoc*percent;
		    var y = yLoc*percent;
		    var w = wSize*percent;
		    var user_id = '<?php echo $this->user->user_id;?>';
		    form.append("x", x);
		    form.append("y", y);
		    form.append("width", w);
		    form.append("upload_head", fileObj);
		    form.append("user_id", user_id);                           // 文件对象

		    // XMLHttpRequest 对象
		    var xhr = new XMLHttpRequest();
		    xhr.open("post", FileController, true);
		    xhr.onload = function () {
		        // showHint("提示信息","上传成功");
		    };
		    xhr.send(form);

		    xhr.onreadystatechange=function(){
		      if (xhr.readyState==4 && xhr.status==200){
		          var code = xhr.responseText;
		          if(code == 0){
		            showHint("提示信息","上传成功！");
		            setTimeout(function(){location.reload();},1000);
		          }else if(code == -1){
		            showHint("提示信息","上传失败！");
		          }else if(code == -2){
		            showHint("提示信息","参数错误！");
		          }else if(code == -3){
		            showHint("提示信息","找不到该用户！");
		          }else if(code == -4){
		            showHint("提示信息","格式错误！");
		          }else if(code == -5){
		            showHint("提示信息","图片大小超过2M！");
		          }else{
		            showHint("提示信息","你没有权限执行此操作！");
		          }
		      }
		    }
		}
	}
</script>
