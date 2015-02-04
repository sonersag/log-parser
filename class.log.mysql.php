<?php
// include the interface class
if (!class_exists('log_processor')) {
  require_once('class.log.processor.php');
}

// -------------------------
// log_mysql
// 
// mysql class for storing parsed log data in a table
// in my tests, this script processes ~1500-4000 rows/second
// 
// -------------------------
// Example table structure for apache:
// 
// CREATE TABLE `log` (
//   `id` int(11) NOT NULL auto_increment,
//   `ip` varchar(255) NOT NULL default '',
//   `identd` varchar(255) NOT NULL default '',
//   `auth` varchar(255) NOT NULL default '',
//   `day` int(8) NOT NULL default '0',
//   `month` varchar(255) NOT NULL default '',
//   `year` int(8) NOT NULL default '0',
//   `time` varchar(255) NOT NULL default '',
//   `request` text NOT NULL,
//   `http_version` varchar(255) NOT NULL default '',
//   `response_code` int(8) NOT NULL default '0',
//   `size` int(11) NOT NULL default '0',
//   `referrer` text NOT NULL,
//   `navigator` text NOT NULL,
//   PRIMARY KEY  (`id`)
// ) ENGINE=MyISAM DEFAULT CHARSET=latin1
// 
// and the example $fields array to complement it:
// 
// $fields => array(
// // key in $data       field name in table
//   'ip'            => 'ip',
//   'identd'        => 'identd',
//   'auth'          => 'auth',
//   'day'           => 'day',
//   'month'         => 'month',
//   'year'          => 'year',
//   'time'          => 'time',
//   'request'       => 'request',
//   'http_version'  => 'http_version',
//   'response_code' => 'response_code',
//   'size'          => 'size',
//   'referrer'      => 'referrer',
//   'navigator'     => 'navigator'
// );
// 
// -------------------------
class log_mysql implements log_processor {
  // set up the variables
  var $host = 'localhost';
  var $user = 'root';
  var $pass = '';
  var $db = '';
  var $table = '';
  // end set up variables
  
  // fields
  // 
  // this should be an array of key => value to 'translate' the data array
  // keys to mysql fields
  // 
  var $fields = array();
  
  // the mysql connection data
  var $connection = false;
  
  // counter for rows processed
  var $rows = 0;
  
  // __construct
  // 
  // executed when instatiated
  // 
  // $settings is an array that contains the database settings
  // host, user, pass, db and table are all strings relating the mysql database
  // fields should be an array of key => value pairs that are $data['key'] => mysql table field
  // 
  final function __construct($settings = array()) {
    // process $settings
    if (!is_array($settings)) {
      throw new Exception('log_mysql $settings should be an array');
    }
    
    if (isset($settings['user'])) {
      $this->user = $settings['user'];
    }
    
    if (isset($settings['pass'])) {
      $this->pass = $settings['pass'];
    }
    
    if (isset($settings['host'])) {
      $this->host = $settings['host'];
    }
    
    if (isset($settings['db'])) {
      $this->db = $settings['db'];
    }
    
    if (isset($settings['table'])) {
      $this->table = $settings['table'];
    }
    
    if (isset($settings['fields'])) {
      $this->fields = $settings['fields'];
    }
    
    if (empty($this->fields)) {
      throw new Exception('Missing field data ($this->fields)');
    }
    
    if (empty($this->table)) {
      throw new Exception('Missing MySQL table name');
    }
    // end process $settings
    
    // connect to the database
    $this->connect();
    
    // don't need to return anything, we're getting the object anyway
  }
  
  // process
  // 
  // the function called by the log class
  // 
  // $data is the array of data from the parsed log
  // 
  final function process($data) {
    // try and insert the data
    if ($this->insert($data)) {
      // if it's worked, increment the $rows counter
      $this->rows++;
      
      // return true for good measure
      return true;
      
    // if not...
    } else {
      // throw an exception
      throw new Exception('Error inserting data to MySQL server');
    }
  }
  
  // connect
  // 
  // connect to the mysql database
  // 
  private function connect() {
    // set $this->connection to the mysql server connection
    $this->connection = mysql_connect($this->host, $this->user, $this->pass);
    
    // if we connected ok...
    if ($this->connection) {
      // try to select the database
      if (mysql_select_db($this->db, $this->connection)) {
        // ... again for good measure...
        return true;
        
      // if something went wrong
      } else {
        // throw an exception
        throw new Exception('Unable to select database ('.$this->db.')');
      }
      
    // if something went wrong
    } else {
      // throw an exception
      throw new Exception('Unable to connect to MySQL server');
    }
  }
  
  // insert
  // 
  // inserts the data to the mysql table
  // 
  // $data is the array passed from process
  // 
  private function insert($data) {
    // build the query
    $q = "INSERT INTO
      `{$this->table}`
    SET ";
    
    // add each set to an array, for easy string concatenation
    $sets = array();
    
    // loop through the fields
    foreach ($this->fields as $name => $field) {
      // escape the data
      $data[$name] = mysql_real_escape_string($data[$name]);
      $field = mysql_real_escape_string($field);
      
      // add it to the array
      $sets[] = "`{$field}` = '{$data[$name]}'";
    }
    
    // implode the array
    $q .= implode(', ', $sets);
    
    // finish the query building
    $q .= ';';
    
    // execute the query
    $result = mysql_query($q, $this->connection);
    
    // return the result
    return $result;
  }
}
