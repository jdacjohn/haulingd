<?php
require_once(dirname(__FILE__).'/project.inc.php');

class send_mail
{
    var $ids; //company ids that will recieve quote
	var $array; //post variables from the form
	var $lead_id; //the id of the lead being requested
	var $carriers_to_select = 1; //the max number of carriers to select BUT if 1 then refer to mySQL call in function as number changes according to db value.
    var $define_all = 999; //if company dist_type sendall, how much all is max.
    var $reminder_frequency = 50; //when a carrier's leads fall below their padding threshold, send a reminder every n leads
	var $debug = false; //enables or disables debugging output (for debugging live transactions)
	var $test = false; //enables or disables the use of test data instead of production data
	var $db = false;

	var $title = 'HD Premium Lead';
	var $email_from  = 'HD Premium Leads <leadinfo@haulingdepotleads.com>';
	var $email_admin = 'Hauling Depot <haulingauthority@yahoo.com>';
	var $email_reply_to = 'Hauling Depot <leadinfo@haulingdepotleads.com>';
	var $email_debug = 'haulingauthority@yahoo.com';
	var $email_test  = 'haulingauthority@yahoo.com';

	//CONSTRUCTOR
	function send_mail ($id = 0) {
		if ($id) {
			$this->lead_id = $id;
		}
		$this->db = &connectToDB();
	}

	function debug_start () {
		if ($this->debug) {
			@ob_start();
			$this->db->debug = true;
		}
	}

	function debug_end () {
		global $gDebug;
		if ($this->debug) {
			$ob = @ob_get_clean();
			$id = (isset($this->lead_id) && intval($this->lead_id)) ? $this->lead_id : date('m/d/y g:i:s');
			if (!
				mail(
					$this->email_debug,
					$this->title . " Request - Reference ID: " . $id,
					"<h1>Debug Trace - " . date('m/d/y g:i:s') . "</h1>\n" . $ob,
					"From: {$this->email_from}" . EMAIL_SEPARATOR . "MIME-Version: 1.0" . EMAIL_SEPARATOR . "Content-type: text/html; charset=iso-8859-1" . EMAIL_SEPARATOR
				)
			) {
				if ($gDebug)
					printvar('COULD NOT SEND MAIL!', 'debug_end');
			}
			else {
				if ($gDebug)
					printvar('MAIL SENT', 'debug_end');
			}
			if ($gDebug)
				echo $ob;
		}
	}

	function debug_print ($var, $label = '') {
		if ($this->debug) {
			printvar($var, $label);
		}
	}

	function test_email () {

	}

	function test_update () {

	}

	function test_insert () {

	}

	function mailer($to, $subject, $email, $headers = false) {
		$this->debug_print(func_get_args(), 'Mailer called with:');
		if(!mail($to, $subject, $email, $headers))
			$this->debug_print('Mailer failed!');
	}

	function build_array($array){
		$this->debug_start();
		$this->array = $this->clean_array($array); // make the info safe for sql insert
		if ($this->get_avaliable()) { //get all the avaliable companies to send quote
			//$this->process_user(); // send email and process the user
			$this->process_companies(); // send email to companies and update processed
		}
		$this->debug_print($this->lead_id, 'build_array() returning:');
		$this->debug_end();
		return $this->lead_id; //retrurn id to thank user
	}

    function build_array_all($array){
		$this->debug_start();
		$this->array = $this->clean_array($array); // make the info safe for sql insert
		if ($this->get_avaliable_all()) { //get all the avaliable companies to send quote
			//$this->process_user(); // send email and process the user
			$this->process_companies(); // send email to companies and update processed
		}
		$this->debug_print($this->lead_id, 'build_array_all() returning:');
		$this->debug_end();
		return $this->lead_id; //retrurn id to thank user
          }

	function clean_array($array){
		foreach($array as $key=>$value){
			// strip quotes if already in
			$value = str_replace("'","&#39;",$value);

			// Stripslashes
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			// Quote value
			if(version_compare(phpversion(),"4.3.0")=="-1") {
				$value = mysql_escape_string($value);
			} else {
				$value = mysql_real_escape_string($value);
			}
			$new_array[$key] = $value;
		}
		return $new_array;
	}

