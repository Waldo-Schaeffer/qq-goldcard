<?php

session_start();
header("Content-type: text/html; charset=utf-8");
# date_default_timezone_set('PRC'); 
include_once('header.php');

function db_connect(){
    try{
        $database_host = '127.0.0.1';         # mysql地址
        $database_user = 'root';              # mysql用户名
        $database_pass = 'QQspider2020';              # mysql用户密码
        $database_name = 'goldcard';           # 数据库名，不存在会自动创建
        $result = mysqli_connect($database_host, $database_user, $database_pass, $database_name);
    } catch (Exception $e) {
        echo $e->message;
        exit;
    }
    if (! $result) {
        return false;
    }
	mysqli_set_charset($result,"utf8");
    # 从数据库取5个字段：期号，号码，欧皇，收益，封盘时间，逆序后取660条
    $sql = 'select name,lucky_number,nick,legend_gcards,end_ts,legend_ncards,legend_type from gold_data order by key_id desc limit 0,660';
    $query_result = mysqli_query($result, $sql);
    if (!$query_result) {
        printf("Error: %s\n", mysqli_error($result));
        exit();
    }
    $data = [];
    while ($temp_data = mysqli_fetch_array($query_result)){
        array_push($data ,$temp_data);
    }
    return $data;
}

# 该函数用于发布广告，输出html代码，位置在表头之上
function advertisement () {
    echo "
				<div class='row'>
				  <div class='col-sm-12 col-lg-12 col-xs-12 col-md-12'>
                    <p style='font-size:22px;color:red'>
                        金卡链接详见群公告，点击一键加群：<a href='https://jq.qq.com/?_wv=1027&k=55uF80w' target='_blank'>936266825</a>
						<p>
							1、进入金卡页面之前需要先前往官网登陆登陆（手机QQ可直接跳过此步骤）：<a href='https://egame.qq.com/usercenter/followlist' target='_blank'>https://egame.qq.com/usercenter/followlist</a><br />
							2、登录后<a href='https://cdn.egame.qq.com/pgg-h5-cdn/module/golden-card.html' target='_blank'>点击此处进入金卡传说页面</a><br />
							3、登录后<a href='https://cdn.egame.qq.com/pgg-h5-cdn/module/golden-record.html' target='_blank'>点击此处查看自己的金卡押宝历史记录</a><br />
							<a href='../' target='_blank'>点击此处返回首页</a>
						</p>
						<p>说明：<font color='#ffff00'>血赚</font>&nbsp;&GT;&nbsp;<font color='#fa1ee8'>超赚</font>&nbsp;&GT;&nbsp;<font color='#ff0000'>大赚</font>&nbsp;&GT;&nbsp;<font color='#a0b8f8'>小赚</font>&nbsp;&GT;&nbsp;<font color='#00ff00'>赔了</font>&nbsp;&GT;&nbsp;<font color='#00ff00'>赔光</font></p>
                    </p>
				  </div>
				</div>
        ";
}

# 该函数将数字替换为对应的号码
function data_clear($data_row) {
    for ($i = 0;$i < count($data_row); ++$i) {
        switch ($data_row[$i][1]) {
            case 1:
                $data_row[$i][1] = 'Ⅰ-1';
                break;
            case 2:
                $data_row[$i][1] = 'Ⅱ-2';
                break;
            case 3:
                $data_row[$i][1] = 'Ⅲ-3';
                break;
            case 4:
                $data_row[$i][1] = 'Ⅳ-4';
                break;
            case 5:
                $data_row[$i][1] = 'Ⅴ-5';
                break;
            case 6:
                $data_row[$i][1] = 'Ⅵ-6';
                break;
            case 7:
                $data_row[$i][1] = 'Ⅶ-7';
                break;
            case 8:
                $data_row[$i][1] = 'Ⅷ-8';
                break;
            case 9:
                $data_row[$i][1] = 'Ⅸ-9';
                break;
            default:
                $data_row[$i][1] = $data_row[$i][1];	
        }
	}
    return $data_row;
}

# 该函数用于计算连出概率。(int)substr($data[0][0],-3,3)是总数，拿总数-$i再-1就是逆序索引
function continuous($data){
    $number_conut = 0;
    $number_temp = '';
    for ($i = 0;$i < (int)substr($data[0][0],-3,3); ++$i) {
        if ($i == 0){
            $number_temp = $data[(int)substr($data[0][0],-3,3)-1-$i][1];
            continue;
        }
        if ($data[(int)substr($data[0][0],-3,3)-1-$i][1] == $number_temp)
            $number_conut += 1;
        else
            $number_temp = $data[(int)substr($data[0][0],-3,3)-1-$i][1];
    }
    #echo $number_conut;
    #echo '<br>';
    #echo (int)substr($data[0][0],-3,3);
    #echo '<br>';
    return round($number_conut/(int)substr($data[0][0],-3,3)*100, 2);

}

