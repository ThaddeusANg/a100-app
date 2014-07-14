<?php
include "cred_int.php";

$appCon = mysqli_connect(DB_HOST,DB_USERNAME, DB_PASSWORD, DB_APP_DATABASE);
$formCon = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_FORM_DATABASE);

if(mysqli_connect_errno()){
	echo "Failed to connect to MySQL: ".mysqli_connect_error();
}
/*
$sqlFieldNames = "SELECT field_id, field_name, response_target FROM fields";
$fieldNamesArray = mysqli_query($formCon, $sqlFieldNames);
$respArray= array();

$x=0;
while($fieldNamesRows = mysqli_fetch_array($fieldNamesArray)){
	$fieldId=$fieldNamesRows['field_id'];
	$varName=$fieldNamesRows['field_name'];
	$response_target=$fieldNamesRows['response_target'];
	$respArray[$x] = array($fieldId, $_POST[$varName], $varName, $response_target);
	echo $x.": ".$_POST[$varName]."->".$fieldId." ,".$varName." ,".$response_target."</br>";
	//echo $x.": ".$respArray[$x][0]." ,".$respArray[$x][1]." ,".$respArray[$x][2]." ,".$respArray[$x][3]." from array</br>";
	$x++;
}
*/

//check for duplicates by applicant ID and cohort content
$sqlDup = 
"SELECT * FROM applications INNER JOIN identity	ON applications.identity_id=identity.identity_id 
WHERE identity.email='".$_POST['email'] ."' AND applications.cohort_name ='".$_POST['cohort_id']."'";  //!!warning var cohort_id CONTAINS cohort_name
$dup = mysqli_query($appCon, $sqlDup);  //sql runs sql code and gets possible duplicates
$dupCount = mysqli_num_rows($dup);  //counts total duplicate values

