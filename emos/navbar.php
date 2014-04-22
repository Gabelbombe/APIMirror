<?php
$thisEnv=array_shift(explode(".",$_SERVER['HTTP_HOST']));
switch($thisEnv){
	case "dev":
	case "dev1":
	case "dev2":
		$url="http://dev.ben.productplacement.corbis.pre/tools";
		break;
	case "sqa":
	case "sqa1":
	case "sqa2":
		$url="http://sqa.ben.productplacement.corbis.pre/tools";
		break;
	case "stg":
	case "stg1":
	case "stg2":
		$url="http://stg.ben.productplacement.com/tools";
		break;
	case "prd":
	case "prd1":
	case "prd2":
	case "productplacement":
		$url="http://ben.productplacement.com/tools";
		break;
}
?>
	<div class="navbar">
		<div class="navbar-inner">
			<a title="BEN Tools Home" href="<?=$url;?>"><img src="Images/logo-main.png" alt="Branded Entertainment Network" class="pull-left" /></a> <a title="BEN Tools Home" href="<?=$url;?>"><img src="Images/admin.png" height="45" alt="Admin panel" /></a>
		</div>
	</div>