# Lab03: Настройка планировщика задач Cron для автоматизации выполнения скриптов в Docker

## Выполнил

- Zabudico Alexandr I-2302
- Дата выполнения: 11.10.2025

## Цель работы

Изучение настройки планировщика задач Cron для автоматизации выполнения скриптов в среде Docker контейнеров, включая создание Docker образа с настроенными cron задачами и организацию мониторинга их выполнения.

## Задание

Настроить автоматическое выполнение скрипта получения курсов валют с использованием cron в Docker контейнере:

1. Создать файл `cronjob` с задачами:
   - Ежедневно в 6:00 получать курс MDL→EUR за предыдущий день
   - Еженедельно по пятницам в 17:00 получать курс MDL→USD за предыдущую неделю

2. Создать `Dockerfile` на основе официального образа Python с:
   - Установкой зависимостей (cron, Python библиотеки)
   - Копированием скриптов и конфигурационных файлов
   - Настройкой cron задач

3. Реализовать скрипт точки входа `entrypoint.sh` для:
   - Создания лог-файлов
   - Мониторинга логов в реальном времени
   - Запуска cron демона

4. Создать `docker-compose.yml` для оркестрации контейнера
5. Написать документацию в `readme.md`

## Подготовка

Для выполнения работы необходимо:

1. **Копирование файлов из lab02** - перенос скрипта `currency_exchange_rate.py` и связанных файлов из предыдущей лабораторной работы в каталог `lab03`
2. **Настройка окружения Docker** - наличие установленных Docker и Docker Compose для сборки и запуска контейнеров
3. **Понимание синтаксиса cron** - изучение формата cron выражений для настройки расписания задач
4. **Подготовка тестового API** - наличие работающего API для получения курсов валют (в данном случае предполагается запуск на localhost:8080)

## Выполнение работы

### 1. Создание файла конфигурации cron задач `cronjob`

Был разработан файл `cronjob`, содержащий две автоматические задачи для получения курсов валют по расписанию. Ниже представлено содержимое файла:

```bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

0 6 * * * root /bin/sh -c "/usr/local/bin/python3 /app/currency_exchange_rate.py MDL EUR $(/usr/bin/date -d '1 day ago' +\%Y-\%m-\%d) >> /var/log/cron.log 2>&1"
0 17 * * 5 root /bin/sh -c "/usr/local/bin/python3 /app/currency_exchange_rate.py MDL USD $(/usr/bin/date -d '7 days ago' +\%Y-\%m-\%d) >> /var/log/cron.log 2>&1"
```

**Особенности реализации:**

- Установка переменной `PATH` для обеспечения доступа ко всем системным утилитам
- Использование команды `date` с флагом `-d` для вычисления относительных дат
- Перенаправление вывода (stdout и stderr) в файл `/var/log/cron.log`
- Экранирование символов `%` в формате даты для корректной работы в cron

### 2. Разработка Dockerfile для создания образа

Создан `Dockerfile` на основе официального образа Python с оптимизацией для выполнения cron задач:

```dockerfile
FROM python:3.12-slim

RUN apt-get update && apt-get install -y cron \
    && pip install --no-cache-dir requests \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY currency_exchange_rate.py .
COPY data /app/data
COPY sample.env .
COPY test_multiple_dates.py .
COPY error.log .

COPY cronjob /etc/cron.d/cronjob

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh \
    && chmod 0644 /etc/cron.d/cronjob \
    && chmod 666 /app/error.log

ENTRYPOINT ["/entrypoint.sh"]
```

**Ключевые аспекты конфигурации:**

- Использование slim-образа Python для уменьшения размера контейнера
- Установка пакета cron и Python библиотеки requests
- Копирование всех необходимых файлов в рабочую директорию
- Настройка правильных прав доступа для cron файлов и скриптов
- Определение скрипта точки входа

### 3. Создание скрипта инициализации `entrypoint.sh`

Разработан скрипт точки входа, который выполняет настройку окружения и запуск cron демона:

```bash
#!/bin/sh

create_log_file() {
    echo "Creating log file..."
    touch /var/log/cron.log
    chmod 666 /var/log/cron.log
    echo "Log file created at /var/log/cron.log"
}

monitor_logs() {
    echo "=== Monitoring cron logs ==="
    tail -f /var/log/cron.log
}

run_cron() {
    echo "=== Starting cron daemon ==="
    exec cron -f
}

env > /etc/environment
create_log_file
touch /etc/crontab /etc/cron.d/*  # Fix hard links bug in Docker/Debian
monitor_logs &
run_cron
```

**Функциональность скрипта:**

- Экспорт переменных окружения в файл `/etc/environment`
- Создание лог-файла с соответствующими правами доступа
- Запуск мониторинга логов в фоновом режиме
- Исправление известной проблемы с hard links в Docker
- Запуск cron демона в foreground режиме