	/**
	 * Return a list of {$carriers_to_select} carriers.
	 */
	function get_avaliable() {
		//select {$carriers_to_select} carriers at random who haven't processes autos yet this round
		$ids = $this->selectRandomCompaniesWeighted($this->carriers_to_select);

		if (sizeof($ids) < $this->carriers_to_select) {
			//select additional carriers from the last round of processing
			//Note this currently has the potential to screw up concurrent processes... (Transactional support would help!!)
			//exclude already selected carriers
			if (sizeof($ids)) {
				$_ids = array();
				foreach ($ids as $id) { $_ids[] = intval($id['id']); }
				$where = 'WHERE id NOT IN (' . implode(',', $_ids) . ')';
			}
			else {
				$where = 'WHERE 1';
			}

			$this->db->Execute("UPDATE carriers SET process=0 $where");

			//$ids = $ids + $this->selectRandomCompaniesWeighted($this->carriers_to_select - sizeof($ids));
			$ids = array_merge($ids, $this->selectRandomCompaniesWeighted($this->carriers_to_select - sizeof($ids)));

			if (!sizeof($ids)) {
				//No companies were matched! Either the DB is empty/foobared, or it was a strict criteria (hawaii=1 for example) that couldn't be matched.
				//Send notice to admin, and report error to user
				global $arrErr;
				$arrErr[] = 'No companies could be found to match your request. A notice has been sent to the website administrators. Someone will be in touch shortly.';

				$this->send_admin('No companies could be found to match the following request:<br>' . str_replace("\n", '<br>', print_r($this->array, 1)));
				return false;
			}
			/* this elseif block messes with later code, I think it's OK to leave off for now
			 *
			elseif (sizeof($ids) < $this->carriers_to_select) {
				//perhaps there just aren't enough companies
				$b = array(
					'id'           => 0,
					'purchaseDate' => '0000-00-00',
					'process'      => 0,
					'package'      => 0,
					'leads'        => 0
				);
				$ids = array_pad($ids, $this->carriers_to_select, $b);
			}
			*/
		}
        $this->db->Execute("UPDATE carriers SET process=0");
		$this->ids = $ids;
		$this->debug_print($this->ids, 'get_available() final ids');
		return true;
	}

