<?php

include_once("settings.php");
include_once("library.php");
global $cookie_user_id;
global $id_USER_ROLE_none;

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => xprint($_COOKIE[$cookie_user_id],false), 'U_DEBUG' => 0);

$operation = get_val('op');
if ($operation == 'u') # update data in database (not portal user)
{
	$edit_user_id = get_val('i', true);
	if ($edit_user_id > 0)
	{
		$field_name = get_val('n');
		if ($field_name)
		{
			sql_connect();
			$abort = false;
			
			$field_name = strtoupper($field_name);
			$field_u_initials = (($field_name == 'U_INITIALS') ? true : false);
			$field_perm = ((substr($field_name, 0, strlen('PERM_')) == 'PERM_') ? true : false);
			$perm_id = ($field_perm ? intval(substr($field_name, strlen('PERM_'))) : 0);
			
			$field_value_unq = get_val2('v');
			if ($field_u_initials)
				$field_value_unq = strtoupper($field_value_unq);
			if (($field_name == 'USER_ROLE_ID_C') || ($field_name == 'USER_ROLE_ID_T') || ($field_name == 'USER_ROLE_ID_A'))
			{
				if ($field_value_unq == '__NULL__')
					$field_value_unq = $id_USER_ROLE_none;
			}
			if ($field_value_unq == '__NULL__')
			{
				$field_value_unq = 'NULL';
				$field_value = 'NULL';
			}
			else 
				$field_value = quote_smart($field_value_unq, true);
			
			if ($field_u_initials)
			{
				if (($field_value_unq == '') || ($field_value_unq == 'NULL'))
				{
					$abort = true;
					print "Initials must be specified";
				}
				else 
				{
					$sql = "SELECT COUNT(*) FROM USERV WHERE U_INITIALS=$field_value"; # Allow Portal users
					sql_execute($sql);
					$count = -1;
					while (($newArray = sql_fetch()) != false)
						$count = $newArray[0];
					if ($count > 0)
					{
						$abort = true;
						print "Sorry, those initials are already in use";
					}
				}
			}
			
			if ($field_perm)
			{
				# For the user being edited, a specific permission has been turned on or off.
				if ($field_value_unq == 1)
				{
					# Turned on
					$sql = "INSERT INTO USER_PERM_LINK (USER_ID,USER_PERMISSION_ID) VALUES ($edit_user_id,$perm_id)";
					#print $sql;#
					audit_setup_user($edit_user_id, 'USER_PERM_LINK', 'USER_PERM_LINK_ID', 0, '', '');
					sql_execute($sql, true); # audited
				}
				else
				{
					# Turned off
					$sql = "SELECT USER_PERM_LINK_ID FROM USER_PERM_LINK 
							WHERE USER_ID=$edit_user_id AND USER_PERMISSION_ID=$perm_id";
					sql_execute($sql);
					$del_perms = array();
					while (($newArray = sql_fetch()) != false)
						$del_perms[] = $newArray[0];
					foreach ($del_perms as $link_id)
					{
						$sql = "DELETE FROM USER_PERM_LINK WHERE USER_PERM_LINK_ID=$link_id";
						#print $sql;#
						audit_setup_user($edit_user_id, 'USER_PERM_LINK', 'USER_PERM_LINK_ID', $link_id, '', '');
						sql_execute($sql, true); # audited
					}
				}
			}
			elseif (!$abort)
			{
				if (encrypted_field('USERV', $field_name))
				{
					sql_encryption_preparation('USERV');
					$field_value = sql_encrypt($field_value, true, 'USERV');
				}
				#print "#$field_value#";#
				$sql = "UPDATE USERV SET $field_name=$field_value WHERE (CLIENT2_ID IS NULL) AND USER_ID=$edit_user_id";
				#print "*$sql*";#
				audit_setup_user($edit_user_id, 'USERV', 'USER_ID', $edit_user_id, $field_name, $field_value_unq);
				#sql_execute($sql);# removed auditing
				sql_execute($sql, true); # audited
			}
			
			if (!$abort)
				print "ok";
			sql_disconnect();
		}
		else 
			print "users_ajax.php/u: no field name specified";
	}
	else 
		print "users_ajax.php/u: bad edit user id";
} # not portal user
elseif ($operation == 'upu') # portal user: update data in database
{
	$edit_user_id = get_val('i', true);
	if ($edit_user_id > 0)
	{
		$field_name = get_val('n');
		if ($field_name)
		{
			sql_connect();
			$abort = false;
			
			$field_name = strtoupper($field_name);
			$field_u_initials = (($field_name == 'U_INITIALS') ? true : false);
			
			$field_value_unq = get_val2('v');
			if ($field_u_initials)
				$field_value_unq = strtoupper($field_value_unq);
			if ($field_value_unq == '__NULL__')
			{
				$field_value_unq = 'NULL';
				$field_value = 'NULL';
			}
			else 
				$field_value = quote_smart($field_value_unq, true);
			
			if ($field_u_initials)
			{
				if (($field_value_unq == '') || ($field_value_unq == 'NULL'))
				{
					$abort = true;
					print "Initials must be specified";
				}
				else 
				{
					$sql = "SELECT COUNT(*) FROM USERV WHERE U_INITIALS=$field_value"; # Allow non-portal users
					sql_execute($sql);
					$count = -1;
					while (($newArray = sql_fetch()) != false)
						$count = $newArray[0];
					if ($count > 0)
					{
						$abort = true;
						print "Sorry, those initials are already in use";
					}
				}
			}
			
			if (!$abort)
			{
				if (encrypted_field('USERV', $field_name))
				{
					sql_encryption_preparation('USERV');
					$field_value = sql_encrypt($field_value, true, 'USERV');
				}
				#print "#$field_value#";#
				$sql = "UPDATE USERV SET $field_name=$field_value WHERE (CLIENT2_ID IS NOT NULL) AND USER_ID=$edit_user_id";
				#print "*$sql*";#
				audit_setup_user($edit_user_id, 'USERV', 'USER_ID', $edit_user_id, $field_name, $field_value_unq);
				#sql_execute($sql);# removed auditing
				sql_execute($sql, true); # audited
				
				if (in_array($field_name, array('CLIENT2_ID', 'USERNAME', 'PASSWORD', 'U_FIRSTNAME', 'U_LASTNAME', 'U_INITIALS', 'U_EMAIL', 'IS_ENABLED', 'IS_LOCKED_OUT')))
				{
					$sql = "UPDATE USERV SET PORTAL_PUSH=1 WHERE USER_ID=$edit_user_id";
					audit_setup_user($edit_user_id, 'USERV', 'USER_ID', $edit_user_id, 'PORTAL_PUSH', 1);
					sql_execute($sql, true); # audited
				}
			}
			
			if (!$abort)
				print "ok";
			sql_disconnect();
		}
		else 
			print "users_ajax.php/upu: no field name specified";
	}
	else 
		print "users_ajax.php/upu: bad edit user id";
} # portal user
else 
	print "users_ajax.php: operation \"$operation\" unrecognised";

?>
