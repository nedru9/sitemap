<?php
namespace nedrug\sitemapGenerator\Exception;

class ArrayException extends \Exception
{
    protected $message = "Невалидные данные при инициализации парсинга";
}