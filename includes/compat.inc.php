<?php if (!defined('CLYDEPHP'))  { header ('HTTP/1.1 404 Not Found'); exit(1); }

//imageconvolution clone with some lite modification
//WARNING: this function work only with truecolor image
//code from http://mgccl.com/2007/03/02/simple-replication-of-imageconvolution-function
//
//include this file whenever you have to use imageconvolution...
//you can use in your project, but keep the comment below :)
//great for any image manipulation library
//Made by Chao Xu(Mgccl) 2/28/07
//www.webdevlogs.com
//V 1.0
if(!function_exists('imageconvolution')){
	function imageconvolution($src, $filter, $filter_div, $offset){
		if ($src==NULL) {
			return false;
		}
		
		if ($filter_div == 0) $filter_div = 1;
		
		$pxl=array(0,0);
		$sx = imagesx($src);
		$sy = imagesy($src);				
		$srcback = imagecreatetrueColor($sx, $sy);		
		imagecopy($srcback, $src,0,0,0,0,$sx,$sy);
	 
		if($srcback==NULL){
			return 0;
		}
	
		for ($y=0; $y<$sy; $y++){
			for($x=0; $x<$sx; $x++){
				$new_r = $new_g = $new_b = 0;
				$alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
				$new_a = ($alpha >> 24) & 0x7F000000;
				
				for ($j=0; $j<3; $j++) {
					$yv = min(max($y - 1 + $j, 0), $sy - 1);
					for ($i=0; $i<3; $i++) {
					    $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
					    $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
						$new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
						$new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
						$new_b += ($rgb & 0xFF) * $filter[$j][$i];						
					}
				}
	 
				$new_r = ($new_r/$filter_div)+$offset;
				$new_g = ($new_g/$filter_div)+$offset;
				$new_b = ($new_b/$filter_div)+$offset;
	 
				$new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
				$new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
				$new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);
	 				
				$new_pxl = imagecolorallocatealpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				if ($new_pxl == -1) {
					$new_pxl = imagecolorclosestalpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				}
				
				if (($y >= 0) && ($y < $sy)) {
					imagesetpixel($src, $x, $y, $new_pxl);
				}
			}
		}
		imagedestroy($srcback);
		return true;
	}
}

if (!function_exists('hex2bin')) {
	function hex2bin($data) {
		static $old;
		if ($old === null) {
			$old = version_compare(PHP_VERSION, '5.2', '<');
		}
		$isobj = false;
		if (is_scalar($data) || (($isobj = is_object($data)) && method_exists($data, '__toString'))) {
			if ($isobj && $old) {
				ob_start();
				echo $data;
				$data = ob_get_clean();
			}
			else {
				$data = (string) $data;
			}
		}
		else {
			trigger_error(__FUNCTION__.'() expects parameter 1 to be string, ' . gettype($data) . ' given', E_USER_WARNING);
			return;//null in this case
		}
		$len = strlen($data);
		if ($len % 2) {
			trigger_error(__FUNCTION__.'(): Hexadecimal input string must have an even length', E_USER_WARNING);
			return false;
		}
		if (strspn($data, '0123456789abcdefABCDEF') != $len) {
			trigger_error(__FUNCTION__.'(): Input string must be hexadecimal string', E_USER_WARNING);
			return false;
		}
		return pack('H*', $data);
	}
}

?>