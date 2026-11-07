<section class="section section-user-list">
    <livewire:table-model-browser
        model="\App\Models\User@summary"
        :viewAttributes="[
            'name' => __('ig-user::user.summary.name'),
            'email' => __('ig-user::user.summary.email'),
            'role' => __('ig-user::user.summary.role'),
            'logged_at' => __('ig-user::user.summary.logged_at'),
        ]"
        :filters="[
            'name' => [
                'label' => __('ig-user::user.summary.name'),
                'column' => 'name',
            ],
            'email' => [
                'label' => __('ig-user::user.summary.email'),
                'column' => 'email',
                'ascii_fast' => true,
            ],
            'role' => [
                'type' => 'options',
                'label' => __('ig-user::user.summary.role'),
                'options' => \App\Models\User::roleOptions(),
                'column' => 'role',
            ],
            'logged' => [
                'type' => 'date',
                'label' => __('ig-user::user.summary.logged_at'),
                'column' => 'logged_at',
            ],
            'never-logged-in' => [
                'type' => 'checkbox',
                'label' => __('ig-user::user.summary.never-logged-in'),
            ],
        ]"
        filterSessionKey="laravel-user-user-filters"
        :formats="[
            'name' => 'formatUserNameLink',
            'role' => 'formatUserRoleLabel',
            'logged_at' => 'formatUserLoggedAt',
        ]"
        :rawFormats="[
            'role' => 'formatUserRole',
        ]"
        :enableSort="false"
        defaultSortColumn="name"
        defaultSortDirection="asc"
        :column-widths="[
            'name' => 'minmax(7em, 0.7fr)',
            'email' => 'minmax(10em, 1fr)',
            'role' => 'minmax(max-content, max-content)',
            'logged_at' => 'minmax(max-content, max-content)',
        ]"
    />
</section>
