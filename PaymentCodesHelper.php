<?php
function getResultDescription($responseCode) {
    switch ($responseCode) {
        case "0" : $result = "Transacción Correcta"; break;
        case "?" : $result = "El estado de la transacción es desconocido"; break;
        case "E" : $result = "Referifo"; break;
        case "1" : $result = "Transacción denegada"; break;
        case "2" : $result = "Transacción denegada por el banco"; break;
        case "3" : $result = "Ni hubo respuesta del banco"; break;
        case "4" : $result = "Tarjeta caducada"; break;
        case "5" : $result = "Fondos insuficientes"; break;
        case "6" : $result = "Error al comunicarse con el banco"; break;
        case "7" : $result = "El servidor de pago detectó un error"; break;
        case "8" : $result = "El tipo de transacción no es soportado"; break;
        case "9" : $result = "Transacción denegada por el banco ( No contactar el banco)"; break;
        case "A" : $result = "Transacción abortada"; break;
        case "B" : $result = "Bloqueo por riesgo de fraude"; break;
	case "C" : $result = "Transacción cancelada"; break;
        case "D" : $result = "Transacción diferida recibida y está a la espera de procesamiento"; break;
        case "E" : $result = "Transacción denegada - Consulte al emisor de la tarjeta"; break;
	case "F" : $result = "3D Secure Autenticación fallida"; break;
        case "I" : $result = "Codigo de seguridad de la tarjeta invalido"; break;
        case "L" : $result = "Transacción bloqueada (por favor intentelo de nuevo mas tarde)"; break;
        case "M" : $result = "Transacción enviada"; break;
	case "N" : $result = "El titular de la tarjeta no está inscrito en el esquema de autenticación"; break;
        case "P" : $result = "La transacción ha sido recidiba por el adaptador del pago y se esta procesando"; break;
        case "R" : $result = "Transacción no procesada - ha alcanzado el limite de reintentos permitidos"; break;
        case "S" : $result = "Sesión duplicada"; break;
        case "T" : $result = "Verificación de dirección fallida"; break;
        case "U" : $result = "Verificación de codigo de seguridad fallida"; break;
        case "V" : $result = "Verificación de dirección y codigo de seguridad fallida"; break;
        default  : $result = "No es posible determinar el error"; 
    }
    return $result;
}

?>