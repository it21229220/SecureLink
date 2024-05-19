<?php
    require_once('../server/DB.php');
    $db = DB::getInstance();

    // Get the POST data sent from the client
    $senderId = $_POST['sid'];
    $receiverId = $_POST['rid'];
    $senderKey = $_POST['skey'];
    $receiverKey = $_POST['rkey'];

     // Prepare a query to check if there is an existing session between these two users
    $query = "SELECT * FROM session 
              WHERE (sender_id=$senderId OR sender_id=$receiverId) AND (receiver_id=$receiverId OR receiver_id=$senderId)";
    $result = mysqli_query($db, $query);
    $numOfRows = mysqli_num_rows($result);

    // If a session already exists between these two users
    if($numOfRows > 0){
      // Prepare a query to update the session with the new keys
      $query = "UPDATE session
                SET sender_key='$senderKey', receiver_key='$receiverKey'
                WHERE (sender_id=$senderId OR sender_id=$receiverId) AND (receiver_id=$receiverId OR receiver_id=$senderId)";
      if($db->query($query) !== true){
         // If there is an error updating the keys, output an error message
        echo "Keys are not updated due to an error".mysqli_error($db);;
      }
    }
    else{
      // If no session exists, prepare a query to insert a new session with the keys
      $query = "INSERT INTO session (sender_id, receiver_id, sender_key, receiver_key)
      VALUES ('$senderId', '$receiverId', '$senderKey', '$receiverKey')";
      // Execute the insert query
      if($db->query($query) !== true){
        // If there is an error storing the keys, output an error message
        echo "Keys are not stored due to an error".mysqli_error($db);;
      }
    }
?>