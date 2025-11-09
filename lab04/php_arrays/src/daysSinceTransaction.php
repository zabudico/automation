<?php

/**
 * Рассчитывает количество дней с момента транзакции до текущей даты
 * 
 * @param string $date Дата транзакции в формате 'Y-m-d'
 * @return int Количество дней между текущей датой и датой транзакции
 */

function daysSinceTransaction(string $date): int
{
    $transactionDate = new DateTime($date);
    $currentDate = new DateTime();

    return abs($transactionDate->diff($currentDate)->days);
}
