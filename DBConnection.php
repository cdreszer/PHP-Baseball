<?php
// Contains connection to baseball database, performs queries, and prints results in table.
class BaseballDB
{
   public $conn;
   public $MIN_AB = 300;
   public $category_to_stat_map = array("speed" => "SB", "power" => "HR", "contact" => "AVG", "eye" => "OBP");

   // Creates a MySQLi connection to the baseball db.
   function __construct()
   {
      $servername = "localhost";
      $username = "root";
      $password = "root";
      $dbname = "lahmansbaseballdb";

      // Create connection
      $this->conn = new mysqli($servername, $username, $password, $dbname);
      
      // Check connection
      if ($conn->connect_error) {
         die("Connection failed: " . $this->conn->connect_error);
      }
   }

   // Gets years in database.
   public function get_years()
   {
      $sql = "SELECT t.yearID as 'Year' from teams t 
              group by t.yearID
              order by t.yearID desc";

      $result = $this->conn->query($sql);

      $years = array();
      while($row = $result->fetch_assoc()) {
         $years[] = $row["Year"];
      }

      return $years; 
   }

   // Get all team names for the years chosen
   public function get_team_names($yearStart, $yearEnd)
   {
      $sql= "SELECT t.name
            from teams t
            where t.yearID >= " . $yearStart. " and t.yearID <= " . $yearEnd .
            " group by t.name";

      $result = $this->conn->query($sql);

      $teams = array();
      while($row = $result->fetch_assoc()) {
         $teams[] = $row["name"];
      }

      return $teams; 
   }

   // Gets the preferred player on a team for a given year, given the selected categories.
   public function get_preferred_player($team, $year, $power, $contact, $speed, $eye)
   {
      // If single category just do query on that category.
      // If multiple categories average out the rank on the team and show the two lowest averages (1st in avg, 3rd in HR, etc.)
      if ($power && !$contact && !$speed && !$eye)
      {
         $result = $this->most_power_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else if (!$power && $contact && !$speed && !$eye)
      {
         $result = $this->best_contact_hitter_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else if (!$power && !$contact && $speed && !$eye)
      {
         $result = $this->most_speed_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else if (!$power && !$contact && !$speed && $eye)
      {
         $result = $this->best_eye_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else
      {
        // If multiple categories average out the rank on the team and sort by average rank
         $categories = $this->get_categories($power, $speed, $contact, $eye);
         
         $result = $this->order_players_based_on_multiple_categories($team, $year, $categories);
         $this->print_2Darray_as_table($result);

         return $result;
      }
   }

   // Returns an array of stat categories corresponding to the selected check boxes.
   public function get_categories($power, $speed, $contact, $eye)
   {
      $categories = array();
      if ($power)
      {
         $categories[] = "HR";
      }

      if ($speed)
      {
         $categories[] = "SB";
      }

      if ($contact)
      {
         $categories[] = "AVG";
      }

      if ($eye)
      {
         $categories[] = "OBP";
      }
      
      return $categories;
   }

   // Builds ranks table
   // categories is an array of stat category ("SB", "HR", "OBP", "SB") 
   public function order_players_based_on_multiple_categories($team, $year, $categories)
   {
      $player_rank_map = array();

      // Gets stats as associative array.
      $stats = $this->get_player_stats_from_team($team, $year);
      $stats = $stats->fetch_all(MYSQLI_ASSOC);

      // Sort stats by each category and add their rank into the players.
      foreach ($categories as $category)
      {
         // Sort stats in descending order
         usort($stats, function($a, $b) use ($category) {
            return $b[$category] - $a[$category] > 0 ? 1 : -1;
         }); 

         for ($i = 0; $i < count($stats); $i++)
         {
            $player = $stats[$i];
            $key = $player["playerID"];
            $rank = $i + 1;

            // If key exists in map add to it otherwise create entry for player
            if (array_key_exists($key, $player_rank_map))
            {
               $player_rank_map[$key][$category] = $player[$category] . " ($rank)";
               $player_rank_map[$key]["Average Rank"] += $rank;
            }
            else
            {
               $player_rank_map[$key]["Name"] = $player["First Name"] . " " . $player["Last Name"];
               $player_rank_map[$key]["Average Rank"] = $rank;
               $player_rank_map[$key][$category] = $player[$category] . " ($rank)";
            }
         }
      } 

      // Gets the average rank for the player... divide rank by number of categories
      foreach ($player_rank_map as $playerid => &$player)
      {
         $player["Average Rank"] = round($player["Average Rank"] / count($categories), 2);
      }

      // Sort playrs by average rank
      usort($player_rank_map, function($a, $b) use ($category) {
         return $b["Average Rank"] - $a["Average Rank"] > 0 ? -1 : 1;
      }); 

      return $player_rank_map;
   }

   // Gets player with the most steals on a team for a given year.
   public function most_speed_on_team($team, $year, $limit)
   {
      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.SB
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where t.name = '$team' and t.yearID = $year
            order by b.SB desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets player with highest AVG on a team for a given year.
   public function best_contact_hitter_on_team($team, $year, $limit)
   {
      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.AVG
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where t.name = '$team' and t.yearID = $year and b.AB > $this->MIN_AB
            order by b.AVG desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets player with highest OBP on a team for a given year (alternatively could just use walks)
   public function best_eye_on_team($team, $year, $limit)
   {
      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.OBP
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where t.name = '$team' and t.yearID = $year and b.AB > $this->MIN_AB
            order by b.OBP desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets player with the most home runs on a team for a given year.
   public function most_power_on_team($team, $year, $limit)
   {
      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.HR
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where t.name = '$team' and t.yearID = $year
            order by b.HR desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets the batting stats for a team for a particular year (for players w/ > 350 ABs)
   public function get_player_stats_from_team($team, $year)
   {
      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.*
               from batting b 
               join people p on p.playerID = b.playerID
               join teams t on b.teamID = t.teamID and b.yearID = t.yearID
               where t.name = '$team' and t.yearID = $year and b.AB > $this->MIN_AB";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Populate options of a drop down list from an array
   public function populate_drop_down($options)
   {
        foreach ($options as $option)
        {
          echo "<option value='$option'>$option</option>";         
        }
   }

   // Prints the database result as a HTML table
   public function print_results_as_table($result)
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


   // Prints the database result as a HTML table
   public function print_2Darray_as_table($result)
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
         echo "* Minimum " . $this->MIN_AB . " ABs";
      } 
      // If no results print 0 results instead of a table.
      else {
         echo "0 results";
      }
   }
}
?>