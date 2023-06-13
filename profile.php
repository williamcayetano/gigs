<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
      $id = $logOptions_id;
  } else {
    header('location: login.php');
  }
  
  if (isset($_GET['id'])) {
    //$subject = '';
    //$message = '';
    $profileID = preg_replace('#[^0-9]#', '', $_GET['id']);
    
    $sqlGetUser = mysqli_query($link, "SELECT * FROM users WHERE id='$profileID' AND active='y' LIMIT 1");
    $sqlUserCount = mysqli_num_rows($sqlGetUser);
    
    if ($sqlUserCount > 0) {
      while($row = mysqli_fetch_array($sqlGetUser)) {
        $username = getUsername($profileID);
        $city = $row['city'];
        $state = $row['country_state'];
        $region = $row['region'];
        $unixLog = strtotime($row['last_log_date']);
        $lastLog = date("D M j", $unixLog);
        
        //get profile score
        $sqlGetMyRev = mysqli_query($link, "SELECT * FROM reviews WHERE voted_on_id='$profileID' AND active='y'");
        $sqlMyRevCount = mysqli_num_rows($sqlGetMyRev);
        $score = 0;
        
        if ($sqlMyRevCount > 0) {
          while ($row = mysqli_fetch_array($sqlGetMyRev)) {
            $score += $row['score'];
          }
          //get average
          $score = $score / $sqlMyRevCount;
        }
         
      }//end while($row = mysqli_fetch_array($sqlGetUser)) 
    } else {
      header('location: index.php');
    }
  } else if (isset($_POST['pmTextArea']) && isset($id)) {
    $subject = clean($_POST['pmSubject']);
    $recID = preg_replace('#[^0-9]#', '', $_POST['pmRecId']);
    $message = clean($_POST['pmTextArea']);
    
    if ($recID == '') {
      echo '<span class="red">ERROR:</span> There doesn\'t appear to be a recipient.';
      exit();
    } else if ($subject == '') {
      echo '<span class="red">ERROR:</span> You need to include a subject.';
      exit();
    } else if ($message == '') {
      echo '<span class="red">ERROR:</span> You haven\'t written a message.';
      exit();
    } else { 
      $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$recID', '$id', '$subject', '$message', now())");
      echo 'Message sent';
      exit();
    }
  } else if (isset($_POST['revTextArea']) && isset($id)) {
    $score = preg_replace('#[^1-5]#', '', $_POST['score']);
    $revText = $_POST['revTextArea'];
    $revListID = preg_replace('#[^0-9]#', '', $_POST['revListId']);
    $recID = preg_replace('#[^0-9]#', '', $_POST['revRecId']);
    
    if ((!$score) || (!$revText) || (!$revListID) || (!$recID) || (!is_numeric($score)) || ($score == 0) ||
        ($score > 5)) {
     $msg = '<span class="red">ERROR:</span> You did not submit the following required information:<br /><br />';   
     
     if (!$score) {
        $msg .= '<span class="red">*</span> Score<br />';
     } 
     if (!$revText) {
        $msg .= '<span class="red">*</span> Comment<br />';
     }
     if (!$revListID) {
       $msg .= '<span class="red">*</span> Listing ID<br />';
     }
     if (!$recID) {
       $msg .= '<span class="red">*</span> Who this review is for<br />';
     }
     if (!is_numeric($score)) {
       $msg .= '<span class="red">*</span> This score is not numeric<br />';
     }
     if ($score == 0 || $score > 5) {
       $msg .= '<span class="red">*</span> Your score needs to be between 1 and 5<br />';
     }
     echo $msg;
     exit();
    } else {
      $sqlCheckList = mysqli_query($link, "SELECT * FROM  listings WHERE id='$revListID' LIMIT 1");
      $sqlListCount = mysqli_num_rows($sqlCheckList);
      
      if ($sqlListCount > 0) {
        while ($row = mysqli_fetch_array($sqlCheckList)) {
          $winID = $row['winner_id'];
          $userID = $row['user_id'];
          $time = strtotime($row['time']);
          $title = 'REVIEW: ' . $row['title'];
            //try to get date 7 days into the future so pepople don't have all the time in the world to rate
          $future7Days = $time + (86400 * 7);
          $nowTime = time();
          
          if(($winID == $id) || ($userID == $id)) {
            if ($nowTime < $future7Days) {
              $sqlCheckRate = mysqli_query($link, "SELECT * FROM reviews WHERE voter_id='$id' AND list_id='$revListID'");
              $sqlCountRate = mysqli_num_rows($sqlCheckRate);
              
              if ($sqlCountRate > 0) {
                echo 'You\'ve already reviewed this transaction';
                exit();
              } else {
                $sqlInsertReview = mysqli_query($link, "INSERT INTO reviews (list_id, voter_id, voted_on_id, score, title, comment, post_date) VALUES ('$revListID', '$id', '$recID', '$score', '$title', '$revText', now())");
                echo 'Your review will be posted 7 days after the end of the auction, as well as the other member\'s review in this transaction.';
                exit();
              }//end if ($sqlCountRate > 0)
            } else {
              echo '<span class="red">ERROR:</span> It is past the 7 day limit to review your transaction with this member.<br /><br />';
              exit();
            }//end if ($nowTime < $future7Days)
          } else {
            echo '<span class="red">ERROR:</span> You are neither the winner or the starter of this auction.<br /><br />';
            exit();
          }//end if(($winID == $id) || ($userID == $id))
        }//end while ($row = mysqli_fetch_array($sqlCheckList)
      }//end if ($sqlCheckList > 0)
    }//end if ((!$score) || (!revTextArea) etc.
  } else if (isset($_POST['cancelBid']) && isset($id)) {
    $bidID = preg_replace('#[^0-9]#', '', $_POST['cancelBid']);
    
    $sqlCheckBid = mysqli_query($link, "SELECT * FROM bids WHERE id='$bidID'");
    $sqlCountBid = mysqli_num_rows($sqlCheckBid);
    
    if ($sqlCountBid > 0) {
      while($row = mysqli_fetch_array($sqlCheckBid)) {
        $listingID = $row['listing_id'];
        $listUserID = $row['listing_owner_id'];
        $listUsername = getUsername($listUserID);
        $bidUserID = $row['bid_user_id'];
        $bidUsername = getUsername($bidUserID);
        $price = $row['price'];
        $recDelete = $row['recipient_delete'];
        $sendDelete = $row['sender_delete'];
        
        if (($listUserID == $id) || ($bidUserID == $id)) {
          $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listingID' LIMIT 1");
          $sqlListCount = mysqli_num_rows($sqlGetList);
            
          if ($sqlListCount > 0) {
            while ($row2 = mysqli_fetch_array($sqlGetList)) {
              $title = $row2['title'];
              $time = strtotime($row2['time']);
              $nowTime = time();
              if ($listUserID == $id && strcmp($recDelete,'n') == 0) {
                if ($nowTime < $time) {
                  $sqlUpdateBid = mysqli_query($link, "UPDATE bids SET recipient_delete='y', active='n' WHERE id='$bidID' LIMIT 1");
                  $message = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $listUserID . '">' . $listUsername . '</a> has cancelled your bid of $' . $price . '.00 on gig/good <a href="listing.php?id=' . $listingID . '">' . $title . '</a>';
                  $title = 'BID REJECTED: ' . $row2['title'];
                  $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$bidUserID', 0, '$title', '$message', now())");
                } else {
                  $sqlUpdateBid = mysqli_query($link, "UPDATE bids SET recipient_delete='y', active='n' WHERE id='$bidID' LIMIT 1");
                }
              } else if ($bidUserID == $id && strcmp($sendDelete,'n') == 0) {
                if ($nowTime < $time) {
                  $sqlUpdateBid = mysqli_query($link, "UPDATE bids SET sender_delete='y', active='n' WHERE id='$bidID' LIMIT 1");
                  $message = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $bidUserID . '">' . $bidUsername . '</a> has cancelled their bid of $' . $price . '.00 on gig/good <a href="listing.php?id=' . $listingID . '">' . $title . '</a>';
                  $title = 'BID CANCELED: ' . $row2['title'];
                  $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$listUserID', 0, '$title', '$message', now())");
                } else {
                  $sqlUpdateBid = mysqli_query($link, "UPDATE bids SET sender_delete='y', active='n' WHERE id='$bidID' LIMIT 1");
                }
              }//end if ($listUserID == $id && strcmp($recDelete,'n') == 0)
            }//end while ($row2 = mysqli_fetch_array($sqlGetList)
          }//end if ($sqlListCount > 0)
        }//end if (($listUserID == $id) || ($bidUserID == $id))
      }//end while($row = mysqli_fetch_array($sqlCheckBid))
    }//if ($sqlCountBid > 0)
  } else if (isset($_POST['delMessage'])) {
    $messID = preg_replace('#[^0-9]#', '', $_POST['delMessage']);
    
    $sqlGetMess = mysqli_query($link, "SELECT * FROM messages WHERE id='$messID' LIMIT 1");
    $sqlMessCount = mysqli_num_rows($sqlGetMess);
    
    if ($sqlMessCount > 0) {
      while ($row = mysqli_fetch_array($sqlGetMess)) {
        $toID = $row['to_id'];
        $fromID = $row['from_id'];
        $recDel = $row['recipient_delete'];
        $sendDel = $row['sender_delete'];
        
        if (($id == $toID) && (strcmp($recDel,'n') == 0)) {
          $sqlUpdateMess = mysqli_query($link, "UPDATE messages SET recipient_delete='y' WHERE id='$messID'");
        } else if (($id == $fromID) && (strcmp($sendDel,'n') == 0)) {
          $sqlUpdateMess = mysqli_query($link, "UPDATE messages SET sender_delete='y' WHERE id='$messID'");
        }//end if (($id == $toID), etc.
      }//end while ($row = mysqli_fetch_array($sqlGetMess))
    }//if ($sqlMessCount > 0)
  } else {
    header("location: index.php");
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
    .msgDefault {font-weight:bold;cursor:pointer;}
	.msgRead {font-weight:100;color:#666;cursor:pointer;}
	.grey_color {color: #666666;font-size: 11px;}
	td,th {text-align: center;}
	.red {color: red;}
	a:link {text-decoration:none;}
	body { 
      background: #f0f0f0; 
    }
    
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
    
    #profile_info {
      clear: both;
      
    }
    
    #profile_info a {
      background: #348075;
      padding: 2px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    .profile_table {
      margin: auto;
    }
    
    #content, #profile_info {
      width: 800px;
      margin: auto;
      padding: 40px;
      background-color: white;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }
    
    #profile_info {
      padding: 5px;
      width: 900px;
    }
    
    .bidTable {
      margin-bottom: 40px;
    }
    
    .bidTable td {
      overflow: hidden;
      table-layout:fixed;
      white-space:nowrap;
    }
    
      .bidTable_field {
      background: #348075;
      padding: 6px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
    }
    
    .bidTable_field a {
      color: white;
      display: block;
      width: 100%;
      height: 100%;
      font-weight: bold;
    }
    
    .bidTable_field:hover {
      background: #287368;
      cursor: pointer;
    }
    
    .bidTable_field a.msgRead {font-weight:100;color:#aaa;cursor:pointer;}
    
    .toggle {
      display: block;
      width: 100%;
      height: 100%;
    }
    
    .hiddenDiv {
      background-color: white;
      color: black;
    }
    
    .hiddenDiv a {
      background: #348075;
      padding: 2px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
      margin: auto;
      text-align: center;
    }
    
    #hidden_name { width: 10%; }
    #hidden_title { width: 90%; }
    #hidden_review { width: 12%; }
    
    #reviewToggle {
      clear: both;
      width: 400px;
      background-color: #b6b6b6;
      padding: 20px;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }
    
    #revBtn, #replyBtn {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #revBtn:hover, #replyBtn:hover {
      background: #287368;
      cursor: pointer;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      $(".hiddenDiv").hide();
      $('#reviewToggle').hide();
      $("#pmFormProcessGif2").hide();
      $('.toggle').css( 'cursor', 'pointer' );
      $('.toggle').click(function () { 
        if ($(this).next().is(":hidden")) {
          //console.log(this);
          $(".hiddenDiv").show();
    	  $(this).next().slideDown("fast"); 
  	  	} else { 
    	  $(this).next().hide();
      	  $('#reviewToggle').hide();
  	  	} 
	  }); //end toggle
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
	  } else if ((("12345").indexOf(keychar) > -1)) {
        return true;
	  } else if (dec && (keychar == ".")) {
   	    myfield.form.elements[dec].focus();
        return false;
      } else {
        return false;
      }
    }
    
    function toggleReviewBox(subject, listID, recUsername, recID) {
      $('#revSubjectShow').text(subject);
      $('#revRecipientShow').text(recUsername);
      document.reviewForm.revListId.value = listID;
      document.reviewForm.revRecId.value = recID;
      if ($('#reviewToggle').is(':hidden')) {
        $('#reviewToggle').fadeIn(1000);
        $('#revFormProcessGif').hide();
      } else {
        $('#reviewToggle').hide();
      }
    }
     
    function processReply() {
      var formData = $('#pmForm').serialize();
      var url = "profile.php";
      
      if ($('.pmSubject').val() == "") {
        $("#pmStatus2").text("Please type in a subject.").show().fadeOut(3000);
      } else if ($(".pmTextArea").val() == "") {
        $("#pmStatus2").text("Please type in a message.").show().fadeOut(3000);
      } else {
        $("#pmFormProcessGif2").show();
        $.post(url, formData,  function(data) {
          $('.pmSubject').val('');
          $('.pmTextArea').val('');
          //document.pmForm.pmTextArea.value = "";
          $("#pmFormProcessGif2").hide();
          $("#pmFinal2").html("&nbsp; &nbsp;"+data).show();
        });
      }
    }
    
    function processReview() {
      var formData = $('#reviewForm').serialize();
      var url = "profile.php";
      if ($("#score").val() == "") {
        $("#revStatus").text("Please type in a score.").show().fadeOut(3000);
      } else if ($("#revTextArea").val() == "") {
        $("#revStatus").text("Please type in a message.").show().fadeOut(3000);
      } else {
        $("#revFormProcessGif").show();
        $.post(url, formData,  
        function(data) {
          console.log(data);
          document.reviewForm.revTextArea.value = "";
          $("#revFormProcessGif").hide();
          $("#reviewToggle").slideUp();
          $("#pmFinal").html("&nbsp; &nbsp;"+data).show();
        });
      }
    }
    
    function cancelBid(bidID) {
      var cancelID = '.cancel_'+bidID; 
      $.post("profile.php",{ cancelBid:bidID } , function(data) {
        $(cancelID).html('canceled!').show();
      });
    }
    
    function delMessage(messID) {
      var messageID = '.delete_'+messID;
      $.post("profile.php",{ delMessage:messID } , function(data) {
        $(messageID).html('deleted!').show();
      });
    }
  </script>
