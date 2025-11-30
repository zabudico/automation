<?php

/**
 * Добавляет новую транзакцию в глобальный массив транзакций
 * 
 * @param int $id Уникальный идентификатор транзакции
 * @param string $date Дата транзакции в формате 'Y-m-d'
 * @param float $amount Сумма транзакции (должна быть положительной)
 * @param string $description Описание транзакции
 * @param string $merchant Название мерчанта
 * @return void
 * @throws InvalidArgumentException Если ID уже существует, неверный формат даты или неположительная сумма
 */

function addTransaction(int $id, string $date, float $amount, string $description, string $merchant): void
{
    global $transactions;

    // Проверка на существующий ID
    if (findTransactionById($id) !== null) {
        throw new InvalidArgumentException("Transaction with ID $id already exists");
    }

    // Проверка формата даты
    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        throw new InvalidArgumentException("Invalid date format");
    }

    // Проверка суммы
    if ($amount <= 0) {
        throw new InvalidArgumentException("Amount must be positive");
    }

    $transactions[] = [
        "id" => $id,
        "date" => $date,
        "amount" => $amount,
        "description" => $description,
        "merchant" => $merchant,
    ];
}