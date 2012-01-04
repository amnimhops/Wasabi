<?php

	require_once("CastorMapper.php");
	require_once("Orm.php");
	require_once("Entities.class.php");
	require_once("DbMappings.php");
	require_once("ServicesTest.php");
	
	$mapper = new CastorMapper("mapping-mimo.xml");
	$input = file_get_contents("php://input");
	
	if($input=="" || $input==null){
		echo "Entrada vacia";
	}else{
		try{

			$request = $mapper->unmarshal($input);

			$requestName = get_class($request);
			
			$ret=Service::$requestName($request);

			$response = $mapper->marshal($ret);
			echo $response;	
		}catch(CastorException $e){
			echo $e->getMessage();
		}
	}

?>