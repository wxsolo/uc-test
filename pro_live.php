<?php 
/** 
 *  Doc by solo
 * $Id:pro_live.php 
 * 功能定时道理判断是否存活以及速度 
 *
 *  执行以下功能:
 *  定期(一小时一次)检查本地已有地每一个代理是否存活及速度如何；
 * 
 */  
	include("conn.php");			//引入数据库文件
	include "Curl.class.php";		//引入Curl类
	
	//客户断开后继续执行脚本：实现定时执行代理存活分析
 	ignore_user_abort(true);
	set_time_limit(0);
	$interval = 60*30;
	do{
		check_all();
		sleep($interval);
	}while(true);

	/**
	*检查函数
	*
	*/
	
	//循环调用检查函数检测得到的列表
	function check_all()
	{
		$result = mysql_query("select pro_host,pro_port from uc_proxy")or die(mysql_error());	
		while ($res = mysql_fetch_array($result)) 
		{
			check_one($res['pro_host'],$res['pro_port']);
		}

		mysql_query("update uc_proxy set pro_check_times = pro_check_times+1") or die(mysql_error());	
	}

	//检测代理的速度和是否存活
	function check_one($pro_host,$pro_port)
	{
		//使用代理  
		$setopt = array('proxy'=>true,'proxyHost' => $pro_host,'proxyPort' => $pro_port);  
		$cu = new Curl($setopt);  
		
		//得到 baidu 的首页内容  
		$cu->get('http://www.baidu.com/'); 

		//echo 'ERROR='.$cu->error();  
		//得到返回的错误信息
 		$info = $cu->getinfo();
 		//得到返回的错误码
		if($cu->errno() == 0&& $info['after']['http_code'] == 200)
		  { 
		  	
		  	$total_time = $info['after']['total_time'];  //获得访问时间

		  	//将此次访问时间与数据库中保存时间相加后写入数据库
		  	mysql_query("update uc_proxy set 
		  						total_time = (total_time + '$total_time'),
		  						pro_survival_times =  pro_survival_times+1
				  		where 
					  		pro_host = '$pro_host' 
					  		and 
					  		pro_port = '$pro_port' ") or die(mysql_error());
  		  }
		 
		 	/*
			//得到所有调试信息  
			echo 'ERRNO='.$cu->errno();  
			echo 'ERROR='.$cu->error();  
			$info = $cu->getinfo();
			print_r($info['after']);
			*/
	}
