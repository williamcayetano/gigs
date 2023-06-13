<?php
//Buy it now button
//javascript, remove countdown when time run outcountry_state
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/includeFunctions.php");
  include_once("scripts/checkuserlog.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  } else {
    $id = 0;
  }
  
  if (isset($_GET['id'])) {
    $listID = preg_replace('#[^0-9]#', '', $_GET['id']);
    $expired = FALSE;
    
    $sqlGetList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listID' LIMIT 1");
    $sqlListCount = mysqli_num_rows($sqlGetList);
    if ($sqlListCount > 0) { 
      while ($row = mysqli_fetch_array($sqlGetList)) {
        $time = strtotime($row['time']);
        $nowTime = time();
        
        $userID = $row['user_id'];
        $categoryID = $row['category_id'];
        $city = htmlkarakter($row['city']);
        $state = htmlkarakter($row['country_state']);
        $region = htmlkarakter($row['region']);
        $reservePrice = $row['reserve_price'];
        $buyPrice = $row['buy_price'];
        $title = htmlkarakter($row['title']);
        //<> messes up html; " and ' mess up javascript, so I just strip them out
        $title = str_replace(array("<", ">", "'", "\""), array('','','',''), $title);
        $title = strtolower($title);
		$desc =  htmlkarakter($row['description']);
		$desc = str_replace(array("<", ">"), array('',''), $desc);
		$desc = strtolower($desc);
		$photo = $row['photo_name'];
		$shipping = $row['shipping'];
		$postDate = strtotime($row['post_date']);
		$postDate = date("D M j g:i a", $postDate);
		$postDate = strtolower($postDate);
		$username = getUsername($userID);
		$winner = $row['winner_id'];
		$end = $row['end_result'];
		$active = $row['active'];
		$maxMinBid = '';
		$cat = '';
		
		$sqlGetCat = mysqli_query($link, "SELECT name FROM categories WHERE id='$categoryID' LIMIT 1");
		$sqlNumCat = mysqli_num_rows($sqlGetCat);
		
		if ($sqlNumCat > 0) {
		  while ($row2 = mysqli_fetch_array($sqlGetCat)) {
		    $cat = $row2['name'];
		  }
		}
		
		$kaboom = explode(' - ', $cat);
        $catFlag = $kaboom[0];
        
        if (strcmp($catFlag, 'gig') == 0){
          if ($reservePrice > 0) {
            $reservePrice = '<strong>max price:</strong> $' . $reservePrice . '.00';
          } else {
            $reservePrice = '<strong>max price:</strong> none';
          }
          
          $sqlGetMin = mysqli_query($link, "SELECT MIN(price) FROM bids WHERE listing_id='$listID' AND active='y' LIMIT 1");
          //$sqlGetMin = mysqli_query($link, "SELECT price FROM bids WHERE listing_id='$listID' AND price=(SELECT MIN(price) FROM bids WHERE listing_id='$listID') AND active='y' LIMIT 1");
		  $sqlMinCount = mysqli_num_rows($sqlGetMin);
		        
		  if ($sqlMinCount > 0) {
		    while ($row3 = mysqli_fetch_array($sqlGetMin)) {
		      $minPrice = $row3['MIN(price)'];
		      if ($minPrice != '') {
		        $maxMinBid = 'minimum bid: $' . $minPrice . '.00';
		      } else {
		        $maxMinBid = 'no bids yet.';
		      }
		    }
		  } else {
		    $maxMinBid = 'no bids yet.';
		  }
        } else {
          if ($reservePrice > 0) {
            $reservePrice = '<strong>reserve price:</strong> $' . $reservePrice . '.00';
          } else {
            $reservePrice = '<strong>reserve price:</strong> none';
          }
          
          $sqlGetMax = mysqli_query($link, "SELECT MAX(price) FROM bids WHERE listing_id='$listID' AND active='y' LIMIT 1");
		  $sqlMaxCount = mysqli_num_rows($sqlGetMax);
		        
		  if ($sqlMaxCount > 0) {
		    while ($row2 = mysqli_fetch_array($sqlGetMax)) {
		      $maxPrice = $row2['MAX(price)'];
		      if ($maxPrice != '') {
		        $maxMinBid = '<strong>maximum bid:</strong> $' . $maxPrice . '.00';
		      } else {
		        $maxMinBid = 'no bids yet.';
		      }
		    }
		  } else {
		    $maxMinBid = 'no bids yet.';
		  }
        }
        
        if (strcmp($shipping, 'l') == 0) {
          $shipping = 'available for local pickup only';
        } else if (strcmp($shipping, 'd') == 0) {
          $shipping = 'will ship within my country';
        } else if (strcmp($shipping, 'i') == 0) {
          $shipping = 'will ship internationally';
        } else {
          $shipping = '';
        }
        
        if ($buyPrice > 0) {
          $buyPrice = 'buy it now for $' . $buyPrice . '.00';
        } else {
          $buyPrice = '';
        }
        
        if (strcmp($active,'n') == 0) {
          $expired = TRUE;
          if (strcmp($end,'b') == 0) {
            if ($winner > 0) {
              $winUsername = getUsername($winner);
              $msg = '<a href="profile.php?id=' . $winner . '">' . strtolower($winUsername) . '</a> has won this auction by using Buy It Now!';
            }
          } else if (strcmp($end,'c') == 0) {
            $msg = '<a href="profile.php?id=' . $userID . '">' . strtolower($username) . '</a> has canceled this auction.';
          } else if (strcmp($end,'w') == 0) {
            if ($winner > 0) {
              $winUsername = getUsername($winner);
              $msg = '<a href="profile.php?id=' . $winner . '">' . strtolower($winUsername) . '</a> has won this auction!';
            }
          } 
        } else {
          if ($time < $nowTime) {
            $expired = TRUE;
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
		          $msg = 'this auction has expired with no bidders.';
              	  $sqlUpdateList = mysqli_query($link, "UPDATE listings SET active='n' WHERE id='$listID' LIMIT 1");         
		        }
              } else {
                $sqlGetMax = mysqli_query($link, "SELECT bid_user_id FROM bids WHERE listing_id='$listID' AND price=(SELECT MAX(price) FROM bids WHERE listing_id='$listID' AND active='y') AND active='y' LIMIT 1");
		  		$sqlMaxCount = mysqli_num_rows($sqlGetMax);  
		          
		  		if ($sqlMaxCount > 0) {
		    	  while ($row2 = mysqli_fetch_array($sqlGetMax)) {
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
		    	  $msg = 'this auction has expired with no bidders.';
              	  $sqlUpdateList = mysqli_query($link, "UPDATE listings SET active='n' WHERE id='$listID' LIMIT 1");         
		    	}
              }
            }//end if ($winner == 0)
		  }//end if ($time < $nowTime)
		}//end if (strcmp($active,'n') == 0)
      }//while ($row = mysqli_fetch_array($sqlGetList))
      
      //log view
      if ($expired == FALSE) {
        if ($userID != $id) {//only post if user is not viewing their own post
          $device = getUserAgent();
          $ipAddress = getenv('REMOTE_ADDR');
          $referer = '';
          if(isset($_SERVER['HTTP_REFERER'])){
            $referer = $_SERVER['HTTP_REFERER'];
          }
          $sqlPostView = mysqli_query($link, "INSERT INTO views (listing_id, user_id, ipaddress, user_agent, referer, post_date) VALUES 
        									  ('$listID', '$id', '$ipAddress', '$device', '$referer', now())");
        }
      }//end if ($expired == FALSE)
      $countDownTime = date("F d, Y H:i:s", $time);
      $time = date("D M j g:i a", $time);
      
      $sqlGetViews = mysqli_query($link, "SELECT * FROM views WHERE listing_id='$listID'");
      $sqlNumViews = mysqli_num_rows($sqlGetViews);
    } else {
      echo '<h3>Listing cannot be found.</h3>';
      exit();
    } 
  } else if (isset($_POST['price']) && isset($id)) {
    $price = preg_replace('#[^0-9]#', '', $_POST['price']);
    $recID = preg_replace('#[^0-9]#', '', $_POST['bidRecId']);
    $listID = preg_replace('#[^0-9]#', '', $_POST['bidListId']);
    
    if (isset($recID) && isset($listID)) {
      $sqlCheckList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listID' AND user_id='$recID' AND active='y' LIMIT 1");
      $sqlCheckCount = mysqli_num_rows($sqlCheckList);
      
      if ($sqlCheckCount > 0) {
        while($row = mysqli_fetch_array($sqlCheckList)) {
          $reservePrice = $row['reserve_price'];
          $buyPrice = $row['buy_price'];
          $categoryID = $row['category_id'];
          $cat = '';
          //stub value in case no one has bid yet. Then price will always be above max
          $maxPrice = 0;
          //stub value in case no one has bid yet. Then price will always be below min
          $minPrice = 1000000;
          //good or gig? If good, bidding goes up; Service, bidding goes down
          $good = FALSE;
          
          $sqlGetCat = mysqli_query($link, "SELECT name FROM categories WHERE id='$categoryID' LIMIT 1");
		  $sqlNumCat = mysqli_num_rows($sqlGetCat);
		  
		  if ($sqlNumCat > 0) {
		    while ($row2 = mysqli_fetch_array($sqlGetCat)) {
		      $cat = $row2['name'];
		      
		      $kaboom = explode(' - ', $cat);
        	  $catFlag = $kaboom[0];
        
        	  if (strcmp($catFlag, 'gig') == 0){
        	    $sqlGetMin = mysqli_query($link, "SELECT MIN(price) FROM bids WHERE listing_id='$listID' AND active='y' LIMIT 1");
		        $sqlMinCount = mysqli_num_rows($sqlGetMin);
		        
		        if ($sqlMinCount > 0) {
		          while ($row3 = mysqli_fetch_array($sqlGetMin)) {
		            $minPrice = $row3['MIN(price)'];
		          }
		        }//end if ($sqlMinCount > 0)
        	  } else {
        	    $good = TRUE;
		        $sqlGetMax = mysqli_query($link, "SELECT MAX(price) FROM bids WHERE listing_id='$listID' AND active='y' LIMIT 1");
		        $sqlMaxCount = mysqli_num_rows($sqlGetMax);
		        
		        if ($sqlMaxCount > 0) {
		          while ($row2 = mysqli_fetch_array($sqlGetMax)) {
		            $maxPrice = $row2['MAX(price)'];
		          }
		        }
        	  }//end if (strcmp($catFlag, 'gig') == 0)
		    }//end while ($row2 = mysqli_fetch_array($sqlGetCat))
		  }//end if ($sqlNumCat > 0)
          
          $title = $row['title'];
          $userID = $row['user_id'];
          $time = strtotime($row['time']);
          $nowTime = time();
          
          if ($time > $nowTime) {
            if ($price != '') {
              if ($id == $userID) {
                echo 'You can\'t send a bid to your own auction!';
                exit(); 
              //if bidding on gig, lower price wins
			  } else if (($good == FALSE) && ($reservePrice > 0) && ($price > $reservePrice)) {
                echo 'The procreator of this gig has specified a price limit of $' . $reservePrice . '.00.<br />
                      Your bid is too high.';
                exit();
              } else if (($good == FALSE) && ($price >= $minPrice)) {
                echo 'You can only bid lower than the lowest bid price.';
                exit();
              } else if (($good == FALSE) && ($buyPrice != 0) && ($price <= $buyPrice)) {
                $title = 'WINNING BID: ' . $title;
                $sqlInsertBid = mysqli_query($link, "INSERT INTO bids (listing_id, listing_owner_id, bid_user_id, price, title, winner, post_date) VALUES ('$listID', '$userID', '$id', '$price', '$title', 'y', now())");
                $sqlUpdateList = mysqli_query($link, "UPDATE listings SET winner_id='$id', end_result='b', active='n' WHERE id='$listID' LIMIT 1");
                
                $winnerName = getUsername($id);
                $listingName = getUsername($userID);
                $message = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $id . '">' . $winnerName . '</a> has won this auction. Please get in contact with them to complete this transaction.';
                $message2 = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $userID . '">' . $listingName . '</a> has been notified that you have won this auction. They should be getting in contact with you shortly to complete this transaction.';
                
                $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$id', 0, '$title', '$message2', now())");
                $sqlInsertMess2 = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$userID', 0, '$title', '$message', now())");
                
                echo 'You\'ve just won this gig!<br/>
                      The listing user will get in contact with you shortly by private message.';
                exit();
              } else if (($good == FALSE) && ($price < $minPrice)) {
                $title = 'BID: ' . $title;
                $sqlInsertBid = mysqli_query($link, "INSERT INTO bids (listing_id, listing_owner_id, bid_user_id, price, title, post_date) VALUES ('$listID', '$userID', '$id', '$price', '$title', now())");
                echo 'Bid received';
                exit();
              } else if (($good == TRUE) && ($reservePrice > 0) && ($price < $reservePrice)) {
                echo 'The member who listed this auction set a reserved price of $' . $reservePrice . '.00.<br />
                      Your bid is too low.';
                exit();
              } else if (($good == TRUE) && ($price <= $maxPrice)) {
                echo 'You can only bid higher than the highest bid price.';
                exit();
              } else if (($good == TRUE) && ($buyPrice != 0) && ($price >= $buyPrice)) {
                $title = 'WINNING BID: ' . $title;
                $sqlInsertBid = mysqli_query($link, "INSERT INTO bids (listing_id, listing_owner_id, bid_user_id, price, title, winner, post_date) VALUES ('$listID', '$userID', '$id', '$price', '$title', 'y', now())");
                $sqlUpdateList = mysqli_query($link, "UPDATE listings SET winner_id='$id', end_result='b', active='n' WHERE id='$listID' LIMIT 1");
                
                $winnerName = getUsername($id);
                $listingName = getUsername($userID);
                $message = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $id . '">' . $winnerName . '</a> has won this auction. Please get in contact with them to complete this transaction.';
                $message2 = 'AUTO GENERATED:<br /><a href="profile.php?id=' . $userID . '">' . $listingName . '</a> has been notified that you have won this auction. They should be getting in contact with you shortly to complete this transaction.';
                
                $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$id', 0, '$title', '$message2', now())");
                $sqlInsertMess2 = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$userID', 0, '$title', '$message', now())");
                
                echo 'You\'ve just won this auction!<br/>
                      The listing user will get in contact with you shortly by private message.';
                exit();
              } else if (($good == TRUE) && ($price > $maxPrice)) {
                $title = 'BID: ' . $title;
                $sqlInsertBid = mysqli_query($link, "INSERT INTO bids (listing_id, listing_owner_id, bid_user_id, price, title, post_date) VALUES ('$listID', '$userID', '$id', '$price', '$title', now())");
                echo 'Bid received';
                exit();
              }
            } else {
              echo 'You have not set a price for your bid'; 
              exit();
            }//end if ($price != '')
          } else {
            echo 'This listing has expired.';
            exit();
          }//end if ($time > $nowTime)
        }//end while($row = mysqli_fetch_array($sqlCheckList))
      } else {
        echo 'Couldn\'t locate. May be expired or already confirmed';
        exit();
      }//end if ($sqlCheckCount > 0)
    } else {
      echo 'Couldn\'t locate listing.';
      exit();
    }//end if (isset($recID) && isset($listID))
  } else if (isset($_POST['pmTextArea']) && isset($id)) {
    $recID = preg_replace('#[^0-9]#', '', $_POST['pmRecId']);
    $listID = preg_replace('#[^0-9]#', '', $_POST['pmListId']);
    $message = clean($_POST['pmTextArea']);
    
    if (isset($recID) && isset($listID)) {
      $sqlCheckList = mysqli_query($link, "SELECT * FROM listings WHERE id='$listID' AND user_id='$recID' AND active='y' LIMIT 1");
      $sqlCheckCount = mysqli_num_rows($sqlCheckList);
      
      if ($sqlCheckCount > 0) {
        while($row = mysqli_fetch_array($sqlCheckList)) {
          $subject = 'QUERY: ' . $row['title'];
          $userID = $row['user_id'];
          $time = strtotime($row['time']);
          $nowTime = time();
          
          if ($time > $nowTime) {
            if (($message != '') && ($id != $userID)) {
              $sqlInsertMess = mysqli_query($link, "INSERT INTO messages (to_id, from_id, subject, message, post_date) VALUES ('$userID', '$id', '$subject', '$message', now())");
              echo 'Message sent';
              exit();
            } else if ($message == '') {
              echo 'You have not set a message for your bid';
              exit();
            } else if ($id == $userID) {
              echo "You can't send a message to your own listing silly!";
              exit();
            } else {
              echo 'There\'s been a gross miscalculation.'; 
              exit();
            }
          } else {
            echo 'This listing has expired.';
            exit();
          }//end if ($time > $nowTime)
        }//end while($row = mysqli_fetch_array($sqlCheckJob)) 
      } else {
        echo 'Couldn\'t locate listing.';
        exit();
      }//end if ($sqlCheckCount > 0) 
    } else {
      echo 'Couldn\'t locate listing.';
      exit();
    }//end if (isset($recID) && isset($listID))
  }
