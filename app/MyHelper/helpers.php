<?php 

define('DATE_FORMAT', 'l, d/m/Y G:i');

function spaceFomat(string $str) : string {
    return preg_replace('/\s+/', ' ', trim($str));
}

function moneyFormat($money) : string{
    $money = (int)$money;
    $number = preg_replace('/\D/','',(string)$money);
    $mod = strlen($number)%3;
    $head = substr($number,0,$mod);
    if(!empty($head))
        $head .= '.';
    return  $head . implode('.',str_split(substr($number,$mod),3));
}


?>