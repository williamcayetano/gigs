<?php
  //add time validation server side, so ninja's won't try to cheat by submitting more time
  //add region, state/country/province validation serverside
//shipping = local, domestic, international, n/a
//end_result = buy it now, canceled, winner, expired
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  } else {
    header("location: index.php");
  }
  
  $title = '';
  $desc = '';
  $city = '';
  $msg = '';
  $cat = '--';
  $day = '--';
  $time = '--';
  $state = '';
  $region = '--';
  $selectedDefault = '--';
  $ampm = 'pm';
  $price = '';
  $buyPrice = '';
  $reservePrice = '';
  //$editing = FALSE;

  $daysArray = array("today");
  for ($i = 1; $i < 7; $i++) {
    $incDays = mktime(0, 0, 0, date("m"), date("d")+$i, date("y"));
    $daysArray[] = date("D M j", $incDays);
  }
  
  $hoursArray = array('n/a', '1:00', '1:30', '2:00', '2:30', '3:00', '3:30', '4:00', '4:30', '5:00', '5:30', '6:00',
                      '6:30', '7:00', '7:30', '8:00', '8:30', '9:00', '9:30', '10:00', '10:30', '11:00', '11:30',
                      '12:00', '12:30');
  $amPmArray = array('am','pm');
  		
  $catArray = array();
  $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE active='y' ORDER BY name DESC");
  $sqlCatCount = mysqli_num_rows($sqlGetCat);
  
  if ($sqlCatCount > 0) {
    while ($row = mysqli_fetch_array($sqlGetCat)) {
      $catID = $row['id'];
	  $category = $row['name'];
	  
	  $catArray[] = $category;
    }
  }
  
  if (isset($_GET['state']) && isset($id)) {
    $state = urldecode($_GET['state']);
    
    if (isset($_GET['city'])) {
      $city = $_GET['city'];
    }
    
    $msg = "start an auction in $state!";
    
    if (in_array($state, $stateArray)) {
      $region = 'united states of america';
    } else if (in_array($state, $europeArray)) {
      $region = 'europe';
    } else if (in_array($state, $canadaArray)) {
      $region = 'canada';
    } else if (in_array($state, $asiaArray)) {
      $region = 'asia / middle east';
    } else if (in_array($state, $oceaniaArray)) {
      $region = 'oceania';
    } else if (in_array($state, $latinArray)) {
      $region = 'latin america / carribean';
    } else if (in_array($state, $africaArray)) {
      $region = 'africa';
    } else {
      header('location: index.php');
    }
  
  } else if (isset($_POST['myButton']) && isset($id)) {
    $cat = $_POST['category'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $day = $_POST['day'];
    $time = preg_replace('#[^0-9:na/-]#', '', $_POST['time']);
    $ampm = preg_replace('#[^amp]#', '', $_POST['ampm']);
    $city = $_POST['city'];
    $state = $_POST['country_state'];
    $region = $_POST['region'];
    $reservePrice = preg_replace('#[^0-9]#', '', $_POST['reservePrice']);
    $buyPrice = preg_replace('#[^0-9]#', '', $_POST['buyPrice']);
    $shipping = $_POST['group1'];
    $kaboom = explode(' - ', $cat);
    $catFlag = $kaboom[0];
    $fileTmpLoc = '';
    
    if (is_uploaded_file($_FILES["uploaded_file"]["tmp_name"])) {
      $fileTmpLoc = $_FILES["uploaded_file"]["tmp_name"];
    }
    
    if ((strcmp($cat,'--') == 0) || (!$title) || (!$desc) || (strcmp($day,'--') == 0) || 
        (strcmp($time,'--') == 0) || (!$city) || (!$state) || (strcmp($region,'--') == 0)) {
      $msg = '<span class="red">ERROR:</span> You did not submit the following required information:<br /><br />';
      
      if (strcmp($cat,'--') == 0) {
        $msg .= '<span class="red">*</span> Category<br />';
      } 
      if (!$title) {
        $msg .= '<span class="red">*</span> Title<br />';
      }
      if (!$desc) {
        $msg .= '<span class="red">*</span> Description<br />';
      }
      if (strcmp($day,'--') == 0) {
        $msg .= '<span class="red">*</span> Day that auction expires<br />';
      }
      if(strcmp($time,'--') == 0) {
        $msg .= '<span class="red">*</span> Specific time auction expires<span class="grey_color">Choose n/a if this is not essential</span><br />';
      }
      if(!$city) {
        $msg .= '<span class="red">*</span> City<br />';
      }
      if (!$state) {
        $msg .= '<span class="red">*</span> State<br />';
      }
      if (strcmp($region,'--') == 0) {
        $msg .= '<span class="red">*</span> Country<br />';
      }
    } else if ((strcmp($catFlag, 'goods') == 0) && ($buyPrice != '') && ($reservePrice > $buyPrice)) {
      $msg = 'Your reserve price is greater than your Buy It Now price. It must be lower.';
    } else if ((strcmp($catFlag, 'service offered') == 0) && ($buyPrice != '') && ($reservePrice > $buyPrice)) {
      $msg = 'Your reserve price is greater than your Buy It Now price. It must be lower.';
    } else if ((strcmp($catFlag, 'service request') == 0) &&  ($buyPrice  != '') && ($reservePrice < $buyPrice)) {
      $msg = 'Your max price is lower than your Buy It Now price. It must be higher.';
    } else {
      $cat = clean($cat);
      $title = clean($title);
      $desc = clean($desc);
      $day = clean($day);
      $city = clean($city);
      $state = clean($state);
      $region = clean($region);
      $city = strtolower($city);
      $state = strtolower($state);
      $region = strtolower($region);
      $databaseTimeFormat = '';
      $catID;
      
      if ((strcmp($shipping,'l')==0) || (strcmp($shipping,'d')==0) || (strcmp($shipping,'i')==0)) {
        //if shipping exists, just get first letter for flag
        if (strcmp(substr($cat, 0,1),'g') != 0) {
          $shipping = 'n';
        }
      } else {
        $shipping = 'n';
      }
      
      if (strcmp($time,'n/a') == 0) {
        if (strcmp($time,'today') == 0) {//posting expires at 12:00 am
          $incDays = mktime(0, 0, 0, date("m"), date("d"), date("y"));
          $databaseTimeFormat = date("Y-m-d", $incDays);
        } else {
          //add full day so posting expires next day at 12:00 am
          $timeStamp = strtotime($day) + 86400;
          $databaseTimeFormat = date("Y-m-d", $timeStamp);
        }
      } else {
        $newDate = $day . ' ' . $time . ' ' . $ampm;
        $timeStamp = strtotime($newDate);
        $databaseTimeFormat = date("Y-m-d H:i:s", $timeStamp);
      }
      
      //get cat id
      $sqlGetCat = mysqli_query($link, "SELECT id FROM categories WHERE name='$cat' LIMIT 1");
      $sqlNumCat = mysqli_num_rows($sqlGetCat);
    
      if ($sqlNumCat > 0) {
        while ($row = mysqli_fetch_array($sqlGetCat)) {
          $catID = $row['id'];
        }
        
        if(isset($catID)) {
		  //query to check if this task posted already
          $sqlCheckList = mysqli_query($link, "SELECT * FROM listings WHERE user_id='$id' AND title='$title' AND active='y'");
          $sqlCheckCount = mysqli_num_rows($sqlCheckList);
          if ($sqlCheckCount > 0) {
            $msg = 'It appears you\'ve posted this auction already.';
          } else {
            if ($fileTmpLoc != '') {
              $fileName = $_FILES["uploaded_file"]["name"]; // The file name
      		  $fileSize = $_FILES["uploaded_file"]["size"]; // File size in bytes
      		  $fileErrorMsg = $_FILES["uploaded_file"]["error"]; // 0 = false | 1 = true
      		  $pathSuffix = pathinfo($fileName);
              $pathExt = $pathSuffix['extension'];
              
              if($fileSize > 1048576) { // if file size is larger than 1 Megabyte in bytes
        		$msg = '<span class="red">ERROR:</span> Your photo was larger than 1 Megabyte in size. Choose a smaller photo';
        		unlink($fileTmpLoc); // Remove the uploaded file from the PHP temp folder
	  		  } else if (!preg_match("/(jpg|png|jpeg|gif)$/i", $pathExt)) {   
         		  $msg = '<span class="red">ERROR:</span> Your image was not .gif, .jpg, .png, .jpeg or .zip';
         		  unlink($fileTmpLoc); 
	  		  } else if ($fileErrorMsg == 1) { // if file upload error key is equal to 1
                $msg = '<span class="red">ERROR:</span> An error occured while processing the file. Try again.';
	  		  } else {
	  		  
	  		    $fileName = time().rand() . "." . $pathExt;
        	    $placeFile = move_uploaded_file($fileTmpLoc, 'media/' . $fileName);
        	  
        	    if ($placeFile != true) {
          		  @unlink($fileTmpLoc); 
          		  $msg = '<span class="red">ERROR:</span> File not uploaded. Try again.';
        	    } else {
        	      //resize image
	    		  include_once("scripts/ak_php_img_lib_2.0.php");
	  			  $target_file = "media/$fileName";
	  			  $resized_file = "media/resized_$fileName"; //you can change this to any other folder to separate smaller images
	  			  $wmax = 300;
	  			  $hmax = 250;
	  			  @ak_img_resize($target_file, $resized_file, $wmax, $hmax, $pathExt);
	  			
	  			  //make thumbnail
	  			  $target_file = "media/resized_$fileName";
	  			  $thumbnail = "media/thumb_$fileName";
	  			  $wthumb = 110;
	  			  $hthumb = 110;
	  			  @ak_img_thumb($target_file, $thumbnail, $wthumb, $hthumb, $pathExt);
	  			
	  			  $sqlPostTask = mysqli_query($link, "INSERT INTO listings (user_id, category_id, time, city, country_state, region, reserve_price, buy_price, title, description, photo_name, shipping, post_date) VALUES
            									  ('$id', '$catID', '$databaseTimeFormat', '$city', '$state', '$region', '$reservePrice', '$buyPrice', '$title', '$desc', '$fileName', '$shipping', now())");
            	  //send emails here
                  $listID = mysqli_insert_id($link);
                  header("location: listing.php?id=$listID");
        	    }//end if ($placeFile != true)
              }//end if($fileSize > 1048576)								  
            } else {
              $sqlPostTask = mysqli_query($link, "INSERT INTO listings (user_id, category_id, time, city, country_state, region, reserve_price, buy_price, title, description, shipping, post_date) VALUES
            									  ('$id', '$catID', '$databaseTimeFormat', '$city', '$state', '$region', '$reservePrice', '$buyPrice', '$title', '$desc', '$shipping', now())");
              //send emails here
              $listID = mysqli_insert_id($link);
              header("location: listing.php?id=$listID");
            }//end if (fileTmpLoc)
          }
        }
      } else {
        $msg = "Your category selection didn't match anything in our system.";
      }
      
    }
  } else { 
    header('location: index.php');
  }
?>

<!DOCTYPE html>
<html>
<head>
  <style>
  .red {
    color: red;
  }
  .grey_color {
    color: #666666;
    font-size: 11px;
  }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      if ($('#category').val().match('--')) {
        $('.fields').hide();
      }
      
      if ($('#category').val().match(/^service/)) {
        $('.service').show();
        $('.good').hide();
      } else if ($('#category').val().match(/^good/)) {
        $('.good').show();
        $('.service').hide(); 
      }
      
      $('#category').change(function() {
        var selectedType = $(this).val();
          if (selectedType.match(/^service request/)) {
            $('.fields').show();
            $('.service').show();
            $('.good').hide();
            $('.good_only').hide();
          } else if (selectedType.match(/^good/)) {
            $('.fields').show();
            $('.good').show();
            $('.good_only').show();
            $('.service').hide(); 
          } else if (selectedType.match(/^service offered/)) {
            $('.fields').show();
            $('.good').show();
            $('.good_only').hide();
            $('.service').hide();
          } else {
            $('.fields').hide();
          }
      });//end change
      
      if ($('#region').val().match('--')) {
        $('.state_type').hide();
        $('.state_drop').hide();
      }
      
      if ($('#region').val().match(/^United States/)) {
         $('.state_drop').show();
         $('.state_type').hide();
      } else if (!$('#region').val().match('--'))  {
        $('.state_type').show();
        $('.state_drop').hide();
      }
      
      $('#region').change(function() {
        var selectedType = $(this).val();
        if (selectedType.match(/^United States/)) {
          $('.state_drop').show();
          $('.state_type').hide(); 
        } else {
          $('.state_type').show();
          $('.state_drop').hide();
        }
      });
    });//end ready
  
	// copyright 1999 Idocs, Inc. http://www.idocs.com
	// Distribute this script freely but keep this notice in place
	function numbersonly(myfield, e, dec)
	{
	  var key;
	  var keychar;

	  if (window.event)
   		key = window.event.keyCode;
	  else if (e)
   		key = e.which;
	  else
   		return true;
	  keychar = String.fromCharCode(key);

	  // control keys
	if ((key==null) || (key==0) || (key==8) || 
    	(key==9) || (key==13) || (key==27))
   	  return true;

     // numbers
	else if ((("0123456789").indexOf(keychar) > -1))
      return true;

	// decimal point jump
	else if (dec && (keychar == ".")) {
   	  myfield.form.elements[dec].focus();
      return false;
    } else {
      return false;
    }
  }
  </script>
</head>
<body>
  <h3><?php echo $msg; ?></h3>
  <form action="submit.php" enctype="multipart/form-data" method="post" id="submitForm">
    Category<br />
    <select name="category" id="category" selected="<?php print $cat; ?>"><?php print dropDown($catArray, $cat); ?></select><br /><br />
    <div class="fields">
      End Time &nbsp;&nbsp;<span class="grey_color">Time this auction will end</span><br />
      <select name="day" id="day" selected="<?php print $day; ?>"><?php print dropDown($daysArray, $day); ?></select>
      <select name="time" id="time" selected="<?php print $time; ?>"><?php print dropDown($hoursArray, $time); ?></select>
      <select name="ampm" id="ampm" selected="<?php print $ampm; ?>"><?php print dropDown($amPmArray, $ampm); ?></select><br /><br />
      Title<br />
      <input type="text" id="title" value="<?php print "$title"; ?>" name="title" size="60" /><br /><br />
      Description<br />
      <textarea name="description" cols="45" rows="5" id="description"><?php print "$desc"; ?></textarea><br /><br />
      Country<br />
      <select name="region" id="region" selected="<?php print $region; ?>"><?php print dropDown($regionArray, $region); ?></select><br />
      City <br />
      <input type="text" value="<?php print "$city"; ?>" name="city" id="city" size="40" /><br />
      <span class="state_drop">State<br />
      <select name="state" selected="<?php print $state; ?>"><?php print dropDown($stateArray, $state); ?></select><br /></span>
      <span class="state_type">State/Territory/Province/etc.<br />
      <input type="text" value="<?php print "$state"; ?>" name="state" size="40" /><br /></span>
      <span class="service">Max</span><span class="good">Reserve</span> price (optional)&nbsp;&nbsp;<span class="grey_color"><span class="service">Most I'm willing to pay (numbers only)</span><span class="good">Least I'm willing to accept (numbers only)</span></span><br />
      $<input type="text" value="<?php print "$reservePrice"; ?>" name="reservePrice" id="reservePrice" size="5" maxlength="6" onKeyPress="return numbersonly(this, event)"/>.00<br />
      Buy it now price (optional)&nbsp;&nbsp;<span class="grey_color">If someone clicks Buy It Now button or bids <span class="service">less</span><span class="good">more</span> than this amount, they will win the auction (numbers only)</span><br />
      $<input type="text" value="<?php print "$buyPrice"; ?>" name="buyPrice" id="buyPrice" size="5" maxlength="6" onKeyPress="return numbersonly(this, event)"/>.00<br /><br />
      <span class="good_only">This item:<br />
      &nbsp;<input type="radio" name="group1" value="l" checked/> is available for local pickup only<br /> 
      &nbsp;<input type="radio" name="group1" value="d" /> can be arranged to be shipped domestically<br />
      &nbsp;<input type="radio" name="group1" value="i" /> can be arranged to be shipped internationally<br /><br /></span>
      Photo upload (optional)&nbsp;&nbsp;<span class="grey_color">.jpg, .jpeg, .gif and .png only please</span><br />
      <input type="file" name="uploaded_file" id="uploaded_file"/><br /><br />
      <input type="hidden" name="edit" value="<?php echo $editing ?>" /></span>
      <!--<?php echo $editing == TRUE ? '<input type="checkbox" name="remove" value="y">Remove this task<br /><br />' : ''; ?>
      <?php echo $editing == TRUE ? '<input type="hidden" name="jobID" value="' . $jobID . '" />' : ''; ?>-->
      <input name="myButton" type="submit" id="myButton" value="Submit" />
    </div><!--end fields-->
  </form>
</body>
</html>