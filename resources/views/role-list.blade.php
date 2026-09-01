<section class="section section-role-policy">
    <div class="row">
        @foreach ($rolePolicy as $roleStructure)
            <div class="card" style="max-width: 25em;">
                <h2 class="h4">
                    <i class="fa-solid fa-fw {{ $roleStructure['role']->icon() }}"></i>
                    {{ $roleStructure['role']->translation() }}
                </h2>
                @if (! $roleStructure['granted'] && ! $roleStructure['revoked'])
                    <p class="text-muted">@lang('ig-user::role-list.no-additional-permissions')</p>
                @endif
                <ul class="list-unstyled">
                    @foreach ($roleStructure['granted'] + $roleStructure['revoked'] as $policyMethod => $policyStructure)
                        <li @if (app()->hasDebugModeEnabled()) x-data="{ open: false }" @endif>
                            <div class="role-policy">
                                <div>
                                    @if ($policyStructure['allowed'])
                                        <i class="fa-solid fa-fw fa-plus text-success"></i>
                                    @else
                                        <i class="fa-solid fa-fw fa-minus text-danger"></i>
                                    @endif
                                    {{ $summary->label($policyMethod) }}
                                    @if (app()->hasDebugModeEnabled())
                                        <a href="#" x-on:click.prevent="open = true">args</a>
                                    @endif
                                </div>
                            </div>
                            @if (app()->hasDebugModeEnabled())
                                <template x-teleport="body">
                                    <div x-show="open" x-cloak>
                                        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-modal="true" aria-label="Arguments"
                                            x-on:keydown.escape.window="open = false"
                                            x-on:click.self="open = false">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $policyMethod }}</h5>
                                                        <button type="button" class="btn-close" aria-label="Close" x-on:click="open = false"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre class="pre-scrollable"><code>{{ $summary->describeArguments($policyStructure['arguments']) }}</code></pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-primary" x-on:click="open = false">@lang('ig-user::role-list.close')</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-backdrop fade show"></div>
                                    </div>
                                </template>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>
