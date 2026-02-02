<?php
/**
 * Contact Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ContactController extends Controller
{
    public function index(): void
    {
        $this->layout('main');
        $this->view('pages/contact', [
            'meta_title' => 'Contact Us | ' . config('app.name'),
            'meta_description' => 'Get in touch with us. We\'d love to hear from you.',
        ]);
    }

    public function submit(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'email' => 'required|email',
            'message' => 'required|min:10|max:5000',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input and try again.');
            $this->redirect('/contact');
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("
            INSERT INTO contact_submissions (name, email, phone, subject, message, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'] ?? null,
            $_POST['subject'] ?? null,
            $_POST['message'],
            clientIp(),
        ]);

        flash('success', 'Thank you for your message! We\'ll get back to you soon.');
        $this->redirect('/contact');
    }
}
