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

  public function GetPlayLog()
  {
    $user = array(
      'ID' => $_GET['id'],
    );

    $query = $this->db->order_by('NUM','desc')
              ->get_where(TABLE_NAME_PLAYDATA,$user);

    // ハイスコアとハイクリアステージを取得する。
    $highScore = 0;
    $highStage = 0;
    foreach ($query->result_array() as $row) {
      if($highScore < $row['SCORE'])
      {
        $highScore = $row['SCORE'];
      }
      if($highStage < $row['CLEARSTAGE'])
      {
        $highStage = $row['CLEARSTAGE'];
      }
    }

    // 10回分のプレイログを取得する。
    for($i = 0;$i<10 && $i<$query->num_rows();$i++){
      $log[$i] = array(
        'score' => $query->row($i)->SCORE,
        'stage' => $query->row($i)->CLEARSTAGE,
        'date' => $query->row($i)->PLAYDATE,
      );
    }
    $data = array(
      'message' => 'GetPlayLog',
      'High_Score' => $highScore,
      'High_ClearStage' => $highStage,
      'LogData' =>$log,
    );


    return json_encode($data);
  }
}
