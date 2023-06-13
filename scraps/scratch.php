<?php
  //my bids
  //sent bids
  //accepted bids
  //my messages
  //sent messages
  //still need to add message read
  //add regarding to message with link to task
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
      $id = $logOptions_id;
  } else {
    header('location: index.php');
  }
  
  if (isset($_GET['id'])) {
    $subject = '';
    $message = '';
    $profileID = preg_replace('#[^0-9]#', '', $_GET['id']);
    
    $sqlGetUser = mysqli_query($link, "SELECT * FROM users WHERE id='$profileID' LIMIT 1");
    $sqlUserCount = mysqli_num_rows($sqlGetUser);
    
    if ($sqlUserCount > 0) {
      while($row = mysqli_fetch_array($sqlGetUser)) {
        $username = getUsername($profileID);
        $city = $row['city'];
        $state = $row['state'];
        $country = $row['country'];
        $score = $row['score'];
        $unixLog = strtotime($row['last_log_date']);
        $lastLog = date("D M j", $unixLog);
        
        $sqlGetReviews = mysqli_query($link, "SELECT * FROM reviews WHERE voted_on_id='$profileID' OR voter_id='$profileID'");
        $sqlReviewCount = mysqli_num_rows($sqlGetReviews);
         
      }//end while($row = mysqli_fetch_array($sqlGetUser)) 
    }//end if ($sqlUserCount > 0)
  } else /*if (isset($_POST['price']) && isset($id)) {
    $price = preg_replace('#[^0-9]#', '', $_POST['price']);
    $bidID = preg_replace('#[^0-9]#', '', $_POST['bidJobBidID']);
    $recID = preg_replace('#[^0-9]#', '', $_POST['bidRecId']);
    $listID = preg_replace('#[^0-9]#', '', $_POST['bidListId']);
    $flag = preg_replace('#[^arcon]#', '', $_POST['bidFlag']);
    $username = getUsername($id);
    
    if (isset($recID) && isset($jobID) && isset($flag) && isset($bidID)) {
      $sqlCheckJob = mysqli_query($link, "SELECT * FROM jobs WHERE id='$jobID' AND user_id='$recID' active='y' LIMIT 1");
      $sqlCheckCount = mysqli_num_rows($sqlCheckJob);
      
      if ($sqlCheckCount > 0) {
        while($row = mysqli_fetch_array($sqlCheckJob)) {
          //$origPrice = $row['price'];
          $title = $row['title'];
          $userID = $row['user_id'];
          $obo = $row['obo'];
          $street = $row['street'];
          $city = $row['city'];
          $state = $row['state'];
          $country = $row['country'];
          $info = $row['info'];
          $time = strtotime($row['time']);
          $nowTime = time();
          
          //get last job_bid 
          $sqlCheckBid = mysqli_query($link, "SELECT * FROM job_bids WHERE id='$bidID' AND from_id='$recID' AND active='y' LIMIT 1");
          $sqlCountBid = mysqli_num_rows($sqlCheckBid);
          
          if ($sqlCountBid > 0) {
            while ($row2 = mysqli_fetch_array($sqlCheckBid)) {
              $toID = $row2['to_id'];
              $fromID = $row2['from_id'];
              $price = $row2['price'];
              $acceptID = $row2['accepted_id'];
              $rejectID = $row2['rejected_id'];
              
              if ($time > $nowTime) {
                if ((strcmp(flag,'a') == 0) && ($acceptID == $id)) {
                  echo 'You\'ve already accepted this bid. You need to confirm it';
                  exit();
                } else if ((strcmp(flag,'a') == 0) && (is_numeric($acceptID))) {
                  echo 'This has already been accepted. It needs to be confirmed.';
                  exit();
                } else if ((strcmp(flag,'a') == 0) && (!is_numeric($acceptID)) && ($fromID == $recID)) {
                  $title = 'BID ACCEPTED: ' . $title;
                  $sqlAcceptBid = mysqli_query($link, "INSERT INTO job_bids (job_id, to_id, from_id, price, title, accepted_id, post_date) VALUES ('$jobID', '$fromID', '$id', '$price', '$title', '$id', now())");
                  echo 'You\'ve just accepted this bid. Once the other party confirms, the details will be sent to them.';
                  exit();
                } else if ((strcmp(flag,'con') == 0) && ($acceptID == $id)) {
                  echo 'You can\'t accept and confirm your own posting.';
                  exit();
                } else if ((strcmp(flag,'con') == 0) && ($acceptID != $fromID)) {
                  echo 'This hasn\'t been accepted by the bidder yet';
                  exit();
                } else if ((strcmp(flag,'con') == 0) && ($acceptID == $fromID)) {
                  $sqlConfirmBid = mysqli_query($link, "UPDATE job_bids SET confirmed_id='$id', active='n' WHERE id='$bidID' LIMIT 1");
                  //$sqlUpdateBids = mysqli_query($link, "UPDATE job_bids SET active='n' WHERE job_id='$jobID' AND active=y");
 
                  $subject = "BID CONFIRMED: $title";
                  $message = "$username has just confirmed <a href=\"task.php?id=$jobID\">$title</a>.<br /> The address is $street, $city, $state, $country.<br />
                              Additional info includes: $info <br />
                              Make sure to leave a <a href=\"review.php\">review</a> after the transaction has completed.";
                                
                  //send confirmation message to bid_sender 
                  $sqlSendMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$fromID', '$id', '$subject', '$message', now())");
                  //send same confirmation message to confirmation sender
                  $sqlConfMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ($id, 'system', '$subject', '$message', now())");
                  
                  echo 'You\'ve just confirmed this bid';
                  exit();
                } else if ((strcmp(flag,'c') == 0) && (is_numeric($acceptID))) {
                  echo 'This bid has already been accepted, you can\'t counteroffer';
                  exit();
                } else if ((strcmp(flag,'c') == 0) && !(is_numeric($acceptID))) {
                  $title = 'COUNTEROFFER: ' . $title;
                  
                  $sqlInsertBid = mysqli_query($link, "INSERT INTO job_bids (job_id, to_id, from_id, price, title, message, post_date) VALUES ('$jobID', '$recID', '$id', '$price', '$title', now())");
                  $returnString = 'Bid counteroffer sent.';
                  if ((strcmp($obo,'n') == 0) && ($id == $userID) && ($price != $origPrice)) {
                    $sqlUpdateJob = mysqli_query($link, "UPDATE jobs SET obo='y' WHERE id='$jobID' LIMIT 1");
                    $returnString .= ' Task updated to "or best offer".'; 
                  }
                  echo $returnString;
                  exit();
                } else if (strcmp(flag,'r') == 0) {
                  $sqlRejectBid = mysqli_query($link, "UPDATE job_bids SET rejected_id='$id', active='n' WHERE id='$bidID' LIMIT 1");
                  $sqlUpdateBids1 = mysqli_query($link, "UPDATE job_bids SET active='n' WHERE job_id='$jobID' AND from_id='$recID' AND active=y");
                  $sqlUpdateBids = mysqli_query($link, "UPDATE job_bids SET active='n' WHERE job_id='$jobID' AND to_id='$recID' AND active=y");
                  
                  $subject = 'REJECTED: ' . $title;
                  $message = "$username has just rejected your bid for <a href=\"task.php?id=$jobID\">$title</a>";
                  $sqlsendMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$recID', '$id', '$subject', '$message', now())"); 
                  echo 'You\'ve just rejected this bid.';
                  exit();
                }
              } else {
                echo 'This task has expired.';
                exit();
              }//end if ($time > $nowTime)
            }
          } else {
            echo 'No one has bid on this yet.';
            exit();
          }//end if ($sqlCountBid > 0)
          
        }//end while($row = mysqli_fetch_array($sqlCheckJob))
      } else {
        echo 'Couldn\'t locate. May be expired or already confirmed.';
        exit();
      } 
    } else {
      echo 'There appears to be some information missing.';
      exit();
    }//end if (isset($recID) && isset($jobID) && isset($flag))
  
  
  } else*/ if (isset($_POST['pmTextArea']) && isset($id)) {
    $recID = preg_replace('#[^0-9]#', '', $_POST['pmRecId']);
    $listID = preg_replace('#[^0-9]#', '', $_POST['pmListId']);
    $message = clean($_POST['bidTextArea']);
    
    if (isset($recID) && isset($listID)) {
      $sqlCheckList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listID' AND active='y' LIMIT 1");
      $sqlCheckCount = mysqli_num_rows($sqlCheckList);
      
      if ($sqlCheckCount > 0) {
        while($row = mysqli_fetch_array($sqlCheckList)) {
          $subject = 'QUERY: ' . $row['title'];
          $userID = $row['user_id'];
          $time = strtotime($row['time']);
          $nowTime = time();
          $returnString;
          
          if ($time > $nowTime) {
            if (($message != '') && ($id != $userID)) {
              $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$recID', '$id', '$subject', '$message', now())");
              echo 'Message sent';
              exit();
            } else if ($message == '') {
              echo 'You have not set a message.';
              exit();
            } else if ($id == $userID) {
              echo "You can't send a message about your own auction silly!";
              exit();
            } else {
              echo 'There\'s been a gross miscalculation.'; 
              exit();
            }
          } else {
            echo 'This auction has expired.';
            exit();
          }//end if ($time > $nowTime)
        }//end while($row = mysqli_fetch_array($sqlCheckList))
      }//end if ($sqlCheckCount > 0)
    }//end if (isset($recID) && isset($listID))
  } 

