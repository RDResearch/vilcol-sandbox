<?php

# mailres.php - Mail Research, from the System screen

include_once("settings.php");
include_once("library.php");
include_once("lib_pm.php");
global $navi_1_system;
global $navi_2_sys_mailres;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (global_debug())
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_mailres = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		screen_layout();
	}
	else 
		print "<p>$denial_message</p>";
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	global $at;
	
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Mail Research</h3>";
	
	dprint(post_values());

	javascript(); # must come after setting of $item etc
	
	$task = post_val('task');
	
	if ($task == 'send_phpmail')
	{
		$mailto = 'kevinbeckett1@gmail.com';
		$subject = 'Test ' . date_now();


//		# the contents of a .xml file exported from Word as "2003 XML"
//		$message = xml_2003_file();
		
		
//		# the contents of a .xml file exported from Word as "XML"
//		$message = xml_file();
		
		
//		# the contents of a .mht file exported from Word as a "single web page"
//		$message = web_archive_file(); 

		
//		# the contents of a .xml file exported from Word as "XML"
//		$xml = new SimpleXMLElement(xml_file()); # fails
		
		
//		$doc = new DomDocument;
//		// We must validate the document before referring to the id
//		$doc->validateOnParse = true;
//		$doc->Load('skycue.xml');
//		echo "The element whose id is myelement is: " . 
//		$doc->getElementById('myelement')->tagName . "\n";
		
		
		# basic test email
		$message = '<html><body>';
			$message .= '<h3>Test message sent at <u>' . date_now() . '</u> from mailres.php</h3>';
			$message .= "<img src=\"http://www.rdresearch.co.uk/viltest/images/skycue.docx\">Word Doc</img>";
			#$message .= "<img src=\"http://www.rdresearch.co.uk/viltest/images/skycue.png\">Download pictures!</img>";
			#$message .= "<iframe src=\"http://www.rdresearch.co.uk/viltest/images/skycue.png\">No iframe!</iframe>";
		$message .= "</body></html>";

		
		$from = 'mailres@rdresearch.co.uk';
		$attachment_file = '';
		$attachment_name = '';
		$bcc = '';
		$image_paths = '';
		$image_names = '';
		mail_pm($mailto, $subject, $message, $from, 'Test Send', $attachment_file, $attachment_name, $bcc,
					$image_paths, $image_names);
	}
	elseif ($task)
		dprint("Unrecognised task \"$task\"");
	
	$shape = 'style="width:100px; height:60px;"';
	$gap = '&nbsp;&nbsp;';
	print "
	<form name=\"form_mailres\" action=\"" . server_php_self() . "\" method=\"post\">
	<br>
	" .
	input_hidden('task', '') .
	"
	<table>
	<tr>
		<td $at>" . input_button("Send Mail via\rphpMailer", "send_pm();", $shape) . "</td>
		$gap
	</tr>
	</table>
	</form><!--form_mailres-->
	";
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function javascript()
{
	#global $item;
	#global $item_id;

	print "
	<script type=\"text/javascript\">
	
	function send_pm()
	{
		document.form_mailres.task.value = 'send_phpmail';
		document.form_mailres.submit();
	}
	
	</script>
	";
}

