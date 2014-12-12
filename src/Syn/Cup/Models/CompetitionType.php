<?php namespace Syn\Cup\Models;

use App;
use Syn\Framework\Abstracts\Model;

class CompetitionType extends Model
{
	public $_validation = [
		'name' => ['required'],
		'elimination' => ['boolean'],

		'elimination_after' => ['integer', 'between:0,3', 'required_if:elimination,true'],

		'points_win' => ['integer', 'required_without:elimination', 'min:-10', 'max:10'],
		'points_draw' => ['integer', 'required_without:elimination', 'min:-10', 'max:10'],
		'points_loss' => ['integer', 'required_without:elimination', 'min:-10', 'max:10'],
		'points_forfeit' => ['integer', 'required_without:elimination', 'min:-10', 'max:10'],
		'points_no_show' => ['integer', 'required_without:elimination', 'min:-10', 'max:10'],

		'bye_enabled' => ['boolean'],
		'selectable' => ['boolean'],
		'admin_only' => ['boolean'],
	];
	public $_types = [

		'name' => 'text',
		'elimination' => 'toggle',

		'elimination_after' => 'slider',

		'points_win' => 'slider',
		'points_draw' => 'slider',
		'points_loss' => 'slider',
		'points_forfeit' => 'slider',
		'points_no_show' => 'slider',

		'bye_enabled' => 'toggle',
		'selectable' => 'toggle',
		'admin_only' => 'toggle',
	];

	public function allowCreate()
	{
		return App::make('Visitor') -> admin;
	}

	public function allowEdit()
	{
		return $this -> allowCreate();
	}


}