#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

if [[ ! -d "frontend" || ! -d "backend" ]]; then
    echo "❌ 請從專案根目錄執行此腳本"
    exit 1
fi

echo "🚀 開始準備部署檔案..."

# 1. 進入前端並構建
echo "📦 Building Frontend..."
cd frontend
npm run build
cd ..

# 2. 確認編譯產物存在
if [[ ! -d "frontend/dist/assets" || ! -f "frontend/dist/index.html" ]]; then
    echo "❌ 前端編譯產物不存在，請檢查 build 是否成功"
    exit 1
fi

# 3. 清理舊的後端資源
echo "🧹 Cleaning old assets in Backend..."
rm -rf backend/public/assets
rm -f backend/resources/views/spa.blade.php

# 4. 搬移編譯後的檔案
echo "🚚 Moving Build Artifacts..."
cp -r frontend/dist/assets backend/public/assets
cp frontend/dist/index.html backend/resources/views/spa.blade.php

echo "✅ 部署準備完成！"
echo "👉 請將 'backend' 資料夾整個內容 (包含 .env, vendor 等) 上傳到伺服器指定目錄"
echo "👉 請將子網域 (path) 指向 'backend/public'"
