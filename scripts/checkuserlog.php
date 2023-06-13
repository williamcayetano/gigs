<?php
  session_start();
  
  include_once "connectToMySQL.php";
  $dyn_www = $_SERVER['HTTP_HOST']; // Dynamic www.domainName
  $logOptions = '<a href="./register.php">register account</a>
       |  
      <a href="./login.php">log in</a>';
  
  // If session ID is set for logged in user without cookies or remember me feature set
  if (isset($_SESSION['idx'])) {
    $decryptedID = base64_decode($_SESSION['idx']);
    $id_array = explode("p3h9xfn8sq03hs2234", $decryptedID);
    $logOptions_id = $id_array[1];
    
    $sqlPmCheck = mysqli_query($link, "SELECT id FROM messages WHERE to_id='$logOptions_id' AND recipient_opened='n'");
    $numNewPm = mysqli_num_rows($sqlPmCheck);
    $PM_envelope = '<img src="./images/pm1.gif" width="18" height="11" alt="messages" border="0"/>';
    $logOptions = $PM_envelope . '&nbsp;(' . $numNewPm . ')&nbsp; &nbsp;
    <a href="profile.php?id=' . $logOptions_id . '">profile</a>
     &nbsp;|&nbsp;
    <a href="logout.php">log out</a>';
    
  } else if (isset($_COOKIE['idCookie'])) { // If id cookie is set, but no session ID is set yet
    $decryptedID = base64_decode($_COOKIE['idCookie']);
    $id_array = explode("nm2c0c4y3dn3727553", $decryptedID);
    $userID = $id_array[1];
    $userPass = $_COOKIE['passCookie'];
    // Get their user first name to set into session var
    $sql_uname = mysqli_query($link, "SELECT username FROM users WHERE id='$userID' AND password='$userPass' LIMIT 1");
    $numRows = mysqli_num_rows($sql_uname);
    
    if ($numRows == 0) {
      //Kill cookies, send back to homepage
      setcookie("idCookie", '', time()-42000, '/');
      setcookie("passCookie", '', time()-42000, '/');
      header("location: index.php");
      exit();
    }
    
    while($row = mysqli_fetch_array($sql_uname)){
      $username = $row["username"];
    }
    
    $_SESSION['id'] = $userID;
	$_SESSION['idx'] = base64_encode("g4p3h9xfn8sq03hs2234$userID");
    $_SESSION['username'] = $username;
	
	$logOptions_id = $userID;
	
	mysqli_query($link, "UPDATE users SET last_log_date=now() WHERE id='$logOptions_id'");
	$sqlPmCheck = mysqli_query($link, "SELECT id FROM messages WHERE to_id='$logOptions_id' AND recipient_opened='n'");
    $numNewPm = mysqli_num_rows($sqlPmCheck);
    $PM_envelope = '<img src="./images/pm1.gif" width="18" height="11" alt="messages" border="0"/>';
    $logOptions = $PM_envelope . '&nbsp;(' . $numNewPm . ')&nbsp; &nbsp;
    <a href="profile.php?id=' . $logOptions_id . '">profile</a>
     &nbsp;|&nbsp;
    <a href="logout.php">log out</a>';
  } 
?>