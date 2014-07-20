<?php

/**
 * using to simplify debug echo.
 *
 * @param mixed, mixed.. All variables to dump.
 * If last parametr == '!@#' - No die after dumping.
 *
 * @return void
 */
function vd() {
//    if (!SL::getProjectConfig('dev_mode')) return;
	$arguments = func_get_args();
	if (count($arguments)) {
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            if(!headers_sent()) {
                header('Content-Type: text/html; charset=utf-8');
            }
            echo '<pre>'."\n";
        }
		$last = array_pop($arguments);
		foreach($arguments as $item) echo dumperGet($item) . "\n";
//		foreach($arguments as $item) echo ($item) . "\n";
		if ($last !== '!@#') {
//            var_dump($last);
            echo dumperGet($last);
            die();
        }
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            echo '</pre>'."\n";
        } else echo "\n";
	}
}

function dumpAsString($var, $new_level = 0) {
    $res = '';

	if (is_bool($var)) {
		$res = $var ? "true" : "false";
	} elseif(is_null($var)) {
		$res = "null";
	} elseif(is_array($var)) {
	    $res = 'array (';
	    
		foreach($var as $key=>$item) {
			$res .= "\n". str_repeat(" ", ($new_level+1)*4);
			$res .= dumpAsString($key, $new_level+1);
			$res .= ' => ';
			$res .= dumpAsString($item, $new_level+1).',';
		}

		$res .= "\n".str_repeat(" ", ($new_level)*4).')';
	} elseif(is_string($var) && (isset($var[0]) && $var[0] != '$')) {
		$res = '"'. (strpos($var, '$__lv') === false ? str_replace('"', '\"', $var) : $var) .'"';
	} else {
		$res = $var;
	}
	
	return $res;
}

if (!function_exists('array_replace_recursive')) {
    function array_replace_recursive() {
        if (!function_exists('recurse')) {
        function recurse($array, $array1) {
            foreach ($array1 as $key => $value) {
                // create new key in $array, if it is empty or not an array
                if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
                    $array[$key] = array();
                }

                // overwrite the value in the base array
                if (is_array($value)) {
                    $value = recurse($array[$key], $value);
                }
                $array[$key] = $value;
            }
            return $array;
        }

        }
        // handle the arguments, merge one by one
        $args = func_get_args();
        $array = $args[0];
        if (!is_array($array)) {
            return $array;
        }
        for ($i = 1; $i < count($args); $i++) {
            if (is_array($args[$i])) {
                $array = recurse($array, $args[$i]);
            }
        }
        return $array;
    }
}


function dumperGet(&$obj, $leftSp = "") {
   if (is_array($obj)) {
       $type = "Array[" . count( $obj ) . "]";
   } elseif( is_object( $obj ) ) {
       ob_start();
       print_r($obj);
       return ob_get_clean();

   } elseif( gettype( $obj ) == "boolean" ) {
       return $obj ? "true" : "false";
   } elseif( is_null( $obj ) ) {
       return "NULL";
   } else {
       ob_start();
       var_dump($obj);
       return ob_get_clean();
   }
   $buf = $type;
   $leftSp .= "    ";
   for (reset( $obj ); list ( $k, $v ) = each( $obj );) {
       if ($k === "GLOBALS" )
           continue;
       $buf .= "\n".$leftSp.'['.$k.'] => ' . dumperGet( $v, $leftSp);
   }

   return $buf;
}