# 该函数用于显示统计信息
function statistics_info ($data) {
    $total = array(
        'Ⅰ-1'    =>    0,
        'Ⅱ-2'    =>    0,
        'Ⅲ-3'    =>    0,
        'Ⅳ-4'    =>    0,
        'Ⅴ-5'    =>    0,
        'Ⅵ-6'    =>    0,
        'Ⅶ-7'    =>    0,
        'Ⅷ-8'    =>    0,
        'Ⅸ-9'    =>    0,
    );

    # $data[0][0] = '0430003';
    # (int)substr($data[0][0],-3,3) 是截取最新一期期号的后三位以控制今日统计,注意查询数据量需要大于期号，否则会索引错误，一般期数一天不超过330期
    for ($i = 0;$i < (int)substr($data[0][0],-3,3); ++$i) {
        switch ($data[$i][1]) {
            case 'Ⅰ-1':
                $total['Ⅰ-1'] += 1;
                break;
            case 'Ⅱ-2':
                $total['Ⅱ-2'] += 1;
                break;
            case 'Ⅲ-3':
                $total['Ⅲ-3'] += 1;
                break;
            case 'Ⅳ-4':
                $total['Ⅳ-4'] += 1;
                break;
            case 'Ⅴ-5':
                $total['Ⅴ-5'] += 1;
                break;
            case 'Ⅵ-6':
                $total['Ⅵ-6'] += 1;
                break;
            case 'Ⅶ-7':
                $total['Ⅶ-7'] += 1;
                break;
            case 'Ⅷ-8':
                $total['Ⅷ-8'] += 1;
                break;
            case 'Ⅸ-9':
                $total['Ⅸ-9'] += 1;
                break;
            default:
                break;
        }
	}
    $vary = $total;
    $min = array();
    $max = array();

    # 正序排序，以取最小的三位
    asort($vary);
    if (count($data) >= 10){
        $number = 0;
        foreach ($vary as $key=>$value){
            $min[$key] = $value;
            $number += 1;
            if ($number >=9)
                break;
        }
    }
    
    # 逆序排序，以取最大的三位
    arsort($vary);
    if (count($data) >= 10){
        $number = 0;
        foreach ($vary as $key=>$value){
            $max[$key] = $value;
            $number += 1;
            if ($number >=9)
                break;
        }
    }
    # var_dump($max);
    # 热门号码少于3个时显示暂无
    $max[array_keys($max)[0]] = $max[array_keys($max)[0]]==0?"暂无":array_keys($max)[0];
    $max[array_keys($max)[1]] = $max[array_keys($max)[1]]==0?"暂无":array_keys($max)[1];
    $max[array_keys($max)[2]] = $max[array_keys($max)[2]]==0?"暂无":array_keys($max)[2];
    
    # 冷门号码少于3个时显示暂无
    if (array_search('0',$min) && array_count_values ($min)['0'] >= 4){
        $min[array_keys($min)[0]] = '暂无';
        $min[array_keys($min)[1]] = '暂无';
        $min[array_keys($min)[2]] = '暂无';
    }else{
        $min[array_keys($min)[0]] = array_keys($min)[0];
        $min[array_keys($min)[1]] = array_keys($min)[1];
        $min[array_keys($min)[2]] = array_keys($min)[2];
    }

    # var_dump($min);


    echo "    <div class='container'>
        <div class='row'>

          <div class='col-sm-6 col-lg-6 col-xs-6 col-md-6' style='background-color:#6fc675'>
            北京时间：<p id=time style='font-size:25px;'></p>
            <div>
            <p style='font-size:18px;'>下期开奖预计:&nbsp;&nbsp;&nbsp;<b id=time2 style='font-size:20px;'></b></p>
            </div>
            <p style='font-size:16px;'>今日连出概率:&nbsp;&nbsp;&nbsp;<b>
            ";
        # =========  调用函数输出概率 ==========
        echo continuous($data);
        echo            "

            %</b></p>
          </div>

          <div class='col-sm-6 col-lg-6 col-xs-6 col-md-6' style='background-color:#00c675'>
            <div class='row' align='center'>
                <!-- 1 -->
              <div class='col-sm-12 col-lg-12 col-xs-12 col-md-12'>
                <p>下期预测</p>
              </div>
                <!-- 2 -->
              <div class=' col-md-6'>
                <p>热门号码</p>
                <p >" . $max[array_keys($max)[0]] . ", " . $max[array_keys($max)[1]] . ", " . $max[array_keys($max)[2]] . "</p>
              </div>
                <!-- 3 -->
              <div class=' col-md-6'>
                <p>冷门号码</p>
                <p >" . $min[array_keys($min)[0]] . ", " . $min[array_keys($min)[1]] . ", " . $min[array_keys($min)[2]] . "</p>
              </div>
            </div>
          </div>

          <div class='col-sm-12 col-lg-12 col-xs-12 col-md-12'>
            <div class='card ' style='background-color:#a0c675'>
              <div class='card-header' align='center'>今日号码出现次数统计</div>
              <div class='card-body'> 
                <table class='table'>
                    <!-- th>产品</th><th>价格 </th -->
                    <tr  align='center'>
                        <td style='background-color:#c0c675'>Ⅰ-1</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅰ-1'] . "</td>
                        <td style='background-color:#c0c675'>Ⅱ-2</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅱ-2'] . "</td>
                        <td style='background-color:#c0c675'>Ⅲ-3</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅲ-3'] . "</td>
                    </tr>
                    <tr  align='center'>
                        <td style='background-color:#c0c675'>Ⅳ-4</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅳ-4'] . "</td>
                        <td style='background-color:#c0c675'>Ⅴ-5</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅴ-5'] . "</td>
                        <td style='background-color:#c0c675'>Ⅵ-6</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅵ-6'] . "</td>
                    </tr>
                    <tr  align='center'>
                        <td style='background-color:#c0c675'>Ⅶ-7</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅶ-7'] . "</td>
                        <td style='background-color:#c0c675'>Ⅷ-8</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅷ-8'] . "</td>
                        <td style='background-color:#c0c675'>Ⅸ-9</td>
                        <td style='background-color:#d9c480'>" . $total['Ⅸ-9'] . "</td>
                    </tr>
                </table>
                    ";
        # =========  广告位 ==========
        advertisement();
        # =========  广告位 ==========
        echo            "
              </div>
            </div>
          </div>

        </div>
      </div>
        ";
}

