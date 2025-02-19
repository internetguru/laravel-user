<section class="section section-user-list">
    <livewire:table-model-browser
        model="\App\Models\User@summary"
        :viewAttributes="[
            'name' => __('ig-user::user.summary.name'),
            'email' => __('ig-user::user.summary.email'),
            'role' => __('ig-user::user.summary.role'),
        ]"
        :filterAttributes="[
            'name',
            'email',
            'role',
        ]"
        :formats="[
            'name' => 'formatUserNameLink',
            'email' => 'formatUserEmail',
            'role' => 'formatUserRole',
        ]"
        :enableSort="$enableSort ?? true"
        defaultSortBy="name"
    >
</section>
