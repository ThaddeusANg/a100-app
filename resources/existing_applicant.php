<html>
 <head><title>PHP Test</title></head>
 <body>
 <?php echo '<p>A100 Application Form</p>'; ?> 
<form action="replace.php" method="post" enctype="multipart/form-data">
<?php
	include "cred_int.php";

		//Create connection
	$formCon = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_FORM_DATABASE);
	$appCon = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_APP_DATABASE);
	// Check connection
	if (mysqli_connect_errno()) {
		echo "Failed to connect to form_db MySQL: " . mysqli_connect_error();
	}

	//sorts content by section on sections.arrange
	$qstnSql="SELECT * FROM fields INNER JOIN sections ON fields.section_id=sections.section_id ORDER BY sections.arrange"; 
	$emailLogin = $_POST['emailLogin'];
	$passwordLogin = $_POST['passwordLogin'];
	//   !!! NOT MODULARIZED !!!
	$backloadSql="SELECT * FROM applications   
		INNER JOIN applicants ON applications.applicant_id=applicants.applicant_id 
		INNER JOIN identity ON applications.identity_id=identity.identity_id
		INNER JOIN referrals ON applications.referral_id=referrals.referral_id
		INNER JOIN schedules ON applications.schedule_id=schedules.schedule_id
		INNER JOIN experiences ON applications.experience_id=experiences.experience_id
		INNER JOIN materials ON applications.material_id=materials.material_id
		WHERE identity.email= '".$emailLogin."' AND identity.password ='".$passwordLogin."'";
		; 
	$qstnArray = mysqli_query($formCon, $qstnSql);
	$backloadArray = mysqli_query($appCon, $backloadSql);
	$backloadRow = mysqli_fetch_array($backloadArray);
	$arrangeCounter =0;
	while($row=mysqli_fetch_array($qstnArray))
		{
		//checks if moving to a new section, if counter is less than section.arrange, print header and body if available
		if($arrangeCounter<$row['arrange'])
		{
			if($row['pre_text']==NULL && $row['post_text']==NULL)
			{
				echo "<h3>".$row['section_name']."*</h3>";
			}else
			{
				echo "<h3>".$row['section_name']."</h3>";
			}
			echo "<b>".$row['section_description']."</b>";
			$arrangeCounter = $row['arrange'];
			}

		if($row['is_active']==0){  //flag functionality not working right now due to ambiguous column headers
			echo "is active flag:". $row['is_active'];
			}else
			{
				if($row['is_required']==1 && $row['post_text'] ==NULL && $row['pre_text']!= NULL){
					echo "<h4>".$row['pre_text']."*</h4>";
				}else{
					echo "<h4>".$row['pre_text']."</h4>";}

				$insideText="";  //variable to hold inside text content/reduce need for " and '
				$fieldName=$row['field_name'];  //variable to hold DB name content/reduce need for " and '
				$fieldId=$row['field_id'];  //variable to hold DB name content/reduce need for " and '

				echo $backloadRow[$fieldName]." from stored ".$fieldName."</br>";
				if($backloadRow[$fieldName]!=NULL){
					//echo $backloadRow['$fieldName']." from stored ".$fieldName."</br>";
					$insideText=$backloadRow[$fieldName];
				}elseif($row['inside_text']!=NULL){
					$insideText=$row['inside_text'];
				}

				if($row['options_target']==NULL)
				{
					echo "<input type='text' name='$fieldName' value='$insideText'>";
				}elseif($row['options_target']=='textarea'){
					echo "</br><textarea name='$fieldName'>".$insideText."</textarea>";
				}elseif($row['options_target']=='file'){
					//echo "</br><label for=".$fieldName.">".$fieldName."</label>";
					echo "<input type='file' name=".$fieldName." id=".$fieldName."><br>";
				}
				elseif($row['options_target']=="question_options"){
					
					//handles multiple choice options reading from question_options table
					$optnSql="SELECT * FROM fields INNER JOIN question_options WHERE fields.field_name = '$fieldName' AND question_options.field_name='$fieldName'";
					$optnArray = mysqli_query($formCon, $optnSql);

					while($optnRow=mysqli_fetch_array($optnArray)){
						$optnInputType = $optnRow['input_type'];
						//$optnFieldId=$optnRow['field_id'];
						$optnId=$optnRow['q_option_id'];
						$optnName=$optnRow['option_name'];
						if($optnInputType!=NULL){
							echo "<input type='$optnInputType' name='$fieldName' value='$optnName'>$optnName";	
						}else{echo "<input type='$optnInputType' name='$fieldName'>$optnName";}
						echo "</br>";
					}

				}else{
					$targetTable = $row['options_target'];
					$dropDownSql="SELECT * FROM $targetTable";
					$dropDownArray=mysqli_query($formCon,$dropDownSql);
					echo "</br>";
					echo "<select name='$fieldName'>";
					echo "<option>Select a value</option>";
						while($dropDownRow=mysqli_fetch_array($dropDownArray)){
							echo "test";
							$dropDownValue=$dropDownRow['name'];
							echo "<option value='$dropDownValue'>$dropDownValue</option>";
						}
					echo "</select>";
				}
				
				echo "</br>";
				if($row['post_text']!=NULL)
				{
					if($row['is_required']==1){
						echo $row['post_text']."*";
					}else{
						echo $row['post_text'];
					}
				}
			}
		}

?>

	<input type="submit" name ="submit" Value ="submit">
	<input type="submit" name ="save" Value ="save">
</form>

	<p><a href="select.php">Click here to view table</a></p>
	<?php include 'select.php' ?>

<p><a href="../index.php">Click here to go to gateway</a></p>
 </body>

</html>
