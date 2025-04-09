<section class="section section-user-list">
    <livewire:table-model-browser
        model="\App\Models\User"
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
            'role' => 'formatUserRole',
        ]"
        :enableSort="$enableSort ?? true"
        :defaultSort="[
            'name' => 'asc',
            'email' => 'asc',
        ]"
        :column-widths="[
            'name' => 'minmax(7em, 1fr)',
            'email' => 'minmax(10em, 1fr)',
        ]"

    >
</section>
