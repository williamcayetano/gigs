<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/includeFunctions.php");
  include_once("scripts/checkuserlog.php");
  
  if (isset($_SESSION['idx'])) {
    $id = $logOptions_id;
  }
  
  if (isset($_GET['name']) && isset($_GET['city']) && isset($_GET['category'])) {
    $state = clean($_GET['name']);
    $city = clean($_GET['city']);
    $catID = preg_replace('#[^0-9]#', '', $_GET['category']);
    $catName = '';
    
    $sqlGetTotal = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC");
    $sqlCountTotal = mysqli_num_rows($sqlGetTotal);
    
    $sqlGetCat = mysqli_query($link, "SELECT name FROM categories WHERE id='$catID' AND active='y'");
    while ($row = mysqli_fetch_array($sqlGetCat)) { $catName = $row['name']; }
    
    $kaboom = explode(' - ', $catName);
    $catName = $kaboom[1];
    
    ############################Begin Pagination Logic#############################
    $numberRows = $sqlCountTotal;
    if (isset($_GET['pn'])) {
      $pageNumber = preg_replace('#[^0-9]#', '', $_GET['pn']);
    } else {
      $pageNumber = 1;
    }
    
    $itemsPerPage = 20;

    // Get the value of the last page in the pagination result set; Gets the total number of pages
    $lastPage = ceil($numberRows / $itemsPerPage);
  
    // Be sure URL variable $pageNumber is no lower than page 1 and no higher than $lastpage
    if ($pageNumber < 1) { 
      $pageNumber = 1; 
    } else if ($pageNumber > $lastPage && $lastPage > 0) { 
      $pageNumber = $lastPage; 
    } 
  
    // This creates 5 numbers to click in between the next and back buttons
    $centerPages = ""; // Initialize this variable
    $sub1 = $pageNumber - 1;//value of subtracting 1 page
    $sub2 = $pageNumber - 2;//value of subtracting 2 pages
    $add1 = $pageNumber + 1;//value of adding 1 page
    $add2 = $pageNumber + 2;//value of adding 2 pages
    if ($pageNumber == 1) { //should be no back button because you're already on page 1
	  //We use $_SERVER['PHP_SELF'] in case script has to change server environments, the pages won't be hardcoded. It will grap this current scripts name (in this case member_search.php)
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 1 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex.page 2
    } else if ($pageNumber == $lastPage) { //should be no forward button because you're already on last page
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 29
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 30 (current page)
    } else if ($pageNumber > 2 && $pageNumber < ($lastPage - 1)) { //set how many clickable numbers inbetween next and back buttons
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $sub2 . '">' . $sub2 . '</a> &nbsp;'; //ex. page 5
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 6
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 7 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex. page 8
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $add2 . '">' . $add2 . '</a> &nbsp;'; //ex. page 9
    } else if ($pageNumber > 1 && $pageNumber < $lastPage) { //on next to last page, just show last page number after current. one on each side. notice add1
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 28
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 29 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex. page 30 (last page)
    }
  
    //Ex. LIMIT 20 returns first 20 results; LIMIT 20, 5 returns 5 results(20, 21, 22, 23, 24)
    //subtract 1 since sql counts from 0
    $limit = 'LIMIT ' . ($pageNumber - 1) * $itemsPerPage . ',' . $itemsPerPage; 
  
    $paginationDisplay = ""; 
    //if only 1 page we require no paginated links to display (so none of this code will run)
    if ($lastPage != "1"){
      // This shows the user what page they are on, and the total number of pages.
      $paginationDisplay .= 'Page <strong>' . $pageNumber . '</strong> of ' . $lastPage. '&nbsp;&nbsp;';
	  // If we are not on page 1 we can place the Back link
      if ($pageNumber != 1) {
	    $previous = $pageNumber - 1;
		$paginationDisplay .=  '&nbsp;  <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $previous . '" class="backNextLink"> Back</a> ';
      } 
      // Lay in the clickable numbers display here between the Back and Next links
      $paginationDisplay .= '<span class="paginationNumbers">' . $centerPages . '</span>';
      // If we are not on the very last page we can place the Next link
      if ($pageNumber != $lastPage) {
        $nextPage = $pageNumber + 1;
		$paginationDisplay .=  '&nbsp;  <a href="' . $_SERVER['PHP_SELF'] . '?name=' . $state . '&city=' . $city . '&category=' . $catID . '&pn=' . $nextPage . '" class="backNextLink"> Next</a> ';
      } 
    }
    ############################End Pagination Logic#############################
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
    
	h1, h2 {
      clear: both;
      text-align: center;
    }
    
    .list_table {
      clear: both;
      margin: auto;
      overflow: hidden;
      table-layout:fixed;
      white-space:nowrap;
    }
    
    .list_pic {
      width: 80px;
      height: 80px;
    }
    
    .list_info {
      width: 300px;
      padding: 10px;
      font-size: 18px;
    }
    
    .list_info a {
      background: #348075;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      padding-left: 3px;
      padding-right: 3px;
    }
    
    #content {
      clear: both;
      width: 500px;
      margin: auto;
      background-color: white;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
      padding: 10px;
    }
    
    #paginationDisplay {
      margin-top: 30px;
      margin-bottom: 30px;
      text-align: center;
      font-size: 18px;
    }
    
    #paginationDisplay a {
      background: #348075;
      padding-left: 5px;
      padding-right: 3px;
      -moz-border-radius: 5px;
      -webkit-border-radius: 5px;
      color: white;
      border: none;
    }
  </style>
