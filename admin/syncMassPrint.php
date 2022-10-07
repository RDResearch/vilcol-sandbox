<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
include_once("lib_mail.php");

include_once(__DIR__.'/vendor/autoload.php');

use iio\libmergepdf\Merger;
use iio\libmergepdf\Pages;
use Spatie\Async\Pool;

global $denial_message;
global $navi_1_jobs;
global $role_agt;
global $time_tests; # settings.php
global $USER; # set by admin_verify()
global $csv_dir;
global $job_id; # set here, used by add_collect_letter()
global $tunnel_ftp_ip;

$subdir = 'search';

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

$jobs = $_POST['data'];



$letter_id_list = implode(',', $jobs);

log_write("Letter id list: ",$letter_id_list);

dprint("mass_print_letters($letter_id_list)");

# --- Delete and Recreate
# PDF Recreate 28/09/17:
# From 28/09/17, we need to delete and recreate all the PDFs. Do this in the same way that auto_letter.php does it using add_collect_letter().
$sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JOB_ID, L.LETTER_TYPE_ID
            FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
            WHERE L.JOB_LETTER_ID IN ($letter_id_list)
            ";
sql_execute($sql);
$letters = array();
while (($newArray = sql_fetch_assoc()) != false)
{
    $dt0 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $newArray['JL_APPROVED_DT']))));
    $newArray['FILENAME'] = "$csv_dir/v{$newArray['J_VILNO']}/" .
        "letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$dt0}.pdf";
    $letters[] = $newArray;
}
$letter_count = count($letters);
for ($lix = 0; $lix < $letter_count; $lix++)
{
    $job_letter_id = $letters[$lix]['JOB_LETTER_ID']; # used by add_collect_letter()
    $job_id = $letters[$lix]['JOB_ID']; # used by add_collect_letter()
    $letter_type_id = $letters[$lix]['LETTER_TYPE_ID']; # used by add_collect_letter()

    # Delete existing PDF
    if (file_exists($letters[$lix]['FILENAME']))
    {
        #dprint("Deleting \"{$letters[$lix]['FILENAME']}\"...");#
        unlink($letters[$lix]['FILENAME']);
    }

    # Recreate PDF
    add_collect_letter($letter_type_id, $job_letter_id); # also uses global $job_id

} # for ($lix)
# --- Delete and Recreate


# Now start again

$sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JL_CREATED_DT
        FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
        WHERE L.JOB_LETTER_ID IN ($letter_id_list)
        ";
sql_execute($sql);
$letters = array();
$pdfs = array();
while (($newArray = sql_fetch_assoc()) != false)
{
    $dt1 = ($newArray['JL_CREATED_DT'] ? $newArray['JL_CREATED_DT'] : $newArray['JL_APPROVED_DT']);
    $dt2 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $dt1))));
    $newArray['FILENAME'] = "$csv_dir/v{$newArray['J_VILNO']}/" .
        "letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$dt2}.pdf";
    $letters[] = $newArray;
    $pdfs[] = $newArray['FILENAME'];
}

//FIXME - MERGE HERE

log_open("vilcol.log");
log_write("Begin merge");

error_log("Starting merge");

$date = new DateTime();
$current_time = $date->format('Y-m-dTH-i-s');

$file_name = "massprint/massPrint-".(string)$current_time;

// asynchronus merging
$pool = Pool::create();

$count = 0;
foreach (array_chunk($pdfs, 100) as $key=>$pdf_chunk){
    log_write("Merging ".$count);
    $pool->add(function () use ($pdf_chunk, $key, $file_name, $count){
        $merger = new Merger;

        foreach($pdf_chunk as $pdf){
            $merger->addFile($pdf);
        }

        $createdPdf = $merger->merge();

        // append count amount
        $file_name .= "-".$count."-".($count+100).".pdf";

        $myfile = fopen($file_name, "wb");
        $txt = $createdPdf;
        fwrite($myfile, $txt);
        fclose($myfile);

    })->then(function($output){
        echo ('Merge completed');
    });
    $count = $count + 100;
}

//	$pool->wait();

log_write('Created merges');

print("
<span>Mass printer - merged $count letters into 1 PDF</span>
<form target=\"_blank\" action=\"https://vilcoldbl.com/admin/$file_name\">
    <input type=\"submit\" value=\"Click to open\" />
</form>
");

sql_disconnect();