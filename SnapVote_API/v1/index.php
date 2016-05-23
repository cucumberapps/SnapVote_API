<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-ACCESS_TOKEN, Access-Control-Allow-Origin, Authorization, Origin, x-requested-with, Content-Type, Content-Range, Content-Disposition, Content-Description');
date_default_timezone_set('America/New_York');


require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
// require ('../CorsSlim/CorsSlim.php');
//
// $corsOptions = array(
//     "origin" => "*",
//     "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
//     "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
// );
// $cors = new \CorsSlim\CorsSlim($corsOptions);
//
// $app->add($cors);

$app->get('/hello', function() {
    header("Access-Control-Allow-Origin: *");
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    $response = array();
    $response['hello'] = 'hello';
    echoResponse(200, $response);
});

$app->get('/images/:id', function($id) use ($app) {
    $image = file_get_contents("../uploads/".$id.".jpg");
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $app->response->header('Content-Type', 'content-type: ' . $finfo->buffer($image));
    echo $image;
});


//Clear DB
$app->get('/cleardb', function() {
    $response = array();
	$db = new DbHandler();
	$truncate = $db->truncateAll();
	$response["truncate"] = $truncate;
	echoResponse(200, $response);
});

//Get all users
$app->get('/users', function() use ($app){
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: X-ACCESS_TOKEN, Access-Control-Allow-Origin, Authorization, Origin, x-requested-with, Content-Type, Content-Range, Content-Disposition, Content-Description');

    $db = new DbHandler();
    $response = $db->getAllUsers();
    echoResponse(200, $response);
});

//Get user with :id
$app->get('/users/:id', function($id) {
    $response = array();
    $db = new DbHandler();
    $response = $db->getUserById($id);
    echoResponse(200, $response);
});

//Get groups for user with :id
$app->get('/users/:id/groups', function($id) {
    $response = array();
    $db = new DbHandler();
    $response = $db->getGroupsByUserId($id);
    echoResponse(200, $response);
});

//Get users for group with id
$app->get('/groups/:id', function($id) {
    $response = array();
    $db = new DbHandler();
    $response = $db->getUsersByGroupId($id);
    echoResponse(200, $response);
});

//Get snappvote with :id
$app->get('/snapvotes/:id/:vid', function($id, $voter_id) {
    $db = new DbHandler();
    $response = $db->getSnappvoteById($id, $voter_id);
    echoResponse(200, $response);
});

//Gets outgoing snappvotes for user with :id
$app->get('/snappvotes/out/:id', function($id) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: X-ACCESS_TOKEN, Access-Control-Allow-Origin, Authorization, Origin, x-requested-with, Content-Type, Content-Range, Content-Disposition, Content-Description');

    $db = new DbHandler();
    $response = $db->getSnappvotesByAuthorId($id);
    echoResponse(200, $response);
});

//Get voters for snapvote with :id
$app->get('/snappvotes/out/answers/:id', function($id) {
    $db = new DbHandler();
    $response = $db->getVotersBySnappvoteId($id);
    echoResponse(200, $response);
});


//Get incoming snappvotes for user with :id
$app->get('/snappvotes/in/:id', function($id) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: X-ACCESS_TOKEN, Access-Control-Allow-Origin, Authorization, Origin, x-requested-with, Content-Type, Content-Range, Content-Disposition, Content-Description');

    $db = new DbHandler();
    $response = $db->getSnappvoteByVoterId($id);
    echoResponse(200, $response);
});

//Get answers for snappvote wiht :id
// $app->get('/snappvotes/answers/:id', function($id) {
//     $response = array();
//     $db = new DbHandler();
//     $result = $db->getAnswersBySnappvoteId($id);
//
//     while ($row = $result->fetch_assoc()) {
//         $tmp = array();
//         $tmp["snappvote_id"] = $row["snappvote_id"];
//         $tmp["voter_id"] = $row["voter_id"];
//         $tmp["answer_id"] = $row["answer_id"];
//
//         array_push($response, $tmp);
//     }
//
//     echoResponse(200, $response);
// });

