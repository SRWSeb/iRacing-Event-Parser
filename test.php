<html>
<body>

<!--<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
  Name: <input type="text" name="fname">
  <input type="submit">
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['fname'];
  if (empty($name)) {
    echo "Name is empty";
  } else {
    echo $name;
  }
}
?>-->

<?php
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

  echo "$eventInfo[1] <br>";
  echo "$leagueInfo[0] <br>";

  $eventStandings = [];

  foreach ($result as $key => $value) {
    $eventStandings[$key] = str_getcsv(array_shift($result));
  }

  for ($row=0; $row < count($eventStandings) ; $row++) {
    echo "<p><b>Row number $row</b></p>";
    echo "<ul>";
    for ($col=0; $col < count($eventStandings[$row]) ; $col++) {
      echo "<li>" . $eventStandings[$row][$col] . "</li>";
    }
    echo "</ul>";
  }

 ?>

</body>
</html>