	/**
	 * Selects all applicable companies from the database and selects $carriers_to_select at random, but weighted
	 * based on their service/package level.
	 */
	function selectRandomCompaniesWeighted($carriers_to_select = 8) {

		//We need to do a manual query since there is no *.links.ini file - CDB - 2007-06-18
		//Note that since this site currently only accepts Auto (CategoryID == 1) quotes, we'll
		//ignore the other cats for now. They were probably only there in the first place as a
		//result of copying code and/or processing logic from the Haulingdepot.com site
		$sql = "SELECT c.*, p.leads as leads FROM carriers c LEFT JOIN packages p ON c.package_id = p.id WHERE c.active=1 AND process = 0 AND c.auto_leads_remaining>0 AND c.dist_type='rotation'";

		if($this->array['vehicle_pickup_state']=='AK' || $this->array['vehicle_destination_state']=='AK')
			$sql .= " AND c.alaska=1";
		if($this->array['vehicle_pickup_state']=='HI' || $this->array['vehicle_destination_state']=='HI')
			$sql .= " AND c.hawaii=1";

		//Dayparting:
		switch( strtolower( date('D') ) ) {
			case 'sat':
			case 'sun':
				//weekend
				$sql .= " AND c.dayparting=1";
				break;
		}
		
		//2007-11-29 - per Steve - if "Any" is selected, only select carriers with BOTH
		//types of transport
		if($this->array['carrier_type']=='open')
			$sql .= " AND c.open=1";
		elseif($this->array['carrier_type']=='enclosed')
			$sql .= " AND c.covered=1";
		else
			$sql .= " AND c.covered=1 AND c.open=1";

		$rs = $this->db->Execute($sql);


		/**
		 * Random selection process:
		 * 1. Calculate a number line whereby each carrier represents a portion of the total, the exact range being equal to the
		 *    size of their lead package. i.e. 1025, 1540, 2050, etc.
		 * 2. Keep track of the range of numbers per package.
		 * 3. Randomly select a number from the number line, and match it to a package based on the range it falls in.
		 * 4. Randomly select a carrier from the appropriate package level.
		 * 5. Wash, Rinse, Repeat.
		 */
$res = mysql_query("select rotationCap from options where id=1 LIMIT 1");
$ro = mysql_fetch_array($res);
$carriers_to_select = $ro[0];
        $carriers = array(); //Container for all found carriers.
		$packages = array(); //An 2-d array of carrier IDs. The keys to the first array are the string representation of the package sizes. (i.e. '2050')
		$numberline = 0; //An integer representing the current size of the numberline, computed as noted above.
		if (!$rs->EOF()) {
			do {
				$_carrier = array(); //Temp array
				$_carrier['id']=$rs->fields['id'];
				$_carrier['purchaseDate']=$rs->fields['purchase_date'];
				$_carrier['process']=$rs->fields['process'];
				$_carrier['package'] = $rs->fields['package_id'];
				$_carrier['leads'] = intval($rs->fields['leads']);
				$carriers["{$_carrier['id']}"] = $_carrier;

				//setup numberline, etc for random selection
				if (!array_key_exists("{$_carrier['leads']}", $packages))
					$packages["{$_carrier['leads']}"] = array();
				$packages["{$_carrier['leads']}"][] = $_carrier['id'];
				$numberline += $_carrier['leads'];
			} while ($rs->MoveNext());
		}
		else {
			//Do nothing - error will be raised in later steps
		}
		ksort($packages, SORT_NUMERIC);

		$carriers_selected = array();
		for ($i = 0; $i < $carriers_to_select && sizeof($packages); $i++) {
			$rand = mt_rand(1, $numberline);
			$min = 0;
			foreach ($packages as $package => $_carriers) {
				$max = $min + (intval($package) * sizeof($_carriers));
				if ($rand >= $min && $rand <= $max) {
					$_key = array_rand($_carriers);
					$_id = intval($_carriers[$_key]);
					$carriers_selected[] = array_merge(
						$carriers["{$_id}"],
						array(
							'rand' => $rand
						)
					);

					//update carrier data in DB
					$this->db->Execute("UPDATE carriers SET process = -1 WHERE id = " . intval($_id));

					//update numberline
					$numberline -= intval($package);
					array_splice($packages["$package"], $_key, 1);
					if (!sizeof($packages["$package"])) unset($packages["$package"]);

					//get next carrier
					continue 2;
				}
				else {
					$min = $max;
				}
			}
		}

		$this->debug_print($carriers_selected, 'selectRandomCompaniesWeighted() random ids');
		return $carriers_selected;
	}

