<!DOCTYPE html>
<html>
<head>
	<title>Pedido generado</title>
</head>
<body>
		
        <h1>Su pedido se ha generado</h1>
        <table>
        	<tr>
        		<td>ID farmacia</td>
        		<td>Codigo venta farmacia</td>
        		<td>Nombre producto</td>
        		<td>Unidades por caja / unidades por presentacion</td>
        		<td>Cantidad solicitada</td>
        		<td>Codigo proveedor</td>
        	</tr>
        	@foreach($datos_pedido->datos->productos_pedido as $k =>  $p)
        		<tr>
        			<td>
        				{{$p->id}}
        			</td>
        			<td>
        				{{$p->codigo_producto}}
        			</td>
        			<td> 
        				{{$p->nombre_producto}}
        			</td>
        			<td>
        				{{$p->unidades_por_caja}}
        			</td>
        			<td>
        				{{$p->cantidad_solicitada}}
        			</td>
        			<td>
        				{{$p->codigo_distribuidor}}
        			</td>
        		</tr>
        	@endforeach

        </table>
</body>
</html>
