
<!DOCTYPE html>
<html>
  <head>
    <title>test</title>
  </head>
  <body>
    <center>
      <h1>LunarLander<br><?php echo $title;?></h1>
      <!--
      <a href="http://localhost/UnityWebTest/index.php/WebTest/Login_Google/?id=<?php echo $id; ?>&pass=<?php echo $pass; ?>&mode=<?php echo $mode;?>">Login</a>
      -->
      <?php echo form_open('WebTest/Login_Google/?id='.$id.'&pass='.$pass.'&mode='.$mode); ?>
      <button name = "Login" style="background-color:Red"><font color='White'>Googleでログイン</font></button>
    </center>
  </body>
</html>
