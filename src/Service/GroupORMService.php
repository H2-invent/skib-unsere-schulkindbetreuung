<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


class GroupORMService
{
    public function groupData($inArr){
        $res = array();
        foreach ($inArr as $data){
            if(isset($res[$data->getCreatedAt()->format('Y-m-d H:i')])){
                $res[$data->getCreatedAt()->format('Y-m-d H:i')]++;
            }else{
                $res[$data->getCreatedAt()->format('Y-m-d H:i')] = 1;
            }
        }
        return $res;

    }
}
