<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="refresh" content="60"> <!-- Refresh every 1 minutes -->
		<link rel="stylesheet" href="css/BaseStyles.css">
		<link rel="stylesheet" href="css/ben.bootstrap.css">
		<link rel="stylesheet" href="css/common.css">
		<link rel="stylesheet" href="css/jquery-ui.css">
		<link rel="stylesheet" href="css/server-check.css">
	</head>
	<body>
		<header>
			<div class="navbar">
				<div class="navbar-inner">
					<a href="https://ben.productplacement.com"><img src="img/logo-main.png" alt="Branded Entertainment Network" class="pull-left"></a>
					<ul class="nav">
						<li class="active"><a href="https://ben.productplacement.com">GO TO BEN!</a></li>
					</ul>
				</div>
			</div>
		</header>
		<div class="row-fluid">
			<div id="prodServers" class="span8">
				<table class="secondary">
					<thead>
						<tr>
							<th id="tblHead" style="width: 100%; float:left;">PRODUCTION SERVERS:</th>
						</tr>
					</thead>
					<tbody id="override">
						<tr id="topRow">
							<td>Server Name:</td>
							<td>URL: </td>
							<td>UP/DOWN: </td>
						</tr>
							<?php

							$ipAddy = array(  
							"ProdWebPool" => "https://ben.productplacement.com",
							"ProdWeb1" => "http://prd1.ben.productplacement.com",
							"ProdWeb2" => "http://prd2.ben.productplacement.com",
							"ProdApiPool" => "http://api.productplacement.com",
							"ProdApi1" => "http://prd1.api.productplacement.com",
							"ProdApi2" => "http://prd2.api.productplacement.com",
							"ProdApi3" => "http://prd3.api.productplacement.com",
							);
							foreach ($ipAddy as $key => $value) {
									$response = file_get_contents($value);
									if( !$response ){
										//Do this if no response
										echo( "<tr style='color:red; font-weight:bold;' id='" . $key . "'><td class='key'>" . $key . "</td><td class='url'><a href='" . $value . "' target='_blank'>" . $value . "</a></td><td><img id='redgreen' src='img/redLight.png'></td></tr>");
									}	
									if( $response ){
										//Do this if we get a response
										echo( "<tr id='" . $key . "'><td class='key'>" . $key . "</td><td class='url'><a style='color:blue' href='" . $value . "' target='_blank'>" . $value . "</a></td><td><img id='redgreen' src='img/greenLight.png'></td></tr>" );
									}
								}
							?>					 
					</tbody>
				</table>
			</div>
		</div>
		<footer>
			<div style="background-color: #eee; padding: 20px 30px;">
				<div class="row-fluid">
					<div class="span3">
						<strong>BEN</strong><br>
						<a href="/CustomerService/HowItWorks" class="alternative">How It Works</a>
					</div>
					<div class="span3">
						<strong>Customer Service</strong><br>
						<a href="/CustomerService/ContactUs" class="alternative">Contact Us</a><br>
						<a href="/CustomerService/Help" class="alternative">FAQs</a><br>
						<a href="/CustomerService/SiteMap" class="alternative">Site Map</a>
					</div>
				<div class="span3">
				<strong>Our Policies</strong><br>
				<a href="/CustomerService/Usage" class="alternative">Site Usage Agreement</a><br>
				<a href="/CustomerService/Privacy" class="alternative">Privacy Policy</a><br>
				<a href="/CustomerService/Cookie" class="alternative">Cookie Policy</a><br>
				</div>
				</div>
			</div>
			<div style="padding: 20px 30px; border-top: 1px solid rgba(0, 0, 0, .2)">
				<div class="row-fluid">
					<strong class="text-smaller">Corbis sites:</strong>
				</div>
				<div class="row-fluid text-smaller corbis-brands">
					<div class="span3">
						<a href="http://corbisimages.com/"><i class="icon-logo-corbis-images"></i></a><br>
						<span>Exceptional visual images for inspired ideas and stories <a href="http://corbisimages.com/" class="red-text">&gt;</a></span>
					</div>
					<div class="span3">
						<a href="http://corbismotion.com/"><i class="icon-logo-corbis-motion"></i></a><br>
						<span>Creative and editorial motion clips from the world's leading collections and studios <a href="http://corbismotion.com/" class="red-text">&gt;</a></span>
					</div>
					<div class="span3">
						<a href="http://corbisentertainment.com//"><i class="icon-logo-corbis-entertainment"></i></a><br>
						<span>A full service agency for Film/TV product integration, music licensing and talent negotiations <a href="http://corbisentertainment.com//" class="red-text">&gt;</a></span>
					</div>
					<div class="span3">
						<a href="http://veer.com/"><i class="icon-logo-veer"></i></a><br>
						<span>Unique photography, illustrations, and fonts hand-selected by creatives, for creatives <a href="http://veer.com/" class="red-text">&gt;</a></span>
					</div>
				</div>
				<div class="row-fluid" style="margin-top: 40px;">
					<p style="margin-bottom: 5px">Powered by Corbis Entertainment</p>
					<p class="text-smaller alternative">© 2013 by BEN, a <a href="http://corporate.corbis.com/" target="_blank" class="red-text">Corbis Corporation</a> brand. All media © by BEN and/or its media providers. All Rights Reserved.</p>
				</div>
			</div>
		</footer>	
	</body>
</html>