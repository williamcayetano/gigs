<?php
  include_once("scripts/connectToMySQL.php");
  
  $todaysDateTime = time();
  $morning = mktime(0, 0, 0, date("m"), date("d"), date("y"));
  $midnight = mktime(0, 0, 0, date("m"), date("d")+1, date("y"));
  $sqlMorning = date("Y-m-d H:i:s", $morning);
  $sqlMidnight = date("Y-m-d H:i:s", $midnight);
    
  $ipAddress = getenv('REMOTE_ADDR');
  $sqlTotalIP = mysqli_query($link, "SELECT * FROM logs");
  $totalIPCount = mysqli_num_rows($sqlTotalIP);
  $sqlTotalIPMinus = mysqli_query($link, "SELECT * FROM logs WHERE ipaddress NOT LIKE '$ipAddress'");
  $totalMinusCount = mysqli_num_rows($sqlTotalIPMinus);
  $sqlTotalDistinct = mysqli_query($link, "SELECT DISTINCT ipaddress FROM logs");
  $totalDistinctCount = mysqli_num_rows($sqlTotalDistinct);
    
  $sqlTotalDay = mysqli_query($link, "SELECT * FROM logs WHERE post_date BETWEEN '$sqlMorning' AND '$sqlMidnight'");
  $totalDay = mysqli_num_rows($sqlTotalDay);
  $sqlDistinctDay = mysqli_query($link, "SELECT DISTINCT ipaddress FROM logs WHERE post_date BETWEEN '$sqlMorning' AND '$sqlMidnight'");
  $totalDistinctDay = mysqli_num_rows($sqlDistinctDay);
   
  $formatPostTime = date("m/d/y"); 
    
  $logTable = '<table class="log_table" border="1" cellpadding="5">
     			   <tr> 
     			     <td class="column_header">
     			       <strong>Date</strong>
     			     </td>
     			     <td class="column_header">
     			       <strong>Total Hits</strong>
     			     </td>
     			     <td class="column_header">
     			       <strong>Total Hits Minus</strong>
     			     </td>
     			     <td class="column_header">
     			       <strong>Total Distinct</strong>
     			     </td>
     			     <td class="column_header">
     			       <strong>Total Today</strong>
     			     </td>
     			     <td class="column_header">
     			       <strong>Total Distinct Today</strong>
     			     </td>
     			   </tr>
     			   <tr>
     			     <td class="column_data">
     			       ' . $formatPostTime . '
     			     </td>
     			     <td class="column_data">
     			       ' . $totalIPCount . '
     			     </td>
     			     <td class="column_data">
     			       ' . $totalMinusCount . '
     			     </td>
     			     <td class="column_data">
     			       ' . $totalDistinctCount . '
     			     </td>
     			     <td class="column_data">
     			       ' . $totalDay . '
     			     </td>
     			     <td class="column_data">
     			       ' . $totalDistinctDay . '
     			     </td>
     			   </tr>
    			 </table>';
?>

<html>
<head>
  <style>
  .column_data {
    text-align: center;
  }
  </style>
</head>
  <body>
    <a href="index.php">Home</a>
    <?php echo isset($logTable) ? $logTable : ''; ?>
  </body>
</html>