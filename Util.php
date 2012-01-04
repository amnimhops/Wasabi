<?php

	class UtilDate{
		// Obtiene una fecha en formato ISO 8601 combinada y la transforma
		// en un formato compatible con la db
		/*public static function fromISO8601($date){
			$arr=array();
			preg_match("/([0-9]+)-([0-9]+)-([0-9]+)T([0-9]+):([0-9]+):([0-9]+)\\.([0-9]+)\\+(.*)/", $date, $arr);
			var_dump($arr);
			if(count($arr)!=8){
				throw new Exception("La fecha ATOM $date no es válida");
			}
			
			return sprintf("%s-%s-%s %s:%s:%s",$arr[1],$arr[2],$arr[3],$arr[4],$arr[5],$arr[6]);
			
		}*/
		public static function toISO8601_combined($date){
			$arr=array();
			preg_match("/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/", $date, $arr);
			
			if(count($arr)!=7){
				throw new Exception("La fecha $date no es válida");
			}
			
			return sprintf("%s-%s-%sT%s:%s:%s.00",$arr[1],$arr[2],$arr[3],$arr[4],$arr[5],$arr[6]);
		}
	}
	
	
	echo date("Y-m-d H:m:s",time());
?>