//checks for duplicates, if greater than 1, throw dup record and stop else write to DB
if($dupCount >1){
	echo "Someone has enrolled in: ".$_POST['cohort_id']." with the provided email address already, thank you for your interest.";
}else{
	//checks if user has submitted or saved the form, if submit switch flag to 1 to lock form else leave at 0
			if($_POST['submit'])
			{
				$status = 1;

			//checks for malformed input 
			 	$reqArray = mysqli_query($formCon, "SELECT field_name, is_required FROM fields");
			 	$malformedInput=0;
			 	while($required = mysqli_fetch_array($reqArray))
			 		{
			 		$tempName = $required['field_name'];
			 		if($required['is_required']==1 && $_POST[$tempName]==NULL){
			 			echo "'".$tempName."' is a required field, you submitted: ".$_POST['$tempName']." please enter a correct value </br>";
			 			$malformedInput = 1;
			 			}
			 		}
			 		if($malformedInput==1){
						echo "<a href='../index.php'>Click Back</a>";
			 			exit;
			 		}

			}elseif($_POST['save'])
			{
				$status = 0;
				$to=$email;
				$subject="Thank you for your interest in Apprentice 100";
				$Message="
				<html>
				<head>
				<title>HTML email</title>
				</head>
				<body>
				<p>Thank you for your continued interest in the Apprentice 100 program. </p>
				<p> We look forward to receiving your completed application. For your reference, your login credentials to our online webportal are</p>
				".$email."<br>
				".$password."<br>
				<p>We look forward to receiving your completed application</p>
				<p>Sincerely \nA100 Developers</p>
				</body>
				</html>";
			}


	$applicationSqlInsert = "NULL";
	//needs to be modularized to read from DB
	$tableInfo = array('applicants', 'identity', 'referrals','schedules', 'experiences', 'materials', 'applications');
	for($x=0;$x<7;$x++)
		{
		$query = "SELECT * from ".$tableInfo[$x];
		if ($result = mysqli_query($appCon, $query)) {

		    /* Get field information for all fields */
		    $submitSqlTable;
		    //Base string, will be formatted as input is entered
		    $submitSqlField="";
		    $submitSqlRecord="";

		    $sqlFirst=0;
		    
		    //start modifying content for insertion
		    while ($finfo = mysqli_fetch_field($result)) {
		    	//formatting Insert statement to add commas before all content excluding first entry
		    	if($sqlFirst!=0){
		    		$submitSqlField=$submitSqlField.",";
		    		$submitSqlRecord=$submitSqlRecord.",";
		    					}

		    	//retrieves column name and table name
		    	$colName = $finfo->name;
		    	$submitSqlTable=$finfo->table;		    	

		    	//echo "1 if required, 0 if optional : colName: ". $colName. "  input is: ".$required['is_required']."</br>";
		    	
		    	//inserts into 
		        $submitSqlField=$submitSqlField."`".$colName."`";

		        //file submission, checks if field names reference resume/cover letter !! not modularized  !!
		        if($colName=="resume"||$colName=="cover_letter"){
		        	if ($_FILES[$colName]["error"] > 0) {
		        		echo "Return Code: " . $_FILES[$colName]["error"] . "<br>";
		        	} else {
		        		echo "Upload: " . $_FILES[$colName]["name"] . "<br>";
		        		//echo "Type: " . $_FILES[$colName]["type"] . "<br>";
		        		//echo "Size: " . ($_FILES[$colName]["size"] / 1024) . " kB<br>";
		        		//echo "Temp file: " . $_FILES[$colName]["tmp_name"] . "<br>";
		        		if (file_exists("upload/" . $_FILES[$colName]["name"])) {
		        			echo $_FILES[$colName]["name"] . " already exists. ";
		        			$fileLoc= "Stored in: " . "upload/" . $_FILES[$colName]["name"];
		        		} else {
		        			move_uploaded_file($_FILES[$colName]["tmp_name"],
		        				"upload/" . $_FILES[$colName]["name"]);
		        			$fileLoc= "Stored in: " . "upload/" . $_FILES[$colName]["name"];
		        			echo $fileLoc;
		        		}
		        	}
		        }

		        if($submitSqltable!="applications"){
			        	//first check if there is not submitted value, like for keys in a completed form
			        if($_POST[$colName]==""){  
			        	//file submission as no submitted value sent via normal ways
			        	//if object name is resume or cover letter write file path to DB
			        	if($colName=="resume"||$colName=="cover_letter"){
			        		$submitSqlRecord=$submitSqlRecord."'".$fileLoc."'";	
			        	}else
			        	// writes a NULL value like for PK
			        	{
			        		if($required==1){
			        			break;
			        		}
			        		$submitSqlRecord=$submitSqlRecord."NULL";
			        		//increment tracker so system knows to format with commas
			        		$sqlFirst=$sqlFirst +1;
			        	}
			        }else
			        //if POST content isn't null, write POST content with formatting into string for SQL insertion
			        	{$submitSqlRecord=$submitSqlRecord."'".$_POST[$colName]."'";}	
		        }
		    }

		    
			//sql for writing to DB
			if($submitSqlTable!="applications"){
				$submitSql = "INSERT INTO `applications_db`.`".$submitSqlTable."` (".$submitSqlField.")VALUES (".$submitSqlRecord.")";
				echo $submitSql;
				if (mysqli_query($appCon, $submitSql)){
					echo "Table updated successfully. \n";
				}else{
				echo "Error executing: " . $submitSql . "\nError produced: " . mysqli_error($appCon) . "\n";
				}
				$identity_id= mysqli_insert_id($appCon);
				echo $submitSqlTable." key: ".$identity_id;
			}
			
			//manual collection of FKs for applications table !! NOT MODULARIZED !!
			if($submitSqlTable!="applications"){
					if($submitSqlTable == "identity"){			
						$applicationSqlInsert=$applicationSqlInsert.",'".$identity_id."','".$_POST['cohort_id']."'";
					}else{		
						$applicationSqlInsert=$applicationSqlInsert.", '".$identity_id."'";
					}
				}
			

	echo "</br>";
		}
	}


	$applicationSqlInsert=$applicationSqlInsert.", '".$status."',NULL";
	$submitSql = "INSERT INTO `applications_db`.`".$submitSqlTable."` (".$submitSqlField.")VALUES (".$applicationSqlInsert.")";
	echo "final insert".$submitSql;

	if (mysqli_query($appCon, $submitSql)){
				echo "Table updated successfully. \n";
			}else{
				echo "Error executing: " . $submitSql . "\nError produced: " . mysqli_error($appCon) . "\n";
			}
		
mysqli_free_result($result);

}




//php file submission

