<!DOCTYPE html>
<html>
<head>
	<title>Pedido generado</title>
</head>
<body>
	<h1>Reporte corte diario generado el dia, {{$fecha}}</h1>
		

			@php
				$caja_inicial=0;
				$total_efectivo=0;
				$total_salida=0;
				$total_factura=0;
				$precio_compra=0;
				$imp=0;
				$dif=0;
				$total_ganancia=0;
			@endphp
			
			@foreach($datos_corte as $key => $d)
				
				<h2>NOMBRE SEDE: {{strtoupper($d["sede"])}}</h2>


				<h3>CAJA INICIAL</h3>	
				@if(count($d["reporte"]["dinero_caja_inicial"])>0)
						@if($d["reporte"]["dinero_caja_inicial"][0]->total_entrada_inicial_caja==NULL)
							<h4>$0 .00</h4>
							@php
								$caja_inicial=0;
							@endphp
						@else
							<h4>$ {{$d["reporte"]["dinero_caja_inicial"][0]->total_entrada_inicial_caja}}</h4>	
							@php
								$caja_inicial=$d["reporte"]["dinero_caja_inicial"][0]->total_entrada_inicial_caja;
							@endphp
						@endif
					
					
				@else
					
					<h4>$0 .00</h4>
					@php
						$caja_inicial=0;
					@endphp
				@endif

				<h3>TOTAL ABONOS:</h3>
				@if(count($d["reporte"]["pago_de_clientes"])>0)
					@if($d["reporte"]["pago_de_clientes"][0]->total_abonos==NULL)
						<h4>$ 0 .00</h4>
						@php
							$total_efectivo=0;
						@endphp
					
					@else
						<h4>$ {{$d["reporte"]["pago_de_clientes"][0]->total_abonos}}</h4>
						@php
							$total_efectivo=$d["reporte"]["pago_de_clientes"][0]->total_abonos;
						@endphp
					@endif
					
					
				@else
						
					
					<h4>$0 .00</h4>
					@php
						$total_efectivo=0;
					@endphp
					

				@endif
				<h3>TOTAL SALIDAS</h3>
				@if(count($d["reporte"]["salidas_dinero_caja"])>0)
					
					@if($d["reporte"]["salidas_dinero_caja"][0]->total_salida==NULL)
						<h4>$0 .00</h4>
						@php
							$total_salida=0;
						@endphp
					@else
						<h4>$ {{$d["reporte"]["salidas_dinero_caja"][0]->total_salida}}</h4
						@php
							$total_salida=$d["reporte"]["salidas_dinero_caja"][0]->total_salida;
						@endphp
					@endif
					
					
					
				@else
					
					<h4>$0 .00</h4>
					@php
						$total_salida=0;
					@endphp
				@endif

				<h3>TOTAL FACTURACION</h3>

				@if(count($d["reporte"]["pagos_de_contado"])>0)
					@if($d["reporte"]["pagos_de_contado"][0]->total_factura!=NULL)

						<h4>$ {{$d["reporte"]["pagos_de_contado"][0]->total_factura}}</h4>
						@php
							$total_factura=$d["reporte"]["pagos_de_contado"][0]->total_factura;
						@endphp
					@else
						<h4>$0 .00</h4>
						@php
							$total_factura=0;
						@endphp
					@endif	
				 @else
				 		<h4>$0 .00</h4>
				 		@php
							$total_factura=0;
						@endphp
				 @endif	

				
				<h3>TOTAL DINERO EFECTIVO:</h3>
				@if(count($d["reporte"]["entradas_en_efectivo"])>0)	
					@if($d["reporte"]["entradas_en_efectivo"][0]->total_entradas_corte!=NULL)
						<h4>$ {{$d["reporte"]["entradas_en_efectivo"][0]->total_entradas_corte+$caja_inicial+$total_efectivo+$total_factura-$total_efectivo}}</h4>
					@else
						<h4>$0 .00</h4>	
					@endif	
				@else
					
					<h4>$0 .00</h4>
				@endif
				
				

				


				<h3>TOTAL VENTAS POR DEPARTAMENTO:</h3>

					@foreach($d["reporte"]["ventas_por_departamento"] as $vd)
						<h3>{{$vd->nombre_departamento}}</h3>
						<h4>$ {{$vd->total_venta_por_departamento}}</h4>
					@endforeach

				<h3>TOTAL DE GANANCIAS</h3>
				   @foreach($d["reporte"]["ganancias_venta_dia"] as $vd)
				   	
				    @php 
				    	switch($vd->tipo_venta){
				    		case "unidad":
				    		 	$precio_compra=$vd->precio_compra_unidad;
				    		break;
				    		case "blister":
				    			$precio_compra=$vd->precio_compra_blister;
				    		break;
				    		case "caja":
				    			$precio_compra=$vd->precio_compra;
				    		break;

				    	}
				    	$imp=(($precio_compra*(int)$vd->impuesto)/100);
				    	$dif=$vd->valor_item-$precio_compra-$imp;
				    	$total_ganancia+=($dif)*$vd->cantidad_producto;


				    @endphp 
				    
				   @endforeach
				
			@endforeach
			<h4>$ {{$total_ganancia}}</h4>
			<h4>======================================================================</h4>
			

		<h6>Reporte generado automaticamente por ERP ASOPHARMA</h6>
		<h6>Desarrollado por: <a href="mohansoft.com">MOHANSOFT © {{$anio}}</a></h6>
		
	

        
</body>
</html>
