<?php

namespace Mainclass\Middleware;

use Mainclass\Models\Usuario;

Class Authentication{
	public function __invoke($request, $response, $next){
    $apikey = $request->getHeader('apikey')[0];
    $user = new Usuario();
    $user = Usuario::where('apikey', '=', $apikey)->take(1)->get();
    $this->details = $user[0];
    $auth =  ($user[0]->exists) ? true : false;
    if (!$auth) {
      $error = array(
        'status' => 401,
        'message' => 'Unauthorized'
      );
      return $response->withStatus(401)->withJson($error);
    }
    $response = $next($request, $response);
    
    return $response;
	}
}