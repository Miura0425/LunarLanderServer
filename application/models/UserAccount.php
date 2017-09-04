<?php
define('TABLE_NAME_USERS','users'); // テーブル名 ユーザー

class UserAccount extends CI_Model
{
  /// コンストラクタ
  public function __construct()
  {
    $this->load->database();
    $this->load->library('session');
  }

  /// オートサインアップテスト
  public function AutoSignUp()
  {
    // データが存在しているかチェック
    if(!isset($_GET["id"]) || !isset($_GET["pass"]) || !isset($_GET["name"]))
    {
        $data['message'] = "Error";
        return json_encode($data);
    }

    // ユーザー情報を受信
    $UserInfo = array(
      'ID' => $_GET["id"],
      'PASS' =>$_GET["pass"],
      'NAME' => $_GET["name"],
    );
    // 返却用のデータ
    $data = array(
      'message' => "",
      'num'=>"",
      'name' => "",
    );

    // ID の重複チェック
    $check = $this->db->get_where(TABLE_NAME_USERS,array('ID'=>$UserInfo['ID']));
    if($check->num_rows() != 0)
    {
      $data['message'] = "Error";
      return json_encode($data);
    }
    // データベースに追加
    $this->db->insert(TABLE_NAME_USERS,$UserInfo);

    // 返却データに値を設定
    $data['message'] = "SignUp";
    $data['num'] = $this->db->get_where(TABLE_NAME_USERS,array('ID'=>$UserInfo['ID']))->row('NUM');
    $data['name'] = $UserInfo['NAME'];

    // セッションにID/PASS/NAMEを追加する
    $this->session->set_userdata("id",$UserInfo['ID']);
    $this->session->set_userdata("pass",$UserInfo['PASS']);
    $this->session->set_userdata("name",$UserInfo['NAME']);
    $this->session->set_userdata("num",$data['num']);

    return json_encode($data);
  }

// オートログインテスト
  public function AutoLogin()
  {
    $ID = "";
    $PASS = "";

    // データが存在するかチェック
    if(isset($_GET["id"]) && isset($_GET["pass"]))
    {
      $ID = $_GET["id"];
      $PASS = $_GET["pass"];
    }else {
      $data["message"] = "Error";
      return json_encode($data);
    }

    // ユーザー情報を受信
    $UserInfo = array(
      'ID' => $ID,
      'PASS' => $PASS,
      'DELETE_FLAG' => false,
    );
    // 返却用のデータ
    $data = array(
      'message' => "",
      'num'=>"",
      'name' => "",
    );

    // ユーザーレコードの検索
    $check = $this->db->get_where(TABLE_NAME_USERS,$UserInfo);
    if($check->num_rows() == 0)
    {
      $data['message'] = "Error";
      return json_encode($data);
    }

    // 返却データに値を設定
    $data['message'] = "Login";
    $data['num'] = $check->row('NUM');
    $data['name'] = $check->row('NAME');

    // セッションにID/PASS/NAMEを追加
    $this->session->set_userdata("id",$check->row('ID'));
    $this->session->set_userdata("pass",$check->row('PASS'));
    $this->session->set_userdata("name",$check->row('NAME'));
    $this->session->set_userdata("num",$check->row('NUM'));

    return json_encode($data);
  }

  // 削除
  public function Delete()
  {
    $ID = "";
    $PASS = "";

    // データが存在しているかチェック
    if(isset($_GET['id']) && isset($_GET['pass']))
    {
      $ID = $_GET['id'];
      $PASS = $_GET['pass'];
    }else if($this->session->has_userdata("id") && $this->session->has_userdata("pass"))
    {
      $ID = $this->session->userdata("id");
      $PASS = $this->session->userdata("pass");
    }else {
      $data['message'] = "";
      return json_encode($data);
    }

    // 削除処理
    $UserData = array(
      'ID'=>$ID,
      'PASS'=>$PASS,
      'DELETE_FLAG' => false,
    );
    $this->db->set("DELETE_FLAG",true)->where($UserData)->update(TABLE_NAME_USERS);

    // 切断処理
    $this->Disconnect();

    $ResultData = array(
      'message' => "Delete Complete",
    );
    return json_encode($ResultData);
  }

  // 切断・ログアウト
  public function Disconnect()
  {
    // セッションの削除
    $this->session->sess_destroy();
  }

