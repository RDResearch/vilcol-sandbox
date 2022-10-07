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
    screen_layout();
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

//    // remove . and .. from the array
//    try {
//
//        $files = array_shift($files);
//        $files = array_shift($files);
//    }
//    catch (Exception $e){
//    }

    return $files;
}

$files = scan_mass_prints();

?>
<div>
    <h1>View massprints</h1>
    <p>Here you can download your massprints</p>

    <?php
    if(isset($files)){
        foreach($files as $file){

//            $split_file_name = preg_split("[\s-]+", $file);
//            $name_array = [8];
//            $name_array[0] = $split_file_name[1];
//            $name_array[1] = $split_file_name[2];
//            $name_array[2] = $split_file_name[3];
//            $name_array[3] = $split_file_name[4];
//            $name_array[4] = $split_file_name[5];
//            $name_array[5] = $split_file_name[6];
//            $name_array[6] = $split_file_name[7];
//            $name_array[7] = $split_file_name[8];
//            $file_date = implode("-", $name_array);

            ?>
                <div>
                    <a href="<?php echo($mass_print_path."/".$file); ?>"><?php echo($file); ?></a>
                </div>
            <?php
        }
    }
    ?>
</div>
