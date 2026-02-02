<?php
/**
 * View Helper
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Core;

class View
{
    private static array $sections = [];
    private static array $sectionStack = [];
    private static ?string $currentSection = null;

    /**
     * Render a view file
     */
    public static function render(string $view, array $data = []): string
    {
        extract($data);

        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found");
        }

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    /**
     * Include a partial view
     */
    public static function partial(string $view, array $data = []): void
    {
        echo self::render($view, $data);
    }

    /**
     * Include a component
     */
    public static function component(string $name, array $props = []): void
    {
        $viewPath = APP_PATH . '/Views/components/' . str_replace('.', '/', $name) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Component '$name' not found");
        }

        extract($props);
        include $viewPath;
    }

    /**
     * Start a section
     */
    public static function section(string $name): void
    {
        self::$sectionStack[] = $name;
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * End current section
     */
    public static function endSection(): void
    {
        if (empty(self::$sectionStack)) {
            throw new \RuntimeException('No section to end');
        }

        $name = array_pop(self::$sectionStack);
        self::$sections[$name] = ob_get_clean();
        self::$currentSection = end(self::$sectionStack) ?: null;
    }

    /**
     * Get section content
     */
    public static function getSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Yield section content (alias for getSection)
     */
    public static function yield(string $name, string $default = ''): string
    {
        return self::getSection($name, $default);
    }

    /**
     * Extend a layout
     */
    public static function extend(string $layout): void
    {
        // This is called at the start of a view that extends a layout
        // We'll capture everything and then render the layout
    }

    /**
     * Include scripts section
     */
    public static function scripts(): void
    {
        echo self::getSection('scripts');
    }

    /**
     * Include styles section
     */
    public static function styles(): void
    {
        echo self::getSection('styles');
    }

    /**
     * Escape HTML
     */
    public static function e(?string $value): string
    {
        return e($value);
    }

    /**
     * Format price
     */
    public static function price(float $amount): string
    {
        return formatPrice($amount);
    }

    /**
     * Format date
     */
    public static function date(string $date, string $format = 'd M Y'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function datetime(string $date, string $format = 'd M Y H:i'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Generate pagination HTML
     */
    public static function pagination(array $pagination, string $baseUrl): string
    {
        if ($pagination['last_page'] <= 1) {
            return '';
        }

        $current = $pagination['current_page'];
        $last = $pagination['last_page'];

        $html = '<nav class="pagination" aria-label="Pagination">';
        $html .= '<ul class="pagination__list">';

        // Previous
        if ($current > 1) {
            $html .= '<li><a href="' . $baseUrl . '?page=' . ($current - 1) . '" class="pagination__link pagination__link--prev" aria-label="Previous page">&laquo;</a></li>';
        } else {
            $html .= '<li><span class="pagination__link pagination__link--disabled">&laquo;</span></li>';
        }

        // Page numbers
        $range = 2;
        for ($i = 1; $i <= $last; $i++) {
            if ($i === 1 || $i === $last || ($i >= $current - $range && $i <= $current + $range)) {
                if ($i === $current) {
                    $html .= '<li><span class="pagination__link pagination__link--active" aria-current="page">' . $i . '</span></li>';
                } else {
                    $html .= '<li><a href="' . $baseUrl . '?page=' . $i . '" class="pagination__link">' . $i . '</a></li>';
                }
            } elseif ($i === $current - $range - 1 || $i === $current + $range + 1) {
                $html .= '<li><span class="pagination__ellipsis">&hellip;</span></li>';
            }
        }

        // Next
        if ($current < $last) {
            $html .= '<li><a href="' . $baseUrl . '?page=' . ($current + 1) . '" class="pagination__link pagination__link--next" aria-label="Next page">&raquo;</a></li>';
        } else {
            $html .= '<li><span class="pagination__link pagination__link--disabled">&raquo;</span></li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
}
