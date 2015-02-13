<!doctype html>
<html>
<head><!--body style="text-align:center"-->
    <style type="text/css">
        select{
            width: 120px;
        }
    </style>
    <?php
            //Data Propogation 1: From Server Directory Files to $fileContents
            //Data Propogation 2: From $fileContents to var raceDatas []
            //Data Propogation 3: For each raceDatas[] add <option> to AWarband && DWarband <select>
            //Data Propogation 4: For 
            $raceValues = array();//[];
            $races = array();//[];
            $fileContents = array();//[];
            //read file contents and populate above php variables
            foreach(glob('./mord/*.txt') as $filename){
                $file = explode("/",$filename); #./mord/Dwarfs.txt -->   [0].         [1]mord    [2]Dwarfs.txt
                $file = $file{'2'};
                $race = explode(".",$file);    #        Dwarfs.txt -->   [0]Dwarfs    [1]txt
                $race = $race{'0'};
                $value = strtolower(substr($race,0,5)); #  Dwarfs     -->   dwarf
                //store file content
                array_push($raceValues, $value);
                array_push($races, $race);
                array_push($fileContents, file_get_contents($filename) );
            }
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="src/mathquill.css" />
    <link rel="stylesheet" type="text/css" href="src/probability_tree.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.2/raphael-min.js"></script>
    <script type="text/javascript" src="src/mathquill.js"></script>
    <script type="text/javascript" src="src/probability_tree.js"></script>
    <script type="text/javascript">
        var p8 = .8;
        var p5 = .5
        $(document).ready(function(){
            $('#display').probability_tree({
                data : {
                    'Miss': { value: '1/2' },//, intersection: .05
                    'Hit': { value: '1/2',
                        'noWound': {
                            value: '4/6'
                        },
                        'Wound': { value: '1/6',
                            'armor save':{ value: '1/6' },
                            'no save':{
                                'Out':{ value: '1/3' },
                                'Stun':{ value: '1/3' },
                                'Down':{ value: '1/3' },
                                value: '5/6'
                            }
                        },
                        'Crit' : { value: '1/6',
                            'Vital':{ value: '1/3',
                                'armor save':{ value: '1/6' },
                                'no save':{ value: '5/6',
                                    'Out':{ value: '5/9' },
                                    'Stun':{ value: '3/9' },
                                    'Down':{ value: '1/9' }
                                }
                            },
                            'Exposed':{ value: '1/3',
                                'Out':{ value: '5/9' },
                                'Stun':{ value: '3/9' },
                                'Down':{  value: '1/9' }
                            },
                            'Master':{
                                'Out':{ value: '8/9' },
                                'Stun':{ value: '1/9' },
                                value: '1/3'
                            }
                        }
                    }
                },
                probability_editable:false,
                end_proba:true
            });
        })
    </script>

