<?php
  require_once('../server/DB.php');
  $db = DB::getInstance();
  session_start();

  $receiverId = $_REQUEST['rid']; // Get the receiver ID from the request

  $whichStatus = $_REQUEST['whichStatus']; // Get the status type from the request
  $uid = $_SESSION['uid']; // Get the user ID from the session

  if($whichStatus == "modal"){
    // If the status type is "modal", retrieve the online status and logout timestamp of the receiver
    $query = "SELECT online, logout_timestamp FROM users WHERE id=$receiverId";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);

    $data = [];
    $isOnline = $row['online']; // Get the online status of the receiver
    $logOutTimeStamp = time_elapsed_string($row['logout_timestamp']); // Get the time elapsed since the receiver's logout

    $data[] = $isOnline;
    $data[] = $logOutTimeStamp;

    echo json_encode($data); // Return the online status and logout timestamp as JSON

  } else if($whichStatus == "home"){
    // If the status type is "home", retrieve the online status of all users except the current user
    $query = "SELECT * FROM users WHERE id != $uid ORDER BY username ASC";
    $result = mysqli_query($db, $query);
    $data = [];
    while($row = mysqli_fetch_assoc($result)){
        if($row['online'] == 0) $data[] = "Offline"; else $data[] = "Online"; // Add the online status of each user to the data array
    }
    echo json_encode($data); // Return the online statuses as JSON
  }

  function time_elapsed_string($datetime, $full = false) {
    // Function to calculate the time elapsed since a given datetime
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>