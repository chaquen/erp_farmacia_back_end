<!DOCTYPE html>
<html>
<head>
	<title>Pedido diario</title>
</head>
<body>
	
	<h4>Pedido generado automaticamente para la sede {{$sede}}, {{$fecha}} </h4>
	<table>

	<tr>
		<td>ID FARMACIA</td>
		<td>COD. FARMACIA</td>
		<td>CODIGO PRODUCTO VENTA</td>
		<td>CODIGO DISTRIBUIDOR</td>
		<td>NOMBRE PRODUCTO</td>
		<td>PRECIO COMPRA</td>
		<td>PRECIO VENTA</td>
		<td>PRECO VENTA SEDE</td>
		<td>TIPO PRESENTACION</td>
		<td>EXISTENCIAS UNIDADES</td>
		<td> MINIMO INVETARIO</td>
	</tr>
	@foreach($datos_pedido["reporte"]["datos"] as $d)
	<tr>
	 	<td>{{$d["id"]}}</td>
	 	<td>{{$d["codigo_sede"]}}</td>
	 	<td>{{$d["codigo_producto"]}}</td>
	 	<td>{{$d["codigo_distribuidor"]}}</td>
	 	<td>{{$d["nombre_producto"]}}</td>
	 	<td>{{$d["precio_compra"]}}</td>
	 	<td>{{$d["precio_venta"]}}</td>
	 	<td>{{$d["precio_mayoreo"]}}</td>
	 	<td>{{$d["tipo_presentacion"]}}</td>	 	
	 	<td>{{$d["cantidad_existencias_unidades"]}}</td>
	 	<td>{{$d["minimo_inventario"]}}</td>
	 </tr>
	@endforeach
	</table>

	<h4>Descargar archivos</h4>
	
	<a target="_blank" href="{{$ruta_xls}}">ARCHIVO EXCEL (XLS)</a>
	<h6>Reporte generado automaticamente por ERP ASOPHARMA</h6>
	<h6>Desarrollado por: <a href="mohansoft.com">MOHANSOFT © {{$anio}}</a></h6>
	
</body>
</html>