## System Requirements

### 1. Check PHP Installation

```bash
php -v
which php
```

### 2. Check Redis Installation

```bash
redis-cli ping
# Should return: PONG
```

### 3. Check Redis Connection

```bash
redis-cli -h 127.0.0.1 -p 6379 ping
# Or if Redis is on localhost:
redis-cli -h localhost -p 6379 ping
```

### 4. Verify Project Structure

```bash
cd /path/to/your/project
ls -la commands/QueueController.php
php yii queue/info
```

---

## Configuration

### 1. Update Redis Configuration

Edit `config/console.php` and `config/web.php` to match your server's Redis configuration:

```php
'redis' => [
    'class' => \yii\redis\Connection::class,
    'hostname' => 'localhost', // Change from '127.0.0.1' to your Redis host
    'port' => 6379,
    'database' => 0,
],
```

### 2. Verify Queue Configuration

The queue is configured in `config/console.php`:

```php
'queue' => [
    'class' => \yii\queue\redis\Queue::class,
    'redis' => 'redis',
    'channel' => 'queue_fcm',
    'serializer' => \yii\queue\serializers\JsonSerializer::class,
    'ttr' => 30,
    'attempts' => 3,
],
```

### 3. Set File Permissions

```bash
chmod +x yii
chmod -R 775 runtime/
chmod -R 775 web/assets/
```

---

## Deployment Methods

# 1. Tạo file service

```bash
sudo nano /etc/systemd/system/yii-queue.service
```

# 2. Dán nội dung sau

```bash
[Unit]
Description=Yii2 Queue Listener
After=network.target redis-server.service

[Service]
User=root
WorkingDirectory=/var/www/html/report
ExecStart=/usr/bin/php /var/www/html/report/yii queue/listen --verbose=1
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

# 3. Reload systemd

```bash
sudo systemctl daemon-reload
```

# 4. Bật service chạy nền:
```bash
sudo systemctl start yii-queue
sudo systemctl enable yii-queue
```

# 5. Kiểm tra trạng thái:
```bash
systemctl status yii-queue
```

Bạn sẽ thấy:

Active: active (running)

# 6. Xem log live:
```bash
journalctl -fu yii-queue
```
