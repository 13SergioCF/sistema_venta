<?php

$item = null;
$valor = null;
$productos = ControladorProductos::ctrMostrarSumaVentasDinero();

// Limitar la cantidad de productos a 10
$productos = array_slice($productos, 0, 10);

$colores = array("red","green","yellow","aqua","purple","blue","cyan","magenta","orange","gold");

$totalVentasDinero = 0;

// Calcula el total de ventas en dinero
foreach($productos as $producto){
    $totalVentasDinero += $producto["total_ventas"];
}

?>

<!--=====================================
PRODUCTOS MÁS VENDIDOS EN DINERO
======================================-->

<div class="box box-default">
    
    <div class="box-header with-border">
        <h3 class="box-title">Productos más vendidos en dinero</h3>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-md-7">
                <ul class="nav nav-pills nav-stacked">
                    <?php
                    foreach($productos as $index => $producto) {
                        // Aplica el color de fondo a cada <li>
                        echo '<li style="background-color: '.$colores[$index].'; color: black; margin-bottom: 5px;">
                                <a href="#" style="color: black;">
                                    <img src="'.$producto["imagen"].'" class="img-thumbnail" width="60px" style="margin-right:10px"> 
                                    '.$producto["descripcion"].'
                                    <span class="pull-right">   
                                    '.ceil($producto["total_ventas"]*100/$totalVentasDinero).'%</span>
                                </a>
                              </li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="col-md-5">
                <div class="chart-responsive" style="height: 150px; position: relative;">
                    <canvas id="pieChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ------------- 
    // - PIE CHART - 
    // ------------- 
    var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
    var pieChart       = new Chart(pieChartCanvas);
    var PieData        = [
        <?php
        foreach($productos as $index => $producto) {
            echo "{
                value    : ".$producto["total_ventas"].",
                color    : '".$colores[$index]."',
                highlight: '".$colores[$index]."',
                label    : '".$producto["descripcion"]."'
            },"; // Color igual que en el gráfico de pastel
        }
        ?>
    ];
    
    var pieOptions = {
        segmentShowStroke    : true,
        segmentStrokeColor   : '#fff',
        segmentStrokeWidth   : 1,
        percentageInnerCutout: 0, // Para un gráfico de pastel
        animationSteps       : 100,
        animationEasing      : 'easeOutBounce',
        animateRotate        : true,
        animateScale         : false,
        responsive           : true,
        maintainAspectRatio  : false,
        tooltipTemplate      : '<%=value %> <%=label%>'
    };
    
    pieChart.Doughnut(PieData, pieOptions);
</script>
