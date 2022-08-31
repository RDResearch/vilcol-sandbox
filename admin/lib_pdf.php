<?php

#require_once("dompdf_060/dompdf/dompdf_config.inc.php");
require_once("dompdf2/autoload.inc.php");
echo ("required dompdf2");
use Dompdf\Dompdf;
$dompdf = new DOMPDF();
echo ("DOMPDF created");

function pdf_create($pdf_dir, $pdf_filename, $html_body)
{
	global $colpdf_colour; # settings.php
	#global $email_collections; # settings.php
	global $email_service; # settings.php
	global $letter_system; # settings.php
	global $vat_number; # settings.php
	global $admin_url; # settings.php

	$debug = false;#
	

	
	if ($debug)
		log_write("pdf_create(\"$pdf_dir\", \"$pdf_filename\", HTML): letter_system=\"$letter_system\"");
	
	$scaling = '';
	if ($letter_system == 'c')
	{
		#$phone = '020 8339 6363';
		#$fax = '020 8399 2959';
		#$email = $email_collections;
		#$firstline = "Vilcollections";
		#$hd_image = "images/coll_hdr.jpg"; # from coll_hdr_1.jpg: 742 x 131 pixels
		#$hd_h = 131; # height of header image in pixels
		#$hd_m = 20; # margin above and below header in pixels
		#$hd_image = "images/coll_hdr.jpg"; # from coll_hdr_2.jpg: 742 x 111 pixels
		#$hd_h = 111; # height of header image in pixels
		#$hd_m = 20; # margin above and below header in pixels
		#$hd_image = "images/coll_hdr.jpg"; # from coll_hdr_3.jpg: 737 x 122 pixels
		$hd_image = $admin_url . "/images/coll_hdr_hires.jpg"; # 1739 x 250 (but resize to 737 x 106) and 600dpi and 104KB
		$hd_w = 737;
		$hd_h = 122; # height of header image in pixels
		$scaling = "width=\"{$hd_w}\" height=\"{$hd_h}\"";
		$hd_m = 20; # margin above and below header in pixels
		$hd_align = "left";
	}
	else
	{
		$phone = '020 8390 9988';
		$fax = '020 8390 9902';
		$email = $email_service;
		$firstline = "Vilcol<br>Vilcol House";
		#$hd_image = $admin_url . "/images/vilcol_logo_2.jpg"; # 221 x 142 pixels
		$hd_image = "https://images.unsplash.com/photo-1493612276216-ee3925520721?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=764&q=80";
		#$hd_h = 142; # height of header image in pixels
		#$hd_h = 110; # height of header image in pixels
		$hd_w = 0;
		$hd_h = 142; # Feedback #223; height of header image in pixels
		$hd_m = 20; # margin above and below header in pixels
		$hd_align = "right";
	}
	
	$hd_hm = $hd_h + $hd_m; # image plus one margin
	$hd_hmm = $hd_h + (2 * $hd_m); # image plus two margins

	$ft_image = ''; #"images/f.jpg"; # if required
	$ft_h = 100; # height of footer image in pixels
	$ft_h2 = $ft_h + 30; # not sure why

	$header = "
	<div id=\"header\" style=\"text-align:{$hd_align}; " . ($debug ? "border:solid 1px blue;" : '') . "\">
		" . ($hd_image ? "<img src=\"$hd_image\" $scaling>" : '<span>&nbsp;</span>') . "
	</div>
	";

	if ($letter_system == 'c')
	{
		$footer = "
		<div id=\"footer\" style=\"text-align:center; font-size:13px; " . ($debug ? "border:solid 1px green;" : '') . "\">
			" . ($ft_image ? "<img src=\"$ft_image\">" : '') . "
			<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><!---->
			<tr>
				<td width=\"50%\">
					&nbsp;
				</td>
				<td align=\"right\" style=\"color:$colpdf_colour\">
					<div style=\"padding:0; text-align:left;\">
						Vilcollections is a trading style of Village Investigations Limited<br>
						Registered in England Number 2267884<br>
						Authorised and regulated by the Financial Conduct Authority
					</div>
				</td>
			</tr>
			</table>
		</div>
		";
	}
	else
	{
		$footer = "
		<div id=\"footer\" style=\"text-align:center; font-size:13px; " . ($debug ? "border:solid 1px green;" : '') . "\">
			" . ($ft_image ? "<img src=\"$ft_image\">" : '') . "
			<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><!---->
			<tr>
				<td width=\"20\">" .
					"&nbsp;" .
				"</td>
				<td width=\"80\" valign=\"top\">" .
					"$firstline<br>97 Ewell Road<br>Surbiton<br>Surrey KT6 6AH" .
				"</td>
				<td width=\"10\">" .
					"&nbsp;" .
				"</td>
				<td width=\"10\" style=\"background-color:black; color:white; padding-left:2px; border-top:solid 1px black;\" valign=\"top\">" .
					"<b>t<br>f<br>e<br>w</b>" .
				"</td>
				<td width=\"5\" style=\"border-top:solid 1px black;\">" .
					"&nbsp;" .
				"</td>
				<td valign=\"top\" style=\"border-top:solid 1px black;\">" .
					"<b>$phone<br>$fax<br>$email<br>www.vilcol.com</b>" .
				"</td>
				<td width=\"10\" style=\"border-top:solid 1px black;\">" .
					"&nbsp;" .
				"</td>
				<td rowspan=\"5\" align=\"right\" valign=\"top\" style=\"border-top:solid 1px black;\">" .
					"<div style=\"padding:0; font-size:10px; text-align:center;\">" .
						"Village Investigations Limited<br>" .
						"trading as Vilcol<br>" .
						"Company Registration No. 2267884<br>" .
						"VAT Registration No. $vat_number<br>" .
						"Authorised and regulated by the<br>" .
						"Financial Conduct Authority<br>" .
						"Data Protection Act Registration No. Z5416" .
					"</div>" .
				"</td>
			</tr>
			</table>
		</div>
		";
	}
	
	$page_style = "margin-top: {$hd_hmm}px; margin-bottom: {$ft_h2}px;";
	$hf_positions = "
		#header { position: fixed; left: 0px; top: -{$hd_hm}px; right: 0px; height: {$hd_hmm}px; }
		#footer { position: fixed; left: 0px; bottom: -{$ft_h2}px; right: 0px; height: {$ft_h}px; }
		";

	$html_pdf = "
	<!DOCTYPE html>
	<html>
	<head>
		<link href=\"./pdf.css\" rel=\"stylesheet\" type=\"text/css\">
		<style>
			@page { $page_style }
			$hf_positions
		</style>
	</head>
	<body>
		$header
		$footer
		<div id=\"content\">
			$html_body
		</div>
	</body>
	</html>
	";
	#log_write($html_body);#
	#log_write($html_pdf);#
	