# 输出表头
function html_header () {
    echo"    <div class='container'>
                <div class='row'>
                  <div class='panel panel-default col-sm-12 col-lg-12 col-xs-12 col-md-12'>
                    <!--div class='panel-heading'>企鹅挖矿大数据4.6</div-->
					<div class='panel-heading'></div>
                    <div class='panel-body table-responsive'>
                      <table class='table table-bordered table-hover'>
                        <thead>
                        <tr>
                          <th>期号</th>
                          <th>号码</th>
                          <th>欧皇</th>
                          <th>收益</th>
						  <th>盈亏</th>
                          <th>封盘时间</th>
                        </tr>
                        </thead>
                        <tbody>
    ";
}

# 该函数用于输出表格尾部
function html_footer () {
    echo "         </tbody>
                  </table>
                 </div>
              </div>
            </div>
          </div>";
}

# 该函数用于将从数据库获取的数据循环输出到表格中
function html_center ($data) {
    # 循环输出表格内容
    $day2_count =  substr($data[(int)substr($data[0][0],-3,3)][0],-3,3) + substr($data[0][0],-3,3);
    for($i=0;$i<$day2_count;++$i) {
        # 根据legend_type优先显示金卡，无金卡时显示银卡
        #if ($data[$i][6] == 2){
        #    $data[$i][3] = $data[$i][5].'&nbsp银卡';
        #    $image = './image/yin.png';
        #}
        #else{
        #    $data[$i][3] = $data[$i][3].'&nbsp金卡';
        #    $image = './image/jin.png';
        #}
		
		#直接显示幸运儿的信息，无论金卡银卡
		
		#判断盈亏
		if ($data[$i][5] == 0 ) {
			$wl = '血赚';
			$wl_color = '#ffff00';
		}elseif ((($data[$i][3] / 2) - $data[$i][5]) > 0 ) {
			$wl = '超赚';
			$wl_color = '#fa1ee8';
		}elseif ((($data[$i][3] / 4 * 3) - $data[$i][5]) > 0 ) {
			$wl = '大赚';
			$wl_color = '#ff0000';
		}elseif ((($data[$i][3] / 8 * 7) - $data[$i][5]) >= 0 ) {
			$wl = '小赚';
			$wl_color = '#a0b8f8';
		}elseif ($data[$i][3] == 0 ) {
			$wl = '赔光';
			$wl_color = '#00c675';
		}else{
			$wl = '赔了';
			$wl_color = '#00ff00';
		}

        echo "
                <tr bgcolor='". $wl_color ."'>
                  <td>" . substr($data[$i][0], -3, 3) . "</td>
                  <td>".$data[$i][1]."</td>
                  <td>".$data[$i][2]."</td>
                  <td><img src='./image/jin.png' width=40 height=25 />&nbsp".$data[$i][3]."金卡<img src='./image/yin.png' width=40 height=25 />&nbsp".$data[$i][5]."银卡&nbsp</td>
				  <td>". $wl ."</td>
                  <td>" . date('H:i:s', substr($data[$i][4], 0,10)+8*60*60) . "</td>
                </tr>
              ";
    }
}

# 连接数据库并查询最新的660条数据
$data = db_connect();
if (!$data) {
    echo 'Did connect to the database faild!!!';
}
# 号码替换
$data = data_clear($data);

# 统计信息输出
statistics_info($data);

# 数据表格输出
html_header();
html_center($data);
html_footer();

include_once('footer.php');
