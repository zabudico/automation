<?php

declare(strict_types=1);

// Подключаем все исходные файлы
require_once __DIR__ . '/../src/calculateTotalAmount.php';
require_once __DIR__ . '/../src/findTransactionByDescription.php';

require_once __DIR__ . '/../src/findTransactionById.php';

require_once __DIR__ . '/../src/daysSinceTransaction.php';

require_once __DIR__ . '/../src/addTransaction.php';

require_once __DIR__ . '/../src/deleteTransaction.php';


class SimpleTests
{
    private array $testTransactions = [];
    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->testTransactions = [
            [
                "id" => 1,
                "date" => "2025-01-01",
                "amount" => 100.00,
                "description" => "Payment for products",
                "merchant" => "Walmart",
            ],
            [
                "id" => 2,
                "date" => "2025-02-15",
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
            ]
        ];
    }

    private function assertEqual($expected, $actual, string $testName): void
    {
        if ($expected === $actual) {
            echo " PASS: $testName\n";
            $this->passed++;
        } else {
            echo " FAIL: $testName\n";
            echo "   Expected: " . print_r($expected, true) . "\n";
            echo "   Actual: " . print_r($actual, true) . "\n";
            $this->failed++;
        }
    }

    private function assertTrue($condition, string $testName): void
    {
        $this->assertEqual(true, $condition, $testName);
    }

    private function assertFalse($condition, string $testName): void
    {
        $this->assertEqual(false, $condition, $testName);
    }

    public function runAllTests(): void
    {
        echo "=== Запуск простых Unit Tests ===\n\n";

        $this->testCalculateTotalAmount();
        $this->testFindTransactionByDescription();
        $this->testFindTransactionById();
        $this->testDaysSinceTransaction();
        $this->testAddTransaction();
        $this->testDeleteTransaction();

        echo "\n=== Результаты тестов ===\n";
        echo " Пройдено: {$this->passed}\n";
        echo " Провалено: {$this->failed}\n";
        echo " Всего: " . ($this->passed + $this->failed) . "\n";
    }

    private function testCalculateTotalAmount(): void
    {
        echo "--- Тесты calculateTotalAmount ---\n";

        $result = calculateTotalAmount($this->testTransactions);
        $expected = 100.00 + 75.50 + 129.50;
        $this->assertEqual($expected, $result, "Сумма транзакций");

        $result = calculateTotalAmount([]);
        $this->assertEqual(0.0, $result, "Сумма пустого массива");
    }

    private function testFindTransactionByDescription(): void
    {
        echo "--- Тесты findTransactionByDescription ---\n";

        global $transactions;
        $backup = $transactions;
        $transactions = $this->testTransactions;

        $result = findTransactionByDescription("Subscribe");
        $this->assertEqual(1, count($result), "Поиск по описанию");

        $result = findTransactionByDescription("subscribe");
        $this->assertEqual(1, count($result), "Поиск без учета регистра");

        $result = findTransactionByDescription("Nonexistent");
        $this->assertEqual(0, count($result), "Поиск несуществующего");

        $transactions = $backup;
    }

    private function testFindTransactionById(): void
    {
        echo "--- Тесты findTransactionById ---\n";

        global $transactions;
        $backup = $transactions;
        $transactions = $this->testTransactions;

        $result = findTransactionById(2);
        $this->assertTrue($result !== null, "Поиск существующего ID");
        $this->assertEqual(2, $result['id'], "Проверка найденного ID");

        $result = findTransactionById(999);
        $this->assertTrue($result === null, "Поиск несуществующего ID");

        $transactions = $backup;
    }

    private function testDaysSinceTransaction(): void
    {
        echo "--- Тесты daysSinceTransaction ---\n";

        // Тестируем с известной датой (10 дней назад)
        $pastDate = date('Y-m-d', strtotime('-10 days'));
        $result = daysSinceTransaction($pastDate);
        $this->assertEqual(10, $result, "Дней с транзакции");

        // Тестируем с сегодняшней датой
        $today = date('Y-m-d');
        $result = daysSinceTransaction($today);
        $this->assertEqual(0, $result, "Транзакция сегодня");
    }

    private function testAddTransaction(): void
    {
        echo "--- Тесты addTransaction ---\n";

        global $transactions;
        $backup = $transactions;
        $transactions = $this->testTransactions;

        $initialCount = count($transactions);

        // Тест успешного добавления
        try {
            addTransaction(4, "2025-03-10", 50.0, "Test transaction", "Test Merchant");
            $this->assertEqual($initialCount + 1, count($transactions), "Добавление транзакции");

            $newTransaction = findTransactionById(4);
            $this->assertTrue($newTransaction !== null, "Поиск новой транзакции");
        } catch (Exception $e) {
            $this->assertFalse(true, "Добавление транзакции не должно выбрасывать исключение");
        }

        // Тест дублирования ID
        try {
            addTransaction(1, "2025-03-10", 50.0, "Duplicate ID", "Test Merchant");
            $this->assertFalse(true, "Дублирование ID должно выбрасывать исключение");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, "Исключение при дублировании ID");
        }

        // Тест отрицательной суммы
        try {
            addTransaction(5, "2025-03-10", -50.0, "Test", "Test Merchant");
            $this->assertFalse(true, "Отрицательная сумма должна выбрасывать исключение");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, "Исключение при отрицательной сумме");
        }

        $transactions = $backup;
    }

    private function testDeleteTransaction(): void
    {
        echo "--- Тесты deleteTransaction ---\n";

        global $transactions;
        $backup = $transactions;
        $transactions = $this->testTransactions;

        $initialCount = count($transactions);

        // Тест успешного удаления
        $result = deleteTransactionById(2);
        $this->assertTrue($result, "Удаление существующей транзакции");
        $this->assertEqual($initialCount - 1, count($transactions), "Проверка количества после удаления");

        // Тест удаления несуществующей
        $result = deleteTransactionById(999);
        $this->assertFalse($result, "Удаление несуществующей транзакции");

        $transactions = $backup;
    }
}

// Запуск тестов
$tests = new SimpleTests();
$tests->runAllTests();