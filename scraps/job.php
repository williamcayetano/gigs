<?php
//age job here; if job strtotime($time) < time()) { $title = 'this task has expired'; };
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  } else {
    $id = 0;
  }
  
  if (isset($_GET['id'])) {
    $jobID = preg_replace('#[^0-9]#', '', $_GET['id']);
    $expired = FALSE;
    
    $sqlGetJob = mysqli_query($link, "SELECT * FROM jobs WHERE id='$jobID' AND active='y' LIMIT 1");
    $sqlJobCount = mysqli_num_rows($sqlGetJob);
    if ($sqlJobCount > 0) { 
      while ($row = mysqli_fetch_array($sqlGetJob)) {
        $time = strtotime($row['time']);
        $hour = $row['time'];
        $nowTime = time();
        
        if ($time < $nowTime) {
          $title = 'This task has expired.';
          $sqlUpdateJob = mysqli_query($link, "UPDATE jobs SET active='n' WHERE id='$jobID' LIMIT 1");
          $sqlUpdateBids = mysqli_query($link, "UPDATE job_bids SET active='n' WHERE job_id='$jobID'");
          $expired = TRUE;
        } else {
        
          $userID = $row['user_id'];
          $categoryID = $row['category_id'];
        
          $street = htmlkarakter($row['street']);
          $city = htmlkarakter($row['city']);
          $state = $row['state'];
          $price = $row['price'];
          $obo = $row['obo'];
          $title = htmlkarakter($row['title']);
          //<> messes up html; " and ' mess up javascript, so I just strip them out
          $title = str_replace(array("<", ">", "'", "\""), array('','','',''), $title);
		  $desc =  htmlkarakter($row['description']);
		  $desc = str_replace(array("<", ">"), array('',''), $desc);
		  $postDate = strtotime($row['post_date']);
		  $postDate = date("D M j g:i a", $postDate);
		  $username = getUsername($userID);
		  $cat = '';
		
		  strcmp($obo,'y') == 0 ? $obo = 'or best offer' : $obo = 'firm';
		
		  $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE id='$categoryID' LIMIT 1");
		  $sqlNumCat = mysqli_num_rows($sqlGetCat);
		
		  if ($sqlNumCat > 0) {
		    while ($row2 = mysqli_fetch_array($sqlGetCat)) {
		      $category = $row2['category_name'];
		      $subcategory = $row2['subcategory_name'];
		    
		      $cat = $category . ' - ' . $subcategory;
		    }
		  } 
		}//end if ($time < $nowTime)
      }//while ($row = mysqli_fetch_array($sqlGetJob))
      if ($expired == FALSE) {
        if ($userID != $id) {//only post if user is not viewing their own post
          $device = getUserAgent();
          $ipAddress = getenv('REMOTE_ADDR');
          $referer = '';
          if(isset($_SERVER['HTTP_REFERER'])){
            $referer = $_SERVER['HTTP_REFERER'];
          }
          $sqlPostView = mysqli_query($link, "INSERT INTO job_views (job_id, user_id, ipaddress, user_agent, referer, post_date) VALUES 
        									  ('$jobID', '$id', '$ipAddress', '$device', '$referer', now())");
        }
      }//end if ($expired == FALSE)
      
      strcmp($hour,'y') == 0 ? $time = date("D M j g:i a", $time) : $time = date("D M j g:i a", $time);
    } else {
      echo '<h3>Task cannot be found. It may have expired or been removed by member</h3>';
      exit();
    } 
  } else if (isset($_POST['pmTextArea']) && isset($id)) {
    $recID = preg_replace('#[^0-9]#', '', $_POST['pmRecId']);
    $jobID = preg_replace('#[^0-9]#', '', $_POST['pmJobId']);
    $message = clean($_POST['pmTextArea']);
    $price = preg_replace('#[^0-9]#', '', $_POST['price']);
    
    if (isset($recID) && isset($jobID)) {
      $sqlCheckJob = mysqli_query($link, "SELECT * FROM jobs WHERE id='$jobID' AND user_id='$recID' AND active='y' LIMIT 1");
      $sqlCheckCount = mysqli_num_rows($sqlCheckJob);
      
      if ($sqlCheckCount > 0) {
        while($row = mysqli_fetch_array($sqlCheckJob)) {
          $title = 'BID: ' . $row['title'];
          $userID = $row['user_id'];
          $origPrice = $row['price'];
          $obo = $row['obo'];
          
          if ((strcmp($obo,'y') == 0) && ($price != '') && ($message != '') && ($id != $userID)) {
            $sqlInsertBid = mysqli_query($link, "INSERT INTO job_bids (job_id, to_id, from_id, price, subject, message, post_date) VALUES ('$jobID', '$userID', '$id', '$price', '$title', '$message', now())");
            echo 'Bid sent';
            exit();
          } else if ((strcmp($obo,'n') == 0) && ($message != '') && ($id != $userID)) {
            $sqlInsertBid = mysqli_query($link, "INSERT INTO job_bids (job_id, to_id, from_id, price, subject, message, post_date) VALUES ('$jobID', '$userID', '$id', '$origPrice', '$title', '$message', now())");
            echo 'Bid sent';
            exit();
          } else if ($message == '') {
            echo 'You have not set a message for your bid';
            exit();
          } else if ($id == $userID) {
            echo "You can't send a bid to your own task silly!";
            exit();
          }
        } 
      } else {
        echo 'Couldn\'t locate job';
        exit();
      }
    } else {
      echo 'Couldn\'t locate job';
      exit();
    }
  }
?>
<!DOCTYPE html>
<html>
<head>
  <style>
    p {
      border:1px dotted black;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      $("#toggle").hide();
    }); //end ready
    
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
    	(key==9) || (key==13) || (key==27)) {
   	    return true;
	  } else if ((("0123456789").indexOf(keychar) > -1)) {
        return true;
	  } else if (dec && (keychar == ".")) {
   	    myfield.form.elements[dec].focus();
        return false;
      } else {
        return false;
      }
    }
    function toggleBidBox(subject, jobID, price, recUsername, recID, obo) {
       $("#subjectShow").text(subject);
       $("#recipientShow").text(recUsername);
       document.replyForm.price.value = price;
       document.replyForm.pmJobId.value = jobID;
       document.replyForm.pmRecId.value = recID;
       document.replyForm.replyBtn.value = "Send bid to "+recUsername;
       if (obo != 'or best offer') {
         $('.priceInput').hide();
       }
       if ($('#toggle').is(":hidden")) {
         $('#toggle').slideDown();
          //$('#toggle').fadeIn(1000);
          $('#pmFormProcessGif').hide();
       } else {
         $('#toggle').slideUp();
          //$('#toggle').hide();
       }
     }
     
     function processReply() {
       var formData = $('#replyForm').serialize();
       var url = "job.php";
       if ($("#pmTextArea").val() == "") {
         $("#pmStatus").text("Please type in your message.").show().fadeOut(6000);
       } else {
         $("#pmFormProcessGif").show();
         $.post(url, formData,  
         function(data) {
           document.replyForm.pmTextArea.value = "";
           $("#pmFormProcessGif").hide();
           $("#toggle").slideUp();
           $("#pmFinal").html("&nbsp; &nbsp;"+data).show();
         });
       }
     }
  </script>
