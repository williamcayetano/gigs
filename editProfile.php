<?php  
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
      $id = $logOptions_id;
  } else {
    header('location: index.php');
  }


  $errorMsg = '';
  $firstName = '';
  $lastName = '';
  $city = '';
  $defaultSelect = '--';
  
  $sqlGetInfo = mysqli_query($link, "SELECT * FROM users WHERE id='$id' AND active='y' LIMIT 1");
  while ($row = mysqli_fetch_array($sqlGetInfo)) {
     $userID = $row['id'];
     $firstName = htmlkarakter($row['first_name']);
     $lastName = htmlkarakter($row['last_name']);
     $city = htmlkarakter($row['city']);
     $state = htmlkarakter($row['country_state']);
     $selectedRegion = $row['region'];
        
     $selectedRegion != "" ? $selectedRegion : $selectedRegion = '--';
     $state != "" ? $state : $state = '--';
  }
    
  if (isset($_POST['firstName']) && isset($id)) {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $city = $_POST['city'];
    $state = $_POST['country_state'];
    $region = $_POST['region'];
    
    if ($firstName != "" && $lastName != "") {
      $firstName = clean($firstName);
      $lastName = clean($lastName);
      $firstName = substr($firstName, 0, 15);//15 char limit on first/last name
      $lastName = substr($lastName, 0, 15);
      //$sqlNameUpdate = mysqli_query($link, "UPDATE users SET first_name='$firstName', last_name='$lastName' WHERE id='$id'");
    }  
    
    if ($city != "" && $state != "--" && $region != "--") {
      $city = clean($city);
      $state = clean($state);
      $region = clean($region);
      
      if (strcmp($region,'united states of america') == 0) {
        if (in_array($state, $stateArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      } else if (strcmp($region,'canada') == 0) {
        if (in_array($state, $canadaArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      } else if (strcmp($region,'europe') == 0) {
        if (in_array($state, $europeArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      } else if (strcmp($region,'asia/middle east') == 0) {
        if (in_array($state, $asiaArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      } else if (strcmp($region,'latin america/carribean') == 0) {
        if (in_array($state, $latinArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      } else if (strcmp($region,'africa') == 0) {
        if (in_array($state, $africaArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      } else if (strcmp($region,'oceania') == 0) {
        if (in_array($state, $oceaniaArray)) {
          $sqlLocUpdate = mysqli_query($link, "UPDATE users SET city='$city', country_state='$state', region='$region' WHERE id='$id'");
        }
      }
    }  
	header('location: profile.php?id=' . $id);
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
  <meta name="description" content="GigNGood is an online marketplace
  where members can list and bid on gigs (services), places, and goods in an auction style
  format." />
  <meta name="keywords" content="gig, good, gigs, goods, auction, online auction, services, classifieds" />
  <title>GigNGood | Bid On Gigs And Goods In Online Classifieds Auctions | Gig Good Auction Service Item</title>
  <style>
     body { 
      background: #f0f0f0; 
    }
    
    a { text-decoration: none; }
    
    h2 { 
      clear: both;
      text-align: center; 
    }
    
     #content {
      clear: both;
      background-color:#FFFFFF;
      padding: 40px;
      width: 350px;
      margin: auto;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }
    
    #edit_link a {
      float: left;
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #edit_link a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    input[type=submit] {
  	  border: none;
  	  margin-right: 1em;
      padding: 6px;
      font-size: 12px;
      -moz-border-radius: 4px;
      -webkit-border-radius: 4px;
      background: #348075;
      color: white;
  	  box-shadow: 0 1px 0 white;
      -moz-box-shadow: 0 1px 0 white;
      -webkit-box-shadow: 0 1px 0 white;
      font-weight: bold;
	}
	
	 #log_options {
      float: right;
      font-weight: bold;
    }
    
    #log_options a {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
    }
    
    #log_options a:hover {
      background: #287368;
      cursor: pointer;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      if ($('#region').val().match('--')) {
        $('#state_wrapper').hide();
      }
      
      if ($('#region').val().match(/^united states of america/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($stateArray, $state); ?>');
      } else if ($('#region').val().match(/^canada/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($canadaArray, $state); ?>');
      } else if ($('#region').val().match(/^europe/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($europeArray, $state); ?>');
      } else if ($('#region').val().match(/^asia\/middle east/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($asiaArray, $state); ?>');
      } else if ($('#region').val().match(/^latin america\/carribean/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($latinArray, $state); ?>');
      } else if ($('#region').val().match(/^africa/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($africaArray, $state); ?>');
      } else if ($('#region').val().match(/^oceania/)) {
        $('#state_wrapper').show();
        $('#country_state').html('<?php print dropDown($oceaniaArray, $state); ?>');
      }
      
      $('#region').change(function() {
        var selectedType = $(this).val();
          if (selectedType.match(/^united states of america/)) {
            $('#state_wrapper').show();
            $('#country_state').html('<?php print dropDown($stateArray, $defaultSelect); ?>');
          } else if (selectedType.match(/^canada/)) {
            $('#state_wrapper').show();
            $('#country_state').html('<?php print dropDown($canadaArray, $defaultSelect); ?>');
          } else if (selectedType.match(/^europe/)) {
            $('#state_wrapper').show();
        	$('#country_state').html('<?php print dropDown($europeArray, $defaultSelect); ?>');
          } else if (selectedType.match(/^asia\/middle east/)) {
            $('#state_wrapper').show();
        	$('#country_state').html('<?php print dropDown($asiaArray, $defaultSelect); ?>');
          } else if (selectedType.match(/^latin america\/carribean/)) {
            $('#state_wrapper').show();
        	$('#country_state').html('<?php print dropDown($latinArray, $defaultSelect); ?>');      
          } else if (selectedType.match(/^africa/)) {
            $('#state_wrapper').show();
        	$('#country_state').html('<?php print dropDown($africaArray, $defaultSelect); ?>');
          } else if (selectedType.match(/^oceania/)) {
            $('#state_wrapper').show();
        	$('#country_state').html('<?php print dropDown($oceaniaArray, $defaultSelect); ?>');
          } else {
            $('#state_wrapper').hide();
          }
      });//end change
    });
  </script>
</head>
<body>
    <div id="edit_link"><a href="editSettings.php">Edit Account Settings</a></div>
    <div id="log_options"><?php echo $logOptions; ?></div>
    <h2>Edit Your Profile Data Here</h2>
    <h3><?php echo $errorMsg; ?></h3>
  <div id="content">
    <form action="editProfile.php" method="post" name="editForm" id="editForm">
    <hr />
      <strong>first name:</strong><input name="firstName" type="text" class="formFields" id="firstName" value="<?php print "$firstName"; ?>" size="12" maxlength="20" />&nbsp;&nbsp;
      <strong>last name:</strong><input name="lastName" type="text" class="formFields" id="lastName" value="<?php print "$lastName"; ?>" size="12" maxlength="20" /> 
      <hr />
      <strong>region:</strong><select name="region" id="region"><?php print dropDown($regionArray, $selectedRegion); ?></select>
      <hr />
      <div id="state_wrapper"><strong>country/state:</strong><select name="country_state" id="country_state"></select>
      <hr />
      </div>
      <strong>city:</strong><input name="city" type="text" class="formFields" id="city" value="<?php print "$city"; ?>" size="12" maxlength="20" /><br />
      <hr />
      <input type="submit" name="button" id="button" value="Submit" />
    </form>
  </div><!--end content-->
</body>
</html>