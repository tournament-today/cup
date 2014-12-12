<?php namespace Syn\Cup;

use Illuminate\Support\ServiceProvider;
use Syn\Cup\Models\Cup;
use Syn\Cup\Models\Participant\Team;
use Syn\Cup\Models\Participant\Team\Member;
use Syn\Cup\Repositories\CupRepository;

class CupServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('syn/cup');

		Member::observe(new Observers\MemberObserver);
		Team::observe(new Observers\TeamObserver);

		$this->app->bindIf('command.syn.cup.progress', function ($app) {
			return new Scheduled\CupProgressScheduled();
		});
		$this->commands(
			'command.syn.cup.progress'
		);

		include __DIR__ . '/../../routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this -> app -> bind('Syn\Cup\Interfaces\CupRepositoryInterface', function()
		{
			return new CupRepository(new Cup);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