</head>
<body>
  <div id="breadcrumb"><a href="index.php">home</a></div>
  <?php echo ($id == $profileID) ? '<div id="log_options"><a href="editProfile.php">edit profile</a> &nbsp;|&nbsp;
    <a href="logout.php">log out</a></div>' : '<div id="log_options"><a href="logout.php">log out</a></div>'; ?>
  <?php if (isset($id) && isset($profileID)) : ?>
    <div id="profile_info">
      <table class="profile_table" cellpadding="10">
        <tr>
          <td class="table_fields">
            <?php echo isset($username) ? '<strong>user:</strong> ' . strtolower($username) : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($city) && isset($state) && isset($region) ? $city != '' && $state != '' ? '<strong>location:</strong> ' . strtolower($city) . ', ' . strtolower($state) . ' &nbsp;&nbsp;' . strtolower($region) : '' : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($score) ? '<strong>score:</strong> ' . strtolower($score) : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($sqlMyRevCount) ? "<strong>reviews:</strong> <a href=\"reviews.php?id=$profileID\">$sqlMyRevCount review(s)</a>" : ''; ?>
          </td>
          <td class="table_fields">
            <?php echo isset($lastLog) ? '<strong>last login:</strong> ' . strtolower($lastLog) : ''; ?>
          </td>
        </tr>
      </table>
    </div><!--end profile_info-->
    <?php if ($id == $profileID) : ?>
    <div id="reviewToggle">
      <h2>reviewing transaction with: <span style="color:white;" id="revRecipientShow"></span><br />
        for: <span style="color:white;" id="revSubjectShow"></span></h2>
        <form action="javascript:processReview();" name="reviewForm" id="reviewForm" method="POST">
          <strong>enter a number 1-5, with five being the greatest amount of satisfaction in this transaction with this member: <input type="text" name="score" id="score" size="1" maxlength="1" onKeyPress="return numbersonly(this, event)"/></strong><br />
          leave a comment about your experience with this member: (required)<br />
          <textarea name="revTextArea" id="revTextArea" rows="8" style="width:98%;"></textarea><br />
          <input type="hidden" name="revListId" id="revListId" />
	  	  <input type="hidden" name="revRecId" id="revRecId" /><br />
	  	  <input name="revBtn" id="revBtn" type="button" value="Submit" onclick="javascript:processReview()" /> &nbsp;&nbsp;&nbsp; 
	  	  <span id="revFormProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
	      <div id="revStatus">&nbsp;</div><!--end pmStatus-->
        </form>
    </div>
    <div id="pmFinal"></div><br />
    <h2>profile dashboard</h2>
    <div id="content">
      <table class="bidTable" width="800">
        <tr> 
          <th colspan="2">auctions started i need to rate/review</th>
        </tr>
        <tr>
          <th>member</th>
          <th>gig/good</th>
        </tr>
      <?php
        
        $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE user_id='$id' AND winner_id > 0 AND time < now()");
        $sqlListCount = mysqli_num_rows($sqlGetList);
        
        if ($sqlListCount > 0) :
          while ($row = mysqli_fetch_array($sqlGetList)) :
            $listID = $row['id'];
            $winner = $row['winner_id'];
            $winnerName = getUsername($winner);
            $title = $row['title'];
            $time = strtotime($row['time']);
            //try to get date 7 days into the future so people don't have all the time in the world to rate
            $future7Days = $time + (86400 * 7);
            $nowTime = time();
            
            $sqlGetRev = mysqli_query($link, "SELECT * FROM reviews WHERE list_id='$listID' AND voter_id='$id'");
            $sqlRevCount = mysqli_num_rows($sqlGetRev);
            if (($sqlRevCount == 0) && ($nowTime < $future7Days)) :
      ?>
        <tr>
          <td class="bidTable_field" width="15%"> 
            <a href="profile.php?id=<?php echo $winner; ?>"><?php echo strtolower($winnerName); ?></a>
          </td>
          <td class="bidTable_field">
            <span class="toggle">
              <strong><?php echo strtolower($title); ?><strong>
            </span>
            <div class="hiddenDiv"><br />
              <strong>review your transaction with:</strong> <a href="profile.php?id=<?php echo $winner; ?>" id="hidden_name"><?php echo strtolower($winnerName); ?></a>
              <strong>for gig/good:</strong> &nbsp;<a href="listing.php?id=<?php echo $listID; ?>" id="hidden_title"><?php echo strtolower($title); ?></a>
              <br /><br /><a href="javascript:toggleReviewBox('<?php echo strtolower($title); ?>', '<?php echo $listID; ?>', '<?php echo $winnerName; ?>', '<?php echo $winner; ?>')" id="hidden_review">REVIEW</a><br />
            </div>
          </td>  
         </tr>
      <?php
        endif;
        endwhile;
        endif;
      ?>
      </table>
	  <hr />
      <table class="bidTable" width="800">
        <tr> 
          <th colspan="2">auctions won i need to rate/review</th>
        </tr>
        <tr>
          <th>member</th>
          <th>gig/good</th>
        </tr>  
      <?php
        
        $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE winner_id='$id' AND time < now()");
        $sqlListCount = mysqli_num_rows($sqlGetList);
        
        if ($sqlListCount > 0) :
          while ($row = mysqli_fetch_array($sqlGetList)) :
            $listID = $row['id'];
            $userID = $row['user_id'];
            $username = getUsername($userID);
            $title = $row['title'];
            $time = strtotime($row['time']);
            //try to get date 7 days into the future so people don't have all the time in the world to rate
            $future7Days = $time + (86400 * 7);
            $nowTime = time();
            
            $sqlGetRev = mysqli_query($link, "SELECT * FROM reviews WHERE list_id='$listID' and voter_id='$id'");
            $sqlRevCount = mysqli_num_rows($sqlGetRev);
            
            if (($sqlRevCount == 0) && ($nowTime < $future7Days)) :
      ?>
        <tr>
          <td class="bidTable_field" width="15%"> 
            <a href="profile.php?id=<?php echo $userID; ?>"><?php echo strtolower($username); ?></a>
          </td>
          <td class="bidTable_field bidTable_toggle">
            <span class="toggle">
              <strong><?php echo strtolower($title); ?></strong>
            </span>
            <div class="hiddenDiv"><br />
              <strong>review your transaction with:</strong> <a href="profile.php?id=<?php echo $userID; ?>" id="hidden_name"><?php echo strtolower($username); ?></a>
              <strong>for gig/good:</strong> &nbsp;<a href="listing.php?id=<?php echo $listID; ?>" id="hidden_title"><?php echo strtolower($title); ?></a>
              <br /><br /><a href="javascript:toggleReviewBox('<?php echo strtolower($title); ?>', '<?php echo $listID; ?>', '<?php echo $username; ?>', '<?php echo $userID; ?>')" id="hidden_review">REVIEW</a><br />
            </div>
          </td>  
         </tr>
      <?php
        endif;
        endwhile;
        endif;
      ?>
      </table>
      <hr />
      <table class="bidTable" width="800">
        <tr> 
          <th colspan="5">received bids</th>
        </tr>
        <tr>
          <th>from</th>
          <th>gig/good</th>
          <th>bid</th>
          <th>date</th>
          <th>remove</th>
        </tr>
      <?php
        $sqlGetBids = mysqli_query($link, "SELECT * FROM bids WHERE listing_owner_id='$id' AND active='y' ORDER BY id DESC LIMIT 100");
        $sqlBidsCount = mysqli_num_rows($sqlGetBids);
        if ($sqlBidsCount > 0) : 
          while ($row2 = mysqli_fetch_array($sqlGetBids)) :
            $date = strftime("%b %d, %Y", strtotime($row2['post_date']));
    	    $bidID = $row2['id'];
    	    $bidListID = $row2['listing_id'];
    	    $bidderID = $row2['bid_user_id'];
    	    $bidPrice = $row2['price'];
    	    $bidderName = getUsername($bidderID);
    	    $subject = htmlkarakter($row2['title']);
    	    $subject = str_replace(array("<", ">", "'", "\""), array('','','',''), $subject);
    	    $listTime = '';
    	    $nowTime = time();
    	    
    	    //make sure job hasn't expired yet; if it has, set active to n
    	    $sqlCheckTime = mysqli_query($link, "SELECT time FROM listings WHERE id='$bidListID' LIMIT 1");
    	    while ($row3 = mysqli_fetch_array($sqlCheckTime)) { $listTime = strtotime($row3['time']); }
    	    //have bids stick around for 7 days in case deals follow through
    	    $future7Days = $listTime + (86400 * 7);
    	    
    	    if ($nowTime < $future7Days) :
        ?>
        
        <tr>
          <td class="bidTable_field" width="15%"> 
            <a href="profile.php?id=<?php echo $bidderID; ?>"><?php echo strtolower($bidderName); ?></a>
          </td>
          <td class="bidTable_field">
            <a href="listing.php?id=<?php echo $bidListID; ?>"><?php echo strtolower($subject); ?></a>
          </td>
          <td width="10%">
            $<?php echo $bidPrice ?>.00
          </td>
          <td width="10%">
            <span style="font-size:10px;"><?php echo strtolower($date); ?></span>
          </td>
          <td class="bidTable_field cancel_<?php echo $bidID; ?>" width="10%">
            <a href="javascript:cancelBid('<?php echo $bidID; ?>');">cancel this</a>
          </td>
        </tr>
        <?php
              endif;
            endwhile; 
          endif; ?>
      </table>
      <hr />
	  <table class="bidTable" width="800">
        <tr> 
          <th colspan="5">sent bids</th>
        </tr>
        <tr>
          <th>auctioneer</th>
          <th>gig/good</th>
          <th>bid</th>
          <th>date</th>
          <th>remove</th>
        </tr>
      <?php
        $sqlGetBids = mysqli_query($link, "SELECT * FROM bids WHERE bid_user_id='$id' AND active='y' ORDER BY id DESC LIMIT 100");
        $sqlBidsCount = mysqli_num_rows($sqlGetBids);
        
        if ($sqlBidsCount > 0) :
          while ($row2 = mysqli_fetch_array($sqlGetBids)) :
            $date = strftime("%b %d, %Y", strtotime($row2['post_date']));
    	    $bidID = $row2['id'];
    	    $bidListID = $row2['listing_id'];
    	    $auctioneerID = $row2['listing_owner_id'];
    	    $bidPrice = $row2['price'];
    	    $auctioneerName = getUsername($auctioneerID);
    	    $subject = htmlkarakter($row2['title']);
    	    $subject = str_replace(array("<", ">", "'", "\""), array('','','',''), $subject);
    	    $listTime = '';
    	    $nowTime = time();
    	    
    	    //make sure job hasn't expired yet; if it has, set active to n
    	    $sqlCheckTime = mysqli_query($link, "SELECT time FROM listings WHERE id='$bidListID' LIMIT 1");
    	    while ($row3 = mysqli_fetch_array($sqlCheckTime)) { $listTime = strtotime($row3['time']); }
    	    //have bids stick around for 7 days in case deals follow through
    	    $future7Days = $listTime + (86400 * 7);
    	    
    	    if ($nowTime < $future7Days) :
        ?>
        
        <tr>
          <td class="bidTable_field" width="15%"> 
            <a href="profile.php?id=<?php echo $auctioneerID; ?>"><?php echo strtolower($auctioneerName); ?></a>
          </td>
          <td class="bidTable_field">
            <a href="listing.php?id=<?php echo $bidListID; ?>"><?php echo strtolower($subject); ?></a>
          </td>
          <td width="10%">
            $<?php echo $bidPrice ?>.00
          </td>
          <td width="10%">
            <span style="font-size:10px;"><?php echo strtolower($date); ?></span>
          </td>
          <td class="bidTable_field cancel_<?php echo $bidID; ?>" width="10%">
            <a href="javascript:cancelBid('<?php echo $bidID; ?>');">cancel this</a>
          </td>
        </tr>
        <?php
              endif;
            endwhile; 
          endif; ?>
      </table>
      <hr />
      <table class="bidTable" width="800">
        <tr> 
          <th colspan="4">received messages</th>
        </tr>
        <tr>
          <th>sender</th>
          <th>title</th>
          <th>date</th>
          <th>remove</th>
        </tr>
      <?php
        $sqlGetMess = mysqli_query($link, "SELECT * FROM messages WHERE to_id='$id' AND recipient_delete='n' ORDER BY id DESC LIMIT 100");
        $sqlMessCount = mysqli_num_rows($sqlGetMess);
        if ($sqlMessCount > 0) : 
          while ($row2 = mysqli_fetch_array($sqlGetMess)) :
            $date = strftime("%b %d, %Y", strtotime($row2['post_date']));
            if($row2['recipient_opened'] == "y") {
      		  $textWeight = 'msgRead';
    	    } else {
      		  $textWeight = 'msgDefault';
    	    }
    	    $messID = $row2['id'];
    	    $fromID = $row2['from_id'];
    	    $fromName = getUsername($fromID);
    	    $subject = htmlkarakter($row2['subject']);
    	    $subject = str_replace(array("<", ">", "'", "\""), array('','','',''), $subject);
        ?>
        
        <tr>
          <td class="bidTable_field" width="15%"> 
            <?php echo ($fromID > 0) ? '<a href="profile.php?id=' . $fromID . '">' . strtolower($fromName) . '</a>' : ''; ?>
          </td>
          <td class="bidTable_field">
            <a class="<?php echo $textWeight; ?>" href="message.php?id=<?php echo $messID; ?>"><?php echo strtolower($subject); ?></a>
          </td>
          <td width="10%">
            <span style="font-size:10px;"><?php echo strtolower($date); ?></span>
          </td>
          <td class="bidTable_field" width="10%">
            <span class="delete_<?php echo $messID; ?>"><a href="javascript:delMessage('<?php echo $messID; ?>');">delete</a></span>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </table>
      <hr />
      <table class="bid_table" width="800">
        <tr> 
          <th colspan="4">sent messages</th>
        </tr>
        <tr>
          <th>receiver</th>
          <th>title</th>
          <th>date</th>
          <th>remove</th>
        </tr>
      <?php
        $sqlGetMess = mysqli_query($link, "SELECT * FROM messages WHERE from_id='$id' AND sender_delete='n' ORDER BY id DESC LIMIT 100");
        $sqlMessCount = mysqli_num_rows($sqlGetMess);
        if ($sqlMessCount > 0) :
          while ($row2 = mysqli_fetch_array($sqlGetMess)) :
            $date = strftime("%b %d, %Y", strtotime($row2['post_date']));
            if($row2['sender_opened'] == "y") {
      		  $textWeight = 'msgRead';
    	    } else {
      		  $textWeight = 'msgDefault';
    	    }
    	    $messID = $row2['id'];
    	    $toID = $row2['to_id'];
    	    $toName = getUsername($toID);
    	    $subject = htmlkarakter($row2['subject']);
    	    $subject = str_replace(array("<", ">", "'", "\""), array('','','',''), $subject);
        ?>
        
        <tr>
          <td class="bidTable_field" width="15%"> 
            <a href="profile.php?id=<?php echo $toID; ?>"><?php echo strtolower($toName); ?></a>
          </td>
          <td class="bidTable_field">
            <a class="<?php echo $textWeight; ?>" href="message.php?id=<?php echo $messID; ?>"><?php echo strtolower($subject); ?></a>
          </td>
          <td width="10%">
            <span style="font-size:10px;"><?php echo strtolower($date); ?></span>
          </td>
          <td class="bidTable_field"  width="11%">
            <span class="delete_<?php echo $messID; ?>"><a href="javascript:delMessage('<?php echo $messID; ?>');">delete</a></span>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </table>
      
    <?php else : ?>
      <div id="pm_form">
        <h3>send message <?php echo isset($username) ? ' to ' . strtolower($username) : '' ?></h3>
        <form action="javascript:processReply();" method="post" id="pmForm">
          <strong>subject</strong><br />
          <input type="text" name="pmSubject" class="pmSubject" value="<?php echo isset($subject) ? $subject : ''; ?>"  size="60" /><br /><br />
          <strong>message</strong><br />
          <textarea name="pmTextArea" cols="45" rows="5" class="pmTextArea"><?php echo isset($message) ? $message : ''; ?></textarea><br /><br />
          <input type="hidden" name="pmRecId" class="pmRecId" value="<?php echo $profileID; ?>" />
          <input name="replyBtn" id="replyBtn" type="button" value="Send message to <?php echo isset($username) ? $username : ''; ?>" onclick="javascript:processReply()" />
          <span id="pmFormProcessGif2"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
	      <div id="pmStatus2">&nbsp;</div><!--end pmStatus-->
        </form>
        <div id="pmFinal2"></div>
      </div>
    <?php endif; ?>
  <?php endif; ?> 
  </div><!--<div id="content">-->
</body>
</html>