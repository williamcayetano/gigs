<?php
//make sure url's match what we're searching for in database
//add php to this page to check for ended auctions AND MAKE SURE THEY END
//log visitors here
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/includeFunctions.php");
  include_once("scripts/checkuserlog.php");
  
  //if referer is coming from home, submit or list
  if (isset($_SERVER['HTTP_REFERER'])) {
    if (strcmp(substr($_SERVER['HTTP_REFERER'], 0, 21), 'http://localhost:8888') == 0) {
      //unset cookie
      if (isset($_COOKIE['auctionLocation'])) {
        setCookie('auctionLocation', '', time()-42000, '/');
      }
    } else {
      if (isset($_COOKIE['auctionLocation'])) {
        $kaboom = explode('`', $_COOKIE['auctionLocation']);
        $state = $kaboom[0];
        $city = $kaboom[1];
        header('location: home.php?name=' . $state . '&city=' . $city);
      }
    }
  } else {
    if (isset($_COOKIE['auctionLocation'])) {
        $kaboom = explode('`', $_COOKIE['auctionLocation']);
        $state = $kaboom[0];
        $city = $kaboom[1];
        header('location: home.php?name=' . $state . '&city=' . $city);
      }
  }
  
  //log view
  $device = getUserAgent();
  $ipAddress = getenv('REMOTE_ADDR');
  $referer = '';
  if(isset($_SERVER['HTTP_REFERER'])){
    $referer = $_SERVER['HTTP_REFERER'];
  }
  $sqlPostView = mysqli_query($link, "INSERT INTO logs (ipaddress, user_agent, referer, location, post_date) VALUES 
        									  ('$ipAddress', '$device', '$referer', 'home', now())");
  
  
  
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
  <meta name="description" content="GigNGood is an online marketplace
  where members can list and bid on gigs (services), places, and goods in an auction style
  format." />
  <meta name="keywords" content="gig, good, gigs, goods, gigging, giggin, auction, online auction, services, classifieds" />
  <title>GigNGood | Bid On Gigs And Goods In Online Classifieds Auctions | Gig Good Auction Service Item</title>
  <style>
    .state {
      width: 180px;
      margin-bottom: 20px;
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
    
    table th {
      background: #348075;
      padding: 6px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
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
    
    #search_box {
      width: 500px;
      float: left;
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
    
    .masonry {
      width: 1100px;
      float: left;
    }
    
    .ad_unit {
      clear: both;
    }
    
    #header {
      clear: both;
      float: left;
      font-size: 30px;
      margin-top: 10px;
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
      $('#usa').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
      
      $('#canada').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
      
      $('#europe').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
      
      $('#asia').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
      
      $('#latin').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
      
      $('#africa').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
      
      $('#oceania').masonry({
        itemSelector: '.state',
        columnWidth: 210,
      });
    });
  </script>
</head>
<body>
  <form action="search.php" method="get" id="search_box">
    <strong>search everywhere:</strong> <input type="text" name="search" size="40" />
    <input type="submit" name="myButton" value="search" />
  </form>
  <div id="log_options"><?php echo $logOptions; ?></div>
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
  
  <h2>united states of america</h2>
  <div id="usa">
  <?php 
    foreach ($stateArray as $state) : 
      $sqlGetCity = mysqli_query($link, "SELECT city FROM listings WHERE region='united states of america' AND country_state='$state' AND active='y'");
      $sqlCountCity = mysqli_num_rows($sqlGetCity);
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $state; ?>"><?php echo $state; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetCity)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?> 
    </table>
  <?php endforeach; ?>
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

  <h2 id="canada_head">canada</h2>
  <div id="canada">
  <?php 
    foreach ($canadaArray as $province) : 
      $sqlGetProv = mysqli_query($link, "SELECT city FROM listings WHERE region='canada' AND country_state='$province' AND active='y'"); 
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $province; ?>"><?php echo $province; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetProv)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?>  
    </table>
  <?php endforeach; ?>
  </div>
  
  <h2>europe</h2>
  <div id="europe">
  <?php 
    foreach ($europeArray as $country) : 
      $sqlGetCon = mysqli_query($link, "SELECT city FROM listings WHERE region='europe' AND country_state='$country' AND active='y'");     
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $country; ?>"><?php echo $country; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetCon)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?> 
    </table>
  <?php endforeach; ?>
  </div>
  
  <h2>asia / middle east</h2>
  <div id="asia">
  <?php 
    foreach ($asiaArray as $country) : 
      $sqlGetCon = mysqli_query($link, "SELECT city FROM listings WHERE region='asia/middle east' AND country_state='$country' AND active='y'");     
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $country; ?>"><?php echo $country; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetCon)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?> 
    </table>
  <?php endforeach; ?>
  </div>
  
  <h2>latin america / carribean</h2>
  <div id="latin">
  <?php 
    foreach ($latinArray as $country) : 
      $sqlGetCon = mysqli_query($link, "SELECT city FROM listings WHERE region='latin america/carribean' AND country_state='$country' AND active='y'");     
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $country; ?>"><?php echo $country; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetCon)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?>
    </table>
  <?php endforeach; ?>
  </div>
  
  <h2>africa</h2>
  <div id="africa">
  <?php 
    foreach ($africaArray as $country) : 
      $sqlGetCon = mysqli_query($link, "SELECT city FROM listings WHERE region='africa' AND country_state='$country' AND active='y'");     
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $country; ?>"><?php echo $country; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetCon)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?>
    </table>
  <?php endforeach; ?>
  </div>
  
  <h2>oceania</h2>
  <div id="oceania">
  <?php 
    foreach ($oceaniaArray as $country) : 
      $sqlGetCon = mysqli_query($link, "SELECT city FROM listings WHERE region='oceania' AND country_state='$country' AND active='y'");     
  ?>
    <table class="state">
      <tr>
        <th <?php echo $sqlCountCity > 0 ? 'class="auctions"' : ''; ?>>
          <a href="cities.php?name=<?php echo $country; ?>"><?php echo $country; ?></a>
        </th>
      </tr>
      <?php 
        $auctCount = 0;
        while ($row = mysqli_fetch_array($sqlGetCon)) {
          $auctCount++;
        }
        if ($auctCount > 0) :
      ?>
      <tr>
        <td><?php echo $auctCount > 1 ? $auctCount . ' auctions' : $auctCount . ' auction'; ?> listed</td>
      </tr>
      <?php endif; ?> 
    </table>
  <?php endforeach; ?>
  </div>
</body>
</html>