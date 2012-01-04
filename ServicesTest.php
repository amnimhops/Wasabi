<?php
	//define("SQL_DEBUG",true);
	class Service{
		
		public static function process(AltaEjemplarRequest $request){
		}
		
		public static function connect(){
		}
		
		public static function NuevoAutorRequest(NuevoAutorRequest $request){
			Service::connect();
			
			
			
			$response=new AutorResponse();
			$response->estado=new Estado();
			$response->estado->ok=true;
			$response->estado->errores=null;
			$response->autor=$request->autor;
			
			$response->autor->id=1;
			
			return $response;
		}
		
		public static function ModificarAutorRequest(ModificarAutorRequest $request){
			Service::connect();
			
			$response=new AutorResponse();
			$response->estado=new Estado();
			$response->estado->ok=true;
			$response->estado->errores=null;
			$response->autor=$request->autor;
			
			return $response;
		}
		
		public static function BuscarAutoresRequest(BuscarAutoresRequest $request){
			Service::connect();
			$materias=array();
			
			if($request->id!=null){
				$autores = array();
				for($c=0;$c<1;$c++){
					$autor=new Autor();
					$autor->id=$request->id;
					$autor->nombre="Autor por id ".($c+1);
					$autores[]=$autor;
				}
			}else if($request->idEjemplar){
				$autores = array();
				for($c=0;$c<3;$c++){
					$autor=new Autor();
					$autor->id=$c+1;
					$autor->nombre="Autor por idejemplar".($c+1);
					$autores[]=$autor;
				}
			}else if($request->nombre!=null){
				$autores = array();
				for($c=0;$c<3;$c++){
					$autor=new Autor();
					$autor->id=$c+1;
					$autor->nombre="Autor por nombre ".($c+1);
					$autores[]=$autor;
				}
			}
			
			$response = new BuscarAutoresResponse();
			$response->lista=$autores;
			$response->estado=new Estado();
			$response->estado->ok=true;
			$response->estado->errores=null;
			return $response;
		}
		
		
		/** MATERIAS **/
		public static function NuevaMateriaRequest(NuevaMateriaRequest $request){
			Service::connect();
			
			$response=new MateriaResponse();
			$response->estado=new Estado();
			$response->estado->ok=true;
			$response->estado->errores=null;
			$response->materia=$request->materia;

			$response->materia->id=1;
						
			return $response;
		}
		
		public static function ModificarMateriaRequest(ModificarMateriaRequest $request){
			Service::connect();
			
			$response=new MateriaResponse();
			$response->estado=new Estado();
			$response->estado->ok=true;
			$response->estado->errores=null;
			$response->materia=$request->materia;
			
			return $response;
		}
		
		public static function BuscarMateriasRequest(BuscarMateriasRequest $request){
			Service::connect();
			$materias=array();
			
			if($request->id!=null){
				$materias = array();
				for($c=0;$c<1;$c++){
					$materia=new Materia();
					$materia->id=$request->id;
					$materia->nombre="Materia por id ".($c+1);
					$materias[]=$materia;
				}
			}else if($request->idEjemplar){
				$materias = array();
				for($c=0;$c<3;$c++){
					$materia=new Materia();
					$materia->id=$c+1;
					$materia->nombre="Materia por idejemplar".($c+1);
					$materias[]=$materia;
				}
			}else if($request->nombre!=null){
				$materias = array();
				for($c=0;$c<3;$c++){
					$materia=new Materia();
					$materia->id=$c+1;
					$materia->nombre="Materia por nombre ".($c+1);
					$materias[]=$materia;
				}
			}
			
			
			
			$response = new BuscarMateriasResponse();
			$response->lista=$materias;
			$response->estado=new Estado();
			$response->estado->ok=true;
			$response->estado->errores=null;
			return $response;
		}
	}
?>