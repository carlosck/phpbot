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
$replace_words = array(' me ',' gustaria', ' gustaría ',' quiero ',' para ', ' una ',' que ',' la ',' el '. ' los ', ' un ', ' puedes ', ' dar ',' proporcionar ', ' hay ', ' darme ', ' favor ');
$text=' '.$_GET['text'];

echo '<br>Texto    :'.$text;
$text = str_replace($prepociciones,' ',$text);
$text = str_replace($replace_words,' ',$text);
echo '<br>Filtrado :'.$text;

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
        "tokens"=> ('reporte reportar reportes levantar dar alta reportando'),
        "response_single" => ' Que asunto te gustaría reportar, "Luminarias", "Limpieza de plazas", "Bacheo", "Recoleción de basura", "Reporte vial", "Control Canino", "Otros"',
        'response_multiple' => 'escuchando tu reporte, ',
        'interactable_with' => [
            'luminaria' => ' ',
            'plaza'  => ' ',
            'basura'  => ' ',
            'bacheo' => ' ',
            'canino' => ' ',            
        ],
        'type' => 'inclusive_silent'
        
    ],
    'status_reporte'=> (object)[
        "tokens"=> ('hice reporte levante status estatus numero número'),
        "response_single" => ' Comparteme tu Folio de reporte, (\"Ejemplo : 301234\").con gusto te daremos el estado en que se encuentra',
        'response_multiple' => 'Revisando tu reporte con folio ',
        'interactable_with' => [
            'number' => ' Revisando tu reporte con número ',            
        ],
        'type' => 'inclusive'
        
    ],
    'luminaria'=> (object)[
        "tokens"=> ('luminaria faro lampara apagada enciende fundida luminarias'),
        "response_single" => 'Ingresa el número de luminaria que comienza con SAL y se encuentra en una etiqueta a mediación de poste ejemplo SAL23456, si no lo tienes a la mano solo escribe \"No\"',
        'response_multiple' => 'Puedes proporcionarnos el número de luminaria ',
        'interactable_with' => [
            'reporte'  => '<h3>atendiendo tu reporte de luminaria:</h3>',
            'direccion' => ' <h3>revisando la dirección, puedes explicarnos cúal es el problema con una foto video o descripción ?</h3>',
            
        ],
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
        "response_single" => ' puedes explicarnos cúal es el problema con la plaza?',
        'response_multiple' => '',
        'interactable_with' => [
            'reporte'  => '<h3>atendiendo tu reporte de plaza: </h3>',
            'direccion' => ' <h3>revisando la dirección, puedes explicarnos cúal es el problema con una foto video o descripción ?</h3>',
            
        ],
        'type' => 'inclusive'
        
    ],
    'bacheo'=> (object)[
        "tokens"=> ('baches pozos bache bacheo hoyos oyos pozo'),
        "response_single" => ' puedes explicarnos cúal es el problema con el bacheo?',
        'response_multiple' => '',
        'interactable_with' => [
            'reporte'  => '<h3>atendiendo tu reporte de bacheo:</h3>',
            'direccion' => ' <h3>revisando la dirección, puedes explicarnos cúal es el problema con una foto video o descripción ?</h3>',            
        ],
        'type' => 'inclusive'
        
    ],
    'basura'=> (object)[
        "tokens"=> ('basura camión de basura bolsas bolsa recolección recoleccion '),
        "response_single" => '  puedes explicarnos cúal es el problema con la basura?',
        'response_multiple' => '',
        'interactable_with' => [
            'reporte'  => '<h3>atendiendo tu reporte de basura:</h3>',
            'direccion' => ' <h3>revisando la dirección, puedes explicarnos cúal es el problema con una foto video o descripción ?</h3>',            
        ],
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
        "response_single" => ' gracias por tu reporte de canino,puedes explicarnos cúal es el problema ? ',
        'response_multiple' => '',
        'interactable_with' => [
            'reporte'  => '<h3>atendiendo tu reporte de canino:</h3>',
            'direccion' => ' <h3>revisando la dirección, puedes explicarnos cúal es el problema con una foto video o descripción ?</h3>',            
        ],
        'type' => 'inclusive'
        
    ],
    'info'=> (object)[
        "tokens"=> ('información informacion servicios info'),
        "response_single" => ' sobre qué tema te gustaría informarte ?  ',
        'response_multiple' => 'claro , aquí te damos información sobre: ',
        'type' => 'inclusive'
        
    ],
    'tramites'=> (object)[
        "tokens"=> ('trámites tramites'),
        "response_single" => ' Que trámite te interesa, cambio us, Juridico, Unidad de Geomática, Enlace Catastral, PDDU, Planos, Informes, Correciones',
        'response_multiple' => ' Que trámite te interesa, cambio us, Juridico, Unidad de Geomática, Enlace Catastral, PDDU, Planos, Informes, Correciones',
        'type' => 'inclusive'
        
    ],
    /*
    'numero de reporte'=> (object)[
        "tokens"=> ('reporte numero número'),
        "response_single" => ' gracias por tu reporte',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],*/
    'direccion'=> (object)[
        "tokens"=> ('direccion dirección calle blvd boulevard periferico periférico'),
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
        "tokens"=> ('muchas gracias agradezco'),
        "response_single" => 'estamos para servirte',
        'response_multiple' => ' ',
        'type' => 'inclusive'
        
    ],
    'servicios'=> (object)[
        "tokens"=> ('servicio'),
        "response_single" => ' ¿ De qué Servicio requieres Información? ',
        'response_multiple' => 'servicio ',
        'type' => 'inclusive'
        
    ],
    'recoleccion'=> (object)[
        "tokens"=> ('horario recolección recoleccion camion camión'),
        "response_single" => ' 😁  ¡Gracias por contactarnos!  Para brindarte la información necesaria. ¿Nos puedes proporcionar de que colonia desea saber el horario de Recolección? ',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'facturacion'=> (object)[
        "tokens"=> ('facturación facturacion'),
        "response_single" => ' Te Proporciono el siguiente enlace para Facturas Con Recibo <br>✅ Impuesto Predial<br>✅ ISAI ( Pago en linea)<br>✅ Ingresos Varios<br>✅ Sm@rt pago en línea<br> ✅ Saneamiento-Aguas de Saltillo <br> ✅ Caja 5 <br> http://cfdi.saltillo.gob.mx:8888/CFDI/facturaConRecibo.php',
        'response_multiple' => '',
        'type' => 'exclusive'
        
    ],
    'queja_ciudadana'=> (object)[
        "tokens"=> ('queja ciudadana'),
        "response_single" => ' ¿ De qué Servicio requieres Información? ',
        'response_multiple' => '',
        'type' => 'inclusive'
        
    ],
    'cambio_us'=> (object)[
        "tokens"=> ('cambio us'),
        "response_single" => ' Una vez que Usted haya elaborado el Estudio Técnico Justificativo de acuerdo a la guía de elaboración que se le entrega en la Dirección de Desarrollo Urbano, será presentado ante el Consejo Municipal de Desarrollo Urbano para obtener su Visto Bueno; posteriormente se turna al R. Ayuntamiento de Saltillo, quién lo analiza a través de la Comisión de Planeación, Urbanismo, Obras Públicas y Centro Histórico y se expone para su autorización ante el Cabildo en Pleno.  ',
        'response_multiple' => 'Una vez que Usted haya elaborado el Estudio Técnico Justificativo de acuerdo a la guía de elaboración que se le entrega en la Dirección de Desarrollo Urbano, será presentado ante el Consejo Municipal de Desarrollo Urbano para obtener su Visto Bueno; posteriormente se turna al R. Ayuntamiento de Saltillo, quién lo analiza a través de la Comisión de Planeación, Urbanismo, Obras Públicas y Centro Histórico y se expone para su autorización ante el Cabildo en Pleno.',
        'type' => 'inclusive'
        
    ],
    'juridico'=> (object)[
        "tokens"=> ('jurídico juridico'),
        "response_single" => ' si me notificarón ,Deberá acudir al área de Inspección adscrita a la Subdirección Jurídica Con escritura pública debidamente inscrita ante el Registro Publico ',
        'response_multiple' => 'Jurídico si me notificarón ,Deberá acudir al área de Inspección adscrita a la Subdirección Jurídica Con escritura pública debidamente inscrita ante el Registro Publico ',
        'type' => 'inclusive'
        
    ],
    'negativa'=> (object)[
        "tokens"=> ('no puedo imposible'),
        "response_single" => ' probemos otro método  ',
        'response_multiple' => '',
        'type' => 'helper'
        
    ],

    
);

