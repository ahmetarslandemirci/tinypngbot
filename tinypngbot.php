#!/usr/bin/php
<?php
	define("DEBUG","0");
	define("COMPRESSED_TAG","tiny_");
	
	function post_it($image_data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://tinypng.com/web/shrink");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0");
		curl_setopt($ch, CURLOPT_POSTFIELDS,
					$image_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec ($ch);
		curl_close ($ch);
		return $output;
	}
	
	function get_subt() {
		$subt = "";
		switch(php_uname('s')) {
			case "Linux":  $subt = "/";break;
			case "Windows NT": $subt = "\\"; break;
			default : 
				if(DEBUG)
					echo php_uname('s');
				die("Unknown OS");
		}
		return $subt;
	}
	
	function get_file_ext($filename) {
		if(!is_dir($filename))
			return substr($filename,strrpos($filename,'.')+1);
		else return false;
	}
	
	function is_image_ext($ext) {
		$image_extensions = array("jpg","jpeg","png");
		return in_array($ext,$image_extensions);
	}
	
	function is_compressed_before($filename) {
		if(DEBUG)
			var_dump(substr($filename,0,strlen(COMPRESSED_TAG)));
		if(strcmp(substr($filename,0,strlen(COMPRESSED_TAG)),COMPRESSED_TAG)==0)
			return true;
		return false;
	}
	
	function compresser($argument) {
		$files = array();
		$param = $argument;
		$path = "";
		$subt = get_subt();
		
		if(is_dir($param)) {
			$files = scandir($param);
			if($param[strlen($param)-1] != $subt)
				$path = $param . $subt;
			else 
				$path = $param;
		}
		else {
			array_push($files,$param);
		}
		
		foreach($files as $file) {
			if(!is_dir($file) && 
					is_image_ext(get_file_ext($file)) != false && 
					is_compressed_before($file) == false) {
				$decoded = json_decode( post_it(file_get_contents($path . $file) ) );
				if(DEBUG)
					print_r($decoded);
				if(!is_null($decoded) || !isset($decoded->error) ) {
					$filename = explode( $subt, $file);
					file_put_contents(COMPRESSED_TAG.$filename[count($filename)-1],
										file_get_contents($decoded->output->url));
					echo "[+]".$file." compressed %".floor((100*$decoded->output->size)/$decoded->input->size).".\n";
				}
			}
		}
	}
	
	if($argc == 1) {
		echo "Usage: php tinypng.php (file|directory)+\n";
		echo "\"+\" means it requires least one time\n\n";
		echo " This tool compresses images given in parameters and writes\n";
		echo "output to working directory.\n";
	}
	else {
		foreach(array_slice($argv,1) as $arg) {
			compresser($arg);
		}
	}
?>
