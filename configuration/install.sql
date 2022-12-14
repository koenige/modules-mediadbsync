/**
 * mediadbsync module
 * SQL for installation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2022 Gustaf Mossakowski
 */


-- dates --
CREATE OR REPLACE VIEW `dates_digits` AS
	SELECT 0 AS `digit`
	UNION ALL SELECT 1 AS `1`
	UNION ALL SELECT 2 AS `2`
	UNION ALL SELECT 3 AS `3`
	UNION ALL SELECT 4 AS `4`
	UNION ALL SELECT 5 AS `5`
	UNION ALL SELECT 6 AS `6`
	UNION ALL SELECT 7 AS `7`
	UNION ALL SELECT 8 AS `8`
	UNION ALL SELECT 9 AS `9`;

CREATE OR REPLACE VIEW `dates_numbers` AS
	SELECT (((`ones`.`digit` + (`tens`.`digit` * 10)) + (`hundreds`.`digit` * 100)) + (`thousands`.`digit` * 1000)) AS `number`
	FROM (((`dates_digits` `ones` JOIN `dates_digits` `tens`) JOIN `dates_digits` `hundreds`) JOIN `dates_digits` `thousands`)

CREATE OR REPLACE VIEW `dates` AS
	SELECT (CURDATE() - INTERVAL `dates_numbers`.`number` DAY) AS `date`
	FROM `dates_numbers`
	UNION ALL SELECT (CURDATE() + INTERVAL (`dates_numbers`.`number` + 1) DAY) AS `date`
	FROM `dates_numbers`;
