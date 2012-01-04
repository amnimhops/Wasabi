<?php
	
	class ModelEjemplar extends Model{
		public function map(){
			$this->Object("Ejemplar");
			$this->Table("ejemplares");
			$this->Field("id", AUTONUM, F_NOTNULL);
			$this->Field("referencia",VARCHAR,F_NOTNULL);
			$this->Field("titulo",VARCHAR,F_NOTNULL);
			$this->Field("edicion",VARCHAR,F_NULL);
			$this->Field("editorial",VARCHAR,F_NULL);
			$this->Field("precio",DECIMAL,F_NULL);
			$this->Field("fechaPublicacion",DATETIME,F_NULL);
			$this->Field("fechaAlta",DATETIME,F_NULL);
			$this->Field("fechaModificacion",DATETIME,F_NULL);
			$this->Field("descripcion",VARCHAR,F_NULL);
			$this->Field("observaciones",VARCHAR,F_NULL);
			$this->PrimaryKey("id");
		}	
	}
	
	class ModelAutor extends Model{
		public function map(){
			$this->Object("Autor");
			$this->Table("autores");
			$this->Field("id",AUTONUM,F_NOTNULL);
			$this->Field("nombre",VARCHAR,F_NOTNULL);
			$this->PrimaryKey("id");
		}
	}
	
	class ModelMateria extends Model{
		public function map(){
			$this->Object("Materia");
			$this->Table("materias");
			$this->Field("id",AUTONUM,F_NOTNULL);
			$this->Field("nombre",VARCHAR,F_NOTNULL);
			$this->PrimaryKey("id");
		}
	}
	class ModelAutorEjemplar extends Model{
		public function map(){
			$this->Object("AutorEjemplar");
			$this->Table("autoresejemplares");
			$this->Field("idEjemplar",INTEGER,F_NOTNULL);
			$this->Field("idAutor",INTEGER,F_NOTNULL);
			$this->Field("orden",INTEGER,F_NULL);
			$this->PrimaryKey("idEjemplar","idAutor");
		}
	}
	class ModelMateriaEjemplar extends Model{
		public function map(){
			$this->Object("MateriaEjemplar");
			$this->Table("materiasejemplares");
			$this->Field("idEjemplar",INTEGER,F_NOTNULL);
			$this->Field("idMateria",INTEGER,F_NOTNULL);
			$this->Field("orden",INTEGER,F_NULL);
			$this->PrimaryKey("idEjemplar","idMateria");
		}
	}?>