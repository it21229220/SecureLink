<?php
  require_once('../server/DB.php'); // Include the DB class file
  require_once('../modules/methods.php'); // Include the methods file
  $db = DB::getInstance(); // Create an instance of the DB class

  $senderId = $_REQUEST['sid']; // Get the sender ID from the request
  $receiverId = $_REQUEST['rid']; // Get the receiver ID from the request
  $whichCase = $_REQUEST['whichCase']; // Get the whichCase value from the request

  $query = ""; // Initialize the query variable
  $updateRequired = false; // Initialize the updateRequired flag
  $senderIsHere = true; // Initialize the senderIsHere flag

  if($whichCase == "displayAll"){
    $updateRequired = false;
    // Query to select all messages between the sender and receiver
    $query = "SELECT * 
              FROM chat 
              WHERE (sender_id=$senderId AND receiver_id=$receiverId) OR (sender_id=$receiverId AND receiver_id=$senderId) 
              ORDER BY timestamp";
  }
  else if ($whichCase == "displayNewOnly"){
    $updateRequired = true;
    // Query to select only new messages between the sender and receiver
    $query = "SELECT * 
              FROM chat
              WHERE (s_stat=0 OR r_stat=0) AND ((sender_id=$senderId AND receiver_id=$receiverId) OR (sender_id=$receiverId AND receiver_id=$senderId))
              ORDER BY timestamp";
  }

  $result = mysqli_query($db, $query); // Execute the query
  $messages = []; // Initialize an array to store the messages

  while($row = mysqli_fetch_assoc($result)){
    $node = []; // Initialize an array to store each message node
    $chatId = $row['id']; // Get the chat ID
    $sender = $row['sender_id']; // Get the sender ID
    $receiver = $row['receiver_id']; // Get the receiver ID
    $enc = $row['enc_method']; // Get the encryption method
    $message = "-1";//This encryption method is still not fully implemented.";
    $timestamp = $row['timestamp']; // Get the timestamp
    $isFile = $row['is_file']; // Get the is_file flag
    $fileName = $row['file_name']; // Get the file name
    $gamalData = []; // Initialize an array to store Gamal data
    $phishing = $row['flagged_as_phishing']; // Initialize an array to store Gamal data

    if($enc == 0){
      // If encryption method is DES
      $DES = new DES(); // Create an instance of the DES class
      $query = "SELECT *
                FROM des
                WHERE message_id=$chatId
                ORDER BY timestamp";
      $desResult = mysqli_query($db, $query); // Execute the query
      $desRow = mysqli_fetch_assoc($desResult); // Fetch the DES row

      $plainTextDes = $DES->DES_DECRYPT($desRow['cipher'], $desRow['receiver_key']); // Decrypt the DES cipher
      $plainTextDesSplit = str_split($plainTextDes, 32); // Split the decrypted text into 32-bit chunks
      $plainHexDes = base_convert($plainTextDesSplit[0], 2, 16) . base_convert($plainTextDesSplit[1], 2, 16); // Convert the binary chunks to hexadecimal
      $message = $plainHexDes; // Set the decrypted message
    }
    else if($enc == 1){
      // If encryption method is AES
      $AES = new AES(); // Create an instance of the AES class
      $query = "SELECT *
                FROM aes
                WHERE message_id=$chatId
                ORDER BY timestamp";
      $aesResult = mysqli_query($db, $query); // Execute the query
      $aesRow = mysqli_fetch_assoc($aesResult); // Fetch the AES row

      $cipherAesBin = hex2bin($aesRow['cipher']);  // Convert the AES cipher from hexadecimal to binary

      $ciphertext = str_split($cipherAesBin,16); // Split the binary cipher into 16-byte chunks
      $finalPlainText = "";
      for($i=0 ; $i<count($ciphertext) ; $i++)
      {
          $plain = $AES->AES_DECRYPT($ciphertext[$i], $aesRow['receiver_key']); // Decrypt each chunk using AES

          $plain = hex2bin($plain); // Convert the decrypted text from hexadecimal to binary
          $removeThePadKeyword = str_replace('#', '', $plain); // Remove the padding keyword
          $finalPlainText .= $removeThePadKeyword; // Append the decrypted text to the final plain text
      }
      $message = $finalPlainText; // Set the decrypted message
    }
    else if($enc == 2){
      // If encryption method is RSA
      $RSA = new RSA(); // Create an instance of the RSA class
      $query = "SELECT *
                FROM rsa
                WHERE message_id=$chatId
                ORDER BY timestamp";
      $rsaResult = mysqli_query($db, $query); // Execute the query
      $rsaRow = mysqli_fetch_assoc($rsaResult); // Fetch the RSA row
      $message = $RSA->decrypt($row['message'], $rsaRow['d'], $rsaRow['n'], $rsaRow['every_separate']); // Decrypt the RSA message
    }
    else if($enc == 3){
      // If encryption method is Gamal
      $query = "SELECT *
                FROM gamal
                WHERE message_id=$chatId
                ORDER BY timestamp";
      $gamalResult = mysqli_query($db, $query); // Execute the query
      $gamalRow = mysqli_fetch_assoc($gamalResult); // Fetch the Gamal row

      $gamalData[] = $gamalRow['c1']; // Add c1 to the Gamal data array
      $gamalData[] = $row['message']; // Add the message to the Gamal data array
      $gamalData[] = $gamalRow['xa']; // Add xa to the Gamal data array
      $gamalData[] = $gamalRow['q']; // Add q to the Gamal data array
      $gamalData[] = $gamalRow['every_separate']; // Add every_separate to the Gamal data array
    }

    $node[] = $sender; // Add the sender ID to the node array
    $node[] = $receiver; // Add the receiver ID to the node array
    $node[] = $enc; // Add the encryption method to the node array
    $node[] = $message; // Add the decrypted message to the node array
    $node[] = $timestamp; // Add the timestamp to the node array
    $node[] = time_elapsed_string($timestamp); // Add the time elapsed string to the node array
    $node[] = $isFile; // Add the is_file flag to the node array
    $node[] = $fileName; // Add the file name to the node array
    $node[] = $gamalData; // Add the Gamal data array to the node array
    $node[] = $phishing; // Add the Gamal data array to the node array
    

    if($updateRequired){
      // If update is required
      if($row['sender_id'] == $senderId && $row['s_stat'] == 0){
        $senderIsHere = true;
        $messages[] = $node; // Add the node to the messages array
      }
      else if($row['sender_id'] == $receiverId && $row['r_stat'] == 0){
        $senderIsHere = false;
        $messages[] = $node; // Add the node to the messages array
      }
    }
    else{
      $messages[] = $node; // Add the node to the messages array
    }
  }

  if($updateRequired){
    // If update is required
    $query = "";
    if($senderIsHere){
      // If the sender is present
      $query = "UPDATE chat 
                SET s_stat=1
                WHERE s_stat=0 AND ((sender_id=$senderId AND receiver_id=$receiverId) OR (sender_id=$receiverId AND receiver_id=$senderId))
                ORDER BY timestamp";
    }
    else{
      // If the receiver is present
      $query = "UPDATE chat 
                SET r_stat=1
                WHERE r_stat=0 AND ((sender_id=$senderId AND receiver_id=$receiverId) OR (sender_id=$receiverId AND receiver_id=$senderId))
                ORDER BY timestamp";
    }

    if($db->query($query) === true){
      echo json_encode($messages); // Output the messages as JSON
    }
    else{
      die(); // Terminate the script
    }
  } else {
    echo json_encode($messages); // Output the messages as JSON
  }
  

  function time_elapsed_string($datetime = "", $full = false) {
    // Function to calculate the time elapsed string
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