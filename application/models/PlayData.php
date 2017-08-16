<?php
define('TABLE_NAME_PLAYDATA','playdata');
define('RANKING_RECORD_NUM',100);

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
        'id' => $query->row($i)->ID,
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

  public function GetScoreRanking()
  {
    $query = $this->db->query('SELECT users.NAME,playdata.SCORE,playdata.CLEARSTAGE FROM users,playdata
                              WHERE users.ID = playdata.ID AND users.DELETE_FLAG = 0 AND SCORE in
                              (SELECT max(SCORE) FROM playdata GROUP BY ID) ORDER BY SCORE desc');

    if($query->num_rows() ==0){
      $data['message'] = "NoData";

      return $data;
    }

    for($i = 0;$i<$query->num_rows();$i++){
      $rank = $i+1;
      if($i>0 && $query->row($i)->SCORE == $query->row($i-1)->SCORE)
      {
        $rank = $rankingdata[$i-1]['rank'];
      }


      $rankingdata[$i] = array(
        'rank' => $rank,
        'name' => $query->row($i)->NAME,
        'score' => $query->row($i)->SCORE,
        'stage' => $query->row($i)->CLEARSTAGE,
      );
    }

    $data = array(
      'message' => "GetData",
      'Data' => $rankingdata,
    );

    return json_encode($data);
  }

  public function InsertTestData()
  {
    $user = $this->db->get_where(TABLE_NAME_USERS,array('DELETE_FLAG'=>0));

    for($i = 0;$i<$user->num_rows();$i++)
    {
      for($j=0;$j<4;$j++){
        $TestData['ID'] = $user->row($i)->ID;
        $TestData['SCORE'] = rand(100,1000);
        $TestData['CLEARSTAGE'] = rand(2,10);
        $TestData['PLAYDATE'] = date("Y/m/d");

        $this->db->insert(TABLE_NAME_PLAYDATA,$TestData);
      }
    }
  }
}
