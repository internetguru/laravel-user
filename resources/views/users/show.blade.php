<section
    class="section section-user-detail"
    style="padding-bottom: 1em"
    x-data="{
        editName: false,
        editEmail: false,
        editPhone: false,
        editRole: false,
    }"
    x-init="closeEdits = (opened) => {
        if (opened) {
            return false;
        }
        editName = false;
        editEmail = false;
        editPhone = false;
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
                            <input name="name" type="text" class="form-control" value="{{ $user->name }}" autocomplete="name" />
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
                    <span x-show="!editEmail">{{ $user->email }}</span>
                    <x-ig::form class="editable-skip" :recaptcha="false" x-show="editEmail" :action="route('users.update', $user)">
                        <div class="input-group">
                            <input name="email" type="email" class="form-control" value="{{ $user->email }}" autocomplete="email" />
                            <button type="submit" class="btn btn-primary">@lang('ig-user::user.save')</button>
                        </div>
                    </x-ig::form>
                </dd>
                {{-- phone --}}
                <dt>
                    @lang('ig-user::user.phone')
                    @can('crud', $user)
                        <button class="btn btn-link" @click.prevent="closeEdits(editPhone); editPhone = !editPhone">
                            <span x-show="!editPhone">@lang('ig-user::user.edit')</span>
                            <span x-show="editPhone">@lang('ig-user::user.cancel')</span>
                        </button>
                    @endcan
                </dt>
                <dd x-bind:class="{ 'user-edit-active': editPhone }">
                    <span x-show="!editPhone">
                        {{ $user->phone ?? '—' }}
                    </span>
                    <x-ig::form class="editable-skip" :recaptcha="false" x-show="editPhone" :action="route('users.update', $user)">
                        <div class="input-group">
                            <input name="phone" type="tel" class="form-control" value="{{ $user->phone }}" autocomplete="tel" />
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
            @if ($ownDetail)
                <div class="text-start">
                    <a class="btn btn-ico btn-simple mt-3" href="{{ route('logout') }}"><i class="fas fa-fw fa-right-from-bracket"></i>{{ Str::ucfirst(__('ig-user::user.logout')) }}</a>
                </div>
            @endif
        </div>
        <div class="card col col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.authentication')</h2>
            <p class="text-muted">@lang('ig-user::user.authentication-desc')</p>
            @if (!$ownDetail)
                <p>
                    {!! Str::inlineMarkdown(__('ig-user::user.authentication-info', ['url' => route('users.show', auth()->user())])) !!}
                </p>
            @endif
            <dl>
                @forelse($user->socialites as $socialite)
                    @php
                        $provider = $socialite->provider->value;
                    @endphp
                    <dt>
                        <x-ig-user::provider-icon :provider="$provider" class="socialite-{{ $provider }}-icon" />
                        {{ Str::ucfirst($provider) }}
                    </dt>
                    <dd class="mb-3" style="line-height: 1.7em; min-height: auto;">
                        {{ strlen($socialite->name) ? $socialite->name : __('ig-user::user.no-name') }}
                        @if ($ownDetail)
                            <a
                                class="btn btn-link link-danger"
                                href="{{ route('socialite.action', [
                                    'provider' => $provider,
                                    'action' => InternetGuru\LaravelUser\Enums\ProviderAction::DISCONNECT,
                                ]) }}"
                            >@lang('ig-user::socialite.unlink')</a>
                        @endif
                        @if ($socialite->email)
                            <br/>{{ $socialite->email }}
                        @endif
                    </dd>
                @empty
                    <p class="text-muted">@lang('ig-user::user.no-identities')</p>
                @endforelse
            </dl>
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::socialite.add')</h2>
            <dl>
                <x-ig-user::buttons
                    :action="InternetGuru\LaravelUser\Enums\ProviderAction::CONNECT"
                    :disabled="! $ownDetail"
                />
            </dl>
        </div>
    </div>
