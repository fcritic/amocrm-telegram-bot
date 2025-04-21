<p align="center">
  <h1 align="center">Интеграция Telegram и amoCRM</h1>
  <p align="center">Двусторонняя синхронизация чатов между Telegram и amoCRM</p>

  <p align="center">
    <a href="https://php.net">
      <img src="https://img.shields.io/badge/PHP-8.2%2B-blue.svg?style=flat-square" alt="PHP">
    </a>
    <a href="https://docker.com">
      <img src="https://img.shields.io/badge/Docker-27.5%2B-2496ED.svg?style=flat-square" alt="Docker">
    </a>
    <a href="LICENSE">
      <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Лицензия">
    </a>
  </p>
</p>

## Основные возможности

- Двусторонняя синхронизация сообщений
- Поддержка вложений:
    - Текстовые сообщения
    - Файлы
    - Изображения
    - Голосовые сообщения
    - Стикеры
    - Контакты
    - Видео
    - Локация
- Реакции на сообщения (эмодзи)
- Уведомления о печатание
- Изменения статуса сообщения в amoCRM
- Подключения канала чатов к аккаунту
- Редактирование сообщений с синхронизацией
- Безопасная аутентификация через OAuth 2.0
- Асинхронная обработка через очереди
- Обновления токенов из amoCRM с помощью крона
- Безопасное получения файлов из Telegram с помощью прокси обработчика

___

## Требования

- Docker 27.5+
- PHP 8.2+
- MySQL 8.0+
- Composer 2.7+

___

## Быстрый старт

1. Клонируйте репозиторий:
```bash
  git clone git@github.com:fcritic/amocrm-telegram-bot.git
  cd amocrm-telegram-integration
```

#

2. Настройте окружение:

```bash
  cp .env.example .env
# Отредактируйте .env файл
```

#

3. Установите конфигурацию
    - Поменяйте в файле ```integrations.global.php``` host

#

4. Запустите систему:

- Сборка Docker-образа на основе ```Dockerfile``` и файлов из текущей папки
> ```bash
>  docker build -t application-backend .
>```


- Поднимает все сервисы из ```docker-compose.yml``` и запускает их в фоне
> ```bash
> docker-compose up -d
> ```

#

5. Выполните миграции из контейнера ```application-backend```:


- Открываем shell-сессию внутри контейнера
> ```bash
> docker exec -it application-backend sh
> ```

- Выполняем миграции
> ```bash
> vendor/bin/phpmig migrate
> ```

- При необходимости можете откатить все миграции
> ```bash
> vendor/bin/phpmig phpmig rollback -t 0
> ```

#

6. Запустите воркеры из контейнера ```application-backend```:

- Запуска воркера для вебхуков из API чатов amoCRM ```AmoJoQueueWorker```
> ```bash
> php console.php app:amojo:sync-message
> ```

- Запуска воркера для вебхуков из Telegram ```TelegramQueueWorker```
> ```bash
> php console.php app:telegram:sync-message
> ```

___

## Настройка интеграций
#### Для amoCRM:

1. Создайте приватную/публичную(требуется тех.аккаунт) интеграцию в amoCRM amoМаркет

    - Укажите redirect_uri: ```https://ваш-домен/api/amocrm/installing-integration```
    - Множественные источники: поддерживает
    - <a href="https://www.amocrm.ru/developers/content/chats/chat-start">Загрузите виджет</a>
    - <a href="https://github.com/fcritic/amocrm-telegram-bot/tree/master/widget">Пример OAuth 2.0 по шагам</a>

3. Зарегистрируйте канал чатов с технической поддержки amoCRM

    - Укажите Webhook URL ```https://ваш-домен/api/amocrm/webhook/amojo/{scope_id}```
    - <a href="https://www.amocrm.ru/developers/content/chats/chat-start">Начало работы c API чатов amoCRM</a>

#

#### Для Telegram:
Создайте бота через @BotFather

___

## Лицензия
Проект распространяется под лицензией MIT - подробности в файле <a href="LICENSE">LICENSE</a>

