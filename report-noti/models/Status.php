<?php

namespace app\models;

class Status
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_OK = 200;
    public const STATUS_CREATED = 201;
    public const STATUS_ACCEPTED = 202;
    public const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
    public const STATUS_NO_CONTENT = 204;
    public const STATUS_RESET_CONTENT = 205;

    public const STATUS_MULTIPLE_CHOICES = 300;
    public const STATUS_MOVED_PERMANENTLY = 301;
    public const STATUS_FOUND = 302;
    public const STATUS_TEMPORARY_REDIRECT = 307;
    public const STATUS_PERMANENT_REDIRECT = 308;

    public const STATUS_BAD_REQUEST = 400;
    public const STATUS_UNAUTHORIZED = 401;
    public const STATUS_PAYMENT_REQUIRED = 402;
    public const STATUS_FORBIDDEN = 403;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_METHOD_NOT_ALLOWED = 405;
    public const STATUS_REQUEST_TIMEOUT = 408;

    public const STATUS_INTERNAL_SERVER_ERROR = 500;
    public const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
}
