<?php

	class Logger{
		static $file;
		
		public static function start($name){
			Logger::$file=fopen($name, "a+");
			Logger::write("Inicio del log");
		}	
	
		public static function info($msg){
			Logger::write("INFO",$msg);
		}
		
		public static function error($msg){
			Logger::write("ERROR",$msg);
		}
		
		public static function write($title,$msg=""){
			fwrite(Logger::$file,"--- $title ---\r\n$msg\r\n");
		}
		
		public static function stop(){
			Logger::write("Fin del log");
			fclose(Logger::$file);
		}
	}
?>
