<?php
  if (!isset($_SESSION['idx'])) {
    header("location: index.php");
  }
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
  <h3>Your task has been posted!</h3>
  <a href="index.php">Home</a>
</body>
</html>


   /* var range = 20;
    function yHandler(){
	  var wrap = document.getElementById('wrap');
	  var contentHeight = wrap.offsetHeight;
	  var yOffset = window.pageYOffset; 
	  var y = yOffset + window.innerHeight;
	  if(y >= contentHeight){
	    $.get('listings.php?name=<?php echo $state; ?>&city=<?php echo $city; ?>&category=<?php echo $catID; ?>&range='+range, function(data) {
		wrap.innerHTML += '<div class="newData">'+data+'</div>';
		range += 20;
	    });
	  }
    }
    window.onscroll = yHandler;*/