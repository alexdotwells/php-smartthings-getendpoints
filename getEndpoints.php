<?php

class SmartThings_Authorization{

  private $client;
  private $secret;
  private $redirect_url;

	public function __construct() {
    $redirect_url = "";  // hardcode app redirect url
    $client = "";
    $secret = "";
  }

  public function getEndpoints() {

    if( !isset($_REQUEST['code']) && !isset($_REQUEST['access_token']) ) {
      // Get Access Code
    	header( "Location: https://graph.api.smartthings.com/oauth/authorize?response_type=code&client_id=$client&redirect_uri=".$redirect_url."&scope=app" ) ;

    } else if( isset($_REQUEST['code']) ) {
      // Use Access Code to claim Access Token
    	$code = $_REQUEST['code'];
    	$page = "https://graph.api.smartthings.com/oauth/token?grant_type=authorization_code&client_id=".$client."&client_secret=".$secret."&redirect_uri=".$redirect_url."&code=".$code."&scope=app";
    	$ch   = curl_init();

    	curl_setopt($ch, CURLOPT_URL,            $page );
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    	curl_setopt($ch, CURLOPT_POST,           0 );
    	curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));

    	$response =  json_decode(curl_exec($ch),true);

    	curl_close($ch);

    	if( isset($response['access_token']) ) {
    		// Redirect to self with access token
    		header( "Location: ?access_token=".$response['access_token'] ) ;

    	} else {
    		print "error requesting access token...";
    		print_r($response);
    	}

    } else if( isset($_REQUEST['access_token']) ) {
      // Find endpoint and display URL
    	$redirect_url = "https://graph.api.smartthings.com/api/smartapps/endpoints/$client?access_token=".$_REQUEST['access_token'];
    	$json         = implode('', file($redirect_url));
    	$theEndpoints = json_decode($json,true);

    	print "<html><head><style>h3{margin-left:10px;}a:hover{background-color:#c4c4c4;} a{border:1px solid black; padding:5px; margin:5px;text-decoration:none;color:black;border-radius:5px;background-color:#dcdcdc}</style></head><body>";
      print "<i>Save the above URL (access_token) for future reference.</i>";
    	print " <i>Right Click on buttons to copy link address.</i>";

    	foreach($theEndpoints as $k => $v) {
        //GET DEVICES 1
        $devices    = "switches";
    		$switchUrl  = "https://graph.api.smartthings.com".$v['url']."/".$devices;
    		$access_key = $_REQUEST['access_token'];

    		$ch = curl_init($switchUrl);
    		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_key ) );
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    		curl_setopt($ch, CURLOPT_POST,           0 );

    		$resp =  curl_exec($ch);
    		curl_close($ch);

    		$respData = json_decode($resp,true);

    		if (count($respData) > 0) { print "<h2>{$devices}</h2>" };

    		foreach($respData as $i => $switch) {
          // Display device list 1
    			$label = $switch['label'] != "" ? $switch['label'] : "Unlabeled Switch";
    			print " <h3>$label</h3>";

    			$onUrl = "https://graph.api.smartthings.com".$v['url']."/switches/".$switch['id']."/on?access_token=".$_REQUEST['access_token'];
    			print "<a target='cmd' href='$onUrl'>On</a>";

    			$offUrl = "https://graph.api.smartthings.com".$v['url']."/switches/".$switch['id']."/off?access_token=".$_REQUEST['access_token'];
    			print "<a  target='cmd' href='$offUrl' value='Off'>Off</a>";

    			$toggleUrl = "https://graph.api.smartthings.com".$v['url']."/switches/".$switch['id']."/toggle?access_token=".$_REQUEST['access_token'];
    			print "<a target='cmd' href='$toggleUrl'>Toggle</a><BR>";
    		}

        // GET DEVICES 2
        $devices2   = "locks";
    		$lockUrl    = "https://graph.api.smartthings.com".$v['url']."/". $devices2;
    		$access_key = $_REQUEST['access_token'];

    		$ch = curl_init($lockUrl);
    		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_key ) );
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    		curl_setopt($ch, CURLOPT_POST,           0 );

    		$resp =  curl_exec($ch);
    		curl_close($ch);

    		$respData = json_decode($resp,true);

    		if(count($respData) > 0) print "<h2>{$devices2}</h2>";


    		foreach($respData as $i => $lock) {
          // Display device list 2
    			$label = $lock['label'] != "" ? $lock['label'] : "Unlabeled Lock";
    			print "<h3>$label</h3>";

    			$lockUrl = "https://graph.api.smartthings.com".$v['url']."/locks/".$lock['id']."/lock?access_token=".$_REQUEST['access_token'];
    			print "<a target='cmd' href='$lockUrl'>Lock</a>";

    			$unlockUrl = "https://graph.api.smartthings.com".$v['url']."/locks/".$lock['id']."/unlock?access_token=".$_REQUEST['access_token'];
    			print "<a  target='cmd' href='$unlockUrl' value='Off'>Unlock</a><BR>";
    		}

    		print "<BR><hr><BR>";
    	}

    	// All links in the html document are targeted at this iframe
    	print "<iframe name='cmd' style='display:none'></iframe></body></html>";
    }
  }

}

?>
