<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title;?></title>
  </head>
  <body>
    <center>
    <h1><?php echo $title;?></h1>
    <?php if($username!=""){?>
    <table border=1>
      <tr>
        <td>PlayerName:<b><?php echo $username; ?></b></td>
      </tr>
    </table>
    <?php } ?>
    <h2><?php echo $result;?></h2>
    <?php if($dialog){?>
      <?php echo form_open('LLS/Inherit');?>
      <button name="YesNo" value="0">No</button>　　<button name = "YesNo" value="1">Yes</button>
    </form>
    <?php } ?>
  </center>
</body>
</html>
