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
  
  if (isset($_POST['submit']) && isset($id)) {
    $currentPass = $_POST['currentPass'];
	$newPass1 = $_POST['newPass1'];
	$newPass2 = $_POST['newPass2'];
	
	if ($newPass1 != $newPass2) {
	  $errorMsg = '<span class="red">ERROR:</span> The confirmation password you provided didn\'t match your new pasword.';
	} else if ($newPass1 == '' || $newPass2 == '') {
	  $errorMsg = '<span class="red">ERROR:</span> You need to type in a new password.';
	} else {
	  $currentPass = clean($currentPass);
      $newPass = clean($newPass1);
      $hashCurPass = md5($currentPass);
      $hashNewPass = md5($newPass);
      
      $sql = mysqli_query($link, "SELECT * FROM users WHERE id='$id' AND password='$hashCurPass'");
      $passCheckNum = mysqli_num_rows($sql);
      
      if ($passCheckNum > 0) {
        $sqlUpdate = mysqli_query($link, "UPDATE users SET password='$hashNewPass' WHERE id='$id'");
        $errorMsg = '* Your password has been successfully changed.
        <br /> Click <a href="profile.php?id=' . $id . '">here</a> to return to your profile';
      } else {
        $errorMsg = '<span class="red">ERROR:</span> Unsuccessful. Your current password did not match your profile.';
      }
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
    td.settings1 {
      text-align: center;
      font-size: 20px;
    }
    
    input[type=submit] {
  	  border: none;
  	  margin-right: 1em;
      padding: 4px;
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
<head>
<body>
    <div id="log_options"><?php echo $logOptions; ?></div>
    <h2>edit your account settings here</h2>
    <?php echo $errorMsg; ?>
    <div id="content">
    <table class="settings0">
      <form action="editSettings.php" method="post">
      <tr>
        <td class="settings1" colspan="2">
          <strong>change your password</strong>
        </td>
      </tr>
      <tr>
        <td class="settings2">
          <strong>your current password:</strong>
        </td>
        <td class="settings3">
          <input name="currentPass" type="password" id="currentPass" size="28" />
        </td>
      </tr>
      <tr>
        <td class="settings4">
          <strong>create new password:</strong>
        </td>
        <td class="settings5">
          <input name="newPass1" type="password" id="newPass1" size="28" />
        </td>
      </tr>
      <tr>
        <td class="settings6">
          <strong>confirm new password:</strong>
        </td>
        <td class="settings7">
          <input name="newPass2" type="password" id="newPass2" size="28" />
        </td>
      </tr>
      <tr>
        <td class="settings8">
          <input name="submit" type="submit" value="change password" />
        </td>
      </tr>
      </form>
    </table>
  </div><!--end content-->
</body>
</html>