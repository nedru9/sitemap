<?php
namespace nedrug\sitemapGenerator;

include 'Exception/ArrayException.php';
include 'Exception/DirectoryCreateException.php';
include 'Exception/FileWriteException.php';
include 'Exception/TypeFileException.php';

use DOMDocument;
use nedrug\sitemapGenerator\Exception\DirectoryCreateException;
use nedrug\sitemapGenerator\Exception\FileWriteException;
use nedrug\sitemapGenerator\Exception\TypeFileException;
use nedrug\sitemapGenerator\Exception\ArrayException;

class SitemapGenerator {
    const XML = 'xml';
    const CSV = 'csv';
    const JSON = 'json';
    private $pages;
    private $fileType;
    private $filePath;
    public function __construct($pages, $fileType, $filePath) {
        $this->pages = $pages;
        $this->fileType = $fileType;
        $this->filePath = $filePath;
        try {
            $this->validation();
            $this->createDir();
            $this->writeFile();
        }
        catch (\Exception $e)
        {
            die($e->getMessage());
        }
    }
    /**
     * @throws TypeFileException
     */
    private function writeFile() {
        switch ($this->fileType) {
            case self::XML:
                $this->generateXMLSitemap();
                break;
            case self::CSV:
                $this->generateCSVSitemap();
                break;
            case self::JSON:
                $this->generateJSONSitemap();
                break;
            default:
                throw new TypeFileException();
        }
    }
    /**
     * @throws DirectoryCreateException
     */
    private function createDir() {
        $filePath = substr($this->filePath, 0, strrpos($this->filePath, '/'));
        if (!is_dir($filePath)) {
            if (mkdir($filePath, 0770, true) === false) {
                throw new DirectoryCreateException();
            }
            return true;
        }
    }
    /**
     * @throws TypeFileException
     * @throws ArrayException
     */
    private function validation() {
        $errors = [];
        foreach ($this->pages as $page => $element) {
            if (isset($element['loc'])) {
                if ( !is_string($element['loc']) || $element['loc'] == '' || !isset($element['loc'])) {
                    $errors[$page]['loc'] = 'Некорректный loc!';
                }
            }
            if (isset($element['lastmod'])) {
                if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $element['lastmod'])) {
                    $errors[$page]['date'] = 'Некорректный date!';
                }
            }
            if (isset($element['priority'])) {
                if (!is_numeric($element['priority'])) {
                    $errors[$page]['priority'] = 'Некорректный priority!';
                }
            }
            if (isset($element['changefreq']))
            {
                if ( !is_string($element['changefreq']) || $element['changefreq'] == '' ) {
                    $errors[$page]['changefreq'] = 'Некорректный changefreq!';
                }
            }  
        }
        if ($errors) {
            throw new ArrayException();
        }
        if ($this->fileType != self::XML && $this->fileType != self::CSV && $this->fileType != self::JSON) {
            throw new TypeFileException();
        }
        return true;
    }

    /**
     * @throws FileWriteException
     */
    private function saveSitemap($content)
    {
        $fp = fopen($this->filePath, "c");
        if($fp === false)
        {
            throw new FileWriteException();
        }
        fwrite($fp, $content);
        fclose($fp);
        return true;
    }
    private function generateXMLSitemap() {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
                xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 
                http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"></urlset>';
        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $urlSet = $xml->documentElement;
        foreach ($this->pages as $page) {
            $url = $xml->createElement('url');
            $loc = $xml->createElement('loc', htmlspecialchars($page['loc']));
            $lastmod = $xml->createElement('lastmod', $page['lastmod']);
            $priority = $xml->createElement('priority', $page['priority']);
            $changefreq = $xml->createElement('changefreq', $page['changefreq']);
            $url->appendChild($loc);
            $url->appendChild($lastmod);
            $url->appendChild($priority);
            $url->appendChild($changefreq);
            $urlSet->appendChild($url);
        }
        $this->saveSitemap($xml->saveXML());
    }
    private function generateCSVSitemap() {
        $fp = fopen('php://temp', 'w+');
        fputcsv($fp, array_keys(reset($this->pages)), ';');
        foreach ($this->pages as $fields) {
            fputcsv($fp, $fields, ';');
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        $this->saveSitemap($csv);
    }
    private function generateJSONSitemap() {
        $this->saveSitemap(json_encode($this->pages, JSON_UNESCAPED_UNICODE));
    }
}