//	$html_filename = $pdf_filename;
//	if (strtolower(substr($html_filename,-4)) == ".pdf")
//	{
//		$html_filename = substr($html_filename, 0, strlen($html_filename)-4) . "_full.html";
//		#dprint("HTML/1 \"$html_filename\"");#
//	}
//	else
//	{
//		$html_filename = str_replace(".pdf", "_full.html", strtolower($html_filename));
//		#dprint("HTML/2 \"$html_filename\"");#
//	}
//	$fp = fopen("$pdf_dir/$html_filename", "a"); 
//	if ($fp)
//	{
//		fwrite($fp, $html_pdf);
//		fclose($fp); 
//	}
//	else 
//		return "pdf_create($pdf_dir, $pdf_filename): fopen(\"$pdf_dir/$html_filename\", a) failed... ";


	$html_filename = $pdf_filename;
	if (strtolower(substr($html_filename,-4)) == ".pdf")
	{
		$html_filename = substr($html_filename, 0, strlen($html_filename)-4) . ".html";
		#dprint("HTML/1 \"$html_filename\"");#
	}
	else
	{
		$html_filename = str_replace(".pdf", ".html", strtolower($html_filename));
		#dprint("HTML/2 \"$html_filename\"");#
	}
	$fp = fopen("$pdf_dir/$html_filename", "a");
	if ($fp)
	{
		fwrite($fp, $html_body);
		fclose($fp);
	}
	else
		return "pdf_create($pdf_dir, $pdf_filename): fopen(\"$pdf_dir/$html_filename\", a) failed... ";

	$dompdf = new DOMPDF();
	$options = $dompdf->getOptions();
	$options->set('isRemoteEnabled', true);
	$dompdf->setOptions($options);
	$dompdf->set_paper('A4', 'portrait');
	#$customPaper = array(0, 0, 595, 841); # units are points
	#$customPaper = array(0, 0, 500, 400); # units are points
	#$dompdf->set_paper($customPaper);

	$html_pdf = preg_replace('/>\s+</', "><", $html_pdf);
	$dompdf->load_html($html_pdf);
	$dompdf->render();

	echo(htmlspecialchars($html_pdf));

	$pdfoutput = $dompdf->output();
	$fp = fopen("$pdf_dir/$pdf_filename", "a"); 
	if ($fp)
	{
		fwrite($fp, $pdfoutput);
		fclose($fp); 
	}
	else 
		return "pdf_create($pdf_dir, $pdf_filename): fopen(\"$pdf_dir/$pdf_filename\", a) failed... ";
	
	return '';
	
} # pdf_create()

function pdf_link($doctype, $subdir, $docnum, $relative=false, $html=false)
{
	# $subdir is subdirectory beneath 'csvex' e.g. 'v123456' or 'c1234' (vilno or client code)
	
	global $admin_domain;
	global $csv_dir; # csvex in settings.php
	global $protocol;
	
	$url = '';

	$filename_prefix = '';
	if ($doctype == 'i')
		$filename_prefix = "invoice_{$docnum}_"; # docnum is e.g. Invoice Number (INVOICE.INV_NUM)
	elseif ($doctype == 'jl')
		$filename_prefix = "letter_{$docnum}_"; # docnum is VILNo_Sequence_LetterID (JOB.J_VILNO, JOB.J_SEQUENCE, JOB_LETTER.JOB_LETTER_ID)
	else
		dprint("pdf_link($doctype, $subdir, $docnum): doctype not recognised", true);
		
	if ($filename_prefix)
	{
		$prefix_len = strlen($filename_prefix);
		
		$dirpath = check_dir("{$csv_dir}/{$subdir}");
		$dir = opendir($dirpath);
		if ($dir)
		{
			$filelist = array();
			while (($file = readdir($dir)) != false)
			{
				if (substr($file, 0, $prefix_len) == $filename_prefix)
				{
					$bits = explode('.', $file);
					if (!$html)
					{
						if (strtolower($bits[count($bits)-1]) == 'pdf')
							$filelist[] = $file;
					}
					else
					{
						if (strtolower($bits[count($bits)-1]) == 'html')
							$filelist[] = $file;
					}
				}
			}
			closedir($dir);
			if ($filelist)
			{
				sort($filelist);
				$found = $filelist[count($filelist) - 1];
				if ($relative)
					$url = "";
				else
					$url = "{$protocol}://{$admin_domain}/";
				$url .= "{$dirpath}/{$found}";
			}
		}
		else
			dprint("pdf_link($doctype, $subdir, $docnum): could not open directory \"$dirpath\"", true);
	}
	
	return $url;
	
} # pdf_link()

?>
