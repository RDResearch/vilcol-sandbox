<?php

function init_data()
{
	global $activities_sel;
	global $activities_sel_nm; # ACT_NON_MAN
	global $ADJUSTMENTS;
	global $id_JOB_STATUS_act; # JOB_STATUS_SD.JOB_STATUS_ID for "ACT"
	global $id_JOB_STATUS_rcr; # JOB_STATUS_SD.JOB_STATUS_ID for "RCR"
//	global $id_JOB_TYPE_tc; # JOB_TYPE_SD.JOB_TYPE_ID for "T/C"
//	global $id_JOB_TYPE_tm; # JOB_TYPE_SD.JOB_TYPE_ID for "T/M"
	global $ids_JOB_TYPE_retraces;
	global $JOB_STATUSES;
	global $job_statuses_sel; # for <select> list
	global $JOB_TYPES;
	global $job_types_sel; # for <select> list
	global $PAYMENT_METHODS;
	global $payment_methods_sel; # for <select> list
	global $PAYMENT_ROUTES;
	global $payment_routes_sel; # for <select> list
	global $sqlFalse;
	global $sqlTrue;

	set_time_limit(60); # 1 min rather than 30, to give SQL Server time to open up decryption keys

	$ids_JOB_TYPE_retraces = array();
	$sql = "SELECT JOB_TYPE_ID FROM JOB_TYPE_SD WHERE JT_CODE IN ('RT1','RT2','RT3')";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$ids_JOB_TYPE_retraces[] = $newArray[0];
	#dprint("\$ids_JOB_TYPE_retraces=" . print_r($ids_JOB_TYPE_retraces,1));#

//	$id_JOB_TYPE_tc = 0;
//	$sql = "SELECT JOB_TYPE_ID FROM JOB_TYPE_SD WHERE JT_CODE = 'T/C'";
//	sql_execute($sql);
//	while (($newArray = sql_fetch()) != false)
//		$id_JOB_TYPE_tc = $newArray[0];
//
//	$id_JOB_TYPE_tm = 0;
//	$sql = "SELECT JOB_TYPE_ID FROM JOB_TYPE_SD WHERE JT_CODE = 'T/M'";
//	sql_execute($sql);
//	while (($newArray = sql_fetch()) != false)
//		$id_JOB_TYPE_tm = $newArray[0];

	$id_JOB_STATUS_act = 0;
	$sql = "SELECT JOB_STATUS_ID FROM JOB_STATUS_SD WHERE J_STATUS='ACT'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$id_JOB_STATUS_act = $newArray[0];
	#dprint("\$id_JOB_STATUS_act=$id_JOB_STATUS_act");#

	$id_JOB_STATUS_rcr = 0;
	$sql = "SELECT JOB_STATUS_ID FROM JOB_STATUS_SD WHERE J_STATUS='RCR'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$id_JOB_STATUS_rcr = $newArray[0];
	#dprint("\$id_JOB_STATUS_rcr=$id_JOB_STATUS_rcr");#

	$JOB_TYPES = array();
	$job_types_sel = array();
	$sql = "SELECT JOB_TYPE_ID, JT_CODE, JT_TYPE FROM JOB_TYPE_SD WHERE OBSOLETE=$sqlFalse ORDER BY JOB_TYPE_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$JOB_TYPES[$newArray['JOB_TYPE_ID']] = array('JT_CODE' => $newArray['JT_CODE'], 'JT_TYPE' => $newArray['JT_TYPE']);
		$job_types_sel[$newArray['JOB_TYPE_ID']] = $newArray['JT_TYPE'];
	}

	$JOB_STATUSES = array();
	$job_statuses_sel = array();
	$sql = "SELECT JOB_STATUS_ID, J_STATUS FROM JOB_STATUS_SD ORDER BY JOB_STATUS_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$JOB_STATUSES[$newArray['JOB_STATUS_ID']] = array('J_STATUS' => $newArray['J_STATUS']);
		$job_statuses_sel[$newArray['JOB_STATUS_ID']] = $newArray['J_STATUS'];
	}

	$ADJUSTMENTS = array();
	$sql = "SELECT ADJUSTMENT_ID, ADJUSTMENT FROM ADJUSTMENT_SD WHERE OBSOLETE=$sqlFalse ORDER BY SORT_ORDER, ADJUSTMENT, ADJUSTMENT_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$ADJUSTMENTS[$newArray['ADJUSTMENT_ID']] = $newArray['ADJUSTMENT'];

	$PAYMENT_ROUTES = array();
	$sql = "SELECT PAYMENT_ROUTE_ID, PAYMENT_ROUTE FROM PAYMENT_ROUTE_SD ORDER BY SORT_ORDER, PAYMENT_ROUTE_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$PAYMENT_ROUTES[$newArray['PAYMENT_ROUTE_ID']] = $newArray['PAYMENT_ROUTE'];

	$PAYMENT_METHODS = array();
	$sql = "SELECT PAYMENT_METHOD_ID, PAYMENT_METHOD FROM PAYMENT_METHOD_SD ORDER BY SORT_ORDER, PAYMENT_METHOD_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$PAYMENT_METHODS[$newArray['PAYMENT_METHOD_ID']] = $newArray['PAYMENT_METHOD'];

	$payment_methods_sel = array();
	$sql = "SELECT PAYMENT_METHOD_ID, PAYMENT_METHOD FROM PAYMENT_METHOD_SD WHERE OBSOLETE=$sqlFalse ORDER BY SORT_ORDER, PAYMENT_METHOD, PAYMENT_METHOD_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$payment_methods_sel[$newArray['PAYMENT_METHOD_ID']] = $newArray['PAYMENT_METHOD'];

	$payment_routes_sel = array();
	$sql = "SELECT PAYMENT_ROUTE_ID, PAYMENT_ROUTE FROM PAYMENT_ROUTE_SD WHERE OBSOLETE=$sqlFalse ORDER BY SORT_ORDER, PAYMENT_ROUTE, PAYMENT_ROUTE_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$payment_routes_sel[$newArray['PAYMENT_ROUTE_ID']] = $newArray['PAYMENT_ROUTE'];

	$activities_sel = array();
	$sql = "SELECT ACTIVITY_ID, ACT_TDX FROM ACTIVITY_SD WHERE OBSOLETE=$sqlFalse ORDER BY ACT_TDX, ACTIVITY_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$activities_sel[$newArray['ACTIVITY_ID']] = $newArray['ACT_TDX'];

	$activities_sel_nm = array();
	$sql = "SELECT ACTIVITY_ID, ACT_TDX FROM ACTIVITY_SD WHERE OBSOLETE=$sqlFalse AND ACT_NON_MAN=$sqlTrue ORDER BY ACT_TDX, ACTIVITY_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$activities_sel_nm[$newArray['ACTIVITY_ID']] = $newArray['ACT_TDX'];

} # init_data()

function vlf_convert_raw($vlf)
{
	# $vlf is 6 bytes from the DBF VLF field

	if ($vlf == "      ")
		return 0;

	$d0 = ord(substr($vlf, 0, 1));
	$d1 = ord(substr($vlf, 1, 1));
	$d2 = ord(substr($vlf, 2, 1));
	$d3 = ord(substr($vlf, 3, 1));
	$d4 = ord(substr($vlf, 4, 1));
	$d5 = ord(substr($vlf, 5, 1));

	return vlf_convert_decimal($d0,$d1,$d2,$d3,$d4,$d5);
}

function vlf_convert_dec_list($dec)
{
	# E.g. $dec = "212 132 128 128 151 224" -- MUST have spaces

	$list = explode(' ', trim(str_replace('  ', ' ', $dec)));
	if (count($list) == 6)
		return vlf_convert_decimal($list[0],$list[1],$list[2],$list[3],$list[4],$list[5]);
	return "Not 6 elements (" . count($list) . ")";
}

function vlf_convert_hex_list($hex)
{
	# E.g. $hex = "C9 8E 81 80 BB E3" (with or without spaces)

	$hex = str_replace(' ', '', $hex);
	$d0 = hexdec(substr($hex, 0, 2));
	$d1 = hexdec(substr($hex, 2, 2));
	$d2 = hexdec(substr($hex, 4, 2));
	$d3 = hexdec(substr($hex, 6, 2));
	$d4 = hexdec(substr($hex, 8, 2));
	$d5 = hexdec(substr($hex, 10, 2));

	return vlf_convert_decimal($d0,$d1,$d2,$d3,$d4,$d5);
}

function vlf_convert_decimal($d0,$d1,$d2,$d3,$d4,$d5)
{
	$debug = false;

	if (($d0==20) && ($d1==20) && ($d2==20) && ($d3==20) && ($d4==20) && ($d5==20))
		$offset = 0; # 6 spaces
	else
	{
		$d0 = ($d0 & 127) | ((($d5     ) & 1) << 7);
		$d1 = ($d1 & 127) | ((($d5 >> 1) & 1) << 7);
		$d2 = ($d2 & 127) | ((($d5 >> 2) & 1) << 7);
		$d3 = ($d3 & 127) | ((($d5 >> 3) & 1) << 7);
		$d4 = ($d4 & 127) | ((($d5 >> 4) & 1) << 7);
		$d5 = ($d5 & 127) | ((($d5 >> 5) & 1) << 7);

		$offset = 0;
		$offset += ($d3 << 24);
		$offset += ($d2 << 16);
		$offset += ($d1 << 8);
		$offset += $d0;
	}

	if ($debug)
	{
		$output = "{$d0},{$d1},{$d2},{$d3},{$d4},{$d5}";
		$output .= " = " . dechex($d0) . "," . dechex($d1) . "," . dechex($d2) . "," .
							dechex($d3) . "," . dechex($d4) . "," . dechex($d5);
		$output .= " = $offset";
		return $output;
	}

	return $offset;
}

function sql_get_roles($item_id=0)
{
	global $roles;

	$where = array();
	if (0 < $item_id)
		$where[] = "USER_ROLE_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT USER_ROLE_ID, UR_ROLE, UR_CODE, UR_COMMENT, SORT_ORDER, OBSOLETE FROM USER_ROLE_SD
			$where ORDER BY SORT_ORDER";
	sql_execute($sql);
	#log_write($sql);#
	$roles = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$roles[] = $newArray;
	return $roles;
}

function sql_get_payment_methods($indexed, $item_id=0)
{
	global $pay_meths;

	if ($item_id == -1)
	{
		$pay_meths = array(array('PAYMENT_METHOD_ID' => 0, 'PAYMENT_METHOD' => '', 'TDX_CODE' => '', 'SORT_ORDER' => 0,
									'OBSOLETE' => 0));
		return $pay_meths;
	}

	$where = array();
	if (0 < $item_id)
		$where[] = "PAYMENT_METHOD_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT PAYMENT_METHOD_ID, PAYMENT_METHOD, TDX_CODE, SORT_ORDER, OBSOLETE
			FROM PAYMENT_METHOD_SD
			$where ORDER BY SORT_ORDER";
	sql_execute($sql);
	#dprint($sql);#
	#log_write($sql);#
	$pay_meths = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($indexed)
			$pay_meths[$newArray['PAYMENT_METHOD_ID']] = $newArray;
		else
			$pay_meths[] = $newArray;
	}
	#dprint("pay_meths=" . print_r($pay_meths,1));#
	return $pay_meths;
}

function sql_get_payment_routes($item_id=0)
{
	global $pay_routes;

	$where = array();
	if (0 < $item_id)
		$where[] = "PAYMENT_ROUTE_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT PAYMENT_ROUTE_ID, PAYMENT_ROUTE, PR_CODE, SORT_ORDER, OBSOLETE FROM PAYMENT_ROUTE_SD
			$where ORDER BY OBSOLETE, SORT_ORDER, PAYMENT_ROUTE";
	sql_execute($sql);
	#log_write($sql);#
	$pay_routes = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$pay_routes[] = $newArray;
	return $pay_routes;
}

function sql_get_job_targets($item_id=0)
{
	global $job_targets;

	if ($item_id == -1)
	{
		$job_targets = array(array('JOB_TARGET_ID' => 0, 'JOB_TYPE_ID' => 0, 'JTA_NAME' => '', 'JTA_TIME' => 0, 'JTA_FEE' => 0.0,
									'OBSOLETE' => 0));
		return $job_targets;
	}

	$where = array();
	if (0 < $item_id)
		$where[] = "JOB_TARGET_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT TA.JOB_TARGET_ID, TA.JOB_TYPE_ID, TA.JTA_NAME, TA.JTA_TIME, TA.JTA_FEE, TA.OBSOLETE, TY.JT_TYPE
			FROM JOB_TARGET_SD AS TA LEFT JOIN JOB_TYPE_SD AS TY ON TY.JOB_TYPE_ID=TA.JOB_TYPE_ID
			$where ORDER BY TY.JOB_TYPE_ID, TA.JTA_TIME DESC";
	#dprint($sql);#
	#log_write($sql);#
	sql_execute($sql);
	$job_targets = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$job_targets[] = $newArray;
	return $job_targets;
}

function sql_get_job_types($for_select_list=false, $include_obsolete=true, $item_id=0)
{
	global $job_types;
	global $sqlFalse;

	if ($item_id == -1)
	{
		$job_types = array(array('JOB_TYPE_ID' => 0, 'JT_TYPE' => '', 'JT_CODE' => '', 'JT_FEE' => 0.0, 'JT_DAYS' => 0,
									'OBSOLETE' => 0));
		return $job_types;
	}

	$where = array();
	if (!$include_obsolete)
		$where[] = "OBSOLETE=$sqlFalse";
	if (0 < $item_id)
		$where[] = "JOB_TYPE_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT JOB_TYPE_ID, JT_TYPE, JT_CODE, JT_FEE, JT_DAYS, OBSOLETE
			FROM JOB_TYPE_SD
			$where
			ORDER BY JOB_TYPE_ID";
	#dprint($sql);#
	#log_write($sql);#
	sql_execute($sql);
	$job_types = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($for_select_list)
			$job_types[$newArray['JOB_TYPE_ID']] = $newArray['JT_TYPE'];
		else
			$job_types[] = $newArray;
	}
	return $job_types;
}

function sql_get_letter_seq_first_client($client2_id)
{
	if ( ! (0 < $client2_id) )
		$client2_id = 0;
	for ($loopy = 0; $loopy <= 1; $loopy++)
	{
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top LETTER_TYPE_ID
				FROM LETTER_SEQ
				WHERE CLIENT2_ID=$client2_id AND OBSOLETE=0
				ORDER BY SEQ_NUM
				$my_limit ";
		$letter_id = 0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$letter_id = $newArray[0];
		if ($letter_id)
			break;
		$client2_id = 0; # try again with default sequence
	}
	return $letter_id;
}

function sql_get_letter_sequences($for_select_list=false, $include_obsolete=true, $item_id=0)
{
	global $letter_sequences;
	global $sqlFalse;

	if ($item_id == -1)
	{
		$letter_sequences = array(array('LETTER_SEQ_ID' => 0, 'CLIENT2_ID' => '', 'SEQ_NUM' => '', 'LETTER_TYPE_ID' => '', 'SEQ_DAYS' => '', 'OBSOLETE' => '',
										'LETTER_NAME' => '', 'C_CODE' => '', 'C_CO_NAME' => ''));
		return $letter_sequences;
	}

	$where = array();
	if (!$include_obsolete)
		$where[] = "OBSOLETE=$sqlFalse";
	if (0 < $item_id)
		$where[] = "LETTER_SEQ_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT LS.LETTER_SEQ_ID, LS.CLIENT2_ID, LS.SEQ_NUM, LS.LETTER_TYPE_ID, LS.SEQ_DAYS, LS.OBSOLETE,
					LT.LETTER_NAME, CL.C_CODE, " . sql_decrypt('CL.C_CO_NAME', '', true) . "
			FROM LETTER_SEQ AS LS
			LEFT JOIN CLIENT2 AS CL ON CL.CLIENT2_ID=LS.CLIENT2_ID
			LEFT JOIN LETTER_TYPE_SD AS LT ON LT.LETTER_TYPE_ID=LS.LETTER_TYPE_ID
			$where
			ORDER BY CLIENT2_ID, SEQ_NUM";
	#dprint($sql);#
	#log_write($sql);#
	sql_execute($sql);
	$letter_sequences = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($for_select_list)
			$letter_sequences[$newArray['LETTER_SEQ_ID']] = $newArray['LETTER_NAME'];
		else
			$letter_sequences[] = $newArray;
	}
	#dprint("Letter Sequences: " . print_r($letter_sequences,1));#
	return $letter_sequences;
}

function sql_get_job_statuses_for_select()
{
	global $job_statuses;
	$job_statuses = sql_get_job_statuses(0, true);
	return $job_statuses;
}

function sql_get_job_statuses($item_id=0, $for_select=false)
{
	global $job_statuses;
	global $sqlFalse;

	if ($item_id == -1)
	{
		$job_statuses = array(array('JOB_STATUS_ID' => 0, 'J_STATUS' => '', 'J_STTS_DESCR' => '', 'J_STTS_CLOSED' => 0,
									'OBSOLETE' => 0));
		return $job_statuses;
	}

	$where = array();
	if (0 < $item_id)
		$where[] = "JOB_STATUS_ID=$item_id";
	if ($for_select)
		$where[] = "OBSOLETE=$sqlFalse";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT JOB_STATUS_ID, J_STATUS, J_STTS_DESCR, J_STTS_CLOSED, OBSOLETE FROM JOB_STATUS_SD
			$where ORDER BY J_STATUS
			";
	#dprint($sql);#
	#log_write($sql);#
	sql_execute($sql);
	$job_statuses = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($for_select)
			$job_statuses[$newArray['JOB_STATUS_ID']] = $newArray['J_STATUS'];
		else
			$job_statuses[] = $newArray;
	}
	return $job_statuses;
}

function sql_get_letter_templates($item_id=0)
{
	global $letter_templates;

	$where = array();
	if (0 < $item_id)
		$where[] = "LETTER_TYPE_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT LETTER_TYPE_ID, LETTER_NAME, JT_T_JOB_TYPE_ID, LETTER_DESCR, LETTER_TEMPLATE,
				LETTER_EM_SUBJECT, LETTER_EM_BODY, JT_T_SUCC, JT_T_OPEN, JT_T_CLOSE, LT_NON_MAN, LT_AUTO_APP, OBSOLETE
  			FROM LETTER_TYPE_SD $where ORDER BY LETTER_TYPE_ID";
	#dprint($sql);#
	sql_execute($sql);
	$letter_templates = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$letter_templates[] = $newArray;
	return $letter_templates;
}

function letter_type_id_from_name($name)
{
	$sql = "SELECT LETTER_TYPE_ID FROM LETTER_TYPE_SD WHERE LETTER_NAME='$name'";
	sql_execute($sql);
	$id = 0;
	while (($newArray = sql_fetch()) != false)
		$id = $newArray[0];
	return $id;
}

function sql_get_letter_open_close()
{
	global $letters_open_close;
	global $sqlFalse;

	$sql = "SELECT JT_T_JOB_TYPE_ID, JT_T_SUCC, JT_T_OPEN, JT_T_CLOSE
  			FROM LETTER_TYPE_SD
  			WHERE (JT_T_JOB_TYPE_ID > 0) AND (OBSOLETE=$sqlFalse)
  			ORDER BY JT_T_JOB_TYPE_ID, JT_T_SUCC";
	#dprint($sql);#
	sql_execute($sql);
	$letters_open_close = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!array_key_exists($newArray['JT_T_JOB_TYPE_ID'], $letters_open_close))
			$letters_open_close[$newArray['JT_T_JOB_TYPE_ID']] =
				array(	 0 => array('OPEN' => '', 'CLOSE' => ''),
						 1 => array('OPEN' => '', 'CLOSE' => ''),
						-1 => array('OPEN' => '', 'CLOSE' => '')
						);
		$success = intval($newArray['JT_T_SUCC']); # 0, 1 or -1
		$letters_open_close[$newArray['JT_T_JOB_TYPE_ID']][$success] =
			array('OPEN' => $newArray['JT_T_OPEN'], 'CLOSE' => $newArray['JT_T_CLOSE']);
	}
}

function sql_get_user_roles_plus($for_select_list=true)
{
	global $id_USER_ROLE_agent;
	global $id_USER_ROLE_developer;
	global $id_USER_ROLE_manager;
	global $id_USER_ROLE_none;
	global $roles;
	global $roles_a;
	global $sqlFalse; # settings.php

	$where = array("OBSOLETE=$sqlFalse");
	if ($for_select_list)
		$where[] = "USER_ROLE_ID <> $id_USER_ROLE_developer";
	$where = 'WHERE (' . implode(') AND (', $where) . ')';

	$sql = "SELECT USER_ROLE_ID, UR_ROLE FROM USER_ROLE_SD $where ORDER BY SORT_ORDER";
	sql_execute($sql);
	$roles = array();
	$roles_a = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($for_select_list)
		{
			if (($newArray['USER_ROLE_ID'] == $id_USER_ROLE_agent) || ($newArray['USER_ROLE_ID'] == $id_USER_ROLE_manager) ||
				($newArray['USER_ROLE_ID'] == $id_USER_ROLE_none))
				$roles[$newArray['USER_ROLE_ID']] = $newArray['UR_ROLE'];
			if (($newArray['USER_ROLE_ID'] == $id_USER_ROLE_manager) || ($newArray['USER_ROLE_ID'] == $id_USER_ROLE_none))
				$roles_a[$newArray['USER_ROLE_ID']] = $newArray['UR_ROLE'];
		}
		else
			$roles[] = $newArray;
	}
} # sql_get_user_roles_plus()

function sql_get_perms()
{
	global $perms;

	$sql = "SELECT USER_PERMISSION_ID, UP_SYS, UP_CODE, UP_PERM, SORT_ORDER, OBSOLETE
					FROM USER_PERMISSION_SD ORDER BY SORT_ORDER";
	sql_execute($sql);
	#log_write($sql);#
	$perms = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$perms[] = $newArray;
}

function sql_get_role_perms()
{
	global $role_perms;

	$sql = "SELECT K.USER_ROLE_PERM_LINK_ID, K.USER_ROLE_ID, K.USER_PERMISSION_ID, P.UP_SYS
			FROM USER_ROLE_PERM_LINK K
			INNER JOIN USER_ROLE_SD R ON R.USER_ROLE_ID=K.USER_ROLE_ID
			INNER JOIN USER_PERMISSION_SD P ON P.USER_PERMISSION_ID=K.USER_PERMISSION_ID
			ORDER BY R.SORT_ORDER, P.SORT_ORDER";
	sql_execute($sql);
	#log_write($sql);#
	$role_perms = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!array_key_exists($newArray['USER_ROLE_ID'], $role_perms))
			$role_perms[$newArray['USER_ROLE_ID']] = array('T' => array(), 'C' => array());
		$role_perms[$newArray['USER_ROLE_ID']][$newArray['UP_SYS']][] = $newArray['USER_PERMISSION_ID'];
	}
}

function sql_get_one_user($user_id)
{
	# Not a portal user.

	global $id_USER_ROLE_none;

	$user = '';
	if ($user_id > 0)
	{
		sql_encryption_preparation('USERV'); # for MS SQL Server

		$sql = "SELECT " . sql_decrypt('USERNAME', '', true) . ",
				" . sql_decrypt('ORIG_USERNAME_C', '', true) . ", " . sql_decrypt('ORIG_USERNAME_T', '', true) . ",
				" . sql_decrypt('U_EMAIL', '', true) . ", IS_ENABLED, IS_LOCKED_OUT, U_IMPORTED, U_HISTORIC,
				" . date_format_sql("CREATED_DT") . ", " . date_format_sql("U_FIRST_DT", 'hms') . ",
				" . date_format_sql("U_LAST_DT", 'hms') . ", " . sql_decrypt('U_NOTES', '', true) . ",
				USER_ROLE_ID_C, USER_ROLE_ID_T, USER_ROLE_ID_A, U_SALES, U_SALES_ISH,
				U_FIRSTNAME, " . sql_decrypt('U_LASTNAME', '', true) . ", U_INITIALS, U_IMPORTED, U_DEBUG, U_JOB_ID
				FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID=$user_id";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$user = $newArray;
			$user['PERMS'] = array();
		}

		$sql = "SELECT USER_PERMISSION_ID FROM USER_PERM_LINK WHERE USER_ID=$user_id";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$user['PERMS'][] = $newArray[0];
	}
	else
		$user = array('USERNAME' => '', 'ORIG_USERNAME_C' => '', 'ORIG_USERNAME_T' => '',
						'U_EMAIL' => '', 'IS_ENABLED' => '1', 'IS_LOCKED_OUT' => '0', 'U_IMPORTED' => '', 'U_HISTORIC' => '',
						'CREATED_DT' => '', 'U_FIRST_DT' => '', 'U_LAST_DT' => '', 'U_NOTES' => '',
						'USER_ROLE_ID_C' => $id_USER_ROLE_none, 'USER_ROLE_ID_T' => $id_USER_ROLE_none, 'USER_ROLE_ID_A' => $id_USER_ROLE_none,
						'U_SALES' => '', 'U_SALES_ISH' => '',
						'U_FIRSTNAME' => '', 'U_LASTNAME' => '', 'U_INITIALS' => '', 'U_IMPORTED' => '', 'U_DEBUG' => '', 'U_JOB_ID' => '',
						'PERMS' => array()
						);
	return $user;

} # sql_get_one_user()

function user_name_from_id($user_id, $full_name=false)
{
	$sql = "SELECT " . sql_decrypt('USERNAME', '', true) . ", U_FIRSTNAME, " . sql_decrypt('U_LASTNAME', '', true) . "
			FROM USERV WHERE USER_ID=$user_id"; # Allow Portal users
	$name = "(user ID $user_id not found)";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$name = ($full_name ? trim("{$newArray['U_FIRSTNAME']} {$newArray['U_LASTNAME']}") : $newArray['USERNAME']);
	}
	return $name;
} # user_name_from_id()

function sql_get_salespersons($real_names=false, $include_disabled=false, $salesperson_table=false)
{
	if ($salesperson_table)
	{

	}
	else
	{
		$detailed = false;
		$for_select_list = true;
		$debug2 = false;
		$sc_text = '';
		$just_salespersons = true;
		$system = '*';
		$role = '';
		return sql_get_users($detailed, $for_select_list, $include_disabled, $debug2, $real_names,
								$sc_text, $just_salespersons, $system, $role);
	}
} # sql_get_salespersons()

function sql_get_salespersons_from_salespersons_table()
{
	# *** THIS IS NOT WORKING ***
//	sql_encryption_preparation('USERV');
//	$sql = "SELECT S.SALESPERSON_ID, S.SP_TXT, U_FIRSTNAME, " . sql_decrypt('U_LASTNAME', '', true) . "
//			FROM SALESPERSON AS S
//			LEFT JOIN USERV AS U ON U.USER_ID=S.SP_USER_ID
//			";
//	sql_execute($sql);
	$salespersons = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$name = trim("{$newArray['U_FIRSTNAME']} {$newArray['U_LASTNAME']}");
//		if (!$name)
//			$name = $newArray['SP_TXT'];
//		if (!$name)
//			$name = "(no name)";
//		$salespersons[$newArray['SALESPERSON_ID']] = $name;
//	}
	return $salespersons;

} # sql_get_salespersons_from_salespersons_table()

function sql_get_agents($system='*', $real_names=false, $include_disabled=false, $include_ids='')
{
	# $system is *, c, t or a

	global $role_agt;

	$detailed = false;
	$for_select_list = true;
	$debug2 = false;
	$sc_text = '';
	$just_salespersons = false;
	$just_managers = false;
	return sql_get_users($detailed, $for_select_list, $include_disabled, $debug2, $real_names,
							$sc_text, $just_salespersons, $system, $role_agt, $include_ids, $just_managers);
} # sql_get_agents()

function sql_get_users($detailed=false, $for_select_list=true, $include_disabled=false, $debug2=false, $real_names=false,
						$sc_text='', $just_salespersons=false, $system='*', $role='', $include_ids='', $just_managers=false,
						$include_historic=true, $sort_time=false)
{
	# Not Portal Users
	# $system is *, c, t or a

	global $id_USER_ROLE_manager;
#	global $id_USER_ROLE_super;
#	global $id_USER_ROLE_rev;
	global $NEW_USERS; # lib_users.php, only used if we are being called from users.php
	global $role_dev;
	global $sqlFalse;
	global $sqlTrue;
	global $users;

	#dprint("sql_get_users(..., \$include_ids=" . print_r($include_ids,1));#

	$old_login_ids = array();
	if ($NEW_USERS && $sc_text)
	{
		# We are being called from users.php and searching for a match on user name.
		# $NEW_USERS is defined in lib_users.php.
		$old_found = false;
		$old_id = 0; # first real user in $NEW_USERS has USER_ID 4
		foreach ($NEW_USERS as $new_user)
		{
			if (4 <= $old_id)
			{
				$old_lg = $new_user[1];
				foreach ($old_lg as $olg)
				{
					# E.g. $olg[1] is 'SALES JGJ' and $olg[2] is 'USER0042'
					if ((strtolower($olg[1]) == strtolower($sc_text)) || (strtolower($olg[2]) == strtolower($sc_text)))
					{
						$old_login_ids[] = $old_id;
						$old_found = true;
						break;
					}
				}
			}
			if ($old_found)
				break;
			$old_id++;
		}
	}
	#dprint("\$old_login_ids=" . print_r($old_login_ids,1));

	$t_user = ($debug2 ? 'USERV2' : 'USERV');
	$t_role = ($debug2 ? 'USER_ROLE_SD_2' : 'USER_ROLE_SD');

	sql_encryption_preparation($t_user); # for MS SQL Server

	$where = array('CLIENT2_ID IS NULL'); # not portal users
	if ($sc_text)
	{
		$sc_user_id = (($sc_text[0] == '*') ? intval(trim(str_replace('*', '', $sc_text))) : 0);
		if ($sc_user_id > 0)
			$where[] = "U.USER_ID = $sc_user_id";
		else
			$where[] = "(" . sql_decrypt('U.USERNAME') . " LIKE '%" . addslashes_kdb($sc_text) . "%') OR
						(U.U_FIRSTNAME LIKE '%" . addslashes_kdb($sc_text) . "%') OR
						(" . sql_decrypt('U.U_LASTNAME') . " LIKE '%" . addslashes_kdb($sc_text) . "%') OR
						(U.U_INITIALS = '" . addslashes_kdb($sc_text) . "')";
	}
	if (!$include_disabled)
		$where[] = "U.IS_ENABLED=$sqlTrue";
	if (!$include_historic)
		$where[] = "U.U_HISTORIC=$sqlFalse";
	if ($just_salespersons)
		$where[] = "(U.U_SALES=$sqlTrue OR U.U_SALES_ISH=$sqlTrue)";
//	if ($just_managers)
//		$where[] = "(U.USER_ROLE_ID_C IN ($id_USER_ROLE_manager, $id_USER_ROLE_super, $id_USER_ROLE_rev)) OR
//					(U.USER_ROLE_ID_T IN ($id_USER_ROLE_manager, $id_USER_ROLE_super, $id_USER_ROLE_rev)) OR
//					(U.USER_ROLE_ID_A IN ($id_USER_ROLE_manager, $id_USER_ROLE_super, $id_USER_ROLE_rev))";
	if ($just_managers)
		$where[] = "(U.USER_ROLE_ID_C IN ($id_USER_ROLE_manager)) OR
					(U.USER_ROLE_ID_T IN ($id_USER_ROLE_manager)) OR
					(U.USER_ROLE_ID_A IN ($id_USER_ROLE_manager))";

	if ($old_login_ids)
		$old_login_ids_txt = "U.USER_ID IN (" . implode(',', $old_login_ids) . ")";
	else
		$old_login_ids_txt = '';

	if (is_array($include_ids))
		$include_ids_txt = "U.USER_ID IN (" . implode(',', $include_ids) . ")";
	else
		$include_ids_txt = '';

	$wor = array();
	if (0 < count($where))
		$wor[] = "(" . implode(') AND (', $where) . ")";
	else
		$wor[] = "(0=0)";
	if ($old_login_ids_txt)
		$wor[] = $old_login_ids_txt;
	if ($include_ids_txt)
		$wor[] = $include_ids_txt;
	if (0 < count($wor))
		$where = "WHERE (" . implode(') OR (', $wor) . ")";
	else
		$where = '';

	if ($detailed)
		$sql = "SELECT U.USER_ID, " . sql_decrypt("U.USERNAME", '', true) . ",
				U.U_FIRSTNAME, " . sql_decrypt("U.U_LASTNAME", '', true) . ", U.U_INITIALS,
				U.IS_ENABLED, U.IS_LOCKED_OUT, U.USER_ROLE_ID_C, U.USER_ROLE_ID_T, U.USER_ROLE_ID_A,
				" . sqla('RC.UR_ROLE') . " AS UR_ROLE_C, " . sqla('RT.UR_ROLE') . " AS UR_ROLE_T,
				" . sqla('RA.UR_ROLE') . " AS UR_ROLE_A, " . sql_decrypt("U.U_EMAIL", '', true) . ",
				U.U_FIRST_DT, U.U_LAST_DT, U.U_HISTORIC, U.U_SALES, U.U_SALES_ISH
				FROM $t_user AS U
				LEFT JOIN $t_role AS RC ON U.USER_ROLE_ID_C=RC.USER_ROLE_ID
				LEFT JOIN $t_role AS RT ON U.USER_ROLE_ID_T=RT.USER_ROLE_ID
				LEFT JOIN $t_role AS RA ON U.USER_ROLE_ID_A=RA.USER_ROLE_ID
				$where ORDER BY " . ($sort_time ? 'U.U_LAST_DT DESC' : sql_decrypt("U.USERNAME")) . "";
				#U.U_FIRSTNAME, " . sql_decrypt("U.U_LASTNAME") . ", " . sql_decrypt("U.USERNAME") . ""
	elseif ($real_names)
		$sql = "SELECT U.USER_ID, U.U_FIRSTNAME, " . sql_decrypt("U.U_LASTNAME", '', true) . ", " . sql_decrypt("U.USERNAME", '', true) . "
				FROM $t_user AS U
				$where ORDER BY U.U_HISTORIC, U.IS_ENABLED, U.U_FIRSTNAME, " . sql_decrypt("U.U_LASTNAME") . " ";
	else
		$sql = "SELECT U.USER_ID, " . sql_decrypt("U.USERNAME", '', true) . "
				FROM $t_user AS U
				$where ORDER BY " . sql_decrypt("U.USERNAME") . "";
	sql_execute($sql);
	if (user_debug())
		dprint($sql);
	$users_1 = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ((!$detailed) && $for_select_list)
		{
			if ($real_names)
			{
				$name = "{$newArray['U_FIRSTNAME']} {$newArray['U_LASTNAME']}";
				if ($name == "- -")
					$name = $newArray['USERNAME'];
			}
			else
				$name = $newArray['USERNAME'];
			$users_1[$newArray['USER_ID']] = $name;
		}
		else
			$users_1[] = $newArray;
	}
	$users = array();
	foreach ($users_1 as $uid => $user)
	{
		if ( (($system == '*') && ($role == '')) || ($include_ids_txt && in_array($uid, $include_ids)) )
			$users[$uid] = $user;
		else
		{
			if (role_check($system, $role, $uid))
			{
				if (($role != $role_dev) && (!role_check($system, $role_dev, $uid)))
					$users[$uid] = $user;
			}
		}
	}
	#dprint(print_r($users,1));#
	return $users;
} # sql_get_users()

function sql_get_clients_for_select($sc_system, $current_client2_id=0, $sort_by_code=false, $portal_only=false)
{
	global $client2_id_currently_selected;
	$client2_id_currently_selected = $current_client2_id;

	$sc_text = '';
	$sc_alpha = '';
	$sc_addr = '';
	$sc_contact = '';
	$sc_group = '';
	$sc_bank = '';
	$sc_open = '';
	$for_select = true;
	$sc_fstmt = false;
	$sc_uninvpay = false;
	$sc_uninvbill = false;
	$sc_stmtinv = false;
	$sc_bacs = false;
	$sc_vat = false;
	$sc_archived = false;
	$sort_by_code = true;
	$sort_column = '';
	return sql_get_clients($sc_system, $sc_text, $sc_alpha, $sc_addr, $sc_contact, $sc_group, $sc_bank, $sc_open, $for_select, $sc_fstmt, $sc_uninvpay, $sc_uninvbill, $sc_stmtinv, $sc_bacs, $sc_vat, $sc_archived, $sort_by_code, $sort_column, $portal_only);
}

function sql_get_clients($sc_system, $sc_text='', $sc_alpha='', $sc_addr='', $sc_contact='', $sc_group='', $sc_bank='', $sc_open='', $for_select=false, $sc_fstmt=false, $sc_uninvpay=false, $sc_uninvbill=false, $sc_stmtinv=false, $sc_bacs=false, $sc_vat=false, $sc_archived=false, $sort_by_code=false, $sort_column='', $portal_only=false)
{
	# $sc_system should be '' or '*' (all), 'c' (collect) or 't' (trace)

	global $client2_id_currently_selected;
	global $sqlFalse;
	global $sqlTrue;
	global $time_tests;

	$abort = false;

	$exclude_codes = '';
	if ($portal_only)
	{
		$sql = "SELECT C_CODE, COUNT(*)
				FROM CLIENT2
				GROUP BY C_CODE
				HAVING 1 < COUNT(*)";
		dprint($sql);
		sql_execute($sql);
		$exclude_codes = array();
		while (($newArray = sql_fetch()) != false)
			$exclude_codes[] = $newArray[0];
		if ($exclude_codes)
			$exclude_codes = "(CL.C_CODE NOT IN(" . implode(',', $exclude_codes) . "))";
		else
			$exclude_codes = " ";
	}

	$client_ids_from_contacts = array();
	if ($sc_contact)
	{
		sql_encryption_preparation('CLIENT_CONTACT');
		sql_encryption_preparation('CLIENT_CONTACT_PHONE');
		$sql = "SELECT DISTINCT CC.CLIENT2_ID FROM CLIENT_CONTACT AS CC
				LEFT JOIN CLIENT_CONTACT_PHONE AS PH ON PH.CLIENT_CONTACT_ID=CC.CLIENT_CONTACT_ID
				WHERE	(" . sql_decrypt('CC.CC_FIRSTNAME') . " LIKE '%" . addslashes_kdb($sc_contact) . "%') OR
						(" . sql_decrypt('CC.CC_LASTNAME') . " LIKE '%" . addslashes_kdb($sc_contact) . "%') OR
						" .
//						"
//						(CONCAT(" . sql_decrypt('CC.CC_FIRSTNAME') . ", ' ', " . sql_decrypt('CC.CC_LASTNAME') . ")
//						" .
						"
						((" . sql_decrypt('CC.CC_FIRSTNAME') . " + ' ' + " . sql_decrypt('CC.CC_LASTNAME') . ")
						" .
						"
															LIKE '%" . addslashes_kdb($sc_contact) . "%') OR
						(" . sql_decrypt('PH.CP_PHONE') . " LIKE '%" . addslashes_kdb($sc_contact) . "%') OR
						(" . sql_decrypt('CC.CC_EMAIL_1') . " LIKE '%" . addslashes_kdb($sc_contact) . "%') OR
						(" . sql_decrypt('CC.CC_EMAIL_2') . " LIKE '%" . addslashes_kdb($sc_contact) . "%')";
		#dprint("sql_get_clients(): $sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_ids_from_contacts[] = $newArray['CLIENT2_ID'];
		if (!$client_ids_from_contacts)
			$abort = true;
	}

	$client_ids_from_jobs = array();
	if ((!$abort) && $sc_open)
	{
		$sql = "SELECT DISTINCT CLIENT2_ID FROM JOB WHERE JOB_CLOSED=$sqlFalse AND J_ARCHIVED=$sqlFalse";
		#dprint("sql_get_clients(): $sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_ids_from_jobs[] = $newArray['CLIENT2_ID'];
		if (!$client_ids_from_jobs)
			$abort = true;
	}

	$client_ids_from_payments = array();
	if ((!$abort) && $sc_uninvpay)
	{
		# We only consider payments made in the last two years on jobs which are still open.
		$dt = date_n_years_ago(2);
		$sql = "SELECT DISTINCT J.CLIENT2_ID
				FROM JOB AS J INNER JOIN JOB_PAYMENT AS P ON P.JOB_ID=J.JOB_ID
				WHERE J.JOB_CLOSED=$sqlFalse AND P.INVOICE_ID IS NULL AND P.COL_DT_RX IS NOT NULL AND '$dt' <= P.COL_DT_RX AND P.OBSOLETE=$sqlFalse
					 AND J.J_ARCHIVED=$sqlFalse";
		#dprint("sql_get_clients(): $sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_ids_from_payments[] = $newArray['CLIENT2_ID'];
		#dprint("\$client_ids_from_payments=" . print_r($client_ids_from_payments,1));#
		if (!$client_ids_from_payments)
			$abort = true;
	}

	$client_ids_from_billing = array();
	if ((!$abort) && $sc_uninvbill)
	{
		# We only consider billing made in the last two years on jobs which are closed with statement invoicing.
		$dt = date_n_years_ago(2);
		$sql = "SELECT DISTINCT J.CLIENT2_ID
				FROM JOB AS J INNER JOIN INV_BILLING AS B ON B.JOB_ID=J.JOB_ID
				INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
				WHERE (J.OBSOLETE=$sqlFalse) AND (J.JT_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlTrue) AND ('$dt' < J.J_CLOSED_DT) AND
						(J.J_S_INVS=$sqlTrue) AND (B.INVOICE_ID IS NULL) AND (B.OBSOLETE=$sqlFalse) AND (0 < B.BL_COST) AND (COALESCE(B.BL_LPOS,1) < 2)
						 AND J.J_ARCHIVED=$sqlFalse
				";
				#		AND ( (C.INV_NEXT_STMT_DT IS NULL) OR (C.INV_NEXT_STMT_DT='') OR (C.INV_NEXT_STMT_DT <= GETDATE()) )
		#dprint("sql_get_clients(): $sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_ids_from_billing[] = $newArray['CLIENT2_ID'];
		#dprint("\$client_ids_from_billing=" . print_r($client_ids_from_billing,1));#
		if (!$client_ids_from_billing)
			$abort = true;
	}
	#dprint("\$client_ids_from_billing=" . print_r($client_ids_from_billing,1));#

	if ($abort)
		return array();

	$where = array();

	$client_id_list = array();
	if ($client_ids_from_contacts)
	{
		if ($client_id_list)
			$client_id_list = array_intersect($client_id_list, $client_ids_from_contacts);
		else
			$client_id_list = $client_ids_from_contacts;
	}
	if ($client_ids_from_jobs)
	{
		if ($client_id_list)
			$client_id_list = array_intersect($client_id_list, $client_ids_from_jobs);
		else
			$client_id_list = $client_ids_from_jobs;
	}
	if ($client_ids_from_payments)
	{
		if ($client_ids_from_billing)
		{
			if ($client_id_list)
				$client_id_list = array_intersect($client_ids_from_payments, $client_ids_from_billing, $client_id_list);
			else
				$client_id_list = array_intersect($client_ids_from_payments, $client_ids_from_billing);
		}
		else
		{
			if ($client_id_list)
				$client_id_list = array_intersect($client_ids_from_payments, $client_id_list);
			else
				$client_id_list = $client_ids_from_payments;
		}
	}
	else
	{
		if ($client_ids_from_billing)
		{
			if ($client_id_list)
				$client_id_list = array_intersect($client_ids_from_billing, $client_id_list);
			else
				$client_id_list = $client_ids_from_billing;
		}
		else
		{
			if ($client_id_list)
				$client_id_list = $client_id_list;
		}
	}

	#dprint("\$client_id_list=" . print_r($client_id_list,1));#
	if ($client_id_list)
		$where[] = "CL.CLIENT2_ID IN (" . implode (',', $client_id_list) . ")";
//	if ($client_ids_from_contacts && $client_ids_from_jobs)
//	{
//		$temp = array_intersect($client_ids_from_contacts, $client_ids_from_jobs);
//		if ($temp)
//			$where[] = "CL.CLIENT2_ID IN (" . implode (',', $temp) . ")";
//	}
//	elseif ($client_ids_from_contacts)
//		$where[] = "CL.CLIENT2_ID IN (" . implode (',', $client_ids_from_contacts) . ")";
//	elseif ($client_ids_from_jobs)
//		$where[] = "CL.CLIENT2_ID IN (" . implode (',', $client_ids_from_jobs) . ")";

	if ($sc_archived)
		$where[] = "CL.C_ARCHIVED=$sqlTrue";
	else
		$where[] = "CL.C_ARCHIVED=$sqlFalse";

	if ($sc_text)
	{
		$sc_text_done = false;
		if ((strpos($sc_text,',') !== false) && is_numeric_kdb(str_replace(',', '', str_replace(' ', '', $sc_text)), false, false, false))
		{
			# Assume this is a comma-separated list of client codes
			$bits = explode(',', $sc_text);
			$cc_list = array();
			for ($ii = 0; $ii < count($bits); $ii++)
			{
				$test_cc = intval(str_replace(' ', '', $bits[$ii]));
				if (0 < $test_cc)
					$cc_list[] = $test_cc;
			} # for $bits
			if ($cc_list)
			{
				$where[] = "CL.C_CODE IN (" . implode(',', $cc_list) . ")";
				$sc_text_done = true;
			}
		}
		if (!$sc_text_done)
		{
			$sc_c_code = intval($sc_text);
			if ($sc_c_code > 0)
				$where[] = "CL.C_CODE = $sc_c_code";
			else
			{
				$sc_client2_id = (($sc_text[0] == '*') ? intval(trim(str_replace('*', '', $sc_text))) : 0);
				if ($sc_client2_id > 0)
				{
					$bits = explode(',', $sc_text);
					$id_list = array();
					for ($ii = 0; $ii < count($bits); $ii++)
					{
						$test_ok = false;
						if ($bits[$ii][0] == '*')
						{
							$test_id = intval(trim(str_replace('*', '', $bits[$ii])));
							if (0 < $test_id)
							{
								$id_list[] = $test_id;
								$test_ok = true;
							}
						}
						if (!$test_ok)
						{
							$id_list = array();
							break;
						}
					} # for $bits
					if (2 <= count($id_list))
						$where[] = "CL.CLIENT2_ID IN (" . implode(',', $id_list) . ")";
					else
						$where[] = "CL.CLIENT2_ID = $sc_client2_id";
				}
				else
					$where[] = sql_decrypt('CL.C_CO_NAME') . " LIKE '%" . addslashes_kdb($sc_text) . "%'";
			}
		}
	}
	if ($sc_alpha)
	{
		$where[] = "CL.ALPHA_CODE='" . addslashes_kdb($sc_alpha) . "'";
	}
	if ($sc_addr)
		$where[] = "(" . sql_decrypt('CL.C_ADDR_1') . " LIKE '%" . addslashes_kdb($sc_addr) . "%') OR
					(" . sql_decrypt('CL.C_ADDR_2') . " LIKE '%" . addslashes_kdb($sc_addr) . "%') OR
					(" . sql_decrypt('CL.C_ADDR_3') . " LIKE '%" . addslashes_kdb($sc_addr) . "%') OR
					(" . sql_decrypt('CL.C_ADDR_4') . " LIKE '%" . addslashes_kdb($sc_addr) . "%') OR
					(" . sql_decrypt('CL.C_ADDR_5') . " LIKE '%" . addslashes_kdb($sc_addr) . "%') OR
					(REPLACE(" . sql_decrypt('CL.C_ADDR_PC') . ",' ','') LIKE '%" . addslashes_kdb(str_replace(' ', '', $sc_addr)) . "%')
					";
	if ($sc_system == 'c')
		$where[] = "CL.C_COLLECT=$sqlTrue";
	elseif ($sc_system == 't')
		$where[] = "CL.C_TRACE=$sqlTrue";
	if ($sc_group)
	{
		$temp = intval($sc_group);
		if (0 < $temp)
			$where[] = "CL.CLIENT_GROUP_ID=$temp";
	}
	if ($sc_bank)
	{
		if ($sc_bank == '*')
			$where[] = "(	((" . sql_decrypt('CL.C_BANK_ACC_NUM') . " IS NOT NULL) AND (" . sql_decrypt('CL.C_BANK_ACC_NUM') . " <> '')) OR
							((" . sql_decrypt('CL.C_BANK_SWIFT') . " IS NOT NULL) AND (" . sql_decrypt('CL.C_BANK_SWIFT') . " <> '')) OR
							((" . sql_decrypt('CL.C_BANK_IBAN') . " IS NOT NULL) AND (" . sql_decrypt('CL.C_BANK_IBAN') . " <> ''))
						)";
		elseif ($sc_bank == 'x')
			$where[] = "(	((" . sql_decrypt('CL.C_BANK_ACC_NUM') . " IS NULL) OR (" . sql_decrypt('CL.C_BANK_ACC_NUM') . " = '')) OR
							((" . sql_decrypt('CL.C_BANK_SWIFT') . " IS NULL) OR (" . sql_decrypt('CL.C_BANK_SWIFT') . " = '')) OR
							((" . sql_decrypt('CL.C_BANK_IBAN') . " IS NULL) OR (" . sql_decrypt('CL.C_BANK_IBAN') . " = ''))
						)";
		else
			$where[] = "(	(" . sql_decrypt('CL.C_BANK_ACC_NUM') . "='" . addslashes_kdb($sc_bank) . "') OR
							(" . sql_decrypt('CL.C_BANK_SWIFT') . "='" . addslashes_kdb($sc_bank) . "') OR
							(" . sql_decrypt('CL.C_BANK_IBAN') . "='" . addslashes_kdb($sc_bank) . "')
						)";
	}
	if ($sc_fstmt)
	{
		$now = time() + (($sc_fstmt+1) * 24 * 60 * 60);
		$now_sql = date_for_sql(date_now(true, $now));
		$where[] = "( (CL.INV_NEXT_STMT_DT IS NULL) OR (CL.INV_NEXT_STMT_DT='') OR (CL.INV_NEXT_STMT_DT < $now_sql) )";
	}

	if ($sc_stmtinv == 1)
		#$where[] = "( (CL.C_COLLECT=$sqlTrue) OR (CL.S_INVS_TRACE=$sqlTrue) )";
		$where[] = "CL.S_INVS_TRACE=$sqlTrue";
	elseif ($sc_stmtinv == -1)
		$where[] = "CL.S_INVS_TRACE=$sqlFalse";

	if ($sc_bacs == 1)
		$where[] = "CL.C_BACS=$sqlTrue";
	elseif ($sc_bacs == -1)
		$where[] = "CL.C_BACS=$sqlFalse";

	if ($sc_vat == 1)
		$where[] = "CL.C_VAT=$sqlTrue";
	elseif ($sc_vat == -1)
		$where[] = "CL.C_VAT=$sqlFalse";

	if ($exclude_codes)
		$where[] = $exclude_codes;

	sql_encryption_preparation('CLIENT2');
	if ($for_select)
	{
		$where_or = '';
		if (0 < $client2_id_currently_selected)
			$where_or = "CL.CLIENT2_ID=$client2_id_currently_selected";
		if ($where)
		{
			$where = "(" . implode(') AND (', $where) . ")";
			if ($where_or)
				$where = "($where) OR ($where_or)";
		}
		elseif ($where_or)
			$where = $where_or;

		$sql = "SELECT CL.CLIENT2_ID, CL.C_CODE, " . sql_decrypt('CL.C_CO_NAME', '', true) . "
				FROM CLIENT2 AS CL
				" . ($where ? "WHERE $where" : '') . "
				ORDER BY " . ($sort_by_code ? 'C_CODE, ' : '') . (($sc_fstmt || $sc_uninvpay) ? "CL.INV_NEXT_STMT_DT, " : '') . sql_decrypt('CL.C_CO_NAME') . "
				";
		#dprint("sql_get_clients()/1: $sql");#
	}
	else
	{
		sql_encryption_preparation('CLIENT_CONTACT');
		sql_encryption_preparation('CLIENT_CONTACT_PHONE');
		$sql = "SELECT CL.CLIENT2_ID, CL.C_CODE, " . sql_decrypt('CL.C_CO_NAME', '', true) . ", CL.C_COLLECT, CL.C_TRACE, CL.IMPORTED,
				" . sql_decrypt('CL.C_ADDR_1', '', true) . ", " . sql_decrypt('CL.C_ADDR_2', '', true) . ",
				" . sql_decrypt('CL.C_ADDR_3', '', true) . ", " . sql_decrypt('CL.C_ADDR_4', '', true) . ",
				" . sql_decrypt('CL.C_ADDR_5', '', true) . ", " . sql_decrypt('CL.C_ADDR_PC', '', true) . ",
				" . sql_decrypt('CC.CC_FIRSTNAME', '', true) . ", " . sql_decrypt('CC.CC_LASTNAME', '', true) . ",
				" . sql_decrypt('CC.CC_EMAIL_1', '', true) . ", " . sql_decrypt('CC.CC_EMAIL_2', '', true) . ",
				" . sql_decrypt('PH.CP_PHONE', '', true) . ",
				" . (($sc_fstmt || $sc_uninvpay) ? "CL.INV_NEXT_STMT_DT, " : '') . "CL.C_ARCHIVED,
				SUM(CASE JB.JOB_CLOSED WHEN 0 THEN (CASE JB.JT_JOB WHEN 1 THEN 1 ELSE 0 END) ELSE 0 END) AS OPEN_JOBS_T,
				SUM(CASE JB.JOB_CLOSED WHEN 0 THEN (CASE JB.JC_JOB WHEN 1 THEN 1 ELSE 0 END) ELSE 0 END) AS OPEN_JOBS_C
				FROM CLIENT2 AS CL
				LEFT JOIN CLIENT_CONTACT AS CC ON CC.CLIENT2_ID=CL.CLIENT2_ID AND CC.CC_MAIN=$sqlTrue
				LEFT JOIN CLIENT_CONTACT_PHONE AS PH ON PH.CLIENT_CONTACT_ID=CC.CLIENT_CONTACT_ID AND PH.CP_MAIN=$sqlTrue
				LEFT JOIN JOB AS JB ON JB.CLIENT2_ID=CL.CLIENT2_ID
				" . ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '') . "
				GROUP BY CL.CLIENT2_ID, CL.C_CODE, " . sql_decrypt('CL.C_CO_NAME') . ", CL.C_COLLECT, CL.C_TRACE, CL.IMPORTED,
						" . sql_decrypt('CL.C_ADDR_1') . ", " . sql_decrypt('CL.C_ADDR_2') . ",
						" . sql_decrypt('CL.C_ADDR_3') . ", " . sql_decrypt('CL.C_ADDR_4') . ",
						" . sql_decrypt('CL.C_ADDR_5') . ", " . sql_decrypt('CL.C_ADDR_PC') . ",
						" . sql_decrypt('CC.CC_FIRSTNAME') . ", " . sql_decrypt('CC.CC_LASTNAME') . ",
						" . sql_decrypt('CC.CC_EMAIL_1') . ", " . sql_decrypt('CC.CC_EMAIL_2') . ",
						" . sql_decrypt('PH.CP_PHONE') . (($sc_fstmt || $sc_uninvpay) ? ", CL.INV_NEXT_STMT_DT" : '') . ", CL.C_ARCHIVED
				ORDER BY ";
				if ($sort_column)
					$sql .= $sort_column;
				else
					$sql .= (($sort_by_code ? 'C_CODE, ' : '') . (($sc_fstmt || $sc_uninvpay) ? "CL.INV_NEXT_STMT_DT, " : '') . "C_CODE, " . sql_decrypt('CL.C_CO_NAME'));
		#dprint("sql_get_clients()/2: $sql");#
	}
	$clients = array();
	sql_execute($sql);
	if ($time_tests)
		$t_start = time();# Takes 10 seconds to fetch data! 19/01/17
	$found_ids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$this_id = $newArray['CLIENT2_ID'];
		if (!in_array($this_id, $found_ids))
		{
			if ($for_select)
				$clients[$this_id] = "{$newArray['C_CODE']} - {$newArray['C_CO_NAME']}";
			else
				$clients[] = $newArray;
			$found_ids[] = $this_id;
		}
	}
	if ($time_tests)
	{
		$t_length = time() - $t_start;
		log_write("sql_get_clients() took $t_length seconds to fetch SQL with " . count($clients) . " clients.", true, true);
	}
	#dprint("clients=" . print_r($clients,1));#
	return $clients;
} # sql_get_clients()

function client_id_from_code($code)
{
	$cid = 0;
	$code = intval($code);
	$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE C_CODE=$code";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$cid = $newArray[0];
	return $cid;
} # client_id_from_code()

function client_name_from_id($client2_id)
{
	sql_encryption_preparation('CLIENT2');
	$client2_id = intval($client2_id);
	$sql = "SELECT C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . ", ALPHA_CODE
			FROM CLIENT2
			WHERE CLIENT2_ID=$client2_id";
	sql_execute($sql);
	$client = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$client = $newArray;
	return $client;
} # client_name_from_id()

function client_name_from_code($code)
{
	# This assumes that CLIENT2.C_CODE is unique although there are one or two clients for whom it is not.
	sql_encryption_preparation('CLIENT2');
	$code = intval($code);
	$sql = "SELECT " . sql_decrypt('C_CO_NAME') . " FROM CLIENT2 WHERE C_CODE=$code";
	sql_execute($sql);
	$client = '';
	while (($newArray = sql_fetch()) != false)
		$client = $newArray[0];
	return $client;
} # client_name_from_code()

function sql_get_one_client($client2_id)
{
	global $sqlFalse;
	global $sqlTrue;

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('CLIENT_NOTE');
	sql_encryption_preparation('CLIENT_CONTACT');
	sql_encryption_preparation('CLIENT_CONTACT_PHONE');
	sql_encryption_preparation('CLIENT_GROUP');

	$client2_id = intval($client2_id);
	$sql = "SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ", C.IMPORTED, C.ALPHA_CODE,
			" . sql_decrypt('C.C_ADDR_1', '', true) . ", " . sql_decrypt('C.C_ADDR_2', '', true) . ",
			" . sql_decrypt('C.C_ADDR_3', '', true) . ", " . sql_decrypt('C.C_ADDR_4', '', true) . ",
			" . sql_decrypt('C.C_ADDR_5', '', true) . ", " . sql_decrypt('C.C_ADDR_PC', '', true) . ",
			C.S_INVS_TRACE, C.DEDUCT_AS, C.COMM_PERCENT, C.SALESPERSON_ID, C.SALESPERSON_TXT,
			" . sql_decrypt('U.USERNAME') . " AS SALESPERSON_USER,
			C.TR_FEE, C.NT_FEE, C.TC_FEE, C.RP_FEE, C.SV_FEE, C.MN_FEE, C.TM_FEE, C.AT_FEE, C.ET_FEE,
			C.C_BACS, " . sql_decrypt('C.C_BANK_SORTCODE', '', true) . ", " . sql_decrypt('C.C_BANK_ACC_NUM', '', true) . ",
			" . sql_decrypt('C.C_BANK_NAME', '', true) . ", C.C_BANK_COUNTRY,
			" . sql_decrypt('C.C_BANK_ACC_NAME', '', true) . ", C.INV_STMT_FREQ, C.INV_NEXT_STMT_DT,
			C.CLIENT_GROUP_ID, " . sql_decrypt('G.GROUP_NAME', '', true) . ", C.C_GROUP_HO, C.C_CLOSEOUT,
			C.C_INDIVIDUAL, C.C_AGENCY, C.S_INVS_TRACE, C.DEDUCT_AS, C.C_TRACE, C.C_COLLECT, C.CREATED_DT, C.UPDATED_DT,
			C.C_VAT, C.INV_EMAILED, C.INV_COMBINE, C.INV_BRANCH_COMB, " . sql_decrypt('C.INV_EMAIL_ADDR', '', true) . ",
			" . sql_decrypt('C.INV_EMAIL_NAME', '', true) . ", C.C_ARCHIVED,
			" . sql_decrypt('C.C_BANK_SWIFT', '', true) . ", " . sql_decrypt('C.C_BANK_IBAN', '', true) . ", C.PORTAL_PUSH
			FROM CLIENT2 AS C
			LEFT JOIN USERV AS U ON U.USER_ID=C.SALESPERSON_ID
			LEFT JOIN CLIENT_GROUP AS G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID
			WHERE C.CLIENT2_ID=$client2_id";
	sql_execute($sql);
	$client = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		#dprint("COMM=\"{$newArray['COMM_PERCENT']}\"");#
		#if ($newArray['COMM_PERCENT'] == ".000")
		#	$newArray['COMM_PERCENT'] = "0.000";
		$newArray['COMM_PERCENT'] = floatval($newArray['COMM_PERCENT']);
		$client = $newArray;
	}
	$client['NOTES_IMP'] = '';
	$client['NOTES_IMP_2'] = ''; # if imported notes were very large then may be split into two, three or even four
	$client['NOTES_IMP_3'] = '';
	$client['NOTES_IMP_4'] = '';
	$client['NOTES'] = array();
	$sql = "SELECT N.CLIENT_NOTE_ID, " . sql_decrypt('N.CN_NOTE', '', true) . ",
				" . sql_decrypt('UC.USERNAME') . " AS USERNAME_C, N.CN_ADDED_DT,
				" . sql_decrypt('UU.USERNAME') . " AS USERNAME_U, N.CN_UPDATED_DT,
					N.IMPORTED
			FROM CLIENT_NOTE AS N
			LEFT JOIN USERV AS UC ON UC.USER_ID=N.CN_ADDED_ID
			LEFT JOIN USERV AS UU ON UU.USER_ID=N.CN_UPDATED_ID
			WHERE N.CLIENT2_ID=$client2_id
			ORDER BY N.CLIENT_NOTE_ID DESC";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($newArray['IMPORTED'])
		{
			if ($client['NOTES_IMP'] == '')
				$client['NOTES_IMP'] = $newArray['CN_NOTE'];
			elseif ($client['NOTES_IMP_2'] == '')
				$client['NOTES_IMP_2'] = $newArray['CN_NOTE'];
			elseif ($client['NOTES_IMP_3'] == '')
				$client['NOTES_IMP_3'] = $newArray['CN_NOTE'];
			else
				$client['NOTES_IMP_4'] = $newArray['CN_NOTE'];
		}
		else
			$client['NOTES'][$newArray['CLIENT_NOTE_ID']] = $newArray;
	}
//	dprint("Imported notes:");
//	dprint("1=" . $client['NOTES_IMP']);
//	dprint("2=" . $client['NOTES_IMP_2']);
//	dprint("3=" . $client['NOTES_IMP_3']);
//	dprint("4=" . $client['NOTES_IMP_4']);

	$client['NUM_JOBS'] = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE CLIENT2_ID=$client2_id AND OBSOLETE=$sqlFalse";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$client['NUM_JOBS'] = $newArray[0];

	$client['NUM_JOBS_OPEN'] = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE CLIENT2_ID=$client2_id AND JOB_CLOSED=$sqlFalse AND OBSOLETE=$sqlFalse";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$client['NUM_JOBS_OPEN'] = $newArray[0];

	$client['CLIENT_Z'] = '';
	if ($client['IMPORTED'])
	{
		$sql = "SELECT Z_ACC, Z_AP_AMOUNT, Z_AP_CODE, Z_AP_DEF, Z_CS_DEL, Z_CSPENT, Z_DI_DEL, Z_DIRECT, Z_FO_DEL,
					Z_FORWARDED, Z_JOBS, Z_LASTREP, Z_MARK, Z_NOTRACES, Z_REP_ORD, Z_RETR, Z_TO_US, Z_TU_DEL, Z_VPS
				FROM CLIENT_Z WHERE CLIENT2_ID=$client2_id";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			foreach ($newArray as $new_f => $new_v)
			{
				if ($new_v == '.00')
					$newArray[$new_f] = '0.00'; # Fix bizarre bug
			}
			$client['CLIENT_Z'] = $newArray;
		}
	}

	$client['CONTACTS'] = array();
	$sql = "SELECT CLIENT_CONTACT_ID, CC_TITLE, " . sql_decrypt('CC_FIRSTNAME', '', true) . ",
				" . sql_decrypt('CC_LASTNAME', '', true) . ", CC_MAIN, CC_INV, CC_REP, OBSOLETE,
				" . sql_decrypt('CC_EMAIL_1', '', true) . ", " . sql_decrypt('CC_EMAIL_2', '', true) . ", " . sql_decrypt('CC_POSITION', '', true) . "
			FROM CLIENT_CONTACT WHERE CLIENT2_ID=$client2_id
			ORDER BY OBSOLETE, CLIENT_CONTACT_ID DESC, CASE WHEN CC_MAIN=$sqlTrue THEN 0 ELSE 1 END";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client['CONTACTS'][$newArray['CLIENT_CONTACT_ID']] = $newArray;

	foreach ($client['CONTACTS'] as $ccid => $ccinfo)
	{
		$client['CONTACTS'][$ccid]['PHONES'] = array();
		$sql = "SELECT CLIENT_CONTACT_PHONE_ID, CP_MAIN, " . sql_decrypt('CP_PHONE', '', true) . ",
					" . sql_decrypt('CP_DESCR', '', true) . ", OBSOLETE
				FROM CLIENT_CONTACT_PHONE WHERE CLIENT_CONTACT_ID=$ccid
				ORDER BY CASE WHEN OBSOLETE=1 THEN 1 ELSE 0 END, CASE WHEN CP_MAIN=1 THEN 0 ELSE 1 END";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client['CONTACTS'][$ccid]['PHONES'][$newArray['CLIENT_CONTACT_PHONE_ID']] = $newArray;
		$ccinfo=$ccinfo; # keep code-checker quiet
	}

	$client['TARGETS'] = array();
	$sql = "SELECT TA.JOB_TARGET_ID, TA.JOB_TYPE_ID, TA.JTA_NAME, TA.JTA_TIME, TA.JTA_FEE,
					TY.JT_TYPE, NULL AS SELECTED
			FROM JOB_TARGET_SD AS TA LEFT JOIN JOB_TYPE_SD AS TY ON TY.JOB_TYPE_ID=TA.JOB_TYPE_ID
			WHERE TA.OBSOLETE=$sqlFalse ORDER BY TY.JOB_TYPE_ID, TA.JTA_TIME DESC";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client['TARGETS'][] = $newArray;

	$ii = 0;
	foreach ($client['TARGETS'] as $target)
	{
		$sql = "SELECT CT_FEE FROM CLIENT_TARGET_LINK
				WHERE CLIENT2_ID=$client2_id AND JOB_TARGET_ID={$target['JOB_TARGET_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$client['TARGETS'][$ii]['SELECTED'] = 1;
			$client['TARGETS'][$ii]['JTA_FEE'] = floatval($newArray[0]);
		}
		$ii++;
	}

	$client['SALESPERSONS'] = array();
	$sql = "SELECT S.SALESPERSON_ID, S.SP_USER_ID, S.SP_TXT, S.SP_DT,
				" . sql_decrypt('U.USERNAME', '', true) . "
			FROM SALESPERSON AS S LEFT JOIN USERV AS U ON U.USER_ID=S.SP_USER_ID
			WHERE S.CLIENT2_ID=$client2_id ORDER BY SALESPERSON_ID DESC";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client['SALESPERSONS'][$newArray['SALESPERSON_ID']] = $newArray;

	$client['GROUP_CLIENTS'] = array();
	if ($client['CLIENT_GROUP_ID'] > 0)
	{
		$sql = "SELECT CLIENT2_ID, C_CODE FROM CLIENT2 WHERE CLIENT_GROUP_ID={$client['CLIENT_GROUP_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			if ($newArray[0] != $client2_id)
				$client['GROUP_CLIENTS'][$newArray[0]] = $newArray[1];
		}
	}

	$client['LETTER_TYPES'] = array();
	$sql = "SELECT L.LETTER_TYPE_ID, L.LETTER_NAME, L.JT_T_JOB_TYPE_ID, K.CLIENT_LETTER_LINK_ID
			FROM LETTER_TYPE_SD AS L
			LEFT JOIN CLIENT_LETTER_LINK AS K ON K.LETTER_TYPE_ID=L.LETTER_TYPE_ID AND K.CLIENT2_ID=$client2_id
			ORDER BY LETTER_TYPE_ID";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($newArray['JT_T_JOB_TYPE_ID'] == '')
			$sys = 'Collect';
		else
			$sys = 'Trace';
		$client['LETTER_TYPES'][$newArray['LETTER_TYPE_ID']] =
			array('NAME' => "$sys: {$newArray['LETTER_NAME']}", 'SEL' => ($newArray['CLIENT_LETTER_LINK_ID'] ? 1 : 0),
					'SYSTEM' => ((0 < $newArray['JT_T_JOB_TYPE_ID']) ? 'T' : 'C'));
	}

	$client['LETTER_SEQ'] = array(-1 => false); # this first element says whether the sequence is client-specific
	for ($ls_default = 0; $ls_default <= 1; $ls_default++)
	{
		$sql = "SELECT LS.LETTER_SEQ_ID, LS.SEQ_NUM, LT.LETTER_NAME, LS.SEQ_DAYS
				FROM LETTER_SEQ AS LS
				INNER JOIN LETTER_TYPE_SD AS LT ON LT.LETTER_TYPE_ID=LS.LETTER_TYPE_ID
				WHERE LS.OBSOLETE=0 AND ";
		if ($ls_default == 0)
			$sql .= " LS.CLIENT2_ID=$client2_id ";
		else
			$sql .= " LS.CLIENT2_ID=0 ";
		$sql .= " ORDER BY LS.SEQ_NUM ";
		sql_execute($sql);
		$last_lsid = 0;
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$client['LETTER_SEQ'][$newArray['LETTER_SEQ_ID']] = array(
				'SEQ_NUM' => $newArray['SEQ_NUM'], 'LETTER_NAME' => $newArray['LETTER_NAME'], 'SEQ_DAYS' => $newArray['SEQ_DAYS']);
			$last_lsid = $newArray['LETTER_SEQ_ID'];
		}
		if (0 < $last_lsid)
			$client['LETTER_SEQ'][$last_lsid]['SEQ_DAYS'] = '';
		if (1 < count($client['LETTER_SEQ']))
		{
			if ($ls_default == 0)
				$client['LETTER_SEQ'][-1] = true;
			break;
		}
	}

	$client['CLIENT_REPORTS'] = array();
	$sql = "SELECT CLIENT_REPORT_ID, REPORT_NAME, REPORT_DT FROM CLIENT_REPORT WHERE CLIENT2_ID=$client2_id ORDER BY REPORT_DT DESC";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client['CLIENT_REPORTS'][] = $newArray;

	#dprint(print_r($client,1)); #
	return $client;
}

function sql_get_invoices_and_allocs($client2_id, $inv_recp_id)
{
	# Return a list of invoices for this client which are not fully paid,
	# but don't include invoices which already have an allocation from the specified receipt.

	global $sqlFalse;

	$disallowed_invoices = '';

	if (0 < $inv_recp_id)
	{
		$sql = "SELECT INVOICE_ID FROM INV_ALLOC WHERE INV_RECP_ID=$inv_recp_id";
		sql_execute($sql);
		$inv = array();
		while (($newArray = sql_fetch()) != false)
			$inv[] = $newArray[0];
		if ($inv)
			$disallowed_invoices = " AND (I.INVOICE_ID NOT IN (" . implode(',', $inv) . "))";
	}

	$sql = "SELECT I.INVOICE_ID, I.INV_NUM, I.INV_DT, I.INV_NET, I.INV_VAT, I.INV_PAID, SUM(COALESCE(A.AL_AMOUNT,0)) AS SUM_AL_AMOUNT
			FROM INVOICE AS I
			LEFT JOIN INV_ALLOC AS A ON A.INVOICE_ID=I.INVOICE_ID
			WHERE (I.OBSOLETE=$sqlFalse) AND (I.CLIENT2_ID=$client2_id) AND (I.INV_TYPE = 'I') $disallowed_invoices
			GROUP BY I.INVOICE_ID, I.INV_NUM, I.INV_DT, I.INV_NET, I.INV_VAT, I.INV_PAID
			ORDER BY I.INV_DT DESC, I.INVOICE_ID DESC";
	sql_execute($sql);
	#dprint($sql);#
	$invoices = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['GROSS'] = (floatval($newArray['INV_NET']) + floatval($newArray['INV_VAT']));
		$newArray['X_GROSS'] = round(floatval($newArray['GROSS']), 2);
		$newArray['X_PAID'] = round(floatval($newArray['INV_PAID']), 2);
		$newArray['OUTSTANDING'] = $newArray['X_GROSS'] - $newArray['X_PAID'];
		if (0 < $newArray['OUTSTANDING'])
			$invoices[] = $newArray;
	}
	return $invoices;

} # sql_get_invoices_and_allocs()

function sql_get_invoices($sc_sys, $sc_type, $sc_client, $sc_text, $sc_amt='', $sc_exact='', $sc_out='', $sc_late='', $sc_date_fr='', $sc_date_to='', $sc_cover_fr='', $sc_cover_to='', $sc_stmt='', $sc_appr='', $sc_emailed='', $sc_posted='', $limit=0)
{
	# Return list of invoices that match on search criteria,
	# but also that match the user's role.
	# $sc_sys is one of g/t/c.

	#global $role_agt;
	global $role_man;
	global $sqlFalse;
	global $sqlNow;
	global $sqlTrue;

	$manager_t = role_check('t', $role_man);
	$manager_c = role_check('c', $role_man);
	$manager_a = role_check('a', $role_man);
	$access_t = ($manager_t || $manager_a);
	$access_c = ($manager_c || $manager_a);

	$invoices = array();
	$count = -1;
	if ((!$access_c) && (!$access_t))
		return array($count, $invoices);

	$abort = false;
	$where = array();

	$obsolete_test = "I.OBSOLETE=$sqlFalse";

	$ms_top = '';
	$my_limit = '';
	if ($limit)
	{
		$limit = intval($limit);
		if (0 < $limit)
			list($ms_top, $my_limit) = sql_top_limit($limit);
	}

	if ($sc_sys == 'g')
		$where[] = "I.INV_SYS='G'";
	elseif ($sc_sys == 't')
	{
		if ($access_t)
			$where[] = "I.INV_SYS='T'";
	}
	elseif ($sc_sys == 'c')
	{
		if ($access_c)
			$where[] = "I.INV_SYS='C'";
	}
	elseif ($sc_sys == '')
	{
		if (!$access_t)
			$where[] = "I.INV_SYS<>'T'";
		if (!$access_c)
			$where[] = "I.INV_SYS<>'C'";
	}
	else
		$abort = true;

	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= I.INV_DT";
	if ($sc_date_to)
		$where[] = "I.INV_DT < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_cover_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_cover_fr) . "' <= I.INV_START_DT";
	if ($sc_cover_to)
		$where[] = "I.INV_END_DT < '" . date_for_sql_nqnt($sc_cover_to, true) . "'"; # NOT "<="

	if ($sc_type == 'i')
		$where[] = "I.INV_TYPE='I'";
	elseif ($sc_type == 'c')
		$where[] = "I.INV_TYPE='C'";
	elseif ($sc_type == 'ic')
		$where[] = "( (I.INV_TYPE='I') OR (I.INV_TYPE='C') )";
	elseif ($sc_type == 'f')
		$where[] = "I.INV_TYPE='F'";
	elseif ($sc_type != '') # error
		$abort = true;

	$co_net = "COALESCE(I.INV_NET,0.0)";
	$co_vat = "COALESCE(I.INV_VAT,0.0)";
	$co_gross = "($co_net + $co_vat)";
	$co_paid = "COALESCE(I.INV_PAID,0.0)";

	if ($sc_amt == 1)
		$where[] = "$co_net = 0.0";
	elseif ($sc_amt == -1)
		$where[] = "0.0 < $co_net";
	if ($sc_exact != '')
		$where[] = "$co_gross = $sc_exact";

	if ($sc_appr == 1)
		$where[] = "I.INV_APPROVED_DT IS NOT NULL";
	elseif ($sc_appr == -1)
		$where[] = "I.INV_APPROVED_DT IS NULL";

	if ($sc_emailed == 1)
		$where[] = "I.IMPORTED=$sqlFalse AND I.INV_EMAIL_ID IS NOT NULL";
	elseif ($sc_emailed == -1)
		$where[] = "I.IMPORTED=$sqlFalse AND I.INV_EMAIL_ID IS NULL";

	if ($sc_posted == 1)
		$where[] = "I.IMPORTED=$sqlFalse AND I.INV_POSTED_DT IS NOT NULL";
	elseif ($sc_posted == -1)
		$where[] = "I.IMPORTED=$sqlFalse AND I.INV_POSTED_DT IS NULL";

	if ($sc_out == 1)
		$where[] = "($co_gross <= $co_paid)";
	elseif ($sc_out == -1)
		$where[] = "($co_paid < $co_gross)";

	if ($sc_stmt == 1)
		$where[] = "I.INV_STMT=$sqlTrue";
	elseif ($sc_stmt == -1)
		$where[] = "I.INV_STMT=$sqlFalse";

	if ($sc_late != '')
	{
		$where[] = "($co_paid < $co_gross)";
		$date_add = sql_date_add('I.INV_DT', 'DAY', 30);
		$where[] = "$sc_late <= " . sql_date_diff("COALESCE(I.INV_DUE_DT, $date_add)", $sqlNow);
	}

	if ($sc_client)
	{
		if (is_numeric_kdb($sc_client, false, false, false))
		{
			$c_code = intval($sc_client);
			$where[] = "C.C_CODE=$c_code";
		}
		else
		{
			$sc_client_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client_id > 0)
				$where[] = "C.CLIENT2_ID=$sc_client_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE (" . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%') OR " .
						"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "') AND C_ARCHIVED=$sqlFalse";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
				{
					dprint("WARNING: Client Filter \"$sc_client\" yielded no client_id(s) - aborting search", true);
					$abort = true;
				}
			}
		}
	}

	if (!$abort)
	{
		# Check user's role for which invoices they can see.
		$inc_g = true; # include General invoices
		$inc_t = $access_t; # include Trace invoices
		$inc_c = $access_c; # include Collect invoices
		if ($inc_g)
		{
			if ($inc_t)
			{
				if ($inc_c)
					{ } # No need to add criteria to SQL
				else
					$where[] = "( (I.INV_SYS='G') OR (I.INV_SYS='T') )";
			}
			else
			{
				if ($inc_c)
					$where[] = "( (I.INV_SYS='G') OR (I.INV_SYS='C') )";
				else
					$where[] = "I.INV_SYS='G'";
			}
		}
		else
		{
			if ($inc_t)
			{
				if ($inc_c)
					$where[] = "( (I.INV_SYS='T') OR (I.INV_SYS='C') )";
				else
					$where[] = "I.INV_SYS='T'";
			}
			else
			{
				if ($inc_c)
					$where[] = "I.INV_SYS='C'";
				else
					$abort = true;
			}
		}
	}

	if ((!$abort) && $sc_text)
	{
		$sc_inv_num = intval($sc_text);
		if ($sc_inv_num > 0)
			$where[] = "I.INV_NUM = $sc_inv_num";
		else
		{
			$sc_invoice_id = (($sc_text[0] == '*') ? intval(trim(str_replace('*', '', $sc_text))) : 0);
			if ($sc_invoice_id > 0)
			{
				$where[] = "I.INVOICE_ID = $sc_invoice_id";
				$obsolete_test = '';
			}
			else
				$abort = true;
		}
	}

	if ($obsolete_test)
		$where[] = $obsolete_test;

	if (!$abort)
	{
		$fields = "I.INVOICE_ID, I.INV_SYS, I.INV_TYPE, I.INV_NUM, I.INV_DT, I.INV_NET, I.INV_VAT, I.INV_DUE_DT,
					I.INV_DT, I.INV_PAID, I.IMPORTED, I.INV_SYS_IMP, I.INV_STMT, I.LINKED_ID, NULL AS LINKED_NUM,
					E.EM_DT, I.INV_POSTED_DT,
					C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) .
					(($sc_emailed == -1) ? (", " . sql_decrypt('C.INV_EMAIL_ADDR', '', true)) : '') . ", C.INV_EMAILED
					";

		$tables = "
				FROM INVOICE AS I
				LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
				LEFT JOIN EMAIL AS E ON E.EMAIL_ID=I.INV_EMAIL_ID
				";
				#LEFT JOIN EMAIL AS E ON I.INV_EMAIL_ID=E.EMAIL_ID
		$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');
		$order = "ORDER BY I.INV_DT DESC, I.INV_NUM DESC";

		$sql = "SELECT COUNT(*) $tables $where";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$count = $newArray[0];

		sql_encryption_preparation('CLIENT2');
		$sql = "SELECT $ms_top $fields $tables $where $order $my_limit";
		#dprint("sql_get_invoices(\"$sc_sys\", \"$sc_type\", \"$sc_client\", \"$sc_text\", \"$limit\"): $sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$invoices[] = $newArray;

		for ($iix = 0; $iix < count($invoices); $iix++)
		{
			if (0 < $invoices[$iix]['LINKED_ID'])
			{
				$sql = "SELECT INV_NUM FROM INVOICE WHERE INVOICE_ID={$invoices[$iix]['LINKED_ID']}";
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$invoices[$iix]['LINKED_NUM'] = $newArray[0];
			}
		}
	}

	#dprint(print_r($invoices,1)); #
	return array($count, $invoices);
} # sql_get_invoices()

function sql_get_receipts($sc_type, $sc_client, $sc_text, $sc_amt, $sc_exact, $sc_date_fr, $sc_date_to, $limit=0)
{
	# Return list of receipts that match on search criteria,
	# but also that match the user's role.

	global $role_man;
	global $sqlFalse;
	global $sqlTrue;

	$manager_t = role_check('t', $role_man);
	$manager_c = role_check('c', $role_man);
	$manager_a = role_check('a', $role_man);
	$access_t = ($manager_t || $manager_a);
	$access_c = ($manager_c || $manager_a);

	$receipts = array();
	$count = -1;
	if ((!$access_c) && (!$access_t))
		return array($count, $receipts);

	$abort = false;
	$where = array("R.OBSOLETE=$sqlFalse");

	$ms_top = '';
	$my_limit = '';
	if ($limit)
	{
		$limit = intval($limit);
		if (0 < $limit)
			list($ms_top, $my_limit) = sql_top_limit($limit);
	}

	if ($sc_type == 'r')
		$where[] = "R.RC_ADJUST=$sqlFalse";
	elseif ($sc_type == 'a')
		$where[] = "R.RC_ADJUST=$sqlTrue";
	elseif ($sc_type == 'ra')
		$where[] = "( (R.RC_ADJUST=$sqlFalse) OR (R.RC_ADJUST=$sqlTrue) )";
	elseif ($sc_type != '') # error
		$abort = true;

	if (!$abort)
	{
		if ($sc_amt == 1)
			$where[] = "R.RC_AMOUNT = 0.0";
		elseif ($sc_amt == -1)
			$where[] = "0.0 < R.RC_AMOUNT";
		if ($sc_exact != '')
			$where[] = "R.RC_AMOUNT = $sc_exact";

		if ($sc_date_fr)
			$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= R.RC_DT";
		if ($sc_date_to)
			$where[] = "R.RC_DT < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	}

	if ((!$abort) && $sc_client)
	{
		if (is_numeric_kdb($sc_client, false, false, false))
		{
			$c_code = intval($sc_client);
			$where[] = "C.C_CODE=$c_code";
		}
		else
		{
			$sc_client_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client_id > 0)
				$where[] = "C.CLIENT2_ID=$sc_client_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE (" . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%') OR " .
						"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "') AND C_ARCHIVED=$sqlFalse";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
				{
					dprint("WARNING: Client Filter \"$sc_client\" yielded no client_id(s) - aborting search", true);
					$abort = true;
				}
			}
		}
	}

	if (!$abort)
	{
		# Check user's role for whether they can see receipts
		$inc_t = $access_t; # can see Trace invoices
		$inc_c = $access_c; # can see Collect invoices
		if ( ! ($inc_t || $inc_c) )
			$abort = true;
	}

	if ((!$abort) && $sc_text)
	{
		$sc_inv_num = intval($sc_text);
		if ($sc_inv_num > 0)
			$where[] = "R.RC_NUM = $sc_inv_num";
		else
		{
			$sc_invoice_id = (($sc_text[0] == '*') ? intval(trim(str_replace('*', '', $sc_text))) : 0);
			if ($sc_invoice_id > 0)
				$where[] = "R.INV_RECP_ID = $sc_invoice_id";
			else
				$abort = true;
		}
	}

	if (!$abort)
	{
		$fields = "R.INV_RECP_ID, R.RC_NUM, R.RC_DT, R.RC_AMOUNT, R.RC_ADJUST, R.IMPORTED, R.RC_SYS_IMP,
					C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . "
					";
		$tables = "
				FROM INV_RECP AS R
				LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=R.CLIENT2_ID
				";
		$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');
		$order = "ORDER BY R.RC_DT DESC, R.RC_NUM DESC
				";

		$sql = "SELECT COUNT(*) $tables $where";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$count = $newArray[0];

		sql_encryption_preparation('CLIENT2');
		$sql = "SELECT $ms_top $fields $tables $where $order $my_limit";
		dprint("sql_get_receipts(\"$sc_type\", \"$sc_client\", \"$sc_text\", \"$limit\"): $sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$receipts[] = $newArray;
	}

	#dprint(print_r($receipts,1)); #
	return array($count, $receipts);

} # sql_get_receipts()

function sql_get_one_invoice($invoice_id)
{
	global $sqlFalse;
	global $sqlTrue;

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('INVOICE');
	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('EMAIL');

	$invoice_id = intval($invoice_id);
	$sql = "SELECT I.INVOICE_ID, I.INV_SYS, I.INV_NUM, I.INV_TYPE, I.INV_DT, I.INV_DUE_DT, I.INV_NET, I.INV_VAT, I.INV_APPROVED_DT,
				I.IMPORTED, I.INV_SYS_IMP, I.INV_PAID, " . sql_decrypt('I.INV_NOTES', '', true) . ",
				I.INV_STMT, I.INV_S_INVS, I.INV_START_DT, I.INV_END_DT, I.INV_COMPLETE, I.INV_EMAIL_ID, I.INV_POSTED_DT,
				C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
				E.EM_DT, " . sql_decrypt('E.EM_TO', '', true) . ", " . sql_decrypt('E.EM_SUBJECT', '', true) . ",
				I.Z_EOM, I.OBSOLETE, C.C_ARCHIVED, I.LINKED_ID, '' AS LINKED_INV_NUM
			FROM INVOICE AS I
			LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
			LEFT JOIN EMAIL AS E ON E.EMAIL_ID=I.INV_EMAIL_ID AND E.OBSOLETE=$sqlFalse
			WHERE I.INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$invoice = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['INV_PAID'] = floatval($newArray['INV_PAID']); # avoid ".00"
		$amount = floatval($newArray['INV_NET']) + floatval($newArray['INV_VAT']);
		$invoice = $newArray;
	}

	if (0 < $invoice['LINKED_ID'])
	{
		$sql = "SELECT INV_NUM FROM INVOICE WHERE INVOICE_ID={$invoice['LINKED_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$invoice['LINKED_INV_NUM'] = $newArray[0];
	}
	$invoice['BILLING'] = array();
	$invoice['BILLING_COST_TOTAL'] = 0.0;
	if (($invoice['INV_SYS'] == 'G') || ($invoice['INV_SYS'] == 'T'))
	{
		$sql = 	"SELECT B.INV_BILLING_ID, B.JOB_ID, J.J_VILNO, B.BL_SYS_IMP, B.BL_DESCR, B.BL_COST, B.BL_LPOS, B.BL_LETTER_DT,
						B.IMPORTED, B.Z_CLID, B.Z_DOCTYPE, B.Z_S_INVS, B.Z_NSECS,
						" . sql_decrypt('S.JS_FIRSTNAME', '', true) . ", " . sql_decrypt('S.JS_LASTNAME', '', true) . ",
						" . sql_decrypt('S.JS_COMPANY', '', true) . "
				FROM INV_BILLING AS B
				LEFT JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
				LEFT JOIN JOB_SUBJECT AS S ON S.JOB_ID=J.JOB_ID AND S.JS_PRIMARY=$sqlTrue
				WHERE B.INVOICE_ID=$invoice_id AND B.OBSOLETE=$sqlFalse
				ORDER BY B.BL_LPOS, B.INV_BILLING_ID
				";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$invoice['BILLING'][] = $newArray;
			$invoice['BILLING_COST_TOTAL'] += floatval($newArray['BL_COST']);
		}
	}
	#dprint("sql_get_one_invoice(): " . count($invoice['BILLING']) . " billing lines");#

	$invoice['ALLOC'] = array();
	$sql = "SELECT A.INV_ALLOC_ID, A.AL_AMOUNT, A.IMPORTED, A.AL_SYS_IMP, A.INV_RECP_ID, A.Z_DOCTYPE, A.Z_DOCAMOUNT,
				R.RC_ADJUST, R.RC_NUM, R.RC_AMOUNT, R.RC_DT
			FROM INV_ALLOC AS A LEFT JOIN INV_RECP AS R ON R.INV_RECP_ID=A.INV_RECP_ID
			WHERE (A.INVOICE_ID=$invoice_id) AND (R.OBSOLETE=$sqlFalse)
			ORDER BY A.INVOICE_ID
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amount -= floatval($newArray['AL_AMOUNT']);
		$invoice['ALLOC'][] = $newArray;
	}
	$invoice['ALLOC_REM'] = round($amount, 2);

	$invoice['PAYMENTS'] = array();
	$sql = "SELECT P.JOB_PAYMENT_ID, P.COL_AMT_RX, P.COL_DT_RX, P.JOB_ID, P.IMPORTED, P.ADJUSTMENT_ID, J.J_VILNO,
						" . sql_decrypt('S.JS_FIRSTNAME', '', true) . ", " . sql_decrypt('S.JS_LASTNAME', '', true) . ",
						" . sql_decrypt('S.JS_COMPANY', '', true) . ",
						P.COL_PERCENT, RT.PAYMENT_ROUTE_ID, RT.PAYMENT_ROUTE, " . sql_decrypt('J.CLIENT_REF', '', true) . "
			FROM JOB_PAYMENT AS P
			LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
			LEFT JOIN JOB_SUBJECT AS S ON S.JOB_ID=J.JOB_ID AND S.JS_PRIMARY=$sqlTrue
			LEFT JOIN PAYMENT_ROUTE_SD AS RT ON RT.PAYMENT_ROUTE_ID=P.COL_PAYMENT_ROUTE_ID
			WHERE P.INVOICE_ID=$invoice_id AND P.OBSOLETE=$sqlFalse
			ORDER BY P.COL_DT_RX
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$invoice['PAYMENTS'][] = $newArray;

	$email_fields = "E.EMAIL_ID, E.EM_DT, " . sql_decrypt('E.EM_TO', '', true) . ", " . sql_decrypt('E.EM_SUBJECT', '', true) . ", E.EM_ATTACH";
	$invoice['EMAIL_HISTORY'] = array();
	$sql = "SELECT $email_fields, 'I' AS SOURCE
			FROM EMAIL AS E
			WHERE E.INVOICE_ID={$invoice_id}
			ORDER BY E.EMAIL_ID
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$invoice['EMAIL_HISTORY'][$newArray['EMAIL_ID']] = $newArray;
	$sql = "SELECT $email_fields, 'B' AS SOURCE
			FROM EMAIL AS E
			INNER JOIN INV_BILLING AS B ON B.JOB_ID=E.JOB_ID AND B.INVOICE_ID={$invoice_id}
			ORDER BY E.EMAIL_ID
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$email_id = $newArray['EMAIL_ID'];
		if (!array_key_exists($email_id, $invoice['EMAIL_HISTORY']))
			$invoice['EMAIL_HISTORY'][$email_id] = $newArray;
	}

	#dprint(print_r($invoice,1)); #
	return $invoice;
} # sql_get_one_invoice()

function sql_get_one_receipt($inv_recp_id)
{
	global $sqlFalse;

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('INV_RECP');

	$amount = 0.0;
	$inv_recp_id = intval($inv_recp_id);
	$sql = "SELECT R.INV_RECP_ID, R.RC_NUM, R.RC_DT, R.RC_AMOUNT, R.RC_ADJUST,
				R.IMPORTED, R.RC_SYS_IMP, R.Z_COMPLETE, " . sql_decrypt('R.RC_NOTES', '', true) . ",
				C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ", C.C_ARCHIVED
			FROM INV_RECP AS R
			LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=R.CLIENT2_ID
			WHERE R.INV_RECP_ID=$inv_recp_id";
	sql_execute($sql);
	$receipt = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amount += floatval($newArray['RC_AMOUNT']);
		$receipt = $newArray;
	}

	$receipt['ALLOC'] = array();
	$sql = "SELECT A.INV_ALLOC_ID, A.AL_AMOUNT, A.IMPORTED, A.AL_SYS_IMP, A.INVOICE_ID, A.Z_DOCTYPE, A.Z_DOCAMOUNT,
				I.INV_TYPE, I.INV_NUM, I.INV_NET, I.INV_VAT, I.INV_DT
			FROM INV_ALLOC AS A LEFT JOIN INVOICE AS I ON I.INVOICE_ID=A.INVOICE_ID
			WHERE (I.OBSOLETE=$sqlFalse) AND (A.INV_RECP_ID=$inv_recp_id)
			ORDER BY A.INV_ALLOC_ID
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amount -= floatval($newArray['AL_AMOUNT']);
		$receipt['ALLOC'][] = $newArray;
	}
	$receipt['ALLOC_REM'] = round($amount, 2);

	#dprint(print_r($receipt,1)); #
	return $receipt;
}

function invoice_num_from_id($invoice_id)
{
	$inv_num = sql_select_single("SELECT INV_NUM FROM INVOICE WHERE INVOICE_ID=$invoice_id");
	return $inv_num;

} # invoice_num_from_id()

function invoice_id_from_docno($docno, $system, $doctype, $sys_imp='')
{
	# $docno is Document Number e.g. invoice number.
	# $system is G for General, T for Trace or C for Collect.
	# $doctype is I for invoice, C for credit or F for FOC.
	# $sys_imp is the system that the invoice was imported from (T for TRACES or C for TCOLLECT), or blank for non-imported invoice.

	# If exactly one matching invoice is found, return its INVOICE_ID.
	# If no matches are found, return zero.
	# If more than one match is found, return that number multiplied by -1.

	global $invoice_error;
	global $sqlFalse;

	$invoice_error = '';

	$inv_num = intval($docno);
	if ($inv_num == 0)
	{
		$invoice_error = "Invalid docno \"$docno\"";
		return 0;
	}

	if ( ! (($system == 'G') || ($system == 'T') || ($system == 'C')) )
	{
		$invoice_error = "Invalid system \"$system\"";
		return 0;
	}

	if ( ! (($doctype == 'I') || ($doctype == 'C') || ($doctype == 'F')) )
	{
		$invoice_error = "Invalid doctype \"$doctype\"";
		return 0;
	}

	if ( ! (($sys_imp == '') || ($sys_imp == 'T') || ($sys_imp == 'C')) )
	{
		$invoice_error = "Invalid sys_imp \"$sys_imp\"";
		return 0;
	}

	$sql = "SELECT INVOICE_ID FROM INVOICE WHERE (OBSOLETE=$sqlFalse) AND (INV_NUM=$inv_num) AND (INV_SYS='$system') AND
				" . ($doctype ? " (INV_TYPE='$doctype') AND " : '') . "
				(INV_SYS_IMP" . ($sys_imp ? "='$sys_imp'" : " IS NULL") . ")";
	sql_execute($sql);
	$invoices = array();
	while (($newArray = sql_fetch()) != false)
		$invoices[] = $newArray[0];
	if (count($invoices) == 0)
	{
		$invoice_error = "No invoices found from<br>$sql";
		return 0;
	}
	if (count($invoices) > 1)
	{
		$invoice_error = "More than 1 invoice found: " . print_r($invoices,1);
		return 0;
	}
	return $invoices[0];
}

function receipt_id_from_docno($docno, $doctype, $sys_imp='')#, $debug=false)
{
	# $docno is Document Number e.g. receipt number.
	# $doctype is R for receipt or A for allocation.
	# $sys_imp is the system that the receipt was imported from (T for TRACES or C for TCOLLECT), or blank for non-imported receipt.

	# If exactly one matching receipt is found, return its INV_RECP_ID.
	# If no matches are found, return zero.
	# If more than one match is found, return that number multiplied by -1.

	global $invoice_error;
	global $sqlFalse;
	global $sqlTrue;

	$invoice_error = '';

	$inv_num = intval($docno);
	if ($inv_num == 0)
	{
		$invoice_error = "Invalid docno \"$docno\"";
		return 0;
	}

	if ($doctype == 'R')
	{
		$adjust = $sqlFalse;
		$doctxt = 'receipt';
	}
	elseif ($doctype == 'A')
	{
		$adjust = $sqlTrue;
		$doctxt = 'adjustment';
	}
	else
	{
		$invoice_error = "Invalid doctype \"$doctype\"";
		return 0;
	}

	if ( ! (($sys_imp == '') || ($sys_imp == 'T') || ($sys_imp == 'C')) )
	{
		$invoice_error = "Invalid sys_imp \"$sys_imp\"";
		return 0;
	}

	$sql = "SELECT INV_RECP_ID FROM INV_RECP WHERE (RC_NUM=$inv_num) AND (RC_ADJUST=$adjust) AND (OBSOLETE=$sqlFalse) AND
				(RC_SYS_IMP" . ($sys_imp ? "='$sys_imp'" : " IS NULL") . ")";
	#if ($debug) dprint($sql);#
	sql_execute($sql);
	$receipts = array();
	while (($newArray = sql_fetch()) != false)
		$receipts[] = $newArray[0];
	if (count($receipts) == 0)
	{
		$invoice_error = "No {$doctxt}s found";
		return 0;
	}
	if (count($receipts) > 1)
	{
		$invoice_error = "More than 1 $doctxt found: " . print_r($receipts,1);
		return 0;
	}
	return $receipts[0];
}

function job_type_id_from_code($code)
{
	$jtid = 0;
	$sql = "SELECT JOB_TYPE_ID FROM JOB_TYPE_SD WHERE JT_CODE='$code'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$jtid = $newArray[0];
	return $jtid;
}

function job_id_from_sequence($sequence)
{
	$jid = 0;
	$sequence = intval($sequence);
	$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE=$sequence";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$jid = $newArray[0];
	return $jid;
}

function job_type_id_from_old_description($descr)
{
	$descr = strtolower(trim(str_replace('  ',' ',str_replace('.','',$descr))));
	if ($descr == 'trace')
		$code = 'TRC';
	elseif ($descr == 'means')
		$code = 'MNS';
	elseif ($descr == 'repo')
		$code = 'RPO';
	elseif ($descr == 'other')
		$code = 'OTH';
	elseif ($descr == 'service')
		$code = 'SVC';
	elseif ($descr == 'retrace 1')
		$code = 'RT1';
	elseif ($descr == 'retrace 2')
		$code = 'RT2';
	elseif ($descr == 'retrace 3')
		$code = 'RT3';
	elseif ($descr == 't/c')
		$code = 'T/C';
	elseif ($descr == 't/m')
		$code = 'T/M';
	elseif ($descr == 'attend')
		$code = 'ATT';
	else
		$code = '*ERROR*';

	$jtid = job_type_id_from_code($code);
	return $jtid;
}

function job_status_id_from_text($j_status)
{
	$jsid = 0;
	$sql = "SELECT JOB_STATUS_ID FROM JOB_STATUS_SD WHERE (JS_STATUS='$j_status')";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$jsid = $newArray[0];
	return $jsid;
}

function sql_get_jobs($sc_sys='', $sc_text='', $sc_addr='', $sc_date_fr='', $sc_date_to='', $sc_client='', $sc_clref='', $sc_group='', $sc_agent='', $sc_inst=false, $limit=0, $sc_closed_fr='', $sc_closed_to='', $sc_jobtype='', $sc_jobstatus='', $sc_jopenclosed='', $sc_success='', $sc_credit='', $sc_complete='', $sc_inv='', $sc_bill='', $sc_act_fr='', $sc_act_to='', $sc_target_fr='', $sc_target_to='', $sc_obsolete='', $sc_letters='', $sc_ltype='', $sc_pmt_fr='', $sc_pmt_to='', $sc_upd_fr='', $sc_upd_to='', $sc_diary='', $sc_archived='', $sc_llist='')#$sc_pending
{
	# Note that $sc_act_fr/$sc_act_to and $sc_pending cannot be used together, which might be a problem as they are both for Collection jobs.
	#
	# Return list of jobs that match on search criteria text,
	# but also that match the user's role.
	# $sc_sys: 't', 'c' or '' or '*' ('' and '*' are equivalent)
	# $sc_text: VILNo, SeqNo, *JOB_ID or subject/company name
	# $sc_addr: subject's address (current and new address)
	# $sc_date_fr and $sc_date_to: placement dates: "date from" and "date to"
	# $sc_closed_fr and $sc_closed_to: job closure dates: "date from" and "date to"
	# $sc_client: Client code, client name or *CLIENT2_ID
	# $sc_clref: Client reference (job reference number)
	# $sc_group: ID of Client Group
	# $sc_agent: Agent name or *USER_ID
	# $sc_jobtype: Job Type (Trace jobs only)
	# $sc_inst: true/false; if true then find jobs that have instalments set up (JC_INSTAL_AMT > 0)
	# $sc_jopen: true/false; if true then find jobs that are marked as open
	# $limit: restriction to number of jobs returned

	global $id_ROUTE_cspent;
	global $role_agt;
	global $role_man;
	global $sqlFalse;
	global $sqlTrue;
	global $time_tests;
	global $ynfrxns_list;
	global $ynfoc_list;
	global $ynpend_list;
	global $USER;

	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Enter.");

	$this_function = "sql_get_jobs(\$sc_sys=$sc_sys, \$sc_text=$sc_text, \$sc_addr=$sc_addr, \$sc_date_fr=$sc_date_fr, \$sc_date_to=$sc_date_to, \$sc_client=$sc_client, \$sc_clref=$sc_clref, \$sc_group=$sc_group, \$sc_agent=$sc_agent, \$sc_inst=$sc_inst, \$limit=$limit, \$sc_closed_fr=$sc_closed_fr, \$sc_closed_to=$sc_closed_to, \$sc_jobtype=$sc_jobtype, \$sc_jobstatus=$sc_jobstatus, \$sc_jopenclosed=$sc_jopenclosed, \$sc_success=$sc_success, \$sc_credit=$sc_credit, \$sc_complete=$sc_complete, \$sc_inv=$sc_inv, \$sc_bill=$sc_bill, \$sc_act_fr=$sc_act_fr, \$sc_act_to=$sc_act_to, \$sc_target_fr=$sc_target_fr, \$sc_target_to=$sc_target_to, \$sc_obsolete=$sc_obsolete, \$sc_letters=$sc_letters, \$sc_ltype=$sc_ltype, \$sc_pmt_fr=$sc_pmt_fr, \$sc_pmt_to=$sc_pmt_to, \$sc_upd_fr=$sc_upd_fr, \$sc_upd_to=$sc_upd_to, \$sc_diary=$sc_diary, \$sc_archived=$sc_archived, \$sc_llist=$sc_llist)";
	$this_function=$this_function; #keep code-checker quiet
	#dprint($this_function);#
	#dprint("sc_complete=$sc_complete");#
	#dprint("\$sc_addr=\"" . str_replace(' ', '^', $sc_addr) . "\"");#

	$debug = false;
	#if ($USER['USER_ID'] == 16) # Matt
	#	$debug = true;
	if ($debug)
		log_write("User {$USER['USER_ID']}: $this_function");

	$feedback_23 = global_debug();#REVIEW: very old code waiting to go live

	$jobs = array();
	$count = -1;
	$abort = false;
	$where = array();
	$act_used = false;
	$ltr_used = false;

	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Checking sc_phone...");
	$sc_phone = str_replace(' ', '', $sc_addr);
	if ($sc_phone)
	{
		if (is_numeric_kdb($sc_phone, false, false, false))
		{
			$sc_phone_2 = (($sc_phone[0] == '0') ? substr($sc_phone, 1) : '');
			$jpp = "REPLACE(" . sql_decrypt('JP_PHONE') . ",' ','')";
			$sql = "SELECT JOB_ID FROM JOB_PHONE WHERE (($jpp='$sc_phone')";
			if ($sc_phone_2)
				$sql .= " OR ($jpp='$sc_phone_2')";
			$sql .= ") AND (OBSOLETE=$sqlFalse)";
			#dprint($sql);#
			sql_execute($sql);
			$phone_jobs = array();
			while (($newArray = sql_fetch()) != false)
				$phone_jobs[] = $newArray[0];
			if ($phone_jobs)
				$sc_phone = implode(',', $phone_jobs);
			else
				$abort = true;
		}
		else
			$sc_phone = '';
	}
	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): ...done sc_phone");

	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Checking sc_postcode...");
	$sc_postcode = '';
	$sc_outcode = '';
	if ($sc_addr && (!$sc_phone))
	{
		$sc_postcode = str_replace(' ', '', $sc_addr);
		if (is_postcode($sc_postcode))
		{
			$sc_outcode = postcode_outcode($sc_postcode);
			$sc_addr = '';
		}
		else
			$sc_postcode = '';
	}
	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): ...done sc_postcode");

	$is_trace = "J.JT_JOB=$sqlTrue";
	$is_collect = "J.JC_JOB=$sqlTrue";
	$obsolete_test = "J.OBSOLETE=$sqlFalse";

	$ms_top = '';
	$my_limit = '';
	if ($limit)
	{
		$limit = intval($limit);
		if (0 < $limit)
			list($ms_top, $my_limit) = sql_top_limit($limit);
	}

	if ($sc_sys == 't')
		$where[] = $is_trace;
	elseif ($sc_sys == 'c')
		$where[] = $is_collect;
	elseif (($sc_sys != '') && ($sc_sys != '*')) # error
		$where[] = "1=0";

	if ($sc_jobtype)
	{
		if (!in_array($is_trace, $where))
			$where[] = $is_trace;
		if (0 < $sc_jobtype)
			$where[] = "J.JT_JOB_TYPE_ID=$sc_jobtype";
		else
			$where[] = "J.JT_JOB_TYPE_ID IS NULL";
	}
	if ($sc_jobstatus)
	{
		if (!in_array($is_collect, $where))
			$where[] = $is_collect;
		if (0 < $sc_jobstatus)
			$where[] = "J.JC_JOB_STATUS_ID=$sc_jobstatus";
		else
			$where[] = "J.JC_JOB_STATUS_ID IS NULL";
	}

	if (($sc_success != '') && array_key_exists($sc_success, $ynfrxns_list))
	{
		if (!in_array($is_trace, $where))
			$where[] = $is_trace;
		if ($sc_success == 'NULL')
			$where[] = "J.JT_SUCCESS IS NULL";
		elseif ($sc_success == -2)
			$where[] = "J.JT_SUCCESS IS NOT NULL";
		else
			$where[] = "J.JT_SUCCESS=$sc_success";
	}
	if (($sc_credit != '') && array_key_exists($sc_credit, $ynfoc_list))
	{
		if (!in_array($is_trace, $where))
			$where[] = $is_trace;
		$where[] = "J.JT_CREDIT=$sc_credit";
	}

	if ($sc_complete != '')
	{
		if (array_key_exists($sc_complete, $ynpend_list))
		{
			if ($sc_complete == 0)
				$where[] = "( (J.JT_JOB=$sqlTrue AND (J.J_COMPLETE=0 OR J.J_COMPLETE IS NULL)) OR (J.JC_JOB=$sqlTrue) )";
			elseif ($sc_complete == -9) # No/Review
				$where[] = "( (J.JT_JOB=$sqlTrue AND (J.J_COMPLETE=0 OR J.J_COMPLETE=-1 OR J.J_COMPLETE IS NULL)) OR (J.JC_JOB=$sqlTrue) )";
			else
				$where[] = "( (J.JT_JOB=$sqlTrue AND J.J_COMPLETE=$sc_complete) OR (J.JC_JOB=$sqlTrue) )";
		}
	}

	if ($sc_inst)
		$where[] = "J.JC_INSTAL_AMT > 0";

	if ($sc_jopenclosed == 1)
		$where[] = "J.JOB_CLOSED=0";
	elseif ($sc_jopenclosed == 2)
		$where[] = "J.JOB_CLOSED=1";

	if ($sc_inv == 1) # "With invoice(s)"
	{
		$where[] = "(J.JOB_ID IN
						(SELECT DISTINCT INV_JP.JOB_ID FROM JOB_PAYMENT AS INV_JP WHERE INV_JP.JOB_ID IS NOT NULL AND 0 < INV_JP.INVOICE_ID AND INV_JP.OBSOLETE=$sqlFalse))
					OR
					(J.JOB_ID IN
						(SELECT DISTINCT INV_BI.JOB_ID FROM INV_BILLING AS INV_BI WHERE INV_BI.JOB_ID IS NOT NULL AND 0 < INV_BI.INVOICE_ID AND INV_BI.OBSOLETE=$sqlFalse))
					";
	}
	elseif ($sc_inv == -1) # "Without invoice(s)"
	{
		$where[] = "(J.JOB_ID NOT IN
						(SELECT DISTINCT INV_JP.JOB_ID FROM JOB_PAYMENT AS INV_JP WHERE INV_JP.JOB_ID IS NOT NULL AND 0 < INV_JP.INVOICE_ID AND INV_JP.OBSOLETE=$sqlFalse))
					AND
					(J.JOB_ID NOT IN
						(SELECT DISTINCT INV_BI.JOB_ID FROM INV_BILLING AS INV_BI WHERE INV_BI.JOB_ID IS NOT NULL AND 0 < INV_BI.INVOICE_ID AND INV_BI.OBSOLETE=$sqlFalse))
					";
	}
	elseif ($sc_inv == -2) # "Uninvoiced Payments"
	{
		# We only consider payments made in the last two years on jobs which are still open.
		$dt = date_n_years_ago(2);
		$where[] = "(J.JOB_CLOSED=$sqlFalse)";
		$where[] = "(J.JOB_ID IN
						(SELECT DISTINCT INV_JP.JOB_ID FROM JOB_PAYMENT AS INV_JP WHERE INV_JP.JOB_ID IS NOT NULL AND INV_JP.INVOICE_ID IS NULL
							AND INV_JP.COL_PAYMENT_ROUTE_ID <> $id_ROUTE_cspent
							AND INV_JP.COL_DT_RX IS NOT NULL AND '$dt' <= INV_JP.COL_DT_RX AND INV_JP.OBSOLETE=$sqlFalse))
					";
	}

	if ($sc_bill == 1) # "With billing [lines]"
	{
		$where[] = "(J.JOB_ID IN
						(SELECT DISTINCT BILL.JOB_ID FROM INV_BILLING AS BILL WHERE BILL.JOB_ID IS NOT NULL AND BILL.OBSOLETE=$sqlFalse))
					";
	}
	elseif ($sc_bill == -1) # "Without billing [lines]"
	{
		$where[] = "(J.JOB_ID NOT IN
						(SELECT DISTINCT BILL.JOB_ID FROM INV_BILLING AS BILL WHERE BILL.JOB_ID IS NOT NULL AND BILL.OBSOLETE=$sqlFalse))
					";
	}

	if ($sc_pmt_fr || $sc_pmt_to)
	{
		$where_pmt = array();
		if ($sc_pmt_fr)
			$where_pmt[] = "'" . date_for_sql_nqnt($sc_pmt_fr) . "' <= INV_JP.COL_DT_RX";
		if ($sc_pmt_to)
			$where_pmt[] = "INV_JP.COL_DT_RX < '" . date_for_sql_nqnt($sc_pmt_to, true) . "'"; # NOT "<="
		$where_pmt = "(" . implode(') AND (', $where_pmt) . ")";
		$where[] = "(J.JOB_ID IN
						(SELECT DISTINCT INV_JP.JOB_ID FROM JOB_PAYMENT AS INV_JP WHERE INV_JP.JOB_ID IS NOT NULL AND INV_JP.OBSOLETE=$sqlFalse
							AND INV_JP.COL_DT_RX IS NOT NULL AND $where_pmt))
					";
	}

	# Check user's role for which jobs they can see.
	$agent_t = role_check('t', $role_agt);
	$agent_c = role_check('c', $role_agt);
	$manager_a = role_check('a', $role_man);
	$inc_t = (($agent_t || $manager_a) ? true : false); # include Trace jobs
	$inc_c = (($agent_c || $manager_a) ? true : false); # include Collect jobs
	if (!$inc_t)
	{
		if (!$inc_c)
			$abort = true;
		else
		{
			if (!in_array($is_collect, $where))
				$where[] = $is_collect;
		}
	}
	elseif (!$inc_c)
	{
		if (!in_array($is_trace, $where))
			$where[] = $is_trace;
	}
	#else include all jobs

	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= J.J_OPENED_DT";
	if ($sc_date_to)
		$where[] = "J.J_OPENED_DT < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_upd_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_upd_fr) . "' <= J.J_UPDATED_DT";
	if ($sc_upd_to)
		$where[] = "J.J_UPDATED_DT < '" . date_for_sql_nqnt($sc_upd_to, true) . "'"; # NOT "<="
	if ($sc_closed_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_closed_fr) . "' <= J.J_CLOSED_DT";
	if ($sc_closed_to)
		$where[] = "J.J_CLOSED_DT < '" . date_for_sql_nqnt($sc_closed_to, true) . "'"; # NOT "<="
	if ($sc_target_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_target_fr) . "' <= J.J_TARGET_DT";
	if ($sc_target_to)
		$where[] = "J.J_TARGET_DT < '" . date_for_sql_nqnt($sc_target_to, true) . "'"; # NOT "<="

	if ($sc_act_fr)
	{
		$where[] = "'" . date_for_sql_nqnt($sc_act_fr) . "' <= JA.JA_DT";
		$act_used = true;
	}
	if ($sc_act_to)
	{
		$where[] = "JA.JA_DT < '" . date_for_sql_nqnt($sc_act_to, true) . "'"; # NOT "<="
		$act_used = true;
	}
	if ($sc_letters == 1) # Approved but not sent
	{
		$mp_age = post_val2('mp_age_main', true);
		$mp_threshold = "'2017-09-27 12:01:01'";
		#$mp_threshold = "'2017-02-01 12:01:01'"; # for local PC only
		if ($mp_age == -1)
			$where[] = "JL.JL_APPROVED_DT IS NOT NULL AND JL.JL_APPROVED_DT < $mp_threshold AND JL.JL_EMAIL_ID IS NULL AND JL.JL_POSTED_DT IS NULL AND JL.IMPORTED=$sqlFalse";
		elseif ($mp_age == 1)
			$where[] = "JL.JL_APPROVED_DT IS NOT NULL AND $mp_threshold < JL.JL_APPROVED_DT AND JL.JL_EMAIL_ID IS NULL AND JL.JL_POSTED_DT IS NULL AND JL.IMPORTED=$sqlFalse";
		else
		$where[] = "JL.JL_APPROVED_DT IS NOT NULL AND JL.JL_EMAIL_ID IS NULL AND JL.JL_POSTED_DT IS NULL AND JL.IMPORTED=$sqlFalse";
		$ltr_used = true;
	}
	elseif ($sc_letters == 2) # Not approved
	{
		$where[] = "JL.JOB_LETTER_ID IS NOT NULL AND JL.JL_APPROVED_DT IS NULL AND JL.IMPORTED=$sqlFalse";
		$ltr_used = true;
	}
	elseif ($sc_letters == 3) # Sent
	{
		$where[] = "JL.JL_EMAIL_ID IS NOT NULL OR JL.JL_POSTED_DT IS NOT NULL OR JL.IMPORTED=$sqlTrue";
		$ltr_used = true;
	}
	if (0 < $sc_ltype)
	{
		$where[] = "JL.LETTER_TYPE_ID=$sc_ltype";
		$ltr_used = true;
	}
	#if ($sc_pending)
	#{
	#	$where[] = "JL.JL_APPROVED_DT IS NULL";
	#	$ltr_used = true;
	#}

	if (!$abort)
	{
		if ($sc_text != '')
		{
			# Check whether the user has supplied a list of job numbers (comma-separated but not space-separated)
			$jn_list = '';
			if (strpos($sc_text,',') !== false)
			{
				$temp = str_replace(',', '', str_replace(' ', '', $sc_text));
				if (is_numeric_kdb($temp, false, false, false))
					$jn_list = str_replace(' ', '', $sc_text); # should just be numbers and commas
			}

			if ($jn_list)
			{
				$where[] = "( (J.J_VILNO IN ($jn_list)) OR (J.J_SEQUENCE IN ($jn_list)) )";
			}
			elseif (($sc_text === 0) || ($sc_text === '0') || is_numeric_kdb($sc_text, false, false, false))
			{
				$sc_number = intval($sc_text);
				$where[] = "( (J.J_VILNO=$sc_number) OR (J.J_SEQUENCE=$sc_number) )";
			}
			else
			{
				$sc_job_id = (($sc_text[0] == '*') ? intval(trim(str_replace('*', '', $sc_text))) : 0);
				if ($sc_job_id > 0)
				{
					$where[] = "J.JOB_ID=$sc_job_id";
					$obsolete_test = '';
				}
				else
				{
					$sc_text = addslashes_kdb($sc_text);
					$where[] = "(" . sql_decrypt('SUB.JS_FIRSTNAME') . " LIKE '%{$sc_text}%') OR
								(" . sql_decrypt('SUB.JS_LASTNAME') . " LIKE '%{$sc_text}%') OR
								(" . sql_decrypt('SUB.JS_COMPANY') . " LIKE '%{$sc_text}%')";
				}
			}
		}

		if ($sc_phone)
			$where[] = "J.JOB_ID IN ($sc_phone)";
		elseif ($sc_addr)
		{
			$sc_addr = addslashes_kdb($sc_addr);
			$where[] = "(" . sql_decrypt('SUB.JS_ADDR_1') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.JS_ADDR_2') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.JS_ADDR_3') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.JS_ADDR_4') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.JS_ADDR_5') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.JS_ADDR_PC') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.NEW_ADDR_1') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.NEW_ADDR_2') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.NEW_ADDR_3') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.NEW_ADDR_4') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.NEW_ADDR_5') . " LIKE '%{$sc_addr}%') OR
						(" . sql_decrypt('SUB.NEW_ADDR_PC') . " LIKE '%{$sc_addr}%')";
		}
		elseif ($sc_postcode)
		{
			if ($feedback_23 && $sc_outcode)
				$where[] = "SUB.JOB_SUBJECT_ID IN (SELECT JOB_SUBJECT_ID FROM JOB_SUBJECT WHERE JS_OUTCODE = '$sc_outcode' OR NEW_OUTCODE = '$sc_outcode')";
			$where[] = "(REPLACE(" . sql_decrypt('SUB.JS_ADDR_PC')  . ",' ','') = '{$sc_postcode}') OR
						(REPLACE(" . sql_decrypt('SUB.NEW_ADDR_PC') . ",' ','') = '{$sc_postcode}')";
		}

		if ($sc_agent)
		{
			if (0 < $sc_agent)
				$where[] = "J.J_USER_ID=$sc_agent";
			else
				$where[] = "J.J_USER_ID IS NULL";
		}

		if ($sc_client)
		{
			if (is_numeric_kdb($sc_client, false, false, false))
			{
				$c_code = intval($sc_client);
				$where[] = "C.C_CODE=$c_code";
			}
			else
			{
				$sc_client_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
				if ($sc_client_id > 0)
					$where[] = "C.CLIENT2_ID=$sc_client_id";
				else
				{
					if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Getting CLIENT2_IDs from name/alpha...");
					sql_encryption_preparation('CLIENT2');
					$sc_client = addslashes_kdb($sc_client);
					$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE (" . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%') OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "') AND C_ARCHIVED=$sqlFalse";
					#dprint($sql);#
					sql_execute($sql);
					$cids = array();
					while (($newArray = sql_fetch()) != false)
						$cids[] = $newArray[0];
					if ($cids)
						$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
					else
					{
						dprint("WARNING: Client Filter \"$sc_client\" yielded no client_id(s) - aborting search", true);
						$abort = true;
					}
					if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): ...got CLIENT2_IDs");
				}
			}
		}

		if ($sc_clref)
		{
			$sub_where = sql_decrypt('J.CLIENT_REF') . "=" . quote_smart($sc_clref, true);
			# If CLIENT_REF is the only part of the WHERE clause then it is mysteriously very slow!
			#if (count($where) == 0)
			#	$where[] = "((J.JT_JOB=1) AND ($sub_where)) OR ((J.JC_JOB=1) AND ($sub_where))";
			#else
				$where[] = "({$sub_where}) OR (J.JC_TRANS_ID=" . quote_smart($sc_clref, true) . ")";
		}

		if ($sc_group)
			$where[] = "C.CLIENT_GROUP_ID=$sc_group";
	}

	if ($sc_obsolete)
		$where[] = "J.OBSOLETE=$sqlTrue";
	else
	{
		if ($obsolete_test)
			$where[] = $obsolete_test;
	}
	#if ((!$sc_obsolete) && $obsolete_test)
	#	$where[] = $obsolete_test;

	if ($sc_archived)
		$where[] = "J.J_ARCHIVED=$sqlTrue";
	else
		$where[] = "J.J_ARCHIVED=$sqlFalse";

	$diary_fields = '';
	if ($sc_diary)
	{
		$where[] = "J.JOB_CLOSED=0";
		$where[] = "J.J_DIARY_DT IS NOT NULL";
		$where[] = "'2005-01-01' <= J.J_DIARY_DT";
		$diary_fields = ", J.J_DIARY_DT, " . sql_decrypt('J.J_DIARY_TXT', '', true) . "";
	}
	if (!$abort)
	{
		if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): preparing decryption keys...");
		sql_encryption_preparation('JOB');
		sql_encryption_preparation('CLIENT2');
		sql_encryption_preparation('JOB_SUBJECT');
		if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): ...done keys");

		$fields_with_alias =
					"J.JOB_ID, J.JT_JOB, J.J_VILNO, J.J_SEQUENCE, J.IMPORTED, J.JC_INSTAL_AMT, J.J_OPENED_DT, J.JT_JOB_TYPE_ID,
					C.CLIENT2_ID, " . sql_decrypt('C.C_CO_NAME', '', true) . ", C.C_CODE, J.JOB_CLOSED, J.JC_JOB_STATUS_ID,
					SUB.JOB_SUBJECT_ID, SUB.JS_TITLE, " . sql_decrypt('SUB.JS_FIRSTNAME', '', true) . ",  J.JT_REPORT_APPR,
					" . sql_decrypt('SUB.JS_LASTNAME', '', true) . ", " . sql_decrypt('SUB.JS_COMPANY', '', true) . ",
					AG.U_INITIALS, AG.U_FIRSTNAME, " . sql_decrypt('AG.U_LASTNAME', '', true) . ", J.OBSOLETE, J.J_ARCHIVED
					";
		$fields_without_alias =
					"J.JOB_ID, J.JT_JOB, J.J_VILNO, J.J_SEQUENCE, J.IMPORTED, J.JC_INSTAL_AMT, J.J_OPENED_DT, J.JT_JOB_TYPE_ID,
					C.CLIENT2_ID, " . sql_decrypt('C.C_CO_NAME', '', false) . ", C.C_CODE, J.JOB_CLOSED, J.JC_JOB_STATUS_ID,
					SUB.JOB_SUBJECT_ID, SUB.JS_TITLE, " . sql_decrypt('SUB.JS_FIRSTNAME', '', false) . ",  J.JT_REPORT_APPR,
					" . sql_decrypt('SUB.JS_LASTNAME', '', false) . ", " . sql_decrypt('SUB.JS_COMPANY', '', false) . ",
					AG.U_INITIALS, AG.U_FIRSTNAME, " . sql_decrypt('AG.U_LASTNAME', '', false) . ", J.OBSOLETE, J.J_ARCHIVED
					";
		if ($sc_llist)
		{
			$fields_with_alias .= ", J.JC_TOTAL_AMT,
									" . sql_decrypt('SUB.JS_ADDR_1', '', true) . ", " . sql_decrypt('SUB.JS_ADDR_2', '', true) . ",
									" . sql_decrypt('SUB.JS_ADDR_3', '', true) . ", " . sql_decrypt('SUB.JS_ADDR_4', '', true) . ",
									" . sql_decrypt('SUB.JS_ADDR_5', '', true) . ", " . sql_decrypt('SUB.JS_ADDR_PC', '', true);
			$fields_without_alias .= ",  J.JC_TOTAL_AMT,
									" . sql_decrypt('SUB.JS_ADDR_1', '', false) . ", " . sql_decrypt('SUB.JS_ADDR_2', '', false) . ",
									" . sql_decrypt('SUB.JS_ADDR_3', '', false) . ", " . sql_decrypt('SUB.JS_ADDR_4', '', false) . ",
									" . sql_decrypt('SUB.JS_ADDR_5', '', false) . ", " . sql_decrypt('SUB.JS_ADDR_PC', '', false);
		}

		$fields_select = "$fields_with_alias $diary_fields";
		if ((!$act_used) && (!$ltr_used))
			$fields_group = '';
		else
			$fields_group = $fields_without_alias;

		$tables = "
				FROM JOB AS J
				LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
				LEFT JOIN JOB_SUBJECT AS SUB ON SUB.JOB_ID=J.JOB_ID AND SUB.JS_PRIMARY=$sqlTrue
				LEFT JOIN USERV AS AG ON AG.USER_ID=J.J_USER_ID
				";
		$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');
		$order = "ORDER BY " . ($sc_diary ? 'J.J_DIARY_DT, ' : '') . ($sc_llist ? '' : "J.J_OPENED_DT DESC, ") . " J.J_VILNO DESC
				";

		if ((!$act_used) && (!$ltr_used))
		{
			# This is the bog-standard list of jobs.
			if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): SQL counting...");
			$sql = "SELECT COUNT(*) $tables $where";
			sql_execute($sql);
			if ($time_tests)
				$t_start = time();
			while (($newArray = sql_fetch()) != false)
				$count = $newArray[0];
			if ($time_tests)
			{
				$t_length = time() - $t_start;
				log_write("...Fetch for SELECT COUNT(*) took $t_length seconds to run.", true, true);
			}

			if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): SQL selecting...");
			$sql = "SELECT $ms_top $fields_select $tables $where $order $my_limit";
			#dprint("$this_function: $sql");#
			#dprint("sql_get_jobs(): $sql");#
			if ($debug)
				log_write("User {$USER['USER_ID']}: $sql");
			$jobs = array();
			sql_execute($sql);
			if ($time_tests)
				$t_start = time();
			while (($newArray = sql_fetch_assoc()) != false)
				$jobs[] = $newArray;
			if ($time_tests)
			{
				$t_length = time() - $t_start;
				log_write("...Fetch for SELECT <FIELDS> took $t_length seconds to run.", true, true);
			}
			if ($debug)
				log_write("User {$USER['USER_ID']}: SQL returned " . count($jobs) . " jobs.");
		}
		elseif ($act_used)
		{
			$sql = "SELECT $fields_select, COUNT(JA.JOB_ACT_ID) AS COUNT_ACT
					$tables INNER JOIN JOB_ACT AS JA ON JA.JOB_ID=J.JOB_ID
					$where
					GROUP BY $fields_group
					HAVING 0 < COUNT(JA.JOB_ACT_ID)
					$order
					";
			#dprint("$this_function: $sql");#
			$count = 0;
			$jobs = array();
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
			{
				$count++;
				if ($count <= $limit)
					$jobs[] = $newArray;
			}
		}
		elseif ($ltr_used)
		{
			$sql = "SELECT $fields_select, COUNT(JL.JOB_LETTER_ID) AS COUNT_LTR
					$tables INNER JOIN JOB_LETTER AS JL ON JL.JOB_ID=J.JOB_ID AND JL.OBSOLETE=0
					$where
					GROUP BY $fields_group
					HAVING 0 < COUNT(JL.JOB_LETTER_ID)
					$order
					";
			#dprint("$this_function: $sql");#
			$count = 0;
			$jobs = array();
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
			{
				$count++;
				if ($count <= $limit)
					$jobs[] = $newArray;
			}
		}

	} # if (!$abort)

	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Checking primary subjects...");
	# Cater for jobs where no subject is marked as primary
	$ii_max = min($limit, $count);
	for ($ii = 0; $ii < $ii_max; $ii++)
	{
		if (	($jobs[$ii]['JS_COMPANY'] == '') &&
				($jobs[$ii]['JS_TITLE'] == '') && ($jobs[$ii]['JS_FIRSTNAME'] == '') && ($jobs[$ii]['JS_LASTNAME'] == '')
			)
		{
			list($ms_top, $my_limit) = sql_top_limit(1);
			$sql = "SELECT $ms_top JOB_SUBJECT_ID, JS_TITLE, " . sql_decrypt('JS_FIRSTNAME', '', true) . ",
					" . sql_decrypt('JS_LASTNAME', '', true) . ", " . sql_decrypt('JS_COMPANY', '', true) . "
					FROM JOB_SUBJECT WHERE JOB_ID={$jobs[$ii]['JOB_ID']} AND OBSOLETE=$sqlFalse
					$my_limit ";
			#dprint($sql);#
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
			{
				$jobs[$ii]['JS_COMPANY'] = $newArray['JS_COMPANY'];
				$jobs[$ii]['JS_TITLE'] = $newArray['JS_TITLE'];
				$jobs[$ii]['JS_FIRSTNAME'] = $newArray['JS_FIRSTNAME'];
				$jobs[$ii]['JS_LASTNAME'] = $newArray['JS_LASTNAME'];
			}
		}
	}
	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): ...done subjects");

	$count_t = 0; # number of trace jobs
	$count_c = 0; # number of collect jobs
	if ($sc_llist)
	{
		# The user wants a list of letters rather than a list of jobs
		if ($sc_letters == 1) # Approved but not sent
			$where = "AND JL.JL_APPROVED_DT IS NOT NULL AND JL.JL_EMAIL_ID IS NULL AND JL.JL_POSTED_DT IS NULL AND JL.IMPORTED=$sqlFalse";
		elseif ($sc_letters == 2) # Not approved
			$where = "AND JL.JOB_LETTER_ID IS NOT NULL AND JL.JL_APPROVED_DT IS NULL AND JL.IMPORTED=$sqlFalse";
		elseif ($sc_letters == 3) # Sent
			$where = "AND (JL.JL_EMAIL_ID IS NOT NULL OR JL.JL_POSTED_DT IS NOT NULL OR JL.IMPORTED=$sqlTrue)";
		else
			$where = '';

		$letters = array();
		$letter_ids = array();
		$lix = 0;
		#dprint(print_r($jobs,1));#
		foreach ($jobs as $one_job)
		{
			if ($one_job['JT_JOB'])
				$count_t++;
			else
				$count_c++;
			$sql = "SELECT JL.JOB_LETTER_ID, LT.LETTER_NAME, JL.JL_APPROVED_DT, JL.JL_EMAIL_ID, JL.JL_POSTED_DT, EM.EM_DT,
					CASE WHEN JL.IMPORTED IS NULL THEN NULL ELSE JL.IMPORTED END AS JL_IMPORTED, JL.LETTER_TYPE_ID
					FROM JOB_LETTER AS JL
					LEFT JOIN LETTER_TYPE_SD AS LT ON LT.LETTER_TYPE_ID=JL.LETTER_TYPE_ID
					LEFT JOIN EMAIL AS EM ON EM.EMAIL_ID=JL.JL_EMAIL_ID
					WHERE JL.JOB_ID={$one_job['JOB_ID']} AND JL.OBSOLETE=$sqlFalse $where
					ORDER BY JL.JOB_ID DESC";
//						CASE JL_APPROVED_DT WHEN NULL THEN 0 ELSE 1 END AS LETTER_APPROVED,
//						CASE JL_EMAIL_ID WHEN NULL
//							THEN CASE JL_POSTED_DT WHEN NULL THEN 0 ELSE 1 END
//							ELSE 1 END AS LETTER_SENT
			#dprint($sql);#
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
			{
				#dprint("Found \"{$newArray['JOB_LETTER_ID']}\" and \"{$newArray['LETTER_TYPE_ID']}\"");#
				$this_lid = $newArray['JOB_LETTER_ID'];
				if (!in_array($this_lid, $letter_ids))
				{
					$letter_ids[] = $this_lid;
					$newArray['LETTER_APPROVED'] = 0;
					$newArray['PDF_LINK'] = '';
					$newArray['LETTER_SENT'] = 0;
					if ($newArray['JL_APPROVED_DT'])
					{
						$newArray['LETTER_APPROVED'] = 1;
						$newArray['PDF_LINK'] = pdf_link('jl', "v{$one_job['J_VILNO']}", "{$one_job['J_VILNO']}_{$one_job['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}");
						if ($newArray['JL_EMAIL_ID'] || $newArray['JL_POSTED_DT'])
							$newArray['LETTER_SENT'] = 1;
					}
					$letters[$lix] = $one_job;
					foreach ($newArray as $key => $val)
						$letters[$lix][$key] = $val;
					$lix++;
				}
			}
		} # foreach ($jobs)
		$jobs = '';

		# Sort jobs and letters by LETTER_TYPE_ID
		$letters_sort_type = array();
		foreach ($letters as $one_ltr)
		{
			$one_sortable = array_merge(array('LETTER_TYPE_ID' => $one_ltr['LETTER_TYPE_ID']), $one_ltr);
			$letters_sort_type[] = $one_sortable;
		}
		$letters = '';
		sort($letters_sort_type);

		# Now sort by JL_APPROVED_DT
		$letters_sort_date = array();
		$letters_temp = array();
		$letter_type = -1;
		foreach ($letters_sort_type as $one_ltr)
		{
			if ($one_ltr['LETTER_TYPE_ID'] != $letter_type)
			{
				if ($letters_temp)
				{
					sort($letters_temp); # sort letters (that have the same LETTER_TYPE_ID) into reverse order of JL_APPROVED_DT
					$letters_sort_date = array_merge($letters_sort_date, $letters_temp);
					$letters_temp = array();
				}
				$letter_type = $one_ltr['LETTER_TYPE_ID'];
			}
			$letters_temp[] = array_merge(array('JL_APPROVED_DT' => $one_ltr['JL_APPROVED_DT']), $one_ltr);
		}
		if ($letters_temp)
		{
			sort($letters_temp); # sort letters (that have the same LETTER_TYPE_ID) into reverse order of JL_APPROVED_DT
			$letters_sort_date = array_merge($letters_sort_date, $letters_temp);
			$letters_temp = array();
		}
		$letters_sort_type = '';

		$jobs = $letters_sort_date;
		#$count = count($jobs);
		#dprint(print_r($jobs,1));#
	} # if ($sc_llist)

	elseif ((!$act_used) && (!$ltr_used))
	{
		if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Checking approved letters...");
		# This is the bog-standard list of jobs. We need to show whether trace jobs have an approved letter.
		$num_jobs = count($jobs);
		for ($ii = 0; $ii < $num_jobs; $ii++)
		{
			if ($jobs[$ii]['JT_JOB'])
				$count_t++;
			else
				$count_c++;
			$jobs[$ii]['JL_APPROVED_DT'] = '';
			if ($jobs[$ii]['JT_JOB'] == 1)
			{
				$sql = "SELECT MAX(JL_APPROVED_DT)
						FROM JOB_LETTER
						WHERE JOB_ID={$jobs[$ii]['JOB_ID']} AND OBSOLETE=$sqlFalse
						";
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$jobs[$ii]['JL_APPROVED_DT'] = $newArray[0];
			}
		}
		if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): ...done approved letters");
	}

	if ($debug)
		log_write("User {$USER['USER_ID']}: sql_get_jobs() returning count of $count and array of " . count($jobs) . " jobs.");
	if ($time_tests) log_write("lib_vilcol.php/sql_get_jobs(): Exit.");
	return array($count, $jobs, $count_t, $count_c);

} # sql_get_jobs()

function sql_get_one_job($job_id, $for_display)
{
	global $crlf;
	global $id_LETTER_TYPE_contact;
	global $id_LETTER_TYPE_demand;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $sqlFalse;
	global $sqlLen;
	#global $sqlTrue;
	global $super_user_id;
	global $time_tests;

	if ($time_tests) log_write("sql_get_one_job(): Enter");

	sql_update_adjustment($job_id);

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('JOB_NOTE');
	sql_encryption_preparation('JOB_PHONE');
	sql_encryption_preparation('JOB_LETTER');
	sql_encryption_preparation('ADDRESS_HISTORY');
	sql_encryption_preparation('EMAIL');

	$job = array();
	$job_id = intval($job_id);
	$sql = "SELECT J.JOB_ID, J.JT_JOB, J.JC_JOB, J.J_VILNO, J.J_SEQUENCE, J.IMPORTED, " . sql_decrypt('J.CLIENT_REF', '', true) . ",
					C.CLIENT2_ID, " . sql_decrypt('C.C_CO_NAME', '', true) . ", C.C_CODE, J.JOB_CLOSED, C.C_CLOSEOUT, J.J_CLOSED_ID,
					J.J_USER_ID, " . sql_decrypt('AG.USERNAME') . " AS AGENT_USER, AG.U_INITIALS AS AGENT_INITIALS, J.J_USER_DT,
					AG.U_FIRSTNAME + ' ' + " . sql_decrypt('AG.U_LASTNAME') . " AS AGENT_FULLNAME, J.J_S_INVS, J.J_ARCHIVED, J.J_AVAILABLE,
					" . sql_decrypt('CU.USERNAME') . " AS CLOSED_USER, J.J_OPENED_DT, J.J_CLOSED_DT, J.J_TARGET_DT, J.J_UPDATED_DT,
					J.JOB_GROUP_ID, J.OBSOLETE, C.C_ARCHIVED, J.J_FRONT_DETAILS
			FROM JOB AS J
			LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			LEFT JOIN USERV AS AG ON AG.USER_ID=J.J_USER_ID
			LEFT JOIN USERV AS CU ON CU.USER_ID=J.J_CLOSED_ID
			WHERE J.JOB_ID=$job_id";
			#, " . sql_decrypt('J.J_REFERRER', '', true) . "
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$job = $newArray;
	#if ($for_display) dprint(print_r($job,1));#

	if (count($job) == 0)
	{
		if ($for_display) dprint("No job found with ID $job_id");
		return $job;
	}

	$job['SUBJECTS'] = array();
	$sql = "SELECT SUB.JOB_SUBJECT_ID, SUB.JS_PRIMARY, SUB.IMPORTED, SUB.OBSOLETE,
					SUB.JS_TITLE, " . sql_decrypt('SUB.JS_FIRSTNAME', '', true) . ", " . sql_decrypt('SUB.JS_LASTNAME', '', true) . ",
					" . sql_decrypt('SUB.JS_COMPANY', '', true) . ", SUB.JS_DOB,
					" . sql_decrypt('SUB.JS_ADDR_1', '', true) . ", " . sql_decrypt('SUB.JS_ADDR_2', '', true) . ",
					" . sql_decrypt('SUB.JS_ADDR_3', '', true) . ", " . sql_decrypt('SUB.JS_ADDR_4', '', true) . ",
					" . sql_decrypt('SUB.JS_ADDR_5', '', true) . ", " . sql_decrypt('SUB.JS_ADDR_PC', '', true) . ", SUB.JS_OUTCODE,
					" . sql_decrypt('SUB.NEW_ADDR_1', '', true) . ", " . sql_decrypt('SUB.NEW_ADDR_2', '', true) . ",
					" . sql_decrypt('SUB.NEW_ADDR_3', '', true) . ", " . sql_decrypt('SUB.NEW_ADDR_4', '', true) . ",
					" . sql_decrypt('SUB.NEW_ADDR_5', '', true) . ", " . sql_decrypt('SUB.NEW_ADDR_PC', '', true) . ", SUB.NEW_OUTCODE,
					" . sql_decrypt('SUB.JS_BANK_NAME', '', true) . ", " . sql_decrypt('SUB.JS_BANK_SORTCODE', '', true) . ",
					" . sql_decrypt('SUB.JS_BANK_ACC_NUM', '', true) . ", " . sql_decrypt('SUB.JS_BANK_ACC_NAME', '', true) . ",
					SUB.JS_BANK_COUNTRY
			FROM JOB_SUBJECT AS SUB
			WHERE SUB.JOB_ID=$job_id
			ORDER BY SUB.OBSOLETE, SUB.JS_PRIMARY DESC, SUB.JOB_SUBJECT_ID
			";
	sql_execute($sql);
	#if ($for_display) dprint($sql);#
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['ADDRESS_HISTORY'] = array();
		$job['SUBJECTS'][] = $newArray;
	}

	for ($ii = 0; $ii < count($job['SUBJECTS']); $ii++)
	{
		$sub = $job['SUBJECTS'][$ii];
		$sql = "SELECT ADDRESS_HISTORY_ID, AD_FROM_DT, AD_TO_DT,
					" . sql_decrypt('ADDR_1', '', true) . ", " . sql_decrypt('ADDR_2', '', true) . ",
					" . sql_decrypt('ADDR_3', '', true) . ", " . sql_decrypt('ADDR_4', '', true) . ",
					" . sql_decrypt('ADDR_5', '', true) . ", " . sql_decrypt('ADDR_PC', '', true) . "
				FROM ADDRESS_HISTORY WHERE JOB_SUBJECT_ID={$sub['JOB_SUBJECT_ID']} ORDER BY AD_TO_DT DESC";
		sql_execute($sql);
		#if ($for_display) dprint($sql);#
		while (($newArray = sql_fetch_assoc()) != false)
			$job['SUBJECTS'][$ii]['ADDRESS_HISTORY'][] = $newArray;
	}

	# For collection jobs, don't display any imported notes
	# 04/08/19: why don't we display imported collection notes?! -- we do now
	$job['NOTES'] = array();
	$sql = "SELECT N.JOB_NOTE_ID, " . sql_decrypt('N.J_NOTE', '', true) . ", N.IMPORTED,
					N.JN_ADDED_DT, UA.USER_ID, " . sql_decrypt('UA.USERNAME') . " AS U_ADDED,
					N.JN_UPDATED_DT, UU.USER_ID, " . sql_decrypt('UU.USERNAME') . " AS U_UPDATED
			FROM JOB_NOTE AS N
				LEFT JOIN USERV AS UA ON UA.USER_ID=N.JN_ADDED_ID
				LEFT JOIN USERV AS UU ON UU.USER_ID=N.JN_UPDATED_ID
			WHERE N.JOB_ID=$job_id " . #(($job['JC_JOB'] == 1) ? "AND N.IMPORTED=$sqlFalse" : '') . 
			"
			ORDER BY N.JN_ADDED_DT DESC
			";
	sql_execute($sql);
	#if ($for_display) dprint($sql);#
	while (($newArray = sql_fetch_assoc()) != false)
		$job['NOTES'][] = $newArray;

	if ($job['J_FRONT_DETAILS'])
		$job['NOTES'][] = array('JOB_NOTE_ID' => 0, 'J_NOTE' => "Old \"Details\":{$crlf}{$job['J_FRONT_DETAILS']}", 'IMPORTED' => 1,
								'JN_ADDED_DT' => '2017-01-01 01:00:00', 'USER_ID' => $super_user_id, 'U_ADDED' => 'Kevin',
								'JN_UPDATED_DT' => '', 'USER_ID_U' => 0, 'U_UPDATED' => '');

	if ($job['JC_JOB'] == 1)
	{
		$end_of_imp_note = false;
		$chunk_len = 3000;
		for ($ii = 0; $ii <= 1000; $ii++)
		{
			# Select 1000 chars/bytes at a time
			$chunk_start = $ii * $chunk_len;
			if ($chunk_start < 100100)
			{
				$sql = "SELECT SUBSTRING(JC_IMP_NOTES_VMAX, $chunk_start, $chunk_len) FROM JOB WHERE JOB_ID=$job_id";
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
				{
					if (0 < strlen($newArray[0]))
						$job['NOTES'][] = array('JOB_NOTE_ID' => 0, 'J_NOTE' => $newArray[0], 'IMPORTED' => 1,
												'JN_ADDED_DT' => '2017-01-01 01:00:00', 'USER_ID' => $super_user_id, 'U_ADDED' => 'Kevin',
												'JN_UPDATED_DT' => '', 'USER_ID_U' => 0, 'U_UPDATED' => '');
					else
						$end_of_imp_note = true;
				}
			}
			else
				$end_of_imp_note = true;

			if ($end_of_imp_note)
				break; # from for($ii)
		} # for($ii)

//		$sql = "SELECT JC_IMP_NOTES_VMAX FROM JOB WHERE JOB_ID=$job_id";
//		#$sql = "SELECT CONVERT(CHAR,JC_IMP_NOTES_VMAX) FROM JOB WHERE JOB_ID=$job_id"; # this seems to truncate it to 30 chars
//		sql_execute($sql);
//		#if ($for_display) dprint($sql);#
//		while (($newArray = sql_fetch()) != false)
//		{
//			$note_len = strlen($newArray[0]);
//			$chunk_len = 1000;
//			for ($ii = 0; $ii < $note_len; $ii += $chunk_len)
//			{
//				if ($chunk_len <= ($note_len - $ii))
//					$bit_len = $chunk_len;
//				else
//					$bit_len = $note_len - $ii;
//				#dprint("JC_IMP_NOTES_VMAX=<br>{$newArray[0]}");#
//				$job['NOTES'][] = array('JOB_NOTE_ID' => 0, 'J_NOTE' => substr($newArray[0], $ii, $bit_len), 'IMPORTED' => 1,
//										'JN_ADDED_DT' => '2017-01-01 01:00:00', 'USER_ID' => $super_user_id, 'U_ADDED' => 'Kevin',
//										'JN_UPDATED_DT' => '', 'USER_ID_U' => 0, 'U_UPDATED' => '');
//			}
//		}
	}

	$job['PHONES'] = array();
	$sql = "SELECT P.JOB_PHONE_ID, P.IMPORTED, P.IMP_PH, P.JP_PRIMARY_P, P.OBSOLETE,
					" . sql_decrypt('P.JP_PHONE', '', true) . ", " . sql_decrypt('P.JP_DESCR', '', true) . "
			FROM JOB_PHONE AS P
			WHERE (P.JOB_ID=$job_id) AND ({$sqlLen}(" . sql_decrypt('P.JP_PHONE') . ") > 0)
			ORDER BY P.OBSOLETE, P.JP_PRIMARY_P DESC, P.JOB_PHONE_ID
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$job['PHONES'][] = $newArray;

	$job['EMAILS'] = array();
	$sql = "SELECT P.JOB_PHONE_ID, P.IMPORTED, P.JP_PRIMARY_E, P.OBSOLETE, P.IMP_PH,
					" . sql_decrypt('P.JP_EMAIL', '', true) . ", " . sql_decrypt('P.JP_DESCR', '', true) . "
			FROM JOB_PHONE AS P
			WHERE (P.JOB_ID=$job_id) AND ({$sqlLen}(" . sql_decrypt('P.JP_EMAIL') . ") > 0)
			ORDER BY P.OBSOLETE, P.JP_PRIMARY_E DESC, P.JOB_PHONE_ID
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$job['EMAILS'][] = $newArray;

	$job['LETTERS_PENDING'] = array(); # pending approval
//	$job['LETTERS_READY'] = array(); # approved but not yet sent
	$job['LETTERS_SENT'] = array(); # sent
	$job['LETTERS_COUNT'] = 0;
	$job['LETTERS_SENT_COUNT'] = 0;
	$job['LETTERS_SENT_LAST'] = '';
	$job['LETTERS_DEMD_COUNT'] = 0;
	$job['LETTERS_DEMD_LAST'] = '';
	$sql = "SELECT L.JOB_LETTER_ID, L.LETTER_TYPE_ID, L.JL_ADDED_DT, L.JL_POSTED_DT, LT.LETTER_NAME, L.JL_EMAIL_RESENDS,
					E.EM_DT, " . sql_decrypt('E.EM_TO', '', true) . ", " . sql_decrypt('E.EM_SUBJECT', '', true) . ",
					" . sql_decrypt('E.EM_MESSAGE', '', true) . ", E.EM_ATTACH,
					" . sql_decrypt('L.JL_TEXT', '', true) . ", " . sql_decrypt('L.JL_TEXT_2', '', true) . ",
					CASE WHEN JL_UPDATED_ID = -1 THEN 'SYSTEM' ELSE " . sql_decrypt('U.USERNAME') . " END AS UPDATED_U,
					L.JL_UPDATED_DT, L.IMPORTED, L.JL_APPROVED_DT, '' AS JL_POSTED_PDF
			FROM JOB_LETTER AS L
			LEFT JOIN USERV AS U ON U.USER_ID=L.JL_UPDATED_ID
			LEFT JOIN EMAIL AS E ON E.EMAIL_ID=L.JL_EMAIL_ID AND E.OBSOLETE=$sqlFalse
			LEFT JOIN LETTER_TYPE_SD AS LT ON LT.LETTER_TYPE_ID=L.LETTER_TYPE_ID
			WHERE L.JOB_ID=$job_id AND L.OBSOLETE=$sqlFalse
			ORDER BY L.JOB_LETTER_ID DESC
			";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['JL_TEXT'] = trim((string)$newArray['JL_TEXT']);
		$newArray['JL_TEXT_2'] = trim((string)$newArray['JL_TEXT_2']);
		$content = ((0 < strlen($newArray['JL_TEXT'] . $newArray['JL_TEXT_2'])) ? true : false);
		$approved = $newArray['JL_APPROVED_DT'] ? true : false;
		$sent = ($newArray['EM_DT'] || $newArray['JL_POSTED_DT']) ? true : false;
		$do_sent_check = false;

		# Letters are either Pending or Sent.

		if ($newArray['IMPORTED'])
		{
			if ($content)
			{
				# Non-blank imported letter
				$job['LETTERS_SENT'][] = $newArray;
				$do_sent_check = true;
				$job['LETTERS_COUNT']++;
			}
		}
		elseif ((!$approved) || (!$sent))
		{
			# Letter not yet approved, or approved but not yet sent
			$job['LETTERS_PENDING'][] = $newArray;
			$job['LETTERS_COUNT']++;
		}
//		elseif (!$newArray['JL_CREATED_DT'])
//		{
//			# Approved letter but not yet created or sent
//			if ($job['JT_JOB'] == 1)
//				$job['LETTERS_PENDING'][] = $newArray;
//			else
//				$job['LETTERS_READY'][] = $newArray;
//			$job['LETTERS_COUNT']++;
//		}
		else
		{
			# Letter has been sent
			$newArray['JL_POSTED_PDF'] = '';
			if ($newArray['JL_POSTED_DT'] && (!$newArray['EM_DT'])) # posted but not emailed
			{
				# Example EM_ATTACH: "v1512257/letter_1512257_90868311_373223_20161123_103127.pdf|c1234/invoice_204145_20161123_102417.pdf"
				# Also: "v1546871/letter_1546871_90902925_1247469_20170103_145712.pdf|c/"
				$temp = find_pdf($job['J_VILNO'], 'letter_');
				if ($temp)
					$newArray['JL_POSTED_PDF'] = "v{$job['J_VILNO']}/$temp";
			}
			$job['LETTERS_SENT'][] = $newArray;
			$do_sent_check = true;
			$job['LETTERS_COUNT']++;
		}

		if ($do_sent_check)
		{
			if ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_contact)
			{
				$job['LETTERS_SENT_COUNT']++;
				if ($job['LETTERS_SENT_LAST'] == '')
				{
					if ($newArray['EM_DT'])
						$job['LETTERS_SENT_LAST'] = $newArray['EM_DT'];
					else
						$job['LETTERS_SENT_LAST'] = date_for_sql($newArray['JL_POSTED_DT'], true, false);
				}
			}
			elseif ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_demand)
			{
				$job['LETTERS_DEMD_COUNT']++;
				if ($job['LETTERS_DEMD_LAST'] == '')
				{
					if ($newArray['EM_DT'])
						$job['LETTERS_DEMD_LAST'] = $newArray['EM_DT'];
					else
						$job['LETTERS_DEMD_LAST'] = date_for_sql($newArray['JL_POSTED_DT'], true, false);
				}
			}
		}
	} # while ($newArray)

	for ($ix = 0; $ix < count($job['LETTERS_SENT']); $ix++)
	{
		$job['LETTERS_SENT'][$ix]['JL_EMAILS_OLD'] = array();
		$sql = "SELECT EMAIL_ID, EM_DT, " . sql_decrypt('EM_TO', '', true) . ", " . sql_decrypt('EM_SUBJECT', '', true) . ",
						" . sql_decrypt('EM_MESSAGE', '', true) . ", EM_ATTACH
				FROM EMAIL WHERE JOB_ID=$job_id AND OBSOLETE=1 ORDER BY EMAIL_ID";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$job['LETTERS_SENT'][$ix]['JL_EMAILS_OLD'][$newArray['EMAIL_ID']] = $newArray;
	}

	if ($job['LETTERS_COUNT'] == 0)
	{
		if ($for_display) dprint("***NO LETTERS***");
	}
	else
	{
		if ($for_display) dprint("Letter(s): " . count($job['LETTERS_PENDING']). " pending, " . count($job['LETTERS_SENT']). " sent.");#" . count($job['LETTERS_READY']). " ready,
	}
	#if ($for_display) dprint("Letters = <br>" . print_r($job['LETTERS_PENDING'],1) . "<br>" . print_r($job['LETTERS_READY'],1) . "<br>" . print_r($job['LETTERS_SENT'],1));#

	$job['TRACE_DETAILS'] = '';
	$job['COLLECT_DETAILS'] = '';
	$job['PREV_ARRANGE'] = array();
	if ($job['JT_JOB'] == 1)
	{
		$sql = "SELECT J.J_COMPLETE, J.JT_JOB_TYPE_ID, TY.JT_TYPE, J.JT_SUCCESS, J.JT_CREDIT, J.JT_FEE_Y, J.JT_FEE_N, J.JT_BACK_DT, J.JT_JOB_TYPE_ID,
					" . sql_decrypt('J.JT_LET_REPORT', '', true) . ", J.JT_REPORT_APPR, J.JT_TM_T_COMP, J.JT_TM_M_COMP, J.JT_PROPERTY,
					J.JT_AMOUNT, " . sql_decrypt('J.JC_REASON_2', '', true) . ", J.JT_JOB_TARGET_ID, J.JT_TM_T_FEE, J.J_DIARY_DT
				FROM JOB AS J
				LEFT JOIN JOB_TYPE_SD AS TY ON TY.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
				WHERE (J.JOB_ID=$job_id)
				";
				#, J.J_TURN_H
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$newArray['JT_SUCCESS'] = "{$newArray['JT_SUCCESS']}"; # convert int to string
			if ($newArray['JT_SUCCESS'] == '')
				$newArray['JT_SUCCESS'] = 'NULL';
			switch ($newArray['JT_PROPERTY'])
			{
				case 1: $newArray['JT_PROPERTY_TXT'] = 'Owner'; break;
				case 2: $newArray['JT_PROPERTY_TXT'] = 'Tenant'; break;
				case 3: $newArray['JT_PROPERTY_TXT'] = 'Parents'; break;
				case 4: $newArray['JT_PROPERTY_TXT'] = 'Forces'; break;
				case 5: $newArray['JT_PROPERTY_TXT'] = 'Other'; break;
				default: $newArray['JT_PROPERTY_TXT'] = ''; break;
			}
			$job['TRACE_DETAILS'] = $newArray;
		}
	}
	else
	{
		$sql = "SELECT J.JC_JOB_STATUS_ID, ST.J_STATUS, J.JC_TOTAL_AMT, J.J_DIARY_DT, " . sql_decrypt('J.J_DIARY_TXT', '', true) . ",
					J.JC_PERCENT, J.JC_PAYMENT_METHOD_ID, PA.PAYMENT_METHOD, J.JC_PAID_SO_FAR, J.JC_LETTER_MORE, J.JC_LETTER_TYPE_ID,
					J.JC_INSTAL_AMT, J.JC_INSTAL_FREQ, J.JC_INSTAL_DT_1, J.JC_TC_JOB, J.JC_TRANS_ID, J.JC_TRANS_CNUM, J.JC_JOB_STATUS_ID,
					" . sql_decrypt('J.JC_REASON_2', '', true) . ", J.JC_LETTER_MORE, J.JC_LETTER_TYPE_ID, J.JC_MIN_SETT, LT.LETTER_NAME,
					J.JC_ADJUSTMENT, J.JC_REVIEW_DT, J.JC_LETTER_DELAY
				FROM JOB AS J
				LEFT JOIN JOB_STATUS_SD AS ST ON ST.JOB_STATUS_ID=J.JC_JOB_STATUS_ID
				LEFT JOIN PAYMENT_METHOD_SD AS PA ON PA.PAYMENT_METHOD_ID=J.JC_PAYMENT_METHOD_ID
				LEFT JOIN LETTER_TYPE_SD AS LT ON LT.LETTER_TYPE_ID=J.JC_LETTER_TYPE_ID
				WHERE (J.JOB_ID=$job_id)
				";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$job['COLLECT_DETAILS'] = $newArray;

		$sql = "SELECT SUM(COALESCE(COL_AMT_RX,0.0)) FROM JOB_PAYMENT WHERE (JOB_ID=$job_id) AND (COL_BOUNCED=$sqlFalse) AND (OBSOLETE=$sqlFalse)
					AND (COL_PAYMENT_ROUTE_ID IN ($id_ROUTE_tous, $id_ROUTE_direct, $id_ROUTE_fwd))"; # Use COALESCE to get rid of warning message "Warning: odbc_fetch_row(): No tuples available at this result index in /var/www/vhosts/rdresearch.co.uk/httpdocs/viltest/lib_sql.php on line 536" on RD server (not local pc)
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$job['COLLECT_DETAILS']['SUM_PAYMENTS'] = floatval($newArray[0]);

		$sql = "SELECT JA.JA_DT, JA.JA_INSTAL_FREQ, JA.JA_INSTAL_AMT, JA.JA_INSTAL_DT_1, JA.JA_PAYMENT_METHOD_ID,
					JA.JA_TOTAL, JA.Z_DEPDATE, CASE WHEN JA.Z_DEPAMOUNT > 0 THEN JA.Z_DEPAMOUNT ELSE NULL END AS Z_DEPAMOUNT,
					PA.PAYMENT_METHOD
				FROM JOB_ARRANGE AS JA
				LEFT JOIN PAYMENT_METHOD_SD AS PA ON PA.PAYMENT_METHOD_ID=JA.JA_PAYMENT_METHOD_ID
				WHERE (JA.JOB_ID=$job_id) ORDER BY JA.JOB_ARRANGE_ID DESC
				";
		sql_execute($sql);
		$first = true;
		while (($newArray = sql_fetch_assoc()) != false)
		{
			if (!$first) # don't copy the first one (i.e. the most recent ID) as this will be a copy of the one in the JOB table.
				$job['PREV_ARRANGE'][] = $newArray;
			else
				$first = false;
		}
	}

	$job['JOB_Z'] = '';
	if ($job['IMPORTED'])
	{
		$sql = "SELECT Z_X_AUTO, Z_T_LETDATE, Z_T_LET1, Z_T_DATE1, Z_T_LET2, Z_T_DATE2, Z_T_LET3, Z_T_DATE3,
					Z_T_LET4, Z_T_DATE4, Z_T_LET5, Z_T_DATE5, Z_T_LET6, Z_T_DATE6, Z_T_LET7, Z_T_DATE7, Z_T_LET8, Z_T_DATE8,
					Z_T_LET9, Z_T_DATE9, Z_T_LET10, Z_T_DATE10, Z_T_LET11, Z_T_DATE11, Z_T_LET12, Z_T_DATE12, Z_T_REP1,
					Z_T_REP2, Z_T_REP3, Z_T_REP4, Z_T_REP5, Z_T_REP6, Z_T_REP7, Z_T_REP8, Z_T_REP9, Z_T_REP10, Z_T_REP11
				FROM JOB_Z WHERE JOB_ID=$job_id";
		#if ($for_display) dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
//			foreach ($newArray as $new_f => $new_v)
//			{
//				if ($new_v == '.00')
//					$newArray[$new_f] = '0.00'; # Fix bizarre bug
//			}
			$job['JOB_Z'] = $newArray;
		}
	}
	#if ($for_display) dprint("z=" . print_r($job['JOB_Z'],1));#

	$job['PAYMENTS'] = array();
	#if ($for_display) dprint("payments/0a={$job['JC_JOB']}");#
	if ($job['JC_JOB'] == 1)
	{
		sql_encryption_preparation('JOB_PAYMENT');
		$sql = "SELECT P.JOB_PAYMENT_ID, P.COL_AMT_RX, P.COL_DT_RX, P.INVOICE_ID, P.IMPORTED, P.Z_DATE, P.ADJUSTMENT_ID, I.INV_NUM,
					P.COL_PAYMENT_METHOD_ID, PM.PAYMENT_METHOD, P.COL_PERCENT, P.COL_PAYMENT_ROUTE_ID, PR.PAYMENT_ROUTE, P.COL_BOUNCED,
					" . sql_decrypt('P.COL_NOTES', '', true) . "
				FROM JOB_PAYMENT AS P
				LEFT JOIN INVOICE AS I ON I.INVOICE_ID=P.INVOICE_ID
				LEFT JOIN PAYMENT_METHOD_SD AS PM ON PM.PAYMENT_METHOD_ID=P.COL_PAYMENT_METHOD_ID
				LEFT JOIN PAYMENT_ROUTE_SD AS PR ON PR.PAYMENT_ROUTE_ID=P.COL_PAYMENT_ROUTE_ID
				WHERE (I.INVOICE_ID IS NULL OR I.OBSOLETE=$sqlFalse) AND (P.JOB_ID=$job_id) AND (P.OBSOLETE=$sqlFalse)
				ORDER BY P.COL_DT_RX DESC, P.JOB_PAYMENT_ID DESC ";
		#if ($for_display) dprint($sql);#
		#if ($for_display) dprint("payments/0b=$sql");#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			#if ($for_display) dprint("found=" . print_r($newArray,1));#
			$newArray['COL_EP_RX'] = date_to_epoch($newArray['COL_DT_RX']);
			$job['PAYMENTS'][] = $newArray;
		}
		#if ($for_display) dprint("end=" . print_r($newArray,1));#
	}
	#if ($for_display) dprint("payments/1=".print_r($job['PAYMENTS'],1));#
	#if ($for_display) dprint("payments=" . print_r($job['PAYMENTS'],1));#

	$job['ACTIVITY'] = array();
	if ($job['JC_JOB'] == 1)
	{
		$sql = "SELECT JA.JOB_ACT_ID, JA.ACTIVITY_ID, A.ACT_TDX, A.ACT_DSHORT, JA.JA_DT, JA.JA_NOTE, JA.IMPORTED
				FROM JOB_ACT AS JA LEFT JOIN ACTIVITY_SD AS A ON A.ACTIVITY_ID=JA.ACTIVITY_ID
				WHERE JA.JOB_ID=$job_id
				ORDER BY JA.JA_DT DESC ";
		#if ($for_display) dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			#if ($for_display) dprint("found=" . print_r($newArray,1));#
			$job['ACTIVITY'][] = $newArray;
		}
		#if ($for_display) dprint("end=" . print_r($newArray,1));#
	}
	#if ($for_display) dprint("activity=" . print_r($job['ACTIVITY'],1));#

	$job['BILLING'] = array();
	$job['BILLING_COST_TOTAL'] = 0.0;
	if ($job['JT_JOB'] == 1)
	{
		$sql = "SELECT B.INV_BILLING_ID, B.INVOICE_ID, B.BL_SYS, B.BL_SYS_IMP, B.BL_DESCR, B.BL_COST, B.BL_LETTER_DT, B.IMPORTED,
					I.INV_NUM, I.INV_SYS, I.INV_TYPE, I.INV_APPROVED_DT
				FROM INV_BILLING AS B
				LEFT JOIN INVOICE AS I ON I.INVOICE_ID=B.INVOICE_ID
				WHERE ((I.INVOICE_ID IS NULL) OR (I.OBSOLETE=$sqlFalse)) AND (B.JOB_ID=$job_id) AND (B.OBSOLETE=$sqlFalse)
				ORDER BY B.INV_BILLING_ID DESC ";
		#if ($for_display) dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			#if ($for_display) dprint("found=" . print_r($newArray,1));#
			$job['BILLING'][] = $newArray;
			$job['BILLING_COST_TOTAL'] += floatval($newArray['BL_COST']);
		}
		#if ($for_display) dprint("end=" . print_r($newArray,1));#
	}
	#if ($for_display) dprint("billing=" . print_r($job['BILLING'],1));#

	$job['GROUP_MEMBERS'] = array();
	if (0 < $job['JOB_GROUP_ID'])
	{
		$sql = "SELECT JOB_ID, J_VILNO, J_SEQUENCE
				FROM JOB
				WHERE (JOB_ID <> $job_id) AND (OBSOLETE=$sqlFalse) AND (JOB_GROUP_ID = {$job['JOB_GROUP_ID']})
				ORDER BY J_VILNO, J_SEQUENCE";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$job['GROUP_MEMBERS'][] = array('JOB_ID' => $newArray['JOB_ID'], 'J_VILNO' => $newArray['J_VILNO'], 'J_SEQUENCE' => $newArray['J_SEQUENCE']);
	}

	$job['COLLECT_1_10'] = '';
//	if ($job['JC_JOB'] == 1)
//	{
//		$sql = "SELECT JC_IN_PROGRESS_CU1, JC_SUBJ_PAID_CU2, JC_PAID_DT_CU2, JC_PAID_AMT_CU2, JC_PDCHEQUES_CU3, JC_PDC_TXT_CU3,
//						JC_SUBJ_CONT_CU4, JC_AGREED_CU5, JC_AGR_TXT_CU5, JC_NEW_ADR_CU6,
//						" . sql_decrypt('JC_ADDR_1_CU6', '', true) . ", " . sql_decrypt('JC_ADDR_2_CU6', '', true) . ",
//						" . sql_decrypt('JC_ADDR_3_CU6', '', true) . ", " . sql_decrypt('JC_ADDR_4_CU6', '', true) . ",
//						" . sql_decrypt('JC_ADDR_5_CU6', '', true) . ", " . sql_decrypt('JC_ADDR_PC_CU6', '', true) . ",
//						JC_NO_ADDR_CU7, JC_FAIL_PROM_CU8, JC_NOT_RESP_CU9, JC_ADD_NOTES_CU10,
//						" . sql_decrypt('JC_AN1_CU10', '', true) . ", " . sql_decrypt('JC_AN2_CU10', '', true) . ",
//						" . sql_decrypt('JC_AN3_CU10', '', true) . "
//				FROM JOB WHERE JOB_ID=$job_id
//				";
//		sql_execute($sql);
//		while (($newArray = sql_fetch_assoc()) != false)
//			$job['COLLECT_1_10'] = $newArray;
//	}

	#if ($for_display) dprint(print_r($job,1));#
	if ($time_tests) log_write("sql_get_one_job(): Exit");
	return $job;

} # sql_get_one_job()

function job_status_id_from_code($code)
{
	$sql = "SELECT JOB_STATUS_ID FROM JOB_STATUS_SD WHERE J_STATUS='$code'";
	sql_execute($sql);
	$id = 0;
	while (($newArray = sql_fetch()) != false)
		$id = $newArray[0];
	return $id;
}

function instal_freq_list()
{
	return array(
			'I' => instal_freq_from_code('I'),
			'D' => instal_freq_from_code('D'),
			'W' => instal_freq_from_code('W'),
			'F' => instal_freq_from_code('F'),
			'M' => instal_freq_from_code('M'),
			'T' => instal_freq_from_code('T'),
			'Q' => instal_freq_from_code('Q')
		);
}

function instal_freq_from_code($code, $just_period=false)
{
	$codel = strtolower($code);
	if ($codel == 'i')
		return "Immediate";
	if ($codel == 'd')
		return ($just_period ? "day" : "Daily");
	if ($codel == 'w')
		return ($just_period ? "week" : "Weekly");
	if ($codel == 'f')
		return ($just_period ? "fortnight" : "Fortnightly");
	if ($codel == 'm')
		return ($just_period ? "month" : "Monthly");
	if ($codel == 't')
		return ($just_period ? "two months" : "Two-monthly");
	if ($codel == 'q')
		return ($just_period ? "quarter" : "Quarterly");
	if ($codel == '')
		return "-";
	return "?(" . $code	. ")?";
}

function stmt_freq_list()
{
	return array(
			'D' => instal_freq_from_code('D'),
			'W' => instal_freq_from_code('W'),
			'F' => instal_freq_from_code('F'),
			'M' => instal_freq_from_code('M'),
			'T' => instal_freq_from_code('T'),
			'Q' => instal_freq_from_code('Q')
		);
}

function stmt_freq_from_code($code)
{
	$codel = strtolower($code);
	if ($codel == 'd')
		return "Daily";
	if ($codel == 'w')
		return "Weekly";
	if ($codel == 'f')
		return "Fortnightly";
	if ($codel == 'm')
		return "Monthly";
	if ($codel == 't')
		return "Two-monthly";
	if ($codel == 'q')
		return "Quarterly";
	if ($codel == '')
		return "-";
	return "?(" . $code	. ")?";
}

function sql_get_client_groups($inc_obsolete)
{
	global $client_groups;
	global $sqlFalse;

	$client_groups = array();

	sql_encryption_preparation('CLIENT_GROUP');
	$sql = "SELECT CLIENT_GROUP_ID, " . sql_decrypt('GROUP_NAME', '', true) . " FROM CLIENT_GROUP
			" . ($inc_obsolete ? '' : "WHERE OBSOLETE=$sqlFalse") . "
			ORDER BY GROUP_NAME";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client_groups[$newArray['CLIENT_GROUP_ID']] = $newArray['GROUP_NAME'];
}

function sql_get_client_groups_with_clients($item_id=0)
{
	global $client_groups_with_clients;

	$client_groups_with_clients = array();

	if ($item_id == -1)
	{
		$client_groups_with_clients[$item_id] = array('GROUP_NAME' => '', 'OBSOLETE' => 0, 'CLIENTS' => array());
		return $client_groups_with_clients;
	}

	sql_encryption_preparation('CLIENT_GROUP');
	$sql = "SELECT CLIENT_GROUP_ID, " . sql_decrypt('GROUP_NAME', '', true) . ", OBSOLETE FROM CLIENT_GROUP ORDER BY OBSOLETE, GROUP_NAME";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client_groups_with_clients[$newArray['CLIENT_GROUP_ID']] =
			array('GROUP_NAME' => $newArray['GROUP_NAME'], 'OBSOLETE' => $newArray['OBSOLETE'], 'CLIENTS' => array());

	sql_encryption_preparation('CLIENT2');
	foreach ($client_groups_with_clients as $cg_id => $cg_data)
	{
		$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . "
				FROM CLIENT2 WHERE CLIENT_GROUP_ID=$cg_id
				ORDER BY C_CODE";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_groups_with_clients[$cg_id]['CLIENTS'][] = $newArray;
		$cg_data=$cg_data; # suppress editor warnings
	}
	#dprint("\$client_groups_with_clients=" . print_r($client_groups_with_clients,1));#

} # sql_get_client_groups_with_clients()

function sql_get_job_groups_with_jobs()
{
	global $job_groups;

	$sql = "SELECT JOB_ID, J_VILNO, J_SEQUENCE, JOB_GROUP_ID FROM JOB WHERE (0 < JOB_GROUP_ID) ORDER BY JOB_GROUP_ID, J_VILNO, J_SEQUENCE";
	sql_execute($sql);
	$job_groups = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$group_id = $newArray['JOB_GROUP_ID'];
		if (!array_key_exists($group_id, $job_groups))
			$job_groups[$group_id] = array('NAME' => 'x', 'JOBS' => array());
		$job_groups[$group_id]['JOBS'][] = $newArray;
	}

//	$sql = "SELECT G.JOB_GROUP_ID, G.JG_NAME FROM JOB_GROUP AS G";
//	sql_execute($sql);
//	$job_groups = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//		$job_groups[$newArray['JOB_GROUP_ID']] = $newArray['JG_NAME'];
//
//	foreach ($job_groups as $group_id => $name)
//	{
//		$job_groups[$group_id] = array('NAME' => $name, 'JOBS' => array());
//
//		$sql = "SELECT J.JOB_ID, J.J_VILNO, J.J_SEQUENCE
//				FROM JOB AS J
//				WHERE J.JOB_GROUP_ID=$group_id";
//		sql_execute($sql);
//		while (($newArray = sql_fetch_assoc()) != false)
//			$job_groups[$group_id]['JOBS'][] = $newArray;
//	}
	#dprint("sql_get_job_groups_with_jobs(): " . print_r($job_groups,1));

} # sql_get_job_groups_with_jobs()

function sql_client_group_name_from_id($group_id)
{
	$name = "(group id $group_id not found)";
	sql_encryption_preparation('CLIENT_GROUP');
	$sql = "SELECT " . sql_decrypt('GROUP_NAME') . " FROM CLIENT_GROUP WHERE CLIENT_GROUP_ID=$group_id";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$name = $newArray[0];
	return $name;

} # sql_client_group_name_from_id()

function sql_get_activity($for_select_list=false, $include_obsolete=true, $item_id=0)
{
	global $activities;
	global $sqlFalse;

	if ($item_id == -1)
	{
		$activities = array(array('ACTIVITY_ID' => 0, 'ACT_TDX' => '', 'ACT_DSHORT' => '', 'ACT_DLONG' => '',
									'SHORT_LIST' => '', 'DIALLER_EV' => 0, 'ACT_NON_MAN' => 0, 'OBSOLETE' => 0));
		return $activities;
	}

	$where = array();
	if (!$include_obsolete)
		$where[] = "OBSOLETE=$sqlFalse";
	if (0 < $item_id)
		$where[] = "ACTIVITY_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT ACTIVITY_ID, ACT_TDX, ACT_DSHORT, ACT_DLONG, SHORT_LIST, DIALLER_EV, ACT_NON_MAN, OBSOLETE
			FROM ACTIVITY_SD
			$where
			ORDER BY ACT_TDX, ACTIVITY_ID";
			#" . ($include_obsolete ? '' : "WHERE OBSOLETE=$sqlFalse") . "
	#dprint($sql);#
	#log_write($sql);#
	sql_execute($sql);
	$activities = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($for_select_list)
			$activities[$newArray['ACTIVITY_ID']] = $newArray['ACT_TDX'];
		else
			$activities[] = $newArray;
	}
	return $activities;
}

function sql_get_adjust($for_select_list=false, $include_obsolete=true, $item_id=0)
{
	global $adjusts;
	global $sqlFalse;

	if ($item_id == -1)
	{
		$adjusts = array(array('ADJUSTMENT_ID' => 0, 'ADJUSTMENT' => '', 'SORT_ORDER' => 0, 'OBSOLETE' => 0));
		return $adjusts;
	}

	$where = array();
	if (!$include_obsolete)
		$where[] = "OBSOLETE=$sqlFalse";
	if (0 < $item_id)
		$where[] = "ADJUSTMENT_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT ADJUSTMENT_ID, ADJUSTMENT, SORT_ORDER, OBSOLETE
			FROM ADJUSTMENT_SD
			$where
			ORDER BY OBSOLETE, SORT_ORDER, ADJUSTMENT, ADJUSTMENT_ID";
			#" . ($include_obsolete ? '' : "WHERE OBSOLETE=$sqlFalse") . "
	#dprint($sql);#
	#log_write($sql);#
	sql_execute($sql);
	$adjusts = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($for_select_list)
			$adjusts[$newArray['ADJUSTMENT_ID']] = $newArray['ADJUSTMENT'];
		else
			$adjusts[] = $newArray;
	}
	return $adjusts;
}

function sql_get_report_fields($indexed, $item_id=0)
{
	global $report_fields;

	$where = array();
	if (0 < $item_id)
		$where[] = "REPORT_FIELD_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');

	$sql = "SELECT REPORT_FIELD_ID, RF_CODE, RF_DESCR, RF_LONG, RF_SEL, RF_ANALYSIS, RF_JOB_DETAIL,
				RF_COLL, RF_TRACE, SORT_ORDER
			FROM REPORT_FIELD_SD
			$where
			ORDER BY
				CASE WHEN RF_ANALYSIS=1 THEN 0 ELSE 1 END,
				CASE WHEN RF_JOB_DETAIL=1 THEN 0 ELSE 1 END,
				SORT_ORDER";
	sql_execute($sql);
	#log_write($sql);#
	$report_fields = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($indexed)
			$report_fields[$newArray['REPORT_FIELD_ID']] = $newArray;
		else
			$report_fields[] = $newArray;
	}
	return $report_fields;
}

function sql_get_reports_custom($user_id, $analysis, $collect, $inc_obsolete=false, $get_obsolete=false)
{
	# For global reports, $user_id should be set to zero.

	global $sqlFalse;
	global $sqlTrue;

	$custom = array();
	$sql = "SELECT REPORT_ID, REP_NAME, OBSOLETE
			FROM REPORT
			WHERE	" . ($inc_obsolete ? '' : "(OBSOLETE=$sqlFalse) AND ") . "
					(REP_TEMP=$sqlFalse) AND
					(REP_USER_ID" . ($user_id ? "=$user_id" : ' IS NULL') . ") AND
					(REP_ANALYSIS=" . ($analysis ? $sqlTrue : $sqlFalse) . ") AND
					(REP_COLLECT=" . ($collect ? $sqlTrue : $sqlFalse) . ")
			ORDER BY OBSOLETE, REP_NAME";
	sql_execute($sql);
	#dprint($sql);#
	#log_write($sql);#
	$custom = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($get_obsolete)
			$custom[$newArray['REPORT_ID']] = array($newArray['REP_NAME'], $newArray['OBSOLETE']);
		else
			$custom[$newArray['REPORT_ID']] = $newArray['REP_NAME'] . ($newArray['OBSOLETE'] ? ' (OBSOLETE)' : '');
	}
	return $custom;
} # sql_get_reports_custom()

function sql_create_custom_report()
{
	# Caller must set globals $rc_global, $rc_analysis and $rc_collect before calling this function.

	global $rc_analysis;
	global $rc_collect;
	global $rc_global;
	global $sqlTrue;
	global $USER;

	$rep_used_id = ($rc_global ? 'NULL' : $USER['USER_ID']);
	$created_dt = "'" . date_now_sql() . "'";
	$sql = "INSERT INTO REPORT (REP_NAME, REP_USER_ID, REP_ANALYSIS, REP_COLLECT, REP_TEMP, CREATED_DT)
			VALUES ('_*_" . time() . "', $rep_used_id, $rc_analysis, $rc_collect, $sqlTrue, $created_dt)";
	audit_setup_gen('REPORT', 'REPORT_ID', 0, '', '');
	$report_id = sql_execute($sql, true); # audited
	#dprint("sql_create_custom_report() returning new ID of $report_id"); #
	return $report_id;
} # sql_create_custom_report()

function sql_get_report($report_id)
{
	$sql = "SELECT REPORT_ID, REP_NAME, REP_TEMP, REP_USER_ID, REP_ANALYSIS, REP_COLLECT,
				CLIENT_PER_SHEET, MONTH_RCVD, SUBT_BY_YEAR, REP_JOB_STATUS, REP_PAYMENTS,
				RUN_RX_DT_FR, RUN_RX_DT_TO, RUN_P1_DT_FR, RUN_P1_DT_TO, OBSOLETE
			FROM REPORT WHERE REPORT_ID=$report_id";
	sql_execute($sql);
	$custom = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$custom = $newArray;
	#dprint("sql_get_report($report_id) returning " . print_r($custom,1));#
	return $custom;

} # sql_get_report()

function sql_get_clients_for_report($report_id, $only_in=true, $all_clients=false, $sort='')
{
	# Build two lists of clients - one that are linked to the given report, and one that are not.
	# If $all_clients is false then the list of clients-out should only contain Collect clients.
	# If $only_in is true then only get clients_in i.e. clients linked to the report.
	# Note $only_is is true when a report is run, but false when a report is being edited.

	global $sqlFalse;

	$clients_in = array();
	if ($only_in)
	{
		# Used for when a report is run
		$sql = "SELECT C.CLIENT2_ID
				FROM CLIENT2 AS C LEFT JOIN REPORT_CLIENT_LINK AS L ON L.CLIENT2_ID=C.CLIENT2_ID
				WHERE L.REPORT_ID=$report_id AND C.C_ARCHIVED=$sqlFalse
				ORDER BY L.SORT_ORDER
				";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$clients_in[] = $newArray['CLIENT2_ID'];
		return $clients_in;
	}

	sql_encryption_preparation('CLIENT2');

	$ids = array();
	$sql = "SELECT C.CLIENT2_ID, C.C_COLLECT, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . "
			FROM CLIENT2 AS C LEFT JOIN REPORT_CLIENT_LINK AS L ON L.CLIENT2_ID=C.CLIENT2_ID
			WHERE L.REPORT_ID=$report_id AND C.C_ARCHIVED=$sqlFalse
			ORDER BY L.SORT_ORDER
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$clients_in[] = array('CLIENT2_ID' => $newArray['CLIENT2_ID'], 'C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME']);
		$ids[] = $newArray['CLIENT2_ID'];
	}
	#dprint("$sql<br>yields " . print_r($ids,1) . "<br>and " . print_r($clients_in,1));#

	$clients_out = array();
	$sql = "SELECT C.CLIENT2_ID, C.C_COLLECT, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . "
			FROM CLIENT2 AS C
			" . ((0 < count($ids)) ? ("WHERE C.CLIENT2_ID NOT IN (" . implode(',', $ids) . ") AND C.C_ARCHIVED=$sqlFalse") : '') . "
			ORDER BY " . (($sort == 'code') ? "CASE WHEN C.C_CODE > 0 THEN C.C_CODE ELSE 99999 END" : sql_decrypt('C.C_CO_NAME')) . "
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if ($all_clients || ($newArray['C_COLLECT'] == 1))
			$clients_out[] = array('CLIENT2_ID' => $newArray['CLIENT2_ID'], 'C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME']);
	}
	#dprint("$sql<br>yields " . print_r($clients_out,1));#

	return array($clients_in, $clients_out);

} # sql_get_clients_for_report()

function sql_get_fields_for_report($report_id, $only_in=true, $rc_analysis=false, $rc_collect=false)
{
	# Build two lists of report fields - one that are linked to the given report, and one that are not.
	# If $only_in is true then only get fields_in i.e. fields linked to the report.
	# Note $only_is is true when a report is run, but false when a report is being edited.

	global $sqlTrue;

	#dprint("sql_get_fields_for_report($report_id, $only_in, $rc_analysis, $rc_collect)");#

	$fields_in = array();
	if ($only_in)
	{
		# Used for when a report is run
		$sql = "SELECT F.RF_CODE
				FROM REPORT_FIELD_SD AS F LEFT JOIN REPORT_FIELD_LINK AS L ON L.REPORT_FIELD_ID=F.REPORT_FIELD_ID
				WHERE L.REPORT_ID=$report_id
				ORDER BY L.SORT_ORDER
				";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$fields_in[] = $newArray['RF_CODE'];
		#dprint("$sql --> " . print_r($fields_in,1));#
		return $fields_in;
	}

	$ids = array();
	$sql = "SELECT F.REPORT_FIELD_ID, F.RF_DESCR
			FROM REPORT_FIELD_SD AS F LEFT JOIN REPORT_FIELD_LINK AS L ON L.REPORT_FIELD_ID=F.REPORT_FIELD_ID
			WHERE L.REPORT_ID=$report_id
			ORDER BY L.SORT_ORDER
			";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$fields_in[] = $newArray;
		$ids[] = $newArray['REPORT_FIELD_ID'];
	}
	#dprint("$sql<br>yields " . print_r($ids,1) . "<br>and " . print_r($fields_in,1));#

	$fields_out = array();
	$sql = "SELECT F.REPORT_FIELD_ID, F.RF_DESCR
			FROM REPORT_FIELD_SD AS F
			WHERE " . ((0 < count($ids)) ? "(F.REPORT_FIELD_ID NOT IN (" . implode(',', $ids) . ")) AND " : '') . "
				(" . ($rc_analysis ? 'F.RF_ANALYSIS' : 'F.RF_JOB_DETAIL') . "=$sqlTrue) AND
				(" . ($rc_collect ? 'F.RF_COLL' : 'F.RF_TRACE') . "=$sqlTrue) AND
				(F.RF_SEL=$sqlTrue)
			ORDER BY F.SORT_ORDER, F.RF_DESCR
			";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$fields_out[] = $newArray;
	#dprint("$sql<br>yields " . print_r($fields_out,1));#

	return array($fields_in, $fields_out);

} # sql_get_fields_for_report()

function u_rep_custom_get()
{
	global $USER;

	$rc = 0;
	if (0 < $USER['USER_ID'])
	{
		$sql = "SELECT U_REP_CUSTOM FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID={$USER['USER_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$rc = $newArray[0];
	}
	return $rc;
}

function u_rep_custom_set($rc)
{
	global $USER;

	if (0 < $USER['USER_ID'])
	{
		$sql = "UPDATE USERV SET U_REP_CUSTOM=$rc WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}
}

function u_report_id_get()
{
	global $USER;

	$rc = 0;
	if (0 < $USER['USER_ID'])
	{
		$sql = "SELECT U_REPORT_ID FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID={$USER['USER_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$rc = intval($newArray[0]);
	}
	return $rc;
}

function u_report_id_set($rid)
{
	global $USER;

	if (0 < $USER['USER_ID'])
	{
		$sql = "UPDATE USERV SET U_REPORT_ID=$rid WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}
}

function u_rep_global_get()
{
	global $USER;

	$rc = 0;
	if (0 < $USER['USER_ID'])
	{
		$sql = "SELECT U_REP_GLOBAL FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID={$USER['USER_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$rc = $newArray[0];
	}
	return $rc;
}

function u_rep_global_set($rc)
{
	global $USER;

	if (0 < $USER['USER_ID'])
	{
		$sql = "UPDATE USERV SET U_REP_GLOBAL=$rc WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}
}

function u_rep_analysis_get()
{
	global $USER;

	$rc = 0;
	if (0 < $USER['USER_ID'])
	{
		$sql = "SELECT U_REP_ANALYSIS FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID={$USER['USER_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$rc = $newArray[0];
	}
	return $rc;
}

function u_rep_analysis_set($rc)
{
	global $USER;

	if (0 < $USER['USER_ID'])
	{
		$sql = "UPDATE USERV SET U_REP_ANALYSIS=$rc WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}
}

function u_rep_collect_get()
{
	global $USER;

	$rc = 0;
	if (0 < $USER['USER_ID'])
	{
		$sql = "SELECT U_REP_COLLECT FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID={$USER['USER_ID']}";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$rc = $newArray[0];
	}
	return $rc;
}

function u_rep_collect_set($rc)
{
	global $USER;

	if (0 < $USER['USER_ID'])
	{
		$sql = "UPDATE USERV SET U_REP_COLLECT=$rc WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}
}

function property_type_txt($pt_int)
{
	switch (intval($pt_int))
	{
		case 1:
			return "Owner";
		case 2:
			return "Tenant";
		case 3:
			return "Parents";
		case 4:
			return "Forces";
		case 5:
			return "Other";
		default:
			return "** unrecognised property type number {$pt_int} **";
	}
	return "** property_type_txt() error **";
}

function property_type_int($pt_txt)
{
	switch (strtolower(trim($pt_txt)))
	{
		case "owner":
			return 1;
		case "tenant":
			return 2;
		case "parents":
			return 3;
		case "forces":
			return 4;
		case "other":
			return 5;
		default:
			return -1;
	}
	return -2;
}

function get_add_phone($add_num, $n_d)
{
	# Get "additional phone" number for a given job.
	# Used by reports_custom.php/run_jobdet_gen and reports.php/rep_cf_noncol()

	global $add_phones; # must be created by caller
	global $job_id; # must be set by caller

	if (array_key_exists($job_id, $add_phones))
	{
		$phone_list = $add_phones[$job_id];
		if (array_key_exists($add_num-1, $phone_list))
		{
			$pair = $phone_list[$add_num-1];
			if ($n_d == 'n')
				return $pair[0];
			elseif ($n_d == 'd')
				return $pair[1];
		}
	}
	return '';
} # get_add_phone()

function get_any_phone($any_num, $n_d)
{
	# Get "home" (0) or "additional phone" (1,2,...) number for a given job.
	# Used by reports_custom.php/run_jobdet_gen()

	global $all_phones; # must be created by caller
	global $job_id; # must be set by caller

	if (array_key_exists($job_id, $all_phones))
	{
		$phone_list = $all_phones[$job_id];
		if (array_key_exists($any_num, $phone_list))
		{
			$pair = $phone_list[$any_num];
			if ($n_d == 'n')
				return $pair[0];
			elseif ($n_d == 'd')
				return $pair[1];
		}
	}
	return '';
} # get_any_phone()

function get_any_email($any_num, $n_d)
{
	# Get email address for a given job, or get all if $any_num == -1
	# Used by reports_custom.php/run_jobdet_gen()

	global $all_emails; # must be created by caller
	global $job_id; # must be set by caller

	if (array_key_exists($job_id, $all_emails))
	{
		$email_list = $all_emails[$job_id];
		if ($any_num == -1)
		{
			$ems = '';
			foreach ($email_list as $ee)
				$ems .= ($ems ? ', ' : '') . $ee[0];
			return $ems;
		}
		elseif (array_key_exists($any_num, $email_list))
		{
			$pair = $email_list[$any_num];
			if ($n_d == 'n')
				return $pair[0];
			elseif ($n_d == 'd')
				return $pair[1];
		}
	}
	return '';
} # get_any_email()

function sql_add_subject($job_id)
{
	global $sqlFalse;

	sql_encryption_preparation('JOB_SUBJECT');
	$js_lastname = sql_encrypt('NEW SUBJECT', false, 'JOB_SUBJECT');

	$fields = "JOB_ID,  JS_PRIMARY, JS_LASTNAME,  IMPORTED";
	$values = "$job_id, $sqlFalse,  $js_lastname, $sqlFalse";

	$sql = "INSERT INTO JOB_SUBJECT ($fields) VALUES ($values)";
	dprint("sql_add_subject($job_id): $sql");#
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', 0, '', '');
	$job_subject_id = sql_execute($sql, true); # audited
	dprint("sql_add_subject($job_id): \$job_subject_id=$job_subject_id");#

	sql_update_job($job_id);

	return $job_subject_id;
}

function sql_add_phone($job_id)
{
	global $sqlFalse;

	sql_encryption_preparation('JOB_PHONE');
	$jp_phone = sql_encrypt('NEW PHONE', false, 'JOB_PHONE');

	$fields = "JOB_ID,  JP_PHONE,  JP_PRIMARY_P,  IMPORTED,  IMP_PH";
	$values = "$job_id, $jp_phone, $sqlFalse,     $sqlFalse, $sqlFalse";

	$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
	dprint("sql_add_phone($job_id): $sql");#
	audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
	$job_phone_id = sql_execute($sql, true); # audited
	dprint("sql_add_phone($job_id): \$job_phone_id=$job_phone_id");#

	sql_update_job($job_id);

	return $job_phone_id;
}

function sql_add_email($job_id)
{
	global $sqlFalse;

	sql_encryption_preparation('JOB_PHONE');
	$jp_email = sql_encrypt('NEW EMAIL', false, 'JOB_PHONE');

	$fields = "JOB_ID,  JP_EMAIL,  JP_PRIMARY_E,  IMPORTED,  IMP_PH";
	$values = "$job_id, $jp_email, $sqlFalse,     $sqlFalse, $sqlFalse";

	$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
	dprint("sql_add_email($job_id): $sql");#
	audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
	$job_email_id = sql_execute($sql, true); # audited
	dprint("sql_add_email($job_id): \$job_email_id=$job_email_id");#

	sql_update_job($job_id);

	return $job_email_id;
}

function sql_add_note($job_id, $note, $update_job=true, $jn_added_dt='')
{
	# $jn_added_dt added 03/12/18 for CR #VIL-1

	global $sqlFalse;
	global $sqlNow;
	global $USER;

	sql_encryption_preparation('JOB_NOTE');
	$j_note = sql_encrypt($note, false, 'JOB_NOTE');

	if ($jn_added_dt)
		$jn_added_dt = "'{$jn_added_dt}'";
	else
		$jn_added_dt = $sqlNow;

	$fields = "JOB_ID,  J_NOTE,  IMPORTED,  JN_ADDED_ID,        JN_ADDED_DT";
	$values = "$job_id, $j_note, $sqlFalse, {$USER['USER_ID']}, $jn_added_dt";

	$sql = "INSERT INTO JOB_NOTE ($fields) VALUES ($values)";
	dprint("sql_add_note($job_id): $sql");#
	audit_setup_job($job_id, 'JOB_NOTE', 'JOB_NOTE_ID', 0, '', '');
	$job_note_id = sql_execute($sql, true); # audited
	dprint("sql_add_note($job_id): \$job_note_id=$job_note_id");#

	if ($update_job)
		sql_update_job($job_id);

	return $job_note_id;
}

function sql_add_payment($job_id)
{
	global $id_ACTIVITY_par;
	global $sqlNow;

	$sql = "SELECT JC_PERCENT FROM JOB WHERE JOB_ID=$job_id";
	$col_percent = 0.0;
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$col_percent = floatval($newArray[0]);

	$fields = "JOB_ID,  COL_DT_RX, COL_PERCENT";
	$values = "$job_id, $sqlNow,   $col_percent";

	$sql = "INSERT INTO JOB_PAYMENT ($fields) VALUES ($values)";
	dprint("sql_add_payment($job_id): $sql");#
	audit_setup_job($job_id, 'JOB_PAYMENT', 'JOB_PAYMENT_ID', 0, '', '');
	$job_payment_id = sql_execute($sql, true); # audited
	dprint("sql_add_payment($job_id): \$job_payment_id=$job_payment_id");#

	sql_update_paid_so_far($job_id);

	$sql = "SELECT JC_LETTER_MORE, JC_LETTER_TYPE_ID, JC_LETTER_DELAY FROM JOB WHERE JOB_ID=$job_id";
	sql_execute($sql);
	$jc_letter_more = 0;
	$jc_letter_type_id = 0;
	$jc_letter_delay = 0;
	while (($newArray = sql_fetch()) != false)
	{
		$jc_letter_more = intval($newArray[0]);
		$jc_letter_type_id = intval($newArray[1]);
		$jc_letter_delay = intval($newArray[2]);
	}
	if ($jc_letter_more && $jc_letter_type_id && (!$jc_letter_delay))
	{
		$sql = "UPDATE JOB SET JC_LETTER_DELAY=30 WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_LETTER_DELAY', $jc_letter_delay);
		sql_execute($sql, true); #audited
	}

	sql_add_activity($job_id, 1, $id_ACTIVITY_par, false); # don't call sql_update_job()
	sql_update_job($job_id);

	return $job_payment_id;
}

function sql_add_trace_invoice($job_id)
{
	# Add invoice or credit to trace job

	global $sqlFalse;

	$sql = "SELECT CLIENT2_ID, J_S_INVS FROM JOB WHERE JOB_ID=$job_id";#, J_FEE
	sql_execute($sql);
	$client2_id = 0;
	#$j_fee = 0.0;
	$j_s_invs = 0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$client2_id = $newArray['CLIENT2_ID'];
		#$j_fee = 1.0 * $newArray['J_FEE'];
		$j_s_invs = intval($newArray['J_S_INVS']);
	}
	if ($j_s_invs == 1)
	{
		dprint("sql_add_trace_invoice($job_id): this job uses Statement Invoicing - aborting!", true);
		return 0;
	}

	$sql = "SELECT INV_BILLING_ID, BL_COST FROM INV_BILLING
			WHERE (JOB_ID=$job_id) AND (BL_COST IS NOT NULL) AND (INVOICE_ID IS NULL) AND (OBSOLETE=$sqlFalse)";
	sql_execute($sql);
	$billings = array();
	$sum_bl_cost = 0.0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$billings[] = $newArray['INV_BILLING_ID'];
		$sum_bl_cost += floatval($newArray['BL_COST']);
	}
	if (count($billings) == 0)
	{
		dprint("sql_add_trace_invoice($job_id): there are no billing lines that are not already attached to an invoice - aborting!", true);
		return 0;
	}
	if ($sum_bl_cost < 0.0)
	{
		$is_credit = true;
		#$sum_bl_cost = -$sum_bl_cost;
	}
	else
		$is_credit = false;

	$vat_rate = 0.0;
	$add_vat = intval(sql_select_single("SELECT C_VAT FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));
	if ($add_vat)
	{
		$temp_vat = floatval(misc_info_read('*', 'VAT_RATE'));
		if ($temp_vat != 0.0)
			$vat_rate = 0.01 * $temp_vat;
	}

	$inv_net = $sum_bl_cost;
	$inv_vat = $inv_net * $vat_rate;

	$now = date_now_sql();
	$now_sql = "'$now'";

	if ($is_credit)
	{
		$due_sql = 'NULL';
		$inv_type = 'C';
	}
	else
	{
		$due_ep = date_add_months_kdb(time(), 1); # add on one month
		$due_dt = date_from_epoch(true, $due_ep, false, false, true);
		$due_sql = "'$due_dt'";
		$inv_type = 'I';
	}

	$inv_num = inv_num_next();

	$fields = "INV_NUM,  INV_SYS, INV_TYPE,    CLIENT2_ID,  INV_NET,  INV_VAT,  INV_DT,   INV_DUE_DT, INV_STMT";
	$values = "$inv_num, 'T',     '$inv_type', $client2_id, $inv_net, $inv_vat, $now_sql, $due_sql,   $sqlFalse";

	$sql = "INSERT INTO INVOICE ($fields) VALUES ($values)";
	dprint("sql_add_trace_invoice($job_id): $sql");#
	audit_setup_job($job_id, 'INVOICE', 'INVOICE_ID', 0, '', '');
	$invoice_id = sql_execute($sql, true); # audited
	dprint("sql_add_trace_invoice($job_id)): \$invoice_id=$invoice_id");#

	foreach ($billings as $ibid)
	{
		$sql = "UPDATE INV_BILLING SET INVOICE_ID=$invoice_id WHERE INV_BILLING_ID=$ibid";
		audit_setup_job($job_id, 'INV_BILLING', 'INV_BILLING_ID', $ibid, 'INVOICE_ID', $invoice_id);
		sql_execute($sql, true); # audited

		$sql = "UPDATE INV_BILLING SET INV_NUM=$inv_num WHERE INV_BILLING_ID=$ibid";
		audit_setup_job($job_id, 'INV_BILLING', 'INV_BILLING_ID', $ibid, 'INV_NUM', $inv_num);
		sql_execute($sql, true); # audited
	}

	sql_update_job($job_id);

	return $invoice_id;

} # sql_add_trace_invoice()

function sql_add_billing($sys, $job_id)
{
	global $sqlFalse;

	if ($sys != 't')
	{
		dprint("sql_add_billing($sys,$job_id): Unrecognised sys \"$sys\" - aborting!", true);
		return 0;
	}
	$sys = strtoupper($sys);

	$bl_cost = 0.0;
	$bl_descr = '';
	$sql = "SELECT COUNT(*) FROM INV_BILLING WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse";
	$count = 0;
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	if ($count == 0)
	{
		$sql = "SELECT J.JT_FEE_Y, J.JT_FEE_N, J.JT_SUCCESS, T.JT_TYPE, J.JT_CREDIT
				FROM JOB AS J LEFT JOIN JOB_TYPE_SD AS T ON T.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
				WHERE J.JOB_ID=$job_id";
		$success = 0;
		$credit = 0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$success = "{$newArray[2]}";
			if ($success == "1")
			{
				$bl_cost = floatval($newArray[0]);
				$bl_descr = $newArray[3] . " - success";
			}
			elseif ($success == "0")
			{
				$bl_cost = floatval($newArray[1]);
				$bl_descr = $newArray[3] . " - no";
			}
			$credit = "{$newArray[4]}";
			if ($credit == 1)
				$scalar = -1; # refund
			else if ($credit == -1)
			{
				$scalar = 0; # FOC
				$bl_descr .= " (Free of charge)";
			}
			else
				$scalar = 1;
			$bl_cost *= $scalar;
		}
	}
	$fields = "JOB_ID,  BL_SYS, BL_COST,  BL_DESCR";
	$values = "$job_id, '$sys', $bl_cost, '{$bl_descr}'";

	$sql = "INSERT INTO INV_BILLING ($fields) VALUES ($values)";
	dprint("sql_add_billing($sys,$job_id): $sql");#
	audit_setup_job($job_id, 'INV_BILLING', 'INV_BILLING_ID', 0, '', '');
	$inv_billing_id = sql_execute($sql, true); # audited
	dprint("sql_add_billing($sys,$job_id): \$inv_billing_id=$inv_billing_id");#

	sql_update_job($job_id);

	return $inv_billing_id;
} # sql_add_billing()

function sql_delete_billing($sys, $job_id, $billing_id)
{
	global $sqlTrue;

	if ($sys != 't')
	{
		dprint("sql_delete_billing($sys,$job_id,$billing_id): Unrecognised sys \"$sys\" - aborting!", true);
		return 0;
	}
	if (!(0 < $job_id))
	{
		dprint("sql_delete_billing($sys,$job_id,$billing_id): Bad Job ID \"$job_id\" - aborting!", true);
		return 0;
	}
	if (!(0 < $billing_id))
	{
		dprint("sql_delete_billing($sys,$job_id,$billing_id): Bad Billing ID \"$billing_id\" - aborting!", true);
		return 0;
	}
	$check_job_id = sql_select_single("SELECT JOB_ID FROM INV_BILLING WHERE INV_BILLING_ID=$billing_id");
	if ($check_job_id != $job_id)
	{
		dprint("sql_delete_billing($sys,$job_id,$billing_id): Job ID \"$job_id\" doesn't match Job ID ($check_job_id) of Billing Item \"$billing_id\" - aborting!", true);
		return 0;
	}

	$sql = "UPDATE INV_BILLING SET OBSOLETE=$sqlTrue WHERE INV_BILLING_ID=$billing_id";
	dprint($sql);#
	audit_setup_job($job_id, 'INV_BILLING', 'INV_BILLING_ID', $billing_id, 'OBSOLETE', $sqlTrue);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # sql_delete_billing()

function sql_delete_payment($job_id, $payment_id)
{
	global $sqlTrue;

	$sql = "UPDATE JOB_PAYMENT SET OBSOLETE=$sqlTrue WHERE JOB_PAYMENT_ID=$payment_id";
	dprint($sql);#
	audit_setup_job($job_id, 'JOB_PAYMENT', 'JOB_PAYMENT_ID', $payment_id, 'OBSOLETE', $sqlTrue);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # sql_delete_payment()

function sql_add_activity($job_id, $act_new_count, $activity_id=0, $update_job=true)
{
	global $sqlFalse;
	global $sqlNow;

	for ($ii = 0; $ii < $act_new_count; $ii++)
	{
		$fields = "JOB_ID,  ACTIVITY_ID,  JA_DT,   IMPORTED";
		$values = "$job_id, $activity_id, $sqlNow, $sqlFalse";

		$sql = "INSERT INTO JOB_ACT ($fields) VALUES ($values)";
		dprint("sql_add_activity($job_id): $sql");#
		audit_setup_job($job_id, 'JOB_ACT', 'JOB_ACT_ID', 0, '', '');
		$job_act_id = sql_execute($sql, true); # audited
		dprint("sql_add_activity($job_id): \$job_act_id=$job_act_id");#
	}

	if ($update_job)
		sql_update_job($job_id);

	return $job_act_id;
}

function sql_add_arrange($job_id)
{
	# Add a Collection Arrangement to the specified Job i.e in the JOB table.
	# Also add it to the JOB_ARRANGE table.

	global $sqlFalse;
	global $sqlNow;

	$sql = "UPDATE JOB SET JC_INSTAL_AMT=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_INSTAL_AMT', 'NULL');
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET JC_INSTAL_DT_1=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_INSTAL_DT_1', 'NULL');
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET JC_INSTAL_FREQ=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_INSTAL_FREQ', 'NULL');
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET JC_PAYMENT_METHOD_ID=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_PAYMENT_METHOD_ID', 'NULL');
	sql_execute($sql, true); # audited

	$fields = "JOB_ID,  JA_DT,   IMPORTED";
	$values = "$job_id, $sqlNow, $sqlFalse";

	$sql = "INSERT INTO JOB_ARRANGE ($fields) VALUES ($values)";
	dprint("sql_add_arrange($job_id): $sql");#
	audit_setup_job($job_id, 'JOB_ARRANGE', 'JOB_ARRANGE_ID', 0, '', '');
	$job_arrange_id = sql_execute($sql, true); # audited
	dprint("sql_add_arrange($job_id): \$job_arrange_id=$job_arrange_id");#

	sql_update_job($job_id);

	return $job_arrange_id;
}

function sql_get_letter_types_for_client($client2_id, $system, $lt_non_man=false)
{
	global $sqlTrue;

	$system_test = '';
	$non_man_test = '';
	if ($system == 'c')
	{
		$system_test = 'AND (JT_T_JOB_TYPE_ID IS NULL)';
		$non_man_test = ($lt_non_man ? "AND (LT_NON_MAN=$sqlTrue)" : '');
	}
	elseif ($system == 't')
		$system_test = 'AND (JT_T_JOB_TYPE_ID IS NOT NULL)';

	if (0 < $client2_id)
		$sql = "SELECT K.LETTER_TYPE_ID, L.LETTER_NAME
				FROM CLIENT_LETTER_LINK AS K
				INNER JOIN LETTER_TYPE_SD AS L ON L.LETTER_TYPE_ID=K.LETTER_TYPE_ID $system_test $non_man_test
				WHERE K.CLIENT2_ID=$client2_id
				ORDER BY LETTER_TYPE_ID";
	else
	{
		$sql = "SELECT LETTER_TYPE_ID, LETTER_NAME
				FROM LETTER_TYPE_SD
				";
		if ($system_test)
			$sql .= "WHERE " . str_replace('AND ', '', $system_test) . $non_man_test;
		$sql .= "ORDER BY LETTER_TYPE_ID";
	}
	#dprint($sql);#
	sql_execute($sql);
	$lt = array();
	while (($newArray = sql_fetch()) != false)
		$lt[$newArray[0]] = $newArray[1];
	return $lt;
}

function sql_update_adjustment($job_id)
{
	# Update JOB.JC_ADJUSTMENT from the JOB_PAYMENT records.

	global $id_ROUTE_cspent;
	global $sqlFalse;

	$sql = "SELECT SUM(COALESCE(COL_AMT_RX,0.0)) FROM JOB_PAYMENT WHERE (JOB_ID=$job_id) AND (OBSOLETE=$sqlFalse) AND (COL_PAYMENT_ROUTE_ID=$id_ROUTE_cspent)"; # Use COALESCE to get rid of warning message "Warning: odbc_fetch_row(): No tuples available at this result index in /var/www/vhosts/rdresearch.co.uk/httpdocs/viltest/lib_sql.php on line 536" on RD server (not local pc)
	$sum = 0.0;
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$sum = floatval($newArray[0]);

	$sql = "SELECT JC_ADJUSTMENT FROM JOB WHERE JOB_ID=$job_id";
	$jca = 0.0;
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$jca = floatval($newArray[0]);

	if ($jca != $sum)
	{
		$sql = "UPDATE JOB SET JC_ADJUSTMENT=$sum WHERE JOB_ID=$job_id";
		#dlog($sql);
		sql_execute($sql, true); # audited
		sql_update_job($job_id);
	}
}

function sql_add_job($client2_id, $type)
{
	global $id_JOB_TYPE_trc;
	global $sqlFalse;
	global $sqlTrue;

	$job_id = 0;
	if (0 < $client2_id)
	{
		if (($type == 't') || ($type == 'c'))
		{
			if ($type == 't')
			{
				$jt_job = $sqlTrue;
				$jc_job = $sqlFalse;
				$j_s_invs = intval(sql_select_single("SELECT S_INVS_TRACE FROM CLIENT2 WHERE CLIENT2_ID=$client2_id")) ? $sqlTrue : $sqlFalse;
			}
			else
			{
				$jt_job = $sqlFalse;
				$jc_job = $sqlTrue;
				$j_s_invs = $sqlTrue;
			}

			$j_vilno = vilno_next();
			$j_sequence = sequence_next();
			$now = "'" . date_now_sql() . "'";

			$fields = "CLIENT2_ID,  J_VILNO,  J_SEQUENCE,  J_OPENED_DT, J_S_INVS,  IMPORTED,  JT_JOB,  JC_JOB ";
			$values = "$client2_id, $j_vilno, $j_sequence, $now,        $j_s_invs, $sqlFalse, $jt_job, $jc_job";

			if ($type == 'c')
			{
				$jc_letter_type_id = sql_get_letter_seq_first_client($client2_id);
				$comm_percent = floatval(sql_select_single("SELECT COMM_PERCENT FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));
				$fields .= ", J_AVAILABLE, JC_LETTER_MORE, JC_LETTER_TYPE_ID,  JC_PERCENT   ";
				$values .= ", $sqlTrue,    $sqlTrue,       $jc_letter_type_id, $comm_percent";
			}
			elseif ($type == 't')
			{
				$sql = "SELECT TR_FEE, NT_FEE FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
				sql_execute($sql);
				$tr_fee = 0.0;
				$nt_fee = 0.0;
				while (($newArray = sql_fetch()) != false)
				{
					$tr_fee = floatval($newArray[0]);
					$nt_fee = floatval($newArray[1]);
				}
				$fields .= ", JT_JOB_TYPE_ID,   JT_FEE_Y, JT_FEE_N";
				$values .= ", $id_JOB_TYPE_trc, $tr_fee,  $nt_fee ";
			}

			$sql = "INSERT INTO JOB ($fields) VALUES ($values)";
			#dlog($sql);#
			audit_setup_gen('JOB', 'JOB_ID', 0, '', '');
			$job_id = sql_execute($sql, true); # audited

			sql_encryption_preparation('JOB_SUBJECT');
			$lastname = sql_encrypt('LASTNAME', false, 'JOB_SUBJECT');

			$fields = "JOB_ID,  JS_PRIMARY, JS_LASTNAME, IMPORTED";
			$values = "$job_id, $sqlTrue,   $lastname,   $sqlFalse";

			$sql = "INSERT INTO JOB_SUBJECT ($fields) VALUES ($values)";
			audit_setup_gen('JOB_SUBJECT', 'JOB_SUBJECT_ID', 0, '', '');
			$job_subject_id = sql_execute($sql, true); # audited

			dprint("sql_add_job(): Created job $job_id and subject $job_subject_id");

			#sql_update_client($client2_id);
		}
		else
			dprint("sql_add_job($client2_id, $type): bad type", true);
	}
	else
		dprint("sql_add_job($client2_id, $type): bad client ID", true);

	dprint("sql_add_job($client2_id, $type) returning new JOB_ID of $job_id"); #
	return $job_id;

} # sql_add_job()

function vilno_next()
{
	# Get the next VIL number for a new job
	$large_oddities = 11189645; # some odd jobs have VIL No of 11,189,645 and over, which we should ignore until all numbers under this have been used up.
	for ($loop = 0; $loop < 2; $loop++)
	{
		$sql = "SELECT MAX(J_VILNO) FROM JOB";
		if ($loop == 0)
			$sql .= " WHERE J_VILNO < $large_oddities";
		#dprint("loop=$loop, sql=$sql");#
		sql_execute($sql);
		$j_vilno = -1;
		while (($newArray = sql_fetch()) != false)
			$j_vilno = $newArray[0];
		$j_vilno++;
		if (($loop == 0) && ($j_vilno < $large_oddities))
			break; # this one is fine
		# otherwise, go round the loop again without the $large_oddities restriction
	}
	return $j_vilno;
}

function sequence_next()
{
	# Get the next sequence number for a new job
	$sql = "SELECT MAX(J_SEQUENCE) FROM JOB";
	sql_execute($sql);
	$j_sequence = -1;
	while (($newArray = sql_fetch()) != false)
		$j_sequence = $newArray[0];
	return $j_sequence + 1;
}

function inv_num_next()
{
	# Get the next invoice/credit number for a new document
	$large_oddities = 900000; # some odd invoices have INV_NUM of 900,000 and over, which we should ignore until all numbers under this have been used up.
	for ($loop = 0; $loop < 2; $loop++)
	{
		$sql = "SELECT MAX(INV_NUM) FROM INVOICE";
		if ($loop == 0)
			$sql .= " WHERE INV_NUM < $large_oddities";
		#dprint("loop=$loop, sql=$sql");#
		sql_execute($sql);
		$inv_num = -1;
		while (($newArray = sql_fetch()) != false)
			$inv_num = $newArray[0];
		$inv_num++;
		if (($loop == 0) && ($inv_num < $large_oddities))
			break; # this one is fine
		# otherwise, go round the loop again without the $large_oddities restriction
	}
	return $inv_num;
}

function sql_get_client_balance_info($client2_id)
{
	global $sqlFalse;

	$invoices = 0.0;
	$credits = 0.0;
	$focs = 0.0;
	$receipts = 0.0;
	$adjusts = 0.0;

	$sql = "SELECT INV_TYPE, INV_NET, INV_VAT FROM INVOICE WHERE (OBSOLETE=$sqlFalse) AND (CLIENT2_ID=$client2_id)";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amount = floatval($newArray['INV_NET']) + floatval($newArray['INV_VAT']);
		if ($newArray['INV_TYPE'] == 'I')
			$invoices -= $amount; # invoice amounts are stored as positive numbers
		elseif ($newArray['INV_TYPE'] == 'C')
			$credits -= $amount; # credit amounts are stored as negative numbers
		elseif ($newArray['INV_TYPE'] == 'F')
			$focs += $amount; # FOC amounts are stored as both positive and negative numbers!
	}

	$sql = "SELECT RC_ADJUST, RC_AMOUNT FROM INV_RECP WHERE CLIENT2_ID=$client2_id AND OBSOLETE=$sqlFalse";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amount = floatval($newArray['RC_AMOUNT']);
		if ($newArray['RC_ADJUST'] == 0)
			$receipts += $amount; # receipt amounts are stored as mostly positive numbers
		else
			$adjusts += $amount; # adjustment amounts are stored as both positive and negative numbers, natrually
	}

	$balance = 0.0 + $invoices + $credits + $focs + $receipts + $adjusts;
	return array($balance, $invoices, $credits, $focs, $receipts, $adjusts);

} # sql_get_client_balance_info()

function sql_get_receipts_and_allocs($client2_id)
{
	global $sqlFalse;

	$sql = "SELECT R.INV_RECP_ID, R.RC_ADJUST, R.RC_NUM, R.RC_AMOUNT, R.RC_DT, SUM(COALESCE(A.AL_AMOUNT,0.0)) AS SUM_AL_AMOUNT
			FROM INV_RECP AS R
			LEFT JOIN INV_ALLOC AS A ON A.INV_RECP_ID=R.INV_RECP_ID
			WHERE (R.CLIENT2_ID=$client2_id) AND (R.OBSOLETE=$sqlFalse)
			GROUP BY R.INV_RECP_ID, R.RC_ADJUST, R.RC_NUM, R.RC_AMOUNT, R.RC_DT
			ORDER BY R.RC_DT DESC, R.INV_RECP_ID DESC";
	#dprint($sql);#
	sql_execute($sql);

	$receipts = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$receipts[] = $newArray;

//	$sql = "SELECT R.INV_RECP_ID, R.RC_NUM, R.RC_AMOUNT, R.RC_DT
//			FROM INV_RECP AS R
//			WHERE (R.CLIENT2_ID=$client2_id) AND (R.RC_ADJUST=$sqlFalse)
//			ORDER BY R.RC_DT DESC";
//	#dprint($sql);#
//	sql_execute($sql);
//
//	$receipts = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//		$receipts[] = $newArray;
//
//	$ii_max = count($receipts);
//	for ($ii = 0; $ii < $ii_max; $ii++)
//	{
//		$sql = "SELECT SUM(AL_AMOUNT) FROM INV_ALLOC WHERE INV_RECP_ID={$receipts[$ii]['INV_RECP_ID']}";
//		#dprint($sql);#
//		sql_execute($sql);
//		while (($newArray = sql_fetch()) != false)
//			$receipts[$ii]['SUM_AL_AMOUNT'] = $newArray[0];
//	}

	return $receipts;
} # sql_get_receipts_and_allocs()

function sql_get_receipt_allocs($inv_recp_id)
{
	global $sqlFalse;

	$sql = "SELECT A.INV_ALLOC_ID, A.AL_AMOUNT, I.INVOICE_ID, I.INV_NUM, I.INV_DT, I.INV_NET, I.INV_VAT, SUM(COALESCE(A2.AL_AMOUNT,0.0)) AS SUM_A2_AMOUNT
			FROM INV_ALLOC AS A
			LEFT JOIN INVOICE AS I ON I.INVOICE_ID=A.INVOICE_ID
			LEFT JOIN INV_ALLOC AS A2 ON A2.INVOICE_ID=I.INVOICE_ID
			WHERE (I.OBSOLETE=$sqlFalse) AND (A.INV_RECP_ID=$inv_recp_id)
			GROUP BY A.INV_ALLOC_ID, A.AL_AMOUNT, I.INVOICE_ID, I.INV_NUM, I.INV_DT, I.INV_NET, I.INV_VAT
			ORDER BY I.INV_DT DESC, I.INVOICE_ID DESC";
	sql_execute($sql);
	$allocs = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['GROSS'] = (floatval($newArray['INV_NET']) + floatval($newArray['INV_VAT']));
		$newArray['OUTSTANDING'] = $newArray['GROSS'] - floatval($newArray['SUM_A2_AMOUNT']);
		if (round(floatval($newArray['OUTSTANDING']), 2) == 0.0)
			$newArray['OUTSTANDING'] = 0.0;
		$allocs[] = $newArray;
	}
	return $allocs;

} # sql_get_receipt_allocs()

function sql_delete_receipt($inv_recp_id)
{
	global $sqlTrue;

	#dprint("sql_delete_receipt($inv_recp_id)");#

	$sql = "SELECT INV_ALLOC_ID, INVOICE_ID FROM INV_ALLOC WHERE INV_RECP_ID=$inv_recp_id";
	sql_execute($sql);
	$allocs = array();
	$invs = array();
	while (($newArray = sql_fetch()) != false)
	{
		$allocs[] = $newArray[0];
		$invs[] = $newArray[1];
	}

	foreach ($allocs as $inv_alloc_id)
	{
		$sql = "DELETE FROM INV_ALLOC WHERE INV_ALLOC_ID=$inv_alloc_id";
		audit_setup_gen('INV_ALLOC', 'INV_ALLOC_ID', $inv_alloc_id, '', '');
		sql_execute($sql, true); # audited
	}

	foreach ($invs as $invoice_id)
		sql_update_inv_paid($invoice_id);

	$sql = "UPDATE INV_RECP SET OBSOLETE=$sqlTrue WHERE INV_RECP_ID=$inv_recp_id";
	audit_setup_gen('INV_RECP', 'INV_RECP_ID', $inv_recp_id, 'OBSOLETE', $sqlTrue);
	sql_execute($sql, true); # audited
} # sql_delete_receipt()

//function sql_delete_invoice($invoice_id)
//{
//	global $sqlTrue;
//
//	#dprint("sql_delete_invoice($inv_recp_id)");#
//
//	$sql = "SELECT INV_ALLOC_ID FROM INV_ALLOC WHERE INVOICE_ID=$invoice_id";
//	sql_execute($sql);
//	$allocs = array();
//	while (($newArray = sql_fetch()) != false)
//		$allocs[] = $newArray[0];
//
//	foreach ($allocs as $inv_alloc_id)
//	{
//		$sql = "DELETE FROM INV_ALLOC WHERE INV_ALLOC_ID=$inv_alloc_id";
//		audit_setup_gen('INV_ALLOC', 'INV_ALLOC_ID', $inv_alloc_id, '', '');
//		sql_execute($sql, true); # audited
//	}
//
//	$sql = "UPDATE INVOICE SET OBSOLETE=$sqlTrue WHERE INVOICE_ID=$invoice_id";
//	audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'OBSOLETE', $sqlTrue);
//	sql_execute($sql, true); # audited
//} # sql_delete_invoice()

function sql_update_inv_paid($invoice_id)
{
	# This function is called automatially whenever an allocation (INV_ALLOC) is inserted, updated or deleted.

	$sql = "SELECT INV_RX FROM INVOICE WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$inv_rx = 0.0;
	while (($newArray = sql_fetch()) != false)
		$inv_rx = floatval($newArray[0]);

	$sql = "SELECT SUM(COALESCE(AL_AMOUNT,0.0)) FROM INV_ALLOC WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$alloc = 0.0;
	while (($newArray = sql_fetch()) != false)
		$alloc += floatval($newArray[0]);

	$paid = max($inv_rx, $alloc);
	$sql = "UPDATE INVOICE SET INV_PAID=$paid WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql); # no need to audit

} # sql_update_inv_paid()

function sql_add_gen_billing_line($invoice_id)
{
	global $sqlFalse;

	$sql = "SELECT INV_NUM FROM INVOICE WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$inv_num = '';
	while (($newArray = sql_fetch()) != false)
		$inv_num = $newArray[0];
	if (!$inv_num)
		$inv_num = 'NULL';

	$sql = "SELECT MAX(COALESCE(BL_LPOS,1)) FROM INV_BILLING WHERE INVOICE_ID=$invoice_id AND OBSOLETE=$sqlFalse";
	sql_execute($sql);
	$bl_lpos = 0;
	while (($newArray = sql_fetch()) != false)
		$bl_lpos = intval($newArray[0]);
	$bl_lpos++;

	$fields = "INVOICE_ID,  INV_NUM,  JOB_ID, BL_SYS, BL_SYS_IMP, BL_LPOS,  IMPORTED";
	$values = "$invoice_id, $inv_num, NULL,   'G',    NULL,       $bl_lpos, $sqlFalse";

	$sql = "INSERT INTO INV_BILLING ($fields) VALUES ($values)";
	dprint($sql);
	audit_setup_gen('INV_BILLING', 'INV_BILLING_ID', 0, '', '');
	$inv_billing_id = sql_execute($sql, true); # audited

	return $inv_billing_id;

} # sql_add_gen_billing_line()

function sql_set_invoice_net_to_billing($invoice_id)
{
	$sql = "SELECT INV_NET, INV_VAT FROM INVOICE WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$inv_net = 0.0;
	$inv_vat = 0.0;
	while (($newArray = sql_fetch()) != false)
	{
		$inv_net = floatval($newArray[0]);
		$inv_vat = floatval($newArray[1]);
	}

	$vat_rate = ($inv_net ? ($inv_vat / $inv_net) : 0.0);

	$sql = "SELECT SUM(BL_COST) FROM INV_BILLING WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$new_net = 0.0;
	while (($newArray = sql_fetch()) != false)
		$new_net = floatval($newArray[0]);

	$new_vat = $vat_rate * $new_net;

	$sql = "UPDATE INVOICE SET INV_NET=$new_net WHERE INVOICE_ID=$invoice_id";
	audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'INV_NET', $new_net);
	sql_execute($sql, true); # audited

	$sql = "UPDATE INVOICE SET INV_VAT=$new_vat WHERE INVOICE_ID=$invoice_id";
	audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'INV_VAT', $new_vat);
	sql_execute($sql, true); # audited

} # sql_set_invoice_net_to_billing()

function sql_remove_job_from_group($job_id)
{
	$sql = "UPDATE JOB SET JOB_GROUP_ID=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JOB_GROUP_ID', 'NULL');
	sql_execute($sql, true); # audited

	sql_update_job($job_id);
}

function letter_placeholder_resolution_coll($job_id, $letter_template, $artificial_date='')
{
	# For a collection job letter, resolve placeholders that were invented in the old DOS based system.
	# See also letter_placeholder_resolution_kdb();
	# $artificial_date added 03/12/18 for CR #VIL-1

	global $id_ROUTE_cspent;
	global $sqlFalse;
	global $sqlTrue;

	$date_today = date_pretty_day($artificial_date ? $artificial_date : date_now_sql());

	# ==== Get data ==========================================================================

	$client2_id = 0;
	$vilno = '';
	$paid_so_far = 0.0;
	$outstanding = 0.0;
	$jc_instal_amt = 0.0;
	$jc_instal_freq = '';
	$client_name = '';
	$subject_name = '';
	$subject_addr = array();
	$last_payment_date = '';
	$last_payment_date_pretty = '';
	$last_payment_amount = 0.0;

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');

	$sql = "SELECT CLIENT2_ID, J_VILNO, JC_TOTAL_AMT, JC_PAID_SO_FAR, JC_INSTAL_AMT, JC_INSTAL_FREQ FROM JOB WHERE JOB_ID=$job_id";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$client2_id = $newArray['CLIENT2_ID'];
		$vilno = $newArray['J_VILNO'];
		$ta = floatval($newArray['JC_TOTAL_AMT']);
		$paid_so_far = floatval($newArray['JC_PAID_SO_FAR']);
		$outstanding = $ta - $paid_so_far; # not finished yet!
		$jc_instal_amt = floatval($newArray['JC_INSTAL_AMT']);
		$jc_instal_freq = instal_freq_from_code($newArray['JC_INSTAL_FREQ'], true);
	}

	$sql = "SELECT COL_AMT_RX FROM JOB_PAYMENT WHERE (JOB_ID=$job_id) AND (COL_BOUNCED=$sqlFalse) AND (OBSOLETE=$sqlFalse) AND
				(COL_PAYMENT_ROUTE_ID = $id_ROUTE_cspent)";
	sql_execute($sql);
	$adjustments = 0.0;
	while (($newArray = sql_fetch()) != false)
		$adjustments += floatval($newArray[0]);
	#print("outstanding=$outstanding, adjustments=$adjustments");#
	$outstanding = money_format_kdb($outstanding - $adjustments, true, false, true); # not finished yet!

	$sql = "SELECT " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$client_name = $newArray['C_CO_NAME'];

	$sql = "SELECT JS_TITLE, " . sql_decrypt('JS_FIRSTNAME', '', true) . ", " . sql_decrypt('JS_LASTNAME', '', true) . ",
				" . sql_decrypt('JS_COMPANY', '', true) . ",
				" . sql_decrypt('JS_ADDR_1', '', true) . ", " . sql_decrypt('JS_ADDR_2', '', true) . ", " . sql_decrypt('JS_ADDR_3', '', true) . ",
				" . sql_decrypt('JS_ADDR_4', '', true) . ", " . sql_decrypt('JS_ADDR_5', '', true) . ", " . sql_decrypt('JS_ADDR_PC', '', true) . "
			FROM JOB_SUBJECT WHERE JOB_ID=$job_id AND JS_PRIMARY=$sqlTrue";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$subject_name = trim($newArray['JS_TITLE']);
		if (trim($newArray['JS_FIRSTNAME']))
			$subject_name .= (($subject_name ? ' ' : '') . trim($newArray['JS_FIRSTNAME']));
		if (trim($newArray['JS_LASTNAME']))
			$subject_name .= (($subject_name ? ' ' : '') . trim($newArray['JS_LASTNAME']));
		if (trim($newArray['JS_COMPANY']))
			$subject_name .= (($subject_name ? ' ' : '') . trim($newArray['JS_COMPANY']));
		$subject_name = trim($subject_name);

		$aix = 1;
		if (trim($newArray['JS_ADDR_1']))
			$subject_addr[$aix++] = trim($newArray['JS_ADDR_1']);
		if (trim($newArray['JS_ADDR_2']))
			$subject_addr[$aix++] = trim($newArray['JS_ADDR_2']);
		if (trim($newArray['JS_ADDR_3']))
			$subject_addr[$aix++] = trim($newArray['JS_ADDR_3']);
		if (trim($newArray['JS_ADDR_4']))
			$subject_addr[$aix++] = trim($newArray['JS_ADDR_4']);
		if (trim($newArray['JS_ADDR_5']))
			$subject_addr[$aix++] = trim($newArray['JS_ADDR_5']);
		if (trim($newArray['JS_ADDR_PC']))
			$subject_addr[$aix++] = trim($newArray['JS_ADDR_PC']);
		for ($aix = 1; $aix <= 6; $aix++)
		{
			if (count($subject_addr) < $aix)
				$subject_addr[$aix] = '';
		}
	}

	if ((strpos($letter_template, '?^D') !== false) || (strpos($letter_template, '^D') !== false) ||
		(strpos($letter_template, '?^L') !== false) || (strpos($letter_template, '^L') !== false))
	{
		# Date and amount of of last payment
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top COL_DT_RX, COL_AMT_RX FROM JOB_PAYMENT WHERE JOB_ID=$job_id AND COL_BOUNCED=$sqlFalse AND OBSOLETE=$sqlFalse
				ORDER BY COL_DT_RX DESC $my_limit";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$last_payment_date = $newArray[0];
			$last_payment_amount = floatval($newArray[1]);
		}
		if ($last_payment_date)
			$last_payment_date_pretty = date_pretty_day($last_payment_date, 0, false, true); # e.g. 31-Jan-2017
	}

	# ==== Make substitutions ==========================================================================

	if (1 < count($subject_addr))
	{
		if (strpos($letter_template, '?^A1') !== false)
			$letter_template = str_replace('?^A1', $subject_addr[1], $letter_template);
		if (strpos($letter_template, '^A1') !== false)
			$letter_template = str_replace('^A1', $subject_addr[1], $letter_template);
	}
	if (2 < count($subject_addr))
	{
		if (strpos($letter_template, '?^A2') !== false)
			$letter_template = str_replace('?^A2', $subject_addr[2], $letter_template);
		if (strpos($letter_template, '^A2') !== false)
			$letter_template = str_replace('^A2', $subject_addr[2], $letter_template);
	}
	if (3 < count($subject_addr))
	{
		if (strpos($letter_template, '?^A3') !== false)
			$letter_template = str_replace('?^A3', $subject_addr[3], $letter_template);
		if (strpos($letter_template, '^A3') !== false)
			$letter_template = str_replace('^A3', $subject_addr[3], $letter_template);
	}
	if (4 < count($subject_addr))
	{
		if (strpos($letter_template, '?^A4') !== false)
			$letter_template = str_replace('?^A4', $subject_addr[4], $letter_template);
		if (strpos($letter_template, '^A4') !== false)
			$letter_template = str_replace('^A4', $subject_addr[4], $letter_template);
	}
	if (5 < count($subject_addr))
	{
		if (strpos($letter_template, '?^A5') !== false)
			$letter_template = str_replace('?^A5', $subject_addr[5], $letter_template);
		if (strpos($letter_template, '^A5') !== false)
			$letter_template = str_replace('^A5', $subject_addr[5], $letter_template);
	}
	if (strpos($letter_template, '?^B') !== false)
		$letter_template = str_replace('?^B', '<b>', $letter_template);
	if (strpos($letter_template, '^B') !== false)
		$letter_template = str_replace('^B', '<b>', $letter_template);

	if (strpos($letter_template, '?^b') !== false)
		$letter_template = str_replace('?^b', '</b>', $letter_template);
	if (strpos($letter_template, '^b') !== false)
		$letter_template = str_replace('^b', '</b>', $letter_template);

	if (strpos($letter_template, '?^C') !== false)
		$letter_template = str_replace('?^C', $client_name, $letter_template);
	if (strpos($letter_template, '^C') !== false)
		$letter_template = str_replace('^C', $client_name, $letter_template);

	if (strpos($letter_template, '?^D') !== false)
		$letter_template = str_replace('?^D', $last_payment_date_pretty, $letter_template);
	if (strpos($letter_template, '^D') !== false)
		$letter_template = str_replace('^D', $last_payment_date_pretty, $letter_template);

	if (strpos($letter_template, '?^d') !== false)
		$letter_template = str_replace('?^d', $date_today, $letter_template);
	if (strpos($letter_template, '^d') !== false)
		$letter_template = str_replace('^d', $date_today, $letter_template);

	if (strpos($letter_template, '?^E') !== false)
		$letter_template = str_replace('?^E', $jc_instal_freq, $letter_template);
	if (strpos($letter_template, '^E') !== false)
		$letter_template = str_replace('^E', $jc_instal_freq, $letter_template);

	if (strpos($letter_template, '?^L') !== false)
		$letter_template = str_replace('?^L', "&pound;{$last_payment_amount}", $letter_template);
	if (strpos($letter_template, '^L') !== false)
		$letter_template = str_replace('^L', "&pound;{$last_payment_amount}", $letter_template);

	if (strpos($letter_template, '?^N') !== false)
		$letter_template = str_replace('?^N', $subject_name, $letter_template);
	if (strpos($letter_template, '^N') !== false)
		$letter_template = str_replace('^N', $subject_name, $letter_template);

	if (strpos($letter_template, '?^O') !== false)
		$letter_template = str_replace('?^O', $outstanding, $letter_template);
	if (strpos($letter_template, '^O') !== false)
		$letter_template = str_replace('^O', $outstanding, $letter_template);

	if (strpos($letter_template, '?^P') !== false)
		$letter_template = str_replace('?^P', "&pound;$jc_instal_amt", $letter_template);
	if (strpos($letter_template, '^P') !== false)
		$letter_template = str_replace('^P', "&pound;$jc_instal_amt", $letter_template);

	if (strpos($letter_template, '?^V') !== false)
		$letter_template = str_replace('?^V', $vilno, $letter_template);
	if (strpos($letter_template, '^V') !== false)
		$letter_template = str_replace('^V', $vilno, $letter_template);

	if (strpos($letter_template, '^_') !== false)
		$letter_template = str_replace('^_', '&nbsp;', $letter_template);

	# Get rid of corrupt pound sign: replace 194,163 with just &pound;
	$letter_template = pound_clean($letter_template, 2);

//	$code_page = '';
//	$strlen = strlen($letter_template);
//	for ($ii=0; $ii < $strlen; $ii++)
//	{
//		$code = ord($letter_template[$ii]);
//		if (($code == 10) || ($code == 13) || ($code == 32))
//			$code_page .= $letter_template[$ii];
//		else
//			$code_page .= "~$code";
//	}
//	return $code_page;#

	return $letter_template;

} # letter_placeholder_resolution_coll()

function letter_placeholder_resolution_kdb($job_id, $letter_text)
{
	# For any job letter text (but probably just for trace job letters),
	# resolve placeholders that were invented by KDB for the new system.
	# See also letter_placeholder_resolution_coll();

	$job_id=$job_id; # keep code-checker quiet

	$letter_text = str_replace("##DATE##", date_now(true, '', false, false, false, true), $letter_text);

	return $letter_text;

} # letter_placeholder_resolution_kdb()

function letter_for_collect_job($job_id, $letter_type_id, $artificial_date='')
{
	# $artificial_date added 03/12/18 for CR #VIL-1

	$feedback_42 = global_debug();

	$sql = "SELECT LETTER_TEMPLATE, LT_AUTO_APP FROM LETTER_TYPE_SD WHERE LETTER_TYPE_ID=$letter_type_id";
	sql_execute($sql);
	$letter_template = '';
	$lt_auto_app = true;
	while (($newArray = sql_fetch()) != false)
	{
		$letter_template = $newArray[0];
		if ($feedback_42)
			$lt_auto_app = ($newArray[1] ? true : false);
	}

	return array(letter_placeholder_resolution_coll($job_id, $letter_template, $artificial_date),
					$lt_auto_app);
} # letter_for_collect_job()

function letter_for_trace_job($job_type_id, $success, $vilno, $client2_id, $client_ref, $letter_body, $sub_name, $sub_addr, $new_addr, $sub_phones)
{
	global $crlf;
	global $id_JOB_TYPE_etr;
	global $id_JOB_TYPE_trc;
	global $sqlFalse;
	global $success_return;

	#dprint("PHONES/2=" . print_r($sub_phones,1));#
	$this_txt = "letter_for_trace_job(job_type=$job_type_id, success=$success, vilno=$vilno, client=$client2_id, cref=$client_ref, subject=$sub_name)";

	if (!(0 < $job_type_id))
		return "(There is no letter text because there is no Job Type)";
		#return "$this_txt: bad job type $job_type_id";

	if ($success == $success_return)
		return "(There is no letter text because SUCCESS is set to RETURN)";
	if (gettype($success) == 'NULL')
		$success = 'NULL';
	if (! (($success == 1) || ($success == -1) || ($success === 0) || ($success === '0') || ($success === '') || ($success === 'NULL')) )
		return "$this_txt: bad success \"$success\"";

	if (!(0 < $vilno))
		return "$this_txt: bad VILNO $vilno";

	if (!(0 < $client2_id))
		return "$this_txt: bad client $client2_id";

	if ($letter_body == '')
		return "(There is no letter text because there is no Report Body)";

	# Hack Sep 2018 - treat E-trace jobs like trace jobs, with regard to header/footer.
	$job_type_2 = $job_type_id;
	if ($job_type_2 == $id_JOB_TYPE_etr)
		$job_type_2 = $id_JOB_TYPE_trc;

	$sql = "SELECT JT_T_OPEN, JT_T_CLOSE FROM LETTER_TYPE_SD
			WHERE (JT_T_JOB_TYPE_ID=$job_type_2) AND (JT_T_SUCC=$success) AND (OBSOLETE=$sqlFalse)";
	#dprint($sql);#
	sql_execute($sql);
	$open = '';
	$close = '';
	while (($newArray = sql_fetch()) != false)
	{
		$open = $newArray[0];
		$close = $newArray[1];
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT " . sql_decrypt('C_CO_NAME', '', true) . ",
			" . sql_decrypt('C_ADDR_1', '', true) . ", " . sql_decrypt('C_ADDR_2', '', true) . ",
			" . sql_decrypt('C_ADDR_3', '', true) . ", " . sql_decrypt('C_ADDR_4', '', true) . ",
			" . sql_decrypt('C_ADDR_5', '', true) . ", " . sql_decrypt('C_ADDR_PC', '', true) . "
			FROM CLIENT2
			WHERE CLIENT2_ID=$client2_id";
	sql_execute($sql);
	$client_name = '';
	$client_addr = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$client_name = $newArray['C_CO_NAME'];
		$temp = trim($newArray['C_ADDR_1']);
		if ($temp)
			$client_addr[] = $temp;
		$temp = trim($newArray['C_ADDR_2']);
		if ($temp)
			$client_addr[] = $temp;
		$temp = trim($newArray['C_ADDR_3']);
		if ($temp)
			$client_addr[] = $temp;
		$temp = trim($newArray['C_ADDR_4']);
		if ($temp)
			$client_addr[] = $temp;
		$temp = trim($newArray['C_ADDR_5']);
		if ($temp)
			$client_addr[] = $temp;
		$temp = trim($newArray['C_ADDR_PC']);
		if ($temp)
			$client_addr[] = $temp;
	}

	$old_addr_line = implode(', ', $sub_addr);
	$new_addr_lines = implode($crlf, $new_addr);

	$phone = '';
	$sub_phones=$sub_phones;
//	foreach ($sub_phones as $one)
//	{
//		if ($one['JP_PHONE'])
//		{
//			$phone = "Tel - " . $one['JP_PHONE'];
//			break;
//		}
//	}

	return	"Our Ref: $vilno" . $crlf . $crlf .
			"Date: ##DATE##" . $crlf . $crlf .
			$client_name . $crlf .
			implode($crlf, $client_addr) . $crlf . $crlf .
			"Your Ref: $client_ref" . $crlf . $crlf .
			"Dear Sirs," . $crlf . $crlf .
			"Re: " . $sub_name . $crlf .
			$old_addr_line . $crlf .
			($phone ? ($phone . $crlf) : '') .
			$crlf .
			$open . $crlf . $crlf .
			(($success && $new_addr_lines) ? ($new_addr_lines . $crlf . $crlf) : '') .
			$letter_body . $crlf . $crlf .
			str_replace(".$crlf", ".$crlf$crlf", $close) . $crlf . $crlf .
			"Assuring you of our best attention at all times." . $crlf . $crlf .
			"Yours faithfully," . $crlf . $crlf . $crlf . $crlf . $crlf .
			"VILCOL" . $crlf .
			'';

} # letter_for_trace_job()

function sql_add_collect_job_to_trace_job($old_job_id)
{
	# Add a new collection job to an existing trace job.
	# The trace job will be of type "T/C".

	global $crlf;
	global $id_LETTER_TYPE_letter_1;
	global $sqlFalse;
	global $sqlTrue;

	# --- Step 1. Copy the Job. ------------------------------------------------------------------------------------------------

	# Get some details of the existing Trace Job

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('JOB_PHONE');
	sql_encryption_preparation('JOB_NOTE');

	$sql = "SELECT CLIENT2_ID, " . sql_decrypt('CLIENT_REF', '', true) . ", J_VILNO, JOB_GROUP_ID,
					JT_AMOUNT, " . sql_decrypt('JC_REASON_2', '', true) . ", J_DIARY_DT FROM JOB WHERE JOB_ID=$old_job_id";
	sql_execute($sql);
	$client2_id = 0;
	$client_ref = '';
	$j_vilno = 0;
	$job_group_id = 0;
	$jt_amount = 0.0;
	$jc_reason_2 = '';
	$j_diary_dt = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$client2_id = $newArray['CLIENT2_ID'];
		$client_ref = sql_encrypt($newArray['CLIENT_REF'], false, 'JOB');
		$j_vilno = $newArray['J_VILNO'];
		$job_group_id = intval($newArray['JOB_GROUP_ID']);
		$jt_amount = floatval($newArray['JT_AMOUNT']);
		$jc_reason_2 = $newArray['JC_REASON_2'];
		$j_diary_dt = $newArray['J_DIARY_DT'];
	}

	$comm_percent = floatval(sql_select_single("SELECT COMM_PERCENT FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));

	# If the existing trace job is not already in a group then create a group and add the old job to it
	if (!$job_group_id)
	{
		$sql = "INSERT INTO JOB_GROUP (JG_NAME) VALUES ('$j_vilno')";
		audit_setup_gen('JOB_GROUP', 'JOB_GROUP_ID', 0, '', '');
		$job_group_id = sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET JOB_GROUP_ID=$job_group_id WHERE JOB_ID=$old_job_id";
		audit_setup_job($old_job_id, 'JOB', 'JOB_ID', $old_job_id, 'JOB_GROUP_ID', $job_group_id);
		sql_execute($sql, true); # audited

		sql_update_job($old_job_id);
	}

	$now = "'" . date_now_sql() . "'";
	$j_sequence = sequence_next();

	$fields = "CLIENT2_ID,  CLIENT_REF,  J_VILNO,  J_SEQUENCE,  JOB_GROUP_ID,  J_OPENED_DT, J_S_INVS, IMPORTED,  JT_JOB,    JC_JOB,   ";
	$values = "$client2_id, $client_ref, $j_vilno, $j_sequence, $job_group_id, $now,        $sqlTrue, $sqlFalse, $sqlFalse, $sqlTrue, ";

	$fields .= "JC_PERCENT,    JC_LETTER_MORE, JC_LETTER_TYPE_ID,        JC_TOTAL_AMT, J_DIARY_DT    ";
	$values .= "$comm_percent, $sqlTrue,       $id_LETTER_TYPE_letter_1, $jt_amount,   '$j_diary_dt' ";

	$sql = "INSERT INTO JOB ($fields) VALUES ($values)";
	audit_setup_gen('JOB', 'JOB_ID', 0, '', '');
	$new_job_id = sql_execute($sql, true); # audited

	# --- Step 2. Copy the Subjects. ------------------------------------------------------------------------------------------------

	$fields_sel = "JS_PRIMARY, JS_TITLE, " . sql_decrypt('JS_FIRSTNAME', '', true) . ", " . sql_decrypt('JS_LASTNAME', '', true) . ",
				" . sql_decrypt('JS_COMPANY', '', true) . ", JS_DOB,
				" . sql_decrypt('JS_ADDR_1', '', true) . ", " . sql_decrypt('JS_ADDR_2', '', true) . ", " . sql_decrypt('JS_ADDR_3', '', true) . ",
				" . sql_decrypt('JS_ADDR_4', '', true) . ", " . sql_decrypt('JS_ADDR_5', '', true) . ", " . sql_decrypt('JS_ADDR_PC', '', true) . ",
					JS_OUTCODE,
				" . sql_decrypt('NEW_ADDR_1', '', true) . ", " . sql_decrypt('NEW_ADDR_2', '', true) . ", " . sql_decrypt('NEW_ADDR_3', '', true) . ",
				" . sql_decrypt('NEW_ADDR_4', '', true) . ", " . sql_decrypt('NEW_ADDR_5', '', true) . ", " . sql_decrypt('NEW_ADDR_PC', '', true) . ",
					NEW_OUTCODE";
	$fields_ins = "JOB_ID, JS_PRIMARY, JS_TITLE, JS_FIRSTNAME, JS_LASTNAME, JS_COMPANY, JS_DOB,
					JS_ADDR_1, JS_ADDR_2, JS_ADDR_3, JS_ADDR_4, JS_ADDR_5, JS_ADDR_PC, JS_OUTCODE";
	$sql = "SELECT $fields_sel FROM JOB_SUBJECT WHERE (JOB_ID=$old_job_id) AND (OBSOLETE=$sqlFalse)";
	sql_execute($sql);
	$subjects = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$subjects[] = $newArray;
	foreach ($subjects as $one_sub)
	{
		$js_addr = array();
		$js_pc = '';
		$js_outcode = '';
		if (trim($one_sub['NEW_ADDR_1']) != '')
			$js_addr[] = trim($one_sub['NEW_ADDR_1']);
		if (trim($one_sub['NEW_ADDR_2']) != '')
			$js_addr[] = trim($one_sub['NEW_ADDR_2']);
		if (trim($one_sub['NEW_ADDR_3']) != '')
			$js_addr[] = trim($one_sub['NEW_ADDR_3']);
		if (trim($one_sub['NEW_ADDR_4']) != '')
			$js_addr[] = trim($one_sub['NEW_ADDR_4']);
		if (trim($one_sub['NEW_ADDR_5']) != '')
			$js_addr[] = trim($one_sub['NEW_ADDR_5']);
		if (trim($one_sub['NEW_ADDR_PC']) != '')
			$js_pc = trim($one_sub['NEW_ADDR_PC']);
		if (trim($one_sub['NEW_OUTCODE']) != '')
			$js_outcode = trim($one_sub['NEW_OUTCODE']);
		if ((count($js_addr) == 0) && ($js_pc == ''))
		{
			if (trim($one_sub['JS_ADDR_1']) != '')
				$js_addr[] = trim($one_sub['JS_ADDR_1']);
			if (trim($one_sub['JS_ADDR_2']) != '')
				$js_addr[] = trim($one_sub['JS_ADDR_2']);
			if (trim($one_sub['JS_ADDR_3']) != '')
				$js_addr[] = trim($one_sub['JS_ADDR_3']);
			if (trim($one_sub['JS_ADDR_4']) != '')
				$js_addr[] = trim($one_sub['JS_ADDR_4']);
			if (trim($one_sub['JS_ADDR_5']) != '')
				$js_addr[] = trim($one_sub['JS_ADDR_5']);
			if (trim($one_sub['JS_ADDR_PC']) != '')
				$js_pc = trim($one_sub['JS_ADDR_PC']);
			if (trim($one_sub['JS_OUTCODE']) != '')
				$js_outcode = trim($one_sub['JS_OUTCODE']);
		}
		while (count($js_addr) < 5)
			$js_addr[] = '';

		$old_addr = array();
		if (trim($one_sub['JS_ADDR_1']) != '')
			$old_addr[] = trim($one_sub['JS_ADDR_1']);
		if (trim($one_sub['JS_ADDR_2']) != '')
			$old_addr[] = trim($one_sub['JS_ADDR_2']);
		if (trim($one_sub['JS_ADDR_3']) != '')
			$old_addr[] = trim($one_sub['JS_ADDR_3']);
		if (trim($one_sub['JS_ADDR_4']) != '')
			$old_addr[] = trim($one_sub['JS_ADDR_4']);
		if (trim($one_sub['JS_ADDR_5']) != '')
			$old_addr[] = trim($one_sub['JS_ADDR_5']);
		if (trim($one_sub['JS_ADDR_PC']) != '')
			$old_addr[] = trim($one_sub['JS_ADDR_PC']);
		$old_addr = implode($crlf, $old_addr);
		if ($old_addr)
		{
			if ($jc_reason_2)
				$jc_reason_2 .= $crlf;
			$jc_reason_2 .= "Old address supplied by Client:$crlf{$old_addr}";
		}

		$values = array($new_job_id, intval($one_sub['JS_PRIMARY']), quote_smart($one_sub['JS_TITLE']),
						sql_encrypt($one_sub['JS_FIRSTNAME'], false, 'JOB_SUBJECT'), sql_encrypt($one_sub['JS_LASTNAME'], false, 'JOB_SUBJECT'),
						sql_encrypt($one_sub['JS_COMPANY'], false, 'JOB_SUBJECT'), "'{$one_sub['JS_DOB']}'",
						sql_encrypt($js_addr[0], false, 'JOB_SUBJECT'), sql_encrypt($js_addr[1], false, 'JOB_SUBJECT'),
						sql_encrypt($js_addr[2], false, 'JOB_SUBJECT'), sql_encrypt($js_addr[3], false, 'JOB_SUBJECT'),
						sql_encrypt($js_addr[4], false, 'JOB_SUBJECT'), sql_encrypt($js_pc, false, 'JOB_SUBJECT'),
						($js_outcode ? "'$js_outcode'" : 'NULL')
						);
		$values = implode(',', $values);
		$sql = "INSERT INTO JOB_SUBJECT ($fields_ins, IMPORTED, OBSOLETE) VALUES ($values, $sqlFalse, $sqlFalse)";
		audit_setup_job($new_job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', 0, '', '');
		sql_execute($sql, true); # audited

		if ($old_addr)
		{
			$sql = "UPDATE JOB SET JC_REASON_2=" . sql_encrypt($jc_reason_2, false, 'JOB') . " WHERE JOB_ID=$new_job_id";
			audit_setup_gen('JOB', 'JOB_ID', $new_job_id, 'JC_REASON_2', $jc_reason_2);
			sql_execute($sql, true); # audited
		}
	}

	# --- Step 3. Copy the Phones & Emails . ------------------------------------------------------------------------------------------------

	$fields_sel = sql_decrypt('JP_PHONE', '', true) . ", JP_PRIMARY_P,
					" . sql_decrypt('JP_EMAIL', '', true) . ", JP_PRIMARY_E, " . sql_decrypt('JP_DESCR', '', true);
	$fields_ins = "JOB_ID, JP_PHONE, JP_PRIMARY_P, JP_EMAIL, JP_PRIMARY_E, JP_DESCR";
	$sql = "SELECT $fields_sel FROM JOB_PHONE WHERE (JOB_ID=$old_job_id) AND (OBSOLETE=$sqlFalse)";
	sql_execute($sql);
	$phones = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$phones[] = $newArray;
	foreach ($phones as $one_ph)
	{
		$jp_phone = trim($one_ph['JP_PHONE']);
		if ($jp_phone == '')
			$jp_phone = 'NULL';
		else
			$jp_phone = sql_encrypt($jp_phone, false, 'JOB_PHONE');
		$jp_email = trim($one_ph['JP_EMAIL']);
		if ($jp_email == '')
			$jp_email = 'NULL';
		else
			$jp_email = sql_encrypt($jp_email, false, 'JOB_PHONE');
		$values = array($new_job_id, $jp_phone, intval($one_ph['JP_PRIMARY_P']), $jp_email, intval($one_ph['JP_PRIMARY_E']),
						sql_encrypt($one_ph['JP_DESCR'], false, 'JOB_PHONE'));
		$values = implode(',', $values);
		$sql = "INSERT INTO JOB_PHONE ($fields_ins, IMPORTED, IMP_PH, OBSOLETE) VALUES ($values, $sqlFalse, $sqlFalse, $sqlFalse)";
		audit_setup_job($new_job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
		sql_execute($sql, true); # audited
	}

	# --- Step 4. Copy the Notes. ------------------------------------------------------------------------------------------------

	$fields_sel = "JOB_NOTE_ID, " . sql_decrypt('J_NOTE', '', true) . ", IMPORTED, IMP_2, JN_ADDED_ID, JN_ADDED_DT, JN_UPDATED_ID, JN_UPDATED_DT";
	$fields_ins = "JOB_ID, J_NOTE, IMPORTED, IMP_2, JN_ADDED_ID, JN_ADDED_DT, JN_UPDATED_ID, JN_UPDATED_DT";
	$sql = "SELECT $fields_sel FROM JOB_NOTE WHERE (JOB_ID=$old_job_id) ORDER BY JOB_NOTE_ID";
	#dprint($sql);#
	sql_execute($sql);
	$notes = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$notes[] = $newArray;
	foreach ($notes as $one_note)
	{
		if (0 < strlen($one_note['J_NOTE']))
		{
			$n_imported = ($one_note['IMPORTED'] ? 1 : 0);
			$n_imp_2 = ($one_note['IMP_2'] ? 1 : 0);
			$n_add_id = ($one_note['JN_ADDED_ID'] ? $one_note['JN_ADDED_ID'] : 'NULL');
			$n_add_dt = ($one_note['JN_ADDED_DT'] ? "'{$one_note['JN_ADDED_DT']}'" : 'NULL');
			$n_up_id = ($one_note['JN_UPDATED_ID'] ? $one_note['JN_UPDATED_ID'] : 'NULL');
			$n_up_dt = ($one_note['JN_UPDATED_DT'] ? "'{$one_note['JN_UPDATED_DT']}'" : 'NULL');
			$values = array($new_job_id, sql_encrypt($one_note['J_NOTE'], false, 'JOB_NOTE'), $n_imported, $n_imp_2,
								$n_add_id, $n_add_dt, $n_up_id, $n_up_dt);
			$values = implode(',', $values);
			$sql = "INSERT INTO JOB_NOTE ($fields_ins) VALUES ($values)";
			audit_setup_job($new_job_id, 'JOB_NOTE', 'JOB_NOTE_ID', 0, '', '');
			#dprint($sql);#
			sql_execute($sql, true); # audited
		}
	} # foreach ($notes)

	# --- Step 5. Add New Notes. ------------------------------------------------------------------------------------------------
	#
	sql_add_note($old_job_id, "Copied to a new collection job (VILNo $j_vilno, DB ID $new_job_id)", false);
	sql_add_note($new_job_id, "Copied from trace job (VILNo $j_vilno, DB ID $old_job_id)", false);

	return $new_job_id;

} # sql_add_collect_job_to_trace_job()

function sql_clone_trace_job($old_job_id)
{
	# Create a clone of an existing trace job, e.g. so it can be set up as a re-trace job by the user.

	global $sqlFalse;
	global $sqlTrue;

	$debug = false;#

	# --- Step 1. Copy the Job. ------------------------------------------------------------------------------------------------

	# Get some details of the existing Trace Job

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('JOB_PHONE');
	sql_encryption_preparation('JOB_NOTE');

	$sql = "SELECT CLIENT2_ID, " . sql_decrypt('CLIENT_REF', '', true) . ", J_VILNO, JOB_GROUP_ID, J_S_INVS FROM JOB WHERE JOB_ID=$old_job_id";
	sql_execute($sql);
	$client2_id = 0;
	$client_ref = '';
	$j_vilno = 0;
	$job_group_id = 0;
	$j_s_invs = 0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$client2_id = $newArray['CLIENT2_ID'];
		$client_ref = sql_encrypt($newArray['CLIENT_REF'], false, 'JOB');
		$j_vilno = $newArray['J_VILNO'];
		$job_group_id = intval($newArray['JOB_GROUP_ID']);
		$j_s_invs = intval($newArray['J_S_INVS']);
	}

	# If the existing trace job is not already in a group then create a group and add the old job to it
	if (!$job_group_id)
	{
		$sql = "INSERT INTO JOB_GROUP (JG_NAME) VALUES ('$j_vilno')";
		audit_setup_gen('JOB_GROUP', 'JOB_GROUP_ID', 0, '', '');
		if ($debug)
			dprint($sql);
		else
			$job_group_id = sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET JOB_GROUP_ID=$job_group_id WHERE JOB_ID=$old_job_id";
		audit_setup_job($old_job_id, 'JOB', 'JOB_ID', $old_job_id, 'JOB_GROUP_ID', $job_group_id);
		if ($debug)
			dprint($sql);
		else
			sql_execute($sql, true); # audited

		if (!$debug)
			sql_update_job($old_job_id);
	}

	$now = "'" . date_now_sql() . "'";
	$j_sequence = sequence_next();

	$fields = "CLIENT2_ID,  CLIENT_REF,  J_VILNO,  J_SEQUENCE,  JOB_GROUP_ID,  J_OPENED_DT, J_S_INVS,  IMPORTED,  JT_JOB,   JC_JOB   ";
	$values = "$client2_id, $client_ref, $j_vilno, $j_sequence, $job_group_id, $now,        $j_s_invs, $sqlFalse, $sqlTrue, $sqlFalse";

	$sql = "INSERT INTO JOB ($fields) VALUES ($values)";
	audit_setup_gen('JOB', 'JOB_ID', 0, '', '');
	if ($debug)
		dprint($sql);
	else
		$new_job_id = sql_execute($sql, true); # audited
	#dlog("sql_clone_trace_job($old_job_id): new job created with ID $new_job_id");
	dprint("A new trace job has been created with VILNo $j_vilno and Sequence $j_sequence (DB ID $new_job_id)", true, 'blue');

	# --- Step 2. Copy the Subjects. ------------------------------------------------------------------------------------------------

	$fields_sel = "JOB_SUBJECT_ID, JS_PRIMARY, JS_TITLE, " . sql_decrypt('JS_FIRSTNAME', '', true) . ", " . sql_decrypt('JS_LASTNAME', '', true) . ",
				" . sql_decrypt('JS_COMPANY', '', true) . ", JS_DOB,
				" . sql_decrypt('JS_ADDR_1', '', true) . ", " . sql_decrypt('JS_ADDR_2', '', true) . ", " . sql_decrypt('JS_ADDR_3', '', true) . ",
				" . sql_decrypt('JS_ADDR_4', '', true) . ", " . sql_decrypt('JS_ADDR_5', '', true) . ", " . sql_decrypt('JS_ADDR_PC', '', true) . ",
					JS_OUTCODE,
				" . sql_decrypt('NEW_ADDR_1', '', true) . ", " . sql_decrypt('NEW_ADDR_2', '', true) . ", " . sql_decrypt('NEW_ADDR_3', '', true) . ",
				" . sql_decrypt('NEW_ADDR_4', '', true) . ", " . sql_decrypt('NEW_ADDR_5', '', true) . ", " . sql_decrypt('NEW_ADDR_PC', '', true) . ",
					NEW_OUTCODE";
	$fields_ins = "JOB_ID, JS_PRIMARY, JS_TITLE, JS_FIRSTNAME, JS_LASTNAME, JS_COMPANY, JS_DOB,
					JS_ADDR_1, JS_ADDR_2, JS_ADDR_3, JS_ADDR_4, JS_ADDR_5, JS_ADDR_PC, JS_OUTCODE,
					NEW_ADDR_1, NEW_ADDR_2, NEW_ADDR_3, NEW_ADDR_4, NEW_ADDR_5, NEW_ADDR_PC, NEW_OUTCODE";
	$sql = "SELECT $fields_sel FROM JOB_SUBJECT WHERE (JOB_ID=$old_job_id) AND (OBSOLETE=$sqlFalse) ORDER BY JOB_SUBJECT_ID";
	sql_execute($sql);
	$subjects = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$subjects[] = $newArray;
	foreach ($subjects as $one_sub)
	{
		$js_addr = array();
		$js_pc = '';
		$js_outcode = '';
		if (trim($one_sub['JS_ADDR_1']) != '')
			$js_addr[] = trim($one_sub['JS_ADDR_1']);
		if (trim($one_sub['JS_ADDR_2']) != '')
			$js_addr[] = trim($one_sub['JS_ADDR_2']);
		if (trim($one_sub['JS_ADDR_3']) != '')
			$js_addr[] = trim($one_sub['JS_ADDR_3']);
		if (trim($one_sub['JS_ADDR_4']) != '')
			$js_addr[] = trim($one_sub['JS_ADDR_4']);
		if (trim($one_sub['JS_ADDR_5']) != '')
			$js_addr[] = trim($one_sub['JS_ADDR_5']);
		if (trim($one_sub['JS_ADDR_PC']) != '')
			$js_pc = trim($one_sub['JS_ADDR_PC']);
		if (trim($one_sub['JS_OUTCODE']) != '')
			$js_outcode = trim($one_sub['JS_OUTCODE']);
		while (count($js_addr) < 5)
			$js_addr[] = '';

		$new_addr = array();
		$new_pc = '';
		$new_outcode = '';
		if (trim($one_sub['NEW_ADDR_1']) != '')
			$new_addr[] = trim($one_sub['NEW_ADDR_1']);
		if (trim($one_sub['NEW_ADDR_2']) != '')
			$new_addr[] = trim($one_sub['NEW_ADDR_2']);
		if (trim($one_sub['NEW_ADDR_3']) != '')
			$new_addr[] = trim($one_sub['NEW_ADDR_3']);
		if (trim($one_sub['NEW_ADDR_4']) != '')
			$new_addr[] = trim($one_sub['NEW_ADDR_4']);
		if (trim($one_sub['NEW_ADDR_5']) != '')
			$new_addr[] = trim($one_sub['NEW_ADDR_5']);
		if (trim($one_sub['NEW_ADDR_PC']) != '')
			$new_pc = trim($one_sub['NEW_ADDR_PC']);
		if (trim($one_sub['NEW_OUTCODE']) != '')
			$new_outcode = trim($one_sub['NEW_OUTCODE']);
		while (count($new_addr) < 5)
			$new_addr[] = '';


		$values = array($new_job_id, intval($one_sub['JS_PRIMARY']), quote_smart($one_sub['JS_TITLE']),
						sql_encrypt($one_sub['JS_FIRSTNAME'], false, 'JOB_SUBJECT'), sql_encrypt($one_sub['JS_LASTNAME'], false, 'JOB_SUBJECT'),
						sql_encrypt($one_sub['JS_COMPANY'], false, 'JOB_SUBJECT'), "'{$one_sub['JS_DOB']}'",
						sql_encrypt($js_addr[0], false, 'JOB_SUBJECT'), sql_encrypt($js_addr[1], false, 'JOB_SUBJECT'),
						sql_encrypt($js_addr[2], false, 'JOB_SUBJECT'), sql_encrypt($js_addr[3], false, 'JOB_SUBJECT'),
						sql_encrypt($js_addr[4], false, 'JOB_SUBJECT'), sql_encrypt($js_pc, false, 'JOB_SUBJECT'),
						($js_outcode ? "'$js_outcode'" : 'NULL'),
						sql_encrypt($new_addr[0], false, 'JOB_SUBJECT'), sql_encrypt($new_addr[1], false, 'JOB_SUBJECT'),
						sql_encrypt($new_addr[2], false, 'JOB_SUBJECT'), sql_encrypt($new_addr[3], false, 'JOB_SUBJECT'),
						sql_encrypt($new_addr[4], false, 'JOB_SUBJECT'), sql_encrypt($new_pc, false, 'JOB_SUBJECT'),
						($new_outcode ? "'$new_outcode'" : 'NULL')
						);
		$values = implode(',', $values);

		$sql = "INSERT INTO JOB_SUBJECT ($fields_ins, IMPORTED, OBSOLETE) VALUES ($values, $sqlFalse, $sqlFalse)";
		audit_setup_job($new_job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', 0, '', '');
		if ($debug)
			dprint($sql);
		else
			sql_execute($sql, true); # audited
	} # foreach ($subjects)

	# --- Step 3. Copy the Phones & Emails. ------------------------------------------------------------------------------------------------

	$fields_sel = sql_decrypt('JP_PHONE', '', true) . ", JP_PRIMARY_P,
					" . sql_decrypt('JP_EMAIL', '', true) . ", JP_PRIMARY_E, " . sql_decrypt('JP_DESCR', '', true);
	$fields_ins = "JOB_ID, JP_PHONE, JP_PRIMARY_P, JP_EMAIL, JP_PRIMARY_E, JP_DESCR";
	$sql = "SELECT $fields_sel FROM JOB_PHONE WHERE (JOB_ID=$old_job_id) AND (OBSOLETE=$sqlFalse)";
	sql_execute($sql);
	$phones = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$phones[] = $newArray;
	foreach ($phones as $one_ph)
	{
		$jp_phone = trim($one_ph['JP_PHONE']);
		if ($jp_phone == '')
			$jp_phone = 'NULL';
		else
			$jp_phone = sql_encrypt($jp_phone, false, 'JOB_PHONE');
		$jp_email = trim($one_ph['JP_EMAIL']);
		if ($jp_email == '')
			$jp_email = 'NULL';
		else
			$jp_email = sql_encrypt($jp_email, false, 'JOB_PHONE');
		$values = array($new_job_id, $jp_phone, intval($one_ph['JP_PRIMARY_P']), $jp_email, intval($one_ph['JP_PRIMARY_E']),
						sql_encrypt($one_ph['JP_DESCR'], false, 'JOB_PHONE'));
		$values = implode(',', $values);
		$sql = "INSERT INTO JOB_PHONE ($fields_ins, IMPORTED, IMP_PH, OBSOLETE) VALUES ($values, $sqlFalse, $sqlFalse, $sqlFalse)";
		audit_setup_job($new_job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
		if ($debug)
			dprint($sql);
		else
			sql_execute($sql, true); # audited
	}

	#dlog("sql_clone_trace_job($old_job_id): subjects and phones created");

	# --- Step 4. Copy the Notes. ------------------------------------------------------------------------------------------------

	$fields_sel = "JOB_NOTE_ID, " . sql_decrypt('J_NOTE', '', true) . ", IMPORTED, IMP_2, JN_ADDED_ID, JN_ADDED_DT, JN_UPDATED_ID, JN_UPDATED_DT";
	$fields_ins = "JOB_ID, J_NOTE, IMPORTED, IMP_2, JN_ADDED_ID, JN_ADDED_DT, JN_UPDATED_ID, JN_UPDATED_DT";
	$sql = "SELECT $fields_sel FROM JOB_NOTE WHERE (JOB_ID=$old_job_id) ORDER BY JOB_NOTE_ID";
	sql_execute($sql);
	$notes = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$notes[] = $newArray;
	foreach ($notes as $one_note)
	{
		if (0 < strlen($one_note['J_NOTE']))
		{
			$n_imported = ($one_note['IMPORTED'] ? 1 : 0);
			$n_imp_2 = ($one_note['IMP_2'] ? 1 : 0);
			$n_add_id = ($one_note['JN_ADDED_ID'] ? $one_note['JN_ADDED_ID'] : 'NULL');
			$n_add_dt = ($one_note['JN_ADDED_DT'] ? "'{$one_note['JN_ADDED_DT']}'" : 'NULL');
			$n_up_id = ($one_note['JN_UPDATED_ID'] ? $one_note['JN_UPDATED_ID'] : 'NULL');
			$n_up_dt = ($one_note['JN_UPDATED_DT'] ? "'{$one_note['JN_UPDATED_DT']}'" : 'NULL');
			$values = array($new_job_id, sql_encrypt($one_note['J_NOTE'], false, 'JOB_NOTE'), $n_imported, $n_imp_2,
								$n_add_id, $n_add_dt, $n_up_id, $n_up_dt);
			$values = implode(',', $values);
			$sql = "INSERT INTO JOB_NOTE ($fields_ins) VALUES ($values)";
			audit_setup_job($new_job_id, 'JOB_NOTE', 'JOB_NOTE_ID', 0, '', '');
			if ($debug)
				dprint($sql);
			else
				sql_execute($sql, true); # audited
		}
	} # foreach ($notes)

	# --- Step 5. Add New Notes. ------------------------------------------------------------------------------------------------
	#
	sql_add_note($old_job_id, "Cloned to a new trace job (VILNo $j_vilno, DB ID $new_job_id)", false);
	sql_add_note($new_job_id, "Cloned from earlier trace job (VILNo $j_vilno, DB ID $old_job_id)", false);

	#dlog("sql_clone_trace_job($old_job_id): notes added");

	sql_update_job($new_job_id);

	return $new_job_id;

} # sql_clone_trace_job()

function sql_update_job($job_id)
{
	$now = date_now_sql();
	$now_sql = "'$now'";

	$sql = "UPDATE JOB SET J_UPDATED_DT=$now_sql WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_UPDATED_DT', $now);
	sql_execute($sql, true); # audited

} # sql_update_job()

function sql_update_letter($job_id, $job_letter_id)
{
	global $USER;

	$now = date_now_sql();
	$now_sql = "'$now'";

	$sql = "UPDATE JOB_LETTER SET JL_UPDATED_DT=$now_sql WHERE JOB_LETTER_ID=$job_letter_id";
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, 'JL_UPDATED_DT', $now);
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB_LETTER SET JL_UPDATED_ID={$USER['USER_ID']} WHERE JOB_LETTER_ID=$job_letter_id";
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, 'JL_UPDATED_ID', $USER['USER_ID']);
	sql_execute($sql, true); # audited

} # sql_update_letter()

function sql_update_client($client2_id)
{
	$now = date_now_sql();
	$now_sql = "'$now'";

	$sql = "UPDATE CLIENT2 SET UPDATED_DT=$now_sql WHERE CLIENT2_ID=$client2_id";
	audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'UPDATED_DT', $now);
	sql_execute($sql, true); # audited

} # sql_update_client()

function sql_close_job($job_id, $update_job=true)
{
	global $sqlTrue;
	global $USER;

	$user_id = intval($USER['USER_ID']);

	$now = "'" . date_now_sql() . "'";

	$sql = "UPDATE JOB SET JOB_CLOSED=$sqlTrue WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JOB_CLOSED', $sqlTrue);
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET J_CLOSED_DT=$now WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_CLOSED_DT', $now);
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET J_CLOSED_ID=$user_id WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_CLOSED_ID', $user_id);
	sql_execute($sql, true); # audited

	if ($update_job)
		sql_update_job($job_id);

} # sql_close_job()

function sql_delete_job($job_id)
{
	global $sqlTrue;

	$sql = "UPDATE JOB SET OBSOLETE=$sqlTrue WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'OBSOLETE', $sqlTrue);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # sql_delete_job()

function sql_reopen_job($job_id)
{
	global $sqlFalse;

	$sql = "UPDATE JOB SET JOB_CLOSED=$sqlFalse WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JOB_CLOSED', $sqlFalse);
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET J_CLOSED_DT=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_CLOSED_DT', 'NULL');
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET J_CLOSED_ID=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_CLOSED_ID', 'NULL');
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET J_COMPLETE=-1 WHERE JOB_ID=$job_id"; # Set COMPLETE to REVlEW
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_COMPLETE', -1);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # sql_reopen_job()

function sql_archive_job($job_id)
{
	global $sqlTrue;

	$sql = "UPDATE JOB SET J_ARCHIVED=$sqlTrue WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_ARCHIVED', $sqlTrue);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # sql_archive_job()

function sql_unarchive_job($job_id)
{
	global $sqlFalse;

	$sql = "UPDATE JOB SET J_ARCHIVED=$sqlFalse WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_ARCHIVED', $sqlFalse);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # sql_unarchive_job()

function sql_archive_client($client2_id)
{
	# This will mark the client, and all of its jobs, as archived.
	# It also sets its portal users to disabled.

	global $sqlFalse;
	global $sqlTrue;

	$jobs = array();
	$sql = "SELECT JOB_ID FROM JOB WHERE CLIENT2_ID=$client2_id AND OBSOLETE=$sqlFalse AND J_ARCHIVED=$sqlFalse";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$jobs[] = $newArray[0];

	foreach ($jobs as $job_id)
		sql_archive_job($job_id);

	$sql = "UPDATE CLIENT2 SET C_ARCHIVED=$sqlTrue WHERE CLIENT2_ID=$client2_id";
	audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'C_ARCHIVED', $sqlTrue);
	sql_execute($sql, true); # audited

	$sql = "UPDATE CLIENT2 SET PORTAL_PUSH=1 WHERE CLIENT2_ID=$client2_id";
	audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'PORTAL_PUSH', 1);
	sql_execute($sql, true); # audited

	sql_update_client($client2_id);

	$portal_users = array();
	$sql = "SELECT USER_ID FROM USERV WHERE CLIENT2_ID=$client2_id";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$portal_users[] = $newArray[0];

	foreach ($portal_users as $pu_id)
	{
		$sql = "UPDATE USERV SET IS_ENABLED=$sqlFalse WHERE USER_ID=$pu_id";
		audit_setup_user($pu_id, 'USERV', 'USER_ID', $pu_id, 'IS_ENABLED', $sqlFalse);
		sql_execute($sql, true); # audited

		$sql = "UPDATE USERV SET PORTAL_PUSH=1 WHERE USER_ID=$pu_id";
		audit_setup_user($pu_id, 'USERV', 'USER_ID', $pu_id, 'PORTAL_PUSH', 1);
		sql_execute($sql, true); # audited
	}

} # sql_archive_client()

function sql_unarchive_client($client2_id)
{
	# Un-archive the client but NOT its jobs

	global $sqlFalse;

	$sql = "UPDATE CLIENT2 SET C_ARCHIVED=$sqlFalse WHERE CLIENT2_ID=$client2_id";
	audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'C_ARCHIVED', $sqlFalse);
	sql_execute($sql, true); # audited

	sql_update_client($client2_id);

} # sql_unarchive_client()

function sql_update_paid_so_far($job_id, $update_job=true)
{
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $sqlFalse;
	global $sqlTrue;

	$sql = "SELECT JC_PAID_SO_FAR, JC_TOTAL_AMT, JC_PAID_IN_FULL FROM JOB WHERE JOB_ID=$job_id";
	sql_execute($sql);
	$jc_paid_so_far = 0.0;
	$jc_total_amt = 0.0;
	$jc_paid_in_full = 0;
	while (($newArray = sql_fetch()) != false)
	{
		$jc_paid_so_far = floatval($newArray[0]);
		$jc_total_amt = floatval($newArray[1]);
		$jc_paid_in_full = intval($newArray[2]);
	}

	$sql = "SELECT COL_AMT_RX FROM JOB_PAYMENT WHERE (JOB_ID=$job_id) AND (COL_BOUNCED=$sqlFalse) AND (OBSOLETE=$sqlFalse) AND
				(COL_PAYMENT_ROUTE_ID IN ($id_ROUTE_tous, $id_ROUTE_direct, $id_ROUTE_fwd))";
	sql_execute($sql);
	$sum_rx = 0.0;
	while (($newArray = sql_fetch()) != false)
		$sum_rx += floatval($newArray[0]);

	$job_updated = false;

	if ($sum_rx != $jc_paid_so_far)
	{
		$sql = "UPDATE JOB SET JC_PAID_SO_FAR=$sum_rx WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_PAID_SO_FAR', $sum_rx);
		sql_execute($sql, true); # audited
		$job_updated = true;
	}

	if ($sum_rx < $jc_total_amt)
	{
		if ($jc_paid_in_full)
		{
			$sql = "UPDATE JOB SET JC_PAID_IN_FULL=$sqlFalse WHERE JOB_ID=$job_id";
			audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_PAID_IN_FULL', $sqlFalse);
			sql_execute($sql, true); # audited
			$job_updated = true;
		}
	}
	else
	{
		if (!$jc_paid_in_full)
		{
			$sql = "UPDATE JOB SET JC_PAID_IN_FULL=$sqlTrue WHERE JOB_ID=$job_id";
			audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_PAID_IN_FULL', $sqlTrue);
			sql_execute($sql, true); # audited
			$job_updated = true;
		}
	}

	if ($update_job && $job_updated)
		sql_update_job($job_id);

} # sql_update_paid_so_far()

function client_uninvoiced_payments($client2_id)
{
	# Find all payments that don't already appear on an invoice, but are no more than two years old, and are linked to an open job.
	# If the payments are paid directly to the client, then the client should be invoiced for the commission amount.
	# But invoice 110804 (ID 203004) is invoicing the client for "to us" payments.

	global $id_ROUTE_direct;
	global $id_ROUTE_tous;
	global $sqlFalse;
	global $sqlTrue;

	$include_to_us = true;
	$dt = date_n_years_ago(2);
	$sql = "SELECT DISTINCT JP.JOB_PAYMENT_ID, JP.COL_PAYMENT_ROUTE_ID, JP.COL_AMT_RX, JP.COL_DT_RX,
				JP.JOB_ID, JB.J_VILNO, PR.PR_CODE,
				CASE WHEN JP.COL_PERCENT IS NOT NULL
				THEN JP.COL_PERCENT
				ELSE
					CASE WHEN JB.JC_PERCENT IS NOT NULL
					THEN JB.JC_PERCENT
					ELSE
						CASE WHEN CL.COMM_PERCENT IS NOT NULL
						THEN CL.COMM_PERCENT
						ELSE 0.0
						END
					END
				END AS PAY_PERCENT
			FROM JOB_PAYMENT AS JP
				INNER JOIN JOB AS JB ON JB.JOB_ID=JP.JOB_ID
				INNER JOIN CLIENT2 AS CL ON CL.CLIENT2_ID=JB.CLIENT2_ID
				LEFT JOIN PAYMENT_ROUTE_SD AS PR ON PR.PAYMENT_ROUTE_ID=JP.COL_PAYMENT_ROUTE_ID
			WHERE (JB.CLIENT2_ID=$client2_id) AND (JB.JC_JOB=$sqlTrue) AND " .#(JB.JOB_CLOSED=$sqlFalse) AND
					"
					(JP.INVOICE_ID IS NULL) AND ('$dt' <= JP.COL_DT_RX) AND (JP.OBSOLETE=$sqlFalse) AND
					((JP.COL_PAYMENT_ROUTE_ID=$id_ROUTE_direct)" .
						($include_to_us ? (" OR (JP.COL_PAYMENT_ROUTE_ID=$id_ROUTE_tous)") : '') . ")
			ORDER BY JP.COL_DT_RX, JB.J_VILNO
		";
	#dprint($sql);#
	sql_execute($sql);
	$payments = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$payments[] = $newArray;
	return $payments;
} # client_uninvoiced_payments()

function client_uninvoiced_billings($client2_id)
{
	global $sqlFalse;
	global $sqlTrue;

	# Find all non-zero billings that don't already appear on an invoice, that are for trace jobs which require statement invoices,
	# where the job was closed within the last two years.
	# We only consider billings that have a line number of 1 or none, otherwise we will be double-counting some bills.

	$cutoff_dt = "2018-09-25";
	$cost_test = "(B.BL_COST IS NOT NULL) AND ((0 < B.BL_COST) OR ((B.BL_COST < 0) AND ('$cutoff_dt' <= J.J_CLOSED_DT)))";
				#"0 < B.BL_COST";

	$dt = date_n_years_ago(2);
	$sql = "SELECT B.INV_BILLING_ID, J.JOB_ID, J.J_VILNO, J.J_CLOSED_DT, B.BL_COST, T.JT_CODE
			FROM INV_BILLING AS B
			INNER JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
			LEFT JOIN JOB_TYPE_SD AS T ON T.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
			WHERE (B.OBSOLETE=$sqlFalse) AND ($cost_test) AND (B.INVOICE_ID IS NULL) AND (COALESCE(B.BL_LPOS,1) < 2) AND
					(J.CLIENT2_ID=$client2_id) AND (J.OBSOLETE=$sqlFalse) AND (J.JT_JOB=1) AND (J.J_S_INVS=$sqlTrue) AND ('$dt' < J.J_CLOSED_DT)
			ORDER BY J.J_CLOSED_DT";
	#dprint($sql);#
	sql_execute($sql);
	$billings = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$billings[] = $newArray;
	return $billings;
} # client_uninvoiced_billings()

function client_last_stmt_invoice($client2_id)
{
	global $sqlTrue;

	list($ms_top, $my_limit) = sql_top_limit(1);
	$sql = "SELECT $ms_top INVOICE_ID, INV_NUM, INV_DT FROM INVOICE
				WHERE CLIENT2_ID=$client2_id AND INV_STMT=$sqlTrue ORDER BY INV_DT DESC, INVOICE_ID DESC $my_limit";
	sql_execute($sql);
	$invoice = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$invoice = $newArray;
	return $invoice;
} # client_last_stmt_invoice()

function sql_delete_invoice($invoice_id)
{
	# Delete the invoice, but only after finding linked records in
	#		INV_ALLOC - delete these records, do audit.
	#		INV_BILLING - set INVOICE_ID to null, do audit.
	#		JOB_PAYMENT - set INVOICE_ID to null, do audit.
	#		INVOICE - set INV_PAID=0.0, OBSOLETE=1, do audit.

	global $sqlFalse;
	global $sqlTrue;

	$sql = "SELECT INV_NUM FROM INVOICE WHERE INVOICE_ID=$invoice_id";
	sql_execute($sql);
	$inv_num = '';
	while (($newArray = sql_fetch()) != false)
		$inv_num = $newArray[0];

	dprint("Deleting Invoice $inv_num (ID $invoice_id)", true);

	# --- Delete Allocation Records ----------------------------------------------

	$sql = "SELECT A.INV_ALLOC_ID, R.RC_NUM
			FROM INV_ALLOC AS A LEFT JOIN INV_RECP AS R ON R.INV_RECP_ID=A.INV_RECP_ID
			WHERE A.INVOICE_ID=$invoice_id
			";
	sql_execute($sql);
	$allocs = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$allocs[$newArray['INV_ALLOC_ID']] = $newArray['RC_NUM'];
	$del_count = 0;
	foreach ($allocs as $alloc_id => $rc_num)
	{
		dprint("...deleting Allocation (ID $alloc_id) from Receipt No $rc_num", true);
		$sql = "DELETE FROM INV_ALLOC WHERE INV_ALLOC_ID=$alloc_id";
		dprint($sql);
		audit_setup_gen('INV_ALLOC', 'INV_ALLOC_ID', $alloc_id, '', '');
		sql_execute($sql, true); # audited
		$del_count++;
	}
	if (!$del_count)
		dprint("...no Allocations found for this invoice", true);

	# --- Detach Billing Records ----------------------------------------------

	$sql = "SELECT B.INV_BILLING_ID, J.J_VILNO
			FROM INV_BILLING AS B LEFT JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
			WHERE B.INVOICE_ID=$invoice_id AND B.OBSOLETE=$sqlFalse
			";
	sql_execute($sql);
	$billings = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$billings[$newArray['INV_BILLING_ID']] = $newArray['J_VILNO'];
	$del_count = 0;
	foreach ($billings as $billing_id => $vilno)
	{
		dprint("...detaching Billing Line (ID $billing_id) from Job with VilNo $vilno", true);
		$sql = "UPDATE INV_BILLING SET INVOICE_ID=NULL WHERE INV_BILLING_ID=$billing_id";
		dprint($sql);
		audit_setup_gen('INV_BILLING', 'INV_BILLING_ID', $billing_id, 'INVOICE_ID', 'NULL');
		sql_execute($sql, true); # audited
		$del_count++;
	}
	if (!$del_count)
		dprint("...no Billing Lines found for this invoice", true);

	# --- Detach Payment Records ----------------------------------------------

	$sql = "SELECT P.JOB_PAYMENT_ID, J.J_VILNO
			FROM JOB_PAYMENT AS P LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
			WHERE P.INVOICE_ID=$invoice_id AND P.OBSOLETE=$sqlFalse
			";
	sql_execute($sql);
	$payments = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$payments[$newArray['JOB_PAYMENT_ID']] = $newArray['J_VILNO'];
	$del_count = 0;
	foreach ($payments as $payment_id => $vilno)
	{
		dprint("...detaching Payment (ID $payment_id) from Job with VilNo $vilno", true);
		$sql = "UPDATE JOB_PAYMENT SET INVOICE_ID=NULL WHERE JOB_PAYMENT_ID=$payment_id";
		dprint($sql);
		audit_setup_gen('JOB_PAYMENT', 'JOB_PAYMENT_ID', $payment_id, 'INVOICE_ID', 'NULL');
		sql_execute($sql, true); # audited
		$del_count++;
	}
	if (!$del_count)
		dprint("...no Payments found for this invoice", true);

	dprint("...deleting actual invoice", true);
	$sql = "UPDATE INVOICE SET INV_PAID=0.0 WHERE INVOICE_ID=$invoice_id";
	dprint($sql);
	audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'INV_PAID', 0.0);
	sql_execute($sql, true); # audited
	$sql = "UPDATE INVOICE SET OBSOLETE=$sqlTrue WHERE INVOICE_ID=$invoice_id";
	dprint($sql);
	audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'OBSOLETE', $sqlTrue);
	sql_execute($sql, true); # audited


} # sql_delete_invoice()

function invoice_info_collect($client2_id, $inv_dt)
{
	# Get information for the collection invoice that isn't got from sql_get_one_invoice().
	# Return info as an array.

	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $sqlFalse;

	$all_jobs_count = 0;
	$all_jobs_value = 0.0;
	$all_jobs_collected = 0.0;
	$closed_jobs_count = 0;
	$closed_jobs_value = 0.0;
	$paid_full_count = 0;
	$paid_full_collected = 0.0;

	$jobs = array();
	$jobs_paid = array();
	$sql = "SELECT JOB_ID, JOB_CLOSED, JC_TOTAL_AMT, JC_PAID_IN_FULL FROM JOB
			WHERE CLIENT2_ID=$client2_id AND (J_OPENED_DT<='$inv_dt') AND OBSOLETE=$sqlFalse AND J_ARCHIVED=$sqlFalse";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$jobs[] = $newArray['JOB_ID'];
		$all_jobs_count++;
		$value = floatval($newArray['JC_TOTAL_AMT']);
		if ($newArray['JOB_CLOSED'] == 1)
		{
			$closed_jobs_count++;
			$closed_jobs_value += $value;
		}
		if ($newArray['JC_PAID_IN_FULL'] == 1)
		{
			$paid_full_count++;
			$jobs_paid[] = $newArray['JOB_ID'];
		}
		$all_jobs_value += $value;
	}

	if ($jobs)
	{
		$sql = "SELECT SUM(COL_AMT_RX) FROM JOB_PAYMENT WHERE (JOB_ID IN (" . implode(',', $jobs) . ")) AND (COL_DT_RX <='$inv_dt') AND
					(OBSOLETE=$sqlFalse) AND (COL_PAYMENT_ROUTE_ID IN ($id_ROUTE_tous, $id_ROUTE_direct, $id_ROUTE_fwd))";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$all_jobs_collected = floatval($newArray[0]);
	}
	else
		$all_jobs_collected = 0.0;

	if ($jobs_paid)
	{
		$sql = "SELECT SUM(COL_AMT_RX) FROM JOB_PAYMENT WHERE (JOB_ID IN (" . implode(',', $jobs_paid) . ")) AND (COL_DT_RX <='$inv_dt') AND
					 (OBSOLETE=$sqlFalse) AND (COL_PAYMENT_ROUTE_ID IN ($id_ROUTE_tous, $id_ROUTE_direct, $id_ROUTE_fwd))";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$paid_full_collected = floatval($newArray[0]);
	}
	else
		$paid_full_collected = 0.0;

	$client_deduct_as = sql_select_single("SELECT DEDUCT_AS FROM CLIENT2 WHERE CLIENT2_ID=$client2_id");

	return array($all_jobs_count, $all_jobs_value, $all_jobs_collected, $closed_jobs_count, $closed_jobs_value,
					$paid_full_count, $paid_full_collected, $client_deduct_as);

} # invoice_info_collect()

function sql_bulk_save_note($text, $jobs)
{
	# Add $text to the comma-separated list of job IDs $jobs

	global $sqlFalse;
	global $USER;

	sql_encryption_preparation('JOB_NOTE');
	$j_note = sql_encrypt($text, '', 'JOB_NOTE');
	$now = "'" . date_now_sql() . "'";
	$jobs = explode(',', $jobs);
	foreach ($jobs as $job_id)
	{
		$fields = "JOB_ID,  J_NOTE,  IMPORTED,  JN_ADDED_ID,        JN_ADDED_DT";
		$values = "$job_id, $j_note, $sqlFalse, {$USER['USER_ID']}, $now       ";
		$sql = "INSERT INTO JOB_NOTE ($fields) VALUES ($values)";
		audit_setup_job($job_id, 'JOB_NOTE', 'JOB_NOTE_ID', 0, '', '');
		sql_execute($sql, true); # audited
		sql_update_job($job_id);
	}
	return '';

} # sql_bulk_save_note()

function sql_bulk_save_commission($rate, $jobs, $clients)
{
	# Set the commission rate for the comma-separated list of job IDs $jobs and/or list of clients

	$jc_percent = floatval($rate);
	if ($jobs)
	{
		$jobs = explode(',', $jobs);
		foreach ($jobs as $job_id)
		{
			$sql = "UPDATE JOB SET JC_PERCENT=$jc_percent WHERE JOB_ID=$job_id";
			audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_PERCENT', $jc_percent);
			sql_execute($sql, true); # audited
			sql_update_job($job_id);
		}
	}
	if ($clients)
	{
		$clients = explode(',', $clients);
		foreach ($clients as $client2_id)
		{
			$sql = "UPDATE CLIENT2 SET COMM_PERCENT=$jc_percent WHERE CLIENT2_ID=$client2_id";
			audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'COMM_PERCENT', $jc_percent);
			sql_execute($sql, true); # audited
			sql_update_client($client2_id);
		}
	}
	return '';

} # sql_bulk_save_commission()

function add_new_address($subject_id, $new_addr, $new_pc)
{
	global $job_id;

	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('ADDRESS_HISTORY');

	list($ms_top, $my_limit) = sql_top_limit(1);
	$sql = "SELECT $ms_top AD_TO_DT FROM ADDRESS_HISTORY WHERE JOB_SUBJECT_ID=$subject_id ORDER BY ADDRESS_HISTORY_ID DESC $my_limit";
	sql_execute($sql);
	$ad_from_dt = '1900-01-01';
	while (($newArray = sql_fetch()) != false)
		$ad_from_dt = $newArray[0];

	$ad_from_dt = "'{$ad_from_dt}'";
	$ad_to_dt = "'" . date_now_sql() . "'";

	$sql = "SELECT " . sql_decrypt('JS_ADDR_1') . ", " . sql_decrypt('JS_ADDR_2') . ", " . sql_decrypt('JS_ADDR_3') . ",
					" . sql_decrypt('JS_ADDR_4') . ", " . sql_decrypt('JS_ADDR_5') . ", " . sql_decrypt('JS_ADDR_PC') . "
			FROM JOB_SUBJECT WHERE JOB_SUBJECT_ID=$subject_id";
	sql_execute($sql);
	$addr_1 = '';
	$addr_2 = '';
	$addr_3 = '';
	$addr_4 = '';
	$addr_5 = '';
	$addr_pc = '';
	while (($newArray = sql_fetch()) != false)
	{
		$addr_1 = $newArray[0];
		$addr_2 = $newArray[1];
		$addr_3 = $newArray[2];
		$addr_4 = $newArray[3];
		$addr_5 = $newArray[4];
		$addr_pc = $newArray[5];
	}

	$addr_1 = sql_encrypt($addr_1, false, 'ADDRESS_HISTORY');
	$addr_2 = sql_encrypt($addr_2, false, 'ADDRESS_HISTORY');
	$addr_3 = sql_encrypt($addr_3, false, 'ADDRESS_HISTORY');
	$addr_4 = sql_encrypt($addr_4, false, 'ADDRESS_HISTORY');
	$addr_5 = sql_encrypt($addr_5, false, 'ADDRESS_HISTORY');
	$addr_pc = sql_encrypt($addr_pc, false, 'ADDRESS_HISTORY');

	$fields = "JOB_SUBJECT_ID, AD_FROM_DT,  AD_TO_DT,  ADDR_1,  ADDR_2,  ADDR_3,  ADDR_4,  ADDR_5,  ADDR_PC,  AD_NOTES";
	$values = "$subject_id,    $ad_from_dt, $ad_to_dt, $addr_1, $addr_2, $addr_3, $addr_4, $addr_5, $addr_pc, NULL";

	$sql = "INSERT INTO ADDRESS_HISTORY ($fields) VALUES ($values)";
	dprint($sql);
	audit_setup_job($job_id, 'ADDRESS_HISTORY', 'ADDRESS_HISTORY_ID', 0, '', '');
	sql_execute($sql, true); # audited

	for ($ii = count($new_addr); $ii < 5; $ii++)
		$new_addr[$ii] = '';

	if ($new_addr[0])
	{
		$adtxt = $new_addr[0];
		$adsql = sql_encrypt($adtxt, false, 'JOB_SUBJECT');
	}
	else
	{
		$adtxt = 'NULL';
		$adsql = 'NULL';
	}
	$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_1=$adsql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_ADDR_1', $adtxt);
	sql_execute($sql, true); # audited

	if ($new_addr[1])
	{
		$adtxt = $new_addr[1];
		$adsql = sql_encrypt($adtxt, false, 'JOB_SUBJECT');
	}
	else
	{
		$adtxt = 'NULL';
		$adsql = 'NULL';
	}
	$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_2=$adsql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_ADDR_2', $adtxt);
	sql_execute($sql, true); # audited

	if ($new_addr[2])
	{
		$adtxt = $new_addr[2];
		$adsql = sql_encrypt($adtxt, false, 'JOB_SUBJECT');
	}
	else
	{
		$adtxt = 'NULL';
		$adsql = 'NULL';
	}
	$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_3=$adsql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_ADDR_3', $adtxt);
	sql_execute($sql, true); # audited

	if ($new_addr[3])
	{
		$adtxt = $new_addr[3];
		$adsql = sql_encrypt($adtxt, false, 'JOB_SUBJECT');
	}
	else
	{
		$adtxt = 'NULL';
		$adsql = 'NULL';
	}
	$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_4=$adsql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_ADDR_4', $adtxt);
	sql_execute($sql, true); # audited

	if ($new_addr[4])
	{
		$adtxt = $new_addr[4];
		$adsql = sql_encrypt($adtxt, false, 'JOB_SUBJECT');
	}
	else
	{
		$adtxt = 'NULL';
		$adsql = 'NULL';
	}
	$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_5=$adsql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_ADDR_5', $adtxt);
	sql_execute($sql, true); # audited

	$js_outcode = '';
	if ($new_pc)
	{
		$js_outcode = postcode_outcode($new_pc);
		$adtxt = $new_pc;
		$adsql = sql_encrypt($adtxt, false, 'JOB_SUBJECT');
	}
	else
	{
		$adtxt = 'NULL';
		$adsql = 'NULL';
	}
	$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_PC=$adsql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_ADDR_PC', $adtxt);
	sql_execute($sql, true); # audited

	if ($js_outcode)
		$js_outcode_sql = "'$js_outcode'";
	else
		$js_outcode_sql = 'NULL';
	$sql = "UPDATE JOB_SUBJECT SET JS_OUTCODE=$js_outcode_sql WHERE JOB_SUBJECT_ID=$subject_id";
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $subject_id, 'JS_OUTCODE', $js_outcode);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

} # add_new_address()

function sql_save_letter($job_id, $job_letter_id, $letter_text, $letter_task)
{
	global $letter_system; # settings.php

//	$debug = false; #
//
//	if ($debug)
//	{
		# Feedback #197 - get rid of 194/160 characters
		$letter_text = str_replace(chr(194) . chr(160), '&nbsp;', $letter_text);

//		$html_codes = '';
//		for ($ii=0; $ii < strlen($letter_text); $ii++)
//			$html_codes .= "(" . ord($letter_text[$ii]) .")";
//		log_write("sql_save_letter($job_id, $job_letter_id, ###{$letter_text}###, $letter_task) ~~~{$html_codes}~~~");
//	}

	$sql = "SELECT JC_JOB FROM JOB WHERE JOB_ID=$job_id";
	sql_execute($sql);
	$jc_job = 0;
	while (($newArray = sql_fetch()) != false)
		$jc_job = intval($newArray[0]);
	if ($jc_job == 1)
		$letter_system = 'c'; # used by pdf_create()

	if (($letter_task == 'approve') || ($letter_task == 'approve_no_pdf') || ($letter_task == 'recreate_pdf'))
		$letter_text = letter_placeholder_resolution_kdb($job_id, $letter_text);
//	if ($debug)
//	{
//		$html_codes = '';
//		for ($ii=0; $ii < strlen($letter_text); $ii++)
//			$html_codes .= "(" . ord($letter_text[$ii]) .")";
//		log_write("sql_save_letter after resolution:###{$letter_text}### ~~~{$html_codes}~~~");
//	}

	sql_encryption_preparation('JOB_LETTER');
	$sql = "UPDATE JOB_LETTER SET JL_TEXT=" . sql_encrypt($letter_text, false, 'JOB_LETTER') . " WHERE JOB_LETTER_ID=$job_letter_id";
//	if ($debug) log_write("sql_save_letter SQL: $sql");
	dprint($sql);
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, 'JL_TEXT', $letter_text);
	sql_execute($sql, true); # audited

	if (($letter_task == 'approve') || ($letter_task == 'approve_no_pdf') || ($letter_task == 'recreate_pdf'))
	{
		$now = date_now_sql();
		if (($letter_task == 'approve') || ($letter_task == 'approve_no_pdf'))
			$date_field_name = 'JL_APPROVED_DT';
		else
			$date_field_name = 'JL_CREATED_DT';
		$sql = "UPDATE JOB_LETTER SET $date_field_name='$now' WHERE JOB_LETTER_ID=$job_letter_id";
		dprint($sql);
		audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, $date_field_name, $now);
		sql_execute($sql, true); # audited

		if ($letter_task != 'approve_no_pdf')
		{
			# Create a PDF of the letter and ensure its filename contains the same timestamp as JL_APPROVED_DT;
			# that way we can securely link the PDF to the JOB_LETTER record (along with JOB_LETTER_ID).
			$timestamp = str_replace(' ', '_', str_replace('-', '', str_replace(':', '', str_replace('.000', '', trim($now)))));
			$vilno = job_vilno_from_id($job_id);
			#dprint("sql_save_letter(): vilno=$vilno");#
			pdf_create_doc_letter($vilno, 'jl', $job_letter_id, $timestamp);
		}
	}
	elseif ($letter_task)
		dprint("** Letter Task \"$letter_task\" not recognised **", true);

	sql_update_letter($job_id, $job_letter_id);
	sql_update_job($job_id);
} # sql_save_letter()

function sql_letter_trace_unapprove($job_id, $job_letter_id)
{
	$sql = "UPDATE JOB_LETTER SET JL_APPROVED_DT=NULL WHERE JOB_LETTER_ID=$job_letter_id";
	dprint($sql);
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, 'JL_APPROVED_DT', 'NULL');
	sql_execute($sql, true); # audited

	sql_update_letter($job_id, $job_letter_id);
	sql_update_job($job_id);
} # sql_letter_trace_unapprove()

function sql_client_emails($client2_id, $for_select=false)
{
	global $sqlFalse;

	sql_encryption_preparation('CLIENT_CONTACT');
	$sql = "SELECT " . sql_decrypt('CC_EMAIL_1') . ", " . sql_decrypt('CC_EMAIL_2') . "
			FROM CLIENT_CONTACT WHERE CLIENT2_ID=$client2_id AND OBSOLETE=$sqlFalse
			ORDER BY CC_MAIN DESC, CLIENT_CONTACT_ID";
	#dprint($sql);#
	sql_execute($sql);
	$emails = array();
	while (($newArray = sql_fetch()) != false)
	{
		for ($ii = 0; $ii < 2; $ii++)
		{
			$temp = trim($newArray[$ii]);
			#dprint("found $temp");
			if ($temp && email_valid($temp))
			{
				if ($for_select)
					$emails[$temp] = $temp;
				else
					$emails[] = $temp;
			}
		}
	}
	return $emails;

} # sql_client_emails()

function sql_subject_emails($job_id, $for_select=false)
{
	global $sqlFalse;

	sql_encryption_preparation('JOB_PHONE');
	$sql = "SELECT " . sql_decrypt('JP_EMAIL') . "
			FROM JOB_PHONE WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse
			ORDER BY JP_PRIMARY_E DESC, " . sql_decrypt('JP_EMAIL') . "";
	#dprint($sql);#
	sql_execute($sql);
	$emails = array();
	while (($newArray = sql_fetch()) != false)
	{
		$temp = trim($newArray[0]);
		if ($temp && email_valid($temp))
		{
			if ($for_select)
				$emails[$temp] = $temp;
			else
				$emails[] = $temp;
		}
	}
	return $emails;

} # sql_subject_emails()

function sql_agent_job_stats($user_id)
{
	# Find number of open trace jobs assigned to this agent that are older than ten days

	global $id_JOB_TYPE_rt1;
	global $id_JOB_TYPE_rt2;
	global $id_JOB_TYPE_rt3;
	global $id_JOB_TYPE_trc;
	global $sqlFalse;
	global $sqlTrue;

	$job_stats = array(	'OPEN_TRACE_DAYS' => 10,	'OPEN_TRACE_COUNT' => 0,	'OPEN_TRACE_COUNT_COMP_NO' => 0,
						'OPEN_RETRACE_DAYS' => 5,	'OPEN_RETRACE_COUNT' => 0,	'OPEN_RETRACE_COUNT_COMP_NO' => 0,
						'OPEN_T_LIMIT' => 20,		'OPEN_T_COUNT' => 0,		'OPEN_T_COUNT_COMP_NO' => 0,
						'OPEN_C_LIMIT' => 0,		'OPEN_C_COUNT' => 0
						);

	$dt = date_from_epoch(true, time() - ($job_stats['OPEN_TRACE_DAYS'] * 24 * 60 * 60), false, false, true);
	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (J_OPENED_DT < '$dt') AND
											(JT_JOB_TYPE_ID=$id_JOB_TYPE_trc) AND J_ARCHIVED=$sqlFalse";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_TRACE_COUNT'] = intval($newArray[0]);

	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (J_OPENED_DT < '$dt') AND
											(JT_JOB_TYPE_ID=$id_JOB_TYPE_trc) AND J_ARCHIVED=$sqlFalse AND
											(J_COMPLETE=0 OR J_COMPLETE IS NULL)";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_TRACE_COUNT_COMP_NO'] = intval($newArray[0]);

	$dt = date_from_epoch(true, time() - ($job_stats['OPEN_RETRACE_DAYS'] * 24 * 60 * 60), false, false, true);
	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (J_OPENED_DT < '$dt') AND
											(JT_JOB_TYPE_ID IN ($id_JOB_TYPE_rt1,$id_JOB_TYPE_rt2,$id_JOB_TYPE_rt3)) AND J_ARCHIVED=$sqlFalse";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_RETRACE_COUNT'] = intval($newArray[0]);

	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (J_OPENED_DT < '$dt') AND
											(JT_JOB_TYPE_ID IN ($id_JOB_TYPE_rt1,$id_JOB_TYPE_rt2,$id_JOB_TYPE_rt3)) AND J_ARCHIVED=$sqlFalse AND
											(J_COMPLETE=0 OR J_COMPLETE IS NULL)";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_RETRACE_COUNT_COMP_NO'] = intval($newArray[0]);

	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (JT_JOB=$sqlTrue) AND J_ARCHIVED=$sqlFalse";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_T_COUNT'] = intval($newArray[0]);

	# Open trace jobs that have "Complete" = "No" or blank
	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (JT_JOB=$sqlTrue) AND J_ARCHIVED=$sqlFalse AND
											(J_COMPLETE=0 OR J_COMPLETE IS NULL)";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_T_COUNT_COMP_NO'] = intval($newArray[0]);

	$sql = "SELECT COUNT(*) FROM JOB WHERE (J_USER_ID=$user_id) AND (JOB_CLOSED=$sqlFalse) AND (JC_JOB=$sqlTrue) AND J_ARCHIVED=$sqlFalse";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$job_stats['OPEN_C_COUNT'] = intval($newArray[0]);

	return $job_stats;

} # sql_agent_job_stats()

function sql_get_client_targets($client2_id, $job_type_id)
{
	$targets = array();
	if (0 < $job_type_id)
	{
		$sql = "SELECT T.JOB_TARGET_ID, T.JTA_NAME
				FROM JOB_TARGET_SD AS T
				INNER JOIN CLIENT_TARGET_LINK AS K ON K.CLIENT2_ID=$client2_id AND K.JOB_TARGET_ID=T.JOB_TARGET_ID AND T.JOB_TYPE_ID=$job_type_id
				ORDER BY JTA_TIME DESC";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$targets[$newArray[0]] = $newArray[1];
	}
	return $targets;

} # sql_get_client_targets()

function sql_add_client_report($client2_id, $report_name, $report_dt)
{
	$client2_id = intval($client2_id);
	$report_name = quote_smart($report_name, true);
	$report_dt = date_for_sql($report_dt);
	$sql = "INSERT INTO CLIENT_REPORT (CLIENT2_ID, REPORT_NAME, REPORT_DT) VALUES ($client2_id, $report_name, $report_dt)";
	audit_setup_gen('CLIENT_REPORT', 'CLIENT_REPORT_ID', 0, '', '');
	#dprint($sql);#
	$report_id = sql_execute($sql, true); # audited
	#dprint("sql_add_client_report() returning new ID of $report_id"); #
	return $report_id;

} # sql_add_client_report()

function client_archived($client2_id)
{
	$archived = intval(sql_select_single("SELECT C_ARCHIVED FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));
	return ($archived ? true : false);
}

function pdf_create_doc_letter($vilno, $doctype, $doc_id, $timestamp)
{
	# Create a PDF of the document (e.g. job letter). Store in /csvex directory.
	# E.g. $doctype = 'jl' and $doc_id is the JOB_LETTER_ID
	# A zero letter ID means we should create a PDF of the report.

	global $cronjob; # settings.php and auto_xxx.php
	global $csv_dir; # csvex in settings.php
	global $pdf_error;
	global $pdf_message;
	global $unix_path;

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');

	$pdf_error = '';
	$pdf_filename = '';
	$html_body = '';

	if ($doctype == 'jl')
	{
		# Job Letter
		list($pdf_filename, $html_body) = pdf_create_letter($doc_id, $timestamp); # this may set $pdf_error
//		if ($doc_id == 9991343886) #
//		{
//			$html_codes = '';
//			for ($ii=0; $ii < strlen($html_body); $ii++)
//				$html_codes .= "(" . ord($html_body[$ii]) .")";
//			log_write("pdf_create_doc_letter: ###{$html_body}### ~~~{$html_codes}~~~");
//		}
		$pdf_dir = "{$csv_dir}/v{$vilno}";
		if ($cronjob)
			$pdf_dir = "{$unix_path}/{$pdf_dir}";
	}
	else
	{
		$pdf_error = "pdf_create_doc_letter($vilno, $doctype, $doc_id): doctype \"$doctype\" not recognised";
		$pdf_dir = '';
	}

	if (!$pdf_error)
	{
		if ($pdf_dir && check_dir($pdf_dir))
		{
			#dprint("Calling pdf_create(\"$pdf_dir\"");#
			$pdf_error = pdf_create($pdf_dir, $pdf_filename, $html_body);
		}
		else
			$pdf_error = "Failed to create directory \"$pdf_dir\"";
	}
	if ($pdf_error)
		$pdf_message[$doc_id] = "PDF Creation failed. Error: $pdf_error";
	else
		$pdf_message[$doc_id] = "The PDF has been created successfully.";

} # pdf_create_doc_letter()

function job_vilno_from_id($job_id)
{
	$job_id = intval($job_id);
	$sql = "SELECT J_VILNO FROM JOB WHERE JOB_ID=$job_id";
	sql_execute($sql);
	$vilno = '';
	while (($newArray = sql_fetch()) != false)
		$vilno = $newArray[0];
	return $vilno;
} # job_vilno_from_id()

function pdf_create_letter($doc_id, $timestamp)
{
	global $cr;
	global $crlf;
	global $lf;
	global $job_id;
	#global $pdf_error;

	#dprint("pdf_create_letter($doc_id)");#
	$job_letter_id = $doc_id;
	if (!$timestamp)
		$timestamp = strftime_rdr("%Y%m%d_%H%M%S");
	$job_pdf = sql_get_one_job($job_id, false);
	$pdf_filename = "letter_{$job_pdf['J_VILNO']}_{$job_pdf['J_SEQUENCE']}_{$job_letter_id}_{$timestamp}.pdf";
	$html_body = '';

	$sub_name = '';
	$sub_addr = array();
	$new_addr = array();
	if (0 < count($job_pdf['SUBJECTS']))
	{
		$sub = $job_pdf['SUBJECTS'][0]; # assume first subject is primary one
		$sub_name = trim("{$sub['JS_TITLE']} {$sub['JS_FIRSTNAME']} {$sub['JS_LASTNAME']}");
		$temp = trim($sub['JS_ADDR_1']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim($sub['JS_ADDR_2']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim($sub['JS_ADDR_3']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim($sub['JS_ADDR_4']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim($sub['JS_ADDR_5']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim($sub['JS_ADDR_PC']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim($sub['NEW_ADDR_1']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim($sub['NEW_ADDR_2']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim($sub['NEW_ADDR_3']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim($sub['NEW_ADDR_4']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim($sub['NEW_ADDR_5']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim($sub['NEW_ADDR_PC']);
		if ($temp)
			$new_addr[] = $temp;
	}

	#dprint("job=" . print_r($job_pdf['LETTERS_PENDING'],1));#
	if (0 < $job_letter_id)
	{
		$letter_body = '';
		foreach ($job_pdf['LETTERS_PENDING'] as $one_ltr)
		{
			if ($one_ltr['JOB_LETTER_ID'] == $job_letter_id)
			{
				$letter_body = $one_ltr['JL_TEXT'] . ' ' . $one_ltr['JL_TEXT_2'];
				#log_write("letter_body/pending=$html_body");#
				break;
			}
		}
		if ($letter_body == '')
		{
			foreach ($job_pdf['LETTERS_SENT'] as $one_ltr)
			{
				if ($one_ltr['JOB_LETTER_ID'] == $job_letter_id)
				{
					$letter_body = $one_ltr['JL_TEXT'] . ' ' . $one_ltr['JL_TEXT_2'];
					#log_write("letter_body/sent=$html_body");#
					break;
				}
			}
		}
		if ($letter_body == '')
			$letter_body = '(blank letter body)';
	}
	elseif ($job_pdf['JT_JOB'] == 1)
	{
		$letter_body = $job_pdf['TRACE_DETAILS']['JT_LET_REPORT'];
		$jt_job_type = $job_pdf['TRACE_DETAILS']['JT_JOB_TYPE_ID'];
		$jt_job_success = $job_pdf['TRACE_DETAILS']['JT_SUCCESS'];
		$letter_body = letter_for_trace_job($jt_job_type, $jt_job_success, $job_pdf['J_VILNO'], $job_pdf['CLIENT2_ID'], $job_pdf['CLIENT_REF'],
									$letter_body, $sub_name, $sub_addr, $new_addr, $job_pdf['PHONES']);
	}

	#$html_body = str_replace($lf, '<br>', str_replace($cr, '<br>', str_replace($crlf, '<br>', str_replace(' ', '&nbsp;', $letter_body))));
	## Only convert double spaces to non-breaking space
	#$html_body = str_replace($lf, '<br>', str_replace($cr, '<br>', str_replace($crlf, '<br>', str_replace('  ', '&nbsp;&nbsp;', $letter_body))));
	$html_body = str_replace($lf, '<br>', str_replace($cr, '<br>', str_replace($crlf, '<br>',
					str_replace('£', '&pound;', $letter_body))));

	#$html_body = "<div style=\"border:solid 1px red;\">$html_body</div>";

	return array($pdf_filename, $html_body);

} # pdf_create_letter()

function add_collect_letter($letter_type_id=0, $job_letter_id=0, $artificial_date='')
{
	# PDF Recreate 28/09/17: caller (jobs.php / mass_print_letters()) will send $job_letter_id
	# $artificial_date added 03/12/18 for CR #VIL-1

	global $auto_letter; # settings.php and auto_letter.php
	global $job_id;
	global $letter_system; # settings.php
	global $sqlFalse;
	global $sqlTrue;

	$recreate_pdf = ((0 < $job_letter_id) ? true : false);

	#dprint("add_collect_letter($letter_type_id, $job_letter_id), recreate_pdf=$recreate_pdf");#

	if ($letter_type_id == 0)
	{
		if ($recreate_pdf)
		{
			dprint("add_collect_letter(recreate_pdf): invalid letter type id \"$letter_type_id\"", true);
			return -1;
		}
		$letter_type_id = post_val('letter_id', true);
		if (!(0 < $letter_type_id))
		{
			dprint("add_collect_letter(): invalid letter type id \"" . post_val('letter_id') . "\"", true);
			return -1;
		}
	}

	sql_encryption_preparation('JOB_LETTER');

	$now = date_now_sql();
	$now_sql = "'$now'";
	if ($artificial_date)
	{
		$jl_added_dt = "'{$artificial_date}'";
		$no_pdf = true;
	}
	else
	{
		$jl_added_dt = $now_sql;
		$no_pdf = false;
	}

	$jl_text = '';
	$automatic_approve = false;
	list($jl_text, $automatic_approve) = letter_for_collect_job($job_id, $letter_type_id, $artificial_date);
	#log_write("jl_text=$jl_text");
	#print("inserting text = \"$jl_text\"");#
	$jl_text_sql = sql_encrypt($jl_text, false, 'JOB_LETTER');

	if (!$recreate_pdf)
	{
		$fields = "JOB_ID,  LETTER_TYPE_ID,  JL_ADDED_DT,  JL_TEXT,      IMPORTED";
		$values = "$job_id, $letter_type_id, $jl_added_dt, $jl_text_sql, $sqlFalse";

		if ($auto_letter)
		{
			$fields .= ", JL_AL";
			$values .= ", $sqlTrue";
		}

		if ($artificial_date)
		{
			$fields .= ", JL_POSTED_DT";
			$values .= ", $jl_added_dt";
		}

		$sql = "INSERT INTO JOB_LETTER ($fields) VALUES ($values)";
		if (!$auto_letter)
			dprint($sql);
		audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', 0, '', '');
		$job_letter_id = sql_execute($sql, true); # audited
		dprint("add_collect_letter(): new JOB_LETTER_ID=$job_letter_id");#
	}

	if ($automatic_approve || $recreate_pdf)
	{
		$letter_system = 'c'; # used by pdf_create()
		if ($recreate_pdf)
			$letter_task = 'recreate_pdf';
		elseif ($no_pdf)
			$letter_task = 'approve_no_pdf';
		else
			$letter_task = 'approve';
		sql_save_letter($job_id, $job_letter_id, $jl_text, $letter_task);
	}
	return $job_letter_id;

} # add_collect_letter()

function find_pdf($j_vilno, $prefix)
{
	# E.g. find_pdf($job['J_VILNO'], 'letter_')

	global $csv_dir;

	$dir = "{$csv_dir}/v{$j_vilno}";
	$files = scandir($dir);
	$fcount = count($files);
	for ($ii = $fcount-1; 0 <= $ii; $ii--)
	{
		$fname = $files[$ii];
		if (substr($fname, 0, strlen($prefix)) == $prefix)
		{
			if (substr($fname, -4) == ".pdf")
				return $fname;
		}
	}
	#dprint("scandir - " . print_r($files,1));
	return '';

} # find_pdf()

function sql_mark_as_sent($ticked_letter_ids)
{
	global $csv_dir;
	global $id_ACTIVITY_lse;

	$debug = false;#
	dprint("sql_mark_as_sent(" . print_r($ticked_letter_ids,1) . ")");
	$now = date_now_sql();
	foreach ($ticked_letter_ids as $letter_id)
	{
		$job_id = sql_select_single("SELECT JOB_ID FROM JOB_LETTER WHERE JOB_LETTER_ID=$letter_id");
		if ($debug) dprint("Letter $letter_id, Job $job_id");

		$sql = "UPDATE JOB_LETTER SET JL_POSTED_DT='$now' WHERE JOB_LETTER_ID=$letter_id";
		if ($debug) dprint($sql);
		audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $letter_id, 'JL_POSTED_DT', $now);
		if (!$debug) sql_execute($sql, true); # audited

		if (!$debug) sql_add_activity($job_id, 1, $id_ACTIVITY_lse, false); # don't call sql_update_job()

		if (!$debug) sql_update_letter($job_id, $letter_id);

		if (!$debug) sql_update_job($job_id);

		# CR #VIL-3 Delete PDFs after print, 18/12/18
		$sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JL_APPROVED_DT
				FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
				WHERE L.JOB_LETTER_ID=$letter_id
				";
		sql_execute($sql);
		$letter_filename = '';
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$dt0 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $newArray['JL_APPROVED_DT']))));
			$letter_filename = "$csv_dir/v{$newArray['J_VILNO']}/letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$letter_id}_{$dt0}.pdf";
		}
		if ($letter_filename)
		{
			# Delete existing PDF
			if (file_exists($letter_filename))
			{
				dprint("LETTER_ID $letter_id: Deleting PDF \"$letter_filename\"...");
				if (!$debug) unlink($letter_filename);
			}
			else
				dprint("LETTER_ID $letter_id: Letter PDF not found: \"$letter_filename\"");
		}
		else
			dprint("LETTER_ID $letter_id: Letter Filename not found.");

	} # foreach ($ticked_letter_ids)
} # sql_mark_as_sent()

function reset_jobs($ticked_jobs)
{
	global $ar;
	global $grey;
	global $id_LETTER_TYPE_letter_1;
	global $sqlTrue;

	if (!is_array($ticked_jobs))
		$ticked_jobs = array($ticked_jobs);

	sql_encryption_preparation('JOB');

	$vilnos = array();
	$now = date_now_sql(false, 1); # tomorrow
	foreach ($ticked_jobs as $job_id)
	{
		dprint("Resetting job $job_id...");

		$j_vilno = sql_select_single("SELECT J_VILNO FROM JOB WHERE JOB_ID=$job_id");
		$vilnos[$job_id] = $j_vilno;

		$sql = "UPDATE JOB SET JC_LETTER_MORE=$sqlTrue WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_LETTER_MORE', $sqlTrue);
		sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET JC_LETTER_TYPE_ID=$id_LETTER_TYPE_letter_1 WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_LETTER_TYPE_ID', $id_LETTER_TYPE_letter_1);
		sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET J_DIARY_DT='$now' WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_DIARY_DT', $now);
		sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET J_DIARY_TXT=" . sql_encrypt('Job Reset', false, 'JOB') . " WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_DIARY_TXT', 'Job Reset');
		sql_execute($sql, true); # audited

		sql_add_note($job_id, 'Job automatically reset to first letter etc', false);

		sql_update_job($job_id);
	}

	print "<h4>The following jobs have been Reset:</h4>
			<table>
			<tr><td>VILNo</td><td width=\"10\"></td><td $grey>DB ID</td></tr>
			";
			foreach ($vilnos as $job_id => $j_vilno)
				print "<tr><td $ar>$j_vilno</td><td></td><td $ar $grey>$job_id</td></tr>
					";
			print "
			</table>
			";

} # reset_jobs()

function job_change_client($job_id, $client2_id, $old_c_code, $new_c_code, $activity_id)
{
	if ((0 < $job_id) && (0 < $client2_id))
	{
		$sql = "UPDATE JOB SET CLIENT2_ID=$client2_id WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'CLIENT2_ID', $client2_id);
		sql_execute($sql, true); # audited

		sql_add_note($job_id, "Client code changed from $old_c_code to $new_c_code", false);

		if ($activity_id)
			sql_add_activity($job_id, 1, $activity_id, false);

		sql_update_job($job_id);
	}

} # job_change_client()

function job_change_user($job_id, $user_id, $old_user_inits, $new_user_inits, $activity_id)
{
	if ((0 < $job_id) && (0 < $user_id))
	{
		$sql = "UPDATE JOB SET J_USER_ID=$user_id WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_USER_ID', $user_id);
		sql_execute($sql, true); # audited

		sql_add_note($job_id, "Allocated User changed from $old_user_inits to $new_user_inits", false);

		if ($activity_id)
			sql_add_activity($job_id, 1, $activity_id, false);

		sql_update_job($job_id);
	}

} # job_change_user()

function agent_sql()
{
	# Used by both get_agent_id() and get_agent_initials()
	global $sqlFalse;
	global $sqlTrue;
	return "
			FROM USERV AS U
			LEFT JOIN USER_ROLE_SD AS RT ON U.USER_ROLE_ID_T=RT.USER_ROLE_ID
			LEFT JOIN USER_ROLE_SD AS RC ON U.USER_ROLE_ID_C=RC.USER_ROLE_ID
			WHERE (U.CLIENT2_ID IS NULL) AND U.IS_ENABLED=$sqlTrue AND U.U_HISTORIC=$sqlFalse AND
				(   (RT.UR_CODE IN ('MAN','SUP','REV','AGT')) OR (RC.UR_CODE IN ('MAN','SUP','REV','AGT'))   )
			";
} # agent_sql()

function get_agent_initials($sys='')
{
	global $agent_initials;

	sql_encryption_preparation('USERV');
	$sql = "SELECT U.USER_ID, U.U_INITIALS, U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME', '', true) . "
			" . agent_sql() . "
			ORDER BY U.U_INITIALS";
	#dprint($sql);#
	sql_execute($sql);
	$users = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$users[] = $newArray;

	$agent_initials = array();
	foreach ($users as $one)
	{
		if (($sys == '') || role_check($sys, 'agt', $one['USER_ID']))
			$agent_initials[strtoupper($one['U_INITIALS'])] = trim("{$one['U_FIRSTNAME']} {$one['U_LASTNAME']}");
	}
	#dprint("agent_initials=" . print_r($agent_initials,1));#
} # get_agent_initials()

function sql_get_portal_users($sc_c_code='', $include_disabled=false, $sc_text='', $sort_time=false)
{
	# Only Portal Users

	global $sqlTrue;
	global $portal_users;

	sql_encryption_preparation('USERV'); # for MS SQL Server
	sql_encryption_preparation('CLIENT2'); # for MS SQL Server

	$where = array('U.CLIENT2_ID IS NOT NULL'); # only portal users
	if ($sc_c_code)
	{
		$temp = explode(',', $sc_c_code);
		$codes = array();
		foreach ($temp as $cc)
		{
			$tmp = trim($cc);
			if (is_numeric_kdb($tmp, false, false, false))
				$codes[] = $tmp;
		}
		if ($codes)
			$where[] = "CL.C_CODE IN (" . implode(',', $codes) . ")";
	}
	if ($sc_text)
	{
		$sc_user_id = (($sc_text[0] == '*') ? intval(trim(str_replace('*', '', $sc_text))) : 0);
		if ($sc_user_id > 0)
			$where[] = "U.USER_ID = $sc_user_id";
		else
			$where[] = "(" . sql_decrypt('U.USERNAME') . " LIKE '%" . addslashes_kdb($sc_text) . "%') OR
						(U.U_FIRSTNAME LIKE '%" . addslashes_kdb($sc_text) . "%') OR
						(" . sql_decrypt('U.U_LASTNAME') . " LIKE '%" . addslashes_kdb($sc_text) . "%') OR
						(U.U_INITIALS = '" . addslashes_kdb($sc_text) . "')";
	}
	if (!$include_disabled)
		$where[] = "U.IS_ENABLED=$sqlTrue";
	if (0 < count($where))
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$sql = "SELECT U.USER_ID, U.CLIENT2_ID, " . sql_decrypt("U.USERNAME", '', true) . ",
				U.U_FIRSTNAME, " . sql_decrypt("U.U_LASTNAME", '', true) . ", U.U_INITIALS,
				U.IS_ENABLED, U.IS_LOCKED_OUT, " . sql_decrypt("U.U_EMAIL", '', true) . ",
				U.U_FIRST_DT, U.U_LAST_DT, CL.C_CODE, " . sql_decrypt("CL.C_CO_NAME", '', true) . "
				FROM USERV AS U
				LEFT JOIN CLIENT2 AS CL ON CL.CLIENT2_ID=U.CLIENT2_ID
				$where ORDER BY " . ($sort_time ? 'U.U_LAST_DT DESC' : ("CL.C_CODE, " . sql_decrypt("U.USERNAME"))) . "";
	sql_execute($sql);
	#dprint($sql);#
	$portal_users = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$portal_users[] = $newArray;
	#dprint(print_r($portal_users,1));#
	return $portal_users;
} # sql_get_portal_users()

function sql_get_one_portal_user($user_id)
{
	# Only a portal user.

	$user = '';
	if ($user_id > 0)
	{
		sql_encryption_preparation('USERV'); # for MS SQL Server

		$sql = "SELECT CLIENT2_ID, " . sql_decrypt('USERNAME', '', true) . ",
				" . sql_decrypt('U_EMAIL', '', true) . ", IS_ENABLED, IS_LOCKED_OUT,
				" . date_format_sql("CREATED_DT") . ", " . date_format_sql("U_FIRST_DT", 'hms') . ",
				" . date_format_sql("U_LAST_DT", 'hms') . ", " . sql_decrypt('U_NOTES', '', true) . ",
				U_FIRSTNAME, " . sql_decrypt('U_LASTNAME', '', true) . ", U_INITIALS, U_DEBUG, PORTAL_PUSH
				FROM USERV WHERE (CLIENT2_ID IS NOT NULL) AND USER_ID=$user_id";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$user = $newArray;
	}
	else
		$user = array('CLIENT2_ID' => '', 'USERNAME' => '',
						'U_EMAIL' => '', 'IS_ENABLED' => '1', 'IS_LOCKED_OUT' => '0',
						'CREATED_DT' => '', 'U_FIRST_DT' => '', 'U_LAST_DT' => '', 'U_NOTES' => '',
						'U_FIRSTNAME' => '', 'U_LASTNAME' => '', 'U_INITIALS' => '', 'U_DEBUG' => '', 'PORTAL_PUSH' => ''
						);
	return $user;

} # sql_get_one_portal_user()

function sql_create_portal_user($client2_id, $c_code, $p_email='', $p_fname='', $p_lname='')
{
	global $sqlNow;

	sql_encryption_preparation('USERV');

	$username = "C{$c_code}.User";
	$username = sql_encrypt($username, false, 'USERV');

	$password_raw = "password";
	$password = sql_encrypt($password_raw, false, 'USERV');

	if ($p_email)
		$u_email = sql_encrypt($p_email, false, 'USERV');
	else
	{
		#$u_email = "user@c{$c_code}.com";
		#$u_email = sql_encrypt($u_email, false, 'USERV');
		$u_email = 'NULL';
	}

	$is_enabled = 0;
	$is_locked_out = 0;

	$u_notes = "User auto-created";
	$u_notes = sql_encrypt($u_notes, false, 'USERV');

	$u_firstname = ($p_fname ? $p_fname : "UserFN");
	$u_firstname = quote_smart($u_firstname);

	$u_lastname = ($p_lname ? $p_lname : "UserLN");
	$u_lastname = sql_encrypt(quote_smart($u_lastname, true), true, 'USERV');

	$u_initials = (($p_fname && $p_lname) ? "{$p_fname[0]}{$p_lname[0]}" : "PU{$c_code}");
	$u_initials = sql_get_initials_unique($u_initials);
	$u_initials = quote_smart($u_initials);

	$fields = "CLIENT2_ID,  USERNAME,  PASSWORD,  U_EMAIL,  IS_ENABLED,  IS_LOCKED_OUT,  FAILED_LOGINS, ";
	$values = "$client2_id, $username, $password, $u_email, $is_enabled, $is_locked_out, 0,             ";

	$fields .= "CREATED_DT, U_FIRST_DT, U_LAST_DT, U_NOTES,  U_FIRSTNAME,  U_LASTNAME,  U_INITIALS,  PORTAL_PUSH";
	$values .= "$sqlNow,    NULL,       NULL,      $u_notes, $u_firstname, $u_lastname, $u_initials, 1          ";

	$sql = "INSERT INTO USERV ($fields) VALUES ($values)";
	$temp = "sql_create_portal_user(): " . str_replace($password_raw, "***", $sql);
	dprint($temp);
	log_write($temp);
	# Set up audit for new record insertion
	audit_setup_gen('USERV', 'USER_ID', 0, '', '');
	$insert_id = sql_execute($sql, true); # audited
	$temp = "sql_create_portal_user(): New USER_ID=$insert_id";
	dprint($temp);
	log_write($temp);

	$sql = "UPDATE CLIENT2 SET PORTAL_PUSH=1 WHERE CLIENT2_ID=$client2_id";
	$temp = "sql_create_portal_user(): $sql";
	dprint($temp);
	log_write($temp);
	audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'PORTAL_PUSH', 1);
	sql_execute($sql, true); # audited

} # sql_create_portal_user()

function sql_get_initials_unique($u_initials)
{
	# Given initials of new user, return those initials if they are not already in use,
	# otherwise add suffix so that they are unique.
	if (!$u_initials)
		return '';
	for ($ii = 0; $ii < 1000; $ii++)
	{
		$init2 = $u_initials . (($ii > 0) ? "$ii" : '');
		$sql = "SELECT COUNT(*) FROM USERV WHERE U_INITIALS='$init2'"; # Allow Portal users
		sql_execute($sql);
		$count = -1;
		while (($newArray = sql_fetch()) != false)
			$count = $newArray[0];
		if ($count == 0)
		{
			$u_initials = $init2;
			break;
		}
	}
	return strtoupper($u_initials);
} # sql_get_initials_unique()

?>
