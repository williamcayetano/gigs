<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/includeFunctions.php");
  include_once("scripts/checkuserlog.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  }
  
  if (isset($_GET['name']) && isset($_GET['city'])) {
    $state = clean($_GET['name']);
    $city = clean($_GET['city']);
    
    //set cookie
    if (!isset($_COOKIE['auctionLocation'])) {
      $location = $state . '`' . $city;
      $expire = time()+60*60*24*30; //one month (60 sec * 60 min * 24 hours * 30 days)
      setcookie('auctionLocation', $location, $expire, '/');
    }
    
    //log view
    $device = getUserAgent();
    $ipAddress = getenv('REMOTE_ADDR');
    $referer = '';
    if(isset($_SERVER['HTTP_REFERER'])){
      $referer = $_SERVER['HTTP_REFERER'];
    }
    $location = $state . ' ' . $city;
    $sqlPostView = mysqli_query($link, "INSERT INTO logs (ipaddress, user_agent, referer, location, post_date) VALUES 
        									  ('$ipAddress', '$device', '$referer', '$location', now())");
  
  
  
    //check for ended auctions
    $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE active='y' AND time < now()");
  
    while ($row = mysqli_fetch_array($sqlGetList)) {
      $listID = $row['id'];
      $userID = $row['user_id'];
      $time = strtotime($row['time']);
      $nowTime = time();
      $catID = $row['category_id'];
      $winner = $row['winner_id'];
      $title = htmlkarakter($row['title']);
      $sqlGetCat = mysqli_query($link, "SELECT name FROM categories WHERE id='$catID' LIMIT 1");
	  $sqlNumCat = mysqli_num_rows($sqlGetCat);
		
	  if ($sqlNumCat > 0) {
	    while ($row2 = mysqli_fetch_array($sqlGetCat)) {
		    $cat = $row2['name'];
	    }
	  }
		
	  $kaboom = explode(' - ', $cat);
      $catFlag = $kaboom[0];
    
      if ($time < $nowTime) {
        if ($winner == 0) {//if no winner yet, find out 
          if (strcmp($catFlag, 'gig') == 0){
            $sqlGetMin = mysqli_query($link, "SELECT bid_user_id FROM bids WHERE listing_id='$listID' AND price=(SELECT MIN(price) FROM bids WHERE listing_id='$listID' AND active='y') AND active='y' LIMIT 1");
		    $sqlMinCount = mysqli_num_rows($sqlGetMin);
		      
		    if ($sqlMinCount > 0) {
		      while ($row = mysqli_fetch_array($sqlGetMin)) {
		        $subject = 'WINNING BID: ' . $title;
		        $bidUserID = $row['bid_user_id'];
		            
		        $sqlUpdateList = mysqli_query($link, "UPDATE listings SET winner_id='$id', end_result='w', active='n' WHERE id='$listID' LIMIT 1");
                
                $winnerName = getUsername($bidUserID);
                $listingName = getUsername($userID);
                $message = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $bidUserID . '">' . $winnerName . '</a> has won this auction. Please get in contact with them to complete this transaction.';
                $message2 = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $userID . '">' . $listingName . '</a> has been notified that you have won this auction. They should be getting in contact with you shortly to complete this transaction.';
                
                $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$bidUserID', 0, '$subject', '$message2', now())");
                $sqlInsertMess2 = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$userID', 0, '$subject', '$message', now())");
		            
		      }
		    } else {
              $sqlUpdateList = mysqli_query($link, "UPDATE listings SET active='n' WHERE id='$listID' LIMIT 1");         
		    }
          } else {
            $sqlGetMax = mysqli_query($link, "SELECT bid_user_id FROM bids WHERE listing_id='$listID' AND price=(SELECT MAX(price) FROM bids WHERE listing_id='$listID' AND active='y') AND active='y' LIMIT 1");
		    $sqlMaxCount = mysqli_num_rows($sqlGetMax);  
		          
		    if ($sqlMaxCount > 0) {
		      while ($row2 = mysqli_fetch_array($sqlGetMax)) {
		        $subject = 'WINNING BID: ' . $title;
		        $bidUserID = $row2['bid_user_id'];
		            
		        $sqlUpdateList = mysqli_query($link, "UPDATE listings SET winner_id='$bidUserID', end_result='w', active='n' WHERE id='$listID' LIMIT 1");
                
                $winnerName = getUsername($bidUserID);
                $listingName = getUsername($userID);
                $message = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $bidUserID . '">' . $winnerName . '</a> has won this auction. Please get in contact with them to complete this transaction.';
                $message2 = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $userID . '">' . $listingName . '</a> has been notified that you have won this auction. They should be getting in contact with you shortly to complete this transaction.';
                
                $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$bidUserID', 0, '$subject', '$message2', now())");
                $sqlInsertMess2 = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$userID', 0, '$subject', '$message', now())");
		      }
		    } else {
              $sqlUpdateList = mysqli_query($link, "UPDATE listings SET active='n' WHERE id='$listID' LIMIT 1");         
		    }
          }
        }//end if ($winner == 0)
	  }//end if ($time < $nowTime)*/
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
  <title>GigNGood | Bid On Gigs And Goods In Online Classifieds Auctions | Gig Good Auction<?php echo isset($state) ? ucfirst($state) : 'Service Item'; ?></title>
</head>
  <style>
    .category, td {
      width: 180px;
      margin-bottom: 20px;
      overflow: hidden;
      table-layout:fixed;
      white-space:nowrap;
    }
    h2 { 
      clear: both; 
      width: 1000px;
      float: left;
    }
    
    body { 
      background: #f0f0f0; 
      font-weight: bold;
    }
    
    a { text-decoration: none; }
    
    td { text-align: center; }
    
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
    
    table th {
      background: #348075;
      padding: 6px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white; 
    }
    
    table th a {
      color: white;
      display: block;
      width: 100%;
      height: 100%;
    }
    
    table th:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #goods {
     width:800 px;
    }
    
    .masonry {
      width: 1100px;
      float: left;
    }
    
    .ad_unit {
      clear: both;
    }
    
    #side_ad {
      float: right;
      margin-bottom: 60px;
    }
    #header {
      clear: both;
      float: left;
      font-size: 30px;
    }
    #slogan {
      clear: both;
      float: left;
      margin-bottom: 20px;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script src="scripts/jquery.masonry.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#gigs').masonry({
        itemSelector: '.category',
        columnWidth: 210,
      });
      
      $('#goods').masonry({
        itemSelector: '.category',
        columnWidth: 210,
      });
    });
  </script>
