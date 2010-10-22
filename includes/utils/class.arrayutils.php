<?php

	class ArrayUtils {
		
		// Returns an array of the values of the specified column from a multi-dimensional array
		function gimme($arr, $key = null)
		{
			if(is_null($key))
				$key = current(array_keys($arr));
		
			$out = array();
			foreach($arr as $a)
				$out[] = $a[$key];
		
			return $out;
		}		
		
	}