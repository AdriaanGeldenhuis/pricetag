<?php
namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BannerController extends Controller
{
    public function index()
    {
        $this->layout('admin');
        $this->view('pages/banners/index', [
            'page_title' => 'Banners',
            'active_page' => 'banners',
        ]);
    }

    protected function view($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        $viewPath = ADMIN_PATH . '/Views/' . $view . '.php';
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        if (isset($this->data['_layout'])) {
            $layoutPath = ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            include $layoutPath;
            return;
        }
        echo $content;
    }

    protected function layout($layout)
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
