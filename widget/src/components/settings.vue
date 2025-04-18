<script>
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({name: "Settings"})
export default class Settings extends Vue {
  static $el = '.widget_settings_block';
  @Prop() widget;

  token = '';
  errorMessage = '';

  /**
   * Заполняет значение в импут
   */
  created() {
    this.token = this.widget.get_settings().api_token || '';
  }

  /**
   * @param message {string}
   */
  setError(message) {
    this.errorMessage = message;
  }

  /**
   * Сохраняет токен в хранилище приложения
   * @returns {Promise<void>}
   */
  async saveToken() {
    if (!this.token) {
      this.setError('Telegram token is required');
      return;
    }
    this.$emit('save-token', this.token);
  }

  /**
   * Вычисляемое свойство для проверки, должна ли кнопка быть отключена
   * @returns {boolean}
   */
  get isSaveButtonDisabled() {
    const apiToken = this.widget.get_settings().api_token || '';
    // Кнопка отключена, если:
    // 1. token пустой
    // 2. оба значения (token и api_token) пустые (включено в первое условие)
    // 3. token равен api_token
    return !this.token || this.token === apiToken;
  }

  /**
   * @returns {string}
   */
  get widgetCode() {
    /** @var widget_code код текущего виджета */
    return this.widget.params.widget_code;
  }

  /**
   * @returns {string}
   */
  get widgetId() {
    return this.widget.params.id;
  }
}
</script>

<template>
  <div class="widget_settings_block">
    <div class="widget_settings_block__descr">
      <svg class="widget-settings-block__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24"
           height="24">
        <path
            d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394a.759.759 0 0 1-.6.295h-.002l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.643-.203-.658-.643.136-.953l11.57-4.458c.537-.232 1.006.128.832.941z"/>
      </svg>
      <h2 class="widget-settings-block__title">Интеграция с Telegram</h2>
    </div>
    <details class="guide-accordion">
      <summary class="guide-summary">Как создать Telegram-бота и получить API-токен</summary>
      <div class="guide-content">
        <section>
          <h3>Создание нового бота</h3>
          <p>Чтобы создать нового бота в Telegram, выполните следующие шаги:</p>
          <ol>
            <li>Откройте Telegram и найдите @BotFather</li>
            <li>Отправьте команду <code>/newbot</code></li>
            <li>Укажите имя и username для бота (username должен заканчиваться на <code>@</code>, например,
              <code>@MyBot</code>)
            </li>
            <li>Скопируйте API-токен, который BotFather отправит в ответном сообщении</li>
          </ol>
        </section>

        <section>
          <h3>Получение токена для существующего бота</h3>
          <p>Если у вас уже есть бот, выполните следующие действия:</p>
          <ol>
            <li>Откройте чат с @BotFather в Telegram</li>
            <li>Отправьте команду <code>/mybots</code></li>
            <li>Выберите нужный бот из списка и нажмите API Token</li>
            <li>Скопируйте полученный токен</li>
          </ol>
        </section>

        <p class="note"><strong>Примечание:</strong> Сохраните API-токен в безопасном месте. Не передавайте его третьим
          лицам, так как он предоставляет полный доступ к вашему боту.</p>
      </div>
    </details>

    <div class="widget_settings_block__fields" id="widget_settings__fields_wrapper">
      <div class="widget_settings_block__item_field">
        <div class="widget_settings_block__title_field" title="">Для активации интеграции введите токен и нажмите кнопку
          сохранить:
        </div>
        <div class="widget_settings_block__input_field">
          <input name="api_token"
                 v-model="token"
                 class="widget_settings_block__controls__ text-input"
                 type="text"
                 placeholder="Введите токен от вашего Telegram Bot`a"
                 autocomplete="off">
        </div>
        <div :class="['widget_settings_block__error', `widget_settings_block__error'${widgetCode}`]">
          {{ errorMessage }}
        </div>
      </div>
      <div class="widget_settings_block__controls widget_settings_block__controls_top">
        <button type="button"
                data-onsave-destroy-modal="true"
                @click="saveToken"
                :data-id="widgetId"
                :disabled="isSaveButtonDisabled"
                :class="['button-input', 'js-widget-save', 'button-input-disabled', { 'custom-button': isSaveButtonDisabled }]"
                tabindex=""
                :id="'save_' + widgetCode">
          <span class="button-input-inner ">
            <span class="button-input-inner__text">Сохранить</span>
          </span>
        </button>
      </div>
      <div class="switcher_wrapper">
        <label for="widget_active__sw"
               class="switcher switcher__on switcher_blue widget-settings__switcher"
               id="">
        </label>
        <input type="checkbox" value="Y" name="widget_active"
               id="widget_active__sw" class="switcher__checkbox"
               checked="">
      </div>
    </div>
  </div>
