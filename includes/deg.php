<?php
  class DEG {
    var $degrees;
    var $minutes;
    var $seconds;
    
    function __construct($degrees = 0, $minutes = 0, $seconds = 0) {
      // předán jen jeden parametr, pravděpodobně půjde o decimální hodnotu stupně
      if ($degrees != 0 && $minutes == 0 && $seconds == 0)
        $this->decToDeg($degrees);
      else {
        $this->degrees = $degrees;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
      }
    }

    // 121°8'6'' = 121.135°
    function degToDec() {
    	$frac = $this->minutes + $this->seconds / 60.0;
    	if ($this->degrees >= 0 && (!isset($this->degrees[0]) || $this->degrees[0] != '-'))
    		$frac = $this->degrees + $frac / 60.0;
    	// zaporne stupne
    	else $frac = $this->degrees - $frac / 60.0;
    
    	return $frac;
    }

    // 121.135° = 121°8'6'', -16.608 = -16°36'28.8
    function decToDeg($dec) {
    	$dec = trim($dec);
      
      $this->degrees = (int)$dec;
      if ($dec[0] == '-' && $this->degrees == 0)
        $this->degrees = '-'.$this->degrees;
    	$frac = ($dec - (int)$dec) * 60;
    	if ($frac < 0) $frac *= -1;
    	$this->minutes = (int)$frac;
    	$frac = ($frac - (int)$frac) * 60;
    	if ($frac < 0) $frac *= -1;
    	$this->seconds = $frac;
    }
  }
?>