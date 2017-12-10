<!DOCTYPE html>
<html>
<head>
	<title>Alerta generada</title>
</head>
<body>
	
        <h1>¡Mensaje de alerta por existencias bajas para el producto!</h1>
        <h2>Codigo:{{$datos[0]->codigo_producto}}</h2>
        <h2>Nombre producto:{{$datos[0]->nombre_producto}}</h2>
        <h2>Existencias producto:{{$datos[0]->cantidad_existencias_unidades}}</h2>
        <h2>Sede:{{$sede[0]->nombre_sede}}</h2>
        <h6>Correo enviado por ERP ASOPHARMA</h6>
	<h6>Desarrollado por: <a href="mohansoft.com">MOHANSOFT © </a></h6>
            
        
</body>
</html>
