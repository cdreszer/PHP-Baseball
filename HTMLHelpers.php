<?php
class HTMLHelpers
{
   // Populates the checkboxes with the array of stats passed in
   // If fantasy is true, adds the fantasy baseball roto checkbox button
   public static function populate_checkboxes($stats, $fantasy)
   {
      $i = 0;

      foreach ($stats as $stat)
      {
       $id = $stat . "Checkbox";
       echo "<input type='checkbox' id='$id' name='$id' value='$stat' >
             <label for='$id'> $stat </label>";

       // Depending if one checkboxes all in one line, two to a line, one to a line
       if ($i++ % 2 != 0)
       {
         //echo '<br>';
       }
      }

      // Adds roto fantasy checkbox
      if ($fantasy)
      {
         echo "<input type='checkbox' id='fantasyCheckbox' name='fantasyCheckbox' value='Fantasy'>
          <label for='fantasyCheckbox'> Fantasy ROTO (R, HR, RBI, SB, AVG) </label>";
      }

      echo "<br><br>";
   }

   // Populate options of a drop down list from an array
   public static function populate_drop_down($options)
   {
        foreach ($options as $option)
        {
          echo "<option value='$option'>$option</option>";         
        }
   }

   // Prints the database result as a HTML table
   public static function print_results_as_table($result)
   {
      // If there are results build a table
      if ($result->num_rows > 0) {
         $tableCreated = false;

         $columns = array();

         // Output data of each row
         while($row = $result->fetch_assoc()) {
            // Create table if doesn't exist
            if (!$tableCreated)
            {
               echo "<table style='width:50%'><tr>";

              // Gets columns from first result.
              $columns = array_keys($row);
              foreach ($columns as $column)
              {
                  echo "<th>$column</th>";
              }

              echo "</tr>";

              $tableCreated = true;
            }

            echo "<tr>";

            foreach ($columns as $column)
            {
               echo "<td>" . $row[$column] . "</td>";
            }
            echo "</tr>";
         }

         echo "</table>";
      } 
      // If no results print 0 results instead of a table.
      else {
         echo "0 results";
      }
   }

   // Prints a two dimensional array as a HTML table
   public static function print_2Darray_as_table($result)
   {
      // If there are results build a table
      if (count($result) > 0) {
         $tableCreated = false;

         $columns = array();

         // Output data of each row
         foreach ($result as $row) {
            // Create table if doesn't exist
            if (!$tableCreated)
            {
               echo "<table style='width:50%'><tr>";

              // Gets columns from first result.
              $columns = array_keys($row);
              foreach ($columns as $column)
              {
                  echo "<th>$column</th>";
              }

              echo "</tr>";

              $tableCreated = true;
            }

            echo "<tr>";

            foreach ($columns as $column)
            {
               echo "<td>" . $row[$column] . "</td>";
            }
            echo "</tr>";
         }

         echo "</table>";
      } 
      // If no results print 0 results instead of a table.
      else {
         echo "0 results";
      }
   }
}



?>