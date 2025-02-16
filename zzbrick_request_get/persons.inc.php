<?php

/**
 * mediadbsync module
 * export persons (only if they participated somewhere) into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2015, 2019, 2021-2022, 2025 Gustaf Mossakowski
 */


function mod_mediadbsync_get_persons($vars) {
	$sql = 'SELECT DISTINCT person_id AS `objects[foreign_key]`
			, identifier AS `objects[identifier]`
			, "Personen" AS `objects[path]`
			, "person" AS `objects[class_name]`
			, IF(sex = "female", "woman", IF(sex = "male", "man", NULL)) AS `objects[sub_class_name]`
			, contact AS `objects[title][---]`
			, identifier AS `objects[title][-id]`
		FROM persons
		JOIN participations USING (contact_id)
		JOIN contacts USING (contact_id)
		WHERE first_name != "unbekannt" AND last_name != "unbekannt"';
	$data = wrap_db_fetch($sql, 'objects[foreign_key]');
	
	// @todo E-Mail-Adresse auslesen
	return $data;
}
