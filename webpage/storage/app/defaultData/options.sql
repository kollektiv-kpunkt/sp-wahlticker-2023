START TRANSACTION;

INSERT INTO `options` (`id`, `created_at`, `updated_at`, `key`, `value`) VALUES
(1, '2023-02-08 13:43:00', '2023-02-08 13:43:00', 'telegram_channel_id', '-800629933')
    ON DUPLICATE KEY
        UPDATE `id` = VALUES(`id`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`), `key` = VALUES(`key`), `value` = VALUES(`value`);

COMMIT;
