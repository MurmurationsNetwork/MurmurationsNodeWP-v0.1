<?php
/**
 * Plugin Name:       LazyLogger
 * Plugin URI:        http://photosynthesis.ca/code/LazyLoggerWP
 * Description:       Making wordpress development fun again!
 * Version:           1.0.0
 * Author:            Photosynthesis / Adam McKenty
 * Author URI:        http://photosynthesis.ca
 * License:           GPL 2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


function llog($item,$label = null,$severity = "!"){
  LazyLog::log($item,$label,$severity);
}


class LazyLog{

  public static $settings = array(
    "logAnything" => true,
    "logFile" => "lazyLog.txt", // File where logs will be written to (unless updated in an instance)
    "bufferLog" => false,       // Logs will not be output to HTML until flush() is called
    "toFile" => true,           // Log to file
    "toDB" => false,            // Log to database
    "toHTML" => true,           // Log to HTML (development only!)
    "toComment" => false,       // Log to HTML comment
    "toConsole" => false,       // Log to JS console (not recommended!)
    "toEmail" => false,         // Send the log output by email
    "dbInfo" => array(
      'database' => null,
      'host' => null,
      'user'=> null,
      'password' => null
    )
  );


    public static $HTMLBuffer;


    public static function setSetting($var,$value){
      self::$settings[$var] = $value;
    }


    public static function setSettings($settings){
      foreach (self::$settings as $key => $value) {
        if(array_key_exists($key,$settings)){
          self::setSetting($key,$value);
        }
      }
    }

    public static function setting($var){
      return self::$settings[$var];
    }

    public static function log($content,$meta = null,$severity = "!"){

      if(is_array($content)||is_object($content)){
        $content = print_r($content,true);
      }

      $message = ($meta ? $meta.': '.$content : $content);

      $logLine = date('Y-m-d h:m:j').' '.$message." \n";

      if(self::channelCheck(self::setting('toHTML'),$severity)){
        if(self::setting('bufferLog') == true){
          self::$HTMLBuffer .= "<pre>[LOG] ".$message."<br /></pre>";
        }else{
          echo "<pre>[LOG] ".$message."</pre><br />";
        }
      }

      if(self::channelCheck(self::setting('toFile'),$severity)){
        file_put_contents(self::setting('logFile'), $logLine, FILE_APPEND);
      }

      if(self::channelCheck(self::setting('toComment'),$severity)){
        echo "<!-- Log: ".$message." -->"."\n";
      }

      if(self::channelCheck(self::setting('toConsole'),$severity)){
        // Todo: clean up message for console output
        echo '<script type="text/javascript">console.log(\''.$message.'\');</script>'."\n";
      }
    }

    public static function flush(){
      echo "Log output:<br>".self::$HTMLBuffer;
    }

    public static function clearLogFile(){
      fclose(fopen(self::setting('logFile'),'w'));
    }



    public static function channelCheck($channelSetting,$severity = "!"){
      if($channelSetting === false){
        return false;
      }else if($channelSetting === true){
        return true;
      }else if($channelSetting <= $severity){
        return true;
      }
    }
  }

?>
