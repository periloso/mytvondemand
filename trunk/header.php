<? include 'functions.php'; ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>MyTV On Demand - v1.0</title>
		<link rel="stylesheet" href="/components/display.css" type="text/css" />
		<link type="text/css" href="/components/smoothness/jquery-ui-1.8.5.custom.css" rel="stylesheet" />	
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<script type="text/javascript" src="/components/jquery-1.4.2.js" charset="utf-8"></script>
		<script type="text/javascript" src="/components/jquery-ui-1.8.5.full.min.js"></script>
		<script type="text/javascript" src="/components/mytvod.js" charset="utf-8"></script>
	</head>
	<body>
		<div class="container">
			<div class="header">
				<div class="entries">
					<ul class="entry">
						<li>
							<a href="/">
								<span class="title">Home</span><br />
								<span class="description">start here</span>
							</a>
						</li>
					</ul>
					<ul class="entry">
						<li>
							<a href="/channels.php">
								<span class="title">Shows</span><br />
								<span class="description">show tv series</span>
							</a>
						</li>
					</ul>
					<ul class="entry">
						<li>
							<a href="/subscriptions.php">
								<span class="title">Subscribed</span><br />
								<span class="description">show subscribed shows</span>
							</a>
						</li>
					</ul>
					<ul class="entry">
						<li>
							<a href="/downloads.php">
								<span class="title">Downloads</span><br />
								<span class="description">show downloads</span>
							</a>
						</li>
					</ul>
					<ul class="entry">
						<li>
							<a id="configuration" href="/configuration.php">
								<span class="title">Configuration</span><br />
								<span class="description">change parameters</span>
							</a>
						</li>
					</ul>
					<!--<ul class="entry">
						<li>
							<a id="login" href="/login.php">
								<span class="title">Login</span><br />
								<span class="description">login!</span>
							</a>
						</li>
					</ul>-->
				</div>
			</div>
			<div class="content">
