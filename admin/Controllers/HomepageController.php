<?php
namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomepageController extends Controller
{
    public function index()
    {
        $sections = [];

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM home_sections ORDER BY sort_order ASC");
            $result = $stmt->fetchAll();
            if ($result) {
                $sections = $result;
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        $this->layout('admin');
        $this->view('pages/homepage/index', [
            'page_title' => 'Homepage Builder',
            'active_page' => 'homepage',
            'sections' => $sections,
        ]);
    }

    public function store()
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("INSERT INTO home_sections (type, title, sort_order, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([
            $_POST['type'] ?? 'custom',
            $_POST['title'] ?? '',
            0
        ]);

        flash('success', 'Section added');
        $this->redirect('/admin/homepage');
    }

    public function edit($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/homepage/edit', [
            'page_title' => 'Edit Section',
            'active_page' => 'homepage',
            'section' => $section,
        ]);
    }

    public function update($id)
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE home_sections SET title=?, is_active=? WHERE id=?");
        $stmt->execute([
            $_POST['title'] ?? '',
            isset($_POST['is_active']) ? 1 : 0,
            $id
        ]);

        flash('success', 'Section updated');
        $this->redirect('/admin/homepage');
    }

    public function destroy($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM home_sections WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Section deleted');
        $this->redirect('/admin/homepage');
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
