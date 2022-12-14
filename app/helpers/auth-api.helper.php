<?php

class AuthApiHelper{

    public function getToken(){
        //Basic base64(user:pass)
        $auth = $this->getAuthHeader(); // Bearer header.payload.signature
        $auth = explode(" ", $auth);
        if($auth[0]!="Bearer" || count($auth) != 2){
            return array();
        }
        $token = explode(".", $auth[1]); //arraycon buenastardes y password
        $header = $token[0];
        $payload = $token[1];
        $signature = $token[2];

        $new_signature = hash_hmac('SHA256', "$header.$payload", ****private key****, true);
        $new_signature = base64url_encode($new_signature);
        if($signature!=$new_signature)
            return array();
        
        $payload = json_decode(base64_decode($payload));
        if(!isset($payload->exp) || $payload->exp<time())
            return array();
        
        return $payload;
    }

    function isLoggedIn(){
        $payload = $this->getToken();
        if(isset($payload->id))
            return true;
        else
            return false;
    }
    
    public function getAuthHeader(){
        $header = "";
        //si es engine next no nos agrega el reditect. Nos deja el http auth como viene
        if(isset($_SERVER['HTTP_AUTHORIZATION']))
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        //si es apache vamos a leerlo desde server 'redirect y el header q le pusimos que era http authorization'
        if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
        $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        //podria retornar null q es mas representativo
        return $header;
    }
}
