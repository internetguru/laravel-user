<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use InternetGuru\LaravelUser\Http\Middleware\SetAppLocale;
use Tests\TestCase;

class SetAppLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('languages', ['en' => 'English', 'es' => 'Spanish']);
    }

    public function test_handle_with_valid_lang_parameter()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path?lang=es', 'GET');
        $next = function ($request) {
            return $request;
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals('es', App::getLocale());
        $this->assertEquals('es', Session::get('locale'));
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals('/some-path', parse_url($response->getTargetUrl(), PHP_URL_PATH));
    }

    public function test_handle_with_invalid_lang_parameter()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path?lang=fr', 'GET');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals(config('app.locale'), App::getLocale());
        $this->assertEquals(config('app.locale'), Session::get('locale'));
    }

    public function test_handle_uses_authenticated_user_language()
    {
        $user = User::factory()->create(['lang' => 'es']);
        Auth::login($user);

        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals('es', App::getLocale());
    }

    public function test_handle_uses_session_locale()
    {
        Session::put('locale', 'es');

        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals('es', App::getLocale());
    }

    public function test_handle_detects_language_from_request()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $request->headers->set('Accept-Language', 'es,en;q=0.8');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals('es', App::getLocale());
        $this->assertEquals('es', Session::get('locale'));
    }

    public function test_handle_defaults_to_app_locale()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals(config('app.locale'), App::getLocale());
        $this->assertEquals(config('app.locale'), Session::get('locale'));
    }

    public function test_set_lang_updates_user_language()
    {
        $user = User::factory()->create(['lang' => 'en']);
        Auth::login($user);

        $middleware = new SetAppLocale;
        $middleware->setLang('es');

        $user->refresh();
        $this->assertEquals('es', $user->lang);
        $this->assertEquals('es', App::getLocale());
        $this->assertEquals('es', Session::get('locale'));
    }

    public function test_detect_lang_returns_supported_language()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $request->headers->set('Accept-Language', 'es,en;q=0.8');

        $lang = $middleware->detectLang($request);

        $this->assertEquals('es', $lang);
    }

    public function test_detect_lang_returns_default_when_no_supported_language()
    {
        Config::set('app.locale', 'en');
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $request->headers->set('Accept-Language', 'fr,de;q=0.8');

        $lang = $middleware->detectLang($request);

        $this->assertEquals('en', $lang);
    }

    public function test_handle_skips_browser_detection_when_lang_domains_configured()
    {
        Config::set('ig-common.lang_domains', ['en' => 'giftcarder.io']);
        Config::set('app.locale', 'cs');
        Config::set('languages', ['cs' => 'Česky', 'en' => 'English']);

        $middleware = new SetAppLocale;
        $request = Request::create('/some-path', 'GET');
        $request->headers->set('Accept-Language', 'en,en-US;q=0.9');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals('cs', App::getLocale());
        $this->assertFalse(Session::has('locale'));
    }

    public function test_handle_enforces_lang_domain_language()
    {
        Config::set('ig-common.lang_domains', ['en' => 'giftcarder.io']);
        Config::set('languages', ['cs' => 'Česky', 'en' => 'English']);

        $middleware = new SetAppLocale;
        $request = Request::create('http://giftcarder.io/some-path', 'GET');
        $next = function ($request) {
            return $request;
        };

        $middleware->handle($request, $next);

        $this->assertEquals('en', App::getLocale());
        $this->assertEquals('en', Session::get('locale'));
    }

    public function test_handle_redirects_lang_param_to_lang_domain()
    {
        Config::set('ig-common.lang_domains', ['en' => 'giftcarder.io']);
        Config::set('languages', ['cs' => 'Česky', 'en' => 'English']);

        $middleware = new SetAppLocale;
        $request = Request::create('http://qrpoukazy.cz/some-path?lang=en', 'GET', ['lang' => 'en']);
        $next = function ($request) {
            return $request;
        };

        $response = $middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('giftcarder.io', $response->getTargetUrl());
    }

    public function test_handle_session_locale_redirects_to_lang_domain()
    {
        Config::set('ig-common.lang_domains', ['en' => 'giftcarder.io']);
        Config::set('languages', ['cs' => 'Česky', 'en' => 'English']);
        Session::put('locale', 'en');

        $middleware = new SetAppLocale;
        $request = Request::create('http://qrpoukazy.cz/some-path', 'GET');
        $next = function ($request) {
            return $request;
        };

        $response = $middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('giftcarder.io', $response->getTargetUrl());
    }

    public function test_handle_on_lang_domain_redirects_mismatched_session_locale()
    {
        Config::set('ig-common.lang_domains', ['en' => 'giftcarder.io']);
        Config::set('app.www', 'qrpoukazy.cz');
        Config::set('languages', ['cs' => 'Česky', 'en' => 'English']);
        Session::put('locale', 'cs');

        $middleware = new SetAppLocale;
        $request = Request::create('http://giftcarder.io/some-path', 'GET');
        $next = function ($request) {
            return $request;
        };

        $response = $middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('qrpoukazy.cz', $response->getTargetUrl());
    }
}
