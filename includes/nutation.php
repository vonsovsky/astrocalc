<?php
/*
    © Jay Tanner - PHP Science Labs - 2012
    Compute nutation in longitude and in obliquity
 */

// vrací pole array(deltaPSI, deltaEpsilon)
function getNutation($JD) { 
  
  $T = ($JD - 2451545.0) / 36525.0;

  $T2 = $T * $T;
  $T3 = $T * $T2;
  $T4 = $T * $T3;

  $DegToRad = 3.141592653589793 / 180.0;

  //Compute mean anomaly of the Moon in radians
  $L = $DegToRad * ((485868.249036 + 1717915923.2178 * $T + 31.8792 * $T2
     + 0.051635 * $T3 - 0.00024470 * $T4) / 3600.0);

  //Compute mean anomaly of the Sun in radians
  $Lp = $DegToRad*((1287104.79305 + 129596581.0481 * $T
        - 0.5532 * $T2  + 0.000136 * $T3 - 0.00001149 * $T4) / 3600.0);

  //Compute mean argument of the latitude of the Moon in radians
  $F = $DegToRad*((335779.526232 + 1739527262.8478 * $T
     - 12.7512 * $T2 - 0.001037 * $T3 + 0.00000417 * $T4) / 3600.0);

  //Compute mean elongation of the Moon from the Sun in radians
  $D = $DegToRad*((1072260.70369 + 1602961601.2090 * $T
      - 6.3706 * $T2  + 0.006593 * $T3 - 0.00003169 * $T4) / 3600.0);

  //Compute mean longitude of the ascending node of the Moon in radians
  $Om = $DegToRad*((450160.398036 - 6962890.5431 * $T
       + 7.4722 * $T2  + 0.007702 * $T3 - 0.00005939 * $T4) / 3600.0);


  //COMPUTE NUTATION IN $LONGITU$DE ACCOR$DING TO IAU 2000B NUTATION SERIES
  $s = 0;
  $s = $s + (-172064161 - 174666*$T)*sin($Om) + 33386*cos($Om);
  $s = $s + (-13170906 - 1675*$T)*sin(2*($F - $D + $Om)) - 13696*cos(2*($F - $D + $Om));
  $s = $s + (-2276413 - 234*$T)*sin(2*($F + $Om)) + 2796*cos(2*($F + $Om));
  $s = $s + (2074554 + 207*$T)*sin(2*$Om) - 698*cos(2*$Om);
  $s = $s + (1475877 - 3633*$T)*sin($Lp) + 11817*cos($Lp);
  $s = $s + (-516821 + 1226*$T)*sin($Lp + 2*($F - $D + $Om)) - 524*cos($Lp + 2*($F - $D + $Om));
  $s = $s + (711159 + 73*$T)*sin($L) - 872*cos($L);
  $s = $s + (-387298 - 367*$T)*sin(2*$F + $Om) + 380*cos(2*$F + $Om);
  $s = $s + (-301461 - 36*$T)*sin($L + 2*($F + $Om)) + 816*cos($L + 2*($F + $Om));
  $s = $s + (215829 - 494*$T)*sin(2*($F - $D + $Om) - $Lp) + 111*cos(2*($F - $D + $Om) - $Lp);
  $s = $s + (128227 + 137*$T)*sin(2*($F - $D) + $Om) + 181*cos(2*($F - $D) + $Om);
  $s = $s + (123457 + 11*$T)*sin(2*($F + $Om) - $L) + 19*cos(2*($F + $Om) - $L);
  $s = $s + (156994 + 10*$T)*sin(2*$D - $L) - 168*cos(2*$D - $L);
  $s = $s + (63110 + 63*$T)*sin($L + $Om) + 27*cos($L + $Om);
  $s = $s + (-57976 - 63*$T)*sin($Om - $L) - 189*cos($Om - $L);
  $s = $s + (-59641 - 11*$T)*sin(2*($F + $D + $Om) - $L) + 149*cos(2*($F + $D + $Om) - $L);
  $s = $s + (-51613 - 42*$T)*sin($L + 2*$F + $Om) + 129*cos($L + 2*$F + $Om);
  $s = $s + (45893 + 50*$T)*sin(2*($F - $L) + $Om) + 31*cos(2*($F - $L) + $Om);
  $s = $s + (63384 + 11*$T)*sin(2*$D) - 150*cos(2*$D);
  $s = $s + (-38571 - $T)*sin(2*($F + $D + $Om)) + 158*cos(2*($F + $D + $Om));
  $s = $s + 32481*sin(2*($F - $Lp - $D + $Om));
  $s = $s - 47722*sin(2*($D - $L)) + 18*cos(2*($D - $L));
  $s = $s + (-31046 - $T)*sin(2*($L + $F + $Om)) + 131*cos(2*($L + $F + $Om));
  $s = $s + 28593*sin($L + 2*($F - $D + $Om)) - cos($L + 2*($F - $D + $Om));
  $s = $s + (20441 + 21*$T)*sin(2*$F + $Om - $L) + 10*cos(2*$F + $Om - $L);
  $s = $s + 29243*sin(2*$L) - 74*cos(2*$L);
  $s = $s + 25887*sin(2*$F) - 66*cos(2*$F);
  $s = $s + (-14053 - 25*$T)*sin($Lp + $Om) + 79*cos($Lp + $Om);
  $s = $s + (15164 + 10*$T)*sin(-$L + 2*$D + $Om) + 11*cos(-$L + 2*$D + $Om);
  $s = $s + (-15794 + 72*$T)*sin(2*($Lp + $F - $D + $Om)) - 16*cos(2*($Lp + $F - $D + $Om));
  $s = $s + 21783*sin(2*($D - $F)) + 13*cos(2*($D - $F));
  $s = $s + (-12873 - 10*$T)*sin($L - 2*$D + $Om) - 37*cos($L - 2*$D + $Om);
  $s = $s + (-12654 + 11*$T)*sin(-$Lp + $Om) + 63*cos(-$Lp + $Om);
  $s = $s - 10204*sin(2*($F + $D) + $Om - $L) - 25*cos(2*($F + $D) + $Om - $L);
  $s = $s + (16707 - 85*$T)*sin(2*$Lp) - 10*cos(2*$Lp);
  $s = $s - 7691*sin($L + 2*($F + $D + $Om)) - 44*cos($L + 2*($F + $D + $Om));
  $s = $s - 11024*sin(2*($F - $L)) + 14*cos(2*($F - $L));
  $s = $s + (7566 - 21*$T)*sin($Lp + 2*($F + $Om)) - 11*cos($Lp + 2*($F + $Om));
  $s = $s + (-6637 - 11*$T)*sin(2*($F + $D) + $Om) + 25*cos(2*($F + $D) + $Om);
  $s = $s + (-7141 + 21*$T)*sin(2*($F + $Om) - $Lp) + 8*cos(2*($F + $Om) - $Lp);
  $s = $s + (-6302 - 11*$T)*sin(2*$D + $Om) + 2*cos(2*$D + $Om);
  $s = $s + (5800 + 10*$T)*sin($L + 2*($F - $D) + $Om) + 2*cos($L + 2*($F - $D) + $Om);
  $s = $s + 6443*sin(2*($L + $F - $D + $Om)) - 7*cos(2*($L + $F - $D + $Om));
  $s = $s + (-5774 - 11*$T)*sin(2*($D - $L) + $Om) - 15*cos(2*($D - $L) + $Om);
  $s = $s - 5350*sin(2*($L + $F) + $Om) - 21*cos(2*($L + $F) + $Om);
  $s = $s + (-4752 - 11*$T)*sin(2*($F - $D) + $Om - $Lp) - 3*cos(2*($F - $D) + $Om - $Lp);
  $s = $s + (-4940 - 11*$T)*sin($Om - 2*$D) - 21*cos($Om - 2*$D);
  $s = $s + 7350*sin(2*$D - $L - $Lp) - 8*cos(2*$D - $L - $Lp);
  $s = $s + 4065*sin(2*($L - $D) + $Om) + 6*cos(2*($L - $D) + $Om);
  $s = $s + 6579*sin($L + 2*$D) - 24*cos($L + 2*$D);
  $s = $s + 3579*sin($Lp + 2*($F - $D) + $Om) + 5*cos($Lp + 2*($F - $D) + $Om);
  $s = $s + 4725*sin($L - $Lp) - 6*cos($L - $Lp);
  $s = $s - 3075*sin(2*($F + $Om - $L)) + 2*cos(2*($F + $Om - $L));
  $s = $s - 2904*sin(3*$L + 2*($F + $Om)) - 15*cos(3*$L + 2*($F + $Om));
  $s = $s + 4348*sin(2*$D - $Lp) - 10*cos(2*$D - $Lp);
  $s = $s - 2878*sin($L - $Lp + 2*($F + $Om)) - 8*cos($L - $Lp + 2*($F + $Om));
  $s = $s - 4230*sin($D) - 5*cos($D);
  $s = $s - 2819*sin(2*($F + $D + $Om) - $L - $Lp) - 7*cos(2*($F + $D + $Om) - $L - $Lp);
  $s = $s - 4056*sin(2*$F - $L) - 5*cos(2*$F - $L);
  $s = $s - 2647*sin(2*($F + $D + $Om) - $Lp) - 11*cos(2*($F + $D + $Om) - $Lp);
  $s = $s - 2294*sin($Om - 2*$L) + 10*cos($Om - 2*$L);
  $s = $s + 2481*sin($L + $Lp + 2*($F + $Om)) - 7*cos($L + $Lp + 2*($F + $Om));
  $s = $s + 2179*sin(2*$L + $Om) - 2*cos(2*$L + $Om);
  $s = $s + 3276*sin($Lp + $D - $L) + cos($Lp + $D - $L);
  $s = $s - 3389*sin($L + $Lp) - 5*cos($L + $Lp);
  $s = $s + 3339*sin($L + 2*$F) - 13*cos($L + 2*$F);
  $s = $s - 1987*sin(2*($F - $D) + $Om - $L) + 6*cos(2*($F - $D) + $Om - $L);
  $s = $s - 1981*sin($L + 2*$Om);
  $s = $s + 4026*sin($D - $L) - 353*cos($D - $L);
  $s = $s + 1660*sin(2*$F + $D + 2*$Om) - 5*cos($D + 2*($F + $Om));
  $s = $s - 1521*sin(2*($F + 2*$D + $Om) - $L) - 9*cos(2*($F + 2*$D + $Om) - $L);
  $s = $s + 1314*sin($Lp + $D + $Om - $L);
  $s = $s - 1283*sin(2*($F - $D - $Lp) + $Om);
  $s = $s - 1331*sin($L + 2*$F + 2*$D + $Om) - 8*cos($L + 2*($F + $D) + $Om);
  $s = $s + 1383*sin(2*($F - $L + $D + $Om)) - 2*cos(2*($F - $L + $D + $Om));
  $s = $s + 1405*sin(2*$Om - $L) + 4*cos(2*$Om - $L);
  $s = $s + 1290*sin($L + $Lp + 2*($F - $D + $Om));

  $dPsiDeg = $s / 36000000000.0;

  //COMPUTE NUTATION IN OBLIQUITY (dEps) ACCORDING TO IAU 2000B NUTATION SERIES

  $s = 0;
  $s = $s + (92052331 + 9086*$T)*cos($Om) + 15377*sin($Om);
  $s = $s + (5730336 - 3015*$T)*cos(2*($F - $D + $Om)) - 4587*sin(2*($F - $D + $Om));
  $s = $s + (978459 - 485*$T)*cos(2*($F + $Om)) + 1374*sin(2*($F + $Om));
  $s = $s + (-897492 + 470*$T)*cos(2*$Om) - 291*sin(2*$Om);
  $s = $s + (73871 - 184*$T)*cos($Lp) - 1924*sin($Lp);
  $s = $s + (224386 - 677*$T)*cos($Lp + 2*($F - $D + $Om)) - 174*sin($Lp + 2*($F - $D + $Om));
  $s = $s - 6750*cos($L) - 358*sin($L);
  $s = $s + (200728 + 18*$T)*cos(2*$F + $Om) + 318*sin(2*$F + $Om);
  $s = $s + (129025 - 63*$T)*cos($L + 2*($F + $Om)) + 367*sin($L + 2*($F + $Om));
  $s = $s + (-95929 + 299*$T)*cos(2*($F - $D + $Om) - $Lp) + 132*sin(2*($F - $D + $Om) - $Lp);
  $s = $s + (-68982 - 9*$T)*cos(2*($F - $D) + $Om) + 39*sin(2*($F - $D) + $Om);
  $s = $s + (-53311 + 32*$T)*cos(2*($F + $Om) - $L) - 4*sin(2*($F + $Om) - $L);
  $s = $s - 1235*cos(2*$D - $L) - 82*sin(2*$D - $L);
  $s = $s - 33228*cos($L + $Om) + 9*sin($L + $Om);
  $s = $s + 31429*cos($Om - $L) - 75*sin($Om - $L);
  $s = $s + (25543 - 11*$T)*cos(2*($F + $D + $Om) - $L) + 66*sin(2*($F + $D + $Om) - $L);
  $s = $s + 26366*cos($L + 2*$F + $Om) + 78*sin($L + 2*$F + $Om);
  $s = $s + (-24236 - 10*$T)*cos(2*($F - $L) + $Om) + 20*sin(2*($F - $L) + $Om);
  $s = $s - 1220*cos(2*$D) - 29*sin(2*$D);
  $s = $s + (16452 - 11*$T)*cos(2*($F + $D + $Om)) + 68*sin(2*($F + $D + $Om));
  $s = $s - 13870*cos(2*($F - $Lp - $D + $Om));
  $s = $s + 477*cos(2*($D - $L)) - 25*sin(2*($D - $L));
  $s = $s + (13238 - 11*$T)*cos(2*($L + $F + $Om)) + 59*sin(2*($L + $F + $Om));
  $s = $s + (-12338 + 10*$T)*cos($L + 2*($F - $D + $Om)) - 3*sin($L + 2*($F - $D + $Om));
  $s = $s - 10758*cos(2*$F + $Om - $L) + 3*sin(2*$F + $Om - $L);
  $s = $s - 609*cos(2*$L) - 13*sin(2*$L);
  $s = $s - 550*cos(2*$F) - 11*sin(2*$F);
  $s = $s + (8551 - 2*$T)*cos($Lp + $Om) - 45*sin($Lp + $Om);
  $s = $s - 8001*cos(2*$D - $L + $Om) + sin(2*$D - $L + $Om);
  $s = $s + (6850 - 42*$T)*cos(2*($Lp + $F - $D + $Om)) - 5*sin(2*($Lp + $F - $D + $Om));
  $s = $s - 167*cos(2*($D - $F)) - 13*sin(2*($D - $F));
  $s = $s + 6953*cos($L - 2*$D + $Om) - 14*sin($L - 2*$D + $Om);
  $s = $s + 6415*cos($Om - $Lp) + 26*sin($Om - $Lp);
  $s = $s + 5222*cos(2*($F + $D) + $Om - $L) + 15*sin(2*($F + $D) + $Om - $L);
  $s = $s + (168 - $T)*cos(2*$Lp) + 10*sin(2*$Lp);
  $s = $s + 3268*cos($L + 2*($F + $D + $Om)) + 19*sin($L + 2*($F + $D + $Om));
  $s = $s + 104*cos(2*($F - $L)) + 2*sin(2*($F - $L));
  $s = $s - 3250*cos($Lp + 2*($F + $Om)) + 5*sin($Lp + 2*($F + $Om));
  $s = $s + 3353*cos(2*($F + $D) + $Om) + 14*sin(2*($F + $D) + $Om);
  $s = $s + 3070*cos(2*($F + $Om) - $Lp) + 4*sin(2*($F + $Om) - $Lp);
  $s = $s + 3272*cos(2*$D + $Om) + 4*sin(2*$D + $Om);
  $s = $s - 3045*cos($L + 2*($F - $D) + $Om) + sin($L + 2*($F - $D) + $Om);
  $s = $s - 2768*cos(2*($L + $F - $D + $Om)) + 4*sin(2*($L + $F - $D + $Om));
  $s = $s + 3041*cos(2*($D - $L) + $Om) - 5*sin(2*($D - $L) + $Om);
  $s = $s + 2695*cos(2*($L + $F) + $Om) + 12*sin(2*($L + $F) + $Om);
  $s = $s + 2719*cos(2*($F - $D) + $Om - $Lp) - 3*sin(2*($F - $D) + $Om - $Lp);
  $s = $s + 2720*cos($Om - 2*$D) - 9*sin($Om - 2*$D);
  $s = $s - 51*cos(2*$D - $L - $Lp) - 4*sin(2*$D - $L - $Lp);
  $s = $s - 2206*cos(2*($L - $D) + $Om) - sin(2*($L - $D) + $Om);
  $s = $s - 199*cos($L + 2*$D) - 2*sin($L + 2*$D);
  $s = $s - 1900*cos($Lp + 2*($F - $D) + $Om) - sin($Lp + 2*($F - $D) + $Om);
  $s = $s - 41*cos($L - $Lp) - 3*sin($L - $Lp);
  $s = $s + 1313*cos(2*($F - $L + $Om)) - sin(2*($F - $L + $Om));
  $s = $s + 1233*cos(3*$L + 2*($F + $Om)) + 7*sin(3*$L + 2*($F + $Om));
  $s = $s - 81*cos(-$Lp + 2*$D) - 2*sin(-$Lp + 2*$D);
  $s = $s + 1232*cos($L - $Lp + 2*($F + $Om)) + 4*sin($L - $Lp + 2*($F + $Om));
  $s = $s - 20*cos($D) + 2*sin($D);
  $s = $s + 1207*cos(2*($F + $D + $Om) - $L - $Lp) + 3*sin(2*($F + $D + $Om) - $L - $Lp);
  $s = $s + 40*cos(2*$F - $L) - 2*sin(2*$F - $L);
  $s = $s + 1129*cos(2*($F + $D + $Om) - $Lp) + 5*sin(2*($F + $D + $Om) - $Lp);
  $s = $s + 1266*cos($Om - 2*$L) - 4*sin($Om - 2*$L);
  $s = $s - 1062*cos($L + $Lp + 2*($F + $Om)) + 3*sin($L + $Lp + 2*($F + $Om));
  $s = $s - 1129*cos(2*$L + $Om) + 2*sin(2*$L + $Om);
  $s = $s - 9*cos($Lp + $D - $L);
  $s = $s + 35*cos($L + $Lp) - 2*sin($L + $Lp);
  $s = $s - 107*cos($L + 2*$F) - sin($L + 2*$F);
  $s = $s + 1073*cos(2*($F - $D) + $Om - $L) - 2*sin(2*($F - $D) + $Om - $L);
  $s = $s + 854*cos($L + 2*$Om);
  $s = $s - 553*cos($D - $L) + 139*sin($D - $L);
  $s = $s - 710*cos(2*($F + $Om) + $D) + 2*sin(2*($F + $Om) + $D);
  $s = $s + 647*cos(2*($F + 2*$D + $Om) - $L) + 4*sin(2*($F + 2*$D + $Om) - $L);
  $s = $s - 700*cos($Lp + $D + $Om - $L);
  $s = $s + 672*cos(2*($F - $Lp - $D) + $Om);
  $s = $s + 663*cos($L + 2*($F + $D) + $Om) + 4*sin($L + 2*($F + $D) + $Om);
  $s = $s - 594*cos(2*($F - $L + $D + $Om)) + 2*sin(2*($F - $L + $D + $Om));
  $s = $s - 610*cos(2*$Om - $L) - 2*sin(2*$Om - $L);
  $s = $s - 556*cos($L + $Lp + 2*($F - $D + $Om));

  $dEpsDeg = $s / 36000000000.0;
  
  return array($dPsiDeg, $dEpsDeg);
}