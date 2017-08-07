<?php
class TypesConverts{


    private static $instance;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct(){}
    protected function __clone(){}



    /**
     * Конвертировать строку в бинарную в 16ричном представлении
     *
     * @param string $value
     * @return string
     */
    function StrToHexS($value)
    {
        $res = unpack('H*', $value);
        return $res[1];
    }


    /**
     * Конвертировать строку в бинарную в двоичном представлении
     *
     * @param string $value
     * @return string
     */
    function StrToBinS($value)
    {
        $str = StrToHexS($value);
        return base_convert($value, 16, 2);
    }


    /**
     * Конвертировать бинарную строку в 16ричном представлении в обычную
     *
     * @param string $value
     * @return string
     */
    function HexSToStr($value)
    {
        return pack('H*', $value);
    }


    /**
     * Конвертировать бинарную строку в двоичном представлении в обычную
     *
     * @param string $value
     * @return string
     */
    function BinSToStr($value)
    {
        return pack('H*', base_convert($value, 2, 16));
    }


    /**
     * Конвертировать строку в integer/float
     *
     * @param string $value
     * @return number
     */
    function StrToDec($value)
    {
        return hexdec(StrToHexS($value));
    }


    /**
     * Конвертировать строковый ipv4 в integer
     *
     * @param string $value
     * @return int
     */
    function IPv4toInt($value)
    {
        return ip2long($value);
    }


    /**
     * Конвертировать штеупук ipv4 в строковый
     *
     * @param integer $value
     * @return string
     */
    function IntToIPv4($value)
    {
        return ip2long($value);
    }
}
?>