<?php
# Not using old style DOCTYPE because we want to use HTML 5


$redis = new Redis();
$redis->connect('localhost', '6379');
echo "Server is running: ".$redis->ping();

printD("
<!DOCTYPE html>
<html lang=\"en\">
<head>
<meta charset=\"utf-8\" />

<!-- Google tag (gtag.js) -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id=G-67Q7QMKMSK\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-67Q7QMKMSK');
</script>


");
#<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
global $customer_url;
global $filedrag;
global $no_header;
global $onload;
global $onkeydown;
global $page_title;
global $screen_width;
global $site_domain;
global $USER;
$logged_in = (($USER && $USER['USER_ID']) ? true : false);

# Scrolling:
# Insert the following line:
#		$onload = "onload=\"set_scroll();\"";
# before this line:
#		screen_layout();
# Also add the following to the form that is submitted:
#		input_hidden('scroll_x', '')
#		input_hidden('scroll_y', '')
# And call JS function save_scroll() just before the page is reloaded.
# JS functions: get_scroll() and save_scroll() in js_main.js, and set_scroll() in header.php.

printD("
<title id=\"page_title\">$page_title</title>
<meta name=\"Author\" content=\"RD Research Ltd (c) 2015\" />
<meta name=\"description\" content=\"\" />
<meta name=\"keywords\" content=\"\" />
<meta name=\"revisit\" content=\"20 days\" />
<meta http-equiv=\"Content-Language\" content=\"en-GB\" />
<link href=\"./vilcol.css\" rel=\"stylesheet\" type=\"text/css\">
<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"js_calendar/skins/aqua/theme.css\" title=\"win2k-cold-1\" />
<script type=\"text/javascript\" SRC=\"js_main.js\"></script>
<script type=\"text/javascript\">
	function set_scroll()
	{
		var x = \"" . (isset($_POST['scroll_x']) ? floatval(post_val('scroll_x')) : '-1') . "\";
		var y = \"" . (isset($_POST['scroll_y']) ? floatval(post_val('scroll_y')) : '-1') . "\";
		if ((x >= 0) && (y >= 0))
			window.scrollTo(x, y);
	}
</script>
<script type=\"text/javascript\" src=\"js_calendar/calendar.js\"></script>
<script type=\"text/javascript\" src=\"js_calendar/lang/calendar-en.js\"></script>
<script type=\"text/javascript\" src=\"js_calendar/calendar-setup.js\"></script>
");
if ($filedrag)
{
	$printD("
	<link href=\"./filedrag.css\" rel=\"stylesheet\" type=\"text/css\">
	");
}
printD("
	<link rel=\"stylesheet\" href=\"spell/spellcheck.min.css\">
	<script src=\"spell/spellcheck.min.js\"></script>
</head>

<body $onload $onkeydown onbeforeunload=\"return js_onunload();\">
	<div id=\"page_top\"></div>
");

global $site_local; # settings.php
global $site_forge; # settings.php
global $button_colour;
if ($site_local)
	printD("
		<div style=\"background-color:pink;\"><div style=\"width:50%; margin:auto; font-weight:bold;\">* * * LOCAL SYSTEM * * *</div></div>
		<br>
		");


if (!$no_header)
{
	printD("
	<div id=\"banner\">
	<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"{$screen_width}px\" height=\"100px\">
		<tr>
			<td style=\"text-align:left; vertical-align:bottom;\">
				<table height=\"70px\">
					<tr>
						<td style='vertical-align: top;'> <span style='padding:2px; font-family: Arial,Helvetica,sans-serif;
font-weight: bold;
font-size: 16px;
color: $button_colour;'>Vilcol Database ");
						if ($site_forge) {
							printD("FORGE EDITION");
							}
						printD("</span>
							</td>
					</tr>
				</table>
			
				");
				if ($logged_in)
					navi_1_heading();
				printD("
			<td style=\"width:175px; text-align:right; vertical-align:middle;\">
				<img style=\"text-align:right; vertical-align:bottom;\" 
					width=\"335\"
					height=\"88\"
					alt=\"Vilcol Logo\"
					src=\"images/vilcol_logo.jpg\"
				>
			</td>
			<td width=\"20\"></td>
		</tr>
	</table>
	</div><!--banner-->
	<div style=\"height:2px;\">&nbsp;</div>
	");
} /* $no_header */

?>
