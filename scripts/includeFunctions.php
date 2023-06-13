<?php
//add sort by new to main app
  include_once("connectToMySQL.php");
  
  $header = 'gign\'good';
  $slogan = 'bid on gigs! bid on goods! from my hood to your hood, we\'re gign\' good!';
  
  //state
  $stateArray = array('alabama', 'alaska', 'arizona', 'arkansas', 'california', 'colorado', 'connecticut', 'delaware', 
  					  'florida', 'georgia', 'hawaii', 'idaho', 'illinois', 'indiana', 'iowa', 'kansas', 'kentucky',
  					  'louisiana', 'maine', 'maryland', 'massachusetts', 'michigan', 'minneapolis', 'mississippi', 
  					  'missouri', 'montana', 'nebraska', 'nevada', 'new hampshire', 'new jersey', 'new mexico', 'new york',
  					  'north carolina', 'north dakota', 'ohio', 'oklahama', 'oregon', 'pennsylvania', 'rhode island', 
  					  'south carolina', 'south dakota', 'tennessee', 'texas', 'utah', 'vermont', 'virginia', 'washington',
  					  'west virginia', 'wisconsin', 'wyoming');
  //country					  
  $europeArray = array('albania', 'andorra', 'austria', 'belarus', 'belgium', 'bosnia and herzegovina', 
  					   'bulgaria', 'croatia', 'cyprus', 'czech republic', 'denmark', 'estonia', 'finland', 'france', 'georgia (country)',
  					   'germany', 'greece', 'hungary', 'iceland', 'ireland', 'italy', 'kosovo', 'latvia', 'liechtenstein', 
  					   'lithuania', 'luxembourg', 'macedonia', 'malta', 'moldova', 'monaco', 'montenegro', 'the netherlands',
  					   'norway', 'poland', 'portugal', 'romania', 'russia', 'san marino', 'serbia', 'slovakia', 'slovenia', 
  					   'spain', 'sweden', 'switzerland', 'turkey', 'ukraine', 'united kingdom', 'vatican city');
  //province					   
  $canadaArray = array('alberta', 'british columbia', 'manitoba', 'new brunswick', 'newfoundland and labrador', 'northwest territories', 
  					   'nova scotia', 'nunavut', 'ontario', 'prince edward island', 'quebec', 'saskatchewan', 'yukon');
  //country		   
  $asiaArray = array('afghanistan', 'armenia', 'azerbaijan', 'bahrain', 'bangladesh', 'bhutan', 'brunei', 'burma', 'cambodia',
  						'china', 'hong kong', 'india', 'indonesia', 'iran', 'iraq', 'israel', 'japan', 'jordan',
  						'kazakhstan', 'korea, north', 'korea, south', 'kuwait', 'kyrgyzstan', 'laos', 'lebanon', 'macau', 'malaysia',
  						'maldives', 'mongolia', 'nepal', 'oman', 'pakistan', 'palestine', 'philippines', 'qatar', 'saudi arabia', 
  						'singapore', 'sri lanka', 'syria', 'taiwan', 'tajikistan', 'thailand', 'turkmenistan', 'united arab emirates',
  						'uzbekistan', 'vietnam', 'yemen');
  //country					
  $oceaniaArray = array('australia', 'fiji', 'fed. states micronesia', 'french polynesia', 'guam', 'new caledonia', 'new zealand', 
  						'papua new guinea', 'samoa', 'soloman islands', 'timor leste', 'tonga', 'vanuata');
  //country				
  $latinArray = array('anguilla', 'antigua and barbuda', 'argentina', 'aruba', 'bahamas', 'barbados', 'belize', 'bolivia', 'brazil', 'cayman islands', 
  					  'chile', 'colombia', 'costa rica', 'cuba', 'curacao', 'dominican republic', 'ecuador', 'el salvador', 'french guyana',
  					  'grenada', 'guatemala', 'guyana', 'haiti', 'honduras', 'jamaica', 'martinique', 'mexico', 'nicaragua', 'panama', 'paraguay',
  					  'peru', 'puerto rico', 'saint kitts and nevis', 'saint lucia', 'suriname', 'trinidad and tobago', 'uruguay', 'venezuela', 'virgin islands');
  					  
  //country
  $africaArray = array('algeria', 'angola', 'benin', 'botswana', 'burkina faso', 'burundi', 'cameroon', 'canary islands', 'cape verde', 'central african republic', 
  					   'chad', 'comoros', 'congo', 'cote d Ivoire', 'djibouti', 'egypt', 'equatorial guinea', 'eritrea', 'ethiopia', 'gabon', 'gambia', 'ghana', 'guinea',
  					   'guinea-bissau', 'kenya', 'lesotho', 'liberia', 'libya', 'madagascar', 'madeira', 'malawi', 'mali', 'mauritania', 'mauritius', 'mayotte', 'morocco', 
  					   'mozambique', 'namibia', 'niger', 'nigeria', 'reunion', 'rwanda', 'sao tome and principe', 'senegal', 'seychelles', 'sierra leone', 'somalia', 
  					   'south africa', 'south sudan', 'sudan', 'swaziland', 'tanzania', 'togo', 'tunisia', 'uganda', 'zambia', 'zimbabwe');
  						
  $regionArray = array('united states of america', 'canada', 'europe', 'asia/middle east', 'latin america/carribean', 'africa', 'oceania');
  					  
  $countryArray = array('United States of America', 'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 
  						'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bonaire', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 
  						'Canada', 'Canary Islands', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Channel Islands', 'Chile', 'China', 'Christmas Island', 'Cocos Island', 'Colombia', 'Comoros', 'Congo', 'Cook Islands', 'Costa Rica', 'Cote D\'Ivoire',
                        'Croatia', 'Cuba', 'Curacao', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia',
                        'Ethiopia', 'Falkland Islands', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe',
                        'Guam', 'Guatemala', 'Guinea', 'Guyana', 'Haiti', 'Hawaii', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 
                        'Kenya', 'Kiribati', 'Korea North', 'Korea South', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia', 'Madagascar', 'Malaysia', 'Malawi', 'Maldives', 
                        'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritius', 'Mayotte', 'Mexico', 'Moldova', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Nambia', 'Nauru', 'Nepal', 'Netherland Antilles', 'Netherlands', 'Nevis', 'New Caledonia', 
                        'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Norway', 'Oman', 'Pakistan', 'Palau Island', 'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn Island', 'Poland', 'Portugal', 'Puerto Rico', 
                        'Qatar', 'Réunion', 'Romania', 'Russia', 'Rwanda', 'St Barthelemy', 'St Eustatius', 'St Helena', 'St Kitts-Nevis', 'St Lucia', 'St Maarten', 'St Pierre and Miquelon', 'St Vincent and Grenadines', 'Saipan', 'Samoa', 'Samoa American', 'San Marino', 
                        'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Seychelles', 'Serbia and Montenegro', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Swaziland', 
                        'Sweden', 'Switzerland', 'Syria', 'Tahiti', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Is', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 
                        'United Kingdom', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City State', 'Venezuela', 'Vietnam', 'Virgin Islands Brit', 'Virgin Islands USA', 'Wake Island', 'Wallis and Futana Is', 'Yemen', 'Zaire', 'Zambia', 'Zimbabwe');
  
  function convertDatetime($str) {
    list($date, $time) = explode(' ', $str);
    list($year, $month, $day) = explode('-', $date);
    list($hour, $minute, $second) = explode(':', $time);
    $timeStamp = mktime($hour, $minute, $second, $month, $day, $year);
    return $timeStamp;
  }
    
  function makeAgo($timeStamp) {
    $difference = time() - $timeStamp;
    $periods = array("sec", "min", "hr", "day", "week", "month", "year", "decade");
    $lengths = array("60","60","24","7","4.35","12","10");
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++)
   	  $difference /= $lengths[$j];
   	  $difference = round($difference);
    if($difference != 1) $periods[$j].= "s";
   	  $text = "$difference $periods[$j] ago";
   	  return $text;
  }
  
  function pagination($count, $page, $items) {
    $numberRows = $count;
    $pageNumber = $page;
    $itemsPerPage = $items;

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
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex.page 2
    } else if ($pageNumber == $lastPage) { //should be no forward button because you're already on last page
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 29
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 30 (current page)
    } else if ($pageNumber > 2 && $pageNumber < ($lastPage - 1)) { //set how many clickable numbers inbetween next and back buttons
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $sub2 . '">' . $sub2 . '</a> &nbsp;'; //ex. page 5
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 6
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 7 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex. page 8
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $add2 . '">' . $add2 . '</a> &nbsp;'; //ex. page 9
    } else if ($pageNumber > 1 && $pageNumber < $lastPage) { //on next to last page, just show last page number after current. one on each side. notice add1
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $sub1 . '">' . $sub1 . '</a> &nbsp;'; //ex. page 28
	  $centerPages .= '&nbsp; <span class="pagNumActive">' . $pageNumber . '</span> &nbsp;'; //ex. page 29 (current page)
	  $centerPages .= '&nbsp; <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $add1 . '">' . $add1 . '</a> &nbsp;'; //ex. page 30 (last page)
    }
  
    //Ex. LIMIT 20 returns first 20 results; LIMIT 20, 5 returns 5 results(20, 21, 22, 23, 24)
    //subtract 1 since sql counts from 0
    $limit = ($pageNumber - 1) * $itemsPerPage; 
  
    $paginationDisplay = ""; 
    //if only 1 page we require no paginated links to display (so none of this code will run)
    if ($lastPage != "1"){
      // This shows the user what page they are on, and the total number of pages.
      $paginationDisplay .= 'Page <strong>' . $pageNumber . '</strong> of ' . $lastPage. '&nbsp;&nbsp;';
	  // If we are not on page 1 we can place the Back link
      if ($pageNumber != 1) {
	      $previous = $pageNumber - 1;
		  $paginationDisplay .=  '&nbsp;  <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $previous . '" class="backNextLink"> Back</a> ';
      } 
      // Lay in the clickable numbers display here between the Back and Next links
      $paginationDisplay .= '<span class="paginationNumbers">' . $centerPages . '</span>';
      // If we are not on the very last page we can place the Next link
      if ($pageNumber != $lastPage) {
        $nextPage = $pageNumber + 1;
		$paginationDisplay .=  '&nbsp;  <a href="' . $_SERVER['PHP_SELF'] . '?pn=' . $nextPage . '" class="backNextLink"> Next</a> ';
      } 
    }
    
    return $paginationDisplay;
  }
  
  function clean($str) {
    global $link;
    $str = preg_replace("#['`]#", "&#039;", $str);
    $str = stripslashes($str);
    $str = htmlspecialchars($str);
    return mysqli_real_escape_string($link, $str);
  }
  
  function dropDown($options_array, $selected = null) 
  { 
    $return = '<option value="'.$selected.'">'.$selected.'</option>'; 
      foreach($options_array as $option) 
      { 
        if ($option != $selected) {
          $return .= '<option value="'.$option.'">'.$option.'</option>';
        }
      } 
      return $return; 
  }
  
  function getUserAgent() {
    $user_device = "";
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match("/iPhone/", $agent)) {
      $user_device = "iPhone Mobile";
    } else if (preg_match("/Android/", $agent)) {
      $user_device = "Android Mobile";
    } else if (preg_match("/IEMobile/", $agent)) {
      $user_device = "Windows Mobile";
    } else if (preg_match("/Chrome/", $agent)) {
      $user_device = "Google Chrome";
    } else if (preg_match("/MSIE/", $agent)) {
      $user_device = "Internet Explorer";
    } else if (preg_match("/Firefox/", $agent)) {
      $user_device = "Firefox";
    } else if (preg_match("/Safari/", $agent)) {
      $user_device = "Safari";
    } else if (preg_match("/Opera/", $agent)) {
      $user_device = "Opera";
    } else {
      $user_device = "Unknown Agent";
    }

    $OSList = array
    (
        // Match user agent string with operating systems
        'Windows 3.11' => 'Win16',
        'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
        'Windows 98' => '(Windows 98)|(Win98)',
        'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
        'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
        'Windows Server 2003' => '(Windows NT 5.2)',
        'Windows Vista' => '(Windows NT 6.0)',
        'Windows 7' => '(Windows NT 6.1)|(Windows NT 7.0)',
        'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
        'Windows ME' => 'Windows ME',
        'Open BSD' => 'OpenBSD',
        'Sun OS' => 'SunOS',
        'Linux' => '(Linux)|(X11)',
        'Mac OS' => '(Mac_PowerPC)|(Macintosh)',
        'QNX' => 'QNX',
        'BeOS' => 'BeOS',
        'OS/2' => 'OS/2',
		'Mac OS' => 'Mac OS',
        'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)'
    );
 
    // Loop through the array of user agents and matching operating systems
    foreach($OSList as $CurrOS=>$Match) {
      // Find a match
      if (preg_match("/$Match/i", $agent)) {
        break;
      } else {
	    $CurrOS = "Unknown OS";
	  }
    }
    $device = "$user_device : $CurrOS";
    return $device;
  }
  
  function htmlkarakter($string) { 
     $string = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;','&lt;', '&gt;'), array("<", ">",'&','\'','"','<','>'), htmlspecialchars_decode($string, ENT_NOQUOTES)); 
     return $string; 
  }
  
  function getUsername($userID) {
    global $link;
    $username = "";
    $firstName = "";
    $lastName = "";
   	$sqlGetUserName = mysqli_query($link, "SELECT username, first_name, last_name FROM users WHERE id='$userID' LIMIT 1");
    while ($row = mysqli_fetch_array($sqlGetUserName)) {
      $username = $row['username'];
      $firstName = htmlkarakter($row['first_name']);
      $lastName = htmlkarakter($row['last_name']);
    }
    
      //check if first, last name replaced
    if ($firstName != "" && $lastName != "") {
      $completeName = $firstName . ' ' . $lastName;
    } else {
      $completeName = $username;
    }
    return $completeName;
  }