START TRANSACTION;

INSERT INTO `parties` (`id`, `created_at`, `updated_at`, `partyId`, `name`, `abbreviation`, `color`, `seats_2023`, `seats_2019`, `seats_2015`, `voteShare_2023`, `voteShare_2019`, `voteShare_2015`) VALUES
(1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_1', 'SVP – Schweizerische Volkspartei', 'SVP', '#3B571C', NULL, '45', '54', NULL, 24.46, 30.02),
(2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_2', 'SP Sozialdemokratische Partei', 'SP', '#D93F3C', NULL, '35', '36', NULL, 19.31, 19.67),
(3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_3', 'FDP.Die Liberalen', 'FDP', '#15549D', NULL, '23', '31', NULL, 15.66, 17.33),
(4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_4', 'Grünliberale Partei (GLP)', 'GLP', '#A2CB4F', NULL, '23', '14', NULL, 12.91, 7.64),
(5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_5', 'Grüne', 'Grüne', '#4E751D', NULL, '22', '13', NULL, 11.91, 7.22),
(6, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_6', 'CVP', 'CVP', '#E78222', NULL, '8', '14', NULL, 5.82, 7.50),
(7, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_7', 'Evangelische Volkspartei (EVP)', 'EVP', '#FFFB3F', NULL, '8', '8', NULL, 4.24, 4.27),
(8, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_8', 'AL-Alternative Liste', 'AL', '#DE388A', NULL, '6', '5', NULL, 2.15, 2.98),
(9, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_9', 'Bürgerlich-Demokratische Partei BDP', 'BDP', '#FDDB36', NULL, '0', '5', NULL, 1.53, 2.62),
(10, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_10', 'EDU Eidgenössisch-Demokratische Union', 'EDU', '#D6311B', NULL, '4', '5', NULL, 2.27, 2.66),
(11, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_11', 'HELVIDA', 'Helvida', '#696969', NULL, NULL, NULL, NULL, NULL, NULL),
(12, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_12', 'die Guten', 'die Guten', '#696969', NULL, NULL, NULL, NULL, NULL, NULL),
(13, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2019_13', 'Partei der Arbeit (PdA)', 'PdA', '#BC2925', NULL, NULL, NULL, NULL, NULL, NULL),
(14, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_1', 'SVP – Schweizerische Volkspartei', 'SVP', '#3B571C', NULL, '45', '54', NULL, 24.46, 30.02),
(15, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_2', 'SP Sozialdemokratische Partei', 'SP', '#D93F3C', NULL, '35', '36', NULL, 19.31, 19.67),
(16, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_3', 'FDP.Die Liberalen', 'FDP', '#15549D', NULL, '23', '31', NULL, 15.66, 17.33),
(17, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_4', 'Grünliberale Partei (GLP)', 'GLP', '#A2CB4F', NULL, '23', '14', NULL, 12.91, 7.64),
(18, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_5', 'Grüne', 'Grüne', '#4E751D', NULL, '22', '13', NULL, 11.91, 7.22),
(19, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_6', 'Die Mitte', 'Die Mitte', '#F79E27', NULL, '8', '14', NULL, 5.82, 7.50),
(20, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_7', 'Evangelische Volkspartei (EVP)', 'EVP', '#FFFB3F', NULL, '8', '8', NULL, 4.24, 4.27),
(21, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_8', 'AL-Alternative Liste', 'AL', '#DE388A', NULL, '6', '5', NULL, 2.15, 2.98),
(22, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_9', 'EDU Eidgenössisch-Demokratische Union', 'EDU', '#D6311B', NULL, '4', '5', NULL, 2.27, 2.66),
(23, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_10', 'Aufrecht / Freie Liste', 'AuFL', '#696969', NULL, NULL, NULL, NULL, NULL, NULL),
(24, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_11', 'Partei der Arbeit (PdA)', 'PdA', '#BC2925', NULL, NULL, NULL, NULL, NULL, NULL),
(25, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_12', 'Ja zu einem Wachstumsstopp', 'STOPP', '#696969', NULL, NULL, NULL, NULL, NULL, NULL),
(26, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, '2023_13', 'SansPapiersPolitiques', 'SaPaPo', '#696969', NULL, '0', '0', NULL, 0.00, 0.00)
    ON DUPLICATE KEY
        UPDATE `id` = VALUES(`id`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`), `name` = VALUES(`name`), `abbreviation` = VALUES(`abbreviation`), `color` = VALUES(`color`), `seats_2023` = VALUES(`seats_2023`), `seats_2019` = VALUES(`seats_2019`), `seats_2015` = VALUES(`seats_2015`), `voteShare_2023` = VALUES(`voteShare_2023`), `voteShare_2019` = VALUES(`voteShare_2019`), `voteShare_2015` = VALUES(`voteShare_2015`);

COMMIT;
