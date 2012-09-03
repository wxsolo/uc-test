<?php 
	/**
	*Doc by solo
	*数据库连接页 conn.php	
	*/
		$conn = mysql_connect('localhost','root','');
		mysql_select_db('uc_test',$conn);
		mysql_query('set names utf8');
?>