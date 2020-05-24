<?php

require 'includes/dbh.inc.php';

$sql = "SELECT race_results.*, drivers.display_name, cars.car_name FROM race_results
JOIN drivers ON race_results.driver_id = drivers.id
JOIN cars ON race_results.car_id = cars.id
WHERE race_results.event_id = 52";
$stmt = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmt, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row =mysqli_fetch_array($result);

echo "<table>";
echo "<tr><td>Pos</td><td>Driver Name</td><td>Car</td><td>Fastest Lap</td></tr>";
foreach ($result as $key => $value) {
  echo "<tr>";
  echo "<td>".$value['race_pos']."</td><td>".$value['display_name']."</td><td>".$value['car_name']."</td><td>".$value['race_fastest_lap']."</td>";
  echo "</tr>";
}
echo "</table>";

?>
