<?php


namespace uukule\vod\core;


use Traversable;

/**
 * Class VideoItems
 * @property int $total
 * @property int $per_page
 * @property int $current_page
 * @property int $last_page
 * @package uukule\vod\core
 */
class VideoItems implements  \IteratorAggregate, \ArrayAccess
{
    private $items = [];
    private $data = [
        'total' => 0,
        'per_page' => 10,
        'current_page' => 1,
        'last_page' => 1
    ];
    public function __get($name)
    {
        return $this->$name;
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