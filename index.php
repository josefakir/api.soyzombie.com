<?php
	if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true ");
        header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
        header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
	require "vendor/autoload.php";
	include "bootstrap.php";

	use Mainclass\Models\Usuario;
	use Mainclass\Middleware\Logging as Logging;
	use Mainclass\Middleware\Authentication as Authentication;

	$app = new \Slim\App();
	$app->add(new Logging());
	$app->get("/",function($request, $response, $args){
		return $response->write("hola");
		$usuario = new Usuario();
		$usuarios = $usuario->all();
		return $response->withStatus(200)->withJson($usuarios);
	});

	$app->post("/registrar",function($request, $response, $args){

		$nombre = $request->getHeader('nombre')[0];
		$correo = $request->getHeader('correo')[0];
		$password = md5($request->getHeader('password')[0]);
		$apikey = md5($password);

		$usuario = new Usuario();
		$usuario->nombre = $nombre;
		$usuario->correo = $correo;
		$usuario->password = $password;
		$usuario->apikey = $apikey;
		$usuario->activo = 1;
		try {
			$usuario->save();
			$payload = array(
				'status' => 'success',
				'id' => $usuario->id
			);
			return $response->withStatus(200)->withJson($payload);
		} catch (Exception $e) {
			$payload = array(
				'status' => 'error',
				'message' => $e->getMessage()
			);
			return $response->withStatus(400)->withJson($payload);
		}
	});

	$app->post("/login",function($request, $response, $args){
		$email = $request->getHeader('correo')[0];
	   	$pass = md5($request->getHeader('password')[0]);
		$user = new Usuario();
		$users = $user::where('correo', $email)->where('password', $pass)->where('activo', 1)->get();
		if($users->count()>0){
			$payload = [];
			foreach($users as $u){
				$payload[] = ['auth' => true, 'apikey' => $u->apikey, 'user_id' => $u->id, 'nombre' => $u->nombre, 'correo' => $u->correo];
			}
			return $response->withStatus(200)->withJson($payload);
		}else{
			$payload = array(
				'status' => 'error',
				'message' => 'Usuario o contraseña incorrectas'
			);
			return $response->withStatus(401)->withJson($payload);
		}
	});

	$app->run();