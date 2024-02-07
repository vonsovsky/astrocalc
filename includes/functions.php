<?php
  function degToRad($deg) {
  	return $deg * M_PI / 180;
  }
  
  function radToDeg($rad) {
  	return $rad * 180 / M_PI;
  }
  
  function julianDate($day, $month, $year) {
      //$day += gmdate("H", $this->time)/24 + date("i", $this->time)/1440 + date("s", $this->time)/86400;
  
      if ($month < 3)
      {
           $year -= 1;
           $month += 12;
      }
      $a = (int)($year/100);
      if ($year < 1582 or ($year == 1582 and $month < 10) or ($year == 1582 and $month == 10 and $day < 15))
           $b = 0;
      else
           $b = 2 - $a + (int)($a/4);
      return((int)(365.25*($year+4716)) + (int)(30.6001*($month+1)) + $day + $b - 1524.5);    
  }

  /*
  function julianDate($day, $month, $year) {
  	$a = (int)((14 - $month) / 12);
  	$y = $year + 4800 - $a;
  	$m = $month + 12 * $a - 3;
  
  	$JDN = $day + (int)((153 * $m + 2) / 5) + 365 * $y + (int)($y / 4) - (int)($y / 100) + (int)($y / 400) - 32045;
  
  	return $JDN;
  }
  */  
  
  function julianDateTime($day, $month, $year, $hour, $minute, $second) {
    return julianDate($day + $hour / 24.0 + $minute / 1440.0 + $second / 86400.0, $month, $year);
  }
  
  /*
   * Vzorec z http://astronomy.villanova.edu/links/jd.htm,
   * ten z Wikipedie vracel záporná data od 2456109.5 nahoru
   */
  function printJulianDate($JD, $printDay = true) {
    if ($JD == '***') // Měsíc někdy neprojde nebeským poledníkem
      return $JD;
    
    $X = $JD + 0.5;
    $Z = floor($X);
    $F = $X - $Z;
    $Y = floor(($Z-1867216.25) / 36524.25);
    $A = $Z + 1 + $Y - floor($Y / 4);
    $B = $A + 1524;
    $C = floor(($B - 122.1) / 365.25);
    $D = floor(365.25 * $C);
    $G = floor(($B - $D) / 30.6001);
    $month = ($G < 13.5) ? ($G - 1) : ($G - 13);
    $year = ($month < 2.5) ? ($C - 4715) : ($C - 4716);
    $UT = $B - $D - floor(30.6001 * $G) + $F;
    $day = floor($UT);
    $UT -= floor($UT);
    $UT *= 24;
    $hour = floor($UT);
    $UT -= floor($UT);
    $UT *= 60;
    $minute = floor($UT);
    // sekundy se normálně dále neposílají, jsou tady jen na debug
    $UT -= floor($UT);
    $UT *= 60;
    $second = $UT;
    
  	// nějaké dodatečné formátování, 1:3 taky vypadá blbě
    if ($minute < 10) $minute = '0'.$minute;
    if ($hour < 10) $hour = '0'.$hour;
    if ($second < 10) $second = '0'.$second;

    if ($printDay)
      return sprintf("%d. %d. %d %s:%s<br />", $day, $month, $year, $hour, $minute);
    else return sprintf("%s:%s<br />", $hour, $minute);
  }

  function localSiderealTime($longitude, $day, $month, $year, $hour, $minute, $second) {
    if ($month == 0 && $year == 0)
      $JD = round($day) - 0.5;
    else $JD = julianDate($day, $month, $year) - 0.5;

    $T = ($JD - 2451545.0) / 36525;
    $LST = normalizeAngle(280.46061837 + 360.98564736629 * ($JD - 2451545.0) + 0.000387933 * $T * $T - $T * $T * $T / 38710000.0);
  
  	$time = $hour + $minute / 60.0 + $second / 3600.0;
  	$LST = normalizeAngle($LST + 15 * $time * 1.002737908 - $longitude);
  
  	return $LST;
  }
  
  function localSiderealDate($longitude, $day, $month = 0, $year = 0) {
  	return localSiderealTime($longitude, $day, $month, $year, 12, 0, 0);
  }

  // prevede uhel do intervalu <0, 360)
  function normalizeAngle($angle) {
  	//printf("angle: %lf\n", angle);
  	if ($angle >= 360)
  		$angle -= (int)($angle / 360) * 360;
  	// tohle by melo jit max do -360, proto staci jednou
  	if ($angle < 0)
  		$angle += 360;
  	//printf("angle2: %lf\n", angle);
  
  	return $angle;
  }

  // Doplní decimální souřadnice, pokud jsou vyplněny alespoň stupně
  function fromPostDeg($decPost, $degPost) {
    $degs = explode(' ', $_POST[$degPost]);
    for ($i = 0; $i <= 2; $i++)
      if (!isset($degs[$i]) || !($degs[$i] != 0)) $degs[$i] = 0;
    $deg = new Deg($degs[0], $degs[1], $degs[2]);
    $_POST[$decPost] = $deg->degToDec();
  }

  // z vlastního pohybu hvězdy upraví rektascenzi a deklinaci
  // výstupem jsou celé hodnoty, ne delta
  function properMotion($deltaJD, $ra0, $dec0, $pmra, $pmde) {
  	$t = $deltaJD / 365.25;

    $pm = sqrt(pow($pmra / 3600 * 15 * cosd($dec0), 2) + pow($pmde / 3600, 2));
    //$phi0 = acosd($pmde / 3600 / $pm);
    //$phi0 = asind($pmra / 3600 * 15 * cosd($dec0) / $pm);
    //echo $phi0;
    //echo $pmra / 3600 * 15 * cosd($dec0).'<br />';
    $dec = asind(sind($dec0) * cosd($pm * $t) +
                         cosd($dec0) * sind($pm * $t) * $pmde / 3600 / $pm);
    $ra = atand($pmra / 3600 * 15 * cosd($dec0) / $pm * sind($pm * $t) / 
            (cosd($dec0) * cosd($pm * $t) -
             sind($dec0) * sind($pm * $t) * $pmde / 3600 / $pm));
  
    return array($ra, $dec - $dec0);
  }
  
  // počítá denní aberaci z hodinového úhlu, deklinace a šířky
  // výstupem je rozdíl rektascenze a deklinace
  function dailyAberration($t, $dec0, $latitude) {
    $o = 1;  // vzdálenost od středu Země

    $dRa = 0.02132 * $o * cosd(($latitude)) * cosd(($t)) / cosd(($dec0));
    $dDec = 0.32 * $o * sind(($latitude)) * sind(($dec0)) * sind(($t));
  
    return array($dRa / 15, $dDec);
  }

  // počítá roční aberaci, vedle rektasceze vyžaduje navíc geocentrickou ekliptikální
  // délku Slunce (ls) a sklon délky k rovníku (e)
  function yearAberration($ra0, $dec0, $epsilon, $lams) {
    $dRa = (-20.496 * (cosd(($ra0)) * cosd(($lams)) * cosd(($epsilon)) +
                       sind(($ra0)) * sind(($lams)))) /
           cosd(($dec0));
    $dDec = -20.496 * ( cosd(($lams)) * cosd(($epsilon)) *
                        (tand(($epsilon)) * cosd(($dec0)) -
                         sind(($ra0)) * sind(($dec0))) +
                      cosd(($ra0)) * sind(($dec0)) * sind(($lams)) );

    return array($dRa / 15, $dDec);
  }
  
  // korekce denní paralaxy, delta je geocentrická vzdálenost v AU
  // vrací se rozdíl rektascenze a deklinace
  function dailyParallax($t, $dec0, $latitude, $delta) {
    $o = 1; // vzdálenost od středu Země
    
    $pi = 8.794 / $delta / 3600;
    $dRa = radToDeg(atan(
            -$o * cosd(($latitude)) * sind(($pi)) * sind(($t)) /
            (cosd(($dec0)) - $o * cosd(($latitude)) * sind(($pi)) * cosd(($t)))));
    $dec = radToDeg(atan(
           (sind(($dec0)) - $o * sind(($latitude)) * sind(($pi))) * cosd(($dRa)) /
            (cosd(($dec0)) - $o * cosd(($latitude)) * sind(($pi)) * cosd(($t)))));
  
    return array($dRa / 15, $dec - $dec0);
  }
  
  function sind($deg) {
    return sin(degToRad($deg));
  }
  
  function cosd($deg) {
    return cos(degToRad($deg));
  }
  
  function tand($deg) {
    return tan(degToRad($deg));
  }
  
  function asind($rad) {
    return radToDeg(asin($rad));
  }

  function acosd($rad) {
    return radToDeg(acos($rad));
  }

  function atand($rad) {
    return radToDeg(atan($rad));
  }
  
  function atand2($rad1, $rad2) {
    return radToDeg(atan2($rad1, $rad2));
  }

  // vrátí zlomkovou část čísla
  function frac($num) {
    return $num - (int)$num;
  }

  // získá pozice planet nebo Měsíce
  function getRisePos($time, $name) {
    $obj = new Objects($time, $name);
    call_user_func(array($obj, $name));
    $data = $obj->obj[$name];

    // po převodech zůstane pole{rektascenze, deklinace}
    $pos = $obj->heliocentricToEcliptical($data['L'], $data['B'], $data['R']);
    $pos = $obj->eclipticalToEquatorial($pos[0], $pos[1]);
    
    return $pos;
  }
  
  // jednoduchý obrázek Měsíce do mapky minim
  function moonP($img, $JD, $startx, $starty, $w, $h) {
    $white_t = ImageColorAllocate($img, 254, 254, 254);
    $gray = ImageColorAllocate($img, 40, 40, 40);
    imagefill($img, 0, 0, $white_t);
    imagecolortransparent($img, $white_t);
    
    $dorusta = true;
    $obj = new Objects($JD);
    $f = $obj->moonPhase();

    $px = $w * 0.5 + $startx;
    $py = $h * 0.50667 + $starty;
    $rx = $w * 0.887;
    $ry = $h * 0.887;

    // první čtvrť až úplněk
    if ($f >= 0 && $f <= 0.25) { // ok
      ImageFilledArc($img, $px, $py, $rx * 4 * $f, $ry, 90, 270, $gray, IMG_ARC_PIE);
      ImageFilledArc($img, $px, $py, $rx, $ry, 270, 90, $gray, IMG_ARC_PIE);
    }
    // úplněk až poslední čtvrť
    if ($f > 0.25 && $f <= 0.5) {
      ImageFilledArc($img, $px, $py, $rx, $ry, 90, 270, $gray, IMG_ARC_PIE);
      ImageFilledArc($img, $px, $py, $rx - 4 * ($f - 0.25) * $rx, $ry, 270, 90, $gray, IMG_ARC_PIE);
    }
    // poslední čtvrť až nov
    if ($f > 0.5 && $f <= 0.75) {
      ImageFilledArc($img, $px, $py, $rx, $ry, 90, 270, $gray, IMG_ARC_PIE);
      ImageFilledArc($img, $px, $py, $rx * 4 * ($f - 0.5), $ry, 90, 270, $white_t, IMG_ARC_PIE);
    }
    // nov až první čtvrť
    if ($f > 0.75 && $f <= 1) {
      ImageFilledArc($img, $px, $py, $rx, $ry, 270, 90, $gray, IMG_ARC_PIE);
      ImageFilledArc($img, $px, $py, $rx * 4 * (1 - $f), $ry, 270, 90, $white_t, IMG_ARC_PIE);
    }
  }
  
  function parseDateToJD($dateString) {
    if (strstr($dateString, '-') !== false) {
      $date = explode('-', $dateString);
      $year = trim($date[0]);
      $month = trim($date[1]);
  
      $date = explode(' ', trim($date[2]));
      $day = trim($date[0]);
      
      $date = explode(':', trim($date[1]));
      $hour = $date[0];
      $minute = $date[1];
    } else {    
      $date = explode('.', $dateString);
      $day = trim($date[0]);
      $month = trim($date[1]);
  
      $date = explode(' ', trim($date[2]));
      $year = trim($date[0]);
      
      $date = explode(':', trim($date[1]));
      $hour = $date[0];
      $minute = $date[1];
    }
    
    return julianDateTime($day, $month, $year, $hour, $minute, 0);
  }
  
  function getParametrizedVariables($astroCalc, $t1, $t2, $f1, $f2, $offset, $search_query, $min_altitude) {
    $stars = dibi::query('SELECT [id], [name], [period], [ra], [dec], [minima_info],
                                 (%f - %f/24.0 - minima) / period AS [t1], (%f - %f/24.0 - minima) / period AS [t2],
                                 CAST((%f - %f/24.0 - minima) / period AS INTEGER) -
                                 CAST((%f - %f/24.0 - minima) / period AS INTEGER) AS days
                          FROM [ephem]
                          WHERE [name] LIKE %s AND
                               ((t1) - CAST(t1 AS INTEGER) < %f AND %f < t2 - CAST(t2 AS INTEGER) + days OR
                                (t1) - CAST(t1 AS INTEGER) < %f - 1 AND %f - 1 < t2 - CAST(t2 AS INTEGER) + days)
                          LIMIT 3000',
                          $t1, $offset, $t2, $offset, $t2, $offset, $t1, $offset,
                          '%'.$search_query.'%', $f2, $f1, $f2, $f1)->fetchAll();
    
    foreach ($stars as $index => $star) {
      $tf1 = frac($star->t1);
      $tf2 = frac($star->t2);
      if ($tf1 < $f2 - 1) $tf1++;
      if ($tf2 < $f1) $tf2++;
  
      if ($tf1 < $f1) {
        $from = $t2 - ($tf2 - $f1) * $star->period;
      } else {
        $from = $t1;
      }
      
      if ($tf2 > $f2) {
        $to = $t2 - ($tf2 - $f2) * $star->period;
      } else {
        $to = $t2;
      }
      
      // pokud perioda umožňuje v zadaném časovém intervalu vícekrát požadovanou fázi
      $star->to = array();
      for ($i = $to; $i > $t1; $i -= $star->period)
        $star->to[] = $i;
      
      $star->from = array();
      for ($i = $from; $i > $t1; $i -= $star->period)
        $star->from[] = $i;
      // pokud je to víckrát než from, from je na začátku požadovaného času
      if (count($star->to) > count($star->from))
        $star->from[] = $t1;
      
      // každý počátační čas, kdy hvězda nevyleze nad zadanou hodnotu, jde z kola ven
      foreach ($star->from as $from_index => $from) {
        $astroCalc->setDateTime($from - $offset / 24);
        $pos = $astroCalc->calcObjectPos($star->ra, $star->dec, false);
        if ($pos[0] < $min_altitude)
          unset($star->from[$from_index]);
      }
      if (count($star->from) == 0)
        unset($stars[$index]);
  
      // hvězdy, které aspoň na jednu hodinu nevylezou nad zadanou výšku, půjdou z kola ven
      /*
      $rise = false;
      for ($i = $star->from; $i < $star->to; $i += 1 / 24.0) {
        $astroCalc->setDateTime($i - $star->from - $offset / 24);
        $pos = $astroCalc->calcObjectPos($star->ra, $star->dec, false);
        if ($pos[0] > $min_altitude) {
          $rise = true;
          break;
        }
      }
      if (!$rise) unset($stars[$index]);
      */
    }
    
    return $stars;
  }
?>