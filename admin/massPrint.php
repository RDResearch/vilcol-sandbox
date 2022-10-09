<?php

?>
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

    <script src='https://code.jquery.com/jquery-3.6.1.min.js' integrity='sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=' crossorigin='anonymous'></script>
    <div id="banner" style="border: #a61d3b 2px solid;">
        <table width="1245px" height="100px" cellspacing="0" cellpadding="0" border="0">
            <tbody><tr>
                <td style="text-align:left; vertical-align:bottom;">
                    <table height="70px">
                        <tbody><tr>
                            <td style="vertical-align: top;"> <span style="padding:2px; font-family: Arial,Helvetica,sans-serif;
font-weight: bold;
font-size: 24px;
color: #a61d3b;">Vilcol MASS PRINTING</span>
                                <p>Below are the downloads links for mass printing</p>
                            </td>
                        </tr>
                        </tbody></table>
                </td><td style="width:175px; text-align:right; vertical-align:middle;">
                    <img style="text-align:right; vertical-align:bottom;" alt="Vilcol Logo" src="images/vilcol_logo.jpg" width="335" height="88">
                </td>
                <td width="20"></td>
            </tr>
            </tbody></table>
    </div>
<?php


include_once("settings.php");
include_once("library.php");
global $navi_1_home;
global $USER; # set by admin_verify()
global $unix_path;
global $admin_domain;
$mass_print_path = "/massprint";

log_open("vilcol.log");

sql_connect();

if ((isset($_POST['admin_username'])) && (isset($_POST['admin_password']))) {
    $logged_in = true;
    admin_login();
} else
    $logged_in = false;

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED']) {
    # If the user has just logged in, rather than clicking on the "Home" tab,
    # and we have a record of the last screen that they were on,
    # then go to that screen now.
    # Ideally this would only happen if they had been logged out through inactivity.
    if ($logged_in && $USER['U_LAST_SCREEN']) {
        # This no longer works now that we are under https://www.vilcoldb.com because there is no longer a $_SERVER['HTTP_REFERER'],
        # in fact there is nothing in $_SERVER to say that we came from login.php. --27/09/16.
        #header("Location: $admin_url/{$USER['U_LAST_SCREEN']}");
        #return;
    }

    $navi_1_home = true; # settings.php; used by navi_1_heading()
    $onload = "onload=\"set_scroll();\"";
} else
    print "<p>" . server_php_self() . ": login is not enabled</p>";

sql_disconnect();
log_close();

function screen_content()
{
    $post2 = array();
    foreach ($_POST as $key => $val) {
        if (($key == 'admin_password') || ($key == 'app_pw'))
            $val = '******';
        $post2[xprint($key, false, 1)] = xprint($val, false, 1);
    }
    dprint("POST = " . print_r($post2, 1));

    if (!user_debug()) {

        return;
    }

}

function screen_content_2(){
    return;
}

function scan_mass_prints()
{
    global $mass_print_path;
    global $unix_path;
    $dir = $unix_path.$mass_print_path;

    $files = scandir($dir);

    // remove . and .. from the array
    try {

        array_shift($files);
        array_shift($files);
    }
    catch (Exception $e){
    }

    return $files;
}

$files = scan_mass_prints();

?>
<div>
    <a href="<?php $_SERVER['PHP_SELF']; ?>">Refresh list</a>

    <?php
    if(isset($files)){
        foreach($files as $file){

            $split_file_name = preg_split("/[-]/", $file);
            $name_array = [3];

            $name_array[0] = $split_file_name[1]; // year
            $name_array[1] = $split_file_name[2]; // month
            $name_array[2] = preg_replace("/BST[0-9]+/",'',$split_file_name[3]); // day
            $file_date = implode("-", $name_array);

            // now check if that matches todays date
            $date = date('y-m-d');
            if (strpos($file_date, $date) !== false){
                ?>
                <div>
                    <a target="_blank" href="<?php echo("/admin".$mass_print_path."/".$file); ?>"><?php echo($file); ?></a>
                </div>
                <?php
            }
            ?>

            <?php
        }
    }
    ?>
</div>
