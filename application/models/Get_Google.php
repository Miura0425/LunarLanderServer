<?php
define('CLIENT_ID', '872171358764-jmdri9u4erk4gtl1h5in5oiqhu34b9eq.apps.googleusercontent.com');
define('CLIENT_SECRET', '5IcR6XK6NKXsP7dulU9sjy3-');
define('REDIRECT_URL',"/LLS/Login_GoogleCallBack");

require_once "HTTP/Request2.php";

class Get_Google extends CI_Model
{
  /// コンストラクタ
  public function __construct()
  {
    $this->load->library('session');
  }

  public function setUserData()
  {
    $data["id"]   = "";
    $data["pass"] = "";
    $data["mode"] = "";

    if(isset($_GET["id"]) && isset($_GET["pass"]) && isset($_GET["mode"]))
    {
      $data["id"]   = $_GET["id"];
      $data["pass"] = $_GET["pass"];
      $data["mode"] = $_GET["mode"];
    }else if($this->session->has_userdata("id") && $this->session->has_userdata("pass") && $this->session->has_userdata("mode")) {
      $data["id"]   = $this->session->userdata("id");
      $data["pass"] = $this->session->userdata("pass");
      $data["mode"] = $this->session->userdata("mode");
    }

    if($data["mode"] == "GET"){
      $data["title"] = "アカウント引き継ぎ";
    }else if($data["mode"] == "SET"){
      $data["title"] = "アカウント引き継ぎ設定";
    }else {
      $data["title"] = "Error";
      $data["message"] = "通信エラー　ゲームに戻ってやり直してください。";
      // セッションの削除
      $this->session->sess_destroy();
      return $data;
    }

    $UserData = array(
      "id" => $data["id"],
      "pass" => $data["pass"],
      "mode" => $data["mode"],
    );

    $this->session->set_userdata($UserData);
    return $data;
  }

  /// 認証ページのURLを返す。
  public function getOAuthURL()
  {
    $baseURL = 'https://accounts.google.com/o/oauth2/auth?';
    $scope = array(
    'https://www.googleapis.com/auth/userinfo.profile', // プロフィール
    'https://www.googleapis.com/auth/userinfo.email',   // メールアドレス
    );

    // 認証用URL生成
    $authURL = $baseURL .
    'scope=' . urlencode(implode(' ', $scope)) .
    '&client_id=' . CLIENT_ID.
    '&response_type=code' .
    '&redirect_uri=' . site_rul().REDIRECT_URL;

    return $authURL;
  }

  public function getAccessToken()
  {
    $code = $_REQUEST['code'];
    // access_token 取得
    $baseURL = 'https://accounts.google.com/o/oauth2/token';
    $params = array(
        'code'          => $code,
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'redirect_uri'  => REDIRECT_URL,
        'grant_type'    => 'authorization_code'
    );
    $req = new Http_Request2($baseURL, Http_Request2::METHOD_POST);
    $req->addPostParameter($params);
    $req->setAdapter('curl');
    $res = $req->send();
    $response = json_decode($res->getBody());

    // 失敗した場合はやり直し。
    if(isset($response->error)){
      // トップページに戻る。
      echo 'エラー発生。<a href="'.site_url().'">最初からやりなおす</a>';
      exit;
    }
    // アクセストークンを返す。
    return $response->access_token;
  }
  public function getUserInfo($access_token)
  {
    // ユーザ情報取得
    $userInfo = json_decode(
        file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?'.
        'access_token=' . $access_token)
    );

    return $userInfo;


  }

  public function urltest()
  {
    echo site_url().REDIRECT_URL;
  }
}
