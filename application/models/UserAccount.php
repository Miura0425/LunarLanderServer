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
    // ユーザー情報を受信
    $UserInfo = array(
      'ID' => $_GET["id"],
      'PASS' =>$_GET["pass"],
      'NAME' => $_GET["name"],
    );
    // 返却用のデータ
    $data = array(
      'result' => "",
      'session_id' => "",
      'num'=>"",
      'name' => "",
    );

    // ID の重複チェック
    $check = $this->db->get_where(TABLE_NAME_USERS,array('ID'=>$UserInfo['ID']));
    if($check->num_rows() != 0)
    {
      $data['result'] = "Error";
      return json_encode($data);
    }
    // データベースに追加
    $this->db->insert(TABLE_NAME_USERS,$UserInfo);
    // セッションに必要なデータを書き込む
    $this->session->set_userdata('id',$UserInfo['ID']);
    $this->session->set_userdata('pass',$UserInfo['PASS']);

    // 返却データに値を設定
    $data['result'] = "SignUp";
    $data['session_id'] = $this->session->session_id;
    $data['num'] = $this->db->get_where(TABLE_NAME_USERS,array('ID'=>$UserInfo['ID']))->row('NUM');
    $data['name'] = $this->session->userdata('name');


    return json_encode($data);
  }

// オートログインテスト
  public function AutoLogin()
  {
    // ユーザー情報を受信
    $UserInfo = array(
      'ID' => $_GET["id"],
      'PASS' => $_GET["pass"],
      'DELETE_FLAG' => false,
    );
    // 返却用のデータ
    $data = array(
      'message' => "",
      'session_id' => "",
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
    // セッションに必要なデータを書き込む
    $this->session->set_userdata('id',$UserInfo['ID']);
    $this->session->set_userdata('pass',$UserInfo['PASS']);

    // 返却データに値を設定
    $data['message'] = "Login";
    $data['session_id'] = $this->session->session_id;
    $data['num'] = $check->row('NUM');
    $data['name'] = $check->row('NAME');

    return json_encode($data);
  }

  // 削除
  public function Delete()
  {
    // 削除処理
    $UserData = array(
      'ID'=>$_GET['id'],
      'PASS'=>$_GET['pass'],
      'DELETE_FLAG' => false,
    );
    $this->db->set("DELETE_FLAG",true)->where($UserData)->update(TABLE_NAME_USERS);

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
    $UserData = array(
      'ID' => $this->session->userdata('id'),
      'PASS' => $this->session->userdata('pass'),
      'DELETE_FLAG' => false,
    );

    $check = $this->db->get_where(TABLE_NAME_USERS,array('GoogleID'=>$Google_Data->id,'DELETE_FLAG' => false));
    if($check->row('ID') == $UserData['ID'])
    {
      $data['result'] = "既に登録済み";
      return $data;
    }else if($check->row('GoogleID') != NULL){
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
    $data['dialog'] = true;
    return $data;
  }
  public function Inherit()
  {
    $data['title'] = "引き継ぎ";
    if($this->input->post('YesNo')==1){

      // 引き継ぎ元のレコードの削除フラグを立てる
      $this->db->set("DELETE_FLAG",true)->where("ID",$this->session->userdata('base_id'))->update(TABLE_NAME_USERS);

      // 引き継ぎ先のレコードの情報を引き継ぎ元の情報で更新する。
      $UserData = array(
        'ID' => $this->session->userdata('base_id'),
        'PASS' => $this->session->userdata('base_pass'),
        'NAME' => $this->session->userdata('base_name'),
        'GoogleID' => $this->session->userdata('base_GoogleID'),
      );
      $this->db->set($UserData);
      $this->db->where('ID', $this->session->userdata('id'));
      $inheriting = $this->db->update(TABLE_NAME_USERS);

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
       'NUM' => $_GET["num"],
       'DELETE_FLAG' => false,
     );

    $data = array(
      'message' => "",
      'id' => "",
      'pass'=>"",
      'name' => "",
    );

    $check = $this->db->get_where(TABLE_NAME_USERS,$UserData);
    if($check->num_rows() <= 0 || $check->row('GoogleID') == NULL)
    {
      $data['message'] = "Failed";
      return json_encode($data);
    }

    $data['message'] = "Complete";
    $data['id'] = $check->row('ID');
    $data['pass'] = $check->row('PASS');
    $data['name'] = $check->row('NAME');

    return json_encode($data);
  }
  /// 引き継ぎ設定が完了したかどうか
  public function CheckInheritSetting()
  {
    $UserData = array(
      'ID' => $_GET["id"],
      'PASS' => $_GET["pass"],
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
