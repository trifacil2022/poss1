<?php

require_once "conexion.php";

class ModeloVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/

	static public function mdlMostrarVentas($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id ASC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}
		
		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE VENTA
	=============================================*/

	static public function mdlIngresarVenta($tabla, $datos){
		try {
			$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_cliente, id_vendedor, productos, impuesto, neto, total, metodo_pago, lugar, vendedora) VALUES (:codigo, :id_cliente, :id_vendedor, :productos, :impuesto, :neto, :total, :metodo_pago, :lugar, :vendedora)");
	
			$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
			$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
			$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
			$stmt->bindValue(":productos", $datos["productos"], PDO::PARAM_STR); // Cambiado a bindValue
			$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
			$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
			$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
			$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
			$stmt->bindParam(":lugar", $datos["lugar"], PDO::PARAM_STR);
			$stmt->bindParam(":vendedora", $datos["vendedora"], PDO::PARAM_STR);
	
			if($stmt->execute()){
				return "ok";
			} else {
				return "Error de PDO: " . implode(", ", $stmt->errorInfo()); // Proporciona más detalles sobre el error
			}
		} catch (Exception $e) {
			return "Error: " . $e->getMessage();
		} finally {
			$stmt->closeCursor(); // Cierra el cursor para prevenir problemas de memoria
			$stmt = null;
		}
	}
	

	/*=============================================
	EDITAR VENTA
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET  id_cliente = :id_cliente, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total= :total, metodo_pago = :metodo_pago WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/

	static public function mdlEliminarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	

	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 55");

			$stmt -> execute();

			return $stmt -> fetchAll();	


		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like '%$fechaFinal%'");

			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{


				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}
		
			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*=============================================
	SUMAR EL TOTAL DE VENTAS
	=============================================*/

	static public function mdlSumaTotalVentas($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	

static public function mdlRangoFechasVentasDelDia(){

	/*=============================================
			$stmt = Conexion::conectar()->prepare("SELECT * FROM 'ventas' WHERE fecha BETWEEN '$fechaActualMasUno' AND '$fechaActualMasUno'");

	=============================================*/

		$fechaActual = new DateTime();
		$fechaActual ->add(new DateInterval("P1D"));
		$fechaActualMasUno = $fechaActual->format("Y-m-d");

	/*=============================================
			$stmt = Conexion::conectar()->prepare("SELECT * FROM 'ventas' WHERE fecha BETWEEN '$fechaActualMasUno' AND '$fechaActualMasUno'");
	=============================================*/
			$stmt = Conexion::conectar()->prepare("SELECT * FROM `ventas` WHERE DATE(`fecha`) = DATE(NOW())  ;");

	
		$stmt -> execute();

		return $stmt -> fetchAll();

	

}
static public function mdlRangoFechasVentasDelDiaEfectivo(){


		$fechaActual = new DateTime();
		$fechaActual ->add(new DateInterval("P1D"));
		$fechaActualMasUno = $fechaActual->format("Y-m-d");

	/*=============================================
			$stmt = Conexion::conectar()->prepare("SELECT * FROM 'ventas' WHERE fecha BETWEEN '$fechaActualMasUno' AND '$fechaActualMasUno'");
	=============================================*/
			$stmt = Conexion::conectar()->prepare("SELECT  * FROM `ventas`  WHERE `metodo_pago` = 'efectivo'  AND DATE(`fecha`) = DATE(NOW()) ;");

	
		$stmt -> execute();

		return $stmt -> fetchAll();

	

}

static public function mdlRangoFechasVentasDelDiaTarjeta(){

	/*=============================================
			$stmt = Conexion::conectar()->prepare("SELECT * FROM 'ventas' WHERE fecha BETWEEN '$fechaActualMasUno' AND '$fechaActualMasUno'");

	=============================================*/

		$fechaActual = new DateTime();
		$fechaActual ->add(new DateInterval("P1D"));
		$fechaActualMasUno = $fechaActual->format("Y-m-d");

	/*=============================================
			$stmt = Conexion::conectar()->prepare("SELECT * FROM 'ventas' WHERE fecha BETWEEN '$fechaActualMasUno' AND '$fechaActualMasUno'");
	=============================================*/
			$stmt = Conexion::conectar()->prepare("SELECT  * FROM `ventas`  WHERE `metodo_pago` != 'efectivo' AND DATE(`fecha`) = DATE(NOW()) ;");

	
		$stmt -> execute();

		return $stmt -> fetchAll();

	

}
static public function mdlRangoFechasVentasDelDiaEnvios(){


	$fechaActual = new DateTime();
	$fechaActual ->add(new DateInterval("P1D"));
	$fechaActualMasUno = $fechaActual->format("Y-m-d");

/*=============================================
		$stmt = Conexion::conectar()->prepare("SELECT * FROM 'ventas' WHERE fecha BETWEEN '$fechaActualMasUno' AND '$fechaActualMasUno'");
=============================================*/
		$stmt = Conexion::conectar()->prepare("SELECT  * FROM `ventas`  WHERE `metodo_pago` = 'envios'  AND DATE(`fecha`) = DATE(NOW()) ;");


	$stmt -> execute();

	return $stmt -> fetchAll();



}

static public function rangodelmes(){

		// Preparar y ejecutar la consulta SQL para obtener el total de ventas por día
		$stmt = Conexion::conectar()->prepare("SELECT  * FROM `ventas`  WHERE 1");
		$stmt->execute();
	
		// Obtener los resultados de la consulta
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		// Devolver el resultado en un array asociativo
		return $resultados;
	

}



}