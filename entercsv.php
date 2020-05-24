<?php
  require "header.php";
  require 'includes/dbh.inc.php';
 ?>

 <main>
   <div class="container">
     <div class="row">
       <div class="col-12">
       <form action="includes/processcsv.inc.php" enctype="multipart/form-data" method="post">
         <div class="custom-file mb-12">
           <input type="file" id="inputcsv" name="inputcsv" class="custom-file-input">
           <label class="custom-file-label" for="inputcsv">Select your results file:</label>
         </div>
         <input type="submit" value="Process CSV" name="process-csv" class="btn btn-primary">
       </form>
     </div>
     </div>

     <?php
     //If there is GET information in the URL, get it and see what to do with it. Should be straight forward
     //Errors first
     if(isset($_GET['error'])) {
       if($_GET['error'] == "double") {
         echo "<div class=\"row\">
                  <div class=\"col-6\">
                    Session already in database.
                  </div>
         </div>";
       }
       if($_GET['error'] == "sqlerror") {
         echo "<div class=\"row\">
                  <div class=\"col-6\">
                    Something went wrong with the database... Slowly back away and pretend it wasn't you!
                  </div>
         </div>";
       }
       if($_GET['error'] == "notrace") {
         echo "<div class=\"row\">
                  <div class=\"col-6\">
                    Entered .csv is not a race session!
                  </div>
         </div>";
       }

     }
     //If the entry was successful, display some of the entered data.
     if (isset($_GET['success'])) {
       if($_GET['success'] == "event") {
         echo "<div class=\"row\">
                  <div class=\"col-6\">
                    Session successfully added to the database. Session ID is: ".$_GET['eventid']."
                  </div>
         </div>";

         //Allmighty SQL statement to get all the stuff we want
         $sql = "SELECT race_results.*, drivers.display_name, cars.car_name FROM race_results
         JOIN drivers ON race_results.driver_id = drivers.id
         JOIN cars ON race_results.car_id = cars.id
         WHERE race_results.event_id = ?";

         //Magic happens here
         $stmt = mysqli_stmt_init($conn);
         mysqli_stmt_prepare($stmt, $sql);
         mysqli_stmt_bind_param($stmt, "i", $_GET['eventid']);
         mysqli_stmt_execute($stmt);
         $result = mysqli_stmt_get_result($stmt);

         //Output the result as a table - we get the result as a array, where the rows are the joined rows from the DB. Each entry is referenced by it's column name.
         echo "<div class=\"row\"><div class=\"col-12\">";
         echo "<table class=\"table table-striped\">";
         echo "<tr><th scope=\"col\">Pos</th><th scope=\"col\">Driver Name</th><th scope=\"col\">Car</th><th scope=\"col\">Fastest Lap</th></tr>";
         foreach ($result as $key => $value) {
           echo "<tr>";
           echo "<th scope=\"row\">".$value['race_pos']."</th><td>".$value['display_name']."</td><td>".$value['car_name']."</td><td>".$value['race_fastest_lap']."</td>";
           echo "</tr>";
         }
         echo "</table>";
         echo "</div></div>";
       }
     }
     ?>
   </div>
 </main>

<?php
  require "footer.php";
 ?>
