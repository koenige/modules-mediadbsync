<?php

/**
 * mediadbsync module
 * export years for events into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2015, 2017, 2020-2022 Gustaf Mossakowski
 */


function mod_mediadbsync_get_years($vars) {
	$sql = 'SELECT DISTINCT CONCAT(IFNULL(event_year, YEAR(IFNULL(date_begin, date_end))), "-", website_id) AS `objects[foreign_key]`
			, IFNULL(event_year, YEAR(IFNULL(date_begin, date_end))) AS `objects[identifier]`
			, IFNULL(event_year, YEAR(IFNULL(date_begin, date_end))) AS `objects[title][---]`
			, "list" AS `objects[category]`
			, contact_abbr AS `objects[path]`

			, "list_display_table" AS `folder_settings[folder_property]`
			, "0" AS `folder_settings[setting]`
			, "0" AS `folder_settings[inheritance]`
			, CONCAT(IFNULL(event_year, YEAR(IFNULL(date_begin, date_end))), "-", website_id) AS `folder_settings[foreign_key]`

		FROM events
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts USING (contact_id)
		WHERE (NOT ISNULL(date_begin) OR NOT ISNULL(date_end))
	';
	$data = wrap_db_fetch($sql, 'objects[foreign_key]');
	return $data;
}
