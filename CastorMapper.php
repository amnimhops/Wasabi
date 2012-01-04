<?php
	//define("debug",true);
	function isISO8601_combined($date){
		return strstr($date,"T")!=false;
			
	}
	function toISO8601_combined($date){
		if($date==null){
			return null;
		}else if(isISO8601_combined($date)){
			return $date;
		}else{
			$arr=array();
			preg_match("/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/", $date, $arr);
					
			if(count($arr)!=7){
				throw new Exception("La fecha $date no es válida ");
			}
			
			return sprintf("%s-%s-%sT%s:%s:%s.00",$arr[1],$arr[2],$arr[3],$arr[4],$arr[5],$arr[6]);
		}
	}
	
	class CastorField{
		var $name;
		var $type;
		var $fullType;
		var $bindName;
		var $collection;
		var $bindNode;
		var $bindContainer;
		var $bindType;
	}
	
	class CastorClass{
		var $name;
		var $parent;
		var $fullName;
		var $tagName;
		var $fields;
	}
	
	
	class CastorException extends Exception{
		public function __construct($msg){
			parent::__construct($msg);
		}
	}
	
	class CastorMapper{
		var $classes = array();
	
		public function __construct($mapping){
			
			$dom = new DOMDocument();
			$dom->preserveWhiteSpace=false;
			$dom->load($mapping,LIBXML_NOBLANKS);
			
			
			
			foreach($dom->getElementsByTagName("class") as $node){
				$castorClass = new CastorClass();
				
				$castorClass->parent=null;
				
				$castorClass->fullName = $node->getAttribute("name");
				$clsNames = explode(".",$castorClass->fullName);
				// Castor requiere clases completament cualificadas (packagename)
				if(count($clsNames)>1){
					$castorClass->name=$clsNames[count($clsNames)-1];
				}else{
					$castorClass->name=$castorClass->fullName;
				}
				
				$castorClass->tagName= $node->getElementsByTagName("map-to")->item(0)->getAttribute("xml");
				$castorClass->fields=array();
				
				// Añadimos el nombre del padre, sin cualificacion
				if($node->hasAttribute("extends")){
					$parentName = $node->getAttribute("extends");
					$parentClsNames = explode(".",$parentName);
					// Castor requiere clases completament cualificadas (packagename)
					if(count($parentClsNames)>1){
						$castorClass->parent=$parentClsNames[count($parentClsNames)-1];
					}else{
						$castorClass->parent=$parentName;
					}
				}
				
				foreach($node->getElementsByTagName("field") as $fieldNode){
					$field = new CastorField();
					$field->name=$fieldNode->getAttribute("name");
					$field->fullType=$fieldNode->getAttribute("type");
					
					$clsType = explode(".",$field->fullType);
					// Castor requiere clases completament cualificadas (packagename)
					if(count($clsType)>1){
						$field->type=$clsType[count($clsType)-1];
					}else{
						$field->type=$field->fullType;
					}
					
					if($fieldNode->hasAttribute("collection")){
						$field->collection=$fieldNode->getAttribute("collection");
					}
					
					$bindNode = $fieldNode->getElementsByTagName("bind-xml")->item(0);
					$field->bindName=$bindNode->getAttribute("name");
					//No existe bindType
					//$field->bindType=$bindNode->getAttribute("type");
					
					if($bindNode->hasAttribute("container")){
						$field->bindContainer=$bindNode->getAttribute("container");
					}
					if($bindNode->hasAttribute("node")){
						$field->bindNode=$bindNode->getAttribute("node");
					}
					$castorClass->fields[$field->name]=$field;
				}
				
				$this->classes[$castorClass->name]=$castorClass;
			}
			
			// A continuacion, recorremos todas las clases y
			// resolvemos la herencia de campos de <extends>
			// Los arrays asociativos se guardan en orden,
			// asi que hay que asignar primero las propiedades
			// del padre para que se serialice con estas al prinicpio
			foreach($this->classes as $name=>$class){
				$newFieldArray=array();
				
				if($class->parent!=null){
					$parent = $class->parent; 
					while($parent!=null){
						$parentClass=$this->getClassByName($parent);
						foreach($parentClass->fields as $fieldName=>$field){
							if(!array_key_exists($fieldName, $class->fields)){
								$newFieldArray[$fieldName]=$field;
							}
						}
						$parent=$parentClass->parent;
					}
					// Ahora añadimos los campos del hijo
					foreach($class->fields as $fieldName=>$field){
						$newFieldArray[$fieldName]=$field;
					}
					// Reasignamos a la clase
					$class->fields=$newFieldArray;
				}
			}
		}

		/**
		 * Devuelve la fecha y hora en formato castor
		 */
		
		function getFieldByBindName($class,$bindName){
			foreach($class->fields as $name=>$field){
				if($field->bindName==$bindName) {
					return $field;
				}
			}
			return null;
		}
		
		function getClassByTagName($tagName){
			foreach($this->classes as $key=>$value){
				if($value->tagName==$tagName){
					return $value;
				}
			}
			return null;
		}
		
		function getClassByName($name){
			// Comprobamos si es un nombre cualificado
			
			if(!strstr($name, ".")){
				if(defined("debug")) echo "\t\tRecuperada clase $name\n\r";
				return $this->classes[$name];
			}else{
				foreach($this->classes as $name=>$class){
					if($class->fullName==$name){
						if(defined("debug")) echo "\t\tRecuperada clase ".$class->name."\n\r";
						return $class;
					}
				}
			}
			
			return null;
		}
		
		function unmarshal($xml){
			
			$dom = new DOMDocument();
			$dom->loadXML($xml);
			
			// Nodo padre
			$root = $dom->documentElement;
			return $this->getObject($root);
		}
		function getFieldValue($value,$field){
			if($field->type=="boolean"){
				return filter_var($value,FILTER_VALIDATE_BOOLEAN);
			}else if($field->type=="integer"){
				return (int)$value;
			}else if($field->type=="string"){
				return $value;
			}
		}
		
		function convertValue($value,$type){
			if($value==null){
				return null;
			}else{
				if($type=="date"){
					// Castor espera que la fecha sea ISO8601 combinada (yyyy-mm-ddThh:mm:ss.ms)
					return toISO8601_combined($value); 
				}else{
					return $value;
				}
			}
		}
		function getObject($node,$nodeClassName=null){
			$tagName = $node->nodeName;
			
			if(defined("debug")) echo "Deserializando nodo $tagName\n";

			$class = ($nodeClassName==null)?$this->getClassByTagName($tagName):$this->getClassByName($nodeClassName);

			$newClsName = $class->name;
			
			if($class==null) throw new CastorException("No se ha encontrado el mapeo para la etiqueta $tagName");
			
			$object = new $newClsName();
			
			foreach($class->fields as $fName=>$field){
				if(defined("debug")) echo "\tRecorriendo $fName\n";
				if($field->bindNode==null){
					if($field->collection!=null){
						if($field->collection=="arraylist"){
							// Buscamos la clase que se mapea en cada
							// elemento de la lista
							$object->$fName = array();

							$itemClass = $this->getClassByName($field->type);
							$nodeList=$node->getElementsByTagName($itemClass->tagName);
							foreach($nodeList as $child){
								array_push($object->$fName,$this->getObject($child));
							}	
						}
					}
				}else if($field->bindNode=="attribute"){
					$object->$fName=$this->getFieldValue($node->getAttribute($field->bindName),$field);
				}else if($field->bindNode=="element"){
					if(class_exists($field->type)){
						// Es una clase, llamamos a getObject
						// OJO: El nodo puede no contener elementos
						if($node->getElementsByTagName($field->bindName)->length>0){
							$object->$fName = $this->getObject($node->getElementsByTagName($field->bindName)->item(0),$field->type);
						}else{
							$object->$fName=null;
						}
					}else{
						// Es una primitiva con nodo de texto
						// OJO: El nodo puede no contener elementos
						if($node->getElementsByTagName($field->bindName)->length>0){
							// Convertimos el valor en funcion del tipo del campo
							// P.E: Date
							
							$object->$fName=$this->getFieldValue($node->getElementsByTagName($field->bindName)->item(0)->textContent,$field);
						}else{
							$object->$fName=null;
						}
					}
				}
			}
		
			return $object;	
					
		}
		
		function marshal($object){
			$class = $this->getClassByName(get_class($object));
			
			if(defined("debug")) echo "Marshalling {$class->name}\r\n";
									
			$dom = new DOMDocument("1.0","utf-8");
			
			$rootNode = $this->createElement($dom, $object);
			$dom->appendChild($rootNode);			
			
			return $dom->saveXML();
		}
		
		private function createElement(&$document, $object,$name=null){
			if(defined("debug")) echo "createElement(?,".get_class($object).",$name)";
			
			$class=$this->getClassByName(get_class($object));
			$nodeTagName=$name;
			
			if($nodeTagName==null) $nodeTagName=$class->tagName;
			
			if(defined("debug")) echo "Serializando miembro $nodeTagName\n";
			
			$node = $document->createElement($nodeTagName);
			
			foreach($class->fields as $fName=>$field){
				if(defined("debug")) echo "\tSerializando {$class->name}/{$field->name}\r\n";
				if($field->bindNode!=null){
					if($field->bindNode=="attribute"){
						$node->setAttribute($field->bindName,$this->convertValue($object->$fName,$field->type));
					}else if($field->bindNode=="element"){
						if(class_exists($field->type)){
							if($object->$fName!=null){
								$node->appendChild($this->createElement($document, $object->$fName,$field->bindName));	
							}else{
								$node->appendChild($document->createElement($field->bindName));
							}
							
						}else{
							if(defined("debug")) echo "bindname ".$field->bindName;
							$childNode = $document->createElement($field->bindName);
							$childNode->appendChild($document->createTextNode($this->convertValue($object->$fName,$field->type)));
							$node->appendChild($childNode);
						}
					}
				}else{
					if($field->collection!=null){
						if($field->collection=="arraylist"){
							// Aplanamos el array y serializamos
							
							// Añadimos primero el nodo
							$listNode = $document->createElement($field->bindName);
							// Si no se ha inicializado la variable, no es un array, pasamos de ella
							if(is_array($object->$fName)){							
								$mtx = array_values($object->$fName);
								
								foreach($mtx as $value){
									$listNode->appendChild($this->createElement($document,$value));
								}
							}
							$node->appendChild($listNode);
						}
					}
				}
			}
			return $node;
		}
	}
	
	//$t0=microtime();
	
	//$cas = new CastorMapper("mapping-mimo.xml");
	
	/*$object=$cas->unmarshal("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<XmlObjeto identificador=\"1\">
	<nombre>pepe</nombre>
	<apellido>Perez</apellido>
	<lista>
		<EstoEsUnItem nombre=\"Uno\">
			<identificador>1</identificador>
		</EstoEsUnItem>
		<EstoEsUnItem nombre=\"Dos\">
			<identificador>2</identificador>
		</EstoEsUnItem>
		<EstoEsUnItem nombre=\"Tres\">
			<identificador>3</identificador>
		</EstoEsUnItem>
	</lista>
	<item1 nombre=\"ititiem1\">
		<identificador>5</identificador>
	</item1>
	<item2 nombre=\"ititiem2\">
		<identificador>15</identificador>
	</item2>
</XmlObjeto>","nodo");*/
	//$object = $cas->unmarshal(file_get_contents("php://input"));
	//var_dump($object);
	//var_dump($cas->marshal($object));
	//var_dump(file_get_contents('php://input'));
	//echo $cas->marshal($object);
	//echo microtime()-$t0;	
	
?>
