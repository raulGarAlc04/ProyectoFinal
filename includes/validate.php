<?php

function is_number($number, $min = 0, $max = 100): bool
{
    return ($number >= $min and $number <= $max);
}

function is_text($text, $min = 0, $max = 1000)
{
    $length = mb_strlen($text);
    return ($length >= $min and $length <= $max);
}


function is_actor_id($actor_id, array $actor_list): bool
{
    foreach ($actor_list as $actor) {
        if ($actor['id'] == $actor_id) {
            return true;
        }
    }
    return false;
}

function is_plataforma_id($plataforma_id, array $plataforma_list): bool
{
    foreach ($plataforma_list as $plataforma) {
        if ($plataforma['id'] == $plataforma_id) {
            return true;
        }
    }
    return false;
}