<?php namespace Syn\Cup\Controllers;

use Carbon\Carbon;
use DB;
use Input;
use Redirect;
use Syn\Cup\Interfaces\CupRepositoryInterface;
use Syn\Cup\Models\Cup;
use Syn\Cup\Models\Participant\Team;
use Syn\Cup\Models\Participant\Team\Member;
use Syn\Framework\Abstracts\Controller;
use Syn\Framework\Exceptions\UnexpectedResultException;
use Syn\Gamer\Models\Gamer;

class CupController extends Controller
{
	public $icon = 'cup';

	public function __construct(CupRepositoryInterface $cup)
	{
		$this -> cup = $cup;
	}

	public function edit(Cup $cup = null)
	{
		if(!$cup)
			$cup = $this -> cup -> getNewModel();
		$this -> title = trans('cup.cup-form-'. ($cup -> exists ? 'edit' : 'create'));

		return $this -> onRequestMethod('post', function() use ($cup)
			{
				return $cup -> validateAndSave();
			})
			?: $this -> view('pages.form', ['model' => $cup]);
	}

	public function show(Cup $cup, $name = null)
	{
		$this -> title = $cup -> name;
		$teamSignUp = new Team;
		$teamSignUp->cup_id = $cup -> id;
		$teamSignUp->gamer_id = $this -> getVisitor()->id;

		return $this->onRequestMethod('post', function() use ($teamSignUp, $cup)
		{
			if(Input::has('team-create'))
			{
				$teamSignUp->_validation['members'][] = "between:{$cup->team_size},{$cup->team_size}";
				$validator = $teamSignUp->getValidator(
					Input::all(),
					array_keys(array_except($teamSignUp->_validation,['cup_id','gamer_id']))
				);
				if($validator->fails())
					return $cup->redirectView->withInput()->withErrors($validator);

				$teamSignUp -> name = Input::get('name');
				$teamSignUp -> clan_id = Input::get('clan_id', null);

				DB::beginTransaction();

				$teamSignUp->save();

				$gamers = explode(',', Input::get('members'));
				foreach($gamers as $i => $gamer_id)
				{
					$member = new Member();
					$member -> participant_team_id = $teamSignUp->id;
					$member -> gamer_id = $gamer_id;
					$member -> invited_at = Carbon::now();
					if($i == 0 && !in_array($this->getVisitor()->id, $gamers))
						$member -> leader = true;
					else
					if(in_array($this->getVisitor()->id, $gamers) && $gamer_id == $this->getVisitor()->id)
					{
						$member -> leader = true;
						$member -> accepted_at = Carbon::now();
					}

					$member -> save();
				}

				DB::commit();

				return $cup->redirectView;
			}
			if(Input::has('team-delete') && Input::has('team-delete-id'))
			{
				$teamSignUp = $teamSignUp -> find(Input::get('team-delete-id'));
				if(!$teamSignUp && !$teamSignUp->visitorIsLeader)
					return;
				$teamSignUp -> delete();
				return $cup->redirectView;
			}
		}) ?: $this -> view('pages.cup.show', compact('cup','teamSignUp'));
	}

	public function index()
	{
		$this -> title = trans_choice('cup.cup',2);
		$models = $this -> cup -> findAll();
		return $this -> view('pages.cup.index', compact('models'));
	}

	public function gamerIndex(Gamer $gamer, $name = null)
	{
		if($this->getVisitor()->id != $gamer->id && !$this->getVisitor()->admin)
			return $this->notAllowed('view tournament participation of gamer', 'no rights');

		$this -> title = trans_choice('cup.cup',2);

		$items = $gamer -> acceptedTeamMemberships;
		return $this -> view('pages.cup.gamer.participation', compact('gamer','items'));
	}


	public function joinTeam($cup, $cupName, $team, $teamName)
	{

		$member = $team->members()->where('gamer_id', $this->getVisitor()->id)->first();
		if(!$member)
			return $this->notAllowed('accept invitation for team', 'not invited');

		if($member->accepted_at)
			return $this->notAllowed('accept invitation for team', 'already accepted');

		if($member->deleted_at)
			return $this->notAllowed('accept invitation for team', 'gamer deleted');

		if(Member::registerForTeam($team->id, $this->getVisitor()->id))
			return $this -> redirect($cup->redirectView);
		else
			throw new UnexpectedResultException("Gamer {$this->getVisitor()->id} could not join team {$team->id}");
	}
}