<?php

/**
 * mediadbsync module
 * export teams of a tournament for events into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2016-2017, 2019-2022, 2025 Gustaf Mossakowski
 */


function mod_mediadbsync_get_teams($vars) {
	$sql = 'SELECT team_id AS `objects[foreign_key]`
			, SUBSTRING_INDEX(teams.identifier, "/", -1) AS `objects[identifier]`
			, CONCAT(contact_abbr, "/", events.identifier, "/Teams") AS `objects[path]`
			, CONCAT(teams.team, IFNULL(CONCAT(" ", team_no), "")) AS `objects[title][deu]`
			, "group" AS `objects[class_name]`
		FROM teams
		LEFT JOIN events USING (event_id)
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts USING (contact_id)
		WHERE teams.spielfrei = "nein"
		AND teams.team_status = "Teilnehmer"
	';
	$data = wrap_db_fetch($sql, 'objects[foreign_key]');
	return $data;
}
