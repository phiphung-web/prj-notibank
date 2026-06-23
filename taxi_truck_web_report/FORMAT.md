# Auto Format Setup

## Cài đặt Extensions cho VS Code/Cursor

Chạy lệnh sau để cài đặt các extensions được khuyến nghị:

```bash
# Hoặc mở VS Code/Cursor và cài đặt từ tab Extensions
code --install-extension bmewburn.vscode-intelephense-client
code --install-extension esbenp.prettier-vscode
code --install-extension editorconfig.editorconfig
code --install-extension junstyle.php-cs-fixer
```

## Cài đặt PHP CS Fixer

```bash
composer install
```

## Sử dụng

### Auto Format khi Save
- Code sẽ tự động format khi bạn save file (Ctrl+S)
- Hỗ trợ PHP, HTML, CSS, JavaScript, JSON

### Manual Format
```bash
# Format tất cả file PHP
composer format

# Kiểm tra format mà không thay đổi file
composer format-check

# Format file cụ thể
./vendor/bin/php-cs-fixer fix path/to/file.php
```

## Cấu hình

- `.editorconfig` - Cài đặt chung cho tất cả editor
- `.vscode/settings.json` - Cài đặt VS Code/Cursor
- `.php-cs-fixer.php` - Cài đặt format PHP
- `.prettierrc` - Cài đặt format JS/CSS/HTML

## Format Rules

### PHP
- PSR-2 và PSR-12 standards
- Short array syntax `[]`
- Single quotes
- Ordered imports

### JavaScript/CSS/HTML
- 2 spaces indentation
- Single quotes
- Semicolons
- Trailing commas
