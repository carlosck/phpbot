<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="ia.php" method="GET">
        <input type="text" autocomplete='none' name='text' id='text'>
        <button type="submit" value="send">Send</button>
    </form>
</body>
</html>
<?php

require __DIR__ . '/vendor/autoload.php';

use TextAnalysis\Tokenizers\GeneralTokenizer;
$tokenizer = new GeneralTokenizer();

define('TOKEN', 'SYiI9n$^l5y6^Eg');
define('FBTOKEN', getenv('FB_TOKEN'));
define('APPSECRET', getenv('APPSECRET'));
define('SECRETTOKEN', getenv('SECRETTOKEN'));

$text=$_GET['text'];
$items = (object)array(
    'saludo'=> [
        "tokens"=> ('saludos hola buenos dias buenas tardes noches como estas estás cómo'),
        "response" => ' Hola !!'
    ],
    'reporte'=> [
        "tokens"=> ('reporte reportar queja'),
        "response" => ' Qué quieres reportar ?'
    ],
    'status_reporte'=> [
        "tokens"=> ('hice reporte levante status estatus numero número'),
        "response" => ' Revisando tu reporte'
    ],
    'luminaria'=> [
        "tokens"=> ('luminaria faro lampara apagada enciende'),
        "response" => 'Puedes proporcionarnos el número de luminaria'
    ],
    'plazas'=> [
        "tokens"=> ('plaza'),
        "response" => ' gracias por tu reporte'
    ],
    'bacheo'=> [
        "tokens"=> ('baches pozos bache bacheo hoyos oyos'),
        "response" => ' gracias por tu reporte'
    ],
    'basura'=> [
        "tokens"=> ('basura camión de basura bolsas bolsa'),
        "response" => ' gracias por tu reporte'
    ],
    'vial'=> [
        "tokens"=> ('vial validad choque accidente'),
        "response" => ' gracias por tu reporte'
    ],
    'canino'=> [
        "tokens"=> ('perro perros canino rabia sueltos callejeros'),
        "response" => ' gracias por tu reporte'
    ],
    'info'=> [
        "tokens"=> ('información informacion servicios info'),
        "response" => ' gracias por tu reporte'
    ],
    'tramites'=> [
        "tokens"=> ('trámites tramites'),
        "response" => ' gracias por tu reporte'
    ],
    'numero de reporte'=> [
        "tokens"=> ('reporte numero número'),
        "response" => ' gracias por tu reporte'
    ],
    'direccion'=> [
        "tokens"=> ('direccion dirección calle blvd boulevard'),
        "response" => ' gracias por tu reporte'
    ],
    'agrecion'=> [
        "tokens"=> ('chinga tu madre pendejo idiota pendejos idiotas bola'),
        "response" => ' gracias por tu reporte'
    ],
    'agradecimiento'=> [
        "tokens"=> ('muchas gracias'),
        "response" => ' gracias por tu reporte'
    ],
    
);

//$command = trim(strtolower($event->entry[0]->changes[0]->value->messages[0]->text->body));        
$tokens =$tokenizer->tokenize($text);
$nb = naive_bayes();


echo '<pre>';
foreach($items as $key => $item){
    /* echo '$key'.$key.'<br>';
    echo '$tokens'.$item['tokens'].'<br>'; */
    $nb->train($key, tokenize($item['tokens']));
}

$weights= $nb->predict(tokenize($text)); 
$keys = array_keys($weights);
echo $text.'<br>';
echo 'Respuesta =><br> ';
echo $keys[0].'=>'.$weights[$keys[0]].'<br>';

if($keys[0]<0.000){
    echo '<br>-indefinido-<br>';
}
$isReport = false;
$isSalute = false;
foreach($weights as $key => $weight){
    
    if($weight>0.01){
        echo '<br> '.$items[$key]->response;
    }
}

var_dump($weights);
//var_dump($nb);
//var_dump($nb);

/* if(isset($_GET['hub_challenge'])){ 

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
 */
?>