?>

<!DOCTYPE html>
<html>
<head>
  <style>
    .msgDefault {font-weight:bold;cursor:pointer;}
	.msgRead {font-weight:100;color:#666;cursor:pointer;}
	.grey_color {color: #666666;font-size: 11px;}
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      $(".hiddenDiv").hide();
      $("#bidToggle").hide();
      $("#toggle").hide();
	  $(".toggle").click(function () { 
        if ($(this).next().is(":hidden")) {
          $(".hiddenDiv").hide();
    	  $(this).next().slideDown("fast"); 
  	  	} else { 
    	  $(this).next().hide();
    	  $('#bidToggle').hide();
  	  	} 
	  }); //end toggle
    }); //end ready
    
    function markAsRead(msgID) {
      $.post("profile.php",{ messageID:msgID } ,
      function(data) {
        $('#subj_line_'+msgID).addClass('msgRead');
      });
    }
    
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
    
    function toggleReplyBox(subject, jobID, recUsername, recID) {
      $('#subjectShow').text(subject);
      $('#recipientShow').text(recUsername);
      document.replyForm.pmJobId.value = jobID;
      document.replyForm.pmRecId.value = recID;
      document.replyForm.replyBtn.value = "Send message to "+recUsername;
      if ($('#toggle').is(":hidden") && $('#bidToggle').is(':hidden')) {
        $('#toggle').fadeIn(1000);
        $('#pmFormProcessGif').hide();
      } else {
        $('#toggle').hide();
      }
    }
    
    function toggleBidBox(subject, bidID, jobID, recID, price, flag) {
      if (flag == 'a') {
        $("#bidSubjectShow").text('Accepting bid for: '+subject);
        $('#price').attr('readonly', true);
        $('.editable').text('non-editable');
      } else if (flag == 'r') {
        $("#bidSubjectShow").text('Rejecting bid for: '+subject);
        $('#price').attr('readonly', true);
        $('.editable').text('non-editable');
      } else if (flag == 'c') {
        $("#bidSubjectShow").text('CounterOffer for: '+subject);
        $('#price').attr('readonly', false);
        $('.editable').text('editable');
      } else if (flag == 'con') {
        $("#bidSubjectShow").text('Confirming: '+subject);
        $('#price').attr('readonly', true);
        $('.editable').text('non-editable');
      } else {
        $("#bidSubjectShow").text(subject);
        $('#price').attr('readonly', true);
        $('.editable').text('non-editable');
      }
      document.bidForm.price.value = price;
      document.bidForm.bidJobBidID.value = bidID;
      document.bidForm.bidJobId.value = jobID;
      document.bidForm.bidRecId.value = recID;
      document.bidForm.bidFlag.value = flag;
      if ($('#bidToggle').is(":hidden")) {
        $('#bidToggle').fadeIn(1000);
        $('#bidProcessGif').hide();
      } else {
        $('#bidToggle').hide();
      }
    }
     
     function processReply() {
       var formData = $('#replyForm').serialize();
       var url = "profile.php";
       if ($("#pmTextArea").val() == "") {
         $("#pmStatus").text("Please type in a price.").show().fadeOut(6000);
       } else {
         $("#pmFormProcessGif").show();
         $.post(url, formData,  
         function(data) {
           document.pmForm.pmTextArea.value = "";
           $("#pmFormProcessGif").hide();
           $("#toggle").slideUp();
           $("#pmFinal").html("&nbsp; &nbsp;"+data).show();
         });
       }
     }
     
     function bidHandler(jobID, recID, price, accRej) {
       $.post("profile.php",{ job:jobID, to:recID, price:price, arFlag:accRej } ,
       function(data) {
         document.bidForm.bidTextArea.value = "";
         $("#bidToggle").slideUp();
         $("#bidFinal").html("&nbsp; &nbsp;"+data).show();
       });
     }
  </script>
