<?php
require __DIR__ . '/autoload.php'; //Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

date_default_timezone_set('America/Tegucigalpa');

class ControladorVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/

	static public function ctrMostrarVentas($item, $valor){

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	CREAR VENTA
	=============================================*/

	static public function ctrCrearVenta(){

		if(isset($_POST["nuevaVenta"])){

			/*=============================================
			ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL stockmetro Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
			=============================================*/

			if($_POST["listaProductos"] == ""){

					echo'<script>

				swal({
					  type: "error",
					  title: "La venta no se ha ejecuta si no hay productos",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "ventas";

								}
							})

				</script>';

				return;
			}


			$listaProductos = json_decode($_POST["listaProductos"], true);

			$totalProductosComprados = array();

			foreach ($listaProductos as $key => $value) {
				print_r($value); // o var_dump($value);
				echo '<br>';
				// Operaciones con $value
				array_push($totalProductosComprados, $value["cantidad"]);
			
				$tablaProductos = "productos";
				$item = "id";
				$valor = $value["id"];
				$orden = "id";
			
				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);
			
				$item1a = "ventas";
				$valor1a = $value["cantidad"] + $traerProducto["ventas"];
				$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);
			
				$item1b = "stockmetro";
				$valor1b = $value["stockmetro"];
				$nuevostockmetro = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);
			$totalproductoactualizado = 101 - $value["cantidad"];
			// Configuración de la solicitud cURL
			$curl = curl_init();
			$url = 'https://ellas-api.tri-facil.com/productos/restandoproductos.php';
			$postData = array(
				'id' => $value["id"],  // Reemplaza con el valor deseado
				'nuevo_stock' =>  $value["stockmetro"],  // Reemplaza con el valor deseado
				'nuevo_tienda' => 'stockmetro',  // Reemplaza con el valor deseado
			);
			
			// Otras opciones de cURL...
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => 'PUT',
				CURLOPT_POSTFIELDS => http_build_query($postData),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/x-www-form-urlencoded'
				),
			));
			
			// Ejecutar la solicitud cURL
			$response = curl_exec($curl);
			
			// Otras operaciones con la respuesta si es necesario...
			
			// Cerrar la conexión cURL
			curl_close($curl);
		}
			
			// Otro código después de la solicitud cURL si es necesario...
			
			$tablaClientes = "clientes";

			$item = "id";
			$valor = $_POST["seleccionarCliente"];

			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);

			$item1a = "compras";
				
			$valor1a = array_sum($totalProductosComprados) + $traerCliente["compras"];

			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valor);

			$item1b = "ultima_compra";

			date_default_timezone_set('America/Tegucigalpa');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$valor1b = $fecha.' '.$hora;

			$fechaCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1b, $valor1b, $valor);

			/*=============================================
			GUARDAR LA COMPRA
			=============================================*/	

			$tabla = "ventas";

			$datos = array("id_vendedor"=>$_POST["idVendedor"],
						   "id_cliente"=>$_POST["seleccionarCliente"],
						   "codigo"=>$_POST["nuevaVenta"],
						   "productos"=>$_POST["listaProductos"],
						   "impuesto"=>$_POST["nuevoPrecioImpuesto"],
						   "neto"=>$_POST["nuevoPrecioNeto"],
						   "total"=>$_POST["totalVenta"],
						   "metodo_pago"=>$_POST["nuevoMetodoPago"]);

			$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);
		print_r($respuesta);
			if($respuesta == "ok"){



				$subtotaldeopresion = $_POST["totalVenta"]*15/100;


				$impresora = "POS-90";
				$conector = new WindowsPrintConnector($impresora);
				$printer = new Printer($conector);
				
				$printer -> text("                  INVERSIONES D. & M. \n");
				$printer -> text("                  Ellas Beauty \n");
				$printer -> text("                 RTN: 08019022438392          \n");
				$printer -> text("             ESTAMOS UBICADOS EN Metromall\n");
				$printer -> text("\n");
				$printer -> text("                TEL:   8785-7642\n");
				$printer -> text("          EMAIL: info@ellas-beauty.com     \n");
				$printer -> text(" \n");
				$printer -> text("             NO. 000-001-01-00000001 \n");
				$printer -> text("            CODIGO DE FACTURA ".$_POST["nuevaVenta"]."\n");
				$printer -> text("          ATENDIDO POR: SINDY RODRIGUEZ \n");
				$printer -> text(" \n");
				$printer -> text("      CAI: \n");
				$printer -> text("     AC042A-CFCA7E-5B4D95-BBF6C7-71A6B8-DE \n");
				$printer -> text("        FECHA LIMITE AUTORIZADO: \n");

				$printer -> text("        DE: 000-001-01-00000001 \n");
				$printer -> text("        HASTA: 000-001-01-00000025 \n");

				$printer -> text("       NOMBRE: Consumidor Final  \n");
				$printer -> text("             RTN : 000000000000000 \n");
				$printer -> text("           FECHA: ".date("Y-m-d H:i:s")."\n" );
				$printer -> feed();
				$printer -> text("--------------------PRODUCTOS------------------\n");
				$printer -> text(" \n");
				foreach ($listaProductos as $key => $value) {
				$printer -> text($value["descripcion"]."\n");
				$printer -> text("                  ".$value["cantidad"]." x L".number_format($value["precio"]*85/100,2)." Und = L".number_format($value["total"]*85/100,2)."\n");
				
			}
				$printer -> text(" \n");
				$printer -> text("SUBTOTAL                            L".number_format($_POST["nuevoPrecioNeto"]-$subtotaldeopresion,2)."\n");
				$printer -> text("DESCUENTO                           L0.00\n");
				$printer -> text("ISV 15%                             L".number_format($subtotaldeopresion,2)."\n");
				$printer -> text("                               -----------\n");
				$printer -> text("Total                               L".number_format($_POST["totalVenta"],2)." \n");
				$printer -> text(" \n");

				$printer -> text("           FECHA: ".date("Y-m-d H:i:s")."\n" );
				$printer -> text(" \n");
				$printer -> text("            VISITA NUESTRA SITIO WEB\n");
				$printer -> text("               ELLAS-BEAUTY.COM\n");
				$printer -> text(" \n");
				$printer -> text(" \n");
				$printer -> text("************************************************\n");
				$printer -> text("    NO DEVOLUCIONES EN PRODUCTOS DE SKINCARE  \n");
				$printer -> text("************************************************\n");
				$printer -> text(" \n");
				$printer -> text(" \n");
				$printer -> text("----------------------------------------------\n");
				$printer -> text("              GRACIAS POR TU COMPRA\n");
				$printer -> text("----------------------------------------------\n");
				$printer -> feed(1);
				$printer -> feed(2);
				$printer -> cut();
				$printer -> close();

			}
			if($respuesta == "ok"){

				$impresora = "POS-90";
				$conector = new WindowsPrintConnector($impresora);
				$printer = new Printer($conector);
				
				$printer -> text("         COPIA DE FACTURA Metromall\n");
				$printer -> text("                     ".$_POST["nuevoMetodoPago"]."\n");
				$printer -> feed(1);
				$printer -> feed(1);

				$printer -> text("              Metromall \n");
				$printer -> text(" \n");
				$printer -> text("          CODIGO DE FACTURA ".$_POST["nuevaVenta"]."\n");
				$printer -> feed();
				$printer -> text("--------------------PRODUCTOS------------------\n");
				$printer -> text(" \n");

				
				foreach ($listaProductos as $key => $value) {
				$printer -> text($value["descripcion"]."\n");
				$printer -> text("                  ".$value["cantidad"]." x L".number_format($value["precio"],2)." Und = L".number_format($value["total"],2)."\n");
				
			}
				$printer -> text(" \n");
				$printer -> text("Total                               L".number_format($_POST["totalVenta"],2)." \n");
				$printer -> text(" \n");

				$printer -> text("           FECHA: ".date("Y-m-d H:i:s")."\n" );
				$printer -> text(" \n");
				$printer -> feed(1);
				$printer -> feed(2);
				$printer -> feed(2);
				$printer -> cut();
				$printer -> close();

	
				echo'<script>

				localStorage.removeItem("rango");
				swal({
					  type: "success",
					  title: "La venta ha sido guardada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "ventas";

								}
							})

				</script>';

			}else{
				echo'<script>

				swal({
					  type: "error",
					  title: "error",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								
							})

				</script>';
			}


	}
}

	/*=============================================
	EDITAR VENTA
	=============================================*/

	static public function ctrEditarVenta(){

		if(isset($_POST["editarVenta"])){

			/*=============================================
			FORMATEAR TABLA DE PRODUCTOS Y LA DE CLIENTES
			=============================================*/
			$tabla = "ventas";

			$item = "codigo";
			$valor = $_POST["editarVenta"];

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			/*=============================================
			REVISAR SI VIENE PRODUCTOS EDITADOS
			=============================================*/

			if($_POST["listaProductos"] == ""){

				$listaProductos = $traerVenta["productos"];
				$cambioProducto = false;


			}else{

				$listaProductos = $_POST["listaProductos"];
				$cambioProducto = true;
			}

			if($cambioProducto){

				$productos =  json_decode($traerVenta["productos"], true);

				$totalProductosComprados = array();

				foreach ($productos as $key => $value) {

					array_push($totalProductosComprados, $value["cantidad"]);
					
					$tablaProductos = "productos";

					$item = "id";
					$valor = $value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					$item1a = "ventas";
					$valor1a = $traerProducto["ventas"] - $value["cantidad"];

					$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

					$item1b = "stockmetro";
					$valor1b = $value["cantidad"] + $traerProducto["stockmetro"];

					$nuevostockmetro = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

				}

				$tablaClientes = "clientes";

				$itemCliente = "id";
				$valorCliente = $_POST["seleccionarCliente"];

				$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);

				$item1a = "compras";
				$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);		

				$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);

				/*=============================================
				ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL stockmetro Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
				=============================================*/

				$listaProductos_2 = json_decode($listaProductos, true);

				$totalProductosComprados_2 = array();

				foreach ($listaProductos_2 as $key => $value) {

					array_push($totalProductosComprados_2, $value["cantidad"]);
					
					$tablaProductos_2 = "productos";

					$item_2 = "id";
					$valor_2 = $value["id"];
					$orden = "id";

					$traerProducto_2 = ModeloProductos::mdlMostrarProductos($tablaProductos_2, $item_2, $valor_2, $orden);

					$item1a_2 = "ventas";
					$valor1a_2 = $value["cantidad"] + $traerProducto_2["ventas"];

					$nuevasVentas_2 = ModeloProductos::mdlActualizarProducto($tablaProductos_2, $item1a_2, $valor1a_2, $valor_2);

					$item1b_2 = "stockmetro";
					$valor1b_2 = $traerProducto_2["stockmetro"] - $value["cantidad"];

					$nuevostockmetro_2 = ModeloProductos::mdlActualizarProducto($tablaProductos_2, $item1b_2, $valor1b_2, $valor_2);

				}

				$tablaClientes_2 = "clientes";

				$item_2 = "id";
				$valor_2 = $_POST["seleccionarCliente"];

				$traerCliente_2 = ModeloClientes::mdlMostrarClientes($tablaClientes_2, $item_2, $valor_2);

				$item1a_2 = "compras";

				$valor1a_2 = array_sum($totalProductosComprados_2) + $traerCliente_2["compras"];

				$comprasCliente_2 = ModeloClientes::mdlActualizarCliente($tablaClientes_2, $item1a_2, $valor1a_2, $valor_2);

				$item1b_2 = "ultima_compra";

				date_default_timezone_set('America/Tegucigalpa');

				$fecha = date('Y-m-d');
				$hora = date('H:i:s');
				$valor1b_2 = $fecha.' '.$hora;

				$fechaCliente_2 = ModeloClientes::mdlActualizarCliente($tablaClientes_2, $item1b_2, $valor1b_2, $valor_2);

			}

			/*=============================================
			GUARDAR CAMBIOS DE LA COMPRA
			=============================================*/	

			$datos = array("id_vendedor"=>$_POST["idVendedor"],
						   "id_cliente"=>$_POST["seleccionarCliente"],
						   "codigo"=>$_POST["editarVenta"],
						   "productos"=>$listaProductos,
						   "impuesto"=>$_POST["nuevoPrecioImpuesto"],
						   "neto"=>$_POST["nuevoPrecioNeto"],
						   "total"=>$_POST["totalVenta"],
						   "metodo_pago"=>$_POST["listaMetodoPago"],
						   "lugar"=>$_POST["lugar"],
						   "vendedora"=>$_POST["vendedora"]);
			$respuesta = ModeloVentas::mdlEditarVenta($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				localStorage.removeItem("rango");

				swal({
					  type: "success",
					  title: "La venta ha sido editada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then((result) => {
								if (result.value) {

								window.location = "ventas";

								}
							})

				</script>';

			}

		}

	}


	/*=============================================
	ELIMINAR VENTA
	=============================================*/

	static public function ctrEliminarVenta0(){

		if(isset($_GET["idVenta"])){

			$tabla = "ventas";

			$item = "id";
			$valor = $_GET["idVenta"];

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			/*=============================================
			ACTUALIZAR FECHA ÚLTIMA COMPRA
			=============================================*/

			$tablaClientes = "clientes";

			$itemVentas = null;
			$valorVentas = null;

			$traerVentas = ModeloVentas::mdlMostrarVentas($tabla, $itemVentas, $valorVentas);

			$guardarFechas = array();

			foreach ($traerVentas as $key => $value) {
				
				if($value["id_cliente"] == $traerVenta["id_cliente"]){

					array_push($guardarFechas, $value["fecha"]);

				}

			}

			if(count($guardarFechas) > 1){

				if($traerVenta["fecha"] > $guardarFechas[count($guardarFechas)-2]){

					$item = "ultima_compra";
					$valor = $guardarFechas[count($guardarFechas)-2];
					$valorIdCliente = $traerVenta["id_cliente"];

					$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);

				}else{

					$item = "ultima_compra";
					$valor = $guardarFechas[count($guardarFechas)-1];
					$valorIdCliente = $traerVenta["id_cliente"];

					$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);

				}


			}else{

				$item = "ultima_compra";
				$valor = "0000-00-00 00:00:00";
				$valorIdCliente = $traerVenta["id_cliente"];

				$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);

			}

			/*=============================================
			FORMATEAR TABLA DE PRODUCTOS Y LA DE CLIENTES
			=============================================*/

			$productos =  json_decode($traerVenta["productos"], true);

			$totalProductosComprados = array();

			foreach ($productos as $key => $value) {

				array_push($totalProductosComprados, $value["cantidad"]);
				
				$tablaProductos = "productos";

				$item = "id";
				$valor = $value["id"];
				$orden = "id";

				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

				$item1a = "ventas";
				$valor1a = $traerProducto["ventas"] - $value["cantidad"];

				$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

				$item1b = "stockmetro";
				$valor1b = $value["cantidad"] + $traerProducto["stockmetro"];

				$nuevostockmetro = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

			}

			$tablaClientes = "clientes";

			$itemCliente = "id";
			$valorCliente = $traerVenta["id_cliente"];

			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);

			$item1a = "compras";
			$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);

			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);

			/*=============================================
			ELIMINAR VENTA
			=============================================*/

			$respuesta = ModeloVentas::mdlEliminarVenta($tabla, $_GET["idVenta"]);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "La venta ha sido borrada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "ventas";

								}
							})

				</script>';

			}		
		}

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	

	static public function ctrRangoFechasVentas($fechaInicial, $fechaFinal){

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal);

		return $respuesta;
		
	}

	/*=============================================
	DESCARGAR EXCEL
	=============================================*/

	public function ctrDescargarReporte(){

		if(isset($_GET["reporte"])){

			$tabla = "ventas";

			if(isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])){

				$ventas = ModeloVentas::mdlRangoFechasVentas($tabla, $_GET["fechaInicial"], $_GET["fechaFinal"]);

			}else{

				$item = null;
				$valor = null;

				$ventas = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			}


			/*=============================================
			CREAMOS EL ARCHIVO DE EXCEL
			=============================================*/

			$Name = $_GET["reporte"].'.xls';

			header('Expires: 0');
			header('Cache-control: private');
			header("Content-type: application/vnd.ms-excel"); // Archivo de Excel
			header("Cache-Control: cache, must-revalidate"); 
			header('Content-Description: File Transfer');
			header('Last-Modified: '.date('D, d M Y H:i:s'));
			header("Pragma: public"); 
			header('Content-Disposition:; filename="'.$Name.'"');
			header("Content-Transfer-Encoding: binary");
		
			echo utf8_decode("<table border='0'> 

					<tr> 
					<td style='font-weight:bold; border:1px solid #eee;'>CÓDIGO</td> 
					<td style='font-weight:bold; border:1px solid #eee;'>LUGAR</td>
					<td style='font-weight:bold; border:1px solid #eee;'>CANTIDAD</td>
					<td style='font-weight:bold; border:1px solid #eee;'>PRODUCTOS</td>
					<td style='font-weight:bold; border:1px solid #eee;'>TOTAL</td>		
					<td style='font-weight:bold; border:1px solid #eee;'>METODO DE PAGO</td	
					<td style='font-weight:bold; border:1px solid #eee;'>EFECTIVO</td>	
					<td style='font-weight:bold; border:1px solid #eee;'>TARJETA</td>	
					<td style='font-weight:bold; border:1px solid #eee;'>FECHA</td>		

					</tr>");

			foreach ($ventas as $row => $item){

				$cliente = ControladorClientes::ctrMostrarClientes("id", $item["id_cliente"]);
				$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

			 echo utf8_decode("<tr>
			 			<td style='border:1px solid #eee;'>".$item["codigo"]."</td> 
			 			<td style='border:1px solid #eee;'>".$vendedor["nombre"]."</td>
			 			<td style='border:1px solid #eee;'>");

			 	$productos =  json_decode($item["productos"], true);

			 	foreach ($productos as $key => $valueProductos) {
			 			
			 			echo utf8_decode($valueProductos["cantidad"]."<br>");
			 		}

			 	echo utf8_decode("</td><td style='border:1px solid #eee;'>");	

		 		foreach ($productos as $key => $valueProductos) {
			 			
		 			echo utf8_decode($valueProductos["descripcion"]."<br>");
		 		
		 		}

		 		echo utf8_decode("</td>
					<td style='border:1px solid #eee;'> ".number_format($item["total"],2)."</td>
					<td style='border:1px solid #eee;'>".$item["metodo_pago"]."</td>
					<td style='border:1px solid #eee;'> ");
					if($item["metodo_pago"]=="Efectivo"){
						echo utf8_decode(number_format($item["total"],2));
					}else{
						echo utf8_decode(0);

					}
					
					
					echo utf8_decode("</td>
					<td style='border:1px solid #eee;'> ");
					if($item["metodo_pago"]=="Efectivo"){
						echo utf8_decode(0);

					}else{
						echo utf8_decode(number_format($item["total"],2));

					}
					
					
					echo utf8_decode("</td>

					<td style='border:1px solid #eee;'>".substr($item["fecha"],0,10)."</td>	

		 			</tr>");


			}


			echo "</table>";

		}

	}


	/*=============================================
	SUMA TOTAL VENTAS
	=============================================*/

	public function ctrSumaTotalVentas(){

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlSumaTotalVentas($tabla);

		return $respuesta;

	}

	/*=============================================
	DESCARGAR XML
	=============================================*/

	static public function ctrDescargarXML(){

		if(isset($_GET["xml"])){


			$tabla = "ventas";
			$item = "codigo";
			$valor = $_GET["xml"];

			$ventas = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			// PRODUCTOS

			$listaProductos = json_decode($ventas["productos"], true);

			// CLIENTE

			$tablaClientes = "clientes";
			$item = "id";
			$valor = $ventas["id_cliente"];

			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);

			// VENDEDOR

			$tablaVendedor = "usuarios";
			$item = "id";
			$valor = $ventas["id_vendedor"];

			$traerVendedor = ModeloUsuarios::mdlMostrarUsuarios($tablaVendedor, $item, $valor);

			//http://php.net/manual/es/book.xmlwriter.php

			$objetoXML = new XMLWriter();

			$objetoXML->openURI($_GET["xml"].".xml"); //Creación del archivo XML

			$objetoXML->setIndent(true); //recibe un valor booleano para establecer si los distintos niveles de nodos XML deben quedar indentados o no.

			$objetoXML->setIndentString("\t"); // carácter \t, que corresponde a una tabulación

			$objetoXML->startDocument('1.0', 'utf-8');// Inicio del documento
			
			// $objetoXML->startElement("etiquetaPrincipal");// Inicio del nodo raíz

			// $objetoXML->writeAttribute("atributoEtiquetaPPal", "valor atributo etiqueta PPal"); // Atributo etiqueta principal

			// 	$objetoXML->startElement("etiquetaInterna");// Inicio del nodo hijo

			// 		$objetoXML->writeAttribute("atributoEtiquetaInterna", "valor atributo etiqueta Interna"); // Atributo etiqueta interna

			// 		$objetoXML->text("Texto interno");// Inicio del nodo hijo
			
			// 	$objetoXML->endElement(); // Final del nodo hijo
			
			// $objetoXML->endElement(); // Final del nodo raíz


			$objetoXML->writeRaw('<fe:Invoice xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:clm54217="urn:un:unece:uncefact:codelist:specification:54217:2001" xmlns:clm66411="urn:un:unece:uncefact:codelist:specification:66411:2001" xmlns:clmIANAMIMEMediaType="urn:un:unece:uncefact:codelist:specification:IANAMIMEMediaType:2003" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dian.gov.co/contratos/facturaelectronica/v1 ../xsd/DIAN_UBL.xsd urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2 ../../ubl2/common/UnqualifiedDataTypeSchemaModule-2.0.xsd urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2 ../../ubl2/common/UBL-QualifiedDatatypes-2.0.xsd">');

			$objetoXML->writeRaw('<ext:UBLExtensions>');

			foreach ($listaProductos as $key => $value) {
				
				$objetoXML->text($value["descripcion"].", ");
			
			}

			

			$objetoXML->writeRaw('</ext:UBLExtensions>');

			$objetoXML->writeRaw('</fe:Invoice>');

			$objetoXML->endDocument(); // Final del documento

			return true;	
		}

	}



		/*=============================================
	impresion del dia
	=============================================*/

	public function ctrReportededia(){
		$tabla = "ventas";


$ventas = ModeloVentas::mdlRangoFechasVentasDelDia();
$ventasEfectivo = ModeloVentas::mdlRangoFechasVentasDelDiaEfectivo();
$ventasTarjeta = ModeloVentas::mdlRangoFechasVentasDelDiaTarjeta();

 $conteoEfectivo=0;
 $conteoTarjeta=0;
$impresora = "POS-90";
$conector = new WindowsPrintConnector($impresora);
	$printer = new Printer($conector);
	$printer -> feed(1);
	$printer -> text("            Cierre de ventas del dia \n");
	$printer -> text(date("               "."Y-m-d H:i:s")."\n");//Fecha de la factura
	$printer -> feed(1);
	$printer -> feed(1);

	$printer -> text("Cierre de ventas en efectivo \n");
$printer -> feed(1);

$printer -> text(" Transacion                   Total". "\n");
$printer -> feed(1);
$conteototal=0;
	foreach ($ventasEfectivo as $row => $item){
$printer -> text(" Tr-".$item["codigo"]."                      ".$item["total"]. "\n");
$conteoEfectivo = $conteoEfectivo  + $item["total"];
}
$conteototal = $conteototal + $conteoEfectivo;


	
$printer -> text("-----------------------------------------"."\n");
$printer -> text("Total vendido en efectivo   --->    ". $conteoEfectivo);
	
	$printer -> feed(1);
	$printer -> feed(1);
$printer -> text("************************************************************************************************************************************************"."\n");

	$printer -> feed(1);

	$printer -> text("Cierre de ventas en tarjeta \n");
	$printer -> feed(1);
	$printer -> text(" Transacion                   Total". "\n");
	$printer -> feed(1);
	foreach ($ventasTarjeta as $row => $item){
		$printer -> text(" Tr-".$item["codigo"]."                      ".$item["total"]. "\n");
		$conteoTarjeta = $conteoTarjeta  + $item["total"];
		}
$conteototal = $conteototal + $conteoTarjeta;

$printer -> text("-----------------------------------------"."\n");
		$printer -> text("Total vendido en Tarjeta   --->    ". $conteoTarjeta);
		
		$printer -> feed(1);
		$printer -> feed(1);
		$printer -> text("************************************************************************************************************************************************"."\n");
$printer -> text(" "."\n");
$printer -> text(" "."\n");

$printer -> text("-----------------------------------------"."\n");
		$printer -> text("Total vendido en Tranferencia   --->    "."\n" );
$printer -> text("-----------------------------------------"."\n");
$printer -> text(" "."\n");


$printer -> text("-----------------------------------------"."\n");
		$printer -> text("Total vendido en envios   --->    "."\n" );
$printer -> text("-----------------------------------------"."\n");
$printer -> text(" "."\n");
		

$printer -> text("-----------------------------------------"."\n");
		$printer -> text("Total  en Otros   --->    "."\n" );
$printer -> text("-----------------------------------------"."\n");
		

$printer -> text(" "."\n");


$printer -> text("Total vendido   --->    ". $conteototal."\n");
$printer -> text("la meta del dia es 12,500   \n" );
$printer -> feed(1);

$printer -> text("************************************************************************************************************************************************"."\n");

		$printer -> text("No olvides que para Dios no hay nada imposible. No te des por vencido, el te ayudara.");
		$printer -> feed(1);
		$printer -> feed(1);

		$printer -> feed(2);
		
		$printer->pulse();
	$printer -> cut(); //Cortamos el papel, si la impresora tiene la opción
	$printer -> pulse(); //Por medio de la impresora mandamos un pulso, es útil cuando hay cajón moneder
	$printer -> close();


	//$para = "info@ellas-beauty.com"; // Cambia esto al correo del destinatario
   // $asunto = "Informe de cierre de ventas del día";
   // $mensaje = "Se ha generado un informe de cierre de ventas del día.";

    // Puedes personalizar y ampliar este mensaje según tus necesidades
   // mail($para, $asunto, $mensaje);



	echo'<script>
	setTimeout(function(){
	  window.location = "salir";

  }, 4000);
		


						
			</script>';

	return $respuesta;
		

}



public function ctrReportedemes() {
    // Obtener las ventas del mes desde el modelo
    $ventas = ModeloVentas::rangodelmes();

    // Inicializar el contador de total de ventas
    $conteoEfectivo = 0;

    // Inicializar el contador de ventas por día
    $ventasPorDia = array();

    // Conectar a la impresora
    $impresora = "POS-90";
    $conector = new WindowsPrintConnector($impresora);
    $printer = new Printer($conector);

    // Encabezado
	$printer->feed(1);
	$printer->text(" Cierre de ventas del mes \n");
	$printer->text(date(" Y-m-d H:i:s") . "\n"); // Fecha de la factura
	$printer->text("-----------------------\n");
	
	// Inicializar el contador de total de ventas
	$conteoEfectivo = 0;
	
	// Inicializar el contador de ventas por día
	$ventasPorDia = array();
	
	// Imprimir detalles de ventas y sumar por día
	foreach ($ventas as $item) {
		$conteoEfectivo += $item["total"];
	
		// Sumar ventas por día
		$fechaVenta = date("Y-m-d", strtotime($item["fecha"])); // Ajusta según el formato de tu fecha
		if (!isset($ventasPorDia[$fechaVenta])) {
			$ventasPorDia[$fechaVenta] = 0;
		}
		$ventasPorDia[$fechaVenta] += $item["total"];
	
		// Imprimir detalles de venta
	}
	
	// Imprimir el total de ventas por día
	$printer->text("-----------------------\n");
	$printer->text(" Total Efectivo: L" . number_format($conteoEfectivo, 2) . "\n");
	$printer->text("-----------------------\n");
	$printer->text(" Total por día:\n");
	foreach ($ventasPorDia as $fecha => $totalDia) {
		$printer->text("  " . $fecha . ": L" . number_format($totalDia, 2) . "\n");
	}
	
	// Cerrar la conexión con la impresora
	$printer->text("-----------------------\n");

		$printer -> feed(2);
		
	$printer -> cut(); 
	$printer -> pulse(); //Por medio de la impresora mandamos un pulso, es útil cuando hay cajón moneder
	$printer -> close();
	

    return $respuesta;
}

public function ctrReportedemespdf() {
        $nombreArchivo = 'pdf_en_blanco.pdf';
        $this->generarPDFEnBlanco($nombreArchivo);
    }

    private function generarPDFEnBlanco($nombreArchivo) {
        $pdf = new TCPDF();
        
        // Establecer algunas propiedades del PDF
        $pdf->SetCreator('Tu Aplicación');
        $pdf->SetAuthor('Tu Nombre');
        $pdf->SetTitle('PDF en Blanco');
        $pdf->SetSubject('PDF en Blanco para imprimir');

        // Agregar una página en blanco
        $pdf->AddPage();

        // Guardar el PDF en un archivo
        $pdf->Output($nombreArchivo, 'F');
        
        // Por ejemplo, redireccionar a una página que muestre el enlace de descarga o realizar otras acciones.




}
}



/*=============================================
	Reimprimir VENTA

	
	static public function ctrReimprimirVenta1(){



		



		$impresora = "TSP100";
		$conector = new WindowsPrintConnector($impresora);
		$printer = new Printer($conector);
		
		$printer -> text("              INVERSIONES D. & M. \n");
		$printer -> text("               Ellas Beauty Shop\n");
	
		$printer -> feed(2);
		$printer -> close();




}

	=============================================*/
