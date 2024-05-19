<?php
  // Include the DB class file
  require_once('../server/DB.php');

  // Get an instance of the DB class
  $db = DB::getInstance();

  // Get the user ID from the session
  $uid = $_SESSION['uid'];

  // Query to select all users except the current user, ordered by username
  $query = "SELECT * FROM users WHERE id != $uid ORDER BY username ASC";

  // Execute the query
  $result = mysqli_query($db, $query);

  // Start building the HTML table
  $table = "<table class='table table-hover' id='users-table' style='margin: 40px auto; width:60%; box-shadow: -2px 6px 10px rgb(235, 235, 235); border-bottom: 1px solid #eeeeee'>
              <thead class='table-dark'>
                <th>User</th>
                <th>Status</th>
                <th>Chat</th>
              </thead>";

  // Loop through the result set and generate table rows for each user
  while($row = mysqli_fetch_assoc($result)){
    $rid = $row['id'];
    $username = $row['username'];
    $online = $row['online'];
    $last_timestamp = $row['last_timestamp'];

    // Set default values for dot color and chat color
    $dotColor = "#6C757D";
    $chatColor = "#6C757D";

    // Set the status and dot color based on the user's online status
    if($online == 1){
      $dotColor = "#198853";
      $chatColor = "#0E6DFD";
      $status = "Online";
    }
    else{
      $status = "Offline";
    }

    // Build the status and dot HTML elements
    $status = "<span class='receiver-public-statusText'>$status</span>";
    $dot = "<i style='font-size:10px; color:$dotColor; transition: all 3s;' class='fas fa-circle align-middle receiver-public-dot'></i>";

    // Build the table row HTML
    $table .= "
    <tr>
      <td class='fw-light' style='padding-left: 20px;'>".ucfirst($username)."</td>
      <td>$status &nbsp $dot</td>
      <td>
        <button class='btn btn-sm border-0' data-bs-toggle='modal' data-bs-target='#chatModal' data-bs-senderId='$uid' data-bs-receiverId='$rid' data-bs-username='$username' data-bs-status='$online'><i style='font-size:18px; color:$chatColor;' class='receiver-public-chatIcon fas fa-comments align-middle'></i></button>
      </td>
    </tr>";
  }

  // Output the final HTML table
  echo $table;
?>