<!DOCTYPE html>
<html>
<head>
	<title>Prueba</title>
</head>
<body>
	<!-- <form>
		<li>
			<input id="txt_prod" type="text" placeholder="Nombre o codigo producto" >
			
		</li>
		<li >
			<ul id="lista_productos"></ul>
		</li>	
	</form> -->
	<!-- <ul id="lista_reportes">
			<li><input id="btnGenerarReporteInventario" type="button" value="reporte inventario todas las sedes"></li>
			<li><input id="btnGenerarReporteInventarioSede" type="button" value="reporte inventario por sedes"></li>
			<li><input id="btnGenerarReporteBajoInventario" type="button" value="reporte bajo inventario 
			"></li>
			<li><input id="btnGenerarReporteBajoInventarioSede" type="button" value="reporte bajo inventario por sede"></li>
			<li>
			<select id="selTipoMovimiento">
				<option value="TODOS">TODOS</option>
				<option value="SALIDA">SALIDAS</option>
				<option value="ENTRADA">ENTRADAS</option>
				<option value="AJUSTES">AJUSTES</option>
				<option value="DEVOLUCION">DEVOLUCION</option>
			</select>
			<input id="btnGenerarReporteMovimientosInventario" type="button" value="reporte movimientos inventario "></li>
			<li><input id="btnGenerarReporteMovimientosInventarioSede" type="button" value="reporte movimientos inventario sede"></li>
			<li><input id="btnGenerarReporteSaldosCreditos" type="button" value="reporte saldos"></li>
			<li><input id="btnGenerarReporteSaldosCreditosSede" type="button" value="reporte saldos por sede"></li>
			<li>
				<select id="selPeriodo">
					<option value="hoy">Hoy</option>
					<option value="ayer">Ayer</option>
					
					<option value="estemes">Este mes</option>
					<option value="periodo">Un periodo en particular</option>
				</select>
				<div id="divFechas" style="display:none">
					<input type="date" id="inicio">
					<input type="date" id="fin">
				</div>
				<input id="btnGenerarReporteVentasPorPeriodo" type="button" value="reporte ventas por periodo"></li>
			<li><input id="btnGenerarReporteVentasPorPeriodoSede" type="button" value="reporte ventas por periodo sede"></li>	
			<li><input id="btnReporteCorteCajaDiario" type="button" value="corte diario"></li>
	</ul> -->

	<!-- <ul id="lista_exportar">
		<li>
			<a id="btnExportarProductos" target="_blank" href="http://localhost/erp_farmacia/exportar">Exportar</a>
		</li>

	</ul> -->

	<!-- <ul id="lista_importar">
		<li>
			<input type="file" id="flMiImportacion">
			<input id="btnImportar" type="button" value="importar">
		</li>	
	</ul> -->
	<li>
		<input id="miFactura" type="button" value="ENVIAR">
	</li>

	@include('scripts.token')
	@include('scripts.basicos')
	<!--@include('scripts.core_importar')-->
	<!-- @include('scripts.core_exportar') -->
	<!-- @include('scripts.core_reportes') -->
	@include('scripts.core_factura')	
</body>
</html>