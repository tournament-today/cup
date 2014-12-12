<?php

Route::group(['namespace' => 'Syn\Cup\Controllers'], function()
{
	Route::group([
		'before' 	=> ['auth'],
	], function()
	{
		/**
		 * MODEL BINDING
		 */
		Route::model('cup', 'Syn\Cup\Models\Cup');
		Route::model('competition_type', 'Syn\Cup\Models\CompetitionType');
		Route::model('team', 'Syn\Cup\Models\Participant\Team');

		/**
		 * Competition type
		 */
		Route::any('/tournament/type/create', [
			'as'	=> 'CompetitionType@create',
			'uses'	=> 'CompetitionTypeController@edit'
		]);

		Route::any('/tournament/types', [
			'as'	=> 'CompetitionType@view',
			'uses'	=> 'CompetitionTypeController@index'
		]);
		Route::any('/tournament/types', [
			'as'	=> 'CompetitionType@list',
			'uses'	=> 'CompetitionTypeController@index'
		]);
		Route::any('/tournament/type/{competition_type}/{name}/edit', [
			'as'	=> 'CompetitionType@edit',
			'uses'	=> 'CompetitionTypeController@edit'
		]);
		/**
		 * Cup
		 */

		Route::any('/tournament/{cup}/{name}/delete', [
			'as'	=> 'Cup@delete',
			'uses'	=> 'CupController@delete'
		]);
		Route::any('/tournament/{cup}/{name}/participants', [
			'as'	=> 'Cup@participants',
			'uses'	=> 'CupController@participants'
		]);
		Route::any('/tournament/create', [
			'as'	=> 'Cup@create',
			'uses'	=> 'CupController@edit'
		]);
		Route::any('/tournament/{cup}/{name}/edit', [
			'as'	=> 'Cup@edit',
			'uses'	=> 'CupController@edit'
		]);
		Route::any('/tournament/{cup}/{name}', [
			'as'	=> 'Cup@view',
			'uses'	=> 'CupController@show'
		]);

		Route::any('/tournaments', [
			'as'	=> 'Cup@list',
			'uses'	=> 'CupController@index'
		]);

		/**
		 * Invites
		 */
		/*Route::any('/gamer/{gamer}/{name}/tournaments', [
			'as'	=> 'Cup@invites',
			'uses' 	=> 'CupController@gamerIndex'
		]);*/
		Route::any('/tournament/{cup}/{name}/{team}/{team_name}/join', [
			'as'	=> 'Cup@joinTeam',
			'uses'	=> 'CupController@joinTeam'
		]);

		/**
		 * Match results
		 */
		Route::any('/match/{id}/enter-result', [
			'as'	=> 'Match@enterResult',
			'uses'	=> 'MatchController@enterResult'
		]);
	});
});