//$command = trim(strtolower($event->entry[0]->changes[0]->value->messages[0]->text->body));        
$tokens =$tokenizer->tokenize($text);
$nb = naive_bayes();


echo '<pre>';
$total = 0;
//train for every option 
foreach($items as $key => $item){
    /* echo '$key'.$key.'<br>';
    echo '$tokens'.$item['tokens'].'<br>'; */
    $nb->train($key, tokenize($item->tokens));
}
//split the text
$words= tokenize($text);

$weights= $nb->predict($words); 



$keys = array_keys($weights);
/* echo $text.'<br>';
echo 'Respuesta =><br> ';
echo $keys[0].'=>'.$weights[$keys[0]].'<br>'; */

if($keys[0]<0.000){
    echo '<br>-indefinido-<br>';
}



$isReport = false;
$isSalute = false;
//$filteredItems = array_filter($weights,"weigh_up");
$filteredItems = [];
$total=array_sum($weights);
// echo '<br> Total = '.$total;



$qty_items=count($weights);
$media=$total / $qty_items;
// echo '<br> media = '.$media;

$i=0;
$address = '';
$number = '';

foreach($weights as $key => $weight)
{
    //echo '<br>'.$weight.'='.(($weight*100)/$total).'%';
    if($i>= count($words)){
        break;
    }

    if($weight > $media){
        $i++;
        $filteredItems[$key]=$weight;
    }
        
}