</head>
<body>
    <form id="myform" action="mordCalcTest.php" method="get">
    <table>
    <thead>
        <tr><th>Attacker</th><th>Defender</th></tr>
    </thead>
    <tbody>
    <tr>
    <td>
        <div id="attackerInfo">
        <select id="AWarband" name="aband">
            <option selected disabled>Select Warband</option>
            <?php
                for($i=0; $i<count($races); $i++){
                    echo "<option value='$raceValues[$i]'>$races[$i]</option>\n";
                }
            ?>
        </select>
        <br/>
        </div>
        <pre>
        Attacks:<span id="Aattacks"></span>
    Weaponskill:<span id="Aweaponskill"></span>
       Strength:<span id="Astrength"></span>
      Toughness:<span id="Atoughness"></span>
     Initiative:<span id="Ainitiative"></span>
     Leadership:<span id="Aleadership"></span>
         Wounds:<span id="Awounds"></span>
           Cost:<span id="Acost"></span>
        </pre>
    </td><!-- End Attacker / Begin Defender !--><td>
        <div id="defenderInfo">
        <select id="DWarband" name="dband">
            <option selected disabled>Select Warband</option>
        <?php
            for($i=0; $i<count($races); $i++){
                echo "<option value='$raceValues[$i]'>$races[$i]</option>\n";
            }
        ?>
        </select>
        <br/>
        </div>
        <pre>
        Attacks:<span id="Dattacks"></span>
    Weaponskill:<span id="Dweaponskill"></span>
       Strength:<span id="Dstrength"></span>
      Toughness:<span id="Dtoughness"></span>
     Initiative:<span id="Dinitiative"></span>
     Leadership:<span id="Dleadership"></span>
         Wounds:<span id="Dwounds"></span>
           Cost:<span id="Dcost"></span>
        </pre>
    </td>
    </tr>
    </tbody>
    </table>
    <input type="submit">
    </form>
    
    <div id="response1"></div>
    <div id="response2"></div>
    
    <h2>Not Implemented Yet - Example Output: 'Orc w/ Dagger' (3ws,3str,+1 arm.save) Attacks 'Orc' (3ws,4tough)</h2>
    <pre>
    OutOfAction:  37/486 --- 7.6%
        Stunned:  21/486 --- 4.3%
      KnockDown:  14/486 --- 2.9%
       Standing: 414/486 -- 85.2%
    </pre>
    <div id="display" style="width:800px;margin:auto">
    </div>
    
    <script type="text/javascript">
    
        //**********
        // Variables
        //**********
        
        var form = document.forms[0];//
        var raceDatas = [];     //Array of File Data
        var attdropdownNodes = []; //Array of att dropdown Nodes
        var defdropdownNodes = []; //Array of def dropdown Nodes
        var ADDIndex = -1;     //Index of warband dropdown(dd) and 'raceDatas' and 'dropdownNodes'
        var DDDIndex = -1;
        
        //*******************************************************
        //Runs Once : Populate <select> drop down and raceDatas[]
        //*******************************************************
        
        raceDatas.push( "empty");//added for "Select Warband" Option
        attdropdownNodes.push( "empty");//added for "Select Warband" Option
        defdropdownNodes.push( "empty");//added for "Select Warband" Option
        
        //populates 'raceDatas' and 'dropdownNodes' based on file content
        <?php
            $i = 0;
            foreach($fileContents as $fileData){
                echo "//".$raceValues[$i]."\n";
                echo "raceDatas.push( $fileData );\n";
                echo "var attSel = document.createElement('select');";
                echo "attSel.name = 'a'+'".$raceValues[$i]."'; ";
                echo "attdropdownNodes.push( attSel );\n";
                echo "var defSel = document.createElement('select');";
                echo "defSel.name = 'd'+'".$raceValues[$i]."'; ";
                echo "defdropdownNodes.push( defSel );\n";
                $i++;
            } 
        ?> 
        //populates 'AWarband' and 'DWarband' dropdown <options>
        for(var i=1; i< raceDatas.length; i++){//mod from 0 to 1 for "Select Warband" Option
            var thisBand = raceDatas[i];
            var attNode = attdropdownNodes[i];
            var defNode = defdropdownNodes[i];
            for(var key in thisBand){
                if (thisBand.hasOwnProperty(key)) {
                    var DOp = document.createElement('option');
                    var AOp = document.createElement('option');
                    DOp.value = AOp.value = key;//.toLowerCase();//lowercase and shorter?
                    DOp.innerHTML = AOp.innerHTML = key;
                    attNode.appendChild(AOp);
                    defNode.appendChild(DOp);
                    //console.log(key);
                }else{
                    document.warn(key+" not own property.");
                }
            }
            //add dropdown listener
            attNode.addEventListener("change", function(){
                var model = raceDatas[ADDIndex][this.value];
                updateAttacker(model);
            });
            
            defNode.addEventListener("change", function(){
                var model = raceDatas[DDDIndex][this.value];
                updateDefender(model);
            });
        }
        
        //************************************************
        //End: Populate <select> drop down and raceDatas[]
        //Begin: Repopulate form input fields / data
        //************************************************
        
        var gotAtt = false, gotDef = false;
        <?php
            if($_GET){ 
                foreach( $_GET as $key => $value ){
                    echo "populateForm('$key','$value');";
                }
            }
        ?>
        //called in php loop on page load for every Key/Value pair in URL
        function populateForm(theKey, theValue){
            if ( form[theKey] ) {
                form[theKey].value = theValue;  //text, select
                form[theKey].checked = true;    //checkbox, radio
            } else {                            //not currently displayed select
                if (gotAtt) {
                    //Dealing with Defender
                    gotDef = true;
                    var loc = locateInDefSelect(theKey);
                    if (loc > 0) {
                        defdropdownNodes[loc].value = theValue;//update select highlight value
                        addDefenderDD(loc);         //display select
                    } else {
                        console.warn('Unknown form input name: '+theKey);
                    }
                } else {
                    //Dealing with Attacker
                    gotAtt = true;//notify us next time that we already got attack
                    var loc = locateInAttSelect(theKey);
                    if (loc > 0) {
                        attdropdownNodes[loc].value = theValue;//update select highlight value
                        addAttackerDD(loc);         //display select
                    } else {
                        console.warn('Unknown form input name: '+theKey);
                    }
                }
            }
        }
        //These 2 functions Used by 'populateForm' only
        //check to see what index 'searchName' is in <select> name
        function locateInAttSelect(searchName){
            for(i=1; i< attdropdownNodes.length; i++){
                if (attdropdownNodes[i].name == searchName) {
                    return i;
                }
            }
            alert( searchName +" not found in Attack select");
            return 0;
        }
        function locateInDefSelect(searchName){
            for(i=1; i< defdropdownNodes.length; i++){
                if (defdropdownNodes[i].name == searchName) {
                    return i;
                }
            }
            alert( searchName +" not found in Defender select");
            return 0;
        }
        //******************************************
        //End: Repopulate form input fields / data
        //******************************************
        
        //aWarband <Select> listener
        AWarband.addEventListener("change", function(){
            addAttackerDD(this.selectedIndex);
            //this.options[this.selectedIndex].id
            //can also access innerHTML/text, value but NOT name
        });
        DWarband.addEventListener("change", function(){
            addDefenderDD(this.selectedIndex);
        });
        
        //changes/displays Model <Select>, does not change 'value'
        //Called by: warband "change" Listener
        //Called by: populateForm()
        function addAttackerDD(newIndex){
            if (ADDIndex != -1) {
                attackerInfo.removeChild(attdropdownNodes[ADDIndex]);
            }
            ADDIndex = newIndex;
            attackerInfo.appendChild(attdropdownNodes[ADDIndex]);
            updateAttacker( raceDatas[ADDIndex][ attdropdownNodes[ADDIndex].value ] );
        }
        function addDefenderDD(newIndex){
            if (DDDIndex != -1) {
                defenderInfo.removeChild(defdropdownNodes[DDDIndex]);
            }
            DDDIndex = newIndex;
            defenderInfo.appendChild(defdropdownNodes[DDDIndex]);
            updateDefender( raceDatas[DDDIndex][ defdropdownNodes[DDDIndex].value ] );
        }
        
        
        function updateAttacker(model){
            Aattacks.innerHTML = model['A'];
            Aleadership.innerHTML = model['L'];
            Aweaponskill.innerHTML = model['WS'];
            Astrength.innerHTML = model['S'];
            Ainitiative.innerHTML = model['I'];
            Awounds.innerHTML = model['W'];
            Atoughness.innerHTML = model['T'];
            Acost.innerHTML = model['Cost'];
        };
        
        function updateDefender(model){
            Dattacks.innerHTML = model['A'];
            Dleadership.innerHTML = model['L'];
            Dweaponskill.innerHTML = model['WS'];
            Dstrength.innerHTML = model['S'];
            Dinitiative.innerHTML = model['I'];
            Dwounds.innerHTML = model['W'];
            Dtoughness.innerHTML = model['T'];
            Dcost.innerHTML = model['Cost'];
        };
        
        //*****
        // AJAJ
        //*****
        
        //if we did have data in $_GET and populated both the attacker and defender:
        //then lets do an AJAJ request for the actual combat results.
        if (gotDef) {
            var mydata = "as="+Astrength.innerHTML+"&dt="+Dtoughness.innerHTML;
            
            AJAJ_POST("testReceive.php",mydata, handleResponse);
        } else {
            response2.innerHTML = "Select both an Attacker and Defender";
        }
        
        function AJAJ_POST(url, sendData, callback) {
                
                var httpRequest = new XMLHttpRequest();
                
                httpRequest.open('post', url);//asynchronous
                httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");//Needed for POST and PUT
                httpRequest.onload = function(){
                    callback( httpRequest.responseText);
                };
                httpRequest.send(sendData);//send data
            }
            
            function handleResponse(responseText){
                
                
                var jsonParsed = JSON.parse(responseText);
                if (jsonParsed.status === 400) {
                    response1.innerHTML = "Error 400 recieved";
                } else if ( jsonParsed.data ) {
                    response1.innerHTML = jsonParsed.data.wound;
                } else {
                    response1.innerHTML = "How we get here?";
                }
                //response2.innerHTML = responseText;
            }
    </script>
</body>
</html>