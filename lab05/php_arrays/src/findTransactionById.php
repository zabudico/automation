<?php

/**
 * Ищет транзакцию по идентификатору с использованием цикла foreach.
 *
 * @param int $id Идентификатор транзакции.
 * @return array|null Найденная транзакция или null, если транзакция не найдена.
 */

/*
function findTransactionById(int $id): ?array {
   global $transactions;
   foreach ($transactions as $transaction) {
       if ($transaction['id'] === $id) {
           return $transaction;
       }
   }
   return null;
}
*/

/**
 * Ищет транзакцию по идентификатору в глобальном массиве транзакций
 * 
 * @param int $id Идентификатор транзакции
 * @return array|null Найденная транзакция или null если не найдена
 */
function findTransactionById(int $id): ?array
{
    global $transactions;
    $result = array_filter($transactions, function ($transaction) use ($id) {
        return $transaction['id'] === $id;
    });
    // Если массив $result пустой, возвращаем null, иначе - первый найденный элемент
    return empty($result) ? null : array_values($result)[0];
}
