<?php
  session_start();
  
  //Unset all session variables
  $_SESSION = array();
  
  if (isset($_COOKIE['idCookie'])) {
    setCookie("idCookie", '', time()-42000, '/');
    setCookie("passCookie", '', time()-42000, '/');
  }
  if (isset($_COOKIE['auctionLocation'])) {
    setCookie('auctionLocation', '', time()-42000, '/');
  }
  
  session_destroy();
  
  if (!isset($_SESSION['id'])) {
    header("location: index.php");
  } else {
    print "<h2>Could not log you out, sorry the system encountered an error.</h2>";
	exit();
  }
?>