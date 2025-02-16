<?php

/**
 * mediadbsync module
 * export days for events into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2017, 2019-2025 Gustaf Mossakowski
 */


function mod_mediadbsync_get_days($vars) {
	$sql = 'SELECT CONCAT(events.event_id, "-", dates.date) AS `objects[foreign_key]`
			, "film" AS `objects[class_name]`
			, CONCAT(contact_abbr, "/", events.identifier, "/Photos") AS `objects[path]`
			, IF(dates.date < events.date_begin, "00",
				IF(dates.date > events.date_end, "99",
				DATE_FORMAT(dates.date, "%%m-%%d"))
			) AS `objects[title][-id]`
			, IF(dates.date < events.date_begin, "00",
				IF(dates.date > events.date_end, "99",
				DATE_FORMAT(dates.date, "%%m-%%d"))
			) AS `objects[identifier]`
			, IF(dates.date < events.date_begin, "vorab",
				IF(dates.date > events.date_end, "danach",
			CONCAT(CASE(WEEKDAY(dates.date))
				WHEN 0 THEN "Mo"
				WHEN 1 THEN "Di"
				WHEN 2 THEN "Mi"
				WHEN 3 THEN "Do"
				WHEN 4 THEN "Fr"
				WHEN 5 THEN "Sa"
				WHEN 6 THEN "So"
				END, " ", DATE_FORMAT(dates.date, "%%d.%%m."))
			)) AS `objects[title][deu]`
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		LEFT JOIN dates
			ON dates.date >= DATE_SUB(events.date_begin, INTERVAL 1 DAY)
			AND dates.date <= DATE_ADD(events.date_end, INTERVAL 1 DAY)
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts USING (contact_id)
		WHERE categories.main_category_id = /*_ID categories events _*/
		AND NOT ISNULL(date_begin)
		AND takes_place = "yes"
		AND DAY(date_begin) != 0
		AND DAY(date_end) != 0
		AND NOT ISNULL(dates.date)
		ORDER BY events.date_begin, dates.date, events.event_id
	';
	$data = wrap_db_fetch($sql, 'objects[foreign_key]');
	return $data;
}