	function get_avaliable_all() {
		//select {$carriers_to_select} carriers at random who haven't processes autos yet this round
		$ids = $this->sendToAllOutsideRotation($this->carriers_to_select);

		if (sizeof($ids) < $this->carriers_to_select) {
			//select additional carriers from the last round of processing
			//Note this currently has the potential to screw up concurrent processes... (Transactional support would help!!)
			//exclude already selected carriers
			if (sizeof($ids)) {
				$_ids = array();
				foreach ($ids as $id) { $_ids[] = intval($id['id']); }
				$where = 'WHERE id NOT IN (' . implode(',', $_ids) . ')';
			}
			else {
				$where = 'WHERE 1';
			}

            $this->db->Execute("UPDATE carriers SET process=0 $where");

			//$ids = $ids + $this->selectRandomCompaniesWeighted($this->carriers_to_select - sizeof($ids));
			$ids = array_merge($ids, $this->sendToAllOutsideRotation($this->carriers_to_select - sizeof($ids)));

			if (!sizeof($ids)) {
				//No companies were matched! Either the DB is empty/foobared, or it was a strict criteria (hawaii=1 for example) that couldn't be matched.
				//Send notice to admin, and report error to user
				global $arrErr;
				$arrErr[] = 'No companies could be found to match your request. A notice has been sent to the website administrators. Someone will be in touch shortly.';

				$this->send_admin('No companies could be found to match the following request:<br>' . str_replace("\n", '<br>', print_r($this->array, 1)));
				return false;
			}
			/* this elseif block messes with later code, I think it's OK to leave off for now
			 *
			elseif (sizeof($ids) < $this->carriers_to_select) {
				//perhaps there just aren't enough companies
				$b = array(
					'id'           => 0,
					'purchaseDate' => '0000-00-00',
					'process'      => 0,
					'package'      => 0,
					'leads'        => 0
				);
				$ids = array_pad($ids, $this->carriers_to_select, $b);
			}
			*/
		}
        $this->db->Execute("UPDATE carriers SET process=0");
		$this->ids = $ids;
		$this->debug_print($this->ids, 'get_available() final ids');
		return true;
	}

	function sendToAllOutsideRotation($define_all = 8) {

		//We need to do a manual query since there is no *.links.ini file - CDB - 2007-06-18
		//Note that since this site currently only accepts Auto (CategoryID == 1) quotes, we'll
		//ignore the other cats for now. They were probably only there in the first place as a
		//result of copying code and/or processing logic from the Haulingdepot.com site
		$sql2 = "SELECT c.*, p.leads as leads FROM carriers c LEFT JOIN packages p ON c.package_id = p.id WHERE c.active=1 AND process = 0 AND c.auto_leads_remaining>0 AND c.dist_type='sendall'";

		if($this->array['vehicle_pickup_state']=='AK' || $this->array['vehicle_destination_state']=='AK')
			$sql2 .= " AND c.alaska=1";
		if($this->array['vehicle_pickup_state']=='HI' || $this->array['vehicle_destination_state']=='HI')
			$sql2 .= " AND c.hawaii=1";

		//Dayparting:
		switch( strtolower( date('D') ) ) {
			case 'sat':
			case 'sun':
				//weekend
				$sql2 .= " AND c.dayparting=1";
				break;
		}

		//2007-11-29 - per Steve - if "Any" is selected, only select carriers with BOTH
		//types of transport
		if($this->array['carrier_type']=='open')
			$sql2 .= " AND c.open=1";
		elseif($this->array['carrier_type']=='enclosed')
			$sql2 .= " AND c.covered=1";
		else
			$sql2 .= " AND c.covered=1 AND c.open=1";

		$rs = $this->db->Execute($sql2);

		/**
		 * Random selection process:
		 * 1. Calculate a number line whereby each carrier represents a portion of the total, the exact range being equal to the
		 *    size of their lead package. i.e. 1025, 1540, 2050, etc.
		 * 2. Keep track of the range of numbers per package.
		 * 3. Randomly select a number from the number line, and match it to a package based on the range it falls in.
		 * 4. Randomly select a carrier from the appropriate package level.
		 * 5. Wash, Rinse, Repeat.
		 */
        $define_all = 999;
        $carriers_all = array(); //Container for all found carriers.
		$packages_all = array(); //An 2-d array of carrier IDs. The keys to the first array are the string representation of the package sizes. (i.e. '2050')
		$numberline_all = 0; //An integer representing the current size of the numberline, computed as noted above.
		if (!$rs->EOF()) {
			do {
				$_carrier = array(); //Temp array
				$_carrier['id']=$rs->fields['id'];
				$_carrier['purchaseDate']=$rs->fields['purchase_date'];
				$_carrier['process']=$rs->fields['process'];
				$_carrier['package'] = $rs->fields['package_id'];
				$_carrier['leads'] = intval($rs->fields['leads']);
				$carriers_all["{$_carrier['id']}"] = $_carrier;

				//setup numberline, etc for random selection
				if (!array_key_exists("{$_carrier['leads']}", $packages_all))
					$packages_all["{$_carrier['leads']}"] = array();
				$packages_all["{$_carrier['leads']}"][] = $_carrier['id'];
				$numberline_all += $_carrier['leads'];
			} while ($rs->MoveNext());
		}
		else {
			//Do nothing - error will be raised in later steps
		}
		ksort($packages_all, SORT_NUMERIC);

		$carriers_selected_all = array();
		for ($i = 0; $i < $define_all && sizeof($packages_all); $i++) {
			$rand = mt_rand(1, $numberline_all);
			$min = 0;
			foreach ($packages_all as $package_all => $_carriers) {
				$max = $min + (intval($package_all) * sizeof($_carriers));
				if ($rand >= $min && $rand <= $max) {
					$_key = array_rand($_carriers);
					$_id = intval($_carriers[$_key]);
					$carriers_selected_all[] = array_merge(
						$carriers_all["{$_id}"],
						array(
							'rand' => $rand
						)
					);

					//update carrier data in DB
					$this->db->Execute("UPDATE carriers SET process = -1 WHERE id = " . intval($_id));

					//update numberline
					$numberline_all -= intval($package_all);
					array_splice($packages_all["$package_all"], $_key, 1);
					if (!sizeof($packages_all["$package_all"])) unset($packages_all["$package_all"]);

					//get next carrier
					continue 2;
				}
				else {
					$min = $max;
				}
			}
		}

        $this->debug_print($carriers_selected_all, 'sendToAllOutsideRotation() random ids');
		return $carriers_selected_all;
	}

