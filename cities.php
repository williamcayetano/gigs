<?php
  //give white background for table
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  }
  
  if (isset($_GET['name'])) {
    $name = clean($_GET['name']);
    $sqlGetCity = mysqli_query($link, "SELECT city, id FROM listings WHERE country_state='$name' AND active='y'");
    $sqlCityCount = mysqli_num_rows($sqlGetCity);
    $locName = '';
    
    if ($sqlCityCount == 0) { 
      if (isset($id)) {
        header('location: submit.php?name=' . $name); 
      } else {
        header('location: login.php');
      }
    }
    
    if (isset($_COOKIE['auctionLocation'])) {
      setCookie('auctionLocation', '', time()-42000, '/');
    }
    
    if (in_array($name, $stateArray)) {
      $locName = 'state';
    } else if (in_array($name, $canadaArray)) {
      $locName = 'province';
    } else {
      $locName = 'country';
    }
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
  <title>GigNGood | Bid On Gigs And Goods In Online Classifieds Auctions | Gig Good Auction<?php echo isset($name) ? ' For ' . ucfirst($name) : 'Service Item'; ?></title>
  <style>
    .state {
      width: 400px;
      margin: auto;
      clear: both;
      background-color: white;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
      padding: 10px;
    }
    
    body { 
      background: #f0f0f0; 
      font-weight: bold;
    }
    
    a { text-decoration: none; }
    
    td { text-align: center; }
    
    h1 { 
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
    
    #search_box {
      width: 500px;
      float: left;
      margin-bottom: 10px;
      clear: left;
    }
    
    #search_box input[type=submit] {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #search_box input[type=submit]:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #log_options {
      float: right;
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
    
    #submit_link {
      float: right;
      font-weight: bold;
      margin-left: auto;
      margin-right: auto;
    }
    
    #submit_link a {
      background: #348075;
      padding-left: 4px;
      padding-right: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
    }
    
    #submit_link a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    table td {
      padding: 10px;
    }
    
    table td a {
      background: #348075;
      padding: 3px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
    }
    
    table td a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #header {
      clear: both;
      float: left;
      font-size: 30px;
    }
    #slogan {
      clear: both;
      float: left;
    }
  </style>
</head>
<body>
  <div id="breadcrumb"><a href="index.php">home</a> >> <?php echo $name; ?></div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <form action="search.php" method="get" id="search_box">
    <strong>search this <?php echo $locName; ?>: </strong><input type="text" name="search" size="40" />
    <input type="hidden" name="name" value="<?php echo $name; ?>" />
    <input type="submit" name="myButton" value="search" />
  </form>
  <div id="submit_link">want to <a href="submit.php?name=<?php echo $name; ?>">start</a> an auction in a city not listed here?</div>
  <div id="header"><?php echo $header; ?></div>
  <div id="slogan"><?php echo $slogan; ?></div><br />
    <h1><?php echo $name; ?></h1>
    <table class="state">
      <?php 
        $cityArray = array();
        while ($row = mysqli_fetch_array($sqlGetCity)) {
          $listID = $row['id'];
          $cityArray[] = strtolower($row['city']);
        }
        $uniqueCities = array_count_values($cityArray);
    
        foreach ($uniqueCities as $key => $value) :
      ?>
      <tr>
        <td><?php echo ($value > 1) ? '<a href="home.php?name=' . $name . '&city=' . $key . '">' : '<a href="listing.php?id=' . $listID . '">'; ?><?php echo $key; ?></a></td>
        <td><?php echo $value > 1 ? $value . ' auctions' : $value . ' auction'; ?></td>
      </tr>
      <?php endforeach;
        unset($cityArray); ?>   
</body>
</html>