<?php
function xrange($start, $end, $step = 1) {

    for ($i = $start; $i <= $end; $i += $step) {
        $a = ['i'=>$i];
        yield $a;
    }
}

$a = xrange(1, 5);


foreach ($a as $num) {
    var_dump($num);
}