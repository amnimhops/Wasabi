<?php

	/**
	 * Unidad de ORM para MVC
	 */
	define("SQL_DEBUG",true);
	/* Tipo de campo */
	define("AUTONUM","autonum");
	define("INTEGER","integer");
	define("VARCHAR","varchar");
	define("DECIMAL","decimal");
	define("DATETIME","datetime");
	/* Capacidad de ser nulo */
	define("F_NULL",1);
	define("F_NOTNULL",2);
	/* Posiciones en FieldDECL */
	define("FIELD_TYPE",0);
	define("FIELD_NULLABLE",1);
	define("FIELD_SIZE",2);
	
	require_once("Logger.php");
	
	class OrmException extends Exception{
		public function __construct($msg){
			$this->message=$msg;
			
		}
	}
	
	class Orm{
		static $db=null;
		static $user=null;
		static $pass=null;
		static $host=null;
		static $db_link=null;

		public static function logSql($sql){
			Logger::write("Orm",$sql);
		}
		
		public static function connect($host=null,$user=null,$pass=null){
			if($host!=null)	Orm::$host=$host;
			if($user!=null)	Orm::$user=$user;
			if($pass!=null)	Orm::$pass=$pass;
			
			Orm::$db_link=mysql_connect(Orm::$host,Orm::$user,Orm::$pass);
			
		}
		
		public static function setDb($db=null){
			if($db!=null) Orm::$db=$db;
			
			mysql_select_db(Orm::$db);
			if(mysql_errno()>0){
				Orm::logSql(mysql_error());	
			}
		}
		
		public static function setCharset($charset){
			mysql_set_charset($charset);
		}
		
		/**
		 * Ejecuta la transacción en curso
		 * @return true si todo fue bien, false en otro caso
		 */
		public static function commitTrans(){
			return mysql_query("COMMIT");
		}
		/**
		 * Empieza una transaccion
		 * @return true si todo fue bien, false en otro caso
		 */
		public static function beginTrans(){
			return mysql_query("BEGIN");
		}
		/**
		 * Cancela una transacción
		 * @return true si todo fue bien, false en otro caso
		 */
		public static function rollbackTrans(){
			return mysql_query("ROLLBACK");
		}
		
		/**
		 * Devuelve un objeto de la base de datos en funcion de su clave primaria
		 * 
		 * @param object $obj Objeto que contiene la información de busqueda
		 * @return Si todo ha ido bien, devuelve el objeto solicitado, o null si no se ha encontrado
		 * @throws OrmException si ha habido un error
		 */
		public static function get(&$obj){
			$model = Model::getModel(get_class($obj));
			$sql=$model->load($obj);
			
			Orm::logSql($sql);
			
			$result=mysql_query($sql);
			
			if(mysql_errno()>0){
				throw new OrmException(mysql_error(mysql_errno()));
			}else if(mysql_num_rows($result)>0){
				return ($model->unmap(mysql_fetch_assoc($result)));	
			}else{
				return null;
			}
		}
		
		public static function find($clsName,$query){
			$found=array();
			$model=Model::getModel($clsName);
			$sql=$query;
			
			if(func_num_args()==3 && is_array(func_get_arg(2))){
				$args=func_get_arg(2);
			}else{
				$args=array_slice(func_get_args(),2);	
			}
			
			$sql=$model->preparedSelect($sql,$args);
			
			Orm::logSql($sql);
			
			$result=mysql_query($sql);
			echo mysql_error();
			while(($row=mysql_fetch_assoc($result))!=null){
				$found[]=$model->unmap($row);
			}
			
			return $found;
			
		}
		
		/**
		 * Igual que find(), pero devuelve el nº de resultados en lugar
		 * de los objetos mapeados. Cualquier cambio en find() debe 
		 * reflejarse aqui
		 */
		public static function findCount($clsName,$query){
			$found=array();
			$model=Model::getModel($clsName);
			$sql=$query;
			
			if(func_num_args()==3 && is_array(func_get_arg(2))){
				$args=func_get_arg(2);
			}else{
				$args=array_slice(func_get_args(),2);	
			}
			
			$sql=$model->preparedSelect($sql,$args);
			
			$hasLimit=false;
			
			$ptrLimit="/.*(limit [0-9]+,[0-9]+)\$/i";
			$pattern=null;
			$mtxPtrLimit=null;
			
			preg_match($ptrLimit,$sql,$mtxPtrLimit);
			
			if(is_array($mtxPtrLimit) && count($mtxPtrLimit)==2){
				$pattern = "/([ ]*select (`.*`)( from .*)(limit [0-9]+,[0-9]+)\$)/i";			
			}else{
				$pattern="/([ ]*select (`.*`)( from .*)\$)/i";
			}
				
			preg_match($pattern, $sql,$array);
			
			$sql="select count(1) as `count` ".$array[3];
			
			Orm::logSql($sql);
			
			$result=mysql_query($sql);
			echo mysql_error();
			
			return mysql_result($result,0,"count");
		}
		
		public static function mappedSelect($cls,$sql){
			$objects=array();
			try{
				$result=Orm::executeQuery($sql);
				$map=Model::getModel($cls);
				while($row=mysql_fetch_assoc($result)){
					$objects[]=$map->unmap($row);
				}
			}catch(Exception $e){
				throw new OrmException($e->getMessage());
			}
			
			return $objects;	
		}
		
		private static function executeQuery($sql){
			Orm::logSql($sql);
			
			$result=mysql_query($sql);
			if(mysql_errno()>0){
				throw new OrmException("Error en la consulta $sql:".mysql_error());
			}
			
			return $result;
		}
		
		/**
		 * Ejecuta una consulta y devuelve el numero de filas afectadas
		 * @param object $sql
		 * @return 
		 */
		public static function execute($sql){
			Orm::logSql($sql);
			
			mysql_query($sql);
			
			if(mysql_errno()>0){
				throw new OrmException(mysql_error());
			}else{
				return mysql_affected_rows();
			}
		}
		/**
		 * Guarda un objeto en la base de datos
		 * @param object $obj El objeto que será guardado
		 * @return Nada
		 * @throws OrmException si no se ha podido guardar el objeto
		 */
		public static function save(&$obj){
			$map = Model::getModel(get_class($obj));
			$sql=$map->insert($obj);

			Orm::logSql($sql);
			
			mysql_query($sql);
			
			if(mysql_errno()==0){
				// Si el ID es un solo campo y es un AUTO
				
				if(count($map->pkeys)==1){
					foreach($map->pkeys as $key=>$value){
						if($value[FIELD_TYPE]==AUTONUM){
							
							$result=mysql_query("SELECT LAST_INSERT_ID()");
							$obj->$key=mysql_result($result, 0);		
						}
					}
				}
			}else{
				throw new OrmException("Save error:".mysql_error());
			}
		}
		
		/**
		 * Actualiza un objeto en la base de datos
		 * @param object $obj El objeto que será actualizado
		 * @return Nada
		 * @throws OrmException si no se ha podido actualizar el objeto
		 */
		public static function update(&$obj){
			$map = Model::getModel(get_class($obj));
			$sql=$map->update($obj);

			Orm::logSql($sql);
			
			mysql_query($sql);
			
			if(mysql_errno()!=0){
				throw new OrmException("Update error:".mysql_error());
			}
		}
		
		/**
		 * elimina un objeto en la base de datos
		 * @param object $obj El objeto que será eliminado
		 * @return Nada
		 * @throws OrmException si no se ha podido eliminar
		 */
		public static function remove(&$obj){
			$map = Model::getModel(get_class($obj));
			$sql=$map->remove($obj);

			Orm::logSql($sql);
			
			mysql_query($sql);
			
			if(mysql_errno()!=0){
				throw new OrmException("Delete error:".mysql_error());
			}
		}
	}

	class Model{
		private static $ModelCache=array();
		
		var $objMapped;
		var $table;
		var $fieldDecl;
		var $pkeys;
		
		
		public static function getModel($name){
			$map=null;
			
			if(array_key_exists($name, Model::$ModelCache)){
				$map=Model::$ModelCache[$name];
			}else{
				$clsname="Model".$name;
				
				if(class_exists($clsname)){
					$map=new $clsname();
					
					$map->map();
	
					Model::$ModelCache[$name]=$map;
				}else{
					throw new OrmException("La clase $clsname no existe");
				}
			}
			
			return $map;
		}
		
		private function __construct(){
			$this->table=null;
			$this->fieldDecl=array();
			$pkeys=array();
			
		}
		/* Metodos de mapeo */
		public function Table($name){
			$this->table=$name;
		}
		
		public function Field($name,$type,$nullable,$size=null){
			if(strpos($name, " ",0)!==false){
				throw new OrmException("El campo $name tiene un espacio en el nombre. Los espacios en los nombres no están permitidos");
			}
			
			$this->fieldDecl[$name]=array($type,$nullable,$size);
		}
		
		public function PrimaryKey(){
			$args=func_get_args();
			foreach($args as $arg){
				if(!empty($this->fieldDecl[$arg])){
					// Las PK's no pueden ser F_NULL
					if($this->fieldDecl[$arg][1]!=F_NULL){
						$this->pkeys[$arg]=&$this->fieldDecl[$arg];	
					}else{
						throw new OrmException("Error al establecer la clave principal en la tabla ".$this->table.": Se está usando el campo $arg nulable para una PK no nulable");
					}
					
				}else{
					throw new OrmException("Error al establecer la clave principal en la tabla ".$this->table.": No se ha encontrado el campo $arg");
				}
			}
		}
		
		public function Object($name){
			$this->objMapped=$name;
		}
		
		public function map(){
			echo "Padre";	
		}

		/**
		 * Desmapea un hash en un objeto.
		 * @param object $mtx
		 * @return 
		 */
		public function unmap($mtx){
			$object = new $this->objMapped();
			
			foreach($mtx as $key=>$value){
				$object->$key=$value;	
			}
			
			return $object;
		}
		
		/**
		 * Devuelve true si el campo es de un tipo que necesita ser aislado entre comillas
		 * 
		 * @param String $name Nombre del campo
		 * @return 
		 */
		function isFieldQuoted($name){
			return ($this->fieldDecl[$name][0] == VARCHAR || $this->fieldDecl[$name][0]==DECIMAL || $this->fieldDecl[$name][0]==DATETIME);
		}
		/**
		 * Devuelve un campo aislado entre comillas si fuera necesario
		 * @param object $name
		 * @param object $value
		 * @return 
		 */
		function getFieldQuoted($name,$value){
			if($this->isFieldQuoted($name)){
				return "'".$value."'";
			}else{
				return $value;
			}
		}
		
		/* Metodos de DML */
		public function insert($object){
			$sql='INSERT INTO `'.$this->table.'`(';
			$iFields=array();
			$iValues=array();
			$wrapValue="";
			
			/*
			 * Para cada campo, comprobar:
			 * 1.- Que no es un AUTO
			 * 2.- Que cumple con la restriccion de nulos
			 * 3.- Que siendo F_NULL no es un pkey
			 */
			/* $data=array($type,$nullable,$size);*/
			foreach($this->fieldDecl as $key=>$data){
				// 1.-()
				if($data[0]!=AUTONUM){
					$iFields[]="`$key`";
					if($data[0]==VARCHAR || $data[0]==DECIMAL || $data[0]==DATETIME){
						$wrapValue="'";
					}else{
						$wrapValue="";
					}
					
					
					if($data[1]==F_NOTNULL){
						// 2.-()	
						// La ultima condicion evita que las variables establecidas a '0' den false en la comprobacion del empty
						if(!is_null($object->$key) && (!empty($object->$key) || $object->$key==0)){
							$iValues[]=$wrapValue.$object->$key.$wrapValue;
						}else{
							throw new OrmException("El campo `$key` no admite valores nulos");
						}
					}else{
						// Si existe este campo como clave primaria, lanzamos un error, no se puede a�adir una PK con un campo nulo
						if(key_exists($key, $this->pkeys)){
							throw new OrmException("Error al insertar en ".$this->table.": Se est� usando el campo $arg nulable para una PK no nulable");
						}
						$iValues[]=(is_null($object->$key) || empty($object->$key))?"null":$wrapValue.$object->$key.$wrapValue;
					}
					
				}
			}
			$sql.=implode(",", $iFields);
			$sql.=") VALUES (";
			$sql.=implode(",",$iValues);
			$sql.=")";
			
			return $sql;
		}
		
		/**
		 * Genera una SQL de seleccion en funcion de un criterio
		 * 
		 * Modo de operacion:
		 * 
		 * 1.- Sin parametros: devuelve todas las filas
		 * 2.- 1 parametro: ejecuta dicho criterio
		 * 3.- 2+ parametros: ejecuta una suerte de preparedstatement
		 * @return 
		 */
		function preparedSelect(){
			$args=func_get_args();
			$argscount=count($args);
			
			$query="";
			$values=array();
			
			if($argscount==0){
				
			}else if($argscount==1){
				$query=$args[0];
			}else{
				$query=$args[0];
				
				if(is_array($args[1])){
					// Si el argumento 1 es un array, tomamos de ahi los parametros
					$values=$args[1];
				}else{
					// En caso contrario, usamos todos (menos query)
					$values=array_slice($args, 1);
				}
			}

			$foundFields=null;
			
			$markPos=array();
			
			
			// Buscamos todos los campos que se han metido en la consulta
			preg_match_all("(%[a-zA-Z0-9_]+)", $query, $arr);

			$foundFields=$arr[0];

			for($c=0;$c<count($foundFields);$c++){
				$curField=substr($foundFields[$c], 1);
				
				$curValue=$values[$c];
				
				if($this->isFieldQuoted($curField)){
					$curValue="'".$curValue."'";
				}
				
				$query=preg_replace("(\?)", $curValue, $query,1);
				$query=preg_replace("(%[a-zA-Z0-9_]+)", $curField, $query,1);
			}
			
			$sql='SELECT ';
			
			$fields=array();
			
			foreach($this->fieldDecl as $field=>$data){
				$fields[]="`".$field."`";				
			}
			
			// Si hay criterio, ponemos where
			if(count($foundFields)>0){
				$query=" WHERE $query";
			}
			
			$sql.=implode(",",$fields). " FROM `{$this->table}` $query";
			
			return $sql;
		}
		
		public function update($object){
			$sql='UPDATE `'.$this->table.'` SET ';
			$iValues=array();
			
			foreach($this->fieldDecl as $field=>$data){
				$value = null;
				if(is_null($object->$field)){
					$value="null";
				}else{
					$value=$this->getFieldQuoted($field, $object->$field);
				}
				
				$iFields[]="`".$field."`=".$value;				
			}
			$sql.=implode(",",$iFields);
			
			// Aplicamos where
			$vKeys=array();
			
			foreach($this->pkeys as $name=>$field){
				$value=null;
				if(is_null($object->$name)){
					$value="null";
				}else{
					$value=$this->getFieldQuoted($name, $object->$name);
				}
				$vKeys[]="`$name`=".$value;	
			}
			
			$sql.=" WHERE ".implode(" AND ",$vKeys);
			
			return $sql;
		}
	
		public function remove($object){
			$sql='DELETE FROM `'.$this->table.'`';

			// Aplicamos where
			$vKeys=array();
			
			foreach($this->pkeys as $name=>$field){
				$value=null;
				if(is_null($object->$name)){
					$value="null";
				}else{
					$value=$this->getFieldQuoted($name, $object->$name);
				}
				// TODO: Esto probablemente no sea correcto en el caso de = NULL, sería mas bien IS NULL
				$vKeys[]="`$name`=".$value;	
			}
			
			$sql.=" WHERE ".implode(" AND ",$vKeys);
			
			return $sql;
		}
		/*
		public function load($object){
			$sql='SELECT ';
			
			$fields=array();
			
			foreach($this->fieldDecl as $field=>$data){
				$fields[]="`".$field."`";				
			}
			$sql.=implode(",",$fields). " FROM `{$this->table}` ";
			
			$vKeys=array();
			
			foreach($this->pkeys as $name=>$field){
				$value=null;
				if(!is_null($object->$name)){
					$value=$this->getFieldQuoted($name, $object->$name);
					$vKeys[]="`$name`=".$value;
				}
					
			}
			
			if(count($vKeys)>0){
				$sql.=" WHERE ".implode(" AND ",$vKeys);
			}
			
			return $sql;
		}*/
		public function load($object){
			$fields=array();
			
			$sql="SELECT ";
			
			foreach($this->fieldDecl as $field=>$data){
				$fields[]="`".$field."`";				
			}
			$sql.=implode(",",$fields). " FROM `{$this->table}` ";
			
			$vKeys=array();
			
			foreach($this->pkeys as $name=>$field){
				$value=null;
				if(!is_null($object->$name)){
					$value=$this->getFieldQuoted($name, $object->$name);
					$vKeys[]="`$name`=".$value;
				}
					
			}
			
			if(count($vKeys)>0){
				$sql.=" WHERE ".implode(" AND ",$vKeys);
			}
			
			return $sql;
		}
	}

	/*$sql="select `asdf`,`1234` from  (select i from 2 where 1=1 limit 1,1) where 1";
	
	$hasLimit=false;
	
	//$pattern="/(limit [0-9]+[ ]*,[ ]*[0-9]+)/i";
	//$pattern = "/(order by .* (asc|desc))/i";
	$ptrLimit="/.*(limit [0-9]+,[0-9]+)\$/i";
	$pattern=null;
	$mtxPtrLimit=null;
	
	preg_match($ptrLimit,$sql,$mtxPtrLimit);
	
	if(is_array($mtxPtrLimit) && count($mtxPtrLimit)==2){
		$pattern = "/([ ]*select (`.*`)( from .*)(limit [0-9]+,[0-9]+)\$)/i";			
	}else{
		$pattern="/([ ]*select (`.*`)( from .*)\$)/i";
	}
		
	preg_match($pattern, $sql,$array);
	var_dump($array[3]);
	
	require_once("Entities.class.php");
	require_once("DbMappings.php");
	require_once("Logger.php");
	
	Logger::start("asdf");
	
	Orm::connect("localhost","root","");
			Orm::setDb("wasabi");
			Orm::setCharset("utf8");
	
	var_dump(Orm::findCount("Ejemplar", "%referencia like ? LIMIT 10,10","%"));*/
?>