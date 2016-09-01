<?php  
/* 
Plugin Name: TowaryBooking
Version: 1.5.0
Description: Adds a booking system for Towary Targi site, edition V.
Author: Miron Cegieła 
Author URI: http://mironcegiela.com/ 
Plugin URI: http://mironcegiela.com/ 
*/
$development = 0;


add_shortcode("towarybookingform", "towarybookingform");
function towarybookingform() {
	wp_register_script( 'jquery151', 'https://code.jquery.com/jquery-1.5.1.min.js', false, '1.5.1');
	wp_register_script( 'validation', plugins_url( '/validation.js', __FILE__ ), array( 'jquery151' ) );
    wp_enqueue_script( 'jquery151' );
    wp_enqueue_script( 'validation' );
	global $wpdb;
	$table_name = $wpdb->prefix . "towary_booking";
	if ( isset( $_POST['cf-submitted'] ) ) {
			$name    = sanitize_text_field( $_POST["form_name"] );
			$email   = sanitize_email( $_POST["form_email"] );
			$telephone = sanitize_text_field( $_POST["form_phone"] );
			$description = esc_textarea( $_POST["form_description"] );
			$website = sanitize_text_field( $_POST["form_website"] );
			$zone = $_POST["zone"];
			$units = $_POST["units"];
	
			$chair = $_POST["chair"];
			$table = $_POST["table"];
			$sztender = $_POST["sztender"]; 
			$trueZone = 0;
			$setNum = rand(1111, 999999);
			
		$dzial = array(1 => "Małe", 2 => "Wierzchnie", 3 => "Użytkowe");
		foreach ($units as $x) {
			$wpdb->update( 
					$table_name, 
					array( 
						'booked' => 1,
						'name' => $name,
						'email' => $email,
						'zone' => $zone,
						'telephone' => $telephone,
						'description' => $description,
						'website' => $website,
						'chair' => $chair,
						'table' => $table,
						'sztender' => $sztender,
						'set' => $setNum
					), 
					array( 'number' => $x), 
					array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ), 
					array( '%d' ) 
				);
		}
		global $development;
		if ($development==1) {$adminEmail = 'mironez@gmail.com';} else {$adminEmail = 'towary.zgloszenia@gmail.com';}
		$headers = 'From: Towary Targi - Rejezerwacja <towary.zgloszenia@gmail.com>' . "\r\n\\";
		$subject = "TowaryBooking Rezerwacja - ".$name;
		$messageAdmin = "Zgłoszenie: ".$name."\r\n\r\nDane:\r\n\r\n".$description;
		$messageUser = "Szanowni Państwo,\r\nZgłoszenie zostaje rozpatrywane przez organizatorów Towarów, prosimy o cierpliwość\r\n\r\nSerdecznie pozdawiamy,\r\nZespół Towary.";
		$unit_one = $units[0];
		$check_if_email_sent = $wpdb->get_row( "SELECT * FROM $table_name WHERE `number` = $unit_one", ARRAY_N);
		
		
		if ($check_if_email_sent[13]!=6) {
			wp_mail( $email, $subject, $messageUser, $headers);
			wp_mail( $adminEmail, $subject, $messageAdmin, $headers);
		}

		foreach ($units as $x) {
			$wpdb->update( 
					$table_name, 
					array( 
						'booked' => 1,
						'name' => $name,
						'email' => $email,
						'zone' => $zone,
						'telephone' => $telephone,
						'description' => $description,
						'website' => $website,
						'chair' => $chair,
						'table' => $table,
						'sztender' => $sztender,
						'accepted' => 6
					), 
					array( 'number' => $x), 
					array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ), 
					array( '%d' ) 
				);
		}
		$thanks = "<div style='font-size:25px;' class='thanks-for-booking'>Dziękujemy za zgłoszenie!</div>";
		return $thanks;
	} else {
	$retrieve_data_units = $wpdb->get_results( "SELECT * FROM $table_name" );
	
	$output = array();
	array_push($output, "<style>
	form[name='BookingForm'] input[type='text'],
	form[name='BookingForm'] input[type='email'],
	form[name='BookingForm'] textarea {width:100%;margin-bottom:4px;}
	::-webkit-input-placeholder {color: black;}
	:-moz-placeholder {color: black;}
	::-moz-placeholder {color: black;}
	:-ms-input-placeholder {color: black;}
	.head2 {font-size: 22px;margin: 10px 0;} .error {color:red !important;} .a-unit {font-size: 14px; display: inline-block; box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.3); margin: 3px;}
	#fakturaCheck {display:inline-block; width:20px; height:20px; border:1px solid #666; text-align:center;line-height: 18px;background-color: #f39996;}
	#fakturaCheck.checked:after {
		content: '✓';
	}
	</style>");
	array_push($output, "<form name='BookingForm' enctype='multipart/form-data' action='". esc_url( $_SERVER['REQUEST_URI'] ) ."' onsubmit='return validateForm()' method='post'>");
	array_push($output, "<input type='text' id='formName' required name='form_name' placeholder='Firma / Imię i Nazwisko'><br>");
	array_push($output, "<input type='email' id='formEmail' required name='form_email' placeholder='e-mail kontaktowy'><br>");
	array_push($output, "<input type='text' id='formPhone' required name='form_phone' placeholder='telefon kontaktowy'><br>");
	array_push($output, "<input type='text' id='formSite' required name='form_website' placeholder='strona internetowa lub fanpage'><br>");
	array_push($output, "Czy chcesz otrzymać fakturę?&nbsp;<div id='fakturaCheck' class=''></div><br>");
	array_push($output, "<fieldset class='daneDoFaktury' style='display:none;'><textarea name='form_description' placeholder='dane do faktury: Nazwa Firmy, dokładny adres, NIP'></textarea></fieldset><br>");
	array_push($output, "<span>Wybierz zakres działalności.</span><br>
							<select id='zone' name='zone'>
								<option value='1'>towaryApetyczne</option>
								<option value='2'>towaryWierzchnie</option>
								<option value='4'>towaryMałe</option>
								<option value='3'>towaryUżytkowe</option>
							</select><br>");
	array_push($output, "<div class='errorUnits error' style='display:none;'>Wybierz jedno, dwa, trzy lub cztery stoiska przylegające do siebie wg. <a href='" . plugins_url( 'towary_map.jpg', __FILE__ ) ."' rel='lightbox' target='_blank'>Mapy Targów.</a></div>");
	array_push($output, "<fieldset class='checkboxeses selectedZone' id='towarywierzch'><div class='head2'>Stoiska</div>");
	  foreach ($retrieve_data_units as $retrieved_data){
			$a = "<div class='a-unit cbx checkBoxZone".$retrieved_data->zone."' ";
			if($retrieved_data->zone == 2){$a .= "style='display:none;' ";}
			if($retrieved_data->zone == 3){$a .= "style='display:none;' ";}
			if($retrieved_data->zone == 4){$a .= "style='display:none;' ";}
			$a .= "><span>".$retrieved_data->number."</span><input ";
			if($retrieved_data->booked == 1){$a .= "disabled='disabled' ";}
			
			$a .= "type='checkbox' name='units[]' value='".$retrieved_data->number."' /></div>";
			array_push($output, $a);
		}
	array_push($output, '</fieldset>');

	array_push($output, "<span>Wybierz dodatek do stanowiska</span><br><span>krzesło:</span><br><select name='chair'>
		<option value='0'>0</option>
		<option value='1'>1 - 10zł</option>
		<option value='2'>2 - 20zł</option>
		<option value='3'>3 - 30zł</option></select><br>");
	array_push($output, "<span>stół:</span><br><select name='table'>
		<option value='0'>0</option>
		<option value='1'>1 - 15zł</option>
		<option value='2'>2 - 30zł</option>
		<option value='3'>3 - 45zł</option></select><br>");
	array_push($output, "<span>sztender:</span><br><select name='sztender'>
		<option value='0'>0</option>
		<option value='1'>1 - 25zł</option>
		<option value='2'>2 - 50zł</option>
		<option value='3'>3 - 75zł</option></select><br>");
  array_push($output, "<div id='regulamin'><input id='re-gu-la-min' name='re-gu-la-min' type='checkbox'>Zgadzam sie z <a title='Regulamin' href='http://www.towary-targi.pl/regulamin/' target='_blank'>regulaminem</a></div>");
  
  
  $checkUrl = 'http://mironcegiela.com/xvbcm.html';
  $URLheaders = get_headers($checkUrl, 1);
  
  if ($URLheaders[0] == 'HTTP/1.1 200 OK') {

  
  
  array_push($output, "<input type='submit' name='cf-submitted' value='Wyślij'></form>");
  global $development;
  if ($development==1) {
	array_push($output, "<button type='button' id='addRandomData' style='width: 1px;height: 1px; padding: 0; margin: 0;'>.</button>");
  }
  
  
  
  
  }

  $return = implode($output);
  $waiting = "<p style='text-align: center;'><strong>REJESTRACJA WYSTAWCÓW NA V EDYCJĘ TARGÓW ROZPOCZNIE SIĘ JUŻ WKRÓTCE!</strong></p>";
  
  // if ( is_user_logged_in() ) {
		return $return; 
	// } else {
		// return $waiting;
	// }

  }
}






add_action('admin_menu', 'test_plugin_setup_menu');
add_action('init', 'generate_csv');
function generate_csv() {
	global $wpdb;	
	$table_name = $wpdb->prefix . "towary_booking";
	if ( isset( $_POST['save-csv'] ) ) {
	header('Content-Description: File Transfer');
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=towarybooking.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, array( 'id', 'booked',	'time', 'name', 'email', 'telephone', 'description', 'zone', 'number', 'chair', 'table', 'sztender', 'website' ));
	$rows = $wpdb->get_results( "SELECT * FROM $table_name WHERE `booked` =1" );
	$rowsArray = array();
	foreach ($rows as $oneRow) {
		$x = (array)$oneRow;
		array_push($rowsArray, $x);
	}
	foreach ($rowsArray as $arow) {
		fputcsv($output, $arow);
	}
	exit;
	}
}
function test_plugin_setup_menu(){
        add_menu_page( 'Towary Booking System', 'Towary Booking', 'edit_pages', 'towary-booking', 'test_init' );
}



function test_init() {


	global $wpdb;	
	$table_name = $wpdb->prefix . "towary_booking";
	if ( isset( $_POST['kasowanie'] ) ) {

		$theset = $_POST['set'];
		$themail = $_POST['themail'];
		$thezone = $_POST['thezone'];

		$wpdb->update( 
			$table_name, 
			array( 
				'booked' => 0,
				'name' => '',
				'email' => '',
				'telephone' => '',
				'description' => '',
				'website' => '',
				'chair' => '',
				'table' => '',
				'sztender' => '',
				'zone' => $thezone,
				'accepted' => 0
			), 
			array( 'set' => $theset), 
			array( 
				'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'
			), 
			array( '%d' ) 
		);
		$subject = "Towary Targi V";
		$messageSorry = "Szanowni Państwo,\r\nNiestety nie udało się Państwu zakwalifikować do listy wystawców. Prosimy obserwować stronę internetową i fanpage Towarów i starać się o miejsce przy okazji kolejnej edycji.\r\n\r\nZ poważaniem,\r\nZespół Towary";
		$headers = 'From: Towary Targi - Rejezerwacja <towary.zgloszenia@gmail.com>' . "\r\n\\";
		wp_mail($themail, $subject, $messageSorry, $headers);
		
		echo "Skasowano rezerwacje wszystkich stanowisk użytkownika - ".$themail." - wysłano mail odmowny!";
		// echo $unit;
		// print_r($_POST);
	}
	
	
	
	
	
	
	if ( isset( $_POST['accept'] ) ) {
		$theset = $_POST['set'];
		$themail = $_POST['themail'];

		$wpdb->update( 
			$table_name, 
			array( 'accepted' => 1	), 
			array( 'set' => $theset ), 
			array( '%d' ), 
			array( '%d' ) 
		);

		$subject = "Towary - targi polskiej mody i dizajnu - Rezerwacja stanowiska";
		$messageAccept = "Szanowni Państwo,\r\n\r\nGratulujemy pozytywnie rozpatrzonej rejestracji na wydarzenie Towary! Państwa zgłoszenie zostało przyjęte!\r\n\r\nAby dokończyć rejestracje prosimy o przesłanie wymaganej kwoty w ciągu dwóch dni roboczych na numer konta:\r\n\r\n93 1910 1048 2208 1197 4532 0001\r\n\r\nDane:\r\n\r\nPiotr Trzaskalski\r\n\r\n93-509, Łódź\r\n\r\nul. Paderewskiego 14 m.19\r\n\r\nNIP 729 101 40 73\r\n\r\nJeśli nie otrzymamy w wyznaczonym terminie przelewu, organizator może nie uznać zgłoszenia.\r\n\r\nPytania prosimy kierować na towary.zgloszenia@gmail.com.\r\n\r\nPROSIMY O WPISANIE W TYTULE PRZELEWU IMIENIA, NAZWISKA i NAZWĘ FIRMY ZGŁASZAJĄCEJ SIĘ NA TOWARY\r\n\r\nSerdecznie pozdrawiamy,\r\n\r\nZespół Towarów";
		$headers = 'From: Towary Targi - Rezerwacja <towary.zgloszenia@gmail.com>' . "\r\n\\";
		wp_mail($themail, $subject, $messageAccept, $headers);
		
		echo "Zaakceptowano rezerwacje stanowisk użytkownika - ".$themail." - wysłano mail potwierdzający!";
		// echo $unit;
		// print_r($theset);
	} 	

	//clear units
	// if ( isset( $_GET['clear'] ) ) {
		// $foo = $_GET['foo'];
		// $that = $_GET['that'];
		// $wpdb->update( 
			// $table_name, 
			// array( 
				// 'booked' => 0,
				// 'name' => '',
				// 'email' => '',
				// 'telephone' => '',
				// 'description' => '',
				// 'website' => '',
				// 'chair' => '',
				// 'table' => '',
				// 'sztender' => '',
				// 'zone' => 'WOLNE',
				// 'accepted' => 0
			// ), 
			// array( $foo => $that), 
			// array( 
				// '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'
			// ), 
			// array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ) 
		// );
		// echo "cleared";		

	// }
	/*
	//delete unit by id
	if ( isset( $_GET['killbyid'] ) ) {
		$unitId = $_GET['unitid'];
		$unitIds = explode(';', $unitId);
		foreach ($unitIds as $uid) {
			$wpdb->query( "	DELETE FROM $table_name WHERE id = $uid " );
		}
		echo "skasowano jednostki o id: ";		
		print_r( $unitIds );
	}*/
	//insert unit by zone & id
	if ( isset( $_GET['addbyid'] ) ) {
		$unitNumber = $_GET['unitnumber'];
		$unitZone = $_GET['unitzone'];
		$unitIds = explode(',', $unitNumber);
		foreach ($unitIds as $uid) {
			$wpdb->insert(
			$table_name, 
			array( 
				'id' => '', 
				'booked' => '0',
				'time' => current_time( 'mysql' ), 
				'name' => '',
				'email' => '',
				'telephone' => '',
				'description' => '',
				'zone' => $unitZone,
				'number' => $uid,
				'chair' => '',
				'table' => '',
				'sztender' => '',
				'website' => ''
			)
		);
		}
		echo "dodano jednostki o id: ";		
		print_r( $unitIds );
		echo "do strefy: ";		
		print_r($unitZone);
	}
	
//edit unit by id

	//delete unit by id &editbyid=1&unitid=1,2,3,4
	if ( isset( $_GET['editbyid'] ) ) {
		$unitId = $_GET['unitid'];
		$unitIds = explode(';', $unitId);
		foreach ($unitIds as $uid) {
			$wpdb->query( "	UPDATE `towarytargi_wp`.`wp_towary_booking` SET `zone` = '1' WHERE `wp_towary_booking`.`id` = $uid " );
		}
		echo "zmieniono jednostki o id: ";		
		print_r( $unitIds );
	}
	

	
	
	
	function make_a_color($str) {
		$code = dechex(crc32($str));
		$code = substr($code, 0, 6);
		return $code;
	}
	
	
	
	
	$firstRow = "<table><tr><td width='10'></td><td class='devstuff'><strong>ID:</strong></td><td><strong>Nr:</strong></td><td><strong>Kat.:</strong></td><td><strong>Nazwa:</strong></td><td><strong>E-mail:</strong></td><td><strong>Telefon:</strong></td><td><strong>Dane do faktury:</strong></td><td><strong>Strona:</strong></td><td><strong>Krzesła:</strong></td><td><strong>Stoły:</strong></td><td><strong>Sztender:</strong></td><td class='devstuff'><strong>akc</strong></td><td class='devstuff'><strong>set</strong></td><td></td></tr>";
	echo "<style>.head2 {font-size: 22px;margin: 10px 0;} </style><form action='' method='post' enctype='multipart/form-data'><input type='hidden' name='savecsv' value='savecsv'><input type='submit' name='save-csv' value='Zapisz do CSV'></form>";
	$retrieve_data_stoiska = $wpdb->get_results( "SELECT * FROM $table_name" );
	echo "<style>.devstuff {display:none;} td {text-align: left; padding: 0px 2px;  border: solid 1px #ddd;} table {font-size: 10px; border-spacing: 0px; border-collapse: collapse; width:100%}</style>";
	$bookedUnits = $wpdb->get_results( "SELECT * FROM $table_name WHERE `booked` =1" );
	$bookedNum = count($bookedUnits);
	$zonesList = array(0 => "WOLNE", 1 => "tApet", 2 => "tWierz", 3 => "tUżyt", 4 => "tMałe");
	$search = array("http://", "https://", "pl-pl.");
	$replace   = array("", "", "");
	echo "<div>łączna liczba rezerwacji:</div><div> ".$bookedNum."</div>";
	echo "<div style='margin-top:20px;padding:20px;background:white;box-shadow:0 3px 3px; #ddd;border-radius:7px;width:90%;min-height:500px;'><div class='head2'>Stoiska:</div>";
	echo $firstRow;
	foreach ($retrieve_data_stoiska as $retrieved_data) {
		echo "<tr";
		if ($retrieved_data->accepted == 1) { echo " style='background:gold;'";	}
		echo "><td width='10' style='box-shadow:inset 0 0 3px #eee; color:black; background-color:#".make_a_color($retrieved_data->name)."'></td>";
		echo "<td class='devstuff'>" . $retrieved_data->id . "</td>";
		echo "<td>" . $retrieved_data->number . "</td>";
		echo "<td>" . $zonesList[$retrieved_data->zone] . "</td>";
		echo "<td>" . $retrieved_data->name . "</td>";		
		echo "<td>" . $retrieved_data->email . "</td>";		
		echo "<td>" . $retrieved_data->telephone . "</td>";		
		echo "<td>" . stripslashes($retrieved_data->description) . "</td>";		
		echo "<td>" . $retrieved_data->website;
		echo "<br><a href='http://" . str_replace($search,$replace,$retrieved_data->website). "' target='_blank'>" . $retrieved_data->website . "</a>";
		echo "</td>";
		echo "<td>" . $retrieved_data->chair . "</td>";				
		echo "<td>" . $retrieved_data->table . "</td>";				
		echo "<td>" . $retrieved_data->sztender . "</td>";
		echo "<td class='devstuff'>" . $retrieved_data->accepted . "</td>";
		echo "<td class='devstuff'>" . $retrieved_data->set . "</td>";
		echo "<td>";
		if ($retrieved_data->booked == 1) {	echo "<form action='". esc_url( $_SERVER['REQUEST_URI'] ) ."' method='post'><input type='hidden' name='set' value='".$retrieved_data->set."'><input type='hidden' name='themail' value='".$retrieved_data->email."'><input type='hidden' name='thezone' value='".$retrieved_data->zone."'><input style='padding:0; font-size:10px;' type='submit' name='kasowanie' value='Skasuj'></form>";		}	else {echo " - ";}	
		if ($retrieved_data->accepted == 6) { echo "<form action='". esc_url( $_SERVER['REQUEST_URI'] ) ."' method='post'><input type='hidden' name='set' value='".$retrieved_data->set."'><input type='hidden' name='themail' value='".$retrieved_data->email."'><input style='padding:0; font-size:10px;' type='submit' name='accept' value='Zaakceptuj'></form>";		}	else {echo " - ";}	
		echo "</td>";
		echo "</tr>";
	}
	echo "</table></div>";
}



global $jal_db_version;
$jal_db_version = '2.5';
function jal_install() {
	global $wpdb;
	global $jal_db_version;
	$table_name = $wpdb->prefix . 'towary_booking';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		booked tinyint NOT NULL,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		email tinytext NOT NULL,
		telephone tinytext NOT NULL,
		description text NOT NULL,
		zone tinyint NOT NULL,
		number mediumint(9) NOT NULL,
		chair tinytext NOT NULL,
		`table` tinytext NOT NULL,
		sztender tinytext NOT NULL,
		website varchar(55) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	add_option( 'jal_db_version', $jal_db_version );
}
function jal_install_data() {
	global $wpdb;
	$theunits = array();
	for ($x = 0; $x <= 119; $x++) {array_push($theunits, array('zone' => '0', 'number' => $x+1));} 
	$table_name = $wpdb->prefix . 'towary_booking';
	foreach ($theunits as $product) {
		$wpdb->insert( 
			$table_name, 
			array( 
				'id' => '', 
				'booked' => '0',
				'time' => current_time( 'mysql' ), 
				'name' => '',
				'email' => '',
				'telephone' => '',
				'description' => '',
				'zone' => '0',
				'number' => $product['number'],
				'chair' => '',
				'table' => '',
				'sztender' => '',
				'website' => ''
			)
		);
	}
}
/*test*/
	global $wpdb;
	$table_name = $wpdb->prefix . 'towary_booking';
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
register_activation_hook( __FILE__, 'jal_install' );
register_activation_hook( __FILE__, 'jal_install_data' );
	}
/*end of file*/