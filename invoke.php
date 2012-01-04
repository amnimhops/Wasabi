<?php
	require_once("Logger.php");
	require_once("CastorMapper.php");
	require_once("Orm.php");
	require_once("Entities.class.php");
	require_once("DbMappings.php");
	require_once("Services.php");
	
	Logger::start("./wasabi.log");
	
	$mapper = new CastorMapper("mapping-mimo.xml");
	$input = file_get_contents("php://input");
	
	if($input=="" || $input==null){
		echo "Entrada vacia";
	}else{
		try{

			$request = $mapper->unmarshal($input);
			
			$requestName = get_class($request);
			
			Logger::write("Comenzando $requestName");
			$ret=Service::$requestName($request);
			Logger::write("Finalizando $requestName");
			
			if($ret==null || !isset($ret)){
				die("Error de código: el servicio no ha devuelto ningún resultado");
			}else{
				$response = $mapper->marshal($ret);
				echo $response;
			}	
		}catch(CastorException $e){
			echo $e->getMessage();
		}
	}

?>
	
	
	
	