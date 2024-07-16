<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    exit(0);
}
class Myclass
{
    function link()
    {
        $connect = new mysqli("localhost", "root", "", "db_name");
        if ($connect->connect_error) {
            die("Connection failed: " . $connect->connect_error);
        }
        return $connect;
    }
    function deleteImage($imgName)
    {
        $imgFolder = __DIR__ . '/../assets/uploads/';
        $imgPath = $imgFolder . $imgName;
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }
    function getExistingImage($table, $con_1, $con_1_value)
    {
        $sql = "SELECT stu_img FROM $table WHERE $con_1 = '$con_1_value'";
        $result = $this->link()->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['stu_img'];
        }
        return null;
    }
}

$object = new Myclass();
$action = $_GET['action'];

if ($action == 'select') {
    $sql = "SELECT * FROM student";
    $result = $object->link()->query($sql);
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode($data);
} elseif ($action == 'insert') {
    $stu_name = $_POST['stu_name'];

    if (isset($_FILES['stu_img']) && $_FILES['stu_img']['error'] == 0) {
        $img_name = basename($_FILES['stu_img']['name']);
        $img_tmp_name = $_FILES['stu_img']['tmp_name'];
        $img_folder = __DIR__ . '/../assets/uploads/';
        move_uploaded_file($img_tmp_name, $img_folder . $img_name);
    } else {
        $img_name = null;
    }

    $sql = "INSERT INTO student (stu_name, stu_img) VALUES ('$stu_name', '$img_name')";
    $query = $object->link()->query($sql);
    if ($query) {
        echo json_encode(["message" => "Record added successfully!"]);
    } else {
        echo json_encode(["message" => "Failed to add record."]);
    }
} elseif ($action == 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stu_id = $data['stu_id'];
    $oldImg = $object->getExistingImage('student', 'stu_id', $stu_id);
    if ($oldImg) {
        $object->deleteImage($oldImg);
    }
    $result = "DELETE FROM student WHERE stu_id = '$stu_id'";
    $query = $object->link()->query($result);
    if ($query) {
        echo json_encode(["message" => "Record deleted successfully!"]);
    } else {
        echo json_encode(["message" => "Failed to delete record."]);
    }
} elseif ($action == 'update') {
    $stu_id = isset($_POST['stu_id']) ? $_POST['stu_id'] : '';
    $stu_name = isset($_POST['stu_name']) ? $_POST['stu_name'] : '';
    $img_name = '';
    if (isset($_FILES['stu_img']) && $_FILES['stu_img']['error'] == 0) {
        $img_name = basename($_FILES['stu_img']['name']);
        $img_tmp_name = $_FILES['stu_img']['tmp_name'];
        $img_folder = __DIR__ . '/../assets/uploads/';
        if (move_uploaded_file($img_tmp_name, $img_folder . $img_name)) {
            $oldImg = $object->getExistingImage('student', 'stu_id', $stu_id);
            if ($oldImg) {
                $object->deleteImage($oldImg);
            }
        } else {
            echo json_encode(["message" => "Failed to move uploaded file."]);
            exit;
        }
    } else {
        // No new image uploaded, keep the existing one if it's not an empty string
        $img_name = isset($_POST['stu_img']) && $_POST['stu_img'] === '' ? '' : $object->getExistingImage('student', 'stu_id', $stu_id);
    }

    echo json_encode(["message" => $img_name]);
    if ($img_name) {
        $result = "UPDATE student SET stu_name = '$stu_name', stu_img = '$img_name' WHERE stu_id = '$stu_id'";
    } else {
        echo json_encode(["message" => "No Image"]);
        $result = "UPDATE student SET stu_name = '$stu_name', stu_img = '' WHERE stu_id = '$stu_id'";
    }

    $query = $object->link()->query($result);

    if ($query) {
        echo json_encode(["message" => "Record updated successfully!"]);
    } else {
        echo json_encode(["message" => "Failed to update record."]);
    }
} elseif ($action == 'removeimage') {
    $stu_id = isset($_POST['stu_id']) ? $_POST['stu_id'] : '';
    $oldImg = $object->getExistingImage('student', 'stu_id', $stu_id);
    if ($oldImg) {
        $object->deleteImage($oldImg);
        echo json_encode(["message" => "Image deleted successfully!"]);
    } else {
        echo json_encode(["message" => "No image found to delete."]);
    }
}
