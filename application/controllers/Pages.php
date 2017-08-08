<?
class Pages extends CI_Controller{
  public function view($page = 'home')
  {
    if(!file_exists(APPATH.'views/pages/'.$page.'.php'))
    {
      // ページがありません。
      show_404();
    }

    $data['title'] = ucfirst($page); // 頭文字を大文字にする。

    $this->load->view('templates/header', $data);
    $this->load->view('pages/'.$page, $data);
    $this->load->view('templates/footer', $data);
  }
}
