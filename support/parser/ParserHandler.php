<?php
class ParserHandler{
    private $pages=[];

    public function __construct(){
        $this->parseConfig();
    }
    private function parseConfig(){
        $doc = new DOMDocument;
        $doc->load(__DIR__."/config.xml");
        $xml = $doc->saveXML();
        $converter = DataConverter::getInstance();
        $pages = $converter->XMLToArray($xml);
        $this->pages = $pages['page'];
    }

    public function parse(){
        if (!is_array($this->pages[0]))
            $this->pages = [$this->pages];
        foreach ($this->pages as $page){
            $data = file_get_contents($page['url']);
            if (!$data){
                die ("Page read error");
            }
            $parser = new $page['parser']($data);
            $parsed_data = $parser->execute();
            $this->putToFile($parsed_data,$page['file']['type'],$page['file']['name']);
            echo "{$page['url']} => parsing complete".PHP_EOL;
        }
    }

    private function putToFile($data,$file_type,$file_name){
        switch ($file_type){
            case 'csv':
                $file = fopen(__DIR__.'/parsed_data/'.$file_name,'a');
                if (!$file){
                    die("File write error");
                }
                foreach ($data as $field) {
                    if (fputcsv($file, $field) === false) {
                        die("File write error");
                    }
                }
        }
    }
}