function xml_2003_file()
{
	# the contents of a .xml file exported from Word as "2003 XML"
	return '
<html>
<body>

<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<?mso-application progid="Word.Document"?>
<w:wordDocument xmlns:aml="http://schemas.microsoft.com/aml/2001/core" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml" xmlns:wx="http://schemas.microsoft.com/office/word/2003/auxHint" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wsp="http://schemas.microsoft.com/office/word/2003/wordml/sp2" xmlns:sl="http://schemas.microsoft.com/schemaLibrary/2003/core" w:macrosPresent="no" w:embeddedObjPresent="no" w:ocxPresent="no" xml:space="preserve"><w:ignoreSubtree w:val="http://schemas.microsoft.com/office/word/2003/wordml/sp2"/><o:DocumentProperties><o:Author>Joe Dyble</o:Author><o:LastAuthor>Kevin Beckett</o:LastAuthor><o:Revision>2</o:Revision><o:TotalTime>5</o:TotalTime><o:LastPrinted>2015-05-26T08:36:00Z</o:LastPrinted><o:Created>2016-05-04T10:13:00Z</o:Created><o:LastSaved>2016-05-04T10:13:00Z</o:LastSaved><o:Pages>2</o:Pages><o:Words>739</o:Words><o:Characters>4216</o:Characters><o:Company>Hewlett-Packard Company</o:Company><o:Lines>35</o:Lines><o:Paragraphs>9</o:Paragraphs><o:CharactersWithSpaces>4946</o:CharactersWithSpaces><o:Version>14</o:Version></o:DocumentProperties><w:fonts><w:defaultFonts w:ascii="Calibri" w:fareast="Calibri" w:h-ansi="Calibri" w:cs="Times New Roman"/><w:font w:name="Times New Roman"><w:panose-1 w:val="02020603050405020304"/><w:charset w:val="00"/><w:family w:val="Roman"/><w:pitch w:val="variable"/><w:sig w:usb-0="E0002EFF" w:usb-1="C0007843" w:usb-2="00000009" w:usb-3="00000000" w:csb-0="000001FF" w:csb-1="00000000"/></w:font><w:font w:name="Arial"><w:panose-1 w:val="020B0604020202020204"/><w:charset w:val="00"/><w:family w:val="Swiss"/><w:pitch w:val="variable"/><w:sig w:usb-0="E0002EFF" w:usb-1="C0007843" w:usb-2="00000009" w:usb-3="00000000" w:csb-0="000001FF" w:csb-1="00000000"/></w:font><w:font w:name="Courier New"><w:panose-1 w:val="02070309020205020404"/><w:charset w:val="00"/><w:family w:val="Modern"/><w:pitch w:val="fixed"/><w:sig w:usb-0="E0002EFF" w:usb-1="C0007843" w:usb-2="00000009" w:usb-3="00000000" w:csb-0="000001FF" w:csb-1="00000000"/></w:font><w:font w:name="Symbol"><w:panose-1 w:val="05050102010706020507"/><w:charset w:val="02"/><w:family w:val="Roman"/><w:pitch w:val="variable"/><w:sig w:usb-0="00000000" w:usb-1="10000000" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="80000000" w:csb-1="00000000"/></w:font><w:font w:name="Wingdings"><w:panose-1 w:val="05000000000000000000"/><w:charset w:val="02"/><w:family w:val="auto"/><w:pitch w:val="variable"/><w:sig w:usb-0="00000000" w:usb-1="10000000" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="80000000" w:csb-1="00000000"/></w:font><w:font w:name="Cambria Math"><w:panose-1 w:val="02040503050406030204"/><w:charset w:val="00"/><w:family w:val="Roman"/><w:pitch w:val="variable"/><w:sig w:usb-0="E00002FF" w:usb-1="420024FF" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="0000019F" w:csb-1="00000000"/></w:font><w:font w:name="Calibri"><w:panose-1 w:val="020F0502020204030204"/><w:charset w:val="00"/><w:family w:val="Swiss"/><w:pitch w:val="variable"/><w:sig w:usb-0="E00002FF" w:usb-1="4000ACFF" w:usb-2="00000001" w:usb-3="00000000" w:csb-0="0000019F" w:csb-1="00000000"/></w:font><w:font w:name="Segoe UI"><w:panose-1 w:val="020B0502040204020203"/><w:charset w:val="00"/><w:family w:val="Swiss"/><w:pitch w:val="variable"/><w:sig w:usb-0="E4002EFF" w:usb-1="C000E47F" w:usb-2="00000009" w:usb-3="00000000" w:csb-0="000001FF" w:csb-1="00000000"/></w:font><w:font w:name="Arial Narrow"><w:panose-1 w:val="020B0606020202030204"/><w:charset w:val="00"/><w:family w:val="Swiss"/><w:pitch w:val="variable"/><w:sig w:usb-0="00000287" w:usb-1="00000800" w:usb-2="00000000" w:usb-3="00000000" w:csb-0="0000009F" w:csb-1="00000000"/></w:font></w:fonts><w:lists><w:listDef w:listDefId="0"><w:lsid w:val="06F22240"/><w:plt w:val="HybridMultilevel"/><w:tmpl w:val="50A2D92E"/><w:lvl w:ilvl="0" w:tplc="08090001"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:listDef><w:listDef w:listDefId="1"><w:lsid w:val="128F206B"/><w:plt w:val="HybridMultilevel"/><w:tmpl w:val="AAA87FDE"/><w:lvl w:ilvl="0" w:tplc="0409000F"><w:start w:val="1"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="04090019" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="4"/><w:lvlText w:val="%2."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="0409001B" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="2"/><w:lvlText w:val="%3."/><w:lvlJc w:val="right"/><w:pPr><w:ind w:left="2160" w:hanging="180"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="0409000F" w:tentative="on"><w:start w:val="1"/><w:lvlText w:val="%4."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="04090019" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="4"/><w:lvlText w:val="%5."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="0409001B" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="2"/><w:lvlText w:val="%6."/><w:lvlJc w:val="right"/><w:pPr><w:ind w:left="4320" w:hanging="180"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="0409000F" w:tentative="on"><w:start w:val="1"/><w:lvlText w:val="%7."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="04090019" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="4"/><w:lvlText w:val="%8."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="0409001B" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="2"/><w:lvlText w:val="%9."/><w:lvlJc w:val="right"/><w:pPr><w:ind w:left="6480" w:hanging="180"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl></w:listDef><w:listDef w:listDefId="2"><w:lsid w:val="76766BA8"/><w:plt w:val="HybridMultilevel"/><w:tmpl w:val="AF2CA9AA"/><w:lvl w:ilvl="0" w:tplc="08090001"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:h-ansi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:h-ansi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="on"><w:start w:val="1"/><w:nfc w:val="23"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:h-ansi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:listDef><w:list w:ilfo="1"><w:ilst w:val="1"/></w:list><w:list w:ilfo="2"><w:ilst w:val="2"/></w:list><w:list w:ilfo="3"><w:ilst w:val="0"/></w:list></w:lists><w:styles><w:versionOfBuiltInStylenames w:val="7"/><w:latentStyles w:defLockedState="off" w:latentStyleCount="267"><w:lsdException w:name="Normal"/><w:lsdException w:name="heading 1"/><w:lsdException w:name="heading 2"/><w:lsdException w:name="heading 3"/><w:lsdException w:name="heading 4"/><w:lsdException w:name="heading 5"/><w:lsdException w:name="heading 6"/><w:lsdException w:name="heading 7"/><w:lsdException w:name="heading 8"/><w:lsdException w:name="heading 9"/><w:lsdException w:name="toc 1"/><w:lsdException w:name="toc 2"/><w:lsdException w:name="toc 3"/><w:lsdException w:name="toc 4"/><w:lsdException w:name="toc 5"/><w:lsdException w:name="toc 6"/><w:lsdException w:name="toc 7"/><w:lsdException w:name="toc 8"/><w:lsdException w:name="toc 9"/><w:lsdException w:name="caption"/><w:lsdException w:name="Title"/><w:lsdException w:name="Default Paragraph Font"/><w:lsdException w:name="Subtitle"/><w:lsdException w:name="Hyperlink"/><w:lsdException w:name="Strong"/><w:lsdException w:name="Emphasis"/><w:lsdException w:name="Table Grid"/><w:lsdException w:name="Placeholder Text"/><w:lsdException w:name="No Spacing"/><w:lsdException w:name="Light Shading"/><w:lsdException w:name="Light List"/><w:lsdException w:name="Light Grid"/><w:lsdException w:name="Medium Shading 1"/><w:lsdException w:name="Medium Shading 2"/><w:lsdException w:name="Medium List 1"/><w:lsdException w:name="Medium List 2"/><w:lsdException w:name="Medium Grid 1"/><w:lsdException w:name="Medium Grid 2"/><w:lsdException w:name="Medium Grid 3"/><w:lsdException w:name="Dark List"/><w:lsdException w:name="Colorful Shading"/><w:lsdException w:name="Colorful List"/><w:lsdException w:name="Colorful Grid"/><w:lsdException w:name="Light Shading Accent 1"/><w:lsdException w:name="Light List Accent 1"/><w:lsdException w:name="Light Grid Accent 1"/><w:lsdException w:name="Medium Shading 1 Accent 1"/><w:lsdException w:name="Medium Shading 2 Accent 1"/><w:lsdException w:name="Medium List 1 Accent 1"/><w:lsdException w:name="Revision"/><w:lsdException w:name="List Paragraph"/><w:lsdException w:name="Quote"/><w:lsdException w:name="Intense Quote"/><w:lsdException w:name="Medium List 2 Accent 1"/><w:lsdException w:name="Medium Grid 1 Accent 1"/><w:lsdException w:name="Medium Grid 2 Accent 1"/><w:lsdException w:name="Medium Grid 3 Accent 1"/><w:lsdException w:name="Dark List Accent 1"/><w:lsdException w:name="Colorful Shading Accent 1"/><w:lsdException w:name="Colorful List Accent 1"/><w:lsdException w:name="Colorful Grid Accent 1"/><w:lsdException w:name="Light Shading Accent 2"/><w:lsdException w:name="Light List Accent 2"/><w:lsdException w:name="Light Grid Accent 2"/><w:lsdException w:name="Medium Shading 1 Accent 2"/><w:lsdException w:name="Medium Shading 2 Accent 2"/><w:lsdException w:name="Medium List 1 Accent 2"/><w:lsdException w:name="Medium List 2 Accent 2"/><w:lsdException w:name="Medium Grid 1 Accent 2"/><w:lsdException w:name="Medium Grid 2 Accent 2"/><w:lsdException w:name="Medium Grid 3 Accent 2"/><w:lsdException w:name="Dark List Accent 2"/><w:lsdException w:name="Colorful Shading Accent 2"/><w:lsdException w:name="Colorful List Accent 2"/><w:lsdException w:name="Colorful Grid Accent 2"/><w:lsdException w:name="Light Shading Accent 3"/><w:lsdException w:name="Light List Accent 3"/><w:lsdException w:name="Light Grid Accent 3"/><w:lsdException w:name="Medium Shading 1 Accent 3"/><w:lsdException w:name="Medium Shading 2 Accent 3"/><w:lsdException w:name="Medium List 1 Accent 3"/><w:lsdException w:name="Medium List 2 Accent 3"/><w:lsdException w:name="Medium Grid 1 Accent 3"/><w:lsdException w:name="Medium Grid 2 Accent 3"/><w:lsdException w:name="Medium Grid 3 Accent 3"/><w:lsdException w:name="Dark List Accent 3"/><w:lsdException w:name="Colorful Shading Accent 3"/><w:lsdException w:name="Colorful List Accent 3"/><w:lsdException w:name="Colorful Grid Accent 3"/><w:lsdException w:name="Light Shading Accent 4"/><w:lsdException w:name="Light List Accent 4"/><w:lsdException w:name="Light Grid Accent 4"/><w:lsdException w:name="Medium Shading 1 Accent 4"/><w:lsdException w:name="Medium Shading 2 Accent 4"/><w:lsdException w:name="Medium List 1 Accent 4"/><w:lsdException w:name="Medium List 2 Accent 4"/><w:lsdException w:name="Medium Grid 1 Accent 4"/><w:lsdException w:name="Medium Grid 2 Accent 4"/><w:lsdException w:name="Medium Grid 3 Accent 4"/><w:lsdException w:name="Dark List Accent 4"/><w:lsdException w:name="Colorful Shading Accent 4"/><w:lsdException w:name="Colorful List Accent 4"/><w:lsdException w:name="Colorful Grid Accent 4"/><w:lsdException w:name="Light Shading Accent 5"/><w:lsdException w:name="Light List Accent 5"/><w:lsdException w:name="Light Grid Accent 5"/><w:lsdException w:name="Medium Shading 1 Accent 5"/><w:lsdException w:name="Medium Shading 2 Accent 5"/><w:lsdException w:name="Medium List 1 Accent 5"/><w:lsdException w:name="Medium List 2 Accent 5"/><w:lsdException w:name="Medium Grid 1 Accent 5"/><w:lsdException w:name="Medium Grid 2 Accent 5"/><w:lsdException w:name="Medium Grid 3 Accent 5"/><w:lsdException w:name="Dark List Accent 5"/><w:lsdException w:name="Colorful Shading Accent 5"/><w:lsdException w:name="Colorful List Accent 5"/><w:lsdException w:name="Colorful Grid Accent 5"/><w:lsdException w:name="Light Shading Accent 6"/><w:lsdException w:name="Light List Accent 6"/><w:lsdException w:name="Light Grid Accent 6"/><w:lsdException w:name="Medium Shading 1 Accent 6"/><w:lsdException w:name="Medium Shading 2 Accent 6"/><w:lsdException w:name="Medium List 1 Accent 6"/><w:lsdException w:name="Medium List 2 Accent 6"/><w:lsdException w:name="Medium Grid 1 Accent 6"/><w:lsdException w:name="Medium Grid 2 Accent 6"/><w:lsdException w:name="Medium Grid 3 Accent 6"/><w:lsdException w:name="Dark List Accent 6"/><w:lsdException w:name="Colorful Shading Accent 6"/><w:lsdException w:name="Colorful List Accent 6"/><w:lsdException w:name="Colorful Grid Accent 6"/><w:lsdException w:name="Subtle Emphasis"/><w:lsdException w:name="Intense Emphasis"/><w:lsdException w:name="Subtle Reference"/><w:lsdException w:name="Intense Reference"/><w:lsdException w:name="Book Title"/><w:lsdException w:name="Bibliography"/><w:lsdException w:name="TOC Heading"/></w:latentStyles><w:style w:type="paragraph" w:default="on" w:styleId="Normal"><w:name w:val="Normal"/><w:rsid w:val="00FB687E"/><w:pPr><w:spacing w:after="200" w:line="276" w:line-rule="auto"/></w:pPr><w:rPr><wx:font wx:val="Calibri"/><w:sz w:val="22"/><w:sz-cs w:val="22"/><w:lang w:val="EN-GB" w:fareast="EN-US" w:bidi="AR-SA"/></w:rPr></w:style><w:style w:type="character" w:default="on" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/></w:style><w:style w:type="table" w:default="on" w:styleId="TableNormal"><w:name w:val="Normal Table"/><wx:uiName wx:val="Table Normal"/><w:rPr><wx:font wx:val="Calibri"/><w:lang w:val="EN-GB" w:fareast="EN-GB" w:bidi="AR-SA"/></w:rPr><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="list" w:default="on" w:styleId="NoList"><w:name w:val="No List"/></w:style><w:style w:type="paragraph" w:styleId="NoSpacing"><w:name w:val="No Spacing"/><w:rsid w:val="00FB687E"/><w:rPr><wx:font wx:val="Calibri"/><w:sz w:val="22"/><w:sz-cs w:val="22"/><w:lang w:val="EN-GB" w:fareast="EN-US" w:bidi="AR-SA"/></w:rPr></w:style><w:style w:type="character" w:styleId="Hyperlink"><w:name w:val="Hyperlink"/><w:rsid w:val="00FB687E"/><w:rPr><w:color w:val="0000FF"/><w:u w:val="single"/></w:rPr></w:style><w:style w:type="character" w:styleId="apple-style-span"><w:name w:val="apple-style-span"/><w:basedOn w:val="DefaultParagraphFont"/><w:rsid w:val="00DF08E0"/></w:style><w:style w:type="paragraph" w:styleId="Header"><w:name w:val="header"/><wx:uiName wx:val="Header"/><w:basedOn w:val="Normal"/><w:link w:val="HeaderChar"/><w:rsid w:val="00DF08E0"/><w:pPr><w:tabs><w:tab w:val="center" w:pos="4513"/><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:rPr><wx:font wx:val="Calibri"/></w:rPr></w:style><w:style w:type="character" w:styleId="HeaderChar"><w:name w:val="Header Char"/><w:link w:val="Header"/><w:rsid w:val="00DF08E0"/><w:rPr><w:rFonts w:ascii="Calibri" w:fareast="Calibri" w:h-ansi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Footer"><w:name w:val="footer"/><wx:uiName wx:val="Footer"/><w:basedOn w:val="Normal"/><w:link w:val="FooterChar"/><w:rsid w:val="00DF08E0"/><w:pPr><w:tabs><w:tab w:val="center" w:pos="4513"/><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:rPr><wx:font wx:val="Calibri"/></w:rPr></w:style><w:style w:type="character" w:styleId="FooterChar"><w:name w:val="Footer Char"/><w:link w:val="Footer"/><w:rsid w:val="00DF08E0"/><w:rPr><w:rFonts w:ascii="Calibri" w:fareast="Calibri" w:h-ansi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="NormalWeb"><w:name w:val="Normal (Web)"/><w:basedOn w:val="Normal"/><w:rsid w:val="002F3F4B"/><w:pPr><w:spacing w:before="100" w:before-autospacing="on" w:after="100" w:after-autospacing="on" w:line="240" w:line-rule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Times New Roman" w:h-ansi="Times New Roman"/><wx:font wx:val="Times New Roman"/><w:sz w:val="24"/><w:sz-cs w:val="24"/><w:lang w:fareast="EN-GB"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="BalloonText"><w:name w:val="Balloon Text"/><w:basedOn w:val="Normal"/><w:link w:val="BalloonTextChar"/><w:rsid w:val="00227314"/><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Segoe UI" w:h-ansi="Segoe UI" w:cs="Segoe UI"/><wx:font wx:val="Segoe UI"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:style><w:style w:type="character" w:styleId="BalloonTextChar"><w:name w:val="Balloon Text Char"/><w:link w:val="BalloonText"/><w:rsid w:val="00227314"/><w:rPr><w:rFonts w:ascii="Segoe UI" w:fareast="Calibri" w:h-ansi="Segoe UI" w:cs="Segoe UI"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:style><w:style w:type="character" w:styleId="apple-converted-space"><w:name w:val="apple-converted-space"/><w:basedOn w:val="DefaultParagraphFont"/><w:rsid w:val="00913D34"/></w:style><w:style w:type="character" w:styleId="Emphasis"><w:name w:val="Emphasis"/><w:rsid w:val="00913D34"/><w:rPr><w:i/><w:i-cs/></w:rPr></w:style></w:styles><w:divs><w:div w:id="1206288148"><w:bodyDiv w:val="on"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/></w:divBdr></w:div><w:div w:id="1361931375"><w:bodyDiv w:val="on"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/></w:divBdr></w:div><w:div w:id="1956525405"><w:bodyDiv w:val="on"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/></w:divBdr></w:div><w:div w:id="2040355805"><w:bodyDiv w:val="on"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" wx:bdrwidth="0" w:space="0" w:color="auto"/></w:divBdr></w:div></w:divs><w:shapeDefaults><o:shapedefaults v:ext="edit" spidmax="1026"/><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1"/></o:shapelayout></w:shapeDefaults><w:docPr><w:view w:val="print"/><w:zoom w:percent="100"/><w:dontDisplayPageBoundaries/><w:doNotEmbedSystemFonts/><w:defaultTabStop w:val="720"/><w:drawingGridHorizontalSpacing w:val="110"/><w:displayHorizontalDrawingGridEvery w:val="2"/><w:punctuationKerning/><w:characterSpacingControl w:val="DontCompress"/><w:optimizeForBrowser/><w:validateAgainstSchema/><w:saveInvalidXML w:val="off"/><w:ignoreMixedContent w:val="off"/><w:alwaysShowPlaceholderText w:val="off"/><w:footnotePr><w:footnote w:type="separator"><w:p wsp:rsidR="00255171" wsp:rsidRDefault="00255171" wsp:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:separator/></w:r></w:p></w:footnote><w:footnote w:type="continuation-separator"><w:p wsp:rsidR="00255171" wsp:rsidRDefault="00255171" wsp:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:continuationSeparator/></w:r></w:p></w:footnote></w:footnotePr><w:endnotePr><w:endnote w:type="separator"><w:p wsp:rsidR="00255171" wsp:rsidRDefault="00255171" wsp:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:separator/></w:r></w:p></w:endnote><w:endnote w:type="continuation-separator"><w:p wsp:rsidR="00255171" wsp:rsidRDefault="00255171" wsp:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:continuationSeparator/></w:r></w:p></w:endnote></w:endnotePr><w:compat><w:breakWrappedTables/><w:snapToGridInCell/><w:wrapTextWithPunct/><w:useAsianBreakRules/><w:dontGrowAutofit/></w:compat><wsp:rsids><wsp:rsidRoot wsp:val="00FB687E"/><wsp:rsid wsp:val="0003215D"/><wsp:rsid wsp:val="00066D3B"/><wsp:rsid wsp:val="00084DBD"/><wsp:rsid wsp:val="000A7F48"/><wsp:rsid wsp:val="000D3160"/><wsp:rsid wsp:val="000E650E"/><wsp:rsid wsp:val="00130AFC"/><wsp:rsid wsp:val="00191968"/><wsp:rsid wsp:val="001E661A"/><wsp:rsid wsp:val="0022614B"/><wsp:rsid wsp:val="00227314"/><wsp:rsid wsp:val="00236A8F"/><wsp:rsid wsp:val="0024249E"/><wsp:rsid wsp:val="00255171"/><wsp:rsid wsp:val="002667BF"/><wsp:rsid wsp:val="002B0120"/><wsp:rsid wsp:val="002E00F6"/><wsp:rsid wsp:val="002F3F4B"/><wsp:rsid wsp:val="00351DD8"/><wsp:rsid wsp:val="00365C28"/><wsp:rsid wsp:val="00394C52"/><wsp:rsid wsp:val="003A3071"/><wsp:rsid wsp:val="003E0FAA"/><wsp:rsid wsp:val="003E210B"/><wsp:rsid wsp:val="00424E13"/><wsp:rsid wsp:val="004A390C"/><wsp:rsid wsp:val="004E76CF"/><wsp:rsid wsp:val="0050181D"/><wsp:rsid wsp:val="0051005D"/><wsp:rsid wsp:val="005208C3"/><wsp:rsid wsp:val="00531D73"/><wsp:rsid wsp:val="00560C08"/><wsp:rsid wsp:val="0058058F"/><wsp:rsid wsp:val="005C51CE"/><wsp:rsid wsp:val="005E6AFA"/><wsp:rsid wsp:val="005F385E"/><wsp:rsid wsp:val="005F5D18"/><wsp:rsid wsp:val="005F6D44"/><wsp:rsid wsp:val="0060362B"/><wsp:rsid wsp:val="00637752"/><wsp:rsid wsp:val="006E68CC"/><wsp:rsid wsp:val="006F6BC7"/><wsp:rsid wsp:val="0077291F"/><wsp:rsid wsp:val="00782F42"/><wsp:rsid wsp:val="0079279C"/><wsp:rsid wsp:val="007A490C"/><wsp:rsid wsp:val="007A5E07"/><wsp:rsid wsp:val="007B6C4E"/><wsp:rsid wsp:val="007C386C"/><wsp:rsid wsp:val="007E51EC"/><wsp:rsid wsp:val="007E613B"/><wsp:rsid wsp:val="00824EE5"/><wsp:rsid wsp:val="00897398"/><wsp:rsid wsp:val="008E500D"/><wsp:rsid wsp:val="008F4B71"/><wsp:rsid wsp:val="00913D34"/><wsp:rsid wsp:val="00916D10"/><wsp:rsid wsp:val="00970A20"/><wsp:rsid wsp:val="009E4595"/><wsp:rsid wsp:val="00A35763"/><wsp:rsid wsp:val="00A51198"/><wsp:rsid wsp:val="00B05AAC"/><wsp:rsid wsp:val="00BC30BD"/><wsp:rsid wsp:val="00BE2F44"/><wsp:rsid wsp:val="00C01032"/><wsp:rsid wsp:val="00C57554"/><wsp:rsid wsp:val="00C779A4"/><wsp:rsid wsp:val="00C8562D"/><wsp:rsid wsp:val="00CD5347"/><wsp:rsid wsp:val="00CE61FD"/><wsp:rsid wsp:val="00D53BA5"/><wsp:rsid wsp:val="00DC7F85"/><wsp:rsid wsp:val="00DD65A7"/><wsp:rsid wsp:val="00DF08E0"/><wsp:rsid wsp:val="00EA5D5B"/><wsp:rsid wsp:val="00EB1362"/><wsp:rsid wsp:val="00F36651"/><wsp:rsid wsp:val="00F66DCB"/><wsp:rsid wsp:val="00FA10D1"/><wsp:rsid wsp:val="00FB687E"/><wsp:rsid wsp:val="00FB72E7"/><wsp:rsid wsp:val="00FE65E2"/></wsp:rsids></w:docPr><w:body><wx:sect><w:tbl><w:tblPr><w:tblpPr w:leftFromText="180" w:rightFromText="180" w:vertAnchor="page" w:horzAnchor="margin" w:tblpXSpec="center" w:tblpY="361"/><w:tblW w:w="11199" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="48" wx:bdrwidth="120" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="48" wx:bdrwidth="120" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="48" wx:bdrwidth="120" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="48" wx:bdrwidth="120" w:space="0" w:color="auto"/><w:insideH w:val="single" w:sz="24" wx:bdrwidth="60" w:space="0" w:color="auto"/><w:insideV w:val="single" w:sz="24" wx:bdrwidth="60" w:space="0" w:color="auto"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="3201"/><w:gridCol w:w="7998"/></w:tblGrid><w:tr wsp:rsidR="00FB687E" wsp:rsidRPr="00A51198" wsp:rsidTr="0051005D"><w:trPr><w:trHeight w:val="21"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/></w:tcPr><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="48"/><w:sz-cs w:val="48"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="48"/><w:sz-cs w:val="48"/></w:rPr><w:t>ISDN INTERVIEW OPPORTUNITY</w:t></w:r></w:p></w:tc></w:tr><w:tr wsp:rsidR="00FB687E" wsp:rsidRPr="00A51198" wsp:rsidTr="0051005D"><w:trPr><w:trHeight w:val="594"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/><w:vAlign w:val="center"/></w:tcPr><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t>DATE: </w:t></w:r><w:r wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t>MONDAY 1 JUNE </w:t></w:r><w:r wsp:rsidR="002B0120" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t>2015</w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="007E613B" wsp:rsidP="002F3F4B"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>EMBARGOED UNTIL 00:01 0</w:t></w:r><w:r wsp:rsidR="00B05AAC" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>1/06</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>/1</w:t></w:r><w:r wsp:rsidR="00C01032" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>5</w:t></w:r></w:p></w:tc></w:tr><w:tr wsp:rsidR="00FB687E" wsp:rsidRPr="00A51198" wsp:rsidTr="0051005D"><w:trPr><w:trHeight w:val="455"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/><w:vAlign w:val="center"/></w:tcPr><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="008F4B71"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t>GUESTS:  </w:t></w:r><w:r wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t>CHARLES COUNSELL</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t> – </w:t></w:r><w:r wsp:rsidR="002667BF" wsp:rsidRPr="002667BF"><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:color w:val="545454"/><w:shd w:val="clear" w:color="auto" w:fill="FFFFFF"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="002667BF" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="002667BF" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>EXECUTIVE DIRECTOR,</w:t></w:r><w:r wsp:rsidR="002667BF" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>THE PENSIONS REGULATOR</w:t></w:r></w:p><w:p wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="002F3F4B" wsp:rsidP="002F3F4B"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="28"/><w:sz-cs w:val="28"/></w:rPr><w:t>MORTEN NILSSON – </w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>CEO, NOW: PENSIONS</w:t></w:r></w:p></w:tc></w:tr><w:tr wsp:rsidR="00FB687E" wsp:rsidRPr="00A51198" wsp:rsidTr="00084DBD"><w:trPr><w:trHeight w:val="8418"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="3201" w:type="dxa"/></w:tcPr><w:p wsp:rsidR="00DF08E0" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>GUESTS:</w:t></w:r><w:r wsp:rsidR="00913D34" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>CHARLES COUNSELL</w:t></w:r><w:r wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t> - EXECUTIVE DIRECTOR, THE PENSIONS REGULATOR</w:t></w:r></w:p><w:p wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="002F3F4B" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="10"/><w:sz-cs w:val="10"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="002F3F4B" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>MORTEN NILSSON</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t> - CEO, NOW: PENSIONS</w:t></w:r></w:p><w:p wsp:rsidR="002F3F4B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="002F3F4B" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="10"/><w:sz-cs w:val="10"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00DF08E0" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00DF08E0" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>BIOGRAPHY:</w:t></w:r></w:p><w:p wsp:rsidR="00365C28" wsp:rsidRDefault="002F3F4B" wsp:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang/></w:rPr></w:pPr><w:r wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang/></w:rPr><w:t>Charles Counsell</w:t></w:r><w:r wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/><w:lang/></w:rPr><w:t> became executive director for automatic enrolment at The Pensions Regulator in 2011. Charles was at the regulator in 2006 - 2007, and since 2008 has been involved with the design and delivery of automatic enrolment.</w:t></w:r></w:p><w:p wsp:rsidR="008F4B71" wsp:rsidRPr="00C57554" wsp:rsidRDefault="002F3F4B" wsp:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t> </w:t></w:r></w:p><w:p wsp:rsidR="002F3F4B" wsp:rsidRDefault="002F3F4B" wsp:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>Prior to NOW: Pensions, </w:t></w:r><w:r wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow"/><wx:font wx:val="Arial Narrow"/><w:b/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>Morten</w:t></w:r><w:r wsp:rsidR="00227314" wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow"/><wx:font wx:val="Arial Narrow"/><w:b/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t> Nilsson</w:t></w:r><w:r wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t> was Vice President and Head of International Operations at ATP. He has over 20 years’ experience in the financial services sector, predominantly within business development, operations, strategy and transformation.</w:t></w:r></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="10"/><w:sz-cs w:val="10"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>NOTES TO EDITORS:</w:t></w:r></w:p><w:p wsp:rsidR="00066D3B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00066D3B" wsp:rsidP="00066D3B"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>1. </w:t></w:r><w:hlink w:dest="http://www.thepensionsregulator.gov.uk/docs/pensions-reform-compliance-and-enforcement-policy.pdf"><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rStyle w:val="Hyperlink"/><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>Enforcement and compliance policy</w:t></w:r></w:hlink></w:p><w:p wsp:rsidR="00066D3B" wsp:rsidRPr="005F385E" wsp:rsidRDefault="00066D3B" wsp:rsidP="008F4B71"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b-cs/><w:sz w:val="10"/><w:sz-cs w:val="10"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00DF08E0" wsp:rsidRDefault="00066D3B" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>2. Research undertaken by BDRC Continental, an award-winning insight agency. Questions were put to 400 UK SMEs (up to and including 250 employees) via BDRC Continental’s monthly Business Opinion Omnibus. 2 - 12 March 2015</w:t></w:r><w:r wsp:rsidR="00394C52"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>. Of those surveyed, 269 SMEs are yet to stage</w:t></w:r><w:r wsp:rsidR="007A5E07"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>.</w:t></w:r></w:p><w:p wsp:rsidR="00084DBD" wsp:rsidRPr="005F385E" wsp:rsidRDefault="00084DBD" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Arial"/><wx:font wx:val="Arial Narrow"/><w:b-cs/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00066D3B" wsp:rsidRPr="005F385E" wsp:rsidRDefault="00066D3B" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial" w:h-ansi="Arial" w:cs="Arial"/><wx:font wx:val="Arial"/><w:b-cs/><w:sz w:val="10"/><w:sz-cs w:val="10"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:b/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:b/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>WHAT IS AUTOMATIC ENROLMENT? </w:t></w:r></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>A slice of an employee\'s pay packet is diverted to their pension fund, assuming they are aged between 22 - 65 and earning more than £10,000 a year. Employers are obliged to pay in as well, with the government adding a little extra through tax relief.</w:t></w:r></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="10"/><w:sz-cs w:val="10"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00066D3B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:h-ansi="Arial Narrow" w:cs="Calibri"/><wx:font wx:val="Arial Narrow"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>At first, an employee sees only a minimum of 0.8% of their earnings going to their workplace pension. Tax relief adds another 0.2% and the employer is obliged to add a contribution of 1% of the worker\'s earnings. This rises to 5% contribution from the employee, 3% from the employer, and 1% in tax relief by October 2018. But there are concerns this many not be sufficient to ensure a reasonable income at retirement.  </w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="7998" w:type="dxa"/><w:vmerge w:val="restart"/></w:tcPr><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="6"/><w:sz-cs w:val="6"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="005208C3" wsp:rsidRPr="00C57554" wsp:rsidRDefault="006F6BC7" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="40"/><w:sz-cs w:val="40"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="40"/><w:sz-cs w:val="40"/></w:rPr><w:t>MANDATORY AUTO ENROLMENT: </w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="006F6BC7" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="40"/><w:sz-cs w:val="40"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="40"/><w:sz-cs w:val="40"/></w:rPr><w:t>THOUSANDS OF FIRMS STILL UNPREPARED</w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00C779A4" wsp:rsidP="00236A8F"><w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="·"/><wx:font wx:val="Symbol"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:ind w:left="257" w:hanging="257"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>350,000 SMALL FIRMS HAVEN’T GIVEN ANY</w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> THOUGH</w:t></w:r><w:r wsp:rsidR="005208C3" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>T</w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> TO FINDING A </w:t></w:r><w:r wsp:rsidR="007A5E07" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>PENSION</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>PROVIDER</w:t></w:r></w:p><w:p wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00B05AAC" wsp:rsidP="00236A8F"><w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="·"/><wx:font wx:val="Symbol"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:ind w:left="257" w:hanging="257"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>A THIRD</w:t></w:r><w:r wsp:rsidR="00DC7F85" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>DO NOT UNDERSTAND HOW AUTO ENROLMENT CONTRIBUTIONS ARE CALCULATED</w:t></w:r></w:p><w:p wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554" wsp:rsidRDefault="006F6BC7" wsp:rsidP="00236A8F"><w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="·"/><wx:font wx:val="Symbol"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:ind w:left="257" w:hanging="257"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>500,000 COMPANIES HAVE TO COMPLY WITH WORKPLACE PENSION AUTO ENROLMENT</w:t></w:r><w:r wsp:rsidR="00365C28" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> LEGISLATION</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> BY THE END OF </w:t></w:r><w:r wsp:rsidR="00B05AAC" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>2016</w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00351DD8" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/></w:pPr></w:p><w:p wsp:rsidR="007B6C4E" wsp:rsidRDefault="00B05AAC" wsp:rsidP="007B6C4E"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t>Today (1st </w:t></w:r><w:r wsp:rsidR="007B6C4E"><w:t>June)</w:t></w:r><w:r><w:t> is a key milestone for </w:t></w:r><w:r wsp:rsidR="00351DD8"><w:t>pension reforms</w:t></w:r><w:r wsp:rsidR="002E00F6"><w:t>,</w:t></w:r><w:r wsp:rsidR="007B6C4E"><w:t> as hundreds of thousands of </w:t></w:r><w:r wsp:rsidR="00365C28"><w:t>small and micro firms</w:t></w:r><w:r wsp:rsidR="007B6C4E"><w:t> </w:t></w:r><w:r wsp:rsidR="003E0FAA"><w:t>–</w:t></w:r><w:r wsp:rsidR="00FA10D1"><w:t> </w:t></w:r><w:r wsp:rsidR="003E0FAA"><w:t>those </w:t></w:r><w:r wsp:rsidR="007B6C4E" wsp:rsidRPr="00351DD8"><w:t>with fewer than 30 staff</w:t></w:r><w:r wsp:rsidR="00FA10D1"><w:t> -</w:t></w:r><w:r wsp:rsidR="007B6C4E" wsp:rsidRPr="00351DD8"><w:t> </w:t></w:r><w:r wsp:rsidR="007B6C4E"><w:t>will </w:t></w:r><w:r wsp:rsidR="007B6C4E" wsp:rsidRPr="00351DD8"><w:t>have to begin complying with the new workplace </w:t></w:r><w:r wsp:rsidR="0077291F" wsp:rsidRPr="00351DD8"><w:t>pensions</w:t></w:r><w:r wsp:rsidR="007B6C4E" wsp:rsidRPr="00351DD8"><w:t> legislation</w:t></w:r><w:r wsp:rsidR="007B6C4E"><w:t>.</w:t></w:r><w:r wsp:rsidR="007C386C"><w:t> Those who don’t</w:t></w:r><w:r wsp:rsidR="003E0FAA"><w:t>,</w:t></w:r><w:r wsp:rsidR="007C386C"><w:t> face fines</w:t></w:r><w:r wsp:rsidR="007C386C" wsp:rsidRPr="00066D3B"><w:rPr><w:vertAlign w:val="superscript"/></w:rPr><w:t>1</w:t></w:r><w:r wsp:rsidR="007C386C"><w:t> of up to £500 a day.</w:t></w:r></w:p><w:p wsp:rsidR="007B6C4E" wsp:rsidRDefault="007B6C4E" wsp:rsidP="007B6C4E"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/></w:pPr></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00CE61FD" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>However, new </w:t></w:r><w:r wsp:rsidR="00B05AAC" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>research</w:t></w:r><w:r wsp:rsidR="00B05AAC" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:vertAlign w:val="superscript"/></w:rPr><w:t>2 </w:t></w:r><w:r wsp:rsidR="00B05AAC" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>from</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> NOW: Pensions</w:t></w:r><w:r wsp:rsidR="005F5D18" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>reveals that </w:t></w:r><w:r wsp:rsidR="0058058F" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>one in four (27%) of all business</w:t></w:r><w:r wsp:rsidR="009E4595" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>es</w:t></w:r><w:r wsp:rsidR="007B6C4E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> who </w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>need to comply</w:t></w:r><w:r wsp:rsidR="007B6C4E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>haven\'t </w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>yet </w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t>given any thought as to how they will find a provider. </w:t></w:r><w:r wsp:rsidR="00084DBD" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="005F385E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/></w:rPr><w:t>While this is an improvement on 2014</w:t></w:r><w:r wsp:rsidR="002E00F6" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/></w:rPr><w:t> -</w:t></w:r><w:r wsp:rsidR="005F385E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/></w:rPr><w:t> when four in ten (44%) SMEs hadn’t thought about it</w:t></w:r><w:r wsp:rsidR="002E00F6" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/></w:rPr><w:t> -</w:t></w:r><w:r wsp:rsidR="005F385E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:b-cs/></w:rPr><w:t> it still means that almost 350,000 businesses are unprepared.</w:t></w:r><w:r wsp:rsidR="005F385E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b-cs/></w:rPr><w:t>  </w:t></w:r></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b-cs/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00FA10D1" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FA10D1" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>Firms</w:t></w:r><w:r wsp:rsidR="00084DBD" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t> can</w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>’t</w:t></w:r><w:r wsp:rsidR="00084DBD" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>bury their heads in the sand over auto enrolment</w:t></w:r><w:r wsp:rsidR="002E00F6" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>;</w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="00084DBD" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>yet </w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>more </w:t></w:r><w:r wsp:rsidR="00191968" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>half (52%) </w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>who haven’t </w:t></w:r><w:r wsp:rsidR="003E0FAA" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>yet </w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>found a pension provider </w:t></w:r><w:r wsp:rsidR="003E0FAA" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>don’t </w:t></w:r><w:r wsp:rsidR="00191968" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>think</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t> there will be any issue finding one. </w:t></w:r></w:p><w:p wsp:rsidR="005F385E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="005F385E" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00191968" wsp:rsidRPr="00C57554" wsp:rsidRDefault="0022614B" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>T</w:t></w:r><w:r wsp:rsidR="000E650E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>hat</w:t></w:r><w:r wsp:rsidR="0058058F" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t> may be easier said than done</w:t></w:r><w:r wsp:rsidR="00227314" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t>,</w:t></w:r><w:r wsp:rsidR="0058058F" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr><w:t> as n</w:t></w:r><w:r wsp:rsidR="00191968"><w:t>ot all employees are an attractive prospect to pension providers - especially </w:t></w:r><w:r wsp:rsidR="000E650E"><w:t>those</w:t></w:r><w:r wsp:rsidR="00191968"><w:t> on lower salaries - and </w:t></w:r><w:r wsp:rsidR="000E650E"><w:t>providers </w:t></w:r><w:r wsp:rsidR="00191968"><w:t>may not be willing to accept all employers and all employees on equal terms</w:t></w:r><w:r wsp:rsidR="000E650E"><w:t>. Less</w:t></w:r><w:r wsp:rsidR="007C386C"><w:t> than one</w:t></w:r><w:r wsp:rsidR="00191968"><w:t> in </w:t></w:r><w:r wsp:rsidR="007C386C"><w:t>ten</w:t></w:r><w:r wsp:rsidR="00191968"><w:t> (9%)</w:t></w:r><w:r wsp:rsidR="000E650E"><w:t> SMEs</w:t></w:r><w:r wsp:rsidR="00191968"><w:t> appreciate this reality and worry that providers might \'cherry pick\' business.</w:t></w:r></w:p><w:p wsp:rsidR="004A390C" wsp:rsidRPr="005F5D18" wsp:rsidRDefault="004A390C" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="004A390C" wsp:rsidRDefault="008E500D" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t>The complexity of the subject compound</w:t></w:r><w:r wsp:rsidR="00227314"><w:t>s</w:t></w:r><w:r><w:t> the issue; a</w:t></w:r><w:r wsp:rsidR="0058058F"><w:t> third (34%) of </w:t></w:r><w:r wsp:rsidR="00365C28"><w:t>small firms</w:t></w:r><w:r wsp:rsidR="00084DBD"><w:t> </w:t></w:r><w:r wsp:rsidR="0058058F"><w:t>don\'t understand how auto enrolment contributions are calculated. </w:t></w:r></w:p><w:p wsp:rsidR="004A390C" wsp:rsidRPr="005F5D18" wsp:rsidRDefault="004A390C" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00191968" wsp:rsidRDefault="000A7F48" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t>Some c</w:t></w:r><w:r wsp:rsidR="005F6D44"><w:t>ompanies - however - </w:t></w:r><w:r wsp:rsidR="005F5D18"><w:t>have at least</w:t></w:r><w:r wsp:rsidR="0058058F"><w:t> given </w:t></w:r><w:r wsp:rsidR="000E650E"><w:t>a bit of</w:t></w:r><w:r wsp:rsidR="0058058F"><w:t> thought as to how they will go about finding a provider, with a quarter (26%) intending to seek help from their accountant;</w:t></w:r><w:r wsp:rsidR="00227314"><w:t> one in six (</w:t></w:r><w:r wsp:rsidR="0058058F"><w:t>16%</w:t></w:r><w:r wsp:rsidR="00227314"><w:t>)</w:t></w:r><w:r wsp:rsidR="0058058F"><w:t> say</w:t></w:r><w:r wsp:rsidR="00227314"><w:t>ing</w:t></w:r><w:r wsp:rsidR="0058058F"><w:t> the will rely on their existing scheme provider while one in eight (12%) will search the market and do the research themselves.</w:t></w:r></w:p><w:p wsp:rsidR="00084DBD" wsp:rsidRDefault="00084DBD" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/></w:pPr></w:p><w:p wsp:rsidR="000A7F48" wsp:rsidRPr="000A7F48" wsp:rsidRDefault="000A7F48" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="000A7F48"><w:rPr><w:b/></w:rPr><w:t>SMALL BUSINESSES IN THE UK </w:t></w:r></w:p><w:p wsp:rsidR="00084DBD" wsp:rsidRPr="00084DBD" wsp:rsidRDefault="00351DD8" wsp:rsidP="00084DBD"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t>SMEs are the backbone of </w:t></w:r><w:r wsp:rsidR="000A7F48"><w:t>our</w:t></w:r><w:r><w:t> economy</w:t></w:r><w:r wsp:rsidR="00D53BA5"><w:t>, employing millions, </w:t></w:r><w:r wsp:rsidR="000A7F48"><w:t>generating</w:t></w:r><w:r wsp:rsidR="00084DBD" wsp:rsidRPr="00084DBD"><w:t> </w:t></w:r><w:r wsp:rsidR="000A7F48"><w:t>a </w:t></w:r><w:r wsp:rsidR="00084DBD" wsp:rsidRPr="00084DBD"><w:t>combined turnover of £1.6 trillion</w:t></w:r><w:r><w:t> and account</w:t></w:r><w:r wsp:rsidR="000A7F48"><w:t>ing</w:t></w:r><w:r><w:t> for 60% of private sector employment</w:t></w:r><w:r wsp:rsidR="000A7F48"><w:t>.</w:t></w:r></w:p><w:p wsp:rsidR="0058058F" wsp:rsidRPr="005F5D18" wsp:rsidRDefault="0058058F" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="005F5D18" wsp:rsidRDefault="00A35763" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:b-cs/></w:rPr></w:pPr><w:r wsp:rsidRPr="00D53BA5"><w:rPr><w:b-cs/></w:rPr><w:t>Around 10 million people are eligible for auto enrolment</w:t></w:r><w:r><w:rPr><w:b-cs/></w:rPr><w:t> and 5 million have already been enrolled. Currently, just </w:t></w:r><w:r wsp:rsidR="00365C28"><w:rPr><w:b-cs/></w:rPr><w:t>9</w:t></w:r><w:r><w:rPr><w:b-cs/></w:rPr><w:t>% of savers are opting out, which highlights </w:t></w:r><w:r wsp:rsidR="00227314"><w:rPr><w:b-cs/></w:rPr><w:t>the fact </w:t></w:r><w:r><w:rPr><w:b-cs/></w:rPr><w:t>that if it’s made simple and easy for people to save</w:t></w:r><w:r wsp:rsidR="00227314"><w:rPr><w:b-cs/></w:rPr><w:t> then</w:t></w:r><w:r><w:rPr><w:b-cs/></w:rPr><w:t>, by and large</w:t></w:r><w:r wsp:rsidR="00227314"><w:rPr><w:b-cs/></w:rPr><w:t>,</w:t></w:r><w:r><w:rPr><w:b-cs/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="005F5D18"><w:rPr><w:b-cs/></w:rPr><w:t>people will do so. </w:t></w:r></w:p><w:p wsp:rsidR="0058058F" wsp:rsidRPr="005F5D18" wsp:rsidRDefault="0058058F" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="006F6BC7" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00A35763" wsp:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/></w:rPr></w:pPr><w:r wsp:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t>We\'re joined </w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t>in the studio by Charles Counsell - Executive Director at The Pensions Regulator and Morten Nilsson, NOW: Pensions</w:t></w:r><w:r wsp:rsidR="000A7F48"><w:rPr><w:b/></w:rPr><w:t>’</w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t> CEO. Together they will be discussing the implications of the new rules, the pitfalls and hurdles that companies will fac</w:t></w:r><w:r wsp:rsidR="00365C28"><w:rPr><w:b/></w:rPr><w:t>e</w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t> and what this legislation means </w:t></w:r><w:r wsp:rsidR="00365C28"><w:rPr><w:b/></w:rPr><w:t>for</w:t></w:r><w:r wsp:rsidR="006F6BC7" wsp:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t> employees. </w:t></w:r></w:p></w:tc></w:tr><w:tr wsp:rsidR="00FB687E" wsp:rsidRPr="00A51198" wsp:rsidTr="0022614B"><w:trPr><w:trHeight w:val="2195"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="3201" w:type="dxa"/></w:tcPr><w:p wsp:rsidR="00DF08E0" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00DF08E0" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="24"/><w:sz-cs w:val="24"/></w:rPr><w:t>TO BOOK AN INTERVIEW OR REQUEST FURTHER INFORMATION</w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="20"/><w:sz-cs w:val="20"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="0024249E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr><w:t>0</w:t></w:r><w:r wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr><w:t>20 </w:t></w:r><w:r wsp:rsidR="00A51198" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="32"/><w:sz-cs w:val="32"/></w:rPr><w:t>7458 4500</w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00255171" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr></w:pPr><w:hlink w:dest="mailto:INTERVIEW@ON-BROADCAST.COM"><w:r wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554"><w:rPr><w:rStyle w:val="Hyperlink"/><w:rFonts w:cs="Calibri"/><w:b/><w:sz w:val="20"/><w:sz-cs w:val="20"/></w:rPr><w:t>INTERVIEW@ON-BROADCAST.COM</w:t></w:r></w:hlink></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="7998" w:type="dxa"/><w:vmerge/></w:tcPr><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr wsp:rsidR="00FB687E" wsp:rsidRPr="00A51198" wsp:rsidTr="0051005D"><w:trPr><w:trHeight w:val="403"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/></w:tcPr><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="000D3160" wsp:rsidP="000D3160"><w:pPr><w:tabs><w:tab w:val="center" w:pos="5491"/><w:tab w:val="right" w:pos="10983"/></w:tabs><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:tab/></w:r><w:r wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:t>If you wish to discuss the type of content you are being offered, please contact a member o</w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:t>f the team on 020 7458</w:t></w:r><w:r wsp:rsidR="00A51198" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:t>4500</w:t></w:r></w:p><w:p wsp:rsidR="00FB687E" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00FB687E" wsp:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr></w:pPr><w:r wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:t>ON-Broadcast</w:t></w:r><w:r wsp:rsidR="00BC30BD" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t>:</w:t></w:r><w:r wsp:rsidR="00970A20" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:color w:val="000000"/><w:sz w:val="18"/><w:sz-cs w:val="18"/></w:rPr><w:t> </w:t></w:r><w:r wsp:rsidR="00BC30BD" wsp:rsidRPr="00C57554"><w:rPr><w:rFonts w:cs="Calibri"/><w:sz w:val="16"/><w:sz-cs w:val="16"/></w:rPr><w:t>5th Floor, 41 – 42 Berners Street, London, W1T 3NB</w:t></w:r></w:p></w:tc></w:tr></w:tbl><w:p wsp:rsidR="00EA5D5B" wsp:rsidRPr="00C57554" wsp:rsidRDefault="00EA5D5B"><w:pPr><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr></w:pPr></w:p><w:p wsp:rsidR="0003215D" wsp:rsidRPr="00C57554" wsp:rsidRDefault="0003215D"><w:pPr><w:rPr><w:rFonts w:cs="Calibri"/></w:rPr></w:pPr></w:p><w:sectPr wsp:rsidR="0003215D" wsp:rsidRPr="00C57554" wsp:rsidSect="00916D10"><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="113" w:right="1440" w:bottom="0" w:left="1440" w:header="0" w:footer="0" w:gutter="0"/><w:cols w:space="708"/><w:docGrid w:line-pitch="360"/></w:sectPr></wx:sect></w:body></w:wordDocument>

</body>
</html>
	';
}

