<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirFactura{

    public $codigo;

    public function traerImpresionFactura(){

        // TRAEMOS LA INFORMACIÓN DE LA VENTA
        $itemVenta = "codigo";
        $valorVenta = $this->codigo;

        $respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

        $fecha = substr($respuestaVenta["fecha"], 0, -8);
        $productos = json_decode($respuestaVenta["productos"], true);
        $neto = number_format($respuestaVenta["neto"], 2);
        $impuesto = number_format($respuestaVenta["impuesto"], 2);
        $total = number_format($respuestaVenta["total"], 2);

        // TRAEMOS LA INFORMACIÓN DEL CLIENTE
        $itemCliente = "id";
        $valorCliente = $respuestaVenta["id_cliente"];

        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

        // TRAEMOS LA INFORMACIÓN DEL VENDEDOR
        $itemVendedor = "id";
        $valorVendedor = $respuestaVenta["id_vendedor"];

        $respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

        // REQUERIMOS LA CLASE TCPDF
        require_once('tcpdf_include.php');

        // Creación del objeto TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuración del documento
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Tamaño de página personalizado: Ancho de ticket (80 mm), altura estimada de 200 mm
        $pdf->AddPage('P', array(80, 130)); 

        // BLOQUE 1: Datos generales de la factura
        $bloque1 = <<<EOF
        <table style="font-size:9px; text-align:center">
            <tr>
                <td style="width:160px;">
                    Fecha: $fecha
                    <br><br>
                    Sistema de venta ING-SISTEMAS
                    <br>
                    NIT: Ninguno
                    <br>
                    Dirección: Facultad FINOR Ing-Sistemas
                    <br>
                    Teléfono: 77810393
                    <br>
                    FACTURA N.$valorVenta
                    <br><br>                    
                    Cliente: $respuestaCliente[nombre]
                    <br>
                    Vendedor: $respuestaVendedor[nombre]
                    <br>
                </td>
            </tr>
        </table>
        EOF;

        $pdf->writeHTML($bloque1, false, false, false, false, '');

        // BLOQUE 2: Detalle de los productos
        foreach ($productos as $key => $item) {
            $valorUnitario = number_format($item["precio"], 2);
            $precioTotal = number_format($item["total"], 2);

            $bloque2 = <<<EOF
            <table style="font-size:9px;">
                <tr>
                    <td style="width:160px; text-align:left">$item[descripcion]</td>
                </tr>
                <tr>
                    <td style="width:160px; text-align:right">
                        bs.- $valorUnitario Und * $item[cantidad] = bs.- $precioTotal
                        <br>
                    </td>
                </tr>
            </table>
            EOF;

            $pdf->writeHTML($bloque2, false, false, false, false, '');
        }

        // BLOQUE 3: Totales
        $bloque3 = <<<EOF
        <table style="font-size:9px; text-align:right">
            <tr>
                <td style="width:80px;">NETO:</td>
                <td style="width:80px;">bs.- $neto</td>
            </tr>
            <tr>
                <td style="width:80px;">Costo de estado frio:</td>
                <td style="width:80px;">bs.- $impuesto</td>
            </tr>
            <tr>
                <td style="width:160px;">--------------------------</td>
            </tr>
            <tr>
                <td style="width:80px;">TOTAL:</td>
                <td style="width:80px;">bs.- $total</td>
            </tr>
            <tr>
                <td style="width:160px;">
                    <br><br>
                    Muchas gracias por su compra
                </td>
            </tr>
        </table>
        EOF;

        $pdf->writeHTML($bloque3, false, false, false, false, '');

        // SALIDA DEL ARCHIVO PDF
        ob_end_clean(); // Limpiar el buffer de salida antes de enviar el PDF
        $pdf->Output('factura.pdf', 'I'); // Visualizar el PDF en pantalla en vez de descargarlo
    }

}

$factura = new imprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();

?>
