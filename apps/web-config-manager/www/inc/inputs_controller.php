<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/session_validator.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';	//contains functions for db interaction
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';	//contains functions for socket interaction, error message display, and logging.
$dbconfig = new dbconfigController();

function getSliderValueNew($pri)
{
  if($pri == '')
  {
    return Wifi_Slider_Value;
  }

  if($pri <= 10 )
  {
    return Iridium_Slider_Value;
  }
  elseif(($pri > 10) && ($pri <= 100))
  {
    return Cell_Slider_Value;
  }
  elseif($pri > 100)
  {
    return Wifi_Slider_Value;
  }

  return Wifi_Slider_Value;
}


class inputStruct
{
  public function __construct($number)
  {
    $this->inputNumber=$number;
  }
  public $inputNumber=NULL;
  public $active=1;
  public $enable=1;
  public $on_pri=20;
  public $off_pri=20;
  public $on_type=10;
  public $off_type=10;
}

class inputs_controller 
{
  function __construct()
  {
  }

  function getMessagesType()
  {
    $out = $this->getMessagesTypeXML();

    if(!empty($out))
    {
      $xml = simplexml_load_string($out);

      $interface = array();
      $interface["None"]="0";
      foreach($xml->msg as $msg)
      {
        $interface[(string)$msg->attributes()->name]=(string)$msg;
      }
      return $interface;
    }
  }

  private function getMessagesTypeXML()
  {
    $sh_args = 'getMessagesType';
    $sh_out = atsexec(escapeshellcmd($sh_args));

    if(strcasecmp($sh_out,'phpcmd: fail') != 0 && strcasecmp($sh_out,'phpcmd: invalid command') != 0)
    {
      //debug('(inputs_controller.inc|getMessagesTypeXML()) get messagestype'.$sh_out);
      return $sh_out;
    }
    else
    {
      return false;
    }
  }
}


?>
