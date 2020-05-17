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
        if(sizeof($inArr)>1){
            $last = $inArr[0]->getCreatedAt();
            $first = $inArr[sizeof($inArr)-1]->getCreatedAt();
            $res[$inArr[0]->getCreatedAt()->format('Y-m-d')] = 0;
            for($i = $first; $i <= $last; $i->modify('+1 day')){
                $res[$i->format('Y-m-d')] = 0;
            }
            foreach ($inArr as $data){
                if(isset($res[$data->getCreatedAt()->format('Y-m-d')])){
                    $res[$data->getCreatedAt()->format('Y-m-d')]++;
                }else{
                    $res[$data->getCreatedAt()->format('Y-m-d')] = 1;
                }
            }
        }

        return $res;

    }
}
