<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// if there is a server id or a page value, this is not the index page
// show menu
if(!empty($ServerID) || !empty($page))
{
	echo '
	<div id="menucontent">
	';
	// player column
	echo '
	<div class="menuitems';
	if($page == 'player')
	{
		echo 'elected';
	}
	echo '" style="width: 19%">
	<form id="ajaxsearch" action="' . $_SERVER['PHP_SELF'] . '" method="get">
	&nbsp; <span class="information">Player:</span>
	<input type="hidden" name="p" value="player" />
	';
	if(!empty($ServerID))
	{
		echo '<input type="hidden" name="sid" value="' . $ServerID . '" />';
	}
	echo '
	<input id="soldiers" type="text" class="inputbox" ';
	// try to fill in search box
	if(!empty($SoldierName))
	{
		echo 'value="' . $SoldierName . '" ';
	}
	echo 'name="player" style="font-size: 12px;"/>
	</form>
	</div>
	';
	// home column
	echo '
	<div class="menuitems';
	if(($page == 'home') || empty($page))
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=home';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Home</a>
	</div>
	';
	// leaders column
	echo '
	<div class="menuitems';
	if($page == 'leaders')
	{
		echo 'elected';
	}
	echo '" style="width: 13%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=leaders';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Leaderboard</a>
	</div>
	';
	// suspicious column
	echo '
	<div class="menuitems';
	if($page == 'suspicious')
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=suspicious';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Suspicious</a>
	</div>
	';
	// chat column
	echo '
	<div class="menuitems';
	if($page == 'chat')
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=chat';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Chat</a>
	</div>
	';
	// countries column
	echo '
	<div class="menuitems';
	if($page == 'countries')
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=countries';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Countries</a>
	</div>
	';
	// maps column
	echo '
	<div class="menuitems';
	if($page == 'maps')
	{
		echo 'elected';
	}
	echo '" style="width: 11%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=maps';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Maps</a>
	</div>
	';
	// server info column
	echo '
	<div class="menuitems';
	if($page == 'server')
	{
		echo 'elected';
	}
	echo '" style="width: 13%">
	<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=server';
	if(!empty($ServerID))
	{
		echo '&amp;sid=' . $ServerID;
	}
	echo '">Server Info</a>
	</div>
	</div>
	';
}
?>