	function process_companies(){
		for($i = 0; $i < sizeof($this->ids); $i++){
			$this->debug_print($this->ids[$i], 'process_companies() processing:');
			$id = $this->ids[$i]['id'];
			if ($id == 0) {
				$this->debug_print('Skipping id #0', 'process_companies()');
				continue;
			}
			$this->db->Execute("INSERT INTO carriers_leads (carrier_id, lead_id, updated_at, created_at) VALUES (?, ?, NOW(), NOW())", array($id, $this->lead_id));
			$this->send($id); //send email to company
			$this->update_process($id); //update process and leads_remaning
		}
		$this->send_admin();
	}

	function send_admin($error = false){
		$lead_email_1= $this->email_admin;
		if ($this->debug || $this->test)
			$lead_email_1 .= ", {$this->email_debug}";
		$EmailFormat=1;
		$subject = $this->title .
			(($error !== false) ? ' Error!' : ' Request - Reference ID: ' . $this->lead_id);
		$date = date("F d, Y", strtotime(trim($this->array['move_date'])));
		if ($error !== false) {
			$br = '<br>';

			$email_str = '<strong><u>There was an error while processing a lead</u></strong>:' .
				$br.$error.$br.$br;
		}
		require(dirname(__FILE__).'/mailer.inc.php');
		if (count($this->ids)) {
			$email_str .= "<strong><u>This Email Went to:</u></strong><br>";
			for($i=0; $i < sizeof($this->ids); $i++){
				$carrier_cont = $this->db->Execute('SELECT company_name FROM carriers WHERE id = ?', array($this->ids[$i]['id']));
				if ($carrier_cont && !$carrier_cont->EOF)
					$email_str .= $carrier_cont->fields['company_name']."<br>";
			}
			if (sizeof($this->ids) < $this->carriers_to_select) {
				$email_str .= "<br><strong><u>Note that the max number of carriers per lead was not reached. This could mean there aren't enough carriers in the database to satisfy the limit, or the selection criteria was too strict. (i.e. lead required service to Hawaii or Alaska)</u></strong>";
			}
		}
		else {
			$email_str .= "<strong>An error occurred before any carriers could be selected, or the error is that no carriers could be found. Therefore, this lead <em>was not</em> sent to any carriers.</strong><br/>";
		}
		$this->mailer($lead_email_1, $subject, $email_str, $headers);
	}

