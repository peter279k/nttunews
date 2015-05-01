<?php
	require 'Slim/Slim.php';
	require 'lib/db.connection.php';
	\Slim\Slim::registerAutoloader();
	
	$app = new \Slim\Slim();
	//http get all categories of news
	$app -> get('/news/:table_name', 'get_news_content'); //取得其中一個種類所有公告
	$app -> get('/news/:table_name/:publish_date', 'get_assign_news'); //取得其中一個種類的指定日期所有公告
	$app -> get('/rss/:table_name', function ($table_name) use ($app) {
		set_headers($app);
		rss_service($table_name);
	}); //取得其中一個種類RSS
	$app -> run();
	
	/*
		create procedure name search_table and input the table name string to query
		the right tables.
		
		create procedure name search_record and input the table name,publish_date string to query 
		the right tables.
		
		create procedure name rss_service and input the table name string to query 
		the right table that has records within a week.
	*/
	
	function get_news_content($table_name)
	{
		$response = array();
		$response["data"] = "";
		
		$link_db = db_connection();
		if($link_db==null)
			$response['data'] = 'cannot link db.';
		else
		{
			$stmt = $link_db -> prepare('CALL search_table(:table_name)'); //CALL procedure_name
			$stmt -> execute(array(":table_name"=>$table_name));
			
			$res_count = 0;
			$result = array();
			while($res=$stmt->fetch())
			{
				$result[$res_count]["date"] = $res["date"];
				$result[$res_count]["title"] = $res["title"];
				if(stristr($res["link"],'?'))
				{
					$temp = explode('?',$res["link"]);
					$result[$res_count]["link"] = $temp[0];
				}
				else
					$result[$res_count]["link"] = $res["link"];
				$res_count++;
			}
			
			$response["data"] = $result;
			$response_count = count($result);
			if($response_count==0)
				$response["data"] = '';
		}
		
		echo json_encode($response);
	}
	
	function get_assign_news($table_name,$publish_date)
	{
		$response = array();
		$response["data"] = "";
		
		if(check_date($publish_date))
		{
			$link_db = db_connection();
			if($link_db==null)
				$response["data"] = "cannot link db.";
			else
			{
				$stmt = $link_db -> prepare("CALL search_record(:table_name,:publish_date)");
				$stmt -> execute(array(":table_name"=>$table_name,":publish_date"=>$publish_date));
				
				$res_count = 0;
				$result = array();
				while($res=$stmt->fetch())
				{
					$result[$res_count]["date"] = $res["date"];
					$result[$res_count]["title"] = $res["title"];
					if(stristr($res["link"],'?'))
					{
						$temp = explode('?',$res["link"]);
						$result[$res_count]["link"] = $temp[0];
					}
					else
						$result[$res_count]["link"] = $res["link"];
					$res_count++;
				}
			}
		}
		else
			$response["data"] = "invalid_date";
		return json_encode($response);
	}
	
	function rss_service($table_name)
	{
		$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
		$rssfeed .= '<rss version="2.0">';
		$rssfeed .= '<channel>';
		$rssfeed .= '<description>'.$table_name.'feed</description>';
		
		$rssfeed .= '<copyright>Copyright (C) 2015 peter279k.com</copyright>';
		
		$link_db = db_connection();
		if($link_db==null)
			exit();
		else
		{
			$stmt = $link_db -> prepare("CALL rss_service(:table_name)");
			$stmt -> execute(array(":table_name"=>$table_name));
			
			while($res=$stmt->fetch())
			{
				if(stristr($res["link"],'?'))
				{
					$temp = explode('?',$res["link"]);
					$res["link"] = $temp[0];
				}
				
				$rssfeed .= '<item>';
				$rssfeed .= '<date>'.$res['date'].'</date>';
				$rssfeed .= '<title>'.$res['title'].'</title>';
				$rssfeed .= '<link>'.$res['link'].'</link>';
				$rssfeed .= '</item>';
			}
			
			$rssfeed .= '</channel>';
			$rssfeed .= '</rss>';
			$xml = new SimpleXMLElement($rssfeed);
			echo $xml->asXML();
		}
	}
	
	function set_headers($app) 
	{
		$app->response->headers->set("Content-Type",'application/xml');
	}
	
	function check_date($chk_date)
	{
		$result = null;
		$arr = explode("-",$chk_date);
		if(count($arr)!=3)
			$result = false;
		if(!stristr($chk_date,'-'))
			$result = false;
		else if(strlen($arr[0])!=4)
			$result = false;
		else if(strlen($arr[1])!=2 || strlen($arr[2])!=2)
			$result = false;
		else
			$result = true;
		return $result;
	}
?>
