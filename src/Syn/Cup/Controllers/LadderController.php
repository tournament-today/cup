<?php namespace Syn\Cup\Controllers;


use Syn\Cup\Models\Ladder;
use Syn\Cup\Models\Participant\Team;
use Syn\Framework\Abstracts\Controller;


class LadderController extends Controller
{
    public $icon = 'ladder';

    /**
     * Creates controller instance
     *
     * @param CupRepositoryInterface $cup
     */
    public function __construct(LadderRepositoryInterface $ladder)
    {
        $this -> ladder = $ladder;
    }

    /**
     * Edit cup
     *
     * @param Cup $cup
     * @return mixed
     */
    public function edit(Ladder $cup = null)
    {
//        if(!$cup)
//            $cup = $this -> cup -> getNewModel();
//        $this -> title = trans('cup.cup-form-'. ($cup -> exists ? 'edit' : 'create'));
//
//        return $this -> onRequestMethod('post', function() use ($cup)
//            {
//                return $cup -> validateAndSave();
//            })
//            ?: $this -> view('pages.form', ['model' => $cup]);
    }



    /**
     * List of all cups
     *
     * @return mixed
     */
    public function index()
    {
//        $this -> title = trans_choice('cup.cup',2);
//        $models = $this -> cup -> findAll();
//        return $this -> view('pages.cup.index', compact('models'));
    }
}