function xml_file()
{
	# the contents of a .xml file exported from Word as "XML"
	return '
<html>
<body>

<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<?mso-application progid="Word.Document"?>
<pkg:package xmlns:pkg="http://schemas.microsoft.com/office/2006/xmlPackage"><pkg:part pkg:name="/_rels/.rels" pkg:contentType="application/vnd.openxmlformats-package.relationships+xml" pkg:padding="512"><pkg:xmlData><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/_rels/document.xml.rels" pkg:contentType="application/vnd.openxmlformats-package.relationships+xml" pkg:padding="256"><pkg:xmlData><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId8" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/endnotes" Target="endnotes.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId7" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footnotes" Target="footnotes.xml"/><Relationship Id="rId12" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/customXml" Target="../customXml/item1.xml"/><Relationship Id="rId6" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/webSettings" Target="webSettings.xml"/><Relationship Id="rId11" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable" Target="fontTable.xml"/><Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/><Relationship Id="rId10" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="mailto:INTERVIEW@ON-BROADCAST.COM" TargetMode="External"/><Relationship Id="rId4" Type="http://schemas.microsoft.com/office/2007/relationships/stylesWithEffects" Target="stylesWithEffects.xml"/><Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="http://www.thepensionsregulator.gov.uk/docs/pensions-reform-compliance-and-enforcement-policy.pdf" TargetMode="External"/></Relationships></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/document.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"><pkg:xmlData><w:document mc:Ignorable="w14 wp14" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"><w:body><w:tbl><w:tblPr><w:tblpPr w:leftFromText="180" w:rightFromText="180" w:vertAnchor="page" w:horzAnchor="margin" w:tblpXSpec="center" w:tblpY="361"/><w:tblW w:w="11199" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="48" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="48" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="48" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="48" w:space="0" w:color="auto"/><w:insideH w:val="single" w:sz="24" w:space="0" w:color="auto"/><w:insideV w:val="single" w:sz="24" w:space="0" w:color="auto"/></w:tblBorders><w:tblLook w:val="0000" w:firstRow="0" w:lastRow="0" w:firstColumn="0" w:lastColumn="0" w:noHBand="0" w:noVBand="0"/></w:tblPr><w:tblGrid><w:gridCol w:w="3201"/><w:gridCol w:w="7998"/></w:tblGrid><w:tr w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidTr="0051005D"><w:trPr><w:trHeight w:val="21"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/></w:tcPr><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="48"/><w:szCs w:val="48"/></w:rPr></w:pPr><w:bookmarkStart w:id="0" w:name="_GoBack"/><w:bookmarkEnd w:id="0"/><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="48"/><w:szCs w:val="48"/></w:rPr><w:t>ISDN INTERVIEW OPPORTUNITY</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidTr="0051005D"><w:trPr><w:trHeight w:val="594"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve">DATE: </w:t></w:r><w:r w:rsidR="002F3F4B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve">MONDAY 1 JUNE </w:t></w:r><w:r w:rsidR="002B0120"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>2015</w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="008F4B71" w:rsidRDefault="007E613B" w:rsidP="002F3F4B"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="008F4B71"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>EMBARGOED UNTIL 00:01 0</w:t></w:r><w:r w:rsidR="00B05AAC"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>1/06</w:t></w:r><w:r w:rsidRPr="008F4B71"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>/1</w:t></w:r><w:r w:rsidR="00C01032"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="FF0000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>5</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidTr="0051005D"><w:trPr><w:trHeight w:val="455"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/><w:vAlign w:val="center"/></w:tcPr><w:p w:rsidR="00FB687E" w:rsidRDefault="00FB687E" w:rsidP="008F4B71"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve">GUESTS:  </w:t></w:r><w:r w:rsidR="002F3F4B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>CHARLES COUNSELL</w:t></w:r><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve"> – </w:t></w:r><w:r w:rsidR="002667BF" w:rsidRPr="002667BF"><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:color w:val="545454"/><w:shd w:val="clear" w:color="auto" w:fill="FFFFFF"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="002667BF" w:rsidRPr="002667BF"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t> </w:t></w:r><w:r w:rsidR="002667BF" w:rsidRPr="002667BF"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>EXECUTIVE DIRECTOR</w:t></w:r><w:r w:rsidR="002667BF"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>,</w:t></w:r><w:r w:rsidR="002667BF" w:rsidRPr="002667BF"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="002F3F4B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>THE PENSIONS REGULATOR</w:t></w:r></w:p><w:p w:rsidR="002F3F4B" w:rsidRPr="00A51198" w:rsidRDefault="002F3F4B" w:rsidP="002F3F4B"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>MORTEN NILSSON</w:t></w:r><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve"> – </w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>CEO, NOW: PENSIONS</w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidTr="00084DBD"><w:trPr><w:trHeight w:val="8418"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="3201" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DF08E0" w:rsidRPr="00913D34" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>GUESTS:</w:t></w:r><w:r w:rsidR="00913D34"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="002F3F4B" w:rsidRPr="002F3F4B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>CHARLES COUNSELL</w:t></w:r><w:r w:rsidR="002F3F4B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve"> - EXECUTIVE DIRECTOR, THE PENSIONS REGULATOR</w:t></w:r></w:p><w:p w:rsidR="002F3F4B" w:rsidRPr="005F385E" w:rsidRDefault="002F3F4B" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="10"/><w:szCs w:val="10"/></w:rPr></w:pPr></w:p><w:p w:rsidR="002F3F4B" w:rsidRDefault="002F3F4B" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="002F3F4B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>MORTEN NILSSON</w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve"> - CEO, NOW: PENSIONS</w:t></w:r></w:p><w:p w:rsidR="002F3F4B" w:rsidRPr="005F385E" w:rsidRDefault="002F3F4B" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="10"/><w:szCs w:val="10"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00DF08E0" w:rsidRPr="00A51198" w:rsidRDefault="00DF08E0" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>BIOGRAPHY:</w:t></w:r></w:p><w:p w:rsidR="00365C28" w:rsidRDefault="002F3F4B" w:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:sz w:val="18"/><w:szCs w:val="18"/><w:lang w:val="en"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:b/><w:sz w:val="18"/><w:szCs w:val="18"/><w:lang w:val="en"/></w:rPr><w:t xml:space="preserve">Charles </w:t></w:r><w:proofErr w:type="spellStart"/><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:b/><w:sz w:val="18"/><w:szCs w:val="18"/><w:lang w:val="en"/></w:rPr><w:t>Counsell</w:t></w:r><w:proofErr w:type="spellEnd"/><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:sz w:val="18"/><w:szCs w:val="18"/><w:lang w:val="en"/></w:rPr><w:t> became executive director for automatic enrolment at The Pensions Regulator in 2011. Charles was at the regulator in 2006 - 2007, and since 2008 has been involved with the design and delivery of automatic enrolment.</w:t></w:r></w:p><w:p w:rsidR="008F4B71" w:rsidRPr="005F385E" w:rsidRDefault="002F3F4B" w:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r></w:p><w:p w:rsidR="002F3F4B" w:rsidRDefault="002F3F4B" w:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve">Prior to NOW: Pensions, </w:t></w:r><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow"/><w:b/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>Morten</w:t></w:r><w:r w:rsidR="00227314" w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow"/><w:b/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve"> Nilsson</w:t></w:r><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve"> was Vice President and Head of International Operations at ATP. He has over 20 years’ experience in the financial services sector, predominantly within business development, operations, strategy and transformation.</w:t></w:r></w:p><w:p w:rsidR="005F385E" w:rsidRPr="005F385E" w:rsidRDefault="005F385E" w:rsidP="005F385E"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:eastAsiaTheme="minorHAnsi" w:hAnsi="Arial Narrow"/><w:sz w:val="10"/><w:szCs w:val="10"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>NOTES TO EDITORS:</w:t></w:r></w:p><w:p w:rsidR="00066D3B" w:rsidRPr="005F385E" w:rsidRDefault="00066D3B" w:rsidP="00066D3B"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve">1. </w:t></w:r><w:hyperlink r:id="rId9" w:history="1"><w:r w:rsidRPr="005F385E"><w:rPr><w:rStyle w:val="Hyperlink"/><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>Enforcement and compliance policy</w:t></w:r></w:hyperlink></w:p><w:p w:rsidR="00066D3B" w:rsidRPr="005F385E" w:rsidRDefault="00066D3B" w:rsidP="008F4B71"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:bCs/><w:sz w:val="10"/><w:szCs w:val="10"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00DF08E0" w:rsidRDefault="00066D3B" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:bCs/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:bCs/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>2. Research undertaken by BDRC Continental, an award-winning insight agency. Questions were put to 400 UK SMEs (up to and including 250 employees) via BDRC Continental’s monthly Business Opinion Omnibus. 2 - 12 March 2015</w:t></w:r><w:r w:rsidR="00394C52"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:bCs/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>. Of those surveyed, 269 SMEs are yet to stage</w:t></w:r><w:r w:rsidR="007A5E07"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:bCs/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>.</w:t></w:r></w:p><w:p w:rsidR="00084DBD" w:rsidRPr="005F385E" w:rsidRDefault="00084DBD" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cs="Arial"/><w:bCs/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00066D3B" w:rsidRPr="005F385E" w:rsidRDefault="00066D3B" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:bCs/><w:sz w:val="10"/><w:szCs w:val="10"/></w:rPr></w:pPr></w:p><w:p w:rsidR="005F385E" w:rsidRPr="005F385E" w:rsidRDefault="005F385E" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:b/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve">WHAT IS AUTOMATIC ENROLMENT? </w:t></w:r></w:p><w:p w:rsidR="005F385E" w:rsidRPr="005F385E" w:rsidRDefault="005F385E" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve">A slice of an employee\'s pay packet is diverted to their pension fund, assuming they are aged </w:t></w:r><w:proofErr w:type="gramStart"/><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>between 22 - 65</w:t></w:r><w:proofErr w:type="gramEnd"/><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve"> and earning more than £10,000 a year. Employers are obliged to pay in as well, with the government adding a little extra through tax relief.</w:t></w:r></w:p><w:p w:rsidR="005F385E" w:rsidRPr="005F385E" w:rsidRDefault="005F385E" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="10"/><w:szCs w:val="10"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00066D3B" w:rsidRPr="0060362B" w:rsidRDefault="005F385E" w:rsidP="005F385E"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="both"/><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:r w:rsidRPr="005F385E"><w:rPr><w:rFonts w:ascii="Arial Narrow" w:hAnsi="Arial Narrow" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve">At first, an employee sees only a minimum of 0.8% of their earnings going to their workplace pension. Tax relief adds another 0.2% and the employer is obliged to add a contribution of 1% of the worker\'s earnings. This rises to 5% contribution from the employee, 3% from the employer, and 1% in tax relief by October 2018. But there are concerns this many not be sufficient to ensure a reasonable income at retirement.  </w:t></w:r></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="7998" w:type="dxa"/><w:vMerge w:val="restart"/></w:tcPr><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:pStyle w:val="NoSpacing"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="6"/><w:szCs w:val="6"/></w:rPr></w:pPr></w:p><w:p w:rsidR="005208C3" w:rsidRDefault="006F6BC7" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/></w:rPr><w:t xml:space="preserve">MANDATORY AUTO ENROLMENT: </w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="006F6BC7" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/></w:rPr><w:t>THOUSANDS OF FIRMS STILL UNPREPARED</w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRDefault="00C779A4" w:rsidP="00236A8F"><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="3"/></w:numPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:ind w:left="257" w:hanging="257"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>350,000 SMALL FIRMS HAVEN’T GIVEN ANY</w:t></w:r><w:r w:rsidR="006F6BC7"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> THOUGH</w:t></w:r><w:r w:rsidR="005208C3"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>T</w:t></w:r><w:r w:rsidR="006F6BC7"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> TO FINDING A </w:t></w:r><w:r w:rsidR="007A5E07"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>PENSION</w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="006F6BC7"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>PROVIDER</w:t></w:r></w:p><w:p w:rsidR="006F6BC7" w:rsidRDefault="00B05AAC" w:rsidP="00236A8F"><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="3"/></w:numPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:ind w:left="257" w:hanging="257"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>A THIRD</w:t></w:r><w:r w:rsidR="00DC7F85"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="006F6BC7"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>DO NOT UNDERSTAND HOW AUTO ENROLMENT CONTRIBUTIONS ARE CALCULATED</w:t></w:r></w:p><w:p w:rsidR="006F6BC7" w:rsidRPr="00A51198" w:rsidRDefault="006F6BC7" w:rsidP="00236A8F"><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="3"/></w:numPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:ind w:left="257" w:hanging="257"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>500,000 COMPANIES HAVE TO COMPLY WITH WORKPLACE PENSION AUTO ENROLMENT</w:t></w:r><w:r w:rsidR="00365C28"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> LEGISLATION</w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> BY THE END OF </w:t></w:r><w:r w:rsidR="00B05AAC"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>2016</w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00351DD8" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr></w:p><w:p w:rsidR="007B6C4E" w:rsidRDefault="00B05AAC" w:rsidP="007B6C4E"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t xml:space="preserve">Today (1st </w:t></w:r><w:r w:rsidR="007B6C4E"><w:t>June)</w:t></w:r><w:r><w:t xml:space="preserve"> is a key milestone for </w:t></w:r><w:r w:rsidR="00351DD8"><w:t>pension reforms</w:t></w:r><w:r w:rsidR="002E00F6"><w:t>,</w:t></w:r><w:r w:rsidR="007B6C4E"><w:t xml:space="preserve"> as hundreds of thousands of </w:t></w:r><w:r w:rsidR="00365C28"><w:t>small and micro firms</w:t></w:r><w:r w:rsidR="007B6C4E"><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="003E0FAA"><w:t>–</w:t></w:r><w:r w:rsidR="00FA10D1"><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="003E0FAA"><w:t xml:space="preserve">those </w:t></w:r><w:r w:rsidR="007B6C4E" w:rsidRPr="00351DD8"><w:t>with fewer than 30 staff</w:t></w:r><w:r w:rsidR="00FA10D1"><w:t xml:space="preserve"> -</w:t></w:r><w:r w:rsidR="007B6C4E" w:rsidRPr="00351DD8"><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="007B6C4E"><w:t xml:space="preserve">will </w:t></w:r><w:r w:rsidR="007B6C4E" w:rsidRPr="00351DD8"><w:t xml:space="preserve">have to begin complying with the new workplace </w:t></w:r><w:proofErr w:type="gramStart"/><w:r w:rsidR="0077291F" w:rsidRPr="00351DD8"><w:t>pensions</w:t></w:r><w:proofErr w:type="gramEnd"/><w:r w:rsidR="007B6C4E" w:rsidRPr="00351DD8"><w:t xml:space="preserve"> legislation</w:t></w:r><w:r w:rsidR="007B6C4E"><w:t>.</w:t></w:r><w:r w:rsidR="007C386C"><w:t xml:space="preserve"> Those who don’t</w:t></w:r><w:r w:rsidR="003E0FAA"><w:t>,</w:t></w:r><w:r w:rsidR="007C386C"><w:t xml:space="preserve"> face fines</w:t></w:r><w:r w:rsidR="007C386C" w:rsidRPr="00066D3B"><w:rPr><w:vertAlign w:val="superscript"/></w:rPr><w:t>1</w:t></w:r><w:r w:rsidR="007C386C"><w:t xml:space="preserve"> of up to £500 a day.</w:t></w:r></w:p><w:p w:rsidR="007B6C4E" w:rsidRDefault="007B6C4E" w:rsidP="007B6C4E"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr></w:p><w:p w:rsidR="005F385E" w:rsidRPr="00084DBD" w:rsidRDefault="00CE61FD" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve">However, new </w:t></w:r><w:r w:rsidR="00B05AAC"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>research</w:t></w:r><w:r w:rsidR="00B05AAC" w:rsidRPr="00066D3B"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:vertAlign w:val="superscript"/></w:rPr><w:t>2</w:t></w:r><w:r w:rsidR="00B05AAC"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:vertAlign w:val="superscript"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="00B05AAC"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>from</w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> NOW: Pensions</w:t></w:r><w:r w:rsidR="005F5D18"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve">reveals that </w:t></w:r><w:r w:rsidR="0058058F"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>one in four (27%) of all business</w:t></w:r><w:r w:rsidR="009E4595"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>es</w:t></w:r><w:r w:rsidR="007B6C4E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:proofErr w:type="gramStart"/><w:r w:rsidR="007B6C4E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>who</w:t></w:r><w:proofErr w:type="gramEnd"/><w:r w:rsidR="007B6C4E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t>need to comply</w:t></w:r><w:r w:rsidR="007B6C4E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve">haven\'t </w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve">yet </w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve">given any thought as to how they will find a </w:t></w:r><w:r w:rsidRPr="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve">provider. </w:t></w:r><w:r w:rsidR="00084DBD" w:rsidRPr="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="005F385E" w:rsidRPr="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/></w:rPr><w:t>While this is an improvement on 2014</w:t></w:r><w:r w:rsidR="002E00F6"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/></w:rPr><w:t xml:space="preserve"> -</w:t></w:r><w:r w:rsidR="005F385E" w:rsidRPr="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/></w:rPr><w:t xml:space="preserve"> when four in ten (44%) SMEs hadn’t thought about it</w:t></w:r><w:r w:rsidR="002E00F6"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/></w:rPr><w:t xml:space="preserve"> -</w:t></w:r><w:r w:rsidR="005F385E" w:rsidRPr="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:bCs/></w:rPr><w:t xml:space="preserve"> it still means that almost 350,000 businesses are unprepared.</w:t></w:r><w:r w:rsidR="005F385E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:bCs/></w:rPr><w:t xml:space="preserve">  </w:t></w:r></w:p><w:p w:rsidR="005F385E" w:rsidRDefault="005F385E" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:bCs/></w:rPr></w:pPr></w:p><w:p w:rsidR="00FA10D1" w:rsidRDefault="00FA10D1" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>Firms</w:t></w:r><w:r w:rsidR="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve"> can</w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>’t</w:t></w:r><w:r w:rsidR="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>bury their heads in the sand over auto enrolment</w:t></w:r><w:r w:rsidR="002E00F6"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>;</w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="00084DBD"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">yet </w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">more </w:t></w:r><w:r w:rsidR="00191968"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">half (52%) </w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">who haven’t </w:t></w:r><w:r w:rsidR="003E0FAA"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">yet </w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">found a pension provider </w:t></w:r><w:r w:rsidR="003E0FAA"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve">don’t </w:t></w:r><w:r w:rsidR="00191968"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>think</w:t></w:r><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve"> there will be any issue finding one. </w:t></w:r></w:p><w:p w:rsidR="005F385E" w:rsidRDefault="005F385E" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00191968" w:rsidRPr="005F385E" w:rsidRDefault="0022614B" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>T</w:t></w:r><w:r w:rsidR="000E650E"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>hat</w:t></w:r><w:r w:rsidR="0058058F"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve"> may be easier said than done</w:t></w:r><w:r w:rsidR="00227314"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t>,</w:t></w:r><w:r w:rsidR="0058058F"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr><w:t xml:space="preserve"> as n</w:t></w:r><w:r w:rsidR="00191968"><w:t xml:space="preserve">ot all employees are an attractive prospect to pension providers - especially </w:t></w:r><w:r w:rsidR="000E650E"><w:t>those</w:t></w:r><w:r w:rsidR="00191968"><w:t xml:space="preserve"> on lower salaries - and </w:t></w:r><w:r w:rsidR="000E650E"><w:t xml:space="preserve">providers </w:t></w:r><w:r w:rsidR="00191968"><w:t>may not be willing to accept all employers and all employees on equal terms</w:t></w:r><w:r w:rsidR="000E650E"><w:t>. Less</w:t></w:r><w:r w:rsidR="007C386C"><w:t xml:space="preserve"> than one</w:t></w:r><w:r w:rsidR="00191968"><w:t xml:space="preserve"> in </w:t></w:r><w:r w:rsidR="007C386C"><w:t>ten</w:t></w:r><w:r w:rsidR="00191968"><w:t xml:space="preserve"> (9%)</w:t></w:r><w:r w:rsidR="000E650E"><w:t xml:space="preserve"> SMEs</w:t></w:r><w:r w:rsidR="00191968"><w:t xml:space="preserve"> appreciate this reality and worry that providers might \'cherry pick\' business.</w:t></w:r></w:p><w:p w:rsidR="004A390C" w:rsidRPr="005F5D18" w:rsidRDefault="004A390C" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p w:rsidR="004A390C" w:rsidRDefault="008E500D" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t>The complexity of the subject compound</w:t></w:r><w:r w:rsidR="00227314"><w:t>s</w:t></w:r><w:r><w:t xml:space="preserve"> the issue; a</w:t></w:r><w:r w:rsidR="0058058F"><w:t xml:space="preserve"> third (34%) of </w:t></w:r><w:r w:rsidR="00365C28"><w:t>small firms</w:t></w:r><w:r w:rsidR="00084DBD"><w:t xml:space="preserve"> </w:t></w:r><w:proofErr w:type="gramStart"/><w:r w:rsidR="0058058F"><w:t>don\'t</w:t></w:r><w:proofErr w:type="gramEnd"/><w:r w:rsidR="0058058F"><w:t xml:space="preserve"> understand how auto enrolment contributions are calculated. </w:t></w:r></w:p><w:p w:rsidR="004A390C" w:rsidRPr="005F5D18" w:rsidRDefault="004A390C" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00191968" w:rsidRDefault="000A7F48" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t>Some c</w:t></w:r><w:r w:rsidR="005F6D44"><w:t xml:space="preserve">ompanies - however - </w:t></w:r><w:r w:rsidR="005F5D18"><w:t>have at least</w:t></w:r><w:r w:rsidR="0058058F"><w:t xml:space="preserve"> given </w:t></w:r><w:r w:rsidR="000E650E"><w:t>a bit of</w:t></w:r><w:r w:rsidR="0058058F"><w:t xml:space="preserve"> thought as to how they will go about finding a provider, with a quarter (26%) intending to seek help from their accountant;</w:t></w:r><w:r w:rsidR="00227314"><w:t xml:space="preserve"> one in six (</w:t></w:r><w:r w:rsidR="0058058F"><w:t>16%</w:t></w:r><w:r w:rsidR="00227314"><w:t>)</w:t></w:r><w:r w:rsidR="0058058F"><w:t xml:space="preserve"> say</w:t></w:r><w:r w:rsidR="00227314"><w:t>ing</w:t></w:r><w:r w:rsidR="0058058F"><w:t xml:space="preserve"> the will rely on their existing scheme provider while one in eight (12%) will search the market and do the research themselves.</w:t></w:r></w:p><w:p w:rsidR="00084DBD" w:rsidRDefault="00084DBD" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr></w:p><w:p w:rsidR="000A7F48" w:rsidRPr="000A7F48" w:rsidRDefault="000A7F48" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:b/></w:rPr></w:pPr><w:r w:rsidRPr="000A7F48"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">SMALL BUSINESSES IN THE UK </w:t></w:r></w:p><w:p w:rsidR="00084DBD" w:rsidRPr="00084DBD" w:rsidRDefault="00351DD8" w:rsidP="00084DBD"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr><w:r><w:t xml:space="preserve">SMEs are the backbone of </w:t></w:r><w:r w:rsidR="000A7F48"><w:t>our</w:t></w:r><w:r><w:t xml:space="preserve"> economy</w:t></w:r><w:r w:rsidR="00D53BA5"><w:t xml:space="preserve">, employing millions, </w:t></w:r><w:r w:rsidR="000A7F48"><w:t>generating</w:t></w:r><w:r w:rsidR="00084DBD" w:rsidRPr="00084DBD"><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="000A7F48"><w:t xml:space="preserve">a </w:t></w:r><w:r w:rsidR="00084DBD" w:rsidRPr="00084DBD"><w:t>combined turnover of £1.6 trillion</w:t></w:r><w:r><w:t xml:space="preserve"> and account</w:t></w:r><w:r w:rsidR="000A7F48"><w:t>ing</w:t></w:r><w:r><w:t xml:space="preserve"> for 60% of private sector employment</w:t></w:r><w:r w:rsidR="000A7F48"><w:t>.</w:t></w:r></w:p><w:p w:rsidR="0058058F" w:rsidRPr="005F5D18" w:rsidRDefault="0058058F" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p w:rsidR="005F5D18" w:rsidRDefault="00A35763" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:bCs/></w:rPr></w:pPr><w:r w:rsidRPr="00D53BA5"><w:rPr><w:bCs/></w:rPr><w:t>Around 10 million people are eligible for auto enrolment</w:t></w:r><w:r><w:rPr><w:bCs/></w:rPr><w:t xml:space="preserve"> and 5 million have already been enrolled. Currently, just </w:t></w:r><w:r w:rsidR="00365C28"><w:rPr><w:bCs/></w:rPr><w:t>9</w:t></w:r><w:r><w:rPr><w:bCs/></w:rPr><w:t xml:space="preserve">% of savers are opting out, which highlights </w:t></w:r><w:r w:rsidR="00227314"><w:rPr><w:bCs/></w:rPr><w:t xml:space="preserve">the fact </w:t></w:r><w:r><w:rPr><w:bCs/></w:rPr><w:t>that if it’s made simple and easy for people to save</w:t></w:r><w:r w:rsidR="00227314"><w:rPr><w:bCs/></w:rPr><w:t xml:space="preserve"> then</w:t></w:r><w:r><w:rPr><w:bCs/></w:rPr><w:t>, by and large</w:t></w:r><w:r w:rsidR="00227314"><w:rPr><w:bCs/></w:rPr><w:t>,</w:t></w:r><w:r><w:rPr><w:bCs/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="005F5D18"><w:rPr><w:bCs/></w:rPr><w:t xml:space="preserve">people will do so. </w:t></w:r></w:p><w:p w:rsidR="0058058F" w:rsidRPr="005F5D18" w:rsidRDefault="0058058F" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:sz w:val="16"/></w:rPr></w:pPr></w:p><w:p w:rsidR="006F6BC7" w:rsidRPr="005F5D18" w:rsidRDefault="00A35763" w:rsidP="00227314"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="both"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/></w:rPr></w:pPr><w:r w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">We\'re joined </w:t></w:r><w:r w:rsidR="006F6BC7" w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">in the studio by Charles </w:t></w:r><w:proofErr w:type="spellStart"/><w:r w:rsidR="006F6BC7" w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t>Counsell</w:t></w:r><w:proofErr w:type="spellEnd"/><w:r w:rsidR="006F6BC7" w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve"> - Executive Director at The Pensions Regulator and Morten Nilsson, NOW: Pensions</w:t></w:r><w:r w:rsidR="000A7F48"><w:rPr><w:b/></w:rPr><w:t>’</w:t></w:r><w:r w:rsidR="006F6BC7" w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve"> CEO. Together they will be discussing the implications of the new rules, the pitfalls and hurdles that companies will fac</w:t></w:r><w:r w:rsidR="00365C28"><w:rPr><w:b/></w:rPr><w:t>e</w:t></w:r><w:r w:rsidR="006F6BC7" w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve"> and what this legislation means </w:t></w:r><w:r w:rsidR="00365C28"><w:rPr><w:b/></w:rPr><w:t>for</w:t></w:r><w:r w:rsidR="006F6BC7" w:rsidRPr="005F5D18"><w:rPr><w:b/></w:rPr><w:t xml:space="preserve"> employees. </w:t></w:r></w:p></w:tc></w:tr><w:tr w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidTr="0022614B"><w:trPr><w:trHeight w:val="2195"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="3201" w:type="dxa"/></w:tcPr><w:p w:rsidR="00DF08E0" w:rsidRPr="00A51198" w:rsidRDefault="00DF08E0" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>TO BOOK AN INTERVIEW OR REQUEST FURTHER INFORMATION</w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr></w:pPr></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="0024249E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr><w:t>0</w:t></w:r><w:r w:rsidR="00FB687E" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr><w:t xml:space="preserve">20 </w:t></w:r><w:r w:rsidR="00A51198" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="32"/><w:szCs w:val="32"/></w:rPr><w:t>7458 4500</w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="009B69E9" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:pPr><w:hyperlink r:id="rId10" w:history="1"><w:r w:rsidR="00FB687E" w:rsidRPr="00A51198"><w:rPr><w:rStyle w:val="Hyperlink"/><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:b/><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr><w:t>INTERVIEW@ON-BROADCAST.COM</w:t></w:r></w:hyperlink></w:p></w:tc><w:tc><w:tcPr><w:tcW w:w="7998" w:type="dxa"/><w:vMerge/></w:tcPr><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr></w:pPr></w:p></w:tc></w:tr><w:tr w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidTr="0051005D"><w:trPr><w:trHeight w:val="403"/></w:trPr><w:tc><w:tcPr><w:tcW w:w="11199" w:type="dxa"/><w:gridSpan w:val="2"/></w:tcPr><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="000D3160" w:rsidP="000D3160"><w:pPr><w:tabs><w:tab w:val="center" w:pos="5491"/><w:tab w:val="right" w:pos="10983"/></w:tabs><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:tab/></w:r><w:r w:rsidR="00FB687E" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:t>If you wish to discuss the type of content you are being offered, please contact a member o</w:t></w:r><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:t>f the team on 020 7458</w:t></w:r><w:r w:rsidR="00A51198" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:t>4500</w:t></w:r></w:p><w:p w:rsidR="00FB687E" w:rsidRPr="00A51198" w:rsidRDefault="00FB687E" w:rsidP="0051005D"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr></w:pPr><w:r w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:t>ON-Broadcast</w:t></w:r><w:r w:rsidR="00BC30BD" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t>:</w:t></w:r><w:r w:rsidR="00970A20" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:color w:val="000000"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve"> </w:t></w:r><w:r w:rsidR="00BC30BD" w:rsidRPr="00A51198"><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/><w:sz w:val="16"/><w:szCs w:val="16"/></w:rPr><w:t>5th Floor, 41 – 42 Berners Street, London, W1T 3NB</w:t></w:r></w:p></w:tc></w:tr></w:tbl><w:p w:rsidR="00EA5D5B" w:rsidRDefault="00EA5D5B"><w:pPr><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr></w:pPr></w:p><w:p w:rsidR="0003215D" w:rsidRPr="00A51198" w:rsidRDefault="0003215D"><w:pPr><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorHAnsi"/></w:rPr></w:pPr></w:p><w:sectPr w:rsidR="0003215D" w:rsidRPr="00A51198" w:rsidSect="00916D10"><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="113" w:right="1440" w:bottom="0" w:left="1440" w:header="0" w:footer="0" w:gutter="0"/><w:cols w:space="708"/><w:docGrid w:linePitch="360"/></w:sectPr></w:body></w:document></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/footnotes.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footnotes+xml"><pkg:xmlData><w:footnotes mc:Ignorable="w14 wp14" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"><w:footnote w:type="separator" w:id="-1"><w:p w:rsidR="009B69E9" w:rsidRDefault="009B69E9" w:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:r><w:separator/></w:r></w:p></w:footnote><w:footnote w:type="continuationSeparator" w:id="0"><w:p w:rsidR="009B69E9" w:rsidRDefault="009B69E9" w:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:r><w:continuationSeparator/></w:r></w:p></w:footnote></w:footnotes></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/endnotes.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml"><pkg:xmlData><w:endnotes mc:Ignorable="w14 wp14" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"><w:endnote w:type="separator" w:id="-1"><w:p w:rsidR="009B69E9" w:rsidRDefault="009B69E9" w:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:r><w:separator/></w:r></w:p></w:endnote><w:endnote w:type="continuationSeparator" w:id="0"><w:p w:rsidR="009B69E9" w:rsidRDefault="009B69E9" w:rsidP="00DF08E0"><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:r><w:continuationSeparator/></w:r></w:p></w:endnote></w:endnotes></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/theme/theme1.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.theme+xml"><pkg:xmlData><a:theme name="Office Theme" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:themeElements><a:clrScheme name="Office"><a:dk1><a:sysClr val="windowText" lastClr="000000"/></a:dk1><a:lt1><a:sysClr val="window" lastClr="FFFFFF"/></a:lt1><a:dk2><a:srgbClr val="1F497D"/></a:dk2><a:lt2><a:srgbClr val="EEECE1"/></a:lt2><a:accent1><a:srgbClr val="4F81BD"/></a:accent1><a:accent2><a:srgbClr val="C0504D"/></a:accent2><a:accent3><a:srgbClr val="9BBB59"/></a:accent3><a:accent4><a:srgbClr val="8064A2"/></a:accent4><a:accent5><a:srgbClr val="4BACC6"/></a:accent5><a:accent6><a:srgbClr val="F79646"/></a:accent6><a:hlink><a:srgbClr val="0000FF"/></a:hlink><a:folHlink><a:srgbClr val="800080"/></a:folHlink></a:clrScheme><a:fontScheme name="Office"><a:majorFont><a:latin typeface="Cambria"/><a:ea typeface=""/><a:cs typeface=""/><a:font script="Jpan" typeface="ＭＳ ゴシック"/><a:font script="Hang" typeface="맑은 고딕"/><a:font script="Hans" typeface="宋体"/><a:font script="Hant" typeface="新細明體"/><a:font script="Arab" typeface="Times New Roman"/><a:font script="Hebr" typeface="Times New Roman"/><a:font script="Thai" typeface="Angsana New"/><a:font script="Ethi" typeface="Nyala"/><a:font script="Beng" typeface="Vrinda"/><a:font script="Gujr" typeface="Shruti"/><a:font script="Khmr" typeface="MoolBoran"/><a:font script="Knda" typeface="Tunga"/><a:font script="Guru" typeface="Raavi"/><a:font script="Cans" typeface="Euphemia"/><a:font script="Cher" typeface="Plantagenet Cherokee"/><a:font script="Yiii" typeface="Microsoft Yi Baiti"/><a:font script="Tibt" typeface="Microsoft Himalaya"/><a:font script="Thaa" typeface="MV Boli"/><a:font script="Deva" typeface="Mangal"/><a:font script="Telu" typeface="Gautami"/><a:font script="Taml" typeface="Latha"/><a:font script="Syrc" typeface="Estrangelo Edessa"/><a:font script="Orya" typeface="Kalinga"/><a:font script="Mlym" typeface="Kartika"/><a:font script="Laoo" typeface="DokChampa"/><a:font script="Sinh" typeface="Iskoola Pota"/><a:font script="Mong" typeface="Mongolian Baiti"/><a:font script="Viet" typeface="Times New Roman"/><a:font script="Uigh" typeface="Microsoft Uighur"/></a:majorFont><a:minorFont><a:latin typeface="Calibri"/><a:ea typeface=""/><a:cs typeface=""/><a:font script="Jpan" typeface="ＭＳ 明朝"/><a:font script="Hang" typeface="맑은 고딕"/><a:font script="Hans" typeface="宋体"/><a:font script="Hant" typeface="新細明體"/><a:font script="Arab" typeface="Arial"/><a:font script="Hebr" typeface="Arial"/><a:font script="Thai" typeface="Cordia New"/><a:font script="Ethi" typeface="Nyala"/><a:font script="Beng" typeface="Vrinda"/><a:font script="Gujr" typeface="Shruti"/><a:font script="Khmr" typeface="DaunPenh"/><a:font script="Knda" typeface="Tunga"/><a:font script="Guru" typeface="Raavi"/><a:font script="Cans" typeface="Euphemia"/><a:font script="Cher" typeface="Plantagenet Cherokee"/><a:font script="Yiii" typeface="Microsoft Yi Baiti"/><a:font script="Tibt" typeface="Microsoft Himalaya"/><a:font script="Thaa" typeface="MV Boli"/><a:font script="Deva" typeface="Mangal"/><a:font script="Telu" typeface="Gautami"/><a:font script="Taml" typeface="Latha"/><a:font script="Syrc" typeface="Estrangelo Edessa"/><a:font script="Orya" typeface="Kalinga"/><a:font script="Mlym" typeface="Kartika"/><a:font script="Laoo" typeface="DokChampa"/><a:font script="Sinh" typeface="Iskoola Pota"/><a:font script="Mong" typeface="Mongolian Baiti"/><a:font script="Viet" typeface="Arial"/><a:font script="Uigh" typeface="Microsoft Uighur"/></a:minorFont></a:fontScheme><a:fmtScheme name="Office"><a:fillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="50000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="35000"><a:schemeClr val="phClr"><a:tint val="37000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:tint val="15000"/><a:satMod val="350000"/></a:schemeClr></a:gs></a:gsLst><a:lin ang="16200000" scaled="1"/></a:gradFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:shade val="51000"/><a:satMod val="130000"/></a:schemeClr></a:gs><a:gs pos="80000"><a:schemeClr val="phClr"><a:shade val="93000"/><a:satMod val="130000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="94000"/><a:satMod val="135000"/></a:schemeClr></a:gs></a:gsLst><a:lin ang="16200000" scaled="0"/></a:gradFill></a:fillStyleLst><a:lnStyleLst><a:ln w="9525" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"><a:shade val="95000"/><a:satMod val="105000"/></a:schemeClr></a:solidFill><a:prstDash val="solid"/></a:ln><a:ln w="25400" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln><a:ln w="38100" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln></a:lnStyleLst><a:effectStyleLst><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="20000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="38000"/></a:srgbClr></a:outerShdw></a:effectLst></a:effectStyle><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="23000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="35000"/></a:srgbClr></a:outerShdw></a:effectLst></a:effectStyle><a:effectStyle><a:effectLst><a:outerShdw blurRad="40000" dist="23000" dir="5400000" rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="35000"/></a:srgbClr></a:outerShdw></a:effectLst><a:scene3d><a:camera prst="orthographicFront"><a:rot lat="0" lon="0" rev="0"/></a:camera><a:lightRig rig="threePt" dir="t"><a:rot lat="0" lon="0" rev="1200000"/></a:lightRig></a:scene3d><a:sp3d><a:bevelT w="63500" h="25400"/></a:sp3d></a:effectStyle></a:effectStyleLst><a:bgFillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="40000"/><a:satMod val="350000"/></a:schemeClr></a:gs><a:gs pos="40000"><a:schemeClr val="phClr"><a:tint val="45000"/><a:shade val="99000"/><a:satMod val="350000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="20000"/><a:satMod val="255000"/></a:schemeClr></a:gs></a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="-80000" r="50000" b="180000"/></a:path></a:gradFill><a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:schemeClr val="phClr"><a:tint val="80000"/><a:satMod val="300000"/></a:schemeClr></a:gs><a:gs pos="100000"><a:schemeClr val="phClr"><a:shade val="30000"/><a:satMod val="200000"/></a:schemeClr></a:gs></a:gsLst><a:path path="circle"><a:fillToRect l="50000" t="50000" r="50000" b="50000"/></a:path></a:gradFill></a:bgFillStyleLst></a:fmtScheme></a:themeElements><a:objectDefaults/><a:extraClrSchemeLst/></a:theme></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/settings.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"><pkg:xmlData><w:settings mc:Ignorable="w14" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:sl="http://schemas.openxmlformats.org/schemaLibrary/2006/main"><w:zoom w:percent="100"/><w:doNotDisplayPageBoundaries/><w:proofState w:spelling="clean" w:grammar="clean"/><w:defaultTabStop w:val="720"/><w:drawingGridHorizontalSpacing w:val="110"/><w:displayHorizontalDrawingGridEvery w:val="2"/><w:characterSpacingControl w:val="doNotCompress"/><w:footnotePr><w:footnote w:id="-1"/><w:footnote w:id="0"/></w:footnotePr><w:endnotePr><w:endnote w:id="-1"/><w:endnote w:id="0"/></w:endnotePr><w:compat><w:compatSetting w:name="compatibilityMode" w:uri="http://schemas.microsoft.com/office/word" w:val="14"/><w:compatSetting w:name="overrideTableStyleFontSizeAndJustification" w:uri="http://schemas.microsoft.com/office/word" w:val="1"/><w:compatSetting w:name="enableOpenTypeFeatures" w:uri="http://schemas.microsoft.com/office/word" w:val="1"/><w:compatSetting w:name="doNotFlipMirrorIndents" w:uri="http://schemas.microsoft.com/office/word" w:val="1"/></w:compat><w:rsids><w:rsidRoot w:val="00FB687E"/><w:rsid w:val="0003215D"/><w:rsid w:val="00066D3B"/><w:rsid w:val="00084DBD"/><w:rsid w:val="000A7F48"/><w:rsid w:val="000D3160"/><w:rsid w:val="000E650E"/><w:rsid w:val="00130AFC"/><w:rsid w:val="00191968"/><w:rsid w:val="001E661A"/><w:rsid w:val="0022614B"/><w:rsid w:val="00227314"/><w:rsid w:val="00236A8F"/><w:rsid w:val="0024249E"/><w:rsid w:val="002667BF"/><w:rsid w:val="002B0120"/><w:rsid w:val="002E00F6"/><w:rsid w:val="002F3F4B"/><w:rsid w:val="00351DD8"/><w:rsid w:val="00365C28"/><w:rsid w:val="00394C52"/><w:rsid w:val="003A3071"/><w:rsid w:val="003E0FAA"/><w:rsid w:val="003E210B"/><w:rsid w:val="00424E13"/><w:rsid w:val="004A390C"/><w:rsid w:val="004E76CF"/><w:rsid w:val="0050181D"/><w:rsid w:val="0051005D"/><w:rsid w:val="005208C3"/><w:rsid w:val="00531D73"/><w:rsid w:val="00560C08"/><w:rsid w:val="0058058F"/><w:rsid w:val="005C51CE"/><w:rsid w:val="005E6AFA"/><w:rsid w:val="005F385E"/><w:rsid w:val="005F5D18"/><w:rsid w:val="005F6D44"/><w:rsid w:val="0060362B"/><w:rsid w:val="00637752"/><w:rsid w:val="006E68CC"/><w:rsid w:val="006F6BC7"/><w:rsid w:val="0077291F"/><w:rsid w:val="00782F42"/><w:rsid w:val="0079279C"/><w:rsid w:val="007A490C"/><w:rsid w:val="007A5E07"/><w:rsid w:val="007B6C4E"/><w:rsid w:val="007C386C"/><w:rsid w:val="007E51EC"/><w:rsid w:val="007E613B"/><w:rsid w:val="007F76F2"/><w:rsid w:val="00824EE5"/><w:rsid w:val="00897398"/><w:rsid w:val="008E500D"/><w:rsid w:val="008F4B71"/><w:rsid w:val="00913D34"/><w:rsid w:val="00916D10"/><w:rsid w:val="00970A20"/><w:rsid w:val="009B69E9"/><w:rsid w:val="009E4595"/><w:rsid w:val="00A35763"/><w:rsid w:val="00A51198"/><w:rsid w:val="00B05AAC"/><w:rsid w:val="00BC30BD"/><w:rsid w:val="00BE2F44"/><w:rsid w:val="00C01032"/><w:rsid w:val="00C779A4"/><w:rsid w:val="00C8562D"/><w:rsid w:val="00CD5347"/><w:rsid w:val="00CE61FD"/><w:rsid w:val="00D53BA5"/><w:rsid w:val="00DC7F85"/><w:rsid w:val="00DD65A7"/><w:rsid w:val="00DF08E0"/><w:rsid w:val="00EA5D5B"/><w:rsid w:val="00EB1362"/><w:rsid w:val="00F36651"/><w:rsid w:val="00F66DCB"/><w:rsid w:val="00FA10D1"/><w:rsid w:val="00FB687E"/><w:rsid w:val="00FB72E7"/><w:rsid w:val="00FE65E2"/></w:rsids><m:mathPr><m:mathFont m:val="Cambria Math"/><m:brkBin m:val="before"/><m:brkBinSub m:val="--"/><m:smallFrac m:val="0"/><m:dispDef/><m:lMargin m:val="0"/><m:rMargin m:val="0"/><m:defJc m:val="centerGroup"/><m:wrapIndent m:val="1440"/><m:intLim m:val="subSup"/><m:naryLim m:val="undOvr"/></m:mathPr><w:themeFontLang w:val="en-GB"/><w:clrSchemeMapping w:bg1="light1" w:t1="dark1" w:bg2="light2" w:t2="dark2" w:accent1="accent1" w:accent2="accent2" w:accent3="accent3" w:accent4="accent4" w:accent5="accent5" w:accent6="accent6" w:hyperlink="hyperlink" w:followedHyperlink="followedHyperlink"/><w:shapeDefaults><o:shapedefaults v:ext="edit" spidmax="1026"/><o:shapelayout v:ext="edit"><o:idmap v:ext="edit" data="1"/></o:shapelayout></w:shapeDefaults><w:decimalSymbol w:val="."/><w:listSeparator w:val=","/></w:settings></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/styles.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"><pkg:xmlData><w:styles mc:Ignorable="w14" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:eastAsiaTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorBidi"/><w:sz w:val="22"/><w:szCs w:val="22"/><w:lang w:val="en-GB" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="200" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults><w:latentStyles w:defLockedState="0" w:defUIPriority="99" w:defSemiHidden="1" w:defUnhideWhenUsed="1" w:defQFormat="0" w:count="267"><w:lsdException w:name="Normal" w:semiHidden="0" w:uiPriority="0" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="heading 1" w:semiHidden="0" w:uiPriority="9" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="heading 2" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 3" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 4" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 5" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 6" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 7" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 8" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 9" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="toc 1" w:uiPriority="39"/><w:lsdException w:name="toc 2" w:uiPriority="39"/><w:lsdException w:name="toc 3" w:uiPriority="39"/><w:lsdException w:name="toc 4" w:uiPriority="39"/><w:lsdException w:name="toc 5" w:uiPriority="39"/><w:lsdException w:name="toc 6" w:uiPriority="39"/><w:lsdException w:name="toc 7" w:uiPriority="39"/><w:lsdException w:name="toc 8" w:uiPriority="39"/><w:lsdException w:name="toc 9" w:uiPriority="39"/><w:lsdException w:name="caption" w:uiPriority="35" w:qFormat="1"/><w:lsdException w:name="Title" w:semiHidden="0" w:uiPriority="10" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Default Paragraph Font" w:uiPriority="1"/><w:lsdException w:name="Subtitle" w:semiHidden="0" w:uiPriority="11" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Hyperlink" w:uiPriority="0"/><w:lsdException w:name="Strong" w:semiHidden="0" w:uiPriority="22" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Emphasis" w:semiHidden="0" w:uiPriority="20" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Table Grid" w:semiHidden="0" w:uiPriority="59" w:unhideWhenUsed="0"/><w:lsdException w:name="Placeholder Text" w:unhideWhenUsed="0"/><w:lsdException w:name="No Spacing" w:semiHidden="0" w:uiPriority="1" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Light Shading" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 1" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 1" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 1" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 1" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 1" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 1" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Revision" w:unhideWhenUsed="0"/><w:lsdException w:name="List Paragraph" w:semiHidden="0" w:uiPriority="34" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Quote" w:semiHidden="0" w:uiPriority="29" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Intense Quote" w:semiHidden="0" w:uiPriority="30" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Medium List 2 Accent 1" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 1" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 1" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 1" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 1" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 1" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 1" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 1" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 2" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 2" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 2" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 2" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 2" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 2" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 2" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 2" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 2" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 2" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 2" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 2" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 2" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 2" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 3" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 3" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 3" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 3" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 3" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 3" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 3" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 3" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 3" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 3" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 3" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 3" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 3" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 3" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 4" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 4" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 4" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 4" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 4" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 4" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 4" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 4" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 4" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 4" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 4" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 4" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 4" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 4" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 5" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 5" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 5" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 5" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 5" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 5" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 5" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 5" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 5" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 5" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 5" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 5" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 5" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 5" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 6" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 6" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 6" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 6" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 6" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 6" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 6" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 6" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 6" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 6" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 6" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 6" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 6" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 6" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Subtle Emphasis" w:semiHidden="0" w:uiPriority="19" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Intense Emphasis" w:semiHidden="0" w:uiPriority="21" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Subtle Reference" w:semiHidden="0" w:uiPriority="31" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Intense Reference" w:semiHidden="0" w:uiPriority="32" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Book Title" w:semiHidden="0" w:uiPriority="33" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Bibliography" w:uiPriority="37"/><w:lsdException w:name="TOC Heading" w:uiPriority="39" w:qFormat="1"/></w:latentStyles><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/><w:rsid w:val="00FB687E"/><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="character" w:default="1" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/><w:uiPriority w:val="1"/><w:semiHidden/><w:unhideWhenUsed/></w:style><w:style w:type="table" w:default="1" w:styleId="TableNormal"><w:name w:val="Normal Table"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="numbering" w:default="1" w:styleId="NoList"><w:name w:val="No List"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/></w:style><w:style w:type="paragraph" w:styleId="NoSpacing"><w:name w:val="No Spacing"/><w:uiPriority w:val="1"/><w:qFormat/><w:rsid w:val="00FB687E"/><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="character" w:styleId="Hyperlink"><w:name w:val="Hyperlink"/><w:basedOn w:val="DefaultParagraphFont"/><w:unhideWhenUsed/><w:rsid w:val="00FB687E"/><w:rPr><w:color w:val="0000FF"/><w:u w:val="single"/></w:rPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="apple-style-span"><w:name w:val="apple-style-span"/><w:basedOn w:val="DefaultParagraphFont"/><w:rsid w:val="00DF08E0"/></w:style><w:style w:type="paragraph" w:styleId="Header"><w:name w:val="header"/><w:basedOn w:val="Normal"/><w:link w:val="HeaderChar"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="00DF08E0"/><w:pPr><w:tabs><w:tab w:val="center" w:pos="4513"/><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="HeaderChar"><w:name w:val="Header Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="Header"/><w:uiPriority w:val="99"/><w:semiHidden/><w:rsid w:val="00DF08E0"/><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Footer"><w:name w:val="footer"/><w:basedOn w:val="Normal"/><w:link w:val="FooterChar"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="00DF08E0"/><w:pPr><w:tabs><w:tab w:val="center" w:pos="4513"/><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="FooterChar"><w:name w:val="Footer Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="Footer"/><w:uiPriority w:val="99"/><w:semiHidden/><w:rsid w:val="00DF08E0"/><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="NormalWeb"><w:name w:val="Normal (Web)"/><w:basedOn w:val="Normal"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="002F3F4B"/><w:pPr><w:spacing w:before="100" w:beforeAutospacing="1" w:after="100" w:afterAutospacing="1" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Times New Roman" w:eastAsiaTheme="minorHAnsi" w:hAnsi="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:eastAsia="en-GB"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="BalloonText"><w:name w:val="Balloon Text"/><w:basedOn w:val="Normal"/><w:link w:val="BalloonTextChar"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="00227314"/><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Segoe UI" w:hAnsi="Segoe UI" w:cs="Segoe UI"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="BalloonTextChar"><w:name w:val="Balloon Text Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="BalloonText"/><w:uiPriority w:val="99"/><w:semiHidden/><w:rsid w:val="00227314"/><w:rPr><w:rFonts w:ascii="Segoe UI" w:eastAsia="Calibri" w:hAnsi="Segoe UI" w:cs="Segoe UI"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="apple-converted-space"><w:name w:val="apple-converted-space"/><w:basedOn w:val="DefaultParagraphFont"/><w:rsid w:val="00913D34"/></w:style><w:style w:type="character" w:styleId="Emphasis"><w:name w:val="Emphasis"/><w:basedOn w:val="DefaultParagraphFont"/><w:uiPriority w:val="20"/><w:qFormat/><w:rsid w:val="00913D34"/><w:rPr><w:i/><w:iCs/></w:rPr></w:style></w:styles></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/numbering.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"><pkg:xmlData><w:numbering mc:Ignorable="w14 wp14" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"><w:abstractNum w:abstractNumId="0"><w:nsid w:val="06F22240"/><w:multiLevelType w:val="hybridMultilevel"/><w:tmpl w:val="50A2D92E"/><w:lvl w:ilvl="0" w:tplc="08090001"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:hAnsi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:hAnsi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:hAnsi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:abstractNum><w:abstractNum w:abstractNumId="1"><w:nsid w:val="128F206B"/><w:multiLevelType w:val="hybridMultilevel"/><w:tmpl w:val="AAA87FDE"/><w:lvl w:ilvl="0" w:tplc="0409000F"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="04090019" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="lowerLetter"/><w:lvlText w:val="%2."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="0409001B" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="lowerRoman"/><w:lvlText w:val="%3."/><w:lvlJc w:val="right"/><w:pPr><w:ind w:left="2160" w:hanging="180"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="0409000F" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%4."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="04090019" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="lowerLetter"/><w:lvlText w:val="%5."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="0409001B" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="lowerRoman"/><w:lvlText w:val="%6."/><w:lvlJc w:val="right"/><w:pPr><w:ind w:left="4320" w:hanging="180"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="0409000F" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%7."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="04090019" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="lowerLetter"/><w:lvlText w:val="%8."/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="0409001B" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="lowerRoman"/><w:lvlText w:val="%9."/><w:lvlJc w:val="right"/><w:pPr><w:ind w:left="6480" w:hanging="180"/></w:pPr><w:rPr><w:rFonts w:cs="Times New Roman"/></w:rPr></w:lvl></w:abstractNum><w:abstractNum w:abstractNumId="2"><w:nsid w:val="76766BA8"/><w:multiLevelType w:val="hybridMultilevel"/><w:tmpl w:val="AF2CA9AA"/><w:lvl w:ilvl="0" w:tplc="08090001"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="1" w:tplc="08090003" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="2" w:tplc="08090005" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2160" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:hAnsi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="3" w:tplc="08090001" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="2880" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="4" w:tplc="08090003" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="3600" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="5" w:tplc="08090005" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="4320" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:hAnsi="Wingdings" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="6" w:tplc="08090001" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5040" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="7" w:tplc="08090003" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="o"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="5760" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New" w:hint="default"/></w:rPr></w:lvl><w:lvl w:ilvl="8" w:tplc="08090005" w:tentative="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val=""/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="6480" w:hanging="360"/></w:pPr><w:rPr><w:rFonts w:ascii="Wingdings" w:hAnsi="Wingdings" w:hint="default"/></w:rPr></w:lvl></w:abstractNum><w:num w:numId="1"><w:abstractNumId w:val="1"/></w:num><w:num w:numId="2"><w:abstractNumId w:val="2"/></w:num><w:num w:numId="3"><w:abstractNumId w:val="0"/></w:num></w:numbering></pkg:xmlData></pkg:part><pkg:part pkg:name="/docProps/app.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.extended-properties+xml" pkg:padding="256"><pkg:xmlData><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Template>Normal.dotm</Template><TotalTime>0</TotalTime><Pages>2</Pages><Words>739</Words><Characters>4216</Characters><Application>Microsoft Office Word</Application><DocSecurity>0</DocSecurity><Lines>35</Lines><Paragraphs>9</Paragraphs><ScaleCrop>false</ScaleCrop><HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Title</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs><TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr/></vt:vector></TitlesOfParts><Company>Hewlett-Packard Company</Company><LinksUpToDate>false</LinksUpToDate><CharactersWithSpaces>4946</CharactersWithSpaces><SharedDoc>false</SharedDoc><HyperlinksChanged>false</HyperlinksChanged><AppVersion>14.0000</AppVersion></Properties></pkg:xmlData></pkg:part><pkg:part pkg:name="/customXml/_rels/item1.xml.rels" pkg:contentType="application/vnd.openxmlformats-package.relationships+xml" pkg:padding="256"><pkg:xmlData><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/customXmlProps" Target="itemProps1.xml"/></Relationships></pkg:xmlData></pkg:part><pkg:part pkg:name="/customXml/itemProps1.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.customXmlProperties+xml" pkg:padding="32"><pkg:xmlData pkg:originalXmlStandalone="no"><ds:datastoreItem ds:itemID="{EE9FDEE8-7FFC-4E6D-A9BC-33BE7B5A13FC}" xmlns:ds="http://schemas.openxmlformats.org/officeDocument/2006/customXml"><ds:schemaRefs><ds:schemaRef ds:uri="http://schemas.openxmlformats.org/officeDocument/2006/bibliography"/></ds:schemaRefs></ds:datastoreItem></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/fontTable.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml"><pkg:xmlData><w:fonts mc:Ignorable="w14" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"><w:font w:name="Symbol"><w:panose1 w:val="05050102010706020507"/><w:charset w:val="02"/><w:family w:val="roman"/><w:pitch w:val="variable"/><w:sig w:usb0="00000000" w:usb1="10000000" w:usb2="00000000" w:usb3="00000000" w:csb0="80000000" w:csb1="00000000"/></w:font><w:font w:name="Times New Roman"><w:panose1 w:val="02020603050405020304"/><w:charset w:val="00"/><w:family w:val="roman"/><w:pitch w:val="variable"/><w:sig w:usb0="E0002EFF" w:usb1="C0007843" w:usb2="00000009" w:usb3="00000000" w:csb0="000001FF" w:csb1="00000000"/></w:font><w:font w:name="Courier New"><w:panose1 w:val="02070309020205020404"/><w:charset w:val="00"/><w:family w:val="modern"/><w:pitch w:val="fixed"/><w:sig w:usb0="E0002EFF" w:usb1="C0007843" w:usb2="00000009" w:usb3="00000000" w:csb0="000001FF" w:csb1="00000000"/></w:font><w:font w:name="Wingdings"><w:panose1 w:val="05000000000000000000"/><w:charset w:val="02"/><w:family w:val="auto"/><w:pitch w:val="variable"/><w:sig w:usb0="00000000" w:usb1="10000000" w:usb2="00000000" w:usb3="00000000" w:csb0="80000000" w:csb1="00000000"/></w:font><w:font w:name="Calibri"><w:panose1 w:val="020F0502020204030204"/><w:charset w:val="00"/><w:family w:val="swiss"/><w:pitch w:val="variable"/><w:sig w:usb0="E00002FF" w:usb1="4000ACFF" w:usb2="00000001" w:usb3="00000000" w:csb0="0000019F" w:csb1="00000000"/></w:font><w:font w:name="Segoe UI"><w:panose1 w:val="020B0502040204020203"/><w:charset w:val="00"/><w:family w:val="swiss"/><w:pitch w:val="variable"/><w:sig w:usb0="E4002EFF" w:usb1="C000E47F" w:usb2="00000009" w:usb3="00000000" w:csb0="000001FF" w:csb1="00000000"/></w:font><w:font w:name="Arial"><w:panose1 w:val="020B0604020202020204"/><w:charset w:val="00"/><w:family w:val="swiss"/><w:pitch w:val="variable"/><w:sig w:usb0="E0002EFF" w:usb1="C0007843" w:usb2="00000009" w:usb3="00000000" w:csb0="000001FF" w:csb1="00000000"/></w:font><w:font w:name="Arial Narrow"><w:panose1 w:val="020B0606020202030204"/><w:charset w:val="00"/><w:family w:val="swiss"/><w:pitch w:val="variable"/><w:sig w:usb0="00000287" w:usb1="00000800" w:usb2="00000000" w:usb3="00000000" w:csb0="0000009F" w:csb1="00000000"/></w:font><w:font w:name="Cambria"><w:panose1 w:val="02040503050406030204"/><w:charset w:val="00"/><w:family w:val="roman"/><w:pitch w:val="variable"/><w:sig w:usb0="E00002FF" w:usb1="400004FF" w:usb2="00000000" w:usb3="00000000" w:csb0="0000019F" w:csb1="00000000"/></w:font></w:fonts></pkg:xmlData></pkg:part><pkg:part pkg:name="/customXml/item1.xml" pkg:contentType="application/xml" pkg:padding="32"><pkg:xmlData><b:Sources SelectedStyle="\APA.XSL" StyleName="APA Fifth Edition" xmlns:b="http://schemas.openxmlformats.org/officeDocument/2006/bibliography" xmlns="http://schemas.openxmlformats.org/officeDocument/2006/bibliography"/></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/webSettings.xml" pkg:contentType="application/vnd.openxmlformats-officedocument.wordprocessingml.webSettings+xml"><pkg:xmlData><w:webSettings mc:Ignorable="w14" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"><w:divs><w:div w:id="1206288148"><w:bodyDiv w:val="1"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/></w:divBdr></w:div><w:div w:id="1361931375"><w:bodyDiv w:val="1"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/></w:divBdr></w:div><w:div w:id="1956525405"><w:bodyDiv w:val="1"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/></w:divBdr></w:div><w:div w:id="2040355805"><w:bodyDiv w:val="1"/><w:marLeft w:val="0"/><w:marRight w:val="0"/><w:marTop w:val="0"/><w:marBottom w:val="0"/><w:divBdr><w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/></w:divBdr></w:div></w:divs><w:optimizeForBrowser/></w:webSettings></pkg:xmlData></pkg:part><pkg:part pkg:name="/word/stylesWithEffects.xml" pkg:contentType="application/vnd.ms-word.stylesWithEffects+xml"><pkg:xmlData><w:styles mc:Ignorable="w14 wp14" xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:asciiTheme="minorHAnsi" w:eastAsiaTheme="minorHAnsi" w:hAnsiTheme="minorHAnsi" w:cstheme="minorBidi"/><w:sz w:val="22"/><w:szCs w:val="22"/><w:lang w:val="en-GB" w:eastAsia="en-US" w:bidi="ar-SA"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="200" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults><w:latentStyles w:defLockedState="0" w:defUIPriority="99" w:defSemiHidden="1" w:defUnhideWhenUsed="1" w:defQFormat="0" w:count="267"><w:lsdException w:name="Normal" w:semiHidden="0" w:uiPriority="0" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="heading 1" w:semiHidden="0" w:uiPriority="9" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="heading 2" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 3" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 4" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 5" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 6" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 7" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 8" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="heading 9" w:uiPriority="9" w:qFormat="1"/><w:lsdException w:name="toc 1" w:uiPriority="39"/><w:lsdException w:name="toc 2" w:uiPriority="39"/><w:lsdException w:name="toc 3" w:uiPriority="39"/><w:lsdException w:name="toc 4" w:uiPriority="39"/><w:lsdException w:name="toc 5" w:uiPriority="39"/><w:lsdException w:name="toc 6" w:uiPriority="39"/><w:lsdException w:name="toc 7" w:uiPriority="39"/><w:lsdException w:name="toc 8" w:uiPriority="39"/><w:lsdException w:name="toc 9" w:uiPriority="39"/><w:lsdException w:name="caption" w:uiPriority="35" w:qFormat="1"/><w:lsdException w:name="Title" w:semiHidden="0" w:uiPriority="10" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Default Paragraph Font" w:uiPriority="1"/><w:lsdException w:name="Subtitle" w:semiHidden="0" w:uiPriority="11" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Hyperlink" w:uiPriority="0"/><w:lsdException w:name="Strong" w:semiHidden="0" w:uiPriority="22" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Emphasis" w:semiHidden="0" w:uiPriority="20" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Table Grid" w:semiHidden="0" w:uiPriority="59" w:unhideWhenUsed="0"/><w:lsdException w:name="Placeholder Text" w:unhideWhenUsed="0"/><w:lsdException w:name="No Spacing" w:semiHidden="0" w:uiPriority="1" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Light Shading" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 1" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 1" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 1" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 1" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 1" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 1" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Revision" w:unhideWhenUsed="0"/><w:lsdException w:name="List Paragraph" w:semiHidden="0" w:uiPriority="34" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Quote" w:semiHidden="0" w:uiPriority="29" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Intense Quote" w:semiHidden="0" w:uiPriority="30" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Medium List 2 Accent 1" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 1" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 1" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 1" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 1" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 1" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 1" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 1" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 2" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 2" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 2" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 2" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 2" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 2" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 2" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 2" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 2" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 2" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 2" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 2" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 2" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 2" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 3" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 3" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 3" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 3" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 3" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 3" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 3" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 3" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 3" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 3" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 3" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 3" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 3" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 3" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 4" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 4" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 4" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 4" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 4" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 4" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 4" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 4" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 4" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 4" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 4" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 4" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 4" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 4" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 5" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 5" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 5" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 5" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 5" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 5" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 5" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 5" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 5" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 5" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 5" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 5" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 5" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 5" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Shading Accent 6" w:semiHidden="0" w:uiPriority="60" w:unhideWhenUsed="0"/><w:lsdException w:name="Light List Accent 6" w:semiHidden="0" w:uiPriority="61" w:unhideWhenUsed="0"/><w:lsdException w:name="Light Grid Accent 6" w:semiHidden="0" w:uiPriority="62" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 1 Accent 6" w:semiHidden="0" w:uiPriority="63" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Shading 2 Accent 6" w:semiHidden="0" w:uiPriority="64" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 1 Accent 6" w:semiHidden="0" w:uiPriority="65" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium List 2 Accent 6" w:semiHidden="0" w:uiPriority="66" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 1 Accent 6" w:semiHidden="0" w:uiPriority="67" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 2 Accent 6" w:semiHidden="0" w:uiPriority="68" w:unhideWhenUsed="0"/><w:lsdException w:name="Medium Grid 3 Accent 6" w:semiHidden="0" w:uiPriority="69" w:unhideWhenUsed="0"/><w:lsdException w:name="Dark List Accent 6" w:semiHidden="0" w:uiPriority="70" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Shading Accent 6" w:semiHidden="0" w:uiPriority="71" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful List Accent 6" w:semiHidden="0" w:uiPriority="72" w:unhideWhenUsed="0"/><w:lsdException w:name="Colorful Grid Accent 6" w:semiHidden="0" w:uiPriority="73" w:unhideWhenUsed="0"/><w:lsdException w:name="Subtle Emphasis" w:semiHidden="0" w:uiPriority="19" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Intense Emphasis" w:semiHidden="0" w:uiPriority="21" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Subtle Reference" w:semiHidden="0" w:uiPriority="31" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Intense Reference" w:semiHidden="0" w:uiPriority="32" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Book Title" w:semiHidden="0" w:uiPriority="33" w:unhideWhenUsed="0" w:qFormat="1"/><w:lsdException w:name="Bibliography" w:uiPriority="37"/><w:lsdException w:name="TOC Heading" w:uiPriority="39" w:qFormat="1"/></w:latentStyles><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/><w:rsid w:val="00FB687E"/><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="character" w:default="1" w:styleId="DefaultParagraphFont"><w:name w:val="Default Paragraph Font"/><w:uiPriority w:val="1"/><w:semiHidden/><w:unhideWhenUsed/></w:style><w:style w:type="table" w:default="1" w:styleId="TableNormal"><w:name w:val="Normal Table"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:tblPr><w:tblInd w:w="0" w:type="dxa"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="108" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="108" w:type="dxa"/></w:tblCellMar></w:tblPr></w:style><w:style w:type="numbering" w:default="1" w:styleId="NoList"><w:name w:val="No List"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/></w:style><w:style w:type="paragraph" w:styleId="NoSpacing"><w:name w:val="No Spacing"/><w:uiPriority w:val="1"/><w:qFormat/><w:rsid w:val="00FB687E"/><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="character" w:styleId="Hyperlink"><w:name w:val="Hyperlink"/><w:basedOn w:val="DefaultParagraphFont"/><w:unhideWhenUsed/><w:rsid w:val="00FB687E"/><w:rPr><w:color w:val="0000FF"/><w:u w:val="single"/></w:rPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="apple-style-span"><w:name w:val="apple-style-span"/><w:basedOn w:val="DefaultParagraphFont"/><w:rsid w:val="00DF08E0"/></w:style><w:style w:type="paragraph" w:styleId="Header"><w:name w:val="header"/><w:basedOn w:val="Normal"/><w:link w:val="HeaderChar"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="00DF08E0"/><w:pPr><w:tabs><w:tab w:val="center" w:pos="4513"/><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="HeaderChar"><w:name w:val="Header Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="Header"/><w:uiPriority w:val="99"/><w:semiHidden/><w:rsid w:val="00DF08E0"/><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="Footer"><w:name w:val="footer"/><w:basedOn w:val="Normal"/><w:link w:val="FooterChar"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="00DF08E0"/><w:pPr><w:tabs><w:tab w:val="center" w:pos="4513"/><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="FooterChar"><w:name w:val="Footer Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="Footer"/><w:uiPriority w:val="99"/><w:semiHidden/><w:rsid w:val="00DF08E0"/><w:rPr><w:rFonts w:ascii="Calibri" w:eastAsia="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="NormalWeb"><w:name w:val="Normal (Web)"/><w:basedOn w:val="Normal"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="002F3F4B"/><w:pPr><w:spacing w:before="100" w:beforeAutospacing="1" w:after="100" w:afterAutospacing="1" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Times New Roman" w:eastAsiaTheme="minorHAnsi" w:hAnsi="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/><w:lang w:eastAsia="en-GB"/></w:rPr></w:style><w:style w:type="paragraph" w:styleId="BalloonText"><w:name w:val="Balloon Text"/><w:basedOn w:val="Normal"/><w:link w:val="BalloonTextChar"/><w:uiPriority w:val="99"/><w:semiHidden/><w:unhideWhenUsed/><w:rsid w:val="00227314"/><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Segoe UI" w:hAnsi="Segoe UI" w:cs="Segoe UI"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="BalloonTextChar"><w:name w:val="Balloon Text Char"/><w:basedOn w:val="DefaultParagraphFont"/><w:link w:val="BalloonText"/><w:uiPriority w:val="99"/><w:semiHidden/><w:rsid w:val="00227314"/><w:rPr><w:rFonts w:ascii="Segoe UI" w:eastAsia="Calibri" w:hAnsi="Segoe UI" w:cs="Segoe UI"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr></w:style><w:style w:type="character" w:customStyle="1" w:styleId="apple-converted-space"><w:name w:val="apple-converted-space"/><w:basedOn w:val="DefaultParagraphFont"/><w:rsid w:val="00913D34"/></w:style><w:style w:type="character" w:styleId="Emphasis"><w:name w:val="Emphasis"/><w:basedOn w:val="DefaultParagraphFont"/><w:uiPriority w:val="20"/><w:qFormat/><w:rsid w:val="00913D34"/><w:rPr><w:i/><w:iCs/></w:rPr></w:style></w:styles></pkg:xmlData></pkg:part><pkg:part pkg:name="/docProps/core.xml" pkg:contentType="application/vnd.openxmlformats-package.core-properties+xml" pkg:padding="256"><pkg:xmlData><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>Joe Dyble</dc:creator><cp:lastModifiedBy>Kevin Beckett</cp:lastModifiedBy><cp:revision>2</cp:revision><cp:lastPrinted>2015-05-26T08:36:00Z</cp:lastPrinted><dcterms:created xsi:type="dcterms:W3CDTF">2016-04-28T15:41:00Z</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">2016-04-28T15:41:00Z</dcterms:modified></cp:coreProperties></pkg:xmlData></pkg:part></pkg:package>

</body>
</html>
	';
}

