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
    $profileID = preg_replace('#[^0-9]#', '', $_GET['id']);
  } else {
    echo 'this member has no reviews';
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
    
    .reviewTable {
      margin: auto;
    }
    
    .reviewTable td {
      overflow: hidden;
      table-layout:fixed;
    }
    
    .revTable_comment {
      overflow: hidden;
      table-layout:fixed;
    }
    
    
    .revTable_field {
      text-align: center;
      
    }
    
    .revTable_field a {
      background: #348075;
      padding-top: 6px;
      padding-bottom: 6px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      display: block;
      width: 100%;
      height: 100%;
      font-weight: bold;
    }
    
    .revTable_field a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #content {
      width: 800px;
      margin: auto;
      padding: 40px;
      background-color: white;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }
  </style>
</head>
<body> 
  <div id="breadcrumb"><a href="index.php">home</a></div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <?php if (isset($_GET['id'])) : 
          $profileID = preg_replace('#[^0-9]#', '', $_GET['id']); ?>
  <h2>reviews</h2>
  <div id="content">
  <table class="reviewTable" width="800">
    <tr> 
      <th colspan="4">my reviews</th>
    </tr>
    <tr>
      <th>reviewer</th>
      <th>service/item</th>
      <th>score</th>
      <th>date</th>
    </tr>
    <?php
      $sqlGetRev = mysqli_query($link, "SELECT * FROM reviews WHERE voted_on_id='$profileID' AND active='y'");
      $sqlCountRev = mysqli_num_rows($sqlGetRev);
    
      if ($sqlCountRev > 0) :
      	while ($row = mysqli_fetch_array($sqlGetRev)) :
          $listID = $row['list_id'];
          $voterID = $row['voter_id'];
          $voterName = getUsername($voterID);
          $score = $row['score'];
          $title = $row['title'];
          $comment = $row['comment'];
          $date = strftime("%b %d, %Y", strtotime($row['post_date']));
          $time;
          
          $sqlGetTime = mysqli_query($link, "SELECT time FROM listings WHERE id='$listID'");
          while ($row2 = mysqli_fetch_array($sqlGetTime)) { $time = strtotime($row2['time']); }
          //try to get date 7 days into the future so people don't have all the time in the world to rate
          $future7Days = $time + (86400 * 7);
          $nowTime = time();
          if ($nowTime > $future7Days) :
    ?>
    <tr>
      <td class="revTable_field" width="15%"> 
        <a href="profile.php?id=<?php echo $voterID; ?>"><?php echo strtolower($voterName); ?></a>
      </td>
      <td class="revTable_field" width="65%">
        <a href="listing.php?id=<?php echo $listID; ?>"><?php echo strtolower($title); ?></a>
      </td>
      <td class="revTable_field" width="5%">
        <?php echo $score; ?>
      </td>
      <td class="revTable_field" width="15%">
        <?php echo strtolower($date); ?>
      </td>
    </tr>
    <tr>
      <td class="revTable_comment" colspan="4">
        <?php echo strtolower($comment); ?>
      </td>
    </tr>
    <?php  endif;
          endwhile; 
         endif; ?>
  <?php endif; ?>
  </div>
</body>
</html>