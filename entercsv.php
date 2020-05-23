<?php
  require "header.php";
 ?>

<main>
  <form action="includes/processcsv.inc.php" enctype="multipart/form-data" method="post">
    <label for="inputcsv">Select your results file:</label>
    <input type="file" id="inputcsv" name="inputcsv">
    <input type="submit" value="Process CSV" name="process-csv">
  </form>
</main>

<?php
  require "footer.php";
 ?>
