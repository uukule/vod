<?php


namespace uukule\vod\core;


use Traversable;

/**
 * Class VideoItems
 * @property int $total
 * @property int $per_page
 * @property int $current_page
 * @property int $last_page
 * @property int $list_rows
 * @package uukule\vod\core
 */
class VideoItems implements  \IteratorAggregate, \ArrayAccess
{
    private $items = [];
    private $data = [
        'total' => null,
        'per_page' => null,
        'current_page' => 1,
        'last_page' => null,
        'list_rows' => 20
    ];
    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function toArray(){
        $this->data['last_page'] = 0 === $this->data['total'] ? 1 :(int) ceil($this->data['total']/$this->data['per_page']);
        $response = $this->data;
        $response['data'] = [];
        foreach ($this->items as $item){
            $response['data'][] = $item->toArray();
        }
        return $response;
    }

    public function getIterator()
    {
        return new ArrayIterator($this);
    }


    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }


    public function offsetGet($offset)
    {

    }


    public function offsetSet($offset, $value)
    {
        if($value instanceof VideoItem){
            array_push($this->items, $value);
        }elseif(is_string($offset)){
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}