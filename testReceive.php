<?php
$A_Str;
$D_Tuf;
if($_POST){
    $A_Str = $_POST['as'];
    $D_Tuf = $_POST['dt'];
} else if($_GET){
    $A_Str = $_GET['as'];
    $D_Tuf = $_GET['dt'];
} else {
    return;
}
    $wound = (4-$A_Str+$D_Tuf);
    $woundRoll = "";
    if($wound > 6){
        $woundRoll = "no chance to wound";
    }else if($wound == 6){
        $woundRoll = "wound on a ".$wound;
        $woundRoll .= ", no chance of Crit";
    }else{
        $woundRoll = "wound on a ".$wound;
        $woundRoll .= ", Crit on a 6";
    }
    
    $D_outOfAction = ".20";
    $D_stunned = ".10";
    $D_knockedDown = ".10";
    
    // fourth is cut off or rounds up 3rd.
    // .2006 = 20.1%    //round down
    // .2004 = 20%      
    // .2094 = 20.9%
    // .2096 = 21%      //round up
    // .XXX5 = rounds up sometimes not others.
    
    $data['D_out'] = .2005;
    $data['D_stun'] = .1095;
    $data['D_kdown'] = .2095;
    $data['wound'] = $woundRoll;
    /*/
    $data[0] = .20;
    $data[1] = .15;
    $data[2] = .10;
    $data[3] = $woundRoll;
    //*/
    
    sendResponse(200,"okay", $data);

function sendResponse($status, $status_message, $data){
        //header("HTTP/1.1 $status $status_message");
        $response['status'] = $status;
        $response['status_message']=$status_message;
        $response['data']=$data;
        $json_response = json_encode($response);
        echo $json_response;
    }
?>