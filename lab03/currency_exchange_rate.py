
"""
Currency Exchange Rate Script
Interacts with local API to get currency exchange rates for specified dates.
"""

import requests
import json
import sys
import os
import logging
from datetime import datetime
from argparse import ArgumentParser


# Конфигурация
API_BASE_URL = "http://host.docker.internal:8080/"
API_KEY = "myapi123"
SUPPORTED_CURRENCIES = ["MDL", "USD", "EUR", "RON", "RUS", "UAH"]
DATA_DIR = "data"


def setup_logging():
    """Настройка системы логирования ошибок"""
    logging.basicConfig(
        level=logging.ERROR,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler('error.log', encoding='utf-8'),
            logging.StreamHandler(sys.stderr)
        ]
    )


def validate_date(date_string):
    """Валидация даты и проверка диапазона"""
    try:
        date_obj = datetime.strptime(date_string, '%Y-%m-%d')
        start_date = datetime(2025, 1, 1)
        end_date = datetime(2025, 9, 15)
        
        if not (start_date <= date_obj <= end_date):
            error_msg = f"Date {date_string} is out of range. Must be between 2025-01-01 and 2025-09-15"
            logging.error(error_msg)
            print(f"[ERROR] {error_msg}")
            return False
        return True
    except ValueError:
        error_msg = f"Invalid date format: {date_string}. Use YYYY-MM-DD"
        logging.error(error_msg)
        print(f"[ERROR] {error_msg}")
        return False


def validate_currencies(from_currency, to_currency):
    """Проверка поддерживаемых валют"""
    if from_currency not in SUPPORTED_CURRENCIES:
        error_msg = f"Invalid source currency: {from_currency}. Available: {SUPPORTED_CURRENCIES}"
        logging.error(error_msg)
        print(f"[ERROR] {error_msg}")
        return False
    
    if to_currency not in SUPPORTED_CURRENCIES:
        error_msg = f"Invalid target currency: {to_currency}. Available: {SUPPORTED_CURRENCIES}"
        logging.error(error_msg)
        print(f"[ERROR] {error_msg}")
        return False
    
    if from_currency == to_currency:
        error_msg = f"Source and target currencies cannot be the same: {from_currency}"
        logging.error(error_msg)
        print(f"[ERROR] {error_msg}")
        return False
    
    return True


def get_exchange_rate(from_currency, to_currency, date):
    """Получение курса валют от API"""
    api_url = f"{API_BASE_URL}?from={from_currency}&to={to_currency}&date={date}"
    payload = {"key": API_KEY}
    
    print(f"Requesting rate: {from_currency} -> {to_currency} for {date}")
    
    try:
        response = requests.post(api_url, data=payload, timeout=10)
        response.raise_for_status()
        
        response_data = response.json()
        
        # Проверка ошибок API
        if response_data.get('error'):
            error_message = response_data['error']
            logging.error(f"API Error for {from_currency}->{to_currency} on {date}: {error_message}")
            print(f"[ERROR] API Error: {error_message}")
            return False
        
        # Извлечение данных
        rate_data = response_data.get('data', {})
        exchange_rate = rate_data.get('rate')
        
        if exchange_rate is None:
            error_msg = "No exchange rate data received from API"
            logging.error(error_msg)
            print(f"[ERROR] {error_msg}")
            return False
        
        # Вывод результата
        print(f"\n=== Exchange Rate ===")
        print(f"From: {from_currency}")
        print(f"To: {to_currency}")
        print(f"Date: {date}")
        print(f"Rate: {exchange_rate}")
        
        # Сохранение данных
        save_to_file(rate_data, from_currency, to_currency, date)
        return True
        
    except requests.exceptions.RequestException as e:
        error_msg = f"Network error: {e}"
        logging.error(f"Network error for {from_currency}->{to_currency} on {date}: {e}")
        print(f"[ERROR] {error_msg}")
        return False
    except json.JSONDecodeError as e:
        error_msg = f"Invalid JSON response: {e}"
        logging.error(f"JSON decode error for {from_currency}->{to_currency} on {date}: {e}")
        print(f"[ERROR] {error_msg}")
        return False
    except Exception as e:
        error_msg = f"Unexpected error: {e}"
        logging.error(f"Unexpected error for {from_currency}->{to_currency} on {date}: {e}")
        print(f"[ERROR] {error_msg}")
        return False


def save_to_file(data, from_currency, to_currency, date):
    """Сохранение данных в JSON файл"""
    # Создание директории, если не существует
    if not os.path.exists(DATA_DIR):
        os.makedirs(DATA_DIR)
        print(f"[INFO] Created directory: {DATA_DIR}")
    
    # Формирование имени файла
    filename = f"{from_currency}_{to_currency}_{date}.json"
    filepath = os.path.join(DATA_DIR, filename)
    
    try:
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2, ensure_ascii=False)
        print(f"[SUCCESS] Data saved to: {filepath}")
    except Exception as e:
        error_msg = f"Failed to save file {filepath}: {e}"
        logging.error(error_msg)
        print(f"[ERROR] {error_msg}")


def get_supported_currencies():
    """Получение списка поддерживаемых валют от API"""
    api_url = f"{API_BASE_URL}?currencies"
    payload = {"key": API_KEY}
    
    try:
        response = requests.post(api_url, data=payload, timeout=10)
        response.raise_for_status()
        response_data = response.json()
        
        if response_data.get('error'):
            print(f"[WARNING] Could not fetch currencies from API: {response_data['error']}")
            return SUPPORTED_CURRENCIES
        
        return response_data.get('data', SUPPORTED_CURRENCIES)
    except:
        print(f"[WARNING] Could not fetch currencies from API, using default list")
        return SUPPORTED_CURRENCIES


def main():
    """Основная функция"""
    # Настройка логирования в САМОМ НАЧАЛЕ
    setup_logging()
    
    # Получение актуального списка валют
    global SUPPORTED_CURRENCIES
    SUPPORTED_CURRENCIES = get_supported_currencies()
    
    # Проверка аргументов командной строки
    if len(sys.argv) != 4:
        error_msg = "Usage: python currency_exchange_rate.py FROM_CURRENCY TO_CURRENCY DATE\n" \
                   "Example: python currency_exchange_rate.py USD EUR 2025-01-01"
        logging.error(error_msg)
        print(f"[ERROR] {error_msg}")
        sys.exit(1)
    
    from_currency = sys.argv[1].upper()
    to_currency = sys.argv[2].upper()
    date = sys.argv[3]
    
    print(f"=== Currency Exchange Rate Script ===")
    print(f"Parameters: {from_currency} -> {to_currency} on {date}")
    print(f"Supported currencies: {', '.join(SUPPORTED_CURRENCIES)}\n")
    
    # Валидация параметров
    if not validate_currencies(from_currency, to_currency):
        sys.exit(1)
    
    if not validate_date(date):
        sys.exit(1)
    
    # Получение курса валют
    success = get_exchange_rate(from_currency, to_currency, date)
    
    if success:
        print(f"\n[SUCCESS] Operation completed successfully!")
    else:
        print(f"\n[FAILED] Operation failed. Check error.log for details.")
        sys.exit(1)


if __name__ == "__main__":
    main()