  /// モードを確認して、処理を実行する。
  public function ChackEventMode($Google_Data)
  {
    if($this->session->userdata('mode') == "SET")
    {
      // 引き継ぎGoogleIDの設定
      $data = $this->SetGoogleID($Google_Data);
    }else if($this->session->userdata('mode') == "GET")
    {
      // 引き継ぎ処理の実行
      $data = $this->Inheriting($Google_Data);
    }
    return $data;
  }
  /// 引き継ぎGoogleIDの設定
  private function SetGoogleID($Google_Data)
  {
    $data['title'] = "引き継ぎ設定";
    $data['dialog'] = false;
    $data['username'] = "";
    $UserData = array(
      'ID' => $this->session->userdata('id'),
      'PASS' => $this->session->userdata('pass'),
      'DELETE_FLAG' => false,
    );

    $check = $this->db->get_where(TABLE_NAME_USERS,array('GoogleID'=>$Google_Data->id,'DELETE_FLAG' => false));
    if($check->row('ID') == $UserData['ID'])
    {
      $data['username'] = $check->row('NAME');
      $data['result'] = "既に登録済み";
      return $data;
    }else if($check->row('GoogleID') != NULL){
      $data['username'] = $check->row('NAME');
      $data['result'] = "このGoogleアカウントには既に別のアカウントが登録されています。";
      return $data;
    }

    // IDとパスを検索する。
    $query = $this->db->get_where(TABLE_NAME_USERS,$UserData);
    // GoogleIDが空
    if($query->row("GoogleID") == NULL)
    {
      $this->db->set("GoogleID",$Google_Data->id);
      $this->db->where($UserData);
      $this->db->update(TABLE_NAME_USERS);

      $data['result'] = "登録完了";
      return $data;
    }
    // 違うGoogleID
    else {
        $data['result'] = "このゲームアカウントには既に別のGoogleアカウントが登録されています。";
        return $data;
    }
  }
  /// 引き継ぎ処理
  private function Inheriting($Google_Data)
  {
    $data['title'] = "引き継ぎ";
    $data['dialog'] = false;
    $data['username'] = "";

    $BaseData = $this->db->get_where(TABLE_NAME_USERS,array('GoogleID'=>$Google_Data->id,'DELETE_FLAG' => false));
    if($BaseData->num_rows() == 0)
    {
      $data['result'] = "登録されたアカウントが存在しません<br>ゲームに戻ってください";
      return $data;
    }
    if($this->session->userdata('id') == $BaseData->row('ID'))
    {
      $data['result'] = "同一アカウントのため引き継ぎをキャンセルします<br>ゲームに戻ってください";
      return $data;
    }

    $BaseUser = array(
      'base_id' => $BaseData->row('ID'),
      'base_pass' => $BaseData->row('PASS'),
      'base_name' => $BaseData->row('NAME'),
      'base_GoogleID' => $BaseData->row('GoogleID'),
    );
    $this->session->set_userdata($BaseUser);

    $data['result'] = "引き継ぎ可能です<br>引き継ぎを行いますか？";
    $data['username'] = $BaseUser['base_name'];
    $data['dialog'] = true;
    return $data;
  }
  public function Inherit()
  {
    $data['title'] = "引き継ぎ";
    if($this->input->post('YesNo')==1){

      // 引き継ぎ元のINHERIT_IDに引き継ぎ先IDを登録
      $this->db->set("INHERIT_ID",$this->session->userdata('base_id'))->where(array("ID"=>$this->session->userdata('id'),"DELETE_FLAG"=>false))->update(TABLE_NAME_USERS);

      // 引き継ぎ先のレコードの情報を引き継ぎ元の情報で更新する。
      /*
      $UserData = array(
        'ID' => $this->session->userdata('base_id'),
        'PASS' => $this->session->userdata('base_pass'),
        'NAME' => $this->session->userdata('base_name'),
        'GoogleID' => $this->session->userdata('base_GoogleID'),
      );
      $this->db->set($UserData);
      $this->db->where('ID', $this->session->userdata('id'));
      $inheriting = $this->db->update(TABLE_NAME_USERS);
      */
      $data['result'] = "引き継ぎ完了<br>ゲームに戻ってください";
    }
    else{
      $data['result'] = "引き継ぎキャンセル<br>ゲームに戻ってください";
    }
    return $data;
  }
  public function CheckInheriting()
  {
    $UserData =array(
       'NUM' => $this->session->userdata("num"),
       'DELETE_FLAG' => false,
     );

    $data = array(
      'message' => "",
      'id' => "",
      'pass'=>"",
      'name' => "",
      'num' => 0,
    );

    $check = $this->db->get_where(TABLE_NAME_USERS,$UserData);
    if($check->num_rows() <= 0 || $check->row('INHERIT_ID') == NULL)
    {
      $data['message'] = "Failed";
      return json_encode($data);
    }

    $InheritData  = $this->db->get_where(TABLE_NAME_USERS,array("ID"=>$check->row('INHERIT_ID'),"DELETE_FLAG"=>false));
    $this->db->set("INHERIT_ID",NULL)->where(array("ID"=>$check->row('ID'),"DELETE_FLAG"=>false))->update(TABLE_NAME_USERS);


    $data['message'] = "Complete";
    $data['id'] = $InheritData->row('ID');
    $data['pass'] = $InheritData->row('PASS');
    $data['name'] = $InheritData->row('NAME');
    $data['num'] = $InheritData->row('NUM');



    $this->Disconnect();

    return json_encode($data);
  }
  /// 引き継ぎ設定が完了したかどうか
  public function CheckInheritSetting()
  {
    $ID = "";
    $PASS = "";
    if(isset($_GET["id"]) && isset($_GET["pass"]))
    {
      $ID = $_GET["id"];
      $PASS = $_GET["pass"];
    }else if($this->session->has_userdata("id") && $this->session->has_userdata("pass")){
      $ID = $this->session->userdata("id");
      $PASS =$this->session->userdata("pass");
    }else {
      $data['message'] = "Error";
      return json_encode($data);
    }

    $UserData = array(
      'ID' => $ID,
      'PASS' => $PASS,
      'DELETE_FLAG' => false,
    );
    $data = array(
      'message' =>"",
    );
    $check = $this->db->get_where(TABLE_NAME_USERS,$UserData);
    if($check->row('GoogleID') != NULL)
    {
      $data['message'] = "Complete";
    }else {
      $data['message'] = "Failed";
    }
    return json_encode($data);
  }
}
