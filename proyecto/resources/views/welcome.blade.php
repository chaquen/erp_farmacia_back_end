<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
    </head>
    <script type="text/javascript">
        var map=new Map();
        var miObj={
            clave1:['1','2',3],
            clave2:{
                sub_clave1:[1,2,3],

            },
            clave3:"**"
        };
        var miObj2={
            
            clave:"++"
        };
        map.set("objeto",miObj);
        console.log("¿Map tiene la clave <<objeto>> ?"+map.has("objeto"));
        console.log(map.get("objeto"));
        map.set(miObj2,"la clave es un objeto");
        console.log("¿Map tiene la clave <<objeto>> ?"+map.has(miObj2));
        console.log(map.get(miObj2));
                

    </script>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Laravel 5</div>
            </div>
        </div>
    </body>
    <script type="text/javascript">
        var isCtrl = false;
        document.onkeyup=function(e){
        if(e.which == 17) isCtrl=false;
        }
        document.onkeydown=function(e){
        if(e.which == 17) isCtrl=true;
            //if(e.which == 83 && isCtrl == true) {
            // acción para CTRL+S y evitar que ejecute la acción propia del navegador
            //console.log("acción para CTRL+S y evitar que ejecute la acción propia del navegador");
            //return false;
            //}
            if(isCtrl && e.which != 17){
                switch(e.which){
                    case 49:
                      //CRTL+1
                      alert("acción para CTRL+1 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                    case 50:
                      //CRTL+2
                      alert("acción para CTRL+2 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                     case 51:
                      //CRTL+3
                      alert("acción para CTRL+3 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                     case 52:
                      //CRTL+4
                      alert("acción para CTRL+4 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                    case 53:
                      //CRTL+5
                      alert("acción para CTRL+5 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                    case 54:
                      //CRTL+6
                      alert("acción para CTRL+6 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                    case 55:
                      //CRTL+7
                      alert("acción para CTRL+7 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                    case 56:
                      //CRTL+8
                      alert("acción para CTRL+8 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;
                    case 57:
                      //CRTL+9
                      alert("acción para CTRL+9 y evitar que ejecute la acción propia del navegador");
                      return false;
                        break;           
                    case 83:
                    //S
                    console.log("acción para CTRL+S y evitar que ejecute la acción propia del navegador");
                    return false;            
                    break;
                    case 82:
                    //R
                    console.log("acción para CTRL+R y evitar que ejecute la acción propia del navegador");
                    return false;
                    break;
                    default:
                        console.log("acción para CTRL+"+e.which+" y evitar que ejecute la acción propia del navegador");
                        return false;
                    break;
                }    
            }
            
        }
    </script>
</html>
