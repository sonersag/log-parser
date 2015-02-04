<?php
// include the interface class
if (!class_exists('log_processor')) {
  require_once('class.log.processor.php');
}

// -------------------------
// log_output
// 
// very basic class to output data in a very simple format
// 
// -------------------------
class log_output implements log_processor {
  // process
  // 
  // the function called by the log class
  // 
  final function process($data) {
    // open a <p> tag
    $r = '<p>';
    
    // loop through each field of the data
    foreach ($data as $key => $value) {
      // build a <span> with a class of $key containing $value
      $r .= "<span class=\"{$key}\">{$value}</span> ";
    }
    
    // close the </p>
    $r .= '</p>';
    
    // output the html
    print $r;
    
    // return it too, just in case
    return $r;
  }
}
