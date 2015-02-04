<?php
// -------------------------
// log_processor interface
// 
// implement this class in any processors your write
// 
// currently the only required function is process, which processes the
// array log of log data returned from preg_match
// 
// -------------------------
interface log_processor {
  function process($data);
}
?>