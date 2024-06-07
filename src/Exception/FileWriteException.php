<?php
namespace nedrug\sitemapGenerator\Exception;

class FileWriteException extends \Exception
{
    protected $message = "Ошибка доступа записи к файлу";
}