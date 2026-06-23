#!/bin/bash

# Thoát nếu có lỗi bất kỳ
set -e

# In thời gian bắt đầu
echo "=== Bắt đầu: $(date) ==="

# Lấy ngày hiện tại theo định dạng YYYY-MM-DD
today=$(date +%F)

# Tên file zip có gắn ngày
filename="report_$today.zip"

# Tạo file zip, loại trừ một số thư mục
echo "→ Đang nén project vào $filename..."
zip -r "$filename" . -x "vendor/*" "node_modules/*" ".git/*" "runtime/*" "$filename"

# Pull code mới nhất từ git
echo "→ Đang pull code từ git..."
git pull

# Chạy migrate
echo "→ Đang chạy migrate..."
./yii migrate --interactive=0

# In thời gian kết thúc
echo "=== Hoàn tất: $(date) ==="
