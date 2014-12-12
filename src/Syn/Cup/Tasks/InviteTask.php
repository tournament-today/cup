<?php namespace Syn\Cup\Tasks;

use Mail;
use Syn\Cup\Models\Participant\Team\Member;
use Syn\Notification\Models\Notification;

class InviteTask
{
	public function send($job, $data)
	{
		$team_member_id = array_get($data, 'id');
		if(!$team_member_id)
			return $job -> delete();
		$member = Member::find($team_member_id);
		if(!$member)
			return $job -> delete();

		if($member -> accepted_at || $member -> deleted_at)
			return $job -> delete();

		$notification = new Notification;
		$notification -> unguard();
		$notification -> fill([
			'title' => "Participate in tournament: \"{$member->cup->name}\"",
			'receiver_id' => $member -> gamer_id,
			'body' => \View::make('e-mail.invited_for_team', compact('member')),
			'team_id' => $member -> team_id,
			'cup_id' => $member -> cup -> id,
			'action_view_url' => $member -> cup -> linkView
		]);
		$notification -> save();

		// send mail
		Mail::send('e-mail.invited_for_team',compact('member'), function($message) use ($member)
		{
			$message -> to($member -> gamer -> email_address, $member -> gamer -> publishedName) -> subject("Participate in tournament: {$member->cup->name}");
		});

		$job -> delete();
	}
}