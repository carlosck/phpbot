<?php

define('TOKEN', 'SYiI9n$^l5y6^Eg');
define('FBTOKEN', getenv('FB_TOKEN'));
define('PHONE', getenv('PHONE'));
define('PHONEID', getenv('PHONE_ID'));
define('URLMESSAGES', 'https://graph.facebook.com/v17.0/'.PHONEID.'/messages');

define('ENTER_NAME_PROMPT', 1);
define('ENTER_LUMINARIA_NUMBER_PROMPT', 11);
define('ENTER_LUMINARIA_SI_NO_NUMBER_PROMPT', 12);

define('ENTER_ISSUE_LOCATION_PROMPT',22);
define('ENTER_ISSUE_GETADDRESS_PROMPT',23);
define('ENTER_ISSUE_GETPROBLEM_PROMPT', 24);
define('ENTER_ISSUE_PHOTO_PROMPT', 25);

define('GET_REPORT_ID_PROMPT',31);

define('GET_CP_PROMPT',41);

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Core\GeoPoint;
use Google\Cloud\Firestore\FieldValue;

$isFirebaseInitialized = false;
$FirestoreDB = null;

function getFirebase(){
    global $isFirebaseInitialized;
    global $FirestoreDB;

    if(!$isFirebaseInitialized){
        require 'vendor/autoload.php';
        $FirestoreDB = new FirestoreClient();
                
        $isFirebaseInitialized = true;
    }

    return $FirestoreDB;
    
}

//DESHABILITAMOS EL MOSTRAR ERRORES
/* ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1); */

//require 'vendor/autoload.php';
//IMPORTAMOS LAS LIBRERIRAS DE Rivescript
//use \Axiom\Rivescript\Rivescript;
function sendCURL($post_fields){
    $header = array('Authorization: Bearer '.FBTOKEN , "Content-Type: application/json",);
    //INICIAMOS EL CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, URLMESSAGES);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //OBTENEMOS LA RESPUESTA DEL ENVIO DE INFORMACION
    $response = json_decode(curl_exec($curl), true);
    
    //OBTENEMOS EL CODIGO DE LA RESPUESTA
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //CERRAMOS EL CURL
    curl_close($curl);
    return(
        (object) array(
            'data'=> $response,
            'status_code'=> $status_code,
        )
        );
}
function sendInteractive($header, $body, $options){
        
    $message = ''
            . '{'
            . '"messaging_product": "whatsapp", '
            . '"recipient_type": "individual",'
            . '"to": "'.PHONE.'", '
            . '"type": "interactive", '
            . '"interactive":{
                "type": "list",
                "header": {
                  "type": "text",
                  "text": "'.$header.'"
                },
                "body": {
                  "text": "'.$body.'"
                },
                "footer": {
                  "text": "Menú"
                },
                "action": {
                  "button": "Opciones Disponibles",
                  "sections":[
                    {
                    "title":"opciones",
                    "rows": [
                        '.$options.'
                        ]
                    }                     
                  ]
                }
              }'
            . '}';
    
    $response = sendCURL($message);
    file_put_contents("sends_interactive.txt", json_encode($response->data).' status_code '.$response->status_code);
}

function sendAskLocation($user_id){
    setStep( ENTER_ISSUE_LOCATION_PROMPT, $user_id);
    sendData('puedes compartir tu ubicación actual desde whatsapp dando click en (+) en IPhone o (📎) en Android o escribe la dirección (calle, número, colonia , CP, entrecalles)');
    
} 
function sendQuickReply($header, $body, $buttons){
       
    $message = ''
            . '{'
            . '"messaging_product": "whatsapp", '
            . '"recipient_type": "individual",'
            . '"to": "'.PHONE.'", '
            . '"type": "interactive", '
            . '"interactive":{
                "type": "button",
                "header": {
                  "type": "text",
                  "text": "'.$header.'"
                },
                "body": {
                  "text": "'.$body.'"
                },
                "footer": {
                  "text": "Menú"
                },
                "action": {
                    "buttons": [
                      '.$buttons.'
                    ] 
                  } 
              }'
            . '}';
    
    $response = sendCURL($message);
    file_put_contents("sends_quickreply.txt", json_encode($response->data).' status_code '.$response->status_code);
}

