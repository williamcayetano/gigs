<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/includeFunctions.php");
  include_once("scripts/checkuserlog.php");
  
  if (isset($_SESSION['idx']) || isset($_COOKIE['id'])) {
    header('location: index.php');
  }
  
  $msg = '';

  if(isset($_POST['username'])) {
    $username = $_POST['username'];
    $pass = $_POST['pass'];
    if (isset($_POST['remember'])) {
      $remember = $_POST['remember'];
    }
    // error handling conditional checks go here
    if ((!$username) || (!$pass)) {
      $msg = 'Error: Please fill in both fields';
    } else {
      $id;
      $username = clean($username);
      $pass = clean($pass);
      $pass = md5($pass);//put OR statement in sql query to login by username
      $sql = mysqli_query($link, "SELECT * FROM users WHERE username='$username' AND password='$pass' AND active='y'");
      $login_check = mysqli_num_rows($sql);
      if ($login_check > 0) {
        while ($row= mysqli_fetch_array($sql)) {
         $id = $row["id"];
         $_SESSION['id'] = $id;
         $_SESSION['idx'] = base64_encode("g4p3h9xfn8sq03hs2234$id");
         $username = $row['username'];
         $_SESSION['username'] = $username;
         
         mysqli_query($link, "UPDATE users SET last_log_date=now() WHERE id='$id' LIMIT 1");
        } // close while
        
        //Remember Me Section
        if ($remember == "yes") {
          $encryptedID = base64_encode("g4enm2c0c4y3dn3727553$id");
    	  setcookie("idCookie", $encryptedID, time()+60*60*24*100, "/"); // Cookie set to expire in about 30 days
		  setcookie("passCookie", $pass, time()+60*60*24*100, "/"); // Cookie set to expire in about 30 days
		}
		header('location: index.php');
      } else {
        sleep(2);
        $msg = "Error: Incorrect login data, please try again";
      }
    }  // Close else after error checks
  } 

?>

<!DOCTYPE html>
<html lang="en">
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
  <meta name="description" content="GigNGood is an online marketplace
  where members can list and bid on gigs (services), places, and goods in an auction style
  format." />
  <meta name="keywords" content="gig, good, gigs, goods, auction, online auction, services, classifieds" />
  <title>GigNGood | Bid On Gigs And Goods In Online Classifieds Auctions | Gig Good Auction Service Item</title>
<head>
  <title>OddJobr</title>
</head>
  <style>
    body { 
      background: #f0f0f0; 
      font-weight: bold;
    }
    
    a { text-decoration: none; }
    
    h2, h3 { 
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
    
    #loginForm input[type=submit] {
      background: #348075;
      padding: 5px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #loginForm input[type=submit]:hover {
      background: #287368;
      cursor: pointer;
    }
   
    .login_account a {
      background: #348075;
      padding-top: 1px;
      padding-bottom: 1px;
      padding-right: 3px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: 400;
      font-size: 14px;
    }
  </style>
<body>
  <div id="breadcrumb"><a href="index.php">home</a></div>
  <h3><?php echo $msg; ?></h3>
  <h2>log in or register to start an auction or bid on one</h2>
  <div id="content">
    <form action="login.php" id="loginForm" name="loginForm" method="post">
      <table class="login_fields">
        <tr> 
          <td class="login_error login_fields">
            <div id="interactionResults" style="font-size:15px; padding:10px; color:red;"></div>
          </td>
        </tr>
        <tr>
          <td class="login_username login_fields">
            username:<br />
            <input name="username" type="text" id="username" size="32" />
          </td>
        </tr>
        <tr>
          <td class="login_pass login_fields">
            password:<br />
            <input name="pass" type="password" id="pass" maxlength="24" size="32" />
          </td>
        </tr>
        <tr>
          <td class="login_remember login_fields">
            <input name="remember" type="checkbox" id="remember" value="yes" checked="yes" />
            remember me
          </td>
        </tr>
        <tr>
          <td class="login_submit login_fields">
            <input name="myButton" type="submit" id="myButton" value="sign in" /> 
          </td>
        </tr>
        <tr>
          <td class="login_account login_fields">
            need an account?<a href="register.php"> click here</a>
          </td>
        </tr>    
      </table>  
    </form>
  </div><!--end content_login-->
</body>
</html>