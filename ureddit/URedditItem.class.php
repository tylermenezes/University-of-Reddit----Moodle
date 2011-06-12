<?php
abstract class URedditItem {
	protected static function Extract($content, $from, $to = false){
		if(strpos($content, $from) === false){
			return;
		}
		
		$content = substr($content, strpos($content, $from) + strlen($from));
		if($to !== false){
			$content = substr($content, 0, strpos($content, $to));
		}
		$content = trim($content);

		return $content;
	}

	protected static function ExtractEnumerable($content, $delim){
		$enum = array();
		while(strpos($content, $delim) !== false){
			$content = self::Extract($content, $delim);
			$nextPos = strpos($content, $delim);

			$enum[] = substr($content, 0, $nextPos);

			if($nextPos){
				$content = substr($content, $nextPos);
			}
		}

		return $enum;
	}

	protected static function Get($endpoint){
		return file_get_contents("http://ureddit.com/$endpoint");
	}
}