
<!DOCTYPE html>
<html>
  <head>
    <title>LoginPage</title>
  </head>
  <body>
    <center>
      <h1>LunarLander<br><?php echo $title;?></h1>
      <?php echo form_open('LLS/Login_Google/?id='.$id.'&pass='.$pass.'&mode='.$mode); ?>
      <button name = "Login" style="background-color:Red"><font color='White'>Googleでログイン</font></button>
    </center>
  </body>
</html>
