<?php
// -------------------------
// log
// 
// main log class for parsing text logs and processing the data
// must be used with a log processor descendent
// 
// -------------------------
class log {
  // $patterns
  // 
  // add other regular expression patterns and matches arrays here to match other log formats
  // the $patterns[$key] (eg. apacheDefault) should be passed to the parse() function as the 
  // $type parameter
  // 
  static $patterns = array(
    'apacheDefault' => array(
      // pattern
      // 
      // a perl compatible regular expression for separating out the data in the log line
      // 
      //            ip address            identd    auth      day    month   year   time                    TZ            request   http        code  size  referrer  navigator
      'pattern' => '/(\d+\.\d+\.\d+\.\d+) ([^\s]+) ([^\s]+) \[(\d+)\/(\w+)\/(\d+):(\d{1,2}:\d{1,2}:\d{1,2} ?[\+\-]?\d*)\] "(.*) (HTTP\/\d\.\d)" (\d+) (\d+) "([^"]*)" "([^"]*)"/',
      
      // matches
      // 
      // the matches here, represent the $matches index from preg_match for the pattern
      // above
      // 
      // if using log.mysql, an array must be passed to the parser constructor containing
      // key => value pairs relating these matches to mysql tables
      // 
      'matches' =>array(
        1 => 'ip',
        2 => 'identd',
        3 => 'auth',
        4 => 'day',
        5 => 'month',
        6 => 'year',
        7 => 'time',
        8 => 'request',
        9 => 'http_version',
        10 => 'response_code',
        11 => 'size',
        12 => 'referrer',
        13 => 'navigator'
      )
    )
  );
  
  // parser
  // 
  // this will be set to the specified parser when required
  // 
  static $parser = null;
  
  // parse
  // 
  // this is the function called when you have set up all the settings
  // 
  // $filename is the filename of the log you wish to parse, and $type is the
  // key in the $patterns array specified above
  // 
  function parse($filename = '', $type = 'apacheDefault') {
    // check all the settings are correct
    if (!isset(self::$patterns[$type])) {
      throw new Exception('Requested type not available ('.$type.')');
    }
    
    if (!file_exists($filename)) {
      throw new Exception('File does not exist ('.$filename.')');
    }
    
    if (!is_readable($filename)) {
      throw new Exception('File is not readable ('.$filename.')');
    }
    
    if (empty(self::$parser)) {
      throw new Exception('No parser specified (Set: log::$parser = new parser_type();)');
    }
    // end check settings

    // open the file
    $handle = fopen($filename, 'r');
    
    // while it's not at the end...
    while (!feof($handle)) {
      // read the line
      $line = fgets($handle);
      
      // if the line matches
      if (preg_match(self::$patterns[$type]['pattern'], $line, $matches)) {
        // set up an array
        $data = array();
        
        // loop through the pattern's matches and set the data array correctly
        foreach (self::$patterns[$type]['matches'] as $i => $key) {
          $data[$key] = $matches[$i];
        }
        
        // parse the data
        self::$parser->process($data);
      }
    }
    
    // close the file
    fclose($handle);
    
    // return true, why not!
    return true;
  }
}
