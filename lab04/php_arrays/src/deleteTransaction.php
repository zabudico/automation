<?php

/**
 * Удаляет транзакцию по ID из глобального массива транзакций
 * 
 * @param int $id Идентификатор транзакции для удаления
 * @return bool true если транзакция была удалена, false если не найдена
 */

function deleteTransactionById(int $id): bool
{
    global $transactions;
    $initialCount = count($transactions);
    foreach ($transactions as $key => $transaction) {
        if ($transaction['id'] === $id) {
            unset($transactions[$key]);
            $transactions = array_values($transactions); // Переиндексация массива
            return true;
        }
    }
    return false;
}