</head>
<body>
  <?php if (isset($catName)) : ?>
    <div id="breadcrumb"><a href="index.php">home</a> >> <a href="cities.php?name=<?php echo $state; ?>"><?php echo $state; ?></a> >> <a href="home.php?name=<?php echo $state; ?>&city=<?php echo $city; ?>"><?php echo $city; ?></a> >> <?php echo $catName; ?></div>
  <?php endif; ?>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <h1><?php echo isset($catName) ? $catName : ''; ?> listings</h1>
  <div id="content">
  <?php 
    if (isset($limit)) :
      //echo 'STATE: ' . $state . ' CITY: ' .  $city . ' CATID: ' . $catID . ' LIMIT: ' . $limit;
     $sqlGetLists = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC $limit");
     $sqlCountLists = mysqli_num_rows($sqlGetLists);
     //echo '<br />' . $sqlCountLists;
     $i = $sqlCountLists;
     if ($sqlCountLists > 0) :
       while ($row = mysqli_fetch_array($sqlGetLists)) :
         $listID = $row['id'];
         $time = strtotime($row['time']);
         $time = date("D M j g:i a", $time);
         $resPrice = $row['reserve_price'];
         $buyPrice = $row['buy_price'];
         $title = strtolower($row['title']);
         $photo = $row['photo_name'];
      
         $photo != '' ? $photoPath = '<div style="overflow:hidden; height: 110px;"><a href="listing.php?id=' . $listID . '"><img src="media/thumb_' . $photo . '" width="110px" border="0" /></a></div>' : $photoPath = '<div style="overflow:hidden; height: 110px; width: 110px;">';
  ?>
    <table class="list_table">
      <tr>
    	<td class="list_pic">
    	  <?php echo $photoPath; ?> 
    	</td>
    	<td class="list_info">
    	  <strong><?php echo $catName; ?></strong><br />
    	  <a href="listing.php?id=<?php echo $listID; ?>"><strong><?php echo $title; ?></strong></a><br />
    	  <strong>expires:</strong> <?php echo strtolower($time); ?><br />
    	  <?php echo $buyPrice > 0 ? '<strong>buy it now:</strong> $' . $buyPrice . '.00' : ''; ?>&nbsp;&nbsp;<?php echo $resPrice > 0 ? '<strong>reserve:</strong> $' . $resPrice . '.00' : ''; ?> <br /> 
    	  <?php $i--; ?>
    	</td>
      </tr>
    </table>
  <?php     
              echo $i > 0 ? '<hr />' : '';
            endwhile; 
          else : 
  ?>
    <h2>no auctions listed in this category</h2>
  <?php
  		  endif;
        endif; ?>
  </div>
  <?php if ((isset($paginationDisplay)) && ($sqlCountLists > 19)) {
  			echo '<div id="paginationDisplay">' . $paginationDisplay . '</div>'; 
  		}
  ?>
</body>
</html>