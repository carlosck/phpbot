<?php
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;

$isFirebaseInitialized = false;
$FirestoreDB = null;

function getFirebase(){
    global $isFirebaseInitialized;
    global $FirestoreDB;
    if(!$isFirebaseInitialized){
        require 'vendor/autoload.php';
        $FirestoreDB = new FirestoreClient();
        
        $collectionReference = $FirestoreDB->collection('users');
        $documentReference = $collectionReference->document('114706781664198');
        $isFirebaseInitialized = true;
    }

    return $FirestoreDB;
    
}
function getCurrentIssueID($user_id){
    $db=getFirebase();
    $query= $db->collection('users')->document($user_id)->collection('reportes')->orderBy("time", 'DESC')->limit(1);
    $issue_array = $query->documents();
    return $issue_array->rows()[0]->id();
}
function setIssue($issue, $user_id){
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('reportes')->add(['type' => $issue,'time'=>new Timestamp(new DateTime())]);
}
function setLuminariaID($command, $user_id){
    $issue = getCurrentIssue($user_id);
    $db=getFirebase();
    $db->collection('users')->document($user_id)->collection('reportes')->document($issue->id)->update(
        [
            [
                'path' => 'luminariaID',
                'value'=> $command
            ]
        ]
        
    );
}

$user_id= '114706781664198';
$db=getFirebase();

/* $query= $db->collection('users')->document($user_id)->collection('steps')->orderBy("time", 'DESC')->limit(1);
$steps_array = $query->documents();
echo '<pre>';
var_dump($steps_array->rows()[0]->data()['id']);

/* foreach ($steps_array as $document) {
    if ($document->exists()) {
        printf('Document data for document %s:' . PHP_EOL, $document->id());
        print_r($document->data());
        printf(PHP_EOL);
    } else {
        printf('Document %s does not exist!' . PHP_EOL, $document->id());
    }
} 
// setIssue('luminaria', $user_id);
$issue = getCurrentIssue($user_id);

$db=getFirebase();
var_dump($issue);  
*/
//setLuminariaID('sa4565654',$user_id);
//$db->collection('users')->document($user_id)->collection('reportes')->add(['type' => $issue,'time'=>new Timestamp(new DateTime())]);

function getReportById($report_id, $user_id){
    $db=getFirebase();
    $query= $db->collection('users')->document($user_id)->collection('reportes')->where('reportID','==',(int)$report_id);
    $issue_array = $query->documents();
    return $issue_array->rows()[0]->data();
}
echo '<pre>';
var_dump(getReportById('1960',$user_id));