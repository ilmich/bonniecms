<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

	class Date {
		
		// Returns an array representation of the given calendar month.
		// The array values are timestamps which allow you to easily format
		// and manipulate the dates as needed.
		public static function calendar($month = null, $year = null) {
			if(is_null($month)) 
				$month = date('n');
			if(is_null($year)) 
				$year = date('Y');
		
			$first = mktime(0, 0, 0, $month, 1, $year);
			$last = mktime(23, 59, 59, $month, date('t', $first), $year);
		
			$start = $first - (86400 * date('w', $first));
			$stop = $last + (86400 * (7 - date('w', $first)));
		
			$out = array();
			while($start < $stop) {
				$week = array();
				if($start > $last) 
					break;
				for($i = 0; $i < 7; $i++) {
					$week[$i] = $start;
					$start += 86400;
				}
				$out[] = $week;
			}
		
			return $out;
		}
		
		// Converts a date/timestamp into the specified format
		public static function dater($date = null, $format = null) {
			if(is_null($format))
				$format = 'Y-m-d H:i:s';
		
			if(is_null($date))
				$date = time();
		
			// if $date contains only numbers, treat it as a timestamp
			if(ctype_digit($date) === true)
				return date($format, $date);
			else
				return date($format, strtotime($date));
		}
		
		// Returns the HTML for a month, day, and year dropdown boxes.
		// You can set the default date by passing in a timestamp OR a parseable date string.
		// $prefix_ will be appened to the name/id's of each dropdown, allowing for multiple calls in the same form.
		// $output_format lets you specify which dropdowns appear and in what order.
		public static function mdy($date = null, $prefix = null, $output_format = 'm d y') {
			if(is_null($date)) 
				$date = time();
			
			if(!ctype_digit($date)) 
				$date = strtotime($date);
			
			if(!is_null($prefix)) 
				$prefix .= '_';
			
			list($yval, $mval, $dval) = explode(' ', date('Y n j', $date));
		
			$month_dd = "<select name='{$prefix}month' id='{$prefix}month'>";
			for($i = 1; $i <= 12; $i++) {
				$selected = ($mval == $i) ? ' selected="selected"' : '';
				$month_dd .= "<option value='$i'$selected>" . date('F', mktime(0, 0, 0, $i, 1, 2000)) . "</option>";
			}
			$month_dd .= "</select>";
		
			$day_dd = "<select name='{$prefix}day' id='{$prefix}day'>";
			for($i = 1; $i <= 31; $i++) {
				$selected = ($dval == $i) ? ' selected="selected"' : '';
				$day_dd .= "<option value='$i'$selected>$i</option>";
			}
			$day_dd .= "</select>";
		
			$year_dd = "<select name='{$prefix}year' id='{$prefix}year'>";
			for($i = date('Y'); $i < date('Y') + 10; $i++) {
				$selected = ($yval == $i) ? ' selected="selected"' : '';
				$year_dd .= "<option value='$i'$selected>$i</option>";
			}
			$year_dd .= "</select>";
		
			$trans = array('m' => $month_dd, 'd' => $day_dd, 'y' => $year_dd);
			return strtr($output_format, $trans);
		}

		// Outputs hour, minute, am/pm dropdown boxes
		public static function hourmin($hid = 'hour', $mid = 'minute', $pid = 'ampm', $hval = null, $mval = null, $pval = null) {
			// Dumb hack to let you just pass in a timestamp instead
			if(func_num_args() == 1) {
				list($hval, $mval, $pval) = explode(' ', date('g i a', strtotime($hid)));
				$hid = 'hour';
				$mid = 'minute';
				$aid = 'ampm';
			}
			else {
				if(is_null($hval)) 
					$hval = date('h');
				if(is_null($mval)) 
					$mval = date('i');
				if(is_null($pval)) 
					$pval = date('a');
			}
		
			$hours = array(1, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12);
			$out = "<select name='$hid' id='$hid'>";
			foreach($hours as $hour)
				if(intval($hval) == intval($hour)) 
					$out .= "<option value='$hour' selected>$hour</option>";
				else 
					$out .= "<option value='$hour'>$hour</option>";
			$out .= "</select>";
		
			$minutes = array('00', 15, 30, 45);
			$out .= "<select name='$mid' id='$mid'>";
			foreach($minutes as $minute)
				if(intval($mval) == intval($minute)) 
					$out .= "<option value='$minute' selected>$minute</option>";
				else 
					$out .= "<option value='$minute'>$minute</option>";
			$out .= "</select>";
					
			$out .= "<select name='$pid' id='$pid'>";
			$out .= "<option value='am'>am</option>";			
			if($pval == 'pm') 
				$out .= "<option value='pm' selected>pm</option>";
			else 
				$out .= "<option value='pm'>pm</option>";				
			$out .= "</select>";
		
			return $out;
		}
		
		// More robust strict date checking for string representations
		function chkdate($str) {
			// Requires PHP 5.2
			if(function_exists('date_parse')) {
				$info = date_parse($str);
				if($info !== false && $info['error_count'] == 0) {
					if(checkdate($info['month'], $info['day'], $info['year']))
						return true;
				}
		
				return false;
			}
		
			// Else, for PHP < 5.2
			return strtotime($str);
		}
		
		// Returns an English representation of a past date within the last month
		// Graciously stolen from http://ejohn.org/files/pretty.js
		public static function time2str($ts) {
			if(!ctype_digit($ts))
				$ts = strtotime($ts);
		
			$diff = time() - $ts;
			if($diff == 0)
				return 'now';
			elseif($diff > 0) {
				$day_diff = floor($diff / 86400);
				if($day_diff == 0) {
					if($diff < 60) 
						return 'just now';
					if($diff < 120) 
						return '1 minute ago';
					if($diff < 3600) 
						return floor($diff / 60) . ' minutes ago';
					if($diff < 7200) 
						return '1 hour ago';
					if($diff < 86400) 
						return floor($diff / 3600) . ' hours ago';
				}
				if($day_diff == 1) 
					return 'Yesterday';
				if($day_diff < 7) 
					return $day_diff . ' days ago';
				if($day_diff < 31) 
					return ceil($day_diff / 7) . ' weeks ago';
				if($day_diff < 60) 
					return 'last month';
				return date('F Y', $ts);
			}
			else {
				$diff = abs($diff);
				$day_diff = floor($diff / 86400);
				if($day_diff == 0) {
					if($diff < 120) 
						return 'in a minute';
					if($diff < 3600) 
						return 'in ' . floor($diff / 60) . ' minutes';
					if($diff < 7200) 
						return 'in an hour';
					if($diff < 86400) 
						return 'in ' . floor($diff / 3600) . ' hours';
				}
				if($day_diff == 1) 
					return 'Tomorrow';
				if($day_diff < 4) 
					return date('l', $ts);
				if($day_diff < 7 + (7 - date('w'))) 
					return 'next week';
				if(ceil($day_diff / 7) < 4) 
					return 'in ' . ceil($day_diff / 7) . ' weeks';
				if(date('n', $ts) == date('n') + 1) 
					return 'next month';
				return date('F Y', $ts);
			}
		}		
	}
