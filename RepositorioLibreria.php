<?php
	define("CRIT_IGUAL","igual");
	define("CRIT_MAYOR_QUE","mayor que");
	define("CRIT_MENOR_QUE","menor que");
	define("CRIT_CONTIENE","contiene");


	
	function busquedaParametrizadaEjemplares(ParametrosBusquedaEjemplar $params){
		$values=array();
		$fullClause=null;		
		$c=0;

		foreach($params->grupos as $group){
			$groupClause=null;
			foreach($group->criterios as $crt){
				$not = ($crt->invertido==true)?"NOT ":null;
				if($crt->criterio==CRIT_IGUAL){
					$clause="{$crt->campo} = ?";
					$values[]=$crt->valor;
				}else if($crt->criterio==CRIT_MAYOR_QUE){
					$clause="{$crt->campo} > ?";
					$values[]=$crt->valor;
				}else if($crt->criterio==CRIT_MENOR_QUE){
					$clause="{$crt->campo} < ?";
					$values[]=$crt->valor;
				}else if ($crt->criterio==CRIT_CONTIENE){
					$clause="{$crt->campo} LIKE ?";
					$values[]=$crt->valor;
				}		
				
				// Indicamos que es un campo del modelo
				$clause="%$clause";
				// Incluimos la negacion si procede
				if($not!=null){
					$clause=$not.$clause;	
				}
				
				// Si no hay clausula de grupo aun, omitimos el primer opcional
				if($groupClause!=null){
					if($crt->opcional==true){
						$groupClause.=" OR $clause";
					}else{
						$groupClause.=" AND $clause";
					}
				}else{
					$groupClause=$clause;
				}	
			}
			
			$groupClause = "($groupClause)";
			
			if($fullClause!=null){
				if($group->opcional==true){
					$fullClause.=" OR $groupClause";
				}else{
					$fullClause.=" AND $groupClause";
				}
			}else{
				$fullClause="$groupClause";
			}
		}

		
		$str_limit="";
		
		if(isset($params->resultadosPorPagina) && $params->resultadosPorPagina>0){
			$limit = $params->resultadosPorPagina;
			$offset=$limit*$params->pagina;
			$str_limit=" LIMIT $offset,$limit";	
		}
			
		$sql="SELECT ejemplares.* FROM ejemplares $fullClause $str_limit";

		$list = Orm::find("Ejemplar", "$fullClause $str_limit",$values);
		
		return $list;
		
	}
	
	/*
	require_once("Orm.php");
	require_once("Entities.class.php");
	require_once("DbMappings.php");
	require_once("Logger.php");
	
	Logger::start("asdf");
	
	Orm::connect("localhost","root","");
	Orm::setDb("wasabi");
	Orm::setCharset("utf8");
	
	$params = new ParametrosBusquedaEjemplar();
	$params->grupos=array();
	
	$grupo = new GrupoCriteriosBusquedaEjemplar();
	$grupo->opcional=false;
	$grupo->invertir=false;
	$grupo->criterios=array();
	
	$criterio=new CriterioBusquedaEjemplar();
	$criterio->campo="precio";
	$criterio->criterio=CRIT_MENOR_QUE;
	$criterio->valor="22";
	$criterio->opcional=false;
	$criterio->invertido=false;
	$grupo->criterios[]=$criterio;
	
	$params->grupos[]=$grupo;
			
	$params->pagina=0;
	$params->resultadosPorPagina=50;
			
	
	var_dump(busquedaParametrizadaEjemplares($params));*/
			
	
	
?>
