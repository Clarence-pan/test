<?php
/**
* This file serves as a benchmark for pthreads initialization/destruction
* usage: php-zts examples/Benchmark.php [threads] [samples]
*   threads - the number of threads to create, default=100
*   samples - the number of times to run the test, default=5
*/

/**
* Nothing
*/
class T extends Thread {
	public function run() {}
}

$max = @$argv[1] ? $argv[1] : 1000;
$sample = @$argv[2] ? $argv[2] : 5;

printf("Start(%d) ...", $max);
$it = 0;
do {
    $s = microtime(true);
    /* begin test */
    $ts = array();
    while (count($ts)<$max) {
        $t = new T();
        $t->start();
        $ts[]=$t;
    }
    $ts = array();
    /* end test */
    
    $ti [] = $max/(microtime(true)-$s);
    printf(".");
} while ($it++ < $sample);

printf(" %.3f tps\n", array_sum($ti) / count($ti));
//Start(100) ......... 589.520 tps (Threads Per Second)
?>