//Create new user
$app->post('/users', function() use ($app) {
    $response = array();
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $username = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $country = $request->country;
    }

    $db = new DbHandler();
    $success = $db->createUser($username, $email, $phone, $country);

    if ($success) {
        $response["error"] = false;
        $response["id"] = $db->getLastId();
        $response["message"] = "User created successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});

//Update user
$app->post('/useredit/:id', function($id) use ($app) {
    // $response = array();
    // $postdata = file_get_contents("php://input");
    // if (isset($postdata)) {
    //     $request = json_decode($postdata);
    //     $username = $request->name;
    //     $email = $request->email;
    // }
    $username = 'edittest';
    $email = 'asdf@asd.asd';
    $db = new DbHandler();
    $success = $db->editUser($id, $username, $email);

    if ($success) {
        $response["error"] = false;
        $response["id"] = $db->getLastId();
        $response["message"] = "User created successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});

//Create new group for user with :id
$app->post('/groups/:id', function($id) use ($app) {
    $response = array();
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $name = $request->name;
    }

    $db = new DbHandler();

    $success = $db->createGroup($id, $name);

    if ($success) {
        $response["error"] = false;
        $response["message"] = "Group created successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});


//Add user with :id to group with :gid
$app->post('/groups/:id/contacts', function($group_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $contacts_ids = $request->contacts_ids;
    }
    $db->removeAllContactsForGroup($group_id);

    $success = $db->addContactsBulk($group_id, $contacts_ids);
    if ($success) {
        $response["error"] = false;
        $response["message"] = "Contacts added successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});

$app->put('/test/:id', function($id) use ($app) {
    $response = array();
    $db = new DbHandler();
    $success = $db->removeAllContactsForGroup($id);

    if ($success) {
        $response["error"] = false;
        $response["message"] = "Contacts added successfully";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});

//Create new outgoing snappvote for user with :id
$app->post('/snappvotes/out/:id', function($id) use ($app) {
    $response = array();
    $author_id = $id;
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $title = $request->title;
        $img_1 = $request->img_1;
        $img_2 = $request->img_2;
        $answer_1 = $request->answer_1;
        $answer_2 = $request->answer_2;
        $expire_date = $request->expire_date;
        $expire_date2 = date('Y-m-d H:i:s', strtotime($expire_date . ' +1 day'));

        $contacts_ids = $request->contacts_ids;
        $groups_ids = $request->groups_ids;
        $date_createdJson = $request->date_created;
        $date_field         = date('Y-m-d',strtotime($expire_date2));
        $date_created         = date('Y-m-d',strtotime($date_createdJson));

        $response['title'] = $title;
        $response['expire'] = $expire_date2;
        $response['date_created'] = $date_created;
        $response['contacts_ids'] = $contacts_ids;
    }

    $db = new DbHandler();
    $success = $db->createSnappvote($author_id, $title, $img_1, $img_2, $answer_1, $answer_2, $date_field);
    $img_1_name = strval($success);
    $img_2_name = strval($success)."_2";
    $response['img_1_name'] = $img_1_name;
    $response["img_2_name"] = $img_2_name;

    $db->saveImage($img_1, $img_1_name);
    //$db->saveImage($img_2, $img_2_name);
    //
    if($groups_ids != ""){
        for ($i=0; $i < count($groups_ids); $i++) {
            $groupsContacts = $db->getUsersByGroupId($groups_ids[$i]);
            $test = array();
            for ($j=0; $j < count($groupsContacts); $j++) {
                array_push($test, $groupsContacts[$j]['id']);
            }
            $db->createAnswersBulk($success, $test, -1);
        }
    }
    if($contacts_ids != ""){
        $db->createAnswersBulk($db->getLastId(), $contacts_ids, -1);
    }
    echoResponse(201, $response);
});

$app->post('/asd', function() use ($app) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: X-ACCESS_TOKEN, Access-Control-Allow-Origin, Authorization, Origin, x-requested-with, Content-Type, Content-Range, Content-Disposition, Content-Description');

    $response = array();
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $contacts_ids = $request->contacts_ids;
        $db = new DbHandler();
        $result = array();
        for ($i=0; $i < count($contacts_ids); $i++) {
            $result[$i] = $db->checkIfExists($contacts_ids[$i]);
        }
        $response = $result;
    }

    // $db = new DbHandler();
    //
    // $success = $db->createSnappvote($author_id, $title, $img_1, $img_2, $answer_1, $answer_2, $expire_date);
    // $contactsSuccess = $db->createAnswersBulk($db->getLastId(), $contacts_ids, -1);
    //
    // if ($success) {
    //     $response["error"] = false;
    //     $response["id"] = $db->getLastId();
    //     $response["message"] = "Snappvote created successfully.";
    // } else {
    //     $response["error"] = true;
    //     $response["message"] = "Error. Please try again";
    // }
    // echoResponse(201, $response);
    echoResponse(201, $response);

});

$app->post('/snappvotes/answers/:id', function($id) use ($app) {
    $response = array();

    $db = new DbHandler();
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $voter_id = $request->voter_id;
        $answer_id = $request->answer_id;


    }
$response["asd"] = $voter_id;
        $response["asd2"] = $answer_id;
    $success = $db->updateAnswer($id, $voter_id, $answer_id);

    if ($success) {
        $response["error"] = false;
        $response["message"] = "Answer updated";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});
//Create new answer for snappvote with :id
// $app->post('/snappvotes/answers/:id', function($id) use ($app) {
//     $response = array();
//     $voter_id = $app->request->post('voter_id');
//     $answer_id = $app->request->post('answer_id');
//
//     $db = new DbHandler();
//
//     $success = $db->createAnswer($id, $voter_id, $answer_id);
//
//     if ($success) {
//         $response["error"] = false;
//         $response["message"] = "Answer created successfully";
//     } else {
//         $response["error"] = true;
//         $response["message"] = "Error. Please try again";
//     }
//
//     echoResponse(201, $response);
// });

$app->put('/snappvotes/answers/:id', function($id) use ($app) {
    $response = array();

    $db = new DbHandler();
    $postdata = file_get_contents("php://input");
    if (isset($postdata)) {
        $request = json_decode($postdata);
        $voter_id = $request->voter_id;
        $answer_id = $request->answer_id;

    }

    $success = $db->updateAnswer($id, $voter_id, $answer_id);

    if ($success) {
        $response["error"] = false;
        $response["message"] = "Answer updated";
    } else {
        $response["error"] = true;
        $response["message"] = "Error. Please try again";
    }
    echoResponse(201, $response);
});

$app->post('/test', function() use ($app) {

});

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}

$app->run();
?>
