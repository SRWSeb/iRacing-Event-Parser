<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "srw_champ";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if(!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully!";

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
function checkDriverExists ($conn, $iracingid) {
  $stmt = "SELECT iracing_name FROM drivers WHERE iracing_id=$iracingid";
  $result = $conn->query($stmt);
  $result = $result->fetch_assoc();
  if($result) {
    return true;
  }
  return false;
}

//Enters a new driver into the database.
function newDriver ($conn, $data) {
  $iracingid = $data[6];
  $name = $data[7];
  $stmt = $conn->prepare("INSERT INTO drivers (iracing_id, iracing_name, display_name) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $iracingid, $name, $name);
  $stmt->execute();
  $stmt->close();
  echo "<b>Driver " . $name . ", ID " . $iracingid . " saved to Database.</b><br>";
}

function enterResult($conn) {
  $double = false;

  //Prepare the statements for the SQL inserts and bind the parameters.
  $stmt_event = $conn->prepare("INSERT INTO events (checksum, time_and_day, league_id, season_id) VALUES (?, ?, ?, ?)");
  $stmt_event->bind_param("ssii", $checksum, $datetime, $leagueid, $seasonid);
  $stmt_results = $conn->prepare("INSERT INTO results (event_id, race_pos, race_fastest_lap, race_fastest_lap_num, race_average_lap, race_inc, gap_ahead) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt_results->bind_param("iiiiiii", $eventid, $racepos, $racefastest, $racefastestnum, $raceaverage, $raceinc, $gapahead);

  //read .csv from file
  $result = file('data2.csv');
  $checksum = "'" . sha1_file('data2.csv') . "'";
  $double = checkDoubles($conn, $checksum);

  //Remove unnecessary Newlines
  $result = array_diff($result,["\n"]);

  //Remove first line, don't need that
  array_shift($result);
  //Extract Event Infos into
  $eventInfo = str_getcsv(array_shift($result));
  //Remove third line, not needed
  array_shift($result);
  //Extract League Info into array
  $leagueInfo = str_getcsv(array_shift($result));
  //Remove fifth line, not needed
  array_shift($result);

  $datetime = parseDate($eventInfo[0]);
  $leagueid = $leagueInfo[1];
  $seasonid = $leagueInfo[3];

  if($double) {
    echo "<br><b>Entry already exists!</b><br>";
  } else {
    $stmt_event->execute();
    $last_id = $conn->insert_id;
    echo "<br>Entry successful! Insert ID is: " . $last_id . "<br>";
  }
  $stmt_event->close();

  foreach ($result as $key => $value) {
    $standing = str_getcsv($value);
    echo "Driver: " . $standing[7] . "<br>";
    if(!checkDriverExists($conn, $standing[6])) {
      newDriver($conn, $standing);
    } else {
      echo "Driver exists in database.<br>";
    }


  }
}

enterResult($conn);

$conn->close();


?>

<html>
<body>

  <form>
    <button formaction="<?php ?>"
    </form>
  </body>
  </html>
