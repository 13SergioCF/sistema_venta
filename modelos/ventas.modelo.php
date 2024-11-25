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

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_cliente, id_vendedor, productos, impuesto, neto, total, metodo_pago) VALUES (:codigo, :id_cliente, :id_vendedor, :productos, :impuesto, :neto, :total, :metodo_pago)");

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

	public static function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
	
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
	
		} else if($fechaInicial == $fechaFinal){
	
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like '%$fechaFinal%'");
	
		} else {
	
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal");
	
			$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
			$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
	
		}
	
		$stmt->execute();
	
		return $stmt->fetchAll();
	
		$stmt = null;
	
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

/*=============================================
SUMAR EL TOTAL DE VENTAS DEL DÍA
=============================================*/

static public function mdlSumaTotalVentasDias($tabla){	

    $stmt = Conexion::conectar()->prepare(
        "SELECT SUM(neto) as total FROM $tabla WHERE DATE(fecha) = CURDATE()"
    );

    $stmt -> execute();

    return $stmt -> fetch();

    $stmt -> close();
    $stmt = null;
}




/*=============================================
	CALCULAR GANANCIAS
=============================================*/
/*=============================================
SUMAR EL TOTAL DE GANANCIAS ACUMULADAS
=============================================*/
static public function mdlSumaTotalGanancias($tablaVentas, $tablaProductos){	

    // Primero obtenemos todas las ventas
    $stmt = Conexion::conectar()->prepare("SELECT productos FROM $tablaVentas");
    $stmt->execute();
    
    // Inicializamos las ganancias totales
    $gananciasTotales = 0;
    
    // Recorremos cada venta para procesar los productos
    while ($venta = $stmt->fetch(PDO::FETCH_ASSOC)) {
        
        // Decodificamos el JSON del campo 'productos'
        $productosVendidos = json_decode($venta["productos"], true);
        
        // Recorremos cada producto vendido
        foreach ($productosVendidos as $producto) {
            
            // Obtenemos el producto de la tabla productos
            $stmtProducto = Conexion::conectar()->prepare("SELECT precio_compra, precio_venta FROM $tablaProductos WHERE id = :id");
            $stmtProducto->bindParam(":id", $producto["id"], PDO::PARAM_INT);
            $stmtProducto->execute();
            $productoInfo = $stmtProducto->fetch(PDO::FETCH_ASSOC);
            
            // Calculamos la ganancia por cada producto
            $gananciaPorProducto = ($productoInfo["precio_venta"] - $productoInfo["precio_compra"]) * $producto["cantidad"];
            
            // Sumamos la ganancia total
            $gananciasTotales += $gananciaPorProducto;
        }
    }

    return array("totalGanancias" => $gananciasTotales);
}




	
}