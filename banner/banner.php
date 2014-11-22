<?php

// first connect to the database
// and include necessary files
require_once('../config/config.php');
require_once('../common/connect.php');
require_once('../common/case.php');
require_once('../common/constants.php');
require_once("class/pData.class.php");
require_once("class/pDraw.class.php");
require_once("class/pImage.class.php");

// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// we will need a server ID from the URL query string!
	// if no data query string is provided, this is an image
	if(!empty($sid) && in_array($sid,$ServerIDs))
	{
		// build graph using pChart API
		$result = @mysqli_query($BF4stats,"
			SELECT SUBSTRING(`TimeMapLoad`, 11, length(`TimeMapLoad`) - 16) AS Hourly, AVG(`MaxPlayers`) AS Average
			FROM `tbl_mapstats`
			WHERE `ServerID` = {$sid}
			AND SUBSTRING(`TimeMapLoad`, 1, LENGTH(`TimeMapLoad`) - 9) BETWEEN CURDATE() - INTERVAL 24 HOUR AND CURDATE()
			AND `Gamemode` != ''
			AND `MapName` != ''
			GROUP BY Hourly
			ORDER BY `TimeMapLoad` DESC
		");

		// initialize empty arrays
		$hour = array();
		$average = array();
		// did the query return results
		if(@mysqli_num_rows($result) != 0)
		{
			// initialize tracking variable
			$increment = '';
			// loop through query results
			while($row = mysqli_fetch_assoc($result))
			{
				$raw_hour = $row['Hourly'];
				// add missing hours to fill in hours for which the query found no results
				while($increment > $raw_hour && $increment != '')
				{
					$hour[] = $increment;
					$average[] = 0;
					$increment--;
				}
				$hour[] = $row['Hourly'];
				$average[] = $row['Average'];
				$increment = ($raw_hour - 1);
			}
			// query ran out of results to finish the day
			if(count($hour) < 23)
			{
				// get last array element to know where we need to start filling in data
				$last = end($hour);
				while(count($hour) < 23)
				{
					$hour[] = $last;
					$average[] = 0;
					$last--;
				}
			}
		}
		// no?
		else
		{
			$increment = 0;
			// add 24 hours of zeroes
			while($increment < 24)
			{
				$hour[] = $increment;
				$average[] = 0;
				$increment++;
			}
		}
		$myData = new pData();
		$myData->addPoints($average,"Serie1");
		$myData->setSerieDescription("Serie1","Average");
		$myData->setSerieOnAxis("Serie1",0);
		$serieSettings = array("R"=>255,"G"=>250,"B"=>200);
		$myData->setPalette("Serie1",$serieSettings);
		$myData->addPoints($hour,"Absissa");
		$myData->setAbscissa("Absissa");
		$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
		$myData->setAxisName(0,"");
		$myData->setAxisUnit(0,"");
		$myPicture = new pImage(160,65,$myData,TRUE);
		$GradientSettings = array("StartR"=>050,"StartG"=>100,"StartB"=>150,"Alpha"=>50,"Levels"=>-100);
		$myPicture->drawGradientArea(0,0,160,80,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->setShadow(FALSE);
		$myPicture->setGraphArea(12,20,153,60);
		$myPicture->setFontProperties(array("R"=>250,"G"=>250,"B"=>250,"FontName"=>"fonts/Forgotte.ttf","FontSize"=>6));
		$max = max($average);
		if($max == 0)
		{
			$max = 1;
		}
		if($max > 40)
		{
			$max = 40;
		}
		$Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
		, "Mode"=>SCALE_MODE_MANUAL, "ManualScale"=>array(0=>array("Min"=>0,"Max"=>$max))
		, "LabelingMethod"=>LABELING_ALL
		, "GridR"=>200, "GridG"=>200, "GridB"=>200, "GridAlpha"=>75
		, "TickR"=>240, "TickG"=>240, "TickB"=>240, "TickAlpha"=>75
		, "LabelRotation"=>0, "LabelSkip"=>1
		, "DrawXLines"=>0
		, "DrawSubTicks"=>1
		, "DrawYLines"=>ALL
		, "SubTickR"=>210, "SubTickG"=>210, "SubTickB"=>210, "SubTickAlpha"=>75
		, "AxisR"=>210, "AxisG"=>210, "AxisB"=>210, "AxisAlpha"=>75);
		$myPicture->drawScale($Settings);
		$Config = "";
		$myPicture->drawSplineChart();
		$myPicture->render("./banner_cache/banner_sid{$sid}.png");
		
		// graph is done
		
		// query for server info
		$Basic_q = @mysqli_query($BF4stats,"
			SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`, `IP_Address`
			FROM `tbl_server`
			WHERE `ServerID` = {$sid}
			AND `GameID` = {$GameID}
		");
		// information was found
		if(@mysqli_num_rows($Basic_q) != 0)
		{
			$Basic_r = @mysqli_fetch_assoc($Basic_q);
			$used_slots = $Basic_r['usedSlots'];
			$available_slots = $Basic_r['maxSlots'];
			$ip = $Basic_r['IP_Address'];
			$servername = $Basic_r['ServerName'];
			if(strlen($servername) > 34)
			{
				$servername = substr($servername,0,33);
				$servername .= '..';
			}
			$mode = $Basic_r['Gamemode'];
			// convert mode to friendly name
			if(in_array($mode,$mode_array))
			{
				$mode_name = array_search($mode,$mode_array);
				if(strlen($mode_name) > 14)
				{
					$mode_name = substr($mode_name,0,13);
					$mode_name .= '..';
				}
			}
			// this mode is missing!
			else
			{
				$mode_name = $mode;
				if(strlen($mode_name) > 14)
				{
					$mode_name = substr($mode_name,0,13);
					$mode_name .= '..';
				}
			}
			$map = $Basic_r['mapName'];
			
			// start outputting the image
			header('Pragma: public');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			header("Content-type: image/png");
			
			// base image
			$base = imagecreatefrompng('./images/background.png');
			
			// text color
			$light = imagecolorallocate($base, 255, 255, 255);
			$yellow = imagecolorallocate($base, 255, 250, 200);
			
			// copy map background onto the base background image
			$back = imagecreatefrompng('./images/map_back.png');
			imagecopy($base, $back, 6, 6, 0, 0, 104, 60);
			
			// convert map to friendly name
			// first find if this map name is even in the map array
			if(in_array($map,$map_array))
			{
				$map_name = array_search($map,$map_array);
				if(strlen($map_name) > 14)
				{
					$map_name = substr($map_name,0,13);
					$map_name .= '..';
				}
				$map_img = imagecreatefrompng('../images/maps/' . $map . '.png'); 
				$resize_map = imagecreatetruecolor(100, 56);
				imagecopyresampled($resize_map, $map_img, 0, 0, 0, 0, 100, 56, 200, 113);
			}
			// this map is missing!
			else
			{
				$map_name = $map;
				if(strlen($map_name) > 14)
				{
					$map_name = substr($map_name,0,13);
					$map_name .= '..';
				}
				$map_img = imagecreatefrompng('../images/maps/missing.png');
				$resize_map = imagecreatetruecolor(100, 56);
				imagecopyresampled($resize_map, $map_img, 0, 0, 0, 0, 100, 56, 200, 113);
			}
			
			// copy the map image onto the background image
			imagecopy($base, $resize_map, 8, 8, 0, 0, 100, 56);
			
			// bf4 logo
			$logo = imagecreatefrompng('./images/bf4.png');
			
			// copy the logo image onto the background image
			imagecopy($base, $logo, 8, 70, 0, 0, 100, 19);
			
			// copy graph background onto the base background image
			$back = imagecreatefrompng('./images/graph_back.png');
			imagecopy($base, $back, 389, 20, 0, 0, 164, 69);
			
			// add graph
			$graph = imagecreatefrompng('./banner_cache/banner_sid' . $sid . '.png');
			
			// copy the graph image onto the background image
			imagecopy($base, $graph, 391, 22, 0, 0, 160, 65);
			
			// figure out server's location
			// set location default to null
			$location = '';
			// remove port from IP address
			$s_explode = explode(":",$ip);
			$server_ip = $s_explode[0];
			// try API
			$json = @file_get_contents('http://ip-api.com/json/' . $server_ip);
			$data = @json_decode($json,true);
			$location = $data['countryCode'];
			// if above API failed ...
			if($location == '')
			{
				// use less accurate method by querying database for players with similar IP address as Server's IP address
				// loop through the query removing last character one at a time until a match is found
				while(@mysqli_num_rows($Location_q) == 0 && strlen($server_ip) > 1)
				{
					// query for server info
					$Location_q = @mysqli_query($BF4stats,"
						SELECT `CountryCode`
						FROM `tbl_playerdata`
						WHERE `IP_Address` LIKE '{$server_ip}%'
						LIMIT 1
					");
					// drop the last character for the next loop
					$server_ip = substr($server_ip, 0, -1);
					// store the value to a variable if a match was found
					$Location_r = @mysqli_fetch_assoc($Location_q);
					$location = strtoupper($Location_r['CountryCode']);
				}
			}
			// compile flag image
			// first find out if this country name is the list of country names
			if(in_array($location,$country_array))
			{
				// compile country flag image
				// if country is null or unknown, use generic image
				if(($location == '') OR ($location == '--'))
				{
					$country_img = '../images/flags/none.png';
				}
				else
				{
					$country_img = '../images/flags/' . strtolower($location) . '.png';	
				}
			}
			// this country is missing!
			else
			{
				$country_img = '../images/flags/none.png';
			}
			// copy country flag onto the base background image
			$flag = imagecreatefrompng($country_img);
			imagecopy($base, $flag, 120, 20, 0, 0, 16, 11);
			
			// add text to image
			imagestring($base, 2, 400, 4, 'Players: Previous 24 Hrs', $yellow);
			imagestring($base, 2, 120, 4, 'Server Name', $yellow);
			imagestring($base, 3, 140, 18, $servername, $light);
			imagestring($base, 2, 120, 32, 'IP Address', $yellow);
			imagestring($base, 3, 120, 47, $s_explode[0], $light);
			imagestring($base, 2, 277, 32, 'Current Mode', $yellow);
			imagestring($base, 3, 277, 47, $mode_name, $light);
			imagestring($base, 2, 120, 62, 'Players', $yellow);
			imagestring($base, 3, 120, 76, $used_slots . ' / ' . $available_slots, $light);
			imagestring($base, 2, 277, 62, 'Current Map', $yellow);
			imagestring($base, 3, 277, 76, $map_name, $light);
			
			$white = imagecolorallocate($base, 255, 255, 255);
			imagecolortransparent($base, $white);
			imagealphablending($base, true);
			imagesavealpha($base, true);
			
			// compile image
			imagepng($base);
			imagedestroy($base);
		}
		// an error occurred while processing query
		else
		{
			// start outputting the image
			header('Pragma: public');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			header("Content-type: image/png");
			
			// base image
			$base = imagecreatefrompng('./images/background.png');
			imagealphablending($base, false);
			imagesavealpha($base, true);
			
			// text color
			$light = imagecolorallocate($base, 255, 255, 255);
			
			// add text to image
			imagestring($base, 4, 100, 40, 'An error occurred while processing your query.', $light);
			
			// compile image
			imagepng($base);
			imagedestroy($base);
		}
	}
	// this server id doesn't exist
	elseif(!empty($sid) && !(in_array($sid,$ServerIDs)))
	{
		// start outputting the image
		header('Pragma: public');
		header('Cache-Control: max-age=0');
		header('Expires: 0');
		header("Content-type: image/png");
		
		// base image
		$base = imagecreatefrompng('./images/background.png');
		imagealphablending($base, false);
		imagesavealpha($base, true);
		
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		
		// add text to image
		imagestring($base, 4, 130, 40, 'The entered Server ID doesn\'t exist.', $light);
		
		// compile image
		imagepng($base);
		imagedestroy($base);
	}
	// there is no server id number in the url query string
	else
	{
		// start outputting the image
		header('Pragma: public');
		header('Cache-Control: max-age=0');
		header('Expires: 0');
		header("Content-type: image/png");
		
		// base image
		$base = imagecreatefrompng('./images/background.png');
		imagealphablending($base, false);
		imagesavealpha($base, true);
		
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		
		// add text to image
		imagestring($base, 4, 200, 40, 'Server ID required.', $light);
		
		// compile image
		imagepng($base);
		imagedestroy($base);
	}
// extension doesn't exist. show error image
}
else
{
	// start outputting the image
	header('Pragma: public');
	header('Cache-Control: max-age=0');
	header('Expires: 0');
	header("Content-type: image/png");
	
	echo file_get_contents('./images/error.png');
}
?>
