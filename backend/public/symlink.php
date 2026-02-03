<?php
// 此檔案用於在僅有 FTP 權限的主機上建立 storage link
// 上傳後請訪問：http://your-domain.com/symlink.php
// 執行成功後請務必刪除此檔案！

$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

echo "<h1>Laravel Storage Link Creator</h1>";

if (file_exists($link)) {
    echo "<p style='color:red'>❌ Link already exists: $link</p>";
} else {
    // 嘗試使用 symlink 函數
    try {
        symlink($target, $link);
        echo "<p style='color:green'>✅ Symlink created via symlink() function.</p>";
    } catch (\Throwable $e) {
        echo "<p>⚠️ symlink() function failed: " . $e->getMessage() . "</p>";
        
        // 嘗試使用 Artisan (如果主機支援)
        try {
            require __DIR__ . '/../vendor/autoload.php';
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            
            // 模擬請求以初始化 App
            $response = $kernel->handle(
                $request = Illuminate\Http\Request::capture()
            );

            Illuminate\Support\Facades\Artisan::call('storage:link');
            echo "<p style='color:green'>✅ Symlink created via Artisan command.</p>";
            echo "<pre>" . Illuminate\Support\Facades\Artisan::output() . "</pre>";
        } catch (\Throwable $e2) {
             echo "<p style='color:red'>❌ Artisan failed: " . $e2->getMessage() . "</p>";
        }
    }
}
?>
