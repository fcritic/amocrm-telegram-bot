/**
 * @typedef {Object} TokenResponse
 * @property {string} token - JWT токен
 *
 * @typedef {Object} ApiError
 * @property {string} [detail] - Детали ошибки
 * @property {string} [message] - Сообщение об ошибке
 * @property {number} [code] - Код ошибки
 *
 * @typedef {Object} Widget
 * @property {function(url: string, data: Object, callback: function, format?: string): void} crm_post - Метод для отправки запросов
 */

/**
 * Сервис для работы с API
 * @namespace
 */
export const ApiService = (() => {

    /**
     * Получение одноразового токена для интеграции. По сути реализует this.$authorizedAjax()
     * @link https://www.amocrm.ru/developers/content/oauth/disposable-tokens
     *
     * @param {string} clientUuid ID интеграции
     * @returns {Promise<TokenResponse>} Промис с объектом токена
     * @throws {Error} Ошибка при получении токена
     */
    const getDisposableToken = async (clientUuid) => {
        try {
            const response = await fetch(`/ajax/v2/integrations/${clientUuid}/disposable_token`, {
                method: 'GET',
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                this.handleError(errorData.detail);
            }

            /** @type {TokenResponse} */
            return await response.json();
        } catch (error) {
            this.handleError(error);
        }
    };

    /**
     * Обработка ошибок API
     * @param {unknown} error Ошибка
     * @throws {Error}
     */
    const handleError = (error) => {
        /** @type {string} */
        let errorMessage = 'Unknown error occurred';

        if (error instanceof Error) {
            errorMessage = error;
        }

        console.error('API Error:', errorMessage);
        throw new Error(errorMessage);
    };

    return {
        /**
         * Отправка данных на сервер
         *
         * @param {Widget} widget Экземпляр виджета
         * @param {int} timeout
         * @param {Object} params Параметры запроса
         * @param {string} params.url URL для отправки
         * @param {Object} params.data Данные для отправки
         * @param {string} params.clientUuid ID интеграции
         * @returns {Promise<Object>}
         * @throws {Error} Ошибка при выполнении запроса
         */
        request: async (widget, timeout, {url, data, clientUuid}) => {
            try {
                const {token: jwtToken} = await getDisposableToken(clientUuid);

                return new Promise((resolve, reject) => {
                    let isTimeout = false;
                    const timer = setTimeout(() => {
                        isTimeout = true;
                        reject(new Error('Request timeout'));
                    }, timeout);

                    /**
                     * @link https://www.amocrm.ru/developers/content/integrations/script_js#:~:text=%D0%9C%D0%B5%D1%82%D0%BE%D0%B4%20%D0%B8%D1%81%D0%BF%D0%BE%D0%BB%D1%8C%D0%B7%D1%83%D0%B5%D1%82%D1%81%D1%8F%20%D0%B4%D0%BB%D1%8F%20%D0%BE%D1%82%D0%BF%D1%80%D0%B0%D0%B2%D0%BA%D0%B8%20%D0%B7%D0%B0%D0%BF%D1%80%D0%BE%D1%81%D0%B0
                     * @method crm_post
                     */
                    widget.crm_post(
                        url,
                        {
                            ...data,
                            jwt_token: jwtToken
                        },
                        /** @param {ApiError & {message: string, code: number}} response */
                        (response) => {
                            if (isTimeout) return;

                            clearTimeout(timer);
                            response.code === 200 ? resolve(response) : reject(response);
                        },
                        'json'
                    );
                });
            } catch (error) {
                handleError(error);
            }
        }
    };
})();