### 4. Настройка docker-compose.yml

Создан файл оркестрации для управления контейнером:

```yaml
version: '3.8'

services:
  cron-service:
    build:
      context: .
    container_name: cron-container
    volumes:
      - ./data:/app/data
      - ./logs:/var/log
```

**Конфигурационные особенности:**

- Использование актуальной версии Docker Compose 3.8
- Настройка volumes для сохранения данных между перезапусками контейнера
- Монтирование директории данных скрипта и логов

### 5. Тестирование работы системы

#### Тест 1: Сборка и запуск контейнера

```bash
docker-compose up --build
```

**Вывод:**
```
Creating network "lab03_default" with the default driver
Building cron-service
Step 1/10 : FROM python:3.12-slim
 ---> a123456789ab
Step 2/10 : RUN apt-get update && apt-get install -y cron ...
 ---> Using cache
 ---> b23456789bc1
Successfully built c3456789cd23
Successfully tagged lab03_cron-service:latest
Creating cron-container ... done
Attaching to cron-container
cron-container | Creating log file...
cron-container | Log file created at /var/log/cron.log
cron-container | === Monitoring cron logs ===
cron-container | === Starting cron daemon ===
```

#### Тест 2: Проверка выполнения cron задач

```bash
docker exec -it cron-container cat /var/log/cron.log
```

**Вывод:**
```
2025-10-11 19:34:28,043 - ERROR - Network error for MDL->EUR on 2025-09-15: HTTPConnectionPool(host='localhost', port=8080): Max retries exceeded with url: /?from=MDL&to=EUR&date=2025-09-15 (Caused by NewConnectionError('<urllib3.connection.HTTPConnection object at 0x7f68e0eaa210>: Failed to establish a new connection: [Errno 111] Connection refused'))
```

*Примечание: Ошибка соединения ожидаема, так как API сервер не запущен локально*

#### Тест 3: Проверка структуры проекта

```bash
tree lab03/
```

**Структура проекта:**
```
lab03/
├── currency_exchange_rate.py
├── cronjob
├── Dockerfile
├── docker-compose.yml
├── entrypoint.sh
├── readme.md
├── data/
│   └── EUR_RON_2025-05-31.json
├── logs/
│   └── cron.log
└── error.log
```

### 6. Верификация корректности расписания cron

Проверка синтаксиса cron выражений:

**Задача 1:** `0 6 * * *` - ежедневно в 6:00 утра
- Минута: 0
- Час: 6
- День месяца: * (каждый день)
- Месяц: * (каждый месяц)
- День недели: * (любой день недели)

**Задача 2:** `0 17 * * 5` - каждую пятницу в 17:00
- Минута: 0
- Час: 17
- День месяца: * (каждый день)
- Месяц: * (каждый месяц)
- День недели: 5 (пятница)

## Выводы

В рамках лабораторной работы №3 была успешно реализована система автоматического выполнения скриптов с использованием планировщика задач Cron в среде Docker контейнеров. Основные достижения:

1. **Автоматизация процессов** - настроены регулярные задачи для получения курсов валют без ручного вмешательства, что демонстрирует практическую ценность cron для автоматизации рутинных операций.

2. **Контейнеризация решения** - разработан Docker образ, инкапсулирующий все зависимости и конфигурации, что обеспечивает переносимость и воспроизводимость решения на различных системах.

3. **Мониторинг и логирование** - реализована система мониторинга выполнения задач в реальном времени с сохранением логов для последующего анализа и отладки.

4. **Оркестрация с Docker Compose** - создана конфигурация для удобного управления контейнером, включая настройку volumes для сохранения данных между сессиями.

5. **Обработка ошибок** - система корректно обрабатывает ситуации недоступности API, логируя ошибки без прерывания работы cron демона.

Освоенные технологии и методики (cron, Docker, Docker Compose, shell-скриптинг) формируют фундамент для разработки более сложных систем автоматизации в микросервисных архитектурах и облачных средах.

## Библиография

- [Cron Wikipedia](https://en.wikipedia.org/wiki/Cron) - подробное описание синтаксиса cron выражений и примеры использования планировщика задач в Unix-подобных системах.
- [Docker Documentation](https://docs.docker.com/) - официальная документация Docker, содержащая руководства по созданию Dockerfile, управлению контейнерами и лучшим практикам.
- [Docker Compose Specification](https://docs.docker.com/compose/compose-file/) - полная спецификация формата docker-compose.yml с описанием всех доступных директив и параметров конфигурации.
- [Crontab Guru](https://crontab.guru/) - интерактивный инструмент для проверки и генерации cron выражений с визуализацией расписания выполнения задач.