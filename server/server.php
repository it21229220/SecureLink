<?php
    require_once('DB.php'); // Include the database connection file
    $db = DB::getInstance();
    $message = ""; // Initialize an empty message variable
    session_start(); // Start a new session or resume an existing sessio

    if(isset($_SESSION['uid']) || isset($_SESSION['username'])){ // Check if the user is already logged in (i.e., session variables are set)
        $uid = $_SESSION['uid']; // Get the user id from the session
        $query = "UPDATE users SET online=0, logout_timestamp=CURRENT_TIMESTAMP() WHERE id=$uid"; // Update the user's status to offline
        if($db->query($query) === true){ // Execute the query and check if it was successful
            session_unset(); // Unset all the session variables
            session_destroy(); // Destroy the session
          }
    }

    if(isset($_POST['login']))
    {
        loginProcess($db); // Call the loginProcess function
        unset($_POST['login']); 
    }

    if(isset($_POST['register']))
    {
        registerProcess($message, $db); // Call the registerProcess function
        unset($_POST['register']);
    }

    function loginProcess($db)
    {
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) // Check if the username and password fields are not empty
        {
            $username = strtolower($_POST['username']); // Get the username from the form and convert it to lowercase
            $password = $_POST['password']; // Get the password from the form
            if(strpos($username, "'") === false && strpos($password, "'") === false) // Check if the username and password do not contain any single quotes
            {
                $password = specialEncryption($password); // Encrypt the password using a special encryption function
                $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'"; // Prepare the query to check if the username and password match
                $result = mysqli_query($db, $query); // Execute the query
                $row = mysqli_fetch_assoc($result); // Fetch the result as an associative array
                if(mysqli_num_rows($result) == 1) // Check if the query returned exactly one row
                {
                    echo "<div class='text-success' style='font-size: 16px; font-weight: bold; margin-top:20px; text-align:center;'>Logging In..&nbsp <i class='fas fa-lock'></i></div>";
                    $_SESSION['uid'] = $row['id']; // Set the user id in the session
                    $_SESSION['username'] = $row['username']; // Set the username in the session
                    $uid = $_SESSION['uid']; // Get the user id from the session
                    $query = "UPDATE users SET online=1, last_timestamp=CURRENT_TIMESTAMP() WHERE id=$uid"; // Update the user's status to online
                    if($db->query($query) === true) // Execute the query and check if it was successful
                    {
                        $token = generateRandomToken($db); // Generate a random token
                        $query = "SELECT * FROM login_details WHERE uid = $uid";  // Prepare the query to check if the user has already logged in
                        $result = mysqli_query($db, $query); // Execute the query

                        if(mysqli_num_rows($result) == 0){ 
                            $query = "INSERT INTO login_details (uid, token) VALUES ($uid, '$token')"; // Insert the user's login details
                            if($db->query($query) === true) 
                            {

                                header( "refresh:1;url=./chat" ); // Redirect the user to the chat page
                                
                                
                            }
                        }
                        else
                        {
                            $query = "UPDATE login_details SET token='$token', last_timestamp = CURRENT_TIMESTAMP() WHERE uid=$uid"; // Update the user's login details
                            if($db->query($query) === true)  // Execute the query and check if it was successful
                            { 
                                header( "refresh:1;url=./chat" );  // Redirect to the chat page after 1 second
                            }
                        }
                    }
                }
                else // If no matching user was found
                {
                    echo "<div class='text-danger' style='font-size: 16px; font-weight: bold; margin-top:20px; text-align:center;'>Wrong Username or Password</div>"; // Display an error message
                }
            }
        }
    }

    function registerProcess($message, $db)
    {
        
        global $message; // Make the message variable available globally
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password']))  // Check if both username and password are provided and not empty
        {
            $username = strtolower($_POST['username']);  // Convert the username to lowercase
            $password = $_POST['password']; // Get the password from the POST data
            if(strpos($username, "'") === false && strpos($password, "'") === false) // Check if the username and password do not contain single quotes (preventing SQL injection)
            {
                $password = specialEncryption($password); // Encrypt the password using a special encryption function
                $query = "SELECT * FROM users WHERE username = '$username'"; // Prepare a query to check if the username already exists in the users table
                $result = mysqli_query($db, $query); // Execute the query
                if(mysqli_num_rows($result) == 1) // Check if the query returned exactly one row
                {
                  echo "<div class='text-danger' style='font-size: 16px; font-weight: bold; margin-top:20px; text-align:center;'>Please choose another username</div>"; // Display an error message
                }
                else // If the username is unique
                {
                  $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')"; // Prepare a query to insert the new user into the users table
                  if($db->query($query) === true){ // Execute the query and check if it was successful
                    echo "<div class='text-success' style='font-size: 16px; font-weight: bold; margin-top:20px; text-align:center;'>Successfully Registered &nbsp <i class='fas fa-lock'></i></div>";   // Display a success message
                    header( "refresh:2;url=./index.php" ); // Redirect to the login page after 2 seconds
                  }
                  else{ // If the query was not successful
                    echo "<div class='text-danger' style='font-size: 16px; font-weight: bold; margin-top:20px; text-align:center;'>". mysqli_error($db) ."</div>"; // Display an error message
                  }
                }
            }
        }
    }

    function generateRandomToken($db) 
    {
        for(;;)     // to make sure that if a key is duplicated, generate a new one automatically
        {
            $length = 16;
            $word = array_merge(range('a', 'z'), range(0, 9), range('A', 'Z'));
            shuffle($word);
            $token = substr(implode($word), 0, $length);

            $query = "SELECT * FROM login_details WHERE token='$token'";
            $result = mysqli_query($db, $query);
            if(mysqli_num_rows($result) == 0)
                break; // If no matching token is found, break the loop

        }
        return $token; // Return the unique token
    }

    // ---- To be implemented in a special way
    function specialEncryption($string){
      return md5($string); // Encrypt the string using MD5 (Note: MD5 is not recommended for secure password storage)
    }
?>