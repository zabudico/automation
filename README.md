# Лабораторная работа №5  
Автоматизация конфигурации сервера с помощью Ansible и Jenkins CI/CD

**Выполнил:** [Ваше ФИО]  
**Группа:** [ваша группа]  

## Цель работы
Создать полностью автоматизированный процесс сборки, тестирования и деплоя PHP-приложения с использованием Jenkins и Ansible в Docker-среде.

## Выполненные требования задания
- Создана папка `lab05` и скопированы файлы из `lab04`
- Реализован `compose.yaml` с четырьмя сервисами:
  - `jenkins-controller`
  - `ssh-agent` (с установленным PHP 8.3 и Composer)
  - `ansible-agent` (Ubuntu + Ansible)
  - `test-server` (Ubuntu + Apache2 + PHP + SSH)
- Сгенерированы и настроены SSH-ключи:
  - `jenkins_to_agents` — для подключения Jenkins к агентам
  - `ansible_to_testserver` — для подключения Ansible к тестовому серверу
- Создан пользователь `ansible` на тестовом сервере
- Реализован Ansible playbook `setup_test_server.yml`, который:
  1. Устанавливает Apache2 и необходимые модули PHP
  2. Создаёт директорию `/var/www/php_arrays`
  3. Разворачивает виртуальный хост с `DocumentRoot /var/www/php_arrays/public`
  4. Копирует код проекта с ansible-agent на test-server
- Создано три Jenkins Pipeline:
  1. `01_PHP_Build_and_Test` — сборка и запуск unit-тестов
  2. `02_Ansible_Configure_and_Deploy` — настройка сервера и деплой проекта
  3. `03_Redeploy_Only` — повторный деплой
- После успешного выполнения пайплайна проект доступен по адресу: **http://localhost:8081**

## Структура проекта
```
lab05/
├── compose.yaml
├── php_arrays/                  ← исходный PHP-проект из lab04
│   ├── public/index.php
│   ├── public/simple_tests.php
│   └── src/
├── ansible/
│   ├── ansible.cfg
│   ├── hosts.ini
│   ├── setup_test_server.yml
│   └── templates/myproject.conf
├── pipelines/
│   ├── 01_php_build_test.groovy
│   ├── 02_ansible_configure_and_deploy.groovy
│   └── 03_redeploy_only.groovy
├── secrets/
│   ├── jenkins_to_agents
│   ├── jenkins_to_agents.pub
│   ├── ansible_to_testserver
│   └── ansible_to_testserver.pub
├── Dockerfile.ssh_agent
├── Dockerfile.ansible_agent
├── Dockerfile.test_server
├── screenshots/                 ← сюда положить скриншоты
└── README.md
```

## Запуск системы
```bash
cd lab05
docker compose up -d --build
```
- Jenkins: http://localhost:8080
- Деплойленный проект: http://localhost:8081

## Скриншоты работы

### 1. Jenkins — успешные пайплайны
![Успешные сборки в Jenkins](screenshots/jenkins-success.png)

### 2. Работающий проект на тестовом сервере
![Проект по адресу localhost:8081](screenshots/localhost-8081.png)

### 3. Выполнение Ansible-пайплайна
![Ansible Configure and Deploy — зелёный](screenshots/ansible-pipeline.png)

### 4. Прохождение PHP-тестов
![PHP Build and Test — зелёный](screenshots/php-tests.png)

### 5. Список узлов (агенты онлайн)
![Агенты Jenkins онлайн](screenshots/nodes-online.png)

## Ответы на вопросы задания

### Преимущества использования Ansible для конфигурации серверов
- Идемпотентность — повторный запуск не меняет уже настроенную систему
- Декларативный стиль — описываем желаемое состояние, а не последовательность команд
- Безагентная архитектура — не требуется устанавливать дополнительное ПО на управляемые хосты
- Простой и читаемый YAML-синтаксис
- Большое сообщество и огромное количество готовых модулей

### Другие полезные модули Ansible для управления конфигурацией
- `apt` / `yum` — установка пакетов  
- `service` — управление службами  
- `template` — генерация конфигов с подстановкой переменных  
- `copy`, `synchronize` — копирование файлов  
- `file` — создание директорий, прав доступа  
- `user`, `group` — управление пользователями  
- `git` — клонирование репозиториев  
- `lineinfile`, `blockinfile` — редактирование конфигов  
- `cron` — настройка заданий по расписанию  
- `systemd` — управление systemd-юнитами

### Проблемы, с которыми столкнулся, и их решения
| Проблема                                           | Решение                                                                 |
|----------------------------------------------------|-------------------------------------------------------------------------|
| Интерактивный запрос tzdata при установке Ubuntu  | Добавлены `DEBIAN_FRONTEND=noninteractive` и `TZ=Europe/Kiev`          |
| Пакеты `php81-*` отсутствуют в Alpine 2025        | Перешёл на `php83-*` + создал симлинки `php → php83`, `composer → composer83` |
| Jenkins не подключался к агентам (Host key verification failed) | Вручную создал `/var/jenkins_home/.ssh/known_hosts` и добавил хосты   |
| Apache выдавал 403 Forbidden                       | Правильно настроил DocumentRoot → `/public` и права `www-data`          |
| Ansible не видел приватный ключ                    | Ключ скопирован в контейнер и указан в `ansible.cfg`                    |

## Заключение
Создана полностью рабочая CI/CD-среда:
- Автоматическое тестирование PHP-приложения
- Автоматическая настройка тестового веб-сервера через Ansible
- Однокнопочный деплой проекта
- Всё запускается одной командой `docker compose up`

**Работа выполнена полностью, система протестирована и работает стабильно.**



