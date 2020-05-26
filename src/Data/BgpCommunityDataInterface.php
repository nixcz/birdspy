<?php


namespace App\Data;


interface BgpCommunityDataInterface
{

    public function getName();

    public function getLabel();

    public function getArray();

    public function getRawValue();

    public function getFilterValue();

}
