<?php
class AstroCalc {
  // Defaultní hodnoty jsou pro Brno
  var $latitude = 49.1952;
  var $longitude = -16.608;
  var $localOffset = +1;
  var $zenith = 90.83333;
  
  var $JD = 0;
  var $deltaJD;
  var $t, $T2, $T3, $T4;
  var $cT1, $cT2, $cT3, $cT4;
  
  var $LST;

  // Slunce a Měsíc ovlivňují Zemi přímo, proto jsou jejich hodnoty
  // v hlavní třídě, slouží k výpočtu roční aberace
  var $Lm;
  var $Mm;
  var $um;
  var $psi;
  var $omega;
  var $lams;

  var $Ls;
  var $Ms;
  
  var $epsilon;
  
  function __construct($latitude = 49.1952, $longitude = -16.608, $localOffset = +1, $zenith = 90.83333) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->localOffset = $localOffset;
    $this->zenith = $zenith;
  }
  
  function setDateTime($day, $month = 0, $year = 0, $hour = 12, $minute = 0, $second = 0) {
  	if ($month == 0 && $year == 0)
      $this->JD = $day;
    else
    	$this->JD = julianDateTime($day, $month, $year, $hour, $minute, $second);
      
    //echo julianDateTime(intval($day), intval($month), intval($year), 11, intval($minute), intval($second));
    $this->JD -= $this->localOffset / 24;
      
    $J2000 = julianDate(1, 1, 2000);
  	$this->deltaJD = $this->JD - $J2000;
    // vypočítá některé proměnné Slunce a Měsíce pro nastavené datum
    //$this->posSunMoon();

    $this->LST = $this->siderealTime();
    $this->getCorrection();

    // časy v tisicíletích
    $this->t = ($this->JD - 2451545.0) / 365250;
    $this->T2 = $this->t * $this->t;
    $this->T3 = $this->T2 * $this->t;
    $this->T4 = $this->T3 * $this->t;

    // časy ve stoletích
    $this->cT1 = ($this->JD - 2451545.0) / 36525;
    $this->cT2 = $this->cT1 * $this->cT1;
    $this->cT3 = $this->cT2 * $this->cT1;
    $this->cT4 = $this->cT3 * $this->cT1;
  }
  
  // http://en.wikipedia.org/wiki/Sunrise_equation
  function sunrise($day = 0, $month = 0, $year = 0) {
    if ($day == 0)
      $JD = $this->JD;
    elseif ($month == 0 && $year == 0)
      $JD = $day;
    else
      $JD = julianDate($day, $month, $year);

    $n = round($JD - julianDate(1, 1, 2000) - 0.0009 - $this->longitude / 360);
  	$J = julianDate(1, 1, 2000) + 0.0009 + $this->longitude / 360 + $n;
  	$M = 357.5291 + 0.98560028 * ($J - julianDate(1, 1, 2000));
  	$M = $M - (int)($M / 360) * 360;
  	$C = 1.9148 * sind($M) + 0.02 * sind(2 * $M) + 0.0003 * sind(3 * $M);
  	$lambda = $M + 102.9372 + $C + 180;
  	$lambda = $lambda - (int)($lambda / 360) * 360;
  	$Jtransit = $J + 0.0053 * sind($M) - 0.0069 * sind(2 * $lambda) + 0.5;
  	$delta = asind(sind($lambda) * sind($this->epsilon));
  	$cosOmega = (cosd($this->zenith) - sind($this->latitude) *
  					     sind($delta)) / (cosd($this->latitude) * cosd($delta));
    if ($cosOmega > 1 || $cosOmega < -1) {
      $Jtransit += $this->localOffset / 24.0;
      if ($cosOmega > 1)
        return array('vychod' => 'NR', 'pruchod' => $Jtransit);
      else return array('vychod' => 'NS', 'pruchod' => $Jtransit);
    } else {
    	$omega = acosd($cosOmega);
      $Jset = 2451545 + 0.0009 + (($omega + $this->longitude) / 360 + $n + 0.0053 * sind($M)) - 0.0069 * sind(2 * $lambda);
    	$Jrise = $Jtransit - ($Jset - $Jtransit);
    
    	$Jtransit += $this->localOffset / 24.0; $Jrise += $this->localOffset / 24.0; $Jset += $this->localOffset / 24.0;
    
      return array('vychod' => $Jrise, 'pruchod' => $Jtransit, 'zapad' => $Jset);
    }
  }
  
  function calcObjectPos($degAlfa, $degDelta, $returnDeg = true) {
    if ($this->JD == 0 || $this->deltaJD == 0) {
      echo 'Datum neinicializováno';
      return;
    }
    
    // rektascenze a deklinace daného objektu
    if (is_object($degAlfa))
      $alfa = $degAlfa->degToDec();
    else $alfa = $degAlfa;
  	if (is_object($degDelta))
      $delta = $degDelta->degToDec();
    else $delta = $degDelta;
    
    $alfa *= 15;
  
  	// hodinovy uhel - pred jakou siderickou dobou prosel objekt pres nebesky polednik
  	$H = normalizeAngle($this->LST + 180 + $this->localOffset * 15 - $alfa);
  	// poloha na obloze v danou dobu

    // Korekce o denní aberaci, návrat je ve vteřinách
    $pos = dailyAberration(-$H * 15, $delta, $this->latitude);
    $delta += $pos[1] / 3600;

    // Korekce o roční aberaci, návrat je ve vteřinách
    if ($this->Lm == 0)
      $this->posSunMoon();
    $pos = yearAberration($alfa, $delta, $this->epsilon, $this->lams);
    $delta += $pos[1] / 3600;

    // Azimut
  	$A = atand2(sind($H), cosd($H) * sind($this->latitude) -
                tand($delta) * cosd($this->latitude));
  	// vyska
    $h = asind(sind($this->latitude) * sind($delta) +
               cosd($this->latitude) * cosd($delta) * cosd($H));
  
  	$A += 180;
  	$A = normalizeAngle($A);
  
  	$degAzimut = new Deg($A);
  	$altitude = new Deg($h);
  	$hAngle = new Deg($H / 15.04107);
  
    if ($returnDeg)
      return array($altitude, $degAzimut);
    else return array($h, $A);
  }
  
  /* Vypočítá východ a západ objektu
   * $mv je na rozlišení odkud se metoda volá kvůli specifickým
   * korekcím pro průchod nebeským poledníkem
   *   $mv = 0 > variable.php, bsc.php
   *   $mv = 1 > rising.php      
   *   $mv = 3 > planets.php   
   *
   * http://www.stjarnhimlen.se/comp/riset.html
   */
  
  // Korekce o aberaci v případě planet?
  function objRise($degAlfa, $degDelta, $mv = 0) {
    if ($this->JD == 0 || $this->deltaJD == 0) {
      echo 'Datum neinicializováno';
      return;
    }
    
  	// rektascenze a deklinace daneho objektu
    if (is_object($degAlfa))
      $alfa = $degAlfa->degToDec();
    else $alfa = $degAlfa;
  	if (is_object($degDelta))
      $delta = $degDelta->degToDec();
    else $delta = $degDelta;
    
    $alfa *= 15;
  
    $south = normalizeAngle($alfa - $this->LST + ($mv % 2) * 180 + $this->localOffset * 15) / 15.0;

    // Korekce o denní aberaci
    $pos = dailyAberration(-$south * 15, $delta, $this->latitude);
    $alfa += $pos[0] / 3600; $delta += $pos[1] / 3600;

    // Korekce o roční aberaci
    if ($this->Lm == 0)
      $this->posSunMoon();
    $pos = yearAberration($alfa, $delta, $this->epsilon, $this->lams);
    $alfa += $pos[0] / 3600; $delta += $pos[1] / 3600;

    $cosOmega = sind($this->zenith - 90) - sind($this->latitude) * sind($delta) / (cosd($this->latitude) * cosd($delta));
    
    // průchod poledníku je třeba občas doladit o +- 1 den, aby seděl s datem uvedeným na začátku řádku tabulky na výstupu
    if ($mv == 0 && round($this->JD + $south / 24.0 + $this->localOffset / 24.0) != round($this->JD + $this->localOffset / 24.0))
      $Jtransit = $this->JD - 1 + $south / 24.0;
    elseif (($mv == 1 || $mv == 3) && round($this->JD + $south / 24.0) != round($this->JD + $this->localOffset / 24.0))
      $Jtransit = $this->JD + 1 + $south / 24.0;
    else $Jtransit = $this->JD + $south / 24.0;
  
  	if ($cosOmega > 1 || $cosOmega < -1) {
      if ($cosOmega > 1)
  			return array('vychod' => 'NR', 'pruchod' => $Jtransit);
  		else return array('vychod' => 'NS', 'pruchod' => $Jtransit);
      
    } else {
  		$LHA = acosd($cosOmega);
  		$LHA = normalizeAngle($LHA) / 15.04107 / 24.0;
  		$Jrise = $Jtransit - $LHA;
  		$Jset = $Jtransit + $LHA;
  
      return array('vychod' => $Jrise, 'pruchod' => $Jtransit, 'zapad' => $Jset);
  	}
  }
  
  function doCorrections($ra0, $dec0, $pmra = 0, $pmde = 0, $dist = 0) {
    $dRa = 0;
    $dDec = 0;
    
    $this->cT1 = ($this->JD - 2451545.0) / 36525;
    $this->cT2 = $this->cT1 * $this->cT1;
    $this->cT3 = $this->cT2 * $this->cT1;
    $this->cT4 = $this->cT3 * $this->cT1;

    // Korekce o precesi, zdroj http://www.bbastrodesigns.com/coordErrors.html
    $radRA = degToRad($ra0) * 15;
    $radDec = degToRad($dec0 > 80 ? 80 : $dec0);
    $dzeta = degToRad((2306.2181 * $this->cT1 + 0.30188 * $this->cT2 + 0.017998 * $this->cT3) / 3600);
    $eta = degToRad((2306.2181 * $this->cT1 + 1.09468 * $this->cT2 + 0.018203 * $this->cT3) / 3600);
    $theta = degToRad((2004.3109 * $this->cT1 - 0.42665 * $this->cT2 - 0.041833 * $this->cT3) / 3600);

    $a = cos($radDec) * sin($radRA + $dzeta);
    $b = cos($theta) * cos($radDec) * cos($radRA + $dzeta) - sin($theta) * sin($radDec);
    $c = sin($theta) * cos($radDec) * cos($radRA + $dzeta) + cos($theta) * sin($radDec);

    // vlastní pohyb hvězdy
    if ($pmra != 0 || $pmde != 0) {
      $dRa += $pmra / 3600 / 15 * cosd($dec0) * 296;
      $dDec += $pmde / 3600 * 296;
    } 

    /*
    $degRA = new Deg($dRa);
    $degDec = new Deg($dDec);
    echo $degRA->degrees.'°'.$degRA->minutes."'".$degRA->seconds."''".'<br />';
    echo $degDec->degrees.'°'.$degDec->minutes."'".$degDec->seconds."''".'<br /><br />';
    */

    $ra = $ra0 + $dRa;
    $dec = $dec0 + $dDec;

    if ($ra > 24) $ra -= 24;
    if ($ra < 0) $ra += 24;
    if ($dec > 90) $dec = 180 - $dec;
    if ($dec < -90) $dec = -(180 + $dec);
    return array($ra, $dec);
  }
  
  function posSunMoon() {
    $T = 1 + $this->deltaJD / 36525;
    
    $this->Lm = normalizeAngle(360 * (0.606434 + 0.03660110129 * $this->deltaJD));
    $this->Mm = normalizeAngle(360 * (0.374897 + 0.03629164709 * $this->deltaJD));
    $this->um = normalizeAngle(360 * (0.259091 + 0.03674819520 * $this->deltaJD));
    $this->psi = normalizeAngle(360 * (0.827362 + 0.03386319198 * $this->deltaJD));
    $this->omega = normalizeAngle(360 * (0.347343 - 0.00014709391 * $this->deltaJD));
    $this->Ls = normalizeAngle(360 * (0.779072 + 0.00273790931 * $this->deltaJD));
    $this->Ms = normalizeAngle(360 * (0.993126 + 0.00273777850 * $this->deltaJD));
    
    $this->lams = $this->Ls + 6910 * sind($this->Ms) +
                                72 * sind(2 * $this->Ms) -
                                17 * $T * sind($this->Ms);/* -
                                7 * cosd($this->Ms - $this->M5) +
                                6 * sind($this->Lm - $this->Ls) +
                                5 * sind(4 * $this->Ms - 8 * $this->M4 + 3 * $this->M5) -
                                5 * cosd(2 * $this->Ms - 2 * $this->M2) -
                                4 * sind($this->Ms - $this->M2) +
                                4 * cosd(4 * $this->Ms - 8 * $this->M4 + 3 * $this->M5) +
                                3 * sind(2 * $this->Ms - 2 * $this->M2) -
                                3 * sind($this->M5) -
                                3 * sind(2 * $this->Ms - 2 * $this->M5);*/
    $this->lams = normalizeAngle($this->lams);
  }
  
  // vypočítá sklon ekliptiky, nutaci a výstřednost Země
  // celestial Sphere
  function getCorrection() {
    $t = ($this->JD - 2451545.0) / 36525;

    // sklon osy
    $this->epsilon = epsilon_mean($this->JD);

    // oprava sklonu osy o nutaci ve sklonu
    $nutation = getNutation($this->JD);
    $this->epsilon += $nutation[1];
  }
  
	// pozorovatel - podle pozice se spočítá siderický čas
  function siderealTime() {
  	$theta0 = 280.46061837;  // Zeme
  	$theta1 = 360.98564736629; // Zeme
    $T = $this->deltaJD / 36525.0; // juliánská století od J2000
  	return normalizeAngle($theta0 + $theta1 * ($this->deltaJD) + 0.000387933 * $T * $T - $T * $T * $T / 38710000.0 - $this->longitude);
  }
}
?>