</head>
<body>
  <?php if (isset($id) && isset($userID) && isset($jobID)) { 
          if ($id == $userID) {
            echo "<span class=\"jobLink\"><a href=\"submit.php?id=$jobID\">Edit Task</a></span>";
          } else {
            echo '<a href="javascript:toggleBidBox(\'' . $title . '\',\'' . $jobID . '\',\'' . $price . '\',\'' . $username . '\',\'' . $userID . '\',\'' . $obo . '\')">Place Bid</a>
                  <div id="toggle">
                    <h2>Message to <span style="color:#ABE3FE;" id="recipientShow"></span><br />
                    Regarding task <span style="color:#ABE3FE;" id="subjectShow"></span></h2>
                    <form action="javascript:processReply();" name="replyForm" id="replyForm" method="POST">
                      <strong><span class="priceInput">$<input type="text" name="price" id="price" size="4" maxlength="5" onKeyPress="return numbersonly(this, event)"/>.00</span></strong>
        		  	  <textarea name="pmTextArea" id="pmTextArea" rows="8" style="width:98%;"></textarea><br />
					  <input type="hidden" name="pmJobId" id="pmJobId" />
					  <input type="hidden" name="pmRecId" id="pmRecId" />
					  <br />
					  <input name="replyBtn" id="replyBtn" type="button" onclick="javascript:processReply()" /> &nbsp;&nbsp;&nbsp; 
					  <span id="pmFormProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
					  <div id="pmStatus" class="privateStatus">&nbsp;</div><!--end pmStatus-->
      			    </form>
      			  </div>
      			  <div id="pmFinal"></div>';
          }
        }
  ?>
  <h3><?php echo isset($title) ? $title : ''; ?></h3>
  <p><?php echo isset($desc) ? $desc : ''; ?></p>
  <?php echo isset($price) ? "\$$price.00 $obo"  : ''; ?><br /><br />
  <?php echo isset($time) ? 'Expires: ' . $time : ''; ?><br /><br />
  <?php echo isset($cat) ? $cat : ''; ?><br /><br />
  <?php if (isset($postDate) && isset($username) && isset($userID)) {
           echo "Posted: $postDate by <a href=\"profile.php?id=$userID\">$username</a>";
  		}
  ?>
</body>
</html>