function web_archive_file()
{
	# the contents of a .mht file exported from Word as a "single web page"
	
	return '
MIME-Version: 1.0
Content-Type: multipart/related; boundary="----=_NextPart_01D1A5F3.8D83DC00"

This document is a Single File Web Page, also known as a Web Archive file.  If you are seeing this message, your browser or editor doesn\'t support Web Archive files.  Please download a browser that supports Web Archive, such as Windows® Internet Explorer®.

------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset="us-ascii"

<html xmlns:v=3D"urn:schemas-microsoft-com:vml"
xmlns:o=3D"urn:schemas-microsoft-com:office:office"
xmlns:w=3D"urn:schemas-microsoft-com:office:word"
xmlns:m=3D"http://schemas.microsoft.com/office/2004/12/omml"
xmlns=3D"http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=3DContent-Type content=3D"text/html; charset=3Dus-ascii">
<meta name=3DProgId content=3DWord.Document>
<meta name=3DGenerator content=3D"Microsoft Word 14">
<meta name=3DOriginator content=3D"Microsoft Word 14">
<link rel=3DFile-List href=3D"skycue_files/filelist.xml">
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>Joe Dyble</o:Author>
  <o:LastAuthor>Kevin Beckett</o:LastAuthor>
  <o:Revision>2</o:Revision>
  <o:TotalTime>1</o:TotalTime>
  <o:LastPrinted>2015-05-26T08:36:00Z</o:LastPrinted>
  <o:Created>2016-05-04T09:55:00Z</o:Created>
  <o:LastSaved>2016-05-04T09:55:00Z</o:LastSaved>
  <o:Pages>2</o:Pages>
  <o:Words>739</o:Words>
  <o:Characters>4216</o:Characters>
  <o:Company>Hewlett-Packard Company</o:Company>
  <o:Lines>35</o:Lines>
  <o:Paragraphs>9</o:Paragraphs>
  <o:CharactersWithSpaces>4946</o:CharactersWithSpaces>
  <o:Version>14.00</o:Version>
 </o:DocumentProperties>
</xml><![endif]-->
<link rel=3DdataStoreItem href=3D"skycue_files/item0001.xml"
target=3D"skycue_files/props002.xml">
<link rel=3DthemeData href=3D"skycue_files/themedata.thmx">
<link rel=3DcolorSchemeMapping href=3D"skycue_files/colorschememapping.xml">
<!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:SpellingState>Clean</w:SpellingState>
  <w:GrammarState>Clean</w:GrammarState>
  <w:TrackMoves>false</w:TrackMoves>
  <w:TrackFormatting/>
  <w:PunctuationKerning/>
  <w:DrawingGridHorizontalSpacing>5.5 pt</w:DrawingGridHorizontalSpacing>
  <w:DisplayHorizontalDrawingGridEvery>2</w:DisplayHorizontalDrawingGridEve=
ry>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:DoNotPromoteQF/>
  <w:LidThemeOther>EN-GB</w:LidThemeOther>
  <w:LidThemeAsian>X-NONE</w:LidThemeAsian>
  <w:LidThemeComplexScript>X-NONE</w:LidThemeComplexScript>
  <w:Compatibility>
   <w:BreakWrappedTables/>
   <w:SnapToGridInCell/>
   <w:WrapTextWithPunct/>
   <w:UseAsianBreakRules/>
   <w:DontGrowAutofit/>
   <w:SplitPgBreakAndParaMark/>
   <w:EnableOpenTypeKerning/>
   <w:DontFlipMirrorIndents/>
   <w:OverrideTableStyleHps/>
  </w:Compatibility>
  <m:mathPr>
   <m:mathFont m:val=3D"Cambria Math"/>
   <m:brkBin m:val=3D"before"/>
   <m:brkBinSub m:val=3D"&#45;-"/>
   <m:smallFrac m:val=3D"off"/>
   <m:dispDef/>
   <m:lMargin m:val=3D"0"/>
   <m:rMargin m:val=3D"0"/>
   <m:defJc m:val=3D"centerGroup"/>
   <m:wrapIndent m:val=3D"1440"/>
   <m:intLim m:val=3D"subSup"/>
   <m:naryLim m:val=3D"undOvr"/>
  </m:mathPr></w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState=3D"false" DefUnhideWhenUsed=3D"true"
  DefSemiHidden=3D"true" DefQFormat=3D"false" DefPriority=3D"99"
  LatentStyleCount=3D"267">
  <w:LsdException Locked=3D"false" Priority=3D"0" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Normal"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"heading 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 7"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 8"/>
  <w:LsdException Locked=3D"false" Priority=3D"9" QFormat=3D"true" Name=3D"=
heading 9"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 7"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 8"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" Name=3D"toc 9"/>
  <w:LsdException Locked=3D"false" Priority=3D"35" QFormat=3D"true" Name=3D=
"caption"/>
  <w:LsdException Locked=3D"false" Priority=3D"10" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Title"/>
  <w:LsdException Locked=3D"false" Priority=3D"1" Name=3D"Default Paragraph=
 Font"/>
  <w:LsdException Locked=3D"false" Priority=3D"11" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Subtitle"/>
  <w:LsdException Locked=3D"false" Priority=3D"0" Name=3D"Hyperlink"/>
  <w:LsdException Locked=3D"false" Priority=3D"22" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Strong"/>
  <w:LsdException Locked=3D"false" Priority=3D"20" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Emphasis"/>
  <w:LsdException Locked=3D"false" Priority=3D"59" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Table Grid"/>
  <w:LsdException Locked=3D"false" UnhideWhenUsed=3D"false" Name=3D"Placeho=
lder Text"/>
  <w:LsdException Locked=3D"false" Priority=3D"1" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"No Spacing"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1 Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2 Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1 Accent 1"/>
  <w:LsdException Locked=3D"false" UnhideWhenUsed=3D"false" Name=3D"Revisio=
n"/>
  <w:LsdException Locked=3D"false" Priority=3D"34" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"List Paragraph"/>
  <w:LsdException Locked=3D"false" Priority=3D"29" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Quote"/>
  <w:LsdException Locked=3D"false" Priority=3D"30" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Intense Quote"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2 Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1 Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2 Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3 Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid Accent 1"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3 Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid Accent 2"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3 Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid Accent 3"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3 Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid Accent 4"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3 Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid Accent 5"/>
  <w:LsdException Locked=3D"false" Priority=3D"60" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Shading Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"61" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light List Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"62" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Light Grid Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"63" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 1 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"64" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Shading 2 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"65" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 1 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"66" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium List 2 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"67" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 1 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"68" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 2 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"69" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Medium Grid 3 Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"70" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Dark List Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"71" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Shading Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"72" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful List Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"73" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" Name=3D"Colorful Grid Accent 6"/>
  <w:LsdException Locked=3D"false" Priority=3D"19" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Subtle Emphasis"/>
  <w:LsdException Locked=3D"false" Priority=3D"21" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Intense Emphasis"/>
  <w:LsdException Locked=3D"false" Priority=3D"31" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Subtle Reference"/>
  <w:LsdException Locked=3D"false" Priority=3D"32" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Intense Reference"/>
  <w:LsdException Locked=3D"false" Priority=3D"33" SemiHidden=3D"false"
   UnhideWhenUsed=3D"false" QFormat=3D"true" Name=3D"Book Title"/>
  <w:LsdException Locked=3D"false" Priority=3D"37" Name=3D"Bibliography"/>
  <w:LsdException Locked=3D"false" Priority=3D"39" QFormat=3D"true" Name=3D=
"TOC Heading"/>
 </w:LatentStyles>
</xml><![endif]-->
<style>
<!--
 /* Font Definitions */
 @font-face
	{font-family:Wingdings;
	panose-1:5 0 0 0 0 0 0 0 0 0;
	mso-font-charset:2;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:0 268435456 0 0 -2147483648 0;}
@font-face
	{font-family:Wingdings;
	panose-1:5 0 0 0 0 0 0 0 0 0;
	mso-font-charset:2;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:0 268435456 0 0 -2147483648 0;}
@font-face
	{font-family:Calibri;
	panose-1:2 15 5 2 2 2 4 3 2 4;
	mso-font-charset:0;
	mso-generic-font-family:swiss;
	mso-font-pitch:variable;
	mso-font-signature:-536870145 1073786111 1 0 415 0;}
@font-face
	{font-family:"Segoe UI";
	panose-1:2 11 5 2 4 2 4 2 2 3;
	mso-font-charset:0;
	mso-generic-font-family:swiss;
	mso-font-pitch:variable;
	mso-font-signature:-469750017 -1073683329 9 0 511 0;}
@font-face
	{font-family:"Arial Narrow";
	panose-1:2 11 6 6 2 2 2 3 2 4;
	mso-font-charset:0;
	mso-generic-font-family:swiss;
	mso-font-pitch:variable;
	mso-font-signature:647 2048 0 0 159 0;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-unhide:no;
	mso-style-qformat:yes;
	mso-style-parent:"";
	margin-top:0cm;
	margin-right:0cm;
	margin-bottom:10.0pt;
	margin-left:0cm;
	line-height:115%;
	mso-pagination:widow-orphan;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";
	mso-fareast-font-family:Calibri;
	mso-bidi-font-family:"Times New Roman";
	mso-fareast-language:EN-US;}
p.MsoHeader, li.MsoHeader, div.MsoHeader
	{mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-link:"Header Char";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	tab-stops:center 225.65pt right 451.3pt;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";
	mso-fareast-font-family:Calibri;
	mso-bidi-font-family:"Times New Roman";
	mso-fareast-language:EN-US;}
p.MsoFooter, li.MsoFooter, div.MsoFooter
	{mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-link:"Footer Char";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	tab-stops:center 225.65pt right 451.3pt;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";
	mso-fareast-font-family:Calibri;
	mso-bidi-font-family:"Times New Roman";
	mso-fareast-language:EN-US;}
a:link, span.MsoHyperlink
	{color:blue;
	text-decoration:underline;
	text-underline:single;}
a:visited, span.MsoHyperlinkFollowed
	{mso-style-noshow:yes;
	mso-style-priority:99;
	color:purple;
	mso-themecolor:followedhyperlink;
	text-decoration:underline;
	text-underline:single;}
p
	{mso-style-noshow:yes;
	mso-style-priority:99;
	mso-margin-top-alt:auto;
	margin-right:0cm;
	mso-margin-bottom-alt:auto;
	margin-left:0cm;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Times New Roman","serif";
	mso-fareast-font-family:Calibri;
	mso-fareast-theme-font:minor-latin;}
p.MsoAcetate, li.MsoAcetate, div.MsoAcetate
	{mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-link:"Balloon Text Char";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:9.0pt;
	font-family:"Segoe UI","sans-serif";
	mso-fareast-font-family:Calibri;
	mso-fareast-language:EN-US;}
p.MsoNoSpacing, li.MsoNoSpacing, div.MsoNoSpacing
	{mso-style-priority:1;
	mso-style-unhide:no;
	mso-style-qformat:yes;
	mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";
	mso-fareast-font-family:Calibri;
	mso-bidi-font-family:"Times New Roman";
	mso-fareast-language:EN-US;}
span.apple-style-span
	{mso-style-name:apple-style-span;
	mso-style-unhide:no;}
span.HeaderChar
	{mso-style-name:"Header Char";
	mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-unhide:no;
	mso-style-locked:yes;
	mso-style-link:Header;
	font-family:"Calibri","sans-serif";
	mso-ascii-font-family:Calibri;
	mso-fareast-font-family:Calibri;
	mso-hansi-font-family:Calibri;
	mso-bidi-font-family:"Times New Roman";}
span.FooterChar
	{mso-style-name:"Footer Char";
	mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-unhide:no;
	mso-style-locked:yes;
	mso-style-link:Footer;
	font-family:"Calibri","sans-serif";
	mso-ascii-font-family:Calibri;
	mso-fareast-font-family:Calibri;
	mso-hansi-font-family:Calibri;
	mso-bidi-font-family:"Times New Roman";}
span.BalloonTextChar
	{mso-style-name:"Balloon Text Char";
	mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-unhide:no;
	mso-style-locked:yes;
	mso-style-link:"Balloon Text";
	mso-ansi-font-size:9.0pt;
	mso-bidi-font-size:9.0pt;
	font-family:"Segoe UI","sans-serif";
	mso-ascii-font-family:"Segoe UI";
	mso-fareast-font-family:Calibri;
	mso-hansi-font-family:"Segoe UI";
	mso-bidi-font-family:"Segoe UI";}
span.apple-converted-space
	{mso-style-name:apple-converted-space;
	mso-style-unhide:no;}
span.SpellE
	{mso-style-name:"";
	mso-spl-e:yes;}
span.GramE
	{mso-style-name:"";
	mso-gram-e:yes;}
.MsoChpDefault
	{mso-style-type:export-only;
	mso-default-props:yes;
	font-family:"Calibri","sans-serif";
	mso-ascii-font-family:Calibri;
	mso-ascii-theme-font:minor-latin;
	mso-fareast-font-family:Calibri;
	mso-fareast-theme-font:minor-latin;
	mso-hansi-font-family:Calibri;
	mso-hansi-theme-font:minor-latin;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;
	mso-fareast-language:EN-US;}
.MsoPapDefault
	{mso-style-type:export-only;
	margin-bottom:10.0pt;
	line-height:115%;}
 /* Page Definitions */
 @page
	{mso-footnote-separator:url("skycue_files/header.htm") fs;
	mso-footnote-continuation-separator:url("skycue_files/header.htm") fcs;
	mso-endnote-separator:url("skycue_files/header.htm") es;
	mso-endnote-continuation-separator:url("skycue_files/header.htm") ecs;}
@page WordSection1
	{size:595.3pt 841.9pt;
	margin:5.65pt 72.0pt 0cm 72.0pt;
	mso-header-margin:0cm;
	mso-footer-margin:0cm;
	mso-paper-source:0;}
div.WordSection1
	{page:WordSection1;}
 /* List Definitions */
 @list l0
	{mso-list-id:116531776;
	mso-list-type:hybrid;
	mso-list-template-ids:1352849710 134807553 134807555 134807557 134807553 1=
34807555 134807557 134807553 134807555 134807557;}
@list l0:level1
	{mso-level-number-format:bullet;
	mso-level-text:\F0B7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l0:level2
	{mso-level-number-format:bullet;
	mso-level-text:o;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:"Courier New";}
@list l0:level3
	{mso-level-number-format:bullet;
	mso-level-text:\F0A7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Wingdings;}
@list l0:level4
	{mso-level-number-format:bullet;
	mso-level-text:\F0B7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l0:level5
	{mso-level-number-format:bullet;
	mso-level-text:o;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:"Courier New";}
@list l0:level6
	{mso-level-number-format:bullet;
	mso-level-text:\F0A7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Wingdings;}
@list l0:level7
	{mso-level-number-format:bullet;
	mso-level-text:\F0B7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l0:level8
	{mso-level-number-format:bullet;
	mso-level-text:o;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:"Courier New";}
@list l0:level9
	{mso-level-number-format:bullet;
	mso-level-text:\F0A7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Wingdings;}
@list l1
	{mso-list-id:311369835;
	mso-list-type:hybrid;
	mso-list-template-ids:-1431797794 67698703 67698713 67698715 67698703 6769=
8713 67698715 67698703 67698713 67698715;}
@list l1:level1
	{mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level2
	{mso-level-number-format:alpha-lower;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level3
	{mso-level-number-format:roman-lower;
	mso-level-tab-stop:none;
	mso-level-number-position:right;
	text-indent:-9.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level4
	{mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level5
	{mso-level-number-format:alpha-lower;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level6
	{mso-level-number-format:roman-lower;
	mso-level-tab-stop:none;
	mso-level-number-position:right;
	text-indent:-9.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level7
	{mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level8
	{mso-level-number-format:alpha-lower;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l1:level9
	{mso-level-number-format:roman-lower;
	mso-level-tab-stop:none;
	mso-level-number-position:right;
	text-indent:-9.0pt;
	mso-bidi-font-family:"Times New Roman";}
@list l2
	{mso-list-id:1987472296;
	mso-list-type:hybrid;
	mso-list-template-ids:-1356027478 134807553 134807555 134807557 134807553 =
134807555 134807557 134807553 134807555 134807557;}
@list l2:level1
	{mso-level-number-format:bullet;
	mso-level-text:\F0B7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l2:level2
	{mso-level-number-format:bullet;
	mso-level-text:o;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:"Courier New";}
@list l2:level3
	{mso-level-number-format:bullet;
	mso-level-text:\F0A7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Wingdings;}
@list l2:level4
	{mso-level-number-format:bullet;
	mso-level-text:\F0B7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l2:level5
	{mso-level-number-format:bullet;
	mso-level-text:o;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:"Courier New";}
@list l2:level6
	{mso-level-number-format:bullet;
	mso-level-text:\F0A7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Wingdings;}
@list l2:level7
	{mso-level-number-format:bullet;
	mso-level-text:\F0B7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l2:level8
	{mso-level-number-format:bullet;
	mso-level-text:o;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:"Courier New";}
@list l2:level9
	{mso-level-number-format:bullet;
	mso-level-text:\F0A7;
	mso-level-tab-stop:none;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Wingdings;}
ol
	{margin-bottom:0cm;}
ul
	{margin-bottom:0cm;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-priority:99;
	mso-style-parent:"";
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin-top:0cm;
	mso-para-margin-right:0cm;
	mso-para-margin-bottom:10.0pt;
	mso-para-margin-left:0cm;
	line-height:115%;
	mso-pagination:widow-orphan;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";
	mso-ascii-font-family:Calibri;
	mso-ascii-theme-font:minor-latin;
	mso-hansi-font-family:Calibri;
	mso-hansi-theme-font:minor-latin;
	mso-fareast-language:EN-US;}
</style>
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext=3D"edit" spidmax=3D"1026"/>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext=3D"edit">
  <o:idmap v:ext=3D"edit" data=3D"1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=3DEN-GB link=3Dblue vlink=3Dpurple style=3D\'tab-interval:36.0pt\'>

<div class=3DWordSection1>

<table class=3DMsoNormalTable border=3D1 cellspacing=3D0 cellpadding=3D0 al=
ign=3Dleft
 width=3D747 style=3D\'width:559.95pt;border-collapse:collapse;border:none;
 mso-border-alt:solid windowtext 6.0pt;mso-table-lspace:9.0pt;margin-left:6=
.75pt;
 mso-table-rspace:9.0pt;margin-right:6.75pt;mso-table-anchor-vertical:page;
 mso-table-anchor-horizontal:margin;mso-table-left:left;mso-table-top:18.05=
pt;
 mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:3.0pt solid windowt=
ext;
 mso-border-insidev:3.0pt solid windowtext\'>
 <tr style=3D\'mso-yfti-irow:0;mso-yfti-firstrow:yes;height:1.05pt\'>
  <td width=3D747 colspan=3D2 valign=3Dtop style=3D\'width:559.95pt;border:s=
olid windowtext 6.0pt;
  border-bottom:solid windowtext 3.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:1=
.05pt\'>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:24.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>ISDN
  INTERVIEW OPPORTUNITY</span></b><span style=3D\'font-size:24.0pt;mso-ascii=
-font-family:
  Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;
  mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;mso-bidi-th=
eme-font:
  minor-latin\'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style=3D\'mso-yfti-irow:1;height:29.7pt\'>
  <td width=3D747 colspan=3D2 style=3D\'width:559.95pt;border-top:none;borde=
r-left:
  solid windowtext 6.0pt;border-bottom:solid windowtext 3.0pt;border-right:
  solid windowtext 6.0pt;mso-border-top-alt:solid windowtext 3.0pt;padding:
  0cm 5.4pt 0cm 5.4pt;height:29.7pt\'>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:14.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>DATE:
  MONDAY 1 JUNE 2015<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:9.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin;
  color:red\'>EMBARGOED UNTIL 00:01 01/06/15<o:p></o:p></span></b></p>
  </td>
 </tr>
 <tr style=3D\'mso-yfti-irow:2;height:22.75pt\'>
  <td width=3D747 colspan=3D2 style=3D\'width:559.95pt;border-top:none;borde=
r-left:
  solid windowtext 6.0pt;border-bottom:solid windowtext 3.0pt;border-right:
  solid windowtext 6.0pt;mso-border-top-alt:solid windowtext 3.0pt;padding:
  0cm 5.4pt 0cm 5.4pt;height:22.75pt\'>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:14.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>GUESTS:<span
  style=3D\'mso-spacerun:yes\'>&nbsp; </span>CHARLES COUNSELL &#8211; </span>=
</b><span
  style=3D\'font-family:"Arial","sans-serif";color:#545454;background:white\'=
><span
  style=3D\'mso-spacerun:yes\'>&nbsp;</span></span><b style=3D\'mso-bidi-font-=
weight:
  normal\'><span style=3D\'font-size:14.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>&nbsp;</span></b><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'font-size:12.0pt;mso=
-ascii-font-family:
  Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;
  mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;mso-bidi-th=
eme-font:
  minor-latin\'>EXECUTIVE DIRECTOR,</span></b><b style=3D\'mso-bidi-font-weig=
ht:
  normal\'><span style=3D\'font-size:14.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
> </span></b><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'font-size:12.0pt;mso=
-ascii-font-family:
  Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;
  mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;mso-bidi-th=
eme-font:
  minor-latin\'>THE PENSIONS REGULATOR<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:14.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>MORTEN
  NILSSON &#8211; </span></b><b style=3D\'mso-bidi-font-weight:normal\'><span
  style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-f=
ont:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>CEO, NOW:
  PENSIONS</span></b><span style=3D\'font-size:14.0pt;mso-ascii-font-family:=
Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style=3D\'mso-yfti-irow:3;height:420.9pt\'>
  <td width=3D213 valign=3Dtop style=3D\'width:160.05pt;border-top:none;bord=
er-left:
  solid windowtext 6.0pt;border-bottom:solid windowtext 3.0pt;border-right:
  solid windowtext 3.0pt;mso-border-top-alt:solid windowtext 3.0pt;padding:
  0cm 5.4pt 0cm 5.4pt;height:420.9pt\'>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b><span
  style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-f=
ont:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>GUESTS: </s=
pan></b><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'font-size:12.0pt;mso=
-ascii-font-family:
  Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;
  mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;mso-bidi-th=
eme-font:
  minor-latin\'>CHARLES COUNSELL</span></b><span style=3D\'font-size:12.0pt;
  mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-=
font-family:
  Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;
  mso-bidi-theme-font:minor-latin\'> - EXECUTIVE DIRECTOR, THE PENSIONS
  REGULATOR<b><o:p></o:p></b></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:5.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-fo=
nt:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'><o:p>&nbsp;=
</o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>MORTEN
  NILSSON</span></b><span style=3D\'font-size:12.0pt;mso-ascii-font-family:C=
alibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
> -
  CEO, NOW: PENSIONS<o:p></o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:5.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-fo=
nt:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'><o:p>&nbsp;=
</o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b><span
  style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-f=
ont:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>BIOGRAPHY:<=
o:p></o:p></span></b></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span lang=3DEN style=3D\'font-size:9.0pt;font-family:"Arial Narro=
w","sans-serif";
  mso-bidi-font-family:Arial;mso-ansi-language:EN\'>Charles <span class=3DSp=
ellE>Counsell</span></span></b><span
  lang=3DEN style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif=
";
  mso-bidi-font-family:Arial;mso-ansi-language:EN\'>&nbsp;became executive
  director for automatic enrolment at The Pensions Regulator&nbsp;in 2011.
  Charles was at the regulator in 2006&nbsp;- 2007, and since 2008 has been
  involved with the design and delivery of automatic enrolment.<o:p></o:p><=
/span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span lang=3DEN
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin\'><span
  style=3D\'mso-spacerun:yes\'>&nbsp;</span></span><span style=3D\'font-size:9=
.0pt;
  font-family:"Arial Narrow","sans-serif";mso-bidi-font-family:Calibri;
  mso-bidi-theme-font:minor-latin\'><o:p></o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif"\'>Prior t=
o NOW:
  Pensions, <b style=3D\'mso-bidi-font-weight:normal\'>Morten Nilsson</b> was=
 Vice
  President and Head of International Operations at ATP. He has over 20
  years&#8217; experience in the financial services sector, predominantly
  within business development, operations, strategy and transformation.<o:p=
></o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:5.0pt;font-family:"Arial Narrow","sans-serif";mso-fare=
ast-font-family:
  Calibri;mso-fareast-theme-font:minor-latin\'><o:p>&nbsp;</o:p></span></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b><span
  style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-f=
ont:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>NOTES TO
  EDITORS:<o:p></o:p></span></b></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin;color:black\'>1. </span><a
  href=3D"http://www.thepensionsregulator.gov.uk/docs/pensions-reform-compl=
iance-and-enforcement-policy.pdf"><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin\'>Enforcement and compliance polic=
y</span></a><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin;color:black\'><o:p></o:p></span></=
p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:5.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Arial;mso-bidi-font-weight:bold\'><o:p>&nbsp;</o:p></span></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Arial;mso-bidi-font-weight:bold\'>2. Research undertaken by BDRC Continent=
al,
  an award-winning insight agency. Questions were put to 400 UK SMEs (up to=
 and
  including 250 employees) via BDRC Continental&#8217;s monthly Business
  Opinion Omnibus. 2 - 12 March 2015. Of those surveyed, 269 SMEs are yet to
  stage.<o:p></o:p></span></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Arial;mso-bidi-font-weight:bold\'><o:p>&nbsp;</o:p></span></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:5.0pt;font-family:"Arial","sans-serif";mso-bidi-font-w=
eight:
  bold\'><o:p>&nbsp;</o:p></span></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-s=
erif";
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin;color:black\'=
>WHAT
  IS AUTOMATIC ENROLMENT? <o:p></o:p></span></b></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin;color:black\'>A slice of an employ=
ee\'s
  pay packet is diverted to their pension fund, assuming they are aged <span
  class=3DGramE>between 22 - 65</span> and earning more than &pound;10,000 a
  year. Employers are obliged to pay in as well, with the government adding=
 a
  little extra through tax relief.<o:p></o:p></span></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:5.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin;color:black\'><o:p>&nbsp;</o:p></s=
pan></p>
  <p class=3DMsoNoSpacing style=3D\'text-align:justify;mso-element:frame;mso=
-element-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:9.0pt;font-family:"Arial Narrow","sans-serif";mso-bidi=
-font-family:
  Calibri;mso-bidi-theme-font:minor-latin;color:black\'>At first, an employee
  sees only a minimum of 0.8% of their earnings going to their workplace
  pension. Tax relief adds another 0.2% and the employer is obliged to add a
  contribution of 1% of the worker\'s earnings. This rises to 5% contribution
  from the employee, 3% from the employer, and 1% in tax relief by October
  2018. But there are concerns this many not be sufficient to ensure a
  reasonable income at retirement.<span style=3D\'mso-spacerun:yes\'>&nbsp; <=
/span><o:p></o:p></span></p>
  </td>
  <td width=3D533 rowspan=3D2 valign=3Dtop style=3D\'width:399.9pt;border-to=
p:none;
  border-left:none;border-bottom:solid windowtext 3.0pt;border-right:solid =
windowtext 6.0pt;
  mso-border-top-alt:solid windowtext 3.0pt;mso-border-left-alt:solid windo=
wtext 3.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:420.9pt\'>
  <p class=3DMsoNoSpacing align=3Dcenter style=3D\'text-align:center;mso-ele=
ment:frame;
  mso-element-frame-hspace:9.0pt;mso-element-wrap:around;mso-element-anchor=
-vertical:
  page;mso-element-anchor-horizontal:margin;mso-element-top:18.05pt;mso-hei=
ght-rule:
  exactly\'><b style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'font-siz=
e:3.0pt;
  mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-=
font-family:
  Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;
  mso-bidi-theme-font:minor-latin\'><o:p>&nbsp;</o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:20.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>MANDATORY
  AUTO ENROLMENT: <o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:20.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>THOUSANDS
  OF FIRMS STILL UNPREPARED<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-top:0cm;margin-right:=
0cm;
  margin-bottom:0cm;margin-left:12.85pt;margin-bottom:.0001pt;text-align:ce=
nter;
  text-indent:-12.85pt;line-height:normal;mso-list:l0 level1 lfo3;mso-eleme=
nt:
  frame;mso-element-frame-hspace:9.0pt;mso-element-wrap:around;mso-element-=
anchor-vertical:
  page;mso-element-anchor-horizontal:margin;mso-element-top:18.05pt;mso-hei=
ght-rule:
  exactly\'><![if !supportLists]><span style=3D\'font-family:Symbol;mso-farea=
st-font-family:
  Symbol;mso-bidi-font-family:Symbol\'><span style=3D\'mso-list:Ignore\'>&midd=
ot;<span
  style=3D\'font:7.0pt "Times New Roman"\'>&nbsp;&nbsp;&nbsp;&nbsp; </span></=
span></span><![endif]><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'mso-ascii-font-famil=
y:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>350,000
  SMALL FIRMS HAVEN&#8217;T GIVEN ANY THOUGHT TO FINDING A PENSION PROVIDER=
<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-top:0cm;margin-right:=
0cm;
  margin-bottom:0cm;margin-left:12.85pt;margin-bottom:.0001pt;text-align:ce=
nter;
  text-indent:-12.85pt;line-height:normal;mso-list:l0 level1 lfo3;mso-eleme=
nt:
  frame;mso-element-frame-hspace:9.0pt;mso-element-wrap:around;mso-element-=
anchor-vertical:
  page;mso-element-anchor-horizontal:margin;mso-element-top:18.05pt;mso-hei=
ght-rule:
  exactly\'><![if !supportLists]><span style=3D\'font-family:Symbol;mso-farea=
st-font-family:
  Symbol;mso-bidi-font-family:Symbol\'><span style=3D\'mso-list:Ignore\'>&midd=
ot;<span
  style=3D\'font:7.0pt "Times New Roman"\'>&nbsp;&nbsp;&nbsp;&nbsp; </span></=
span></span><![endif]><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'mso-ascii-font-famil=
y:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>A
  THIRD DO NOT UNDERSTAND HOW AUTO ENROLMENT CONTRIBUTIONS ARE CALCULATED<o=
:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-top:0cm;margin-right:=
0cm;
  margin-bottom:0cm;margin-left:12.85pt;margin-bottom:.0001pt;text-align:ce=
nter;
  text-indent:-12.85pt;line-height:normal;mso-list:l0 level1 lfo3;mso-eleme=
nt:
  frame;mso-element-frame-hspace:9.0pt;mso-element-wrap:around;mso-element-=
anchor-vertical:
  page;mso-element-anchor-horizontal:margin;mso-element-top:18.05pt;mso-hei=
ght-rule:
  exactly\'><![if !supportLists]><span style=3D\'font-family:Symbol;mso-farea=
st-font-family:
  Symbol;mso-bidi-font-family:Symbol\'><span style=3D\'mso-list:Ignore\'>&midd=
ot;<span
  style=3D\'font:7.0pt "Times New Roman"\'>&nbsp;&nbsp;&nbsp;&nbsp; </span></=
span></span><![endif]><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'mso-ascii-font-famil=
y:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>500,000
  COMPANIES HAVE TO COMPLY WITH WORKPLACE PENSION AUTO ENROLMENT LEGISLATIO=
N BY
  THE END OF 2016<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><o:p>&nbsp;</o:p>=
</p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'>Today (1st June) =
is a
  key milestone for pension reforms, as hundreds of thousands of small and
  micro firms &#8211; those with fewer than 30 staff - will have to begin
  complying with the new workplace <span class=3DGramE>pensions</span>
  legislation. Those who don&#8217;t, face fines<sup>1</sup> of up to
  &pound;500 a day.</p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><o:p>&nbsp;</o:p>=
</p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font=
:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin\'>However, new research<sup>2 </su=
p>from
  NOW: Pensions reveals that one in four (27%) of all businesses <span
  class=3DGramE>who</span> need to comply haven\'t yet given any thought as =
to how
  they will find a provider. <span style=3D\'mso-spacerun:yes\'>&nbsp;</span>=
<span
  style=3D\'mso-bidi-font-weight:bold\'>While this is an improvement on 2014 =
- when
  four in ten (44%) SMEs hadn&#8217;t thought about it - it still means that
  almost 350,000 businesses are unprepared.</span></span></b><span
  style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin;mso-bidi-font-weight:bold\'><span
  style=3D\'mso-spacerun:yes\'>&nbsp; </span></span><b style=3D\'mso-bidi-font=
-weight:
  normal\'><span style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font=
:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin\'><o:p></o:p></span></b></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin;mso-bidi-font-weight:bold\'><o:p>&=
nbsp;</o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin\'>Firms can&#8217;t bury their hea=
ds
  in the sand over auto enrolment; yet more half (52%) who haven&#8217;t ye=
t found
  a pension provider don&#8217;t think there will be any issue finding one.=
 <o:p></o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin\'><o:p>&nbsp;</o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin\'>That may be easier said than don=
e,
  as n</span>ot all employees are an attractive prospect to pension provide=
rs -
  especially those on lower salaries - and providers may not be willing to
  accept all employers and all employees on equal terms. Less than one in t=
en
  (9%) SMEs appreciate this reality and worry that providers might \'cherry
  pick\' business.<span style=3D\'mso-ascii-font-family:Calibri;mso-ascii-the=
me-font:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'><o:p></o:p>=
</span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:8.0pt;mso-bidi-font-size:11.0pt\'><o:p>&nbsp;</o:p></sp=
an></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'>The complexity of=
 the
  subject compounds the issue; a third (34%) of small firms <span class=3DG=
ramE>don\'t</span>
  understand how auto enrolment contributions are calculated. </p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:8.0pt;mso-bidi-font-size:11.0pt\'><o:p>&nbsp;</o:p></sp=
an></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'>Some companies -
  however - have at least given a bit of thought as to how they will go abo=
ut
  finding a provider, with a quarter (26%) intending to seek help from their
  accountant; one in six (16%) saying the will rely on their existing scheme
  provider while one in eight (12%) will search the market and do the resea=
rch
  themselves.</p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><o:p>&nbsp;</o:p>=
</p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'>SMALL BUSINESSES IN THE UK <o:p></o:p></b></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'>SMEs are the back=
bone
  of our economy, employing millions, generating a combined turnover of
  &pound;1.6 trillion and accounting for 60% of private sector employment.<=
/p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:8.0pt;mso-bidi-font-size:11.0pt\'><o:p>&nbsp;</o:p></sp=
an></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'mso-bidi-font-weight:bold\'>Around 10 million people are eligible=
 for
  auto enrolment and 5 million have already been enrolled. Currently, just =
9%
  of savers are opting out, which highlights the fact that if it&#8217;s ma=
de
  simple and easy for people to save then, by and large, people will do so.=
 <o:p></o:p></span></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:8.0pt;mso-bidi-font-size:11.0pt\'><o:p>&nbsp;</o:p></sp=
an></p>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;tex=
t-align:
  justify;line-height:normal;mso-element:frame;mso-element-frame-hspace:9.0=
pt;
  mso-element-wrap:around;mso-element-anchor-vertical:page;mso-element-anch=
or-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'>We\'re joined in the studio by Charles <span class=3DSpellE>Counse=
ll</span>
  - Executive Director at The Pensions Regulator and Morten Nilsson, NOW:
  Pensions&#8217; CEO. Together they will be discussing the implications of=
 the
  new rules, the pitfalls and hurdles that companies will face and what this
  legislation means for employees. </b><b style=3D\'mso-bidi-font-weight:nor=
mal\'><span
  style=3D\'mso-ascii-font-family:Calibri;mso-ascii-theme-font:minor-latin;
  mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;mso-bidi-f=
ont-family:
  Calibri;mso-bidi-theme-font:minor-latin\'><o:p></o:p></span></b></p>
  </td>
 </tr>
 <tr style=3D\'mso-yfti-irow:4;height:109.75pt\'>
  <td width=3D213 valign=3Dtop style=3D\'width:160.05pt;border-top:none;bord=
er-left:
  solid windowtext 6.0pt;border-bottom:solid windowtext 3.0pt;border-right:
  solid windowtext 3.0pt;mso-border-top-alt:solid windowtext 3.0pt;padding:
  0cm 5.4pt 0cm 5.4pt;height:109.75pt\'>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;lin=
e-height:
  normal;mso-element:frame;mso-element-frame-hspace:9.0pt;mso-element-wrap:
  around;mso-element-anchor-vertical:page;mso-element-anchor-horizontal:mar=
gin;
  mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-bidi-fon=
t-weight:
  normal\'><span style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
><o:p>&nbsp;</o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:12.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>TO
  BOOK AN INTERVIEW OR REQUEST FURTHER INFORMATION<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:10.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
><o:p>&nbsp;</o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><b style=3D\'mso-b=
idi-font-weight:
  normal\'><span style=3D\'font-size:16.0pt;mso-ascii-font-family:Calibri;
  mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;mso-hansi-=
theme-font:
  minor-latin;mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'=
>020
  7458 4500<o:p></o:p></span></b></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><a
  href=3D"mailto:INTERVIEW@ON-BROADCAST.COM"><b style=3D\'mso-bidi-font-weig=
ht:normal\'><span
  style=3D\'font-size:10.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-f=
ont:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>INTERVIEW@O=
N-BROADCAST.COM</span></b></a><b
  style=3D\'mso-bidi-font-weight:normal\'><span style=3D\'font-size:9.0pt;mso-=
ascii-font-family:
  Calibri;mso-ascii-theme-font:minor-latin;mso-hansi-font-family:Calibri;
  mso-hansi-theme-font:minor-latin;mso-bidi-font-family:Calibri;mso-bidi-th=
eme-font:
  minor-latin\'><o:p></o:p></span></b></p>
  </td>
 </tr>
 <tr style=3D\'mso-yfti-irow:5;mso-yfti-lastrow:yes;height:20.15pt\'>
  <td width=3D747 colspan=3D2 valign=3Dtop style=3D\'width:559.95pt;border:s=
olid windowtext 6.0pt;
  border-top:none;mso-border-top-alt:solid windowtext 3.0pt;padding:0cm 5.4=
pt 0cm 5.4pt;
  height:20.15pt\'>
  <p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;lin=
e-height:
  normal;tab-stops:center 274.55pt right 549.15pt;mso-element:frame;mso-ele=
ment-frame-hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:8.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-fo=
nt:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'><span
  style=3D\'mso-tab-count:1\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp=
;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&n=
bsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp=
;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>If
  you wish to discuss the type of content you are being offered, please con=
tact
  a member of the team on 020 7458 4500<o:p></o:p></span></p>
  <p class=3DMsoNormal align=3Dcenter style=3D\'margin-bottom:0cm;margin-bot=
tom:.0001pt;
  text-align:center;line-height:normal;mso-element:frame;mso-element-frame-=
hspace:
  9.0pt;mso-element-wrap:around;mso-element-anchor-vertical:page;mso-elemen=
t-anchor-horizontal:
  margin;mso-element-top:18.05pt;mso-height-rule:exactly\'><span
  style=3D\'font-size:8.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-fo=
nt:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>ON-Broadcas=
t</span><span
  style=3D\'font-size:9.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-fo=
nt:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin;color:black\'=
>: </span><span
  style=3D\'font-size:8.0pt;mso-ascii-font-family:Calibri;mso-ascii-theme-fo=
nt:
  minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-lati=
n;
  mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'>5th Floor, =
41
  &#8211; 42 Berners Street, London, W1T 3NB<o:p></o:p></span></p>
  </td>
 </tr>
</table>

<p class=3DMsoNormal><span style=3D\'mso-ascii-font-family:Calibri;mso-ascii=
-theme-font:
minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;
mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'><o:p>&nbsp;</=
o:p></span></p>

<p class=3DMsoNormal><span style=3D\'mso-ascii-font-family:Calibri;mso-ascii=
-theme-font:
minor-latin;mso-hansi-font-family:Calibri;mso-hansi-theme-font:minor-latin;
mso-bidi-font-family:Calibri;mso-bidi-theme-font:minor-latin\'><o:p>&nbsp;</=
o:p></span></p>

</div>

</body>

</html>

------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue_files/item0001.xml
Content-Transfer-Encoding: quoted-printable
Content-Type: text/xml

<b:Sources SelectedStyle=3D"\APA.XSL" StyleName=3D"APA Fifth Edition" xmlns=
:b=3D"http://schemas.openxmlformats.org/officeDocument/2006/bibliography" x=
mlns=3D"http://schemas.openxmlformats.org/officeDocument/2006/bibliography"=
></b:Sources>

------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue_files/props002.xml
Content-Transfer-Encoding: quoted-printable
Content-Type: text/xml

<?xml version=3D"1.0" encoding=3D"UTF-8" standalone=3D"no"?>
<ds:datastoreItem ds:itemID=3D"{7FEFD034-3C5A-4A84-B815-7BAB8C9FF697}" xmln=
s:ds=3D"http://schemas.openxmlformats.org/officeDocument/2006/customXml"><d=
s:schemaRefs><ds:schemaRef ds:uri=3D"http://schemas.openxmlformats.org/offi=
ceDocument/2006/bibliography"/></ds:schemaRefs></ds:datastoreItem>
------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue_files/themedata.thmx
Content-Transfer-Encoding: base64
Content-Type: application/vnd.ms-officetheme

UEsDBBQABgAIAAAAIQDp3g+//wAAABwCAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbKyRy07DMBBF
90j8g+UtSpyyQAgl6YLHjseifMDImSQWydiyp1X790zSVEKoIBZsLNkz954743K9Hwe1w5icp0qv
8kIrJOsbR12l3zdP2a1WiYEaGDxhpQ+Y9Lq+vCg3h4BJiZpSpXvmcGdMsj2OkHIfkKTS+jgCyzV2
JoD9gA7NdVHcGOuJkTjjyUPX5QO2sB1YPe7l+Zgk4pC0uj82TqxKQwiDs8CS1Oyo+UbJFkIuyrkn
9S6kK4mhzVnCVPkZsOheZTXRNajeIPILjBLDsAyJX89nIBkt5r87nons29ZZbLzdjrKOfDZezE7B
/xRg9T/oE9PMf1t/AgAA//8DAFBLAwQUAAYACAAAACEApdan58AAAAA2AQAACwAAAF9yZWxzLy5y
ZWxzhI/PasMwDIfvhb2D0X1R0sMYJXYvpZBDL6N9AOEof2giG9sb69tPxwYKuwiEpO/3qT3+rov5
4ZTnIBaaqgbD4kM/y2jhdj2/f4LJhaSnJQhbeHCGo3vbtV+8UNGjPM0xG6VItjCVEg+I2U+8Uq5C
ZNHJENJKRds0YiR/p5FxX9cfmJ4Z4DZM0/UWUtc3YK6PqMn/s8MwzJ5PwX+vLOVFBG43lExp5GKh
qC/jU72QqGWq1B7Qtbj51v0BAAD//wMAUEsDBBQABgAIAAAAIQBreZYWgwAAAIoAAAAcAAAAdGhl
bWUvdGhlbWUvdGhlbWVNYW5hZ2VyLnhtbAzMTQrDIBBA4X2hd5DZN2O7KEVissuuu/YAQ5waQceg
0p/b1+XjgzfO3xTVm0sNWSycBw2KZc0uiLfwfCynG6jaSBzFLGzhxxXm6XgYybSNE99JyHNRfSPV
kIWttd0g1rUr1SHvLN1euSRqPYtHV+jT9yniResrJgoCOP0BAAD//wMAUEsDBBQABgAIAAAAIQCW
ta3ilgYAAFAbAAAWAAAAdGhlbWUvdGhlbWUvdGhlbWUxLnhtbOxZT2/bNhS/D9h3IHRvYyd2Ggd1
itixmy1NG8Ruhx5piZbYUKJA0kl9G9rjgAHDumGHFdhth2FbgRbYpfs02TpsHdCvsEdSksVYXpI2
2IqtPiQS+eP7/x4fqavX7scMHRIhKU/aXv1yzUMk8XlAk7Dt3R72L615SCqcBJjxhLS9KZHetY33
37uK11VEYoJgfSLXcduLlErXl5akD8NYXuYpSWBuzEWMFbyKcCkQ+AjoxmxpuVZbXYoxTTyU4BjI
3hqPqU/QUJP0NnLiPQaviZJ6wGdioEkTZ4XBBgd1jZBT2WUCHWLW9oBPwI+G5L7yEMNSwUTbq5mf
t7RxdQmvZ4uYWrC2tK5vftm6bEFwsGx4inBUMK33G60rWwV9A2BqHtfr9bq9ekHPALDvg6ZWljLN
Rn+t3slplkD2cZ52t9asNVx8if7KnMytTqfTbGWyWKIGZB8bc/i12mpjc9nBG5DFN+fwjc5mt7vq
4A3I4lfn8P0rrdWGizegiNHkYA6tHdrvZ9QLyJiz7Ur4GsDXahl8hoJoKKJLsxjzRC2KtRjf46IP
AA1kWNEEqWlKxtiHKO7ieCQo1gzwOsGlGTvky7khzQtJX9BUtb0PUwwZMaP36vn3r54/RccPnh0/
+On44cPjBz9aQs6qbZyE5VUvv/3sz8cfoz+efvPy0RfVeFnG//rDJ7/8/Hk1ENJnJs6LL5/89uzJ
i68+/f27RxXwTYFHZfiQxkSim+QI7fMYFDNWcSUnI3G+FcMI0/KKzSSUOMGaSwX9nooc9M0pZpl3
HDk6xLXgHQHlowp4fXLPEXgQiYmiFZx3otgB7nLOOlxUWmFH8yqZeThJwmrmYlLG7WN8WMW7ixPH
v71JCnUzD0tH8W5EHDH3GE4UDklCFNJz/ICQCu3uUurYdZf6gks+VuguRR1MK00ypCMnmmaLtmkM
fplW6Qz+dmyzewd1OKvSeoscukjICswqhB8S5pjxOp4oHFeRHOKYlQ1+A6uoSsjBVPhlXE8q8HRI
GEe9gEhZteaWAH1LTt/BULEq3b7LprGLFIoeVNG8gTkvI7f4QTfCcVqFHdAkKmM/kAcQohjtcVUF
3+Vuhuh38ANOFrr7DiWOu0+vBrdp6Ig0CxA9MxHal1CqnQoc0+TvyjGjUI9tDFxcOYYC+OLrxxWR
9bYW4k3Yk6oyYftE+V2EO1l0u1wE9O2vuVt4kuwRCPP5jeddyX1Xcr3/fMldlM9nLbSz2gplV/cN
tik2LXK8sEMeU8YGasrIDWmaZAn7RNCHQb3OnA5JcWJKI3jM6rqDCwU2a5Dg6iOqokGEU2iw654m
EsqMdChRyiUc7MxwJW2NhyZd2WNhUx8YbD2QWO3ywA6v6OH8XFCQMbtNaA6fOaMVTeCszFauZERB
7ddhVtdCnZlb3YhmSp3DrVAZfDivGgwW1oQGBEHbAlZehfO5Zg0HE8xIoO1u997cLcYLF+kiGeGA
ZD7Ses/7qG6clMeKuQmA2KnwkT7knWK1EreWJvsG3M7ipDK7xgJ2uffexEt5BM+8pPP2RDqypJyc
LEFHba/VXG56yMdp2xvDmRYe4xS8LnXPh1kIF0O+EjbsT01mk+Uzb7ZyxdwkqMM1hbX7nMJOHUiF
VFtYRjY0zFQWAizRnKz8y00w60UpYCP9NaRYWYNg+NekADu6riXjMfFV2dmlEW07+5qVUj5RRAyi
4AiN2ETsY3C/DlXQJ6ASriZMRdAvcI+mrW2m3OKcJV359srg7DhmaYSzcqtTNM9kCzd5XMhg3kri
gW6Vshvlzq+KSfkLUqUcxv8zVfR+AjcFK4H2gA/XuAIjna9tjwsVcahCaUT9voDGwdQOiBa4i4Vp
CCq4TDb/BTnU/23OWRomreHAp/ZpiASF/UhFgpA9KEsm+k4hVs/2LkuSZYRMRJXElakVe0QOCRvq
Griq93YPRRDqpppkZcDgTsaf+55l0CjUTU4535waUuy9Ngf+6c7HJjMo5dZh09Dk9i9ErNhV7Xqz
PN97y4roiVmb1cizApiVtoJWlvavKcI5t1pbseY0Xm7mwoEX5zWGwaIhSuG+B+k/sP9R4TP7ZUJv
qEO+D7UVwYcGTQzCBqL6km08kC6QdnAEjZMdtMGkSVnTZq2Ttlq+WV9wp1vwPWFsLdlZ/H1OYxfN
mcvOycWLNHZmYcfWdmyhqcGzJ1MUhsb5QcY4xnzSKn914qN74OgtuN+fMCVNMME3JYGh9RyYPIDk
txzN0o2/AAAA//8DAFBLAwQUAAYACAAAACEADdGQn7YAAAAbAQAAJwAAAHRoZW1lL3RoZW1lL19y
ZWxzL3RoZW1lTWFuYWdlci54bWwucmVsc4SPTQrCMBSE94J3CG9v07oQkSbdiNCt1AOE5DUNNj8k
UeztDa4sCC6HYb6ZabuXnckTYzLeMWiqGgg66ZVxmsFtuOyOQFIWTonZO2SwYIKObzftFWeRSyhN
JiRSKC4xmHIOJ0qTnNCKVPmArjijj1bkIqOmQci70Ej3dX2g8ZsBfMUkvWIQe9UAGZZQmv+z/Tga
iWcvHxZd/lFBc9mFBSiixszgI5uqTATKW7q6xN8AAAD//wMAUEsBAi0AFAAGAAgAAAAhAOneD7//
AAAAHAIAABMAAAAAAAAAAAAAAAAAAAAAAFtDb250ZW50X1R5cGVzXS54bWxQSwECLQAUAAYACAAA
ACEApdan58AAAAA2AQAACwAAAAAAAAAAAAAAAAAwAQAAX3JlbHMvLnJlbHNQSwECLQAUAAYACAAA
ACEAa3mWFoMAAACKAAAAHAAAAAAAAAAAAAAAAAAZAgAAdGhlbWUvdGhlbWUvdGhlbWVNYW5hZ2Vy
LnhtbFBLAQItABQABgAIAAAAIQCWta3ilgYAAFAbAAAWAAAAAAAAAAAAAAAAANYCAAB0aGVtZS90
aGVtZS90aGVtZTEueG1sUEsBAi0AFAAGAAgAAAAhAA3RkJ+2AAAAGwEAACcAAAAAAAAAAAAAAAAA
oAkAAHRoZW1lL3RoZW1lL19yZWxzL3RoZW1lTWFuYWdlci54bWwucmVsc1BLBQYAAAAABQAFAF0B
AACbCgAAAAA=

------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue_files/colorschememapping.xml
Content-Transfer-Encoding: quoted-printable
Content-Type: text/xml

<?xml version=3D"1.0" encoding=3D"UTF-8" standalone=3D"yes"?>
<a:clrMap xmlns:a=3D"http://schemas.openxmlformats.org/drawingml/2006/main"=
 bg1=3D"lt1" tx1=3D"dk1" bg2=3D"lt2" tx2=3D"dk2" accent1=3D"accent1" accent=
2=3D"accent2" accent3=3D"accent3" accent4=3D"accent4" accent5=3D"accent5" a=
ccent6=3D"accent6" hlink=3D"hlink" folHlink=3D"folHlink"/>
------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue_files/header.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset="us-ascii"

<html xmlns:v=3D"urn:schemas-microsoft-com:vml"
xmlns:o=3D"urn:schemas-microsoft-com:office:office"
xmlns:w=3D"urn:schemas-microsoft-com:office:word"
xmlns:m=3D"http://schemas.microsoft.com/office/2004/12/omml"
xmlns=3D"http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=3DContent-Type content=3D"text/html; charset=3Dus-ascii">
<meta name=3DProgId content=3DWord.Document>
<meta name=3DGenerator content=3D"Microsoft Word 14">
<meta name=3DOriginator content=3D"Microsoft Word 14">
<link id=3DMain-File rel=3DMain-File href=3D"../skycue.htm">
<![if IE]>
<base href=3D"file:///C:\2CEE1B05\skycue_files\header.htm"
id=3D"webarch_temp_base_tag">
<![endif]>
</head>

<body lang=3DEN-GB link=3Dblue vlink=3Dpurple>

<div style=3D\'mso-element:footnote-separator\' id=3Dfs>

<p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;line-=
height:
normal\'><span style=3D\'mso-special-character:footnote-separator\'><![if !sup=
portFootnotes]>

<hr align=3Dleft size=3D1 width=3D"33%">

<![endif]></span></p>

</div>

<div style=3D\'mso-element:footnote-continuation-separator\' id=3Dfcs>

<p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;line-=
height:
normal\'><span style=3D\'mso-special-character:footnote-continuation-separato=
r\'><![if !supportFootnotes]>

<hr align=3Dleft size=3D1>

<![endif]></span></p>

</div>

<div style=3D\'mso-element:endnote-separator\' id=3Des>

<p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;line-=
height:
normal\'><span style=3D\'mso-special-character:footnote-separator\'><![if !sup=
portFootnotes]>

<hr align=3Dleft size=3D1 width=3D"33%">

<![endif]></span></p>

</div>

<div style=3D\'mso-element:endnote-continuation-separator\' id=3Decs>

<p class=3DMsoNormal style=3D\'margin-bottom:0cm;margin-bottom:.0001pt;line-=
height:
normal\'><span style=3D\'mso-special-character:footnote-continuation-separato=
r\'><![if !supportFootnotes]>

<hr align=3Dleft size=3D1>

<![endif]></span></p>

</div>

</body>

</html>

------=_NextPart_01D1A5F3.8D83DC00
Content-Location: file:///C:/2CEE1B05/skycue_files/filelist.xml
Content-Transfer-Encoding: quoted-printable
Content-Type: text/xml; charset="utf-8"

<xml xmlns:o=3D"urn:schemas-microsoft-com:office:office">
 <o:MainFile HRef=3D"../skycue.htm"/>
 <o:File HRef=3D"item0001.xml"/>
 <o:File HRef=3D"props002.xml"/>
 <o:File HRef=3D"themedata.thmx"/>
 <o:File HRef=3D"colorschememapping.xml"/>
 <o:File HRef=3D"header.htm"/>
 <o:File HRef=3D"filelist.xml"/>
</xml>
------=_NextPart_01D1A5F3.8D83DC00--
';
}

?>
