<?php

/**
 * mediadbsync module
 * export events into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014, 2017, 2019-2022, 2024-2025 Gustaf Mossakowski
 */


function mod_mediadbsync_get_events($vars) {
	$sql = 'SELECT events.event_id AS `objects[foreign_key]`
			, SUBSTRING_INDEX(events.identifier, "/", -1) AS `objects[identifier]`
			, SUBSTRING_INDEX(events.identifier, "/", -1) AS `objects[title][-id]`
			, events.event AS `objects[title][deu]`
			, "event" AS `objects[class_name]`
			, CONCAT(contact_abbr, "/", IFNULL(event_year, YEAR(IFNULL(date_begin, date_end)))) AS `objects[path]`
			, CONCAT(IFNULL(CONCAT(events.description, "\n\n"), "")
				, IFNULL(CONCAT(" in ", place), "")
			) AS `objects[description][deu]`
			, CONCAT(contact_abbr, "/", events.identifier) AS `times[0][object]`
			, IFNULL(date_begin, date_end) AS `times[0][start_date]`
			, "begin" AS `times[0][time_property]`
			, CONCAT(events.event_id, "-", 0)  AS `times[0][foreign_key]`
			, CONCAT(contact_abbr, "/", events.identifier) AS `times[1][object]`
			, date_end AS `times[1][start_date]`
			, "end" AS `times[1][time_property]`
			, CONCAT(events.event_id, "-", 1) AS `times[1][foreign_key]`

		FROM events
		LEFT JOIN events_contacts events_places
			ON events.event_id = events_places.event_id
			AND events_places.role_category_id = /*_ID categories roles/location _*/
			AND events_places.sequence = 1
		LEFT JOIN addresses
			ON addresses.contact_id = events_places.contact_id
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts
			ON websites.contact_id = contacts.contact_id
		WHERE categories.main_category_id = /*_ID categories events _*/
		AND (NOT ISNULL(date_begin) OR NOT ISNULL(date_end))
	';
	$data = wrap_db_fetch($sql, 'objects[foreign_key]');
	return $data;
}