//add data types to filtered items
$data = [];

foreach($filteredItems as $key => $weight)
{
    if($key=='direccion'){
        $address = get_address($filteredItems, $text, $items);
        echo '<br> <h4>Dirección '.$address.'</h4>';
        $data['address']= $address;
    }
    
    if($key=='status_reporte'){
        $number = get_number($words);
        echo '<br> <h4>Número '.$number.'</h4>';
        $data['number']= $number;
    }
}
$filteredItems = array_merge($filteredItems, $data);

$response = [];

$i=0;
foreach($filteredItems as $key => $weight){
    $i++;
    if($key==='number' || $key==='address') break;
    $item = $items->{$key};
    
    // at least same words than commands
    if($i> count($words)){
        break;
    }
    
    //var_dump($weight);
    //var_dump($key);
    switch($item->type)
    {
        case 'inclusive': 
            if(count($filteredItems)>1)
            {
                //if its a single word
                if(count($words)===1)
                {
                    echo '<br> <h3>'.$item->response_single.'</h3>';    
                }
                else{
                    //using interact with
                    $is_valid_interactive = false;
                    if(isset($item->interactable_with))
                    {
                        foreach($item->interactable_with as $key_interact => $interact){
                            if(isset($filteredItems[$key_interact]))
                            {
                                echo '<br><h3>'.$interact.'</h3>';
                                $is_valid_interactive= true;
                            }
                        }
                    }
                    if(!$is_valid_interactive)
                    {
                        echo '<h3>'.$item->response_multiple.'</h3>';
                    }
                    
                }
                
            }
            else{
                echo '<br> <h2>'.$item->response_single.'</h2>';
            }
            
        break;
        case 'inclusive_silent':
            if(count($filteredItems)===1){
                echo '<br> <h2>'.$item->response_single.'</h2>';
            }
            break;
        case 'exclusive':
            echo '<br> <h2>'.$item->response_single.'</h2>';            
        break;
        case 'helper':
            if(count($filteredItems)===1){
                echo '<h4>'.$item->response_single.'</h4>';
            }
            break;

    }
    
    
}

function get_address($filteredItems ,$text, $items){
   
    $address = $text;
    foreach($filteredItems as $key => $filterItem){
        
        if($key==='number' || $key==='address') break;
        $address= str_replace(explode(' ',$items->{$key}->tokens) ,'', $address);
    }
    //echo '<br> $address'.$address;
    return $address;
}
function get_number($words){
    
    $number = 0;
    foreach($words as $word){
        
        if(is_numeric($word)){
            $number= $word;
            break;
        }
        
    }
    //echo '<br> $address'.$address;
    return $number;
}

var_dump($filteredItems);
//var_dump($weights);


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
    $data  =json_encode($event)."<br>";
    
    

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