function sendData($action){
       
    $message = ''
            . '{'
            . '"messaging_product": "whatsapp", '
            . '"recipient_type": "individual",'
            . '"to": "'.PHONE.'", '
            . '"type": "text", '
            . '"text": '
            . '{'
            . '     "body":"'.$action.'",'
            . '     "preview_url": true, '
            . '} '
            . '}';
    $response = sendCURL($message);
    file_put_contents("sends_json.txt", $message);
    file_put_contents("sends.txt", json_encode($response->data).' status_code '.$response->status_code);
    
}

function sendAskDescription($user_id){
    setStep(ENTER_ISSUE_GETPROBLEM_PROMPT, $user_id);
    sendData('¿puedes explicarme el problema? (escribe de que se trata el reporte)');
}

function sendAskPhoto($user_id){    
    setStep(ENTER_ISSUE_PHOTO_PROMPT, $user_id);
    //sendData('¿me podrías compartir una fotografía del reporte?');
    
    /* sendInteractive(
        '¿me podrías compartir una fotografía del reporte?',
        '(trata que sea una imagen que describa lo mejor posible el problema) puedes compartirla desde whatsapp dando click en (+) en IPhone o (📎) en Android o escribe \"No\" para continuar',
        '{}'
    ); */
    sendData('¿me podrías compartir una fotografía del reporte?(trata que sea una imagen que describa lo mejor posible el problema)');
    sendData('puedes compartirla desde whatsapp dando click en (+) en IPhone o (📎) en Android o escribe \"No\" para continuar');
    
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

function sendMenu(){
    sendInteractive(
        'Elige la opción de tu preferencia.',
        'Puedes escribir \"Salir\" en cualquier momento...',
        '
        {
            "id":"reportes",
            "title": "Reportes",
        },
        {
            "id":"consulta reportes",
            "title": "Consulta de Reporte",
        },
        {
            "id":"servicios",
            "title": "Info de servicios",
        },
        {
            "id":"tramites",
            "title": "Trámites",
        },'
    );
}

function getCurrentStep($user_id){
    $db=getFirebase();
    $query= $db->collection('users')->document($user_id)->collection('steps')->orderBy("time", 'DESC')->limit(1);
    $steps_array = $query->documents();
    return $steps_array->rows()[0]->data()['id'];
}

function getCurrentIssue($user_id){
    $db=getFirebase();
    $query= $db->collection('users')->document($user_id)->collection('reportes')->orderBy("time", 'DESC')->limit(1);
    $issue_array = $query->documents();
    return $issue_array->rows()[0]->data();
}

function setStep($data, $user_id){
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('steps')->add(['id' => $data,'time'=>new Timestamp(new DateTime())]);
}

function setUserData($name, $phone, $user_id){
    $db=getFirebase();
    $db->collection('users')->document($user_id)->set(['Name' => $name, 'phone'=>$phone]);
}

function setIssue($issue, $user_id){
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('reportes')->add(['type' => $issue,'time'=>new Timestamp(new DateTime()),'status'=>'open']);
}

function getCurrentIssueID($user_id){
    $db=getFirebase();
    $query= $db->collection('users')->document($user_id)->collection('reportes')->orderBy("time", 'DESC')->limit(1);
    $issue_array = $query->documents();
    return $issue_array->rows()[0]->id();
}

function updateCurrentIssue($command, $field, $user_id)
{
    $issueID = getCurrentIssueID($user_id);
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('reportes')->document($issueID)->update(
        [
            [
                'path' => $field,
                'value'=> $command
            ]
        ]
        
    );
}

function setReport($user_id){
    $report_number= rand(1,2500);
    updateCurrentIssue($report_number, 'reportID',$user_id);
    updateCurrentIssue('filled', 'status',$user_id);
    sendData('Tu Reporte ha sido exitoso Tu número de Folio es '.$report_number.' para tu reporte es el siguiente, guárdalo! es importante por si quieres darle seguimiento con nosotros');
    sendMenu();
}

function setLocation($latitude, $longitude, $user_id){
    $issueID = getCurrentIssueID($user_id);
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('reportes')->document($issueID)->update(
        [
            [
                'path' => 'location',
                'value'=>  new GeoPoint($latitude,$longitude)
            ]            
        ]
    );
}

function setImage($image, $user_id){
    $issueID = getCurrentIssueID($user_id);
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('reportes')->document($issueID)->update(
        [
            [
                'path' => 'images',
                'value' => FieldValue::arrayUnion([$image])
            ]            
        ]
    );
}

function getReportById($report_id, $user_id){
    $db=getFirebase();
    $query= $db->collection('users')->document($user_id)->collection('reportes')->where('reportID','==',(int)$report_id);
    $issue_array = $query->documents();
    if(isset($issue_array->rows()[0]))
    {
        return $issue_array->rows()[0]->data();
    }
    else{
        return null;
    }
}

function setResponse($command, $user_id){
    global $name;
    global $phone;
    switch($command){        
        case 'hola':
            setUserData($name, $phone, $user_id);
            sendData(' ¡Hola  '.$name.'! Soy Tu Asistente Virtual de El Municipio de Saltillo, será un placer atenderte si tienes una emergencia 🚓 🚑 marca inmediatamente al 911');
            sendMenu();
            //setStep(ENTER_NAME_PROMPT, $user_id);
            //sendData('Hola , ¿Con quién tengo el gusto?');
            break;
        case 'salir':
            sendData('Gracias , ');
            break;
        break;        
        case 'luminarias':
            setIssue('luminaria', $user_id);
            setStep(ENTER_LUMINARIA_NUMBER_PROMPT, $user_id);
            sendData('Ingresar el número de luminaria que comienza con SAL y se encuentra en una etiqueta a mediación de poste ejemplo SAL23456, si no lo tienes a la mano solo escribe \"No\"');
            break;                
        break;
        case 'limpieza plazas':
            setIssue('plaza', $user_id);
            sendAskLocation($user_id);
        break;
        case 'bacheo':
            setIssue('bacheo', $user_id);
            sendAskLocation($user_id);
        break;
        case 'recoleccion':
            setIssue('recoleccion', $user_id);
            sendAskLocation($user_id);
        break;
        case 'reporte vial':
            setIssue('reporte vial', $user_id);
            sendAskLocation($user_id);
        break;
        case 'control canino':
            setIssue('control canino', $user_id);
            sendAskLocation($user_id);
        break;
        case 'reporte otros':
            setIssue('reporte otros', $user_id);
            sendAskLocation($user_id);
        break;      
        case 'reportes':
            sendInteractive(
                'Que asunto te gustaría reportar',
                'Selecciona una opción',
                '
                {
                    "id":"luminarias",
                    "title": "Luminarias",
                },
                {
                    "id":"limpieza plazas",
                    "title": "Limpieza de plazas",
                },
                {
                    "id":"bacheo",
                    "title": "Bacheo",
                },
                {
                    "id":"recoleccion",
                    "title": "Recoleción de basura",
                },
                {
                    "id":"reporte vial",
                    "title": "Reporte vial",
                },
                {
                    "id":"control canino",
                    "title": "Control Canino",
                },
                {
                    "id":"reporte otros",
                    "title": "Otros",
                },
                {
                    "id":"reporte salir",
                    "title": "Salir",
                },'
            );
        break;        
        
        case 'consulta reportes':
            setStep(GET_REPORT_ID_PROMPT,$user_id);
            sendData('Comparteme tu Folio de reporte, (\"Ejemplo : 301234\").con gusto te daremos el estado en que se encuentra');            
        break;


        case 'servicios':
                sendQuickReply('¿ De qué Servicio requieres Información?','selecciona una opción',
                    '
                        {
                            "type": "reply",
                            "reply": {
                              "id": "horario recoleccion",
                              "title": "Horarios recolección" 
                            }
                        },
                        {
                            "type": "reply",
                            "reply": {
                              "id": "facturacion",
                              "title": "Facturación" 
                            }
                        },
                        {
                            "type": "reply",
                            "reply": {
                              "id": "queja ciudadana",
                              "title": "Queja ciudadana" 
                            }
                        }
                        '
                    );
        break;
        case 'horario recoleccion':
            setStep(GET_CP_PROMPT, $user_id);
            sendData('😁  ¡Gracias por contactarnos!  Para brindarte la información necesaria. ¿Nos puedes proporcionar de que colonia desea saber el horario de Recolección?');
        break;
        case 'facturacion':
            sendData('Te Proporciono el siguiente enlace para Facturas Con Recibo \n✅ Impuesto Predial\n✅ ISAI ( Pago en linea)\n✅ Ingresos Varios\n✅ Sm@rt pago en línea\n ✅ Saneamiento-Aguas de Saltillo \n ✅ Caja 5 \n http://cfdi.saltillo.gob.mx:8888/CFDI/facturaConRecibo.php');            
        break;
        case 'queja ciudadana':
            sendData('quejas ');
        break;

        case 'tramites':
            sendInteractive(
                'Que trámite te interesa',
                'Selecciona una opción',
                '
                {
                    "id":"cambio us",
                    "title": "Cambio us",
                },
                {
                    "id":"juridico",
                    "title": "Juridico",
                },
                {
                    "id":"geomatica",
                    "title": "Unidad de Geomática",
                },
                {
                    "id":"catastral",
                    "title": "Enlace Catastral",
                },
                {
                    "id":"pddu",
                    "title": "PDDU",
                },
                {
                    "id":"planos",
                    "title": "Planos",
                },
                {
                    "id":"informes",
                    "title": "Informes",
                },
                {
                    "id":"correcciones",
                    "title": "Correciones",
                },'
            );
        break;
        case 'cambio us':
            sendData('Una vez que Usted haya elaborado el Estudio Técnico Justificativo de acuerdo a la guía de elaboración que se le entrega en la Dirección de Desarrollo Urbano, será presentado ante el Consejo Municipal de Desarrollo Urbano para obtener su Visto Bueno; posteriormente se turna al R. Ayuntamiento de Saltillo, quién lo analiza a través de la Comisión de Planeación, Urbanismo, Obras Públicas y Centro Histórico y se expone para su autorización ante el Cabildo en Pleno.');
        break;
        case 'juridico':
            sendData('si me notificarón ,Deberá acudir al área de Inspección adscrita a la Subdirección Jurídica Con escritura pública debidamente inscrita ante el Registro Publico');
        break;
        case 'geomatica':
            sendData('Es un área de la Dirección de Desarrollo Urbano, dedicada a digitalizar la cartografía del Municipio, el enlace operativo con la Dirección de Catastro Municipal y a aplicar herramientas geomáticas como la Plataforma CIVIT Saltillo A los ciudadanos les provee de la pre-revisión de los planos de de aquellos trámites que después se registrarán en la Dirección de Catastro Munuicipal. Así mismo, difunde de forma impresa o digital, el Plan Director de Desarrollo Urbano.  Predominantemente  apoya a las distintas dependencias municipales que requieran planos para proyectos, georreferenciación de predios o acceso a la cartografía existente. Sin embargo, también los ciudadanos podrían acceder a estos servicios a través de un oficio-solicitud y su posterior análisis.');
        break;
        case 'catastral':
            sendData('Conjunta a las direcciones de Desarrollo Urbano y Catastro, para unificar criterios en la revisión de los trámites de Fraccionamientos, Urbanizaciones Menores, Elevación a Régimen en Condominio, Adecuaciones, Fusiones y/o Subdivisiones, donde participan las dos dependencias. Se apoya a la Subdirección de Gestión Urbana, en la pre-revisión catastral de los planos de Subdivisiones, Fusiones y/o Adecuaciones, Elevaciones de Regimen en Condominio, Urbanizaciones Menores y Fraccionamientos.');
        break;
        case 'pddu':
            sendData('El Plan Director de Desarrollo Urbano (PDDU) se solicita en la ventanilla 1 de la UMR y después de haber sido pagado se entrega en el departamento de Cartografía dentro de la Unidad de Geomatica, Se manejan dos versiones, digital e impreso, digital tiene un costo de $120 e impreso de $470, de igual forma se te indica que debes de traer tu dispositivo de almacenamiento para poder guardarte la versión digital.');
        break;
        case 'planos':
            sendData('¿Cuentan con algún plano de la ciudad? . Si, en el departamento de Cartografía se cuenta con otros dos planos adicionales, uno de la ciudad y uno de arroyos que tienen el mismo precio que el PDDU en versiones digitales e impresas.');
        break;
        case 'informes':
            sendData('El horario de atencion presencial es de 8:00 am a 3:00 pm, el ingreso de trámite es de 8:30 a 1:30 pm, al igual puede descargar los requisitos en la Plataforma Smart (tramites.saltillo.gob.mx)');
        break;
        case 'correcciones':
            sendData('Aquí podemos hacerle en ingreso de su corrección, o si gusta puede entrar a la página https://tramites.saltillo.gob.mx/ y en la seccion de Solicitud de Corrección puede tramitarla, las correcciones no tienen ningún costo.');
        break;
        

        default:
            
            $current_step = getCurrentStep($user_id);
            
            switch($current_step)
            {
                
                case ENTER_LUMINARIA_NUMBER_PROMPT:
                    if($command==="no")
                    {                        
                        sendAskLocation($user_id);   
                    }
                    else{
                        updateCurrentIssue($command,'luminariaID', $user_id);                        
                        sendAskDescription($user_id);
                    }
                break;
                case ENTER_ISSUE_LOCATION_PROMPT:
                    updateCurrentIssue($command,'direccion', $user_id);
                    sendAskDescription($user_id);
                break;
                                       
                case ENTER_ISSUE_GETPROBLEM_PROMPT:
                    updateCurrentIssue($command, 'descripcion', $user_id);                    
                    sendAskPhoto($user_id);                        
                break;
                case ENTER_ISSUE_PHOTO_PROMPT:                     
                    if($command==='no'){
                        setReport($user_id);
                    }
                break;
                case GET_REPORT_ID_PROMPT:
                    $report = getReportById($command, $user_id);
                    if($report!==null){
                        sendData('tu reporte de '.$report['type'].' '.' se encuentra en estatus de '.$report['status']);
                    }
                    else{
                        sendData('Reporte no existe');
                    }
                    
                break;
                case GET_CP_PROMPT:
                    sendData('Gracias por comunicarte con el Gobierno Municipal de Saltillo, es un gusto para nosotros atenderte. Unidos trabajamos por un Saltillo limpio. 🌱');
                    
                    sendData('En este sitio podrás ubicar la unidad 🚛 de recolección asignada a tu zona📍, así como el horario de operación 🔂 de la misma.https://saltillo.gob.mx/horarios-de-recoleccion-de-basura/');
                    
                    sendData('Los horarios de recolección de basura en la colonia   LOS PARQUES es en horario VESPERTINA los días LU, MI y VI de 18:00 a 01:00 HRS. Saltillo nos gusta ♥ limpio y con servicios de calidad. 👍🏻');
                    sendMenu();
                break;

            }
        break;
    }
}

//RETO QUE RECIBIREMOS DE FACEBOOK
if(isset($_GET['hub_challenge'])){
    $palabraReto = $_GET['hub_challenge'];
    //TOQUEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
    $tokenVerificacion = $_GET['hub_verify_token'];
    //SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
    if ($TOKEN === $tokenVerificacion) {
        echo $palabraReto;
        exit;
    }
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
                    setResponse($command, $user_id);
                break;
                case 'interactive':
                    $interactive_type= $event->entry[0]->changes[0]->value->messages[0]->interactive->type;
                    $interactive_respose = $event->entry[0]->changes[0]->value->messages[0]->interactive->{$interactive_type}->id;
                    sendData('$interactive_type_'.$interactive_type);
                    setResponse($interactive_respose, $user_id);
                break;
                case 'location':
                    $latitude = $event->entry[0]->changes[0]->value->messages[0]->location->latitude;
                    $longitude = $event->entry[0]->changes[0]->value->messages[0]->location->longitude;                    
                    setLocation($latitude, $longitude, $user_id);
                    sendAskDescription($user_id);
                break;
                case 'image':
                    $image = $event->entry[0]->changes[0]->value->messages[0]->image;
                    $sha256 = $event->entry[0]->changes[0]->value->messages[0]->image->sha256;                    
                    setImage($image, $user_id);
                    setReport($user_id);
                break;
            }
            
            
            flock($lock, LOCK_UN);  // unlock
            fclose($lock);  // close
        }
    }
    
}
else{
    $file = 'empty.txt';  
    $data_save =json_encode($data)."\n";  
    file_put_contents($file, $data_save, FILE_APPEND | LOCK_EX);
}
 
http_response_code(200);
exit();
?>