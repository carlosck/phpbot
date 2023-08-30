<?php

require 'vendor/autoload.php';



define('TOKEN', 'SYiI9n$^l5y6^Eg');
define('FBTOKEN', getenv('FB_TOKEN'));
define('APPSECRET', getenv('APPSECRET'));
define('SECRETTOKEN', getenv('SECRETTOKEN'));

if(isset($_GET['hub_challenge'])){
    $palabraReto = $_GET['hub_challenge'];
    //TOQUEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
    $tokenVerificacion = $_GET['hub_verify_token'];
    //SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
    if (SECRETTOKEN === $tokenVerificacion) {
        echo $palabraReto;
        exit;
    }
}

function create_and_lock($file) {
    if (!$fd = fopen($file, 'xb')) {
        return false;
    }
    if (!flock($fd, LOCK_EX|LOCK_NB)) {  // may fail for other reasons, LOCK_NB will prevent blocking
        fclose($fd);
        unlink($file);  // clean up
        return false;
    }
    return $fd;
}

http_response_code(200);

//CONVERTIMOS EL JSON EN ARRAY DE PHP

$data = file_get_contents("php://input");
$event = json_decode($data);


if(isset($event)){
    //Here, you now have event and can process them how you like e.g Add to the database or generate a response
    if(!isset($event->entry[0]->changes[0]->value->messages)){
        http_response_code(200);
        exit();
    }
    $message_id = $event->entry[0]->changes[0]->value->messages[0]->id;
    $filename = './messages/'.$message_id.'.txt';
    $user_id = $event->entry[0]->id;
    $name  = $event->entry[0]->changes[0]->value->contacts[0]->profile->name;
    $phone = $event->entry[0]->changes[0]->value->contacts[0]->wa_id;
    $type  = $event->entry[0]->changes[0]->value->messages[0]->type;
    $file  = 'log.txt';  
    $data  =json_encode($event)."\n";
    
    

    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
    //file_put_contents($message_id, $event);
    if (!$message_id || file_exists($filename)){
        http_response_code(200);
        exit();
    }
    else{
        if ($lock = create_and_lock($filename)) {
            // do stuff
            switch($type){
                case 'text':
                    $command = trim(strtolower($event->entry[0]->changes[0]->value->messages[0]->text->body));
                    $tokenizer = new \TextAnalysis\Tokenizers\GeneralTokenizer();                    
                    $tokens = $tokenizer->tokenize($command);
                    
                    file_put_contents("ia.txt", $tokens);
                break;                
            }
            
            
            flock($lock, LOCK_UN);  // unlock
            fclose($lock);  // close
        }
    }
}


