<!DOCTYPE html>
<html>
<head>
	<title>Alerta generada</title>
</head>
<body>
	
        <h1>¡Mensaje de alerta por existencias bajas para el producto!</h1>
        <h2>Codigo:{{$datos->codigo_producto}}</h2>
        <h2>Nombre producto:{{$datos->nombre_producto}}</h2>
        <h2>Existencias producto:{{$datos->cantidad_existencias_unidades}}</h2>
        <h2>Sede:{{$sede->nombre_sede}}</h2>
        <h6>Correo enviado por ERP ASOPHARMA</h6>
	<h6>Desarrollado por: <a href="mohansoft.com">MOHANSOFT © </a></h6>
            
        
</body>
</html>
