<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/checkuserlog.php");
  include_once("scripts/includeFunctions.php");
  
  function pageString($search, $state = null, $city = null) {
    if (!$state) {
      return "search=$search";
    } else if (!$city) {
      return 'search=' . $search . '&name=' . $state;
    } else {
      return 'search=' . $search . '&name=' . $state . '&city=' . $city;
    }
  }
  
  if (isset($_GET['search']) && $_GET['search'] != '') {
    //if isset state, if isset city hidden values
    //Pattern Matching
    //SELECT * FROM table WHERE field LIKE %$searchquery%
    /*This type of search method will give exact results where 
    $searchquery is found. You can order results by date field or any other
    
    //FullText Search
    //SELECT * FROM table WHERE MATCH (field1,field2) AGAINST ('$searchquery')
    /*MATCH function performs a natural language search for a 
    string against a text collection. A collection is a of one
    or more columns in a FULLTEXT index. Matches more similar will appear higher*/
    
    //Union / As
    //(SELECT page_title AS title FROM pages) UNION (SELECT blog_title AS title FROM pages)
    /*AS makes title an alias for both page_title and blog_title, and then queries title*/
    
    $search = clean($_GET['search']);
    $sqlQuery = '';
    $pageString = '';
    $links = '';
    
    if (isset($_GET['name'])) {
      $state= clean($_GET['name']);
      $city= '';
      
      if (isset($_GET['city'])) {
        $city = clean($_GET['city']);
      }
      
      if ($state != '' && $city != '') {
        $sqlQuery = "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND active='y' AND title LIKE '%$search%' OR description LIKE '%$search%' ORDER BY time ASC";
        $pageString = pageString($search, $state, $city);
        $links = '<a href="index.php">home</a> >> <a href="cities.php?name=' . $state . '">' . $state . '</a> >> <a href="home.php?name=' . $state . '&city=' . $city . '">' . $city . '</a> >> ' . $search;
      } else if ($state != '') {
        $sqlQuery = "SELECT * FROM listings WHERE country_state='$state' AND active='y' AND title LIKE '%$search%' OR description LIKE '%$search%' ORDER BY time ASC";
        $pageString = pageString($search, $state);
        $links = '<a href="index.php">home</a> >> <a href="cities.php?name=' . $state . '">' . $state . '</a> >> ' . $search;
      } else {
        $msg = 'No Results';
        $pageString = pageString($search);
        $links = '<a href="index.php">home</a> >> ' . $search;
      }
    } else {
      $sqlQuery = "SELECT * FROM listings WHERE active='y' AND title LIKE '%$search%' OR description LIKE '%$search%' ORDER BY time ASC";
      $pageString = pageString($search);
      $links = '<a href="index.php">home</a> >> ' . $search;
    }
    
    $sqlSearch = mysqli_query($link, $sqlQuery);
    $sqlSearchCount = mysqli_num_rows($sqlSearch);
    
    ############################Begin Pagination Logic#############################
    $numberRows = $sqlSearchCount;
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
    } else if ($pageNumber > $lastPage) { 
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
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex.page 2
    } else if ($pageNumber == $lastPage) { //should be no forward button because you're already on last page
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 29
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 30 (current page)
    } else if ($pageNumber > 2 && $pageNumber < ($lastPage - 1)) { //set how many clickable numbers inbetween next and back buttons
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $sub2 . '">' . $sub2 . '</a> &nbsp;'; //ex. page 5
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 6
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 7 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex. page 8
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $add2 . '">' . $add2 . '</a> &nbsp;'; //ex. page 9
    } else if ($pageNumber > 1 && $pageNumber < $lastPage) { //on next to last page, just show last page number after current. one on each side. notice add1
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 28
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 29 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex. page 30 (last page)
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
		$paginationDisplay .=  '&nbsp;  <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $previous . '" class="backNextLink"> Back</a> ';
      } 
      // Lay in the clickable numbers display here between the Back and Next links
      $paginationDisplay .= '<span class="paginationNumbers">' . $centerPages . '</span>';
      // If we are not on the very last page we can place the Next link
      if ($pageNumber != $lastPage) {
        $nextPage = $pageNumber + 1;
		$paginationDisplay .=  '&nbsp;  <a href="' . $_SERVER['PHP_SELF'] . '?' . $pageString . '&pn=' . $nextPage . '" class="backNextLink"> Next</a> ';
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
    
	h2 {
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
      border: none;*/
    }
    
    #header {
      font-size: 30px;
      font-weight: bold;
    }
    
    #slogan {
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div id="header"><?php echo $header; ?></div>
  <div id="slogan"><?php echo $slogan; ?></div><br />
  <?php if (isset($search)) { echo '<div id="breadcrumb">' . $links . '</div>'; } ?>
  <div id="log_options"><?php echo $logOptions; ?></div>
  <h2><?php echo isset($sqlSearchCount) ? $sqlSearchCount . ' results for ' . $search : ''; ?></h2>
  <div id="content">
  <?php 
    if (isset($limit)) :
     $sqlQuery = $sqlQuery . ' ' . $limit;
     $sqlGetLists = mysqli_query($link, $sqlQuery);
     $i = mysqli_num_rows($sqlGetLists);
     while ($row = mysqli_fetch_array($sqlGetLists)) :
       $listID = $row['id'];
       $catID = $row['category_id'];
       $time = strtotime($row['time']);
       $time = date("D M j g:i a", $time);
       $resPrice = $row['reserve_price'];
       $buyPrice = $row['buy_price'];
       $title = $row['title'];
       $photo = $row['photo_name'];
       $catName = '';
      
       $photo != '' ? $photoPath = '<div style="overflow:hidden; height: 60px;"><a href="listing.php?id=' . $listID . '"><img src="media/thumb_' . $photo . '" width="60px" border="0" /></a></div>' : $photoPath = '';
       
       $sqlGetCat = mysqli_query($link, "SELECT name FROM categories WHERE id='$catID' AND active='y'");
       while ($row2 = mysqli_fetch_array($sqlGetCat)) { 
         $catName = $row2['name']; 
         $kaboom = explode(' - ', $catName);
    	 $catName = $kaboom[1];
       }
  ?>
    <table class="list_table">
      <tr>
    	<td class="list_pic">
    	  <?php echo $photoPath; ?> 
    	</td>
    	<td class="list_info">
    	  <strong><?php echo $catName; ?></strong><br />
    	  <a href="listing.php?id=<?php echo $listID; ?>"><strong><?php echo $title; ?></strong></a><br />
    	  <strong>expires:</strong> <?php echo $time; ?><br />
    	  <?php echo $buyPrice > 0 ? 'buy it now: $' . $buyPrice . '.00' : ''; ?>&nbsp;&nbsp;<?php echo $resPrice > 0 ? 'reserve: $' . $resPrice . '.00' : ''; ?> <br /> 
    	  <?php $i--; ?>
    	</td>
      </tr>
    </table>
  <?php   
            echo $i > 0 ? '<hr />' : '';
          endwhile; 
        endif; ?>
  </div><!--end content-->
  <?php echo isset($paginationDisplay) ? '<div id="paginationDisplay">' . $paginationDisplay . '</div>' : ''; ?>
</body>
</html>