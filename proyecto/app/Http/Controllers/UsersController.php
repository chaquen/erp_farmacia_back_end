<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;

use DB;

use Mail;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $us=new User();
        return response()->json($us->consultar_todos());


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $us=new User();
        $datos=json_decode($request->get("datos"));

        $rr=$us->consultar_por_campo(array(array("usuario","=",$datos->datos->documento)),"AND",array([]));

        if($rr["respuesta"]==false){
            
                $rr=$us->insertar(array(
              
                    "nombres"=>$datos->datos->nombres,
                    "apellidos"=>$datos->datos->apellidos,
                    "usuario"=>strtoupper($datos->datos->documento),
                    "documento"=>$datos->datos->documento,
                    "email"=>$datos->datos->email,
                    "password"=>$datos->datos->password,
                    "fk_id_rol"=>$datos->datos->rol, 
                    "codigo_venta"=>$datos->datos->codigo_venta,
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente,
                    ));
                
                if($rr["respuesta"]==true){
                    
                    switch ($datos->datos->rol) {
                        case 6:
                            $tipo="administrador";

                            break;

                        default:
                            $tipo="cajero";
                            break;
                    }
                    
                    if($datos->datos->sede==0){
                    //activo en todas las sedes
                        $sed=DB::table('sedes')
                           ->get();    
                        foreach ($sed as $key => $value) {
                             DB::table('detalle_cajero_sedes')
                                     ->insert([
                                         "fk_id_usuario"=>$rr["id"],
                                         "fk_id_sede"=>$value->id,
                                         "tipo"=>$tipo,
                                         "created_at"=>$datos->hora_cliente,
                                         "updated_at"=>$datos->hora_cliente,
                                         ]);
                        }
                    }else{
                        DB::table('detalle_cajero_sedes')
                                     ->insert([
                                         "fk_id_usuario"=>$rr["id"],
                                         "fk_id_sede"=>$datos->datos->sede,
                                         "tipo"=>$tipo,
                                         "created_at"=>$datos->hora_cliente,
                                         "updated_at"=>$datos->hora_cliente,
                                         ]);
                    }
                }
                return response()->json($rr);    
                
        }else{
            return response()->json(["mensaje"=>$datos->datos->email.", Correo ya existe","respuesta"=>false]);
        }



        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $us=new User();
        return response()->json($us->consultar_por_campo(array(array("documento","=",$id)),"OR",array(array("nombres","LIKE","%".$id."%"))));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $datos= json_decode($request->get("datos"));
        
        //  var_dump($datos);
        $us=new User();
        $rr=$us->editar(array(
                "nombres"=>$datos->datos->nombres,
                "apellidos"=>$datos->datos->apellidos,
                "usuario"=>$datos->datos->documento,
                "documento"=>$datos->datos->documento,
                "email"=>$datos->datos->email,
                "codigo_venta"=>$datos->datos->codigo_venta,
               
                "fk_id_rol"=>$datos->datos->rol,
                "updated_at"=>$datos->hora_cliente,  

            ),array(["id","=",$id]));
        if($datos->datos->password!=false){
            //cambiar clave
            DB::table('users')
                    ->where("id","=",$id)
                    ->update(["password"=>$datos->datos->password]);
        }
        
        if($datos->datos->nueva_sede!=false){
            //registrar nueva sede
            if($datos->datos->nueva_sede[0]!=0){
                $exi=DB::table("detalle_cajero_sedes")
                    ->where([  
                                ["fk_id_sede","=",$datos->datos->nueva_sede[0]],
                                ["fk_id_usuario","=",$id]
                            ]
                               
                             )
                    ->get();
            
              if(count($exi)==0){
                    DB::table('detalle_cajero_sedes')
                        ->insert(["fk_id_sede"=>$datos->datos->nueva_sede[0],
                                   "fk_id_usuario"=>$id,
                                    "tipo"=>$datos->datos->nueva_sede[1]]);
              }
            }else{
                //todas las sedes
                $s=DB::table("sedes")->get();
                foreach ($s as $key => $value) {
                    $exi=DB::table("detalle_cajero_sedes")
                        ->where([  
                                    ["fk_id_sede","=",$value->id],
                                    ["fk_id_usuario","=",$id]
                                ]
                                   
                                 )
                        ->get();
                
                      if(count($exi)==0){
                            DB::table('detalle_cajero_sedes')
                                ->insert(["fk_id_sede"=>$value->id,
                                           "fk_id_usuario"=>$id,
                                            "tipo"=>$datos->datos->nueva_sede[1]]);
                      }    
                }
                
            }
            
        }
        return response()->json($rr);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $us=new User();
        return response()->json($us->eliminar(array("id","=",$id)));
    }

    public function mostrar_administradores(){
        $r=DB::table("users")
            ->join("rols","users.fk_id_rol","=","rols.id")
            ->where("rols.nombre_rol","=","admin")
            ->select(
                'rols.nombre_rol',
                'users.nombres',
                'users.apellidos',
                'users.id'
                )
            ->get();
        return response()->json(["respuesta"=>true,"mensaje"=>"Administradores","datos"=>$r]);    
    }
    public function mostrar_cajeros($id_sede){
        $r=DB::table("users")
            ->join("rols","users.fk_id_rol","=","rols.id")
            ->join("detalle_cajero_sedes","detalle_cajero_sedes.fk_id_usuario","=","users.id")    
            ->join("sedes","sedes.id","=","detalle_cajero_sedes.fk_id_sede")
             ->where("sedes.id","=",$id_sede)   
            ->select(
                'rols.nombre_rol',
                'users.nombres',
                'users.apellidos',
                'users.id',
                 'users.codigo_venta',   
                'detalle_cajero_sedes.tipo'   
                )
            ->get();
        return response()->json(["respuesta"=>true,"mensaje"=>"Administradores","datos"=>$r]);    
    }
    public function login(Request $request){
        $datos=json_decode($request->get("datos"));
        $r=DB::table("users")
            ->join("detalle_cajero_sedes","users.id","=","detalle_cajero_sedes.fk_id_usuario")
            ->join('sedes','detalle_cajero_sedes.fk_id_sede',"=",'sedes.id')
            ->where([
                    ["users.usuario","=",strtoupper($datos->datos->usario)],
                    ["users.password","=",$datos->datos->password],
                    ["detalle_cajero_sedes.fk_id_sede","=",$datos->datos->sede]                    
                ])
            ->select(
                'users.nombres',
                'users.apellidos',
                'users.id as id_usuario',
                'detalle_cajero_sedes.fk_id_sede as id_sede',
                'sedes.nombre_sede',
                
                'detalle_cajero_sedes.tipo',
                'sedes.horario',
                
                'users.codigo_venta',
                'users.fk_id_rol',
                'users.usuario',
                'users.email'    
                 )
                
            ->get();
        
         if(count($r)>0){
             
             if($r[0]->tipo=="cajero"){
                 //desahabilitar para que se logue sin cerrar sesion
                 $hor= json_decode($r[0]->horario);
                 $ing_hora=explode(" ",$datos->hora_cliente)[1];
                 $respuesta=[];
                 //var_dump($datos->datos->dia);      
                 //var_dump($datos->datos->sede);  

                 
                            
                 switch($datos->datos->dia)
                 {
                     case 0:
                            //DOMINGO
                             if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[6][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     case 1:
                     //LUNES
                            /*echo "horario sede";
                            var_dump($hor[0][0]);
                            var_dump($hor[0][1]);
                            var_dump((int)explode(":",$hor[0][1])[0]);
                            echo "hora ingreso";*/
                            //var_dump((int)explode(":",$ing_hora)[0]);
                             if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[0][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     case 2:
                        //MARTES
                      
                           if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[1][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     case 3:
                     //MIERCOLES
                          if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[2][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     case 4:
                            //JUEVES
                             if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[3][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     case 5:
                     //VIERNES
                           if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[4][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     case 6:
                     //SABADO
                           if((int)explode(":",$ing_hora)[0]>=(int)explode(":",$hor[5][1])[0]){
                                return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Lo sentimos pero en este horario no esta permitido ingresar al sistema","datos"=>$r]);
                                
                            }
                         break;
                     
                 }
             }else{
                 return response()->json(["respuesta"=>true,"mensaje"=>"Bienvenido","datos"=>$r]);
             }
             
            
         }
         else{
            return response()->json(["respuesta"=>false,"mensaje"=>"Parece que no estas registrado o tus datos no coinciden","datos"=>$r]);
         }   


    }
    function buscar_user_por_id($id){
         $dat=DB::table('users')
                  ->where("id","=",$id)
                 ->get();
         $sed_rol=DB::table('detalle_cajero_sedes')
                 ->join('sedes','sedes.id','=',"detalle_cajero_sedes.fk_id_sede")
                  ->where("fk_id_usuario","=",$id)
                 ->select("detalle_cajero_sedes.id",
                         "sedes.nombre_sede",
                         "detalle_cajero_sedes.tipo")
                 ->get();
        return response()->json(["mensaje"=>"Usuario encontrado","respuesta"=>true,"datos"=>$dat,"datos_sede"=>$sed_rol]);
    }
    
    function editar_perfil(Request $request,$id){
        $datos=json_decode($request->get("datos"));

        $val=DB::table('users')
            ->where([["id","<>",$id],["codigo_venta","=",$datos->datos->codigo_venta]])
            ->get();
         
        if(count($val)==0){
            DB::table('users')
                ->where("id","=",$id)
                ->update(["nombres"=>$datos->datos->nombres,"apellidos"=>$datos->datos->apellidos,"usuario"=>$datos->datos->usuario,"email"=>$datos->datos->email,"codigo_venta"=>$datos->datos->codigo_venta]);
             if($datos->datos->clave!=false){
               DB::table('users')
                ->where("id","=",$id)
                ->update(["password"=>$datos->datos->clave]);

             }  

             return response()->json(["mensaje"=>"Pefil editado","respuesta"=>true]); 
        }else{
            return response()->json(["mensaje"=>"El codigo de venta ya esta en uso","respuesta"=>false]); 
        }
    }
    function recuperar_clave(Request $request){
        $datos=json_decode($request->get("datos"));
        $r=DB::table("users")
            ->join("detalle_cajero_sedes","users.id","=","detalle_cajero_sedes.fk_id_usuario")
            ->join('sedes','detalle_cajero_sedes.fk_id_sede',"=",'sedes.id')
            ->where([
                    ["email","=",$datos->datos->email],
                                     
                ])
            ->select('users.password',
                    DB::RAW('CONCAT(users.nombres," ",users.apellidos) as nombre'))
                
            ->get();
   
         if(count($r)>0){
             
            Mail::send("email.recuperar_pass",["nombre"=>$r[0]->nombre,"clave"=>$r[0]->password],function($msn) use($datos){
                                $msn->from('erp@asopharma.com',"ERP ASOPHARMA");
                                $msn->to($datos->datos->email);
                                
                                
                                $msn->subject("RECORDATORIO CLAVE");
                        });
            return response()->json(["respuesta"=>true,"mensaje"=>"Hemos enviado la clave a tu correo electronico","datos"=>$r]); 
            
         }
         else{
            return response()->json(["respuesta"=>false,"mensaje"=>"Parece que no estas registrado o tus datos no coinciden","datos"=>$r]);
         }   
    }
    
}
