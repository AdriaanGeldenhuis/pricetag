<?php
namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BannerController extends Controller
{
    public function index()
    {
        $banners = [];

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM banners ORDER BY location, sort_order ASC");
            $result = $stmt->fetchAll();
            if ($result) {
                $banners = $result;
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        $this->layout('admin');
        $this->view('pages/banners/index', [
            'page_title' => 'Banners & Sliders',
            'active_page' => 'banners',
            'banners' => $banners,
        ]);
    }

    public function create()
    {
        $this->layout('admin');
        $this->view('pages/banners/form', [
            'page_title' => 'Add Banner',
            'active_page' => 'banners',
            'banner' => null,
        ]);
    }

    public function store()
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("INSERT INTO banners (location, title, image, url, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? '',
            $_POST['image'] ?? '',
            $_POST['url'] ?? '',
            isset($_POST['is_active']) ? 1 : 0
        ]);

        flash('success', 'Banner created');
        $this->redirect('/admin/banners');
    }

    public function edit($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/banners/form', [
            'page_title' => 'Edit Banner',
            'active_page' => 'banners',
            'banner' => $banner,
        ]);
    }

    public function update($id)
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE banners SET location=?, title=?, image=?, url=?, is_active=? WHERE id=?");
        $stmt->execute([
            $_POST['location'] ?? 'hero',
            $_POST['title'] ?? '',
            $_POST['image'] ?? '',
            $_POST['url'] ?? '',
            isset($_POST['is_active']) ? 1 : 0,
            $id
        ]);

        flash('success', 'Banner updated');
        $this->redirect('/admin/banners');
    }

    public function destroy($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Banner deleted');
        $this->redirect('/admin/banners');
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
