START TRANSACTION;
INSERT INTO `tele_chats` VALUES
    (2,'2023-02-08 11:25:29','2023-02-08 11:25:29','1493758515','private',NULL,'lisaschweizer','Lisa','Schweizer'),
    (3,'2023-02-08 18:53:55','2023-02-08 18:53:55','475888222','private',NULL,'kTextMe','kevin',NULL),
    (4,'2023-02-09 13:42:39','2023-02-09 13:42:39','156751956','private',NULL,'NicoSigi','Nico','Sigi')
    ON DUPLICATE KEY
        UPDATE `id` = VALUES(`id`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`), `chat_id` = VALUES(`chat_id`), `chat_type` = VALUES(`chat_type`), `chat_title` = VALUES(`chat_title`), `chat_username` = VALUES(`chat_username`), `chat_first_name` = VALUES(`chat_first_name`), `chat_last_name` = VALUES(`chat_last_name`);

COMMIT;
