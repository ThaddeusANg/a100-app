<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>A100 Application Gateway</title>

		<!-- Bootstrap -->
		<link href="public_html/css/bootstrap.css" rel="stylesheet">

		<!-- Signin stylesheet from http://getbootstrap.com/examples/signin/ -->
		<link href="public_html/css/signin.css" rel="stylesheet">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

		<!-- Fonts -->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,800italic,300italic,600italic,400,300,600,700,800|Montserrat:400,700' rel='stylesheet' type='text/css'>
    	<link href="public_html/css/font-awesome.min.css" rel="stylesheet">

	</head>

	<body>

		<div class="container form-signin">

		    <div>
		    	<p>
		    		<h3><a href="resources/new_applicant.php">New applicant? Click here.</a></h3>
		    	</p>
		    </div>

			<form action="resources/existing_applicant.php" method="post" class="form-signin" role="form">
		        <h3 class="form-signin-heading">Returning Applicants</h3>
		        <input type="email" class="form-control" placeholder="Email address" name="emailLogin" required autofocus>
		        <input type="password" class="form-control" placeholder="Password" name="passwordLogin" required>
		        <button class="btn btn-lg btn-primary btn-block" type="submit" name="login" value="login">
		        	Resume Application</button>
		    </form>



		</div>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="public_html/js/bootstrap.js"></script>

	</body>

</html>