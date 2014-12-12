<?php namespace Syn\Cup\Controllers;

use Syn\Cup\Models\CompetitionType;
use Syn\Framework\Abstracts\Controller;

class CompetitionTypeController extends Controller
{
	public $icon = 'cup';

	public function __construct(CompetitionType $model)
	{
		$this -> model = $model;
	}

	public function edit(CompetitionType $model = null)
	{
		if(!$model)
			$model = $this->model;

		if(!$model->allowEditOrCreate())
			return $this -> notAllowed('edit or create competition type', 'missing access rights');

		return $this -> onRequestMethod('post', function() use ($model)
			{
				return $model -> validateAndSave();
			})
			?: $this -> view('pages.form', compact('model'));
	}

	public function show(CompetitionType $model, $name)
	{
		return;
		if(!$model->allowView())
			return $this -> notAllowed('view competition type', 'missing access rights');
		return $this -> view('pages.show', compact('model'));
	}

	public function index()
	{

		if(!$this->model->allowView())
			return $this -> notAllowed('view competition type', 'missing access rights');
		$items = $this -> model -> all();
		$this -> title = trans_choice('competition_type.competition-type',2);
		return $this -> view('pages.cup.types_index', compact('items'));
	}
}