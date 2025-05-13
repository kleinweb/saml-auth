@props(['users'])

<ul>
    @foreach ($users as $user)
        <x-kleinweb-auth::import-users.results-list-item
            :id="$user->id"
            :has-authz="$user->hasAuthz"
            :is-existing="$user->isExisting"
            :is-new="$user->isNew"
            :username="$user->username"
        />
    @endforeach
</ul>
