<?php
  if (isset($_GET['name'])) {
    $name = $_GET['name'];
  }
?>

<!DOCTYPE html>
<html>
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
    
    #main_image {
      padding: 70px 65px 65px 65px;
      background-color: white;
      display: block;
      margin-left:auto; 
      margin-right:auto;
      -moz-border-radius: 20px;
      -webkit-border-radius: 20px;
	}
  </style>
</head>
<body>
  <?php echo isset($name) ? '<img id="main_image" src="media/' . $name . '" />' : ''; ?>
</body>
</html>