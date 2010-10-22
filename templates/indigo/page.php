<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html><head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<meta name="description" content="description">
<meta name="keywords" content="keywords"> 
<meta name="author" content="author"> 
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT."templates/indigo/default.css"?>" 
media="screen">
<title><?php echo SITE_NAME." - ".SITE_SLOGAN; ?></title>
</head><body>

<div class="container">

	<div class="header">
		
		<div class="title">
			<h1><?php echo SITE_NAME; ?></h1>
		</div>

		<div class="navigation">
			<?php echo buildMenu("top_menu",MENU_SIMPLE); ?>
			<div class="clearer"><span></span></div>
		</div>

	</div>

	<div class="main">
		
		<div class="content">

			<h1><?php echo $title; ?></h1>
			<?php echo $content; ?>
		</div>

		<div class="sidenav">			

			<h1>Something</h1>
			<?php echo buildMenu("right_menu"); ?>

		</div>
	
		<div class="clearer"><span></span></div>

	</div>

</div>

<div class="footer">© 2006 <a 
href="http://www.openwebdesign.org/design/3711/Indigo/index.html">Website.com</a>.
 Valid <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
 &amp; <a href="http://validator.w3.org/check?uri=referer">XHTML</a>. 
Template design by <a href="http://arcsin.se/">Arcsin</a> | Courtesy <a 
href="http://www.openwebdesign.org/" target="_blank">Open Web Design</a>,
 Thanks to <a href="http://www.lessnau.com/2009/09/godaddy-promo-codes/"
 target="_blank">Promo Code</a>
</div>

</body></html>