<?php
class ErrorController {
    public function notFound() {
        $this->render('error/404', [
            'title' => '404 - Page Not Found',
            'currentPage' => 'error'
        ]);
    }

    private function render($view, $data = []) {
        $content = __DIR__ . "/../views/{$view}.php";
        extract($data);
        include __DIR__ . "/../views/layouts/main.php";
    }
} 