</template>

<style scoped>
.widget_settings_block {
  background: #FFFFFF;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  padding: 32px;
  font-family: 'Inter', system-ui, sans-serif;
}

.widget-settings-block__title {
  font: revert;
}

.widget_settings_block__descr {
  display: flex;
  align-items: center;
  margin-bottom: 32px;
  gap: 16px;
  padding-bottom: 24px;
  border-bottom: 1px solid #F0F2F5;
}

.widget_settings_block__descr svg {
  width: 40px;
  height: 40px;
  fill: #28A8EA;
  flex-shrink: 0;
}

.widget_settings_block__descr p {
  font-size: 24px;
  font-weight: 600;
  color: #1A1D1F;
  margin: 0;
}

.widget_settings_block__fields {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.widget_settings_block__item_field {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.widget_settings_block__title_field {
  font-size: 14px;
  color: #6F767E;
  line-height: 1.5;
  font-weight: 500;
}

.widget_settings_block__input_field {
  position: relative;
  width: 100%;
}

.widget_settings_block__controls__.text-input {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid #EFEFEF;
  border-radius: 12px;
  font-size: 15px;
  color: #1A1D1F;
  transition: all 0.25s ease;
  background: #FCFCFC;
  padding-left: 48px;
}

.widget_settings_block__controls__.text-input:focus {
  outline: none;
  border-color: #28A8EA;
  box-shadow: 0 0 0 4px rgba(40, 168, 234, 0.15);
  background: #FFFFFF;
}

.widget_settings_block__controls__.text-input::placeholder {
  color: #9A9FA5;
}

.widget_settings_block__input_field:before {
  content: '';
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
}

.widget_settings_block__controls_top {
  margin-top: 16px;
  /*padding-top: 24px;*/
}

.button-input {
  background: #28A8EA;
  border: none;
  border-radius: 12px;
  padding: 14px 28px;
  color: #FFFFFF;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.25s ease;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.button-input:hover {
  background: #1EB4FF;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(40, 168, 234, 0.25);
}

.button-input-disabled {
  background: #EFEFEF;
  color: #9A9FA5;
  cursor: not-allowed;
  box-shadow: none;
  transform: none !important;
}

.button-input[disabled] {
  pointer-events: none;
  opacity: 0.7;
}

.switcher_wrapper {
  margin-top: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.switcher__checkbox {
  position: absolute;
  opacity: 0;
}

.switcher {
  position: relative;
  width: 48px;
  height: 28px;
  background: #EFEFEF;
  border-radius: 14px;
  transition: background 0.25s ease;
  cursor: pointer;
}

.switcher:before {
  content: '';
  position: absolute;
  left: 4px;
  top: 4px;
  width: 20px;
  height: 20px;
  background: #FFFFFF;
  border-radius: 50%;
  transition: transform 0.25s ease;
}

.switcher__checkbox:checked + .switcher {
  background: #28A8EA;
}

.switcher__checkbox:checked + .switcher:before {
  transform: translateX(20px);
}

/* Базовые переменные */
:root {
  --tg-widget-background-primary: #FFFFFF;
  --tg-widget-background-secondary: #FCFCFC;
  --tg-widget-text-primary: #1A1D1F;
  --tg-widget-text-secondary: #6F767E;
  --tg-widget-border-default: #EFEFEF;
  --tg-widget-primary-color: #28A8EA;
  --tg-widget-icon-color: #9A9FA5;
  --tg-widget-disabled-bg: #EFEFEF;
}

/* Темная тема */
:root[data-color-scheme="dark"] {
  --tg-widget-background-primary: #0f2231;
  --tg-widget-background-secondary: #1A1D1F;
  --tg-widget-text-primary: #F2F2F2;
  --tg-widget-text-secondary: #92989B;
  --tg-widget-border-default: #363B44;
  --tg-widget-primary-color: #3DB4F2;
  --tg-widget-icon-color: #6B6D72;
  --tg-widget-disabled-bg: #363B44;
}

.widget_settings_block {
  background: var(--tg-widget-background-primary);
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  padding: 32px;
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--tg-widget-text-primary);
  transition: all 0.3s ease;
}

.widget_settings_block__descr {
  border-bottom-color: var(--tg-widget-border-default);
}

.widget_settings_block__descr p {
  color: var(--tg-widget-text-primary);
}

.widget_settings_block__title_field {
  color: var(--tg-widget-text-secondary);
}

.widget_settings_block__controls__.text-input {
  border-color: var(--tg-widget-border-default);
  background: var(--tg-widget-background-secondary);
  color: var(--tg-widget-text-primary);
}

.widget_settings_block__controls__.text-input:focus {
  border-color: var(--tg-widget-primary-color);
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--tg-widget-primary-color) 15%, transparent);
}

