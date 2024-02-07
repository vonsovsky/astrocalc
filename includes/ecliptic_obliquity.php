<?php
/*

   This PHP function computes the mean obliquity of the ecliptic
   given a JD argument corresponding to any given date and time.

   Author: Jay Tanner - 2010

   The algorithm used here is based on work published by J. Laskar
   Astronomy and Astrophysics, Vol 157, p68 (1986),
   New Formulas for the Precession, Valid Over 10000 years,
   Table 8.

   Source code provided under the provisions of the
   GNU Affero General Public License (AGPL), version 3.
   http://www.gnu.org/licenses/agpl.html

*/


function epsilon_mean ($JD) {

// -----------------------------------------------------------
// Compute the (t) value in Julian decamillennia corresponding
// to the JD argument and reckoned from J2000.
   $t = ($JD - 2451545.0) / 3652500.0;

// --------------------------------------
// Compute mean obliquity in arc seconds.
   $w  = 84381.448;   $p  = $t;
   $w -=  4680.93*$p; $p *= $t;
   $w -=     1.55*$p; $p *= $t;
   $w +=  1999.25*$p; $p *= $t;
   $w -=    51.38*$p; $p *= $t;
   $w -=   249.67*$p; $p *= $t;
   $w -=    39.05*$p; $p *= $t;
   $w +=     7.12*$p; $p *= $t;
   $w +=    27.87*$p; $p *= $t;
   $w +=     5.79*$p; $p *= $t;
   $w +=     2.45*$p;

// -----------------------
// Compute mean ecliptic obliquity in
// degrees from arc seconds value.
   $EpsMeanDeg = $w / 3600.0;

   return $EpsMeanDeg;

} // End of  epsilon_mean()

?>