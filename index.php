<?php


	/*
		<libro>
	 * 		<id>0xCAFEBABE</id>
	 * 		<nombre>el quihote</nombre>
	 * 		<propiedades>
	 * 			<propiedad>
	 * 				<nombre>prop1</nombre>
	 * 				<valor>valor1</valor>
	 * 			</propiedad>
	 * 		
	 */
	interface IXMLSerializable{
		public function unmarshal($name);
	}

	class FueraDeRangoException extends Exception{
		public function __construct($msg){
			parent::__construct($msg);
			
		}
	}

	class Collection implements IXMLSerializable{
		var $_data;
		
		public function __construct(){
			$this->_data=array();
			$args = func_get_args();
		
			if(count($args)>0){
				if(is_object($args[0]) && get_class($args[0])=="Collection"){
					foreach($args[0]->_data as $item){
						$this->_data[]=$item;
					}
				}else{
					foreach($args as $arg){
						$this->_data[]=$arg;
					}	
				}
			}
		}
		
		public static function copy(Collection $other){
			$newcol = new Collection();
			
			foreach($other->_data as $element){
				$newcol->_data[]=$element;
			}
		}
		
		public function size(){
			return count($this->data);
		}
		
		public function add($item){
			$this->_data[]=$item;
		}
		
		public function remove($index){
			if($index<count($this->_data)){
				$c=0;
				while($c<$index) $c++;
				
				$value = $this->_data[$c];
				return $value;
			}else{
				throw new FueraDeRangoException("");
			}
		}
		
		public function unmarshal($name){
			$xml ="<xxx{$name}_list";
			if(count($this->_data)>0){
				$xml.=">";

				foreach($this->_data as $item){
					if(is_object($item)){
						if(class_implements("IXmlSerializable")){
							$xml.=$item->unmarshal($name);
						}else{
							$xml.=XmlSerializer::serialize_object($name,$item);
						}
					}else{
						$xml.="<$name>$item</$name>";
					}
				}
				
				$xml.="</xxx{$name}_list>";
			}else{
				$xml.=" />";
			}
			
			return $xml;
		}
	}
	class SimpleXmlDeserializer{
		
		public function __construct($xmlstr,$clsName){
			$dom = new DOMDocument();
			$nodes = $dom->loadXML($xmlstr);		
			$object = new $clsName();
			
			foreach($dom->getElementsByTagName($clsName) as $node){
				$member = $node->nodeName;
				$object->$member
			}		
		}
		
		private function getObjectFromNode($node,$member){
			// El funcionamiento de este metodo es simple
			// Cojemos cada elemento del nodo, y rezamos
			// al dios que prefiramos para que todo esté
			// en su sitio.
			
		}
	}
	class XmlSerializer{
		public static function serialize($name,$object){
			$xml = "<?xml version='1.0' encoding='utf-8'?>";
			
			if(!is_object($object)){
				throw new Exception("$object no es serializable");
			}
			
			return $xml.XmlSerializer::serialize_object($name,$object);
		}
		
		public static function serialize_object($tagname,$object){
			if(!is_object($object)) throw new Exception("$object no es un objeto");
			$cls = get_class($object);
			
			$xml="<$tagname";
			
			$attributes = array();
			$objects = array();
						
			foreach($object as $member=>$value){
				if(!is_object($value) && !is_array($value)){
					$attributes[$member]=$value;
				}else{
					$objects[$member]=$value;
				}
			}
			
			foreach($attributes as $name=>$value){
				if(substr($name, 0,1)!="_"){
					$xml.=" $name='$value'";	
				}
				
			}
			
			if(count($objects)==0){
				$xml.=" />";
			}else{
				$xml.=">";
				foreach($objects as $name=>$value){
					// Objetos especiales
					$isSerializable = array_key_exists("IXMLSerializable",class_implements($value));

					if($isSerializable && substr($name,0,1)!="_"){
						$xml.="---".$value->unmarshal($name)."---";
					}else{
						$xml.=XmlSerializer::serialize_object($name,$value);
					}
				}
				$xml.="</$tagname>";
			}
			return $xml;
		}
		
		
	}
	
	class Probe{
		var $int=1;
		var $str="dos";
		//var $array = array();
		var $child=null;
		var $coleccion = null;
		public function __construct(){
			$this->child=new Child();
			$this->coleccion = null;
		}
		
	}
	class Child{
		var $val1='a';
		var $val2='b';
		var $val3=null;
		
		public function __construct(){
			$this->val3=new stdClass;
		}
	}
	
	//echo XmlSerializer::serialize("Cosa",new Probe());
	$col = new Collection(1,2,3);
	$bre=new Collection($col);
	$child = new Probe();
	$child->coleccion = $col;
	
	$xml = XmlSerializer::serialize("Probe",$child);
	echo $xml;
	new SimpleXmlDeserializer($xml,"Probe");
	
	
	
	
?>