?>
<!DOCTYPE html>
<html lang="en">
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
  <meta name="description" content="GigNGood is an online marketplace
  where members can list and bid on gigs (services), places, and goods in an auction style
  format." />
  <meta name="keywords" content="gig, good, gigs, goods, auction, online auction, services, classifieds" />
  <title>GigNGood | Bid On Gigs And Goods In Online Classifieds Auctions | Gig Good Auction<?php echo isset($title) ? ' For ' . ucfirst($title) : 'Service Item'; ?></title>
<head>
  <style>
    body { 
      background: #f0f0f0; 
      /*font-weight: bold;*/
    }
    
    a { text-decoration: none; }
    
    #content {
      clear: both;
      background-color:#FFFFFF;
      padding: 40px;
      width: 700px;
      margin: auto;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }
    
    #content h1 {
      text-align: center;
      margin-top: -20px;
    }
    
    #content img {
      float: right;
    }
    
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
    
    .list_link {
      font-weight: bold;
      float: right;
      margin-top: -20px;
    }
    
    .list_link a {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
    }
    
    .list_link a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    .bid_link {
      font-weight: bold;
    }
    
    .bid_link a {
      float: left;
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
    }
    
    .bid_link a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #info {
     float: left;
     overflow: hidden;
    }
    
    #center_content {
      clear: both;
      text-align: center;
    }
    
    #center_content a {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #center_content a:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #bidToggle, #toggle {
      float: right;
      clear: both;
      width: 400px;
      background-color: #b6b6b6;
      padding: 20px;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
    }
    
    #replyBtn, #bidReplyBtn {
      background: #348075;
      padding: 4px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
      font-weight: bold;
    }
    
    #replyBtn:hover, #bidReplyBtn:hover {
      background: #287368;
      cursor: pointer;
    }
    
    #pmFinal {
      clear: both;
    }
    
	.grey_color {color: #666666;font-size: 11px;}
	
	 #header {
      clear: both;
      float: left;
      font-size: 30px;
      font-weight:bold;
    }
    #slogan {
      clear: both;
      float: left;
      font-weight: bold;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#toggle').hide();
      $('#bidToggle').hide();
       
      new DaysHMSCounter('<?php echo $countDownTime; ?>', 'counter'); 
    }); //end ready
    
    function numbersonly(myfield, e, dec)
	{
	  var key;
	  var keychar;

	  if (window.event)
   		key = window.event.keyCode;
	  else if (e)
   		key = e.which;
	  else
   		return true;
	  keychar = String.fromCharCode(key);

	  // control keys
	  if ((key==null) || (key==0) || (key==8) || 
    	(key==9) || (key==13) || (key==27)) {
   	    return true;
	  } else if ((("0123456789").indexOf(keychar) > -1)) {
        return true;
	  } else if (dec && (keychar == ".")) {
   	    myfield.form.elements[dec].focus();
        return false;
      } else {
        return false;
      }
    }
    
    function DaysHMSCounter(initDate, id) {
      this.counterDate = new Date(initDate);
      this.container = document.getElementById(id);
      this.update();
    }

    DaysHMSCounter.prototype.calculateUnit=function(secDiff, unitSeconds){
      var tmp = Math.abs((tmp = secDiff/unitSeconds)) < 1? 0 : tmp;
      return Math.abs(tmp < 0 ? Math.ceil(tmp) : Math.floor(tmp));
    }

    DaysHMSCounter.prototype.calculate=function(){
      var secDiff = Math.abs(Math.round(((new Date()) - this.counterDate)/1000));
      this.days = this.calculateUnit(secDiff,86400);
      this.hours = this.calculateUnit((secDiff-(this.days*86400)),3600);
      this.mins = this.calculateUnit((secDiff-(this.days*86400)-(this.hours*3600)),60);
      this.secs = this.calculateUnit((secDiff-(this.days*86400)-(this.hours*3600)-(this.mins*60)),1);
    }

    DaysHMSCounter.prototype.update=function(){ 
      this.calculate();
      this.container.innerHTML =
        " <strong>" + this.days + "</strong> " + (this.days == 1? "day" : "days") +
        " <strong>" + this.hours + "</strong> " + (this.hours == 1? "hour" : "hours") +
        " <strong>" + this.mins + "</strong> " + (this.mins == 1? "min" : "mins") +
        " <strong>" + this.secs + "</strong> " + (this.secs == 1? "sec" : "secs");
      var self = this;
      setTimeout(function(){self.update();}, (1000));
    }

    
    function toggleReplyBox(subject, listID, recUsername, recID) {
       $('#subjectShow').text(subject);
       $('#recipientShow').text(recUsername);
       document.replyForm.pmListId.value = listID;
       document.replyForm.pmRecId.value = recID;
       document.replyForm.replyBtn.value = "send message to "+recUsername;
       if ($('#toggle').is(":hidden") && $('#bidToggle').is(':hidden')) {
         $('#toggle').slideDown();
          $('#pmFormProcessGif').hide();
       } else {
         $('#toggle').slideUp();
       }
     }
     
     function toggleBidBox(subject, listID, recID, maxMinBid) {
       $('#bidSubjectShow').text(subject);
       $('#maxMinBidShow').text(maxMinBid);
       document.bidForm.bidListId.value = listID;
       document.bidForm.bidRecId.value = recID;
       if ($('#bidToggle').is(':hidden') && $('#toggle').is(':hidden')) {
         $('#bidToggle').slideDown();
          $('#bidProcessGif').hide();
       } else {
         $('#bidToggle').slideUp();
       }
     }
     
     function processReply() {
       var formData = $('#replyForm').serialize();
       var url = 'listing.php';
       if ($('#pmTextArea').val() == "") {
         $('#pmStatus').text('Please type in a message.').show().fadeOut(6000);
       } else {
         $('#pmFormProcessGif').show();
         $.post(url, formData,  
         function(data) {
           document.replyForm.pmTextArea.value = "";
           $('#pmFormProcessGif').hide();
           $('#toggle').slideUp();
           $('#pmFinal').html(data).show();
         });
       }
     }
     
     function processBid() {
       var formData = $('#bidForm').serialize();
       var url = 'listing.php';
       if ($('price').val() == "") {
         ('#bidStatus').text('Please type in a price.').show().fadeOut(6000);
       } else {
         $('#bidProcessGif').show();
         $.post(url, formData,
         function(data) {
           document.bidForm.price.value = "";
           $('#bidProcessGif').hide();
           $('#bidToggle').slideUp();
           $('#pmFinal').html(data).show();
         });
       }
     }
  </script>