</section>
@can('merge', [$user, $user])
<section class="section user-merges" style="padding-top: 0;">
    <div class="row row-stretched">
        <div class="card col col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.merges')</h2>
            <p class="text-muted">@lang('ig-user::user.merges-info')</p>
            <dl class="mb-0 mt-3">
                @forelse ($user->mergedUsers() as $mergedUser)
                    <dt>{{ $mergedUser->name }}</dt>
                    <dd class="mb-3" style="line-height: 1.7em; min-height: auto; margin-left: 0;">
                        {{ $mergedUser->email }}
                        <x-ig::form class="editable-skip d-inline" :recaptcha="false" :action="route('users.unmerge', $user)">
                            <input type="hidden" name="merge_user_id" value="{{ $mergedUser->id }}" />
                            <button type="submit" class="btn btn-link link-danger">@lang('ig-user::user.unmerge')</button>
                        </x-ig::form>
                    </dd>
                @empty
                    <p class="text-muted">@lang('ig-user::user.merges-empty')</p>
                @endforelse
            </dl>
            @php
                // One row over the inline limit is enough to tell whether the whole list still
                // fits in the page or the picker has to search the server instead. Above it
                // nothing travels with the page: every keystroke searches the server.
                $mergeInlineLimit = (int) config('ig-user.merge_inline_limit', 100);
                $mergeCandidates = $user::mergeCandidateOptions($user, limit: $mergeInlineLimit + 1);
                $mergeSearchUrl = count($mergeCandidates) > $mergeInlineLimit
                    ? route('users.merge-candidates', $user)
                    : null;
                $mergeCandidates = $mergeSearchUrl ? [] : $mergeCandidates;
                // A list that already fits is simply shown: there is nothing to search through
                $mergeShowSearch = $mergeSearchUrl || count($mergeCandidates) > $user::MERGE_CANDIDATES_SHOWN;
            @endphp
            @if ($mergeSearchUrl || count($mergeCandidates))
                <h2 class="h3 mb-3 mt-3 fw-normal">@lang('ig-user::user.merge')</h2>
                <x-ig::form class="editable-skip" :recaptcha="false" :action="route('users.merge', $user)">
                    <div
                        @class(['merge-search', 'merge-search-static' => ! $mergeShowSearch])
                        x-data="mergeSearch(@js($mergeCandidates), @js($mergeSearchUrl), @js($user::MERGE_CANDIDATES_SHOWN))"
                    >
                        @if ($mergeShowSearch)
                            <input
                                type="search"
                                class="form-control"
                                autocomplete="off"
                                aria-controls="merge-candidate-list"
                                aria-label="@lang('ig-user::user.merges-select')"
                                placeholder="@lang('ig-user::user.merges-search')"
                                x-model="search"
                                x-on:input="onInput()"
                                x-on:keydown.arrow-down.prevent="move(1)"
                                x-on:keydown.arrow-up.prevent="move(-1)"
                                x-on:keydown.enter.prevent="addActive()"
                            />
                        @endif
                        {{-- Fixed height for as many rows as the search shows, so nothing below it jumps --}}
                        <ul class="merge-candidate-list" id="merge-candidate-list" role="listbox" aria-live="polite">
                            <template x-for="(candidate, index) in visible" :key="candidate.id">
                                <li
                                    class="merge-candidate"
                                    role="option"
                                    :class="{ active: index === active }"
                                    :aria-selected="index === active"
                                    x-on:mouseenter="active = index"
                                >
                                    <span class="merge-candidate-name" x-text="candidate.name"></span>
                                    <span class="merge-candidate-email" x-text="candidate.email"></span>
                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-primary merge-candidate-add"
                                        name="merge_user_id"
                                        :value="candidate.id"
                                        :data-index="index"
                                    >@lang('ig-user::user.merges-add')</button>
                                </li>
                            </template>
                            {{-- Without a search box the list always has rows, so none of the notes can apply --}}
                            @if ($mergeShowSearch)
                                <li class="merge-candidate-note" x-show="note === 'loading'">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    @lang('ig-user::user.merges-loading')
                                </li>
                                <li class="merge-candidate-note" x-show="note === 'hint'">@lang('ig-user::user.merges-hint')</li>
                                <li class="merge-candidate-note" x-show="note === 'none'">@lang('ig-user::user.merges-none')</li>
                                <li class="merge-candidate-note" x-show="note === 'more'">@lang('ig-user::user.merges-more')</li>
                            @endif
                        </ul>
                    </div>
                </x-ig::form>
            @endif
        </div>
    </div>
</section>
@endcan
@can('administrate', $user)
<section class="section user-history" style="padding-top: 0;">
    <div class="row row-stretched">
        <div class="card col col-centered">
            <h2 class="h3 mb-3 fw-normal">@lang('ig-user::user.history')</h2>
            <x-ig::association-history :model="$user" />
        </div>
    </div>
</section>
@endcan
