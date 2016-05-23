<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 */
class DbHandler {

    private $conn;

    function __construct() {
        //require_once dirname(__FILE__) . './DbConnect.php';
        // opening db connection
        //$db = new DbConnect();
        //  $this->conn = mysqli_connect("localhost", "aktf22fc_1", "Crtstr#21", "aktf22fc_1");

        $this->conn = mysqli_connect("localhost", "root", "", "Snapvote");
    }

    public function getLastId() {
        return mysqli_insert_id($this->conn);
    }

    function truncateAll(){
        $names = array("Group_Contact", "Snappvote", "Snappvote_Answer", "User", "`Group`");
        for ($x = 0; $x < count($names); $x++) {
            $table_name = $names[$x];
            $sql = "TRUNCATE TABLE ".$table_name;
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
            } else {
                return FALSE;
            }
            $stmt->close();
        }

        return TRUE;
    }

    public function getAllUsers() {
        header("Access-Control-Allow-Origin: *");

        $query = "SELECT id, username FROM User";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stmt->bind_result($id, $username);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["username"] = $username;
            array_push($response, $tmp);
        }
        $stmt->close();
        return $response;
    }

    public function checkIfExists($phoneNumber) {
        $query = "SELECT id, username FROM User WHERE phone = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $phoneNumber);
        $stmt->execute();
        $stmt->bind_result($id, $username);
        $response = array();
        while ($stmt->fetch()) {
            $response["id"] = $id;
            $response["username"] = $username;
        }
        return $response;
    }

    public function getUserById($id) {
        $query = "SELECT id, username FROM User WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $username);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["username"] = $username;
            array_push($response, $tmp);
        }
        $stmt->close();
        return $response;
    }

    public function getGroupsByUserId($id) {
        $query = "SELECT `id`, `user_id`, `name` FROM `Group` WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $user_id, $name);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["user_id"] = $user_id;
            $tmp["name"] = $name;

            array_push($response, $tmp);
        }
        $stmt->close();
        return $response;
    }

    public function getGroupById($id) {
        $stmt = $this->conn->prepare("SELECT `id`, `user_id`, `name` FROM `Group` WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getUsersByGroupId($id) {
        $query = "SELECT User.id, User.username FROM `User` INNER JOIN `Group_Contact` ON Group_Contact.contact_id = User.id AND Group_Contact.group_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $username);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["username"] = $username;

            array_push($response, $tmp);
        }
        $stmt->close();
        return $response;
    }

    public function removeAllContactsForGroup($group_id){
        $query = "DELETE FROM `Group_Contact` WHERE group_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $stmt->close();
        return TRUE;
    }

    public function getSnappvoteById($id, $voter_id) {
        $query = "SELECT `id`, `author_id`, `title`, `answer_1`, `answer_2`, `img_1`, `img_2`  FROM `Snappvote` WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $author_id, $title, $answer_1, $answer_2, $img_1, $img_2);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["author_id"] = $author_id;
            $tmp["title"] = $title;
            $tmp["answer_1"] = $answer_1;
            $tmp["answer_2"] = $answer_2;
            $tmp["img_1"] = "http://creative2thoughts.com/test/v1/images/".$id;//$img_1;

            if($img_2 != "..."){
                $tmp["img_2"] = "http://localhost/test/v1/images/".$id."_2";
            }
            else{
                $tmp["img_2"] = "...";
            }

            array_push($response, $tmp);
        }
        for ($i=0; $i < count($response); $i++) {
            $tmp = $response[$i];
            $voters = $this->getAnswerForSnapvoteByVoterId($id, $voter_id);
            $tmp["voter_answer"] = $voters[0]["answer_id"];
            $response[$i] = $tmp;
        }
        $stmt->close();
        return $response;
        // $stmt = $this->conn->prepare("SELECT `id`, `author_id`, `title`, "
        //         . "`img_1`, `img_2`, `answer_1`, `answer_2`, `expire_date`"
        //         . " FROM `Snappvote` WHERE id = ?");
        // $stmt->bind_param("i", $id);
        // $stmt->execute();
        // $tasks = $stmt->get_result();
        // $stmt->close();
        // return $tasks;
    }
    public function getAnswerForSnapvoteByVoterId($id, $voter_id) {
        $query = "SELECT `answer_id`FROM `Snappvote_Answer` WHERE snappvote_id = ? AND voter_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id, $voter_id);
        $stmt->execute();
        $stmt->bind_result($answer_id);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["answer_id"] = $answer_id;
            array_push($response, $tmp);
        }
        $stmt->close();
        return $response;
        // $stmt = $this->conn->prepare("SELECT `id`, `author_id`, `title`, "
        //         . "`img_1`, `img_2`, `answer_1`, `answer_2`, `expire_date`"
        //         . " FROM `Snappvote` WHERE id = ?");
        // $stmt->bind_param("i", $id);
        // $stmt->execute();
        // $tasks = $stmt->get_result();
        // $stmt->close();
        // return $tasks;
    }

    public function getSnappvotesByAuthorId($id) {
        $query = "SELECT `id`, `author_id`, `title`, `answer_1`, `answer_2`, `expire_date`, `date_created`  FROM `Snappvote` WHERE author_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $author_id, $title, $answer_1, $answer_2, $expire_date, $date_created);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["author_id"] = $author_id;
            $tmp["title"] = $title;
            $tmp["answer_1"] = $answer_1;
            $tmp["answer_2"] = $answer_2;
            $tmp["expire_date"] = $expire_date;
            $tmp["date_created"] = $date_created;

            array_push($response, $tmp);
        }
        for ($i=0; $i < count($response); $i++) {
            $tmp = $response[$i];
            $voters = $this->getVotersBySnappvoteId($tmp["id"]);
            $answer_1_count = 0;
            $answer_2_count = 0;
            for ($j=0; $j < count($voters); $j++) {
                if($voters[$j]["answer_id"] == 0){
                    $answer_1_count++;
                }
                if($voters[$j]["answer_id"] == 1){
                    $answer_2_count++;
                }
            }
            $tmp["answer_1_count"] = $answer_1_count;
            $tmp["answer_2_count"] = $answer_2_count;
            $tmp["voters"] = $voters;
            $response[$i] = $tmp;
        }

        $stmt->close();
        return $response;
    }

    public function test()
    {
        echo "string";
    }
    public function getVotersBySnappvoteId($id) {
        $stmt = $this->conn->prepare("SELECT User.username, Snappvote_Answer.answer_id  FROM `User` INNER JOIN `Snappvote_Answer` ON Snappvote_Answer.voter_id = User.id AND Snappvote_Answer.snappvote_id = ?");

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $answer_id);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["answer_id"] = $answer_id;

            array_push($response, $tmp);
        }
        $stmt->close();
        return $response;
    }

    public function getAnswersBySnappvoteId($id) {
        $stmt = $this->conn->prepare("SELECT `id`, `snappvote_id`, `voter_id`, `answer_id`"
                . " FROM `Snappvote_Answer` WHERE snappvote_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        $stmt->close();
        return $tasks;
    }

    public function getSnappvoteByVoterId($id) {
        $stmt = $this->conn->prepare("SELECT s.id, s.author_id, s.title, s.answer_1, s.answer_2, s.expire_date, s.date_created, a.answer_id FROM Snappvote s INNER JOIN Snappvote_Answer a ON a.snappvote_id = s.id AND a.voter_id = ?");

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $author_id, $title, $answer_1, $answer_2, $expire_date, $date_created, $answer_id);
        $response = array();
        while ($stmt->fetch()) {
            $tmp = array();
            $tmp["id"] = $id;
            $tmp["author_id"] = $author_id;
            $tmp["title"] = $title;
            $tmp["title"] = $title;
            $tmp["expire_date"] = $expire_date;
            $tmp['date_created'] = $date_created;
            $tmp['date_created'] = $date_created;
            $tmp['answer_id'] = $answer_id;

            array_push($response, $tmp);
        }
        // $user_result = $db->getUserById($tmp["author_id"]);
        for ($i=0; $i < count($response); $i++) {
            $tmp = $response[$i];
            $author = $this->getUserById($tmp["author_id"]);
            $tmp["author_username"] = $author[0]["username"];
            $response[$i] = $tmp;
        }
        $stmt->close();
        return $response;
    }

    public function createGroup($user_id, $name) {
        $stmt = $this->conn->prepare("INSERT INTO `Group`(`user_id`, `name`) VALUES (?,?)");
        $stmt->bind_param("is", $user_id, $name);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function createUser($username, $email, $phone, $country) {
        $stmt = $this->conn->prepare("INSERT INTO `User`(`email`, `phone`, `country`, `username`) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $email, $phone, $country, $username);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function editUser($id, $username, $email) {
        $stmt = $this->conn->prepare("UPDATE `User` SET `email`=?, `username`=? WHERE id=?");
        $stmt->bind_param("ssi", $email, $username, $id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function saveImage($base64img, $name) {
        define('UPLOAD_DIR', '../uploads/');
        $base64img = str_replace('data:image/jpeg;base64,', '', $base64img);
        $data = base64_decode($base64img);
        $filename = UPLOAD_DIR . $name . ".jpg";
        file_put_contents($filename, $data);
        return $filename;
    }

    public function createSnappvote($author_id, $title, $img_1, $img_2, $answer_1, $answer_2, $expire_date) {
    $date_created = date("Y-m-d H:i:s");
             $stmt = $this->conn->prepare("INSERT INTO `Snappvote`"
                    . "(`author_id`, `title`, `img_1`, `img_2`, `answer_1`, `answer_2`, `expire_date`, `date_created`)"
                    . " VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssssss", $author_id, $title, $img_1, $img_2, $answer_1, $answer_2, $expire_date, $date_created);

            // $stmt = $this->conn->prepare("INSERT INTO `Snappvote`"
            //         . "(`author_id`, `title`, `img_1`, `img_2`, `answer_1`, `answer_2`, `expire_date`, `date_created`)"
            //         . " VALUES (?,?,?,?,?,?,?,?)");
            // $stmt->bind_param("isssssss", $author_id, $title, $img_1, $img_2, $answer_1, $answer_2, $expire_date, $date_created);
            $result = $stmt->execute();
            $stmt->close();
            return $this->getLastId();
            if ($result) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

    public function createAnswer($snappvote_id, $voter_id, $answer_id) {
        $stmt = $this->conn->prepare("INSERT INTO `Snappvote_Answer`(`snappvote_id`, `voter_id`, `answer_id`)"
                . " VALUES (?,?,?)");
        $stmt->bind_param("iii", $snappvote_id, $voter_id, $answer_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function addContactsBulk($group_id, $contacts_ids) {
        $stmt = $this->conn->prepare("INSERT INTO `Group_Contact`(`group_id`, `contact_id`) VALUES (?, ?)");
        for ($x = 0; $x < count($contacts_ids); $x++) {
            $stmt->bind_param("ii", $group_id, $contacts_ids[$x]);
            $result = $stmt->execute();
            if ($result) {
        } else {
            return FALSE;
        }
        }

        $stmt->close();
        return TRUE;

    }

    public function createAnswersBulk($snappvote_id, $contacts_ids, $answer_id) {
        $stmt = $this->conn->prepare("INSERT INTO `Snappvote_Answer`(`snappvote_id`, `voter_id`, `answer_id`) VALUES (?,?,?)");
        for ($x = 0; $x < count($contacts_ids); $x++) {
            $stmt->bind_param("iii", $snappvote_id, $contacts_ids[$x], $answer_id);
            $result = $stmt->execute();
            if ($result) {
        } else {
            return FALSE;
        }
        }

        $stmt->close();
        return TRUE;

    }

    public function addUserToGroup($user_id, $group_id) {
        $stmt = $this->conn->prepare("INSERT INTO `Group_Contact`(`group_id`, `contact_id`) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $group_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function createContact($user_id, $name) {
        $stmt = $this->conn->prepare("INSERT INTO `Group`(`user_id`, `name`) VALUES (?,?)");
        $stmt->bind_param("is", $user_id, $name);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function updateAnswer($snappvote_id, $voter_id, $answer_id) {
        $stmt = $this->conn->prepare("UPDATE `Snappvote_Answer` SET `answer_id`=? WHERE snappvote_id = ? AND voter_id = ?");
        $stmt->bind_param("iii", $answer_id, $snappvote_id, $voter_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

?>
