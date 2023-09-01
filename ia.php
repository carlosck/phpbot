<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="ia.php" method="GET">
        <div style="width: 50%;position: relative;margin: 0 auto;">
            <input type="text" autocomplete='none' name='text' id='text' style="width: 100%;display: block;clear: both;">
            <button type="submit" value="send">Send</button>
        <div>
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

function weigh_up($item){
    return $item > 0.122;
}

$prepociciones = array(' a ',' ante ',' bajo ',' con ',' contra ',' de ',' desde ',' durante ',' en ',' entre ',' hacia ',' hasta ',' mediante ',' para ',' por ',' segun ',' sin ',' so ',' sobre ',' tras ',' versus ',' via ');
$words = array(' me ',' gustaria', ' gustaría ',' quiero ',' para ', ' una ',' que ',' la ',' el '. ' los ', ' un ');
$text=' '.$_GET['text'];

echo '<br>'.$text;
$text = str_replace($prepociciones,' ',$text);
$text = str_replace($words,' ',$text);
echo '<br>'.$text;
$items = (object)array(
    'saludo'=> (object)[
        "tokens"=> ('saludos hola buenos dias buenas tardes noches como estas estás cómo'),
        "response_single" => '! Soy Tu Asistente Virtual de El Municipio de Saltillo, será un placer atenderte si tienes una emergencia 🚓 🚑 marca inmediatamente al 911'.
        '<br>Elige la opción de tu preferencia, Reportes, Consulta de Reporte, Info de servicios, Trámites '.
        '<br>Puedes escribir "Salir" en cualquier momento...',
        'response_multiple' => ' Hola !',        
        'type' => 'inclusive'
    ],
    'reporte'=> (object)[
        "tokens"=> ('reporte reportar reportes levantar dar alta'),
        "response_single" => ' Que asunto te gustaría reportar, "Luminarias", "Limpieza de plazas", "Bacheo", "Recoleción de basura", "Reporte vial", "Control Canino", "Otros"',
        'response_multiple' => 'escuchando tu reporte, ',
        'type' => 'inclusive_silent'
        
    ],
    'status_reporte'=> (object)[
        "tokens"=> ('hice reporte levante status estatus numero número'),
        "response_single" => ' Comparteme tu Folio de reporte, (\"Ejemplo : 301234\").con gusto te daremos el estado en que se encuentra',
        'response_multiple' => 'Revisando tu reporte con folio ',
        'type' => 'exclusive'
        
    ],
    'luminaria'=> (object)[
        "tokens"=> ('luminaria faro lampara apagada enciende fundida luminarias'),
        "response_single" => 'Ingresar el número de luminaria que comienza con SAL y se encuentra en una etiqueta a mediación de poste ejemplo SAL23456, si no lo tienes a la mano solo escribe \"No\"',
        'response_multiple' => 'Puedes proporcionarnos el número de luminaria ',
        'type' => 'inclusive'
        
    ],
    /* 'numero_luminaria'=> (object)[
        "tokens"=> ('luminaria numero número luminaria lampara'),
        "response_single" => '¿puedes explicarme el problema? (escribe de que se trata el reporte)',
        'response_multiple' => '¿puedes explicarme el problema? (escribe de que se trata el reporte) ',
        'type' => 'inclusive'
        
    ], */
    'plazas'=> (object)[
        "tokens"=> ('plaza Limpieza de plazas'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'bacheo'=> (object)[
        "tokens"=> ('baches pozos bache bacheo hoyos oyos'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'basura'=> (object)[
        "tokens"=> ('basura camión de basura bolsas bolsa recolección recoleccion '),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'vial'=> (object)[
        "tokens"=> ('vial validad choque accidente'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'canino'=> (object)[
        "tokens"=> ('perro perros canino rabia sueltos callejeros control'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'info'=> (object)[
        "tokens"=> ('información informacion servicios info'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'tramites'=> (object)[
        "tokens"=> ('trámites tramites'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'numero de reporte'=> (object)[
        "tokens"=> ('reporte numero número'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'direccion'=> (object)[
        "tokens"=> ('direccion dirección calle blvd boulevard'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'agrecion'=> (object)[
        "tokens"=> ('chinga tu madre pendejo idiota pendejos idiotas bola'),
        "response_single" => ' soy un robot con sentimientos...',
        'response_multiple' => '',
        'type' => 'exclusive'
        
    ],
    'agradecimiento'=> (object)[
        "tokens"=> ('muchas gracias'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'servicios'=> (object)[
        "tokens"=> ('servicio servicios'),
        "response_single" => ' ¿ De qué Servicio requieres Información? ',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'recoleccion'=> (object)[
        "tokens"=> ('servicio servicios'),
        "response_single" => ' 😁  ¡Gracias por contactarnos!  Para brindarte la información necesaria. ¿Nos puedes proporcionar de que colonia desea saber el horario de Recolección? ',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'facturacion'=> (object)[
        "tokens"=> ('Facturación facturacion'),
        "response_single" => ' Te Proporciono el siguiente enlace para Facturas Con Recibo \n✅ Impuesto Predial\n✅ ISAI ( Pago en linea)\n✅ Ingresos Varios\n✅ Sm@rt pago en línea\n ✅ Saneamiento-Aguas de Saltillo \n ✅ Caja 5 \n http://cfdi.saltillo.gob.mx:8888/CFDI/facturaConRecibo.php',
        'response_multiple' => '',
        'type' => 'exclusive'
        
    ],
    'queja_ciudadana'=> (object)[
        "tokens"=> ('queja ciudadana Queja ciudadana'),
        "response_single" => ' ¿ De qué Servicio requieres Información? ',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'negativa'=> (object)[
        "tokens"=> ('no puedo imposible'),
        "response_single" => ' puedes  ',
        'response_multiple' => '',
        'type' => 'helper'
        
    ],
    
);

//$command = trim(strtolower($event->entry[0]->changes[0]->value->messages[0]->text->body));        
$tokens =$tokenizer->tokenize($text);
$nb = naive_bayes();


echo '<pre>';
foreach($items as $key => $item){
    /* echo '$key'.$key.'<br>';
    echo '$tokens'.$item['tokens'].'<br>'; */
    $nb->train($key, tokenize($item->tokens));
}

$weights= $nb->predict(tokenize($text)); 

$keys = array_keys($weights);
/* echo $text.'<br>';
echo 'Respuesta =><br> ';
echo $keys[0].'=>'.$weights[$keys[0]].'<br>'; */

if($keys[0]<0.000){
    echo '<br>-indefinido-<br>';
}

var_dump($weights);

$isReport = false;
$isSalute = false;
$filteredItems = array_filter($weights,"weigh_up");
var_dump($filteredItems);

$alpha = false;
$response = [];
foreach($filteredItems as $key => $weight){
    $item = $items->{$key};
    if(!$alpha && $weight>0.5){
        $alpha=true;
    }
    //var_dump($weight);
    //var_dump($key);
    switch($item->type)
    {
        case 'inclusive': 
            if(count($filteredItems)>1)
            {
                echo '<br> '.$items->{$key}->response_multiple;
            }
            else{
                echo '<br> '.$items->{$key}->response_single;
            }
            
        break;
        case 'inclusive_silent':
            if(count($filteredItems)===1){
                echo '<br> '.$items->{$key}->response_single;
            }
            break;
        case 'exclusive':
            echo '<br> '.$items->{$key}->response_single;
            die();
        break;
        case 'helper':
            if(count($filteredItems)===1){
                echo '<br> '.$items->{$key}->response_single;
            }
            break;

    }
    
}




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


