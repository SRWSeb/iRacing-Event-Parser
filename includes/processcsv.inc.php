<?php

if(!isset($_POST['process-csv'])) {
  header("Location: ../entercsv.php");
  exit();
}

require 'dbh.inc.php';

function checkDoubles($conn, $checksum) {
  //Get field checksum from table events if the provided checksum matches
  $stmt = "SELECT checksum FROM events WHERE checksum=\"$checksum\"";
  $result = $conn->query($stmt);
  $result = $result->fetch_assoc();
  //If we get a result, we obviously have a double and return true.
  if($result) {
    return true;
  }
  //If otherwise we had no match and no double. So we return false.
  return false;
}


$input = file($_FILES['inputcsv']['tmp_name']);
//Create Checksum to verify doubles;
$checksum = "'".sha1_file($_FILES['inputcsv']['tmp_name'])."'";
if(checkDoubles($conn, $checksum)) {
  header("Location: ../entercsv.php?error=double");
  exit();
}


//Remove first line, don't need that
array_shift($input);
//Extract Event Infos into
$eventInfo = str_getcsv(array_shift($input));
//Remove third-fifth line, not needed
array_shift($input);
array_shift($input);
array_shift($input);
array_shift($input);
//Extract League Info into array
$leagueInfo = str_getcsv(array_shift($input));
//Remove seventh & eighth line, not needed
array_shift($input);
array_shift($input);

echo "<b>".$eventInfo[1]."</b><br>";
echo "<b>".$leagueInfo[0]."</b><br><br>";

foreach ($input as $key => $value) {
  echo $value."<br>";
}

if($double) {
  echo "<b>Double!</b>";
}
