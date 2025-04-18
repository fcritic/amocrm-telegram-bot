import Vue from 'vue/dist/vue.js';

import store from './stores/index';

Vue.config.devtools = true;
Vue.config.silent = true;

import {TableComponent, TableColumn} from 'vue-table-component';
import config from "./config";
import {ApiService} from "./service/apiService";
import Settings from './components/settings.vue';

Vue.component('table-component', TableComponent);
Vue.component('table-column', TableColumn);

const Widget = {

    render(self) {
        return true;
    },

    init(self, moment) {
        return true;
    },

    bind_actions() {
        return true;
    },

    settings(self) {
        store.commit('clearError');
        const vm = new Vue({
            store: store,
            render: h => h(Settings, {
                props: {
                    widget: self
                },
                ref: 'settingsComponent',
                on: {
                    'save-token': (token) => {
                        store.commit('setToken', token);
                    }
                }
            }),
        }).$mount(Settings.$el);

        const settingsComponent = vm.$refs.settingsComponent;
        store.commit('setSettingsComponent', settingsComponent);
    },

    advancedSettings() {},

    async onSave(widget, params) {
        store.commit('clearError');

        if (params.active === 'N') return true;

        /**
         * @const APP глобальный объект аккаунта amoCRM
         * @link https://www.amocrm.ru/developers/content/web_sdk/env_variables
         */
        const account = APP.constant('account');
        const component = store.state.settings.component;
        const token = store.state.settings.token;

        if (component || token) {
            console.error('Fatal error');
            throw new Error('The Settings component or token is not defined');
        }

        try {
            const response = await ApiService.request(widget, {
                url: config.URL,
                data: {
                    account_id: account.id,
                    account_uid: account.amojo_id,
                    telegram_token: token,
                },
                clientUuid: widget.params.oauth_client_uuid,
            });

            if (response.code === 200) return true;

            store.commit('setError', response);
            if (component.setError) {
                component.setError(response.message);
            }
            return false;

        } catch (err) {
            store.commit('setError', err);
            component.setError(err.message);
            return false;
        }
    },

    destroy() {},

    contacts_selected() {},

    leads_selected() {},

    tasks_selected() {}
};

export default Widget;
