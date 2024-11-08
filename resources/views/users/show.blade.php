<section
    class="section section-user-detail"
    x-data="{
        editName: false,
        editEmail: false,
        editRole: false,
    }"
    x-init="closeEdits = (opened) => {
        if (opened) {
            return false;
        }
        editName = false;
        editEmail = false;
        editRole = false;
    }"
>
    <div class="row row-stretched">
        <div class="card col col-narrow col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.information')</h2>
            <dl class="mb-0">
                {{-- name --}}
                <dt>
                    @lang('ig-user::user.name')
                    @can('crud', $user)
                        <a @click.prevent="closeEdits(editName); editName = !editName" href="#">
                            <span x-show="!editName">@lang('ig-user::user.edit')</span>
                            <span x-show="editName">@lang('ig-user::user.cancel')</span>
                        </a>
                    @endcan
                </dt>
                <dd>
                    <span x-show="!editName">{{ $user->name }}</span>
                    <x-ig::form :recaptcha="false" x-show="editName" :action="route('users.update', $user)">
                        <div class="input-group">
                            <input name="name" type="text" class="form-control" value="{{ $user->name }}" />
                            <button type="submit" class="btn btn-primary">@lang('ig-user::user.save')</button>
                        </div>
                    </x-ig::form>
                </dd>
                {{-- email --}}
                <dt>
                    @lang('ig-user::user.email')
                    @can('administrate', $user)
                        <a @click.prevent="closeEdits(editEmail); editEmail = !editEmail" href="#">
                            <span x-show="!editEmail">@lang('ig-user::user.edit')</span>
                            <span x-show="editEmail">@lang('ig-user::user.cancel')</span>
                        </a>
                    @endcan
                </dt>
                <dd>
                    <span x-show="!editEmail">{{ $user->email }}</span>
                    <x-ig::form :recaptcha="false" x-show="editEmail" :action="route('users.update', $user)">
                        <div class="input-group">
                            <input name="email" type="email" class="form-control" value="{{ $user->email }}" />
                            <button type="submit" class="btn btn-primary">@lang('ig-user::user.save')</button>
                        </div>
                    </x-ig::form>
                </dd>
                {{-- role --}}
                <dt>
                    @lang('ig-user::user.role')
                    @can('administrate', $user)
                        <a @click.prevent="closeEdits(editRole); editRole = !editRole" href="#">
                            <span x-show="!editRole">@lang('ig-user::user.edit')</span>
                            <span x-show="editRole">@lang('ig-user::user.cancel')</span>
                        </a>
                    @endcan
                </dt>
                <dd>
                    <span x-show="!editRole">@lang('ig-user::user.roles.' . $user->role->value)</span>
                    <x-ig::form :recaptcha="false" x-show="editRole" :action="route('users.update', $user)">
                        <div class="input-group">
                            <select name="role" class="form-select" value="{{ $user->role->value }}">
                                @foreach (\InternetGuru\LaravelUser\Enums\Role::cases() as $role)
                                    @if(auth()->user()->can('setRole', [$user, $role]))
                                        <option value="{{ $role->value }}">{{ __('ig-user::user.roles.' . $role->value) }}</option>
                                    @endcan
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">@lang('ig-user::user.save')</button>
                        </div>
                    </x-ig::form>
                </dd>
            </dl>
            <p class="text-end mb-0"><a href="{{ route('logout') }}">@lang('ig-user::user.logout')</a></p>
        </div>
        <div class="card col col-narrow col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.authentication')</h2>
            {{-- TODO list connected providers --}}
            <dl>
                @foreach($user->socialites as $socialite)
                    @php
                        $provider = $socialite->provider->value;
                    @endphp
                    <dt>
                        @lang("ig-user::socialite.$provider")
                        <a class="ms-1" href="{{ route('socialite.action', [
                            'provider' => $provider,
                            'action' => InternetGuru\LaravelUser\Enums\ProviderAction::DISCONNECT,
                        ]) }}">@lang('ig-user::socialite.unlink')</a>
                    </dt>
                    <dd>{{ $socialite->email }}</dd>
                @endforeach
            </dl>
            {{-- TODO allow to connect any socialite --}}
        </div>
    </div>
</section>