<body>
  <?php if (isset($city)) : ?>
  <div id="breadcrumb"><a href="index.php">home</a> >> <a href="cities.php?name=<?php echo $state; ?>"><?php echo $state; ?></a> >> <?php echo $city; ?></div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  
  <form action="search.php" method="get" id="search_box">
    search this city: <input type="text" name="search" size="40" />
    <input type="hidden" name="name" value="<?php echo $state; ?>" />
    <input type="hidden" name="city" value="<?php echo $city; ?>" />
    <input type="submit" name="myButton" value="Search" />
  </form>
  <div id="submit_link">submit an <a href="submit.php?name=<?php echo $state; ?>&city=<?php echo $city; ?>">auction</a> to this city!</div>
  <div id="header"><?php echo $header; ?></div>
  <div id="slogan"><?php echo $slogan; ?></div>
    
  <div class="ad_unit">
    <script type="text/javascript"><!--
       google_ad_client = "ca-pub-8140905193772687";
       /* AuctionWide */
       google_ad_slot = "3529678498";
       google_ad_width = 728;
       google_ad_height = 90;
       //-->
    </script>
    <script type="text/javascript"
       src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>
  </div>
  
  <h2>gigs</h2>
  <div id="gigs">
  <?php 
    $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE id <= 9");
    while ($row = mysqli_fetch_array($sqlGetCat)) :
      $catID = $row['id'];
      $name = $row['name'];
      $kaboom = explode(' - ', $name);
      //service request id between 1-9 
      $sqlGetLists = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC");
      $sqlCountLists = mysqli_num_rows($sqlGetLists);
      $listID;
      while ($row2 = mysqli_fetch_array($sqlGetLists)) { $listID = $row2['id']; }
  ?>
    <table class="category">
      <tr>
        <th>
          <?php 
            if ($sqlCountLists > 1) {
              echo '<a href="listings.php?name=' . $state . '&city=' . $city . '&category=' . $catID . '">' . $kaboom[1] . '</a>'; 
          	} else if ($sqlCountLists == 1) {
           	  echo '<a href="listing.php?id=' . $listID . '">' . $kaboom[1] . '</a>';
            } else {
           	  echo '<a href="submit.php?name=' . $state . '&city=' . $city. '&category=' . $name . '">' .  $kaboom[1] . '</a>';
          	}
          ?>
        </th>
      </tr>
      <?php 
        if ($sqlCountLists > 0) :
      ?> 
      <tr>
        <td><?php echo $sqlCountLists > 1 ? $sqlCountLists . ' auctions' : $sqlCountLists . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?>
    <?php endwhile; ?>
    </table>
  </div>
  
  <div class="ad_unit">
    <script type="text/javascript"><!--
      google_ad_client = "ca-pub-8140905193772687";
      /* AuctionWideMiddle */
      google_ad_slot = "3201572140";
      google_ad_width = 728;
      google_ad_height = 15;
      //-->
    </script>
    <script type="text/javascript"
      src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>
  </div>
  
  <h2 id="goods_header">goods</h2>
  <div id="goods">
  <?php 
    $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE id BETWEEN 10 AND 40");
    while ($row = mysqli_fetch_array($sqlGetCat)) :
      $catID = $row['id'];
      $name = $row['name'];
      $kaboom = explode(' - ', $name);
      $sqlGetLists = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC");
      $sqlCountLists = mysqli_num_rows($sqlGetLists);
      $listID;
      while ($row2 = mysqli_fetch_array($sqlGetLists)) { $listID = $row2['id']; }
  ?>
    <table class="category">
      <tr>
        <th>
          <?php 
            if ($sqlCountLists > 1) {
              echo '<a href="listings.php?name=' . $state . '&city=' . $city . '&category=' . $catID . '">' . $kaboom[1] . '</a>'; 
          	} else if ($sqlCountLists == 1) {
           	  echo '<a href="listing.php?id=' . $listID . '">' . $kaboom[1] . '</a>';
            } else {
           	  echo '<a href="submit.php?name=' . $state . '&city=' . $city. '&category=' . $name . '">' .  $kaboom[1] . '</a>';
          	}
          ?>
        </th>
      </tr>
      <?php
        if ($sqlCountLists > 0) :
      ?> 
      <tr>
        <td><?php echo $sqlCountLists > 1 ? $sqlCountLists . ' auctions' : $sqlCountLists . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?>
    <?php endwhile; ?>
    </table>
  <?php endif; ?>
  </div>
</body>
</html>