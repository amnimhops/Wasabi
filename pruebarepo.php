<?php

	require_once("Entities.class.php");
	require_once("model.php");
	require_once("mapping.php");
	
	define("SQL_DEBUG",true);
	
	Orm::connect("localhost","root","");
	Orm::setDb("wasabi");
		
	$materia = new Materia();
	$materia->nombre="nueva materia";
	Orm::save($materia);
	
	$autor = new Autor();
	$autor->nombre="Primer autor";
	Orm::save($autor);
	
	$ejemplar = new Ejemplar();
	$ejemplar->fechaAlta="2011-01-01 00:00:00";
	$ejemplar->edicion="primera";
	$ejemplar->fechaPublicacion="2011-01-02 00:00:00";
	$ejemplar->precio=25.95;
	$ejemplar->referencia="RX-43";
	$ejemplar->observaciones="Observacion";
	$ejemplar->editorial="Canalla";
	$ejemplar->titulo="Buffon moderno";
	$ejemplar->descripcion="Descripcion";
	$ejemplar->fechaModificacion="2011-01-03 00:00:00";
	
	Orm::save($ejemplar);
	
	$ae=new AutorEjemplar();
	$ae->idAutor=1;
	$ae->idEjemplar=1;
	
	Orm::save($ae);
	
	$me=new MateriaEjemplar();
	$me->idMateria=1;
	$me->idEjemplar=1;
	
	Orm::save($me);
	
?>