</head>
<body>
  <?php if (isset($id) && isset($profileID)) : ?>
    <div id="profile_info">
      <table class="profile_table" cellpadding="10">
        <tr>
          <td class="table_fields">
            <?php echo isset($username) ? 'User: ' . $username : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($city) && isset($state) && isset($country) ? $city != '' && $state != '' ? 'Location: ' . $city . ' ' . $state . ' ' . $country : '' : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($score) ? 'Score: ' . $score : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($sqlReviewCount) ? "Reviews: <a href=\"reviews.php?id=$profileID\">$sqlReviewCount Reviews</a>" : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($lastLog) ? 'Last Login: ' . $lastLog : ''; ?>
          </td>
        </tr>
      </table>
    </div><!--end profile_info-->
    <?php if ($id == $profileID) : ?>
      <div id="profile_edit">
        <a href="edit.php?id=<?php echo $id; ?>">Edit Profile</a>
      </div><!--end profile_edit-->
      <div id="bidToggle">
    	<h2><span style="color:#ABE3FE;" id="bidSubjectShow"></span></h2><br />
    	<form action="javascript:processBid();" name="bidForm" id="bidForm" method="POST">
      	  <strong>$<input type="text" name="price" id="price" size="4" maxlength="5" onKeyPress="return numbersonly(this, event)"/>.00</strong>&nbsp;&nbsp;<span class="editable grey_color"></span>
      	  <input type="hidden" name="bidJobBidId" id="bidJobBidId" /> 
	  	  <input type="hidden" name="bidJobId" id="bidJobId" />
	  	  <input type="hidden" name="bidRecId" id="bidRecId" />
	  	  <input type="hidden" name="bidFlag"  id="bidFlag"  /><br />
	  	  <input name="replyBtn" id="replyBtn" type="button" value="Confirm" onclick="javascript:processBid()" /> &nbsp;&nbsp;&nbsp; 
	  	  <span id="bidProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
	      <div id="bidStatus">&nbsp;</div><!--end pmStatus-->
        </form>
    </div><!--end bidToggle-->
    <div id="toggle">
      <h2>Message to <span style="color:#ABE3FE;" id="recipientShow"></span><br />
    	Re <span style="color:#ABE3FE;" id="subjectShow"></span></h2>
    	<form action="javascript:processReply();" name="replyForm" id="replyForm" method="POST">
          <textarea name="pmTextArea" id="pmTextArea" rows="8" style="width:98%;"></textarea><br />
	  	  <input type="hidden" name="pmJobId" id="pmJobId" />
	  	  <input type="hidden" name="pmRecId" id="pmRecId" /><br />
	  	  <input name="replyBtn" id="replyBtn" type="button" onclick="javascript:processReply()" /> &nbsp;&nbsp;&nbsp; 
	  	  <span id="pmFormProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
	      <div id="pmStatus">&nbsp;</div><!--end pmStatus-->
        </form>
    </div><!--end toggle-->
    <div id="pmFinal"></div>
      <br /><br />
      <table class="bidTable" border="1">
        <tr> 
          <th colspan="3">Received Bids</th>
        </tr>
        <tr>
          <th>From</th>
          <th>Subject</th>
          <th>Date</th>
        </tr>
        <?php 
          $sqlGetBids = mysqli_query($link, "SELECT * FROM job_bids WHERE to_id='$id' AND active='y' ORDER BY id DESC LIMIT 100");
        
          while ($row2 = mysqli_fetch_array($sqlGetBids)) :
            $date = strftime("%b %d, %Y", strtotime($row2['post_date']));
            if($row2['recipient_opened'] == "y") {
      		  $textWeight = 'msgDefault';
    	    } else {
      		  $textWeight = 'msgRead';
    	    }
    	    $bidID = $row2['id'];
    	    $bidJobID = $row2['job_id'];
    	    $bidderID = $row2['from_id'];
    	    $bidPrice = $row2['price'];
    	    $accept = $row2['accepted_id'];
    	    $reject = $row2['rejected_id'];
    	    $confirm = $row2['confirmed_id'];
    	    $bidderName = getUsername($bidderID);
    	    $subject = htmlkarakter($row2['title']);
    	    $subject = str_replace(array("<", ">", "'", "\""), array('','','',''), $subject);
    	    $jobTime = '';
    	    $origPrice = '';
    	    $origUserID = '';
    	    $display = '';
    	    $nowTime = time();
    	    
    	    //make sure job hasn't expired yet; if it has, set active to n
    	    $sqlCheckTime = mysqli_query($link, "SELECT time, price, user_id FROM jobs WHERE id='$bidJobID' LIMIT 1");
    	    while ($row3 = mysqli_fetch_array($sqlCheckTime)) { $jobTime = strtotime($row3['time']); $origPrice = $row3['price']; $origUserID = $row3['user_id']; }
    	    
    	    //new bid coming in
    	    if (!is_numeric($accept) && !is_numeric($confirm)) {
    	      $display = "<a href=\"javascript:toggleBidBox('$subject', '$bidID', '$bidJobID', '$bidderID', '$bidPrice', 'a')\">ACCEPT</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:toggleBidBox('$subject', '$bidID', '$bidJobID', '$bidderID', '$bidPrice', 'r')\">REJECT</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:toggleBidBox('$subject', '$bidID', '$bidJobID', '$bidderID', '$bidPrice', 'c')\">COUNTEROFFER</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:toggleReplyBox('$subject', '$bidJobID', '$bidderName', '$bidderID')\">MESSAGE</a><br />";
    	    } else if (is_numeric($accept) && !is_numeric($confirm)) { //if other user accepted, then this user needs to confirm
    	      $display = "<a href=\"javascript:toggleBidBox('$subject', '$bidID', '$bidJobID', '$bidderID', '$bidPrice', 'con')\">CONFIRM</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:toggleBidBox('$subject', '$bidID', '$bidJobID', '$bidderID', '$bidPrice', 'r')\">REJECT</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:toggleReplyBox('$subject', '$bidID', '$bidJobID', '$bidderName', '$bidderID')\">MESSAGE</a><br />";
    	    } else if (is_numeric($accept) && is_numeric($confirm)) { //this job has already been accepted and confirmed, skip it
    	      continue;
    	    }
    	    if ($nowTime < $jobTime) :
        ?>
        
        <tr>
          <td class="bidTable_field"> 
            <a href="profile.php?id=<?php echo $bidderID; ?>"><?php echo $bidderName; ?></a>
          </td>
          <td class="bidTable_field">
            <span class="toggle">
              <a class="<?php echo $textWeight; ?>" id="subj_line_<?php echo $bidID; ?>" onclick="markAsRead(<?php echo $bidID; ?>)"><?php echo $subject ?></a>
            </span>
            <div class="hiddenDiv"><br />
              Posted Price: &nbsp;$<?php echo $origPrice ?>.00<br />
              Bid Price: &nbsp;$<?php echo $bidPrice ?>.00<br /><br />
              <?php echo $message; ?><br /><br />
              <span class="grey_color">Once accepted, you can submit a review of the transaction</span>
              <br /><br /><?php echo $display; ?>
            </div>
          </td>
          <td class="bidTable_field">
            <span style="font-size:10px;"><?php echo $date; ?></span>
          </td>
        </tr>
          <?php else : 
                  $sqlUpdateBids = mysqli_query($link, "UPDATE job_bids SET active='n' WHERE job_id='$bidJobID'"); 
                endif;
          ?>
        <?php endwhile; ?>
      </table>
    <?php else : ?>
      <div id="pm_form">
        <form action="profile.php" method="post" id="pmForm">
          Subject<br />
          <input type="text" id="subject" value="<?php echo isset($subject) ? $subject : ''; ?>" name="subject" size="60" /><br /><br />
          Message<br />
          <textarea name="message" cols="45" rows="5" id="message"><?php echo isset($message) ? $message : ''; ?></textarea><br /><br />
          <input type="hidden" name="profileID" value="<?php echo $profileID; ?>" />
          <input name="myButton" type="submit" id="myButton" value="Submit" />
        </form>
      </div>
    <?php endif; ?>
  <?php endif; ?> 
</body>
</html>