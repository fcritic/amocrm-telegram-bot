<p align="center">
  <h1 align="center">Vue.js Widget для интеграции Telegram-бота с amoCRM</h1>

  <p align="center">
    <a href="https://nodejs.org">
      <img src="https://img.shields.io/badge/Node.js-16.20.2-green.svg?style=flat-square&logo=node.js" alt="Node.js">
    </a>
    <a href="https://vuejs.org">
      <img src="https://img.shields.io/badge/Vue.js-2.x-4FC08D.svg?style=flat-square&logo=vue.js" alt="Vue.js">
    </a>
    <a href="LICENSE">
      <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Лицензия">
    </a>
    <img src="https://img.shields.io/badge/Webpack-4.46.0-8dd6f9?style=flat-square&logo=webpack" alt="Webpack">
    <img src="https://img.shields.io/badge/npm-8.19.4-CB3837?style=flat-square&logo=npm" alt="npm"
  </p>
</p>



**Назначение проекта**\
Виджет предназначен для интеграции Telegram-бота с платформой amoCRM. Основные возможности:

- Управление API-токеном Telegram-бота напрямую из интерфейса amoCRM
- Сохранение токена в безопасном хранилище на стороне backend-приложения
- Валидация запроса
- Поддержка светлой/темной темы интерфейса
- Генерация ZIP-архива для установки виджета в amoCRM

---

**Технологический стек**

- **Vue.js 2** + **Vuex** (управление состоянием)
- **Webpack 4** (сборка проекта)
- **Vue Property Decorator** (синтаксис TypeScript для Vue)
- **SCSS** (стилизация с поддержкой CSS-переменных для тем)
- **amoCRM Web SDK** (интеграция с API amoCRM)

---

**Структура проекта**

```
Widget --/amocrm-telegram-bot/widget
├── node_modules/         # Зависимости проекта
├── src/
│   ├── components/       # Vue-компоненты
│   ├── service/          # Сервисы (API, утилиты)
│   │   └── apiService.js # Логика запросов к API
│   ├── stores/           # Vuex-хранилища
│   ├── app.js            # Конфигурационные константы
│   └── config.js         # Точка входа
├── widget/
│   ├── i18n/             # Локализаций
│   ├── images/           # Иконки и логотипы
│   ├── app.js            # Сборка приложения
│   ├── manifest.json     # Метаданные виджета для amoCRM
│   └── script.js         # Скрипт который будет активирован в указанном locations
├── package.json          # Зависимости и скрипты
└── webpack.config.js     # Конфигурация сборки
```

---

**Быстрый старт**\
**Требования к окружению**

- **Node.js** v16.x (рекомендуется 16.20.2)
- **npm** v8.x (рекомендуется 8.19.4)

**Установка**

1. Клонировать репозиторий:

   ```bash
   git clone https://github.com/fcritic/amocrm-telegram-bot.git
   cd amocrm-telegram-bot/widget
   ```

2. Установить зависимости:

   ```bash
   npm install
   ```

**Команды**

- **Разработка (hot-reload)**:

  ```bash
  npm run dev
  ```

  Запускает dev-сервер на `localhost:8080`

- **Сборка production**:

  ```bash
  npm run build
  ```

  Генерирует файлы в `/widget`

- **Создание ZIP-архива**:

  ```bash
  npm run build
  ```

  После сборки автоматически создает `widget.zip` в корне проекта

---

**Интеграция с amoCRM**\
В `src/config.js` задайте эндпоинты API:

```javascript
export default {
  URL: 'https://your-domain.com/api/endpoint',
  TIMEOUT: 5000
}
```

---

**Особенности реализации**\
**Работа с токенами**

- **Disposable Token**:\
  Генерируется через `ApiService.getDisposableToken()`\
  Передается вместе с запросом на сторонние ресурсы из Web интерфейса amoCRM.\
  Используется для валидации запроса на вашем backend-приложение

  Подробнее https://www.amocrm.ru/developers/content/oauth/disposable-tokens

  ```javascript
  widget.crm_post(url, { jwt_token }, callback, 'json')
  ```

---

**Темная тема**\
Стили используют CSS-переменные:

```css
:root[data-color-scheme="dark"] {
  --tg-widget-background-primary: #0f2231;
  --tg-widget-text-primary: #F2F2F2;
}
```

---

**Документация**\
Официальная документация amoCRM:
- <a href="https://www.amocrm.ru/developers/content/web_sdk/start">Возможности</a>
- <a href="https://www.amocrm.ru/developers/content/web_sdk/mechanics">Механика работы</a>
- <a href="https://www.amocrm.ru/developers/content/integrations/script_js">JS-Виджет </a>

---

**Лицензия**\
Проект распространяется под лицензией MIT - подробности в файле <a href="LICENSE">LICENSE</a>