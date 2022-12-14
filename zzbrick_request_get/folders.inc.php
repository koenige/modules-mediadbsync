<?php

/**
 * mediadbsync module
 * export folders for events into media database
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/mediadbsync
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014, 2016-2017, 2019-2022 Gustaf Mossakowski
 */


/**
 * Unterordner DSJ/2013/dem/Photos etc. erstellen
 */
function mod_mediadbsync_get_folders($vars) {
	$team_ids = [];
	$team_ids[] = wrap_id('usergroups', 'bulletin');
	$team_ids[] = wrap_id('usergroups', 'freizeit');
	$team_ids[] = wrap_id('usergroups', 'information');
	$team_ids[] = wrap_id('usergroups', 'oeffentlichkeitsarbeit');
	$team_ids[] = wrap_id('usergroups', 'organisator');
	$team_ids[] = wrap_id('usergroups', 'referent');
	$team_ids[] = wrap_id('usergroups', 'schiedsrichter');
	$team_ids[] = wrap_id('usergroups', 'turnierleitung');
	$team_ids[] = wrap_id('usergroups', 'technik');
	$team_ids[] = wrap_id('usergroups', 'veranstalter');

	$sql = 'SELECT events.event_id AS `objects[foreign_key]`
			, CONCAT(contact_abbr, "/", events.identifier) AS `objects[path]`
			, IF((SUBSTRING_INDEX(categories.path, "/", -1) = "einzel" OR SUBSTRING_INDEX(categories.path, "/", -1) = "mannschaft"), 1, 0) AS spieler
			, IF((SUBSTRING_INDEX(categories.path, "/", -1) = "mannschaft"), 1, 0) AS teams
			, (SELECT COUNT(*) FROM participations WHERE participations.event_id = events.event_id AND usergroup_id IN (%s)) AS team
		FROM events
		LEFT JOIN categories
			ON events.event_category_id = categories.category_id
		LEFT JOIN websites USING (website_id)
		LEFT JOIN contacts USING (contact_id)
		WHERE categories.main_category_id = %d
		AND (NOT ISNULL(date_begin) OR NOT ISNULL(date_end))
	';
	$sql = sprintf($sql,
        implode(',', $team_ids),
        wrap_category_id('events')
	);
	$events = wrap_db_fetch($sql, 'objects[foreign_key]');

	foreach ($events as $event_id => $values) {
		// 1 Photos			film	immer
		$index = $values['objects[foreign_key]'].'-1';
		$data[$index] = [
			'objects[foreign_key]' => $index,
			'objects[path]' => $values['objects[path]'],
			'objects[identifier]' => 'Photos',
			'objects[title][deu]' => 'Photos',
			'objects[category]' => 'folder'
		];
		// 2 Materialien	folder	immer
		$index = $values['objects[foreign_key]'].'-2';
		$data[$index] = [
			'objects[foreign_key]' => $index,
			'objects[path]' => $values['objects[path]'],
			'objects[identifier]' => 'Materialien',
			'objects[title][deu]' => 'Materialien',
			'objects[category]' => 'folder',
			'folder_settings[folder_property]' => 'quicklinks',
			'folder_settings[setting]' => 1,
			'folder_settings[inheritance]' => 1,
			'folder_settings[foreign_key]' => $index.'-1',
			'quicklinks[link_object]' => 'DSJ/Web/Materialien',
			'quicklinks[foreign_key]' => $index.'-1'
		];
		// 4 Team			list	if Gruppe E von Bulletin, Freizeit, Information, 
		// Öffentlichkeitsarbeit, Organisator, Referent, Schiedsrichter, Technik, Veranstalter
		if ($values['team']) {
			$index = $values['objects[foreign_key]'].'-4';
			$data[$index] = [
				'objects[foreign_key]' => $index,
				'objects[path]' => $values['objects[path]'],
				'objects[identifier]' => 'Team',
				'objects[title][deu]' => 'Team',
				'objects[category]' => 'list',
				'folder_settings[folder_property]' => 'exclude_from_search',
				'folder_settings[setting]' => 1,
				'folder_settings[inheritance]' => 1,
				'folder_settings[foreign_key]' => $index.'-1'
			];
		}
		// 5 Spieler		list	if Einzelturnier, Mannschaftsturnier, Gruppe = Spieler
		if ($values['spieler']) {
			$index = $values['objects[foreign_key]'].'-5';
			$data[$index] = [
				'objects[foreign_key]' => $index,
				'objects[path]' => $values['objects[path]'],
				'objects[identifier]' => 'Spieler',
				'objects[title][deu]' => 'Spieler',
				'objects[category]' => 'list'
			];
		}
		// 6 Teams 			list	if Mannschaftsturnier 
		if ($values['teams']) {
			$index = $values['objects[foreign_key]'].'-6';
			$data[$index] = [
				'objects[foreign_key]' => $index,
				'objects[path]' => $values['objects[path]'],
				'objects[identifier]' => 'Teams',
				'objects[title][deu]' => 'Teams',
				'objects[category]' => 'list'
			];
		}
		// 7 immer  
		if ($values['teams']) {
			$index = $values['objects[foreign_key]'].'-7';
			$data[$index] = [
				'objects[foreign_key]' => $index,
				'objects[path]' => $values['objects[path]'],
				'objects[identifier]' => 'Website',
				'objects[title][deu]' => 'Website',
				'objects[category]' => 'publication',
				'folder_settings[folder_property]' => 'inherit_public_access',
				'folder_settings[setting]' => 1,
				'folder_settings[inheritance]' => 1,
				'folder_settings[foreign_key]' => $index.'-1',
				'access_rights[group_object]' => 'Gruppen/public',
				'access_rights[access_right_property]' => 'read',
				'access_rights[show_access]' => 'no',
				'access_rights[foreign_key]' => $index.'-1'
			];
		}
	}
	return $data;
}
