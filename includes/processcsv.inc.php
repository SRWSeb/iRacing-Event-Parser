<?php

if(!isset($_POST['process-csv'])) {
  header("Location: ../entercsv.php");
  exit();
}

require 'dbh.inc.php';

//Takes day and time straight out of the results csv and returns it in a MySQL compatible format.
function parseDate($datetime) {
  //Get rid of the GMT
  $datetime = chop($datetime,"GMT");
  //Parse Date into usable array
  $datetime = date_parse_from_format("Y.m.d h:i a",$datetime);
  //Build string for MySQL and return it
  return $datetime['year'] . "-" . $datetime['month'] . "-" . $datetime['day'] . " " . $datetime['hour'] . ":" . $datetime['minute'] . ":00";
}

//Checks the results table if the checksum is already in there. Returns true for a double, false for no double.
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

//Checks the drivers table if driver is already in the database
function checkDriverExists($conn, $iracingid) {
  $sql = "SELECT iracing_name FROM drivers WHERE iracing_id=?";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)) {
    header("Location: ../entercsv.php?error=sqlerror");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "i", $iracingid);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  $rows = mysqli_stmt_num_rows($stmt);
  mysqli_stmt_free_result($stmt);
  mysqli_stmt_close($stmt);

  if($rows > 0) {
    return true;
  }
  return false;
}

//Enters a new driver into the database.
function newDriver($conn, $data) {
  $iracingid = $data[6];
  $name = $data[7];

  $sql = "INSERT INTO drivers (iracing_id, iracing_name, display_name) VALUES (?, ?, ?)";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)) {
    header("Location: ../entercsv.php?error=sqlerror");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "iss", $iracingid, $name, $name);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

}

//Get intern ID of car
function getCarID($conn, $iracing_car_id) {
  $sql = "SELECT * FROM cars WHERE iracing_car_id=?";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)) {
    header("Location: ../entercsv.php?error=sqlerror");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "i", $iracing_car_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row =mysqli_fetch_array($result);
  return $row['id'];
}

//Get inter ID of driver
function getDriverID($conn, $iracing_id) {
  $sql = "SELECT * FROM drivers WHERE iracing_id=?";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)) {
    header("Location: ../entercsv.php?error=sqlerror");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "i", $iracing_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row =mysqli_fetch_array($result);
  return $row['id'];
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

$stmt_event = $conn->prepare("INSERT INTO events (checksum, time_and_day, league_id, season_id) VALUES (?, ?, ?, ?)");
$stmt_event->bind_param("ssii", $checksum, $datetime, $leagueid, $seasonid);

$datetime = parseDate($eventInfo[0]);
$leagueid = $leagueInfo[1];
$seasonid = $leagueInfo[3];
$trackid = 1;

$sql = "INSERT INTO events (checksum, time_and_day, track_id, league_id, season_id) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_stmt_init($conn);
if(!mysqli_stmt_prepare($stmt, $sql)) {
  header("Location: ../entercsv.php?error=sqlerror");
  exit();
}
mysqli_stmt_bind_param($stmt, "ssiii", $checksum, $datetime, $trackid, $leagueid, $seasonid);
mysqli_stmt_execute($stmt);
$eventid = mysqli_stmt_insert_id($stmt);
mysqli_stmt_close($stmt);

foreach ($input as $key => $value) {
  $line = str_getcsv($value);
  if(!checkDriverExists($conn, $line[6])) {
    newDriver($conn, $line);
  }
  $car_id = getCarID($conn,$line[1]);
  $driver_id = getDriverID($conn, $line[6]);
  $carclass_id = 1;
  $start_pos = $line[8];
  $race_pos = $line[0];
  $laps_comp = $line[18];
  $race_fastest_lap = "00:".$line[16]."000";
  $race_fastest_lap_num = $line[17];
  $race_average_lap = "00:".$line[15]."000";
  $race_inc = $line[19];

  $sql = "INSERT INTO race_results (event_id, driver_id, car_id, carclass_id, start_pos, race_pos, laps_comp, race_fastest_lap, race_fastest_lap_num, race_average_lap, race_inc) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
  $stmt = mysqli_stmt_init($conn);

  if(!mysqli_stmt_prepare($stmt, $sql)) {
    header("Location: ../entercsv.php?error=sqlerror");
    exit();
  }

  mysqli_stmt_bind_param($stmt, "iiiiiiisisi", $eventid, $driver_id, $car_id, $carclass_id, $start_pos, $race_pos, $laps_comp, $race_fastest_lap, $race_fastest_lap_num, $race_average_lap, $race_inc);

  if(mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
  } else {
    echo mysqli_stmt_error($stmt)."<br>";
  }

}

header("Location: ../entercsv.php?success=event&eventid=".$eventid);
