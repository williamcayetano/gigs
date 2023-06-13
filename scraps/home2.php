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
    $locName = '';
    
    //set cookie
    if (!isset($_COOKIE['auctionLocation'])) {
      $location = $state . '`' . $city;
      $expire = time()+60*60*24*30; //one month (60 sec * 60 min * 24 hours * 30 days)
      setcookie('auctionLocation', $location, $expire, '/');
    }
  } else {
    header('location: index.php');
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title>OddJobr</title>
</head>
  <style>
    .category, td {
      width: 200px;
      margin-bottom: 20px;
      overflow: hidden;
      table-layout:fixed;
      white-space:nowrap;
    }
    h2 { clear: both; }
    
    body { 
      background: #f0f0f0; 
      font-weight: bold;
    }
    
    a { text-decoration: none; }
    
    td { text-align: center; }
    
    td a { 
      color: black; 
      display: block;
      width: 100%;
      height: 100%;
    }
    
    td a:hover { background: #d0d0d0; }
    
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
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script src="scripts/jquery.masonry.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#service_requests').masonry({
        itemSelector: '.category',
        columnWidth: 220,
      });
      
      $('#services_offered').masonry({
        itemSelector: '.category',
        columnWidth: 220,
      });
      
      $('#goods').masonry({
        itemSelector: '.category',
        columnWidth: 220,
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
  
  <h2>service requests</h2>
  <div id="service_requests">
  <?php 
    $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE id <= 9");
    while ($row = mysqli_fetch_array($sqlGetCat)) :
      $name = $row['name'];
      $kaboom = explode(' - ', $name);
    
  ?>
    <table class="category">
      <tr>
        <th>
          <?php echo $kaboom[1]; ?>
        </th>
      </tr>
      <?php 
        $catID = $row['id'];
        $i = 0;
        //service request id between 1-9 
        $sqlGetLists = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC");
        $sqlCountLists = mysqli_num_rows($sqlGetLists);
        for ($i = 0; $row2 = mysqli_fetch_array($sqlGetLists); $i++) :
          if ($i < 5) :
      ?> 
      <tr>
        <td class="cat_overflow">
          <a href="listing.php?id=<?php echo $row2['id']; ?>"><?php echo strtolower($row2['title']); ?></a>
        </td>
      </tr>
          <?php endif; ?>
        <?php endfor; ?>
      <?php echo ($sqlCountLists > 5) ? '<tr><td><a href="listings.php?name=' . $state . '&city=' . $city . '&category=' . $catID . '">More</a></td></tr>' : ''; ?>
    <?php endwhile; ?>
    </table>
  </div>
  
  <h2>services offered</h2>
  <div id="services_offered">
  <?php 
    $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE id > 40");
    while ($row = mysqli_fetch_array($sqlGetCat)) :
      $name = $row['name'];
      $kaboom = explode(' - ', $name);
  ?>
    <table class="category">
      <tr>
        <th>
          <?php echo $kaboom[1]; ?>
        </th>
      </tr>
      <?php 
        $catID = $row['id'];
        $i = 0;
        //service request id between 1-9 
        $sqlGetLists = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC");
        $sqlCountLists = mysqli_num_rows($sqlGetLists);
        for ($i = 0; $row2 = mysqli_fetch_array($sqlGetLists); $i++) :
          if ($i < 5) :
      ?> 
      <tr>
        <td class="cat_overflow">
          <a href="listing.php?id=<?php echo $row2['id']; ?>"><?php echo strtolower($row2['title']); ?></a>
        </td>
      </tr>
          <?php endif; ?>
        <?php endfor; ?>
      <?php echo ($sqlCountLists > 5) ? '<tr><td><a href="listings.php?name=' . $state . '&city=' . $city . '&category=' . $catID . '">More</a></td></tr>' : ''; ?>
    <?php endwhile; ?>
    </table>
  </div>
  
  <h2>goods</h2>
  <div id="goods">
  <?php 
    $sqlGetCat = mysqli_query($link, "SELECT * FROM categories WHERE id BETWEEN 10 AND 40");
    while ($row = mysqli_fetch_array($sqlGetCat)) :
      $name = $row['name'];
      $kaboom = explode(' - ', $name);
  ?>
    <table class="category">
      <tr>
        <th>
          <?php echo $kaboom[1]; ?>
        </th>
      </tr>
      <?php 
        $catID = $row['id'];
        $i = 0;
        //service request id between 1-9 
        $sqlGetLists = mysqli_query($link, "SELECT * FROM listings WHERE country_state='$state' AND city='$city' AND category_id='$catID' AND active='y' ORDER BY time ASC");
        $sqlCountLists = mysqli_num_rows($sqlGetLists);
        for ($i = 0; $row2 = mysqli_fetch_array($sqlGetLists); $i++) :
          if ($i < 5) :
      ?> 
      <tr>
        <td class="cat_overflow">
          <a href="listing.php?id=<?php echo $row2['id']; ?>"><?php echo strtolower($row2['title']); ?></a>
        </td>
      </tr>
          <?php endif; ?>
        <?php endfor; ?>
      <?php echo ($sqlCountLists > 5) ? '<tr><td><a href="listings.php?name=' . $state . '&city=' . $city . '&category=' . $catID . '">More</a></td></tr>' : ''; ?>
    <?php endwhile; ?>
    </table>
  </div>
  <?php endif; ?>
</body>
</html>