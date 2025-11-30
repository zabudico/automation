<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/calculateTotalAmount.php';
require_once __DIR__ . '/../src/findTransactionByDescription.php';

require_once __DIR__ . '/../src/findTransactionById.php';

require_once __DIR__ . '/../src/daysSinceTransaction.php';

require_once __DIR__ . '/../src/addTransaction.php';

require_once __DIR__ . '/../src/deleteTransaction.php';


$transactions = [
    [
        "id" => 1,
        "date" => "2025-01-01",
        "amount" => 100.00,
        "description" => "Payment for products",
        "merchant" => "Walmart",
    ],
    [
        "id" => 2,
        "date" => "2025-02-15",     //DateTime
        "amount" => 75.50,
        "description" => "Dinner with friends",
        "merchant" => "Vezuvi Restaurant",
    ],
    [
        "id" => 3,
        "date" => "2025-03-01",
        "amount" => 129.50,
        "description" => "Subscribe",
        "merchant" => "world of warcraft",
    ],
    [
        "id" => 4,
        "date" => "2025-03-08",
        "amount" => 50.75,
        "description" => "Coffee shop purchase",
        "merchant" => "Cafe Latte",
    ],
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        caption {
            font-weight: bold;
            margin-bottom: 10px;
        }

        pre {
            background-color: #f8f8f8;
            padding: 10px;
        }
    </style>
</head>

<body>

    <h1>Тестирование функций работы с транзакциями</h1>

    <!-- 1. Вывод исходного массива транзакций с количеством дней с момента транзакции -->
    <h2>Исходные транзакции</h2>
    <table>
        <caption>Исходные транзакции</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Merchant</th>
                <th>Days Since Transaction</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) $transaction['id']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['date']) ?></td>
                    <td><?php echo htmlspecialchars((string) $transaction['amount']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['merchant']) ?></td>
                    <td><?php echo daysSinceTransaction($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total Amount</th>
                <td><?php echo calculateTotalAmount($transactions) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. Тест функции поиска транзакции по описанию -->
    <h2>Поиск транзакции по описанию</h2>
    <?php
    $descriptionSearch = "Subscribe";
    $foundByDescription = findTransactionByDescription($descriptionSearch);
    ?>
    <p>Результаты поиска транзакций по описанию, содержащему
        <strong><?php echo htmlspecialchars($descriptionSearch) ?></strong>:
    </p>
    <pre><?php print_r($foundByDescription); ?></pre>

    <!-- 3. Тест функции поиска транзакции по ID -->
    <h2>Поиск транзакции по идентификатору</h2>
    <?php

    $idToSearch = 3;

    // Использование реализации с array_filter
    $foundByIdArrayFilter = findTransactionById($idToSearch);
    ?>

    <p>Результат поиска транзакции с ID <strong><?php echo $idToSearch ?></strong> (array_filter):</p>
    <pre><?php print_r($foundByIdArrayFilter); ?></pre>

    <!-- 4. Тест функции добавления новой транзакции -->
    <h2>Добавление новой транзакции</h2>
    <?php

    addTransaction(5, "2025-03-09", 200.00, "New product purchase", "Amazon");
    ?>
    <p>Новая транзакция (ID 5) добавлена. Обновлённый список транзакций:</p>
    <table>
        <caption>Обновлённые транзакции</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Merchant</th>
                <th>Days Since Transaction</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) $transaction['id']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['date']) ?></td>
                    <td><?php echo htmlspecialchars((string) $transaction['amount']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['merchant']) ?></td>
                    <td><?php echo daysSinceTransaction($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total Amount</th>
                <td><?php echo calculateTotalAmount($transactions) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- 5. Тест сортировки транзакций -->
    <h2>Сортировка транзакций</h2>
    <?php
    // Сортировка транзакций по дате (от ранней к поздней)
    $transactionsByDate = $transactions;
    usort($transactionsByDate, function ($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    ?>
    <h3>Транзакции, отсортированные по дате:</h3>
    <table>
        <caption>Сортировка по дате</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Merchant</th>
                <th>Days Since Transaction</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactionsByDate as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) $transaction['id']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['date']) ?></td>
                    <td><?php echo htmlspecialchars((string) $transaction['amount']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['merchant']) ?></td>
                    <td><?php echo daysSinceTransaction($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    // Сортировка транзакций по сумме (по убыванию)
    $transactionsByAmount = $transactions;
    usort($transactionsByAmount, function ($a, $b) {
        return $b['amount'] <=> $a['amount'];
    });
    ?>
    <h3>Транзакции, отсортированные по сумме (по убыванию):</h3>
    <table>
        <caption>Сортировка по сумме</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Merchant</th>
                <th>Days Since Transaction</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactionsByAmount as $transaction): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) $transaction['id']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['date']) ?></td>
                    <td><?php echo htmlspecialchars((string) $transaction['amount']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']) ?></td>
                    <td><?php echo htmlspecialchars($transaction['merchant']) ?></td>
                    <td><?php echo daysSinceTransaction($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <!-- 6. Тестирование удаления транзакции -->
    <h2>Удаление транзакции</h2>
    <?php
    $idToDelete = 3;
    $deleted = deleteTransactionById($idToDelete);
    ?>
    <p>Попытка удаления транзакции с ID <strong><?php echo $idToDelete ?></strong>:
        <?php echo $deleted ? 'Успешно удалено' : 'Не найдено' ?>
    </p>

    <table>
        <caption>Транзакции после удаления</caption>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Merchant</th>
                <th>Days Since</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo $transaction['id'] ?></td>
                    <td><?php echo $transaction['date'] ?></td>
                    <td><?php echo $transaction['amount'] ?></td>
                    <td><?php echo $transaction['description'] ?></td>
                    <td><?php echo $transaction['merchant'] ?></td>
                    <td><?php echo daysSinceTransaction($transaction['date']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <td><?php echo calculateTotalAmount($transactions) ?></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>