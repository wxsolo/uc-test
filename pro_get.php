<?php 
/** 
 *  Doc by solo
 * $Id:proxy.php 
 * 	主操作类 
 *
 *  执行以下功能:
 *  1、定期(一小时一次)从若干个著名代理站点抓取代理列表到本地
 *	2、定期(一小时一次)检查本地已有地每一个代理是否存活及速度如何；
 * 
 */  
	include("conn.php");			//引入数据库文件
	include "Curl.class.php";		//引入Curl类


	//http://info.hustonline.net/proxy/prolist.aspx?show=3&indexPage=531
	//hustonline
	//http://proxy.ipcn.org/proxylist.html
	//proxy.ipcn
	$url = 'http://info.hustonline.net/proxy/prolist.aspx?show=3&indexPage=529';		//抓取的页面
	$analy_type = 'hustonline';   //选择分析过滤数据的入口


	//客户断开后继续执行脚本：实现定时抓取代理列表
 	ignore_user_abort(true);
	set_time_limit(0);
	$interval = 60*30;
	do{
		getproxy($url,$analy_type);

		sleep($interval);
	}while(true);

	

	/**
	*抓取函数
	*
	*/
	function getproxy($url = "http://proxy.ipcn.org/proxylist.html",$analy_type = 'proxy.ipcn')
	{
		$cu = new Curl();
		$result = $cu->get($url);

		analysis($analy_type,$result);


	}

	/**
	*数据分析函数
	*功能：提取代理列表的host和port
	*抓取不同的网页使用不同的代码
	*@param $analy_type 目标网页的代号，用于调用相对应的数据分析代码块
	*@param $result   抓取的网页内容
	*/
	function analysis($analy_type =null,$result)
	{
		$proxy = array();	//代理列表数组

		//aim to http://proxy.ipcn.org/proxylist.html
		if($analy_type == "proxy.ipcn")
		{
			preg_match_all("/<pre([\w\W]*?)<\/pre>/i",$result,$aim);	//正则获得目标区域

		 		$result = substr($aim[0][0], 93);
		 		//var_dump($result);
		       // $res = explode(":", $result);
		 		//抓取IP 都为数字的.
		         preg_match_all ( '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{2,4}/', $result, $match);  //获得host和port
		       
		      		
		         for($i=0; $i<count($match[0]); $i++)
		         {	
		         	$host = $match[0][$i];

		         	$res = explode(":", $host);	//分割host和port

		         	//$proxy = array('pro_host' => $res[0], 'pro_port' => $res[1]);	

		         	insert_proxy($res[0],$res[1]);
		         }
         	return 0;
		}

		//aim to http://info.hustonline.net/proxy/prolist.aspx
		if($analy_type == "hustonline")
		{
			//var_dump($result);
			preg_match_all("/<div class=\"list_r2 float\"([\w\W]*?)<\/div>\s{1,}<div class=\"list_r3 float\"([\w\W]*?)<\/div>/i",$result,$aim);
			//var_dump($aim[0]);

			for($i=0; $i<count($aim[0]);$i++)
			{
				$aim_value = $aim[0][$i];
				
				//get host
				preg_match_all("/<div class=\"list_r2 float\"([\w\W]*?)<\/div>/i",$aim_value,$aim_host);
				//var_dump($aim_host);
				preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$aim_host[0][0],$res_host);
				//echo $res_host[0][0];
				
				//get port
				preg_match_all("/<div class=\"list_r3 float\"([\w\W]*?)<\/div>/i",$aim_value,$aim_port);
				//var_dump($aim_port);
				preg_match_all('/\d{2,4}/',$aim_port[0][0],$res_port);
				//echo $res_port[0][0]."</br>";
				
				//写入数据库
				insert_proxy($res_host[0][0],$res_port[0][0]);
				//$proxy = array('pro_host' => $res_host[0][0], 'pro_port' =>$res_port[0][0]);	
				//var_dump($proxy);
				
			}
			//echo $title[0][0];
			
		}

		return 0; 
	}

	//写入数据库
	function insert_proxy($host,$port)
	{
		echo $host;
		echo $port;

		
		mysql_query(" 	INSERT INTO `uc_proxy` 
							(pro_host, pro_port) 
						SELECT 
							'$host', '$port' 
						FROM 
							dual 
						WHERE not exists 				#防止插入数据库中已存在数据
						(SELECT * FROM `uc_proxy` 
							WHERE 
							`pro_host` = '$host' 
							AND 
							`pro_port` = '$port' )") or die(mysql_error());
		return 0;

	}


