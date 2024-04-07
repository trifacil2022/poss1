<?php

$reporte = new ControladorVentas;
$reporte -> ctrReportededia();




use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;


date_default_timezone_set('America/Tegucigalpa');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
   
$impresora = "TSP1000";
$conector = new WindowsPrintConnector($impresora);
$printer = new Printer($conector);

date_default_timezone_set('America/Tegucigalpa');

$printer -> text("            Cierre de ventas del dia \n");
$printer -> text("           FECHA: ".date("Y-m-d H:i:s")."\n" );
$printer -> feed(1);

$printer -> text("     Ventas de tarjeta " );



				$printer -> feed(3);
				$printer -> cut();
				$printer -> pulse(); 
				$printer -> close();

        echo'<script>
        setTimeout(function(){
          window.location = "salir";

      }, 4000);
			


							
				</script>';


?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
     Espera mientras se imprimen todas las ventas del dia de hoy

     Tenga un feliz dia!!!
    
    </h1>
    <img src="https://imgs.search.brave.com/pypygr_VBgxnZH139N5kAU7DTsa07q4gb5m4Abu8aEY/rs:fit:128:128:1/g:ce/aHR0cDovL3d3dy5n/aWZkZS5jb20vZ2lm/L290cm9zL2RlY29y/YWNpb24vY2FyZ2Fu/ZG8tbG9hZGluZy9j/YXJnYW5kby1sb2Fk/aW5nLTA0MS5naWY.gif" width="”100”" height="”100”" />


  </section>

</div>








