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
  $checksum = "'" . sha1_file('data.csv') . "'";

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

  $sql_checkdoubles = "SELECT checksum FROM events";
  $sql_eventInfo = "INSERT INTO events (checksum, time_and_day, league_id, season_id) VALUES ($checksum, $datetime, " . $leagueInfo[1] . ", " . $leagueInfo[3] . ");";

  $answer = $conn->query($sql_checkdoubles);
  $answer = $answer->fetch_assoc();
  $checkcheck = "'" . $answer["checksum"] . "'";

  if($checkcheck == $checksum) {
    echo "<br><b>Entry already exists!</b><br>" . $answer[0] . "<br>";
  } elseif ($conn->query($sql_eventInfo) === TRUE) {
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
