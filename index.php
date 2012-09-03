<?php 
	/**
	*Doc by solo
	*访问主页 index.php	
	*根据数据库中已有的信息：
	*使用当前"总的访问时间"/"当前已经检测次数" = 平均访问速度
	*使用当前"总的存活速度"/"当前已经检测次数" = 有效度
	*得到平均访问速度升序、有效度（24小时内各时间点存活性计数）降序列表展示前50条代理记录；
	*/
	header('Content-type: text/html;charset = utf-8');
	
	include("conn.php");			//引入数据库文件


	$result = mysql_query("select 
							pro_host,
							pro_port,
							(total_time/pro_check_times) as ave_speed, 
							(pro_survival_times/pro_check_times) as ave_survival_time
						from 
							uc_proxy
						where 
							total_time > 0.0
							and
							pro_survival_times > 0.0
						order by 
							ave_speed asc,
							ave_survival_time desc
						limit 
							50
						");

	echo "<table>";
	echo "<tr>";
	echo "<td>代理IP</td>";
	echo "<td>端口</td>";
	echo "<td>平均访问速度</td>";
	echo "<td>有效度</td>";
	echo "</tr>";

	while($res = mysql_fetch_array($result))
	{
		echo "<tr>";
		echo "<td>".$res['pro_host']."</td>";
		echo "<td>".$res['pro_port']."</td>";
		echo "<td>".$res['ave_speed']."</td>";
		echo "<td>".$res['ave_survival_time']."</td>";
		echo "</tr>";

	}
		echo "</table>";

?>