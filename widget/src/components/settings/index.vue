<script>
import {Component, Prop, Vue} from 'vue-property-decorator';
import template from './template.html';
import './styles.scss';

@Component({
  name: "Settings",
  template
})
export default class Settings extends Vue {
  static $el = '.widget_settings_block';
  @Prop() widget;

  token = '';
  errorMessage = '';

  created() {
    // Инициализируем token из widget.get_settings().api_token, если он есть
    this.token = this.widget.get_settings().api_token || '';
  }

  /**
   * @param message {string}
   */
  setError(message) {
    console.log('сет мессаджа в компоненте сетинга', message);
    this.errorMessage = message;
  }

  saveToken() {
    console.log('токен из компонента сетингс', this.token);
    if (!this.token) {
      this.setError('Telegram token is required');
      return;
    }
    this.$emit('save-token', this.token);
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
