<?php
  //add time validation server side, so ninja's won't try to cheat by submitting more time
//shipping = local, domestic, international, n/a
//end_result = buy it now, canceled, winner, expired
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  } else {
    header("location: login.php");
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
  $locName = 'country/state/province';

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
  $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE active='y' ORDER BY name ASC");
  $sqlCatCount = mysqli_num_rows($sqlGetCat);
  
  if ($sqlCatCount > 0) {
    while ($row = mysqli_fetch_array($sqlGetCat)) {
      $catID = $row['id'];
	  $category = $row['name'];
	  
	  $catArray[] = $category;
    }
  }
  
  if (isset($_GET['name']) && isset($id)) {
    $state = urldecode($_GET['name']);
    
    if (isset($_GET['city'])) {
      $city = urldecode($_GET['city']);
    }
    
    if (isset($_GET['category'])) {
      $category = urldecode($_GET['category']);
      
      if(in_array($category, $catArray)) {
        $cat = $category;
      }
    }
    
    $msg = "start an auction in $state!";
    
    
    if (in_array($state, $stateArray)) {
      $region = 'united states of america';
      $locName = 'state';
    } else if (in_array($state, $europeArray)) {
      $region = 'europe';
      $locName = 'country';
    } else if (in_array($state, $canadaArray)) {
      $region = 'canada';
      $locName = 'province';
    } else if (in_array($state, $asiaArray)) {
      $region = 'asia/middle east';
      $locName = 'country';
    } else if (in_array($state, $oceaniaArray)) {
      $region = 'oceania';
      $locName = 'country';
    } else if (in_array($state, $latinArray)) {
      $region = 'latin america/carribean';
      $locName = 'country';
    } else if (in_array($state, $africaArray)) {
      $region = 'africa';
      $locName = 'country';
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
    $state = $_POST['state'];
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
        $msg .= '<span class="red">*</span> Specific time auction expires <span class="grey_color">Choose n/a if this is not essential</span><br />';
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
    } else if ((strcmp($catFlag, 'good') == 0) && ($buyPrice != '') && ($reservePrice > $buyPrice)) {
      $msg = 'Your reserve price is greater than your Buy It Now price. It must be lower.';
    } else if ((strcmp($catFlag, 'gig') == 0) &&  ($buyPrice  != '') && ($reservePrice < $buyPrice)) {
      $msg = 'Your max price is lower than your Buy It Now price. It must be higher.';
    } else if (!in_array($region, $regionArray)) {
      $msg = 'We can\'t seem to find the region you have submitted';
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
      
      //this is used to see if time has already passed
      $nowTime = time();
      $postedTime = strtotime($databaseTimeFormat);
      
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
          
          
          //query to check if user posted auction more than 3 times in one day
          $morning = mktime(0, 0, 0, date("m"), date("d"), date("y"));
          $midnight = mktime(0, 0, 0, date("m"), date("d")+1, date("y"));
          $sqlMorning = date("Y-m-d H:i:s", $morning);
          $sqlMidnight = date("Y-m-d H:i:s", $midnight);
          $sqlCheckPost = mysqli_query($link, "SELECT * FROM listings WHERE user_id='$id' AND active='y' AND post_date BETWEEN '$sqlMorning' AND '$sqlMidnight'");
          $sqlPostCount = mysqli_num_rows($sqlCheckPost);

          if ($sqlCheckCount > 0) {
            $msg = 'It appears you\'ve posted this auction already.';
          } else if ($sqlPostCount > 5) {
            $msg = 'To guard against spam, we only allow a maximum of 5 auctions posted a day.';
          } else if ($postedTime < $nowTime) {
            $msg = 'You cannot post an auction in the past.';
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
    if (isset($_SESSION['idx'])) {
      header('location: index.php');
    } else {
      header("location: login.php");
    }
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
  .red {
    color: red;
  }
  .grey_color {
    color: #666666;
    font-size: 11px;
  }
  
  body { 
      background: #f0f0f0; 
      font-weight: bold;
    }
    
    a { text-decoration: none; }
    
    h2 {
      clear: both;
      text-align: center;
    }
    
    #breadcrumb {
      float: left;
      margin-bottom: 10px;
    }
    
    #breadcrumb a {
      background: #348075;
      padding: 3px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
    }
    
    #breadcrumb a:hover {
      background: #287368;
      cursor: pointer;
    }
    
     #log_options {
      float: right;
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
    
    #content {
      width: 370px;
      margin: auto;
      clear: both;
      background-color: white;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
      padding: 30px;
    }
    
    .cat_choose {
      text-align: center;
    }
    
    form input[type=submit], input[type=file] {
      background: #348075;
      padding: 5px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    form input[type=submit]:hover, input[type=file]:hover {
      background: #287368;
      cursor: pointer;
    }
    
    .gig_title {
      font-size: 20px;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      if ($('#category').val().match('--')) {
        $('.fields').hide();
      }
      
      if ($('#category').val().match(/^gig/)) {
        $('.gig').show();
        $('.good').hide();
      } else if ($('#category').val().match(/^good/)) {
        $('.good').show();
        $('.gig').hide(); 
      }
      
      $('#category').change(function() {
        var selectedType = $(this).val();
          if (selectedType.match(/^gig/)) {
            $('.fields').show();
            $('.gig').show();
            $('.good').hide();
            $('.good_only').hide();
          } else if (selectedType.match(/^good/)) {
            $('.fields').show();
            $('.good').show();
            $('.good_only').show();
            $('.gig').hide(); 
          } else {
            $('.fields').hide();
          }
      });//end change
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
  <div id="breadcrumb"><a href="index.php">home</a> >> <a href="cities.php?name=<?php echo $state; ?>"><?php echo $state; ?></a> <?php echo $city != '' ? ">> $city" : ''; ?></div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <h2><?php echo $msg; ?></h2>
  <div id="content">
  <form action="submit.php" enctype="multipart/form-data" method="post" id="submitForm">
    <div class="cat_choose">
    category<br />
    <select name="category" id="category" selected="<?php print $cat; ?>"><?php print dropDown($catArray, $cat); ?><br /><br /></select>
    </div>
    <div class="fields">
      <span class="gig gig_title"><br />lowest bid wins</span><br /><br />
      end time &nbsp;&nbsp;<span class="grey_color">time this auction will end</span><br />
      <select name="day" id="day" selected="<?php print $day; ?>"><?php print dropDown($daysArray, $day); ?></select>
      <select name="time" id="time" selected="<?php print $time; ?>"><?php print dropDown($hoursArray, $time); ?></select>
      <select name="ampm" id="ampm" selected="<?php print $ampm; ?>"><?php print dropDown($amPmArray, $ampm); ?></select><br /><br />
      title<br />
      <input type="text" id="title" value="<?php print "$title"; ?>" name="title" size="60" /><br /><br />
      description<br />
      <textarea name="description" cols="45" rows="5" id="description"><?php print "$desc"; ?></textarea><br /><br />
      region<br />
      <input type="text" name="region" id="region" value="<?php print $region; ?>" size="40" readonly="readonly" /><br />
      <?php echo $locName ?><br />
      <input type="text" name="state" id="state" value="<?php print "$state"; ?>" size="40" readonly="readonly" /><br />
      city <br />
      <input type="text" value="<?php print "$city"; ?>" name="city" id="city" size="40" /><br /><br />
      <span class="gig">max</span><span class="good">reserve</span> price (optional)&nbsp;&nbsp;<span class="grey_color"><span class="gig">most i'm willing to pay (numbers only)</span><span class="good">least I'm willing to accept (numbers only)</span></span><br />
      $<input type="text" value="<?php print "$reservePrice"; ?>" name="reservePrice" id="reservePrice" size="5" maxlength="6" onKeyPress="return numbersonly(this, event)"/>.00<br />
      buy it now price (optional)<br /><span class="grey_color">if bidder bids <span class="gig">less</span><span class="good">more</span> than this amount, they win the auction (numbers only)</span><br />
      $<input type="text" value="<?php print "$buyPrice"; ?>" name="buyPrice" id="buyPrice" size="5" maxlength="6" onKeyPress="return numbersonly(this, event)"/>.00<br /><br />
      <span class="good_only">this item:<br />
      &nbsp;<input type="radio" name="group1" value="l" checked/> is available for local pickup only<br /> 
      &nbsp;<input type="radio" name="group1" value="d" /> can be arranged to be shipped domestically<br />
      &nbsp;<input type="radio" name="group1" value="i" /> can be arranged to be shipped internationally<br /><br /></span>
      photo upload (optional)&nbsp;&nbsp;<span class="grey_color">.jpg, .jpeg, .gif and .png only please</span><br />
      <input type="file" name="uploaded_file" id="uploaded_file"/><br /><br />
      <input type="hidden" name="edit" value="<?php echo $editing ?>" /></span>
      <!--<?php echo $editing == TRUE ? '<input type="checkbox" name="remove" value="y">Remove this task<br /><br />' : ''; ?>
      <?php echo $editing == TRUE ? '<input type="hidden" name="jobID" value="' . $jobID . '" />' : ''; ?>-->
      <input name="myButton" type="submit" id="myButton" value="Submit" />
    </div><!--end fields-->
  </form>
  </div>
</body>
</html>