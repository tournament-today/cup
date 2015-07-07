<?php namespace Syn\Cup\Models;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Syn\Framework\Abstracts\Model;

class Ladder extends Model
{
    use SoftDeletingTrait;


    /**
     * Validation definition
     * @var array
     */
    public $_validation = [
        'clan_id' => ['required', 'exists:clans,id'],
        'gamer_id' => ['required', 'exists:gamers,id'],
        'name' => ['required'],
        'description' => ['required'],
        'steam_game_id' => ['required'],
        'invite_only' => ['boolean'],
        'public' => ['boolean'],
        'disputable' => ['boolean'],
        'human_admin' => ['boolean'],
        'use_trustworthiness' => ['boolean'],
        'commercial' => ['boolean'],
        'entry_fee' => ['integer', 'disabled'],
        'award_honor' => ['boolean'],
        'award_prizes' => ['boolean'],
        'starts_at' => ['required', 'date'],
        'closes_at' => ['date'],
        'team_size' => ['integer', 'min:1', 'max:10'],
        'days' => ['integer', 'min:1', 'max: 30', 'disabled'],
        'play_weekdays' => ['boolean', 'required_without:play_weekends'],
        'play_weekends' => ['boolean', 'required_without:play_weekdays'],
        'daily_play_time_starts' => ['time'],
        'daily_play_time_ends' => ['time'],
    ];

    /**
     * Type definition
     * @var array
     */
    public $_types = [
        'gamer_id' => 'Visitor.id',
        'name' => 'text',
        'clan_id' => 'select',
        'description' => 'wysiwyg',
        'steam_game_id' => 'select2',
        'invite_only' => 'toggle',
        'human_admin' => 'toggle',
        'disputable' => 'toggle',
        'use_trustworthiness' => 'toggle',
        'public' => 'toggle',
        'commercial' => 'toggle',
        'entry_fee' => 'text',
        'award_honor' => 'toggle',
        'award_prizes' => 'toggle',
        'starts_at' => 'datetime',
        'closes_at' => 'datetime',
        'team_size' => 'slider',
        'days' => 'slider',
        'play_weekdays' => 'toggle',
        'play_weekends' => 'toggle',
        'daily_play_time_starts' => 'time',
        'daily_play_time_ends' => 'time',
    ];

    /**
     * Loads the following methods for select input values
     * @var array
     */
    public $_select_values = [
        'steam_game_id' => ['Syn\Steam\Models\SteamGame', 'selectable'],
        'clan_id' => ['Visitor', 'clans'],
    ];
}