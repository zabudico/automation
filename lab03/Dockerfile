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