<?php

class SPBGUPTD_Institutes implements iParser {
    private $data='';

    public function __construct($data){
        $this->data = $data;
    }

    public function execute(){
        $doc = new DOMDocument;
        $doc->loadHTML($this->data);
        $docxpath = new DOMXPath($doc);
        $institutes=[];
        foreach ($doc->getElementsByTagName('table') as $table) {
            $td_nodes = $docxpath->query('tbody/tr/td', $table);
            foreach ($td_nodes as $td_node){
                $attr = $td_node->getAttributeNode('width');
                if ($attr->value == "725" OR $attr->value == "728"){
                    $institute = trim(str_replace(['   ','  '],[' ',''], $td_node->textContent),
                        " \t\n\r\0\x0B-");
                    $institutes[$institute] = [];
                }
                if ($attr->value == "175" OR $attr->value == "177"){
                    $items = $docxpath->query('p', $td_node);
                    $i=0;
                    $qualifications=[];
                    foreach ($items as $item){
                        if ($i == 0) {
                            $department = trim(str_replace(['   ', '  '], [' ', ''], $item->textContent),
                                " \t\n\r\0\x0B-");
                        }
                        else {
                            $qualification = trim(str_replace(['   ', '  '], [' ', ''], $item->textContent),
                                " \t\n\r\0\x0B-");
                            if (strlen($qualification) > 2)
                                $qualifications[] = $qualification;
                        }
                            $i++;
                    }
                    if (!isset($institutes[$institute][$department]))
                        $institutes[$institute][$department] = $qualifications;
                    else
                        $institutes[$institute][$department] = array_merge($institutes[$institute][$department],$qualifications);
                }
            }
        }
        //var_dump($institutes);
        $result_array = [];
        foreach ($institutes as $institute => $inst_value){
            if (!empty($inst_value)){
                foreach ($inst_value as $department => $dep_value) {
                    foreach ($dep_value as $qualification) {
                        $result_array[] = [$institute, $department, $qualification];
                    }
                }
            }
        }
        return $result_array;
    }
}