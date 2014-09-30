<?php
/*
 * Application name: OpenRate-it!
* A general-purpose polling platform
* Copyright (C) 2014  Alain Bindele (alain.bindele@gmail.com)
* This file is part of OpenRate-it!
* OpenRate-it! is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* OpenRate-it! is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


/**
 * function to swap the char at pos $i and $j of $str.
 *
 * @param string $str
 * @param
 *            start index $i
 * @param
 *            end index $j
 */
function swap(&$str, $i, $j)
{
	$temp = $str[$i];
	$str[$i] = $str[$j];
	$str[$j] = $temp;
}

/**
 * Returns all permutations of $str
 *
 * @param array $str
 * @param array $perm
 * @param
 *            start index $i
 * @param
 *            end index $n
 * @return the array $perm containing all permutations of $str
 */
function permute($str, & $perm, $i, $n)
{
	if ($i == $n)
		array_push($perm, $str);
	else {
		for ($j = $i; $j < $n; $j ++) {
			swap($str, $i, $j);
			permute($str, $perm, $i + 1, $n);
			swap($str, $i, $j); // backtrack.
		}
	}
	return $perm;
}
