<!DOCTYPE html>
<html lang="en">
<head>
                <meta charset="UTF-8">
                <title>  ريكس بوست rix post | اضافة منشور</title>
                <meta name="keywords" content=",rixpost,rix post,ريكس بوست,abbasawad25,معلومات دينية,abbasawad,خواطر,,موقع خواطر دينية,موقع خواطر,معلوماصصةت عامة,">
               <meta name="description" content="موقع منشورات و خواطر دينية و خواطر ومعلومات عامة">
               <meta property="rix post ريكس بوست" content="rix post ريكس بوست"/>
               <link rel="shortcut icon" href="logo.png">
                <link rel="stylesheet" href="index.css">
</head>
<body>
                <center>
                        <div class="main">
                                <form action="insert.php" method="POST" enctype="multipart/form-data">
                                     <h1>rix post ريكس بوست</h1>
                                     <h2>اضافة منشور</h2>
                                     <img src="logo.png" alt="logo" style="width:245px">
                                     	<hlabel>العنوان</label>
                                     <input type="text" name="title">
                                     <br>
                                     	<hlabel>النوع</label>
                                     <input type="text" name="type">
                                     <br>
                                     <span> اكتب المنشور<span>
                                     <textarea rows="25" cols="45" name="body"></textarea>
                                     <br>
                                     	<br>
                                     	<br>
                                        
                                        <label for="file">حذف المحتوى</label>
                                        <button name="upload">رفع المنشور</button>
                                        <br><br>
                                        <a href="<?php echo $_SERVER['HTTP_HOST'] .'/'.'index.php';?>">عرض المنشورات</a>
                                </form>
                        </div>
                </center>
</body>
</html>
