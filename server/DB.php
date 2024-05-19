<?php 
    class DB
    {
        private static $instance;
        private $db_conn; // Property to hold the database connection resource

        private function __construct() 
        {
            $this->db_conn = $this->databaseConnection(); // Initialize the database connection
            $this->databaseSelection(); // Select the database
        }

        private function databaseConnection()
        {
            if($db = mysqli_connect('localhost', 'root', '', 'sp_chat')) // Attempt to connect to the database(you can use yout own hosting server)
            {
                return $db; // Return the database connection resource if successful
            }
            else
            {
                die(mysqli_error($db)); // Terminate script execution and output an error message if connection fails
            }
        }

        private function databaseSelection()
        {
            mysqli_select_db($this->db_conn, 'sp_chat'); // Select the database named 'sp_chat' using the established connection
        }

        public static function getInstance() 
        {
            if(!isset(self::$instance)){   // Check if an instance of the class already exists
                self::$instance = new DB(); // If not, create a new instance
            }
            return self::$instance->db_conn;
        }
       
    }
?>