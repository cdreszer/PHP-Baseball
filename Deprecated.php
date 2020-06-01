<?php
   // Gets the preferred player on a team for a given year, given the selected categories.
   public function get_preferred_player($team, $year, $yearTo, $power, $contact, $speed, $eye, $fantasy)
   {
      // If single category just do query on that category.
      // If multiple categories average out the rank on the team and show the two lowest averages (1st in avg, 3rd in HR, etc.)
      if ($power && !$contact && !$speed && !$eye && !$fantasy)
      {
         $result = $this->most_power_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else if (!$power && $contact && !$speed && !$eye&& !$fantasy)
      {
         $result = $this->best_contact_hitter_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else if (!$power && !$contact && $speed && !$eye && !$fantasy)
      {
         $result = $this->most_speed_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else if (!$power && !$contact && !$speed && $eye && !$fantasy)
      {
         $result = $this->best_eye_on_team($team, $year, 5);
         $this->print_results_as_table($result);
         return $result;
      }
      else
      {
        // If multiple categories average out the rank on the team and sort by average rank
         $categories = $this->get_categories($power, $speed, $contact, $eye, $fantasy);
         
         $result = $this->order_players_based_on_multiple_categories($team, $year, $categories);
         $this->print_2Darray_as_table($result);

         return $result;
      }
   }


   // Gets player with the most steals on a team for a given year.
   public function most_speed_on_team($team, $year, $limit)
   {
      // If no team is selected get stats for all players, dont add team to where clause
      $teamWhereClause = $team != "" && $team != "0" ? "t.name = '$team' and " : "";

      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.SB
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where $teamWhereClause t.yearID = $year
            order by b.SB desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets player with highest AVG on a team for a given year.
   public function best_contact_hitter_on_team($team, $year, $limit)
   {
      // If no team is selected get stats for all players, dont add team to where clause
      $teamWhereClause = $team != "" && $team != "0" ? "t.name = '$team' and " : "";

      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.AVG
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where $teamWhereClause t.yearID = $year and b.AB > $this->MIN_AB
            order by b.AVG desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets player with highest OBP on a team for a given year (alternatively could just use walks)
   public function best_eye_on_team($team, $year, $limit)
   {
      // If no team is selected get stats for all players, dont add team to where clause
      $teamWhereClause = $team != "" && $team != "0" ? "t.name = '$team' and " : "";

      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.OBP
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where $teamWhereClause t.yearID = $year and b.AB > $this->MIN_AB
            order by b.OBP desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

   // Gets player with the most home runs on a team for a given year.
   public function most_power_on_team($team, $year, $limit)
   {
      // If no team is selected get stats for all players, dont add team to where clause
      $teamWhereClause = $team != "" && $team != "0" ? "t.name = '$team' and " : "";

      $sql= "SELECT p.nameFirst as 'First Name', p.nameLast as 'Last Name', b.HR
            from batting b 
            join people p on p.playerID = b.playerID
            join teams t on b.teamID = t.teamID and b.yearID = t.yearID
            where $teamWhereClause t.yearID = $year
            order by b.HR desc 
            limit $limit";

      $result = $this->conn->query($sql);

      return $result;
   }

?>