	function send($id){ //sends emails to companies
		$carrier_cont = $this->db->Execute('SELECT * FROM carriers WHERE id = ?', array($id));
		if (!$carrier_cont || $carrier_cont->EOF) {
			$this->debug_print('could not find carrier info. no email sent.', '**send returning false**');
			return false;
		}

		$lead_email_1 = $carrier_cont->fields['lead_email_1'];
	 	$lead_email_2 = $carrier_cont->fields['lead_email_2'];
		if (isset($lead_email_2)) $lead_email_1 .= ", ".$lead_email_2;
		$EmailFormat = $carrier_cont->fields['email_format'];
		$subject = $this->title . " Request - Reference ID: {$this->lead_id}";
		$date = date("F d, Y", strtotime(trim($this->array['move_date'])));
		include(dirname(__FILE__).'/mailer.inc.php');
		$this->mailer($lead_email_1, $subject, $email_str, $headers);
	}

	function update_process($id){ // updates process and leads_remaining for each company
		$carrier = $this->db->Execute("SELECT * FROM carriers WHERE id = ?", array($id));

		$this->debug_print("in update_process($id)");
		$this->debug_print($carrier, "carrier($id)");
		$this->debug_print("new_leads = {$carrier->fields['auto_leads_remaining']}-1");
		$new_leads = $carrier->fields['auto_leads_remaining']-1;
		$this->db->Execute("UPDATE carriers SET auto_leads_remaining=? WHERE id=?", array($new_leads, $id));

		//refresh the carrier leads data
		$carrier = $this->db->Execute("SELECT c.*, p.leads, (p.leads * .1) as padding FROM carriers c LEFT JOIN packages p ON c.package_id = p.id WHERE c.id = ?", array($id));
		$this->debug_print($carrier, "{$carrier->fields['company_name']} leads (#$id)");

		$this->debug_print("carrier #$id: package = {$carrier->fields['leads']}, remaining leads = $new_leads, padding = {$carrier->fields['padding']}");

		if ($this->test || $new_leads == 0 || ($new_leads <= $carrier->fields['padding'] && $new_leads % $this->reminder_frequency == 0)) { //send reminder when leads are out or every n leads when they are under 10% of their package size (padding)

			if ($this->test && !($new_leads == 0 || ($new_leads <= $carrier->fields['padding'] && $new_leads % $this->reminder_frequency == 0))) {
				//we wouldn't normally be here, but we're testing something.
				$contact_email = array($this->email_test);
				$bcc = '';
			}
			else {
				$contact_email = array();
				if (strlen($carrier->fields['contact_email'])) $contact_email[] = strtolower(trim($carrier->fields['contact_email']));
				if (strlen($carrier->fields['lead_email_1']))  $contact_email[] = strtolower(trim($carrier->fields['lead_email_1']));
				if (strlen($carrier->fields['lead_email_2']))  $contact_email[] = strtolower(trim($carrier->fields['lead_email_2']));
				$contact_email = array_unique($contact_email);
				$bcc = $this->email_from;
				if ($this->debug || $this->test)
					$bcc .= ", {$this->email_debug}";
			}

			if (sizeof($contact_email)) {
				$contact_email = implode(',', $contact_email);
				$this->debug_print("sending renew email to carrier #$id");
				$headers = "From: ".$this->email_from.EMAIL_SEPARATOR;
				$headers .= "Content-type: text/plain; charset=iso-8859-1".EMAIL_SEPARATOR;
				$headers .= "BCC: $bcc".EMAIL_SEPARATOR;
				$subject = strtoupper($this->title) . 'S NOTICE';
				/**
				 * CDB - 2007-08-03
				 * Even though there are options for auto-debit in the company admin screens, there is
				 * currently no capacity to actually do auto-debits. So treat all auto_debit values
				 * the same.
				 */

				if ($new_leads == 0) {
					$body = "This is an auto generated email to let you know that your lead package has been depleted. No additional leads will be sent until your package is renewed.";
				}
				else {
					$remainder = round(($new_leads/$carrier->fields['leads'])*100);
					$body = "This is an auto generated email to let you know that you have less than {$remainder}% of your lead package remaining. If your account automatically renews itself (most do) please disregard this message.";
				}
				$body .= <<<EOF

Thank you.

Please email or call us @ 877-619-0710 if you have any questions or would like to otherwise upgrade/renew your lead package.
EOF;
				$this->mailer($contact_email, $subject, $body, $headers);
				$this->db->Execute("UPDATE carriers SET reminder = 1 WHERE id =?", array($id));
			}
			else {
				$this->debug_print("sending renew email to carrier #$id **FAILED**");
				$debug_email = $this->email_from;
				if ($this->debug || $this->test) $debug_email .= ", {$this->email_debug}";
				$this->mailer($debug_email, $this->title . ' Error', "The carrier \"{$carrier->company_name}\" is running out of leads, but does not have a contact email on file. Please update the company info with a valid email address.");
			}
		}
	}