//working submits not auto
/*
$submitSql = "INSERT INTO `applications_db`.`".$respArray[0][3]."` (`identity_id`, `first_name`, `last_name`, `email`, `password`) VALUES 
		(NULL, '".$respArray[0][1]."', '".$respArray[1][1]."', '".$respArray[2][1]."', '".$respArray[3][1]."')";

if (mysqli_query($appCon, $submitSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $submitSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}
$identity_id= mysqli_insert_id($appCon);

$applicantSql = "INSERT INTO `applications_db`.`".$respArray[4][3]."` (`applicant_id`, `identity_id`, `school_name`, `major`, `graduation_date`, 
		`street_address`, `city`, `state`, `zipcode`, `phone_number`, `linkedin`, `portfolio`, `age_check`, `legal_status`) VALUES 
		(NULL, '$identity_id','".$respArray[4][1].
			"', '".$respArray[5][1].
			"', '".$respArray[6][1].
			"', '".$respArray[8][1].
			"', '".$respArray[9][1].
			"', '".$respArray[10][1].
			"', '".$respArray[11][1].
			"', '".$respArray[12][1].
			"', '".$respArray[13][1].
			"', '".$respArray[14][1].
			"', '".$respArray[15][1].
			"', '".$respArray[16][1]."')";

$referralSql = "INSERT INTO `applications_db`.`".$respArray[18][3]."` (`referral_id`, `referral_1`, `referral_2`, `referral_3`, `referral_4`, 
		`referral_5`,`referral_6`, `referral_7`, `referral_8`,`referral_9`, `referral_10`, `referral_11`) VALUES 
		(NULL, '".$respArray[18][1].
			"', '".$respArray[19][1].
			"', '".$respArray[20][1].
			"', '".$respArray[21][1].
			"', '".$respArray[22][1].
			"', '".$respArray[23][1].
			"', '".$respArray[24][1].
			"', '".$respArray[25][1].
			"', '".$respArray[26][1].
			"', '".$respArray[27][1].
			"', '".$respArray[28][1]."')";

$scheduleSql = "INSERT INTO `applications_db`.`".$respArray[29][3]."` (`schedule_id`, `weekly_hours`, `commitments`) VALUES 
		(NULL, '".$respArray[29][1]."', '".$respArray[30][1]."')";

$experiencesSql = "INSERT INTO `applications_db`.`".$respArray[31][3]."` (`experience_id`, `programming_option`, `work_option`, `job_title`, `front_end_experience`, 
		`lamp_stack_experience`,`mobile_experience`, `cms_experience`, `other_experience`) VALUES 
		(NULL, '".$respArray[31][1].
			"', '".$respArray[32][1].
			"', '".$respArray[33][1].
			"', '".$respArray[34][1].
			"', '".$respArray[35][1].
			"', '".$respArray[36][1].
			"', '".$respArray[37][1].
			"', '".$respArray[38][1]."')";

$materialsSql = "INSERT INTO `applications_db`.`".$respArray[39][3]."` (`material_id`, `resume`, `cover_letter`, `reference_list`, `additional_info`) VALUES 
		(NULL, '".$respArray[39][1]."', '".$respArray[40][1]."', '".$respArray[41][1]."', '".$respArray[42][1]."')";

if (mysqli_query($appCon, $applicantSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $applicantSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}

		$applicant_id= mysqli_insert_id($appCon);

if (mysqli_query($appCon, $referralSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $referralSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}
		$referral_id= mysqli_insert_id($appCon);

if (mysqli_query($appCon, $scheduleSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $scheduleSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}
		$schedule_id= mysqli_insert_id($appCon);

if (mysqli_query($appCon, $experiencesSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $experiencesSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}
		$experience_id= mysqli_insert_id($appCon);

if (mysqli_query($appCon, $materialsSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $materialsSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}
		$material_id= mysqli_insert_id($appCon);

$applicationSql="INSERT INTO `applications_db`.`applications`(
	`application_id`, 
	`applicant_id`,
	`cohort_name`,
	`referral_id`, 
	`schedule_id`, 
	`experience_id`, 
	`material_id`, 
	`is_complete`)
	VALUES (NULL, '".$applicant_id."','".$respArray[7][1]."','".$referral_id."','".$schedule_id."' ,'".$experience_id."' ,'".$material_id."', 1)";
 
if (mysqli_query($appCon, $applicationSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $applicationSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}

/*
$applicationSql="INSERT INTO `applications_db`.`applications` (`application_id`, `applicant_id`, `cohort_id`, `referral_id`, `schedule_id`,
`experience_id`, `material_id`, `is_complete`, `submit_timestamp`) VALUES (NULL";

//IDENTITY TABLE ENTRY
$sqlFieldNames = "SELECT field_id, field_name, response_target FROM fields WHERE response_target='identity'";
$fieldNamesArray = mysqli_query($formCon, $sqlFieldNames);
echo "total rows:".mysqli_num_rows($fieldNamesArray);
echo "</br>";
$x=0;
while($fieldNamesRows = mysqli_fetch_array($fieldNamesArray)){
	$varName=$fieldNamesRows['field_name'];
	$response_target=$fieldNamesRows['response_target'];
	if($x==0){
	$submitSql = "INSERT INTO `applications_db`.`".$response_target."` (`identity_id`, `first_name`, `last_name`, `email`, `password`) VALUES 
		(NULL";
		$submitSql = $submitSql.", '".$_POST[$varName]."' ";
		echo $_POST[$varName].": goes into ".$fieldNamesRows['field_name'];
		echo "</br>";
		$x++;
	}else{
		echo $_POST[$varName].": goes into ".$fieldNamesRows['field_name'];
		$submitSql = $submitSql.", '".$_POST[$varName]."' ";
		echo "</br>";
	}
}
$submitSql = $submitSql.")";

if (mysqli_query($appCon, $submitSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $submitSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}

//APPLICANT TABLE ENTRY
$identity_id= mysqli_insert_id($appCon);
$sqlFieldNames = "SELECT field_id, field_name, response_target FROM fields WHERE response_target='applicants'";
$fieldNamesArray = mysqli_query($formCon, $sqlFieldNames);
echo "total rows:".mysqli_num_rows($fieldNamesArray);
echo "</br>";
$x=0;
while($fieldNamesRows = mysqli_fetch_array($fieldNamesArray)){
	$varName=$fieldNamesRows['field_name'];
	$response_target=$fieldNamesRows['response_target'];
	if($x==0){
	$submitSql = "INSERT INTO `applications_db`.`".$response_target."` (`applicant_id`, `identity_id`, `school_id`, `major`, `graduation_date`, 
		`street_address`, `city`, `state`, `zipcode`, `phone_number`, `linkedin`, `portfolio`, `age_check`, `legal_status`) VALUES 
		(NULL, '$identity_id'";
		$submitSql = $submitSql.", '".$_POST[$varName]."' ";
		echo $_POST[$varName].": goes into ".$fieldNamesRows['field_name'];
		echo "</br>";
		$x++;
	}else{
		echo $_POST[$varName].": goes into ".$fieldNamesRows['field_name'];
		$submitSql = $submitSql.", '".$_POST[$varName]."' ";
		echo "</br>";
	}
}
$submitSql = $submitSql.")";

if (mysqli_query($appCon, $submitSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $submitSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}

$applicant_id= mysqli_insert_id($appCon);
$applicationSql= $applicationSql.", '".$applicant_id."'";

//APPLICANT TABLE ENTRY
$identity_id= mysqli_insert_id($appCon);
$sqlFieldNames = "SELECT field_id, field_name, response_target FROM fields WHERE response_target='experiences'";
$fieldNamesArray = mysqli_query($formCon, $sqlFieldNames);
echo "total rows:".mysqli_num_rows($fieldNamesArray);
echo "</br>";
$x=0;
while($fieldNamesRows = mysqli_fetch_array($fieldNamesArray)){
	$varName=$fieldNamesRows['field_name'];
	$response_target=$fieldNamesRows['response_target'];
	if($x==0){
	$submitSql = "INSERT INTO `applications_db`.`".$response_target."` (`experience_id`, `programming_option`, `work_option`, `job_title`, 
		`front_end_experience`, `lamp_stack_experience`, `mobile_experience`, `cms_experience`, `other_experience`) VALUES 
		(NULL";
		$submitSql = $submitSql.", '".$_POST[$varName]."' ";
		echo $_POST[$varName].": goes into ".$fieldNamesRows['field_name'];
		echo "</br>";
		$x++;
	}else{
		echo $_POST[$varName].": goes into ".$fieldNamesRows['field_name'];
		$submitSql = $submitSql.", '".$_POST[$varName]."' ";
		echo "</br>";
	}
}
$submitSql = $submitSql.")";

if (mysqli_query($appCon, $submitSql)){
			echo "Table updated successfully. \n";
		}else{
			echo "Error executing: " . $submitSql . "\nError produced: " . mysqli_error($appCon) . "\n";
		}
*/
mysql_close($con);
?>