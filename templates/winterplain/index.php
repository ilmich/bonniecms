<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="description"/>
<meta name="keywords" content="keywords"/> 
<meta name="author" content="author"/>
<?php echo getHtmlHeaders();?> 
<title><?php echo SITE_NAME." - ".SITE_SLOGAN; ?></title>
<link rel="stylesheet" href="<?php echo WEB_ROOT."templates/winterplain/"?>style.css" type="text/css" media="screen" />
</head>

<body>

<div id="container">

	<div id="top">

		<div class="left">
			<h1 id="site_title"><?php echo SITE_NAME; ?></h1>
			<div id="site_description"><?php echo SITE_SLOGAN; ?></div>
		</div>	

		<div class="clearer"></div>

	</div>

	<div class="path" id="nav">

		<?php echo buildMenu('top_menu'); ?>
		<div class="clearer"></div>

	</div>

	<div id="main">

		<div class="left" id="main_left">

			<?php echo $mainBody; ?>
			
		</div>		

		<div class="clearer"></div>

	</div>

	<div id="footer">

		<div class="left">
			&copy; 2010 <a href="#">YourSite.com</a> | <a href="#top">Go to top</a>
		</div>

		<div class="right"><a href="http://templates.arcsin.se/">Website template</a> by <a href="http://arcsin.se/">Arcsin</a></div>

		<div class="clearer"></div>
	
	</div>

</div>
</body>
</html>