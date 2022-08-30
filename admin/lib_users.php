<?php

# The list of users when the system is first set up.
# Each user has two blocks of data:
# 	First block:
#		Username, Password, Firstname, Lastname, Initials, Role/C, Role/T, Role/A, Salesperson, Historic user
# 	Second block:
#		A list where each one is: T or C, old login name, old "filename"

global $id_USER_ROLE_agent;
global $id_USER_ROLE_developer;
global $id_USER_ROLE_manager;
global $id_USER_ROLE_none;
global $id_USER_ROLE_rev;
global $id_USER_ROLE_super;

$last_live_user_id = 18; # AutoCU's user ID

$NEW_USERS = array(
		
		# Each user has two blocks.
		# Block[0] is login name, initial password, firstname, lastname, initials,
		#				role-c, role-t, role-a, whether sales, whether historic.
		# Block[1] is a list of login accounts that user had on the old system.
		
		'', # ID 0 (not a user)
		'', # ID 1 (KDB user, created by hand)

		# ID 2, system user
		array(	array(	'SYSTEM', 'k893FGHJDY232', 'System', 'System', 'ZSYS', 
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 0
					 ),
				array(	
					 )
			 ),
		# ID 3, house user
		array(	array(	'HOUSE', 'k893FGHJDY232', 'House', 'House', 'ZHOU', 
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 0
					 ),
				array(	
					 )
			 ),
		# ID 4, first real user
		array(	array(	'James', 'iu54wehfieue', 'James', 'Gordon Johnson', 'JGJ', 
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_none, 1, 0
					 ),
				array(		array(	'T', 'SALES JGJ',	'USER0042'),
							array(	'T', 'JGJ', 		'USER0041'),
							array(	'T', 'SALES04 JGJ',	'USER0043'),
							array(	'C', 'C-JGJ',		'USER0046'),
							array(	'C', 'JGJ',			'USER0050'),
							array(	'C', 'SALES JGJ',	'USER0052'),
							array(	'C', 'SALES04',		'USER0043'),
							array(	'C', 'SALES04 JGJ',	'USER0051'),
							array(	'T', 'C-JGJ',		'USER0044'),
							array(	'T', 'SALES04',		'USER0023')
					 )
			 ),
		# ID 5:
		array(	array(	'Rosita', 'wei8765ufehfif', 'Rosita', 'Kelly', 'RK', 
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_manager, 0, 0
					 ),
				array(		array(	'T', 'USER 1',		'USER0016'),
							array(	'C', 'R KELLY', 	'USER0037')
					 )
			 ),
		# ID 6:
		array(	array(	'Jim', 'wefui768vbsf', 'Jim', 'Patterson', 'JP', 
						$id_USER_ROLE_manager, $id_USER_ROLE_none, $id_USER_ROLE_manager, 0, 0
					 ),
				array(		array(	'T', 'USER 3',		'USER0017'),
							array(	'T', 'USER 5',		'USER0018'),
							array(	'C', 'J',			'USER0017'),
							array(	'C', 'J PATERSON',	'USER0018')
					 )
			 ),
		# ID 7:
		array(	array(	'Steve', 'Harry1234', 'Steve', 'Rowlands', 'SR', 
						$id_USER_ROLE_manager, $id_USER_ROLE_manager, $id_USER_ROLE_manager, 0, 0
					 ),
				array(		array(	'T', 'S ROWLANDS',	'USER0045'),
							array(	'T', 'USER 2',		'USER0007'),
							array(	'C', 'S ROWLANDS',	'USER0032')
					 )
			 ),
		# ID 8:
		array(	array(	'Suganya', 'wesf965hg', 'Suganya', 'Goulson', 'SG', 
						$id_USER_ROLE_manager, $id_USER_ROLE_manager, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'T', 'S GOULSON',	'USER0046'),
							array(	'C', 'S GOULSON',	'USER0013')
					 )
			 ),
		# ID 9:
		array(	array(	'Sharon', 'qpdfcnb2837djkf', 'Sharon', 'Harrison', 'SH', 
						$id_USER_ROLE_manager, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'C', 'S HARRISON',	'USER0030'),
							array(	'C', 'USER 15',		'USER0040'),
							array(	'T', 'USER 13',		'USER0000')
					 )
			 ),
		# ID 10:
		array(	array(	'Derek', 'swef87312jhn', 'Derek', 'Bevan', 'DB', 
						$id_USER_ROLE_manager, $id_USER_ROLE_manager, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'T', 'D BEVAN',		'USER0039'),
							array(	'T', 'USER 14',		'USER0004'),
							array(	'C', 'D BEVAN',		'USER0038'),
							array(	'C', 'USER 14',		'USER0042')
					 )
			 ),
		# ID 11:
		array(	array(	'Denise', 'wefkij378324', 'Denise', 'Gorman', 'DG', 
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_manager, 0, 0
					 ),
				array(		array(	'T', 'D GORMAN',	'USER0037'),
							array(	'C', 'D GORMAN',	'USER0044')
					 )
			 ),
		# ID 12:
		array(	array(	'Del', 'ewqdi2873df', 'Del', 'Hudson', 'DH', 
						$id_USER_ROLE_none, $id_USER_ROLE_manager, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'C', 'D HUDSON',	'USER0010'),
							array(	'T', 'USER 18',		'USER0012')
					 )
			 ),
		# ID 13:
		array(	array(	'Tony', 'iwef87234hi23', 'Tony', 'Morse-Woolford', 'TMW', 
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'T', 'USER 7',		'USER0031')
					 )
			 ),
		# ID 14:
		array(	array(	'Val', 'pms6732jr9', 'Val', 'Morse-Woolford', 'VMW', 
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'C', 'V MORSE',		'USER0059'),
							array(	'C', 'V MWOOLFORD',	'USER0058')
					 )
			 ),
		# ID 15:
		array(	array(	'Alex', '3kuwnf94hgf', 'Alex', 'Salamone', 'AS', 
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'T', 'USER 19',		'USER0036')
					 )
			 ),
		# ID 16:
		array(	array(	'Matt', 'qklf943ewqp3', 'Matt', 'Clarke', 'MC', 
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'C', 'M CLARKE',	'USER0022')
					 )
			 ),
		# ID 17:
		array(	array(	'Brett', 'g7w873hy6fv', 'Brett', 'Ruddock', 'BR', 
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 0
					 ),
				array(		array(	'C', 'BRETT RUDDOCK',	'BRETT RUDDOCK')
					 )
			 ),
		# ID 18:
		array(	array(	'AutoCU', '', '', 'Automatic Collection User', 'ACU', 
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'PENDING',		'USER0028')
					 )
			 ),
			 
		# The following users are Trace users who are "historic" i.e. no longer have a login
		
		array(	array(	'COLL02-T', '', '-', '-', "X" . ($last_live_user_id+1),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLL02',		'USER0026')
					 )
			 ),
		array(	array(	'COLL03-T', '', '-', '-', "X" . ($last_live_user_id+2),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLL03',		'USER0027')
					 )
			 ),
		array(	array(	'COLL04-T', '', '-', '-', "X" . ($last_live_user_id+3),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLL04',		'USER0029')
					 )
			 ),
		array(	array(	'EVE01-T', '', '-', '-', "X" . ($last_live_user_id+4),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'EVE01',		'USER0028')
					 )
			 ),
		array(	array(	'EVE02-T', '', '-', '-', "X" . ($last_live_user_id+5),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'EVE02',		'USER0013')
					 )
			 ),
		array(	array(	'USER TC-T', '', '-', '-', "X" . ($last_live_user_id+6),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER TC',		'USER0020')
					 )
			 ),
		array(	array(	'VILCOL-T', '', '-', '-', "X" . ($last_live_user_id+7),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'VILCOL',		'USER0033')
					 )
			 ),
		array(	array(	'COLIN-T', '', '-', '-', "X" . ($last_live_user_id+8),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLIN',		'USER0008')
					 )
			 ),
		array(	array(	'COLL05-T', '', '-', '-', "X" . ($last_live_user_id+9),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLL05',		'USER0030')
					 )
			 ),
		array(	array(	'COLL06-T', '', '-', '-', "X" . ($last_live_user_id+10),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLL06',		'USER0021')
					 )
			 ),
		array(	array(	'HOUSE-T', '', '-', '-', "X" . ($last_live_user_id+11),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'HOUSE',		'USER0038')
					 )
			 ),
		array(	array(	'SALES01-T', '', '-', '-', "X" . ($last_live_user_id+12),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'SALES01',		'USER0024')
					 )
			 ),
		array(	array(	'SALES02-T', '', '-', '-', "X" . ($last_live_user_id+13),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'SALES02',		'USER0034')
					 )
			 ),
		array(	array(	'SALES03-T', '', '-', '-', "X" . ($last_live_user_id+14),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'SALES03',		'USER0022')
					 )
			 ),
		array(	array(	'SALES05-T', '', '-', '-', "X" . ($last_live_user_id+15),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'SALES05',		'USER0014')
					 )
			 ),
		array(	array(	'USER 6-T', '', '-', '-', "X" . ($last_live_user_id+16),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 6',		'USER0006')
					 )
			 ),
		array(	array(	'Z5-T', '', '-', '-', "X" . ($last_live_user_id+17),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'Z5',			'USER0035')
					 )
			 ),
		array(	array(	'COLL01-T', '', '-', '-', "X" . ($last_live_user_id+18),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'COLL01',		'USER0025')
					 )
			 ),
		array(	array(	'S MURPHY-T', '', '-', '-', "X" . ($last_live_user_id+19),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'S MURPHY',	'USER0047')
					 )
			 ),
		array(	array(	'SALES-T', '', '-', '-', "X" . ($last_live_user_id+20),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'SALES',		'USER0040')
					 )
			 ),
		array(	array(	'USER 10-T', '', '-', '-', "X" . ($last_live_user_id+21),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 10',		'USER0003')
					 )
			 ),
		array(	array(	'USER 11-T', '', '-', '-', "X" . ($last_live_user_id+22),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 11',		'USER0002')
					 )
			 ),
		array(	array(	'USER 12-T', '', '-', '-', "X" . ($last_live_user_id+23),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 12',		'USER0005')
					 )
			 ),
		array(	array(	'USER 15-T', '', '-', '-', "X" . ($last_live_user_id+24),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 15',		'USER0001')
					 )
			 ),
		array(	array(	'USER 16-T', '', '-', '-', "X" . ($last_live_user_id+25),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 16',		'USER0010')
					 )
			 ),
		array(	array(	'USER 17-T', '', '-', '-', "X" . ($last_live_user_id+26),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 17',		'USER0009')
					 )
			 ),
		array(	array(	'USER 22-T', '', '-', '-', "X" . ($last_live_user_id+27),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 22',		'USER0015')
					 )
			 ),
		array(	array(	'USER 4-T', '', '-', '-', "X" . ($last_live_user_id+28),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 4',		'USER0019')
					 )
			 ),
		array(	array(	'USER 8-T', '', '-', '-', "X" . ($last_live_user_id+29),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'USER 8',		'USER0032')
					 )
			 ),
		array(	array(	'Z4-T', '', '-', '-', "X" . ($last_live_user_id+30),
						$id_USER_ROLE_none, $id_USER_ROLE_agent, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'T', 'Z4',			'USER0011')
					 )
			 ),
			 
		# The following users are Collection users who are "historic" i.e. no longer have a login
		
		array(	array(	'BRETT2-C', '', '-', '-', "X" . ($last_live_user_id+31),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'BRETT2',		'USER0064')
					 )
			 ),
		array(	array(	'C PERRY-C', '', '-', '-', "X" . ($last_live_user_id+32),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'C PERRY',		'USER0006')
					 )
			 ),
		array(	array(	'CANDY2-C', '', '-', '-', "X" . ($last_live_user_id+33),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'CANDY2',		'USER0066')
					 )
			 ),
		array(	array(	'COLIN-C', '', '-', '-', "X" . ($last_live_user_id+34),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'COLIN',		'USER0041')
					 )
			 ),
		array(	array(	'HOLD-C', '', '-', '-', "X" . ($last_live_user_id+35),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'HOLD',		'USER0055')
					 )
			 ),
		array(	array(	'HOUSE-C', '', '-', '-', "X" . ($last_live_user_id+36),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'HOUSE',		'USER0048')
					 )
			 ),
		array(	array(	'IVANA-C', '', '-', '-', "X" . ($last_live_user_id+37),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'IVANA',		'USER0056')
					 )
			 ),
		array(	array(	'J WILLIAMS-C', '', '-', '-', "X" . ($last_live_user_id+38),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'J WILLIAMS',	'USER0019')
					 )
			 ),
		array(	array(	'MATT2-C', '', '-', '-', "X" . ($last_live_user_id+39),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'MATT2',		'USER0065')
					 )
			 ),
		array(	array(	'S ARUMUGAM-C', '', '-', '-', "X" . ($last_live_user_id+40),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'S ARUMUGAM',	'USER0069')
					 )
			 ),
		array(	array(	'S MURPHY-C', '', '-', '-', "X" . ($last_live_user_id+41),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'S MURPHY',	'USER0031')
					 )
			 ),
		array(	array(	'S MURPHY2-C', '', '-', '-', "X" . ($last_live_user_id+42),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'S MURPHY2',	'USER0053')
					 )
			 ),
		array(	array(	'SALES-C', '', '-', '-', "X" . ($last_live_user_id+43),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'SALES',		'USER0049')
					 )
			 ),
		array(	array(	'VILCOL-C', '', '-', '-', "X" . ($last_live_user_id+44),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'VILCOL',		'USER0035')
					 )
			 ),
		array(	array(	'A MWOOLFORD-C', '', '-', '-', "X" . ($last_live_user_id+45),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'A MWOOLFORD',	'USER0063')
					 )
			 ),
		array(	array(	'ACT CREDIT MGT-C', '', '-', '-', "X" . ($last_live_user_id+46),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'ACT CREDIT MGT',	'USER0001')
					 )
			 ),
		array(	array(	'AMEXNB-C', '', '-', '-', "X" . ($last_live_user_id+47),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'AMEXNB',		'USER0060')
					 )
			 ),
		array(	array(	'AMEXPC-C', '', '-', '-', "X" . ($last_live_user_id+48),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'AMEXPC',		'USER0061')
					 )
			 ),
		array(	array(	'B DAVIS-C', '', '-', '-', "X" . ($last_live_user_id+49),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'B DAVIS',		'USER0002')
					 )
			 ),
		array(	array(	'B RUDDOCK-C', '', '-', '-', "X" . ($last_live_user_id+50),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'B RUDDOCK',	'USER0003')
					 )
			 ),
		array(	array(	'C GERHARDT-C', '', '-', '-', "X" . ($last_live_user_id+51),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'C GERHARDT',	'USER0004')
					 )
			 ),
		array(	array(	'C GULLOCK-C', '', '-', '-', "X" . ($last_live_user_id+52),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'C GULLOCK',	'USER0005')
					 )
			 ),
		array(	array(	'C RUSHTON-C', '', '-', '-', "X" . ($last_live_user_id+53),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'C RUSHTON',	'USER0007')
					 )
			 ),
		array(	array(	'C WATTS-C', '', '-', '-', "X" . ($last_live_user_id+54),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'C WATTS',		'USER0008')
					 )
			 ),
		array(	array(	'CAPQUEST-C', '', '-', '-', "X" . ($last_live_user_id+55),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'CAPQUEST',	'USER0009')
					 )
			 ),
		array(	array(	'COLLECT-C', '', '-', '-', "X" . ($last_live_user_id+56),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'COLLECT',		'USER0039')
					 )
			 ),
		array(	array(	'D LAROSE-C', '', '-', '-', "X" . ($last_live_user_id+57),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'D LAROSE',	'USER0011')
					 )
			 ),
		array(	array(	'E NILAND-C', '', '-', '-', "X" . ($last_live_user_id+58),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'E NILAND',	'USER0012')
					 )
			 ),
		array(	array(	'EVENING-C', '', '-', '-', "X" . ($last_live_user_id+59),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'EVENING',		'USER0047')
					 )
			 ),
		array(	array(	'G REIMANN-C', '', '-', '-', "X" . ($last_live_user_id+60),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'G REIMANN',	'USER0068')
					 )
			 ),
		array(	array(	'GPB-C', '', '-', '-', "X" . ($last_live_user_id+61),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'GPB',			'USER0014')
					 )
			 ),
		array(	array(	'GPB SOLS-C', '', '-', '-', "X" . ($last_live_user_id+62),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'GPB SOLS',	'USER0015')
					 )
			 ),
		array(	array(	'HL LEGAL-C', '', '-', '-', "X" . ($last_live_user_id+63),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'HL LEGAL',	'USER0016')
					 )
			 ),
		array(	array(	'IVANA2-C', '', '-', '-', "X" . ($last_live_user_id+64),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'IVANA2',		'USER0057')
					 )
			 ),
		array(	array(	'J NICHOLSON-C', '', '-', '-', "X" . ($last_live_user_id+65),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'J NICHOLSON',	'USER0054')
					 )
			 ),
		array(	array(	'K STOWNSON-C', '', '-', '-', "X" . ($last_live_user_id+66),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'K STOWNSON',	'USER0020')
					 )
			 ),
		array(	array(	'L DAVIS-C', '', '-', '-', "X" . ($last_live_user_id+67),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'L DAVIS',		'USER0021')
					 )
			 ),
		array(	array(	'M NILAND-C', '', '-', '-', "X" . ($last_live_user_id+68),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'M NILAND',	'USER0045')
					 )
			 ),
		array(	array(	'MACKENZIE HALL-C', '', '-', '-', "X" . ($last_live_user_id+69),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'MACKENZIE HALL',	'USER0023')
					 )
			 ),
		array(	array(	'MMURPHY-C', '', '-', '-', "X" . ($last_live_user_id+70),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'MMURPHY',		'USER0062')
					 )
			 ),
		array(	array(	'MNYLAND-C', '', '-', '-', "X" . ($last_live_user_id+71),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'MNYLAND',		'USER0024')
					 )
			 ),
		array(	array(	'NEW WORK-C', '', '-', '-', "X" . ($last_live_user_id+72),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'NEW WORK',	'USER0025')
					 )
			 ),
		array(	array(	'NOT ASSIGNED-C', '', '-', '-', "X" . ($last_live_user_id+73),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'NOT ASSIGNED',	'USER0026')
					 )
			 ),
		array(	array(	'OLD JOBS-C', '', '-', '-', "X" . ($last_live_user_id+74),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'OLD JOBS',	'USER0027')
					 )
			 ),
		array(	array(	'R MANSELL-C', '', '-', '-', "X" . ($last_live_user_id+75),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'R MANSELL',	'USER0029')
					 )
			 ),
		array(	array(	'S WARREN-C', '', '-', '-', "X" . ($last_live_user_id+76),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'S WARREN',	'USER0067')
					 )
			 ),
		array(	array(	'SC JOBS-C', '', '-', '-', "X" . ($last_live_user_id+77),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'SC JOBS',		'USER0033')
					 )
			 ),
		array(	array(	'SCOTCALL-C', '', '-', '-', "X" . ($last_live_user_id+78),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'SCOTCALL',	'USER0034')
					 )
			 ),
		array(	array(	'XARROW-C', '', '-', '-', "X" . ($last_live_user_id+79),
						$id_USER_ROLE_agent, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'XARROW',		'USER0036')
					 )
			 ),
		# Next one is not from Steve's spreadsheet, but needed for "ARCHIVES" user in some Trace jobs
		array(	array(	'ARCHIVES', '', '-', '-', "X" . ($last_live_user_id+80),
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'ARCHIVES',	'--------'),
							array(	'T', 'ARCHIVES',	'--------')
					 )
			 ),
		# Next one was found during import of Collection jobs.
		array(	array(	'G BODDY-C', '', '-', '-', "X" . ($last_live_user_id+81),
						$id_USER_ROLE_none, $id_USER_ROLE_none, $id_USER_ROLE_none, 0, 1
					 ),
				array(		array(	'C', 'G BODDY',	'--------')
					 )
			 )
		);
	
function user_id_from_old_username($old_username, $sys)
{
	global $NEW_USERS;
	
	$user_id = 0;
	$sys = strtoupper($sys);
	
	$found_login = '';
	$new_ix = 0;
	foreach ($NEW_USERS as $newu)
	{
		if ($newu)
		{
			$users_old_logins = $newu[1];
			foreach ($users_old_logins as $old_login)
			{
				if (	(($sys == '') || ($old_login[0] == $sys)) && 
						(strtolower($old_login[1]) == strtolower($old_username))
				   )
				{
					$found_login = $NEW_USERS[$new_ix][0][0];
					break;
				}
			}
			if ($found_login)
				break;
		}
		$new_ix++;
	}
	if ($found_login)
	{
		sql_encryption_preparation('USERV');
		$sql = "SELECT USER_ID FROM USERV WHERE (CLIENT2_ID IS NULL) AND " . sql_decrypt('USERNAME') . " = '$found_login'";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$user_id = $newArray[0];
	}
	return $user_id;
}

?>
