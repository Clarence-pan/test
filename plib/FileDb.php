<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-15
 * Time: 上午9:26
 */

namespace plib;


class FileDb {
    private $lines = array();
    private $fileName = "fdb.db.php";
    /**
     *
     */
    public function __construct($fileName = null) {
        if (is_null($fileName)){
            $fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . "fdb.db.php";
        }
        $this->fileName = $fileName;
//        $content = file_get_contents($fileName);
//        dump($content);
//        $this->lines = eval($content);
        $this->lines = include($fileName);
        if (!$this->lines) {
            $this->lines = array();
        }
    }

    public function __destruct() {
        $this->save();
    }

    public function &getLines(){
        return $this->lines;
    }

    public function count(){
        return count($this->lines);
    }

    public function get($index) {
        return $this->lines[$index];
    }

    public function set($index=null, $data) {
        if (is_null($index)){
            $this->lines[] = $data;
        }
        else {
            $this->lines[$index] = $data;
        }
    }

    public function add($data) {
        $this->lines[] = $data;
    }

    public function append($data) {
        return $this->add($data);
    }

    public function find($needle) {
        foreach ($this->lines as &$line) {
            if ($line == $needle){
                return true;
            }
        }
        return false;
    }

    public function save(){
        $content = var_export($this->lines, true);
        $r = file_put_contents($this->fileName, '<?PHP return ' . $content . ';');
    }
}