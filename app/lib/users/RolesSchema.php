<?php
class RolesSchema {

    public static $data_model = ['polzovatel' => [["column_name" => "user_id","column_type" => "varchar(32)","is_nullable" => "NO",],
["column_name" => "test","column_type" => "int(11)","is_nullable" => "YES",],
],'privet_mir' => [["column_name" => "user_id","column_type" => "varchar(32)","is_nullable" => "NO",],
["column_name" => "privet","column_type" => "int(11)","is_nullable" => "NO",],
["column_name" => "mir","column_type" => "varchar(32)","is_nullable" => "NO",],
],];

}