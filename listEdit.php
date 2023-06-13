<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
   if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  } else {
    header("location: index.php");
  }
  
  $msg = '';
  $title = '';
  $desc = '';
  $listID = '';
  $cancelText = '';

  if (isset($_GET['id']) && isset($id)) {
    $listID = preg_replace('#[^0-9]#', '', $_GET['id']);
    $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listID' AND active='y' LIMIT 1"); 
    $sqlListCount = mysqli_num_rows($sqlGetList);
    
    if ($sqlListCount > 0) {
      while ($row = mysqli_fetch_array($sqlGetList)) {
        $userID = $row['user_id'];
        
        if ($id != $userID) {
          header("location: index.php");
        } else {
          $title = htmlkarakter($row['title']);
          $desc = htmlkarakter($row['description']);
          $photo = $row['photo_name'];
          $msg = 'In respect of possible past bidders, only certain parts can be edited.<br /> 
                  If you must make a change that isn\'t possible here, please cancel this auction and start a new one.';
          /*
          $day = date("D M j", $unixTime);
          $time = date("g:i", $unixTime);
          $ampm = date("a", $unixTime);
          */
        }//end if ($id != $userID)
      }//while ($row = mysqli_fetch_array($sqlGetJob))
    } else {
      $msg = 'This listing doesn\'t exist.';
    }
  } else if (isset($_POST['myButton']) && isset($id)) { 
    $listID = preg_replace('#[^0-9]#', '', $_POST['listID']);
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $cancelText = $_POST['cancelText'];
    $remove = '';
    
    isset($_POST['remove']) ? $remove = 'y' : $remove = 'n';
    $fileTmpLoc = '';
    
    if (is_uploaded_file($_FILES["uploaded_file"]["tmp_name"])) {
      $fileTmpLoc = $_FILES["uploaded_file"]["tmp_name"];
    }
    
    $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listID' AND active='y' LIMIT 1"); 
    $sqlListCount = mysqli_num_rows($sqlGetList);
    
    if ($sqlListCount > 0) {
      while ($row = mysqli_fetch_array($sqlGetList)) {
        $userID = $row['user_id'];
        $origTitle = $row['title'];
        
        if ($id != $userID) {
          header("location: index.php");
        } else if (($title == '') || ($desc == '')) {
          $msg = 'You need a title and a description';
        } else if ((strcmp($remove,'y') == 0) && ($cancelText == '')) {
          $msg = 'You need to give a reason as to why you\'re cancelling this auction'; 
        } else if (($title != '') && ($desc != '') && ($fileTmpLoc != '') && ($id == $userID) && (strcmp($remove,'n') == 0)) {
          $title = clean($title);
          $desc = clean($desc);
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
	  			
	  		  $sqlUpdateTask = mysqli_query($link, "UPDATE listings SET title='$title', description='$desc',photo_name='$fileName' WHERE id='$listID'");
              header("location: listing.php?id=$listID");
        	}//end if ($placeFile != true)
          }//end if($fileSize > 1048576)
        } else if (($title != '') && ($desc != '') && ($fileTmpLoc == '') && ($id == $userID) && (strcmp($remove,'y') != 0)) { 
          $title = clean($title);
          $desc = clean($desc);
          
          $sqlUpdateTask = mysqli_query($link, "UPDATE listings SET title='$title', description='$desc' WHERE id='$listID'");
          header("location: listing.php?id=$listID");
        } else if ((strcmp($remove,'y') == 0) && ($cancelText != '') && ($id == $userID)) {
          $cancelText = clean($cancelText);
          $sqlUpdateJob = mysqli_query($link, "UPDATE listings SET end_result='c', active='n' WHERE id='$listID' LIMIT 1");
          
          $sqlDistinctBids = mysqli_query($link, "SELECT DISTINCT bid_user_id FROM bids WHERE listing_id='$listID' AND active='y'");
          $sqlCountBids = mysqli_num_rows($sqlDistinctBids);
          
          if ($sqlCountBids > 0) {
            while ($row = mysqli_fetch_array($sqlDistinctBids)) {
              $listUserID = $row['listing_owner_id'];
              $username = getUsername($listUserID);
              $bidUserID = $row['bid_user_id'];
              
              $title = 'AUCTION CANCELED: ' . $origTitle;
              $cancelMess = '<a href="profile.php?id=' . $listUserID . '">' . $username . '</a> has canceled this auction. Their reason:<br /> ' . $cancelText;
              
              $slqInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$bidUserID', '$id', '$title', '$cancelMess', now())");
            }//end while ($row = mysqli_fetch_array($sqlDistinctBids)
          }//end if ($sqlCountBids > 0)
          header("location: index.php");
        } else {
          $msg = 'Couldn\'t process request';
        }
      }//end while ($row = mysqli_fetch_array($sqlGetList))
    }//end if ($sqlListCount > 0)
  } else {
    header('location: index.php');
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
  
    body { 
      background: #f0f0f0; 
    }
    
    a { text-decoration: none; }
    
    #content {
      clear: both;
      background-color:#FFFFFF;
      padding: 40px;
      width: 350px;
      margin: auto;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }

    #breadcrumb {
      float: left;
      margin-bottom: 10px;
      font-weight: bold;
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
    
	h3 {
      clear: both;
      text-align: center;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      if ($('#remove').is(':checked')) {
        $('.canceled').show();
      } else {
        $('.canceled').hide();
      }
      
      $('#remove').click(function () {
      $(".canceled").toggle(this.checked);
      });
    });
    
  </script>
</head>
<body>
  <div id="breadcrumb"><a href="index.php">home</a> >> <a href="listing.php?id=<?php echo $listID; ?>">auction</a></div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <h3><?php print $msg; ?></h3>
  <div id="content">
  <?php echo isset($photo) ? $photo != '' ? '<a href="photo.php?name=' . $photo . '"><img src="media/resized_' . $photo . '" /></a>' : '' : ''; ?>
  <form action="listEdit.php" enctype="multipart/form-data" method="post" id="submitForm">
     <strong>title</strong><br />
     <input type="text" id="title" value="<?php print $title; ?>" name="title" size="60" /><br /><br />
     <strong>description</strong><br />
     <textarea name="description" cols="45" rows="5" id="description"><?php print $desc; ?></textarea><br /><br />
     <strong>photo upload</strong> &nbsp;&nbsp;<span class="grey_color">.jpg, .jpeg, .gif and .png only please</span><br />
     <input type="file" name="uploaded_file" id="uploaded_file"/><br /><br />
     <input type="hidden" name="listID" value="<?php print $listID; ?>" />
     <input type="checkbox" id="remove" name="remove" value="y"><strong>cancel this auction</strong><br /><br />
     <span class="canceled">reason you are cancelling this auction:<br />
     <textarea name="cancelText" cols="45" rows="5" id="description"><?php print $cancelText; ?></textarea></span><br />
     <input name="myButton" type="submit" id="myButton" value="Submit" />
  </form>
  </div>
</body>
<html>