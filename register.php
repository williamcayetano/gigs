<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if(isset($_SESSION['id']) || isset($_COOKIE['id'])) {
    header("location: index.php");
  }
  
  if(isset($_POST['submit'])) {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $pass1 = clean($_POST['pass1']);
    $pass2 = clean($_POST['pass2']);

    //Query to check username/email duplication
    $sqlUnameCheck = mysqli_query($link, "SELECT username FROM users WHERE username='$username'");
    $unameCheck = mysqli_num_rows($sqlUnameCheck);
    
    $sqlEmailCheck = mysqli_query($link, "SELECT email FROM users WHERE email='$email'");
    $emailCheck = mysqli_num_rows($sqlEmailCheck);
    
    //Error handling
    if ((!$username) || (!$email) || (!$pass1) || (!$pass2)) {
      $errorMsg = "ERROR: You did not submit the following required information:< br /><br />";
    	
      if(!$username) {
        $errorMsg .= ' * User Name<br />';
      }
      if(!$email) {
        $errorMsg .= ' * Email Address<br />';
      }
      if(!$pass1) {
        $errorMsg .= ' * Login Password<br />';
      }
      if(!$pass2) {
        $errorMsg .= ' * Confirmation Password<br />';
      }
    } else if ($pass1 != $pass2) {
      $errorMsg = "ERROR: Your password fields did not match<br />";
    } else if (strlen($username) < 4) {
      $errorMsg = "ERROR: Your Username is too short. 4 - 20 characters please<br />";
    } else if (strlen($username) > 20) {
      $errorMsg = "ERROR: Your Username is too long. 4 - 20 characters please<br />";
    } else if (preg_match('#[^a-z0-9_]#i', $username)) {
      $errorMsg = "ERROR: Alphanumeric [a-Z] [0-9] characters only please.<br />";
    } else if ($unameCheck > 0) {
      $errorMsg = "ERROR: Your Username is already in use inside of our system. Please try another.<br />";
    } else if ($emailCheck > 0) {
      $errorMsg = "ERROR: Your Email is already in use inside of our system.<br />";
    } else if (stristr($email, "'")) {
      $errorMsg = "ERROR: Please, no apostrophe's in email address.<br />";
    } else if (stristr($email, "`")) {
      $errorMsg = "ERROR: Please, no backtick's in email address.<br />";
    } else if (stristr($email, "\\")) {
      $errorMsg = "ERROR: Please, no backslashes in email address.<br />";
    } else if (strlen($email) > 50) {
      $errorMsg = "ERROR: Please no email addresses over 50 characters.<br />";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  	  $errorMsg = "ERROR: This email address doesn't appear to be valid.<br />";
    } else {
      $errorMsg = "";
      
      $dbPassword = md5($pass1);
      
      //Get User Ip Address
      $ipaddress = getenv('REMOTE_ADDR');
      
      $username = strtolower($username);
      $email = strtolower($email);
      $sql = mysqli_query($link, "INSERT INTO users (username, password, email, ipaddress, join_date)
      VALUES('$username', '$dbPassword', '$email', '$ipaddress', now())")
      or die (mysqli_error($link));
            
      $id = mysqli_insert_id($link);
      
      //mkdir("users/$id", 0755);
      
      $_SESSION['id'] = $id;
      $_SESSION['idx'] = base64_encode("g4p3h9xfn8sq03hs2234$id");
      $_SESSION['username'] = $username;
      
      $encryptedID = base64_encode("g4enm2c0c4y3dn3727553$id");
      setcookie("idCookie", $encryptedID, time()+60*60*24*100, "/"); // Cookie set to expire in about 30 days
	  setcookie("passCookie", $pass2, time()+60*60*24*100, "/"); // Cookie set to expire in about 30 days
   	   
   	  header("location: index.php");
   	}
  
  } else {
    $errorMsg = "";
    $username = "";
    $email = "";
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
    .grey_color {
      color: #666666;
      font-size: 11px;
    }
    
    #content {
      width: 250px;
      margin: auto;
      clear: both;
      background-color: white;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
      padding-top: 30px;
      padding-right: 10px;
      padding-left: 50px;
      padding-bottom: 30px;
    }
    
    #registerForm input[type=submit] {
      background: #348075;
      padding: 5px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #registerForm input[type=submit]:hover {
      background: #287368;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div id="breadcrumb"><a href="index.php">home</a></div>
  <h2>sign up</h2>
  <div id="content">
    <form action="register.php" method="post" enctype="multipart/form data" id="registerForm">
      <table class="register_fields">
        <tr> 
          <td class="register_error">
            <?php print "$errorMsg"; ?>
          <td>
        </tr>
        <tr>
          <td class="register_name">
            user name: <span class="grey_color">alphanumeric characters</span><br />
            <input name="username" type="text" class="formFields" id="username" value="<?php print "$username"; ?>" size="32" maxlength="20" />
          <td>
        </tr>
        <tr>
          <td class="email">
            email address:<br />
            <input name="email" type="text" class="formFields" id="email" value="<?php print "$email"; ?>" size="32" maxlength="50" />
          </td>
        </tr>
        <tr>
          <td class="register_password1">
            password:<br />
            <input name="pass1" type="password" class="formFields" id="pass1" size="32" />
          </td>
        </tr>
        <tr>
          <td class="register_password2">
            confirm password:<br />
            <input name="pass2" type="password" class="formFields" id="pass2" size="32" />
          </td>
        </tr>
        <tr>
          <td class="register_submit">
            <input type="submit" name="submit" value="Submit" />
          </td>
        </tr>             
      </table>     
    </form> 
  </div>
</body>
</html>