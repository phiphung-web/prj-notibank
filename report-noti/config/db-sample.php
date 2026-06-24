<?php

const URL_WEB_CLIENT = 'https://example.com/?token=';

// Google Excel

const GOOGLE_FILE_AUTH_PATH = 'https://example.com/upload/credentials.json';
const GOOGLE_APP_NAME = 'Google Sheet';
const GOOGLE_ID_SHEET = 'CHANGE_ME';
const CHAT_ID_TELEGRAM_ERROR = 'CHANGE_ME';

// API address
const API_SEARCH_ADDRESS = 'http://localhost:8080/';
const API_SEARCH_DISTANCE_ADDRESS = 'http://router.project-osrm.org/';

return [
  'class' => 'yii\db\Connection',
  'dsn' => 'mysql:host=localhost;dbname=driver',
  'username' => 'root',
  'password' => 'change-me',
  'charset' => 'utf8',
];
