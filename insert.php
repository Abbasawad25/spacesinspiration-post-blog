<?php

   include('php/config.php');
$a = $_POST['upload'];
   if(isset($a)) {
                   $id = 1;
                   $body = $_POST['body'];
                   $title = $_POST['title'];
                   $category = "culture";
          
                $sql = "INSERT INTO posts (user_id,body,title,category)
VALUES ('$id', '$body','$title','$category')";
//

//
if (getDB()->query($sql) === TRUE) {
  echo "تم اضافة المنشور بنجاح";
  echo "<script>alert('تم اضافة المنشور بنجاح')</script>";
  header('REFRESH:1;url=post.php');
$last_id = getDB()->name;
  echo "New record created successfully. Last inserted ID is: " . $last_id;
} else {
  echo "Error: " . $sql . "<br>" . getDB()->error;
  echo "<script>alert('تعذر اضافة المنشور')</script>";
  header('REFRESH:1;url=post.php');
}
   }