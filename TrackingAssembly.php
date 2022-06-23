<?php

$conn= mysqli_connect("localhost", "root", "", "track");                                   //(Server name, User name, Password, Database name)
$barcode;
$employee;
$action;
$errormsg;
$productInfo;
$output;
$msg;
$button;
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
$employee = isset($_GET['employee']) ? $_GET['employee'] : '';
$action= isset($_GET['action']) ? $_GET['action'] : '';
$url = "TrackingAssembly.php?barcode=$barcode&employee=$employee";
$urlAddEmp = "TrackingAssembly.php?barcode=$barcode&employee=";
$urlBlank = "TrackingAssembly.php";

execute();

function  execute() {
	global $conn, $errormsg, $barcode, $employee ,$action, $output, $msg, $button;

	$errormsg = '';
	if (CheckServerConnection($conn)){
		if($barcode != ''){
			$data = GetData($barcode);					
		
			switch (true) {

			case (empty($data['Barcode'])):
				$action = InsertData($barcode);              //Inserts initial barcode
				$action = "employee"; 
				break;

			case (empty($data['Employee'])):
				$barcode = $data['Barcode'];
				$action = "employee"; 
				break;
			
			case (empty($data['StartDate'])):
				 $action = "Start";                   
				 break;
				
			}
		}
	
		switch ($action) {

			case "Start":
				UpdateEmployee($barcode, $employee);    //Updates Employee
				StartTime($barcode);                	//Saves the start time 
				$msg = "Assembly Started.";
				break;

			case "End":
				EndTime($barcode);			//Saves the End time 
				$msg = "Assembly Ended."; 	
				break;
		}    
	    }
	}else{
		$errormsg = "Could not connect to database!";
		return $errormsg;
	}

	// returns info & buttons or error message 
	if($errormsg != ''){
			return $errormsg;
		    }else{ 
			$button = GetButtons($action);
			$output = GetData($barcode);
			return $output;
}
//Get buttons for page. Can add pause button or more processes 
function GetButtons ($action){
    global $button, $action, $url, $urlAddEmp, $urlBlank; 
    switch ($action) {
	case "Start":
        	$button = '<a href= "'.$urlAddEmp.'&action=AddEmployee"><img src="images/button_change-employee.png" alt="Change Employee Button"></a>'
                .'<br><a href= "'.$url.'&action=End"><img src="images/button_end-assembly.png" alt="End Assembly"></a>';
		    break;
	case 'End':
        	$button = '<a href= "'.$urlBlank.'"><img src="images/button_start-new-assembly.png" alt="Start new assembly"></a>&nbsp';  
		    break;
	    default:
        $button = '';
    }
    return $button;
}
//Inserts new row with barcode and start date/time into database
function InsertBarcode($barcode){
    $conn = $GLOBALS["conn"];
    $sql = "Insert into assembly (Barcode) Values ('".addslashes($barcode)."')";
    $query = mysqli_query($conn, $sql);

}
//Updates employee in Database
function UpdateEmployee($bar, $emp){
    $conn = $GLOBALS["conn"];
    $sql = "Update assembly SET Employee = '".addslashes($emp)."' Where Barcode = '".$bar."'";
    $result = mysqli_query($conn, $sql );

}
//Inserts start date/time 
function StartTime($bar){
    $conn = $GLOBALS["conn"];
    $sql = "Update assembly SET StartDate = NOW() Where Barcode = '".$bar."'";
    $result = mysqli_query($conn, $sql );
	
}
//Inserts end date/time 
function EndTIme($bar){
    $conn = $GLOBALS["conn"];
    $sql = "Update assembly SET EndDate = NOW() Where Barcode = '".$bar."'";
    $result = mysqli_query($conn, $sql );

}
// Retrives assembly data from database
function GetData($bar){
    $conn = $GLOBALS["conn"];
    $where = " Where Barcode = '".addslashes($bar)."'"; 
    $sql = "Select * from assembly".$where;
    $result = mysqli_query($conn, $sql);
    if(empty($result)){
        $row = $sql;
    }else{
    $row = mysqli_fetch_array($result);
    }
    return $row;
}
// Checks server connection 
function CheckServerConnection($conn){   
    if (mysqli_ping($conn)){
        return true;
    }else{
        return false;
    }
}
?>
<!DOCTYPE HTML>
<html>

	<head>
    
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="assets/css/main.css" />
        <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>

	</head>

    <body>

    <div id="main">
        <header>
            <h1>Tracking App</h1>
        
        </header>
        <!-- shows error message if one is flagged-->
        <?php if ($errormsg != '') { ?>   
            <div style="color:red; text-align: center; font-size:20px;"><?php echo $errormsg; ?></div>
        <?php }else{ ?>
		 <!-- Switch statment to decide page displayed -->
                <?php switch($action) { 
			Case(''):  ?>
                    <h2>Please scan or enter Barcode</h2>
                     <!-- Get barcode form -->
                        <form method="get">
                            <div class="field">
                                <div style="margin: auto; width: 50%; padding: 10px;"><img src="images/barcode.png" alt="Enter Barcode" />
                                <div style="padding: 10px 15%;"><label for="barcode">Barcode:</label>
                                <input type="number" id="barcode" name="barcode" value=""><br><div>
                                <div style="padding: 10px 20%;"><input  type="submit" value="Submit"></div>
                            </div>
                        </form>	
            
                <?php Case('employee'): ?>
                    <h2>Please scan or enter Employee Name</h2>
                   
                    <!-- Get employee form -->
                        <form method="get">
                            <div class="field">
                                <label for="employee">Employee Name:</label>
                                <input type="text" id="employee" name="employee" value=""><br>
                            </div>
                                <input type="hidden" id="barcode" name="barcode" value="<?php echo $barcode ?>">
                                <input type="submit" value="Submit">
                        </form>		

                <?php default: ?>

                <!-- Information Table-->
                <table>
                    <tr>
                        <th colspan="2" style="color:green; text-align: center; font-size:20px;"> <?php echo $msg ?></th>
                    </tr>
                    <tr>
                        <td width="30%">Barcode</td>
                        <td><?php echo $output['Barcode']; ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Employee</td>
                        <td><?php echo $output['Employee']; ?></td>
                    </tr>
                    <tr>
                        <td width="30%">StartDate</td>
                        <td><?php echo $output['StartDate']; ?></td>
                    </tr><tr>
                        <td width="30%">EndDate</td>
                        <td><?php echo $output['EndDate']; ?></td>
                    </tr>
                </table>
                <?php } echo $button; ?>
	<?php } else{ ?>
    </div>  
           
    <!-- Scripts -->
            <script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrollex.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
            <script>
     </body>
</html>