	/* CDB - 2007-08-28
	 * This function is not used in this implimentation - the lead is saved outside of the
	 * class and the new ID is passed in when it is created
	 */
	function process_user(){ //adds the user to the leads table and returns $this->lead_id
		$leads = new DB_Leads();
		$leads->categoryid=$this->array['CategoryID'];
		$leads->vehicle_year=$this->array['vehicle_year'];
		$leads->vehicle_make=$this->array['vehicle_make'];
		$leads->vehicle_model=$this->array['vehicle_model'];
		$leads->vehicle_condition1=$this->array['vehicle_condition1'];
		$leads->mobilehometype=$this->array['MobileHomeType'];
		$leads->vehicle_length=$this->array['vehicle_length'];
		$leads->vehicle_width=$this->array['vehicle_width'];
		$leads->vehicle_weight=$this->array['vehicle_weight'];
		$leads->beam=$this->array['beam'];
		$leads->trailer=$this->array['Trailer'];

		$leads->trailertow=$this->array['TrailerTow'];
		$leads->vehicle_year2=$this->array['vehicle_year2'];
		$leads->vehicle_make2=$this->array['vehicle_make2'];
		$leads->vehicle_model2=$this->array['vehicle_model2'];
		$leads->vehicle_condition2=$this->array['vehicle_condition2'];
		$leads->transporttype=$this->array['TransportType'];
		$leads->aprox_wt=$this->array['APROX_WT'];
		$leads->vehicle_pickup_location=$this->array['vehicle_pickup_location'];
		$leads->vehicle_pickup_state=$this->array['vehicle_pickup_state'];
		$leads->vehicle_pickup_zipcode=$this->array['Vehicle_pickup_zipcode'];
		$leads->vehicle_destination_city=$this->array['Vehicle_destination_city'];
		$leads->vehicle_destination_state=$this->array['Vehicle_destination_state'];
		$leads->vehicle_destination_zipcode=$this->array['Vehicle_destination_zipcode'];
		$leads->move_date=$this->array['move-date-year']."-".$this->array['move-date-month']."-".$this->array['move-date-day'];
		$leads->flexable_move=$this->array['flexable_move'];
		$leads->carrier_type=$this->array['carrier_type'];
		$leads->customername=$this->array['CustomerName'];
		$leads->phone=$this->array['Phone'];
		$leads->email=$this->array['Email'];
		$leads->contactmethod=$this->array['ContactMethod'];
		$leads->comments=$this->array['Comments'];
		$leads->submit_date=date("Y-m-d");
		$leads->carrier_1=$this->ids[0]['id'];
		$leads->carrier_2=$this->ids[1]['id'];
		$leads->carrier_3=$this->ids[2]['id'];
		$leads->carrier_4=$this->ids[3]['id'];
		$leads->carrier_5=$this->ids[4]['id'];
		$leads->carrier_6=$this->ids[5]['id'];
		$leads->carrier_7=$this->ids[6]['id'];
		$leads->carrier_8=$this->ids[7]['id'];
		$this->lead_id = $leads->insert();
	}
}
?>
