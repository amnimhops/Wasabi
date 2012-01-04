<?php
	//define("SQL_DEBUG",true);
	define("TESTENV",true);
	//define("SQL_DEBUG",true);
	require_once("RepositorioLibreria.php");
	
	class Service{
		
		public static function process(AltaEjemplarRequest $request){
	
			Orm::connect("localhost","root","");
			Orm::setDb("wasabi");
			Orm::setCharset("utf8");
			//$response = new Ejemplar
			return Orm::save($ejemplar);
		}
		
		public static function getOkStatus(){
			$estado = new Estado();
			$estado->ok=true;
			
			return $estado;
		}
		
		public static function getErrorStatus($errors=null){
			$estado = new Estado();
			
			if($errors!=null && is_array($errors) && array_count_values($errors)>0){
				$estado->ok=false;
				$estado->errores=array();
					
				foreach($errors as $key=>$value){
					$err = new Error();
					$err->id=$key;
					$err->descripcion=$value;
					$estado->errores[]=$err;
				}
			}
			
			return $estado;
		}
		
		public static function connect(){
			Orm::connect("localhost","root","");
			Orm::setDb("wasabi");
			Orm::setCharset("utf8");
		}
		
		public static function TestSetupRequest(){
			$response = new TestSetupResponse();
			$response->estado=new Estado();
				
			if(defined("TESTENV") && TESTENV==true){
				Logger::write("Service","Limpiando la base de datos");
				Service::connect();
				// 1.- Limpiamos
				Orm::execute("DELETE FROM autoresejemplares");
				Orm::execute("DELETE FROM materiasejemplares");
				Orm::execute("DELETE FROM autores");
				Orm::execute("DELETE FROM materias");
				Orm::execute("DELETE FROM ejemplares");
				// 1.1- Restablecemos los id's
				Orm::execute("ALTER TABLE autores AUTO_INCREMENT=1");
				Orm::execute("ALTER TABLE materias AUTO_INCREMENT=1");
				Orm::execute("ALTER TABLE ejemplares AUTO_INCREMENT=1");
				// 2.- Añadimos autores de prueba
				$autor1 = new Autor();
				$autor1->nombre="Cervantes Saavedra, Miguel";
				$autor2=new Autor();
				$autor2->nombre="Perez Reverte, Arturo";
				$autor3=new Autor();
				$autor3->nombre="Eco, Umberto";
				$autor4=new Autor();
				$autor4->nombre="Kinnang Rawling, Marjorie";
				
				Orm::save($autor1);
				Orm::save($autor2);
				Orm::save($autor3);
				Orm::save($autor4);
				// 3.- Añadimos materias de prueba
				$materia1=new Materia();
				$materia1->nombre="Fantasía Épica";
				$materia2=new Materia();
				$materia2->nombre="Novela";
				$materia3=new Materia();
				$materia3->nombre="Ciencia Ficción";
				$materia4=new Materia();
				$materia4->nombre="Geografía";
				
				Orm::save($materia1);
				Orm::save($materia2);
				Orm::save($materia3);
				Orm::save($materia4);
				// 4.- Añadimos los ejemplares de prueba
				$ejemplar1 = new Ejemplar();
				$ejemplar1->fechaAlta=date("Y-m-d H:m:s",time());
				$ejemplar1->edicion="Primera";
				$ejemplar1->fechaPublicacion=date("Y-m-d H:m:s",time());
				$ejemplar1->precio="11.1";
				$ejemplar1->referencia="RF-0001";
				$ejemplar1->observaciones="Observacion1";
				$ejemplar1->editorial="canaya";
				$ejemplar1->titulo="Primer titulo";
				$ejemplar1->descripcion="Descripcion 1";
				$ejemplar1->fechaModificacion=date("Y-m-d H:m:s",time());
				
				Orm::save($ejemplar1);
				
				$ejemplar2 = new Ejemplar();
				$ejemplar2->fechaAlta=date("Y-m-d H:m:s",time());
				$ejemplar2->edicion="Segunda";
				$ejemplar2->fechaPublicacion=date("Y-m-d H:m:s",time());
				$ejemplar2->precio="23.4";
				$ejemplar2->referencia="RF-0002";
				$ejemplar2->observaciones="Observacion2";
				$ejemplar2->editorial="Alfaguarra";
				$ejemplar2->titulo="Segundo titulo";
				$ejemplar2->descripcion="Descripcion 2";
				$ejemplar2->fechaModificacion=date("Y-m-d H:m:s",time());
				
				Orm::save($ejemplar2);
				// 5.- Añadimos los autores a los libros
				$ea1=new AutorEjemplar();
				$ea1->idEjemplar=$ejemplar1->id;
				$ea1->idAutor=$autor1->id;
				
				$ea2=new AutorEjemplar();
				$ea2->idEjemplar=$ejemplar1->id;
				$ea2->idAutor=$autor2->id;
				
				Orm::save($ea1);
				Orm::save($ea2);
				
				$ea3=new AutorEjemplar();
				$ea3->idEjemplar=$ejemplar2->id;
				$ea3->idAutor=$autor3->id;
				
				$ea4=new AutorEjemplar();
				$ea4->idEjemplar=$ejemplar2->id;
				$ea4->idAutor=$autor4->id;
				
				Orm::save($ea3);
				Orm::save($ea4);
				
				// 5.- Añadimos las materias a los libros
				$em1=new MateriaEjemplar();
				$em1->idEjemplar=$ejemplar1->id;
				$em1->idMateria=$materia1->id;
				
				$em2=new MateriaEjemplar();
				$em2->idEjemplar=$ejemplar1->id;
				$em2->idMateria=$materia2->id;
				
				Orm::save($em1);
				Orm::save($em2);
				
				$em3=new MateriaEjemplar();
				$em3->idEjemplar=$ejemplar2->id;
				$em3->idMateria=$materia3->id;
				
				$em4=new MateriaEjemplar();
				$em4->idEjemplar=$ejemplar2->id;
				$em4->idMateria=$materia4->id;
				
				Orm::save($em3);
				Orm::save($em4);
				
				$response->estado->ok=true;
			}else{
				$response->estado->ok=false;
			}
			
			return $response;
		}
		
		public static function NuevoAutorRequest(NuevoAutorRequest $request){
			Service::connect();
			
			$response=new AutorResponse();
			
			try{
				Orm::save($request->autor);
				$response->estado=Service::getOkStatus();
				$response->autor=$request->autor;
				Logger::write("NuevoAutorRequest","Autor {$response->autor->id} añadido con exito");	
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception",$e->getMessage()));
			}
			
			return $response;
		}
		
		public static function ModificarAutorRequest(ModificarAutorRequest $request){
			Service::connect();
			
			$response=new AutorResponse();
			
			try{
				Orm::update($request->autor);
				$response->estado=Service::getOkStatus();
				$response->autor=$request->autor;	
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception",$e->getMessage()));
			}
			
			return $response;
		}
		
		public static function BuscarAutoresRequest(BuscarAutoresRequest $request){
			Service::connect();
			$autores=array();
			
			$response = new BuscarAutoresResponse();
			
			try{
				if($request->id!=null){
					$autores = Orm::mappedSelect("Autor", "SELECT * FROM AUTORES WHERE id = ".$request->id);	
				}else if($request->idEjemplar){
					$autores = Orm::mappedSelect("Autor", "SELECT AUTORES.* FROM AUTORES WHERE id IN (SELECT DISTINCT idautor FROM autoresejemplares WHERE idejemplar= ".$request->idEjemplar.")");
				}else if($request->nombre!=null){
					$autores = Orm::mappedSelect("Autor", "SELECT * FROM AUTORES WHERE Nombre like '%".$request->nombre."%' ORDER BY Nombre ASC");
				}
				
				Logger::write("BuscarAutoresRequest",count($autores)." autores encontrados");
				
				$response->lista=$autores;
				$response->estado=Service::getOkStatus();
				
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
			
			return $response;
		}
		
		
		/** MATERIAS **/
		public static function NuevaMateriaRequest(NuevaMateriaRequest $request){
			Service::connect();
			
			$response=new MateriaResponse();
			try{					
				Orm::save($request->materia);
				$response->estado=Service::getOkStatus();
				$response->materia=$request->materia;
				
				Logger::write("NuevaMateriaRequest","Materia {$response->materia->id} añadida con exito");
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
			
			return $response;
		}
		
		public static function ModificarMateriaRequest(ModificarMateriaRequest $request){
			Service::connect();
			
			$response=new MateriaResponse();
			
			try{
				Orm::update($request->materia);
				$response->estado=Service::getOkStatus();
				
				$response->materia=$request->materia;
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
			
			return $response;
		}
		
		public static function BuscarMateriasRequest(BuscarMateriasRequest $request){
			Service::connect();
			
			$response = new BuscarMateriasResponse();
			
			$materias=array();
			
			try{
					
				if($request->id!=null){
					$materias = Orm::mappedSelect("Materia", "SELECT * FROM MATERIAS WHERE id = ".$request->id);	
				}else if($request->idEjemplar){
					$materias = Orm::mappedSelect("Materia", "SELECT MATERIAS.* FROM MATERIAS WHERE id IN (SELECT DISTINCT idmateria FROM materiasejemplares WHERE idejemplar= ".$request->idEjemplar.")");
				}else if($request->nombre!=null){
					$materias = Orm::mappedSelect("Materia", "SELECT * FROM MATERIAS WHERE Nombre like '%".$request->nombre."%' ORDER BY Nombre ASC");
				}
				
				$response->lista=$materias;
				
				$response->estado=Service::getOkStatus();
				
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
			
			return $response;
		}
		
		public static function AltaEjemplarRequest(AltaEjemplarRequest $request){
			Service::connect();
			
			$response = new EjemplarResponse();
			try{
				if($request->ejemplar==null){
					throw new Exception("No se ha suministrado ningun ejemplar");
				}
				if($request->ejemplar->id!=null){
					throw new Exception("No se puede dar de alta un ejemplar existente (id={$request->ejemplar->id})");
				}
				
				Orm::beginTrans();
				
				Orm::save($request->ejemplar);
				foreach($request->ejemplar->autores as $autor){
					
					$autorEjemplar = new AutorEjemplar();
					$autorEjemplar->idEjemplar=$request->ejemplar->id;
					$autorEjemplar->idAutor=$autor->id;

					Orm::save($autorEjemplar);
				}
				
				foreach($request->ejemplar->materias as $materia){
					$materiaEjemplar = new MateriaEjemplar();
					$materiaEjemplar->idEjemplar=$request->ejemplar->id;
					$materiaEjemplar->idMateria=$materia->id;
					Orm::save($materiaEjemplar);
				}
				
				Orm::commitTrans();
				
				$response->ejemplar=$request->ejemplar;
				$response->estado=Service::getOkStatus();
				
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
			

			return $response;
		}
		
		public static function ModificarEjemplarRequest(ModificarEjemplarRequest $request){
			Service::connect();
			
			$response = new EjemplarResponse();
			
			try{
				if($request->ejemplar==null){
					throw new Exception("No se ha suministrado ningun ejemplar");
				}
				if($request->ejemplar->id==null){
					throw new Exception("No se puede modificar un ejemplar sin id");
				}
				
				Orm::beginTrans();
				
				Orm::update($request->ejemplar);
				
				// Borramos los autores
				Orm::execute("DELETE FROM autoresejemplares WHERE idEjemplar=".$request->ejemplar->id);
				
				$orden = 1;
				
				foreach($request->ejemplar->autores as $autor){
					
					$autorEjemplar = new AutorEjemplar();
					$autorEjemplar->idEjemplar=$request->ejemplar->id;
					$autorEjemplar->idAutor=$autor->id;
					$autorEjemplar->orden=$orden++;
					
					Orm::save($autorEjemplar);
				}
				// Borramos las materias
				Orm::execute("DELETE FROM materiasejemplares WHERE idEjemplar=".$request->ejemplar->id);
				
				$orden=1;
				
				foreach($request->ejemplar->materias as $materia){
					$materiaEjemplar = new MateriaEjemplar();
					$materiaEjemplar->idEjemplar=$request->ejemplar->id;
					$materiaEjemplar->idMateria=$materia->id;
					$materiaEjemplar->orden=$orden++;
					
					Orm::save($materiaEjemplar);
				}
				
				Orm::commitTrans();
				
				$response->ejemplar=$request->ejemplar;
				$response->estado=Service::getOkStatus();
				
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
			

			return $response;
		}

		public static function BuscarEjemplaresRequest(BuscarEjemplaresRequest $request){
			$response = new BuscarEjemplaresResponse();
			$response->resultado=new ResultadoBusquedaEjemplares();
			
			
			try{
				if($request->parametros!=null){
					Service::connect();
					
					$lista=array();
					//1.-Busqueda por id
					if($request->parametros->id!=null){
						$ejemplar=new Ejemplar();
						$ejemplar->id=$request->parametros->id;
						$ejemplar=Orm::get($ejemplar);
						
						if($ejemplar!=null){
							$lista[]=$ejemplar;
							$response->resultado->numeroResultados=1;
							$response->resultado->numeroPaginas=1;
							$response->resultado->pagina=0;
						}
					}else{ // El chorizo!
						$lista=busquedaParametrizadaEjemplares($request->parametros);
					}
					//var_dump($lista);
					$response->resultado->ejemplares=$lista;
					
					// Obtenemos autores y materias de los ejemplares encontrados
					foreach($lista as $ejemplar){
						$autores = Orm::mappedSelect("Autor", "SELECT autores.* FROM autores LEFT JOIN autoresejemplares on autoresejemplares.idAutor=autores.id WHERE autoresejemplares.idEjemplar={$ejemplar->id} ORDER BY autoresejemplares.orden ASC");
						$materias= Orm::mappedSelect("Materia", "SELECT materias.* FROM materias LEFT JOIN materiasejemplares on materiasejemplares.idMateria=materias.id WHERE materiasejemplares.idEjemplar={$ejemplar->id} ORDER BY materiasejemplares.orden ASC");
						
						$ejemplar->autores=$autores;
						$ejemplar->materias=$materias;
					}
					
					$response->estado=Service::getOkStatus();
				}else{
					throw new Exception("No se han indicado parametros de busqueda");
				}
			}catch(Exception $e){
				$response->estado=Service::getErrorStatus(array("exception"=>$e->getMessage()));
			}
					
			return $response;
		}
	}

	
	
	function __modificacionEjemplarRequest(ModificacionEjemplarRequest $request){
		
	}

	function __eliminacionEjemplarRequest(EliminacionEjemplarRequest $request){
		
	}
	
	
