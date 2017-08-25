
<!DOCTYPE html>
<html>
  <head>
    <title>LoginPage</title>
  </head>
  <body>
    <center>
      <h1>LunarLander<br><?php echo $title;?></h1>
      <?php
        if(isset($message))
        { ?>
          <font size=8 color=red><?php echo $message; ?></font>
        <?php
      }else{
        echo form_open('LLS/Login_Google/?id='.$id.'&pass='.$pass.'&mode='.$mode); ?>
      <button name = "Login" style="background-color:Red"><font color='White'>Googleでログイン</font></button>
    <?php } ?>
    </center>
  </body>
</html>
