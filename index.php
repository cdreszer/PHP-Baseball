
<!-- 
   Builds queries from input and displays the players statistics for a single year.
   - User chooses year or range of years.
   - User chooses team, division, or league, 
   - User chooses what they want to consider: 
        power (HR), speed (SB), contact (AVG), eye (OBP).
   - User selects how many players to show top 1/5/10.

   If user chooses multiple categories average player's rank in category and player with lowest average is displayed.
   ... maybe add in some images for categories and player results

-->
<html>
   <head>
   <link rel="stylesheet" type="text/css" href="style.css">
   </head>
   <body>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script src="js/scripts.js"></script>
      <script type="text/javascript">
         $(document).ready(function(){

          var selectedYear = 2019;
          var selectedYearTo = 0;
          var selectedTeam = "";
          var statCheckboxes = ["R", "HR", "RBI", "SB", "AVG", "OBP", "SLG", "OPS"];
          var isHitter = 1;
          var isSimple = 1;

          // When year is selected, retreive MLB teams that were active for that year and populate team dropdown.
          $("#yearsDropdown").change(function(){
              selectedYear = $(this).val();
              console.log(selectedYear);

              $.ajax({
                  url: 'getTeams.php',
                  type: 'post',
                  data: {yearStart: selectedYear, yearEnd:selectedYear},
                  dataType: 'json',
                  success:function(response){
                      var len = response.length;

                      $("#teamsDropdown").empty();
                      $("#teamsDropdown").append("<option value='0'>- Select -</option>")
                      for( var i = 0; i<len; i++){
                          var name = response[i];
                          
                          $("#teamsDropdown").append("<option value='" + name + "'>" + name + "</option>");

                      }
                  }
              });
          });

          // When year is selected, retreive MLB teams that were active for that year and populate team dropdown.
          $("#yearsDropdownTo").change(function(){
              selectedYearTo = $(this).val();
              console.log(selectedYear + " - " + selectedYearTo);

              $.ajax({
                  url: 'getTeams.php',
                  type: 'post',
                  data: {yearStart: selectedYear, yearEnd:selectedYearTo},
                  dataType: 'json',
                  success:function(response){
                      var len = response.length;

                      $("#teamsDropdown").empty();
                      $("#teamsDropdown").append("<option value='0'>- Select -</option>")
                      for( var i = 0; i<len; i++){
                          var name = response[i];
                          
                          $("#teamsDropdown").append("<option value='" + name + "'>" + name + "</option>");

                      }
                  }
              });
              
          });

          $("#simple, #advanced").change(function(){
              if ($("#simple").is(":checked"))
              {
                isSimple = 1;
              }
              else
              {
                isSimple = 0;
              }
          });

          // When year is selected, retreive MLB teams that were active for that year and populate team dropdown.
          $("#hitter, #pitcher").change(function(){

              if ($("#hitter").is(":checked"))
              {
                statCheckboxes = ["R", "HR", "RBI", "SB", "AVG", "OBP", "SLG", "OPS"];
                isHitter = 1;
              }
              else
              {
                statCheckboxes = ["W", "L", "SO", "ERA", "BAOpp"];
                isHitter = 0;
              }

              console.log(statCheckboxes.toString());

              $.ajax({
                  url: 'getCheckboxes.php',
                  type: 'post',
                  data: {stats: statCheckboxes, fantasy:isHitter},
                  dataType: 'html',
                  success:function(html){
                     console.log(html)
                     $("#checkboxes").empty();
                     $("#checkboxes").append(html);
                  }
              });
          });

          // Set selected team
          $("#teamsDropdown").change(function(){
              selectedTeam = $(this).val();
              console.log(selectedTeam + " " + selectedYear);
          });

          // Submit form ... send ajax request to get result
          $("#Submit").click(function(){
               console.log(selectedTeam + " " + selectedYear);

               var categories = [];

               // Check which stat checkboxes are selected
               // If fantasy is checked ignore all other checkboxes
               if (isHitter && $("#fantasyCheckbox").is(":checked"))
               {
                  categories = ["R", "HR", "RBI", "SB", "AVG"];
               }
               else
               {
                   // Checks to see which stat category is selected.
                   statCheckboxes.forEach(stat =>
                   {
                      var id = "#" + stat + "Checkbox";
                      if ($(id).is(":checked"))
                      {
                        categories.push(stat);
                      }
                   });
               }
               console.log(categories.toString());

              // Send ajax request to get preferred player based on selected categories.
              $.ajax({
                  url: 'getPreferredPlayer.php',
                  type: 'post',
                  data: {team: selectedTeam, year:selectedYear, yearTo: selectedYearTo, categories:categories, isHitter:isHitter, isSimple:isSimple},
                  dataType: 'html',
                  success:function(html){
                     console.log(html)
                     $("#result").empty();
                     $("#result").append(html);
                  }
              });
          });

         });
      </script>

      <?php
         include 'DBConnection.php';
         //include 'HTMLHelpers.php';

         // Create connection
         $db = new BaseballDB();
         $years = $db->get_years();
         $db->conn->close();
      ?>

      <h1> Welcome to PHP Baseball Player Retreiver!</h1>

      <!-- Year dropdown menus -->
      <div>
         <label for="yearsDropdown">Choose a year:</label>
         <select id='yearsDropdown' name='yearsDropdown'>"
            <option value="0">- Select -</option>
            <?php
              HTMLHelpers::populate_drop_down($years);
            ?>
         </select>
         <label for="yearsDropdownTo"> to </label>
         <select id='yearsDropdownTo' name='yearsDropdownTo'>"
            <option value="0">- Select -</option>
            <?php
              HTMLHelpers::populate_drop_down($years);
            ?>
         </select>
      </div>

      <!-- Team dropdown menu -->
      <div>
         <label for="teamsDropdown">Choose a team:</label>
         <select id='teamsDropdown' name='teamsDropdown'>
            <option value="0">- Select -</option>
            <?php
               //$teams = $db->get_team_names($selectedYear, $selectedYear);
               //$db->populate_drop_down($teams);
            ?>
         </select>
      </div>

      
      <div>
        <h3>What do you prefer in a player?</h3>

        <!-- Hitter / Pitcher Radio Buttons-->
        <input type="radio" id="hitter" name="position" value="hitter" checked>
        <label for="male">Hitter</label>
        <input type="radio" id="pitcher" name="position" value="pitcher">
        <label for="pitcher">Pitcher</label><br><br>

        <!-- Checkboxes of what the user prefers in a player -->
        <div id="checkboxes">
        <?php
            // Populate stat checkboxes with hitter stats as default.
            $stats = array("R", "HR", "RBI", "SB", "AVG", "OBP", "SLG", "OPS");
            //$stats = array("W", "L", "SO", "ERA", "BAOpp");
            HTMLHelpers::populate_checkboxes($stats, true);
        ?>
        </div>

        <!-- Simple / Advanced Ranking Radio Buttons-->
        <input type="radio" id="simple" name="ranking" value="simple" checked>
        <label for="simple">Simple Ranking</label>
        <input type="radio" id="advanced" name="ranking" value="advanced">
        <label for="advanced">Advanced Ranking</label><br><br>

        <!-- Submit button -->
        <input type="submit" id="Submit" value="Submit"><br><br>
      </div>

      <!-- Result table -->
      <div id= "result">
      </div>
   </body>
</html>