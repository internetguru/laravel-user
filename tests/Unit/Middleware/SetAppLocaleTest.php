<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetAppLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SetAppLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshApplication();
        Config::set('languages', ['en' => 'English', 'es' => 'Spanish']);
    }

    public function testHandleWithValidLocale()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path?lang=en', 'GET');
        $next = function ($request) {
            return $request;
        };

        $response = $middleware->handle($request, $next);
        $this->assertEquals('en', app()->getLocale());

        $request = Request::create('/some-other-path', 'GET');
        $response = $middleware->handle($request, $next);
        $this->assertEquals('en', app()->getLocale());
    }

    public function testHandleWithInvalidLocale()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/some-path?lang=fr', 'GET');
        $next = function ($request) {
            return $request;
        };

        $this->assertEquals(config('app.locale'), app()->getLocale());
        $middleware->handle($request, $next);
    }

    public function testHandleWithEmptyLocale()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/', 'GET');
        $next = function ($request) {
            return $request;
        };

        $response = $middleware->handle($request, $next);

        $this->assertEquals('en', app()->getLocale());
    }

    public function testDetectLang()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'es-ES,es;q=0.9,en;q=0.8');

        $lang = $middleware->detectLang($request);

        $this->assertEquals('es', $lang);
    }

    public function testDetectLangWithUnsupportedLanguage()
    {
        $middleware = new SetAppLocale;
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9');

        $lang = $middleware->detectLang($request);

        $this->assertEquals('en', $lang);
    }
}
