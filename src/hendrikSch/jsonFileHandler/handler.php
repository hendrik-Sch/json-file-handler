<?php

namespace hendrikSch\jsonFileHandler;

class JsonFileHandler
{
    public function __construct($_path = __DIR__)
    {
        $this->path = $_path;
    }

    private function getContent($fileName)
    {
        $this->json = file_get_contents($this->path . "/" . $fileName);
    }

    private function isValid($json)
    {
        json_decode($json);
        if (json_last_error() === 0) {
            return true;
        }
        return false;
    }

    private function mergeJson($json1, $json2)
    {
        if (!$this->isValid(json_encode($json1))) {
            return $json2;
        }
        if (is_array($json1) && is_array($json2)) {
            return array_merge($json1, $json2);
        }
        if (is_array($json1)) {
            array_push($json1, $json2);
            return $json1;
        }
        if (is_array($json2)) {
            throw new Error("Can not merge a JSON-Array into an JSON-Object");
        }

        $mergedJson = new stdClass;
        foreach ($json1 as $k => $v) {
            $mergedJson->$k = $v;
        }
        foreach ($json2 as $k => $v) {
            $mergedJson->$k = $v;
        }
        return $mergedJson;
    }

    public function getJSON($fileName, $format = "string")
    {
        $this->getContent($fileName);
        if ($format === "php") {
            return json_decode($this->json);
        }
        return $this->json;
    }

    public function saveJSON($json, $fileName, $createIfNotExist = true)
    {
        $fileUrl = $this->path . "/" . $fileName;
        $exists = file_exists($fileUrl);
        if (!$createIfNotExist && !$exists) {
            throw new Error("File does not exist");
        }
        if (!$this->isValid($json)) {
            throw new Error("Invalid JSON");
        }

        if ($exists) {
            $oldJson = $this->getJSON($fileName);
            $json = $this->mergeJson(json_decode($oldJson), json_decode($json));
            $json = json_encode($json);
        }

        if (!$file = fopen($fileUrl, 'w')) {
            throw new Error("can not open/create file: " . $fileUrl);
        }
        fwrite($file, $json);
        fclose($file);
    }
}