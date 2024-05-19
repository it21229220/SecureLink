# SecureLink Chatting Web Application
SecureLink developed by PHP, JavaScript, Python, ML model, and MySQL.

## Algorithms
• DES (Not Fully Functional)<br/>
• AES<br/>
• RSA<br/>
• Diffie-Hellman (Key Exchange for DES and AES)<br/>
• ElGamal<br/>
• SKlearn

## Included Files
• Documentation (Detailed Description on each Algorithm)<br/>
• SQL file (sp_chat.sql)

## Extra Feature
• All the files are encrypted by encrypting their base64 arrayBuffer not by encrypting the path! (It's a requirement)<br/>
• Malicious or phishing URLS sent through the chat detected and flagged by ML algorithm and virustotal scans.

## Deployment guide
1. Download all the files.
2. Install xampp server in your default drive.
3. place these files in htdocs folder (C:\xampp\htdocs\SecureLink).
4. Run xampp or your prefered server.
5. Place SQL file in the database.
6. Change DB.php file connection as your system "mysqli_connect('localhost', 'your_root', '', 'sp_chat').
7. Change virus total api key to your api key.
8. If there is an issue with the database add a column as "flagged_as_phishing" to the chat table.
   ![image](https://github.com/it21229220/SecureLink/assets/99592732/cab70e68-dbc5-4b9b-84f2-a5d02cb63d84)

10. The web application should work now.
