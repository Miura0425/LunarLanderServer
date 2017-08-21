<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title;?></title>
  </head>
  <body>
    <center>
    <h1><?php echo $title;?></h1>
    <table>
      <tr>
        <td>Name:</td>
        <td><?php echo $name; ?></td>
      </tr>
      <tr>
        <td>email:</td>
        <td><?php echo $email; ?></td>
      </tr>
    </table>
    <h2><?php echo $result;?></h2>
    <?php if($dialog){?>
      <?php echo form_open('LunarLanderServer/Inherit');?>
      <button name="YesNo" value="0">No</button>　　<button name = "YesNo" value="1">Yes</button>
    </form>
    <?php } ?>
  </center>
</body>
</html>
