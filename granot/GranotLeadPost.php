<?php
/**
* GranotLeadPost is used to post XML moving leads to the Granot API Gateway from the HaulingDepot.com
* Vehicle Shipping and International Vehicle Shipping pages.
*
* The GranotLeadPost class is constructed using the the form post field values, and a boolean indicating whether the move
* is an international move.  Once the class is instantiated, methods can be called to post the xml
* to the gateway.
*
* @package    GranotLeadPost
* @author     John Arnold <john@jdacsolutions.com>
* @copyright  Copyright (c) 2018 HaulingDepot.com
* @version    $1.0$
* @link       https://github.com/jdacjohn/GranotLeads
* @since      File available since Release 1.0
* @access     public
* @property   formVals - contains the moving leads company's form post values
* @property   intMove - boolean value indicating whether a move is international.
* @property   xmlArr - contains elements used to construct the XML content to be posted to Granot.
*
* @example  $myLeadPoster = new GranotLeadPost($posterID, $moverEmail, $formValues, $international);
* @example  $result = $myLeadPoster->postXMLLead();
*/

//require('./_includes/common.inc.php');
class GranotLeadPost {

  private $formVals = array();
  private $intMove = false;
  private $xmlArr = array(
    'custcode' => LEADS_API_ID, 'leadno' => '~', 'servtypeid'=> 0, 'firstname' => '~', 'lastname' => '~',
    'ocity' => '~', 'ostate' => '~', 'ozip' => '~', 'ocountry' => '~', 'dcity' => '~',
    'dstate' => '~', 'dzip' => '~', 'dcountry' => '~', 'weight' => '~', 'volume' => '~',
    'movesize' => '~', 'notes' => '~', 'movedte' => '~', 'movedte2' => '~', 'email' => '~',
    'phone1' => '~', 'phone2' => '~', 'cell' => '~', 'source' => LEAD_SOURCE, 'mincount' => '~',
    'maxcount' => '~', 'soldcount' => '~', 'moverref' => '~', 'repnotes' => '~',
    'others1txt' => '~', 'others1amt' => '~', 'others2txt' => '~', 'others2amt' => '~', 'label' => '~');

  /**
   * Return an instance of the class.
   * @param   postVals - array: array containing form post values.  NOT $_POST.
   * @param   international - bool:  Flag indicating an international move.
   * @param   moverRef - string - Subscriber Lead email
   * @param   leadID - int - Lead ID
   */
  public function __construct($postVals, $international, $moverRef, $leadID) {
    $this->formVals = $postVals;
    $this->intMove = $international;
    $this->populateXMLArray();
    $this->xmlArr['moverref'] = $moverRef;
    $this->xmlArr['leadno'] = $leadID;
  }

  /**
   * Build out contents of the private instance variable $xmlArr using the form post values
   * contained in the $formVals instance variable.
   */
  public function populateXMLArray() {
    // Determine move type and set the servtypeid appropriately.
    if ($this->intMove) {
      $this->xmlArr['servtypeid'] = INTL_MOVE;
    }

    // Figure out the zipcodes and cities.  Set the servtypeid based on what we find.
    $startLoc = explode(" ",$this->formVals['vehicle_pickup_location']);
    $endLoc = explode(" ",$this->formVals['vehicle_destination_city']);
    $this->xmlArr['ozip'] = array_pop($startLoc);
    $this->xmlArr['dzip'] = array_pop($endLoc);
    $this->xmlArr['ocity'] = implode(" ", $startLoc);
    $this->xmlArr['dcity'] = implode(" ", $endLoc);

    // Compare start and end zipcodes to see if local or long distance and set servtypid accordingly.
    if ($this->xmlArr['ozip'] == $this->xmlArr['dzip']) {
      $this->xmlArr['servtypeid'] = LOCAL_MOVE;
    } else {
      $this->xmlArr['servtypeid'] = LD_MOVE;
    }
    // Set the start and end states.
    $this->xmlArr['ostate'] = $this->formVals['vehicle_pickup_state'];
    $this->xmlArr['dstate'] = $this->formVals['vehicle_destination_state'];

    // Customer Name
    $custName = explode(" ", $this->formVals['customername']);
    if (count($custName) == 1) {
      $this->xmlArr['lastname'] = $custName[0];
    } else {
      $lastName = array_pop($custName);
      $firstName = implode(" ", $custName);
      $this->xmlArr['lastname'] = $lastName;
      $this->xmlArr['firstname'] = $firstName;
    }

    $this->xmlArr['notes'] = $this->formVals['comments'];
    $this->xmlArr['phone1'] = $this->formVals['phone'];
    $this->xmlArr['movedte'] = $this->formVals['move_date'];
    $this->xmlArr['email'] = $this->formVals['email'];

    // Debugs
    //foreach ($this->xmlArr as $key => $value) {
    //  echo $key . "=>" . $value . "<br />";
    //}
  }

  private function buildXML() {

    $xmlString = '<AAA />';
    $xml = new SimpleXMLElement($xmlString);
    $xmlChild = $xml->addChild('BBB');
    $this->to_xml($xmlChild, $this->xmlArr);
    //print_r($simpleXMLString);
    $fp = fopen('./granot/post.xml', 'a+');
    fwrite($fp, $xml->asXML());
    fclose($fp);
    return $xml;
  }

  function to_xml(SimpleXMLElement $object, array $data) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $new_object = $object->addChild($key);
        to_xml($new_object, $value);
      } else {
        $object->addChild($key, $value);
      }
    }
  }

  public function postXMLLead() {
    $xmlContent = $this->buildXML();

    //$id = "93F6F0E40E79";
    //$moverRef = "leads@granot.com";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, LEAD_GATEWAY);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-type: application/x-www-form-urlencoded;charset=UTF-8"));
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
    curl_setopt($ch, CURLOPT_REFERER, LEAD_REF);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "xml=".$xmlContent."&API_ID=".LEADS_API_ID."&MOVERREF=".$this->xmlArr['moverref']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $fp = fopen('./granot/post.xml', 'a+');
    fwrite($fp, "XML POST RESPONSE:" . $httpcode . "\n");
    fwrite($fp, $result . "\n");
    fclose($fp);

    //$oXML = new SimpleXMLElement($result);
    //echo $result;
    //foreach($oXML->entry as $oEntry){
    //  echo $oEntry->title . "\n";
    //}
  }
}

?>
