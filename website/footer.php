<p center>
    &copy;jsly
</p>
<script language="JavaScript">
function myrefresh()
{
   window.location.reload();
}
//将时间转换为时间戳，mod取余得到时分秒时间戳，判断时间02:03:00和10:03:10

//获取精确到秒的时间戳 
//if ((getSecondTimestampTwo mod 86400) >= 7200 || (getSecondTimestampTwo mod 86400) <= 36190) {
//	setTimeout('myrefresh()',<?php echo ( ($data[0][4] + 187) - round(microtime(true)) )*1000 ?>); //指定10000毫秒刷新一次，1秒等于1000毫秒
//else
//	setTimeout('myrefresh()',<?php echo ( ($data[0][4] + 28800) - round(microtime(true)) )*1000 ?>);
//}
//setTimeout('myrefresh()',30000); //指定10000毫秒刷新一次，1秒等于1000毫秒  
setTimeout('myrefresh()',<?php $a=($data[0][4] + 185) - round(microtime(true));echo ($a<0?28800000:$a*1000) ?>);


// 以下两个函数从服务器获取时间并以js动态显示在网页中
function get_obj(time){  
	return document.getElementById(time);  
}  
var ts=<?php echo (round(microtime(true)*1000)) ?>  
function getTime(){  
	var t=new Date(ts);  
	with(t){  
	var _time=(getHours()<10 ? "0" :"") + getHours() + ":" + (getMinutes()<10 ? "0" :"") + getMinutes() + ":" +  (getSeconds()<10 ? "0" :"") + getSeconds();
        }  
	get_obj("time").innerHTML=_time;  
	setTimeout("getTime()",1000);  
	ts+=1000;  
}  
//倒计时
function get_obj2(time2){  
	return document.getElementById(time2);  
} 

var ts2=<?php echo ( ($data[0][4] + 180)*1000 - round(microtime(true)*1000) ) ?> //<?php echo (round(microtime(true)*1000)) ?> 
function getTime2(){  
	var t2=new Date(ts2);  
	
	with(t2){  
	var _time2=(getMinutes()<10 ? "0" :"") + getMinutes() + ":" +  (getSeconds()<10 ? "0" :"") + getSeconds();
        }
        if (t2.getMinutes()==59 || t2.getMinutes()==58 ||t2.getMinutes()==57 ){
        	get_obj2("time2").innerHTML='结算中...';
		return false;
	}
	get_obj2("time2").innerHTML=_time2;  
	setTimeout("getTime2()",1000);  
	ts2-=1000;
}  
var ts3=<?php echo ( ($data[0][4] + 86400)*1000 - round(microtime(true)*1000) ) ?>;
function getTime3(){  
	var t3=new Date(ts3);  
	with(t3){  
	var _time3=(getHours()<10 ? "0" :"") + getHours() + ":" + (getMinutes()<10 ? "0" :"") + getMinutes() + ":" +  (getSeconds()<10 ? "0" :"") + getSeconds();
        }  
        if (t3.getHours()=='00' && t3.getMinutes()=='00' && t3.getSeconds()=='00'){
        	get_obj2("time2").innerHTML='结算中...';
		return false;
	}
	get_obj2("time2").innerHTML=_time3;  
	setTimeout("getTime3()",1000);  
	ts3-=1000;  
} 
// 显示北京时间
getTime(); 

var ctrl_time=<?php echo (round(microtime(true)*1000)) ?>;
var ctrl=new Date(ctrl_time);
// 2点到10点03分前显示开盘倒计时，其他时候显示下一盘的倒计时
if (ctrl.getHours() >= '02' && ctrl.getHours() <= '09' )
	getTime3();
else if(ctrl.getHours() == '10' && ctrl.getMinutes() < '03' )
	getTime3();
else 
	getTime2();

</script>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-3.1.1.min.js" ></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
