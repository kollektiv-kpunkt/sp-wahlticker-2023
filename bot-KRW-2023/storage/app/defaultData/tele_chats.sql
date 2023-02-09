START TRANSACTION;
INSERT INTO `tele_chats` (`id`, `created_at`, `updated_at`, `chat_id`, `chat_type`, `chat_title`, `chat_username`, `chat_first_name`, `chat_last_name`) VALUES
(1, '2023-02-08 13:43:00', '2023-02-08 13:43:00', '-800629933', 'group', 'Wahlsonntag Zürich 2023', NULL, NULL, NULL)
    ON DUPLICATE KEY
        UPDATE `id` = VALUES(`id`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`), `chat_id` = VALUES(`chat_id`), `chat_type` = VALUES(`chat_type`), `chat_title` = VALUES(`chat_title`), `chat_username` = VALUES(`chat_username`), `chat_first_name` = VALUES(`chat_first_name`), `chat_last_name` = VALUES(`chat_last_name`);

COMMIT;
