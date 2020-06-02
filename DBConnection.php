<?php
include_once('HTMLHelpers.php');

// Contains connection to baseball database, performs queries, and prints results in table.
class BaseballDB
{
   public $conn;
   public $MIN_AB = 300;
   public $MIN_IP = 100;

   // Hitting fantasy baseball categories
   public $fantasy_roto_cats = array("R", "HR", "RBI", "SB", "AVG");

   // Lower is better
   public $ascending_stat_categories = array("ERA", "WHIP", "L", "BAOpp");

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
            where t.yearID >= $yearStart and t.yearID <= $yearEnd
            group by t.name";

      $result = $this->conn->query($sql);

      $teams = array();
      while($row = $result->fetch_assoc()) {
         $teams[] = $row["name"];
      }

      return $teams; 
   }

   // Gets the preferred player on a team for a given year, given the selected categories.
   public function get_preferred_player_from_cats($team, $year, $yearTo, $categories, $isHitting)
   {

      // If single category just do query on that category.
      // If multiple categories average out the rank on the team
      if (count($categories) == 1)
      {
         $result = $this->query_baseball_stats($team, $year, $yearTo, $categories[0], 10, $isHitting);
         HTMLHelpers::print_results_as_table($result);
         return $result;
      }
      else
      {
         // If multiple categories average out the rank on the team and sort by average rank
         $result = $this->order_players_based_on_multiple_categories($team, $year, $yearTo, $categories, $isHitting);
         HTMLHelpers::print_2Darray_as_table($result);
         echo "* Minimum $this->MIN_AB ABs";
         return $result;
      }
   }

   // If multiple years, take the players average for each category
   public function avg_stats_when_range_of_years_chosen()
   {

   }

   // Builds ranks table
   // categories is an array of stat category ("SB", "HR", "OBP", "SB") 
   public function order_players_based_on_multiple_categories($team, $year, $yearTo, $categories, $isHitting)
   {
      $player_rank_map = array();
      $includeYear = is_numeric($yearTo) && $yearTo > $year;

      // Gets stats as associative array.
      $stats = $this->get_player_stats_from_team($team, $year, $yearTo, $isHitting);
      $stats = $stats->fetch_all(MYSQLI_ASSOC);

      // Sort stats by each category and add their rank into the players.
      foreach ($categories as $category)
      {
         // Sort depending on if stat should be ascending or descending
         if (in_array($category, $this->ascending_stat_categories))
         {
            // Sort stats in ascending order
            usort($stats, function($a, $b) use ($category) {
               return $b[$category] - $a[$category] > 0 ? -1 : 1;
            }); 
         }
         else
         {
            // Sort stats in descending order
            usort($stats, function($a, $b) use ($category) {
               return $b[$category] - $a[$category] > 0 ? 1 : -1;
            }); 
         }


         for ($i = 0; $i < count($stats); $i++)
         {
            $player = $stats[$i];
            $key = $player["playerID"].$player["yearID"];
            $rank = $i + 1;

            // If key exists in map add to it otherwise create entry for player
            if (array_key_exists($key, $player_rank_map))
            {
               $player_rank_map[$key][$category] = $player[$category] . " ($rank)";
               $player_rank_map[$key]["Average Rank"] += $rank;
            }
            else
            {
               if ($includeYear)
               {
                  $player_rank_map[$key]["Year"] = $player["Year"];
               }

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

   // Build query by passed in stat.
   public function query_baseball_stats($team, $year, $yearTo, $stat, $limit, $isHitting)
   {
      $stat = "b." . $stat;

      $showYear = "";

      if (!is_numeric($yearTo) || $yearTo <= $year)
      {
         $yearTo = $year;
      }
      else
      {
         $showYear = "b.yearID as 'Year',";
      }

      // ABs or IP minimum
      $minimum = "";

       // Checks whether pitching or hitting stats
      if ($isHitting)
      {
         $table = "batting";
         $minimum = "b.AB > $this->MIN_AB";
      }
      else
      {
         $table = "pitching";
         $minimum = "b.IPouts > $this->MIN_IP and b.GS > 10";
      }

      // If no team is selected get stats for all players, dont add team to where clause
      $teamWhereClause = $team != "" && $team != "0" ? "t.name = '$team' and " : "";

      $sql= "SELECT $showYear p.nameFirst as 'First Name', p.nameLast as 'Last Name', $stat
            from $table b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where $teamWhereClause t.yearID >= $year and t.yearID <= $yearTo and $minimum
            order by $stat desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;

   }

   // Gets the batting stats for a team for a particular year 
   public function get_player_stats_from_team($team, $year, $yearTo, $isHitting)
   {
      // Set year to if not set and return column for year if query is over multiple years
      $showYear = "";
      if (!is_numeric($yearTo) || $yearTo <= $year)
      {
         $yearTo = $year;
      }
      else
      {
         $showYear = "b.yearID as 'Year',";
      }

      // ABs or IP minimum
      $minimum = "";

       // Checks whether pitching or hitting stats
      if ($isHitting)
      {
         $table = "batting";
         $minimum = "b.AB > $this->MIN_AB";
      }
      else
      {
         $table = "pitching";
         $minimum = "b.IPouts > $this->MIN_IP and b.GS > 10";
      }

      // If no team is selected get stats for all players, dont add team to where clause
      $teamWhereClause = $team != "" && $team != "0" ? "t.name = '$team' and " : "";

      $sql= "SELECT $showYear p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.*
               from $table b 
               join people p on p.playerID = b.playerID
               join teams t on b.teamID = t.teamID and b.yearID = t.yearID
               where $teamWhereClause t.yearID >= $year and t.yearID <= $yearTo and $minimum";

      $result = $this->conn->query($sql);

      return $result;
   }

}
?>