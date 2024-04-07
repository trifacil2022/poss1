<?php

// Definir una variable global
$variable_global = "Hola, soy una variable global.";

// Función que utiliza la variable global
function mostrar_variable_global() {
    // Acceder a la variable global dentro de la función
    global $variable_global;
    echo $variable_global;
}

// Llamar a la función
mostrar_variable_global();
?>
