<?php

/**
 * Вычисляет общую сумму всех транзакций
 * 
 * @param array $transactions Массив транзакций
 * @return float Общая сумма всех транзакций
 */

function calculateTotalAmount(array $transactions): float
{
    $fSumOfTransactions = 0;
    foreach ($transactions as $transaction) {
        $fSumOfTransactions += $transaction['amount'];
    }
    return $fSumOfTransactions;
}

/*
Создайте функцию calculateTotalAmount(array $transactions): float, которая вычисляет общую сумму всех транзакций.
Выведите сумму всех транзакций в конце таблицы.
*/