</head>
<body>
  <div id="breadcrumb"><a href="index.php">home</a> >> auction</div>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <div id="header"><?php echo $header; ?></div>
  <div id="slogan"><?php echo $slogan; ?></div>
  <?php if (isset($id) && $id != 0 && isset($userID) && isset($listID) && isset($active) && strcmp($active,'y')==0 && $expired == FALSE) { 
          if ($id == $userID) {
            echo "<div class=\"list_link\"><a href=\"listEdit.php?id=$listID\">edit task</a></div>";
          } else {
            $bidMess = '<div class="bid_link"><a href="javascript:toggleBidBox(\'' . $title . '\',\'' . $listID . '\',\'' . $userID . '\',\'' . preg_replace('#</?strong>#', '', $maxMinBid) . '\')">place bid</a></div>';
            echo '<div class="list_link"><a href="javascript:toggleReplyBox(\'' . $title . '\',\'' . $listID . '\',\'' . $username . '\',\'' . $userID . '\')">send message</a></div>
                  <div id="bidToggle">
                    <h2>Sending bid for: <span style="color:white;" id="bidSubjectShow"></span></h2>
                    <h2><span style="color:white;" id="maxMinBidShow"></span></h2>
                    <form action="javascript:processBid();" name="bidForm" id="bidForm" method="POST">
                      <strong>$<input type="text" name="price" id="price" size="5" maxlength="6" onKeyPress="return numbersonly(this, event)"/>.00</strong>&nbsp;&nbsp;<span class="editable grey_color"></span><br />
                      <input type="hidden" name="bidListId" id="bidListId" />
					  <input type="hidden" name="bidRecId" id="bidRecId" />
                      <input name="bidReplyBtn" id="bidReplyBtn" type="button" value="confirm bid" onclick="javascript:processBid()" /> &nbsp;&nbsp;&nbsp
                      <span id="bidProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
                      <div id="bidStatus">&nbsp;</div><!--end pmStatus-->
                    </form>
                  </div>
                  <div id="toggle">
                    <h2>Message to: <span style="color:white;" id="recipientShow"></span><br />
                    Regarding listing: <span style="color:white;" id="subjectShow"></span></h2>
                    <form action="javascript:processReply();" name="replyForm" id="replyForm" method="POST">
        		  	  <textarea name="pmTextArea" id="pmTextArea" rows="8" style="width:98%;"></textarea><br />
					  <input type="hidden" name="pmListId" id="pmListId" />
					  <input type="hidden" name="pmRecId" id="pmRecId" />
					  <br />
					  <input name="replyBtn" id="replyBtn" type="button" onclick="javascript:processReply()" /> &nbsp;&nbsp;&nbsp; 
					  <span id="pmFormProcessGif"><img src="images/loading.gif" width="28" height="10" alt="Loading" /></span>
					  <div id="pmStatus">&nbsp;</div><!--end pmStatus-->
      			    </form>
      			  </div>
      			  <div id="pmFinal"></div>';
          }
        }
  ?>
  <div id="content">
    <h3><?php echo isset($msg) ? $msg : ''; ?></h3>
    <h1><?php echo isset($title) ? $title : ''; ?></h1>
    <?php echo isset($photo) ? $photo != '' ? '<a href="photo.php?name=' . $photo . '"><img src="media/resized_' . $photo . '" /></a>' : '' : ''; ?>
    <div id="info">
      <?php echo isset($countDownTime) ? $nowTime < strtotime($time) ? '<strong>time left:</strong> <div id="counter"></div>' : '' : ''; ?>
      <br />
      <?php echo isset($maxMinBid) ?  $maxMinBid  : ''; echo isset($bidMess) && $id != 0 ? $bidMess : '' ?><br /><br />
      <?php echo isset($reservePrice) ? $reservePrice : ''; ?><br />
      <?php echo isset($buyPrice) ? $buyPrice : ''; ?><br />
      <?php echo isset($time) ? '<br /><strong>expires:</strong> ' . strtolower($time) : ''; ?><br /><br />
      <?php echo isset($sqlNumViews) ? '<strong>views:</strong> ' . $sqlNumViews . ' views' : ''; ?><br /><br />
      <?php echo isset($city) ? '<strong>location:</strong> ' . $city : ''; ?>&nbsp;
      <?php echo isset($state) ? $state : ''; ?>&nbsp;
      <?php echo isset($region) ? $region : ''; ?><br /><br />
      <?php echo isset($shipping) ? '<strong>ship option:</strong> ' . $shipping : ''; ?><br /><br />
      <?php echo isset($cat) ? '<strong>category:</strong> ' . $cat : ''; ?><br /><br />
    </div>
    <div id="center_content">
      <p><?php echo isset($desc) ? $desc : ''; ?></p>
      <?php if (isset($postDate) && isset($username) && isset($userID)) {
              echo "<strong>posted:</strong> $postDate by <a href=\"profile.php?id=$userID\">$username</a>";
  		    }
      ?>
    </div>
  </div>
</body>
</html>