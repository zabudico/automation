<?php

/**
 * Ищет транзакции по части описания (без учета регистра)
 * 
 * @param string $descriptionPart Часть описания для поиска
 * @return array Массив найденных транзакций
 */

function findTransactionByDescription(string $descriptionPart): array
{
    global $transactions;
    return array_filter($transactions, function ($transaction) use ($descriptionPart) {
        return stripos($transaction['description'], $descriptionPart) !== false;
    });
}       //What is the best way to pass an array to a function? as an argument or through a global array?