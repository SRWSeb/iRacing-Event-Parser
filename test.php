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

function parseDate($datetime) {
  //Get rid of the GMT
  $datetime = chop($datetime,"GMT");
  //Parse Date into usable array
  $datetime = date_parse_from_format("Y.m.d h:i a",$datetime);
  //Build string for MySQL and return it
  return "'" . $datetime['year'] . "-" . $datetime['month'] . "-" . $datetime['day'] . " " . $datetime['hour'] . ":" . $datetime['minute'] . ":00'";
}

function enterResult($conn) {
  //read .csv from file
  $result = file('data.csv');

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
  /*
  $datetime = chop($eventInfo[0],"GMT");
  $datetime = date_parse_from_format("Y.m.d h:i a",$datetime);

  $finaldatetime ="'" . $datetime['year'] . "-" . $datetime['month'] . "-" . $datetime['day'] . " " . $datetime['hour'] . ":" . $datetime['minute'] . ":00'";
  echo $finaldatetime;*/

  $sql_eventInfo = "INSERT INTO events (time_and_day, lone_event, league_id, season_id, teamevent, multiclass) VALUES ($datetime, FALSE, " . $leagueInfo[1] . ", " . $leagueInfo[3] . ", FALSE, FALSE);";

  if($conn->query($sql_eventInfo) === TRUE) {
    $last_id = $conn->insert_id;
    echo "<br>Entry successful! Insert ID is: " . $last_id;
  } else {
    echo "Error: " . $sql_eventInfo . "<br>" . $conn->error;
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
