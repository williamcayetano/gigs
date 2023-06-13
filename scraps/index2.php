<?php
  include_once("scripts/connectToMySQL.php");
  include_once("scripts/includeFunctions.php");
  include_once("scripts/checkuserlog.php");
  
?>

<!DOCTYPE html>
<html>
<head>
  <title>OddJobr</title>
</head>
  <style>
    .state {
      width: 300px;
      margin-bottom: 40px;
    }
  </style>
  <script src="scripts/jquery-1.8.1.min.js"></script>
  <script src="scripts/jquery.masonry.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#usa').masonry({
        itemSelector: '.state',
        columnWidth: 350,
      });
    });
  </script>
<body>
  <h2>United States of America</h2>
  <div id="usa">
  <?php 
    foreach ($stateArray as $state) : 
      $sqlGetCity = mysqli_query($link, "SELECT city FROM listings WHERE country='United States of America' AND state='$state' AND active='y'");
  ?>
    <table border="1" class="state">
      <tr>
        <th colspan="2">
          <?php echo $state; ?>
        </th>
      </tr>
      <?php 
        $cityArray = array();
        while ($row = mysqli_fetch_array($sqlGetCity)) {
          $cityArray[] = strtolower($row['city']);
        }
        $uniqueCities = array_count_values($cityArray);
        
        if (count($uniqueCities) > 0) :
          foreach ($uniqueCities as $key => $value) :
      ?>
      <tr>
        <td><?php echo $key; ?></td>
        <td><?php echo $value; ?> Auction(s)</td>
      </tr>
      <?php endforeach;
           else: 
      ?>
      <tr>
        <td>0 auctions listed</td>
      </tr>
      <?php endif; 
        unset($cityArray); ?>   
  <?php endforeach; ?>
  </div>
</body>
</html>