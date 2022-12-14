<?php

/**
 * mediadbsync module
 * export usergroups for events into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2016-2017, 2019-2022 Gustaf Mossakowski
 */


function mod_mediadbsync_get_usergroups($params) {
	if (empty($params)) return mod_mediadbsync_get_usergroups_orga();
	if (count($params) !== 1) return false;
	switch ($params[0]) {
		case 'orga': return mod_mediadbsync_get_usergroups_orga();
		case 'gremien': return mod_mediadbsync_get_usergroups_gremien();
		case 'teilnehmer': return mod_mediadbsync_get_usergroups_teilnehmer('teilnehmer');
		case 'referent': return mod_mediadbsync_get_usergroups_teilnehmer('referent');
	}
	return false;
}

/**
 * DSJ/2016/dvm-u10/Team/Schiedsrichter
 */
function mod_mediadbsync_get_usergroups_orga() {
	$sql = 'SELECT CONCAT(event_id, "-", usergroup_id) AS `objects[foreign_key]`
			, usergroups.identifier AS `objects[identifier]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/Team") AS `objects[path]`
			, usergroup AS `objects[title][deu]`
			, "group" AS `objects[category]`

			, participation_id AS `objectrelations[foreign_key]`
			, "member" AS `objectrelations[relation_type_property]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/Team/", usergroups.identifier) AS `objectrelations[parent_object]`
			, CONCAT("Personen/", contacts.identifier) AS `objectrelations[child_object]`
			, "" AS `objectrelations[role_property]`

			, participation_id AS `access_rights[foreign_key]`
			, "yes" AS `access_rights[show_access]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier) AS `access_rights[object]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/Team/", usergroups.identifier) AS `access_rights[group_object]`
			, "delete" AS `access_rights[access_right_property]`
			
		FROM usergroups
		JOIN participations USING (usergroup_id)
		JOIN events USING (event_id)
		JOIN contacts USING (contact_id)
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts organisationen
			ON websites.contact_id = organisationen.contact_id
		WHERE usergroups.usergroup_category_id = %d
		AND (NOT ISNULL(events.date_begin) OR NOT ISNULL(events.date_end))
		ORDER BY event_id, usergroup_id, participation_id';
	$sql = sprintf($sql, wrap_category_id('gruppen/organisatoren'));
	$data = wrap_db_fetch($sql, 'objects[foreign_key]', 'numeric');
	return $data;
}

function mod_mediadbsync_get_usergroups_gremien() {
	$sql = 'SELECT usergroup_id AS `objects[foreign_key]`
			, usergroups.identifier AS `objects[identifier]`
			, "Gruppen" AS `objects[path]`
			, usergroups.identifier AS `objects[title][-id]`
			, usergroup AS `objects[title][deu]`
			, "group" AS `objects[category]`

			, participation_id AS `objectrelations[foreign_key]`
			, "member" AS `objectrelations[relation_type_property]`
			, CONCAT("Gruppen/", usergroups.identifier) AS `objectrelations[parent_object]`
			, CONCAT("Personen/", contacts.identifier) AS `objectrelations[child_object]`
			, "" AS `objectrelations[role_property]`
			
		FROM usergroups
		JOIN participations USING (usergroup_id)
		JOIN contacts USING (contact_id)
		WHERE usergroups.usergroup_category_id = %d
		AND (ISNULL(participations.date_end)
			OR DATE_ADD(participations.date_end
			, INTERVAL IFNULL(SUBSTRING_INDEX(SUBSTRING_INDEX(usergroups.parameters, "transition_days=", -1), "&", 1), 0) DAY
			) > CURDATE()
		)
		ORDER BY usergroup_id, participation_id';
	$sql = sprintf($sql, wrap_category_id('gruppen/gremien'));
	$data = wrap_db_fetch($sql, 'objects[foreign_key]', 'numeric');
	return $data;
}

function mod_mediadbsync_get_usergroups_teilnehmer($key) {
	$sql = 'SELECT CONCAT(event_id, "-", usergroup_id) AS `objects[foreign_key]`
			, IF(usergroup = "Referent", "Referenten", usergroup) AS `objects[identifier]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier) AS `objects[path]`
			, IF(usergroup = "Referent", "Referenten", usergroup) AS `objects[title][deu]`
			, "group" AS `objects[category]`

			, participation_id AS `objectrelations[foreign_key]`
			, "member" AS `objectrelations[relation_type_property]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/", IF(usergroup = "Referent", "Referenten", usergroup)) AS `objectrelations[parent_object]`
			, CONCAT("Personen/", contacts.identifier) AS `objectrelations[child_object]`
			, "" AS `objectrelations[role_property]`

			, participation_id AS `access_rights[0][foreign_key]`
			, "yes" AS `access_rights[0][show_access]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier) AS `access_rights[0][object]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/", IF(usergroup = "Referent", "Referenten", usergroup)) AS `access_rights[0][group_object]`
			, "list" AS `access_rights[0][access_right_property]`

			, CONCAT(participation_id, "-2") AS `access_rights[1][foreign_key]`
			, "no" AS `access_rights[1][show_access]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/Materialien") AS `access_rights[1][object]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/", IF(usergroup = "Referent", "Referenten", usergroup)) AS `access_rights[1][group_object]`
			, IF(usergroup = "Referent", "delete-own", "read") AS `access_rights[1][access_right_property]`

			, CONCAT(participation_id, "-3") AS `access_rights[2][foreign_key]`
			, "no" AS `access_rights[2][show_access]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/Photos") AS `access_rights[2][object]`
			, CONCAT(organisationen.contact_abbr, "/", events.identifier, "/", IF(usergroup = "Referent", "Referenten", usergroup)) AS `access_rights[2][group_object]`
			, "delete-own" AS `access_rights[2][access_right_property]`

			, "list_display_table" AS `folder_settings[0][folder_property]`
			, "0" AS `folder_settings[0][setting]`
			, "0" AS `folder_settings[0][inheritance]`
			, CONCAT(event_id, "-", usergroup_id) AS `folder_settings[0][foreign_key]`

			, "exclude_from_search" AS `folder_settings[1][folder_property]`
			, "1" AS `folder_settings[1][setting]`
			, "1" AS `folder_settings[1][inheritance]`
			, CONCAT(event_id, "-", usergroup_id, "-2") AS `folder_settings[1][foreign_key]`

		FROM usergroups
		JOIN participations USING (usergroup_id)
		JOIN events USING (event_id)
		JOIN persons USING (contact_id)
		JOIN contacts USING (contact_id)
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts organisationen
			ON websites.contact_id = organisationen.contact_id
		WHERE usergroups.usergroup_id = %d
		AND (NOT ISNULL(events.date_begin) OR NOT ISNULL(events.date_end))
		ORDER BY event_id, usergroup_id, participation_id';
	$sql = sprintf($sql, wrap_id('usergroups', $key));
	$data = wrap_db_fetch($sql, 'objects[foreign_key]', 'numeric');
	return $data;
}
