import Vue from 'vue/dist/vue.js';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        settings: {
            component: null,
            token: null,
            error: null
        },
    },
    mutations: {
        setSettingsComponent(state, component) {
            state.settings.component = component;
        },
        setToken(state, token) {
            state.settings.token = token;
        },
        setError(state, error) {
            state.settings.error = error;
        },
        clearError(state) {
            state.settings.error = null;
        }
    }
})
