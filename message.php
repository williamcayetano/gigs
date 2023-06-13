<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  } else {
    header("location: index.php");
  }
  
  if (isset($_GET['id']) && isset($id)) {
    $messID = preg_replace('#[^0-9]#', '', $_GET['id']);
    $sqlGetMess = mysqli_query($link, "SELECT * FROM messages WHERE id='$messID' LIMIT 1");
    $sqlMessCount = mysqli_num_rows($sqlGetMess);
    
    if ($sqlMessCount > 0) {
      while ($row = mysqli_fetch_array($sqlGetMess)) {
        $toID = $row['to_id'];
        $toName = getUsername($toID);
        $fromID = $row['from_id'];
        $fromName = getUsername($fromID);
        $subject = htmlkarakter($row['subject']);
        $message = htmlkarakter($row['message']);
        $recOpen = $row['recipient_opened'];
        $sendOpen = $row['sender_opened'];
        $recDel = $row['recipient_delete'];
        $sendDel = $row['sender_delete'];
        
        if ($id == $toID) {
          if (strcmp($recDel,'y') == 0) {
            header('location: profile.php?id=' . $id);
          } else if (strcmp($recOpen,'n') == 0) {
            $sqlUpdateMess = mysqli_query($link, "UPDATE messages SET recipient_opened='y' WHERE id='$messID'");
          }
        } else if ($id == $fromID) {
          if (strcmp($sendDel,'y') == 0) {
            header('location: profile.php?id=' . $id);
          } else if (strcmp($sendOpen,'n') == 0) {
            $sqlUpdateMess = mysqli_query($link, "UPDATE messages SET sender_opened='y' WHERE id='$messID'");
          }
        } else {
          header('location: profile.php?id=' . $id);
        }
      }
    } else {
      header("location: index.php");
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
      echo 'message sent. click <a href="profile.php?id=' . $id . '">here</a> to return to your profile.';
      exit();
    }
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
          echo 'message deleted. click <a href="profile.php?id=' . $id . '">here</a> to return to your profile.';
          exit();
        } else if (($id == $fromID) && (strcmp($sendDel,'n') == 0)) {
          $sqlUpdateMess = mysqli_query($link, "UPDATE messages SET sender_delete='y' WHERE id='$messID'");
          echo 'message deleted. click <a href="profile.php?id=' . $id . '">here</a> to return to your profile.';
          exit();
        }//end if (($id == $toID), etc.
      }//end while ($row = mysqli_fetch_array($sqlGetMess))
    }//if ($sqlMessCount > 0)
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
      /*font-weight: bold;*/
    }
    
    a { text-decoration: none; }
    
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
    
    #toggle_buttons {
      clear: both;
    }
    
    #toggle_buttons a {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #toggle_buttons a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #content {
      clear: both;
      background-color:#FFFFFF;
      padding: 20px;
      width: 400px;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
      overflow: hidden;
      margin: auto;
    }
    
    #toggle {
      clear: both;
      width: 400px;
      background-color: #b6b6b6;
      padding: 20px;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
      margin-bottom:20px;
      /*overflow:hidden;*/
    }
    
     #title {
      clear: both;
      text-align: center;
      width: 900px;
      margin: auto;
    }
    
    #replyBtn {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #replyBtn:hover {
      background: #287368;
      cursor: pointer;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
  $(document).ready(function() {
    $("#toggle").hide();
  });
  
  function toggleReplyBox(subject, recUsername, recID) {
      $('#recipientShow').text(recUsername);
      if (!subject.match(/^RE:/)) {
        $subject = 'RE: '+subject;
      }
      document.replyForm.pmSubject.value = subject;
      document.replyForm.pmRecId.value = recID;
      document.replyForm.replyBtn.value = "send message to "+recUsername;
      if ($('#toggle').is(":hidden")) {
        $('#toggle').fadeIn(1000);
        $('#pmFormProcessGif').hide();
      } else {
        $('#toggle').hide();
      }
    }
    
    function processReply() {
      var formData = $('#replyForm').serialize();
      var url = "message.php";
      
      if ($('.pmSubject').val() == "") {
        $("#pmStatus").text("Please type in a subject.").show().fadeOut(3000);
      } else if ($(".pmTextArea").val() == "") {
        $("#pmStatus").text("Please type in a message.").show().fadeOut(3000);
      } else {
        $("#pmFormProcessGif").show();
        $.post(url, formData,  
          function(data) {
            if (data.match(/^message/)) {
              window.location = "profile.php?id="+<?php echo $id; ?>;
            }
          });
      }
    }
    
    function delMessage(messID) {
      var messageID = '.delete_'+messID;
      $.post("message.php",{ delMessage:messID } , function(data) {
        if (data.match(/^message/)) {
              window.location = "profile.php?id="+<?php echo $id; ?>;
        }
        //$("#pmFinal").html(data).show();
      });
    }
  </script>
</head>
<body> 
  <?php if (isset($toID)) : ?>
  <div id="breadcrumb"><a href="index.php">home</a></div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <div id="toggle">
    <h2>message to <span style="color:white;" id="recipientShow"></span></h2>
      <form action="javascript:processReply();" name="replyForm" id="replyForm" method="POST">
    	<h2>re <input type="text" name="pmSubject" id="pmSubject" size="60"/><h2>
        <textarea name="pmTextArea" id="pmTextArea" rows="8" style="width:98%;"></textarea><br />
	  	<input type="hidden" name="pmRecId" id="pmRecID" />
	  	<input name="replyBtn" id="replyBtn" type="button" onclick="javascript:processReply()" /> &nbsp;&nbsp;&nbsp; 
	  	<span id="pmFormProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
	    <div id="pmStatus"></div><!--end pmStatus-->
      </form>
    </div><!--end toggle-->
  <div id="pmFinal"></div>
  <div id="toggle_buttons">
    <a href="javascript:delMessage('<?php echo $messID; ?>')">delete</a>&nbsp;&nbsp;
    <a href="javascript:toggleReplyBox('<?php echo $subject; ?>', '<?php echo ($toID == $id) ? $fromName : $toName; ?>', '<?php echo ($toID == $id) ? $fromID : $toID; ?>')">reply</a>
  </div>
  <h2 id="title">title: <?php echo strtolower($subject); ?></h2>
  <div id="content">
    <p><strong>from:</strong> <?php echo strtolower($fromName); ?></p>
    <p><strong>to:</strong> <?php echo strtolower($toName); ?></p>
    <p><strong>message:</strong><br /><br /><?php echo strtolower($message); ?></p>
  </div>
  <?php endif; ?>
</body>
</html>