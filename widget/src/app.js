import Vue from 'vue/dist/vue.js';

import store from './stores/index';

Vue.config.devtools = true;
Vue.config.silent = true;

import {TableComponent, TableColumn} from 'vue-table-component';
import config from "./config";
import {ApiService} from "./service/apiService";
import Settings from './components/settings/index.vue';

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
            store,
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

    advancedSettings() {

    },

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

        console.log('стор', {
            'компонент': component,
            'токен': token
        });

        try {
            const response = await ApiService.request(widget, {
                url: config.WEBHOOK_URL,
                data: {
                    account_id: account.id,
                    account_uid: account.amojo_id,
                    telegram_token: token,
                },
                clientUuid: widget.params.oauth_client_uuid,
            });

            console.log('после респонса', response);

            if (response.code === 200) return true;


            store.commit('setError', response);
            if (component && component.setError) {
                component.setError(response.message || 'Произошла ошибка');
            }
            return false;

        } catch (err) {
            console.log('экзепшен', err);
            store.commit('setError', err);
            console.log('компонент сетинга в экзепшене', component);
            console.log('сеттим ошибку');
            component.setError(err.message || 'Произошла ошибка');
            return false;
        }
    },

    destroy() {

    },

    contacts_selected() {

    },

    leads_selected() {

    },

    tasks_selected() {

    }
};

export default Widget;
