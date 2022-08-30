<?php

include_once("settings.php");
include_once("library.php");
global $ascii_pound;
global $cookie_user_id;
global $pound_194;
global $safe_amp;
global $safe_pound;
global $safe_slash;
#global $site_local;

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => xprint($_COOKIE[$cookie_user_id], false), 'U_DEBUG' => 0);

$operation = get_val('op');
$data_type = get_val('ty'); # see ledger.php / javascript() / update_invoice() for list of data types
$doctype = get_val('t');
if ($operation == 'appchk')
{
	$invoice_id = get_val('i', true);
	if (0 < $invoice_id)
	{
		sql_connect();
		$inv_approved = (sql_select_single("SELECT INV_APPROVED_DT FROM INVOICE WHERE INVOICE_ID=$invoice_id") ? 1 : 0);
		sql_disconnect();
	}
	else
		$inv_approved = 0;
	print "$inv_approved|$invoice_id|$doctype";
	exit;		
}
elseif (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'm') || ($data_type == 'p') || 
		($data_type == 'n') || ($data_type == 't') || ($data_type == 'e') || ($data_type == 'i'))
{
	if ($operation == 'ui') # update INVOICE table
	{
		$invoice_id = get_val('i', true);
		$inv_type = get_val('t');
		if ((0 < $invoice_id) && (($inv_type == 'I') || ($inv_type == 'C') || ($inv_type == 'F')))
		{
			$field_name = get_val('n');
			if ($field_name)
			{
				sql_connect();
				$abort = '';
				
				$field_name = strtoupper($field_name);
				$field_value_raw = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v'))));

				$return = 'ok';
				if (!$abort)
				{
					if ($field_value_raw == '__NULL__')
					{
						$field_value_unq = 'NULL';
						$field_value = 'NULL';
						$field_value_maths = 0.0;
					}
					else 
					{
						$field_value_unq = $field_value_raw;
						$force_quotes = false;
						if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'e') || ($data_type == 'i'))
							$force_quotes = true;
						$field_value = quote_smart($field_value_unq, $force_quotes);
						$field_value_maths = floatval($field_value_raw);
					}

					if (encrypted_field('INVOICE', $field_name))
					{
						sql_encryption_preparation('INVOICE');
						$field_value = sql_encrypt($field_value, true, 'INVOICE');
					}
					#print "#$field_value#";#
					
					$inv_net = 0.0;
					$vat_rate = 0.0;
					$inv_vat = 0.0; # amount
					$gross = 0.0;
					$inv_paid = 0.0;
					$outst = 0.0;
					$linked_id = 0;
					
					$now = date_now_sql();
					
					if ($field_name == 'INV_NET')
					{
						# First, get old INV_NET to work out VAT rate
						$sql = "SELECT INV_NET, INV_VAT, INV_PAID FROM INVOICE WHERE INVOICE_ID=$invoice_id";
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$inv_net = floatval($newArray[0]);
							$inv_vat = floatval($newArray[1]);
							$inv_paid = floatval($newArray[2]);
						}
						$vat_rate = ($inv_net ? (100.0 * $inv_vat / $inv_net) : 0.0);

						# Second, work on new INV_NET
						$inv_net = $field_value_maths;
						$inv_vat = $inv_net * 0.01 * $vat_rate;
						$gross = round($inv_net,2) + round($inv_vat,2);
						$outst = $gross - $inv_paid;

						$sql = "UPDATE INVOICE SET INV_NET=$inv_net WHERE INVOICE_ID=$invoice_id";
						#print "*$sql*";#
						audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'INV_NET', $inv_net);
						sql_execute($sql, true); # audited

						$sql = "UPDATE INVOICE SET INV_VAT=$inv_vat WHERE INVOICE_ID=$invoice_id";
						#print "*$sql*";#
						audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'INV_VAT', $inv_vat);
						sql_execute($sql, true); # audited
					}
					elseif ($field_name == 'X_VAT_RATE')
					{
						$sql = "SELECT INV_NET, INV_PAID FROM INVOICE WHERE INVOICE_ID=$invoice_id";
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$inv_net = floatval($newArray[0]);
							$inv_paid = floatval($newArray[1]);
						}

						$vat_rate = floatval($field_value_maths);
						$inv_vat = $inv_net * 0.01 * $vat_rate;
						$gross = round($inv_net,2) + round($inv_vat,2);
						$outst = $gross - $inv_paid;

						$sql = "UPDATE INVOICE SET INV_VAT=$inv_vat WHERE INVOICE_ID=$invoice_id";
						#print "*$sql*";#
						audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'INV_VAT', $inv_vat);
						sql_execute($sql, true); # audited
					}
					elseif ($field_name == 'INV_APPROVED_DT')
					{
						if ($field_value_raw == 0)
						{
							# User has "un-approved" the invoice
							$sql = "UPDATE INVOICE SET $field_name=NULL WHERE INVOICE_ID=$invoice_id";
							audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, $field_name, 'NULL');
							sql_execute($sql, true); # audited
						}
						else
						{
							$sql = "UPDATE INVOICE SET $field_name='$now' WHERE INVOICE_ID=$invoice_id";
							audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, $field_name, $now);
							sql_execute($sql, true); # audited
						}
					}
					else
					{
						if ($field_name == 'INV_NUM')
						{
							if ($field_value_raw != '__NULL__')
							{
								$inv_num = intval($field_value_raw);
								$sql = "SELECT COUNT(*) FROM INVOICE WHERE (INV_NUM=$inv_num) AND (INVOICE_ID<>$invoice_id)";#(INV_TYPE='{$inv_type}') AND 
								$count = 0.0;
								sql_execute($sql);
								while (($newArray = sql_fetch()) != false)
									$count = $newArray[0];
								if (0 < $count)
									$abort = "Document number $inv_num already exists for another document";
									#$return = "warning|Document number $inv_num already exists for another document";
							}
							else
								$abort = 'Please enter an Invoice Number';
						}
						elseif ($field_name == 'LINKED_INV_NUM')
						{
							# The document we are working on is an Invoice or a Credit, and the user wants to link it to a credit/invoice with 
							# number LINKED_INV_NUM.
							# We need to find that credit/invoice now, and check that it exists and that it is for the same client.
							# If OK then link the two together, which involves making a change to both documents.
							$other_type = (($doctype == 'I') ? 'C' : 'I');
							if ($field_value_raw == '__NULL__')
							{
								# Delete the link if it exists
								$sql = "SELECT LINKED_ID FROM INVOICE WHERE INVOICE_ID=$invoice_id";
								$linked_id = 0;
								sql_execute($sql);
								while (($newArray = sql_fetch()) != false)
									$linked_id = $newArray[0];
								$field_name = 'LINKED_ID';
								$field_value_unq = 'NULL';
								$field_value = 'NULL';
								$link_back = 'NULL';
							}
							else
							{
								$inv_num = intval($field_value_raw);
								$sql = "SELECT CLIENT2_ID FROM INVOICE WHERE INVOICE_ID=$invoice_id";
								$clid = 0;
								sql_execute($sql);
								while (($newArray = sql_fetch()) != false)
									$clid = $newArray[0];
								$sql = "SELECT INVOICE_ID FROM INVOICE 
										WHERE INV_TYPE='$other_type' AND INV_NUM=$inv_num AND CLIENT2_ID=$clid AND OBSOLETE=$sqlFalse";
								sql_execute($sql);
								$linked_id = 0;
								while (($newArray = sql_fetch()) != false)
									$linked_id = $newArray[0];
								if (0 < $linked_id)
								{
									$field_name = 'LINKED_ID';
									$field_value_unq = $linked_id;
									$field_value = $linked_id;
									$link_back = $invoice_id;
								}
								else
									$abort = (($other_type == 'I') ? "Invoice" : "Credit") . " number $inv_num does not exist for this client";
							}
						}
						if (!$abort)
						{
							$sql = "UPDATE INVOICE SET $field_name=$field_value WHERE INVOICE_ID=$invoice_id";
							#print "*$sql*";#
							audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, $field_name, $field_value_unq);
							sql_execute($sql, true); # audited
						}
						if (0 < $linked_id)
						{
							$sql = "UPDATE INVOICE SET LINKED_ID=$link_back WHERE INVOICE_ID=$linked_id";
							#print "*$sql*";#
							audit_setup_gen('INVOICE', 'INVOICE_ID', $linked_id, 'LINKED_ID', $link_back);
							sql_execute($sql, true); # audited
						}
					}

					if ((!$abort) && (($field_name == 'INV_NET') || ($field_name == 'X_VAT_RATE')))
					{
						$inv_net = sprintf("%.2f", $inv_net);
						$vat_rate = round($vat_rate,1);
						$inv_vat = sprintf("%.2f", $inv_vat);
						$gross = sprintf("%.2f", $gross);
						$outst = sprintf("%.2f", $outst);
						$return = "amounts|$inv_net|$vat_rate|$inv_vat|$gross|$outst";
					}
				}

				if ($abort)
					print "-1|$abort";
				else
					print "1|$return";
				sql_disconnect();
			}
			else 
				print "-1|ledger_ajax.php: no field name specified";
		}
		else 
			print "-1|ledger_ajax.php: bad invoice id or inv_type (invoiceID=$invoice_id, inv_type=\"$inv_type\")";
	} # operation 'ui'
	elseif ($operation == 'ur') # update INV_RECP table
	{
		$receipt_id = get_val('i', true);
		if (0 < $receipt_id)
		{
			$field_name = get_val('n');
			if ($field_name)
			{
				sql_connect();
				$abort = '';
				
				$field_name = strtoupper($field_name);
				$field_value_raw = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v'))));

				$return = 'ok';
				if (!$abort)
				{
					if ($field_value_raw == '__NULL__')
					{
						$field_value_unq = 'NULL';
						$field_value = 'NULL';
						#$field_value_maths = 0.0;
					}
					else 
					{
						$field_value_unq = $field_value_raw;
						$force_quotes = false;
						if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'e') || ($data_type == 'i'))
							$force_quotes = true;
						$field_value = quote_smart($field_value_unq, $force_quotes);
						#$field_value_maths = 1.0 * $field_value_raw;
					}

					if (encrypted_field('INV_RECP', $field_name))
					{
						sql_encryption_preparation('INV_RECP');
						$field_value = sql_encrypt($field_value, true, 'INV_RECP');
					}
					#print "#$field_value#";#
					
					if ($field_name == 'RC_NUM')
					{
						if ($field_value_raw != '__NULL__')
						{
							$rc_num = intval($field_value_raw);
							$sql = "SELECT COUNT(*) FROM INV_RECP WHERE (RC_NUM=$rc_num) AND (INV_RECP_ID<>$receipt_id) AND (OBSOLETE=$sqlFalse)";
							$count = 0.0;
							sql_execute($sql);
							while (($newArray = sql_fetch()) != false)
								$count = $newArray[0];
							if (0 < $count)
								$return = "warning|Document number $rc_num already exists for another document";
						}
						else
							$abort = 'Please enter an Receipt/Adjustment Number';
					}
					if (!$abort)
					{
						$sql = "UPDATE INV_RECP SET $field_name=$field_value WHERE INV_RECP_ID=$receipt_id";
						#print "*$sql*";#
						audit_setup_gen('INV_RECP', 'INV_RECP_ID', $receipt_id, $field_name, $field_value_unq);
						sql_execute($sql, true); # audited
					}
				}

				if ($abort)
					print "-1|$abort";
				else
					print "1|$return";
				sql_disconnect();
			}
			else 
				print "-1|ledger_ajax.php: no field name specified";
		}
		else 
			print "-1|ledger_ajax.php: bad receipt id (receiptID=$receipt_id)";
	} # operation 'ur'
	elseif ($operation == 'ua') # update INV_ALLOC table
	{
		$receipt_id = get_val('ri', true);
		$invoice_id = get_val('ii', true);
		$alloc_id = get_val('ai', true);
		if ((0 < $receipt_id) && (0 < $invoice_id) && (0 <= $alloc_id)) # $alloc_id will be zero if we need to create a new record
		{
			$field_name = 'AL_AMOUNT';
			sql_connect();
			$abort = '';
			$return = 'ok';
			
			$field_value_raw = get_val2('v');
			if ($field_value_raw == '__NULL__')
				$field_value_raw = 0.0;
			if (is_numeric_kdb($field_value_raw, true, true, true))
			{
				$field_value_raw = floatval($field_value_raw);
				if ($alloc_id == 0)
				{
					if ($field_value_raw != 0.0)
					{
						$sql = "INSERT INTO INV_ALLOC (INV_RECP_ID, INVOICE_ID,  AL_AMOUNT,        IMPORTED ) " .
											"VALUES (  $receipt_id, $invoice_id, $field_value_raw, $sqlFalse)";
						audit_setup_gen('INV_ALLOC', 'INV_ALLOC_ID', 0, '', '');
						$alloc_id = sql_execute($sql, true); # audited
					}
				}
				elseif ($field_value_raw != 0.0)
				{
					$sql = "UPDATE INV_ALLOC SET AL_AMOUNT=$field_value_raw WHERE INV_ALLOC_ID=$alloc_id";
					audit_setup_gen('INV_ALLOC', 'INV_ALLOC_ID', $alloc_id, 'AL_AMOUNT', $field_value_raw);
					sql_execute($sql, true); # audited
				}
				else
				{
					$sql = "DELETE FROM INV_ALLOC WHERE INV_ALLOC_ID=$alloc_id";
					audit_setup_gen('INV_ALLOC', 'INV_ALLOC_ID', $alloc_id, '', '');
					sql_execute($sql, true); # audited
				}
			}
			else
				$abort = 'Amount must be a number';
			
			if (!$abort)
				sql_update_inv_paid($invoice_id);
			
			if ($abort)
				print "-1|$abort";
			else
				print "1|$return";
			sql_disconnect();
		}
		else 
			print "-1|ledger_ajax.php: bad receipt/invoice/alloc id ($receipt_id/$invoice_id/$alloc_id)";
	} # operation 'ua'
	elseif ($operation == 'ub') # update INV_BILLING table
	{
		$inv_billing_id = get_val('bi', true);
		$invoice_id = get_val('ii', true);
		if ((0 < $inv_billing_id) && (0 < $invoice_id))
		{
			sql_connect();
			$field_name = get_val('n');
			if ($field_name)
			{
				sql_connect();
				$abort = '';
				
				$field_name = strtoupper($field_name);
				
//				if ($site_local)
//				{
//					# Works on local PC but not server ($safe_pound):
//					$field_value_raw = trim(str_replace($safe_pound, $pound_194, str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v')))));
//				}
//				else
//				{
//					# Works on server but not local PC ($safe_pound):
					$field_value_raw = trim(str_replace($safe_pound, chr($ascii_pound), str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v')))));
//				}
				
				$return = 'ok';
				if ($field_value_raw == '__NULL__')
				{
					$field_value_unq = 'NULL';
					$field_value = 'NULL';
					#$field_value_maths = 0.0;
				}
				else 
				{
					$field_value_unq = $field_value_raw;
					$force_quotes = false;
					if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'e') || ($data_type == 'i'))
						$force_quotes = true;
					$field_value = quote_smart($field_value_unq, $force_quotes);
					#$field_value_maths = 1.0 * $field_value_raw;
				}

				if (encrypted_field('INV_BILLING', $field_name))
				{
					sql_encryption_preparation('INV_BILLING');
					$field_value = sql_encrypt($field_value, true, 'INV_BILLING');
				}
				#print "#$field_value#";#

				$sql = "UPDATE INV_BILLING SET $field_name=$field_value WHERE INV_BILLING_ID=$inv_billing_id";
				#print "*$sql*";#
				audit_setup_gen('INV_BILLING', 'INV_BILLING_ID', $inv_billing_id, $field_name, $field_value_unq);
				sql_execute($sql, true); # audited

				print "1|$return";
				sql_disconnect();
			}
			else 
				print "-1|ledger_ajax.php: no field name specified";
		}
		else 
			print "-1|ledger_ajax.php: bad billing/invoice id ($inv_billing_id/$invoice_id)";
	} # operation 'ub'
	else 
		print "-1|ledger_ajax.php: operation \"$operation\" unrecognised";
} # if ($data_type...)
else 
	print "-1|ledger_ajax.php: bad data type";

?>
