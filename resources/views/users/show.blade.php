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
    @php
        $ownDetail = $user->id == auth()->user()->id;
    @endphp
    <div class="row row-stretched">
        <div class="card col col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.information')</h2>
            <dl class="mb-0">
                {{-- name --}}
                <dt>
                    @lang('ig-user::user.name')
                    @can('crud', $user)
                        <button class="btn btn-link" @click.prevent="closeEdits(editName); editName = !editName">
                            <span x-show="!editName">@lang('ig-user::user.edit')</span>
                            <span x-show="editName">@lang('ig-user::user.cancel')</span>
                        </button>
                    @endcan
                </dt>
                <dd x-bind:class="{ 'user-edit-active': editName }">
                    <span x-show="!editName">
                        {{ $user->name }}
                    </span>
                    <x-ig::form class="editable-skip" :recaptcha="false" x-show="editName" :action="route('users.update', $user)">
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
                        <button class="btn btn-link" @click.prevent="closeEdits(editEmail); editEmail = !editEmail">
                            <span x-show="!editEmail">@lang('ig-user::user.edit')</span>
                            <span x-show="editEmail">@lang('ig-user::user.cancel')</span>
                        </button>
                    @endcan
                </dt>
                <dd x-bind:class="{ 'user-edit-active': editEmail }">
                    <span x-show="!editEmail"><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></span>
                    <x-ig::form class="editable-skip" :recaptcha="false" x-show="editEmail" :action="route('users.update', $user)">
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
                        <button class="btn btn-link" @click.prevent="closeEdits(editRole); editRole = !editRole">
                            <span x-show="!editRole">@lang('ig-user::user.edit')</span>
                            <span x-show="editRole">@lang('ig-user::user.cancel')</span>
                        </button>
                    @endcan
                </dt>
                <dd x-bind:class="{ 'user-edit-active': editRole }">
                    <span x-show="!editRole">{{ $user->role->translation() }}</span>
                    <x-ig::form class="editable-skip" :recaptcha="false" x-show="editRole" :action="route('users.update', $user)">
                        <div class="input-group">
                            <select name="role" class="form-select" value="{{ $user->role->value }}">
                                @foreach ($user::roles()::cases() as $role)
                                    @if(auth()->user()->can('setRole', [$user, $role->level()]))
                                        <option
                                            value="{{ $role->value }}"
                                            @if($role->value == $user->role->value) selected @endif
                                        >{{ $role->translation() }}</option>
                                    @endcan
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">@lang('ig-user::user.save')</button>
                        </div>
                    </x-ig::form>
                </dd>
            </dl>
        </div>
        <div class="card col col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.authentication')</h2>
            @if (!$ownDetail)
                <p>
                    {!! Str::inlineMarkdown(__('ig-user::user.authentication-info', ['url' => route('users.show', auth()->user())])) !!}
                </p>
            @endif
            <dl>
                @foreach($user->socialites as $socialite)
                    @php
                        $provider = $socialite->provider->value;
                    @endphp
                    <dt>
                        {{ $socialite->name }} ({{ Str::ucfirst($provider) }})
                        @if ($ownDetail)
                            <a
                                class="btn btn-link link-danger"
                                href="{{ route('socialite.action', [
                                    'provider' => $provider,
                                    'action' => InternetGuru\LaravelUser\Enums\ProviderAction::DISCONNECT,
                                ]) }}"
                            >@lang('ig-user::socialite.unlink')</a>
                        @endif
                    </dt>
                    <dd class="d-flex flex-wrap gap-2">
                        <a href="mailto:{{ $socialite->email }}">{{ $socialite->email }}</a>
                        @if ($socialite->email != $user->email)
                            <x-ig::form class="editable-skip" :recaptcha="false" :action="route('users.update', $user)">
                                <input type="hidden" name="email" value="{{ $socialite->email }}" />
                                <button type="submit" class="btn btn-link">@lang('ig-user::user.set-primary')</button>
                            </x-ig::form>
                        @endif
                    </dd>
                @endforeach
                <dt>@lang('ig-user::socialite.add')</dt>
                <dd></dd>
                <x-ig-user::buttons
                    :action="InternetGuru\LaravelUser\Enums\ProviderAction::CONNECT"
                    :disabled="! $ownDetail"
                />
            </dl>
        </div>
    </div>
</section>
