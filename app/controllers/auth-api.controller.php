<?php
require_once './app/views/api.view.php';
require_once './app/models/user.model.php';
require_once './app/helpers/auth-api.helper.php';

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

class AuthApiController{
    private $model;
    private $view;
    private $authHelper;
    private $key;

    public function __construct()
    {
        $this->key = "claveprivada"; //se entiende que la clave privada deberia ir en archivos de configuracion
        $this->view = new ApiView();
        $this->authHelper = new AuthApiHelper();
        $this->model = new UserModel();

    }

    private function getData(){
        return json_decode($this->data);
    }
    
    //a esta funcion entro desde el router
    public function getToken($params = null) {
        // Obtener "Basic base64(user:pass)
        $basic = $this->authHelper->getAuthHeader();
        if(empty($basic)){
            $this->view->response('No autorizado', 401);
            return;
        }
        $basic = explode(" ",$basic); // ["Basic" "base64(user:pass)"]
        if($basic[0]!="Basic"){
            $this->view->response('La autenticación debe ser Basic', 401);
            return;
        }

        //validar usuario:contraseña
        $userpass = base64_decode($basic[1]); // user:pass
        $userpass = explode(":", $userpass);
        $user = $userpass[0];
        $pass = $userpass[1];
        $usuario=$this->model->getUserByUsername($user);
        if($usuario && password_verify($pass, $usuario->password)){
            //  crear un token
            $header = array(
                'alg' => 'HS256',
                'typ' => 'JWT'
            );
            $payload = array(
                'id' => $usuario->id,
                'name' => $usuario->user,
                'exp' => time()+3600
            );
            //en json_encode lo paso de json a string y despues a ese string lo codifico en base64
            $header = base64url_encode(json_encode($header));
            $payload = base64url_encode(json_encode($payload));
            $signature = hash_hmac('SHA256', "$header.$payload", $this->key, true);
            $signature = base64url_encode($signature);
            $token = "$header.$payload.$signature";
             $this->view->response($token);
        }else{
            $this->view->response('No autorizado', 401);
        }
    }
}