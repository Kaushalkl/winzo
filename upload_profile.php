<?php
require_once 'db.php';
session_start();
if(empty($_SESSION['user_id'])){echo json_encode(['success'=>false,'message'=>'Not logged in']);exit;}
$uid=intval($_SESSION['user_id']);
if(isset($_FILES['profile'])){
  $ext=strtolower(pathinfo($_FILES['profile']['name'],PATHINFO_EXTENSION));
  if(!in_array($ext,['jpg','jpeg','png','gif'])){echo json_encode(['success'=>false,'message'=>'Invalid file type']);exit;}
  $file="uploads/profile_$uid.$ext";
  move_uploaded_file($_FILES['profile']['tmp_name'],$file);
  $conn->query("UPDATE users SET profile_image='$file' WHERE id=$uid");
  echo json_encode(['success'=>true,'url'=>$file]);exit;
}
if(isset($_POST['avatar'])){
  $avatar=$_POST['avatar'];
  $conn->query("UPDATE users SET profile_image='".$conn->real_escape_string($avatar)."' WHERE id=$uid");
  echo json_encode(['success'=>true,'url'=>$avatar]);exit;
}
echo json_encode(['success'=>false,'message'=>'No file']);
?>