.widget_settings_block__controls__.text-input::placeholder {
  color: var(--tg-widget-text-secondary);
}

.widget_settings_block__input_field:before {
  filter: invert(25%);
}

.button-input {
  color: var(--tg-widget-text-primary);
}

.button-input-disabled {
  background: var(--tg-widget-disabled-bg);
  color: var(--tg-widget-text-secondary);
}

.switcher {
  background: var(--tg-widget-border-default);
}

.switcher__checkbox:checked + .switcher {
  background: var(--tg-widget-primary-color);
}

.widget-settings-block__icon {
  fill: var(--tg-widget-primary-color);
}

.widget_settings_block__controls__.text-input:focus {
  background: var(--tg-widget-background-primary);
}

/* Адаптация иконок */
:root[data-color-scheme="dark"] .widget-settings-block__icon {
  filter: brightness(1.2);
}

/* Сохранение оригинальных классов */
.text-input {
  border-radius: 1vh;
}

.switcher_wrapper {
  margin-top: 16px;
}

.widget_settings_block__input_field {
  width: 90%;
}

.guide-accordion {
  margin: 1.5rem 0;
  border: 1px solid var(--tg-widget-border-default);
  border-radius: var(--tg-widget-radius-md);
  background: var(--tg-widget-background-primary);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;

  &[open] {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);

    .guide-summary::after {
      transform: rotate(180deg);
    }
  }
}

.guide-summary {
  list-style: none;
  cursor: pointer;
  padding: 1rem 1.5rem;
  font-weight: 600;
  color: var(--tg-widget-text-primary);
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: background-color 0.2s ease;
  font-size: 18px;

  &::-webkit-details-marker {
    display: none;
  }

  &:hover {
    background: var(--tg-widget-background-secondary);
  }

  &::after {
    content: '';
    margin-left: auto;
    width: 1.25rem;
    height: 1.25rem;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2328A8EA'%3E%3Cpath d='M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z'/%3E%3C/svg%3E") no-repeat center;
    transition: transform 0.2s ease;
  }
}

.guide-content {
  padding: 1.5rem 2rem 2rem;
  color: var(--tg-widget-text-secondary);
  animation: fadeIn 0.3s ease-out;
  line-height: 1.6;

  section {
    margin-bottom: 1.5rem;

    &:last-child {
      margin-bottom: 0;
    }
  }

  h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--tg-widget-text-primary);
    margin: 12px 0 8px;
  }

  p {
    margin: 8px 0;
  }

  ol {
    padding-left: 24px;
    margin: 8px 0;
  }

  li {
    margin-bottom: 8px;
  }

  code {
    background: var(--tg-widget-disabled-bg);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Consolas', monospace;
    color: var(--tg-widget-text-primary);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.note {
  background: color-mix(in srgb, var(--tg-widget-primary-color) 5%, transparent);
  padding: 1.5rem;
  border-radius: 8px;
  font-style: italic;
  font-size: 1em;
  position: relative;

  &::before {
    content: '!';
    position: absolute;
    left: -1.8rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.5rem;
    height: 1.5rem;
    background: var(--tg-widget-primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
  }
}
</style>