
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


          // When year is selected, retreive MLB teams that were active for that year and populate team dropdown.
          $("#yearsDropdown").change(function(){
              selectedYear = $(this).val();

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

              /*
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
              */
          });

          // Set selected team
          $("#teamsDropdown").change(function(){
              selectedTeam = $(this).val();
              console.log(selectedTeam + " " + selectedYear);
          });

          // Submit form ... send ajax request to get result
          $("#Submit").click(function(){
               console.log(selectedTeam + " " + selectedYear);

               /* Check which checkboxes are selected (php needs 1s and 0s for bool)
               var power = $("#powerCheckbox").is(":checked") ? 1 : 0;
               var contact = $("#contactCheckbox").is(":checked")? 1 : 0;
               var speed = $("#speedCheckbox").is(":checked")? 1 : 0;
               var eye= $("#eyeCheckbox").is(":checked")? 1 : 0; */
               var fantasy = $("#fantasyCheckbox").is(":checked")? 1 : 0;
               console.log(power + " " + contact + " " +  speed+ " " + eye + " "+ fantasy);

              $.ajax({
                  url: 'getPreferredPlayer.php',
                  type: 'post',
                  data: {team: selectedTeam, year:selectedYear, yearTo: selectedYearTo,
                    power:power, contact:contact, eye:eye, speed:speed, fantasy:fantasy},
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

      <!-- Checkboxes of what the user prefers in a player -->
      <div>
        <h3>What do you prefer in a player?</h3>
        <input type="checkbox" id="powerCheckbox" name="powerCheckbox" value="Power" checked>
        <label for="powerCheckbox"> Power </label><br>
        <input type="checkbox" id="contactCheckbox" name="contactCheckbox" value="Contact">
        <label for="contactCheckbox"> Contact </label><br>
        <input type="checkbox" id="speedCheckbox" name="speedCheckbox" value="Speed">
        <label for="speedCheckbox"> Speed </label><br>
        <input type="checkbox" id="eyeCheckbox" name="eyeCheckbox" value="Eye">
        <label for="eyeCheckbox"> Eye </label><br>
        <input type="checkbox" id="fantasyCheckbox" name="fantasyCheckbox" value="Fantasy">
        <label for="fantasyCheckbox"> Fantasy ROTO (R, HR, RBI, SB, AVG) </label><br><br>
        <input type="submit" id="Submit" value="Submit"><br><br>
      </div>

      <!-- Result table -->
      <div id= "result">
      </div>
   </body>
</html>