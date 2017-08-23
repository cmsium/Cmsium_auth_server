<?php
class Pager {
    private static $instance;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct(){}
    private function __clone(){}

    /**
     * Render pages block
     * @return string Pages links
     */
    public function pages($start = false,$all_elements){
        if ($start) {
            $current = $start/PAGES_OFFSET + 1;
        } else {
            $start_str="?start=0";
            $current  = 1;
        }
        $range = floor(PAGES_FOCUS_COUNT/2);
        $param_str = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $param_str =  preg_replace("/start=\d*&/", "", $param_str);
        $pages_count = ceil((float)$all_elements/PAGES_OFFSET);
        $left_border = max(min($current - $range,$pages_count - 2*$range),1);
        $right_border = min($left_border + 2*$range,$pages_count);
        $links = [];
        for ($i=$left_border;$i<=$right_border;$i++){
            $start_str = "?start=".($i-1)*PAGES_OFFSET;
            $link = htmlspecialchars(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH).$start_str.'&'.$param_str, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            if ($i != $current)
                $links[] = "<a href='{$link}'>$i</a>";
            else $links[] = "$i";
        }
        if ($right_border<$pages_count-1){
            $links[] = "...";
            $start_str = "?start=".($pages_count-1)*PAGES_OFFSET;
            $link = htmlspecialchars(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH).$start_str.'&'.$param_str, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $links[] = "<a href='{$link}'>$pages_count</a>";
        }
        return implode (', ',$links);
    }
}