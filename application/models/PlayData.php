<?php
define('TABLE_NAME_PLAYDATA','playdata');
//define('TABLE_NAME_USERS','users');

class PlayData extends CI_Model
{
  /// コンストラクタ
  public function __construct()
  {
    $this->load->database();
  }

  // 新規プレイデータをDBに挿入する
  public function AddPlayData()
  {
    $data = array(
      'ID' => $_GET["id"],
      'SCORE' => $_GET["score"],
      'CLEARSTAGE' => $_GET["stage"],
      'PLAYDATE' => date("Y/m/d"),
    );

    $this->db->insert(TABLE_NAME_PLAYDATA,$data);


    return $this->db->error();
  }
}
