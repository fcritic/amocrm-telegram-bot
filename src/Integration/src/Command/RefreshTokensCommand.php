<?php

declare(strict_types=1);

namespace Integration\Command;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Model\Account;
use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use AmoCRM\Service\OAuthService;
use App\Database\BootstrapperInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Обновляет все токены из БД на новую паруя
 */
class RefreshTokensCommand extends Command
{
    protected string $commandName = 'app:refresh-tokens';

    public function __construct(
        private readonly OAuthService $oauthService,
        private readonly AccountRepositoryInterface $accountRepo,
        private readonly BootstrapperInterface $dbBootstrapper,
    ) {
        parent::__construct($this->commandName);
    }

    /**
     * Настраивает базовые параметры команды:
     * - Имя команды
     * - Описание (реализуется в дочерних классах)
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName($this->commandName)
            ->setDescription('Refresh amoCRM access tokens using refresh tokens');
    }

    /**
     * Основной метод выполнения команды:
     * 1. Инициализирует UI для вывода
     * 2. Загружает подключение к БД
     * 3. Получает записи по всем токенам и аккаунтам
     * 4. Обновляет токены и сохраняет их в БД
     * 5. Обрабатывает исключения
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting token refresh process');

        try {
            $this->bootstrapDatabase();

            // Получение всех аккаунтов с токенами
            $accounts = $this->accountRepo->getAllAccountsWithTokens();
            $processed = 0;
            $errors = 0;

            foreach ($accounts as $account) {
                foreach ($account->accessToken as $token) {
                    try {
                        $this->refreshAccountToken($account, $token);
                        $processed++;
                    } catch (\Exception $e) {
                        $errors++;
                        $io->error([
                            'Account' => $account->amo_account_id,
                            'Error' => $e->getMessage()
                        ]);
                    }
                }
            }

            $io->success(sprintf(
                'Processed tokens: %d | Errors: %d',
                $processed,
                $errors
            ));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Critical error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Обновляет токен для указанного аккаунта
     *
     * @param Account $account
     * @param \AmoCRM\Model\AccessToken $token
     * @return void
     * @throws AmoCRMoAuthApiException
     */
    private function refreshAccountToken(Account $account, \AmoCRM\Model\AccessToken $token): void
    {
        $oldToken = new AccessToken([
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires' => $token->expires,
        ]);

        $newToken = $this->oauthService->refreshToken(
            $account->sub_domain,
            $oldToken
        );

        $this->oauthService->saveOAuthToken(
            $newToken,
            $account->sub_domain
        );
    }

    /**
     *  Инициализирует подключение к базе данных.
     *  Вызывается перед запуском воркера для гарантии работоспособности DB-слоя.
     *
     * @return void
     */
    private function bootstrapDatabase(): void
    {
        $this->dbBootstrapper->bootstrap();
    }
}
