<section class="section section-user-list">
    <livewire:table-model-browser
        model="\App\Models\User@summary"
        :viewAttributes="[
            'name' => __('ig-user::user.summary.name'),
            'email' => __('ig-user::user.summary.email'),
            'role' => __('ig-user::user.summary.role'),
        ]"
        :formats="[
            'name' => 'formatUserNameLink',
            'role' => 'formatUserRole',
        ]"
    >
</section>
