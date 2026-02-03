<?php
/**
 * Base Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Core;

abstract class Controller
{
    protected $data = [];

    /**
     * Render a view
     */
    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);

        // Make data available to view
        extract($this->data);

        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found at '$viewPath'");
        }

        // Start output buffering for the view content
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // If layout is specified, wrap content in layout
        if (isset($this->data['_layout'])) {
            $layoutPath = APP_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }

        echo $content;
    }

    /**
     * Set the layout for the view
     */
    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        jsonResponse($data, $status);
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $status = 302): void
    {
        redirect($url, $status);
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!verifyCsrfToken($token)) {
            if (isAjax()) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            }
            flash('error', 'Session expired. Please try again.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
            return false;
        }

        return true;
    }

    /**
     * Validate request data
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $_POST[$field] ?? $_GET[$field] ?? null;
            $data[$field] = $value;

            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }

                $error = $this->validateRule($field, $value, $rule, $params);
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['_old_input'] = $_POST;
            $_SESSION['_errors'] = $errors;

            if (isAjax()) {
                $this->json(['errors' => $errors], 422);
            }
        }

        return ['valid' => empty($errors), 'data' => $data, 'errors' => $errors];
    }

    /**
     * Validate a single rule
     */
    private function validateRule(string $field, $value, string $rule, array $params): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    return "$label is required";
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "$label must be a valid email address";
                }
                break;

            case 'min':
                if ($value && strlen($value) < (int) $params[0]) {
                    return "$label must be at least {$params[0]} characters";
                }
                break;

            case 'max':
                if ($value && strlen($value) > (int) $params[0]) {
                    return "$label must not exceed {$params[0]} characters";
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    return "$label must be a number";
                }
                break;

            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return "$label must be an integer";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($_POST[$confirmField] ?? null)) {
                    return "$label confirmation does not match";
                }
                break;

            case 'unique':
                if ($value) {
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    $except = $params[2] ?? null;

                    $db = Database::getInstance();
                    $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
                    $bindings = [$value];

                    if ($except) {
                        $sql .= " AND id != ?";
                        $bindings[] = $except;
                    }

                    $stmt = $db->prepare($sql);
                    $stmt->execute($bindings);

                    if ($stmt->fetchColumn() > 0) {
                        return "$label already exists";
                    }
                }
                break;

            case 'exists':
                if ($value) {
                    $table = $params[0];
                    $column = $params[1] ?? 'id';

                    $db = Database::getInstance();
                    $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
                    $stmt->execute([$value]);

                    if ($stmt->fetchColumn() === 0) {
                        return "$label does not exist";
                    }
                }
                break;

            case 'in':
                if ($value && !in_array($value, $params, true)) {
                    return "$label must be one of: " . implode(', ', $params);
                }
                break;

            case 'regex':
                if ($value && !preg_match($params[0], $value)) {
                    return "$label format is invalid";
                }
                break;
        }

        return null;
    }

    /**
     * Check if there are validation errors
     */
    protected function hasErrors(): bool
    {
        return !empty($_SESSION['_errors']);
    }

    /**
     * Get validation errors
     */
    protected function getErrors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!auth()) {
            if (isAjax()) {
                $this->json(['error' => 'Unauthorized'], 401);
            }
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
        }
    }

    /**
     * Require admin access
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();

        if (!isAdmin()) {
            if (isAjax()) {
                $this->json(['error' => 'Forbidden'], 403);
            }
            $this->redirect('/');
        }
    }
}
