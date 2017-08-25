<?php

//require_once "HTTP/Request2.php";

class LLS extends CI_Controller{

  // コンストラクタ
  public function __construct()
  {
    parent::__construct();
    $this->load->model('UserAccount');
    $this->load->model('PlayData');
    $this->load->model('Get_Google');
    $this->load->helper('url_helper');
  }

  public function top()
  {
    $this->load->helper('form');

    $data = $this->Get_Google->setUserData();

    $this->load->view('lls/top',$data);
  }

  // 認証ページ
  public function Login_Google()
  {
    $url = $this->Get_Google->getOAuthURL();
    // Google認証ページを開く
    header("Location: " . $url);
  }
  // 認証コールバック　トークン取得からユーザ情報取得まで
  public function Login_GoogleCallBack()
  {
    $this->load->helper('form');

    // アクセストークンの取得
    $access_token = $this->Get_Google->getAccessToken();
    // Googleアカウントのユーザー情報を取得
    $userInfo = $this->Get_Google->getUserInfo($access_token);

    // モードによって処理を変える。
    $data = $this->UserAccount->ChackEventMode($userInfo);

    $this->load->view('lls/result',$data);
  }

  public function SignUp()
  {
    echo $this->UserAccount->AutoSignUp();
  }

  public function Login()
  {
    echo $this->UserAccount->AutoLogin();
  }

  public function Delete()
  {
    echo $this->UserAccount->Delete();
  }

  public function Disconnect()
  {
    $this->UserAccount->Disconnect();
  }
  public function Inherit()
  {
    $data = $this->UserAccount->Inherit();
    $this->load->view('lls/inherit',$data);
  }

  public function CheckInheriting()
  {
    echo $this->UserAccount->CheckInheriting();
  }

  public function CheckInheritSetting()
  {
    echo $this->UserAccount->CheckInheritSetting();
  }

  /*------------------------------------------------------------------*/
  public function SendPlayData()
  {
    echo $this->PlayData->AddPlayData();
  }
  public function PlayLog()
  {
    echo $this->PlayData->GetPlayLog();
  }
  public function ScoreRanking()
  {
    echo $this->PlayData->GetScoreRanking();
  }

  public function TestData()
  {
    $this->load->helper('form');

    $data['title'] = "テストデータ作成";
     $this->PlayData->InsertTestData();

    $this->load->view('lls/TestData',$data);
  }
  /*------------------------------------------------------------------*/
}
