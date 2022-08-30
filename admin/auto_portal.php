<?php

error_reporting(E_ALL);

$pwa_tests = 1; # progressive web apps, or programmers with attitude

$manual_run = 1; # 1 or 0, like boolean [REVIEW]

print "
<html>
<head>
	<script type=\"text/javascript\" SRC=\"js_main.js\"></script>
</head>
<body>
	";

	print "
	<p>This is auto_portal.php</p>
	<span id=\"manual_output\"></span>

	<script>
	
	var manual_run = $manual_run;
	";

	if ($pwa_tests)
		print_pwa_js();
	else
	{
		print_js();
		print "
		check_requests();
		";
	}
	
	print "
	</script>
	
</body>
</html>
";
	
function print_js()
{
	print "
		
	function check_requests()
	{
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			console.log('GetXmlHttpObject() returned null!!');
		var url = 'portal_ajax.php?op=check';
		url = url + '&ran=' + Math.random();
		xmlHttp2.onreadystatechange = stateChanged_check_requests;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
		
	function stateChanged_check_requests()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			var bits = resptxt.split('|');
			if (bits[0] == 'ok')
			{
				if (manual_run)
					document.getElementById('manual_output').innerHTML = '<h1>Manual Output</h1>' + bits[1];
			}
			else
			{
				var err_msg = '';
				if (bits.length == 1)
					err_msg = bits[0];
				else
					err_msg = bits[1];
				alert('Error: ' + err_msg);
				if (manual_run)
					document.getElementById('manual_output').innerHTML = '<h1>Manual Output</h1>' + err_msg;
			}
		}
	}
	
	";
} # print_js()

function print_pwa_js()
{
	print "
	
	console.log('Hello from PWA');
	
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/vilcol/web/sw.js').then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
    }, function(err) {
      // registration failed :(
      console.log('ServiceWorker registration failed: ', err);
    });
  });
}

	console.log('Goodbye from PWA');
	";
} # print_pwa_js()

?>
